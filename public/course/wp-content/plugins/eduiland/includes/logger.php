<?php
// WP Log
// --------------------------------------------------------------------------
if (!function_exists('wp_log')) {
    function wp_log($log) {
        if (true === WP_DEBUG && true === WP_DEBUG_LOG) {
            error_log(is_array($log) || is_object($log) ? print_r($log, true) : $log);
        }
    }
}
