<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

//
// Non-Authenticated
// --------------------------------------------------------------------------
Auth::routes();
Route::get('/login', 'Auth\AuthController@show')->name('login');
Route::post('login', 'Auth\LoginController@login')->middleware('sess');
Route::get('logout', 'Auth\AuthController@logout');
Route::get('wp-userdata-logout', 'Auth\AuthController@wpUserdataLogout');

Route::group(['middleware' => ['activity']], function () {
    // Clever Login
    Route::get('/loginusername/{driver}', 'Auth\LoginController@loginUsername');
    Route::get('/processlogin/{driver}/{username}/', 'Auth\LoginController@processLogin');

    // Activation
    Route::get('/activate/school', 'ActivationController@show')->name('process');
    Route::get('/activate/student', 'ActivationController@show');
    Route::get('/activate/teacher', 'ActivationController@show');
    Route::get('/activate/assistant', 'ActivationController@show');
    Route::get('/activate/custom', 'ActivationController@show');

    Route::post('/activate/process', 'ActivationController@processCode')->middleware('activation');
    Route::post('/activate/process/{code}/{type}', 'ActivationController@processCode');
    Route::get('/activate/process/{code}/{type}', 'ActivationController@processCode');
    Route::post('/activate/user', 'ActivationController@processUser');

    // Zoom
    Route::get('redirect/zoom', 'ZoomController@redirectToProvider')
        ->where('driver', implode('|', config('auth.socialite.drivers')));

    Route::get('zoom/callback', 'ZoomController@handleProviderCallback')
        ->where('driver', implode('|', config('auth.socialite.drivers')));

    Route::webhooks('/zoom/deauth');

    // Socialite
    Route::get('redirect/{driver}', 'Auth\LoginController@redirectToProvider')
        ->name('login.provider')
        ->where('driver', implode('|', config('auth.socialite.drivers')));

    Route::get('{driver}/callback', 'Auth\LoginController@handleProviderCallback')
        ->name('login.callback')
        ->where('driver', implode('|', config('auth.socialite.drivers')))->middleware('sess');
});

//
// Authenticated - Non-Admin
// --------------------------------------------------------------------------
Route::group(['middleware' => ['web', 'auth', 'user', 'nonadmin', 'activity']], function () {

    // Support
    Route::get('/support', 'SupportController@show');
    Route::get('/support/{type?}/{id?}', 'SupportController@show');
    Route::post('/support', 'SupportController@send');

    // Activation upload
    Route::get('/upload/accounts/{class_id?}', 'ActivationController@showUpload');
    Route::post('/upload/accounts', 'ActivationController@processUpload');
    Route::post('/upload/accounts/edit', 'ActivationController@editUpload');
    Route::post('/upload/accounts/send', 'ActivationController@sendUpload');
    Route::get('/get/upload/accounts', 'ActivationController@getAccounts');

    // Send Activation Codes
    Route::post('/activate/send', 'ActivationController@send');

    // Student Assignments Page/list
    Route::get('assignments', 'AssignmentsHomeController@show');
    Route::get('assignments/view/{id?}/{catid?}', 'AssignmentsHomeController@show');
    Route::get('assignments/team/', 'AssignmentsHomeController@show');

    // Worksheets
    Route::get('worksheets/{id}/{projectid}', 'WorksheetController@show')->where('projectid', '[0-9]+');
    Route::get('worksheets/{id}/find', 'WorksheetController@find');
    Route::get('worksheets/{id}/pcount', 'WorksheetController@find');
    Route::get('worksheets/team/{id}/{projectid}', 'WorksheetController@show');
    Route::post('/worksheets/store/{id}/{projectid}/', 'WorksheetController@store')->middleware('optimizeImages');
    Route::get('/worksheets/get/{id}/{projectid}/', 'WorksheetController@get');
    Route::post('/create/fields/', 'WorksheetController@createFields');
    Route::post('/remove/fields/', 'WorksheetController@removeFields');

    // Projects
    Route::post('/create/project', 'WorksheetController@create');
    Route::get('/create/project', 'WorksheetController@create');
    Route::post('/project/management/', 'WorksheetController@manage');
    Route::post('/project/switch/', 'WorksheetController@switchLocked');
    Route::get('/switched/project/', 'WorksheetController@switchRedirect');

    // Messages
    Route::get('/messages/{subpage?}', 'MessageController@show');
    Route::post('/send/message/', 'MessageController@send');
    Route::post('/update/message/', 'MessageController@update');
    Route::post('/delete/message/', 'MessageController@delete');

    // Due dates
    Route::get('/edit/duedates/{id?}/{type?}', 'DueDateController@show');
    Route::post('/update/duedate/', 'DueDateController@update');

    // Chats
    Route::get('/chat/{ctype}/{ctype_id?}', 'ChatController@index');
    Route::get('/msgs/{ctype}/{ctype_id?}', 'ChatController@fetch');
    Route::post('/msgs', 'ChatController@send');
    Route::post('/delete/msgs', 'ChatController@delete');
    Route::post('/download/chat', 'ChatController@download');

    // Video Conference
    Route::get('/videocon', 'VideoConController@show');
    Route::post('/create/videocon', 'VideoConController@create');
    Route::post('/send/videocon', 'VideoConController@send');
    Route::post('/search/videocon', 'VideoConController@search');
    Route::post('/delete/videocon', 'VideoConController@delete');
    Route::get('/zoom/auth', 'ZoomController@returnAuth'); // Zoom
    Route::get('/zoom/auth/delete', 'ZoomController@deleteAuth'); // Zoom

    // Class creation
    Route::get('/create/class', 'ClassController@showCreate');
    Route::post('/create/class', 'ClassController@create');
    Route::get('/edit/class', 'ClassController@showEdit');
    Route::get('/edit/class/{id}/{user_id?}', 'ClassController@showEdit');

    // Class update / delete
    Route::post('/update/class', 'ClassController@update');
    Route::post('/delete/class', 'ClassController@delete');
    Route::post('/update/classmembers/', 'ClassController@updateMembers');
    Route::get('/deleted/class', 'ClassController@deleteRedirect');

    // Regenerate user codes for class
    Route::post('/update/codes/', 'ClassController@regenerate');

    // Teams
    Route::get('/edit/team/{class_id?}/{team_id?}', 'TeamController@show');
    Route::post('/create/team/', 'TeamController@create');
    Route::post('/delete/team/', 'TeamController@delete');
    Route::post('/update/teammembers/', 'TeamController@updateTeamMembers');
    Route::post('/edit/teamname/', 'TeamController@updateTeamName');

    // Edit Settings
    Route::get('/edit/settings', 'RubricController@show');

    // Course Settings
    Route::post('/save/settings/', 'CourseSettingsController@save');

    // Rubric
    Route::post('/update/rubric/', 'RubricController@update');
    Route::post('/add/rubric/', 'RubricController@add');
    Route::post('/activate/rubric/', 'RubricController@activate');
    Route::post('/delete/rubric/', 'RubricController@delete');

    // Asignments
    Route::get('/edit/assignments/{id?}/{view?}', 'AssignmentController@show');
    Route::get('/edit/assignments/{id?}', 'AssignmentController@show');
    Route::post('/submit/assignment/', 'AssignmentController@submit')->middleware('optimizeImages');
    Route::post('/select/assignmentinsert', 'AssignmentController@selectAssignmentInsert');

    // Manage Assignments
    Route::post('/store/assignment/', 'AssignmentController@store')->middleware('optimizeImages');
    Route::post('/update/assignment/', 'AssignmentController@update');
    Route::post('/update/assignment/class', 'AssignmentController@updateClass');
    Route::post('/update/assignment/insertpage', 'AssignmentController@updateInsertPage');
    Route::post('/update/assignment/insertstatus', 'AssignmentController@updateInsertStatus');
    Route::post('/delete/assignment/', 'AssignmentController@delete');
    Route::get('/get/assignment/screenshot/{id}', 'AssignmentController@getScreenshot');

    // Gradebook
    Route::post('/save/gradebook/', 'GradebookController@save');
    Route::post('/update/gradebook/', 'GradebookController@update');
    Route::post('/delete/gradebook/', 'GradebookController@delete');
    Route::get('gradebook/{pending_user_type?}/{pending_user_id?}', 'GradebookController@show');
    Route::get('/get/gradebook/{teacherid}/', 'GradebookController@get');

    // School admin page for hybrid teachers
    Route::get('/schooladmin', 'SchoolController@showSchoolAdmin');

    // School Tree
    Route::get('/dashboard/schooltree', 'SchoolController@showSchoolTree');

});

//
// Authenticated - Shared
// --------------------------------------------------------------------------
Route::group(['middleware' => ['web', 'auth', 'user', 'activity']], function () {
    // Dashboard (To prevent having to change header logo link)
    Route::get('/dashboard', 'DashboardController@show')->name('dashboard');
    // Login as
    Route::get('/dashboard/loginas/{id?}', 'AdminController@loginAs')->middleware('sess');
    Route::get('/eduadmin/loginas/{id?}', 'AdminController@loginAs')->middleware('sess');
    // Edit Account
    Route::get('/edit/account', 'UserController@showAccount');
    // Update Users
    Route::post('/update/user/', 'UserController@update');
    // Demo Agreement
    Route::post('/demo/agreement', 'UserController@agreement');
    // Switch Class Type
    Route::get('/switchclasstype/{ct}', 'UserController@switchClassType');
    // WP Posts Announcements
    //Route::get('/announcements', 'AnnouncementsController@show');
});

// Don't log activity
Route::group(['middleware' => ['web', 'auth', 'user']], function () {
    // WP Activity Log
    Route::get('/wp/log', 'WordpressController@log');
    // Home
    Route::get('/', 'HomeController@index');
    Route::get('/home', 'HomeController@index');
    // Fetch chat messages
    Route::get('/msgs/{ctype}/{ctype_id?}', 'ChatController@fetch');
    // Avatar requests
    Route::get('/avatars/{path}', 'UserController@createAvatarUrl')->where('path', '.*');
});

//
// Authenticated - Admin
// --------------------------------------------------------------------------
Route::group(['middleware' => ['web', 'auth', 'user', 'admin']], function () {
    Route::get('/eduadmin/create/school', 'SchoolController@showCreate');
    Route::post('/eduadmin/create/school', 'SchoolController@create');
    Route::get('/eduadmin', 'AdminController@show');
    Route::get('/eduadmin/edit/school/{school_id?}', 'AdminController@show');
    Route::post('/eduadmin/edit/school', 'SchoolController@edit');
    Route::get('/eduadmin/delete/school/{school_id}', 'SchoolController@delete');
    Route::post('/eduadmin/edit/school/note', 'SchoolController@editNote');
    Route::get('/eduadmin/search/users', 'AdminController@userSearch');
    Route::post('/eduadmin/search/users', 'AdminController@userSearch');
    Route::get('/eduadmin/search/schools', 'AdminController@schoolSearch');
    Route::get('/eduadmin/edit/screenshot', 'AssignmentController@replaceScreenshot');
    Route::post('/eduadmin/edit/screenshot', 'AssignmentController@replaceScreenshot'); //->middleware('optimizeImages'); (doesn't work with intervention image::make)
    Route::post('/eduadmin/search/schools', 'AdminController@schoolSearch');
    Route::get('/contracts/{path}', 'AdminController@getContracts')->where('path', '.*');
});

//
// Laravel Activity Logger
// --------------------------------------------------------------------------
// 'namespace' => '\jeremykenedy\LaravelLogger\App\Http\Controllers'
Route::group(['prefix' => 'eduadmin/activity', 'middleware' => ['web', 'auth', 'admin']], function () {

    // Dashboards
    Route::get('/', 'LaravelLoggerController@showAccessLog')->name('activity');
    Route::get('/cleared', ['uses' => 'LaravelLoggerController@showClearedActivityLog'])->name('cleared');

    // Drill Downs
    Route::get('/log/{id}', 'LaravelLoggerController@showAccessLogEntry');
    Route::get('/cleared/log/{id}', 'LaravelLoggerController@showClearedAccessLogEntry');

    // Forms
    Route::delete('/clear-activity', ['uses' => 'LaravelLoggerController@clearActivityLog'])->name('clear-activity');
    Route::delete('/destroy-activity', ['uses' => 'LaravelLoggerController@destroyActivityLog'])->name('destroy-activity');
    Route::post('/restore-log', ['uses' => 'LaravelLoggerController@restoreClearedActivityLog'])->name('restore-activity');
});
