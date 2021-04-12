<?php
if (!defined('ABSPATH')) exit;


class EduHelpers {

    /**
     * getChildPages
     *
     * @param  mixed $db
     * @param  mixed $user
     * @return void
     */
    public static function getChildPages() {
        $edu = Edu::instance();
        $db = $edu->db;
        $user = $edu->session->user;
        /**
        * $usersess used for remembering these page lists once back at Laravel
        * @var string|object $post
        */
        global $post;

        // Excludes
        $page_excludes = str_replace(' ', '', rtrim(get_option('eduiland_sidebar_excludes'), ','));

        // Exclude Chipper / Bug Report for em/hs
        if($user->role != 'admin') {
            if($user->class_type > 2) {
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

        if( (self::getCurrentPageDepth() == 1 && !in_array($post->post_parent, $page_excludes_array)) || is_page('hs') || is_page('em') || is_page('ms') || is_page('student-resources') || is_page('teacher-resources')) {
            // Show / set menu header for parent pages
            if( is_page('hs') || is_page('em') || is_page('ms') ) {
                echo '<h3 id="sidebar-header-courseware" class="sidebar-header"><a href="'.EduHelpers::getHomelink()->link.'">Get Started</a></h3><ul class="sidebar-menu">';
                $usersess['parent_page'] = (object) [
                    'title' => 'Get Started',
                    'link' => EduHelpers::getHomelink()->link
                ];

            } else {
                echo '<h3 id="sidebar-header-courseware" class="sidebar-header"><a href='.get_the_permalink() . '>' . get_the_title() . '</a></h3><ul id="sidebar-courseware" class="sidebar-menu">';
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
            echo '<h3 id="sidebar-header-courseware" class="sidebar-header"><a href='.get_the_permalink($post->post_parent) . '>' . get_the_title($post->post_parent) . '</a></h3><ul id="sidebar-courseware" class="sidebar-menu">';
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
        if($user->demo && self::getCurrentPageDepth() > 0) {
            $usersess['page_list'] = preg_replace(
                '/href="((?!.*step-1|.*a-setting|.*history-of-innovation|.*4-script-storming|.*number-1|.*influences|.*success-qualities|.*character-traits).+)"/', '
                href="#" data-toggle="tooltip" title="Disabled for demo"',
                $usersess['page_list']
            );
        }

        echo $usersess['page_list'];
        echo '</ul>';

        // Update db session if not on resource pages
        if(false === array_search(true, array_map('self::isTree', $page_excludes_array))) {
            $usersess['page_list'] = self::strRemoveHost($usersess['page_list']);
            if(is_object($usersess['parent_page'])) {
                $usersess['parent_page']->link = self::strRemoveHost($usersess['parent_page']->link);
            }

            $db->update('users_session_data', [
                    'page_list' => json_encode($usersess['page_list']),
                    'parent_page' => json_encode($usersess['parent_page']),
                    'page_last_visited' => $usersess['page_last_visited']
                ], ['hash' => $_COOKIE['tgui']
            ]);
        }
    }


    /**
     * Get home link
     *
     * @param  mixed $user
     * @return object
     */
    public static function getHomelink() {
        $user = Edu::instance()->session->user;
        switch ($user->class_type) {
            case 4: // highschool
                $link = '/course/hs';
                break;
            case 3: // middlechool
                $link = '/course/hs'; //'/course/ms';
                break;
            case 2: // elementary
                $link = '/course/em';
                break;
            case 1: // elementary
                $link = '/course/em';
                break;
            default:
                $link = '/dashboard';
        }
        $current = preg_match('/\/course\/(em|hs)\/$/', $_SERVER['REQUEST_URI']);
        return (object)[
            'link' => $link,
            'css' => $current ? ' current_page_item' : ''
        ];
    }


    /**
     * Is Laravel Admin
     *
     * @param  object $user
     * @return boolean
     */
    public static function isLaraAdmin() {
        $user = Edu::instance()->session->user;
        return in_array($user->role, ['admin', 'manager', 'developer']);
    }



    /**
     * Remove host from url
     *
     * @param  string $str
     * @return string
     */
    private static function strRemoveHost($str) {
        $host = $_SERVER['HTTP_HOST'];
        $http = 'http://';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $http = 'https://';
        }
        $host = $http . $host;
        return str_replace($host, '', $str);
    }

    //
    // Is page or its children
    // --------------------------------------------------------------------------
    private static function isTree($pid) {
        /** @var string|object $post */
        global $post;
        if (is_page() && ($post->post_parent == $pid || is_page($pid))) {
            return true;
        }
        return false;
    }

    /**
     * Get children of pages array
     *
     * @param  array $pages_array
     * @return array
     */
    public static function getChildrenPageIds($pages_array) {
        // Get children of page excludes
        $page_children = array_map(function ($n) {
            $pageids = [];
            $children = get_pages('child_of=' . $n);
            if (count($children)) {
                foreach ($children as $page) {
                    $pageids[] = $page->ID;
                }
            }
            return $pageids;
        }, $pages_array);
        // Flatten array
        $page_children = array_merge(...$page_children);
        return $page_children;
    }


    /**
     * Get Current Page Depth
     *
     * @return int
     */
    public static function getCurrentPageDepth() {
        /** @var string|object $wp_query */
        global $wp_query;
        $object = $wp_query->get_queried_object();
        $parent_id = $object->post_parent;
        $depth = 0;
        while ($parent_id > 0) {
            $page = get_post($parent_id);
            $parent_id = $page->post_parent;
            $depth++;
        }

        return $depth;
    }

}
