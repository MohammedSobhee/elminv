<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatDeleteEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $ctype;
    public $ctype_id;

    public function __construct($id, $ctype, $ctype_id) {
        $this->id = $id;
        $this->ctype = $ctype;
        $this->ctype_id = $ctype_id;
    }

    public function broadcastOn() {
        return new PresenceChannel('chat.' . $this->ctype . '.' . $this->ctype_id);
    }
}
