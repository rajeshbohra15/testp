<?php
/**
 * Order methods.
 *
 * @package Orderable/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Orders class.
 */
class Orderable_Orders {
	/**
	 * Is orders page.
	 *
	 * @return bool
	 */
	public static function is_orders_page() {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen || 'edit-shop_order' !== $screen->id ) {
			return false;
		}

		return true;
	}
}