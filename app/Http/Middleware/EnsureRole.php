<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:
     *   ->middleware('role:admin')
     *   ->middleware('role:vendor,admin')
     *   ->middleware('role:inspector')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
                'message' => 'Authentication required.',
            ], 401);
        }

        if (!$user->role) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'FORBIDDEN',
                'message' => 'User role not assigned.',
            ], 403);
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'FORBIDDEN',
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
