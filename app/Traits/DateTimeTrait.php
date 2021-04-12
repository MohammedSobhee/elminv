<?php
namespace App\Traits;


trait DateTimeTrait {
    public function getDateTime($date, $fromFormat = 'F j, Y') {
        if ($date == '') {
            return NULL;
        } else {
            $fdate = \DateTime::createFromFormat($fromFormat, $date);
            return $fdate->format('Y-m-d');
        }
    }
}
