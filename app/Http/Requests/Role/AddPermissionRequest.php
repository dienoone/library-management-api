<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseRequest;

class AddPermissionRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => 'required|string|max:255',
      'action' => 'required|string|max:255',
      'resource' => 'required|string|max:255',
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Permission name is required.',
      'action.required' => 'Permission action is required.',
      'resource.required' => 'Permission resource is required.',
    ];
  }
}
