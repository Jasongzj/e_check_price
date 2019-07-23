<?php

namespace App\Services;

use App\Exceptions\WechatException;
use Illuminate\Support\Facades\Log;
use Overtrue\LaravelWeChat\Facade as EasyWeChat;

class WechatService extends AbstractService
{

    public function session($code)
    {
        $response = $this->getMiniProgram()->auth->session($code);

        $this->checkResponse($response);

        return $response;
    }

    public function decryptData($sessionKey, $iv, $encrypted)
    {
        $response = $this->getMiniProgram()->encryptor->decryptData($sessionKey, $iv, $encrypted);

        $this->checkResponse($response);

        return $response;
    }

    /**
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
}
