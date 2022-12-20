<?php

/**
 * Add Homepage Slider to the theme customizer
 */
function estera_slider_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'slider',
		array(
			'title'       => esc_html__( 'Header Slider', 'estera' ),
			'description' => esc_html__( 'Customize the homepage slider to taste with the options below. This slider can display slides from the post type "post" or post type "product". To make it work, you need to create a few slides by adding posts and optionally assign a specific post category for the slides. You also need to add a featured image for each post, otherwise a default fallback image is dislayed. Our lightweight vanilla JavaScript slider will do all the rest for you.', 'estera' ),
			'priority'    => '99',
		)
	);
	/* Show or hide Post Slider on Homepage */
	$wp_customize->add_setting(
		'display-slider',
		array(
			'default'           => 1,
			'sanitize_callback' => 'estera_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'display-slider',
		array(
			'label'       => esc_html__( 'Show Slider', 'estera' ),
			'section'     => 'slider',
			'description' => esc_html__( 'Display the Slider in the homepage header.', 'estera' ),
			'type'        => 'checkbox',
		)
	);
	// Setting - select_slider_from.
	$wp_customize->add_setting(
		'select_slider_from',
		array(
			'default'           => 'post',
			'sanitize_callback' => 'estera_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'select_slider_from',
		array(
			'label'       => __( 'Select Slides From', 'estera' ),
			'section'     => 'slider',
			'type'        => 'select',
			'choices'     => array(
				'post'    => __( 'Posts', 'estera' ),
				'product' => __( 'Products', 'estera' ),
			),
			'description' => esc_html__( 'Pages and custom post types => go pro version. Choose between post slider or product slider. If you want to display products, you need to have WooCommerce plugin installed and configured.', 'estera' ),
		)
	);
	/* Choose a specific post category to display slides from */
	$wp_customize->add_setting(
		'home_slider_category',
		array(
			'default'           => 0,
			'sanitize_callback' => 'estera_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'home_slider_category',
		array(
			'section'         => 'slider',
			'label'           => esc_html__( 'Slider Post Category', 'estera' ),
			'description'     => esc_html__( 'Select the post category that the slider will pull posts from. If no category is selected, the slider will display posts from all categories', 'estera' ),
			'type'            => 'select',
			'choices'         => estera_slide_cats(),
			'active_callback' => 'estera_is_select_post',
		)
	);
	/* Choose a specific product category to display slides from */
	$wp_customize->add_setting(
		'home_slider_woo_category',
		array(
			'default'           => 0,
			'sanitize_callback' => 'estera_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'home_slider_woo_category',
		array(
			'section'         => 'slider',
			'label'           => esc_html__( 'Slider Product Category', 'estera' ),
			'description'     => esc_html__( 'Select the post category that the slider will pull posts from. If no category is selected, the slider will display posts from all categories', 'estera' ),
			'type'            => 'select',
			'choices'         => estera_slide_woo_cats(),
			'active_callback' => 'estera_is_select_product',
		)
	);
	/* Number of Slides */
	$wp_customize->add_setting(
		'number_of_home_slider',
		array(
			'default'           => '3',
			'sanitize_callback' => 'estera_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'number_of_home_slider',
		array(
			'label'       => __( 'Select Number of Slides', 'estera' ),
			'description' => __( 'Choose thu maximum number of slides to add to the slider. Default is 3.', 'estera' ),
			'section'     => 'slider',
			'choices'     => array(
				'1' => __( '1', 'estera' ),
				'2' => __( '2', 'estera' ),
				'3' => __( '3', 'estera' ),
				'4' => __( '4', 'estera' ),
				'5' => __( '5', 'estera' ),
				'6' => __( '6', 'estera' ),
			),
			'type'        => 'select',
		)
	);
	// Slider Height
	$wp_customize->add_setting(
		'slide_height',
		array(
			'default'           => '600px',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'slide_height',
		array(
			'label'       => esc_html__( 'Slider Height', 'estera' ),
			'section'     => 'slider',
			'type'        => 'text',
			'description' => esc_html__( 'Change the height of the slides. Write below a number in pixels (default is 420px).', 'estera' ),
		)
	);
	// SLider Image Position
	$wp_customize->add_setting(
		'slide-background-position',
		array(
			'default'           => 'center',
			'sanitize_callback' => 'estera_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'slide-background-position',
		array(
			'label'       => esc_html__( 'Slide Background Position', 'estera' ),
			'section'     => 'slider',
			'description' => esc_html__( 'Choose how you want to position the header image.', 'estera' ),
			'type'        => 'select',
			'choices'     => array(
				'top'    => esc_html( 'top' ),
				'center' => esc_html( 'center' ),
				'bottom' => esc_html( 'bottom' ),
			),
		)
	);
	// SLider Image Size
	$wp_customize->add_setting(
		'slide-background-size',
		array(
			'default'           => 'cover',
			'sanitize_callback' => 'estera_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'slide-background-size',
		array(
			'label'       => esc_html__( 'Slide Background Size', 'estera' ),
			'section'     => 'slider',
			'description' => esc_html__( 'Resize the slide to adjust to the width of the whole screen or choose to keep its initial width.', 'estera' ),
			'type'        => 'select',
			'choices'     => array(
				'initial' => esc_html( 'initial' ),
				'cover'   => esc_html( 'cover' ),
				'contain' => esc_html( 'contain' ),
			),
		)
	);
	/* Slider Opacity */
	$wp_customize->add_setting(
		'cover_template_overlay_opacity',
		array(
			'default'           => '2',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'cover_template_overlay_opacity',
		array(
			'label'       => __( 'Slider Overlay Opacity', 'estera' ),
			'description' => __( 'Make sure that the contrast is high enough so that the slider text is readable.', 'estera' ),
			'section'     => 'slider',
			'type'        => 'range',
			'input_attrs' => apply_filters(
				'estera_customize_opacity_range',
				array(
					'min'  => 0,
					'max'  => 9,
					'step' => 1,
				)
			),
		)
	);
	/* Auto Play Slider */
	$wp_customize->add_setting(
		'autoplay-slider',
		array(
			'default'           => 0,
			'sanitize_callback' => 'estera_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'autoplay-slider',
		array(
			'label'       => esc_html__( 'Auto Play Slides', 'estera' ),
			'description' => __( 'Enable this if you want the slides to rotate automatically', 'estera' ),
			'section'     => 'slider',
			'type'        => 'checkbox',
		)
	);
	/* Auto Drag Slider */
	$wp_customize->add_setting(
		'autodrag-slider',
		array(
			'default'           => 1,
			'sanitize_callback' => 'estera_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'autodrag-slider',
		array(
			'label'       => esc_html__( 'Auto Drag Slides', 'estera' ),
			'description' => __( 'Drag the slides with the mouse.', 'estera' ),
			'section'     => 'slider',
			'type'        => 'checkbox',
		)
	);
	/* Display Slider Arrows */
	$wp_customize->add_setting(
		'display-slider-arrows',
		array(
			'default'           => 1,
			'sanitize_callback' => 'estera_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'display-slider-arrows',
		array(
			'label'   => esc_html__( 'Show Slider Arrows', 'estera' ),
			'section' => 'slider',
			'type'    => 'checkbox',
		)
	);
	/* Display Slider Pagination */
	$wp_customize->add_setting(
		'display-slider-pagination',
		array(
			'default'           => 1,
			'sanitize_callback' => 'estera_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'display-slider-pagination',
		array(
			'label'   => esc_html__( 'Show Slider Pagination', 'estera' ),
			'section' => 'slider',
			'type'    => 'checkbox',
		)
	);
	/* Display Slider Animation */
	$wp_customize->add_setting(
		'display-slider-animation',
		array(
			'default'           => 0,
			'sanitize_callback' => 'estera_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'display-slider-animation',
		array(
			'label'       => esc_html__( 'Show Slider Animation', 'estera' ),
			'description' => __( 'Make the slides expand with a beautiful zoom-in animation.', 'estera' ),
			'section'     => 'slider',
			'type'        => 'checkbox',
		)
	);
	/* Slider Excerpt Length */
	$wp_customize->add_setting(
		'home_slider_excerpt_size',
		array(
			'default'           => 25,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'home_slider_excerpt_size',
		array(
			'type'        => 'number',
			'label'       => esc_html__( 'Slider Excerpt Size', 'estera' ),
			'description' => esc_html__( 'This lets you choose how many words to show in your slide excerpt. Enter a number between 10 and 55. Default is 25. Please note that the slide excerpt length cannot be more than the post summary length spcified in the Blog Settings section.', 'estera' ),
			'section'     => 'slider',
			'input_attrs' => array(
				'min'  => 10,
				'max'  => 55,
				'step' => 1,
			),
		)
	);
	/* Call to Action */
	$wp_customize->add_setting(
		'slider_button_text',
		array(
			'default'           => esc_html__( 'Read More', 'estera' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'slider_button_text',
		array(
			'label'       => esc_html__( 'Slider Button Text', 'estera' ),
			'section'     => 'slider',
			'description' => esc_html__( 'Change the default text of the slider call to action button.', 'estera' ),
			'type'        => 'text',
		)
	);
}

add_action( 'customize_register', 'estera_slider_customize_register' );
/*
 * Display Slider Preferences on the frontend
 *
 */
function estera_customize_swiper_slider() {
	 $slider_display = get_theme_mod( 'display-slider', 1 );

	if ( ! $slider_display ) {
		return;
		// Stop right here if the user does not want a header slider
	}

	$slider_arrows    = ( get_theme_mod( 'display-slider-arrows', 1 ) ? 'block' : 'none' );
	$overlay          = get_theme_mod( 'cover_template_overlay_opacity', '1' );
	$slider_height    = get_theme_mod( 'slide_height', '600px' );
	$slider_size      = get_theme_mod( 'slide-background-size', 'cover' );
	$slider_position  = get_theme_mod( 'slide-background-position', 'center' );
	$trimmed_height   = str_replace( ' ', '', $slider_height );
	$slider_animation = get_theme_mod( 'display-slider-animation', 0 );
	?>
	
	<style type="text/css">

	.swiper-button-prev, .swiper-button-next {
		display: 
		<?php
		echo esc_attr( $slider_arrows );
		?>
	;        
	}
	.image-overlay {
		background: rgba(0, 0, 0, .<?php echo esc_attr( $overlay );?>);
	}
	.text-wrapper, .image-overlay {
		min-height: 
		<?php
		echo esc_attr( $trimmed_height );
		?>
	;
	}
	.header-slider-item {
		background-size: 
		<?php
		echo esc_attr( $slider_size );
		?>
	 !important;
		background-position: 
		<?php
		echo esc_attr( $slider_position );
		?>
	 !important;
	}
	<?php
	if ( $slider_animation ) {
		?>
	.header-slider-item {
		animation: estera_zoomInOut 15s linear 0s infinite alternate;
	}
		<?php
	}
	?>
	</style>

	<?php
	// Slider configuration
	$auto_play         = get_theme_mod( 'autoplay-slider', 0 );
	$auto_drag         = ( get_theme_mod( 'autodrag-slider', 1 ) ? 'true' : 'false' );
	$slider_pagination = get_theme_mod( 'display-slider-pagination', 1 );
	?>

	<script>
		var mySwiper = new Swiper('.header-slider-wrapper', {
			speed: 500,
			loop: true,
			<?php
			if ( $auto_play ) {
				?>
			autoplay: {
				delay: 6000,
				disableOnInteraction: false
			},
			<?php
			}
			?>
			<?php
			if ( $slider_pagination ) {
				?>
			pagination: {
				el: '.swiper-pagination',
				clickable: true,
			},
			<?php
			}
			?>
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
            preventClicks: false,
			simulateTouch: 
			<?php
			echo esc_attr( $auto_drag );
			?>
            ,
		});

	</script>
	<?php
}

add_action( 'wp_footer', 'estera_customize_swiper_slider' );
/**
 * Function to list post categories in the dropdown customizer control
 * This loads categories for our slider dropdown select
 */
function estera_slide_cats() {
	$cats    = array();
	$cats[0] = 'All';
	foreach ( get_categories() as $categories => $category ) {
		$cats[ $category->term_id ] = $category->name;
	}
	return $cats;
}

/**
 * Function to list Woocommerce product categories in the dropdown customizer control
 * This loads categories for our slider dropdown select
 */
function estera_slide_woo_cats() {
	$cats    = array();
	$cats[0] = 'All';
	foreach ( get_categories(
		array(
			'taxonomy' => 'product_cat',
		)
	) as $categories => $category ) {
		$cats[ $category->term_id ] = $category->name;
	}
	return $cats;
}

function estera_post_types() {
	// We create a variable to house our post types
	$post_types = get_post_types(
		array(
			'public' => true,
		),
		'object'
	);
	$types      = array();
	// we loop and assign.
	foreach ( $post_types as $type ) {
		// name is the registered name and label is what the user sees.
		$types[ $type->name ] = $type->label;
	}
	return $types;
}