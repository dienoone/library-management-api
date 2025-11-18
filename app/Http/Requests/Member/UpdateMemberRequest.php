<?php

namespace App\Http\Requests\Member;

use App\Http\Requests\BaseRequest;

class UpdateMemberRequest extends BaseRequest
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
            'membership_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'max_borrow_limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
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
            'status.in' => 'Status must be one of: active, inactive, suspended',
            'max_borrow_limit.min' => 'Max borrow limit must be at least 1',
            'max_borrow_limit.max' => 'Max borrow limit cannot exceed 20',
        ];
    }
}
