<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClerkResource;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoresController extends Controller
{
    /**
     * 创建店铺
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($user->store) {
            return $this->failed('你已经有店铺了哦', 40001);
        }

        $attribute = $request->only(['name', 'img']);

        DB::transaction(function () use ($attribute, $user) {
            $store = new Store($attribute);
            $store->save();


            $user->store()->associate($store);
            $user->is_manager = true;
            $user->save();
        });

        return $this->message('保存成功');
    }

    /**
     * 成为店员
     * @param Request $request
     * @return mixed
     */
    public function addClerk(Request $request)
    {
        $user = Auth::guard('api')->user();
        // 判断用户是否有店铺
        if ($user->store_id) {
            return $this->failed('你已经有店铺了哦', 40001);
        }
        Log::debug('店铺ID：' . $request->input('store_id'));
        
        $user->update(['store_id' => $request->input('store_id')]);

        return $this->message('添加成功');
    }


    /**
     * 移除店员
     * @param User $clerk
     * @return mixed
     */
    public function delClerk(User $clerk)
    {
        $user = Auth::guard('api')->user();
        if ($clerk->id == $user->id) {
            return $this->failed('不能移除自己哦', 40007);
        }

        if (!$user->is_manager) {
            return $this->failed('你不是店长哦', 40002);
        }
        if ($user->store_id != $clerk->store_id) {
            return $this->failed('该用户不是你的店员', 40006);
        }

        $clerk->update(['store_id' => null]);
        return $this->message('移除成功');
    }

    /**
     * 店员列表
     */
    public function clerksIndex()
    {
        $user = Auth::guard('api')->user();
        if (!$user->is_manager) {
            return $this->failed('你不是店长哦',40002);
        }

        $clerks = User::query()->where('store_id', $user->store_id)
            ->where('is_manager', 0)
            ->select(['id', 'store_id', 'is_manager', 'nick_name', 'avatar_url'])
            ->paginate();

        return ClerkResource::collection($clerks);
    }

    /**
     * 上传店铺图片
     * @param Request $request
     * @return mixed
     */
    public function uploadImg(Request $request)
    {
        $path = $request->file('img')->store('stores', 'public');
        $url = asset('storage/' . $path);

        return $this->success($url);
    }

    /**
     * 注销店铺
     * @return mixed
     */
    public function destroy()
    {
        $user = Auth::guard('api')->user();
        if (!$user->is_manager) {
            return $this->failed('你不是店长，不能注销店铺哦', 40010);
        }
        $storeId = $user->store_id;

        DB::transaction(function () use ($storeId) {
            // 删除店内商品
            StoreProduct::query()->where('store_id', $storeId)
                ->delete();
            // 注销店铺
            Store::query()->where('id', $storeId)->delete();
            // 移除所有店员
            User::query()->where('store_id', $storeId)
                ->update([
                    'store_id' => null,
                    'is_manager' => 0,
                ]);
        });

        return $this->success('移除成功');
    }
}
