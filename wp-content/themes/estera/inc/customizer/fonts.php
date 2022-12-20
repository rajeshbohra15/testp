<?php

/**
 * Register Fonts Section in the theme customizer.
 *
 * @package Estera
 *
 */
function estera_register_fonts_theme_customizer( $wp_customize ) {
    $wp_customize->add_section( 'font_section', array(
        'title'       => __( 'Fonts', 'estera' ),
        'description' => __( '700+ google fonts - Go Pro version.', 'estera' ),
    ) );
    $wp_customize->add_setting( 'headings_fontfamily', array(
        'default'           => 'Lato',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'headings_fontfamily', array(
        'label'       => __( 'Headings Font Family', 'estera' ),
        'section'     => 'font_section',
        'type'        => 'select',
        'choices'     => estera_font_family(),
        'description' => __('Choose font for the headlines.', 'estera'),
    ) );
    $wp_customize->add_setting( 'body_fontfamily', array(
        'default'           => 'Open Sans',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'body_fontfamily', array(
        'label'       => __( 'Body Font Family', 'estera' ),
        'section'     => 'font_section',
        'type'        => 'select',
        'choices'     => estera_font_family(),
        'description' => __('Choose font for the text.', 'estera'),
    ) );
    /* Regulate body size */
    $wp_customize->add_setting( 'body_font_size', array(
        'default'           => '16',
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'body_font_size', array(
        'label'       => __( 'Body Font Size', 'estera' ),
        'section'     => 'font_section',
        'type'        => 'number',
        'input_attrs' => array(
        'min'  => 8,
        'max'  => 30,
        'step' => 1,
    ),
        'description' => __('Change the size of the text. Enter a number in pixels between 8 and 30. Default is 16.', 'estera'),
    ) );
}

add_action( 'customize_register', 'estera_register_fonts_theme_customizer' );
function estera_font_family() {
    $google_fonts = array(
        "Times New Roman" => "Times New Roman, Sans Serif",
        "Open Sans"       => "Open Sans",
        "Roboto"          => "Roboto",
        "Lato"            => "Lato",
        "Oswald"          => "Oswald",
        "Alegreya"        => "Alegreya",
        "Dosis"           => "Dosis",
        "Montserrat"      => "Montserrat",
        "Raleway"         => "Raleway",
        "PT Sans"         => "PT Sans",
        "Lora"            => "Lora",
        "Noto Sans"       => "Noto Sans",
        "Concert One"     => "Concert One",
        "Nunito Sans"     => "Nunito Sans",
        "Oxygen"          => "Oxygen",
        "Work Sans"       => "Work Sans",
    );
    return $google_fonts;
}

/**
 * Register custom fonts.
 * Combine headings and body custom fonts in one http request.
 * @link https://wordpress.org/themes/new-york-business/
 */
function estera_fonts_url() {
    $fonts_url = '';
    /*
     * Translators: If there are characters in your language that are not
     * supported by "Open Sans", sans-serif;, translate this to 'off'. Do not translate
     * into your own language.
     */
    $typography = _x( 'on', 'Open Sans font: on or off', 'estera' );
    
    if ( 'off' !== $typography ) {
        $font_families = array();
        $font_families[] = wp_strip_all_tags( get_theme_mod( 'headings_fontfamily', 'Lato' ) ) . ':500,700,900';
        $font_families[] = wp_strip_all_tags( get_theme_mod( 'body_fontfamily', 'Open Sans' ) ) . ':300,400';
        $query_args = array(
            'family'  => urlencode( implode( '|', $font_families ) ),
            'subset'  => urlencode( 'latin,latin-ext' ),
            'display' => urlencode( 'swap' ),
        );
        $fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css' );
    }
    
    return esc_url( $fonts_url );
}

/**
 * Display custom font CSS.
 */
function estera_business_fonts_css_container() {
    ?>
<style type="text/css">
h1,
h2,
h3,
h4,
h5,
h6 {
    font-family: <?php echo esc_attr( get_theme_mod( 'headings_fontfamily', 'Lato' ) ) ;?>;
}

body {
    font-family: <?php echo esc_attr(get_theme_mod('body_fontfamily', 'Open Sans'));?>;
    font-size: <?php echo esc_attr(get_theme_mod('body_font_size', '16'));?>px;
}
</style>
<?php 
}

add_action( 'wp_head', 'estera_business_fonts_css_container' );