<?php
get_header();
if (class_exists('ACF')) {
    $disable_sidebar = get_field( 'disable_sidebar' );
} else {
    $disable_sidebar = '';
}
?>

<?php if ( $disable_sidebar ): ?>
    <div class="content col-lg-12" id="content">
<?php else: ?>
    <div class="content col-lg-9" id="content">


    <?php if(isset($_GET['ct'])) : ?>
    <div class="notification alert alert-success alert-dismissible mx-2 mt-2">
        Switched to <?php echo eduiland()->user->courseware_types[eduiland()->user->class_type]; ?>
        <button type="button" data-dismiss="alert" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
    </div>
    <?php endif; ?>


<?php endif; ?>
    <div id="sidebar-drop-menu" class="sidebar-drop-menu"></div>
<?php

// Limit access to only step 1 and all overview pages if demo account
if(
    !class_exists('Edu')
    || !eduiland()->user->demo
    || (EduHelpers::getCurrentPageDepth() !== 2
    || is_page('step-1')
    || is_page('a-setting')
    || is_page('history-of-innovation')
    || is_page('4-script-storming')
    || is_page('number-1'))
    || in_array(get_direct_parent_id(), [18, 961]) // Introduction / History pages
) {
    get_posts_loop();
} else {
    echo '<div class="mt-2 alert alert-warning" role="alert">This content has been disabled for demo accounts.</div>';
}

?>
</div>

<?php
if ( !$disable_sidebar ):
    get_sidebar();
endif;
    get_footer();
