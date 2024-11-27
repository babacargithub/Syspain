<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {


       // check if route starts with api/admin
        if ( \Str::startsWith($request->path(), 'api/admin') ) {
            if (\Str::contains($request->path(), 'boulangeries/change_active', ignoreCase: true)) {
                return $next($request);
            }

            if ($request->user() != null && !$request->user()->isAdmin() && !$request->user()->isSuperAdmin()) {
                return response()->json(['message' => 'Accès refusé ! Vous devez être admin pour voir cette page'], 403);
            }
        }
        return $next($request);
    }
}
