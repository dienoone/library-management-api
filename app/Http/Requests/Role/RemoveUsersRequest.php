<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseRequest;

class RemoveUsersRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'user_ids' => 'required|array|min:1',
      'user_ids.*' => 'exists:users,id',
    ];
  }

  public function messages(): array
  {
    return [
      'user_ids.required' => 'At least one user ID is required.',
      'user_ids.min' => 'At least one user ID is required.',
    ];
  }
}
