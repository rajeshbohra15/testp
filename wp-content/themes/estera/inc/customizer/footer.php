<?php

function estera_register_footer_customizer( $wp_customize )
{
    $wp_customize->add_section( 'custom_footer', array(
        'title'       => __( 'Footer', 'estera' ),
        'description' => __( 'Edit Footer Credits - Go Pro version', 'estera' ),
    ) );
    /* Footer Background Color */
    $wp_customize->add_setting( 'footer_background_color', array(
        'default'           => "#262626",
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_background_color', array(
        'label'   => esc_html__( 'Footer Background Color', 'estera' ),
        'section' => 'custom_footer',
    ) ) );
    // Footer text color
    $wp_customize->add_setting( 'footer_text_color', array(
        'default'           => "#888",
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_text_color', array(
        'label'   => esc_html__( 'Footer Text Color', 'estera' ),
        'section' => 'custom_footer',
    ) ) );
    /* Footer Links Color */
    $wp_customize->add_setting( 'footer_link_color', array(
        'default'           => "#f5f5f5",
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_link_color', array(
        'label'   => esc_html__( 'Footer Links Color', 'estera' ),
        'section' => 'custom_footer',
    ) ) );
}

add_action( 'customize_register', 'estera_register_footer_customizer' );
function estera_footer_customize_css()
{
    ?>
    
    <style type="text/css">

    .site-footer {
        background: <?php 
    echo  esc_attr( get_theme_mod( 'footer_background_color', '#262626' ) ) ;
    ?>;
        color: <?php 
    echo  esc_attr( get_theme_mod( 'footer_text_color', '#888' ) ) ;
    ?>;
    }

    .site-footer a {
        color: <?php 
    echo  esc_attr( get_theme_mod( 'footer_link_color', '#f5f5f5' ) ) ;
    ?>;
    }

    </style>

    <?php 
}

add_action( 'wp_footer', 'estera_footer_customize_css' );