<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        if (!file_exists(storage_path('framework/down'))) {
            return $next($request);
        }

        $path = $request->getPathInfo();
        
        // Allow admin and api routes
        if (strpos($path, '/admin') === 0 || strpos($path, '/api') === 0) {
            return $next($request);
        }
        
        // Allow direct PHP files (maintenance admin panel)
        if (substr($path, -4) === '.php') {
            return $next($request);
        }

        return response()->view('maintenance', [], 503);
    }
}
