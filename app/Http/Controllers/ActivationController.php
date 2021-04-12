<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ActivationPost;
use DB;
use Hash;

// Email validator
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

// Models
use App\User;
use App\Classes;
use App\WorksheetRubric;
use App\Schools;
use App\ActivationAccounts;

use Mail;
use App\Http\Requests\SendCodesPost;
use App\Mail\SendCodesEmail;
use App\Mail\SendAccountEmail;
use App\Mail\SendDemoNoticeEmail;
use App\Roles;

class ActivationController extends Controller {

    public function __construct() {
        $this->middleware('sess')->only('processUser');
    }

    /**
     * Send Codes to Teachers
     *
     *
     * @param  Request $request
     * @return Redirect
     */
    public function send(SendCodesPost $request) {
        $v = $request->validated();
        $emails = preg_replace("/((\r?\n)|(\r\n?))/", ',', $request->input('email'));
        $emails = explode(',', $emails);
        $url = config('app.url') . $request->input('url');
        $demo = $request->school_id == config('app.demo.school');
        foreach ($emails as $email) {
            $email = trim($email, ' ');
            Mail::to($email)
                ->bcc(config('mail.admin_notification'))
                ->send(new SendCodesEmail($request->input('code'), $url, $demo));
        }
        return redirect()->to('/dashboard')->with('success', 'Activation codes have been sent.');
    }

    /**
     * Send Upload Codes
     *
     * @param  Request $request
     * @return Response
     */
    public function sendUpload(Request $request) {
        $accounts = ActivationAccounts::where('school_id', auth()->user()->school_id)
            ->whereIn('id', $request->IDs)
            ->select('id', 'first_name', 'email', 'password', 'sent', 'code', 'note')->get();

        $mail = null;

        if ($accounts) {
            foreach ($accounts as $account) {
                $note = $request->note ? $account->note : '';
                $demo = auth()->user()->demo;

                if ($account->password) {
                    $url = config('app.url') . '/login?' . 'username=' . urlencode($account->email);
                    $mail = new SendAccountEmail($account->email, $url, $demo, $note, $account->first_name);

                } else if ($account->code) {
                    $url = config('app.url') . '/activate/process/' . $account->code . '/100';
                    $mail = new SendCodesEmail($account->code, $url, $demo, $note, $account->first_name);
                }

                if ($url && $mail && !$account->sent) {
                    Mail::to($account->email)
                        ->bcc(config('mail.admin'))
                        ->send($mail);
                    ActivationAccounts::where('id', $account->id)->update(['sent' => 1]);
                }
            }
            return response()->json(['success' => 'Successfully sent emails.'], 200);
        } else {
            return response()->json(['error' => 'Something went wrong. Please contact <a href="/support">support.</a>'], 503);
        }
    }

    /**
     * Show Activation Page
     *
     * @param  Request $request
     * @return resource  School activation view
     */
    public function show(Request $request) {
        if (\Auth::check()) {
            return redirect('/dashboard');
        }

        $url = $request->path();
        switch ($url) {
            case 'activate/teacher':
                $type = 3;
                $type_name = 'Teacher';
                break;
            case 'activate/student':
                $type = 4;
                $type_name = 'Student';
                break;
            case 'activate/school':
                $type = 6;
                $type_name = 'School Administrator';
                break;
            case 'activate/assistant':
                $type = 7;
                $type_name = 'Assistant Teacher';
                break;
            case 'activate/custom':
                $type = 100;
                $type_name = 'Account';
                break;
            default:
                // Developer should never actually happen
                $type = 1;
                break;
        }
        return view('school_management.activation', [
            'type' => $type,
            'type_name' => $type_name
        ]);
    }

    /**
     * Show Activation Upload
     *
     * @param  int $class_id
     * @return resource Add accounts view
     */
    public function showUpload($class_id = 0) {
        if (!(auth()->user()->role->slug == 'school-admin' || auth()->user()->role->slug == 'teacher' || auth()->user()->role->slug == 'assistant-teacher')) {
            return redirect()->back()->with('error', 'You must be a school administrator or teacher to do that.');
        }

        if (auth()->user()->role->slug == 'school-admin' || auth()->user()->canSchoolAdmin()) {
            $classes = Classes::with('type')->where('school_id', auth()->user()->school_id)->get();
        } else {
            $classes = auth()->user()->classes()->get();
        }

        $classes->append('class_type_name');
        $classes->map(function ($class) {
            return $class->only(['id', 'class_name', 'class_type_name']);
        });

        $user_roles = collect(config('constants.activation_roles'));

        // Modify user_roles list for demo account
        if(auth()->user()->demo) { // && auth()->user()->demo_admin
            $user_roles = $user_roles->whereNotIn('slug', ['school-admin', 'hybrid']);
        }

        return view('school_management.add_accounts', [
            'class_id' => $class_id,
            'school_id' => auth()->user()->school_id,
            'school_name' => auth()->user()->school->name,
            'classes' => $classes,
            // Use roles and which roles can add them
            'user_roles' => $user_roles,
            'school_admin' => auth()->user()->canSchoolAdmin(),
            'users' => self::getAccounts()
        ]);
    }

    /**
     * getAccounts
     *
     * @return Array
     */
    public static function getAccounts() {
        $roles = collect(config('constants.activation_roles'));
        // Get only own added accounts if not school admin
        if(!auth()->user()->canSchoolAdmin()) {
            $users = ActivationAccounts::where(['school_id' => auth()->user()->school_id, 'activator_id' => auth()->user()->id]);
        } else {
            $users = ActivationAccounts::where('school_id', auth()->user()->school_id);
        }

        $users = $users->select('id', 'role', 'first_name', 'last_name', 'email', 'code', 'password', 'note', 'dual', 'class_id', 'sent', 'updated_at as date')
        ->orderBy('updated_at', 'desc')
        ->get();

        $users->each(function ($u) use ($roles) {
            if($u->dual) {
                $u->role = $roles->where('slug','hybrid')->first();
            } else {
                $u->role = $roles->where('role', $u->role)->whereNotIn('slug', ['hybrid'])->first();
            }
            $u->user_id = User::where('email', $u->email)->value('id');
        });
        return $users;
    }

    /**
     * Process Upload
     *
     * @param  Request $request
     * @return Response
     */
    public function processUpload(Request $request) {
        ini_set('max_execution_time', 300);
        $success = 1;
        $password = null;
        $error_message = 'There was a problem.';
        $response_data = $request->accounts;

        // Early checks that require laravel/database and an immediate error response
        foreach ($response_data as $k => $user) {
            // If email/username exists, add user->error
            if (User::where('email', $user['email'])->exists() || ActivationAccounts::where('email', $user['email'])->exists()) {
                $success = 0;
                $error_message = 'The username(s) already exist in our system. Please make adjustments.';
                $response_data[$k]['error'][] = 'email';
            }
            // Require valid email for all but student
            if (isset($user['role']['role']) && $user['role']['role'] !== 4) {
                $validator = new EmailValidator();
                if (!$validator->isValid($user['email'], new RFCValidation())) {
                    $success = 0;
                    $error_message = 'This type of account requires a valid email address.';
                    $response_data[$k]['error'][] = 'email';
                }
            }
        }

        // If early checks pass:
        if ($success) {
            foreach ($response_data as $k => $user) {
                // Process optionals
                $note = $user['note'] ?? null;
                $class_id = $user['class_id'] ?? null;
                $user['role'] = $user['role'] ?? ['id' => 1, 'role' => 4, 'slug' => 'student'];

                // Check if csv role exists
                $roles = collect(config('constants.activation_roles'));
                $role = collect($roles)->where('id', $user['role']['id'])->first();

                if($role) {
                    $user['role'] = $role['role'];
                    $response_data[$k]['role'] = $role;
                    if($role['slug'] === 'hybrid') {
                        $user['dual'] = 1;
                    }
                } else {
                    $success = 0;
                    $error_message = 'Role does not exist';
                    $response_data[$k]['error'][] = 'role';
                }

                // Null class_id for all but student/assistant
                if ($user['role'] != 4 && $user['role'] !== 7) {
                    $class_id = null;
                }

                // Check if array (happens with adding users via multiselect class options)
                if(is_array($class_id)) {
                    $class_id = $class_id['id'];
                    $user['class_id'] = $class_id;
                    $response_data[$k]['class_id'] = $class_id;
                }

                // If password exists or generate_password
                if ($success && (isset($user['password']) || $request->has('generate_password'))) {

                    // Make password
                    if (isset($user['password'])) {
                        // Requires at least one uppercase letter, a number, and be 6 characters or more.'
                        if (!preg_match(config('constants.password_regex'), $user['password'])) {
                            $success = 0;
                            $error_message = 'Passwords must contain at least one uppercase letter, a number, and be 6 characters or more.';
                            $response_data[$k]['error'][] = 'password';
                        } else {
                            $user['password'] = Hash::make($user['password']);
                        }
                    } else {
                        $response_data[$k]['password'] = substr(md5(microtime()), rand(0, 26), 6); // Send password back for display
                        $user['password'] = Hash::make($response_data[$k]['password']);
                    }
                    $password = $user['password'];

                    if ($success) {
                        // Create account
                        $user['school_id'] = $request->school_id;
                        $request_data = new Request($user);
                        $u = self::createAccount($request_data, $user['role'], $class_id);
                        $response_data[$k]['user_id'] = $u->id;

                        if (!$u) {
                            $success = 0;
                            $error_message = 'Something went wrong. Please contact support.';
                        }
                    }
                }

                if ($success) {
                    // Insert into activation_accounts
                    $s = Schools::find($request->school_id);
                    $a = ActivationAccounts::create([
                        'activator_id' => auth()->user()->id,
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'password' => $password,
                        'school_id' => $s->id,
                        'class_id' => $class_id,
                        'note' => $note,
                        'dual' => $user['dual'] ?? 0,
                        'role' => $user['role']
                    ]);

                    if ($a->save()) {
                        $response_data[$k]['id'] = $a->id; // Send activation_accounts id back instead
                        $response_data[$k]['sent'] = 0;
                        if ($request->generate_code) {
                            $a->generateCode();
                            $response_data[$k]['code'] = $a->code; // Send code back for display
                        }
                    } else {
                        $success = 0;
                        $error_message = 'Something went wrong.';
                    }
                }
            }
        }

        if ($success) {
            $response = ['success' => 'Your accounts have been processed.', 'accounts' => $response_data]; // $response_data returned from save()
            $response_type = 200;
        } else {
            $response = ['error' => $error_message, 'accounts' => $response_data];
            $response_type = 422;
        }
        return response()->json($response, $response_type);
    }

    /**
     * Edit Upload
     *
     * @param  Request $request
     * @return Response
     */
    public function editUpload(Request $request) {
        $success = 1;
        $error_message = 'Something went wrong.';
        $data = $request->accounts;
        foreach ($data as $k => $user) {

            $class_id = isset($user['class_id']['id']) ? $user['class_id']['id'] : null;
            $note = isset($user['note']) ? $user['note'] : null;
            $code = isset($user['code']) ? $user['code'] : null;
            $sent = isset($user['sent']) ? $user['sent'] : null;

            // Update activation accounts user
            if (ActivationAccounts::where('id', $user['id'])->exists()) {
                try {
                    ActivationAccounts::where('id', $user['id'])->update([
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'code' => $code,
                        'class_id' => $class_id,
                        'note' => $note,
                        'sent' => $sent,
                        'role' => $user['role']['role'],
                        'dual' =>  $user['role']['slug'] === 'hybrid' ? 1 : 0
                    ]);
                } catch (\Illuminate\Database\QueryException $exception) {
                    $success = 0;
                    if (strpos($exception, 'activation_accounts_code_unique')) {
                        $data[$k]['error'][] = 'code';
                        $error_message = 'Code already exists in the system.';
                    } else if (strpos($exception, 'activation_accounts_email_unique')) {
                        $data[$k]['error'][] = 'email';
                        $error_message = 'Username already exists in the system.';
                    }
                }
            }

            // Update User
            if (
                isset($user['user_id']) &&
                User::where('id', $user['user_id'])->exists()
                //User::where('email', $user['user_id'])->value('first_name') === $user['first_name']
                // User::where('id', $user['user_id'])->value('first_name') === $user['first_name'] &&
                // User::where('id', $user['user_id'])->value('last_name') === $user['last_name']
            ) {
                try {
                    User::where('id', $user['user_id'])->update([
                        'name' => $user['first_name'] . ' ' . $user['last_name'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'note' => $note
                    ]);

                    // Update class
                    if ($class_id) {

                        DB::table('class_members')->updateOrInsert(
                            ['class_id' => $class_id, 'user_id' => $user['user_id']],
                            ['role_id' => $user['role']['role']]
                        );
                    }

                    // Update role
                    DB::table('users_roles')->where('user_id', $user['user_id'])->update(['role_id' => $user['role']['role']]);

                    // Update hybrid teacher
                    if($user['role']['slug'] === 'hybrid') {
                        DB::table('users_permissions')->updateOrInsert(['user_id' => $user['user_id'], 'permission_id' => 6]);
                    } else if(DB::table('users_permissions')->where('user_id', $user['user_id'])->exists()) {
                        DB::table('users_permissions')->where('user_id', $user['user_id'])->delete();
                    }


                } catch (\Illuminate\Database\QueryException $exception) {
                    $success = 0;
                    \Log::debug($exception);
                    if (strpos($exception, 'users_email_unique')) {
                        $data[$k]['error'][] = 'email';
                        $error_message = 'Username already exists in the system.';
                    }
                }
            }
        }

        if ($success) {
            $response = ['success' => 'Your accounts have been updated.'];
            $response_type = 200;
        } else {
            $response = ['error' => $error_message, 'accounts' => $data];
            $response_type = 503;
        }
        return response()->json($response, $response_type);
    }

    /**
     * Process Code
     *
     * @param  string $code
     * @param  int $type
     * @return resource|Redirect - Return view or redirect on error
     */
    public function processCode($code, $type) {

        $types = [
            3 => ['code' => 'teacher_code', 'name' => 'Teacher', 'db' => 'schools'],
            4 => ['code' => 'student_code', 'name' => 'Student', 'db' => 'class'],
            6 => ['code' => 'school_code', 'name' => 'School', 'db' => 'schools'],
            7 => ['code' => 'assistant_teacher_code', 'name' => 'Assistant Teacher', 'db' => 'class'],
            // Custom Code from Activation Upload
            100 => ['code' => 'code', 'name' => 'Custom', 'db' => 'activation_accounts']
        ];

        $db = DB::table($types[$type]['db'])->where($types[$type]['code'], $code)->exists();

        if ($db) {
            $db = DB::table($types[$type]['db'])->where($types[$type]['code'], $code)->first();
            $school_id = $types[$type]['db'] == 'schools' ? $db->id : $db->school_id;
            $class = ($type == 4 || $type == 7) ? 1 : 0;

            // Get role name if type custom
            if ($type == 100) {
                $types[$type]['name'] = DB::table('roles')->where('id', $db->role)->value('name');
                $type = $db->role; // This needs to be changed if trying to upload non-student accounts
            }
        }

        if ($db) {
            return view('school_management.user_registration', [
                'type' => $type,
                'type_name' => $types[$type]['name'],
                'first_name' => $db->first_name ?? '',
                'last_name' => $db->last_name ?? '',
                'class_id' => $db->class_id ?? '',
                'email' => $db->email ?? '',
                'dual' => $db->dual ?? '',
                'activation_code' => $code,
                'school_id' => $school_id,
                'class' => $class
            ]);
        } else {
            return redirect()->back()->withErrors(['Code Mismatch', 'We were unable to match your code, please try again.']);
        }

    }

    /**
     * Create account - add to class if $class_id available
     *
     * @param  Request $request
     * @param  int $role
     * @param  int $class_id
     * @return User
     */
    private static function createAccount($request, $role, $class_id = 0) {
        $firstname = ucwords($request->first_name);
        $lastname = ucwords($request->last_name);

        $name = $firstname . ' ' . $lastname;
        $u = new User();
        $u->email = $request->email;
        $u->name = $name;
        $u->first_name = $firstname;
        $u->last_name = $lastname;
        $u->school_id = $request->school_id;
        $u->password = Hash::make($request->password);
        $u->note = isset($request->note) ? $request->note : NULL;
        $u->subject = $request->type === 3 ? $request->subject : NULL;
        if ($u->save()) {
            $u->searchable();
            // Add to class for activation upload
            if ($class_id) {
                // Needs canAddtoClass or something
                DB::table('class_members')->insert([
                    'class_id' => $class_id,
                    'user_id' => $u->id,
                    'role_id' => $role
                ]);
            }
            // Add user into proper role
            DB::table('users_roles')->insert([
                'user_id' => $u->id,
                'role_id' => $role == 6 && $request->dual ? 3 : $role
            ]);

            // Hybrid teacher permissions
            if ($request->dual) {
                DB::table('users_permissions')->insert([
                    'user_id' => $u->id,
                    'permission_id' => 6
                ]);
            }

            // Add default rubric settings
            if($request->dual || $role == 3) {
                DB::select('call default_rubric_value(?)', [$u->id]);
                DB::table('worksheet')->select('id', 'title', 'order', 'default_point_value')
                    ->where('status', 1)
                    ->orderBy('order', 'asc')
                    ->get()
                    ->each(function ($w) use ($u) {
                        $wr = new WorksheetRubric();
                        $wr->category_id = $w->id;
                        $wr->category_name = $w->title;
                        $wr->order = $w->order;
                        $wr->category_value = $w->default_point_value;
                        $wr->teacher_id = $u->id;
                        $wr->save();
                    });
            }
            // Send admin's an announcement if the account is a demo account
            if($u->school_id == config('app.demo.school')) {
                $roles = Roles::find($role);
                Mail::to(config('mail.demo_notification'))
                    ->send(new SendDemoNoticeEmail($u->first_name, $u->last_name, $u->email, $roles->name));
            }

            return $u;
        }
    }

    /**
     * Create User
     *
     * @param  Request $request
     * @return Redirect
     */
    public function processUser(ActivationPost $request) {
        $v = $request->validated();
        $role = $request->type;
        $class_id = 0;
        $code_type = $role == 4 ? 'student_code' : 'assistant_teacher_code';

        // Require valid email for all but student
        if ($role != 4) {
            $validator = new EmailValidator();
            if (!$validator->isValid($request->email, new RFCValidation())) {
                return redirect()->back()->withErrors(['email' => 'This type of account requires a valid email address.'])->withInput();
            }
        }

        // Determine if user should be added to a class
        if ($request->class == 1) { // Regular activation
            $class_id = Classes::where($code_type, $request->activation_code)->value('id');
        } else if ($request->class_id) { // Upload activation
            $class_id = $request->class_id;
        }

        // Create account
        $u = self::createAccount($request, $role, $class_id);

        auth()->login($u);
        return redirect()->to('/dashboard')->with('success', 'You have successfully activated your account.');
    }
}
