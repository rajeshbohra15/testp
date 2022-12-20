<?php

/**
 * Sample implementation of the Custom Header feature
 *
 * @link       https://developer.wordpress.org/themes/functionality/custom-headers/
 * @package Estera
 * 
 * @copyright  Copyright (c) 2020, Nasio Themes
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Set up the WordPress core custom header feature.
 *
 * @uses estera_header_style()
 */

/* Create the header image object */

function estera_custom_header_setup() {
    $args = array(
        'default-text-color' => '333',
        'width'              => 2000,
		'height'             => 220,
        'flex-width'         => true,
        'flex-height'        => true,
        'wp-head-callback'   => 'estera_header_image_css',
    );
    add_theme_support( 'custom-header', $args );
}
add_action( 'after_setup_theme', 'estera_custom_header_setup' );

/* Add control to Show or hide the header image on Homepage */
    
function estera_header_image_options_customize_register( $wp_customize ){

    $wp_customize->add_setting(
        'show-header-image-homepage',
        array(
            'default' => 0,
            'sanitize_callback' => 'estera_sanitize_checkbox',
        )
    );

    $wp_customize->add_control(
        'show-header-image-homepage',
        array(
            'label' => esc_html__('Disable Header Image on Homepage', 'estera'),
            'section'  => 'header_image',
            'description' => esc_html__('If you prefer to use the theme homepage slider, you can choose to hide the header image on the homepage but keep it on all other site pages.', 'estera'),
            'type' => 'checkbox'
        )
    );

}

add_action( 'customize_register', 'estera_header_image_options_customize_register' );

/* Style the custom header image */

function estera_header_image_css() {

	$header_text_color = get_header_textcolor();
    $height = get_theme_mod( 'header-background-height', '220px' );
	$repeat = get_theme_mod( 'header-background-repeat', 'no-repeat' );
	$hide_header_image_homepage = get_theme_mod ('show-header-image-homepage', 0);
    
    ?>
    
<style type="text/css">

.site-title a, .site-description {
	color: #<?php echo esc_attr($header_text_color); ?>;
}

<?php if ( has_header_image() ) :
	
	if ( is_front_page() && !$hide_header_image_homepage || !is_front_page() ) : //Show custom header image and site title if header image is specified ?>
    .header-image-wrapper {
		background-image: url(<?php header_image(); ?>);
        height: <?php echo esc_attr( $height ); ?>;
        background-repeat: <?php echo esc_attr ( $repeat ); ?>;
        background-size: cover;
        background-position: center;
        position: relative;
        padding-bottom: 2em;
    }
	<?php endif; 

endif; ?>

</style>
<?php
}