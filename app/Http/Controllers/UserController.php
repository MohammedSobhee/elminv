<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Hash;

// Model
use App\User;
use App\Messages;
use App\Scopes\ActiveScope;
use App\UserSessionData;

use DB;
// use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

// use Illuminate\Routing\Redirector;
// use Illuminate\Http\RedirectResponse;
// use Illuminate\Contracts\Container\BindingResolutionException;
// use InvalidArgumentException;

class UserController extends Controller {

    /**
     * Show Profile
     *
     * @return resource - View
     */
    public function showAccount() {
        return view('pages.edit_account', [
            'user' => User::find(auth()->user()->id),
            'school_name' => ucwords(auth()->user()->school->name)
        ]);
    }

    /**
     * Update user info
     *
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request) {

        $request->validate([
            'email' => 'unique:users',
            'confirm_password' => [
                'regex:' . config('constants.password_regex')
            ]
        ],
        [
            'email.unique' => 'The email or username has already been taken.',
            'confirm_password.regex' => 'The password must contain least one uppercase letter, a number, and be 6 or more characters.'
        ]);

        // User activate / deactivate
        if ($request->has('status')) {
            $u = User::withoutGlobalScope(ActiveScope::class)->find($request->user_id);
            $u->status = $request->status;
            if (auth()->user()->canSchoolAdmin() && $u->update()) {
                return response()->json(['success' => 'Successfully changed member status.'], 200);
            } else {
                return response()->json(['error' => 'Failed to change member status.'], 500);
            }
        } else {
            $u = User::find($request->user_id);
        }



        // If updating role/permissions, do nothing but that
        if ($request->has('role')) {
            return static::updateRole($u, $request->role);
        }

        // If avatar, do nothing but upload avatar
        if ($request->has('avatar')) {
            return static::uploadAvatar($u, $request->avatar);
        }

        // Remove avatar
        if ($request->has('remove_avatar')) {
            $u->avatar = '';
            UserSessionData::put($u->id, 'avatar', '');
        }

        // First / Last Name
        if ($request->first_name || $request->last_name) {
            $first = $request->input('first_name') ?? $u->first_name;
            $last = $request->input('last_name') ?? $u->last_name;
            $u->first_name = $first;
            $u->last_name = $last;
            $u->name = $first . ' ' . $last;
            UserSessionData::put($u->id, 'firstname', $request->first_name);
            UserSessionData::put($u->id, 'lastname', $request->last_name);
        }

        // Password
        if ($request->has('confirm_password')) {
            $u->password = Hash::make($request->confirm_password);
        }

        // Email, Note, Nickname
        $u->email = request('email', $u->email);
        $u->note = request('note', $u->note);

        if($request->nickname) {
            $u->nickname = $request->nickname;
            UserSessionData::put($u->id, 'nickname', $request->nickname);
        }

        $message = $request->input('message');
        if (isset($message)) {
            if ($message == '<p></p>') {
                $m = Messages::where('sender_id', $request->input('teacher_id'))
                    ->where('recipient_id', $request->input('user_id'))
                    ->where('type', 3);

                if ($m->delete()) {
                    return response()->json(['success' => 'Successfully deleted message'], 200);
                } else {
                    return response()->json(['error' => 'Failed to message message'], 400);
                }
            } else if (Messages::updateOrCreate(
                ['recipient_id' => $request->input('user_id'), 'type' => 3],
                ['sender_id' => $request->input('teacher_id'), 'content' => $message]
            )) {
                return response()->json(['success' => 'Successfully saved message.'], 200);
            } else {
                return response()->json(['error' => 'Failed to retrieve data'], 500);
            }
        }

        if ($u->save()) {
            $u->searchable();
            return response()->json(['success' => 'Successfully updated account.'], 200);
        } else {
            return response()->json(['message' => 'Failed to save record'], 500);
        }
    }

    /**
     * updateRole
     *
     * @param  object $user
     * @param  string $role
     * @return
     */
    public static function updateRole($user, $role) {
        if (!auth()->user()->hasAdminPermission() || ($role == 'admin' && $user->school_id != config('app.admin_school'))) {
            return response()->json(['error' => 'Permission denied'], 422);
        }

        $roles = [
            'hybrid' => 3,
            'teacher' => 3,
            'admin' => 5,
            'school-admin' => 6,
            'assistant-teacher' => 7,
            'student' => 4
        ];

        DB::table('users_roles')->updateOrInsert(
            ['user_id' => $user->id],
            ['role_id' => $roles[$role]]
        );

        switch ($role) {
            case 'hybrid':
                DB::table('users_permissions')->updateOrInsert(
                    ['user_id' => $user->id],
                    ['permission_id' => 6]
                );
                break;
            case 'admin':
                DB::table('users_permissions')->updateOrInsert(
                    ['user_id' => $user->id],
                    ['permission_id' => 2]
                );
                break;
        }

        return response()->json(['success' => 'Successfully updated role.'], 200);
    }

    /**
     * Upload Avatar
     *
     * @param  object $user
     * @param  resource $avatar
     * @return Response
     */
    public static function uploadAvatar($user, $avatar) {
        if (preg_match("/^\s*data:([a-z]+\/[a-z]+(;[a-z\-]+\=[a-z\-]+)?)?(;base64)?,[a-z0-9\!\$\&\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i", $avatar)) {
            $name = date('Y') . '/' . time() . \Str::random(5) . '.jpg';
            $file = \Image::make($avatar)->encode('jpg', 80);

            if(strpos('http', $user->avatar) === false)
                \Storage::delete('avatars/' . $user->avatar);
            \Storage::put('avatars/' . $name, $file->__toString());

            $user->avatar = $name;
            //$path = storage_path('app/avatars/' . $name);
            //ImageOptimizer::optimize($path);
        }

        $response = [
            'success' => 'Avatar has been uploaded.',
            'avatar' => $user->avatar
        ];

        if ($user->update()) {
            UserSessionData::put($user->id, 'avatar', '/avatars/' . $user->avatar);
            return response()->json($response, Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed save avatar.'], 503);
        }
    }

    /**
     * Get avatar
     *
     * @param  string $path
     * @return Response
     */
    public function createAvatarUrl($path) {
        $storage_path = storage_path('app/avatars/' . $path);
        if (!\File::exists($storage_path)) {
            abort(404);
        }
        $img = \Image::cache(function ($image) use ($storage_path) {
            $image->make($storage_path)->resize(100, 100);
        });
        //return \Response::make(file_get_contents($storage_path), 200, $headers);
        return \Response::make($img, 200, ['Content-Type' => 'image'])->setMaxAge(86400)->setPublic();
    }


    /**
     * deleteUsers
     *
     * @param  int $user_id
     * @param  Array $users
     * @return response
     */
    public function deleteUsers($users) {
        if(!auth()->user()->canSchoolAdmin()) {
            return response()->json(['error' => "You don't have access to do that"], 200);
        }
        $users = User::whereIn('id', $users)->where('school_id', auth()->user()->school_id)->get();
        $users->each(function ($u) {
            $u->delete(); // Delete individually for UserObserver
        });
        return response()->json(['success' => 'Successfully deleted students and all their data.'], 200);
    }


    /**
     * Post Demo Agreement
     * @param Request $request
     * @return Redirect
     */
    public function agreement(Request $request) {
        if($request->no) {
            \Auth::logout();
            return redirect('login')->with('primary', 'Thank you for your interest! <a href="https://inventionlandinstitute.com/support" class="alert-link">Contact us</a> if you have any concerns.');
        }
        $user = User::find(auth()->user()->id);
        $user->agreement = $request->initials;
        if($user->save()) {
            return redirect('dashboard')->with('success', "Welcome to Inventionland Institute's Course Demo");
        }
    }



    /**
     * Switch class type (courseware select)
     *
     * @param  mixed $request
     * @return void
     */
    public function switchClassType($ct) {
        if (!$user = UserSessionData::get()) {
            return redirect()->route('dashboard')->with('error', 'Something went wrong. Please contact support with details of this request.');
        } else {
            $class_type = intval($ct);
            $coursewareTypes = $user->user_data->courseware_types;
            // Update user_data->class_type and reset page lists, last visited
            UserSessionData::put(auth()->user()->id, [
                'user_data->class_type' => $class_type,
                'parent_page' => null,
                'page_list' => null,
                'page_last_visited' => null
            ]);
            return redirect()->route('dashboard')->with('success', 'Switched to ' . $coursewareTypes[$class_type]);
        }
    }
}
