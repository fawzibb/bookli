<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class PosTransport
{
    public function handle(Request $request, Closure $next)
    {
        if (!app()->environment(['local', 'testing']) && !$request->secure())
            return response()->json(['error' => 'https_required', 'message' => 'HTTPS is required.'], 426);
        return $next($request)->header('Cache-Control', 'no-store, private');
    }
}
