<?php

namespace App\Listeners\Chat;

use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserDisconnected
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BroadcastEvent $event): void
    {
        //
    }
}
