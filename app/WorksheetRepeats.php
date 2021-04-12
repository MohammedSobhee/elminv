<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class WorksheetRepeats extends Model {
    //use SoftDeletes;

    protected $table = 'worksheet_group_repeats';
    protected $guarded = ['id'];
}
