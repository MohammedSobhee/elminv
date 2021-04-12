<?php
/*
Plugin Name: Inventionland Institute Must Use Plugin
Plugin URI: https://inventionlandinstitute.com
Description: Things to do before everything else.
Author: Kristi Russell
Version: 1.0
Author URI: https://www.flyingcork.com
 */

//
// Disable plugins on Laravel side
// --------------------------------------------------------------------------
add_filter('option_active_plugins', 'edu_disable_plugin');
function edu_disable_plugin($plugins) {
    if (strpos($_SERVER['REQUEST_URI'], '/course') === false) {
        $deactivated = [
            'elementor-pro/elementor-pro.php',
            'elementor/elementor.php',
            'elementor-extras/elementor-extras.php',
            'wp-migrate-db-pro-media-files/wp-migrate-db-pro-media-files.php',
            'wp-migrate-db-pro/wp-migrate-db-pro.php',
            'duplicate-post/duplicate-post.php',
            'simple-page-ordering/simple-page-ordering.php',
            'admin-collapse-subpages/admin_collapse_subpages.php',
            'classic-editor/classic-editor.php',
            'post-types-order/post-types-order.php',
            'search-regex/search-regex.php',
            'wp-fastest-cache/wpFastestCache.php',
            'query-monitor/query-monitor.php',
            'eduiland/eduiland.php'
        ];
        foreach ($deactivated as $plugin) {
            $key = array_search($plugin, $plugins);
            if (false !== $key) {
                unset($plugins[$key]);
            }
        }
    }
    return $plugins;
}
