<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('is_admin_logged_in')) {
            return redirect()->route('admin.login')->with('error', 'Silakan login terlebih dahulu untuk mengakses Panel Admin.');
        }

        return $next($request);
    }
}