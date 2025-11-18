<?php

namespace App\Http\Requests\Member;

use App\Http\Requests\BaseRequest;

class MemberFilterRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive,suspended',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'order_by' => 'nullable|string|in:membership_date,status,max_borrow_limit,first_name,last_name,email,created_at',
            'order_direction' => 'nullable|string|in:asc,desc',
            'with_borrowings' => 'nullable|boolean',
            'with_purchases' => 'nullable|boolean',
            'with_user' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'order_by.in' => 'The order_by field must be one of: membership_date, status, max_borrow_limit, first_name, last_name, email, created_at.',
            'order_direction.in' => 'The order_direction field must be one of: asc, desc.',
        ];
    }
}
