<?php
    $featured = get_query_var('featured');
    $search_count = !empty($search_count) ? $search_count . '. ' : '';
?>

    <li>
    <div class="wrapper">
      <div class="posts-list-image">
        <?php get_template_part('templates/content/content', 'thumbnail'); ?>
      </div>
      <a href="<?php the_permalink() ?>" title="<?php the_title() ?>" class="posts-list-info">
        <h3><?php echo $search_count ?><?php the_title(); ?></h3>
        <p>
        <?php
        if( !$featured ) {
          the_excerpt();
        }
        ?>
        </p>
        <span class="posts-list-link"><?php the_readmore_text() ?></span>
      </a>
    </div>
    </li>
