<?php
// database/seeders/PermissionSeeder.php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Authorization\AuthorizationPermission;
use App\Authorization\AuthorizationRole;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing permissions
        Permission::query()->delete();

        // Create permissions
        foreach (AuthorizationPermission::all() as $permissionDef) {
            Permission::create([
                'name' => $permissionDef->name,
                'description' => $permissionDef->description,
                'action' => $permissionDef->action,
                'resource' => $permissionDef->resource,
            ]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate([
            'name' => AuthorizationRole::ADMIN,
        ], [
            'description' => 'Administrator role with all permissions'
        ]);

        $librarianRole = Role::firstOrCreate([
            'name' => AuthorizationRole::LIBRARIAN,
        ], [
            'description' => 'Librarian role with library management permissions'
        ]);

        $authorRole = Role::firstOrCreate([
            'name' => AuthorizationRole::AUTHOR,
        ], [
            'description' => 'Author role with book creation and management permissions'
        ]);

        $memberRole = Role::firstOrCreate([
            'name' => AuthorizationRole::MEMBER,
        ], [
            'description' => 'Member role with basic library access'
        ]);

        // Assign permissions based on flags

        // Admin gets all isRoot permissions
        $adminPermissions = Permission::whereIn(
            'name',
            array_map(fn($p) => $p->name, AuthorizationPermission::admin())
        )->pluck('id');
        $adminRole->permissions()->sync($adminPermissions);

        // Librarian gets all isLibrarian permissions
        $librarianPermissions = Permission::whereIn(
            'name',
            array_map(fn($p) => $p->name, AuthorizationPermission::librarian())
        )->pluck('id');
        $librarianRole->permissions()->sync($librarianPermissions);

        // Author gets all isAuthor permissions
        $authorPermissions = Permission::whereIn(
            'name',
            array_map(fn($p) => $p->name, AuthorizationPermission::author())
        )->pluck('id');
        $authorRole->permissions()->sync($authorPermissions);

        // Member gets all isMember permissions
        $memberPermissions = Permission::whereIn(
            'name',
            array_map(fn($p) => $p->name, AuthorizationPermission::member())
        )->pluck('id');
        $memberRole->permissions()->sync($memberPermissions);
    }
}
