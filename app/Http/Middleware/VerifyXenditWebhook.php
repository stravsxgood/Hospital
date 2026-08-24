<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyXenditWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $incomingToken = $request->header('x-callback-token');
        $validToken = config('services.xendit.webhook_token');

        // Pastikan token terkonfigurasi di aplikasi
        if (empty($validToken)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Server webhook token is not configured.'
            ], 500);
        }

        // Bandingkan token masuk dengan token resmi dari config secara aman
        if (!$incomingToken || !hash_equals($validToken, $incomingToken)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid or missing x-callback-token header.'
            ], 401);
        }

        return $next($request);
    }
}
