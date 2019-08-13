<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClerkResource;
use App\Map\ErrcodeMap;
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
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::STORE_EXIST], ErrcodeMap::STORE_EXIST);
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
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::CANNOT_CANCEL_STORE], ErrcodeMap::CANNOT_CANCEL_STORE);
        }
        $storeId = $user->store_id;

        DB::transaction(function () use ($storeId) {
            // 移除所有店员
            User::query()->where('store_id', $storeId)
                ->update([
                    'store_id' => null,
                    'is_manager' => 0,
                ]);
            // 删除店内商品
            StoreProduct::query()->where('store_id', $storeId)
                ->delete();
            // 注销店铺
            Store::query()->where('id', $storeId)->delete();

        });

        return $this->success('移除成功');
    }
}
