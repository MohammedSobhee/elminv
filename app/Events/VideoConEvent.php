<?php

namespace App\Events;
use App\VideoCon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VideoConEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $videocon;
    public $vtype; // 1 = class, 2 = team, 3 = private
    public $vtype_id; // class, team id, user_id

    public function __construct(VideoCon $videocon, $vtype, $vtype_id) {
        $this->videocon = $videocon;
        $this->vtype = $vtype;
        $this->vtype_id = $vtype_id;
    }

    public function broadcastOn() {
        return new PresenceChannel('videocon.' . $this->vtype . '.' . $this->vtype_id);
    }
}
