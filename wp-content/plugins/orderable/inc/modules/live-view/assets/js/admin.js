(function( $, document ) {
	"use strict";

	var orderable_live_view = {
		/**
		 * On doc ready.
		 */
		on_ready: function() {
			orderable_live_view.mute_button();
			orderable_live_view.update_url_for_order_status_buttons();
		},

		/**
		 * Heartbeat send.
		 *
		 * @param e
		 * @param data
		 */
		on_heartbeat_send: function( e, data ) {
			data[ 'orderable_heartbeat' ] = 'orderable_live_view';
			data[ 'orderable_filtered_service' ] = orderable_live_view_vars.filtered_service;
			data[ 'orderable_filtered_due_date' ] = orderable_live_view_vars.filtered_due_date;
			data[ 'orderable_orderby' ] = orderable_live_view_vars.orderby;
			data[ 'orderable_last_order_id' ] = orderable_live_view.get_last_order_id();
		},

		/**
		 * Get last order ID.
		 *
		 * @return {number}
		 */
		get_last_order_id: function() {
			return parseInt( orderable_live_view_vars.last_order_id );
		},

		/**
		 * Heartbeat tick.
		 *
		 * @param e
		 * @param data
		 */
		on_heartbeat_tick: function( e, data ) {
			if ( typeof data.orderable === 'undefined' ) {
				return;
			}

			if ( data.orderable.last_order_id <= orderable_live_view.get_last_order_id() ) {
				 return;
			}

			// If there are new orders, reload the page.
			$( '#posts-filter' ).load( orderable_live_view_vars.url + ' #posts-filter > *', function( response, status, xhr ) {
				orderable_live_view.play_ding();

				if ( status === "error" ) {
					console.log( 'Live View Error Response', response );
					console.log( 'Live View Error Status', status );
				}

				$( document ).trigger( 'orderable-live-view-updated' );
			} );
		},

		/**
		 * Play ding sound.
		 */
		play_ding: function() {
			var mute_status = parseInt( $( '.orderable-live-view-button--audio' ).data( 'orderable-mute-status' ) ),
				$ding = $( '#orderable_ding' );

			if ( 1 === mute_status || $ding.length < 0 ) {
				return;
			}

			$ding[0].play();
		},

		/**
		 * Setup mute button.
		 */
		mute_button: function() {
			$( '.orderable-live-view-button--audio' ).on( 'click', function() {
				var $button = $( this ),
					mute_status = parseInt( $button.data( 'orderable-mute-status' ) ) === 1 ? 0 : 1, // invert status.
					current_text = $button.text(),
					new_text = $button.data( 'orderable-alt-text' );

				$button.data( 'orderable-mute-status', mute_status ).data( 'orderable-alt-text', current_text ).text( new_text );
			} );
		},

		/**
		 * Append '&orderable_live_view' to the order status hyperlinks.
		 */
		update_url_for_order_status_buttons: function () {
			$( '.subsubsub li a' ).each( function () {
				var href = $( this ).attr( 'href' );
				$( this ).attr( 'href', `${href}&orderable_live_view` );
			} );
		}
	};

	$( document ).ready( orderable_live_view.on_ready );
	$( document ).on( 'heartbeat-send', orderable_live_view.on_heartbeat_send );
	$( document ).on( 'heartbeat-tick', orderable_live_view.on_heartbeat_tick );
}( jQuery, document ));