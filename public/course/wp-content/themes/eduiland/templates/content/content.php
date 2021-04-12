<?php
//
// Check which CSS class to use
// --------------------------------------------------------------------------
if ( is_search() || is_archive() ) {
    $css = "post search-listing";
} elseif ( is_page() ) {
    $css = "page";
} else {
    $css = "post";
}
?>

<div class="<?php echo $css . (is_home() ? ' announcements' : '') ?>" id="post-<?php the_ID(); ?>"  <?php post_class(); ?>>

<?php
//
// Post Info
// --------------------------------------------------------------------------
if (is_posts()) {
    get_template_part('templates/content/content', 'title');
    get_template_part('templates/content/content', 'post-info');
}

//
// The Content
// --------------------------------------------------------------------------
get_template_part( 'templates/content/content', 'content' );
?>

</div>
