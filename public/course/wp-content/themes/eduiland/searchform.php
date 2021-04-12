<?php
$search_value = '';
if (get_query_var('s')) {
    $search_value = get_query_var('s');
    $replacements = array('+', '"');
    $search_value = str_replace($replacements, '', $search_value);
}
?>

<form method="get" class="form searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
  <label for="s" class="screen-reader-text"><?php echo _x('Search', 'eduiland'); ?></label>
  <input type="search" name="s" placeholder="<?php echo $search_value ?>">
  <button type="submit" class="button-search"><span class="screen-reader-text"><?php echo _x( 'Search', 'submit button', 'eduiland' ); ?></span></button>
</form>
