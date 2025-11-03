<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePelajar
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isPelajar()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Pelajar access required.'
            ], 403);
        }

        return $next($request);
    }
}

