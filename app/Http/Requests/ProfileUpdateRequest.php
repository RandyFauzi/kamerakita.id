<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        if ($partner = $this->user()?->partner) {
            $rules = array_merge($rules, [
                'full_name' => ['required', 'string', 'max:255'],
                'nik' => ['nullable', 'string', 'max:30', Rule::unique('partners', 'nik')->ignore($partner->id)],
                'whatsapp_number' => ['required', 'string', 'max:20'],
                'full_address' => ['nullable', 'string'],
                'bank_name' => ['nullable', 'string', 'max:100'],
                'bank_account_number' => ['nullable', 'string', 'max:50'],
                'bank_account_owner' => ['nullable', 'string', 'max:255'],
                'smartphone_type' => ['nullable', 'string', 'max:100'],
            ]);
        }

        return $rules;
    }
}
