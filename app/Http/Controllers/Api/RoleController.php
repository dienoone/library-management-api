<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\AddPermissionRequest;
use App\Http\Requests\Role\AssignUsersRequest;
use App\Http\Requests\Role\RemoveUsersRequest;
use App\Http\Requests\Role\RoleFilterRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\SyncPermissionsRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService) {}

    /**
     * Display a listing of roles
     * 
     * GET /api/roles
     * Query params: search, permission, action, resource, order_by, order_direction, 
     * with_permissions, with_users, per_page
     */
    public function index(RoleFilterRequest $request)
    {
        $roles = $this->roleService->getAllRolesPaginate($request->validated());
        return $this->successWithPagination(RoleResource::collection($roles));
    }

    /**
     * Store a newly created role
     * 
     * POST /api/roles
     */
    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->createRole($request->validated());
        return $this->created(
            new RoleResource($role),
            'Role created successfully'
        );
    }

    /**
     * Display the specified role
     * 
     * GET /api/roles/{id}
     */
    public function show(string $id)
    {
        $role = $this->roleService->getRole($id);
        return $this->success(new RoleResource($role));
    }

    /**
     * Update the specified role
     * 
     * PUT/PATCH /api/roles/{id}
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        $role = $this->roleService->updateRole($id, $request->validated());
        return $this->success(new RoleResource($role), 'Role updated successfully');
    }

    /**
     * Remove the specified role
     * 
     * DELETE /api/roles/{id}
     */
    public function destroy(string $id)
    {
        $this->roleService->deleteRole($id);
        return $this->noContent('Role deleted successfully');
    }

    /**
     * Get all roles without pagination
     * 
     * GET /api/roles/all
     */
    public function all(Request $request)
    {
        $roles = $this->roleService->getAll();
        return $this->success(RoleResource::collection($roles));
    }

    /**
     * Add permission to role
     * 
     * POST /api/roles/{id}/permissions
     * Body: { "name": "permission_name", "action": "create", "resource": "books" }
     */
    public function addPermission(AddPermissionRequest $request, int $id)
    {
        $permission = $this->roleService->addPermission($id, $request->validated());
        $role = $this->roleService->getRole($id);
        return $this->success(
            new RoleResource($role),
            'Permission added to role successfully'
        );
    }

    /**
     * Remove permission from role
     * 
     * DELETE /api/roles/{id}/permissions/{permissionId}
     */
    public function removePermission(int $id, int $permissionId)
    {
        $role = $this->roleService->removePermission($id, $permissionId);
        return $this->success(
            new RoleResource($role),
            'Permission removed from role successfully'
        );
    }

    /**
     * Sync permissions for role
     * 
     * POST /api/roles/{id}/permissions/sync
     * Body: { "permissions": [{ "name": "perm1", "action": "create", "resource": "books" }] }
     */
    public function syncPermissions(SyncPermissionsRequest $request, int $id)
    {
        $role = $this->roleService->syncPermissions($id, $request->validated()['permissions']);
        return $this->success(
            new RoleResource($role),
            'Permissions synced successfully'
        );
    }

    /**
     * Check if role has specific permission
     * 
     * GET /api/roles/{id}/has-permission/{permissionName}
     */
    public function hasPermission(string $id, string $permissionName)
    {
        $hasPermission = $this->roleService->hasPermission($id, $permissionName);
        return $this->success([
            'has_permission' => $hasPermission
        ]);
    }

    /**
     * Check if role has permission by action and resource
     * 
     * GET /api/roles/{id}/has-permission-by-action
     * Query params: action, resource
     */
    public function hasPermissionByAction(Request $request, string $id)
    {
        $request->validate([
            'action' => 'required|string',
            'resource' => 'required|string',
        ]);

        $hasPermission = $this->roleService->hasPermissionByAction(
            $id,
            $request->action,
            $request->resource
        );

        return $this->success([
            'has_permission' => $hasPermission
        ]);
    }

    /**
     * Get role permission names
     * 
     * GET /api/roles/{id}/permission-names
     */
    public function getPermissionNames(string $id)
    {
        $permissionNames = $this->roleService->getPermissionNames($id);
        return $this->success([
            'permission_names' => $permissionNames
        ]);
    }

    /**
     * Assign users to role
     * 
     * POST /api/roles/{id}/users
     * Body: { "user_ids": [1, 2, 3] }
     */
    public function assignUsers(AssignUsersRequest $request, int $id)
    {
        $role = $this->roleService->assignToUsers($id, $request->validated()['user_ids']);
        return $this->success(
            new RoleResource($role),
            'Users assigned to role successfully'
        );
    }

    /**
     * Remove users from role
     * 
     * DELETE /api/roles/{id}/users
     * Body: { "user_ids": [1, 2, 3] }
     */
    public function removeUsers(RemoveUsersRequest $request, int $id)
    {
        $role = $this->roleService->removeFromUsers($id, $request->validated()['user_ids']);
        return $this->success(
            new RoleResource($role),
            'Users removed from role successfully'
        );
    }

    /**
     * Get role users
     * 
     * GET /api/roles/{id}/users
     */
    public function getUsers(string $id)
    {
        $users = $this->roleService->getUsers($id);
        return $this->success($users);
    }
}
