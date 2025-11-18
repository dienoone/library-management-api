<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AssignRolesRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UserFilterRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users
     * 
     * GET /api/users
     * Query params: search, user_type, role, email_verified, start_date, end_date, 
     * order_by, order_direction, per_page
     */
    public function index(UserFilterRequest $request): JsonResponse
    {
        $users = $this->userService->getAllPaginated($request->validated());
        return $this->successWithPagination(UserResource::collection($users));
    }

    /**
     * Display the specified user
     * 
     * GET /api/users/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {

        $user = $this->userService->getById($id);

        return $this->success(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Update the specified user
     * 
     * PUT/PATCH /api/users/{id}
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {

        $user = $this->userService->update($id, $request->validated());

        return $this->success(
            new UserResource($user),
            'User updated successfully'
        );
    }

    /**
     * Remove the specified user
     * 
     * DELETE /api/users/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->userService->delete($id);
        return $this->success(null, 'User deleted successfully');
    }

    /**
     * Get all users without pagination
     * 
     * GET /api/users/all
     */
    public function all(Request $request): JsonResponse
    {
        $users = $this->userService->getAll();

        return $this->success(
            UserResource::collection($users),
            'All users retrieved successfully'
        );
    }

    /**
     * Search users
     * 
     * GET /api/users/search?q={query}
     */
    public function search(Request $request): JsonResponse
    {
        $users = $this->userService->search($request->get('q'));

        return $this->success(
            UserResource::collection($users),
            'Search completed successfully'
        );
    }

    /**
     * Assign roles to user
     * 
     * POST /api/users/{id}/roles
     * Body: { "role_ids": [1, 2, 3] }
     */
    public function assignRoles(AssignRolesRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->assignRoles($id, $request->role_ids);

        return $this->success(
            new UserResource($user),
            'Roles assigned successfully'
        );
    }

    /**
     * Get users by type
     * 
     * GET /api/users/by-type/{type}
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $users = $this->userService->getUsersByType($type);

        return $this->success(
            UserResource::collection($users),
            ucfirst($type) . 's retrieved successfully'
        );
    }

    /**
     * Get user statistics
     * 
     * GET /api/users/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $statistics = $this->userService->getStatistics();

        return $this->success(
            $statistics,
            'User statistics retrieved successfully'
        );
    }

    /**
     * Get current authenticated user
     * 
     * GET /api/users/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['userable', 'roles.rolePermissions']);

        return $this->success(
            new UserResource($user),
            'Current user retrieved successfully'
        );
    }
}
