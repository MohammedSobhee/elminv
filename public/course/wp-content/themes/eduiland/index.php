<?php
    if(eduiland()->user->role === 'student') {
        wp_safe_redirect('/dashboard');
        exit;
    }
    get_header();
    if (class_exists('ACF')) {
        $disable_sidebar = get_field('disable_sidebar');
    } else {
        $disable_sidebar = '';
    }
?>


<?php if ( $disable_sidebar ): ?>
    <div class="content col-lg-12" id="content">
<?php else: ?>
    <div class="content col-lg-9 pt-4" id="content">
        <div id="sidebar-drop-menu" class="sidebar-drop-menu"></div>
        <?php get_posts_loop(['pagination']); ?>
    </div>

<?php endif;


if (!$disable_sidebar):
    get_sidebar();
endif;
    get_footer();
