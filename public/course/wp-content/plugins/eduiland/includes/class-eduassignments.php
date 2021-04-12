
<?php
if (!defined('ABSPATH')) exit;

require __DIR__ . '/../../../../../../vendor/autoload.php';
use Carbon\Carbon;

class EduAssignments {

    private $user;
    private $db;
    private $class_id;
    private $class_ids;
    private $classes;
    private $class_types;
    private $assignments;
    private $has_assignments;

    public function __construct() {
        $this->user = Edu::instance()->session->user;
        $this->db = Edu::instance()->db;
    }

    /**
     * Show inserted assignments
     *
     * @param  object $db
     * @param  object $user
     * @return void
     */
    public function init() {
        $user = $this->user;

        // Check if teacher is using custom assignments
        if(
            !in_array($user->role, ['student', 'teacher', 'assistant-teacher']) &&
            !$this->db->get_row("SELECT 1 FROM assignment WHERE user_id = $user->teacher_id LIMIT 1")
        ) return;

        // Student
        if($user->role == 'student')
            $this->assignments = $this->getStudentAssignments();

        // Teacher - First check if teacher has classes
        if(in_array($user->role, ['teacher', 'assistant-teacher']) && $class_ids = $user->class_ids) {
            if(!$class_ids) return;

            $this->class_ids = implode(',', $class_ids);
            $class_types = $user->class_type < 2 ? [1, 2] : [3, 4];
            $this->class_types = implode(',', $class_types);

            // Check if page has ANY inserted assignments regardless of class
            $this->has_assignments = $this->checkForAssignments();

            // Get classes (for select input) associated with these inserted assignments
            $this->classes = $this->getAssignmentClasses();

            // Update session with selected class or if there is only 1 class and isn't the one in session
            $this->class_id = $this->updateClassId();

            // Get associated assignments for selected class
            $this->assignments = $this->getAssignments();
        }

        $this->showAssignments();
    }



    /**
     * Get student assignments
     *
     * @param  mixed $db
     * @param  object $user
     * @return array
     */
    private function getStudentAssignments() {
        /*** @var string|object $post */
        global $post;
        $class_id = $this->user->class_id;
        $user_id = $this->user->user_id;
        return $this->db->get_results($this->db->prepare(
            "SELECT assignment_id, category_id, assignment_name, due_date
            FROM assignment_classes ac
            INNER JOIN assignment a ON ac.assignment_id = a.id
            LEFT JOIN assignment_submitted asub ON ac.assignment_id = asub.type_id
            AND asub.user_id = $user_id
            AND asub.type = 2
            LEFT JOIN assignment_due ad ON ad.class_id = $class_id
            AND ad.type_id = a.id
            WHERE ac.class_id = $class_id
            AND ac.insert_status = 1
            AND a.status = 1
            AND asub.status IS NULL
            AND course_pages LIKE %s
            ORDER BY -due_date DESC",
            '%' . $this->db->esc_like($post->ID) . '%')
        );
    }

    /**
     * checkForAssignments
     *
     * @param  object $db
     * @param  array $class_ids
     * @param  string $class_types
     * @return array
     */
    private function checkForAssignments() {
        /*** @var string|object $post */
        global $post;
        $class_ids = $this->class_ids;
        $class_types = $this->class_types;
        return $this->db->get_results($this->db->prepare(
            "SELECT assignment_id, ac.class_id
            FROM assignment_classes ac
            INNER JOIN assignment a ON ac.assignment_id = a.id
            LEFT JOIN class c ON ac.class_id = c.id
            WHERE ac.class_id IN ($class_ids)
            AND c.class_type IN ($class_types)
            AND ac.insert_status = 1
            AND a.status = 1
            AND course_pages LIKE %s",
            '%' . $this->db->esc_like($post->ID) . '%')
        );
    }

    /**
     * Get assignment's associated classes
     *
     * @param  array $this->has_assignments
     * @return array
     */
    private function getAssignmentClasses()  {
        if(empty($this->has_assignments))
            return [];

        $ac_ids = array_map(fn($c) => $c->class_id, $this->has_assignments); // Array of ids
        $ac_ids = implode(',', $ac_ids);
        return $this->db->get_results(
            "SELECT id, class_name
            FROM class c
            WHERE c.id IN ($ac_ids)
            ORDER BY c.class_name"
        );
    }

    /**
     * Update selected (or default) class id
     *
     * @param  array $classes
     * @return int
     */
    private function updateClassId() {
        $class_id = $this->user->class_id;
        $db = $this->db;
        $classes = $this->classes;
        if ((count($classes) == 1 && $class_id != $classes[0]->id) || isset($_REQUEST['class_id'])) {
            $class_id = $_REQUEST['class_id'] ?? $classes[0]->id;
            $ud = (array) $this->user;
            $ud['class_id'] = intval($class_id);
            $db->update('users_session_data', ['user_data' => json_encode($ud)], ['hash' => $_COOKIE['tgui']]);
        }
        return $class_id;
    }

    /**
     * Get assignments
     *
     * @param  object $db
     * @param  object $user
     * @param  string $class_types
     * @return void
     */
    private function getAssignments() {
        if(!$this->class_id)
            return [];

        /*** @var string|object $post */
        global $post;
        $db = $this->db;
        $class_id = $this->class_id;
        $class_types = $this->class_types;
        return $db->get_results($db->prepare(
            "SELECT assignment_id, category_id, assignment_name, due_date, class_name
            FROM assignment_classes ac
            INNER JOIN assignment a ON ac.assignment_id = a.id
            LEFT JOIN class c ON ac.class_id = c.id
            LEFT JOIN assignment_due ad ON ad.class_id = ac.class_id
            AND ad.type_id = a.id
            WHERE ac.class_id = $class_id
            AND c.class_type IN ($class_types)
            AND ac.insert_status = 1
            AND a.status = 1
            AND course_pages LIKE %s
            ORDER BY -a.updated_at DESC",
            '%' . $db->esc_like($post->ID) . '%')
        );
    }

    /**
     * Show Assignments
     *
     * @return void
     */
    private function showAssignments() {
        // If assignments available
        if(!empty($this->assignments) || !empty($this->has_assignments)) {
            $this->showHeader(); ?>
            <ul id="sidebar-assignments" class="sidebar-menu sidebar-menu-assignments">
            <?php
            if(!empty($this->has_assignments))
                $this->showForm();
            if(!empty($this->assignments))
                $this->showLinks();
            ?>
            </ul>
        <?php
        }
    }

    /**
     * Show Header
     *
     * @return void
     */
    private function showHeader() {
        if (in_array($this->user->role, ['teacher', 'assistant-teacher'])): ?>
            <h3 id="sidebar-header-assignments" class="sidebar-header sidebar-header-assignments" data-toggle="tooltip" title="These are the assignments selected to be inserted into this page. They are removed from a student's sidebar once completed.">
            <?php else: ?>
            <h3 id="sidebar-header-assignments"class="sidebar-header sidebar-header-assignments">
            <?php endif?>
                <a href="/assignments"><i class="fas fa-edit mr-2"></i>Additional Assignments</a>
            </h3>
        <?php
    }

    /**
     * Show select class form
     *
     * @param  mixed $this->assignments
     * @param  mixed $classes
     * @param  mixed $class_id
     * @return void
     */
    private function showForm() {
        $class_id = $this->class_id;
        $classes = $this->classes;
        ?>
        <li>
            <div  class="sidebar-select">
            <?php echo count($this->assignments) ? 'Viewing' : 'View' ?> inserted assignments for:
            <?php
            if(count($classes) > 1): ?>
            <form method="post" class="select-submit">
                <select name="class_id" class="custom-select custom-select-sm custom-select-sidebar custom-select-light-arrow">
                    <?php if(!$class_id || !count($this->assignments)): ?><option>Select a class</option><?php endif; ?>
                    <?php foreach ($classes as $cls): ?>
                    <option <?php echo $cls->id == $class_id ? 'selected ' : ''?>value="<?php echo $cls->id ?>"><?php echo $cls->class_name ?></option>
                    <?php endforeach;?>
                </select>
                <noscript><input type="submit" class="btn btn-success" value="Select"></noscript>
            </form>
            <?php else: ?>
            <span class="text-dark-secondary"><?php echo $classes[0]->class_name ?></span>
            <?php endif; ?>
            </div>
        </li>
        <?php
    }

    /**
     * Show links to assignments
     *
     * @param  mixed $this->assignments
     * @return void
     */
    private function showLinks() {
        foreach($this->assignments as $asgmt): ?>
            <li>
                <a href="<?php echo $this->insertAssignmentUrl($asgmt) ?>">
                <?php
                echo $asgmt->assignment_name;
                if($asgmt->due_date):
                    $duedate = new Carbon($asgmt->due_date);
                    echo "<br><span class=\"small sidebar-duedate\">Due {$duedate->diffForHumans()}</span>";
                endif;
                ?>
                </a>
            </li>
        <?php
        endforeach;
    }

    /**
     * Get assignment insert url
     *
     * @param  object $user
     * @param  object $asgmt
     * @return string
     */
    private function insertAssignmentUrl(object $asgmt) {
        if ($this->user->role == 'student') {
            return '/assignments/view/' . $asgmt->assignment_id . '/' . $asgmt->category_id;
        } else {
            return '/edit/assignments/' . $asgmt->assignment_id . '/view';
        }
    }
}
