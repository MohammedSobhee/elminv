<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Carbon\Carbon;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Messages extends Model {
    //use SoftDeletes;

    protected $table = 'messages';
    protected $guarded = ['id'];
    protected $appends = ['updated'];

    //
    // Accessors
    // --------------------------------------------------------------------------
    public function getUpdatedAttribute($value) {
        if ($this->updated_at) {
            return $this->updated_at->diffForHumans();
        }
    }
}
