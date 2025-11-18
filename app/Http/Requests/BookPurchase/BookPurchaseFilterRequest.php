<?php

namespace App\Http\Requests\BookPurchase;

use App\Http\Requests\BaseRequest;

class BookPurchaseFilterRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'member_id' => 'nullable|integer|exists:members,id',
      'book_id' => 'nullable|integer|exists:books,id',
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date|after_or_equal:start_date',
      'min_amount' => 'nullable|numeric|min:0',
      'max_amount' => 'nullable|numeric|min:0',
      'order_by' => 'nullable|string|in:purchase_date,total_amount,quantity,unit_price,created_at',
      'order_direction' => 'nullable|string|in:asc,desc',
      'with_book' => 'nullable|boolean',
      'with_member' => 'nullable|boolean',
      'per_page' => 'nullable|integer|min:1|max:100',
    ];
  }

  public function messages(): array
  {
    return [
      'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
      'order_by.in' => 'The order_by field must be one of: purchase_date, total_amount, quantity, unit_price, created_at.',
      'order_direction.in' => 'The order_direction field must be one of: asc, desc.',
    ];
  }
}
