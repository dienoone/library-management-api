<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UserFilterRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'user_type' => 'nullable|string|in:Member,Author,Librarian',
            'role' => 'nullable|string|in:Admin,Librarian,Author,Member',
            'email_verified' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'order_by' => 'nullable|string|in:first_name,last_name,email,created_at',
            'order_direction' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'user_type.in' => 'The user_type must be one of: Member, Author, Librarian.',
            'role.in' => 'The role must be one of: Admin, Librarian, Author, Member.',
            'order_by.in' => 'The order_by field must be one of: first_name, last_name, email, created_at.',
            'order_direction.in' => 'The order_direction field must be one of: asc, desc.',
        ];
    }
}
