<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreWinnerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'full_name' => ['required', 'string', 'max:255'],
            'zipcode' => ['required', 'string', 'regex:/^[1-9][0-9]{3} [A-Z]{2}$/'],
            'house_number' => ['required', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Token is verplicht.',
            'full_name.required' => 'Volledige naam is verplicht.',
            'full_name.max' => 'Volledige naam mag niet uit meer dan :max tekens bestaan.',
            'zipcode.required' => 'Postcode is verplicht.',
            'zipcode.regex' => 'Het formaat van postcode is ongeldig.',
            'house_number.required' => 'Huisnummer is verplicht.',
            'house_number.max' => 'Huisnummer mag niet uit meer dan :max tekens bestaan.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'volledige naam',
            'zipcode' => 'postcode',
            'house_number' => 'huisnummer',
        ];
    }

    /**
     * @return array{full_name: string, zipcode: string, house_number: string}
     */
    public function addressData(): array
    {
        /** @var array{full_name: string, zipcode: string, house_number: string} $data */
        $data = $this->safe()->only(['full_name', 'zipcode', 'house_number']);

        return $data;
    }

    protected function prepareForValidation(): void
    {
        $zipcode = mb_strtoupper(preg_replace('/\s+/', '', $this->string('zipcode')->trim()->toString()) ?? '');

        if (preg_match('/^([1-9]\d{3})([A-Z]{2})$/', $zipcode, $matches) === 1) {
            $zipcode = $matches[1].' '.$matches[2];
        }

        $this->merge([
            'full_name' => $this->string('full_name')->trim()->toString(),
            'zipcode' => $zipcode,
            'house_number' => $this->string('house_number')->trim()->toString(),
        ]);
    }
}
