<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\CampaignEmail;
use App\Rules\ValidCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class CreateEntryRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:8', resolve(ValidCode::class)],
            'name' => ['required', 'string', 'max:255'],
            'email' => CampaignEmail::rules(strictDns: ! app()->runningUnitTests()),
            'soup' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array{code: string, name: string, email: string, soup: string}
     */
    public function entryData(): array
    {
        /** @var array{code: string, name: string, email: string, soup: string} $data */
        $data = $this->safe()->only(['code', 'name', 'email', 'soup']);

        return $data;
    }
}
