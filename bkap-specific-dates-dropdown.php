<?php
/**
 * Plugin Name: BKAP Specific Dates Dropdown
 * Description: Showing specific dates in dropdown on front end product page.
 * Version: 1.0
 * Author: Tyche Softwares
 * Author URI: http://www.tychesoftwares.com/
 * Requires PHP: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 3.3.4
 * Text Domain: bkap-specific-dates-dropdown
 */

if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BKAP_Specific_Dates_Dropdown' ) ) :

/**
 * Booking & Appointment Plugin Specific Dates Dropdown Class
 *
 * @class BKAP_Specific_Dates_Dropdown
 */

class BKAP_Specific_Dates_Dropdown {

	/**
     * Default constructor
     *
     * @since 1.0
     */

	public function __construct() {

		/* Showing Column on Product Page which displays Specific Dates */
		add_filter( 'manage_product_posts_columns', 					array( $this, 'smashing_filter_posts_columns' ) );
		add_action( 'manage_product_posts_custom_column', 				array( $this, 'wcbkap_custom_columns' ) , 2 );

		/* Below hooks are for showing the specific dates in dropdown */
		add_filter( 'bkap_check_to_show_start_date_field', 				array( $this, 'bkap_check_to_show_start_date_field_callback' ), 10, 5 );
		add_action( 'bkap_after_purchase_wo_date', 						array( $this, 'bkap_specific_dropdown_option' ), 11, 2 );
		add_filter( 'bkap_update_serialized_post_meta_booking_option', 	array( $this, 'bkap_update_serialized_post_meta_booking_option_callback' ), 10, 2 );
		add_filter( 'bkap_add_setup_data_for_booking_options', 			array( $this, 'bkap_add_setup_data_for_booking_options_callback' ), 10, 2 );

		add_action( 'bkap_before_enable_bookingoption',    array( &$this, 'bkap_preset_settings' ), 10, 1 );

		add_action( 'after_bkap_load_product_scripts_js', array( $this, 'after_bkap_load_product_scripts_js_callback' ), 10 );

		//add_filter( 'bkap_display_booking_fields', array( $this, 'bkap_display_booking_fields_callback' ), 10, 3 );
		//add_action( 'admin_head', array( $this, 'bsdd_hook_css') );
	}

	public function after_bkap_load_product_scripts_js_callback(){

		wp_register_script(
				'bkap-specific-dates-dropdown',
				plugins_url().'/bkap-specific-dates-dropdown/assets/js/bkap-specific-date-dropdown.js',
				'',
				'1.0',
				true );

		wp_enqueue_script( 'bkap-specific-dates-dropdown' );
	}

	public static function bkap_preset_settings( $post_id ) {

        ?>
        <div class="bkap_preset_div button" style="margin-bottom: 5px;">
        <b><a class="bkap_preset_link" title="Click to preset the Booking Settings" >
            <?php _e( 'Preset', 'woocommerce-booking' ); ?>
        </a></b>
    	</div>
        <?php
    }

	public function bkap_display_booking_fields_callback( $display, $product_id, $booking_settings ) {
	  	if ( $display ) {

		    $display = bkap_check_weekdays_status( $product_id, $display );

		    if ( ! $display ) {
		      	if ( isset( $booking_settings[ 'booking_specific_booking' ] ) && $booking_settings['booking_specific_booking'] == "on" ) {
			        	$today_midnight     = strtotime('today midnight');
			        	$booking_dates_arr  = $booking_settings['booking_specific_date'];
			        	foreach ( $booking_dates_arr as $key => $value) {
			            	if ( strtotime( $key ) < $today_midnight ){
			                	unset( $booking_dates_arr[$key] );
			            	}
			        	}

			        	if ( ! empty( $booking_dates_arr ) ) {
			          		foreach ($booking_dates_arr as $key => $value) {
			            		$status = bkap_check_day_booking_available( $product_id, $key );
			            		if ( $status ) {
			              			$display = true;
			              			break;
			            		}
			          		}
		        		}
		      	}
		    }
	  	}
	  return $display;
	}

	public function bsdd_hook_css() {
    ?>
        <style type="text/css">
            table.fixed {
			    table-layout: none !important;
			}
        </style>
    <?php
	}

	/**
	 * Adding custom column in Admin Column
	 *
	 * @param string $column key of column.
	 */
	public function smashing_filter_posts_columns( $columns ) {
	  	$columns['specific_dates'] = __( 'Specific Dates' );
	  	return $columns;
	}

	/**
	 * Displaying the value of the column.
	 *
	 * @param string $column key of column.
	 */
	public function wcbkap_custom_columns( $column ) {
	  	global $post;
	  	if ( get_post_type( $post->ID ) === 'product' ) {
	    	if ( 'specific_dates' === $column ) {
	      		$specific_dates = get_post_meta( $post->ID, '_bkap_specific_dates', true );
	      		if ( $specific_dates && count($specific_dates) > 0 ){
	        		$specific_dates_keys  = array_keys($specific_dates);
	        		$dates                = implode(",", $specific_dates_keys);
	        		echo '<div class="bkap-specific-dates">'.$dates.'</div>';
	      		}
	    	}
	  	}
	}

	/**
	 * Showing Specific Dates in DropDown instead of calendar.
	 *
	 * @param bool $display Show Start Date Calendar Field.
	 * @param int $product_id Product ID.
	 * @param array $booking_settings Booking Settings.
	 * @param array $hidden_dates Hidden Dates array which contains the extra infomration.
	 * @param obj $global_settings Global Booking Settings.
	 *
	 * @return $display True if Start Date Calendar field should be displayed else false.
	 */
	public function bkap_check_to_show_start_date_field_callback( $display, $product_id, $booking_settings, $hidden_dates, $global_settings ){

	  	if ( is_product() && isset( $booking_settings['bkap_date_in_dropdown'] ) && "on" === $booking_settings['bkap_date_in_dropdown'] ){

	    	if ( isset( $hidden_dates['additional_data']['specific_dates'] ) && "" != $hidden_dates['additional_data']['specific_dates'] ) {
	      		//if ( isset( $booking_settings[ 'booking_recurring_booking' ] ) && $booking_settings['booking_recurring_booking'] == "" ) {

			        $display = false;
			        $specific_dates_str = $hidden_dates['additional_data']['specific_dates'];
			        $specific_dates     = explode( ",", $specific_dates_str );

			        $label              = __( get_option( "book_date-label" ) ,"woocommerce-booking" );
			        $date_formats       = bkap_get_book_arrays( 'bkap_date_formats' );
			        $date_format_set    = $date_formats[ $global_settings->booking_date_format ];
			        $lockout_dates      = $hidden_dates['additional_data']['wapbk_lockout_days'];
					$select_date        = __( "Choisissez une date", "woocommerce-booking" );
					// $selected 			= count( $specific_dates ) == 1 ? 'selected' : ''; // auto-select the first occurency of the dropdown menu (YB)
					
			        ?>

			        <label class="book_start_date_label" style="margin-top:5em;"><?php echo $label; ?>: </label>
			        <select name="booking_calender" id="booking_calender">
			            <option value="select_date"><?php echo $select_date; ?></option>
			            <?php
			            foreach( $specific_dates as $specific_dates_array_key => $specific_dates_array_value ) {
			                $date           = strtotime( trim( $specific_dates_array_value, '"' ) );
			                // $check_in_date  = date( $date_format_set, $date );
			                $check_in_date  = date_i18n( $date_format_set, $date );

			                if ( !preg_match ('/'.$specific_dates_array_value.'/', $lockout_dates ) ) {
			                  ?><option value=<?php echo $specific_dates_array_value; ?> <?php echo $selected; ?> ><?php echo $check_in_date; ?></option><?php
			                }
			            }
			            ?>
		            </select><br/>
		            <?php
	      		//}
	    	}
	  	}
	  	return $display;
	}

	/* Adding option in Booking Meta box for enabling the specific date dropdown */
	function bkap_specific_dropdown_option( $product_id, $booking_settings ) {

	  	?>
	  	<div id="date_in_dropdown_section" class="booking_options-flex-main" style="padding: 10px 0;">
	    	<div class="booking_options-flex-child">
	        	<label for="bkap_date_in_dropdown"><?php _e( 'Show dates in dropdown?', 'woocommerce-booking' ); ?></label>
	    	</div>

		    <?php
		      	$bkap_date_in_dropdown = '';
		      	if ( isset( $booking_settings[ 'bkap_date_in_dropdown' ] ) && 'on' == $booking_settings[ 'bkap_date_in_dropdown' ] ) {
		          	$bkap_date_in_dropdown = 'checked';
		      	}
		    ?>
		    <div class="booking_options-flex-child">
		        <label class="bkap_switch">
		            <input type="checkbox" name="bkap_date_in_dropdown" id="bkap_date_in_dropdown" <?php echo $bkap_date_in_dropdown; ?>>
		            <div class="bkap_slider round"></div>
		        </label>
		    </div>

		    <div class="booking_options-flex-child bkap_help_class">
		        <img class="help_tip" width="16" height="16" data-tip="<?php _e( 'Enable this setting if you want to show dates in dropdown instead of calendar', 'woocommerce-booking' ); ?>" src="<?php echo plugins_url() ;?>/woocommerce/assets/images/help.png" />
		    </div>
		</div>
		<?php
	}

	function bkap_update_serialized_post_meta_booking_option_callback( $updated_settings, $booking_options ) {
	  	if ( isset( $booking_options[ '_bkap_date_in_dropdown' ] ) ) {
	      	$updated_settings[ 'bkap_date_in_dropdown' ] = $booking_options[ '_bkap_date_in_dropdown' ];
	  	} else {
	      	$updated_settings[ 'bkap_date_in_dropdown' ] = '';
	  	}
	  	return $updated_settings;
	}

	/* Adding date in dropdown option in booking option array for storing it properly */
	function bkap_add_setup_data_for_booking_options_callback( $final_booking_options, $clean_booking_options ) {
	  	if ( isset( $clean_booking_options->bkap_date_in_dropdown ) ) {
	    	$final_booking_options[ '_bkap_date_in_dropdown' ] = $clean_booking_options->bkap_date_in_dropdown;
	  	}
	  	return $final_booking_options;
	}
}
$bkap_specific_dates_dropdown = new BKAP_Specific_Dates_Dropdown();

endif;