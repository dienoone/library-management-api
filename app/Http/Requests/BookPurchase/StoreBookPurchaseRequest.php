<?php

namespace App\Http\Requests\BookPurchase;

use App\Http\Requests\BaseRequest;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Validation\Rule;

class StoreBookPurchaseRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:1',
            'total_amount' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'book_id' => [
                'required',
                'integer',
                Rule::exists('books', 'id'),
                function ($attribute, $value, $fail) {
                    $book = Book::find($value);
                    if ($book && !$book->can_purchase) {
                        $fail('The selected book is not available for purchase.');
                    }
                },
            ],
            'member_id' => 'required|integer|exists:members,id',
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.exists' => 'The selected book does not exist.',
            'member_id.exists' => 'The selected member does not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set purchase_date to current date if not provided
        if (!$this->has('purchase_date')) {
            $this->merge([
                'purchase_date' => now()->toDateString(),
            ]);
        }

        // Calculate total_amount if not provided but quantity and unit_price are provided
        if ($this->has('quantity') && $this->has('unit_price') && !$this->has('total_amount')) {
            $this->merge([
                'total_amount' => $this->quantity * $this->unit_price,
            ]);
        }
    }
}
