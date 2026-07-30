<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline response hardening. These are cheap, apply to every response, and
 * close off whole classes of attack that application code cannot.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Streamed responses (the CSV export) must not have headers added
        // after they have begun sending.
        if ($response->isRedirection() || ! $response->headers) {
            return $response;
        }

        // Stop the browser MIME-sniffing an upload into something executable.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // The app is never legitimately framed, so refuse clickjacking outright.
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Do not leak record ids in the Referer header to third parties.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // No feature of this app needs these device APIs.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
