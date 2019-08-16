<?php
/**
 * Created by PhpStorm.
 * User: jasongao
 * Date: 2019-08-15
 * Time: 10:05
 */

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
        $path = $this->getFilePath($url);
        $encodePath = str_replace('%2F', '/', urlencode($path));
        $sign = strtolower(md5($this->key . $encodePath . $expired));
        $antiTheftUrl = $url . '?sign=' . $sign . '&t=' . $expired;
        return $antiTheftUrl;
    }

    /**
     * 删除七牛的文件
     * @param $url
     */
    public function deleteFile($url)
    {
        $disk = Storage::disk('qiniu');
        $path = substr($url, strlen($this->domain) + 1);  // 不带根路径
        logger('qiniu file path = ' . $path);
        $disk->delete($path);
    }

    /**
     * 获取不带域名的文件路径
     * @param $url
     * @return bool|string
     */
    protected function getFilePath($url)
    {
        return substr($url, strlen($this->domain));
    }
}
