<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RemoveExpiredFormId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $expired = Carbon::now()->getTimestamp();

        // 删除用户队列里过期的form_id
        $owners = User::query()->where('is_manager', 1)->get(['id']);
        foreach ($owners as $owner) {
            \Redis::zRemRangeByScore('form_id_of_'.$owner->id,0, $expired);
        }
    }
}
