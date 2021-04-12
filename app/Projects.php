<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

//use Illuminate\Database\Eloquent\SoftDeletes;

class Projects extends Model {
    //use SoftDeletes;

    protected $table = 'project';

    public function members() {
        return $this->hasManyThrough(User::class, ProjectMembers::class, 'project_id', 'id', 'id', 'user_id');
    }

    public function lock() {
        $this->locked = 1;
        return $this->save();
    }

    public function unlock() {
        $this->locked = 0;
        return $this->save();
    }

    public static function findByUser($user_id) {
        return self::join('project_members as pm', 'pm.project_id', 'project.id')
            ->where('pm.user_id', $user_id)
            ->select('project.*', 'pm.user_id');
    }
}
