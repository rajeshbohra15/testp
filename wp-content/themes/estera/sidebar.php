<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Estera
 */

if (!is_active_sidebar('sidebar-1') && !is_active_sidebar('sidebar-2') &&
	!is_active_sidebar('sidebar-3') && !is_active_sidebar('sidebar-woocommerce')) {
	return;
}
?>

<aside id="secondary" class="widget-area"> <?php

//page sidebar
if (is_page() && is_active_sidebar('sidebar-2')) {
	dynamic_sidebar('sidebar-2');
}
//woocommerce shop sidebar
elseif (class_exists('Woocommerce') && is_product()) {
	if (is_active_sidebar('sidebar-woocommerce')) {
		dynamic_sidebar('sidebar-woocommerce');
	} else {
		dynamic_sidebar('sidebar-1');
	}
}
//woocommerce shop sidebar
elseif (class_exists('Woocommerce') && is_shop()) {
	if (is_active_sidebar('sidebar-woocommerce')) {
		dynamic_sidebar('sidebar-woocommerce');
	} else {
		dynamic_sidebar('sidebar-1');
	}
}
//post sidebar
elseif (is_single()) {
	if (is_active_sidebar('sidebar-3')) {
		dynamic_sidebar('sidebar-3');
	} else {
		dynamic_sidebar('sidebar-1');
	}
}
//global sidebar
else {
	dynamic_sidebar('sidebar-1');
}?>

</aside><!-- #secondary -->