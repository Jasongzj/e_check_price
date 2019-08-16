<?php

namespace App\Http\Controllers;

use App\Events\ClerkAdded;
use App\Http\Resources\ClerkResource;
use App\Map\ErrcodeMap;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClerksController extends Controller
{
    /**
     * 店员列表
     */
    public function index()
    {
        $user = Auth::guard('api')->user();
        if (!$user->is_manager) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::NOT_OWNER], ErrcodeMap::NOT_OWNER);
        }

        $clerks = User::query()->where('store_id', $user->store_id)
            ->where('is_manager', 0)
            ->select(['id', 'store_id', 'is_manager', 'nick_name', 'avatar_url'])
            ->paginate();

        return ClerkResource::collection($clerks);
    }

    /**
     * 成为店员
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        // 判断用户是否有店铺
        if ($user->store_id) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::STORE_EXIST], ErrcodeMap::STORE_EXIST);
        }
        Log::debug('店铺ID：' . $request->input('store_id'));

        // 判断店铺是否还存在
        $store = Store::query()->find($request->input('store_id'));
        if (!$store) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::STORE_CANCELLED], ErrcodeMap::STORE_CANCELLED);
        }

        $user->update(['store_id' => $request->input('store_id')]);
        
        // 发送店员加入店铺通知
        // event(new ClerkAdded($store, $user));

        return $this->message('添加成功');
    }

    /**
     * 移除店员
     * @param User $clerk
     * @return mixed
     */
    public function destroy(User $clerk)
    {
        $user = Auth::guard('api')->user();
        if ($clerk->id == $user->id) {
            return $this->forbidden('不能移除自己哦', 40007);
        }
        if (!$user->is_manager) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::NOT_OWNER], ErrcodeMap::NOT_OWNER);
        }
        if ($user->store_id != $clerk->store_id) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::NOT_YOUR_CLERK], ErrcodeMap::NOT_YOUR_CLERK);
        }

        $clerk->update(['store_id' => null]);
        return $this->message('移除成功');
    }

    /**
     * 店员退出店铺
     * @return mixed
     */
    public function quit(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($user->is_manager) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::OWNER_CANNOT_QUIT], ErrcodeMap::OWNER_CANNOT_QUIT);
        }
        $user->store_id = NULL;
        $user->save();

        // 发送退出店铺通知

        return $this->message('退出成功');
    }

    /**
     * 保存点击表单的FormId
     * @param Request $request
     * @return mixed
     */
    public function storeFormId(Request $request)
    {
        $user = Auth::guard('api')->user();
        $expiredAt = Carbon::now()->addDays(7)->getTimestamp();
        // 在队列尾部插入新的form_id
        \Redis::zadd('form_id_of_'. $user->id, $expiredAt, $request->input('form_id'));
        return $this->message('保存成功');
    }
}
