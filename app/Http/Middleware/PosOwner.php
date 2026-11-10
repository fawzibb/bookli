<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class PosOwner
{
    public function handle(Request $request, Closure $next)
    {
        $owner = auth('business')->user();
        abort_unless($owner && $owner->is_owner && $owner->is_active && $owner->business?->is_active, 403);
        return $next($request);
    }
}
