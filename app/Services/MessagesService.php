<?php

namespace App\Services;

use DB;

// Models
use App\Teams;
use App\TeamMembers;
use App\ClassMembers;
use App\Grades;
use App\User;
use App\Schools;
use App\Messages;
use App\MessagesType;
use App\Classes;

class MessagesService {

    /**
     * Get Messages
     *
     * @param  string $subpage
     * @return array|object
     */
    public static function getSentMessages($subpage = '') {

        // Get Messages
        $messages = [];

        $archive_count = Messages::where('sender_id', auth()->user()->id)
            ->orderby('updated_at', 'desc')
            ->whereNotNull('archive')->get()->count();

        $ms = Messages::where('sender_id', auth()->user()->id)
            ->orderby('updated_at', 'desc');

        if ($subpage == 'archive') {
            $ms = $ms->whereNotNull('archive')->get();
        } else {
            $ms = $ms->whereNull('archive')->get();
        }

        foreach ($ms as $m => $v) {
            switch ($v->type) {
                case 1: // Class
                    $cls = Classes::find($v->recipient_id);
                    $messages[$m]['name'] = $cls ? $cls->class_name : null;
                    //if(!$cls) Messages::where('recipient_id', $v->recipient_id)->delete();
                    break;

                case 2: // Team
                    $team = Teams::find($v->recipient_id);
                    $messages[$m]['name'] = $team ? $team->team_name : null;
                    break;

                case 3: // Student / User
                    $user = User::find($v->recipient_id);
                    $messages[$m]['name'] = $user ? $user->full_name : null;
                    break;

                case 4: // Grades
                    $grade = Grades::select('grades.id', 'grades.type', 'users.first_name', 'users.last_name', 'project.groups', 'users_teams.team_name', 'assignment.assignment_name', 'worksheet_rubric.category_name')
                        ->join('messages', 'messages.id', '=', 'grades.message_id')
                        ->leftJoin('project', 'project.id', '=', 'grades.project_id')
                        ->leftJoin('users_teams_members', 'users_teams_members.user_id', '=', 'grades.user_id')
                        ->leftJoin('users_teams', 'users_teams.id', '=', 'users_teams_members.team_id')
                        ->leftJoin('users', 'users.id', '=', 'grades.user_id')
                        ->leftJoin('assignment', 'assignment.id', '=', 'grades.type_id')
                        ->leftJoin('worksheet_rubric', 'worksheet_rubric.id', '=', 'grades.type_id')
                        ->where('messages.sender_id', auth()->user()->id)
                        ->where('messages.recipient_id', $v->recipient_id)
                        ->first();

                    if($grade) {
                        if ($grade->groups == 1) {
                            if ($grade->type == 1) {
                                $messages[$m]['name'] = $grade->team_name . ' - ' . $grade->category_name;
                            } else {
                                $messages[$m]['name'] = $grade->team_name . ' - ' . $grade->assignment_name;
                            }
                        } else {
                            if ($grade->type == 1) {
                                $messages[$m]['name'] = $grade->first_name . ' ' . $grade->last_name . ' - ' . $grade->category_name;
                            } else {
                                $messages[$m]['name'] = $grade->first_name . ' ' . $grade->last_name . ' - ' . $grade->assignment_name;
                            }

                        }
                    }
                    break;
            }
            if (isset($messages[$m]['name'])) {
                $messages[$m]['id'] = $v->id;
                $messages[$m]['content'] = $v->content;
                $messages[$m]['type'] = MessagesType::find($v->type)->name;
                $messages[$m]['date'] = $v->updated_at->format('F j, Y');
                $messages[$m]['updated'] = $v->updated_at->diffForHumans();
            } else {
                unset($messages[$m]);
            }
        }
        $messages = array_values($messages);

        // Get User List
        $user_role = auth()->user()->role->slug;

        if ($user_role == 'teacher' || $user_role == 'assistant-teacher') {

            $s = auth()->user()->school;
            $user_list = $s->users()
                ->join('users_roles', 'users_roles.user_id', 'users.id')
                ->leftJoin('class_members', 'class_members.user_id', 'users.id')
                ->select('users.id', 'users.first_name', 'users.last_name')
                ->where('users_roles.role_id', 4)
                ->whereIn('class_members.class_id', auth()->user()->getclassID())
                ->groupBy('users.id')
                ->orderBy('users_roles.role_id', 'asc')
                ->orderBy('users.last_name', 'asc')
                ->distinct()->get();

            // Get Class List
            $class_list = auth()->user()->classes()->get();

            // Get Team List
            $team_list = collect([]);
            foreach ($class_list as $v) {
                if (Teams::where('class_id', $v->id)->exists()) {
                    $team_list = $team_list->concat(
                        Teams::where('class_id', $v->id)->select('id', 'team_name')->GroupBy('id')->get()
                    );
                }
            }
        }

        $data = (object) [];
        $data->messages = $messages;
        $data->userList = $user_list;
        $data->teamList = $team_list;
        $data->classList = $class_list;
        $data->archiveCount = $archive_count;
        return $data;
    }

    /**
     * Student Messages
     *
     * @return object
     */
    public static function getStudentMessages() {
        $messages = (object) [];
        // Get Class, user, and team Message
        if (auth()->user()->role->slug == 'student' && auth()->user()->getClassType() != 99) {

            $messages->class = Messages::where('recipient_id', auth()->user()->getclassID())
                ->where('type', 1)
                ->whereNull('messages.archive')
                ->orderBy('messages.updated_at', 'desc')
                ->select('messages.id', 'content', 'messages.updated_at')
                ->take(5)
                ->get()->toArray();

            // Student Messages
            $messages->user = Messages::where('recipient_id', auth()->user()->id)
                ->where('type', 3)
                ->whereNull('messages.archive')
                ->orderBy('messages.updated_at', 'desc')
                ->select('messages.id', 'content', 'messages.updated_at')
                ->take(5)
                ->get()->toArray();

            // Team Messages
            if (auth()->user()->team) {
                $messages->team = Messages::where('recipient_id', auth()->user()->team->id)
                    ->where('type', 2)
                    ->whereNull('messages.archive')
                    ->orderBy('messages.updated_at', 'desc')
                    ->select('messages.id', 'content', 'messages.updated_at')
                    ->take(5)
                    ->get()->toArray();
            }

        }

        $messages->class ??= [];
        $messages->user ??= [];
        $messages->team ??= [];
        return $messages;
    }
}
