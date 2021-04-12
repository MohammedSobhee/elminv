<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RubricSettings extends Model {
    protected $table = 'rubric_settings';

    public function cats() {
        return $this->hasOne('App\RubricCategories', 'id', 'category_id');
    }
}
