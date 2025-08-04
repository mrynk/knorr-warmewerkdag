<?php

namespace App\Http\Requests;

use App\Rules\ValidCode;
use Illuminate\Foundation\Http\FormRequest;

class CreateEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:8', new ValidCode],
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns,strict,filter,spoof|max:255',
            'soup' => 'required|string|max:255',
        ];
    }
}
