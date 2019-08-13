<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\JsonResponse;
use App\Map\ErrcodeMap;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckStore
{
    use JsonResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::guard('api')->user();

        if (!$user->store_id) {
            return $this->failed(ErrcodeMap::$errcode[ErrcodeMap::NO_STORE], ErrcodeMap::NO_STORE);
        }

        return $next($request);
    }
}
