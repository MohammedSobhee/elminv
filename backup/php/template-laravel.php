<?php
//
// Laravel
// --------------------------------------------------------------------------

// ROLES:
// 1	developer
// 2	manager
// 3	teacher
// 4	student
// 5	admin
// 6	school-admin
// 7    assistant-teacher

// CLASS TYPES:
// 1    K-3     Elementary
// 2    4-5     Elementary
// 3    6-8     Middle School
// 4    9-12+   High School

add_action('init', 'start_edu_session', 1);
function start_edu_session() {
    global $edu_userdata;
    global $edu_announcement;
    // Prevent undefined index error
    $edu_userdata = [
        'role' => '',
        'school_id' => 0,
        'firstname' => '',
        'nickname' => '',
        'avatar' => '',
        'email' => '',
        'class_type' => 0,
        'class_types' => [],
        'class_id' => 0,
        'class_types' => [],
        'team_check' => 0,
        'school_admin' => 0,
        'settings' => [],
        'loggedinas' => [],
        'worksheets' => 0,
        'courseware_types' => [],
        'environment' => '',
        'standards' => '',
        'demo' => 0
    ];

    // Get laravel data
    if(isset($_COOKIE['tgui'])) {
        global $edudb;
        $edudb = new wpdb(COOKIE_DB_USER, COOKIE_DB_PASSWORD, COOKIE_DB, DB_HOST);

        $results = $edudb->get_row("SELECT * FROM users_session_data WHERE hash = " . "'" . $_COOKIE['tgui'] . "'", ARRAY_A);

        if($results) {
            $edu_announcement = json_decode($results['announcement']);
            $ud = (array) json_decode($results['user_data']);
        }
    }

    // Get current URL
    global $current_url;
    $current_url = $_SERVER['REQUEST_URI'];

    if((!isset($ud)) && is_wordpress()) {
        // Redirect to logout if session userdata not set
        wp_safe_redirect( '/wp-userdata-logout' );
        exit;
    } else if(isset($ud) && (!count($ud) || $ud == null) && is_wordpress()) {
        // Artisan command:deleteusersessiondata used. Send friendly message.
        wp_safe_redirect('/wp-userdata-logout');
        exit;
    } else if(!isset($ud) || !count($ud) || $ud == null) {
        setcookie('tgui', '', time() - 3600);
    } else {

        // If school is deactivated, redirect to dashboard
        if(!$ud['school_status']) {
            wp_safe_redirect('/dashboard');
        }

        // Check for course select and assign selected course to session
        if(isset($_GET['ct'])) {
           $ud['class_type'] = intval($_GET['ct']);
           $edudb->update('users_session_data', ['user_data' => json_encode($ud)], ['hash' => $_COOKIE['tgui']]);
        }

        // Set session data to global
        $ud['settings'] = (array) $ud['settings'];
        $ud['courseware_types'] = (array) $ud['courseware_types'];
        $edu_userdata = $ud;

        // Temp nickname check
        $edu_userdata['nickname'] ??='';
        $edu_userdata['avatar'] ??='';

        // Temp standards check
        $edu_userdata['standards'] ??= '';

        // Demo check
        $edu_userdata['demo'] ??= 0;

        // Check if a non admin or developer accesses wp-admin
        if (strstr($current_url, 'wp-admin') && !strstr($current_url, 'wp-admin/admin-ajax.php')) {
            // Redirect away from wp-admin
            if(!is_lara_admin()) {
                session_start();
                $_SESSION['wp_error'] = 'You are not logged in as an admin. You\'re logged in as a ' . $edu_userdata['role_name'] . '.';
                wp_safe_redirect('/404');
                exit;

            // Redirect to wp dashboard if laravel administrator
            } else {
                add_filter('authenticate', function() use ($edu_userdata, $current_url) {
                    $u = get_user_by('email', $edu_userdata['email']);
                    if ($u) {
                        wp_set_current_user($u->ID, $u->data->user_login);
                        wp_set_auth_cookie($u->ID);
                        do_action('wp_login', $u->data->user_login);
                        $page = strstr($current_url, 'edit.php') ? 'edit.php' : '';
                        wp_safe_redirect(admin_url($page));
                        exit;
                    }
                }, 3, 10);
            }
        }
    }
}

//
// Redirect if /course/
// --------------------------------------------------------------------------
add_action('template_redirect', 'edu_home_redirect' );
function edu_home_redirect() {
    if(is_front_page()) {
        wp_redirect('/dashboard', 301);
    }
}

//
// Get Main and Sub Menus
// --------------------------------------------------------------------------
add_filter( 'wp_nav_menu_args', 'eduiland_get_menu' );
function eduiland_get_menu( $args ) {
    global $edu_userdata;

    //------ Header Menu
    if( $args['theme_location'] == 'header-menu' ) {
        switch( $edu_userdata['class_type'] ) {
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
add_filter('wp_nav_menu_items', 'add_other_pages', 10, 2);
function add_other_pages($items, $args) {
    if ($args->theme_location == 'header-menu') {
        $items = '<li class="menu-item"><a href="' . get_homelink() . '" class="dropdown-item">Get Started</a></li>' . $items;
    }
    return $items;
}

//
// Get Child Pages
// --------------------------------------------------------------------------
function get_child_pages() {
    /**
    * $usersess used for remembering these page lists once back at Laravel
    * @var string|object $post
    */
    global $post;
    global $edu_userdata;
    global $edudb;

    // Excludes
    $page_excludes = str_replace(' ', '', rtrim(get_option('eduiland_sidebar_excludes'), ','));
    $page_excludes .= ',7462,921'; // Resources pages

    // Exclude Chipper / Bug Report for em/hs
    if($edu_userdata['role'] != 'admin') {
        if($edu_userdata['class_type'] > 2) {
            $page_excludes .= ',7464'; // Hide Chipper (EM ONLY)
        } else {
            $page_excludes .= ',7466'; // Hide Bug Report (HS ONLY)
        }
    }

    $page_excludes_array = explode(',', $page_excludes);

    // Initial wp_list_pages arguments array
    $args = [
        'depth' => 1,
        'title_li' => null,
        'exclude' => $page_excludes,
        'sort_column' => 'menu_order'
    ];

    if( (get_current_page_depth() == 1 && !in_array($post->post_parent, $page_excludes_array)) || is_page('hs') || is_page('em') || is_page('ms') || is_page('student-resources') || is_page('teacher-resources')) {
        // Show / set menu header for parent pages
        if( is_page('hs') || is_page('em') || is_page('ms') ) {
            echo '<h3 class="sidebar-header"><span>Get Started</span></h3><ul class="sidebar-menu">';
            $usersess['parent_page'] = 'Get Started';

        } else {
            echo '<h3 class="sidebar-header"><a href='.get_the_permalink() . '>' . get_the_title() . '</a></h3><ul id="sidebar-courseware" class="sidebar-menu">';
            $usersess['parent_page'] = (object) [
                'title' => get_the_title(),
                'link' => get_the_permalink()
            ];
        }

        // Arguments for parent pages
        $args = array_merge($args, [
            'child_of' => $post->ID,
            'parent' => $post->ID,
        ]);


    } else {
        // Show / set menu header for child pages
        echo '<h3 class="sidebar-header"><a href='.get_the_permalink($post->post_parent) . '>' . get_the_title($post->post_parent) . '</a></h3><ul id="sidebar-courseware" class="sidebar-menu">';
        $usersess['parent_page'] = (object) [
            'title' => get_the_title($post->post_parent),
            'link' => get_the_permalink($post->post_parent)
        ];

        // Arguments for child pages
        $args = array_merge($args, [
            'child_of' => $post->post_parent,
        ]);

    }

    // Add page last visited and ul list of pages to user session array
    $usersess['page_last_visited'] = $post->ID;
    ob_start();
    wp_list_pages($args);
    $usersess['page_list'] = ob_get_clean();

    // Remove all links except intro/history, all overviews, 9 step step 1 if demo school
    if($edu_userdata['demo'] && get_current_page_depth() > 0) {
        $usersess['page_list'] = preg_replace(
            '/href="((?!.*step-1|.*a-setting|.*history-of-innovation|.*4-script-storming|.*number-1|.*influences|.*success-qualities|.*character-traits).+)"/', '
            href="#" data-toggle="tooltip" title="Disabled for demo"',
            $usersess['page_list']
        );
    }

    echo $usersess['page_list'];
    echo '</ul>';

    // Update db session if not on resource pages
    if(false === array_search(true, array_map('is_tree', $page_excludes_array))) {
        $usersess['page_list'] = str_remove_host($usersess['page_list']);
        if(is_object($usersess['parent_page'])) {
            $usersess['parent_page']->link = str_remove_host($usersess['parent_page']->link);
        }

        $edudb->update('users_session_data', [
                'page_list' => json_encode($usersess['page_list']),
                'parent_page' => json_encode($usersess['parent_page']),
                'page_last_visited' => $usersess['page_last_visited']
            ], ['hash' => $_COOKIE['tgui']
        ]);
    }
}

//
// Only include a user's class pages in search
// --------------------------------------------------------------------------
add_filter( 'pre_get_posts','edu_search_filter' );
function edu_search_filter( $query ) {
    global $edu_userdata;
    if ( $query->is_search ) {
        if ( !is_user_logged_in() ) {

            // Assign parent based on class type
            switch($edu_userdata['class_type']) {
                case 4: // highschool
                    $parent = 16;
                    break;
                case 3: // middleschool
                    $parent = 16;
                    break;
                case 2: // elementary
                    $parent = 934;
                    break;
                case 1: // elementary
                    $parent = 934;
                    break;
                default:
                    $parent = 16;
                    break;
            }

            // Get children of parent
            $children_data = get_pages( array(
                'child_of' => $parent,
                'post_type' => 'page'
            ) );

            // Assign parent and children ids to include array
            $include = array( $parent );
            foreach( $children_data as $child ) {
                array_push( $include, $child->ID );
            }

            // Remove resources for students
            if($edu_userdata['role'] == 'student') {
                $option_excludes = str_replace(' ', '', rtrim(get_option('eduiland_sidebar_excludes'), ','));
                $page_excludes = explode(', ', $option_excludes);
                $page_excludes_children = get_children_page_ids($page_excludes);
                $not_in = array_merge($page_excludes, $page_excludes_children);
                $include = array_diff($include, $not_in);
            }

            // Temporarily exclude resource children for teachers while they are under development
            if($edu_userdata['role'] != 'student') {
                $option_excludes = str_replace(' ', '', rtrim(get_option('eduiland_sidebar_excludes'), ','));
                $page_excludes = explode(', ', $option_excludes);
                $not_in = get_children_page_ids($page_excludes);
                $include = array_diff($include, $not_in);
            }

            $query->set('post__in', $include);


        }
    }
    return $query;
}


//
// Get Home Link
// --------------------------------------------------------------------------
function get_homelink() {
    global $edu_userdata;
    switch ($edu_userdata['class_type']) {
        case 4: // highschool
            $home = '/course/hs';
            break;
        case 3: // middlechool
            $home = '/course/hs'; //'/course/ms';
            break;
        case 2: // elementary
            $home = '/course/em';
            break;
        case 1: // elementary
            $home = '/course/em';
            break;
        default:
            $home = '/dashboard';
    }
    return $home;
}

//
// Get assignment insert url
// --------------------------------------------------------------------------
function insert_assignment_url($asgmt) {
    global $edu_userdata;

    if($edu_userdata['role'] == 'student') {
        return '/assignments/view/' . $asgmt->assignment_id . '/' . $asgmt->category_id;
    } else {
        return '/edit/assignments/' . $asgmt->assignment_id . '/view';
    }
}

//
// Check if inside Wordpress course pages
// --------------------------------------------------------------------------
function is_wordpress() {
    global $current_url;
    return strstr($current_url, 'course');
}

//
// Is Laravel Admin
// --------------------------------------------------------------------------
function is_lara_admin() {
    global $edu_userdata;
    return in_array($edu_userdata['role'], ['admin', 'manager', 'developer']);
}

//
// Unset 'no-cache' to fix profile avatars not being cached
// --------------------------------------------------------------------------
add_filter('wp_headers', 'eduiland_nocache');
function eduiland_nocache($headers) {
    unset($headers['Cache-Control']);
    return $headers;
}


//
// Detect removal and publishing of announcements
// --------------------------------------------------------------------------
add_action('transition_post_status', 'announcement_transition', 10, 3);
// Announcement transition
function announcement_transition($new_status, $old_status, $post) {
    if (
        ($old_status == $new_status) ||
        ($post->post_type != 'post') ||
        (in_array($old_status, ['new', 'draft', 'auto-draft']) && strstr($new_status, 'draft')) ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    ) {
        return;
    }
    // Remove from announcement
    if ($old_status == 'publish' && $new_status != 'publish') {
        announcement_remove($post->ID);
    // Add announcement
    } else if (($old_status != 'publish' && $new_status == 'publish')) {
        announcement_add($post->ID);
    }
}

//
// Update users_session_data when a new post is published
// --------------------------------------------------------------------------
function announcement_add($post_id) {
    global $edudb;
    $cat = get_the_category($post_id);

    // All class types for all but student (empty courseware_types)
    if (array_search('all', array_column($cat, 'slug')) !== false) {
        $edudb->query($edudb->prepare('UPDATE users_session_data
            SET announcement = COALESCE(JSON_ARRAY_APPEND(announcement, "$", %s), JSON_ARRAY(%s))
            WHERE JSON_VALID(user_data)
            AND JSON_LENGTH(JSON_EXTRACT(user_data, "$.class_types")) > 0
            AND JSON_SEARCH(announcement, "one", %s) IS NULL',
            $post_id, $post_id, $post_id
        ));
    }

    // High School
    else if (array_search('high-school', array_column($cat, 'slug')) !== false) {
        $edudb->query($edudb->prepare('UPDATE users_session_data
            SET announcement = COALESCE(JSON_ARRAY_APPEND(announcement, "$", %s), JSON_ARRAY(%s))
            WHERE JSON_VALID(user_data)
            AND (JSON_CONTAINS(JSON_EXTRACT(user_data, "$.class_types[*]"), %s)
            OR JSON_CONTAINS(JSON_EXTRACT(user_data, "$.class_types[*]"), %s))
            AND JSON_SEARCH(announcement, "one", %s) IS NULL',
            $post_id, $post_id, 3, 4, $post_id
        ));
    }

    // Elementary
    else if (array_search('elementary', array_column($cat, 'slug')) !== false) {
        $edudb->query($edudb->prepare('UPDATE users_session_data
            SET announcement = COALESCE(JSON_ARRAY_APPEND(announcement, "$", %s), JSON_ARRAY(%s))
            WHERE JSON_VALID(user_data)
            AND (JSON_CONTAINS(JSON_EXTRACT(user_data, "$.class_types[*]"), %s)
            OR JSON_CONTAINS(JSON_EXTRACT(user_data, "$.class_types[*]"), %s))
            AND JSON_SEARCH(announcement, "one", %s) IS NULL',
            $post_id, $post_id, 1, 2, $post_id
        ));
    }

    wp_log('A new post (' . $post_id . ') has been published for ' . $cat[0]->name);
}

//
// Remove announcement
// --------------------------------------------------------------------------
function announcement_remove($post_id) {
    global $edudb;

    $edudb->query($edudb->prepare('UPDATE users_session_data
        SET announcement = JSON_REMOVE(announcement, JSON_UNQUOTE(JSON_SEARCH(announcement, "one", %s)))
        WHERE json_search(announcement, "one", %s) IS NOT NULL',
        $post_id,
        $post_id
    ));

    $edudb->query('UPDATE users_session_data
        SET announcement = NULL
        WHERE JSON_VALID(user_data)
        AND JSON_LENGTH(announcement) < 1',
    );

    wp_log('A post (' . $post_id . ') has been removed.');
}

//
// Announcement Filter
// --------------------------------------------------------------------------
add_filter('pre_get_posts', 'announcement_exclude_category');
function announcement_exclude_category($query) {
    if ($query->is_home) {
        global $edu_userdata;
        if (count($edu_userdata['courseware_types']) >= 3) {
            return;
        }

        $excludes = [];
        $ctypes = $edu_userdata['class_types'];
        // Exclude High School if only elementary
        if (!in_array(3, $ctypes) && !in_array(4, $ctypes)) {
            $excludes[] = 'high-school';
        // Exclude Elementary if only high school
        } else if (!in_array(1, $ctypes) && !in_array(2, $ctypes)) {
            $excludes[] = 'elementary';
        }
        if (count($excludes)) {
            $query->set('tax_query', [
                [
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => $excludes,
                    'operator' => 'NOT IN'
                ]
            ]);
            return $query;
        }
    }
}

//
// Set announcement to 0 (mark as read)
// --------------------------------------------------------------------------
add_action('template_redirect', 'announcement_mark_read');
function announcement_mark_read() {
    global $edudb;
    global $edu_announcement;
    if (is_posts() && $edu_announcement) {
        $edudb->update('users_session_data', ['announcement' => NULL], ['hash' => $_COOKIE['tgui']]);
    }
}
