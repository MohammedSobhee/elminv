<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentClasses extends Model {
    //use SoftDeletes;

    protected $table = 'assignment_classes';
    protected $primaryKey = 'class_id';

    protected $casts = [
        'course_pages' => 'array'
    ];

}
