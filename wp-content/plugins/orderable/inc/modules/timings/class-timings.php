<?php
/**
 * Module: Timings.
 *
 * Delivery/pickup date and time and lead times.
 *
 * @package Orderable/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Timings module class.
 */
class Orderable_Timings {
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
			'timings-settings' => 'Orderable_Timings_Settings',
			'timings-checkout' => 'Orderable_Timings_Checkout',
			'timings-order'    => 'Orderable_Timings_Order',
		);

		foreach ( $classes as $file_name => $class_name ) {
			require_once ORDERABLE_MODULES_PATH . 'timings/class-' . $file_name . '.php';

			$class_name::run();
		}
	}

	/**
	 * Add services shrotcodes.
	 */
	public static function add_shortcodes() {
		add_shortcode( 'orderable-open-hours', array( __CLASS__, 'orderable_open_hours_shortcode' ) );
	}

	/**
	 * Get days of the week.
	 *
	 * @param string $label    Label length 'full' or 'short'.
	 * @param int    $last_day Last day of the week.
	 *
	 * @return array
	 */
	public static function get_days_of_the_week( $label = 'full', $last_day = 6 ) {
		$days = array(
			0 => 'full' === $label ? __( 'Sunday', 'orderable' ) : __( 'Sun', 'orderable' ),
			1 => 'full' === $label ? __( 'Monday', 'orderable' ) : __( 'Mon', 'orderable' ),
			2 => 'full' === $label ? __( 'Tuesday', 'orderable' ) : __( 'Tue', 'orderable' ),
			3 => 'full' === $label ? __( 'Wednesday', 'orderable' ) : __( 'Wed', 'orderable' ),
			4 => 'full' === $label ? __( 'Thursday', 'orderable' ) : __( 'Thu', 'orderable' ),
			5 => 'full' === $label ? __( 'Friday', 'orderable' ) : __( 'Fri', 'orderable' ),
			6 => 'full' === $label ? __( 'Saturday', 'orderable' ) : __( 'Sat', 'orderable' ),
		);

		if ( 6 !== $last_day ) {
			$index = $last_day + 1;
			$start = array_slice( $days, $index, null, true );
			$end   = array_slice( $days, 0, $index, true );

			$days = $start + $end;
		}

		return $days;
	}

	/**
	 * Get open hours.
	 *
	 * @return array
	 */
	public static function get_open_hours() {
		$open_hours          = array();
		$days                = Orderable_Timings::get_days_of_the_week( 'full', 0 );
		$open_hours_settings = Orderable_Settings::get_setting( Orderable_Timings_Settings::$open_hours_key );
		$current_day         = absint( current_time( 'w' ) );
		$tense               = 'last';

		foreach ( $days as $index => $day ) {
			$day_settings = isset( $open_hours_settings[ $index ] ) ? $open_hours_settings[ $index ] : null;

			if ( empty( $day_settings ) ) {
				continue;
			}

			if ( $index === $current_day ) {
				$tense = 'this';
			}

			$day_name_en     = date( 'l', strtotime( "Sunday +{$index} days" ) );
			$timestamp       = strtotime( $tense . ' ' . $day_name_en );
			$services_on_day = Orderable_Services::get_services_on_day( $timestamp );
			$hours           = __( 'Closed', 'orderable' );
			$is_holiday      = self::is_holiday( $timestamp );
			$is_holiday      = $is_holiday && empty( array_filter( $services_on_day ) );
			$open            = isset( $day_settings['enabled'] );

			if ( $open && $is_holiday ) {
				$hours = __( 'Holiday', 'orderable' );
			} elseif ( $open && ! $is_holiday ) {
				$from  = sprintf( '%s:%s %s', $day_settings['from']['hour'], $day_settings['from']['minute'], $day_settings['from']['period'] );
				$to    = sprintf( '%s:%s %s', $day_settings['to']['hour'], $day_settings['to']['minute'], $day_settings['to']['period'] );
				$hours = sprintf( '%s &mdash; %s', $from, $to );
			}

			$open_hours[ $index ] = array(
				'day'       => $day,
				'date'      => date( 'd', $timestamp ),
				'hours'     => $hours,
				'is_closed' => ! $open,
				'services'  => $services_on_day,
			);
		}

		return apply_filters( 'orderable_open_hours', $open_hours );
	}

	/**
	 * Get services offered.
	 *
	 * @return array
	 */
	public static function get_services() {
		$services = Orderable_Settings::get_setting( 'store_general_services' );

		return $services ? $services : array();
	}

	/**
	 * Get service days for service type.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function get_service_days( $type = 'delivery' ) {
		$days = Orderable_Timings::get_days_of_the_week();

		if ( 'pickup' === $type ) {
			$pickup_same = Orderable_Settings::get_setting( Orderable_Timings_Settings::$service_hours_key . '_pickup_same' );
			$type        = $pickup_same ? 'delivery' : $type;
		}

		$settings     = Orderable_Settings::get_setting( Orderable_Timings_Settings::$service_hours_key . '_' . $type );
		$service_days = array();

		if ( empty( $settings ) ) {
			return $service_days;
		}

		foreach ( $settings as $setting_row ) {
			if ( empty( $setting_row['days'] ) ) {
				continue;
			}

			foreach ( $setting_row['days'] as $day_number ) {
				$service_days[ $day_number ] = $days[ $day_number ];
			}
		}

		return $service_days;
	}

	/**
	 * Get dates available for service type.
	 *
	 * @param string $type
	 *
	 * @return array|bool Array when dates are available, "true" when no date selection required, "false" when no dates available.
	 * @throws Exception
	 */
	public static function get_service_dates( $type = false ) {
		if ( ! WC()->cart->needs_shipping() ) {
			// Return true. No service is required.
			return apply_filters( 'orderable-service-dates', true, $type );
		}

		$type          = ! $type ? Orderable_Services::get_selected_service( false ) : $type;
		$service_dates = array();

		if ( ! $type ) {
			// Return false. A service should be selected.
			// @todo Check if this should be true when no shipping method is selected yet.
			return apply_filters( 'orderable-service-dates', false, $type );
		}

		$services = Orderable_Timings::get_services();

		if ( ! in_array( $type, $services, true ) ) {
			// Return true. This service doesn't require date/time selection.
			return apply_filters( 'orderable-service-dates', true, $type );
		}

		$lead_days     = (int) Orderable_Settings::get_setting( 'store_general_lead_time' );
		$preorder_days = (int) Orderable_Settings::get_setting( 'store_general_preorder' );
		$service_days  = self::get_service_days( $type );
		$start_date    = new DateTime( 'now', wp_timezone() );
		$start_date->setTime( 0, 0 ); // Set time to midnight 00:00:00.
		$holidays = self::get_holiday_timestamps_by_type( $type );

		$date_range = new ArrayIterator( array( $start_date ) );

		$counted_lead_days     = 0;
		$counted_preorder_days = 0;

		if ( ! empty( $service_days ) ) {
			foreach ( $date_range as $index => $date ) {
				// If we're at the preorder day limit, break the loop.
				if ( $counted_preorder_days > $preorder_days ) {
					break;
				}

				$timestamp = $date->getTimestamp();
				$date_range->append( clone $date->modify( '+1 day' ) );

				// If this date is a holiday, add a date to the array and continue.
				if ( in_array( $timestamp, $holidays, true ) ) {
					continue;
				}

				$today              = self::is_today( $timestamp );
				$timestamp_adjusted = $timestamp + self::get_timezone_offset( $timestamp );

				if ( ! self::is_delivery_day( $timestamp_adjusted, $type ) ) {
					continue;
				}

				// If date isn't available, add a date to the array and continue.
				if ( ! apply_filters( 'orderable_date_available', true, $timestamp, $today, $type ) ) {
					continue;
				}

				// Check if we've counted enough lead days, otherwise add a date to the array and continue.
				if ( $lead_days > $counted_lead_days ) {
					$counted_lead_days ++;
					continue;
				}

				// Check if we've counted enough lead days, otherwise add a date to the array and continue.
				if ( $lead_days > $counted_lead_days ) {
					$counted_lead_days ++;
					continue;
				}

				$format = get_option( 'date_format' );
				$slots  = self::get_slots( $timestamp, $type );

				if ( empty( $slots ) ) {
					continue;
				}

				$service_dates[] = array(
					'timestamp' => $timestamp,
					'datetime'  => date( $format, $timestamp ),
					'formatted' => self::get_formatted_date( $timestamp ),
					'slots'     => $slots,
				);

				// Now that we've counted lead days, start counting preorder days.
				// The first starting date is day "0".
				$counted_preorder_days ++;
			}
		}

		// If empty, return false; no dates available.
		// Otherwise, return dates.
		$service_dates = empty( $service_dates ) ? false : $service_dates;

		return apply_filters( 'orderable-service-dates', $service_dates, $type );
	}

	/**
	 * Is the given day a allowed day as per the selected Lead time calculation method.
	 *
	 * @param int $timestamp Timestamp.
	 */
	public static function is_delivery_day( $timestamp, $type ) {
		$minmax_method = Orderable_Settings::get_setting( 'store_general_calculation_method' );
		$day           = (int) date( 'w', $timestamp );

		// If minmax method is all, return true. All days are delivery days.
		if ( 'all' === $minmax_method ) {
			return apply_filters( 'orderable_is_delivery_day', true, $timestamp, $type );
		}

		// If minmax method is weekdays, check if the day we're checking is a weekday.
		// If so, return true.
		if ( 'weekdays' === $minmax_method ) {
			$is_delivery_day = in_array( $day, array( 1, 2, 3, 4, 5 ), true );

			return apply_filters( 'iconic_wds_is_delivery_day', $is_delivery_day, $timestamp, $type );
		}

		if ( 'service' === $minmax_method ) {
			$service_days = self::get_service_days( $type );
			return apply_filters( 'iconic_wds_is_delivery_day', array_key_exists( $day, $service_days ), $timestamp, $type );
		}

		if ( 'open' === $minmax_method ) {
			$open_days = self::get_open_days();
			return apply_filters( 'iconic_wds_is_delivery_day', array_key_exists( $day, $open_days ), $timestamp, $type );
		}
	}

	/**
	 * Get formatted date.
	 *
	 * @param int $timestamp GMT timestamp
	 *
	 * @return string|void
	 * @throws Exception
	 */
	public static function get_formatted_date( $timestamp ) {
		$format             = get_option( 'date_format' );
		$timestamp_adjusted = $timestamp + self::get_timezone_offset( $timestamp );
		$date               = date_i18n( $format, $timestamp_adjusted );

		if ( self::is_today( $timestamp ) ) {
			$date = __( 'Today', 'orderable' );
		} elseif ( self::is_tomorrow( $timestamp ) ) {
			$date = __( 'Tomorrow', 'orderable' );
		}

		return apply_filters( 'orderable_get_formatted_date', $date, $timestamp );
	}

	/**
	 * Is this timestamp today?
	 *
	 * @param int $timestamp GMT timestamp
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function is_today( $timestamp ) {
		$timestamp_date = date( 'Y-m-d', $timestamp + self::get_timezone_offset( $timestamp ) );
		$current_date   = current_time( 'Y-m-d' );

		return $timestamp_date === $current_date;
	}

	/**
	 * Is this timestamp today?
	 *
	 * @param int $timestamp GMT timestamp
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function is_tomorrow( $timestamp ) {
		$timestamp_date = date( 'Y-m-d', $timestamp + self::get_timezone_offset( $timestamp ) );
		$current_date   = current_time( 'timestamp' );
		$tomorrows_date = date( 'Y-m-d', strtotime( '+1 day', $current_date ) );

		return $timestamp_date === $tomorrows_date;
	}

	/**
	 * Get slots for service type and day of the week.
	 *
	 * @param int    $timestamp Timestamp (GMT).
	 * @param string $type
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function get_slots( $timestamp, $type = 'delivery' ) {
		$slots = array();

		if ( empty( $timestamp ) ) {
			return $slots;
		}

		if ( 'pickup' === $type ) {
			$pickup_same = Orderable_Settings::get_setting( Orderable_Timings_Settings::$service_hours_key . '_pickup_same' );
			$type        = $pickup_same ? 'delivery' : $type;
		}

		$settings = Orderable_Settings::get_setting( Orderable_Timings_Settings::$service_hours_key . '_' . $type );

		if ( empty( $settings ) ) {
			return $slots;
		}

		$date_time         = self::get_date_time_by_timestamp( $timestamp );
		$current_timestamp = time();
		$timestamp         = ! $timestamp ? $current_timestamp : $timestamp;
		$day_number        = $date_time->format( 'w' );

		foreach ( $settings as $setting_key => $setting_row ) {
			$days = array_map( 'absint', $setting_row['days'] );

			if ( ! in_array( $day_number, $days ) ) {
				continue;
			}

			$slots = array(
				'all-day' => array(
					'formatted'   => __( 'All Day', 'orderable' ),
					'value'       => 'all-day',
					'timestamp'   => $timestamp,
					'setting_key' => $setting_key,
					'setting_row' => $setting_row,
				),
			);

			break;
		}

		return apply_filters( 'orderable_get_slots', $slots, $timestamp, $type );
	}

	/**
	 * Get timezone offset for the given timestamp.
	 *
	 * This function is inspired by wc_timezone_offset().
	 * wc_timezone_offset() always returns the offset for today's date,
	 * which is inacurate during daylight savings.
	 * Hence this function, which returns offset for specified date.
	 *
	 * @param int $timestamp Timestamp.
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function get_timezone_offset( $timestamp ) {
		$timezone = get_option( 'timezone_string' );

		if ( $timezone ) {
			$timezone_object = new DateTimeZone( $timezone );
			$datetime        = new DateTime();

			$datetime->setTimestamp( $timestamp );

			return $timezone_object->getOffset( $datetime );
		} else {
			return floatval( get_option( 'gmt_offset', 0 ) ) * HOUR_IN_SECONDS;
		}
	}

	/**
	 * Get holidays.
	 *
	 * @return array
	 */
	public static function get_holidays() {
		$holidays = array();
		$settings = Orderable_Settings::get_setting( Orderable_Timings_Settings::$holidays_key );

		if ( empty( $settings ) ) {
			return apply_filters( 'orderable_get_holidays', $holidays );
		}

		$today           = new DateTime( 'now', wp_timezone() );
		$today_timestamp = $today->getTimestamp();

		foreach ( $settings as $index => $setting ) {
			if ( empty( $setting['from'] ) ) {
				continue;
			}

			$holidays[ $index ] = $setting;

			if ( empty( $setting['services'] ) ) {
				$holidays[ $index ]['services'] = array();
			}

			$holidays[ $index ]['timestamps'] = array();

			$from = DateTime::createFromFormat( 'Y-m-d H:i:s', $setting['from'] . ' 00:00:00', wp_timezone() );
			$to   = ! empty( $setting['to'] ) ? DateTime::createFromFormat( 'Y-m-d H:i:s', $setting['to'] . ' 00:00:00', wp_timezone() ) : $from;

			// Add one minute so last slot is included.
			$to->modify( '+1 minute' );

			// Add years to from and to if this holiday repeats and is in the past.
			if ( $today_timestamp > $to->getTimestamp() && ! empty( $setting['repeat'] ) ) {

				// Calculate how many years have past and append 1 additional.
				$today_datetime = new DateTime();
				$today_datetime->setTimestamp( $today_timestamp );
				
				$interval = $today_datetime->diff( $to );
				$year     = intval( $interval->format( '%Y' ) ) + 1;
				
				$from->modify( '+' . $year . ' year' );
				$to->modify( '+' . $year . ' year' );

				$holidays[ $index ]['from'] = $from->format( 'Y-m-d' );
				$holidays[ $index ]['to']   = ! empty( $holidays[ $index ]['to'] ) ? $to->format( 'Y-m-d' ) : '';
			}

			$range = new DatePeriod(
				$from,
				new DateInterval( 'P1D' ), // Every 1 day.
				$to
			);

			if ( empty( $range ) ) {
				continue;
			}

			foreach ( $range as $time ) {
				$holidays[ $index ]['timestamps'][] = $time->getTimestamp();
			}
		}

		return apply_filters( 'orderable_get_holidays', $holidays );
	}

	/**
	 * Is this a holiday?
	 *
	 * @param bool $timestamp Timestamp of date at 00:00am
	 *
	 * @return bool
	 */
	public static function is_holiday( $timestamp, $service = null ) {
		static $is_holiday = array();

		$key = $timestamp . $service;

		if ( isset( $is_holiday[ $key ] ) ) {
			return $is_holiday[ $key ];
		}

		$holidays = self::get_holidays();

		$is_holiday[ $key ] = false;

		if ( empty( $holidays ) ) {
			return $is_holiday[ $key ];
		}

		foreach ( $holidays as $holiday ) {
			if ( ! in_array( $timestamp, $holiday['timestamps'], true ) ) {
				continue;
			}

			if ( is_null( $service ) || in_array( $service, $holiday['services'], true ) ) {
				$is_holiday[ $key ] = $holiday;

				return $is_holiday[ $key ];
			}
		}

		return $is_holiday[ $key ];
	}

	/**
	 * Get holiday timestamps by service type.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function get_holiday_timestamps_by_type( $type = 'delivery' ) {
		$timestamps = array();
		$holidays   = self::get_holidays();

		if ( empty( $holidays ) ) {
			return apply_filters( 'orderable_get_holiday_timestamps_by_type', $timestamps, $type );
		}

		foreach ( $holidays as $holiday ) {
			if ( ! in_array( $type, $holiday['services'], true ) || empty( $holiday['timestamps'] ) ) {
				continue;
			}

			$timestamps = array_merge( $timestamps, $holiday['timestamps'] );
		}

		$timestamps = array_unique( $timestamps );

		sort( $timestamps );

		return apply_filters( 'orderable_get_holiday_timestamps_by_type', $timestamps, $type );
	}

	/**
	 * Open hours shortcode.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function orderable_open_hours_shortcode( $args = array() ) {
		$defaults = array(
			'services' => true,
			'date'     => true,
		);

		$args             = wp_parse_args( $args, $defaults );
		$args['services'] = (boolean) json_decode( strtolower( $args['services'] ) );
		$args['date']     = (boolean) json_decode( strtolower( $args['date'] ) );

		ob_start();

		include Orderable_Helpers::get_template_path( 'open-hours.php', 'timings' );

		return ob_get_clean();
	}

	/**
	 * Get date/time by timestamp in correct timezone.
	 *
	 * @param $timestamp
	 *
	 * @return DateTime
	 * @throws Exception
	 */
	public static function get_date_time_by_timestamp( $timestamp ) {
		$date = new DateTime( 'now', wp_timezone() );
		$date->setTimestamp( $timestamp );

		return $date;
	}

	/**
	 * Convert time array to 24 hour.
	 *
	 * @param array $time
	 *
	 * @return array
	 */
	public static function convert_time_to_24_hour( $time ) {
		$time['hour'] = absint( $time['hour'] );

		if ( 'PM' === $time['period'] && 12 !== $time['hour'] ) {
			$time['hour'] = $time['hour'] + 12;
		}

		if ( 'AM' === $time['period'] && 12 === $time['hour'] ) {
			$time['hour'] = 0;
		}

		return $time;
	}

	/**
	 * ASAP Setting.
	 * 
	 * Returns an array of places where ASAP should be returned.
	 *
	 * @return array
	 */
	public static function get_asap_setting() {
		return (array) Orderable_Settings::get_setting( 'store_general_asap' );
	}

	/**
	 * Get open days.
	 *
	 * @return array
	 */
	public static function get_open_days() {
		$open_hours_settings = Orderable_Settings::get_setting( Orderable_Timings_Settings::$open_hours_key );
		$open_hours          = array();
		$days                = self::get_days_of_the_week();

		foreach ( $open_hours_settings as $day => $open_hour ) {
			if ( ! empty( $open_hour['enabled'] ) ) {
				$open_hours[ $day ] = $days[ $day ];
			}
		}

		return $open_hours;
	}
}
