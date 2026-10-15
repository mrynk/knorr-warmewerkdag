<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

final class AddressService
{
    /**
     * Look up a Dutch address from a postcode and house number.
     *
     * The house number may include an addition, for example "12a" or "12-a".
     * A null return means the combination does not exist. Transport failures
     * and server errors are thrown so they are not treated as an unknown address.
     *
     * @return array{street: string, city: string}|null
     *
     * @throws HttpClientException
     */
    public function lookup(string $zipcode, string $houseNumber): ?array
    {
        $parsed = $this->parse($zipcode, $houseNumber);

        if ($parsed === null) {
            return null;
        }

        $cacheKey = 'postcode-eu.'.$parsed['postcode'].'.'.$parsed['houseNumber'].'.'.$parsed['addition'];
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            $address = $this->addressFromPayload($cached);

            if ($address !== null) {
                return $address;
            }
        }

        $payload = $this->fetch($parsed['postcode'], $parsed['houseNumber'], $parsed['addition']);

        if ($payload === null) {
            return null;
        }

        $address = $this->matchingAddress($payload, $parsed['addition']);

        if ($address === null) {
            return null;
        }

        Cache::put($cacheKey, $address, now()->addMonth());

        return $address;
    }

    /**
     * @return array{postcode: string, houseNumber: string, addition: string}|null
     */
    private function parse(string $zipcode, string $houseNumber): ?array
    {
        $postcode = mb_strtoupper(preg_replace('/\s+/', '', mb_trim($zipcode)) ?? '');

        if (preg_match('/^[1-9]\d{3}[A-Z]{2}$/', $postcode) !== 1) {
            return null;
        }

        if (preg_match('/^(?<number>[1-9]\d{0,4})(?<rest>.*)$/u', mb_trim($houseNumber), $matches) !== 1) {
            return null;
        }

        $rest = $matches['rest'];

        if ($rest === '') {
            $addition = '';
        } elseif (preg_match('/^[\s-]*(?<addition>.{1,6})$/u', $rest, $additionMatch) === 1) {
            $addition = $additionMatch['addition'];
        } else {
            return null;
        }

        return [
            'postcode' => $postcode,
            'houseNumber' => $matches['number'],
            'addition' => $addition,
        ];
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws HttpClientException
     */
    private function fetch(string $postcode, string $houseNumber, string $addition): ?array
    {
        $path = 'addresses/postcode/'.rawurlencode($postcode).'/'.rawurlencode($houseNumber);

        if ($addition !== '') {
            $path .= '/'.rawurlencode($addition);
        }

        $response = Http::withBasicAuth(
            config()->string('services.postcode_eu.key'),
            config()->string('services.postcode_eu.secret'),
        )
            ->baseUrl('https://api.postcode.eu/nl/v1')
            ->connectTimeout(3)
            ->timeout(5)
            ->retry(
                2,
                200,
                fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429)),
                throw: false,
            )
            ->get($path);

        if ($response->notFound()) {
            return null;
        }

        $response->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            return null;
        }

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{street: string, city: string}|null
     */
    private function matchingAddress(array $payload, string $addition): ?array
    {
        $returnedAddition = $payload['houseNumberAddition'] ?? null;

        if (! is_string($returnedAddition)) {
            return null;
        }

        if (mb_strtolower($returnedAddition) !== mb_strtolower($addition)) {
            return null;
        }

        return $this->addressFromPayload($payload);
    }

    /**
     * @param  array<mixed>  $payload
     * @return array{street: string, city: string}|null
     */
    private function addressFromPayload(array $payload): ?array
    {
        $street = $payload['street'] ?? null;
        $city = $payload['city'] ?? null;

        if (! is_string($street) || $street === '' || ! is_string($city) || $city === '') {
            return null;
        }

        return [
            'street' => $street,
            'city' => $city,
        ];
    }
}
