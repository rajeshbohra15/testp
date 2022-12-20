<?php

/**
 * Register Theme Layout Section and change sidebar layout.
 * 
 * @package Estera
 * 
 */
function estera_register_layout_theme_customizer( $wp_customize )
{
    $wp_customize->add_section( 'layout_options', array(
        'title'       => esc_html__( 'Sidebar Layout', 'estera' ),
        'description' => esc_html__( 'Override global sidebar options for specific site content - Go Pro Version', 'estera' ),
    ) );
    /* 
     * Default Sidebar Layout
     * 
     */
    $wp_customize->add_setting( 'default_sidebar_layout', array(
        'default'           => 'one',
        'sanitize_callback' => 'estera_sanitize_select',
    ) );
    $wp_customize->add_control( 'default_sidebar_layout', array(
        'label'       => esc_html__( 'Default Sidebar Position', 'estera' ),
        'section'     => 'layout_options',
        'type'        => 'select',
        'choices'     => array(
        'one'   => esc_html__( 'Right Sidebar', 'estera' ),
        'two'   => esc_html__( 'Full-width', 'estera' ),
        'three' => esc_html__( 'Left Sidebar', 'estera' ),
    ),
        'description' => esc_html__( 'Change the sidebar position in the whole website. You can choose between right sidebar, full-width and left sidebar layout.', 'estera' ),
    ) );
}

add_action( 'customize_register', 'estera_register_layout_theme_customizer' );
function estera_sidebar_css()
{
    $sidebar_layout = get_theme_mod( 'default_sidebar_layout', 'one' );
    $page_layout = get_theme_mod( 'page_layout', 'one' );
    $post_layout = get_theme_mod( 'post_layout', 'one' );
    $post_archives_layout = get_theme_mod( 'post_archives_layout', 'one' );
    $shop_page_layout = get_theme_mod( 'shop_page_layout', 'one' );
    $single_product_page_layout = get_theme_mod( 'single_product_page_layout', 'one' );
    //Default Sidebar Layout
    
    if ( $sidebar_layout == 'three' ) {
        //left sidebar
        ?>

    <style type="text/css">
    .wrapper {
        flex-direction: row-reverse;
    }
    </style>

    <?php 
    } elseif ( $sidebar_layout == "two" ) {
        //fullwidth
        ?>
    <style type="text/css">
    #secondary {
        display: none;
    }
    .site-main {
        max-width: 100%;
        width: 100%;
    }
    </style>
    <?php 
    }
    
    //exit if no custom sidebar is enanled
    if ( !estera_is_custom_sidebar_layout() ) {
        return;
    }
}

add_action( 'wp_head', 'estera_sidebar_css' );