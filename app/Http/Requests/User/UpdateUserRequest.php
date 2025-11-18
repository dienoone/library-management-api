<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $userId = $this->route('user');

    return [
      'first_name' => 'sometimes|required|string|max:255',
      'last_name' => 'sometimes|required|string|max:255',
      'email' => [
        'sometimes',
        'required',
        'email',
        'max:255',
        Rule::unique('users', 'email')->ignore($userId)
      ],
      'phone' => 'nullable|string|max:20',
      'address' => 'nullable|string|max:500',
      'birth_date' => 'nullable|date|before:today',
      'password' => 'nullable|string|min:8|confirmed',

      // Role management
      'role_ids' => 'sometimes|array',
      'role_ids.*' => 'exists:roles,id',

      // Member-specific fields
      'member_data' => 'sometimes|array',
      'member_data.status' => 'sometimes|string|in:active,inactive,suspended',
      'member_data.max_borrow_limit' => 'sometimes|integer|min:1|max:20',

      // Author-specific fields
      'author_data' => 'sometimes|array',
      'author_data.bio' => 'nullable|string|max:1000',
      'author_data.nationality' => 'nullable|string|max:100',

      // Librarian-specific fields
      'librarian_data' => 'sometimes|array',
      'librarian_data.hire_date' => 'sometimes|date|before_or_equal:today',
    ];
  }

  public function messages(): array
  {
    return [
      'first_name.required' => 'First name is required',
      'last_name.required' => 'Last name is required',
      'email.required' => 'Email address is required',
      'email.email' => 'Please provide a valid email address',
      'email.unique' => 'This email address is already registered',
      'password.min' => 'Password must be at least 8 characters',
      'password.confirmed' => 'Password confirmation does not match',
      'birth_date.before' => 'Birth date must be in the past',
      'member_data.status.in' => 'Status must be one of: active, inactive, suspended',
      'member_data.max_borrow_limit.min' => 'Borrow limit must be at least 1',
      'member_data.max_borrow_limit.max' => 'Borrow limit cannot exceed 20',
      'librarian_data.hire_date.before_or_equal' => 'Hire date cannot be in the future',
    ];
  }
}
