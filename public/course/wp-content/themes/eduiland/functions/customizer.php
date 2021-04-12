<?php
// ==========================================================================
// Customizer
// ==========================================================================

add_action( 'customize_register', 'eduiland_customize_register' );
function eduiland_customize_register( $wp_customize ) {

  //
  // Add Theme Options Panel
  // --------------------------------------------------------------------------
  $wp_customize->add_panel( 'eduiland_theme_options', array(
    'priority' => 30,
    'title' => get_bloginfo() . __( ' Theme Options', 'eduiland' ),
    'description' => __( 'Add Defaults', 'eduiland' ),
    'capability' => 'edit_theme_options',
    'theme_supports' => '',
  ) );

  //
  // Default Thumbnail section
  // --------------------------------------------------------------------------
  $wp_customize->add_section( 'eduiland_images', array(
    "title" => __(  'Default thumbnail', 'eduiland' ),
    'capability' => 'edit_theme_options',
    'transport' => 'postMessage',
    'theme_supports' => '',
    'description' => '',
    'panel' => 'eduiland_theme_options',
  ) );

  //
  // Theme Language section
  // --------------------------------------------------------------------------
  $wp_customize->add_section( 'eduiland_language', array(
    "title" => __(  'Theme Language', 'eduiland' ),
    'capability' => 'edit_theme_options',
    'transport' => 'postMessage',
    'theme_supports' => '',
    'description' => 'Language used throughout the theme',
    'panel' => 'eduiland_theme_options',
  ) );



  //
  // Set Default thumbnail
  // --------------------------------------------------------------------------
  $wp_customize->add_setting( 'eduiland_default_thumbnail', array(
    'type' => 'theme_mod',
    'default' => '',
    'transport' => 'postMessage',
    'capability' => 'edit_theme_options',
    'sanitize_callback' => 'absint',
  ) );

  $wp_customize->add_control(
    new WP_Customize_Media_Control(  $wp_customize, 'eduiland_default_thumbnail', array(
    'label' => __( 'Default thumbnail', 'eduiland' ),
    'section' => 'eduiland_images',
    'mime_type' => 'image',
    'description' => __( 'This image will be used when a post or video has no featured image or images placed in content.', 'eduiland' ),
   ) ) );

  //
  // Set Read more text
  // --------------------------------------------------------------------------
  $wp_customize->add_setting( 'eduiland_read_more', array(
    'type' => 'theme_mod',
    'default' => 'Read more',
    'transport' => 'postMessage',
    'capability' => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_text_field',
   ) );

  $wp_customize->add_control( 'eduiland_read_more', array(
    'type'    => 'text',
    'label'   => 'Read more text',
    'section' => 'eduiland_language',
    'description' => __( 'The "read more" link text used for blog post snippets in post listings.', 'eduiland' ),
    'input_attrs' => array(
      'placeholder' => __( 'Read more', 'eduiland' ),
     )
   ) );

   //
  // Set Previous / Next Next
  // --------------------------------------------------------------------------
  $wp_customize->add_setting( 'eduiland_prev_next', array(
    'type' => 'theme_mod',
    'default' => 'Post',
    'transport' => 'postMessage',
    'capability' => 'edit_theme_options',
    'sanitize_callback' => 'eduiland_sanitize_select_text',
   ) );

  $wp_customize->add_control( 'eduiland_prev_next', array(
    'type'    => 'select',
    'label'   => 'Previous / Next Text',
    'section' => 'eduiland_language',
    'settings'   => 'eduiland_prev_next',
    'description' => __( 'The "Previous / Next" link text used for blog posts.', 'eduiland' ),
    'choices' => array(
      '' => __( 'Select preference:', 'eduiland' ),
      'Post' => __( 'Post', 'eduiland' ),
      'Article' => __( 'Article', 'eduiland' ),
      'Page' => __( 'Page', 'eduiland' ),
      'none' => __( 'Display nothing', 'eduiland' ),
     ),
   ) );
}

function eduiland_sanitize_select_text(  $input, $setting  ) {
  $input = ucfirst( sanitize_key( $input ) );
  $choices = $setting->manager->get_control( $setting->id )->choices;
  return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}
