<?php

namespace App\Listeners;

use App\Events\ClerkAdded;
use App\Models\User;
use App\Services\WechatService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendClerkJoinNotification implements ShouldQueue
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
     * @param ClerkAdded $event
     * @return void
     * @throws \App\Exceptions\WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function handle(ClerkAdded $event)
    {
        $wechatService = new WechatService();
        $message = [
            'keyword1' => [
                'value' => $event->store->name,
            ],
            'keyword2' => [
                'value' => $event->clerk->nick_name,
            ],
            'keyword' => [
                'value' => '加入店铺成功',
            ]
        ];
        $templateId = '';

        $owner = User::query()
            ->where('store_id', $event->store->id)
            ->where('is_manager', 1)
            ->first();

        // Redis 按过期时间排序，取最临近过期的值
        $formId = \Redis::zrange('form_id_of'.$owner->id, 0, 0);
        if ($formId) {
            $wechatService->sendTemplateMessage($owner->openid, $templateId, $formId, $message);

        }
    }
}
