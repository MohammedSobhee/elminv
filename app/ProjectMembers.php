<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectMembers extends Model {
    //use SoftDeletes;

    protected $table = 'project_members';
    protected $fillable = ['user_id', 'project_id'];

    // public function project() {
    //     return $this->belongsTo('App\Projects');
    // }
    // public function user() {
    //     return $this->hasOne('App\User', 'id', 'user_id');
    // }
}
