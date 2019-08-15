<?php

namespace App\Listeners;

use App\Events\CreateStore;
use App\Services\WechatService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateStoreQrCode implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param CreateStore $event
     * @param WechatService $wechatService
     * @return void
     * @throws \App\Exceptions\WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function handle(CreateStore $event, WechatService $wechatService)
    {
        // 获取店铺二维码地址
        $qrcode = $wechatService->getStoreQrCodeUrl($event->store->id);
        // 保存二维码地址
        $event->store->qr_code = $qrcode;
        $event->store->save();
    }
}
