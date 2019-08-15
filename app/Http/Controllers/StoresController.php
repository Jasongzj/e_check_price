<?php

namespace App\Http\Controllers;

use App\Events\CreateStore;
use App\Http\Resources\ClerkResource;
use App\Jobs\generateStoreQrCode;
use App\Map\ErrcodeMap;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\User;
use App\Services\QiniuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        $store = DB::transaction(function () use ($attribute, $user) {
            $store = new Store($attribute);
            $store->save();

            $user->store()->associate($store);
            $user->is_manager = true;
            $user->save();
            return $store;
        });

        // 异步生成店铺二维码
        event(new CreateStore($store));

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
    public function destroy(QiniuService $qiniuService)
    {
        $user = Auth::guard('api')->user();
        if (!$user->is_manager) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::CANNOT_CANCEL_STORE], ErrcodeMap::CANNOT_CANCEL_STORE);
        }
        $storeId = $user->store_id;

        DB::transaction(function () use ($storeId, $qiniuService) {
            $store = Store::query()->find($storeId);
            // 移除所有店员
            User::query()->where('store_id', $storeId)
                ->update([
                    'store_id' => null,
                    'is_manager' => 0,
                ]);
            // 删除店内商品
            StoreProduct::query()->where('store_id', $storeId)
                ->delete();

            // 删除店铺图片
            $domain = config('app.url');
            $imgPath = substr($store->img,strlen($domain));
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }

            // 删除店铺二维码
            $qiniuService->deleteFile($store->qr_code);

            // 注销店铺
            Store::query()->where('id', $storeId)->delete();


        });

        return $this->success('注销成功');
    }

    /**
     * 店长信息
     * @param Request $request
     * @return mixed
     */
    public function getOwner(Request $request)
    {
        $owner = User::query()
            ->where('store_id', $request->input('store_id'))
            ->where('is_manager', 1)
            ->select(['id', 'nick_name', 'avatar_url'])
            ->first();

        return $this->success($owner);
    }
}
