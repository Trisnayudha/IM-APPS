<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ScanApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-Scan-Key') ?? $request->query('scan_key');

        if (!$key || $key !== config('app.scan_api_key')) {
            return response()->json([
                'status'  => 401,
                'message' => 'Unauthorized',
                'data'    => null,
            ], 401);
        }

        return $next($request);
    }
}
