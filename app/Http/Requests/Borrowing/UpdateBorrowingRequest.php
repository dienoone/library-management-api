<?php

namespace App\Http\Requests\Borrowing;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateBorrowingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'borrow_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after:borrow_date',
            'return_date' => 'nullable|date|after:borrow_date',
            'status' => [
                'sometimes',
                Rule::in(['borrowed', 'returned', 'overdue']),
            ],
            'renewal_count' => 'sometimes|integer|min:0|max:3',
            'notes' => 'nullable|string|max:1000',
            'book_id' => 'sometimes|exists:books,id',
            'member_id' => 'sometimes|exists:members,id',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: borrowed, returned, overdue.',
            'due_date.after' => 'Due date must be after borrow date.',
            'return_date.after' => 'Return date must be after borrow date.',
            'renewal_count.max' => 'Renewal count cannot exceed 3.',
        ];
    }
}
