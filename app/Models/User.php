<?php

namespace App\Models;

use App\Authorization\AuthorizationRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'birth_date',
        'password',
        'userable_id',
        'userable_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
    ];

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role);
        }
    }

    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role);
        }
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('rolePermissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    public function hasPermissionByAction(string $action, string $resource): bool
    {
        $permissionName = \App\Authorization\AuthorizationPermission::nameFor($action, $resource);
        return $this->hasPermission($permissionName);
    }

    public function getPermissions(): array
    {
        return $this->roles()
            ->with('rolePermissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions->pluck('name');
            })
            ->unique()
            ->values()
            ->toArray();
    }

    public function getUserTypeAttribute(): string
    {
        if (!$this->userable_type) {
            return 'User';
        }

        return class_basename($this->userable_type);
    }

    public function isMember(): bool
    {
        return $this->userable_type === Member::class;
    }

    public function isAuthor(): bool
    {
        return $this->userable_type === Author::class;
    }

    public function isLibrarian(): bool
    {
        return $this->userable_type === Librarian::class;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(AuthorizationRole::ADMIN);
    }

    public function member()
    {
        return $this->isMember() ? $this->userable : null;
    }

    public function author()
    {
        return $this->isAuthor() ? $this->userable : null;
    }

    public function librarian()
    {
        return $this->isLibrarian() ? $this->userable : null;
    }

    public function profile()
    {
        return $this->userable;
    }

    public function getNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
