<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMembers extends Model {
    //use SoftDeletes;

    protected $table = 'users_teams_members';
    protected $primaryKey = 'class_id';
    protected $appends = ['updated'];

    public function getUpdatedAttribute() {
        if ($this->updated_at) {
            return $this->updated_at->diffForHumans();
        }
    }
}
