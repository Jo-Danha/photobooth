<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PhotoboothSession;

class EnsureSessionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token') ?? $request->input('session_token');

        if (!$token) {
            return redirect()->route('photobooth.index')->with('error', 'Token sesi tidak valid.');
        }

        $session = PhotoboothSession::where('session_token', $token)->first();

        if (!$session) {
            return redirect()->route('photobooth.index')->with('error', 'Sesi tidak ditemukan.');
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('photobooth.checkout', ['token' => $token])
                ->with('error', 'Silakan selesaikan pembayaran terlebih dahulu.');
        }

        if (!$session->isSessionActive()) {
            return response()->view('photobooth.expired', ['session' => $session], 403);
        }

        $request->merge(['photobooth_session' => $session]);

        return $next($request);
    }
}