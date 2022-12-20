<?php
/**
 * Module: Services.
 *
 * Delivery/pickup services.
 *
 * @package Orderable/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Services module class.
 */
class Orderable_Services {
	/**
	 * Init.
	 */
	public static function run() {
		self::load_classes();
		add_action( 'init', array( __CLASS__, 'add_shortcodes' ) );
	}

	/**
	 * Load classes for this module.
	 */
	public static function load_classes() {
		$classes = array(
			'services-order' => 'Orderable_Services_Order',
		);

		foreach ( $classes as $file_name => $class_name ) {
			require_once ORDERABLE_MODULES_PATH . 'services/class-' . $file_name . '.php';

			$class_name::run();
		}
	}

	/**
	 * Add services shrotcodes.
	 */
	public static function add_shortcodes() {
		add_shortcode( 'orderable-services', array( __CLASS__, 'orderable_services_shortcode' ) );
	}

	/**
	 * Services selector shortcode.
	 *
	 * @param array  $args
	 * @param string $content
	 * @param string $name
	 *
	 * @return string|void
	 */
	public static function orderable_services_shortcode( $args = array(), $content = '', $name = '' ) {
		ob_start();
		include Orderable_Helpers::get_template_path( 'service-selector.php', 'services' );

		return ob_get_clean();
	}

	/**
	 * Get delivery and pickup methods for address.
	 *
	 * @param array $address Array containing address fields.
	 *
	 * @return array
	 */
	public static function get_delivery_pickup_methods( $address ) {
		$shipping_methods = array(
			'delivery' => false,
			'pickup'   => false,
			'rates'    => false,
		);

		$default_address = array(
			'country'   => WC()->countries->get_base_country(),
			'state'     => WC()->countries->get_base_state(),
			'postcode'  => '',
			'city'      => WC()->countries->get_base_city(),
			'address'   => '',
			'address_1' => '', // Provide both address and address_1 for backwards compatibility.
			'address_2' => '',
		);

		$address = wp_parse_args( $address, $default_address );

		$packages = WC()->shipping()->calculate_shipping( array(
			array(
				'contents'        => array(),
				'contents_cost'   => 0,
				'applied_coupons' => array(),
				'user'            => array(
					'ID' => get_current_user_id(),
				),
				'destination'     => $address,
				'cart_subtotal'   => 0,
			),
		) );

		if ( empty( $packages ) ) {
			return $shipping_methods;
		}

		$package                   = $packages[0];
		$rates                     = $package['rates'];
		$shipping_methods['rates'] = array_keys( $package['rates'] );

		if ( empty( $rates ) ) {
			return $shipping_methods;
		}

		foreach ( $rates as $rate_id => $rate ) {
			$pickup        = self::is_pickup_method( $rate );
			$rate->service = $pickup ? 'pickup' : 'delivery';

			if ( ! $shipping_methods[ $rate->service ] ) {
				$shipping_methods[ $rate->service ] = $rate;
			}
		}

		return $shipping_methods;
	}

	/**
	 * Is pickup method?
	 *
	 * @param string|WC_Shipping_Method $shipping_method
	 *
	 * @return bool
	 */
	public static function is_pickup_method( $shipping_method ) {
		if ( ! $shipping_method ) {
			return false;
		}

		if ( ! is_string( $shipping_method ) ) {
			$shipping_method = $shipping_method->get_method_id();
		}

		$explode = explode( ':', $shipping_method );

		return in_array( $explode[0], array( 'local_pickup' ) );
	}

	/**
	 * Get selected service.
	 *
	 * @param bool $label Return the label?
	 *
	 * @return bool|WC_Shipping_Method
	 */
	public static function get_selected_service( $label = true ) {
		if ( empty( WC()->session ) ) {
			return false;
		}

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( empty( $chosen_methods ) ) {
			return false;
		}

		$chosen_method = $chosen_methods[0];
		$is_pickup     = self::is_pickup_method( $chosen_method );
		$type          = $is_pickup ? 'pickup' : 'delivery';

		if ( ! $label ) {
			return $type;
		}

		return self::get_service_label( $type );
	}

	/**
	 * Get service label.
	 *
	 * @param string $type   pickup|delivery
	 * @param bool   $plural Return the plural label?
	 *
	 * @return bool|string
	 */
	public static function get_service_label( $type, $plural = false ) {
		if ( empty( $type ) ) {
			return false;
		}

		$type = $plural ? $type . '_plural' : $type;

		$labels = apply_filters( 'orderable_service_labels', array(
			'pickup'          => __( 'Pickup', 'orderable' ),
			'delivery'        => __( 'Delivery', 'orderable' ),
			'pickup_plural'   => __( 'Pickups', 'orderable' ),
			'delivery_plural' => __( 'Deliveries', 'orderable' ),
		) );

		if ( ! isset( $labels[ $type ] ) ) {
			return false;
		}

		return $labels[ $type ];
	}

	/**
	 * How many services are active?
	 *
	 * @return int
	 */
	public static function get_services_count() {
		return count( Orderable_Timings::get_services() );
	}

	/**
	 * Get services on day.
	 *
	 * @param bool|int $timestamp Timestamp of specific day at 00:00am.
	 *
	 * @return array
	 */
	public static function get_services_on_day( $timestamp = false ) {
		$services_on_day = array();
		$services = Orderable_Timings::get_services();

		if ( empty( $services ) ) {
			return $services_on_day;
		}

		$day_to_check = absint( date( 'w', $timestamp ) );

		foreach( $services as $service ) {
			$service_days = Orderable_Timings::get_service_days( $service );
			$services_on_day[ $service ] = isset( $service_days[ $day_to_check ] ) && ! Orderable_Timings::is_holiday( $timestamp, $service );
		}

		return $services_on_day;
	}
}