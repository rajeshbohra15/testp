<?php

namespace WpCafe\Core\Modules\Reservation;

use WpCafe\Utils\Wpc_Utilities;

defined( 'ABSPATH' ) || exit;

class Wpc_Reservation_Report {
    
    use \WpCafe\Traits\Wpc_Singleton;

    /**
     * Class constructor.
     */
    public function init() {
        add_action( "manage_wpc_reservation_posts_custom_column",
            [$this, 'wpc_reservation_custom_column'], 10, 2 );

        $filter_hooks = array(
                array(
                    'hook'      =>'manage_wpc_reservation_posts_columns',
                    'callback'  =>'wpc_reservation_post_columns',
                ),
                // remove bulk action edit
                array(
                    'hook'      =>'bulk_actions-edit-wpc_reservation',
                    'callback'  =>'custom_bulk_actions',
                ),
                // reservation report order by desc
                array(
                    'hook'      =>'pre_get_posts',
                    'callback'  =>'reservation_report_desc_order',
                ),
        );    

        if( ! empty( $filter_hooks)){
            foreach ($filter_hooks as $key => $value) {
                add_filter( $value['hook'], [$this, $value['callback'] ] );
            }
        }

    }

    /**
     * Reservation report order by desc
     */
    public function reservation_report_desc_order( $wp_query ) {

        if (is_admin() && !empty($wp_query->query['post_type'])) {
            // Get the post type from the query
            $post_type = $wp_query->query['post_type'];
            if ( $post_type == 'wpc_reservation') {
        
                $wp_query->set('orderby', 'date');
        
                $wp_query->set('order', 'DESC');
                
            }
        }
    }

    /**
     * Remove edit bulk action
     */
    public function custom_bulk_actions( $actions ){
        unset( $actions[ 'edit' ] );

        return $actions;
    }

    /**
     * Column name
     */
    public function wpc_reservation_post_columns( $columns ) {
        unset( $columns['date'] );
        unset( $columns['title'] );
        $columns['id']                      =   esc_html__(  'Id', 'wpcafe' );
        $settings = \WpCafe\Core\Base\Wpc_Settings_Field::instance()->get_settings_option();
        if( isset($settings['show_branches']) && $settings['show_branches'] !==""){
            $columns['wpc_branch']          =   esc_html__(  'Branch', 'wpcafe' );
        }
        $columns['wpc_name']                =   esc_html__(  'Name', 'wpcafe' );
        $columns['wpc_email']               =   esc_html__(  'Email', 'wpcafe' );
        $columns['wpc_phone']               =   esc_html__(  'Phone', 'wpcafe' );
        $columns['wpc_guest_count']         =   esc_html__(  'Seat(s)', 'wpcafe' );
        $columns['wpc_booking_date']        =   esc_html__(  'Date', 'wpcafe' );
        $columns['wpc_reservation_state']   =   esc_html__(  'Status', 'wpcafe' );
        $columns['wpc_reservation_invoice'] =   esc_html__(  'Invoice', 'wpcafe' );
        $columns = apply_filters( 'wpcafe_pro/reservation/report_extra_field_title', $columns);

        return $columns;
    }

    /**
     * Return row
     */
    public function wpc_reservation_custom_column( $column, $post_id ) {
        switch ( $column ) {
        case 'id':
            echo intval( $post_id );
            break;
        case 'wpc_branch':
            echo Wpc_Utilities::wpc_render( get_post_meta( $post_id, 'wpc_branch', true ) );
            break;
        case 'wpc_name':
            echo Wpc_Utilities::wpc_render( get_post_meta( $post_id, 'wpc_name', true ) ) ;
            break;
        case 'wpc_guest_count':
            $total_guest = get_post_meta( $post_id, 'wpc_total_guest', true );
            
            if ( class_exists( 'Wpcafe_Pro' ) ) {
                if ( !empty( \WpCafe_Pro\Utils\Utilities::is_table_layout_enabled() ) ) {
                    $booked_seats_info = \Wpcafe_Pro\Utils\Table_Utils::get_booked_seats_info( $post_id );

                    if ( !empty( $booked_seats_info ) && count( $booked_seats_info ) > 0 ) {
                        $total_guest = esc_html__( 'Total Guest: ', 'wpcafe' ) . $total_guest . '<br>';
                        $total_guest .= join( ';<br>', $booked_seats_info );
                    }
                }
            }
            echo Wpc_Utilities::wpc_render( $total_guest );
            break;
        case 'wpc_reservation_state':
            $status_meta =  get_post_meta( $post_id, 'wpc_reservation_state', true );
            $reservation_states = array(
                'pending'   => esc_html__( 'Pending', 'wpcafe' ),
                'confirmed' => esc_html__( 'Confirmed', 'wpcafe' ),
                'cancelled' => esc_html__( 'Cancelled', 'wpcafe' ),
                'completed' => esc_html__( 'Completed', 'wpcafe' ),
                'processing' => esc_html__( 'Processing', 'wpcafe' )
            );
            echo Wpc_Utilities::wpc_render( ucfirst( $reservation_states[$status_meta] ) );
            break;
        case 'wpc_phone':
            echo Wpc_Utilities::wpc_render( get_post_meta( $post_id, 'wpc_phone', true ) );
            break;
        case 'wpc_email':
            echo Wpc_Utilities::wpc_render( get_post_meta( $post_id, 'wpc_email', true ) );
            break;
        case 'wpc_booking_date':
            $wpc_date_format    =  get_option('date_format');
            $wpc_time_format    =  get_option('time_format');
            $wpc_booking_date   = get_post_meta( $post_id, 'wpc_booking_date', true );
            $wpc_from_time      = get_post_meta( $post_id, 'wpc_from_time', true );
            $wpc_to_time        = get_post_meta( $post_id, 'wpc_to_time', true );
            
            if ( $wpc_booking_date !=="" ) {
                $wpc_booking_date = date_i18n($wpc_date_format, strtotime( $wpc_booking_date ) );
            }
            
            if ( $wpc_from_time !=="" ) {
                $wpc_from_time = date_i18n($wpc_time_format, strtotime( $wpc_from_time ) );
            }
            
            if ( $wpc_to_time !=="" ) {
                $wpc_to_time = " - " . date_i18n($wpc_time_format, strtotime( $wpc_to_time ) );
            }

            echo Wpc_Utilities::wpc_render( $wpc_booking_date . "<br>" . $wpc_from_time. $wpc_to_time );
            
            break;
        case 'wpc_reservation_invoice':
            echo Wpc_Utilities::wpc_render( get_post_meta( $post_id, 'wpc_reservation_invoice', true ) );
            break;
        default:
            apply_filters( 'wpcafe_pro/reservation/report_extra_field_value', $column, $post_id);
        }
    }
}
