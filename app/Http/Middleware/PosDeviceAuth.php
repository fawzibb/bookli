<?php
namespace App\Http\Middleware;
use App\Models\PosDevice;
use Closure;
use Illuminate\Http\Request;
class PosDeviceAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $hash = $token ? hash('sha256', $token) : '';
        $device = $token ? PosDevice::where('token_hash', $hash)->where('status', 'active')->first() : null;
        if (!$device) return response()->json(['error' => 'invalid_device_token', 'message' => 'Activate this device again.'], 401);
        $request->attributes->set('pos_device', $device);
        $request->attributes->set('pos_token_hash', $hash);
        return $next($request);
    }
}
