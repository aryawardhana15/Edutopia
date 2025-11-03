<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMentor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isMentor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Mentor access required.'
            ], 403);
        }

        return $next($request);
    }
}

