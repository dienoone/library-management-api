<?php

namespace App\Http\Requests\Book;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookId = $this->route('book');

        return [
            'title' => 'sometimes|string|max:255',
            'isbn' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ],
            'description' => 'nullable|string',
            'publisher_name' => 'sometimes|string|max:255',
            'cover_image' => 'nullable|string|max:500',
            'total_copies' => 'sometimes|integer|min:0',
            'available_copies' => 'sometimes|integer|min:0|lte:total_copies',
            'price' => 'nullable|numeric|min:0',
            'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'publication_date' => 'nullable|date',
            'can_borrow' => 'boolean',
            'can_purchase' => 'boolean',
            'author_ids' => 'sometimes|array|min:1',
            'author_ids.*' => 'exists:authors,id',
            'category_ids' => 'sometimes|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'author_ids.min' => 'At least one author is required when providing authors.',
            'category_ids.min' => 'At least one category is required when providing categories.',
            'available_copies.lte' => 'Available copies cannot exceed total copies.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Ensure boolean fields are properly cast
        if ($this->has('can_borrow')) {
            $this->merge([
                'can_borrow' => filter_var($this->can_borrow, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('can_purchase')) {
            $this->merge([
                'can_purchase' => filter_var($this->can_purchase, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
