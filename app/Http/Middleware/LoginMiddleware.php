<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Jika user BELUM login, lempar ke halaman login
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Login dulu bro!');
        }

        return $next($request);
    }
}