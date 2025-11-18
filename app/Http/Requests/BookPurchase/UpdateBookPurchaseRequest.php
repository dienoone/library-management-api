<?php

namespace App\Http\Requests\BookPurchase;

use App\Http\Requests\BaseRequest;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Validation\Rule;

class UpdateBookPurchaseRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'sometimes|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'total_amount' => 'sometimes|numeric|min:0',
            'purchase_date' => 'sometimes|date',
            'book_id' => [
                'sometimes',
                'integer',
                Rule::exists('books', 'id'),
                function ($attribute, $value, $fail) {
                    $book = Book::find($value);
                    if ($book && !$book->can_purchase) {
                        $fail('The selected book is not available for purchase.');
                    }
                },
            ],
            'member_id' => 'sometimes|integer|exists:members,id',
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
        // Recalculate total_amount if quantity or unit_price are being updated
        if (($this->has('quantity') || $this->has('unit_price')) && !$this->has('total_amount')) {
            $quantity = $this->quantity;
            $unitPrice = $this->unit_price;

            $this->merge([
                'total_amount' => $quantity * $unitPrice,
            ]);
        }
    }
}
