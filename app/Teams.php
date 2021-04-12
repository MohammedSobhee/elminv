<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Teams extends Model {
    //use SoftDeletes;

    protected $table = 'users_teams';

    public function members() {
        //return $this->hasMany('App\TeamMembers', 'team_id', 'id');
        return $this->hasManyThrough(User::class, TeamMembers::class, 'team_id', 'id', 'id', 'user_id');
    }

    public function class() {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
