<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * EduAdminSetup
 */
class EduAdminSetup {

    public function __construct() {
        add_action('admin_menu', [$this, 'create_settings_page']);
        add_action('admin_init', [$this, 'setup_sections']);
        add_action('admin_init', [$this, 'setup_fields']);
        add_action('save_post', [$this, 'send_email_updates'], 1, 2);
        add_action('wp_head', [$this, 'modify_admin_interface']);
        add_action('admin_head', [$this, 'modify_admin_interface']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_links']);
        add_action('admin_menu', [$this, 'remove_admin_menu_items'], 801);
        add_action('wp_before_admin_bar_render', [$this, 'remove_wpadminbar_items'], 999);
        add_action('init', [$this, 'remove_admin_bar']);
        add_action('init', [$this, 'edu_unregister_tags_for_posts']);
        add_filter('plugin_action_links', [$this, 'disable_plugin_deactivation'], 10, 4);
        add_action('admin_notices', [$this, 'activation_notices']);
    }


    /**
     * Add Menu Item and Page
     *
     * @return void
     */
    public function create_settings_page() {
        $page_title = 'Inventionland Institute Website Plugin Settings';
        $menu_title = 'Institute Settings';
        $capability = 'manage_options';
        $slug = 'eduiland_settings';
        $callback = [$this, 'settings_page_html'];
        add_submenu_page('options-general.php', $page_title, $menu_title, $capability, $slug, $callback);
    }


    /**
     * Settings Content
     *
     * @return void
     */
    public function settings_page_html() {?>
        <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form id="form-eduiland-settings" action="options.php" method="post">
            <?php
            settings_fields('eduiland_settings');
            do_settings_sections('eduiland_settings');
            submit_button('Save Settings');
            ?>
        </form>
        <h2>Available User Data</h2>
        <p>An example of the user session data available to utilize in the UI:</p>
        <pre>
        <?php print_r(eduiland()->user) ?>
        </pre>
        <br><br>
        </div>
    <?php
    }


    /**
     * Setup Sections
     *
     * @return void
     */
    public function setup_sections() {
        add_settings_section('content_update_notifications', 'Content Update Notification', [$this, 'section_callback'], 'eduiland_settings');
        add_settings_section('sidebar_excludes', 'Parent pages to exclude from navigation session save', [$this, 'section_callback'], 'eduiland_settings');
        add_settings_section('courseware_parent_pages', 'Courseware Parent Page IDs', [$this, 'section_callback'], 'eduiland_settings');
    }


    /**
     * Sections Callback
     *
     * @param  mixed $arguments
     * @return void
     */
    public function section_callback($arguments) {
        switch ($arguments['id']) {
            case 'content_update_notifications':
                echo 'Provide an email address to receive content update notifications:';
                break;
                case 'sidebar_excludes':
                  echo 'Provide parent page IDs to exclude from the session save. The session save serves to remember the last place in the courseware pages. Separate by a comma:';
                  break;
        }
    }


    /**
     * Setup Fields
     *
     * @return void
     */
    public function setup_fields() {
        $fields = [
            [
                'uid' => 'eduiland_admin_email',
                'label' => 'Email Address',
                'section' => 'content_update_notifications',
                'type' => 'text',
                'placeholder' => 'Email',
                'default' => '',
                'supplemental' => false
            ],
            [
                'uid' => 'eduiland_disable_notifications',
                'label' => 'Disable Update Notifications',
                'section' => 'content_update_notifications',
                'type' => 'checkbox',
                'options' => [
                    1 => 'Disable'
                ],
                'default' => [0],
                'supplemental' => false
            ],
            [
              'uid' => 'eduiland_sidebar_excludes',
              'label' => 'Page IDs',
              'section' => 'sidebar_excludes',
              'type' => 'textarea',
              'placeholder' => 'Example: 332, 20341, 5889, 1290, 30021',
              'default' => '',
              'supplemental' => "Anything that isn't an actual courseware page. For example, resource page parents.",
            ],
            [
                'uid' => 'eduiland_courseware_elementary_page',
                'label' => 'Elementary Parent Page ID',
                'section' => 'courseware_parent_pages',
                'type' => 'text',
                'placeholder' => '',
                'default' => '',
                'supplemental' => false
            ],
            [
                'uid' => 'eduiland_courseware_highschool_page',
                'label' => 'High School Parent Page ID',
                'section' => 'courseware_parent_pages',
                'type' => 'text',
                'placeholder' => '',
                'default' => '',
                'supplemental' => false
            ]
        ];
        foreach ($fields as $field) {
            add_settings_field($field['uid'], $field['label'], [$this, 'field_callback'], 'eduiland_settings', $field['section'], $field);
            register_setting('eduiland_settings', $field['uid']);
        }
    }

    public function field_callback($arguments) {
        $value = get_option($arguments['uid']); // Get the current value, if there is one
        if (!$value) { // If no value exists
            $value = $arguments['default']; // Set to our default
        }
        // Check which type of field we want
        switch ($arguments['type']) {
            case 'text': // Text field
                printf('<input name="%1$s" style="height:35px" size="50" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value);
                break;

            case 'radio':
            case 'checkbox':
                if (!empty($arguments['options']) && is_array($arguments['options'])) {
                    $option_count = round(count($arguments['options']) / 2);
                    $options_markup = $option_count > 10 ? '<div style="width:300px;float:left">' : '';
                    $iterator = 0;
                    $count = 0;
                    //echo $br;
                    foreach ($arguments['options'] as $key => $label) {
                        $iterator++;
                        if ($count == $option_count) {
                            $options_markup .= '</div><div style="width:300px;float:left">';
                            $count = 0;
                        }

                        $options_markup .= sprintf(
                            '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>',
                            $arguments['uid'], $arguments['type'], $key, checked($value[array_search($key, $value)], $key, false), $label, $iterator
                        );

                        $count++;
                    }
                    $options_markup .= '</div>';
                    printf('<fieldset>%s</fieldset>', $options_markup);
                }
                break;

            case 'textarea': // Textarea
                printf('<textarea name="%1$s" id="%1$s" placeholder="%3$s" rows="5" cols="49"/>%4$s</textarea>', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value);
                break;

            case 'select': // Select
                if (!empty($arguments['options']) && is_array($arguments['options'])) {
                    $options_markup = '';
                    foreach ($arguments['options'] as $key => $label) {
                        $options_markup .= sprintf('<option value="%s" %s>%s</option>', $key, selected($value, $key, false), $label);
                    }
                    printf('<select name="%1$s" id="%1$s">%2$s</select>', $arguments['uid'], $options_markup);
                }
                break;
        }

        // If there is supplemental text
        if ($supplimental = $arguments['supplemental']) {
            printf('<p class="description">%s</p>', $supplimental); // Show it
        }
    }


    /**
     * Update Notifications
     *
     * @param  int $post_id
     * @param  object $post
     * @return void
     */
    public function send_email_updates($post_id, $post) {

        if (
            (isset($post->post_status) && 'auto-draft' == $post->post_status) ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
            wp_doing_ajax()
        ) {
            return;
        }

        $email = get_option('eduiland_admin_email');
        $disabled = get_option('eduiland_disable_notifications');

        if (!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id) && $email && !$disabled) {
            $siteurl = get_bloginfo('url');
            $http = ['http://', 'https://'];
            $subject_url = str_replace($http, '', $siteurl);
            $post_title = get_the_title($post_id);
            $post_url = get_permalink($post_id);
            $post_status = get_post_status($post_id);

            $date = get_post_modified_time('F d, Y g:i a', false, $post_id);

            $last_id = get_post_meta($post_id, '_edit_last', true);
            $last_user = get_userdata($last_id);

            if ($last_user) {
                $author = apply_filters('the_modified_author', $last_user->display_name);
            } else {
                $author = 'Unknown';
            }

            $subject = $subject_url . ' updated (' . $post_status . '): ' . $post_title;

            $message = 'A post has been updated on ' . get_bloginfo() . ':<br><br>';
            $message .= '<a href="' . $post_url . '">' . $post_title . '</a> ';
            $message .= 'by ' . $author . ' on ' . $date . '<br><br>';
            $message .= 'STATUS: ' . $post_status . '<br>';
            $message .= 'URL: ' . $post_url . '<br>';
            $message .= 'EDIT: ' . get_edit_post_link($post_id) . '<br><br><br>';
            add_filter('wp_mail_content_type', [$this, 'set_content_type']);
            wp_mail($email, $subject, $message);
            remove_filter('wp_mail_content_type', [$this, 'set_content_type']);
        }
    }

    // Set text/html emails
    public function set_content_type() {
        return "text/html";
    }

    //
    // Admin Interface Modifications
    // --------------------------------------------------------------------------

    /**
     * Admin styling
     *
     * @return void
     */
    function modify_admin_interface() {
        if (is_user_logged_in()) {
            echo '<style type="text/css">
            .wp-media-buttons a[title*="slideshow"] {
                display: none !important;
            }
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before  {
                content: " ";
            }
            #wpadminbar>#wp-toolbar>#wp-admin-bar-root-default>#wp-admin-bar-wp-logo .ab-icon {
                background-image: url(' . get_stylesheet_directory_uri() . '/assets/images/admin-logo-icon-white.png) !important;
                font: none !important;
                background-repeat: no-repeat;
                background-size: contain;
                background-position: 0 0;
                top: 8px;
                left: 5px;
                width: 20px;
                height: 20px;
            }
            #welcome-panel .welcome-panel-column:first-of-type,
            .welcome-panel-content h2,
            .welcome-panel-content .about-description {
                display: none !important;
            }
            .post-type-post #pageparentdiv,
            #category-add-toggle {
                display: none;
            }';

            $current_user = wp_get_current_user(); // Can't find another way to hide theme builder at the moment...
            if (!in_array($current_user->user_login, WP_EDU_ADMIN)) {
                echo '#wp-admin-bar-elementor_edit_page-default { display: none; }';
            }

            echo '</style>';
        }
    }


    /**
     * Add dashboard links
     *
     * @return void
     */
    public function add_dashboard_links() {
        global $wp_meta_boxes;
        wp_add_dashboard_widget('custom_help_widget', 'Go to Course Pages', [$this, 'custom_dashboard_links']);
    }

    public function custom_dashboard_links() {
        echo '<ul>
        <li><h3><a href="/course/hs/?ct=4">High School Course Pages</a></h3></li>
        <li><h3><a href="/course/hs/?ct=3">Middle School Course Pages</a></h3></li>
        <li><h3><a href="/course/em/?ct=1">Elementary Course Pages</a></h3></li>
        <li><h3><a href="/eduadmin">Admin Dashboard</a></h3></li>
        </ul>';
    }

    /**
     * Remove items from admin menu  and bar for non-dev admins
     *
     * @return void
     */
    public function remove_admin_menu_items() {
        $current_user = wp_get_current_user();
        if (!in_array($current_user->user_login, WP_EDU_ADMIN)) {

            remove_menu_page('elementor');
            remove_submenu_page('elementor', 'elementor');
            remove_submenu_page('elementor', 'elementor-role-manager');
            remove_submenu_page('elementor', 'elementor-tools');
            remove_submenu_page('elementor', 'elementor-system-info');
            remove_submenu_page('elementor', 'elementor-getting-started');
            remove_submenu_page('elementor', 'go_knowledge_base_site');
            remove_submenu_page('elementor', 'elementor_custom_fonts');
            remove_submenu_page('elementor', 'elementor_custom_icons');
            remove_submenu_page('elementor', 'go_elementor_pro');
            remove_submenu_page('edit.php?post_type=elementor_library', 'edit.php?post_type=elementor_library&tabs_group=theme');

            remove_menu_page('edit.php?post_type=acf-field-group');
            remove_menu_page('wordfence');
            remove_menu_page('tools.php');
            remove_menu_page('plugins.php');
            remove_menu_page('themes.php');
            remove_menu_page('edit-comments.php');

            remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=category');
            remove_submenu_page('options-general.php', 'options-general.php?page=mailgun');
            remove_submenu_page('options-general.php', 'options-general.php?page=mailgun-lists');
            remove_submenu_page('options-general.php', 'options-writing.php');
            remove_submenu_page('options-general.php', 'options-reading.php');
            remove_submenu_page('options-general.php', 'options-permalink.php');
            remove_submenu_page('options-general.php', 'options-media.php');
            remove_submenu_page('options-general.php', 'options-discussion.php');

            $request = urlencode($_SERVER['REQUEST_URI']);
            remove_submenu_page('themes.php', 'customize.php?return=' . $request);
        }
    }


    /**
     * Remove elementor inspector
     *
     * @param  mixed $wp_admin_bar
     * @return void
     */
    public function remove_wpadminbar_items() {
        global $wp_admin_bar;
        $current_user = wp_get_current_user();
        if (!in_array($current_user->user_login, WP_EDU_ADMIN)) {
            $wp_admin_bar->remove_node('customize');
            $wp_admin_bar->remove_node('updates');
            $wp_admin_bar->remove_node('comments');
            $wp_admin_bar->remove_node('duplicate-post');
            // $wp_admin_bar->remove_node('elementor_edit_page-default');
            // $wp_admin_bar->remove_node('elementor_inspector');
            // $wp_admin_bar->remove_node('elementor_app_site_editor');
        }
    }


    /**
     * Remove admin bar if not laravel admin
     *
     * @return void
     */
    public function remove_admin_bar() {
        if (!EduHelpers::isLaraAdmin()) {
            show_admin_bar(false);
        }
    }

    /**
     * Remove tag support from posts
     *
     * @return void
     */
    public function edu_unregister_tags_for_posts() {
        unregister_taxonomy_for_object_type('post_tag', 'post');
    }


    /**
     * Prevent deactivation of important plugins
     *
     * @param  mixed $actions
     * @param  mixed $plugin_file
     * @param  mixed $plugin_data
     * @param  mixed $context
     * @return void
     */
    public function disable_plugin_deactivation($actions, $plugin_file, $plugin_data, $context) {
        if (array_key_exists('deactivate', $actions) && in_array($plugin_file, [
            'eduiland/eduiland.php',
            'elementor/elementor.php',
            'elementor-pro/elementor-pro.php',
            'advanced-custom-fields-pro/acf.php',
            'elementor-extras/elementor-extras.php'
        ])) {
            unset($actions['deactivate']);
        }
        return $actions;
    }


    /**
     * Admin notices to add sidebar widget
     *
     * @return void
     */
    public function activation_notices() {
        // if (!is_active_widget(false, false, 'eduiland_sidebar_nav_widget', true)) {
        //     echo '<div class="notice notice-error"><h3>Be sure to add the <a href="' . admin_url('widgets.php') . '">Inventionland Institute Navigation Widget</a></h3></div>';
        // }

        if(!defined('ELEMENTOR_PATH')) {
           echo '<div class="notice notice-error"><h3>Elementor and Elementor Pro plugins must be installed and activated.</h3></div>';
        }
    }
}
