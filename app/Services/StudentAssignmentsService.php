<?php

namespace App\Services;

// Models
use App\User;
use App\TeamMembers;
use App\ProjectMembers;
use App\Worksheet;
use App\Assignment;
use App\Projects;
use App\RubricSettings;
use App\Classes;
use App\UserSessionData;
use Carbon\Carbon;

class StudentAssignmentsService {

    /**
     * Get inserted pages (currently dashboard only on laravel side)
     *
     * @return object
     */
    public static function getAssignmentsInsert() {
        if (
            !in_array(auth()->user()->role->slug, ['student', 'teacher', 'assistant-teacher']) &&
            !Assignment::where('user_id', auth()->user()->getTeacherID())->count()
        ) {
            return;
        }

        $routeName = \Route::currentRouteName() ?: 'dashboard';

        $assignments = [];
        $classes = [];
        $has_assignments = [];

        // Student
        if(auth()->user()->role->slug === 'student') {
            $assignments = Assignment::join('assignment_classes as ac', function($join) {
                    $join->on('ac.assignment_id', '=', 'assignment.id')
                    ->where('ac.class_id', auth()->user()->getClassID())
                    ->whereNull('ac.deleted_at');
                })
                ->leftJoin('assignment_submitted as asub', function ($join) {
                    $join->on('asub.type_id', '=', 'assignment.id')
                        ->where('asub.type', 2)
                        ->where('asub.user_id', auth()->user()->id)
                        ->whereNull('asub.deleted_at');
                })
                ->leftJoin('assignment_due as ad', function ($join) {
                    $join->on('ad.type_id', '=', 'ac.assignment_id')
                        ->where('ad.class_id', '=', auth()->user()->getClassID())
                        ->whereNull('ad.deleted_at');
                })
                ->where(['ac.insert_status' => 1, 'ac.status' => 1])
                ->where('ac.course_pages', 'LIKE', '%' . $routeName . '%')
                ->whereNull('asub.status')
                ->select('ac.assignment_id', 'category_id', 'assignment_name', 'ad.due_date')
                ->groupBy('assignment.id')
                ->orderByRaw('-due_date DESC')
                ->get();

        // Teacher / Assistant
        } else {
            $usd = UserSessionData::get();
            $class_id = $usd->user_data->class_id;
            $class_types = $usd->user_data->class_type < 3 ? [1, 2] : [3, 4];

            // First check if page has ANY inserted assignments regardless of class
            $has_assignments = Assignment::join('assignment_classes as ac', function ($join) {
                $join->on('ac.assignment_id', '=', 'assignment.id')
                    ->whereIn('ac.class_id', auth()->user()->getClassID())
                    ->whereNull('ac.deleted_at');
            })
            ->join('class as c', function ($join) use ($class_types) {
                $join->on('c.id', '=', 'ac.class_id')
                    ->whereIn('ac.class_id', auth()->user()->getClassID())
                    ->whereIn('c.class_type', $class_types)
                    ->whereNull('c.deleted_at');
            })
            ->where(['ac.insert_status' => 1, 'ac.status' => 1])
            ->where('ac.course_pages', 'LIKE', '%' . $routeName . '%')
            ->select('ac.assignment_id', 'c.id as class_id')
            ->get();

            // Get classes (for select input) associated with these inserted assignments
            if($has_assignments->count()) {
                $ac_ids = $has_assignments->map(fn($a) => $a->class_id);
                $classes = auth()->user()->classes()->whereIn('class.id', $ac_ids)->get();
            }

            // Update session if one class
            if (count($classes) == 1 && $class_id != $classes[0]->id) {
                $class_id = $classes[0]->id;
                UserSessionData::put(auth()->user()->id, 'class_id', $classes[0]->id);
            }

            // Get associated assignments for selected class
            if($class_id) {
                $assignments = Assignment::join('assignment_classes as ac', function ($join) use($class_id) {
                    $join->on('ac.assignment_id', '=', 'assignment.id')
                        ->where('ac.class_id', $class_id)
                        ->whereNull('ac.deleted_at');
                })
                ->join('class as c', function ($join) use ($class_types) {
                    $join->on('c.id', '=', 'ac.class_id')
                    ->whereIn('c.class_type', $class_types)
                    ->whereNull('c.deleted_at');
                })
                ->leftJoin('assignment_due as ad', function ($join) use ($class_id) {
                    $join->on('ad.type_id', '=', 'ac.assignment_id')
                        ->where('ad.class_id', $class_id)
                        ->whereNull('ad.deleted_at');
                })
                ->where(['ac.insert_status' => 1, 'ac.status' => 1])
                ->where('ac.course_pages', 'LIKE', '%' . $routeName . '%')
                ->select('ac.assignment_id', 'category_id', 'assignment_name', 'ad.due_date', 'c.id as class_id')
                ->groupBy('assignment.id')
                ->orderByRaw('-due_date DESC')
                ->get();
            }
        }

        $final = (object) [
            'has_assignments' => count($has_assignments),
            'class_id' => $usd->user_data->class_id ?? 0,
            'classes' => $classes,
            'assignments' => $assignments
        ];

        return $final;
    }

    /**
     * Get all assignments and tally grade total and percentages
     *
     * @return object
     */
    public static function getAll($user_id = 0) {
        $project_locked = [];

        $projects = self::getAllWorksheets(0, $user_id);
        $customs = self::getAllCustom($user_id);

        // Get locked project
        $project_locked = $projects->filter(function ($p) {
            return $p->locked === 1;
        });

        if (count($customs)) {
            $customs->each(function ($c) use ($project_locked) {
                // Add projects' total grade / points to classwork category
                if ($c->name === 'Classwork') {
                    $c->totalGrade += count($project_locked)
                    ? self::getTotal($project_locked[0]->worksheets, 'grade')
                    : 0;
                    $c->totalPoints += count($project_locked)
                    ? self::getTotal($project_locked[0]->worksheets, 'category_value')
                    : 0;
                }

                // Add overall total percentage and total custom percentage
                // Separate overall and custom for use in UI
                $c->totalGradePercentage = self::getTotalPercentage($c->totalGrade, $c->totalPoints, $c->category_value);
                $c->totalCustomGradePercentage = self::getTotalPercentage($c->totalCustomGrade, $c->totalCustomPoints, $c->category_value);
            });
        }

        $all = [
            'projects' => $projects,
            'customs' => $customs
        ];

        return (object) $all;
    }

    /**
     * Get Recent worksheets
     *
     * @param  int $team
     * @return object
     */
    public static function getRecentWorksheets() {
        $data = collect([]);

        if (auth()->user()->role->slug !== 'student' || !$projects = self::selectProjects()) {
            return $data;
        }

        $projects = $projects->get();

        // Add worksheets to projects
        foreach ($projects as $p) {
            $worksheets = self::selectWorksheets($p);
            $worksheets = self::recentAssignmentQuery($worksheets)->get();

            $worksheets->each(function ($w) use ($p) {
                $w->team_id = $p->team_id;
                $w->project_name = $p->project_name;
                $w->project_id = $p->project_id;
            });
            $data = $data->concat($worksheets);
        }

        return self::sortRecentAssignments($data);
    }

    /**
     * Get Worksheet list (currently used for /assignments index)
     *
     * @param  int $team
     * @return object
     */
    public static function getAllWorksheets($user_id = 0) {
        if (!$projects = self::selectProjects($user_id)) {
            return $projects;
        }

        $projects = $projects->get();

        $projects->each(function ($p, $k) {
            $p->worksheets = self::selectWorksheets($p)->orderBy('worksheet.order', 'asc')->get();

            $p->worksheets->each(function ($w) {
                // Demo
                if(auth()->user()->demo && !in_array($w->category_id, config('app.demo.worksheets'))) {
                    $w->disabled = 1;
                }

                $w->gradePercentage = self::getPercentage($w->grade, $w->category_value);
            });

            $p->totalGrade = self::getTotal($p->worksheets, 'grade');
            $p->totalPoints = self::getTotal($p->worksheets, 'category_value');
            $p->gradePercentage = self::getPercentage($p->totalGrade, $p->totalPoints);

            if ($p->team_id && $k === 0) { // Add member list to first team project for use in UI
                $p->members = $p->members()->pluck('name')->toArray();
            }
        });
        return $projects;

    }

    /**
     * Get Recent custom assignments
     *
     * @return object
     */
    public static function getRecentCustom() {
        if (auth()->user()->role->slug !== 'student' || auth()->user()->getClassType() == 99) {
            return collect([]);
        }

        $assignments = self::selectCustom();
        $assignments = self::recentAssignmentQuery($assignments)->get();
        return self::sortRecentAssignments($assignments);
    }

    /**
     * Get Custom list (currently used for /assignments index)
     *
     * @return object
     */
    private static function getAllCustom($user_id = 0) {

        if (!$user_id && (auth()->user()->role->slug !== 'student' || auth()->user()->getClassType() == 99)) {
            return [];
        }

        $categories = RubricSettings::where([
            ['teacher_id', auth()->user()->getTeacherID($user_id)],
            ['rubric_settings.active', 1]
        ])
            ->join('rubric_categories', function($join) {
                $join->on('rubric_categories.id', '=', 'rubric_settings.category_id')
                    ->whereNull('rubric_categories.deleted_at');
            })
            ->select('category_name as name', 'rubric_categories.id', 'category_value')
            ->get();

        $categories->each(function ($r) use ($user_id) {
            $r->assignments = self::selectCustom($r->id, $user_id)->get();
            $r->assignments->each(function ($a) {
                $a->gradePercentage = self::getPercentage($a->grade, $a->points);
            });
            // Separate overall and custom grade / points for use in UI
            $r->totalGrade = self::getTotal($r->assignments, 'grade');
            $r->totalPoints = self::getTotal($r->assignments, 'points');
            $r->totalCustomGrade = $r->totalGrade;
            $r->totalCustomPoints = $r->totalPoints;
            $r->gradePercentage = self::getPercentage($r->totalGrade, $r->totalPoints);
        });

        return $categories;
    }

    /**
     * selectProjects
     *
     * @param  int $team
     * @return object
     */
    public static function selectProjects($user_id = 0) {
        $user = $user_id ? User::find($user_id) : auth()->user();
        return $user->projects()
            ->select('project.id as project_id', 'project.*')
            ->orderBy('project.locked', 'desc')->orderBy('project.updated_at', 'desc');
    }

    /**
     * Select Worksheets
     *
     * @param  object $p
     * @return object
     */
    private static function selectWorksheets($p) {
        $pid = $p->project_id;

        // Student select
        if (auth()->user()->role->slug === 'student') {
            $worksheets = Worksheet::select(
                'worksheet.id',
                'worksheet.title',
                'worksheet.example',
                'worksheet.order',
                'worksheet_rubric.category_value',
                'worksheet_rubric.active',
                'worksheet_rubric.category_id',
                'grades.points as grade',
                'ad.due_date',
                'messages.content as message',
                'messages.updated_at as message_updated_date',
                'grades.updated_at as grade_updated_date',
                'assignment_submitted.status',
                'assignment_submitted.updated_at as assignment_submitted_date',
                'assignment_submitted.id as assignment_submitted_id',
                \DB::raw('count(worksheet_answers.worksheet_id) as has_answers')
            );

        // Everyone else select
        } else {
            $worksheets = Worksheet::select(
                'worksheet.id',
                'worksheet.title',
                'worksheet.example',
                'worksheet.order',
                'worksheet_rubric.category_value',
                'worksheet_rubric.category_id',
                'worksheet_rubric.active',
                \DB::raw('count(worksheet_answers.worksheet_id) as has_answers')
            );
        }

        // Answer joins
        $worksheets = $worksheets->join('worksheet_rubric', 'worksheet_rubric.category_id', '=', 'worksheet.id')
            ->leftJoin('worksheet_answers', function ($join) use ($pid) {
                $join->on('worksheet_answers.worksheet_id', '=', 'worksheet_rubric.category_id')
                    ->where('worksheet_answers.project_id', '=', $pid)
                    ->whereNull('worksheet_answers.deleted_at');
            });

        // Student joins
        if (auth()->user()->role->slug === 'student') {
            $worksheets = $worksheets->leftjoin('grades', function ($join) use ($pid) {
                $join->on('grades.type_id', '=', 'worksheet_rubric.id')
                    ->where('grades.type', '=', 1)
                    ->where('grades.project_id', $pid)
                    ->whereNull('grades.deleted_at');
            })
                ->leftJoin('assignment_due as ad', function ($join) {
                    $join->on('ad.type_id', '=', 'worksheet_rubric.id')
                        ->where('ad.class_id', '=', auth()->user()->getClassID())
                        ->where('ad.type', '=', 1)
                        ->whereNull('ad.deleted_at');
                })
                ->leftJoin('assignment_submitted', function ($join) use ($pid) {
                    $join->on('assignment_submitted.type_id', '=', 'worksheet_rubric.category_id')
                        ->where('assignment_submitted.project_id', '=', $pid)
                        ;//->whereNull('assignment_submitted.deleted_at');
                })
                ->leftJoin('messages', function ($join) {
                    $join->on('grades.message_id', '=', 'messages.id');
                });
        }

        // Where active
        $worksheets = $worksheets->where('worksheet_rubric.active', '=', 1)
            ->where('worksheet_rubric.teacher_id', auth()->user()->getTeacherID())
            ->groupBy('worksheet.id');

        // Limit Demo School
        // if(auth()->user()->demo) {
        //     $worksheets = $worksheets->whereIn('worksheet_rubric.category_id', [25]); //[25, 24, 3, 4, 5]
        // }

        return $worksheets;
    }

    /**
     * Select Custom Assignments
     *
     * @param  int $category
     * @return object
     */
    private static function selectCustom($category = 0, $user_id = 0) {
        $uid = $user_id ?: auth()->user()->id;

        if ($category) {
            $a = Assignment::byClassAndCategory($category);
        } else {
            $a = Assignment::byClass();
        }

        return $a->join('assignment_classes', 'assignment_classes.assignment_id', '=', 'assignment.id')
            ->leftJoin('grades', function ($join) use ($uid) {
                $join->on('grades.type_id', '=', 'assignment.id')
                    ->where('grades.type', 2)
                    ->where('grades.user_id', $uid)
                    ->whereNull('grades.deleted_at');
            })
            ->leftJoin('assignment_due as ad', function ($join) {
                $join->on('ad.type_id', '=', 'assignment_classes.assignment_id')
                    ->where('ad.type', '=', 2)
                    ->whereNull('ad.deleted_at');
            })
            ->leftJoin('assignment_submitted', function ($join) use ($uid) {
                $join->on('assignment_submitted.type_id', '=', 'assignment.id')
                    ->where('assignment_submitted.type', 2)
                    ->where('assignment_submitted.user_id', $uid)
                    ->whereNull('assignment_submitted.deleted_at');
            })
            ->leftJoin('messages', function ($join) {
                $join->on('grades.message_id', '=', 'messages.id');
            })
            ->leftJoin('messages as m2', function ($join) {
                $join->on('assignment_submitted.message_id', '=', 'm2.id');
            })
            ->select(
                'assignment.id',
                'assignment.assignment_name as label',
                'assignment.type',
                'assignment.file_location as teacher_file_location',
                'assignment_submitted.file_location as student_file_location',
                'assignment.file_screenshot',
                'assignment_submitted.updated_at as assignment_submitted_date',
                'assignment_submitted.id as assignment_submitted_id',
                'assignment_submitted.status',
                'assignment.points',
                'ad.due_date',
                'grades.points as grade',
                'grades.updated_at as grade_updated_date',
                'messages.content as message',
                'messages.updated_at as message_updated_date',
                'm2.content as comments'
            )
            ->groupBy('assignment.id');
    }

    /**
     * Common recent
     *
     * @param  object $data
     * @return object
     */
    private static function sortRecentAssignments($data) {
        return $data->sortBy('due_date')
            ->sortBy('message_updated_date')
            ->sortBy('grade_updated_date')
            //->sortBy('assignment_submitted_date')
            ->take(5)
            ->values();
    }

    /**
     * Recent Assignments Query
     *
     * @param  object $data
     * @return object
     */
    private static function recentAssignmentQuery($data) {
        return $data->where(function ($query) {
            // Don't show assignments already submitted
            $query->whereNull('assignment_submitted.updated_at')
            // Unless they have a message from teacher
                ->orWhereNotNull('messages.content');
        })
            ->where(function ($query) {
                // Grades updated anywhere between today and 5 days ago
                return $query->where('grades.updated_at', '<=', Carbon::now()->subDays(7)->toDateTimeString())
                    ->orWhere(function ($query) {
                        // Anything due today, past due, or within 5 days from now
                        $query->where('ad.due_date', '<=', Carbon::now()->addDays(7)->toDateTimeString());
                    });
            });
    }

    //
    // Grade total Helpers
    // --------------------------------------------------------------------------

    /**
     * Get totals
     *
     * @param  object $list
     * @param  string $prop
     * @return object
     */
    public static function getTotal($list, $prop) {
        return $list->reduce(function ($total, $item) use ($prop) {
            return $total + $item->{$prop};
        });
    }

    /**
     * Get percentage
     *
     * @param  int $grade
     * @param  int $points
     * @return int
     */
    private static function getPercentage($grade, $points) {
        $percentage = 0;
        if ($grade && $points) {
            $percentage = round(($grade / $points) * 100);
        }
        return $percentage;
    }

    /**
     * Get total percentage
     *
     * @param  int $totalGrade
     * @param  int $totalPoints
     * @param  int $catValue
     * @return int
     */
    private static function getTotalPercentage($totalGrade, $totalPoints, $catValue) {
        $percentage = 0;
        if ($totalGrade) {
            $percentage = ($totalGrade / $totalPoints) * ($catValue / 100);
            return round(100 * $percentage) ?? 0;
        }
        return $percentage;
    }
}
