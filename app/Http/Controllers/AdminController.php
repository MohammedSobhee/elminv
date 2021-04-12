<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Schools;
use App\User;
use App\Services\AdminService;

class AdminController extends Controller {

    private static $customSearchTerms = [
        'expiring' => ['expir'],
        'deactivated_nosearch' => ['/^[dein]{2}acti[v\w]+(?:[^\w]+)?$/'], // (de/in)activ with no additional words
        'deactivated_with_search' => ['deactiv', 'inactiv']
    ];

    private static $colorCodes = [
        ['id' => 1, 'value' => '#cccccc', 'label' => 'Inactive'], // Gray
        ['id' => 7, 'value' => '#1eaa73', 'label' => 'Expiring soon'], // Green
        ['id' => 2, 'value' => '#3c63d6', 'label' => 'Free'], // Blue
        ['id' => 3, 'value' => '#dc3eda', 'label' => 'Needs yearly invoice'], // Pink
        ['id' => 4, 'value' => '#e13012', 'label' => 'Payment outstanding'], // Red
        ['id' => 5, 'value' => '#fe9f2b', 'label' => 'ILI Grant'], // Orange
        ['id' => 6, 'value' => '#ffdb69', 'label' => 'About to expire'], // Yellow
        ['id' => 7, 'value' => '#000000', 'label' => 'Not renewing'] // Black
    ];

    public function __construct() {
        view()->share('school_id', 0);
        view()->share('search_term', '');
    }

    /**
     * Show Admin Dashboard
     *
     * @param  int $school_id
     * @return Redirect|View
     */
    public function show($school_id = 0) {
        // User::all()->each(function ($u) {
        //     \App\UserSessionData::put($u->id, 'teacher_id', $u->getTeacherID());
        // });
        // Editing School
        if ($school_id) {
            $s = Schools::find($school_id);

            if ($s) {
                $school = $s->load('contact', 'settings', 'types');

                $school_users = $s->users()
                    ->with('school', 'role', 'classes', 'permissions')
                    ->get();

                if ($school->id == config('app.demo.school')) {
                    $school_users = $school_users
                        ->sortByDesc('created_at');
                } else {
                    $school_users = $school_users
                        ->sortBy('last_name')
                        ->sortByDesc('role.name');
                }
                $school_users = $school_users->values();

                $school_types = $s->types;
                $color_codes = collect(static::$colorCodes);
                $color_code = $color_codes->where('id', $school->contact->color_code)->pluck('value')->first();
            } else {
                return redirect('/eduadmin')->with('error', 'School does not exist.');
            }
        }

        return view('admin.admin', [
            'schools' => AdminService::getSchoolList()->get(),
            'school' => $school ?? 0,
            'school_id' => (int) $school_id,
            'school_users' => $school_users ?? 0,
            'school_types' => $school_types ?? 0,
            'color_codes' => $color_codes ?? 0,
            'color_code' => $color_code ?? 0,
            'countries' => \Config::get('constants.countries'),
            'standards' => \Config::get('constants.states'),
            'notes' => $school->notes ?? 0,
            //'orders' => app('App\Http\Controllers\WebhooksController')->shopifyGet(),
            'expiring' => AdminService::expiring() ?? 0,
            'payment_due' => AdminService::paymentDue() ?? 0
        ]);
    }

    /**
     * User Search
     *
     * @param  Request $request
     * @return resource Results view
     */
    public function userSearch(Request $request) {
        $results = User::search($request->search)->query(function ($builder) {
            $builder->with('school', 'role', 'classes', 'permissions');
        })
        ->take(500)->get()
            ->sortBy('last_name')
            ->sortByDesc('role.name')
            ->values();
        return view('admin.admin', [
            'results' => $results,
            'schools' => AdminService::getSchoolList()->get(),
            'search_term' => $request->search
        ]);
    }

    /**
     * School Ajax Search
     *
     * @param  Request $request
     * @return object
     */
    public function schoolSearch(Request $request) {
        $terms = static::$customSearchTerms;
        $all_terms = array_merge(...array_values($terms));

        if (\Str::contains($request->search, $all_terms)) {
            $results = AdminService::getSchoolList();

            switch (true) {
                case \Str::contains($request->search, $terms['expiring']):
                    $results = AdminService::expiring()->filter(function ($item) {
                        return $item->user_count > 0;
                    })->values();
                    break;
                case preg_match($terms['deactivated_nosearch'][0], $request->search):
                    $results = Schools::where('status', 0)->get();
                    break;
                case \Str::contains($request->search, $terms['deactivated_with_search']):
                    preg_match('/^[dein]{2}acti[v\w]+\s([\w]+)/', $request->search, $actual_search);
                    $results = Schools::search($actual_search[1])->query(function ($builder) {
                            $builder->with('settings', 'contact');
                        })
                        ->take(500)->get()
                        ->filter(function ($item) {
                            return $item->status === 0;
                        })->values();
                    break;
            }

        } else {
            $results = Schools::search($request->search)
            ->query(function ($builder) {
                $builder->with('settings', 'contact');
            })
            ->take(500)->get();
        }

        return $results;
    }

    /**
     * Get contracts
     *
     * @param  string $path
     * @return Response
     */
    public function getContracts($path) {
        if (auth()->user()->hasAdminPermission()) {
            $storage_path = storage_path('app/contracts/' . $path);
            $mime_type = mime_content_type($storage_path);
            if (!\File::exists($storage_path)) {
                abort(404);
            }
            $headers = [
                'Content-Type' => $mime_type
            ];
            return \Response::make(file_get_contents($storage_path), 200, $headers);
        } else {
            abort(404);
        }
    }

    /**
     * Login as...
     *
     * @param  int $id
     * @return Redirect
     */
        public function loginAs($id = 0) {
        // If $id isn't set, log back in to own user
        if (!$id && session()->get('ownUser')) {
            $own = session()->pull('ownUser');
            $u = User::find($own->id);
            auth()->loginUsingId($own->id);
            $url = strpos($own->lastURL, 'search') !== false ? 'dashboard' : $own->lastURL;
            return redirect($url)->with('success', 'Successfully logged back in as ' . $u->name . ' - ' . $u->role->name);

        } else if ($u = auth()->user()->getLoginAsPermission($id)) {
            // Set own user only if it doesn't yet exist which allows for
            // loginas inceptions that can eventually go back to their own account
            if(!session()->get('ownUser')) {
                session()->put('ownUser',
                    (object) [
                        'id' => auth()->user()->id,
                        'role' => auth()->user()->role->slug,
                        'lastURL' => url()->previous()
                    ]
                );
            }

            auth()->loginUsingId($id);
            return redirect('dashboard')->with('success', 'Successfully logged in as ' . $u->name . ' - ' . $u->role->name);

        } else {
            return redirect(url()->previous())->with('error', 'Permission denied.');
        }
    }
}
