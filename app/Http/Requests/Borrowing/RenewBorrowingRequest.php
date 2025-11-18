<?php

namespace App\Http\Requests\Borrowing;

use App\Http\Requests\BaseRequest;

class RenewBorrowingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
