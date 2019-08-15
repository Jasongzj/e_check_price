<?php

namespace App\Events;

use App\Models\Store;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class CreateStore
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $store;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }
}
