<?php

namespace App\Http\Middleware;

// app/Http/Middleware/XSSProtection.php
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XSSProtection
{
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        
        array_walk_recursive($input, function(&$input) {
            if (is_string($input)) {
                $input = strip_tags($input);
            }
        });
        
        $request->merge($input);
        
        return $next($request);
    }
}