<?php
if (!defined('ABSPATH')) {
    exit;
}

// ==========================================================================
// Custom Post Types
// ==========================================================================

class EduPostTypes {
    public function __construct() {
        add_action('init', [$this, 'create_standards_category']);
        add_action('init', [$this, 'create_standards_post_type']);
        add_action('init', [$this, 'create_standards_post_type']);
        add_action('admin_menu', [$this, 'eduiland_remove_states_metabox']);
        add_action('manage_standards_posts_custom_column', [$this, 'standards_backend_columns'], 10, 2);
        add_filter('manage_standards_posts_columns', [$this, 'set_standards_backend_columns']);
    }


    /**
     * Video Post Type
     *
     * @return void
     */
    public function create_standards_post_type() {
        register_post_type('standards',
            [
                'labels' => [
                    'name' => __('Standards'),
                    'singular_name' => __('Standard'),
                    'add_new_item' => __('Add New Standard'),
                    'edit_item' => __('Edit Standard'),
                    'view_item' => __('View Standard'),
                    'view_items' => __('View Standards'),
                    'search_items' => __('Search Standards'),
                    'all_items' => __('All Standards'),
                    'item_published' => __('Video published'),
                    'item_published_privately' => __('Standard published privately'),
                    'item_scheduled' => __('Standard scheduled'),
                    'item_updated' => __('Standard updated')
                ],
                'exclude_from_search' => true,
                'public' => true,
                'has_archive' => 'standards',
                'taxonomies' => ['states'],
                'menu_icon' => 'dashicons-welcome-learn-more',
                'supports' => ['title', 'editor', 'revisions', 'custom-fields'],
                'rewrite' => ['slug' => 'standards', 'with_front' => false]
            ]
        );
    }


    /**
     * Standards Categories and  Tags
     *
     * @return void
     */
    public function create_standards_category() {
        // create a new taxonomy
        register_taxonomy(
            'states',
            'standards',
            [
                'labels' => [
                    'name' => __('States'),
                    'singular_name' => __('State'),
                    'add_new_item' => __('Add New State'),
                    'add_new_item' => __('Add New State'),
                    'new_item_name' => __('New State Name'),
                    'edit_item' => __('Edit State'),
                    'view_item' => __('View State'),
                    'update_item' => __('Update State'),
                    'search_items' => __('Search States'),
                    'choose_from_most_used' => __('Choose most used states'),
                    'back_to_items' => __( '← Back to states' )
                ],
                'hierarchical' => true,
                'query_var' => true,
                //'rewrite' => ['slug' => 'products', 'with_front' => false]
            ]
        );
    }


    /**
     * Hide Standards States Taxonomy
     *
     * @return void
     */
    public function eduiland_remove_states_metabox() {
        remove_meta_box('statesdiv', 'standards', 'side');
    }


    /**
     * Modify / add columns to backend for Standards
     *
     * @param  mixed $column
     * @param  mixed $post_id
     * @return void
     */
    public function standards_backend_columns($column, $post_id) {
        switch ($column) {
        case 'states':
            $terms = get_the_term_list($post_id, 'states', '', ',', '');
            if (is_string($terms)) echo $terms;
            break;
        case 'class_type' :
            $class_types = [
                1 => 'Elementary',
                2 => 'Elementary',
                3 => 'Middle School',
                4 => 'High School'
            ];
            $type = get_post_meta( $post_id , 'class_type' , true );
            if(array_key_exists($type, $class_types))
                echo $class_types[get_post_meta( $post_id , 'class_type' , true )];
            break;
        }
    }
    public function set_standards_backend_columns($columns) {
        unset($columns['title']);
        unset($columns['date']);
        $columns['title'] = __('Standard', 'eduiland');
        $columns['states'] = __('State', 'eduiland');
        $columns['class_type'] = __('Class Type', 'eduiland');
        $columns['date'] = __('Date', 'eduiland');
        return $columns;
    }

}
