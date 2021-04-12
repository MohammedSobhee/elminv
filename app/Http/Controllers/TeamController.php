<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Classes;
use App\Teams;
use App\TeamMembers;
use App\UserSessionData;
use App\ProjectMembers;
use App\Projects;

class TeamController extends Controller {

    /**
     * Get available users
     *
     * @param  int $class_id
     * @return object
     */
    private static function availableUsers($class_id) {
        // $c = ClassMembers::find($class_id);
        $users = DB::table('class_members')
            ->where([['role_id', 4], ['class_id', $class_id]])
            ->join('users', 'class_members.user_id', 'users.id')
            ->where('role_id', 4)
            ->whereRaw('class_members.user_id not in(select users_teams_members.user_id from users_teams_members)')
            ->select('users.id', 'users.first_name', 'users.last_name')
            ->orderBy('users.last_name', 'asc')
            ->get();
        return $users;
    }

    /**
     * Blade  for /edit/team
     *
     * @return resource - View
     */
    public function show($class_id = 0, $team_id = 0) {
        $classes = auth()->user()->classes->load('teams');

        $classes->each(function ($c) {
            $c->teams->load('members')->each(function ($t) use ($c) {
                $t->project_count = Projects::where('team_id', $t->id)->count();
                $t->available = static::availableUsers($c->id);
            });
        });

        return view('school_management.edit_team', [
            'classes' => $classes,
            'class_id' => $class_id,
            'team_id' => $team_id
        ]);
    }

    /**
     * Create Team
     *
     * @param  Request $request
     * @return Redirect
     */
    public function create(Request $request) {
        $t = new Teams();

        $t->team_name = $request->input('team_name');
        $t->class_id = $request->input('class_id');
        if ($t->save()) {
            //return redirect()->back()->with($t);
            return redirect()->to('/edit/team/' . $t->class_id)->with('success', 'Successfully created the team: ' . $t->team_name);
        } else {
            return redirect()->back()->with('error', 'failed to create team');
        }
    }


    /**
     * Delete team
     * See TeamsObserver for additional operations associated with deleting a team
     * @param  Request $request
     * @return void
     */
    public function delete(Request $request) {
        if(Teams::find($request->team_id)->delete()) {
            return response()->json(['success' => 'Successfully deleted team and associated projects'], 200);
        } else {
            return response()->json(['error' => 'Failed to delete team'], 503);
        }
    }

    /**
     * Add / Remove team members
     *
     * @param  Request $request
     * @return Response
     */
    public function updateTeamMembers(Request $request) {
        $response = '';
        switch ($request->type) {

            case 'add':
                $c = new TeamMembers();
                $c->user_id = $request->user_id;
                $c->team_id = $request->team_id;
                $c->class_id = $request->class_id;
                if ($c->save()) {
                    $response = 'added to team.';
                }
                break;

            case 'remove':
                if (TeamMembers::where([['user_id', $request->user_id], ['team_id', $request->team_id]])->delete()) {
                    $response = 'removed user from team.';
                }
                break;
        }

        if($response) {
            // Force logout to refresh user session data
            UserSessionData::where('user_id', $request->user_id)->update(['user_data' => null]);
            // Add/Remove member to new team's projects if they exist
            self::updateTeamProjectMembers($request->user_id, $request->team_id, $request->type);
            return response()->json(['success' => 'Successfully '. $response], 200);
        } else {
            return response()->json(['error' => 'Failed to save record'], 503);
        }
    }


    /**
     * Update team's project members
     *
     * @param  int $user_id
     * @param  int $team_id
     * @param  string $type
     * @return void
     */
    private static function updateTeamProjectMembers($user_id, $team_id, $type) {
        $pids = Projects::where('team_id', $team_id)->pluck('id')->values();
        if (ProjectMembers::whereIn('project_id', $pids)->count()) {
            foreach ($pids as $pid) {
                if($type == 'add')
                    ProjectMembers::firstOrCreate(['user_id' => $user_id, 'project_id' => $pid]);
                else if($type == 'remove')
                    ProjectMembers::where(['user_id' => $user_id, 'project_id' => $pid])->delete();

            }
        }
    }

    /**
     * Update Team name
     *
     * @param  Request $request
     * @return Response
     */
    public function updateTeamName(Request $request) {
        $t = Teams::find($request->input('team_id'));
        $t->team_name = $request->input('team_name');

        if ($t->save()) {
            return response()->json(['success' => 'Successfully updated team'], 200);
        } else {
            return response()->json(['error' => 'Failed to update team'], 503);
        }
    }
}
