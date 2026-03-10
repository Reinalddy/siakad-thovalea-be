<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthorized. Please login first.'
            ], 401);
        }

        $user = auth()->user();
        $roles = $request->route('roles'); // e.g., "Student|Lecturer|Admin"
        $requiredRoles = explode('|', $roles);

        // 2. Check if user has ANY of the required roles
        $hasRole = false;
        foreach ($requiredRoles as $role) {
            if ($user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            return response()->json([
                'message' => 'Forbidden. You do not have permission to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}
