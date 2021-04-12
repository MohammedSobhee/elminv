<?php
if (!defined('ABSPATH')) exit;


class EduSetup {
    private $user;

    public function __construct($user) {
        add_action('template_redirect', [$this,'home_redirect']);
        add_filter('wp_nav_menu_args', [$this,'get_menu']);
        add_filter('wp_nav_menu_items', [$this, 'add_other_pages'], 10, 2);
        add_filter('pre_get_posts', [$this, 'search_filter']);
        add_filter('wp_headers', [$this, 'nocache']);
        add_action('widgets_init', [$this, 'eduiland_widgets']);

        $this->user = $user;
    }

    /**
     * Redirect if /course/
     *
     * @return redirect
     */
    function home_redirect() {
        if (is_front_page() && !is_customize_preview()) {
            wp_redirect('/dashboard', 301);
            exit;
        }
    }


    /**
     * Get Main and Sub Menus
     *
     * @param  array $args
     * @return array
     */
    function get_menu($args) {
        //------ Header Menu
        if ($args['theme_location'] == 'header-menu') {
            switch ($this->user->class_type) {
                case 4: // highschool
                    $args['menu'] = 'main-menu-student-high-school';
                    break;
                case 3: // middleschool
                    $args['menu'] = 'main-menu-student-high-school'; //'main-menu-student-middle-school';
                    break;
                case 2: // elementary
                    $args['menu'] = 'main-menu-student-elementary';
                    break;
                case 1: // elementary
                    $args['menu'] = 'main-menu-student-elementary';
                    break;
                default:
                    $args['menu'] = 'blank-menu';
            }
        }

        return $args;
    }

    //
    // Add Get Started to Header menu
    // --------------------------------------------------------------------------

    function add_other_pages($items, $args) {
        if ($args->theme_location == 'header-menu') {
            $items = '<li class="menu-item'. EduHelpers::getHomelink()->css .'"><a href="' . EduHelpers::getHomelink()->link . '" class="dropdown-item">Get Started</a></li>' . $items;
        }
        return $items;
    }

    //
    // Only include a user's class pages in search
    // --------------------------------------------------------------------------
    function search_filter($query) {
        if (!$query->is_search || EduHelpers::isLaraAdmin()) {
            return $query;
        }

        // Assign parent based on class type
        $parent = $this->user->class_type > 2 ? Edu::instance()->hsParentID : Edu::instance()->emParentID;

        // Assign course page parent and children ids to include array;
        $include = [$parent];
        array_push($include, ...EduHelpers::getChildrenPageIds($include));

        // Include resources and their children
        $resources_ids[] = get_pageid_by_slug('student-resources');
        if ($this->user->role != 'student') {
            $resources_ids[] = get_pageid_by_slug('teacher-resources');
        }
        array_push($include, ...$resources_ids);
        array_push($include, ...EduHelpers::getChildrenPageIds($resources_ids));

        $query->set('post__in', $include);

        return $query;
    }

    /**
     * Register widgets
     *
     * @return void
     */
    public function eduiland_widgets() {
        register_widget('EduSideBarNavWidget');
    }

    /**
     * Unset 'no-cache' to fix profile avatars not being cached
     *
     * @param  array $headers
     * @return array
     */
    public function nocache($headers) {
        unset($headers['Cache-Control']);
        return $headers;
    }
}
