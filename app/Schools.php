<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use App\Scopes\ActiveScope;

use Laravel\Scout\Searchable;

class Schools extends Model {
    use Searchable;

    //
    // Relationships
    // --------------------------------------------------------------------------

    public function users() {
        return $this->hasMany('App\User', 'school_id', 'id');
    }

    public function contact() {
        return $this->hasOne('App\SchoolContactInfo', 'school_id', 'id');
    }

    public function settings() {
        return $this->hasOne('App\SchoolSettings', 'school_id', 'id');
    }

    public function types() {
        return $this->hasManyThrough(ClassType::class, SchoolTypes::class, 'school_id', 'id', 'id', 'class_type');
    }

    //
    // Searching
    // --------------------------------------------------------------------------
    public function searchableAs() {
        return 'schools_index';
    }

    public function toSearchableArray() {
        return $this->toArray();
    }

    protected function makeAllSearchableUsing($query) {
        return $query->with('contact', 'types', 'settings');
    }

    /**
     * Generate school, student, and teacher code
     * TODO: Does assistant teacher need to be added to this?
     *
     * @return void
     */
    public function generateCode() {
        $id = $this->id;
        $school_code = $id . str_random(4);
        $student_code = $id . str_random(4);
        $teacher_code = $id . str_random(4);
        if ($this->where('school_code', $school_code)->exists()) {
            // Generate new code
            $this->generateCode($id);
        } elseif ($this->where('student_code', $student_code)->exists()) {
            // Generate new code
            $this->generateCode($id);
        } elseif ($this->where('teacher_code', $teacher_code)->exists()) {
            // Generate new code
            $this->generateCode($id);
        } else {
            $this->school_code = $school_code;
            $this->student_code = $student_code;
            $this->teacher_code = $teacher_code;
            $this->save();
        }
    }

    /**
     * Get school status
     *
     * @param  mixed $id
     * @return void
     */
    public function status($id) {
        $this_id = $id ?: $this->id;
        return $this->select('status')->where('id', $this_id)->first();
    }

    //
    // Accessors
    // --------------------------------------------------------------------------
    public function getPaymentStatusAttribute() {
        if (isset($this->settings)) {
            switch ($this->settings->payment_due) {
                case 1:
                    $status = 'Payment Due';
                    break;
                case 0:
                    $status = 'Paid';
                    break;
                default:
                    $status = 'N/A';
            }
            return $status;
        } else {
            return 'N/A';
        }
    }

    protected static function boot() {
        parent::boot();

        // static::addGlobalScope(new ActiveScope);
    }
}
