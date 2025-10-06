<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTechnician
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role !== 'technician') {
            abort(403, 'สำหรับช่างเท่านั้น');
        }
        return $next($request);
    }
}
