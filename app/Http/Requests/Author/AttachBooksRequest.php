<?php

namespace App\Http\Requests\Author;

use App\Authorization\AuthorizationAction;
use App\Authorization\AuthorizationResource;
use App\Http\Requests\BaseRequest;

class AttachBooksRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionByAction(AuthorizationAction::UPDATE, AuthorizationResource::AUTHORS);
    }

    public function rules(): array
    {
        return [
            'book_ids' => ['required', 'array', 'min:1'],
            'book_ids.*' => ['integer', 'exists:books,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_ids.required' => 'The book IDs field is required.',
            'book_ids.array' => 'The book IDs must be an array.',
            'book_ids.min' => 'At least one book ID is required.',
            'book_ids.*.integer' => 'Each book ID must be an integer.',
            'book_ids.*.exists' => 'One or more book IDs do not exist.',
        ];
    }
}
