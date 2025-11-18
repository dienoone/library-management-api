<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Support\Facades\DB;

class RoleService
{
  public function getAllRolesPaginate(array $filters = [])
  {
    $query = Role::query();

    // Apply search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    // Filter by permission
    if (!empty($filters['permission'])) {
      $query->whereHas('rolePermissions', function ($q) use ($filters) {
        $q->where('name', $filters['permission'])
          ->orWhere('action', $filters['permission'])
          ->orWhere('resource', $filters['permission']);
      });
    }

    // Filter by action
    if (!empty($filters['action'])) {
      $query->whereHas('rolePermissions', function ($q) use ($filters) {
        $q->where('action', $filters['action']);
      });
    }

    // Filter by resource
    if (!empty($filters['resource'])) {
      $query->whereHas('rolePermissions', function ($q) use ($filters) {
        $q->where('resource', $filters['resource']);
      });
    }

    // Apply sorting
    $orderBy = $filters['order_by'] ?? 'name';
    $orderDirection = $filters['order_direction'] ?? 'asc';

    // Validate order_by field to prevent SQL injection
    $allowedOrderColumns = ['name', 'description', 'created_at', 'updated_at'];
    if (in_array($orderBy, $allowedOrderColumns)) {
      $query->orderBy($orderBy, $orderDirection);
    } else {
      $query->orderBy('name', 'asc');
    }

    // Load permissions relationship if requested
    if (isset($filters['with_permissions']) && filter_var($filters['with_permissions'], FILTER_VALIDATE_BOOLEAN)) {
      $query->with('rolePermissions');
    }

    // Load users relationship if requested
    if (isset($filters['with_users']) && filter_var($filters['with_users'], FILTER_VALIDATE_BOOLEAN)) {
      $query->with('users');
    }

    $perPage = $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getAll()
  {
    return Role::orderBy('name', 'asc')->get();
  }

  public function getRole(int $id)
  {
    $role = Role::find($id);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    return $role->load(['rolePermissions', 'users']);
  }

  public function createRole(array $data)
  {
    return DB::transaction(function () use ($data) {
      // Check if role name already exists
      $existingRole = Role::where('name', $data['name'])->first();
      throw_if($existingRole, ConflictException::class, 'Role with this name already exists');

      // Create the role
      $role = Role::create([
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
      ]);

      // Create permissions if provided
      if (!empty($data['permissions'])) {
        foreach ($data['permissions'] as $permission) {
          $role->addPermission(
            $permission['name'],
            $permission['action'],
            $permission['resource']
          );
        }
      }

      return $role->load(['rolePermissions', 'users']);
    });
  }

  public function updateRole(int $id, array $data)
  {
    $role = Role::find($id);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    return DB::transaction(function () use ($role, $data) {
      // Check if role name already exists (excluding current role)
      if (isset($data['name']) && $data['name'] !== $role->name) {
        $existingRole = Role::where('name', $data['name'])->where('id', '!=', $role->id)->first();
        throw_if($existingRole, ConflictException::class, 'Role with this name already exists');
      }

      // Update role attributes
      $role->update([
        'name' => $data['name'] ?? $role->name,
        'description' => $data['description'] ?? $role->description,
      ]);

      // Sync permissions if provided
      if (isset($data['permissions'])) {
        $role->syncPermissions($data['permissions']);
      }

      return $role->load(['rolePermissions', 'users']);
    });
  }

  public function deleteRole(int $id)
  {
    $role = Role::find($id);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    throw_if(
      $role->users()->exists(),
      ConflictException::class,
      'Cannot delete role with assigned users. Please reassign users first.'
    );

    return DB::transaction(function () use ($role) {
      // Delete all permissions
      $role->rolePermissions()->delete();

      $role->delete();
    });
  }

  public function addPermission(int $roleId, array $data)
  {
    $role = Role::find($roleId);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    // Check if permission already exists
    $existingPermission = $role->rolePermissions()
      ->where('action', $data['action'])
      ->where('resource', $data['resource'])
      ->first();

    throw_if($existingPermission, ConflictException::class, 'Permission already exists for this role');

    return $role->addPermission(
      $data['name'],
      $data['action'],
      $data['resource']
    );
  }

  public function removePermission(int $roleId, int $permissionId)
  {
    $role = Role::find($roleId);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    $permission = $role->rolePermissions()->find($permissionId);
    throw_if(!$permission, NotFoundException::class, 'Permission not found');

    $permission->delete();

    return $role->load('rolePermissions');
  }

  public function syncPermissions(int $roleId, array $permissions)
  {
    $role = Role::find($roleId);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    $role->syncPermissions($permissions);

    return $role->load('rolePermissions');
  }

  public function hasPermission(int $roleId, string $permissionName)
  {
    $role = Role::find($roleId);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    return $role->hasPermission($permissionName);
  }

  public function hasPermissionByAction(int $roleId, string $action, string $resource)
  {
    $role = Role::find($roleId);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    return $role->hasPermissionByAction($action, $resource);
  }

  public function getPermissionNames(int $roleId)
  {
    $role = Role::find($roleId);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    return $role->getPermissionNames();
  }

  public function assignToUsers(int $roleId, array $userIds)
  {
    $role = Role::find($roleId);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    $role->users()->syncWithoutDetaching($userIds);

    return $role->load('users');
  }

  public function removeFromUsers(int $roleId, array $userIds)
  {
    $role = Role::find($roleId);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    $role->users()->detach($userIds);

    return $role->load('users');
  }

  public function getUsers(int $roleId)
  {
    $role = Role::find($roleId);
    throw_if(!$role, NotFoundException::class, 'Role not found');

    return $role->users;
  }
}
