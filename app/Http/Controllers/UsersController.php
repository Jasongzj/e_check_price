<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WechatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    /**
     * @param Request $request
     * @param WechatService $service
     * @return mixed
     */
    public function updateInfo(Request $request, WechatService $service)
    {
        $user = Auth::guard('api')->user();
        $cacheKey = $cacheKey = User::$cacheSessionKeyPrefix. $user->id . User::$cacheSessionKeySuffix;
        $sessionKey = Cache::get($cacheKey);
        if (!$sessionKey) {
            return $this->unauthorized('授权过期，请重新登录');
        }

        $info = $service->decryptData($sessionKey, $request->input('iv'), $request->input('encrypted_data'));
        // 更新用户信息
        foreach ($info as $key => $value) {
            $key = Str::snake($key);
            $info[$key] = $value;
        }
        $user->update($info);
        return $this->success('更新成功');
    }
}
