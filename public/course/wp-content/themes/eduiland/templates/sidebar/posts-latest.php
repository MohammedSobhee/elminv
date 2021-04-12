<h3  id="sidebar-header-updates" class="sidebar-header"><a href="/course/updates">Latest Updates</a></h3>
<ul id="sidebar-posts-anchors" class="sidebar-menu">
<?php
$posts_query = new WP_Query('posts_per_page=5');
    while ($posts_query->have_posts()):
        $posts_query->the_post();
    ?>
    <li class="page_item"><a href="<?php the_permalink()?>"><?php the_title(); ?></a></li>

    <?php
    endwhile;
    wp_reset_postdata();
?>
</ul>
