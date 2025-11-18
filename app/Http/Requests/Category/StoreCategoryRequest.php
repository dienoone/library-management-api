<?php

namespace App\Http\Requests\Category;

use App\Authorization\AuthorizationAction;
use App\Authorization\AuthorizationResource;
use App\Http\Requests\BaseRequest;

class StoreCategoryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionByAction(AuthorizationAction::CREATE, AuthorizationResource::CATEGORIES);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
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
