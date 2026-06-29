<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $userRole = $request->user()->role_id;

        if (!in_array($userRole, $roles)) {
            return response()->json(['message' => 'Forbidden: Anda tidak memiliki akses.'], 403);
        }

        return $next($request);
    }
}
