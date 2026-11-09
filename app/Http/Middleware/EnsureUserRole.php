<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user?->is_active) {
            abort(403, 'Your account is inactive.');
        }

        if ($user->role !== $role) {
            if ($request->expectsJson()) {
                abort(403, 'Unauthorized.');
            }

            if ($user->role === 'student' && $role === 'registrar') {
                return redirect()->route('student.enrollments.index')->with('error', 'You are currently signed in as a Student. Registrar tools (like Grade Encoding) require signing in with a Registrar account (registrar@bcc.edu.ph).');
            }

            if ($user->role === 'registrar' && $role === 'student') {
                return redirect()->route('dashboard');
            }

            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
