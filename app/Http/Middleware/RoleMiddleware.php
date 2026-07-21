<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: middleware('role:admin') or middleware('role:import_manager')
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        // Cek apakah sudah login via session
        if (!session('auth_user_id')) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        $userRole = session('auth_user_role');

        if (!in_array($userRole, $roles)) {
            // Redirect ke portal yang sesuai dengan role mereka
            if ($userRole === 'admin') {
                return redirect('/')->with('error', 'Access denied.');
            }
            return redirect('/manager')->with('error', 'Access denied.');
        }

        return $next($request);
    }
}
