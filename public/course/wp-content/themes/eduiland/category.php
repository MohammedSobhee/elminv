<?php
if (eduiland()->user->role == 'student') {
    wp_safe_redirect('/dashboard');
    exit;
} else {
    get_template_part('index');
}
?>
