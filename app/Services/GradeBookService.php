<?php

namespace App\Services;

use DB;

// Models
use App\Teams;
use App\TeamMembers;
use App\ClassMembers;
use App\RubricSettings as rs;
use App\AssignmentClasses as ac;
use App\AssignmentSubmitted;
use App\Grades;
use App\User;
use App\Schools;
use App\WorksheetRubric;
use App\ProjectMembers;
use App\Projects;

class GradeBookService {

    /**
     * Get all data
     *
     * @param  int $teacherid
     * @return object
     */
    public static function getAllData($teacherid = 0) {
        $teacherid ?? auth()->user()->getTeacherID();
        $cm = ClassMembersService::get($teacherid);

        if (auth()->user()->role->slug == 'assistant-teacher') {
            $class_members_data = ClassMembers::where([['class_id', $cm[0]['id']], ['role_id', 3]])
                ->select('user_id')
                ->first();
            $teacherid = $class_members_data->user_id;
        }

        $classes = [];
        $class_list = [];

        foreach ($cm as $c) {

            // Get rubric categories
            $rubric = rs::where([['teacher_id', $teacherid], ['rubric_settings.active', 1]])
                ->join('rubric_categories', 'rubric_categories.id', 'rubric_settings.category_id')
                ->select('rubric_categories.id', 'rubric_categories.category_name as name', 'rubric_categories.category_value as value')
                ->get();

            // Get teams count
            $team_count = Teams::where('class_id', $c->id)->count();

            // Get students count
            $u = User::find($teacherid);
            $s = $u->school;
            $user_count = $s->users()
                ->join('class_members', 'class_members.user_id', 'users.id')
                ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'class_members.class_id', 'class_members.role_id')
                ->where('class_members.role_id', 4)
                ->where('class_members.class_id', $c->id)->count();

            $teamList = [];
            $userList = [];
            $class_list = [
                'id' => $c->id,
                'class_name' => $c->class_name,
                'class_type' => $c->class_type,
                'categories' => $rubric,
                'team_count' => $team_count,
                'user_count' => $user_count,
                'teamList' => $teamList,
                'userList' => $userList
            ];

            array_push($classes, $class_list);
        }

        foreach ($classes as $i => $v) {
            // Get Users
            $u = User::find($teacherid);
            $s = $u->school;
            $userList = $s->users()
                ->join('class_members', 'class_members.user_id', 'users.id')
                ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'class_members.class_id', 'class_members.role_id')
                ->where('class_members.role_id', 4)
                ->where('class_members.class_id', $classes[$i]['id'])->get();

            foreach ($userList as $u => $v) {

                $projects = Projects::findByUser($userList[$u]->id)->orderBy('project.updated_at', 'desc')->get();

                // Get team information
                $team_name = '';
                $team_members = [];
                if ($project_team = $projects->firstWhere('team_id')) {
                    $team_members = TeamMembers::select('users.id', 'users.name', 'users.first_name', 'users.last_name')
                        ->join('users', 'users.id', '=', 'users_teams_members.user_id')
                        ->where('users_teams_members.team_id', $project_team->team_id)
                        ->get()->map(function ($member) {
                        return $member->last_name . ', ' . $member->first_name;
                    });
                    $team_name = Teams::find($project_team->team_id);
                    if($team_name)
                        $team_name = $team_name->team_name;
                }

                $projectList = [];
                foreach ($projects as $p) {
                    $pid = $p->id;
                    $worksheet_data = WorksheetRubric::where('teacher_id', $teacherid)
                        ->leftJoin('grades', function ($join) use ($pid) {
                            $join->on('worksheet_rubric.id', '=', 'grades.type_id')
                                ->where('grades.project_id', '=', $pid);
                        })
                        ->leftJoin('assignment_submitted', function ($join) use ($pid) {
                            $join->on('assignment_submitted.type_id', '=', 'worksheet_rubric.category_id')
                                ->where('assignment_submitted.project_id', '=', $pid);
                        })
                        ->leftJoin('worksheet_answers', function ($join) use ($pid) {
                            $join->on('worksheet_answers.worksheet_id', '=', 'worksheet_rubric.category_id')
                                ->where('worksheet_answers.project_id', '=', $pid);
                        })
                        ->leftJoin('worksheet', function ($join) {
                            $join->on('worksheet_rubric.category_id', '=', 'worksheet.id');
                        })
                        ->leftJoin('messages', function ($join) {
                            $join->on('grades.message_id', '=', 'messages.id');
                        })
                        ->leftJoin('messages as m2', function ($join) {
                            $join->on('assignment_submitted.message_id', '=', 'm2.id');
                        })
                        ->select(
                            'worksheet_rubric.id',
                            'worksheet_rubric.category_id',
                            'worksheet.title',
                            'worksheet_rubric.active',
                            'worksheet_rubric.category_value as points',
                            'messages.content as message',
                            'm2.content as comments',
                            'grades.points as grade',
                            'grades.id as grade_id',
                            'grades.updated_at',
                            'assignment_submitted.status',
                            DB::raw('count(worksheet_answers.worksheet_id) as has_answers'))
                        ->where('worksheet_rubric.active', 1)
                        ->orderBy('worksheet.order', 'asc')
                        ->groupBy('worksheet_rubric.id')
                        ->get()->each(function ($item) use ($p, $team_name) {
                            $item->locked = $p->locked;
                            $item->team_id = $p->team_id;
                            $item->team_name = $team_name;
                            $item->project_name = $p->project_name;
                            $item->comments = stripslashes($item->comments);
                            // Demo
                            if(auth()->user()->demo && !in_array($item->category_id, config('app.demo.worksheets'))) {
                                $item->disabled = 1;
                            }
                        });


                    $projectItem = [
                        'project_id' => $p->id,
                        'project_name' => $p->project_name,
                        'members' => $p->team_id ? $team_members : [],
                        'team_id' => $p->team_id,
                        'updated_at' => $p->updated_at,
                        'locked' => $p->locked,
                        'worksheets' => $worksheet_data
                    ];

                    array_push($projectList, $projectItem);
                }
                $userList[$u]['activity'] = $projectList;

                // Get user custom assignments
                $uid = $userList[$u]->id;
                foreach ($rubric as $cat) {

                    $custom = ac::where([
                        ['class_id', $classes[$i]['id']],
                        ['assignment.category_id', $cat->id]
                    ])
                        ->join('assignment', 'assignment.id', '=', 'assignment_classes.assignment_id')
                        ->leftJoin('grades', function ($join) use ($uid) {
                            $join->on('grades.type_id', '=', 'assignment_classes.assignment_id')
                                ->where('grades.type', '=', 2)
                                ->where('grades.user_id', '=', $uid);
                        })
                        ->leftJoin('assignment_submitted', function ($join) use ($uid) {
                            $join->on('assignment_submitted.type_id', '=', 'assignment_classes.assignment_id')
                                ->where('assignment_submitted.user_id', '=', $uid)
                                ->where('assignment_submitted.type', '=', 2);
                        })
                        ->leftJoin('messages', function ($join) {
                            $join->on('grades.message_id', '=', 'messages.id');
                        })
                        ->leftJoin('messages as m2', function ($join) {
                            $join->on('assignment_submitted.message_id', '=', 'm2.id');
                        })
                        ->where('assignment.status', 1)
                        ->select(
                            'assignment.id',
                            'assignment_name as label',
                            'assignment.points',
                            'assignment.type',
                            'assignment_submitted.file_location as student_file_location',
                            'assignment.file_location as teacher_file_location',
                            'assignment.file_screenshot',
                            'assignment.file_name',
                            'grades.points as grade',
                            'grades.id as grade_id',
                            'grades.updated_at',
                            'messages.content as message',
                            'm2.content as comments',
                            'assignment_submitted.status')
                        ->groupBy('assignment.id')
                        ->get()->each(function ($item) {
                        $item->comments = stripslashes($item->comments);
                    });

                    $userList[$u][strtolower($cat->name)] = $custom;
                }
            }
            // Append locked team project to userList projects if it exists:
            // if (count($teamList)) {
            //     foreach ($userList as $user) {
            //         foreach ($teamList as $team) {
            //             // Search array for locked team project
            //             if (false !== $tdix = array_search(1, array_column(($team['activity']), 'locked'))) {
            //                 // Search array for user's id in locked team project's member list
            //                 // If found, add additional team centric properties
            //                 if ($team['members']->where('id', $user['id'])->count()) {
            //                     $team['activity'][$tdix]['team'] = $team['team_name'];
            //                     $team['activity'][$tdix]['members'] = $team['members']->map(function ($member) {
            //                         return $member->last_name . ', ' . $member->first_name;
            //                     });
            //                     // Only way I (Kristi) couldn't get Laravel 'Indirect modification of overloaded element'
            //                     // error is via collect() & push:
            //                     $user['activity'] = collect($user['activity'])->push($team['activity'][$tdix]);
            //                 }
            //                 break; // Stop searching once locked team project is found
            //             }
            //         }
            //     };
            // }
            $classes[$i]['userList'] = $userList;
        }
        return $classes;
    }

    //
    // Teacher - Assignments sent to teacher / pending
    // --------------------------------------------------------------------------
    public static function getTeacherPending() {
        if(!in_array(auth()->user()->role->slug, ['teacher', 'assistant-teacher'])) {
            return [];
        }

        $cl = ClassMembers::where('user_id', auth()->user()->id)
            ->whereIn('role_id', [3, 7])
            ->select('class_id')
            ->get();

        $gc = Grades::select('type_id')
            ->where('type', 2)
            ->whereNotNull('points')
            ->get();

        $custom_assignments = AssignmentSubmitted::select(
            'assignment_submitted.id as id',
            'assignment.category_id as category_id',
            'assignment.id as assignment_id',
            'assignment.points',
            'assignment.type',
            'assignment.file_location as teacher_file_location',
            'assignment_submitted.file_location as student_file_location',
            'assignment.file_screenshot',
            'assignment_submitted.status',
            'assignment.assignment_name',
            'g_custom.points as grade',
            'g_custom.id as grade_id',
            'users.id as user_id',
            'users.first_name',
            'users.last_name',
            'mc.content as comments',
            'mg.content as message',
            'assignment_submitted.updated_at'
        )
            ->join('class_members', 'class_members.user_id', 'assignment_submitted.user_id')
            ->join('assignment', 'assignment.id', 'assignment_submitted.type_id')
            ->join('users', 'assignment_submitted.user_id', 'users.id')
            ->leftJoin('messages as mc', 'mc.id', 'assignment_submitted.message_id')
            ->leftJoin('grades as g_custom', function ($join) {
                $join->on('assignment_submitted.type_id', '=', 'g_custom.type_id')
                    ->where('g_custom.type', 2);
            })
            ->leftJoin('messages as mg', 'mg.id', 'g_custom.message_id')
            ->whereIn('class_members.class_id', $cl)
            ->whereNotIn('assignment_submitted.type_id', $gc)
            ->where('assignment_submitted.type', 2)
            ->orderBy('assignment_submitted.updated_at', 'desc')
            ->get();

        $worksheet_assignments = AssignmentSubmitted::select(
            'assignment_submitted.id as id',
            'assignment_submitted.type_id as category_id',
            'assignment_submitted.project_id',
            'assignment_submitted.status',
            'class.class_name',
            'wr.id as assignment_id',
            'wr.category_value as points',
            'worksheet.title as assignment_name',
            'g_ws.points as grade',
            'g_ws.id as grade_id',
            'g_ws.points as grade_point',
            'users.first_name',
            'users.id as user_id',
            'utm.team_id',
            'users.last_name',
            'ut.team_name',
            'p.groups',
            'p.project_name',
            'p.locked',
            'mc.content as comments',
            'mg.content as message',
            'assignment_submitted.updated_at'
        )
            ->join('class_members', 'class_members.user_id', 'assignment_submitted.user_id')
            ->join('users', 'assignment_submitted.user_id', 'users.id')
            ->leftJoin('class', 'class.id', 'class_members.class_id')
            ->leftJoin('messages as mc', 'mc.id', 'assignment_submitted.message_id')
            ->leftJoin('project as p', 'p.id', 'assignment_submitted.project_id')
            ->leftJoin('users_teams_members as utm', 'utm.user_id', 'assignment_submitted.user_id')
            ->leftJoin('users_teams as ut', 'utm.team_id', 'ut.id')
            ->leftJoin('worksheet_rubric as wr', function ($join) {
                $join->on('assignment_submitted.type_id', '=', 'wr.category_id')
                    ->where('teacher_id', auth()->user()->getTeacherID());
            })
            ->leftJoin('worksheet', function ($join) {
                $join->on('wr.category_id', '=', 'worksheet.id');
            })
            ->leftJoin('grades as g_ws', function ($join) {
                $join->on('wr.id', '=', 'g_ws.type_id')->on('assignment_submitted.project_id', '=', 'g_ws.project_id')
                    ->where('g_ws.type', 1);
            })
            ->leftJoin('messages as mg', 'mg.id', 'g_ws.message_id')
            ->whereIn('class_members.class_id', $cl)
            ->where([['g_ws.points', null], ['assignment_submitted.type', 1]])
            ->orderBy('assignment_submitted.updated_at', 'desc')
            ->get();

        $merged = $worksheet_assignments->merge($custom_assignments)->each(function ($item) {
            $item->updated = $item->updated_at->diffForHumans();
            $item->updated_long = $item->updated_at->format('F j, Y - g:i a');
            $item->comments = stripslashes($item->comments);
        });
        $data = $merged->sortByDesc('updated_at')->values();

        return $data ?? [];
    }
}
