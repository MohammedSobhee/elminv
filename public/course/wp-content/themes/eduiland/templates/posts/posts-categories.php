<?php
  $cat_id = get_query_var('category');
  $tabbed = get_query_var('page-list-tabbed') ? ' tabbed' : '';
  $cs = (is_home() || is_page('news')) ? ' class="active"' : '';
?>
<div class="page-list-navigation<?php echo $tabbed?>">
  <div class="content-container">
    <ul>

      <?php if($tabbed) : ?>
      <li class="active"><a href="#" data-tab="all"><span>View All</span></a></li>

      <?php else : ?>
      <li<?php echo $cs?>><a href="/news/all/"><span>View All</span></a></li>
      <?php endif;

      $terms = get_terms('category');
      if(!empty($terms) && !is_wp_error($terms)) :
        foreach ($terms as $term) :
          $term_name = strtolower($term->name);
          $cs = ($term->term_id === $cat_id) ? ' class="active"' : '';
          if($tabbed && !is_paged())
            echo '<li><a href="#" data-tab="' . $term_name . '">' . $term->name . '</a></li>';
          else
            echo '<li'.$cs.'><a href="/news/category/'.$term_name.'">' . $term->name . '</a></li>';
        endforeach;
      endif;
      ?>
    </ul>
  </div>
</div>
