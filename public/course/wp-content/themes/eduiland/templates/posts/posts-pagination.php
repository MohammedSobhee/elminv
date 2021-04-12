<?php
global $wp_query;

$args_pag = array( // Pagination Arguments
  'end_size' => 3,
  'mid_size' => 3,
  'show_all' => false,
  'prev_text' => __('<span>Previous</span>&nbsp;'),
  'next_text' => __('&nbsp;<span>Next</span>'),
);
?>

<?php if($wp_query->max_num_pages > 1) : ?>
<div class="pagination-wrapper">
    <?php edu_bootstrap_pagination( $args_pag ); ?>
</div>
<?php endif; ?>
