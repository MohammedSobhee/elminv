<?php

namespace App\Events;

use App\Chat;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;
    public $ctype; // 1 = class, 2 = team, 3 = private
    public $ctype_id; // class, team id

    public function __construct(Chat $chat, $ctype, $ctype_id) {
        $this->chat = $chat;
        $this->ctype = $ctype;
        $this->ctype_id = $ctype_id;
    }

    public function broadcastOn() {
        return new PresenceChannel('chat.' . $this->ctype . '.' . $this->ctype_id);
    }
}
