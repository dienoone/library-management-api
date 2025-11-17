<?php
// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use App\Authorization\AuthorizationPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $action, string $resource): Response
    {
        $permissionName = AuthorizationPermission::nameFor($action, $resource);

        if (!$request->user() || !$request->user()->hasPermission($permissionName)) {
            return response()->json([
                'message' => 'Unauthorized. Required permission: ' . $permissionName
            ], 403);
        }

        return $next($request);
    }
}
