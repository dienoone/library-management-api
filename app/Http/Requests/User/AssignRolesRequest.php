<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class AssignRolesRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'role_ids' => 'required|array|min:1',
      'role_ids.*' => 'integer|exists:roles,id',
    ];
  }

  public function messages(): array
  {
    return [
      'role_ids.required' => 'At least one role ID is required.',
      'role_ids.min' => 'At least one role ID is required.',
      'role_ids.*.exists' => 'One or more selected roles do not exist.',
    ];
  }
}
