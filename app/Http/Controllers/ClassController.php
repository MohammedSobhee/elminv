<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Validation
use App\Http\Requests\CreateClassPost;

// Models
use App\Classes;
use App\User;
use App\Schools;
use App\ClassMembers;
use App\TeamMembers;

class ClassController extends Controller {

    /**
     * Show bare bones create class page (unused)
     *
     * @param  mixed $request
     * @return void
     */
    public function showCreate(Request $request) {
        return view('school_management.create_class');
    }

    /**
     * Show Edit Class
     *
     * @param  int $id - Linking directly to class
     * @param  int $user_id - Used for SchoolController@listMembers user links
     * @return resource - Edit class view
     */
    public function showEdit($id = 0, $user_id = 0) {
        $assigned = [];
        $unassigned = [];

        $s = auth()->user()->school;
        $classes = auth()->user()->classes()->with('type')->orderBy('class_name');

        //
        // Class list index
        // --------------------------------------------------------------------------
        if (!$id) {
            if ($classes->get()->count() == 1) {
                // Only one class... redirect to class id
                $classes = $classes->first();
                return redirect()->to('/edit/class/' . $classes->id);
            } else {
                $classes = $classes->with('users')->get();
                $classes->append('class_type_name');
            }
            // Determine if classes have any members (requested unused by Clay)
            // $classes_has_members = $classes->reduce(function ($total, $item) {
            //     return $total + $item->users->count() - 1;
            // }, 0);

            //
            // Class selected
            // --------------------------------------------------------------------------
        } else {
            // Class name options in <select>
            $classes = $classes->get();
            $classes->append('class_type_name');

            // Check if current user can edit class
            if (!auth()->user()->canEditClass($id)) {
                return redirect('dashboard')->with('error', 'You don\'t have access to that class.');
            }

            // Selected class data
            $cls = Classes::find($id);

            // Assigned users
            $assigned = $cls->users
                ->load('role', 'cls')
                ->whereIn('role.id', [4, 7])
                ->where('id', '!=', auth()->user()->id)
                ->sortBy([['role.id', 'asc'], ['last_name', 'asc']])
                ->values()->toArray();

            // All users
            $unassigned = $s->users
                ->load('role', 'cls')
                ->whereIn('role.id', [4, 7])
                ->where('id', '!=', auth()->user()->id)
                ->sortBy([['role.id', 'asc'], ['last_name', 'asc']]);

            //
            // Fix user list for Vue - Filter and sort
            // --------------------------------------------------------------------------
            $unassigned = $unassigned->filter(function ($u) use ($cls) {
                return !$u->cls || ($u->role->id == 7 && !in_array($cls->class_name, $u->getClassName()));
            })->values()->toArray();
        }

        // Return view
        return view('school_management.edit_class', [
            'classes' => $classes,
            'show_users' => count($unassigned) + count($assigned),
            'student_id' => $user_id,
            'class_id' => (int) $id,
            'class_types' => $s->types ?? '',
            'class_count' => count($classes),
            'class_name' => $cls->class_name ?? '',
            'class_type' => $cls->class_type ?? '',
            'class_type_name' => $cls->class_type_name ?? '',
            'users' => $unassigned,
            'assigned' => $assigned,
            'student_code' => $cls->student_code ?? '',
            'assistant_code' => $cls->assistant_teacher_code ?? ''
        ]);
    }

    /**
     * Create class
     * See ClassesObserver for additional operations associated with creating a class
     *
     * @param  CreateClassPost $request
     * @return Redirect
     */
    public function create(CreateClassPost $request) {
        if (auth()->user()->classes()->where('class_name', $request->class_name)->exists()) {
            return redirect()->back()->with('error', 'Class name already exists.');
        }

        $validated = $request->validated();
        $c = new Classes();
        $c->school_id = auth()->user()->school_id;
        $c->class_name = stripslashes($request->input('class_name'));
        $c->class_type = $request->input('class_type');
        if ($c->save()) {
            return redirect()->to('/edit/class/' . $c->id)->with('success', 'Successfully created the class: ' . $c->class_name);
        } else {
            return redirect()->back()->with('error', 'Failed to create class');
        }
    }

    /**
     * Update class name, class type, welcome message
     *
     * @param  Request $request
     * @return void
     */
    public function update(Request $request) {
        // Check if current user can edit class
        if (!auth()->user()->canEditClass($request->class_id)) {
            return redirect('dashboard')->with('error', 'You don\'t have access to that class.');
        }

        // Update class data
        $c = Classes::find($request->class_id);
        $c->class_name = stripslashes($request->input('class_name'));
        $c->class_type = $request->input('class_type');
        if ($c->save()) {
            return $c;
        }
    }

    /**
     * Delete class
     * See ClassesObserver for additional operations associated with deleting a class
     *
     * @param Request $request
     * @return void
     */
    public function delete(Request $request) {
        $class_id = $request->class_id;

        // Check if current user can edit class
        if (!auth()->user()->canEditClass($class_id)) {
            return redirect('dashboard')->with('error', 'You don\'t have access to that class.');
        }

        // Deactivate the students
        if ($request->delete_class_students == 2) {
            $students = Classes::find($class_id)->members()->where('role_id', 4)->pluck('user_id')->toArray();
            User::whereIn('id', $students)->update(['status' => 0]);
        }

        // Delete the student and all associated submitted assignments, etc (see UserObserver)
        if($request->delete_class_students == 3) {
            $students = Classes::find($class_id)->members()->where('role_id', 4)->pluck('user_id')->toArray();
            $students = User::whereIn('id', $students)->get();
            $students->each(function ($u) {
                $u->delete();
            });
        }

        Classes::find($class_id)->delete();
    }

    /**
     * Delete class redirect
     *
     * @return Redirect
     */
    // TODO: /deleted/class is a bandaid
    public function deleteRedirect() {
        return redirect('edit/class')->with('success', 'Class has been successfully deleted.');
    }

    /**
     * Update members of class
     *
     * @param  Request $request
     * @return Response
     */
    public function updateMembers(Request $request) {
        switch ($request->input('type')) {
            case 'add':
                $c = new ClassMembers();
                $u = User::find($request->input('user_id'));

                // Update search index for User model
                $u->searchable();

                $c->user_id = $request->input('user_id');
                $c->role_id = $u->role->id;
                $c->class_id = $request->input('class_id');

                if ($c->save()) {
                    return response()->json(['success' => 'Successfully added user to class'], 200);
                } else {
                    return response()->json(['error' => 'Failed to save record'], 503);
                }
                break;

            case 'remove':

                if (ClassMembers::where([['user_id', $request->input('user_id')], ['class_id', $request->input('class_id')]])->delete()) {
                    // Check and remove from team too
                    if (TeamMembers::where('user_id', $request->input('user_id'))->delete()) {
                        return response()->json(['success' => 'Successfully removed user and removed from team'], 200);
                    } else {
                        return response()->json(['success' => 'Successfully removed user'], 200);
                    }

                } else {
                    return response()->json(['error' => 'Failed to remove record'], 503);
                }

                break;
        }
    }

    /**
     * Regenerate student and assistant teacher codes
     *
     * @param  Request $request
     * @return Response
     */
    public function regenerate(Request $request) {
        $class = Classes::where('id', $request->class_id)->first();
        $code = $class->school_id . str_random(4);

        // Student
        if ($request->type == 1) {
            $class->student_code = $code;
        // Assistant
        } else {
            $class->assistant_teacher_code = $code;
        }

        if ($class->save()) {
            return response()->json(['success' => $code], 200);
        } else {
            return response()->json(['error' => 'Failed'], 422);
        }
    }
}
