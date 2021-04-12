<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class WorksheetAnswers extends Model {
    //use SoftDeletes;

    protected $table = 'worksheet_answers';
    protected $guarded = ['id'];
}
