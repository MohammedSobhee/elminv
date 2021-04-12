<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;

class Worksheet extends Model {
    protected $table = 'worksheet';
    public function groups() {
        return $this->hasMany('App\WorksheetFormFieldGroups', 'worksheet_id', 'id');
    }
}
