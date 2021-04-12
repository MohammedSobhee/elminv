<?php

namespace App\Services;

// Model
use App\ClassMembers;

class ClassMembersService {

    /**
     * Get list of class members by user ID
     *
     * @param  int $user_id
     * @return array
     */
    public static function get($user_id) {
        $cm = ClassMembers::where('user_id', $user_id)
            ->join('class', 'class.id', '=', 'class_members.class_id')
            ->select('class.id', 'class.class_name', 'class.class_type')
            ->orderBy('class.class_name', 'asc')
            ->get();
        return $cm;
    }
}
