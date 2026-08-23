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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if(!$user || !in_array($user->roles, $roles)) {
            return response()->json([
               'message' => 'You are not authorised to perform this action.' 
            ], 403);
        }
        return $next($request);
    }
}
