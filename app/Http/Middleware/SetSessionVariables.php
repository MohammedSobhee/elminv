<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;

use Closure;
use Hash;

// Model
use App\SchoolSettings;
use App\UserSessionData;

// Service
use App\Services\CourseSettingsService;

// Trait
use App\Traits\CoursewareTypesTrait;

// Legend:
// 1 - K-3 (nologin)
// 2 - 4-5 (elementary)
// 3 - 6-8 (middleschool)
// 4 - 9-12 (highschool)

class SetSessionVariables {
    /**
     * Handle setting session variables on login for Laravel and WP
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    use CoursewareTypesTrait;

    public function handle($request, Closure $next) {
        $response = $next($request);

        if (Auth::check()) {
            $u = Auth::user()->load('role');
            // School class types
            $school_class_types = $u->school->types()->pluck('class_type')->sort()->values()->toArray();
            // Initial user data array
            $user_data = [
                'firstname' => $u->first_name,
                'lastname' => $u->last_name,
                'nickname' => $u->nickname,
                'avatar' => $u->getAvatar(),
                'email' => $u->email,
                'user_id' => $u->id,
                'role_id' => $u->role->id,
                'role' => $u->role->slug,
                'role_name' => $u->role->name,
                'school_id' => $u->school_id,
                'school_name' => $u->school->name,
                'school_admin' => $u->canSchoolAdmin(),
                'school_status' => $u->school->status,
                'teacher_id' => $u->getTeacherID(),
                'settings' => CourseSettingsService::get(['objectives', 'standards']),
                'team_id' => 0,
                'class_id' => 0,
                'class_ids' => [],
                'class_type' => 0,
                'class_types' => [],
                'request_path' => $request->path(),
                //'course_notes' => $u->can('course-notes') ? 1 : 0,
                'courseware_types' => [],
                'worksheets' => $u->hasWorksheetAccess(),
                'loggedinas' => 0,
                'demo' => $u->demo,
                'environment' => app()->environment(),
                'standards' => SchoolSettings::where('school_id', $u->school_id)->first()->standards
            ];

            // Get any existing wp session
            $usersess = UserSessionData::get();

            //
            // Check if logged in 'as' someone
            // --------------------------------------------------------------------------
            if (session()->has('ownUser')) {
                $user_data['loggedinas'] = session()->get('ownUser')->role;
            }
            //
            // Data for specific roles
            // --------------------------------------------------------------------------
            switch ($user_data['role']) {
                // Student
                // --------------------------------------------------------------------------
                case 'student':
                    $user_data['class_id'] = $u->getClassID();
                    $user_data['class_type'] = $u->getClassType();
                    $user_data['team_id'] = $u->getTeamID();
                    break;

                // Teacher, Assistant, School Admin
                // --------------------------------------------------------------------------
                case 'assistant-teacher':
                case 'school-admin':
                case 'teacher':
                    $user_data['class_types'] = $u->canSchoolAdmin() ? $school_class_types : ($u->getClassTypes() ?: $school_class_types);
                    $user_data['class_ids'] = $u->getClassIDs(); // Array of ids (currently used in WP sidebar.php)
                    $user_data['class_id'] = $usersess->user_data->class_id ?? 0; // Remember selected class

                    // Set options for header select courseware dropdown
                    $user_data['courseware_types'] = $this->getCoursewareTypes($school_class_types);

                    // Set user session data class type or default for use in WP
                    if (isset($usersess->user_data->class_type) && $usersess->user_data->class_type) {
                        $user_data['class_type'] = $usersess->user_data->class_type;
                    } elseif (in_array(4, $user_data['class_types'])) {
                        $user_data['class_type'] = 4; // HS
                    } elseif (in_array(3, $user_data['class_types'])) {
                        $user_data['class_type'] = 3; // MS
                    } else { // Ele
                        $user_data['class_type'] = 1;
                    }
                    break;

                //
                // System admin
                // --------------------------------------------------------------------------
                case 'developer':
                case 'manager':
                case 'admin':
                    $user_data['courseware_types'] = $this->getCoursewareTypes($school_class_types);
                    $user_data['class_type'] = 4;
                    $user_data['class_types'] = $school_class_types;
                    break;

            }

            //
            // Set Session Data for Wordpress and Laravel
            // --------------------------------------------------------------------------

            // Create tgui & set it as a cookie
            $tgui = Hash::make($u->id);

            $freshUsersess = UserSessionData::updateOrCreate(
                ['user_id' => $u->id],
                ['hash' => $tgui, 'user_data' => json_encode($user_data)]
            );

            // Add latest announcement for fresh logins
            if(!$usersess && $user_data['role'] !== 'student') {
                self::addLatestAnnouncement($freshUsersess);
            }

            // setcookie(name, value, expire, path, domain, secure, httponly);
            setcookie('tgui', $tgui, time() + 60 * \Config::get('session.lifetime'), '/', '', \Config::get('session.secure'), true);
            // Laravel session
            // session(['userdata' => $user_data]);
        }

        return $response;
    }

    /**
     * Add latest announcement to UserSessionData
     *
     * @param  mixed $usersess
     * @return void
     */
    private static function addLatestAnnouncement($usersess) {
        $postArgs = ['numberposts' => 1, 'category_name' => 'all'];
        $latest = get_posts($postArgs);
        if(count($latest)) {
            $usersess->announcement = (array) strval($latest[0]->ID);
            $usersess->save();
        }
    }
}
