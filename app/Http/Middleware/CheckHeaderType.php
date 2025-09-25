<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHeaderType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$allowedTypes
     */
    public function handle(Request $request, Closure $next, ...$allowedTypes): Response
    {
        $headerType = $request->header('type');

        // Check if header exists
        if (!$headerType) {
            return response()->json([
                'error' => 'Missing required header: type'
            ], 400);
        }

        // Check if header type is valid
        $validTypes = ['user', 'admin', 'merchant'];
        if (!in_array($headerType, $validTypes)) {
            return response()->json([
                'error' => 'Invalid header type. Must be: ' . implode(', ', $validTypes)
            ], 400);
        }

        // Check if header type is allowed for this route
        if (!empty($allowedTypes) && !in_array($headerType, $allowedTypes)) {
            return response()->json([
                'error' => 'Access denied. This endpoint requires: ' . implode(' or ', $allowedTypes)
            ], 403);
        }

        // Add the type to request for easy access in controllers
        $request->merge(['user_type' => $headerType]);

        return $next($request);
    }
}
