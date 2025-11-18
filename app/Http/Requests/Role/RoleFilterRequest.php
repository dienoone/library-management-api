<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseRequest;

class RoleFilterRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'search' => 'nullable|string|max:255',
      'permission' => 'nullable|string|max:255',
      'action' => 'nullable|string|max:255',
      'resource' => 'nullable|string|max:255',
      'order_by' => 'nullable|string|in:name,description,created_at,updated_at',
      'order_direction' => 'nullable|string|in:asc,desc',
      'with_permissions' => 'nullable|boolean',
      'with_users' => 'nullable|boolean',
      'per_page' => 'nullable|integer|min:1|max:100',
    ];
  }

  public function messages(): array
  {
    return [
      'order_by.in' => 'The order_by field must be one of: name, description, created_at, updated_at.',
      'order_direction.in' => 'The order_direction field must be one of: asc, desc.',
    ];
  }
}
