jQuery( document ).ready( function () {
	jQuery( '.bkap_preset_link' ).click( function() {

	    if( jQuery( "#booking_enable_date" ).prop("checked") == false ) {
		    jQuery( "#booking_enable_date" ).attr( 'checked', true );
		}

		jQuery("#enable_booking_day_and_time_type").prop("checked", true);
		jQuery( "#enable_date_time_booking_section" ).removeAttr( "style" );
		jQuery("#enable_fixed_time").prop("checked", true);
		
		jQuery( ".bkap_date_timeslot_div" ).removeAttr( "style" );
      	jQuery( ".bkap_duration_date_timeslot_div" ).css( "display", "none" );


      	jQuery( "#enable_only_day_booking_section" ).css( "display", "none" );
      
      	//Hide Fixed Block Booking and Price By Range tab when Date and Time option. 
      	jQuery( "#block_booking" ).css( "display", "none" );
      	jQuery( "#block_booking_price" ).css( "display", "none" );
      
      	bkap_add_weekdays_avail();
      	// hide the multiple days setup fields in the Availability tab
      	jQuery( ".multiple_days_setup" ).css( "display", "none" );
      	// display the purchase without date setting in the General tab
      	jQuery( "#purchase_wo_date_section" ).removeAttr( "style" );
      
      	// Display day/date and timeslot table when Date and time type is enabled.
      	if ( jQuery('#enable_fixed_time').is(':checked') ) { 
        	jQuery( ".bkap_date_timeslot_div" ).removeAttr( "style" );
        	append_weekdays();
      	}

      	if ( jQuery('#enable_duration_time').is(':checked') ) { 
        	jQuery( ".bkap_date_timeslot_div" ).css( "display", "none" );
      	}

      	// all weekdays data   
		  var all_weekdays  = jQuery('*[id^="booking_weekday_"]');
		  var all_lockout   = jQuery('*[id^="weekday_lockout_"]');
		  var all_price     = jQuery('*[id^="weekday_price_"]');

		  console.log(all_weekdays);

      	for ( i=0; i <= 6; i++ ) {    
		    // weekdays
		    var weekday_name = 'booking_weekday_' + i;		    
		    jQuery( all_weekdays[ i ] ).attr( "checked",false );
		    jQuery( all_lockout[ i ] ).prop( "disabled", true );
		    jQuery( all_price[ i ] ).prop( "disabled", true );

		    // removing the weekdays from timeslots table

		    id = 'booking_weekday_' + i;

		    // remove the weekday in the default row
	        jQuery( '#bkap_date_timeslot_table tr[id="bkap_default_date_time_row"]' ).each(function (i, row) {
	          var selector = '#bkap_dateday_selector option[value="' + id + '"]';
	          jQuery( selector ).remove();
	        });
	      
	        // remove the weekday from all the existing rows
	        jQuery( '#bkap_date_timeslot_table tr[id^="bkap_date_time_row_"]' ).each(function (i, row) {
	          var element_id = jQuery( this ).find( 'select[id^="bkap_dateday_selector_"]' ).attr( 'id' );
	          element_id = '#' + element_id;
	          
	          // first check if the same weekday has been selected here
	          var weekday_selected = jQuery( element_id ).val();
	          
	          // if yes then we need to hide that row
	          if ( weekday_selected == id ) {
	            jQuery( this ).hide();
	          } else { // else simply remove the option from the drop down
	            var selector = element_id + ' option[value="' + id + '"]';
	              jQuery( selector ).remove();
	          }
	        });
		}

		jQuery( "#booking_minimum_number_days" ).val( '48' );

		jQuery( "#booking_maximum_number_days" ).val( '360' );
		
		jQuery( "#specific_date_checkbox" ).click();
		
		jQuery( ".bkap_add_new_range" ).click();

		jQuery( "#bkap_date_in_dropdown" ).attr( 'checked', true );

		jQuery("#range_dropdown_2 option[value=specific_dates]").attr('selected', 'selected');
		jQuery("#range_dropdown_2").change();
		jQuery( "#bkap_bookable_nonbookable_2" ).attr( 'checked', true );
		jQuery( "#bkap_specific_date_lockout_2" ).val( '12' );

		/*multiple_dates_specific_holiday_new_id  = "#specific_dates_multidatepicker_2";
        multiple_dates_specific_holiday_value  = jQuery( multiple_dates_specific_holiday_new_id ).val();
		append_specific_dates( 'specific_dates_multidatepicker_2', multiple_dates_specific_holiday_value ); 

		jQuery( ".bkap_add_new_date_time_range" ).click();*/


	    return false;
	 });
});