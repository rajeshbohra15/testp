<?php
/**
 * Customizer callback functions for active_callback.
 * 
 * Use these functions to show or hide customizer options.
 *
 * @package Estera
 */

 
function estera_is_select_product (){
    return 'product' == get_theme_mod( 'select_slider_from', 'product' );
}

function estera_is_select_post (){
    return 'post' == get_theme_mod( 'select_slider_from', 'post' );
}

/* Callback to check if WooCommerce plugin is active */
function estera_is_woocommerce_active () {
    if (class_exists('WooCommerce')==true) :
         return true;
    else :
        return false;
    endif;
}

/* 
 * Callback to show excerpt or full post content on archive pages 
 * Based on theme customizer
 */
function estera_is_excerpt (){
	$estera_post_excerpt = get_theme_mod( 'post_archives_content', 1 );
	if ($estera_post_excerpt) :
		return true;
	else :
		return false;
	endif;
}

/* 
 * Callback to show excerpt or full post content on archive pages 
 * Based on theme customizer
 */
function estera_remove_post_content (){
	$estera_post_content = get_theme_mod( 'remove_post_content', 0 );
	if ($estera_post_content) :
		return true;
	else :
		return false;
	endif;
}

/* Callback for custom sidebar options for specific pages */

function estera_is_custom_sidebar_layout (){

    $is_custom_sidebar_layout = get_theme_mod( 'custom_sidebar_layout', 0 );
    if($is_custom_sidebar_layout) :
    	return true;
	else :
		return false;
    endif;
}