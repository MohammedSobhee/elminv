<?php
if (!defined('ABSPATH')) exit;

class EduEnqueue {
    private $user;

    public function __construct($user) {
        add_action('wp_enqueue_scripts', [$this, 'eduiland_scripts']);
        add_action('init', [$this, 'mod_jQuery']);
        $this->user = $user;
    }

    /**
     * Add plugin-centric JS and styles
     *
     * @return void
     */
    public function eduiland_scripts() {
        // Show hidden content of section accordion content
        if (EduHelpers::isLaraAdmin()) {
            wp_enqueue_style('eduiland-admin-style', plugin_dir_url( __DIR__ ) . 'assets/css/eduiland-admin.css', false, filemtime( plugin_dir_path(__DIR__) . 'assets/css/eduiland-admin.css'));
        }
        // Show teacher notes if not student
        if($this->user->role != 'student') {
            wp_enqueue_style( 'eduiland-acs-style', plugin_dir_url( __DIR__ ) . 'assets/css/acs.css', false, filemtime( plugin_dir_path(__DIR__) . 'assets/css/acs.css' ) );
        }
        // Load WP only JS
        if( is_wordpress() ) {
            wp_enqueue_script( 'eduiland-js', plugin_dir_url( __DIR__ ) . 'assets/js/eduiland_wp.bundle.js', array( 'jquery' ), filemtime( plugin_dir_path(__DIR__) . 'assets/js/eduiland_wp.bundle.js' ), true );
            wp_enqueue_script( 'eduiland-popper','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', array( 'jquery' ),'',true );
            wp_enqueue_script( 'eduiland-bootstrap','https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array( 'jquery' ),'',true );
            // Pass edu userdata to JS
            if( !is_admin() ) {
                wp_localize_script('eduiland-js', 'eduUser', (array) $this->user);
                wp_localize_script('eduiland-js', 'eduData', [
                    'hs_parent_id' => Edu::instance()->hsParentID,
                    'em_parent_id' => Edu::instance()->emParentID
                ]);
            }
        }
    }

    /**
     * Move jQuery to the footer
     *
     * @return void
     */
    function mod_jQuery() {
        if (!is_admin() && !($GLOBALS['pagenow'] == 'wp-login.php')) {
            wp_deregister_script( 'jquery' );
            wp_deregister_script('jquery-core');
            wp_deregister_script('jquery-migrate');
            if( is_wordpress() ) {
                wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', false, '1.12.4', true );
                wp_enqueue_script( 'jquery' );
            }
        }
    }
}
