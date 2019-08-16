<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\JsonResponse;
use App\Map\ErrcodeMap;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    use JsonResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        $user = Auth::guard('api')->user();
        if (!$user->permissions->contains('name', $permission) || !$user->is_manager) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::NO_PERMISSION], ErrcodeMap::NO_PERMISSION);
        }
        return $next($request);
    }
}
