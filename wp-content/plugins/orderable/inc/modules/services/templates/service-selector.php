<?php
/**
 * Template: Service Selector.
 *
 * This template can be overridden by copying it to yourtheme/orderable/services/service-selector.php
 *
 * HOWEVER, on occasion Orderable will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package Orderable/Templates
 */

defined( 'ABSPATH' ) || exit;

$selected_service  = Orderable_Services::get_selected_service();
$shipping_city     = ! empty( WC()->customer ) ? WC()->customer->get_shipping_city() : '';
$shipping_postcode = ! empty( WC()->customer ) ? WC()->customer->get_shipping_postcode() : '';
?>

<div class="orderable-services-selector <?php if ( $selected_service ) {
	echo 'orderable-services-selector--selected';
} ?>">
	<div class="orderable-services-selector__selected">
		<?php if ( $selected_service ) { ?>
			<p>
				<?php printf( __( 'You have selected %s for your order.' ), '<strong>' . $selected_service . '</strong>' ); ?>
				<button class="orderable-services-selector__selected-change" data-orderable-trigger="show-lookup-services"><?php _e( 'Change?', 'orderable' ); ?></button>
			</p>
		<?php } ?>
	</div>
	<div class="orderable-services-selector__lookup">
		<!--input type="text" class="orderable-services-selector__lookup-city" value="<?php echo esc_attr( $shipping_city ); ?>" placeholder="<?php esc_attr_e( 'City', 'woocommerce' ); ?>" name="orderable_city" /-->
		<input type="text" class="orderable-services-selector__lookup-postcode" value="<?php echo esc_attr( $shipping_postcode ); ?>" placeholder="<?php esc_attr_e( 'Postcode / ZIP', 'woocommerce' ); ?>" name="orderable_postcode" />
		<span class="orderable-services-selector__lookup-buttons">
			<button data-orderable-trigger="lookup-services" data-orderable-service="delivery"><?php esc_html_e( 'Delivery', 'woocommerce' ); ?></button>
			<button data-orderable-trigger="lookup-services" data-orderable-service="pickup"><?php esc_html_e( 'Pickup', 'woocommerce' ); ?></button>
		</span>
	</div>
	<div class="orderable-services-selector__lookup-message"></div>
</div>
