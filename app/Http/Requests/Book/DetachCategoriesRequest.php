<?php

namespace App\Http\Requests\Book;

use App\Http\Requests\BaseRequest;

class DetachCategoriesRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'category_ids' => 'required|array|min:1',
      'category_ids.*' => 'exists:categories,id',
    ];
  }

  public function messages(): array
  {
    return [
      'category_ids.required' => 'At least one category ID is required.',
      'category_ids.min' => 'At least one category ID is required.',
    ];
  }
}
