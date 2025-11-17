<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    public function addPermission(string $name, string $action, string $resource): RolePermission
    {
        return $this->rolePermissions()->create([
            'name' => $name,
            'action' => $action,
            'resource' => $resource,
        ]);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->rolePermissions()->where('name', $permissionName)->exists();
    }

    public function hasPermissionByAction(string $action, string $resource): bool
    {
        return $this->rolePermissions()
            ->where('action', $action)
            ->where('resource', $resource)
            ->exists();
    }

    public function getPermissionNames(): array
    {
        return $this->rolePermissions()->pluck('name')->toArray();
    }

    public function syncPermissions(array $permissions): void
    {
        $this->rolePermissions()->delete();

        foreach ($permissions as $permission) {
            $this->addPermission(
                $permission->name,
                $permission->action,
                $permission->resource
            );
        }
    }
}
