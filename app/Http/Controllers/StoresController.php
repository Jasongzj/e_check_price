<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            return $this->failed('你已经有店铺了哦', 4002);
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
     * 添加店员
     */
    public function addClerk()
    {

    }


    /**
     * 移除店员
     */
    public function delClerk()
    {

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
}
