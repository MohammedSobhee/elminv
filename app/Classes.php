<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\ActiveScope;

class Classes extends Model {
    //use SoftDeletes;

    protected $table = 'class';
    //protected $appends = ['updated'];

    public function members() {
        return $this->hasManyThrough(User::class, ClassMembers::class, 'class_id', 'id', 'id', 'user_id');
    }

    public function users() {
        return $this->hasManyThrough(User::class, ClassMembers::class, 'class_id', 'id', 'id', 'user_id');
    }

    public function type() {
        return $this->hasOne(ClassType::class, 'id', 'class_type');
    }

    public function teams() {
        return $this->hasMany(Teams::class, 'class_id', 'id');
    }

    /**
     * Get class by class ID
     *
     * @param  int $id
     * @return object
     */
    public function classByID($id) {
        return $this->where('class.id', $id)
            ->select('class.id', 'school_id', 'class_name', 'class_type', 'student_code', 'assistant_teacher_code')->first();
    }

    /**
     * Generate student and assistant teacher activation codes
     *
     * @param  int $id
     * @return void
     */
    public function generate($id) {
        $teacher_code = $id . str_random(4);
        $student_code = $id . str_random(4);

        if ($this->where('student_code', $student_code)->exists()) {
            // Generate new code
            $this->generate($id);
        } elseif ($this->where('assistant_teacher_code', $teacher_code)->exists()) {
            // Generate new code
            $this->generate($id);
        } else {
            $this->student_code = $student_code;
            $this->assistant_teacher_code = $teacher_code;
            $this->save();
        }
    }

    //
    // Accessors
    // --------------------------------------------------------------------------
    // public function getUpdatedAttribute() {
    //     if ($this->updated_at) {
    //         return $this->updated_at->diffForHumans();
    //     }
    // }

    public function getClassTypeNameAttribute() {
        return $this->type->name ?? '';
    }

    protected static function boot() {
        parent::boot();
        static::addGlobalScope(new ActiveScope);
    }
}
