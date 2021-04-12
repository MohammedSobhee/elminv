<?php
if (!defined('ABSPATH')) exit;

//
// Theme setup
// --------------------------------------------------------------------------
add_action('after_setup_theme', 'eduiland_theme_setup');
function eduiland_theme_setup() {
    $GLOBALS['content_width'] = 880;

    load_theme_textdomain('eduiland'); // load text domain for translations
    add_theme_support('custom-logo', [
        'width' => 250,
        'height' => 250,
        'flex-width' => true
    ]); // Add custom logo
    add_theme_support('html5', [
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'search-form'
    ]); // Switch to HTML5 markup
    add_theme_support('title-tag'); // Auto Title Tags
    add_theme_support('post-thumbnails'); // Add Featured Image Support
    //add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link', 'gallery', 'audio' ) ); // Enable support for Post Formats
    add_theme_support('customize-selective-refresh-widgets'); // Add selective refresh for widgets
    add_image_size('extra-large', 2280, 9999); // Add extra large image size
}

//
// Content Width
// --------------------------------------------------------------------------
add_action('template_redirect', 'eduiland_content_width', 0);
function eduiland_content_width() {
    $content_width = $GLOBALS['content_width'];
    if (class_exists('ACF')) {
        if (!get_field('disable_sidebar')) {
            $content_width = 1220;
        }
    }
    $GLOBALS['content_width'] = $content_width;
}

// Sidebar Widget
add_action('widgets_init', 'eduiland_widgets_init');
function eduiland_widgets_init() {
    register_sidebar([
        'name' => __('Sidebar Navigation', 'eduiland'),
        'id' => 'eduiland-sidebar-widget'
    ]);
}

//
// Menus
// --------------------------------------------------------------------------
add_action('init', 'eduiland_register_menus');
function eduiland_register_menus() {
    register_nav_menus([
        'header-menu' => __('Header Menu', 'eduiland')
    ]);
}

// Load Styles and Scripts
// --------------------------------------------------------------------------
add_action('wp_enqueue_scripts', 'eduiland_scripts');
function eduiland_scripts() { // Scripts

    // Styles
    wp_enqueue_style('eduiland-style', get_template_directory_uri() . '/assets/css/site.css', false, filemtime(get_template_directory() . '/assets/css/site.css'));
    wp_enqueue_style('eduiland-google-fonts', 'https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,600,600ii&display=swap', false);

    // Add elementor lib on non-elementor search results page
    if (is_search()) {
        wp_enqueue_style('font-awesome-5-all-css', get_site_url() . '/wp-content/plugins/elementor/assets/lib/font-awesome/css/all.min.css', false);
        wp_enqueue_style('font-awesome-4-shim-css', get_site_url() . '/wp-content/plugins/elementor/assets/lib/font-awesome/css/v4-shims.min.css', false);
    }

    // Load font-awesome if not an elementor page
    if (!is_elementor()) {
        wp_enqueue_style('eduiland-fa', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css');
    }
}

//
// New Excerpt More symbol and Length
// --------------------------------------------------------------------------
add_filter('excerpt_more', 'eduiland_excerpt_more');
function eduiland_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_length', 'eduiland_excerpt_length', 999);
function eduiland_excerpt_length($length) {
    return 150;
}

add_filter('get_the_excerpt', 'eduiland_remove_urls_excerpt', 999);
function eduiland_remove_urls_excerpt($excerpt) {
    $regex = "/^http:\/\/|https:\/\/vimeo\.com\/[\w\/]+([\?].*)?/i";
    $excerpt = preg_replace($regex, '', $excerpt);
    return $excerpt;
}

//
// Change Login Logo
// --------------------------------------------------------------------------
add_action('login_enqueue_scripts', 'eduiland_login_logo');
function eduiland_login_logo() {?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url( <?php echo get_stylesheet_directory_uri() . '/assets/images/admin-logo.png'; ?>);
            height:60px;
            width:300px;
            background-size: contain;
            background-repeat: no-repeat;
        }
    </style>
<?php }


//
// Include required files
// --------------------------------------------------------------------------
require get_parent_theme_file_path('/functions/template-helpers.php');
require get_parent_theme_file_path('/functions/customizer.php');
