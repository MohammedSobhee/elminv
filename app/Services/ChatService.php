<?php

namespace App\Services;

use App\Schools;
use App\Classes;
use App\Teams;
use App\Chat;

class ChatService {

    /**
     * Fetch all messages
     *
     * @param  string $ctype
     * @param  int $ctype_id
     * @return object - chat messages
     */
    public static function fetchMessages($ctype, $ctype_id) {
        $chats = Chat::with('user')->where([
            'ctype' => $ctype,
            'ctype_id' => $ctype_id
        ])->limit(100)->get();
        return $chats;
    }

    /**
     * Chat list of channels
     *
     * @return object - channels
     */
    public static function channelList() {
        $user_role = auth()->user()->role->slug;
        $class_id = auth()->user()->getClassID();
        $class_id = (array) $class_id;

        $chatlist = [];

        $settings = self::settings();

        // Classes
        if (isset($settings['chat_class']) && $settings['chat_class']) {
            if ($user_role != 'student') { // Student
                $classlist = Classes::where('school_id', auth()->user()->school_id)
                    ->join('class_members', 'class_members.class_id', 'class.id')
                    ->where('class_members.user_id', auth()->user()->id)
                    ->select('class.id as ctype_id', 'class.class_name as name')
                    ->get();
            } else { // Teacher
                $classlist = Classes::whereIn('id', $class_id)
                    ->select('id as ctype_id', 'class_name as name')
                    ->get();
            }
            $classlist->each(function ($item) {
                $item->ctype = 1;
                $item->display_name = 'Class: ' . $item->name;
            });
            $chatlist = $classlist->toArray();
        }

        // Teams
        if (isset($settings['chat_team']) && $settings['chat_team']) {
            $teamlist = Teams::whereIn('users_teams.class_id', $class_id); // Teachers
            if ($user_role == 'student') { // Students
                $teamlist = $teamlist->join('users_teams_members as utm', function ($join) {
                    $join->on('utm.team_id', '=', 'users_teams.id')
                        ->where('utm.user_id', auth()->user()->id);
                });
            }
            $teamlist = $teamlist->select('users_teams.id as ctype_id', 'team_name as name')->get();
            $teamlist->each(function ($item) {
                $item->ctype = 2;
                $item->display_name = 'Team: ' . $item->name;
            });
            $teamlist = $teamlist->toArray();
            $chatlist = array_merge($chatlist, $teamlist);
        }

        // Members
        if (isset($settings['chat_private']) && $settings['chat_private']) {
            $s = auth()->user()->school;
            $userlist = $s->users()
                ->join('users_roles', 'users_roles.user_id', 'users.id')
                ->leftJoin('class_members', 'class_members.user_id', 'users.id')
                ->where('users_roles.role_id', 4)
                ->whereIn('class_members.class_id', $class_id)
                ->groupBy('users.id')
                ->orderBy('users.first_name', 'asc')
                ->select('users.id as ctype_id', 'users.name')
                ->distinct()->get();
            $userlist->each(function ($item) {
                $item->ctype = 3;
                $item->display_name = 'Member: ' . $item->name;
            });
            $userlist = $userlist->toArray();
            $chatlist = array_merge($chatlist, $userlist);
        }
        return $chatlist;
    }

    /**
     * Get Chat Settings
     *
     * @return object
     */
    public static function settings() {
        $settings = ['chat_class', 'chat_team', 'chat_private'];
        return CourseSettingsService::get($settings);
    }
}
