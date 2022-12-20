<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Estera
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'estera' ); ?></a>
	<header id="masthead" class="site-header">
		<div class="main-navigation-container">
		<div class="site-branding">
			<?php
				the_custom_logo();
			if (display_header_text()==true) : ?>
			    <div class="site-title-wrapper">
    				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
    				
    				<?php $estera_description = get_bloginfo( 'description', 'display' );
    				
    				if ( $estera_description || is_customize_preview() ) :
    					?>
    					<p class="site-description"><?php echo $estera_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
    				<?php endif; ?> 
                </div> 
			<?php endif; ?>
		</div><!-- .site-branding -->
		
		<nav id="site-navigation" class="main-navigation">
			<!-- <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php // esc_html_e( 'Primary Menu', 'estera' ); ?></button> -->
			<button class="menu-toggle" id="nav-icon1"aria-controls="primary-menu" aria-expanded="false">
				<span></span>
				<span></span>
				<span></span>
			</button>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-1',
					'menu_id'        => 'primary-menu',
				)
			); ?>
			
		</nav><!-- #site-navigation -->
		</div>
		<?php if (is_front_page()) {
			/**
			 * Slider Hook
			 *      
			 * @hooked estera_header_slider 
			*/
			do_action( 'estera_action_front_page_slider' );

		}
		
		if ( has_header_image() ) : ?>
			
			<div class="header-image-wrapper">

			</div>

		<?php endif; ?>
			
	</header><!-- #masthead -->
	
	<?php /*Woocommerce fixed menu */ if(class_exists('woocommerce')) { ?>
	<div id="scroll-cart" class="topcorner">
		<ul>
			<li class="my-account"><a class="login-register" href="<?php echo esc_url(get_permalink( wc_get_page_id( 'myaccount' )));?>"><span><i class="author vcard"></i></span></a></li>				
			<li class="my-cart">
			<?php 
				/**
				 * Cart Icon Top Menu Hook
				 *      
				 * @hooked estera_woocommerce_cart_top
				*/
				do_action( 'estera_woocommerce_cart_top' ); ?>
			</li>
			</ul>
	</div>
	<?php } ?>