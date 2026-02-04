<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

public function handle($request, Closure $next)
{
    $token = $request->bearerToken();

    if (!$token) {
        return response()->json(['error' => 'Token required'], 401);
    }

    $user = User::where('api_token', hash('sha256', $token))->first();

    if (!$user) {
        return response()->json(['error' => 'Invalid token'], 401);
    }

    auth()->login($user);
    return $next($request);
}
