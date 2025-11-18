<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseRequest;

class SyncPermissionsRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'permissions' => 'required|array|min:1',
      'permissions.*.name' => 'required|string|max:255',
      'permissions.*.action' => 'required|string|max:255',
      'permissions.*.resource' => 'required|string|max:255',
    ];
  }

  public function messages(): array
  {
    return [
      'permissions.required' => 'At least one permission is required.',
      'permissions.min' => 'At least one permission is required.',
      'permissions.*.name.required' => 'Permission name is required.',
      'permissions.*.action.required' => 'Permission action is required.',
      'permissions.*.resource.required' => 'Permission resource is required.',
    ];
  }
}
