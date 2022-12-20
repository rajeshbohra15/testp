<?php

/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Estera
 */
/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
/**
 * Estera only works in WordPress 4.7 or later.
 */

if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
    require get_template_directory() . '/inc/back-compat.php';
    return;
}

/* Enqueue Slider scripts and styles based on user preferences in customizer */
function estera_enqueue_slider_scripts_and_styles() {
    $estera_slider_display = get_theme_mod( 'display-slider', 1 );
    
    if ( $estera_slider_display ) {
        //Slider css
        wp_enqueue_style(
            'estera-swiper-slider',
            get_template_directory_uri() . '/assets/css/swiper.min.css',
            array(),
            ESTERA_VERSION
        );
        //Slider js
        wp_enqueue_script(
            'estera-swiper-slider',
            get_template_directory_uri() . '/assets/js/swiper.min.js',
            null,
            null,
            false
        );
    }

}

/* Check if a given page is the blog page */
function estera_is_blog() {
    return (is_archive() || is_author() || is_category() || is_home() || is_tag()) && 'post' == get_post_type();
}

function estera_body_classes( $classes ) {
    // Adds a class of hfeed to non-singular pages.
    if ( !is_singular() ) {
        $classes[] = 'hfeed';
    }
    // Adds a class of no-sidebar when there is no sidebar present.
    if ( !is_active_sidebar( 'sidebar-1' ) ) {
        $classes[] = 'no-sidebar';
    }
    /* Add full-width body class when full-width sidebar is selected globally */
    $global_sidebar = get_theme_mod( 'default_sidebar_layout', 'one' );
    $is_custom_sidebar = get_theme_mod( 'custom_sidebar_layout', 0 );
    if ( $global_sidebar == 'two' ) {
        $classes[] = 'estera-full-width';
    }
    return $classes;
}

add_filter( 'body_class', 'estera_body_classes' );
/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function estera_pingback_header() {
    if ( is_singular() && pings_open() ) {
        printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
    }
}

add_action( 'wp_head', 'estera_pingback_header' );
/**
 * Change the excerpt length for post archives
 *
 * @param int $length Length of excerpt in number of words.
 * @return int
 */
function estera_custom_excerpt_length( $length ) {
    if ( is_admin() ) {
        return $length;
    }
    // Get excerpt length from database.
    $excerpt_length = esc_attr( get_theme_mod( 'estera_excerpt_length', '55' ) );
    // Return excerpt text.
    
    if ( $excerpt_length >= 0 ) {
        return absint( $excerpt_length );
    } else {
        return 55;
        // Number of words.
    }

}

add_filter( 'excerpt_length', 'estera_custom_excerpt_length' );
/* Custom function that shows post excerpt in the homepage slider
 * It accepts number of words as a parameter from the theme customizer 
 * This is required for managing the slider exerpt size independently of the blog
 * Does not affect the post archives excerpt 
 */
function estera_slide_excerpt( $limit ) {
    $excerpt = explode( ' ', get_the_excerpt(), $limit );
    
    if ( count( $excerpt ) >= $limit ) {
        array_pop( $excerpt );
        $excerpt = implode( " ", $excerpt ) . '...';
    } else {
        $excerpt = implode( " ", $excerpt );
    }
    
    $excerpt = preg_replace( '`\\[[^\\]]*\\]`', '', $excerpt );
    return $excerpt;
}

/* 
 * Homepage slider content
 */
function estera_header_slider() {
    //check user preference to show or hide slider
    $slider_display = get_theme_mod( 'display-slider', 1 );
    
    if ( !$slider_display ) {
        return;
        //Stop right here if the user does not want a slider
    }
    
    global  $post ;
    //determine desired post type
    $slider_post_type = get_theme_mod( 'select_slider_from', 'post' );
    //Get the desired post category from theme customizer
    $slider_post_category = get_theme_mod( 'home_slider_category', '0' );
    //Get the desired product category from theme customizer
    $slider_product_category = get_theme_mod( 'home_slider_woo_category', '0' );
    $slider_number = get_theme_mod( 'number_of_home_slider', '3' );
    $slider_excerpt_size = esc_attr( get_theme_mod( 'home_slider_excerpt_size', 25 ) );
    $button_text = get_theme_mod( 'slider_button_text', __( 'Read More', 'estera' ) );
    //post query
    
    if ( $slider_post_type == 'post' ) {
        $args = array(
            'posts_per_page' => $slider_number,
            'post_type'      => 'post',
            'cat'            => $slider_post_category,
        );
        //product query
    } else {
        
        if ( $slider_post_type == 'product' ) {
            
            if ( $slider_product_category ) {
                $args = array(
                    'posts_per_page' => $slider_number,
                    'post_type'      => 'product',
                    'tax_query'      => array( array(
                    'taxonomy' => 'product_cat',
                    'terms'    => $slider_product_category,
                    'operator' => 'IN',
                ) ),
                );
            } else {
                $args = array(
                    'posts_per_page' => $slider_number,
                    'post_type'      => 'product',
                );
            }
        
        } else {
            $args = array(
                'posts_per_page' => $slider_number,
                'post_type'      => $slider_post_type,
            );
        }
    
    }
    
    $slider_query = new WP_Query( $args );
    
    if ( $slider_query->have_posts() ) { ?>

    <div class="header-slider-wrapper swiper-container">
        <div class="swiper-wrapper">
            <?php 
            while ( $slider_query->have_posts() ) {
                $slider_query->the_post();
                $url = ( has_post_thumbnail( get_the_ID() ) ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : get_template_directory_uri() . '/assets/img/fallback-header.jpg' );
                ?>

            <div class="swiper-slide">
                <div class="header-slider-item" style="background: url('<?php echo esc_attr( $url );?>') no-repeat">
                    <div class="image-overlay"></div>
                </div>
                <div class="text-wrapper">
                    <div class="entry-text">
                        <h2><a href="<?php the_permalink(); ?>"> <?php the_title(); ?></a></h2> 
                        <?php 
                        if ( $slider_excerpt_size ) { 
                        ?>
                            <p><?php echo esc_html( estera_slide_excerpt( $slider_excerpt_size ) ) ;?></p>
                        <?php 
                        }
                
                        if ( $button_text ) {
                        ?>
                            <div class="button-wrapper">
                                <a href="<?php the_permalink();?>">
                                    <button><?php echo esc_html( $button_text ), ( is_rtl() ? ' &larr;' : ' &rarr;' ) ;?></button>
                                </a>
                            </div>
                        <?php 
                        } ?>
                    </div>
                </div>
            </div>

            <?php 
            }
            wp_reset_postdata();
            ?>
        </div>
        <!-- Slider Pagination -->
        <div class="swiper-pagination"></div>
        <!-- Slider Arrows -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div> <?php

    }

}

add_action( 'estera_action_front_page_slider', 'estera_header_slider' );
/* Post navigation in archive.php */
function estera_the_posts_navigation() {
    $estera_prev_arrow = ( is_rtl() ? '&rarr;' : '&larr;' );
    $estera_next_arrow = ( is_rtl() ? '&larr;' : '&rarr;' );
    the_posts_navigation( array(
        'prev_text'          => $estera_prev_arrow . __( ' Older posts', 'estera' ),
        'next_text'          => __( 'Newer posts', 'estera' ) . $estera_next_arrow,
        'screen_reader_text' => __( 'Posts navigation', 'estera' )
    ) );
}

/* Post navigation in single.php */
function estera_the_post_navigation() {
    $estera_prev_arrow = ( is_rtl() ? '&rarr;' : '&larr;' );
    $estera_next_arrow = ( is_rtl() ? '&larr;' : '&rarr;' );
    the_post_navigation( array(
        'prev_text' => '<span class="nav-subtitle">' . $estera_prev_arrow . '</span> <span class="nav-title">%title</span>',
        'next_text' => '<span class="nav-title">%title </span>' . '<span class="nav-subtitle">' . $estera_next_arrow . '</span>',
        'screen_reader_text' => __( 'Posts navigation', 'estera' )
        ) );
}

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and
 * a 'Continue reading' link.
 *
 * @since estera 0.1
 *
 * @param string $link Link to single post/page.
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
function estera_excerpt_more( $link ) {
    if ( is_admin() ) {
        return $link;
    }
    $link = sprintf(
        '<p class="link-more"><a href="%1$s" class="more-link">%2$s</a></p>',
        esc_url( get_permalink( get_the_ID() ) ),
        /* translators: %s: Name of current post */
        sprintf( __( 'Read more %1$s<span class="screen-reader-text">"%2$s"</span>', 'estera' ), ( is_rtl() ? '&larr;' : '&rarr;' ), esc_html( get_the_title( get_the_ID() ) ) )
    );
    return ' &hellip; ' . $link;
}

add_filter( 'excerpt_more', 'estera_excerpt_more' );
/**
 * Back to top
*/
function estera_back_to_top() {
    ?>
<button class="back-to-top">
    <i class="arrow_carrot-2up"></i>
</button>
<?php 
}

add_action( 'estera_footer', 'estera_back_to_top', 45 );

//Filter Classes of wp_list_pages items to match menu items

function estera_filter_menu_item_classes( $css_class) {
	// Add menu-item-has-children class.
	if ( in_array( 'page_item_has_children', $css_class, true ) ) {
		$css_class[] = 'menu-item-has-children';
    }
	return $css_class;
}

add_filter( 'page_css_class', 'estera_filter_menu_item_classes', 10, 5 );

/**
 * Enable dark theme mode
 * Hook js right after the body tag to avoid light flash of unstyled content
 */
function estera_dark_mode() {
    ?>
        <script>
            if (localStorage.getItem('esteraNightMode')) {
                document.body.className +=' dark-mode';
	        }
        </script>
    <?php
}
add_action('wp_body_open', 'estera_dark_mode');