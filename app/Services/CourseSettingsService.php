<?php

namespace App\Services;

use App\CourseSettings;

class CourseSettingsService {

    /**
     * Get Course Settings
     *
     * @param  array $settings
     * @return object
     */
    public static function get(array $settings = NULL) {
        $cs = CourseSettings::select('name', 'value')->where('teacher_id', auth()->user()->getTeacherID());
        if ($settings) {
            $cs->whereIn('name', $settings);
        }
        $cs = $cs->get();
        $csettings = [];

        // Ensure that no requested setting has undefined index
        if ($settings) {
            foreach ($settings as $s) {
                if ($cs->contains('name', $s)) {
                    $value = $cs->where('name', $s)->pluck('value')[0];
                    $csettings[$s] = ($value == 1 || $value == 0) ? (int) $value : $value;
                } else {
                    $csettings[$s] = 0;
                }

            }

        // Else return all set settings of a teacher
        } else {
            foreach ($cs as $c) {
                $csettings[$c->name] = ($c->value == 1 || $c->value == 0) ? (int) $c->value : $c->value;
            }
        }
        return $csettings;
    }
}
