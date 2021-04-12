<?php
// if(is_posts())
//     get_template_part('templates/sidebar/posts-latest');

if( !is_search() && !is_posts())
    EduHelpers::getChildPages();

// Get nav from session
if(is_search() || is_posts() || strpos(eduiland()->url, 'resources') !== false)
    EduSidebarSessionNav::show();
