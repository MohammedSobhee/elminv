<?php
namespace App\Traits;


trait CoursewareTypesTrait {
    /**
     * Get Courseware types for use in header's courseware select
     *
     * @param  array $arr
     * @return array
     */
    public function getCoursewareTypes($arr) {
        $courseware_types = config('constants.courseware_types');
        foreach ($arr as $value) {
            $returned_types[$value] = $courseware_types[$value];
        }

        // Remove one of the elementary options
        if (array_key_exists(2, $returned_types)) {
            $returned_types[1] = $returned_types[2];
            unset($returned_types[2]);
        }

        krsort($returned_types);
        return $returned_types;
    }
}
