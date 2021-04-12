<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model {
    //use SoftDeletes;

    protected $table = 'assignment';

    /**
     * Get assignment by auth user class ID and supplied category ID
     *
     * @param  int $categoryID
     * @return object
     */
    public static function byClassAndCategory($categoryID) {
        return self::where([
            ['assignment.category_id', $categoryID],
            ['assignment_classes.class_id', auth()->user()->getClassID()],
            ['assignment_classes.status', 1],
            ['assignment.status', 1]
        ]);
    }

    /**
     * Get assignment by auth user class ID
     *
     * @return object
     */
    public static function byClass() {
        return self::where([
            ['assignment_classes.class_id', auth()->user()->getclassID()],
            ['assignment_classes.status', 1],
            ['assignment.status', 1]
        ]);
    }
}
