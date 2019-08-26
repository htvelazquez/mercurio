<?php

namespace App\Http\Middleware;

use Closure;

class ApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $envApiKey = env('API_KEY');
        $headerApiKey = $request->header('x-api-key');

        if (empty($headerApiKey) || $request->header('x-api-key') !== $envApiKey) {
            return response()->json(['success' => false, 'error' => 'Invalid API key'], 200);
        }

        return $next($request);
    }
}
