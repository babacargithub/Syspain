<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Accès refusé ! Vous devez être admin pour voir cette page'], 403);
        }
        return $next($request);
    }
}
