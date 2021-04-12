<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoCon extends Model {
    protected $guarded = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['date', 'name'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getDateAttribute() {
        return $this->updated_at->diffForHumans();
    }

    public function getNameAttribute() {
        $name = '';

        switch ($this->vtype) {
            case 1:
                $name = Classes::find($this->vtype_id)->class_name;
                break;

            case 2:
                $name = Teams::find($this->vtype_id)->team_name;
                break;

            case 3:
                $user = User::find($this->vtype_id);
                if ($user->role->slug === 'student') {
                    $name = $user->name;
                } else {
                    $name = $user->nickname ?: $user->name;
                }
                break;

            default:
                $name = 'Unknown';
                break;

        }
        return $name;
    }
}
