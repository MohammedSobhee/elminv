<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model {
    protected $guarded = [];

    protected $appends = ['date'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getDateAttribute() {
        if ($this->updated_at) {
            return $this->updated_at->format('g:i A');
        } else {
            return null;
        }
    }

}
