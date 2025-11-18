<?php

namespace App\Http\Requests\Member;

use App\Http\Requests\BaseRequest;

class UpdateMemberStatusRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'status' => ['required', 'string', 'in:active,inactive,suspended'],
    ];
  }

  public function messages(): array
  {
    return [
      'status.required' => 'Status is required',
      'status.in' => 'Status must be one of: active, inactive, suspended',
    ];
  }
}
