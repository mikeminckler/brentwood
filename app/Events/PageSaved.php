<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

use App\Models\Page;
use App\Models\Role;

class PageSaved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $page;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($page)
    {
        $this->page = $page;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('role.'.Str::plural($this->page->type).'-editor');
    }
}
