<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || trim((string)auth()->user()->role) !== 'admin') {
            abort(403, 'Admin only');
        }
        return $next($request);
    }
}
