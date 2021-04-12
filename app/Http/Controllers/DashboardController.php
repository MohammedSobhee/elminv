<?php

namespace App\Http\Controllers;

//use

use Cookie;
use App\Http\Requests;

// Model
use App\Schools;
use App\Classes;
use App\Messages;

// Service
use App\Services\ChatService;
use App\Services\VideoConService;
use App\Services\GradeBookService;
use App\Services\MessagesService;
use App\Services\StudentAssignmentsService;
use App\Services\CourseSettingsService;

class DashboardController extends Controller {

    /**
     * Show Dashboard
     *
     * @return resource - View
     */
    public function show() {
        if (auth()->user()->hasAdminPermission()) {
            return redirect('/eduadmin');
        }

        // School admin member edit list
        if (auth()->user()->role->slug === 'school-admin') {
            $s = auth()->user()->school;
            $school_users = $s->users()->with('school', 'role', 'classes', 'permissions')->withoutGlobalScopes()->get();

            // Demo School filter
            if(auth()->user()->demo) {
                $school_users = $school_users->where('id', '!=', config('app.demo.admin')); // Main Demo school admin
            }

            $school_users = $school_users->sortBy('last_name')->sortByDesc('role.name')->values();
        }

        // Teacher counts
        if(in_array(auth()->user()->role->slug, ['teacher', 'assistant-teacher'])) {
            // Check if teacher has sent any messages - Suggest adding a welcome message for fresh teacher
            $teacher_message_count = Messages::where('sender_id', auth()->user()->id)->count();

            // Check if teacher has any classes for use in notifications - Suggest adding a class
            $teacher_class_count = auth()->user()->classes()->count();
        }

        // Chat / Wideo
        if(in_array(auth()->user()->role->slug, ['student', 'teacher', 'assistant-teacher'])) {
            // Get list of chat channels
            $chatlist = ChatService::channelList();
            // Load Chat messages immediately if only 1 chat channel available
            if (count($chatlist) === 1) {
                $chat_messages = ChatService::fetchMessages($chatlist[0]['ctype'], $chatlist[0]['ctype_id']);
            }
        }

        // Return view
        return view('dashboard.dashboard', [
            'user_messages' => MessagesService::getStudentMessages()->user,
            'team_messages' => MessagesService::getStudentMessages()->team,
            'class_messages' => MessagesService::getStudentMessages()->class,
            'teacher_pending' => GradeBookService::getTeacherPending(),
            'school_code' => $s->school_code ?? 0,
            'teacher_code' => $s->teacher_code ?? 0,
            'school_users' => $school_users ?? [],
            'worksheets' => StudentAssignmentsService::getRecentWorksheets(),
            'assignments' => StudentAssignmentsService::getRecentCustom(),
            'insert' => StudentAssignmentsService::getAssignmentsInsert(),
            'chatlist' => $chatlist ?? [],
            'chat_messages' => $chat_messages ?? [],
            'team_id' => auth()->user()->getTeamID(),
            'services_available' => VideoConService::getServices(),
            'participants_list' => VideoConService::channelList() ?? [],
            'conferences' => VideoConService::fetchConferences() ?? [],
            // For fresh teacher dashboard notice about enabling chat / video cons:
            'chat_settings' => ChatService::settings(),
            'teacher_settings' => CourseSettingsService::get(),
            'videocon_settings' => VideoConService::settings(),
            'teacher_message_count' => $teacher_message_count ?? 0,
            'teacher_class_count' => $teacher_class_count ?? 0,
            'agreement' => auth()->user()->demo && !auth()->user()->agreement
        ]);
    }
}
