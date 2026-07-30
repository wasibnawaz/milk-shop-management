<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Route-level role gate, e.g. ->middleware('role:admin,manager').
 *
 * Policies remain the authority on individual records; this is a coarse
 * first filter so an unauthorised role never reaches the controller at all.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role->value, $roles, true)) {
            throw new AccessDeniedHttpException('You do not have permission to access this area.');
        }

        return $next($request);
    }
}
