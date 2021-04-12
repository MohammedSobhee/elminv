<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentSubmitted extends Model {
    //use SoftDeletes;

    protected $table = 'assignment_submitted';
    protected $guarded = ['id'];

}
