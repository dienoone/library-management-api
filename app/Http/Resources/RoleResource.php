<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'permissions' => RolePermissionResource::collection($this->whenLoaded('rolePermissions')),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'permission_count' => $this->when($this->rolePermissions_count !== null, $this->rolePermissions_count),
            'user_count' => $this->when($this->users_count !== null, $this->users_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
