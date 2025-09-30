<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemIntegrity
{
     public function handle(Request $request, Closure $next)
    {
        // Always allow access - no purchase code validation required
        return $next($request);
    }
}
