<?php

namespace App\Events;

use App\Models\Store;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ClerkAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $store;

    public $clerk;

    public $formId;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Store $store, User $clerk)
    {
        $this->store = $store;
        $this->clerk = $clerk;
    }
}
