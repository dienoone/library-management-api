<?php

namespace App\Http\Requests\Borrowing;

use App\Http\Requests\BaseRequest;

class ReturnBorrowingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_date' => 'sometimes|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set return_date to now if not provided
        if (!$this->has('return_date')) {
            $this->merge([
                'return_date' => now(),
            ]);
        }
    }
}
