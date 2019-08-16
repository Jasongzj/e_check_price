<?php

namespace App\Http\Controllers;

use App\Map\ErrcodeMap;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionsController extends Controller
{
    public function store(Request $request)
    {
        $owner = Auth::guard('api')->user();
        if (!$owner->is_manager) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::NOT_OWNER], ErrcodeMap::NOT_OWNER);
        }
        $user = User::query()->where('id', $request->input('user_id'))
            ->where('is_manager', 0)
            ->first();
        if (!$user || $owner->store_id != $user->store_id) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::NOT_YOUR_CLERK], ErrcodeMap::NOT_YOUR_CLERK);
        }

        // 保存用户权限
        $user->permissions()->sync($request->input('pids'));

        return $this->message('保存成功');
    }
}
