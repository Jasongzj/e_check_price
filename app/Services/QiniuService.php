<?php
/**
 * Created by PhpStorm.
 * User: jasongao
 * Date: 2019-08-15
 * Time: 10:05
 */

namespace App\Services;

use Carbon\Carbon;

class QiniuService
{
    public $key;

    public $domain;

    public function __construct()
    {
        $this->key = config('filesystems.disks.qiniu.anti_theft_key');
        $this->domain = config('filesystems.disks.qiniu.domain');
    }

    /**
     * 获取七牛云防盗链链接
     * @param $url
     * @return string
     */
    public function getAntiTheftUrl($url)
    {
        $expired = dechex(Carbon::now()->addMinutes(60)->getTimestamp());
        $path = substr($url, strlen($this->domain));
        $encodePath = str_replace('%2F', '/', urlencode($path));
        $sign = strtolower(md5($this->key . $encodePath . $expired));
        $antiTheftUrl = $url . '?sign=' . $sign . '&t=' . $expired;
        return $antiTheftUrl;
    }
}
