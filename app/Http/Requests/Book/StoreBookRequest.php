<?php

namespace App\Http\Requests\Book;

use App\Http\Requests\BaseRequest;

class StoreBookRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn|max:20',
            'description' => 'nullable|string',
            'publisher_name' => 'required|string|max:255',
            'cover_image' => 'nullable|string|max:500',
            'total_copies' => 'required|integer|min:0',
            'available_copies' => 'sometimes|integer|min:0|lte:total_copies',
            'price' => 'nullable|numeric|min:0',
            'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'publication_date' => 'nullable|date',
            'can_borrow' => 'boolean',
            'can_purchase' => 'boolean',
            'author_ids' => 'required|array|min:1',
            'author_ids.*' => 'exists:authors,id',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'author_ids.required' => 'At least one author is required.',
            'author_ids.min' => 'At least one author is required.',
            'category_ids.required' => 'At least one category is required.',
            'category_ids.min' => 'At least one category is required.',
            'available_copies.lte' => 'Available copies cannot exceed total copies.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set available_copies to total_copies if not provided
        if ($this->has('total_copies') && !$this->has('available_copies')) {
            $this->merge([
                'available_copies' => $this->total_copies,
            ]);
        }

        // Ensure boolean fields are properly cast
        $this->merge([
            'can_borrow' => filter_var($this->can_borrow, FILTER_VALIDATE_BOOLEAN),
            'can_purchase' => filter_var($this->can_purchase, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
