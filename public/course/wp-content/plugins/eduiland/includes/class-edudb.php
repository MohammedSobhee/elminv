<?php
if (!defined('ABSPATH')) {
    exit;
}

class EduDB {
    public function connect() {
        return new wpdb(COOKIE_DB_USER, COOKIE_DB_PASSWORD, COOKIE_DB, DB_HOST);
    }
}
