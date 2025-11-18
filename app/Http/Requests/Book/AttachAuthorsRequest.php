<?php

namespace App\Http\Requests\Book;

use App\Http\Requests\BaseRequest;

class AttachAuthorsRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'author_ids' => 'required|array|min:1',
      'author_ids.*' => 'exists:authors,id',
    ];
  }

  public function messages(): array
  {
    return [
      'author_ids.required' => 'At least one author ID is required.',
      'author_ids.min' => 'At least one author ID is required.',
    ];
  }
}
