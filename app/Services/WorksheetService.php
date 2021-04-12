<?php

namespace App\Services;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
// Models
use App\Worksheet;
use App\WorksheetAnswers;
use App\Projects;
use App\ProjectMembers;
use App\TeamMembers;
use App\AssignmentSubmitted;
use App\Grades;

// Service
use App\Services\StudentAssignmentsService;

class WorksheetService {

    /**
     * Get single worksheet data
     *
     * @param  int $id
     * @param  int $project_id
     * @return array
     */
    public static function get($id, $project_id) {
        $g = Worksheet::find($id);
        $gids = $g->groups()->select('worksheet_form_field_groups.group_id', 'count', 'type')->
            leftJoin('worksheet_group_repeats', function ($join) use ($project_id) {
            $join->on('worksheet_group_repeats.group_id', '=', 'worksheet_form_field_groups.group_id')
                ->on('worksheet_group_repeats.worksheet_id', '=', 'worksheet_form_field_groups.worksheet_id')
                ->where('worksheet_group_repeats.project_id', '=', $project_id);
        })->distinct()->orderBy('worksheet_form_field_groups.group_id', 'asc')->get();

        $fld = [];
        $data = [];
        $final = [];
        foreach ($gids as $gid) {
            $groups = $g->groups()
                ->join('worksheet_form_fields', 'worksheet_form_field_groups.form_field_id', 'worksheet_form_fields.id')
                ->leftJoin('worksheet_answers', function ($join) use ($project_id) {
                    $join->on('worksheet_answers.form_field_id', '=', 'worksheet_form_fields.id')
                        ->on('worksheet_answers.worksheet_id', '=', 'worksheet_form_field_groups.worksheet_id')
                        ->where('worksheet_answers.project_id', '=', $project_id);
                })
                ->leftJoin('worksheet_group_repeats', function ($join) use ($project_id) {
                    $join->on('worksheet_group_repeats.worksheet_id', '=', 'worksheet_form_field_groups.worksheet_id')
                        ->on('worksheet_group_repeats.group_id', '=', 'worksheet_form_field_groups.group_id')
                        ->where('worksheet_group_repeats.project_id', '=', $project_id);
                })
                ->leftJoin('project', 'worksheet_answers.project_id', '=', 'project.id')
                ->select('worksheet_form_field_groups.worksheet_id', 'worksheet_form_field_groups.form_field_id', 'worksheet_form_fields.heading', 'worksheet_form_fields.question', 'worksheet_form_fields.description', 'worksheet_form_fields.value', 'worksheet_form_fields.type', 'worksheet_form_fields.display_size', 'worksheet_answers.answer', 'worksheet_form_field_groups.group_id', 'project.last_updated_by')
                ->where('worksheet_form_field_groups.group_id', $gid->group_id)
                ->groupBy('worksheet_form_fields.id')
                ->orderBy('worksheet_form_field_groups.group_id', 'asc')->orderBy('worksheet_form_field_groups.order', 'asc')
                ->get();

            if ($gid->count) {
                $ct = $gid->count;
            } else {
                $ct = 1;
            }
            for ($i = 1; $i <= $ct; $i++) {
                foreach ($groups as $group) {
                    if ($i == 1) {
                        if ($group->form_field_id == 1127) {
                            $fields = [
                                'form_field_id' => $group->form_field_id,
                                'heading' => $group->heading,
                                'question' => $group->question,
                                'description' => $i,
                                'value' => $group->value,
                                'type' => $group->type,
                                'display_size' => $group->display_size,
                                'answer' => $group->answer

                            ];
                        } else {
                            $fields = [
                                'form_field_id' => $group->form_field_id,
                                'heading' => $group->heading,
                                'question' => $group->question,
                                'description' => $group->description,
                                'value' => $group->value,
                                'type' => $group->type,
                                'display_size' => $group->display_size,
                                'answer' => $group->answer

                            ];
                        }
                    } else {
                        $aw = DB::table('worksheet_answers')
                            ->where([
                                ['form_field_id', $group->form_field_id . '_' . $i],
                                ['project_id', $project_id],
                                ['worksheet_id', $id]
                            ])->first();

                        if (isset($aw->answer)) {
                            $aws = $aw->answer;
                        } else {
                            $aws = "";
                        }

                        if ($group->form_field_id == 1127) {
                            $fields = [
                                'form_field_id' => $group->form_field_id . '_' . $i,
                                'heading' => $group->heading,
                                'question' => $group->question,
                                'description' => $i,
                                'value' => $group->value,
                                'type' => $group->type,
                                'display_size' => $group->display_size,
                                'answer' => $aws

                            ];
                        } else {
                            $fields = [
                                'form_field_id' => $group->form_field_id . '_' . $i,
                                'heading' => $group->heading,
                                'question' => $group->question,
                                'description' => $group->description,
                                'value' => $group->value,
                                'type' => $group->type,
                                'display_size' => $group->display_size,
                                'answer' => $aws

                            ];
                        }
                    }
                    array_push($data, $fields);
                }
            }
            $p = Projects::where('id', $project_id)->first();

            if (!$p) { // Troublshoot non-object last_updated_by error
                \Log::error('Non-Object Last Updated By Error: ', [
                    'Project' => $project_id . ', worksheet_id ' . $id,
                    'Path' => request(),
                    'User' => auth()->user()
                ]);
            }

            $fld = [
                'group_id' => $gid->group_id,
                'settings' => [
                    'type' => $gid->type,
                    'count' => $ct,
                    'last_updated_by' => $p->last_updated_by
                ],
                'fields' => $data
            ];

            array_push($final, $fld);
            $data = [];
        }

        return $final;
    }

    /**
     * Send project to team
     *
     * @param  int $project_id
     * @return Repsonse
     */
    public static function sendProjectToTeam($project_id) {
        // Get team id
        $tex = TeamMembers::where('user_id', auth()->user()->id)->select('team_id')->exists();

        if (!$tex) {
            return redirect()->back()->with('error', 'You are not currently in a team.');
        } else {
            $t = TeamMembers::where('user_id', auth()->user()->id)->select('team_id')->first();

            // Enable teams
            Projects::where('id', $project_id)->update(['groups' => 1, 'team_id' => $t->team_id]);

            // Get other team members
            $tm = TeamMembers::where([['team_id', '=', $t->team_id], ['user_id', '!=', auth()->user()->id]])->select('user_id')->get();

            // Add team members to project members
            foreach ($tm as $mem) {
                $p = new ProjectMembers;
                $p->user_id = $mem->user_id;
                $p->project_id = $project_id;
                $p->save();

            }
            $project_name = Projects::find($project_id)->project_name;
            return response()->json(['success' => 'Successfully sent ' . $project_name . ' to team.'], Response::HTTP_OK);
        }
    }

    /**
     * Delete project
     * See ProjectsObserver for additional operations associated with deleting a project
     * @param  int $project_id
     * @return Response
     */
    public static function deleteProject($project_id) {
        Projects::find($project_id)->delete();
        return response()->json(['success' => 'Your project was successfully deleted.'], Response::HTTP_OK);
    }

    /**
     * Change project name
     *
     * @param  int $project_id
     * @param  int $project_name
     * @return Response
     */
    public static function changeProjectName($project_id, $project_name) {
        $p = Projects::where('id', $project_id)->first();
        $p->project_name = $project_name;
        if ($p->save()) {
            return response()->json(['success' => 'Your project was successfully updated.'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to update the project.'], Response::HTTP_OK);
        }
    }

    /**
     * Switch locked project
     *
     * @param  int $old_project_id
     * @param  int $new_project_id
     * @return Response
     */
    public static function switchProjectLock($old_project_id, $new_project_id) {
        $pmUserIDs = [];
        // Unlock old
        if ($old_project_id) {
            $project_old = Projects::find($old_project_id);
            $project_old->unlock();
        }

        // If student is a team member, get other member's user ids of team project
        if (auth()->user()->team &&
            $pmUserIDs = ProjectMembers::where('project_id', $new_project_id)
            ->where('user_id', '!=', auth()->user()->id)
            ->pluck('user_id')->toArray()) {

            // New primary is a team project if there is more than one project member
            foreach ($pmUserIDs as $userID) {
                // Get other member project ids and unlock
                $otherMembersProjectIDs = StudentAssignmentsService::selectProjects($userID)
                    ->pluck('project_id')->toArray();
                foreach ($otherMembersProjectIDs as $pid) {
                    Projects::find($pid)->unlock();
                }
            }
        }

        // Only switch grades if there are no 'other' project member user ids (i.e not switching from
        // personal to team) and previous primary project is not a team project
        if (!count($pmUserIDs) && (isset($project_old) && !$project_old->groups)) {
            AssignmentSubmitted::where('project_id', $old_project_id)
                ->update(['project_id' => $new_project_id]);

            Grades::where('project_id', $old_project_id)
                ->update(['project_id' => $new_project_id]);
        }

        if (Projects::find($new_project_id)->lock()) {
            return response()->json(['success' => 'Successfully switched your primary project.'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to switch project.'], 503);
        }
    }
}
