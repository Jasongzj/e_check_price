<?php
/**
 * Created by PhpStorm.
 * User: jasongao
 * Date: 2019-08-14
 * Time: 00:06
 */

namespace App\Map;


class ErrcodeMap
{
    const STORE_EXIST = 40001;
    const NOT_OWNER = 40002;
    const NOT_YOUR_CLERK = 40003;
    const CANNOT_CANCEL_STORE = 40004;
    const NOT_YOUR_PROD = 40005;
    const OWNER_CANNOT_QUIT = 40006;
    const NO_STORE = 40007;

    public static $errcode = [
        self::STORE_EXIST => '你已经有店铺了哦',
        self::NOT_OWNER => '你不是店长哦',
        self::NOT_YOUR_CLERK => '该用户不是你的店员',
        self::CANNOT_CANCEL_STORE => '你不是店长，不能注销店铺哦',
        self::NOT_YOUR_PROD => '这不是你店铺的商品哦',
        self::OWNER_CANNOT_QUIT => '店长不能退出店铺，可以去注销',
        self::NO_STORE => '你还没有店铺，无法操作',
    ];
}
