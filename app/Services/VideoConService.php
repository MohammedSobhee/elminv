<?php

namespace App\Services;

use App\Schools;
use App\Classes;
use App\Teams;
use App\VideoCon;

use App\Services\ZoomService;

use App\Events\VideoConDeleteEvent;
use App\Events\VideoConEvent;

class VideoConService {

    /**
     * Video Conferencing Recipient List
     *
     * @return object - channels
     */
    public static function channelList() {
        $user_role = auth()->user()->role->slug;
        $class_id = auth()->user()->getClassID();
        $class_id = (array) $class_id;

        $vidconlist = [];

        $settings = self::settings();

        if ($settings['videocon_google'] || $settings['videocon_zoom']) {
            // Classes
            if ($user_role != 'student') { // Student
                $classlist = Classes::where('school_id', auth()->user()->school_id)
                    ->join('class_members', 'class_members.class_id', 'class.id')
                    ->where('class_members.user_id', auth()->user()->id)
                    ->select('class.id as vtype_id', 'class.class_name as name')
                    ->get();

                $classlist->each(function ($item) {
                    $item->vtype = 1;
                    $item->display_name = 'Class: ' . $item->name;
                });
                $vidconlist = $classlist->toArray();
            }

            // Teams
            $teamlist = Teams::whereIn('users_teams.class_id', $class_id); // Teachers
            if ($user_role == 'student') { // Students
                $teamlist = $teamlist->join('users_teams_members as utm', function ($join) {
                    $join->on('utm.team_id', '=', 'users_teams.id')
                        ->where('utm.user_id', auth()->user()->id);
                });
            }
            $teamlist = $teamlist->select('users_teams.id as vtype_id', 'team_name as name')->get();
            $teamlist->each(function ($item) {
                $item->vtype = 2;
                $item->display_name = 'Team: ' . $item->name;
            });
            $teamlist = $teamlist->toArray();
            $vidconlist = array_merge($vidconlist, $teamlist);

            // Members
            if ($user_role != 'student' || $settings['videocon_student']) {
                $s = auth()->user()->school;
                $userlist = $s->users()
                    ->join('users_roles', 'users_roles.user_id', 'users.id')
                    ->leftJoin('class_members', 'class_members.user_id', 'users.id')
                    ->whereIn('users_roles.role_id', [3, 4, 7])
                    ->whereIn('class_members.class_id', $class_id)
                    ->groupBy('users.id')
                    ->orderBy('users.first_name', 'asc')
                    ->select('users.id as vtype_id', 'users.name', 'users.nickname', 'users_roles.role_id')
                    ->distinct()->get();
                $userlist->each(function ($item) {
                    $item->vtype = 3;
                    $item->display_name = ($item->role_id == 4 ? 'Member: ' : 'Teacher: ') . ($item->role_id == 3 || $item->role_id == 7 ? ($item->nickname ?: $item->name): $item->name);
                });
                $userlist = $userlist->sortByDesc('display_name');
                $userlist = $userlist->toArray();
                $vidconlist = array_merge($vidconlist, $userlist);
            }

        }

        $vidconlist = collect($vidconlist)->filter(function ($item) {
            return $item['vtype_id'] != auth()->user()->id;
        })->values();

        return $vidconlist;
    }

    /**
     * Get current video conferences
     *
     * @return object
     */
    public static function fetchConferences() {
        $class_id = auth()->user()->getClassID();
        $class_id = (array) $class_id;

        $team_id = auth()->user()->getTeamIDs();

        $videocons = VideoCon::with('user:id,name,nickname')
        //->where('updated_at', '>=', Carbon::now()->subMinutes(60)->toDateTimeString())
            ->where(function ($query) use ($class_id, $team_id) {
                $query->whereIn('vtype_id', $class_id)
                    ->OrWhereIn('vtype_id', $team_id)
                    ->OrWhere('vtype_id', auth()->user()->id)
                    ->OrWhere('user_id', auth()->user()->id);
            })
            ->orderBy('updated_at', 'DESC')
            ->get();
        return $videocons;
    }

    /**
     * Get list of available video conference services
     * Filter out zoom for students
     *
     * @return array
     */
    public static function getServices() {
        $settings = VideoConService::settings();
        $services = collect(\Config::get('constants.videocon_services'));
        $services = $services->filter(function ($value, $key) use ($settings) {
            if (!(auth()->user()->role->slug === 'student' && $value['setting'] === 'videocon_zoom')) {
                return isset($settings[$value['setting']]) && $settings[$value['setting']] == 1;
            }
        });
        return $services->values();
    }

    /**
     * Search for meetings
     *
     * @param  Request $request
     * @return object|int
     */
    public static function searchMeetings($request) {
        $videocon = VideoCon::where([
            //'service' => $request->service,
            'vtype' => $request->vtype,
            'vtype_id' => $request->vtype_id
        ])->latest('updated_at')->first();
        if ($videocon) {
            return $videocon;
        }
    }

    /**
     * Create or update meeting
     *
     * @param  Request $request
     * @return Response
     */
    public static function createOrUpdateMeeting($request) {
        $password = $request->password ?: NULL;
        $link = $request->link;
        $meeting_id = NULL;
        $error = '';
        $videocon = NULL;
        if ($request->id) {
            $videocon = auth()->user()->videocons()->find($request->id);
        }

        if ($request->service == 'zoom') {
            $zoom = new ZoomService;
            $zoomLabel = $request->label ?: $request->vtype_name;
            //$existingZoom = self::searchMeetings($request);

            // Update Zoom meeting
            if ($videocon && $videocon->meeting_id) {
                if (!$zoom = $zoom->updateMeeting($videocon->meeting_id, $zoomLabel, $password)) {
                    $error = 'Failed to update meeting.';
                } else {
                    $meeting_id = $videocon->meeting_id;
                }
                // Create Zoom meeting
            } else {
                if (!$zoom = $zoom->createMeeting($zoomLabel, $password)) {
                    $error = 'Failed to create meeting.';
                } else {
                    $meeting_id = $zoom['id'];
                    $password = $zoom['password'] ?: $zoom['h323_password'];
                    $link = $zoom['join_url'];
                }
            }
        }

        // Save videocon model
        if (!$error) {
            if ($videocon) {
                $videocon->service = $request->service;
                $videocon->vtype = $request->vtype;
                $videocon->vtype_id = $request->vtype_id;
                $videocon->link = $link;
                $videocon->label = $request->label;
                $videocon->password = $password;
                $videocon->meeting_id = $meeting_id;
                if (!$videocon->save()) {
                    $error = 'Error updating video conference.';
                }
            } else {
                $videocon = auth()->user()->videocons()->create([
                    'service' => $request->service,
                    'vtype' => $request->vtype,
                    'vtype_id' => $request->vtype_id,
                    'link' => $link,
                    'label' => $request->label,
                    'password' => $password,
                    'meeting_id' => $meeting_id
                ]);
            }
            if (!$videocon) {
                $error = 'Something went wrong.';
            }
        }

        if ($error) {
            \Log::debug('error creating/updating meeting for user_id  ' . auth()->user()->id . ': ' . $error);
            return response()->json(['error' => $error], 503);
        } else {
            $videocon->touch(); // Update timestamp
            broadcast(new VideoConEvent($videocon->load('user'), $request->vtype, $request->vtype_id));
            return response()->json(['success' => 'Successfully sent alert.'], 200);
        }
    }

    /**
     * Delete Meeting
     *
     * @param  Request $request
     * @return Response
     */
    public static function deleteMeeting($request) {
        $videocon = VideoCon::find($request->id);
        if ($videocon->service === 'zoom') {
            $zoom = new ZoomService;
            $zoom->deleteMeeting($videocon->meeting_id);
        }
        broadcast(new VideoConDeleteEvent($videocon->id, $videocon->vtype, $videocon->vtype_id))->toOthers();
        if ($videocon->delete()) {
            return response()->json(['success' => 'Successfully deleted video conference'], 200);
        }
    }

    /**
     * Get Video Conferencing Settings
     *
     * @return object
     */
    public static function settings() {
        $settings = ['videocon_google', 'videocon_zoom', 'videocon_student'];
        return CourseSettingsService::get($settings);
    }
}
