<?php
/**
 * Ajax methods.
 *
 * @package Orderable/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ajax class.
 */
class Orderable_Ajax {
	/**
	 * Run.
	 */
	public static function run() {
		$methods = array(
			'get_product_options'    => true,
			'add_to_cart'            => true,
			'lookup_service'         => true,
			'get_onboard_woo_fields' => false,
			'get_cart_item_options'  => true,
		);

		self::add_ajax_methods( $methods, __CLASS__ );
	}

	/**
	 * Add ajax methods helper.
	 *
	 * @param array         $methods
	 * @param object|string $class
	 */
	public static function add_ajax_methods( $methods, $class ) {
		if ( empty( $methods ) ) {
			return;
		}

		foreach ( $methods as $method => $nopriv ) {
			add_action( 'wp_ajax_orderable_' . $method, array( $class, $method ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_orderable_' . $method, array( $class, $method ) );
			}
		}
	}

	/**
	 * Get product options for a variable product.
	 */
	public static function get_product_options() {
		$product_id = absint( filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( empty( $product_id ) ) {
			wp_send_json_error();
		}

		$focus   = filter_input( INPUT_POST, 'focus', FILTER_SANITIZE_STRING );
		$product = wc_get_product( $product_id );

		$response = array(
			'product_id' => $product_id,
			'product'    => $product,
		);

		if ( 'variable' === $product->get_type() ) {
			$attributes           = Orderable_Products::get_available_attributes( $product );
			$available_variations = $product->get_available_variations();
			$variations_json      = wp_json_encode( $available_variations );
		}

		$args = array(
			'images' => true,
			'focus'  => $focus,
		);

		ob_start();

		include Orderable_Helpers::get_template_path( 'templates/product/options.php' );

		$response['html'] = ob_get_clean();

		wp_send_json_success( $response );
	}

	/**
	 * Get cart item options for a variable product.
	 */
	public static function get_cart_item_options() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['cart_item_key'] ) ) {
			wp_send_json_error();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) );

		if ( empty( $cart_item_key ) ) {
			wp_send_json_error();
		}

		$cart_item = WC()->cart->get_cart_item( $cart_item_key );

		if ( empty( $cart_item ) ) {
			wp_send_json_error();
		}

		$product_id = $cart_item['product_id'];
		$product    = wc_get_product( $product_id );

		$response = array(
			'product_id' => $product_id,
			'product'    => $product,
		);

		if ( 'variable' === $product->get_type() ) {
			$selected             = $cart_item['variation'];
			$attributes           = Orderable_Products::get_available_attributes( $product );
			$available_variations = $product->get_available_variations();
			$variations_json      = html_entity_decode( wp_json_encode( $available_variations ) );
		}

		$args = array(
			'images' => true,
		);

		add_filter(
			'orderable_get_group_data',
			/**
			 * Fill the value of the fields.
			 *
			 * @param array $field_group The field group data.
			 *
			 * @return array
			 */
			function( $field_group ) use ( $cart_item ) {
				foreach ( $field_group as $key => $value ) {
					if ( empty( $cart_item['orderable_fields'][ $value['id'] ] ) ) {
						continue;
					}

					switch ( $value['type'] ) {
						case 'text':
							$field_group[ $key ]['default'] = $cart_item['orderable_fields'][ $value['id'] ]['value'];

							break;

						case 'select':
						case 'visual_radio':
							foreach ( $value['options'] as $key_option => $option ) {
								if ( $option['label'] === $cart_item['orderable_fields'][ $value['id'] ]['value'] ) {
									$field_group[ $key ]['options'][ $key_option ]['selected'] = '1';

									break;
								}
							}

							break;

						case 'visual_checkbox':
							foreach ( $value['options'] as $key_option => $option ) {
								if ( empty( $cart_item['orderable_fields'][ $value['id'] ]['value'] ) ) {
									continue;
								}

								$field_value = $cart_item['orderable_fields'][ $value['id'] ]['value'];

								if ( ! is_array( $field_value ) ) {
									continue;
								}

								if ( ! in_array( $option['label'], $field_value, true ) ) {
									$field_group[ $key ]['options'][ $key_option ]['selected'] = '0';
									continue;
								}

								$field_group[ $key ]['options'][ $key_option ]['selected'] = '1';
							}

							break;
					}
				}

				return $field_group;
			}
		);

		ob_start();

		include ORDERABLE_TEMPLATES_PATH . 'product/options.php';

		$response['html'] = ob_get_clean();

		wp_send_json_success( $response );
	}

	/**
	 * AJAX add to cart.
	 */
	public static function add_to_cart() {
		ob_start();

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$product_id = absint( filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( empty( $product_id ) ) {
			return;
		}

		$variation_id      = absint( filter_input( INPUT_POST, 'variation_id', FILTER_SANITIZE_NUMBER_INT ) );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );
		$attributes        = (array) json_decode( filter_input( INPUT_POST, 'attributes', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES ), true );
		$attributes        = array_map( 'wp_unslash', $attributes );

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes ) && 'publish' === $product_status ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
		}

		WC_AJAX::get_refreshed_fragments();
		// phpcs:enable
	}

	/**
	 * Lookup delivery and set chosen shipping method.
	 */
	public static function lookup_service() {
		$service = filter_input( INPUT_POST, 'service', FILTER_SANITIZE_STRING );

		$address = array(
			'city'     => filter_input( INPUT_POST, 'city', FILTER_SANITIZE_STRING ),
			'postcode' => filter_input( INPUT_POST, 'postcode', FILTER_SANITIZE_STRING ),
		);

		$delivery_pickup_methods = Orderable_Services::get_delivery_pickup_methods( $address );
		$rates                   = array_pop( $delivery_pickup_methods );

		if ( empty( array_filter( $delivery_pickup_methods ) ) ) {
			wp_send_json_error( array(
				'message' => __( 'Sorry, there are no delivery or pickup options available.', 'orderable' ),
			) );
		}

		if ( empty( $delivery_pickup_methods[ $service ] ) ) {
			$other_service = 'delivery' === $service ? __( 'pickup', 'orderable' ) : __( 'delivery', 'orderable' );

			wp_send_json_error( array(
				'message' => sprintf( __( 'Sorry, there are no %s options available. Please try %s instead.', 'orderable' ), $service, $other_service ),
			) );
		}

		// Start session if it doesn't already exist.
		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		// Set customer data.
		$customer = WC()->customer;
		$customer->set_billing_city( $address['city'] );
		$customer->set_billing_postcode( $address['postcode'] );
		$customer->set_shipping_city( $address['city'] );
		$customer->set_shipping_postcode( $address['postcode'] );
		$customer->save();

		do_action( 'woocommerce_checkout_update_user_meta', $customer->get_id(), array(
			'billing_city'      => $address['city'],
			'billing_postcode'  => $address['postcode'],
			'shipping_city'     => $address['city'],
			'shipping_postcode' => $address['postcode'],
		) );

		// Set the chosen shipping method.
		$chosen = $delivery_pickup_methods[ $service ]->get_id();

		WC()->session->set( 'chosen_shipping_methods', array( $chosen ) );

		do_action( 'woocommerce_shipping_method_chosen', $chosen );

		// Added this so right shipping method is selected at checkout.
		// @see wc-cart-functions.php:425 `wc_get_chosen_shipping_method_for_package()`.
		WC()->session->set( 'previous_shipping_methods', array( $rates ) );
		WC()->session->set( 'shipping_method_counts', array( count( $rates ) ) );

		$data = array(
			'fragments' => apply_filters( 'orderable_service_fragments', array() ),
		);

		ob_start();
		include Orderable_Helpers::get_template_path( 'service-selector.php', 'services' );
		$data['fragments']['.orderable-services-selector'] = ob_get_clean();

		wp_send_json_success( $data );
	}

	/**
	 * Get countries states.
	 */
	public static function get_onboard_woo_fields() {
		$response = array(
			'default_country'    => self::get_default_country_options(),
			'business_address'   => WC()->countries->get_base_address(),
			'business_address_2' => WC()->countries->get_base_address_2(),
			'business_city'      => WC()->countries->get_base_city(),
			'business_postcode'  => WC()->countries->get_base_postcode(),
		);

		wp_send_json_success( $response );
	}

	/**
	 * Get country/state options.
	 *
	 * @return string
	 */
	public static function get_default_country_options() {
		$countries_states = Orderable_Settings::get_countries_states();

		if ( empty( $countries_states ) ) {
			return false;
		}

		ob_start();

		require ORDERABLE_INC_PATH . "/vendor/iconic-onboard/inc/class-settings.php";

		$base    = wc_get_base_location();
		$default = '';

		if ( isset( $base['country'] ) && isset( $countries_states['country:' . $base['country'] ] ) ) {
			$default = 'country:' . $base['country'];
		}

		if ( isset( $base['country'] ) && isset( $base['state'] ) && isset( $countries_states[$base['country'] ] ) ) {
			$state = 'state:' . $base['country'] . ':' . $base['state'];
			if ( isset( $countries_states[ $base['country'] ]['values'][ $state ] ) ) {
				$default = $state;
			}
		}

		Orderable_Onboard_Settings::generate_select_field( array(
			'id'      => 'default_country',
			'title'   => __( 'Country / State', 'orderable' ),
			'desc'    => '',
			'choices' => $countries_states,
			'value'   => $default,
			'name'    => '',
			'class'   => '',
		) );

		return strip_tags( ob_get_clean(), '<option><optgroup>' );
	}
}