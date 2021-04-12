<?php
if (!defined('ABSPATH')) exit;


class EduAnnouncements {
    private $user;
    private $db;
    private $announcement;

    public function __construct($db, $session) {
        add_action('transition_post_status', [$this, 'announcement_transition'], 10, 3);
        add_filter('pre_get_posts', [$this, 'announcement_exclude_category']);
        add_action('template_redirect', [$this, 'announcement_mark_read']);

        $this->user = $session->user;
        $this->db = $db;
        $this->announcement = $session->announcement;
    }


    /**
     * Detect removal and publishing of announcements
     *
     * @param  string $new_status
     * @param  string $old_status
     * @param  object $post
     * @return void
     */
    public function announcement_transition($new_status, $old_status, $post) {
        if (
            ($old_status == $new_status) ||
            ($post->post_type != 'post') ||
            (in_array($old_status, ['new', 'draft', 'auto-draft']) && strpos($new_status, 'draft') !== false) ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        ) {
            return;
        }
        // Remove from announcement
        if ($old_status == 'publish' && $new_status != 'publish') {
            $this->announcementRemove($post->ID);
            // Add announcement
        } else if (($old_status != 'publish' && $new_status == 'publish')) {
            $this->announcementAdd($post->ID);
        }
    }


    /**
     * Update users_session_data when a new post is published
     *
     * @param  int $post_id
     * @return void
     */
    private function announcementAdd($post_id) {

        $cat = get_the_category($post_id);

        // All class types for all but student (empty courseware_types)
        if (array_search('all', array_column($cat, 'slug')) !== false) {
            $this->db->query($this->db->prepare('UPDATE users_session_data
                SET announcement = COALESCE(JSON_ARRAY_APPEND(announcement, "$", %s), JSON_ARRAY(%s))
                WHERE JSON_VALID(user_data)
                AND JSON_LENGTH(JSON_EXTRACT(user_data, "$.class_types")) > 0
                AND JSON_SEARCH(announcement, "one", %s) IS NULL',
                $post_id, $post_id, $post_id
            ));
        }

        // High School
        else if (array_search('high-school', array_column($cat, 'slug')) !== false) {
            $this->db->query($this->db->prepare('UPDATE users_session_data
                SET announcement = COALESCE(JSON_ARRAY_APPEND(announcement, "$", %s), JSON_ARRAY(%s))
                WHERE JSON_VALID(user_data)
                AND (JSON_CONTAINS(JSON_EXTRACT(user_data, "$.class_types[*]"), %s)
                OR JSON_CONTAINS(JSON_EXTRACT(user_data, "$.class_types[*]"), %s))
                AND JSON_SEARCH(announcement, "one", %s) IS NULL',
                $post_id, $post_id, 3, 4, $post_id
            ));
        }

        // Elementary
        else if (array_search('elementary', array_column($cat, 'slug')) !== false) {
            $this->db->query($this->db->prepare('UPDATE users_session_data
                SET announcement = COALESCE(JSON_ARRAY_APPEND(announcement, "$", %s), JSON_ARRAY(%s))
                WHERE JSON_VALID(user_data)
                AND (JSON_CONTAINS(JSON_EXTRACT(user_data, "$.class_types[*]"), %s)
                OR JSON_CONTAINS(JSON_EXTRACT(user_data, "$.class_types[*]"), %s))
                AND JSON_SEARCH(announcement, "one", %s) IS NULL',
                $post_id, $post_id, 1, 2, $post_id
            ));
        }

        wp_log('A new post (' . $post_id . ') has been published for ' . $cat[0]->name);
    }


    /**
     * Remove announcement
     *
     * @param  int $post_id
     * @return void
     */
    private function announcementRemove($post_id) {
        $this->db->query($this->db->prepare('UPDATE users_session_data
            SET announcement = JSON_REMOVE(announcement, JSON_UNQUOTE(JSON_SEARCH(announcement, "one", %s)))
            WHERE json_search(announcement, "one", %s) IS NOT NULL',
            $post_id,
            $post_id
        ));

        $this->db->query('UPDATE users_session_data
            SET announcement = NULL
            WHERE JSON_VALID(user_data)
            AND JSON_LENGTH(announcement) < 1',
        );

        wp_log('A post (' . $post_id . ') has been removed.');
    }


    /**
     * Announcement Filter
     *
     * @param  object $query
     * @return object
     */
    function announcement_exclude_category($query) {
        if ($query->is_home) {

            if (count($this->user->courseware_types) >= 3) {
                return;
            }

            $excludes = [];
            $ctypes = $this->user->class_types;
            // Exclude High School if only elementary
            if (!in_array(3, $ctypes) && !in_array(4, $ctypes)) {
                $excludes[] = 'high-school';
                // Exclude Elementary if only high school
            } else if (!in_array(1, $ctypes) && !in_array(2, $ctypes)) {
                $excludes[] = 'elementary';
            }
            if (count($excludes)) {
                $query->set('tax_query', [
                    [
                        'taxonomy' => 'category',
                        'field' => 'slug',
                        'terms' => $excludes,
                        'operator' => 'NOT IN'
                    ]
                ]);
                return $query;
            }
        }
    }


    /**
     * Set announcement to 0 (mark as read)
     *
     * @return void
     */
    public function announcement_mark_read() {
        if (is_posts() && $this->announcement) {
            $this->db->update('users_session_data', ['announcement' => NULL], ['hash' => $_COOKIE['tgui']]);
        }
    }
}
