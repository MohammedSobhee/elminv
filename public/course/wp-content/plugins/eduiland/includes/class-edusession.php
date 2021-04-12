<?php
if (!defined('ABSPATH')) exit;

/* ================================================
 *
 * eduiland session
 * ROLES:
 *  1    developer
 *  2    manager
 *  3    teacher
 *  4    student
 *  5    admin
 *  6    school-admin
 *  7    assistant-teacher

 * CLASS TYPES:
 *  1    K-3     Elementary
 *  2    4-5     Elementary
 *  3    6-8     Middle School
 *  4    9-12+   High School
 * ================================================*/

class EduSession {
    private $db;
    private $currentURL;
    public $user;
    public $announcement;

    public function __construct($db) {
        $this->db = $db;
        $this->init();
    }
    /**
     * init
     *
     * @return void
     */
    public function init() {
        $this->currentURL = $_SERVER['REQUEST_URI'];
        $this->validateUser();
        $this->checkCourseSelect();
        $this->prepUserData();
        $this->checkAdmin();
    }


    /**
     * Validate user
     *
     * @return void
     */
    private function validateUser() {
        // wp_log('validate ' . $_SERVER['SCRIPT_FILENAME']);
        if (isset($_COOKIE['tgui'])) {

            $results = $this->db->get_row("SELECT * FROM users_session_data WHERE hash = " . "'" . $_COOKIE['tgui'] . "'");

            if ($results) {
                $this->announcement = (array) json_decode($results->announcement);
                $this->user = json_decode($results->user_data);
            }
        }

        // Redirect to logout if session userdata not set
        if ((!isset( $this->user)) || !$this->user) {
            wp_safe_redirect('/wp-userdata-logout');
            exit;
        }

        // If school is deactivated, redirect to dashboard
        if (! $this->user->school_status) {
            wp_safe_redirect('/dashboard');
            exit;
        }
    }



    /**
     * Prep user data
     *
     * @return void
     */
    private function prepUserData() {
        // Prep user data
        $this->user->settings = (array) $this->user->settings;
        $this->user->courseware_types = (array) $this->user->courseware_types;

        // Temp nickname check
        $this->user->nickname ??= '';
        $this->user->avatar ??= '';

        // Temp standards check
        $this->user->standards ??= '';

        // Demo check
        $this->user->demo ??= 0;
    }


    /**
     * Check if a non admin accesses wp-admin
     *
     * @return void
     */
    private function checkAdmin() {
        if (strpos($this->currentURL, 'wp-admin') == false || strpos($this->currentURL, 'wp-admin/admin-ajax.php') !== false)
            return;

        // Redirect away from wp-admin
        if (!in_array($this->user->role, ['admin', 'manager', 'developer'])) {
            wp_safe_redirect('/404');
            exit;
        }

        // Login and redirect to desired wp-admin page if laravel administrator
        preg_match('/wp-admin\/(.+)([\&|\?].*$)/', urldecode($this->currentURL), $matches); // Just get edit.php, etc
        $page = $matches[1] ?? '';
        add_filter('authenticate', function () use ($page) {
            $u = get_user_by('email', $this->user->email);
            if ($u) {
                wp_set_current_user($u->ID, $u->data->user_login);
                wp_set_auth_cookie($u->ID);
                do_action('wp_login', $u->data->user_login);
                wp_safe_redirect(admin_url($page));
                exit;
            }
        }, 3, 10);
    }

    /**
     * Check for course select and assign selected course to session
     *
     * @param  mixed $usr
     * @return void
     */
    private function checkCourseSelect() {
        if (isset($_GET['ct'])) {
            $this->user->class_type = intval($_GET['ct']);
            $this->db->update('users_session_data', ['user_data' => json_encode($this->user)], ['hash' => $_COOKIE['tgui']]);
        }
    }
}
