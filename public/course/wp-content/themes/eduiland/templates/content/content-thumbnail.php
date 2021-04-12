<?php
$image_size = ( get_query_var( 'image_size' ) ) ? get_query_var( 'image_size' ) : '';

// Use link on all but single blog posts
if ( is_single() ) {
    echo '<div';
    echo ' class="post-featured-image" ';
} else {
    echo '<a href="';
    the_permalink();
    echo '" class="thumbnail" ';
}

echo 'style="background-image:url( ';
echo get_thumbnail_url( $image_size );
echo ' )"><span class="ir">Image</span>';

if ( is_single() ) {
    echo '</div>';
} else {
    echo '</a>';
}
