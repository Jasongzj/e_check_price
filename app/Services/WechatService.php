<?php

namespace App\Services;

use App\Exceptions\WechatException;
use EasyWeChat\Kernel\Http\StreamResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Overtrue\LaravelWeChat\Facade as EasyWeChat;

class WechatService extends AbstractService
{
    /**
     * 根据code获取用户信息
     * @param $code
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function session($code)
    {
        $response = $this->getMiniProgram()->auth->session($code);

        $this->checkResponse($response);

        return $response;
    }

    /**
     * 解密微信加密的用户信息
     * @param $sessionKey
     * @param $iv
     * @param $encrypted
     * @return array
     * @throws WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     */
    public function decryptData($sessionKey, $iv, $encrypted)
    {
        $response = $this->getMiniProgram()->encryptor->decryptData($sessionKey, $iv, $encrypted);

        $this->checkResponse($response);

        return $response;
    }

    /**
     * 校验微信返回响应
     * @param $response
     * @throws WechatException
     */
    public function checkResponse($response)
    {
        if (isset($response['errcode'])) {
            Log::error('登录失败，错误原因' . $response['errcode'] . '|' . $response['errmsg']);
            throw new WechatException($response['errmsg'], $response['errcode']);
        }
    }

    /**
     * 生成店铺二维码
     * @param $storeId
     * @return string
     * @throws WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function getStoreQrCodeUrl($storeId)
    {
        // 邀请加入页面
        $path = 'pages/clerks_add/clerks_add';
        $response = $this->getMiniProgram()->app_code->getUnlimit($storeId, [
            'path' => $path,
        ]);
        if ($response instanceof StreamResponse) {
            // 保存到本地
            $filename = $response->saveAs(storage_path('store_code'), 'store_'. $storeId . '.png');
            $localPath = storage_path('store_code/'. $filename);
            $fileContents = file_get_contents($localPath);
            // 上传七牛云
            $disk = Storage::disk('qiniu');
            $disk->put('store_qr_code', $fileContents);
            $qiniuPath = 'store_qr_code/' . $filename;
            // 删除本地文件
            unlink($localPath);
            return $disk->getUrl($qiniuPath);
        }
        throw new WechatException($response['errmsg'], $response['errcode']);
    }
}
