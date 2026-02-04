<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek pakai Auth::check(), bukan session manual
        if (!Auth::check()) {
            return redirect('/login');
        }

        return $next($request);
    }
}