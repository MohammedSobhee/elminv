<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Models
use App\Assignment;
use App\AssignmentClasses;
use App\AssignmentSubmitted;
use App\RubricCategories;
use App\Messages;
use App\Projects;
use App\User;
use App\UserSessionData;

// Services
use App\Services\StudentAssignmentsService;

// Requests
use App\Http\Requests\ClassUpsertPost;
use App\Http\Requests\CreateAssignmentPost;
use App\Http\Requests\DeleteAssignmentPost;
use App\Http\Requests\SendToTeacherPost;
use App\Http\Requests\UpdateAssignmentPost;

// Misc
use App\Jobs\ScreenshotAssignment;
use App\Mail\SendScreenShotReplacedEmail;
use Mail;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class AssignmentController extends Controller {

    /**
     * Show Custom Assignments
     *
     * @param  int $id
     * @return resource Assignments view
     */
    public function show($id = 0, $view = '') {

        // Get all available classes for teacher
        $classes = auth()->user()->classes()->select('class.id', 'class_name as name', 'class_type')->get();

        // Get all rubric categories
        $categories = RubricCategories::where([
            ['teacher_id', auth()->user()->getTeacherID()],
            ['active', 1]
        ])
            ->join('rubric_settings', 'rubric_settings.category_id', '=', 'rubric_categories.id')
            ->select('rubric_categories.id', 'rubric_categories.category_name as name', 'rubric_categories.category_value as value')
            ->get();

        // Get assignment data
        $asn = Assignment::where([
            ['user_id', auth()->user()->getTeacherID()]
        ])
            ->leftJoin('rubric_categories', 'rubric_categories.id', '=', 'assignment.category_id')
            ->leftJoin('rubric_settings', 'rubric_settings.category_id', '=', 'assignment.category_id')
            ->select(
                'assignment.id',
                'assignment_name',
                'assignment.type',
                'file_name',
                'file_location',
                'file_screenshot',
                'user_id',
                'assignment.category_id',
                'category_name',
                'points',
                'assignment.status',
                'assignment.created_at',
                'rubric_settings.active'
            )
            ->orderBy('assignment.status', 'desc')
            ->orderBy('assignment.created_at', 'desc')
            ->get();

        $assignment_data = [];
        foreach ($asn as $a) {
            // Get active classes before building final array
            $acs = AssignmentClasses::where([
                ['assignment_id', $a->id],
                ['assignment_classes.status', 1]
            ])
                ->join('class', function ($join) {
                    $join->on('class.id', '=', 'assignment_classes.class_id')
                    ->whereIn('class.id', auth()->user()->getClassIDs());
                })
                ->leftJoin('assignment_due as ad', function ($join) use ($a) {
                    $join->on('ad.class_id', '=', 'class.id')
                        ->where('ad.type_id', '=', $a->id)
                        ->groupBy('ad.due_date');
                })
                ->select('class.id', 'class.class_name as name', 'ad.due_date', 'course_pages', 'class.class_type', 'insert_status')
                ->groupBy('class.id')
                ->get();

            // Get assigned categories
            $assigned_category = [];
            if ($a->category_id && $a->active) {
                $assigned_category = [
                    'id' => $a->category_id,
                    'name' => $a->category_name
                ];
            }

            // Get assigned courseware pages
            $assigned_pages = $acs->whereNotNull('course_pages')
                ->filter()->pluck('course_pages')
                ->flatten()
                ->map(function ($p) {
                    return (object) ['id' => $p];
                })->unique();

            $fields = [
                'id' => $a->id,
                'file_location' => $a->file_location,
                'file_name' => $a->file_name,
                'file_screenshot' => $a->file_screenshot,
                'label' => $a->assignment_name,
                'type' => $a->type,
                'points' => $a->points,
                'classes' => $acs,
                'course_pages' => $assigned_pages,
                'category' => $assigned_category,
                'created' => $a->created_at,
                'status' => $a->status

            ];
            array_push($assignment_data, $fields);
        }

        // Get full list of course pages
        $course_pages = self::coursePageList();

        // Array of data
        $all_assignment_data = [
            'course_pages' => $course_pages,
            'classes' => $classes,
            'categories' => $categories,
            'files' => $assignment_data
        ];

        return view('school_management.manage_assignments', [
            'view' => $view,
            'editing_id' => $id,
            'data' => $all_assignment_data
        ]);
    }

    /**
     * Get full list of Wordpress courseware pages and Dashboard
     *
     * @return array
     */
    private static function coursePageList() {
        $insert_classtype_title = false;

        // Additional pages to allow insertion besides WP pages
        $course_pages = \Config::get('constants.insert_page_list');

        // Available class types
        $class_types = auth()->user()->classes()->pluck('class_type')->unique()->sort()->values()->toArray();

        // Determine if class type needs to be prepended to course page title
        $insert_classtype_title = (
            in_array(3, $class_types) || in_array(4, $class_types))
            && (in_array(1, $class_types) || in_array(2, $class_types)
        );

        // Build high school / middle school page list
        if (in_array(3, $class_types) || in_array(4, $class_types)) {
            $parent_id = intval(\Config::get('app.courseware_parent.highschool'));
            $title = $insert_classtype_title ? 'High / Middle School' . ': ' : '';
            $pages = self::getWordPressPages($parent_id, $title, [3, 4]);
            array_push($course_pages, ...$pages);
        }

        // Build elementary page list
        if (in_array(1, $class_types) || in_array(2, $class_types)) {
            $parent_id = intval(\Config::get('app.courseware_parent.elementary'));
            $title = $insert_classtype_title ? 'Elementary' . ': ' : '';
            $pages = self::getWordpressPages($parent_id, $title, [1, 2]);
            array_push($course_pages, ...$pages);
        }

        return $course_pages;
    }

    /**
     * Add class types and Get Started page to student page list
     *
     * @param  int $parent_id
     * @param  string $title
     * @param  array $class_types
     * @return array
     */
    private static function getWordpressPages(int $parent_id, string $title, array $class_types) {
        // Arguments for get_pages Wordpress function
        $args = [
            'child_of' => $parent_id,
            'depth' => 2,
            'title_li' => null,
            'sort_column' => 'menu_order',
            'show_date' => '',
            'echo' => 1,
            'authors' => ''
        ];

        // Wordpress get pages function (WP install required)
        $pages = get_pages($args);

        // Add class types and elementary/hs to title
        $page_list = [];
        foreach ($pages as $page) {
            $page_list[] = (object) [
                'id' => $page->ID,
                'title' =>  $title . $page->post_title,
                'link' => get_page_link($page->ID),
                'class_types' => $class_types
            ];
        }

        // Add Get started page
        array_unshift($page_list, (object) [
            'id' => $parent_id,
            'title' => $title . 'Get Started',
            'link' => get_page_link($parent_id),
            'class_types' => $class_types
        ]);

        return $page_list;
    }

    /**
     * Store custom assignments
     *
     * @param  CreateAssignmentPost $request
     * @return Response
     */
    public function store(CreateAssignmentPost $request) {

        $validated = $request->validated();

        switch ($request->type) {
            case 1:
                if ($request->hasfile('files')) {
                    $response = [];
                    foreach ($request->file('files') as $file) {
                        $original_name = $file->getClientOriginalName();
                        $stored_name = time() . \Str::random(5) . '.' . $file->getClientOriginalExtension();
                        $file->storeAs('assignments/' . date('Y'), $stored_name, 'uploads');
                        $stored_name = date('Y') . '/' . $stored_name;

                        $a = new Assignment();
                        $a->user_id = auth()->user()->getTeacherID();
                        $a->assignment_name = $original_name;
                        $a->file_name = $original_name;
                        $a->file_location = $stored_name;

                        if ($a->save()) {
                            $response[] = [
                                'id' => $a->id,
                                'file_location' => $a->file_location,
                                'created' => $a->created_at,
                                'file_name' => $a->file_name,
                                'type' => $a->type
                            ];
                        }
                    }
                }
                break;
            case 2:
                $a = new Assignment();
                $a->user_id = auth()->user()->getTeacherID();
                $a->assignment_name = $request->link;
                $a->file_name = $request->link;
                $a->file_location = $request->link;
                $a->type = 2;

                if ($a->save()) {
                    ScreenshotAssignment::dispatch($a);
                    $response = [
                        'id' => $a->id,
                        'file_location' => $a->file_location,
                        'created' => $a->created_at,
                        'file_name' => $a->file_name,
                        'type' => $a->type

                    ];
                }
                break;
        }

        if (count($response)) {
            return response()->json($response, Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to save record'], 503);
        }
    }

    /**
     * Update Custom Assignment
     *
     * @param  UpdateAssignmentPost $request
     * @return Response
     */
    public function update(UpdateAssignmentPost $request) {
        $validated = $request->validated();
        $a = Assignment::find($request->id);

        $a->assignment_name = request('label', $a->assignment_name);
        $a->points = request('points', $a->points);
        $a->category_id = request('category_id', $a->category_id);
        $a->status = request('status', $a->status);

        if ($a->save()) {
            return response()->json(['success' => 'Record saved'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to save record'], 503);
        }
    }

    /**
     * Update or insert assigned classes
     *
     * @param  ClassUpsertPost $request
     * @return Response
     */
    public function updateClass(ClassUpsertPost $request) {
        $validated = $request->validated();

        if (AssignmentClasses::updateOrInsert(
            [
                'class_id' => $request->class_id,
                'assignment_id' => $request->assignment_id
            ],
            [
                'status' => $request->status,
                'course_pages' => $request->has('course_pages') ? json_encode($request->course_pages) : NULL,
                'insert_status' => $request->insert_status
            ]
        )) {
            if ($request->has('course_pages') && $request->course_pages) {
                $ac = AssignmentClasses::where('assignment_id', $request->assignment_id)
                    ->update([
                        'insert_status' => $request->insert_status,
                        'course_pages' => json_encode($request->course_pages)
                    ]);
            }
            return response()->json(['success' => 'Record updated'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to update record'], 503);
        }
    }

    /**
     * Update courseware page array
     *
     * @param  mixed $request
     * @return void
     */
    public function updateInsertPage(Request $request) {
        $class_ids = is_array($request->class_id) ? $request->class_id : (array) $request->class_id;
        if (AssignmentClasses::whereIn('class_id', $class_ids)
            ->where('assignment_id', $request->assignment_id)
            ->update(['course_pages' => $request->course_pages])) {
            return response()->json(['success' => 'Insert pages updated'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to update insert pages'], 503);
        }
    }

    /**
     * Update course pages insert status
     *
     * @param  Request $request
     * @return Response
     */
    public function updateInsertStatus(Request $request) {
        $class_ids = is_array($request->class_id) ? $request->class_id : (array) $request->class_id;
        if (AssignmentClasses::whereIn('class_id', $class_ids)
            ->where('assignment_id', $request->assignment_id)
            ->update(['insert_status' => $request->insert_status])) {
            return response()->json(['success' => 'Insert status updated'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to update insert status'], 503);
        }
    }

    /**
     * Delete custom assignment
     *
     * @param  DeleteAssignmentPost $request
     * @return Response
     */
    public function delete(DeleteAssignmentPost $request) {
        $validated = $request->validated();
        $a = Assignment::find($request->id);

        if ($a->delete()) {
            AssignmentClasses::where('assignment_id', $request->id)->delete();

            if ($a->type == 1) {
                \Storage::disk('uploads')->delete('assignments/' . $a->file_location);
            } elseif ($a->type == 2 && $a->file_screenshot && $a->file_screenshot !== 'default/screenshot_unavailable.png') {
                \Storage::disk('uploads')->delete('assignments/' . $a->file_screenshot);
            }

            return response()->json(['success' => 'Record deleted'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to delete record -a'], 422);
        }
    }

    /**
     * Student submit custom assignment
     *
     * @param  SendToTeacherPost $request
     * @return Response
     */
    public function submit(SendToTeacherPost $request) {
        $validated = $request->validated();

        if ($request->input('assignment_submitted_id') != "NULL" && $request->input('assignment_submitted_id') != "") {
            $a = AssignmentSubmitted::find($request->assignment_submitted_id);
        } else {
            $a = new AssignmentSubmitted();
        }

        // Determine if a project should be locked
        if ($request->type == 1 && $request->project_id && !StudentAssignmentsService::selectProjects()->where('locked', 1)->exists()) {
            Projects::find($request->project_id)->lock();
        }

        $stored_name = 0;

        $a->type = $request->type;
        $a->user_id = $request->user_id;
        $a->project_id = $request->project_id;
        $a->type_id = $request->type_id;
        $a->file_name = $request->file_label;

        if ($request->hasfile('file')) {
            $file = $request->file('file');
            $stored_name = time() . \Str::random(5) . '.' . $file->getClientOriginalExtension();
            if ($a->file_location) {
                \Storage::disk('uploads')->delete('assignments/' . $a->file_location);
            }

            $file->storeAs('assignments/' . date('Y'), $stored_name, 'uploads');
            $stored_name = date('Y') . '/' . $stored_name;
            $a->file_location = $stored_name;
        }

        if ($a->save()) {

            $this->id = $a->id;

            if ($request->has('comments')) {
                // Find message
                $m = Messages::where(['recipient_id' => $a->id, 'type' => 5]);
                // Student clears message
                if ($m->exists() && $request->comments == '') {
                    if ($m->delete()) {
                        // Null message_id from Assignments Submitted
                        AssignmentSubmitted::where('id', $this->id)->update(['message_id' => NULL]);
                        return response()->json(['success' => 'Successfully deleted comment.'], 200);
                    } else {
                        return response()->json(['error' => 'Failed to delete comment.'], 503);
                    }

                    // Student adds message
                } else if ($request->comments && $request->comments !== 'null') {
                    // Insert message
                    Messages::updateOrInsert(
                        ['recipient_id' => $a->id, 'type' => 5],
                        ['content' => $request->input('comments'), 'sender_id' => $request->input('user_id'), 'created_at' => now(), 'updated_at' => now()]
                    );

                    // Get message id
                    if ($m = Messages::where([['recipient_id', $this->id], ['type', 5]])->first()) {
                        AssignmentSubmitted::where('id', $this->id)->update(['message_id' => $m->id]);
                    } else {
                        return response()->json(['error' => 'Failed to load message'], 503);
                    }

                }
            }

            if ($stored_name) {
                $response = [
                    'success' => 'Your assignment has been submitted.',
                    'file_name' => $a->file_name,
                    'type' => $request->type,
                    'student_file_location' => $a->file_location,
                    'assignment_submitted_id' => $a->id
                ];
            } else {
                $response = [
                    'success' => 'Your assignment has been submitted.',
                    'file_name' => $a->file_name,
                    'assignment_submitted_id' => $a->id
                ];
            }
        }

        if (count($response)) {
            return response()->json($response, Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to turn in assignment.'], 503);
        }

    }

    /**
     * Replace screen shot
     *
     * @param  mixed $request
     * @return void
     */
    public function replaceScreenshot(Request $request) {
        if ($request->isMethod('post')) {
            $a = Assignment::find($request->assignment_id);
            if (!$a || ($a->type != 2)) {
                return redirect()->back()->with('error', "Assignment ID: <strong>{$request->assignment_id}</strong> doesn't exist or isn't an assignment link.");
            }

            // Resize image
            $file = $request->file('screenshot');
            $image = \Image::make($file);
            $image->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->stream();

            // Use existing image name if replacing, otherwise generate file name and save to db
            if (preg_match('/(\d{4})\/(\S*$)/', $a->file_screenshot, $matches)) {
                $name = $matches[1] . '/' . $matches[2];
            } else {
                $name = date('Y') . '/' . time() . \Str::random(5) . '.' . $file->getClientOriginalExtension();
                $a->file_screenshot = $name;
                $a->save();
            }

            // Store and optimize image
            $file_location = 'assignments/' . $name;
            $success = \Storage::disk('uploads')->put($file_location, $image->__toString());
            $path = \Storage::disk('uploads')->path($file_location);
            ImageOptimizer::optimize($path);

            if ($success) {
                if (auth()->user()->hasAdminPermission()) {
                    self::sendScreenShotReplacementEmail($a);
                }
                return redirect()->back()->with('success', "Screenshot for assignment named <strong>{$a->assignment_name}</strong> (ID: {$a->id}) has been replaced.");
            } else {
                return redirect()->back()->with('error', 'Something went wrong.');
            }

        } else {
            return view('pages.replacescreenshot');
        }
    }

    /**
     * Get newly generated screenshot
     *
     * @param  int|string $id
     * @return void
     */
    public function getScreenshot(int $id) {
        $a = Assignment::find($id);
        if ($a->file_screenshot) {
            return response()->json([
                'success' => 'Screenshot generated',
                'id' => $a->id,
                'file_screenshot' => $a->file_screenshot
            ], 200);
        }
    }

    /**
     * Teacher's select of class to view inserted assignments
     *
     * @param  mixed $request
     * @return Redirect
     */
    public function selectAssignmentInsert(Request $request) {
        UserSessionData::put(auth()->user()->id, 'class_id', intval($request->class_id));
        return redirect()->back();
    }

    /**
     * sendReplacementEmail
     *
     * @param  boject $a
     * @return void
     */
    private static function sendScreenShotReplacementEmail(Assignment $a) {
        $teacher = User::find($a->user_id);
        Mail::to($teacher->email)
            ->bcc(config('mail.admin_notification'))
            ->send(new SendScreenShotReplacedEmail($teacher->first_name, $a->assignment_name, $a->file_screenshot));
    }
}
