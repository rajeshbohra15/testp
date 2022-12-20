<?php

/**
 * Estera Theme Customizer
 *
 * @package Estera
 */
/* Call Custom Sanitization Functions */
require get_template_directory() . '/inc/customizer/sanitization-functions.php';
/* Call Callback Functions  */
require get_template_directory() . '/inc/customizer/callback.php';
/* Call Homepage Slider Options */
require get_template_directory() . '/inc/customizer/slider.php';
/* Call Layout Options */
require get_template_directory() . '/inc/customizer/sidebar-layout.php';
/* Fonts */
require get_template_directory() . '/inc/customizer/fonts.php';
/* Blog settings */
require get_template_directory() . '/inc/customizer/blog-settings.php';
/* Colors settings */
require get_template_directory() . '/inc/customizer/colors.php';
/* Footer settings */
require get_template_directory() . '/inc/customizer/footer.php';
/* Dark Mode */
require get_template_directory() . '/inc/customizer/dark-mode.php';
require get_template_directory() . '/inc/customizer/go-pro.php';