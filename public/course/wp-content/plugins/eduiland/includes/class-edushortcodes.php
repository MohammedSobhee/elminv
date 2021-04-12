<?php
// ==========================================================================
// Shortcodes
// ==========================================================================

class EduShortCodes {
    private $user;

    public function __construct($session) {
        add_shortcode('edu-user', [$this, 'show_user_data']);
        add_shortcode('standards', [$this, 'standards']);

        $this->user = $session->user;
    }

    //
    // Show User Data
    // --------------------------------------------------------------------------
    public function show_user_data($atts) {
        $type = isset($atts['type']) ? $atts['type'] : 'firstname';
        return $this->user->{$type};
    }

    //
    //
    // --------------------------------------------------------------------------
    public function standards() {
        $current_page_id = get_queried_object_id();
        $class_type = $this->user->class_type > 2 ? 4 : 1;
        $args = [
            'post_type' => 'standards',
            'numberposts' => 1,
            'tax_query' => [
                [
                    'taxonomy' => 'states',
                    'field' => 'slug',
                    'terms' => strtolower($this->user->standards) ?: 'pa',
                    'operator' => 'IN'
                ]
            ],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'class_type',
                    'value' => $class_type,
                    'compare' => '='
                ],
                [
                    'key' => 'page_location',
                    'value' => $current_page_id,
                    'compare' => 'LIKE'
                ]
            ]
        ];

        if(defined( 'ELEMENTOR_PATH' ) && Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return '<span class="text-gray">Standards will be shown here when not editing.</span>';
        }

        $standards = get_posts($args);
        if (isset($standards[0])) {
            return apply_filters('the_content', $standards[0]->content);
        } else {
            $this->standardsErrorLog(1);
            $args['tax_query'][0]['terms'] = 'pa';
            $standards = get_posts($args);
            if(isset($standards[0])) {
                return apply_filters('the_content', $standards[0]->content);
            } else if($this->user->role === 'student') {
                $this->standardsErrorLog();
                return 'There is an issue with loading standards. Please let your teacher know.';
            } else {
                $this->standardsErrorLog();
                return 'There is an issue with loading standards. Please <a href="/support">contact support</a> and let us know.';
            }
        }
    }

    private function standardsErrorLog($missing = 0) {
        $msg = $missing ? 'MISSING STANDARDS' : 'STANDARDS ERROR:';
        wp_log($msg);
        wp_log($_SERVER['REQUEST_URI']);
        wp_log($this->user);
    }
}
