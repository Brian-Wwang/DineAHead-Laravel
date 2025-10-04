<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if ($user = $request->user()) {
            Cache::put("user:{$user->id}:online", true, now()->addSeconds(120));
        }
        return $next($request);
    }
}
