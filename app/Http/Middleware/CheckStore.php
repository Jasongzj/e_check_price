<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\JsonResponse;
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
            return $this->failed('你还没有店铺，无法操作', 40011);
        }

        return $next($request);
    }
}
