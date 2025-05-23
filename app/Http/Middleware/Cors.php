<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get response
        $response = $next($request);

        // Handle OPTIONS preflight request
        if ($request->isMethod('OPTIONS')) {
            // Return empty response with CORS headers for preflight
            return response('', 200)
                ->header('Access-Control-Allow-Origin', 'http://localhost:9000')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-HTTP-Method-Override')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400'); // Cache preflight for 24 hours
        }

        // For all other requests, add CORS headers to the response
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:9000');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-HTTP-Method-Override');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
