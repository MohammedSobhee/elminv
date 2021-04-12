<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VideoConDeleteEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $vtype;
    public $vtype_id;

    public function __construct($id, $vtype, $vtype_id) {
        $this->id = $id;
        $this->vtype = $vtype;
        $this->vtype_id = $vtype_id;
    }

    public function broadcastOn() {
        return new PresenceChannel('videocon.' . $this->vtype . '.' . $this->vtype_id);
    }
}
