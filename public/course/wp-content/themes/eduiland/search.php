<?php
get_header();
global $wp_query;
$paged = get_query_var( 'paged' );
if ( $paged > 1 ) {
    $search_count = $paged * 10 - 10;
} else {
    $search_count = 0;
}
?>

<div class="content content-search col-md-9" id="content">
    <strong class="search-results"><?php echo $wp_query->found_posts . __( ' results found', 'eduiland' ) . ' for \'' . get_query_var('s') . '\''?></strong>

        <?php
        if (have_posts()) {
        while (have_posts()) {
            the_post();
            $search_count++;
            get_template_part( 'templates/content/content', 'title-search' );
            get_template_part( 'templates/content/content', 'excerpt' );
        }
        }
        get_template_part('templates/posts/posts', 'pagination');
    ?>
</div>

<?php
get_sidebar();
get_footer();
