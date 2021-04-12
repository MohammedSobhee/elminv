<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSessionData extends Model {
    protected $table = 'users_session_data';
    protected $guarded = ['id'];


    /**
     * Get all user session data
     *
     * @param  int $user_id
     * @return object
     */
    public static function get($user_id = 0) {
        $id = $user_id ?: auth()->user()->id;
        $sess = self::where('user_id', $id)->first();

        if (isset($sess->user_data)) {
            $sess->user_data = json_decode($sess->user_data);
        }
        if(isset($sess->user_data->courseware_types)) {
            $ct = (array) $sess->user_data->courseware_types;
            $sess->user_data->courseware_types = $ct;
        }
        return $sess;
    }

    /**
     * Put use session data
     *
     * @param  int $uid
     * @param  array|string $prop
     * @param  array|string|int $val
     * @return int
     */
    public static function put($uid, $prop, $val = 0) {
        if (is_array($prop)) {
            return self::where('user_id', $uid)->update($prop);
        } else if(!is_array($val)) {
            // casting arrays as json sql not compatible with live server's mariadb version or maybe mariadb in general:
            return self::where('user_id', $uid)->update(['user_data->' . $prop . '' => $val]);
        } else {
            // Temp quick fix
            if($usd = self::get($uid)) {
                $user_data = (array)$usd->user_data;
                if(count($user_data)) {
                    $user_data[$prop] = $val;
                    return self::where('user_id', $uid)->update(['user_data' => json_encode($user_data)]);
                }
            }
        }
    }
}
