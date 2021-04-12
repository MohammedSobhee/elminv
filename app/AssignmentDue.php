<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentDue extends Model {
    //use SoftDeletes;

    protected $table = 'assignment_due';
    protected $guarded = ['id'];
}
