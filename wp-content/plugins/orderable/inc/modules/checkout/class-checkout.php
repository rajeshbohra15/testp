<?php
/**
 * Module: Checkout.
 *
 * @package Orderable/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checkout module class.
 */
class Orderable_Checkout {
	/**
	 * Init.
	 */
	public static function run() {
		add_filter( 'wpsf_register_settings_orderable', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Register settings.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function register_settings( $settings = array() ) {
		$settings['tabs'][] = array(
			'id'		 => 'checkout',
			'title'		 => __( 'Checkout Settings', 'orderable-pro' ),
			'priority'	 => 20,
		);

		$settings['sections'][] = array(
			'tab_id'              => 'checkout',
			'section_id'          => 'general',
			'section_title'       => __( 'Checkout Settings', 'orderable' ),
			'section_description' => '',
			'section_order'       => 0,
			'fields'              => array(
				array(
					'id'       => 'pro',
					'title'    => __( 'Enable Custom Checkout', 'orderable' ),
					'subtitle' => __( "When enabled, your theme's checkout will be replaced by Orderable's optimized checkout experience.", 'orderable' ),
					'type'     => 'custom',
					'output'   => Orderable_Helpers::get_pro_button( 'checkout' ),
				),
			),
		);

		return $settings;
	}
}
