<?php

namespace App\Http\Requests\Borrowing;

use App\Http\Requests\BaseRequest;

class StoreBorrowingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => 'required|exists:books,id',
            'member_id' => 'required|exists:members,id',
            'borrow_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after:borrow_date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'Book ID is required.',
            'book_id.exists' => 'The selected book does not exist.',
            'member_id.required' => 'Member ID is required.',
            'member_id.exists' => 'The selected member does not exist.',
            'due_date.after' => 'Due date must be after borrow date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Ensure dates are properly formatted
        if ($this->has('borrow_date')) {
            $this->merge([
                'borrow_date' => $this->borrow_date ?: now(),
            ]);
        }
    }
}
