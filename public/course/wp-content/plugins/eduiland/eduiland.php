<?php
/*
Plugin Name: Inventionland Institute
Description: Site functionality for Inventionland Insitute
Version: 0.9
Author: Kristi Russell
Author URI: https://www.linkedin.com/in/kristi-russell-b3455786/
Text Domain: eduiland
Copyright (C)2021 Flying Cork
*/
defined('ABSPATH') || exit;

// Define who can see certain aspects of wp-admin (plugins, elementor, etc)
if (!defined('WP_EDU_ADMIN'))
    define('WP_EDU_ADMIN', ['krussell']);

require_once dirname(__FILE__) . '/includes/logger.php';

spl_autoload_register(function ($class_name) {
    if (false !== strpos($class_name, 'Edu')) {
        $parts = explode('\\', $class_name);
        $class = 'class-' . strtolower(array_pop($parts));
        $class_path = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $class . '.php';
        if (file_exists($class_path)) {
            include_once $class_path;
        }
    }
});

// Initialize edu session / create helper function
add_action('plugins_loaded', 'eduiland');
function eduiland() {
    $edu = Edu::instance();
    return (object) [
        'db' => $edu->db,
        'user' => $edu->session->user,
        'announcement' => $edu->session->announcement,
        'url' => $_SERVER['REQUEST_URI']
    ];
}
