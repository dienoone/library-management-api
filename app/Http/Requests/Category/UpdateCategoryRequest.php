<?php

namespace App\Http\Requests\Category;

use App\Authorization\AuthorizationAction;
use App\Authorization\AuthorizationResource;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionByAction(AuthorizationAction::UPDATE, AuthorizationResource::CATEGORIES);
    }

    public function rules(): array
    {
        $categoryId = $this->route('category');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required',
            'name.unique' => 'A category with this name already exists',
            'name.max' => 'Category name cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 1000 characters',
        ];
    }
}
