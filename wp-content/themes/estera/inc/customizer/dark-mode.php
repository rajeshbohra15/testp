<?php
/*
 * Night Mode
 * @since version 0.1
 */

function estera_night_mode_customizer($wp_customize) {

    $wp_customize->add_section('night_mode', array(
        'title' => esc_html(__('Night Mode', 'estera')),
        'description' => esc_html(__('Customize the dark theme mode. For additional customizations, you can use the "dark-mode" body class and add the code to the Additional Css tab.', 'estera' )
	)));
	
	//Enable Dark Mode 
	$wp_customize->add_setting(
        'enable_dark_mode',
        array(
			'default' => 1,
			'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'enable_dark_mode',
        array(
            'label' => esc_html__('Enable Dark Mode', 'estera'),
            'section' => 'night_mode',
            'description' => esc_html__('Enable site visitors to switch to dark theme mode in the theme footer.', 'estera'),
            'type' => 'checkbox',
        )
	);
	
	//Change Dark Mode Colors

	$wp_customize->add_setting('dark_mode_background_color', array(
		'default'        => '#262626',
		'sanitize_callback' => 'sanitize_hex_color',
	   ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dark_mode_background_color', array(
	   'label'   => __('Background', 'estera'),
	   'section' => 'night_mode'
		)));
}

add_action('customize_register', 'estera_night_mode_customizer');

function estera_customize_night_mode_css() {

	$isDarkMode = get_theme_mod('enable_dark_mode', 1)? 'block': 'none';
    
    ?>

<style type="text/css">

.dark-mode .main-navigation-container, .dark-mode .container,
.dark-mode .sub-menu, .dark-mode .main-navigation ul ul {
    background-color: <?php echo esc_attr(get_theme_mod('dark_mode_background_color', "#262626")); ?>;
}

.dark-mode #nav-icon1 span {
    background-color: #fff;
}

.dark-mode h2.entry-title a, .dark-mode *, .dark-mode .main-navigation-container .main-navigation a,
.dark-mode .site-title a, .dark-mode .site-description, .dark-mode .more-link {
    color: #fff;
}

.dark-mode pre {
    background: #000;
}

.dark-mode .topcorner .icon_cart_alt, .dark-mode .topcorner i {
	color: #ff499a;
}

.dark-mode.woocommerce nav.woocommerce-pagination ul li a, 
.dark-mode.woocommerce nav.woocommerce-pagination ul li span {
    color: #fff;
}

</style>

	<?php
}

add_action( 'wp_head', 'estera_customize_night_mode_css');