<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
            'description' => 'nullable|string|max:500',
            'permissions' => 'sometimes|array',
            'permissions.*.name' => 'required|string|max:255',
            'permissions.*.action' => 'required|string|max:255',
            'permissions.*.resource' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A role with this name already exists.',
            'permissions.*.name.required' => 'Permission name is required.',
            'permissions.*.action.required' => 'Permission action is required.',
            'permissions.*.resource.required' => 'Permission resource is required.',
        ];
    }
}
