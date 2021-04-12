<?php
namespace App;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\PermissionTrait;
use App\Scopes\ActiveScope;
use DB;
// use App\Teams;
// use App\ClassMembers;
// use App\TeamMembers;
// use App\Classes;
// use App\Schools;

use Laravel\Scout\Searchable;
//use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\MailResetPasswordNotification;

class User extends Authenticatable {
    use Notifiable, PermissionTrait, Searchable;
    //use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'name', 'email', 'password', 'note', 'provider_name', 'provider_id'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'provider_name', 'provider_id', 'password', 'remember_token'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // protected $appends = ['school_name', 'school_status', 'school_district', 'role_name', 'role_slug', 's_class_name', 'can_school_admin'];

    //
    // Relationships
    // --------------------------------------------------------------------------
    public function school() {
        return $this->hasOne(Schools::class, 'id', 'school_id');
    }

    public function role() {
        // user_roles->user_id, roles->id, user->id, user_roles->role_id
        return $this->hasOneThrough(Roles::class, UserRoles::class, 'user_id', 'id', 'id', 'role_id');
    }

    public function permissions() {
        return $this->hasManyThrough(Permission::class, UserPermissions::class, 'user_id', 'id', 'id', 'permission_id');
    }

    public function cls() {
        return $this->hasOneThrough(Classes::class, ClassMembers::class, 'user_id', 'id', 'id', 'class_id');
    }

    public function classes() {
        return $this->hasManyThrough(Classes::class, ClassMembers::class, 'user_id', 'id', 'id', 'class_id');
    }

    public function projects() {
        return $this->hasManyThrough(Projects::class, ProjectMembers::class, 'user_id', 'id', 'id', 'project_id');
    }

    public function team() {
        return $this->hasOneThrough(Teams::class, TeamMembers::class, 'user_id', 'id', 'id', 'team_id');
    }

    public function chats() {
        return $this->hasMany(Chat::class);
    }

    public function videocons() {
        return $this->hasMany(VideoCon::class);
    }

    public function zoomtoken() {
        return $this->hasOne(ZoomAuthToken::class);
    }

    //
    // Searching
    // --------------------------------------------------------------------------
    public function searchableAs() {
        return 'users_index';
    }

    public function toSearchableArray() {
        $array = $this->toArray();
        if($this->role && $this->role->slug == 'teacher' && $this->canSchoolAdmin()) {
            $array['role']['name'] = 'Hybrid Teacher';
        }
        return $array;
    }

    protected function makeAllSearchableUsing($query) {
        return $query->with('school', 'role', 'classes', 'permissions');
    }

    public function shouldBeSearchable() {
        return $this->role !== null;
    }

    /**
     * getAvatar - Get user's avatar
     *
     * @return string
     */
    public function getAvatar() {
        $image = '';
        if (!$this->avatar || (substr($this->avatar, 0, 8) == 'https://')) {
            $image = $this->avatar;
            // Prevent not found (typical after live db import to dev / local)
        } else if (\Storage::exists('avatars/' . $this->avatar)) {
            $image = "/avatars/" . $this->avatar;
        }
        return $image;
    }

    /**
     * isInTeam
     *
     * @param  mixed $team_id
     * @return int|null
     */
    public function isInTeam($team_id) {
        if ($this->role->slug == 'student') {
            // Student
            return $this->team->id == $team_id;
        } else if ($this->role->slug == 'teacher' || $this->role->slug == 'assistant-teacher') {
            // Teacher
            return in_array($team_id, $this->getTeamIDs());
        } else {
            return false;
        }
    }

    /**
     * getTeamID - team.id, array of team.ids, or null
     *
     * @return int
     */
    public function getTeamID() {
        if ($this->role->slug == 'student' && $this->team) {
            return $this->team->id;
        } else {
            return 0;
        }
    }

    /**
     * getTeamIDs
     *
     * @return array
     */
    public function getTeamIDs() {
        $ids = [];
        if ($this->role->slug == 'student' && $this->team) {
            $ids = (array) $this->team->id;
        } else if ($this->role->slug == 'teacher' || $this->role->slug == 'assistant-teacher') {
            $ids = Teams::whereIn('class_id', $this->getClassID())->pluck('id')->toArray();
            if(!is_array($ids)) {
                $ids = [];
            }
        }
        return $ids;
    }

    /**
     * isInClass - Determine if user is in a class
     *
     * @param  mixed $class_id
     * @return int|null
     */
    public function isInClass($class_id) {
        if ($this->role->slug == 'student') {
            // Student
            return $this->getClassID() == $class_id;
        } else if ($this->role->slug == 'teacher' || $this->role->slug == 'assistant-teacher') {
            // Teacher / Assistant Teacher
            $classes = $this->getClassID();
            return in_array($class_id, $classes);
        }
    }

    /**
     * getClassType - Return class type # for student, array of type #s for teacher,
     * 99 for student without a class.
     *
     * @return int|array
     */
    public function getClassType() {
        if ($this->role->slug == 'student') {
            return $this->cls->class_type ?? 99;
        } else if ($this->role->slug == 'teacher' || $this->role->slug == 'assistant-teacher') {
            return $this->classes->pluck('class_type')->unique()->sort()->values()->toArray();
        } else {
            return 99;
        }
    }
    public function getClassTypes() {
        return $this->classes->pluck('class_type')->unique()->sort()->values()->toArray();
    }

    /**
     * getClassID - Class.id, array of class.ids, or null
     *
     * @return int|array
     */
    public function getClassID() {
        if ($this->role->slug == 'student') {
            // Student
            return $this->cls->id ?? 0;
        } else if ($this->role->slug == 'teacher' || $this->role->slug == 'assistant-teacher') {
            // Teacher / Assistant Teacher
            return $this->classes->pluck('id')->sort()->values()->toArray();
        } else {
            return 0;
        }
    }
    public function getClassIDs() {
        return $this->classes->pluck('id')->sort()->values()->toArray();
    }

    /**
     * getClassName - Get class name by ID or $this->getClassID if student
     *
     * @return string|array
     */
    public function getClassName($id = '') {
        $name = null;
        if ($id) {
            $name = Classes::find($id)->class_name ?? null;
        } else if ($this->role->slug == 'student') {
            $name = $this->cls->class_name ?? null;
        } else if ($this->role->slug == 'teacher' || $this->role->slug == 'assistant-teacher') {
            $name = $this->classes->pluck('class_name')->toArray();
        }
        return $name;
    }

    /**
     * getTeacherID - Get teacher ID of current user
     *
     * @return int
     */
    public function getTeacherID($id = 0) {
        $this_id = $id ?: $this->id;
        $teacher_id = 0;
        if ($this->role->slug == 'teacher') {
            $teacher_id = $this->id;
        } else if ($this->role->slug == 'assistant-teacher' || $this->role->slug == 'student') {
            $class = ClassMembers::where('user_id', $this_id)->first();
            if ($class) {
                $cm = ClassMembers::where(['class_id' => $class->class_id, 'role_id' => 3])->first();
                if ($cm) {
                    $teacher_id = $cm->user_id;
                }
            }
        }
        return $teacher_id;
    }

    /**
     * getTeacherName - Get name of teacher of current user
     *
     * @return string
     */
    public function getTeacherName() {
        $teacher = User::find($this->getTeacherID());
        $teacher_name = $teacher
            ? ($teacher->nickname ?: $teacher->name)
            : '';
        return $teacher_name;
    }

    /**
     * canSchoolAdmin - Determine if a user can also be a school admin
     *
     * @return boolean
     */
    public function canSchoolAdmin() {
        return $this->permissions->where('slug', 'school-admin')->isNotEmpty() || $this->role->slug == 'school-admin';
    }

    /**
     * canEditClass - Determine if a user can edit the specified class
     *
     * @param  int $id
     * @return boolean
     */
    public function canEditClass($id) {
        if (is_array($id)) {
            return $this->getClassID() == $id;
        } else {
            $class_school_id = Classes::find($id)->school_id;
            return (
                (in_array($this->role->slug, ['teacher', 'assistant-teacher']) && in_array($id, $this->getClassID())) ||
                ($class_school_id == $this->school_id && ($this->role->slug == 'school-admin' || $this->canSchoolAdmin()))
            );
        }
    }

    /**
     * canEditTeam - Determine if teacher and can edit team.
     *
     * @param  mixed $id
     * @return boolean
     */
    public function canEditTeam($id) {
        $class_id = Teams::find($id)->class_id;
        if (in_array($this->role->slug, ['teacher', 'assistant-teacher'])) {
            return in_array($class_id, $this->getClassID());
        }
    }

    /**
     * hasAdminPermission
     *
     * @return boolean
     */
    public function hasAdminPermission() {
        return in_array($this->role->slug, ['admin', 'developer', 'manager']);
    }

    /**
     * getLoginAsPermission - Determine if user has permission to Login as a specified user
     *
     * @param  int $id
     * @return boolean
     */
    public function getLoginAsPermission($id) {
        $u = User::find($id);
        if(!$u) return;

        // Prevent --anyone-- from running as one of these:
        if(in_array($u->role->slug, ['admin', 'developer', 'manager']))
            return;

        // Admin
        if ($this->hasAdminPermission()) {
            return $u;
        // School admin
        } else if ($this->canSchoolAdmin() && ($u->school_id == $this->school_id)) {
            return $u;
        // Teacher / Assistant
        } else if (
            ($this->role->slug == 'teacher' || $this->role->slug == 'assistant-teacher') &&
            $this->classes()
                ->whereHas('users', fn($q) => $q
                ->where('user_id', '=', $id)
                ->where('user_id', '<>', $this->getTeacherID()))
                ->exists()
        ) {
            return $u;
        }
    }

    /**
     * hasWorksheetAccess
     *
     * @return int
     */
    public function hasWorksheetAccess() {
        $access = 0;
        if($this->role->slug == 'student') {
            $access = $this->getClassType() > 2;
        } else if ($this->role->slug == 'teacher' || $this->role->slug == 'assistant-teacher') {
            $ct = $this->getClassType();
            $access = in_array(3, $ct) || in_array(4, $ct);
        } else if($this->role->slug == 'school-admin') {
            $ct = $this->school->types()->pluck('class_type')->sort()->values()->toArray();
            $access = in_array(3, $ct) || in_array(4, $ct);
        }
        return intval($access);
    }

    //
    // Accessors
    // --------------------------------------------------------------------------
    public function getFullNameAttribute() {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function getFullNameReversedAttribute() {
        return ucfirst($this->last_name) . ', ' . ucfirst($this->first_name);
    }

    public function getCanSchoolAdminAttribute() {
        return $this->canSchoolAdmin();
    }

    public function getDemoAttribute() {
        return $this->school_id == config('app.demo.school');
    }

    public function getDemoAdminAttribute() {
        return $this->id == config('app.demo.admin');
    }

    /**
     * sendPasswordResetNotification
     *
     * @param  mixed $token
     * @return void
     */
    public function sendPasswordResetNotification($token) {
        $this->notify(new MailResetPasswordNotification($token));
    }

    protected static function boot() {
        parent::boot();
        static::addGlobalScope(new ActiveScope);
    }
}
