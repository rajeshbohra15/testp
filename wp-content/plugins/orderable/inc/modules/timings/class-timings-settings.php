<?php
/**
 * Timings settings.
 *
 * @package Orderable/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Timings settings class.
 */
class Orderable_Timings_Settings {
	/**
	 * Open hours settings key.
	 *
	 * @var string
	 */
	public static $open_hours_key = 'store_general_open_hours';

	/**
	 * Service hours settings key.
	 *
	 * @var string
	 */
	public static $service_hours_key = 'store_general_service_hours';

	/**
	 * Holidays settings key.
	 *
	 * @var string
	 */
	public static $holidays_key = 'holidays';

	/**
	 * Init.
	 */
	public static function run() {
		add_filter( 'orderable_default_settings', array( __CLASS__, 'default_settings' ) );
		add_filter( 'wpsf_register_settings_orderable', array( __CLASS__, 'register_settings' ), 20 );
		add_filter( 'orderable_settings_validate', array( __CLASS__, 'validate_settings' ), 10 );
	}

	/**
	 * Add default settings.
	 *
	 * @param array $default_settings
	 *
	 * @return array
	 */
	public static function default_settings( $default_settings = array() ) {
		$default_settings[ self::$open_hours_key ]                  = array();
		$default_settings[ self::$service_hours_key . '_delivery' ] = array(
			array(
				'days'       => array(),
				'period'     => 'all-day',
				'from'       => array(),
				'to'         => array(),
				'frequency'  => '',
				'cutoff'     => '',
				'max_orders' => '',
			),
		);
		$default_settings[ self::$service_hours_key . '_pickup' ]   = array(
			array(
				'days'       => array(),
				'period'     => 'all-day',
				'from'       => array(),
				'to'         => array(),
				'frequency'  => '',
				'cutoff'     => '',
				'max_orders' => '',
			),
		);
		$default_settings[ self::$holidays_key ]                    = array(
			array(
				'from'     => '',
				'to'       => '',
				'services' => array(),
				'repeat'   => 0,
			),
		);

		$default_settings['store_general_lead_time']                 = 0;
		$default_settings['store_general_preorder']                  = 2;
		$default_settings['store_general_service_hours_pickup_same'] = 1;
		$default_settings['store_general_calculation_method']        = 'service';

		return $default_settings;
	}

	/**
	 * Register settings.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function register_settings( $settings = array() ) {
		$settings['tabs'][] = array(
			'id'       => 'store',
			'title'    => __( 'Store Settings', 'orderable' ),
			'priority' => 10,
		);

		$settings['sections'][ 'store_general' ] = array(
			'tab_id'              => 'store',
			'section_id'          => 'general',
			'section_title'       => __( 'Store Settings', 'orderable' ),
			'section_description' => '',
			'section_order'       => 0,
			'fields'              => array(
				array(
					'id'       => 'open_hours',
					'title'    => __( 'Open Hours', 'orderable' ),
					'subtitle' => __( 'The days and hours your store is open. Leave "Max Orders" empty for no limit.', 'orderable' ),
					'type'     => 'custom',
					'default'  => Orderable_Settings::get_setting_default( self::$open_hours_key ),
					'output'   => self::get_open_hours_fields(),
				),
				array(
					'id'       => 'timezone',
					'title'    => __( 'Timezone', 'orderable' ),
					'subtitle' => __( "Your store's current timezone. This should be set to the location of your store.", 'orderable' ),
					'type'     => 'custom',
					'default'  => '',
					'output'   => self::get_timezone_fields(),
				),
				array(
					'id'       => 'services',
					'title'    => __( 'Services', 'orderable' ),
					'subtitle' => sprintf( __( 'Which services do you offer? Please ensure there are <a href="%s" target="_blank">shipping methods</a> available for these services.', 'orderable' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ) ),
					'type'     => 'checkboxes',
					'default'  => array(),
					'choices'  => array(
						'delivery' => __( 'Delivery', 'orderable' ),
						'pickup'   => __( 'Pickup', 'orderable' ),
					),
				),
				array(
					'id'       => 'service_hours',
					'title'    => __( 'Service Hours', 'orderable' ),
					'subtitle' => __( 'The days and hours where you offer delivery/pickup services.', 'orderable' ),
					'type'     => 'custom',
					'default'  => Orderable_Settings::get_setting_default( 'store_general_service_hours' ),
					'output'   => self::get_service_hours_fields(),
				),
				'asap' => array(
					'id'       => 'asap',
					'title'    => __( 'As Soon As Possible', 'orderable' ),
					'subtitle' => __( 'Allow customers to request delivery "ASAP".', 'orderable' ),
					'type'     => 'checkboxes',
					'default'  => array(),
					'choices'  => array(
						'day'  => __( 'Allow "ASAP" as an option when choosing delivery date', 'orderable' ),
					),
				),
				array(
					'id'       => 'lead_time',
					'title'    => __( 'Lead Time', 'orderable' ),
					'subtitle' => __( 'How many days do you need to prepare the order? Leave blank or "0" for same day.', 'orderable' ),
					'type'     => 'number',
					'default'  => Orderable_Settings::get_setting_default( 'store_general_lead_time' ),
				),
				array(
					'id'       => 'preorder',
					'title'    => __( 'Preorder Days', 'orderable' ),
					'subtitle' => __( 'For how many days after the lead time will you accept orders?', 'orderable' ),
					'type'     => 'number',
					'default'  => Orderable_Settings::get_setting_default( 'store_general_preorder' ),
				),
				array(
					'id'       => 'calculation_method',
					'title'    => __( 'Delivery Days Calculation Method', 'orderable' ),
					'subtitle' => __( 'Calculate Lead time and Preorder Days based on all days of the week, open days, service days or weekdays.', 'orderable' ),
					'type'     => 'select',
					'default'  => 'service',
					'choices'  => array(
						'service'  => __( 'Service Days', 'orderable' ),
						'open'     => __( 'Open Days', 'orderable' ),
						'weekdays' => __( 'Weekdays Only', 'orderable' ),
						'all'      => __( 'All Days', 'orderable' ),
					),
					'default'  => Orderable_Settings::get_setting_default( 'store_general_calculation_method' ),
				),
				array(
					'id'       => 'holidays',
					'title'    => __( 'Holidays', 'orderable' ),
					'subtitle' => __( 'Days when your store is closed.', 'orderable' ),
					'type'     => 'custom',
					'default'  => Orderable_Settings::get_setting_default( self::$holidays_key ),
					'output'   => self::get_holidays_fields(),
				),
			),
		);

		return $settings;
	}

	/**
	 * Validate settings.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function validate_settings( $settings = array() ) {
		if ( empty( $settings[ self::$service_hours_key . '_pickup_same' ] ) ) {
			$settings[ self::$service_hours_key . '_pickup_same' ] = 0;
		}

		return $settings;
	}

	/**
	 * Get open hours fields.
	 *
	 * @return void|string
	 */
	public static function get_open_hours_fields() {
		if ( ! is_admin() ) {
			return;
		}

		$days                = Orderable_Timings::get_days_of_the_week();
		$open_hours_settings = Orderable_Settings::get_setting( self::$open_hours_key );

		ob_start();
		?>
		<table class="orderable-table orderable-table--open-hours" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th class="orderable-table__column orderable-table__column--checkbox">&nbsp;</th>
				<th class="orderable-table__column orderable-table__column--label">&nbsp;</th>
				<th class="orderable-table__column orderable-table__column--time"><?php  esc_html_e( 'Open Hours (From)', 'orderable' ); ?></th>
				<th class="orderable-table__column orderable-table__column--time"><?php  esc_html_e( 'Open Hours (To)', 'orderable' ); ?></th>
				<th class="orderable-table__column orderable-table__column--last"><?php  esc_html_e( 'Max Orders (Day)', 'orderable' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $days as $day_number => $day_name ) {
				$day_settings = isset( $open_hours_settings[ $day_number ] ) ? $open_hours_settings[ $day_number ] : array();
				$enabled      = ! empty( $day_settings['enabled'] );
				$from         = ! empty( $day_settings['from'] ) ? $day_settings['from'] : array();
				$to           = ! empty( $day_settings['to'] ) ? $day_settings['to'] : array();
				?>
				<tr class="orderable-table__row <?php if ( ! $enabled ) {
					echo 'orderable-table__row--hidden';
				} ?>">
					<td class="orderable-table__column orderable-table__column--checkbox orderable-table__column--always-visible">
						<input class="orderable-enable-day" type="checkbox" name="orderable_settings[<?php echo esc_attr( self::$open_hours_key ); ?>][<?php echo esc_attr( $day_number ); ?>][enabled]" id="store_general_open_hours_<?php echo esc_attr( $day_number ); ?>_enabled" value="1" <?php checked( $enabled ); ?> data-orderable-day="<?php echo esc_attr( $day_number ); ?>">
					</td>
					<td class="orderable-table__column orderable-table__column--label orderable-table__column--always-visible">
						<label for="<?php echo esc_attr( self::$open_hours_key ); ?>_<?php echo esc_attr( $day_number ); ?>_enabled"><?php echo esc_html( $day_name ); ?></label>
					</td>
					<td class="orderable-table__column orderable-table__column--time">
						<strong class="orderable-table__rwd-labels"><?php  esc_html_e( 'Open Hours (From)', 'orderable' ); ?></strong>
						<?php echo Orderable_Helpers::kses( self::get_time_field( 'orderable_settings[' . self::$open_hours_key . '][' . $day_number . '][from]', $from ), 'form' ); ?>
					</td>
					<td class="orderable-table__column orderable-table__column--time">
						<strong class="orderable-table__rwd-labels"><?php  esc_html_e( 'Open Hours (To)', 'orderable' ); ?></strong>
						<?php echo Orderable_Helpers::kses( self::get_time_field( 'orderable_settings[' . self::$open_hours_key . '][' . $day_number . '][to]', $to ), 'form' ); ?>
					</td>
					<td class="orderable-table__column orderable-table__column--last">
						<?php echo Orderable_Helpers::kses( self::get_max_orders_field( 'orderable_settings[' . self::$open_hours_key . '][' . $day_number . '][max_orders]', $day_settings ), 'form' ); ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get service hours fields.
	 *
	 * @return void|string
	 */
	public static function get_service_hours_fields() {
		if ( ! is_admin() ) {
			return;
		}

		ob_start();

		$services       = Orderable_Timings::get_services();
		$active_service = empty( $services ) ? false : reset( $services );
		$pickup_same    = Orderable_Settings::get_setting( self::$service_hours_key . '_pickup_same' );
		?>
		<p class="orderable-notice orderable-notice--select-service <?php if ( ! empty( $services ) ) {
			echo 'orderable-ui-hide';
		} ?>"><?php _e( 'Please select services available for this store.', 'orderable' ); ?></p>

		<div class="orderable-toolbar">
			<button class="orderable-admin-button orderable-admin-button--delivery <?php if ( 'delivery' === $active_service ) {
				echo 'orderable-trigger-element--active';
			} ?> <?php if ( ! in_array( 'delivery', $services ) ) {
				echo 'orderable-ui-hide';
			} ?>" data-orderable-trigger="toggle-wrapper" data-orderable-wrapper="delivery" data-orderable-wrapper-group="service"><?php _e( 'Delivery', 'orderable' ); ?></button>
			<button class="orderable-admin-button orderable-admin-button--pickup <?php if ( 'pickup' === $active_service ) {
				echo 'orderable-trigger-element--active';
			} ?> <?php if ( ! in_array( 'pickup', $services ) ) {
				echo 'orderable-ui-hide';
			} ?>" data-orderable-trigger="toggle-wrapper" data-orderable-wrapper="pickup" data-orderable-wrapper-group="service"><?php _e( 'Pickup', 'orderable' ); ?></button>
			<div class="orderable-toolbar__actions">
				<span class="orderable-toggle-wrapper orderable-toggle-wrapper--delivery <?php if ( 'delivery' === $active_service ) {
					echo 'orderable-toggle-wrapper--active';
				} ?>" data-orderable-wrapper-group="service">
					<button class="orderable-admin-button orderable-admin-button--primary" data-orderable-trigger="new-row" data-orderable-target=".orderable-table--service-hours-delivery"><span class="dashicons dashicons-plus"></span> <?php _e( 'Add service hours', 'orderable' ); ?></button>
				</span>
				<span class="orderable-element--pickup <?php echo $pickup_same ? 'orderable-element--disabled' : ''; ?> orderable-toggle-wrapper <?php if ( 'pickup' === $active_service ) {
					echo 'orderable-toggle-wrapper--active';
				} ?> orderable-toggle-wrapper--pickup" data-orderable-wrapper-group="service">
					<button class="orderable-admin-button orderable-admin-button--primary" data-orderable-trigger="new-row" data-orderable-target=".orderable-table--service-hours-pickup"><span class="dashicons dashicons-plus"></span> <?php _e( 'Add service hours', 'orderable' ); ?></button>
				</span>
			</div>
		</div>

		<div class="orderable-toggle-wrapper orderable-toggle-wrapper--delivery <?php if ( 'delivery' === $active_service ) {
			echo 'orderable-toggle-wrapper--active';
		} ?>" data-orderable-wrapper-group="service">
			<?php self::output_service_hours_table( 'delivery' ); ?>
		</div>

		<div class="orderable-toggle-wrapper orderable-toggle-wrapper--pickup <?php if ( 'pickup' === $active_service ) {
			echo 'orderable-toggle-wrapper--active';
		} ?>" data-orderable-wrapper-group="service">
			<label for="orderable_settings_<?php echo esc_attr( self::$service_hours_key ); ?>_pickup_same" id="orderable_settings_<?php echo esc_attr( self::$service_hours_key ); ?>_pickup_same_label">
				<input type="checkbox" id="orderable_settings_<?php echo esc_attr( self::$service_hours_key ); ?>_pickup_same" name="orderable_settings[<?php echo esc_attr( self::$service_hours_key ); ?>_pickup_same]" value="1" <?php checked( $pickup_same ); ?> data-orderable-trigger="toggle-element" data-orderable-target=".orderable-element--pickup" data-orderable-toggle-class="orderable-element--disabled">
				<?php _e( 'Same as delivery service hours', 'orderable' ); ?>
			</label>
			<?php self::output_service_hours_table( 'pickup', $pickup_same ); ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Output service hours table.
	 *
	 * @param string $type     Type is 'delivery' or 'pickup'.
	 * @param bool   $disabled Disable the table?
	 */
	public static function output_service_hours_table( $type, $disabled = false ) {
		if ( empty( $type ) ) {
			return;
		}

		$settings = self::get_service_hours_settings( $type );

		if ( empty( $settings ) ) {
			return;
		}

		$open_hours_settings = Orderable_Settings::get_setting( self::$open_hours_key );
		$days_of_the_week    = Orderable_Timings::get_days_of_the_week( 'short' );
		?>
		<table class="orderable-table orderable-table--service-hours-<?php echo esc_attr( $type ); ?> orderable-element--<?php echo esc_attr( $type ); ?> <?php echo $disabled ? 'orderable-element--disabled' : ''; ?>" cellpadding="0" cellspacing="0">
			<tbody class="orderable-table__body">
			<?php foreach ( $settings as $index => $settings_row ) { ?>
				<tr class="orderable-table__row orderable-table__row--repeatable" data-orderable-index="<?php echo esc_attr( $index ); ?>">
					<td class="orderable-table__cell orderable-table__cell--no-padding">
						<table class="orderable-table orderable-table--child orderable-table--compact" cellpadding="0" cellspacing="0">
							<tbody>
							<tr>
								<th class="orderable-table__column orderable-table__column--medium"><?php _e( 'Days', 'orderable' ); ?></th>
								<td>
									<select class="orderable-select orderable-select--multi-select orderable-select--days" name="orderable_settings[<?php echo esc_attr( self::$service_hours_key ); ?>_<?php echo esc_attr( $type ); ?>][<?php echo esc_attr( $index ); ?>][days][]" multiple data-orderable-select-none-option="<?php esc_attr_e( 'Select "Open Hours" first', 'orderable' ); ?>">
										<?php foreach ( $days_of_the_week as $day_number => $day_label ) {
											$is_day_enabled = ! empty( $open_hours_settings[ $day_number ]['enabled'] ); ?>
											<option value="<?php echo esc_attr( $day_number ); ?>" <?php selected( in_array( $day_number, $settings_row['days'] ) && $is_day_enabled ); ?> <?php disabled( ! $is_day_enabled ); ?>><?php echo esc_attr( $day_label ); ?></option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<tr <?php if ( 'all-day' === $settings_row['period'] ) {
								echo 'class="orderable-table__row--last"';
							} ?>>
								<th class="orderable-table__column orderable-table__column--medium"><?php esc_html_e( 'Period', 'orderable' ); ?></th>
								<td>
									<select class="orderable-select" name="orderable_settings[<?php echo esc_attr( self::$service_hours_key ); ?>_<?php echo esc_attr( $type ); ?>][<?php echo esc_attr( $index ); ?>][period]" data-orderable-trigger="toggle-element-select" data-orderable-parent=".orderable-table__row" data-orderable-target="<?php echo esc_attr( json_encode(
										array(
											'all-day'    => array(
												'hide' => '[data-orderable-period="time-slots"]',
											),
											'time-slots' => array(
												'show' => '[data-orderable-period="time-slots"]',
											),
										)
									) ); ?>">
										<option value="all-day" <?php selected( $settings_row['period'], 'all-day' ); ?>><?php esc_html_e( 'All Day', 'orderable' ); ?></option>
										<option value="time-slots" <?php selected( $settings_row['period'], 'time-slots' ); ?>><?php esc_html_e( 'Time Slots', 'orderable' ); ?></option>
									</select>
								</td>
							</tr>
							<?php
							echo Orderable_Helpers::kses( self::get_time_slot_fields( 'orderable_settings[' . self::$service_hours_key . '_' . $type . '][' . $index . ']', $settings_row ), 'form' ); ?>
							</tbody>
						</table>
					</td>
					<td class="orderable-table__column orderable-table__column--remove">
						<a href="javascript: void(0);" class="orderable-table__remove-row" data-orderable-trigger="remove-row"><span class="dashicons dashicons-trash"></span></a>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Output holiday fields.
	 *
	 * @return void|string
	 */
	public static function get_holidays_fields() {
		if ( ! is_admin() ) {
			return;
		}

		$settings = Orderable_Settings::get_setting( self::$holidays_key );
		$defaults = Orderable_Settings::get_setting_default( self::$holidays_key );

		$defaults = $defaults[0];

		ob_start();
		?>
		<div class="orderable-toolbar">
			<div class="orderable-toolbar__actions">
				<button class="orderable-admin-button orderable-admin-button--primary" data-orderable-trigger="new-row" data-orderable-target=".orderable-table--holidays">
					<span class="dashicons dashicons-plus"></span> <?php esc_html_e( 'Add holiday', 'orderable' ); ?></button>
			</div>
		</div>
		<table class="orderable-table orderable-table--holidays" cellpadding="0" cellspacing="0">
			<tbody class="orderable-table__body">
			<?php foreach ( $settings as $index => $settings_row ) {
				$from     = isset( $settings_row['from'] ) ? $settings_row['from'] : $defaults['from'];
				$to       = isset( $settings_row['to'] ) ? $settings_row['to'] : $defaults['to'];
				$services = isset( $settings_row['services'] ) ? $settings_row['services'] : $defaults['services'];
				$repeat   = isset( $settings_row['repeat'] ) ? $settings_row['repeat'] : $defaults['repeat'];
				?>
				<tr class="orderable-table__row orderable-table__row--repeatable" data-orderable-index="<?php echo esc_attr( $index ); ?>">
					<td class="orderable-table__cell orderable-table__cell--no-padding">
						<table class="orderable-table orderable-table--child orderable-table--compact" cellpadding="0" cellspacing="0">
							<tbody>
							<tr>
								<th class="orderable-table__column orderable-table__column--medium"><?php esc_html_e( 'From', 'orderable' ); ?></th>
								<td>
									<input type="text" class="datepicker" name="orderable_settings[<?php echo esc_attr( self::$holidays_key ); ?>][<?php echo esc_attr( $index ); ?>][from]" value="<?php esc_attr_e( $from ); ?>" data-datepicker="{&quot;dateFormat&quot;:&quot;yy-mm-dd&quot;}" readonly="readonly">
								</td>
							</tr>
							<tr>
								<th class="orderable-table__column orderable-table__column--medium"><?php esc_html_e( 'To', 'orderable' ); ?></th>
								<td>
									<input type="text" class="datepicker" name="orderable_settings[<?php echo esc_attr( self::$holidays_key ); ?>][<?php echo esc_attr( $index ); ?>][to]" value="<?php esc_attr_e( $to ); ?>" data-datepicker="{&quot;dateFormat&quot;:&quot;yy-mm-dd&quot;}" readonly="readonly">
								</td>
							</tr>
							<tr>
								<th class="orderable-table__column orderable-table__column--medium"><?php esc_html_e( 'Services', 'orderable' ); ?></th>
								<td>
									<ul class="wpsf-list wpsf-list--checkboxes">
										<li>
											<label>
												<input type="checkbox" name="orderable_settings[<?php echo esc_attr( self::$holidays_key ); ?>][<?php echo esc_attr( $index ); ?>][services][]" value="delivery" <?php checked( in_array( 'delivery', $services, true ) ); ?>>
												<?php esc_html_e( 'Delivery', 'orderable' ); ?>
											</label>
										</li>
										<li>
											<label>
												<input type="checkbox" name="orderable_settings[<?php echo esc_attr( self::$holidays_key ); ?>][<?php echo esc_attr( $index ); ?>][services][]" value="pickup" <?php checked( in_array( 'pickup', $services, true ) ); ?>>
												<?php esc_html_e( 'Pickup', 'orderable' ); ?>
											</label>
										</li>
									</ul>
								</td>
							</tr>
							<tr>
								<th class="orderable-table__column orderable-table__column--medium"><?php esc_html_e( 'Repeat Yearly?', 'orderable' ); ?></th>
								<td>
									<input type="checkbox" name="orderable_settings[<?php echo esc_attr( self::$holidays_key ); ?>][<?php echo esc_attr( $index ); ?>][repeat]" value="1" <?php checked( 1, $repeat ); ?>>
								</td>
							</tr>
							</tbody>
						</table>
					</td>
					<td class="orderable-table__column orderable-table__column--remove">
						<a href="javascript: void(0);" class="orderable-table__remove-row" data-orderable-trigger="remove-row"><span class="dashicons dashicons-trash"></span></a>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get time field.
	 *
	 * @param string $name
	 * @param array  $values
	 *
	 * @return false|string
	 */
	public static function get_time_field( $name, $values = array() ) {
		$defaults = array(
			'hour'   => '',
			'minute' => '',
			'period' => '',
		);

		$values = wp_parse_args( $values, $defaults );

		$hours   = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );
		$minutes = array( '00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55' );

		ob_start();
		?>
		<span class="orderable-time">
			<select class="orderable-time__select orderable-time__select--hour" name="<?php echo esc_attr( $name ); ?>[hour]">
				<?php foreach ( $hours as $hour ) { ?>
					<option value="<?php echo esc_attr( $hour ); ?>" <?php selected( $values['hour'], $hour ); ?>><?php echo esc_html( $hour ); ?></option>
				<?php } ?>
			</select>
			<select class="orderable-time__select orderable-time__select--minute" name="<?php echo esc_attr( $name ); ?>[minute]">
				<?php foreach ( $minutes as $minute ) { ?>
					<option value="<?php echo esc_attr( $minute ); ?>" <?php selected( $values['minute'], $minute ); ?>><?php echo esc_html( $minute ); ?></option>
				<?php } ?>
			</select>
			<select class="orderable-time__select orderable-time__select--period" name="<?php echo esc_attr( $name ); ?>[period]">
				<option value="AM" <?php selected( $values['period'], 'AM' ); ?>>AM</option>
				<option value="PM" <?php selected( $values['period'], 'PM' ); ?>>PM</option>
			</select>
		</span>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get max orders field.
	 *
	 * @param string $name
	 * @param array  $settings
	 *
	 * @return mixed|void
	 */
	public static function get_max_orders_field( $name, $settings = array() ) {
		ob_start();
		?>
		<strong class="orderable-table__rwd-labels"><?php esc_html_e( 'Max Orders (Day)', 'orderable' ); ?></strong>
		<?php echo Orderable_Helpers::get_pro_button( 'max-orders' ); ?>
		<?php

		return apply_filters( 'orderable_get_max_orders_field', ob_get_clean(), $name, $settings );
	}

	/**
	 * Get time slot fields.
	 *
	 * @param string $name
	 * @param array  $settings
	 *
	 * @return mixed|void
	 */
	public static function get_time_slot_fields( $name, $settings = array() ) {
		ob_start();
		?>
		<tr data-orderable-period="time-slots" class="orderable-table__no-td-border" <?php if ( 'all-day' === $settings['period'] ) {
			echo 'style="display: none;"';
		} ?>>
			<th class="orderable-table__column orderable-table__column--medium"><?php esc_html_e( 'Hours', 'orderable' ); ?></th>
			<td class="orderable-table__column orderable-table__column--time" rowspan="4" style="text-align: center;">
				<?php echo Orderable_Helpers::get_pro_button( 'time-slots' ); ?>
			</td>
		</tr>
		<tr data-orderable-period="time-slots" <?php if ( 'all-day' === $settings['period'] ) {
			echo 'style="display: none;"';
		} ?>>
			<th class="orderable-table__column orderable-table__column--medium"><?php esc_html_e( 'Frequency (Mins)', 'orderable' ); ?></th>
		</tr>
		<tr data-orderable-period="time-slots" <?php if ( 'all-day' === $settings['period'] ) {
			echo 'style="display: none;"';
		} ?>>
			<th class="orderable-table__column orderable-table__column--medium"><?php esc_html_e( 'Cutoff (Mins)', 'orderable' ); ?></th>
		</tr>
		<tr data-orderable-period="time-slots" <?php if ( 'all-day' === $settings['period'] ) {
			echo 'style="display: none;"';
		} ?>>
			<th class="orderable-table__column orderable-table__column--medium"><?php esc_html_e( 'Max Orders (Slot)', 'orderable' ); ?></th>
		</tr>
		<?php

		return apply_filters( 'orderable_get_time_slot_fields', ob_get_clean(), $name, $settings );
	}

	/**
	 * Get service hours settings.
	 *
	 * @param string $type
	 *
	 * @return mixed|void
	 */
	public static function get_service_hours_settings( $type = 'delivery' ) {
		$service_hours = array();
		$settings      = Orderable_Settings::get_setting( self::$service_hours_key . '_' . $type );
		$defaults      = Orderable_Settings::get_setting_default( self::$service_hours_key . '_' . $type );

		if ( empty( $settings ) || empty( $defaults ) ) {
			return apply_filters( 'orderable_get_service_hours_settings', $service_hours, $type );
		}

		$defaults = $defaults[0];

		foreach ( $settings as $index => $settings_row ) {
			$service_hours[ $index ] = wp_parse_args( $settings_row, $defaults );
		}

		return apply_filters( 'orderable_get_service_hours_settings', $service_hours, $type );
	}

	/**
	 * Timezone fields.
	 */
	public static function get_timezone_fields() {
		$current_offset  = get_option( 'gmt_offset' );
		$tzstring        = get_option( 'timezone_string' );
		$timezone_format = _x( 'Y-m-d H:i:s', 'timezone date format' );

		$check_zone_info = true;

		// Remove old Etc mappings. Fallback to gmt_offset.
		if ( false !== strpos( $tzstring, 'Etc/GMT' ) ) {
			$tzstring = '';
		}

		if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists.
			$check_zone_info = false;
			if ( 0 == $current_offset ) {
				$tzstring = 'UTC+0';
			} elseif ( $current_offset < 0 ) {
				$tzstring = 'UTC' . $current_offset;
			} else {
				$tzstring = 'UTC+' . $current_offset;
			}
		}

		ob_start();
		?>
		<a href="<?php echo esc_url( admin_url( 'options-general.php#timezone_string' ) ); ?>" target="_blank" class="orderable-admin-button" style="margin-bottom: 20px;"><?php esc_html_e( 'Update Timezone', 'orderable' ); ?></a>

		<p id="timezone-description">
			<?php
				// Translators: %s: Timezone string.
				echo wp_kses( sprintf( __( "Your store's current timezone is %s.", 'orderable' ), '<code>' . $tzstring . '</code>' ), array( 'code' => array() ) ); 
			?>
		</p>

		<p class="timezone-info">
			<span id="utc-time">
				<?php
				echo wp_kses( sprintf(
				/* translators: %s: UTC time. */
					__( 'Universal time is %s.' ),
					'<code>' . date_i18n( $timezone_format, false, true ) . '</code>'
				), array( 'code' => array() ) );
				?>
			</span>

			<?php if ( get_option( 'timezone_string' ) || ! empty( $current_offset ) ) : ?>
				<span id="local-time">
					<?php
					echo wp_kses( sprintf(
					/* translators: %s: Local time. */
						__( 'Local time is %s.' ),
						'<code>' . date_i18n( $timezone_format ) . '</code>'
					), array( 'code' => array() ) );
					?>
				</span>
			<?php endif; ?>
		</p>

		<?php if ( $check_zone_info && $tzstring ) : ?>
			<p class="timezone-info">
				<span>
					<?php
					$now = new DateTime( 'now', new DateTimeZone( $tzstring ) );
					$dst = (bool) $now->format( 'I' );

					if ( $dst ) {
						 esc_html_e( 'This timezone is currently in daylight saving time.' );
					} else {
						 esc_html_e( 'This timezone is currently in standard time.' );
					}
					?>
					<br />
					<?php
					if ( in_array( $tzstring, timezone_identifiers_list(), true ) ) {
						$transitions = timezone_transitions_get( timezone_open( $tzstring ), time() );

						// 0 index is the state at current time, 1 index is the next transition, if any.
						if ( ! empty( $transitions[1] ) ) {
							echo ' ';
							$message = $transitions[1]['isdst'] ?
								/* translators: %s: Date and time. */
								__( 'Daylight saving time begins on: %s.' ) :
								/* translators: %s: Date and time. */
								__( 'Standard time begins on: %s.' );
							echo wp_kses( sprintf(
								$message,
								'<code>' . wp_date( __( 'F j, Y' ) . ' ' . __( 'g:i a' ), $transitions[1]['ts'] ) . '</code>'
							), array( 'code' => array() ) );
						} else {
							esc_html_e( 'This timezone does not observe daylight saving time.' );
						}
					}
					?>
				</span>
			</p>
		<?php endif; ?>
		<?php

		return ob_get_clean();
	}
}