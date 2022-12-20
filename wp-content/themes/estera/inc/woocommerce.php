<?php
/**
 * WooCommerce Compatibility File
 *
 * @link https://woocommerce.com/
 *
 * @package Estera
 */

/**
 * WooCommerce setup function.
 *
 * @link https://docs.woocommerce.com/document/third-party-custom-theme-compatibility/
 * @link https://github.com/woocommerce/woocommerce/wiki/Enabling-product-gallery-features-(zoom,-swipe,-lightbox)
 * @link https://github.com/woocommerce/woocommerce/wiki/Declaring-WooCommerce-support-in-themes
 *
 * @return void
 */
function estera_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 1,
				'max_columns'     => 6,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'estera_woocommerce_setup' );

/**
 * WooCommerce specific scripts & stylesheets.
 *
 * @return void
 */
function estera_woocommerce_scripts() {
	/* Global Woocommerce css file */
	wp_enqueue_style( 'estera-woocommerce-style', get_template_directory_uri() . '/assets/css/woocommerce.css', array(), ESTERA_VERSION );
	
	/* RTL css */
	wp_style_add_data('estera-woocommerce-style', 'rtl', 'replace');
	
	$font_path   = esc_url(WC()->plugin_url()) . '/assets/fonts/';
	$inline_font = '@font-face {
			font-family: "star";
			src: url("' . $font_path . 'star.eot");
			src: url("' . $font_path . 'star.eot?#iefix") format("embedded-opentype"),
				url("' . $font_path . 'star.woff") format("woff"),
				url("' . $font_path . 'star.ttf") format("truetype"),
				url("' . $font_path . 'star.svg#star") format("svg");
			font-weight: normal;
			font-style: normal;
		}';

	wp_add_inline_style( 'estera-woocommerce-style', $inline_font );
}
add_action( 'wp_enqueue_scripts', 'estera_woocommerce_scripts' );

/**
 * Disable the default WooCommerce stylesheet.
 *
 * Removing the default WooCommerce stylesheet and enqueing your own will
 * protect you during WooCommerce core updates.
 *
 * @link https://docs.woocommerce.com/document/disable-the-default-stylesheet/
 */

 add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Add 'woocommerce-active' class to the body tag.
 *
 * @param  array $classes CSS classes applied to the body tag.
 * @return array $classes modified to include 'woocommerce-active' class.
 */
function estera_woocommerce_active_body_class( $classes ) {
	$classes[] = 'woocommerce-active';

	return $classes;
}
add_filter( 'body_class', 'estera_woocommerce_active_body_class' );

/**
 * Related Products Args.
 *
 * @param array $args related products args.
 * @return array $args related products args.
 */
function estera_woocommerce_related_products_args( $args ) {
	$defaults = array(
		'posts_per_page' => 3,
		'columns'        => 3,
	);

	$args = wp_parse_args( $defaults, $args );

	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'estera_woocommerce_related_products_args' );

/**
 * Remove default WooCommerce wrapper.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

if ( ! function_exists( 'estera_woocommerce_wrapper_before' ) ) {
	/**
	 * Before Content.
	 *
	 * Wraps all WooCommerce content in wrappers which match the theme markup.
	 *
	 * @return void
	 */
	function estera_woocommerce_wrapper_before() {
		?>
		<div class="container">
		<div class="wrapper elvira-shop">
			<main id="primary" class="site-main">
		<?php
	}
}
add_action( 'woocommerce_before_main_content', 'estera_woocommerce_wrapper_before' );


/**
* Cart Fragments.
*
* Ensure cart contents update when products are added to the cart via AJAX.
*
* @param array $fragments Fragments to refresh via AJAX.
* @return array Fragments to refresh via AJAX.
*/
function estera_woocommerce_cart_link_fragment( $fragments ) {

	global $woocommerce;
	ob_start();
	estera_wc_cart_count();
	$cart_fragments['a.cart-contents'] = ob_get_clean();
	
	return $cart_fragments;
	
}
add_filter( 'woocommerce_add_to_cart_fragments', 'estera_woocommerce_cart_link_fragment' );


if ( ! function_exists( 'estera_woocommerce_wrapper_after' ) ) {
	/**
	 * After Content.
	 *
	 * Closes the wrapping divs.
	 *
	 * @return void
	 */
	function estera_woocommerce_wrapper_after() {
		?>
			</main><!-- #main -->
			<?php get_sidebar(); ?>
			</div>
			</div>
		<?php
	}
}
add_action( 'woocommerce_after_main_content', 'estera_woocommerce_wrapper_after' );

/**
 * Add Cart icon and count to header if WC is active
 */
function estera_wc_cart_count() {

	global $woocommerce; 
	?>
    <a class="cart-contents" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php esc_attr_e('Cart View', 'estera'); ?>">
	<span class="cart-contents-count">
	<span class="icon_cart_alt">
	</span>
	&nbsp;<?php 
	
	$cart_contents_count =$woocommerce->cart->cart_contents_count;

	if($cart_contents_count>0){ ?>
		<span class="cart-counter">
			<?php echo esc_html ($cart_contents_count); ?>
		</span> <?php
	}

	?></span>
    </a> 
    <?php
 
}

add_action( 'estera_woocommerce_cart_top', 'estera_wc_cart_count' );

/**
 * Ensure cart contents update when products are added to the cart via AJAX
 * 
 */

function estera_wishlist_count( ) {

	$wishlist_count = 0;
	if(function_exists('YITH_WCWL')){
		global $yith_wcwl;
		$wishlist_count = $yith_wcwl->count_products();
	} else {
		return;
	}
?>
	<a class="wishlist-contents"  href="<?php echo esc_url( $yith_wcwl->get_wishlist_url() ); ?>" title="<?php esc_attr_e( 'View your whishlist','estera' ); ?>">
		<i class="icon_heart_alt"></i>	
		<?php if ($wishlist_count>0) : ?>
		<span class="wishlist-counter"><?php echo esc_html($wishlist_count);?></span>
		<?php endif; ?>
	</a>
	<?php
}

/* Append Woocommerce shopping cart and wishlist as menu items */

function estera_add_last_nav_item ($items) {

    ob_start(); ?>

    <li class="my-cart">
	<?php 
		/**
		 * Cart Icon Top Menu Hook
		 *      
		 * @hooked estera_woocommerce_cart_top
		*/
		do_action( 'estera_woocommerce_cart_top' ); ?>
	</li>
	<?php if(function_exists('YITH_WCWL')) : ?>
		<li class="my-wishlist">
			<span class="qode-wishlist-widget-icon"><?php estera_wishlist_count();?></span>							
        </li>
    <?php endif;

    $items .= ob_get_clean();

    return $items;

}

add_filter('wp_nav_menu_items','estera_add_last_nav_item');