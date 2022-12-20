<?php
/**
 * testdemo Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package testdemo
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_TESTDEMO_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'testdemo-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_TESTDEMO_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

    function print_menu_shortcode($atts, $content = null) {
extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
return wp_nav_menu( array( 'menu' => $name, 'menu_class' => $class, 'echo' => false ) );
}

add_shortcode('menu', 'print_menu_shortcode');

function print_menu_shortcode_r($atts, $content = null) {
       extract( shortcode_atts(
            array(
                'name' => null, 
                'class' => null
            ), 
            $atts
        ));

        // Assuming $name contains slug or  name of menue
        $menu_items = wp_get_nav_menu_items($name); 

        // Sample Output. Adjsut as per your exact requirements.

        // Sample Output variable.
        $menu_dropdown  = '';

        if ($menu_items){

            $menu_dropdown  .= '<select onChange="document.location.href=this.options[this.selectedIndex].value;">';

            foreach( $menu_items as $menu_item ) {

               $link = $menu_item->url;
               $title = $menu_item->title;
               $menu_dropdown  .= '<option value="' . $link .'">'. $title . '</option>' ;

            }

             $menu_dropdown  .= '</select>';

        } else {
           $menu_dropdown  = '<!-- no menu defined in location "'.$theme_location.'" -->';
        }

           return $menu_dropdown ;
}

add_shortcode('menu1', 'print_menu_shortcode_r');