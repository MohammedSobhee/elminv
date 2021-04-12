<?php
if (!defined('ABSPATH')) exit;

/**
 * Edu Sidebar Navigation Widget
 */
class EduSideBarNavWidget extends WP_Widget {

    public function __construct() {
        $widget_ops = [
            'description' => __('Inventionland Institute Navigation'),
            'customize_selective_refresh' => true
        ];
        parent::__construct('eduiland_sidebar_nav_widget', __('Inventionland Institute Navigation'), $widget_ops);
    }


    /**
     * Display nav on front end
     *
     * @param  array $args
     * @param  array $instance
     * @return void
     */
    public function widget($args, $instance) {
        // Remove before/after strings added by default from WP
        $args['before_widget'] ??= '';
        $args['after_widget'] ??= '';
        $args['before_title'] ??= '';
        $args['after_title'] ??= '';

        echo '<div class="sidebar-wrapper col-lg-3"><div class="sidebar" id="sidebar">';
        // Get nav based on current page
        if (is_page()) {
            EduHelpers::getChildPages();
            // Get inserted assignments
            (new EduAssignments())->init();
        }

        // Get nav from session
        if (!is_page() || strpos($_SERVER['REQUEST_URI'], 'resources') !== false) {
            EduSidebarSessionNav::show();
        }
        echo '</div></div>';
    }


    /**
     * Back end form
     *
     * @param  mixed $instance
     * @return void
     */
    public function form($instance) {
        echo "<p>This will display Inventionland Institute's Custom Navigation. There are no settings.</p>";
    }
}
