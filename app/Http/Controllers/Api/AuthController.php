<?php

namespace App\Http\Controllers\Api;

use App\Authorization\AuthorizationRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterAuthorRequest;
use App\Http\Requests\Auth\RegisterLibrarianRequest;
use App\Http\Requests\Auth\RegisterMemberRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function registerAuthor(RegisterAuthorRequest $request)
    {
        $author = $this->authService->register($request->validated(), AuthorizationRole::AUTHOR);

        return $this->created([
            'user' => new UserResource($author['user']),
            'access_token' => $author['token'],
            'token_type' => 'Bearer'
        ], 'Author created successfully');
    }

    public function registerLibrarian(RegisterLibrarianRequest $request)
    {
        $librarian = $this->authService->register($request->validated(), AuthorizationRole::LIBRARIAN);

        return $this->created([
            'user' => new UserResource($librarian['user']),
            'access_token' => $librarian['token'],
            'token_type' => 'Bearer'
        ], 'Author created successfully');
    }

    public function registerMember(RegisterMemberRequest $request)
    {
        $member = $this->authService->register($request->validated(), AuthorizationRole::MEMBER);

        return $this->created([
            'user' => new UserResource($member['user']),
            'access_token' => $member['token'],
            'token_type' => 'Bearer'
        ], 'Author created successfully');
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'access_token' => $result['token'],
            'token_type' => 'Bearer'
        ], 'Login successfully');
    }

    public function logout()
    {
        $this->authService->logout();
        return $this->success(null, 'Logged out successfully');
    }
}
