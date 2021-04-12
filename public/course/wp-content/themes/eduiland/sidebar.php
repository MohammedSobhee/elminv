<div class="sidebar-wrapper col-lg-3">
<div class="sidebar" id="sidebar">
    <?php
    if(class_exists('Edu')) {
        //
        // Side navigation
        // --------------------------------------------------------------------------
        get_template_part('templates/sidebar/navigation');

        //
        // Get custom assignments
        // --------------------------------------------------------------------------
        if(!is_posts() && !is_search()) {
            get_template_part('templates/sidebar/assignments');
        }
    }

    //
    // Dyanmic Sidebar Widgets
    // --------------------------------------------------------------------------
   if (is_active_sidebar('sidebar-widget')) : ?>
        <aside class="sidebar-aside">
            <?php dynamic_sidebar('sidebar-widget'); ?>
        </aside>
    <?php endif; ?>

</div>
</div>
