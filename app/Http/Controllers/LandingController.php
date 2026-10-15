<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateEntry;
use App\Actions\StoreWinnerAddress;
use App\Exceptions\DailySubmissionLimitReached;
use App\Http\Requests\CreateEntryRequest;
use App\Http\Requests\StoreWinnerAddressRequest;
use App\Models\Entry;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Sqids\Sqids;
use Throwable;

final readonly class LandingController
{
    public function __construct(private Sqids $sqids) {}

    public function index(): Response
    {
        return Inertia::render('Landing');
    }

    public function store(CreateEntryRequest $request, CreateEntry $createEntry): RedirectResponse
    {
        try {
            $entry = $createEntry->handle($request->entryData());
        } catch (DailySubmissionLimitReached $exception) {
            return to_route('landing')->withErrors(['email' => $exception]);
        } catch (LockTimeoutException) {
            return to_route('landing')->withErrors([
                'message' => 'Te veel aanvragen, probeer het later opnieuw.',
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            Log::error('Duplicate entry attempt', ['error' => $exception]);

            return to_route('landing')->withErrors(['code' => 'Ongeldige actiecode (011)']);
        } catch (Throwable $exception) {
            Log::error('Error creating entry', ['error' => $exception]);

            return to_route('landing')->withErrors(['code' => 'Ongeldige actiecode (010)']);
        }

        if ($entry->reward === null) {
            return to_route('result', $entry->code);
        }

        return to_route('result', [
            'code' => $entry->code,
            'token' => $entry->addressToken(),
        ]);
    }

    public function show(Request $request, string $code): Response
    {
        $entry = $this->entryForCode($code);
        $showAddressForm = $entry->reward !== null
            && $entry->shippingAddressIsEmpty()
            && $entry->addressTokenMatches($request->query('token'));

        return Inertia::render('Landing', [
            'entry' => $entry,
            'showAddressForm' => $showAddressForm,
            'addressProvided' => $entry->reward !== null && ! $entry->shippingAddressIsEmpty(),
            'addressToken' => $showAddressForm ? $entry->addressToken() : null,
        ]);
    }

    public function storeAddress(StoreWinnerAddressRequest $request, string $code, StoreWinnerAddress $storeWinnerAddress): RedirectResponse
    {
        $entry = $this->entryForCode($code);
        $token = $request->string('token')->toString();

        $storeWinnerAddress->handle($entry, $token, $request->addressData());

        return to_route('result', [
            'code' => $entry->code,
            'token' => $token,
        ]);
    }

    private function entryForCode(string $code): Entry
    {
        $decoded = $this->sqids->decode($code);

        abort_if(count($decoded) !== 2, 404);

        [$batchNumber, $serialNumber] = $decoded;

        return Entry::query()
            ->where('code', $code)
            ->where('batch_number', $batchNumber)
            ->where('serial_number', $serialNumber)
            ->firstOrFail();
    }
}
