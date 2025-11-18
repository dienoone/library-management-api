<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class UpdateAuthorRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'nationality' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => 'First name must be a string',
            'first_name.max' => 'First name cannot exceed 255 characters',
            'last_name.string' => 'Last name must be a string',
            'last_name.max' => 'Last name cannot exceed 255 characters',
            'birth_date.before' => 'Birth date must be in the past',
            'bio.max' => 'Bio cannot exceed 1000 characters',
            'nationality.max' => 'Nationality cannot exceed 100 characters',
        ];
    }
}
