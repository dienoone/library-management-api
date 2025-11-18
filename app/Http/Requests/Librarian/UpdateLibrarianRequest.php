<?php

namespace App\Http\Requests\Librarian;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateLibrarianRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $librarianId = $this->route('librarian');

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($librarianId, 'userable_id')->where('userable_type', \App\Models\Librarian::class)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'hire_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => 'First name must be a string',
            'first_name.max' => 'First name cannot exceed 255 characters',
            'last_name.string' => 'Last name must be a string',
            'last_name.max' => 'Last name cannot exceed 255 characters',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'birth_date.before' => 'Birth date must be in the past',
            'hire_date.date' => 'Hire date must be a valid date',
        ];
    }
}
