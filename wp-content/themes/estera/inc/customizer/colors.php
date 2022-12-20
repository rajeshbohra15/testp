<?php
/*
 ** Allow users to change theme colors through the WordPress Customizer
 */

function estera_customize_colors($wp_customize) {

    $wp_customize->get_section('colors')->description = esc_html__( 'Customze the colors of the light theme mode. To customize the dark theme mode, go to the Night Mode section.', 'estera');

    //Primary menu text color
    $wp_customize->add_setting('top_menu_text_color', array(
        'default' => '#101010',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'top_menu_text_color', array(
        'label' => esc_html__('Top Menu Text Color', 'estera'),
        'section' => 'colors',
    )));

    //Primary menu background color
    $wp_customize->add_setting('top_menu_background_color', array(
        'default' => '#fff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'top_menu_background_color', array(
        'label' => esc_html__('Top Menu Background Color', 'estera'),
        'section' => 'colors',
    )));

    // Headings color
    $wp_customize->add_setting('headings_textcolor', array(
        'default' => "#161616",
		'sanitize_callback' => 'sanitize_hex_color'
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'headings_textcolor', array(
        'label' => esc_html__('Headings Text Color', 'estera'),
        'section' => 'colors',
    )));

    // Links color
    $wp_customize->add_setting('links_textcolor', array(
        'default' => "#1e73be",
		'sanitize_callback' => 'sanitize_hex_color'
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'links_textcolor', array(
        'label' => esc_html__('Links Text Color', 'estera'),
        'section' => 'colors',
    )));

    // Sidebar Links Text color
    $wp_customize->add_setting('sidebar_link_textcolor', array(
        'default' => "#888",
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sidebar_link_textcolor', array(
        'label' => esc_html__('Sidebar Links Color', 'estera'),
        'section' => 'colors',
    )));

}
add_action('customize_register', 'estera_customize_colors');

function estera_customize_colors_css() {
    $header_text_color = get_theme_mod('header_textcolor'); ?>

<style type="text/css">
body h1,
body h2,
body h3,
h2.entry-title a {
    color: <?php echo esc_attr(get_theme_mod('headings_textcolor', '#161616'));?>;
}
a{
    color: <?php echo esc_attr(get_theme_mod('links_textcolor', '#1e73be'));?>;
}
.widget-area .widget ul a {
    color: <?php echo esc_attr(get_theme_mod('sidebar_link_textcolor', '#888'));
    ?>;
}
.main-navigation-container {
    background-color: <?php echo esc_attr(get_theme_mod('top_menu_background_color', "#fff"));
    ?>;
}
.main-navigation-container .main-navigation a {
    color: <?php echo esc_attr(get_theme_mod('top_menu_text_color', "#101010")); ?>
}

</style>

<?php
}
add_action('wp_head', 'estera_customize_colors_css');