<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SafariCookieFix
{
    /**
     * Handle an incoming request to fix Safari cookie issues.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add headers to improve Safari cookie compatibility
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        // Add Vary header to help with caching issues
        $response->headers->set('Vary', 'Accept-Encoding, User-Agent');
        
        // For Safari, we need to be more explicit about cookie handling
        if ($this->isSafari($request)) {
            // Set additional headers for Safari
            $response->headers->set('P3P', 'CP="CAO PSA OUR"');
            
            // If this is a form submission, ensure CSRF token is properly handled
            if ($request->isMethod('POST') && $request->ajax()) {
                $token = csrf_token();
                $response->headers->set('X-CSRF-TOKEN', $token);
            }
        }

        return $response;
    }

    /**
     * Detect if the request is coming from Safari.
     */
    private function isSafari(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');
        
        // Check for Safari (but not Chrome, which also contains Safari in UA)
        return str_contains($userAgent, 'Safari') && 
               !str_contains($userAgent, 'Chrome') && 
               !str_contains($userAgent, 'Chromium');
    }
}
