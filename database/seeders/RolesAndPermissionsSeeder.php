<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Authorization\AuthorizationPermission;
use App\Authorization\AuthorizationRole;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        RolePermission::query()->delete();
        Role::query()->delete();

        // Create roles
        $roles = [
            AuthorizationRole::ADMIN => 'Administrator role with all permissions',
            AuthorizationRole::LIBRARIAN => 'Librarian role with library management permissions',
            AuthorizationRole::AUTHOR => 'Author role with book creation and management permissions',
            AuthorizationRole::MEMBER => 'Member role with basic library access',
        ];

        $roleModels = [];
        foreach ($roles as $name => $description) {
            $roleModels[$name] = Role::create([
                'name' => $name,
                'description' => $description,
            ]);
        }

        // Assign permissions to roles
        $this->assignPermissions($roleModels[AuthorizationRole::ADMIN], AuthorizationPermission::admin());
        $this->assignPermissions($roleModels[AuthorizationRole::LIBRARIAN], AuthorizationPermission::librarian());
        $this->assignPermissions($roleModels[AuthorizationRole::AUTHOR], AuthorizationPermission::author());
        $this->assignPermissions($roleModels[AuthorizationRole::MEMBER], AuthorizationPermission::member());
    }

    private function assignPermissions(Role $role, array $permissions): void
    {
        foreach ($permissions as $permission) {
            $role->addPermission($permission->name, $permission->action, $permission->resource);
        }
    }
}
