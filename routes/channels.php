<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Chat
Broadcast::channel('chat.{ctype}.{ctype_id}', function ($user, $ctype, $ctype_id) {
    if ($ctype == 1 && $user->isInClass($ctype_id)) { // Class
        return $user->toArray();
    } else if($ctype == 2 && $user->isInTeam($ctype_id)) { // Team
        return $user->toArray();
    } else if($ctype == 3 && auth()->check()) { // Private
        return $user->toArray();
    }
});

// Video Conference Alerts
Broadcast::channel('videocon.{vtype}.{vtype_id}', function ($user, $vtype, $vtype_id) {
    if ($vtype == 1 && $user->isInClass($vtype_id)) { // Class
        return $user->toArray();
    } else if ($vtype == 2 && $user->isInTeam($vtype_id)) { // Team
        return $user->toArray();
    } else if ($vtype == 3 && auth()->check()) { // Private
        return $user->toArray();
    }
});
