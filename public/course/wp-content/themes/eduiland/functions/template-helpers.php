<?php
//
// Check if inside Wordpress course pages
// --------------------------------------------------------------------------
function is_wordpress() {
    return strpos($_SERVER['REQUEST_URI'], 'course') !== false;
}

//
// Is Laravel Admin
// --------------------------------------------------------------------------
function is_lara_admin() {
    return is_wordpress() && class_exists('Edu') && EduHelpers::isLaraAdmin();
}

//
// Check if built in elementor
// --------------------------------------------------------------------------
function is_elementor() {
    /** @var string|object $post */
    if(is_wordpress()) {
        global $post;
        if ( defined( 'ELEMENTOR_PATH' ) && is_object($post) ) {
            if ( Elementor\Plugin::$instance->db->is_built_with_elementor( $post->ID ) ) {
                return true;
            }
        }
        return false;
    }
    return false;
}

//
// Get parent id
// --------------------------------------------------------------------------
function get_parent_id() {
    /** @var string|object $post */
    global $post;
    if ( $post->post_parent ) {
        $ancestors = get_post_ancestors( $post->ID );
        $root = count( $ancestors ) - 1;
        $parent = $ancestors[$root];
    } else {
        $parent = $post->ID;
    }
    return $parent;
}

function get_direct_parent_id() {
    /** @var string|object $post */
    global $post;
    return $post->post_parent;
}

//
// Get Template Loop
// --------------------------------------------------------------------------
function get_posts_loop( $options = array() ) {
    if ( have_posts() ) {
        while ( have_posts() ) {
            the_post();
            get_template_part( 'templates/content/content' );
        }
    } else {
        get_template_part( 'templates/content/content', 'not-found' );
    }
    if ( in_array( 'pagination', $options ) ) {
        get_template_part( 'templates/posts/posts', 'pagination' );
    } elseif ( in_array( 'previous-next', $options ) ) {
        get_template_part( 'templates/posts/posts', 'prev-next' );
    }
}

//
// Get the title
// --------------------------------------------------------------------------
function get_which_title() {
    /** @var string|object $post */
    global $post;
    if(!is_object($post)) {
        return;
    }
    if (class_exists('ACF')) {
        $alt_title = get_field( 'heading_alternative', $post->ID );
    }
    if ( $alt_title ) {
        $title = $alt_title;
    } else {
        $title = get_the_title( $post->ID );
    }
    return apply_filters( 'the_title', $title );
}

//
// Get Category Name
// --------------------------------------------------------------------------
function the_category_name() {
    foreach ( ( get_the_category() ) as $category ) {
        echo $category->cat_name . ' ';
    }
}

function get_category_name() {
    $category = explode( '/', get_query_var( 'category_name' ) );
    if ( $category ) {
        $category = $category[0]; // print_r( $category ); to see everything
        $category = ucwords( str_replace( '-', ' ', $category ) );
    }
    return $category;
}

//
// Get page id by slug
// --------------------------------------------------------------------------
function get_pageid_by_slug( $slug ) {
    $page = get_page_by_path( $slug );
    $pageid = !empty( $page->ID ) ? $page->ID : null;
    return $pageid;
}

//
// Get first image in a post
// --------------------------------------------------------------------------

// Get first image in a post (new)
function get_first_image( $post_id, $size = 'medium' ) {
    /** @var string|object $post */
    global $post;
    $args = array(
        'posts_per_page' => 1,
        'order' => 'ASC',
        'post_mime_type' => 'image',
        'post_parent' => $post_id,
        'post_status' => null,
        'post_type' => 'attachment',
    );
    $attachments = array_values( get_children( $args ) );

    if ( $attachments[0] ) {
        $img = wp_get_attachment_image_src( $attachments[0]->ID, $size, true );
        if ( !empty( $img ) ) {
            return $img[0];
        }

    } else {
        ob_start();
        ob_end_clean();
        $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
        if ( !empty( $matches[1][0] ) ) {
            return $matches[1][0];
        }
    }
    return false;
}

//
// Get Featured image or First image in content or load default
// --------------------------------------------------------------------------
function get_thumbnail_url( $size = 'medium_large' ) {
    /** @var string|object $post */
    global $post;
    $size = empty( $size ) ? 'medium_large' : $size;
    $thumb_url = get_the_post_thumbnail_url( $post->ID, $size );

    if ( empty( $thumb_url ) ) {
        $first_img = get_first_image( $post->ID, $size );

        if ( empty( $first_img ) ) {
            $thumb_id = get_theme_mod( 'eduiland_default_thumbnail' );
            $thumb_url_array = wp_get_attachment_image_src( $thumb_id, $size, true );

            $thumb_url = !strpos( $thumb_url_array[0], 'wp-includes' ) ? $thumb_url_array[0] : 0;
            if ( !$thumb_url ) {
                $thumb_url = get_template_directory_uri() . '/assets/images/default_thumbnail.png';
            }

        } else {
            $thumb_url = $first_img;
        }
    }
    return $thumb_url;
}
function the_thumbnail_url( $size = 'medium_large' ) {
    echo get_thumbnail_url( $size );
}


//
// Get read more text
// --------------------------------------------------------------------------
function get_readmore_text() {
    $text = get_theme_mod( 'eduiland_read_more' );
    $text = !empty( $text ) ? $text : __( 'Read more' );
    return $text;
}
function the_readmore_text() {
    echo get_readmore_text();
}

//
// Get Prev / Next text from Customizer
// --------------------------------------------------------------------------
function get_prevnext_text() {
    $text = get_theme_mod( 'eduiland_prev_next' );
    $text = ( !empty( $text ) && $text !== 'none' ) ? $text : '';
    return $text;
}

function the_prevnext_text() {
    echo get_prevnext_text();
}

//
// Determine if blog post
// --------------------------------------------------------------------------
function is_posts() {
    return get_post_type() == 'post' || is_home();
}


//
// Number to roman numeral
// --------------------------------------------------------------------------
function roman_numeral($number) {
    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}

//
// Bootstrap Walker Menu
// --------------------------------------------------------------------------
class BootstrapMenu extends Walker_Nav_Menu {
    function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

      if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
        $t = '';
        $n = '';
      } else {
        $t = "\t";
        $n = "\n";
      }
      $indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

      $classes = empty( $item->classes ) ? array() : (array) $item->classes;
      $classes[] = 'menu-item-' . $item->ID;

      // Filters the arguments for a single nav menu item
      $args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

      // Filters the CSS class(es) applied to a menu item's list item element
      $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
      $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

      // Filters the ID applied to a menu item's list item element
      $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
      $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

      //$output .= $indent . '<li' . $id . $class_names .'>';

      // it would be better to enter the class in Appearance -> Menus -> Screen Options -> CSS classes
      $output .= $indent . '<li' . $id . $class_names .'>';
      //$output .= $indent . '<li class="nav-item">';

      $atts = array();
      $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
      $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
      $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
      $atts['href']   = ! empty( $item->url )        ? $item->url        : '';

      // Filters the HTML attributes applied to a menu item's anchor element
      $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

      $attributes = '';
      foreach ( $atts as $attr => $value ) {
        if ( ! empty( $value ) ) {
          $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
          $attributes .= ' ' . $attr . '="' . $value . '"';
        }
      }
      $title = apply_filters( 'the_title', $item->title, $item->ID );

      // Filters a menu item's title
      $title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

      $item_output = $args->before;
      $item_output .= '<a class="dropdown-item"'. $attributes .'>';
      $item_output .= $args->link_before . $title . $args->link_after;
      $item_output .= '</a>';
      $item_output .= $args->after;

      $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}


//
//
// --------------------------------------------------------------------------
function edu_bootstrap_pagination( $args = [], $wp_query = false, $echo = true) {
    //Fallback to $wp_query global variable if no query passed
    if (false === $wp_query) {
        global $wp_query;
    }

    //Set Defaults
    $defaults = [
        //'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format' => '?paged=%#%',
        //'current' => max(1, get_query_var('page')),
        'total' => $wp_query->max_num_pages,
        'type' => 'array',
        'show_all' => false,
        'end_size' => 2,
        'mid_size' => 1,
        'prev_text' => __('« Prev'),
        'next_text' => __('Next »'),
        'add_fragment' => ''
    ];

    //Merge the defaults with passed arguments
    $merged = wp_parse_args($args, $defaults);

    //Get the paginated links
    $lists = paginate_links($merged);

    if (is_array($lists)) {

        $html = '<nav><ul class="pagination justify-content-center">';

        foreach ($lists as $list) {
            $html .= '<li class="page-item' . (strpos($list, 'current') !== false ? ' active' : '') . '"> ' . str_replace('page-numbers', 'page-link', $list) . '</li>';
        }

        $html .= '</ul></nav>';

        if ($echo) {
            echo $html;
        } else {
            return $html;
        }
    }
    return false;
}
