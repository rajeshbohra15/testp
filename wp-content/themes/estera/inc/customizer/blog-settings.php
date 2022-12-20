<?php

/**
 * Register Blog Settings Section in the theme customizer.
 *
 * @package Estera
 *
 */
function estera_register_blog_theme_customizer( $wp_customize )
{
    $wp_customize->add_section( 'blog_options', array(
        'title'       => esc_html__( 'Blog Settings', 'estera' ),
        'description' => esc_html__( 'Customize the way blog posts in post archives are displayed. Display posts in multi-column layout - go pro version.', 'estera' ),
    ) );
    $wp_customize->add_setting( 'show_post_categories', array(
        'default'           => 1,
        'sanitize_callback' => 'estera_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'show_post_categories', array(
        'label'       => esc_html__( 'Show post categories', 'estera' ),
        'description' => esc_html__( 'Show the categories, associated to the post.', 'estera' ),
        'section'     => 'blog_options',
        'type'        => 'checkbox',
    ) );
    /* Show tags entry meta */
    $wp_customize->add_setting( 'show_post_tags', array(
        'default'           => 1,
        'sanitize_callback' => 'estera_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'show_post_tags', array(
        'label'       => esc_html__( 'Show post tags', 'estera' ),
        'description' => esc_html__( 'Show the tags, associated to the post.', 'estera' ),
        'section'     => 'blog_options',
        'type'        => 'checkbox',
    ) );
    /* Show Published date entry meta */
    $wp_customize->add_setting( 'show_post_date', array(
        'default'           => 1,
        'sanitize_callback' => 'estera_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'show_post_date', array(
        'label'       => esc_html__( 'Show post date', 'estera' ),
        'description' => esc_html__( 'Show the published date of the post.', 'estera' ),
        'section'     => 'blog_options',
        'type'        => 'checkbox',
    ) );
    /* Show Published date entry meta */
    $wp_customize->add_setting( 'show_post_author', array(
        'default'           => 1,
        'sanitize_callback' => 'estera_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'show_post_author', array(
        'label'       => esc_html__( 'Show post author', 'estera' ),
        'description' => esc_html__( 'Show the published date of the post.', 'estera' ),
        'section'     => 'blog_options',
        'type'        => 'checkbox',
    ) );
    $wp_customize->add_setting( 'show_post_comments', array(
        'default'           => 1,
        'sanitize_callback' => 'estera_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'show_post_comments', array(
        'label'       => esc_html__( 'Show Comments', 'estera' ),
        'description' => esc_html__( 'Display the number of comments.', 'estera' ),
        'section'     => 'blog_options',
        'type'        => 'checkbox',
    ) );
    // Add Settings and Controls for blog content.
    $wp_customize->add_setting( 'post_archives_content', array(
        'default'           => 1,
        'sanitize_callback' => 'estera_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'post_archives_content', array(
        'label'       => esc_html__( 'Blog Excerpts', 'estera' ),
        'description' => esc_html__( 'Show post excerpts instead of full content for your post archives', 'estera' ),
        'section'     => 'blog_options',
        'type'        => 'checkbox',
    ) );
    // Add Setting and Control for Excerpt Length.
    $wp_customize->add_setting( 'estera_excerpt_length', array(
        'default'           => 55,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'estera_excerpt_length', array(
        'label'           => esc_html__( 'Post Excerpt Length', 'estera' ),
        'section'         => 'blog_options',
        'type'            => 'number',
        'input_attrs'     => array(
        'min'  => 10,
        'max'  => 55,
        'step' => 1,
    ),
        'description' => esc_html__('Enter a number between 10 and 55. Default is 55.', 'estera'),
        'active_callback' => 'estera_is_excerpt',
    ) );
}

add_action( 'customize_register', 'estera_register_blog_theme_customizer' );