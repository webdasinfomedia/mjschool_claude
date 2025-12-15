jQuery(document).ready(function(jQuery)
{
	"use strict";
	jQuery( '#custom_field_form' ).validationEngine({promptPosition : "bottomLeft",maxErrorsPerField: 1});			
 	jQuery( ".file_edit").on( "load", function() {	
		return false;
		// Handler for .load() called.
	});
	jQuery( ".required_rule").on( 'change', function (event) 
	{
		jQuery( '.nullable_rule' ).iCheck( 'uncheck' );
	});
	jQuery( ".nullable_rule").on( 'change', function (event) 
	{		 
		jQuery( '.required_rule' ).iCheck( 'uncheck' );
	});
	jQuery( ".nullable_rule").on( 'ifUnchecked', function (event) 
	{
		 
		jQuery( '.required_rule' ).iCheck( 'check' );
	});
	jQuery( ".required_rule").on( 'ifUnchecked', function (event) 
	{
		 
		jQuery( '.nullable_rule' ).iCheck( 'check' );
	});
	jQuery( ".only_number").on( 'change', function (event) 
	{
		if (jQuery( "input#only_number_id").is( ':checked' ) ) { 
		
			jQuery( '.only_char,.char_space,.char_num,.email,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_char,.char_space,.char_num,.email,.url,.date' ).iCheck( 'uncheck' );
			jQuery( '.only_char,.char_space,.char_num,.email,.url,.date' ).attr( 'disabled', true);
		}
		else{
			jQuery( '.only_char,.char_space,.char_num,.email,.url,.date' ).iCheck( 'enable' );
			jQuery( '.only_char,.char_space,.char_num,.email,.url,.date' ).attr( 'disabled', false);
		}
	});
	jQuery( ".only_char").on( 'change', function (event)
	{
		if (jQuery( "input#only_char_id").is( ':checked' ) ) {
			jQuery( '.only_number,.char_space,.char_num,.email,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_number,.char_space,.char_num,.email,.url,.date' ).iCheck( 'uncheck' );
			jQuery( '.only_number,.char_space,.char_num,.email,.url,.date' ).attr( 'disabled', true);
		}
		else{
			
			jQuery( '.only_number,.char_space,.char_num,.email,.url,.date' ).iCheck( 'enable' );
			jQuery( '.only_number,.char_space,.char_num,.email,.url,.date' ).attr( 'disabled', false);
		}
	});
	jQuery( ".char_num").on( 'change', function (event) 
	{
		if (jQuery( "input#char_num_id").is( ':checked' ) ) {
			jQuery( '.only_char,.only_number,.char_space,.email,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_char,.only_number,.char_space,.email,.url,.date' ).iCheck( 'uncheck' );
			jQuery( '.only_char,.only_number,.char_space,.email,.url,.date' ).attr( 'disabled', true);
		}
		else{
			jQuery( '.only_char,.only_number,.char_space,.char_num,.email,.url,.date' ).iCheck( 'enable' );
			jQuery( '.only_char,.only_number,.char_space,.char_num,.email,.url,.date' ).attr( 'disabled', false);
		}
	});
	
	jQuery( ".char_space").on( 'change', function (event)
	{
		if (jQuery( "input#char_space_id").is( ':checked' ) ) {
			jQuery( '.only_char,.only_number,.char_num,.email,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_char,.only_number,.char_num,.email,.url,.date' ).iCheck( 'uncheck' );
			jQuery( '.only_char,.only_number,.char_num,.email,.url,.date' ).attr( 'disabled', true);
		}
		else{
			jQuery( '.only_char,.only_number,.char_num,.email,.url,.date' ).iCheck( 'enable' );
			jQuery( '.only_char,.only_number,.char_num,.email,.url,.date' ).attr( 'disabled', false);
		}
	});
	jQuery( ".email").on( 'change', function (event) 
	{
		if (jQuery( "input#email_id").is( ':checked' ) ) {
			jQuery( '.only_char,.only_number,.char_num,.char_space,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.url,.date' ).iCheck( 'uncheck' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.url,.date' ).attr( 'disabled', true);
		}
		else{
			jQuery( '.only_char,.only_number,.char_num,.char_space,.url,.date' ).iCheck( 'enable' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.url,.date' ).attr( 'disabled', false);
		}
	});
	jQuery( ".url").on( 'change', function (event) 
	{
		if (jQuery( "input#url_id").is( ':checked' ) ) {
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.date' ).iCheck( 'disable' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.date' ).iCheck( 'uncheck' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.date' ).attr( 'disabled', true);
		}
		else{
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.date' ).iCheck( 'enable' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.date' ).attr( 'disabled', false);
		}
	});
	jQuery( ".date").on( 'change', function (event) 
	{
		if (jQuery( "input#date0").is( ':checked' ) ) 
		{
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.url,.min,.max' ).iCheck( 'disable' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.url,.min,.max' ).iCheck( 'uncheck' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.url,.min,.max' ).attr( 'disabled', true);
			jQuery.each(jQuery( '.date' ), function (key, value) 
			{
				jQuery( '#date' + key).iCheck( 'disable' );
				jQuery( '#date' + key).attr( 'disabled', true);
			});
			jQuery(this).iCheck( 'enable' );
			jQuery(this).attr( 'disabled', false);
		}
		else if (jQuery( "input#date1").is( ':checked' ) ) 
		{
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.url,.min,.max' ).iCheck( 'disable' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.url,.min,.max' ).iCheck( 'uncheck' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.url,.min,.max' ).attr( 'disabled', true);
			jQuery.each(jQuery( '.date' ), function (key, value) 
			{
				jQuery( '#date' + key).iCheck( 'disable' );
				jQuery( '#date' + key).attr( 'disabled', true);
			});
			jQuery(this).iCheck( 'enable' );
			jQuery(this).attr( 'disabled', false);
		}
		else if (jQuery( "input#date2").is( ':checked' ) ) 
		{
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.url,.min,.max' ).iCheck( 'disable' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.url,.min,.max' ).iCheck( 'uncheck' );
			jQuery( '.only_char,.only_number,.char_num,.char_space,.email,.url,.min,.max' ).attr( 'disabled', true);
			jQuery.each(jQuery( '.date' ), function (key, value) 
			{
				jQuery( '#date' + key).iCheck( 'disable' );
				jQuery( '#date' + key).attr( 'disabled', true);
			});
			jQuery(this).iCheck( 'enable' );
			jQuery(this).attr( 'disabled', false);
		}
		else
		{
			jQuery( '.only_char,.only_number,.char_num,.char_num,.char_space,.email,.url,.min,.max' ).iCheck( 'uncheck' );
			jQuery( '.only_char,.only_number,.char_num,.char_num,.char_space,.email,.url,.min,.max' ).attr( 'disabled', false);
			jQuery.each(jQuery( '.date' ), function (key, value) 
			{
				jQuery( '#date' + key).iCheck( 'enable' );
				jQuery( '#date' + key).attr( 'disabled', false);
			});
		}	
	});
	jQuery( 'body' ).on( 'change', '.dropdown_change', function () 
	{
		var dropdwon_data = jQuery( ".dropdown_change option:selected").val();
	 	 
		if (dropdwon_data == 'text' || dropdwon_data == 'textarea' ) 
		{
			jQuery( '.date' ).iCheck( 'disable' );
			jQuery( '.date' ).attr( 'disabled', true);
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url' ).iCheck( 'enable' );
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url' ).attr( 'disabled', false);
			
			jQuery( '.file_type_and_size' ).fadeOut(1000);
			jQuery( '.radio_cat' ).fadeOut(1000);
			jQuery( '.checkbox_cat' ).fadeOut(1000);
			jQuery( '.sub_cat' ).fadeOut(1000);
		}
		else if (dropdwon_data == 'dropdown' ) 
		{ 
			jQuery( '.radio_cat' ).fadeOut(1000);
			jQuery( '.checkbox_cat' ).fadeOut(1000);
			jQuery( '.sub_cat' ).fadeIn(1000);
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).attr( 'disabled', true);
			
			jQuery( '.file_type_and_size' ).fadeOut(1000);	
			jQuery( '#max_value' ).val( 'max' );
			jQuery( '#max_limit' ).fadeOut(1000);
			jQuery( '#min_value' ).val( 'min' );
			jQuery( '#min_limit' ).fadeOut(1000);
		}
		else if (dropdwon_data == 'checkbox' ) 
		{
			 
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).attr( 'disabled', true);
			
			jQuery( '.file_type_and_size' ).fadeOut(1000);
			jQuery( '.sub_cat' ).fadeOut(1000);
			jQuery( '.radio_cat' ).fadeOut(1000);
			jQuery( '.checkbox_cat' ).fadeIn(1000);
		}
		else if (dropdwon_data == 'radio' ) 
		{
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).attr( 'disabled', true);
			
			jQuery( '.file_type_and_size' ).fadeOut(1000);
			jQuery( '.sub_cat' ).fadeOut(1000);
			jQuery( '.checkbox_cat' ).fadeOut(1000);
			jQuery( '.radio_cat' ).fadeIn(1000);
			jQuery( '#max_value' ).val( 'max' );
			jQuery( '#max_limit' ).fadeOut(1000);
			jQuery( '#min_value' ).val( 'min' );
			jQuery( '#min_limit' ).fadeOut(1000);
		}
		else if (dropdwon_data == 'date' ) 
		{
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url' ).iCheck( 'disable' );
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url' ).attr( 'disabled', true);
			jQuery( '.date' ).iCheck( 'enable' );
			jQuery( '.date' ).attr( 'disabled', false);
			jQuery( '.file_type_and_size' ).fadeOut(1000);
			jQuery( '.radio_cat' ).fadeOut(1000);
			jQuery( '.checkbox_cat' ).fadeOut(1000);
			jQuery( '.sub_cat' ).fadeOut(1000);
			jQuery( '#max_value' ).val( 'max' );
			jQuery( '#max_limit' ).fadeOut(1000);
			jQuery( '#min_value' ).val( 'min' );
			jQuery( '#min_limit' ).fadeOut(1000);
		}
		else if (dropdwon_data == 'file' ) 
		{
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).attr( 'disabled', true);
			
			jQuery( '.file_type_and_size' ).fadeIn(1000);
			jQuery( '.radio_cat' ).fadeOut(1000);
			jQuery( '.checkbox_cat' ).fadeOut(1000);
			jQuery( '.sub_cat' ).fadeOut(1000);
			jQuery( '#max_value' ).val( 'max' );
			jQuery( '.file_types_value' ).val( 'file_types' );
			jQuery( '.file_size_value' ).val( 'file_upload_size' );
			jQuery( '#max_limit' ).fadeOut(1000);
			jQuery( '#min_value' ).val( 'min' );
			jQuery( '#min_limit' ).fadeOut(1000);
		}
		else 
		{
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).iCheck( 'disable' );
			jQuery( '.only_number,.only_char,.char_space,.char_num,.email,.max,.min,.url,.date' ).attr( 'disabled', false);
			
			jQuery( '.file_type_and_size' ).fadeOut(1000);
			jQuery( '.radio_cat' ).fadeOut(1000);
			jQuery( '.checkbox_cat' ).fadeOut(1000);
			jQuery( '.sub_cat' ).fadeOut(1000);
			jQuery( '#max_value' ).val( 'max' );
			jQuery( '#max_limit' ).fadeOut(1000);
			jQuery( '#min_value' ).val( 'min' );
			jQuery( '#min_limit' ).fadeOut(1000);
		}
	});
	jQuery( 'body' ).on( 'change', '#module_name', function () 
	{
		var module_name_data = jQuery( "#module_name option:selected").val();
		if (module_name_data == 'user' ) 
		{
			jQuery( '.role_div' ).fadeIn(1000);			
		}
		else
		{
			jQuery( '.role_div' ).fadeOut(1000);
		}	
	});	
		jQuery( 'body' ).on( 'click', '.add_more_drop', function () 
	{
		var text = jQuery( '.d_label' ).val();
		if(text == '' )
		{
			alert(language_translate2.enter_value_alert);
			return false;
		}
		else
		{
			if(text.length>0){
				jQuery( '.drop_label' ).append( '<div class="badge badge-danger label_data custom-margin"><input type="hidden" value="' + text + '" name="d_label[]"><span>' + text + '</span><a href="#"><i class="fa fa-trash font-medium-2 delete_d_label" aria-hidden="true"></i></a></div> ' );
				jQuery( '.d_label' ).val( '' );
			}
			
		}
	});
	 
	jQuery( 'body' ).on( 'click', '.delete_d_label', function () 
	{
		jQuery(this).parents( '.label_data' ).remove();
	});
	jQuery( 'body' ).on( 'click', '.add_more_checkbox', function () 
	{
		
		var text = jQuery( '.c_label' ).val();
		if(text == '' )
		{
			alert(language_translate2.enter_value_alert);
			return false;
		}
		else
		{
			if(text.length>0){
				jQuery( '.checkbox_label' ).append( '<div class="badge badge-danger label_data label_checkbox custom-margin"  ><input type="hidden" value="' + text + '"  name="c_label[]"><span>' + text + '</span><a href="#"><i class="fa fa-trash font-medium-2 delete_c_label" aria-hidden="true"></i></a></div> ' );
				jQuery( '.c_label' ).val( '' );
			}	
		}
	});
	jQuery( 'body' ).on( 'click', '.delete_c_label', function () 
	{
		jQuery(this).parents( '.label_checkbox' ).remove();
	});
	jQuery( 'body' ).on( 'click', '.add_more_radio', function ()
	{
		var text = jQuery( '.r_label' ).val();
		if(text.length>0)
		{
			jQuery( '.radio_label' ).append( '<div class="badge badge-danger label_data label_radio custom-margin mjschool-custom-css"><input type="hidden" value="' + text + '"  name="r_label[]"><span>' + text + '</span><a href="#" class="ml_5"><i class="fa fa-trash font-medium-2 delete_r_label" aria-hidden="true"></i></a></div>' );
			jQuery( '.r_label' ).val( '' );
		}	
	});
	jQuery( 'body' ).on( 'click', '.delete_r_label', function () 
	{
		jQuery(this).parents( '.label_radio' ).remove();
	});
	jQuery( ".opentext").on( 'change', function (event) 
	{
		if (jQuery(this).prop( "checked") == true) 
		{
			var value_data = jQuery(this).attr( 'value' );
			if (value_data == 'max' ) 
			{
				jQuery( '#max_limit' ).fadeIn(1000);
			}
			else if (value_data == 'min' ) 
			{
				jQuery( '#min_limit' ).fadeIn(1000);
			}
		} 
		else
		{
			var value_data = jQuery(this).attr( 'value' );
			if (value_data == 'max' ) 
			{
				jQuery( '#max_limit' ).fadeOut(1000);
			}
			else if (value_data == 'min' ) 
			{
				jQuery( '#min_limit' ).fadeOut(1000);
			}
		}
	});
	
	jQuery( 'body' ).on( 'keyup', '#max', function () 
	{
		var limit = 'max:' + jQuery(this).val();
		jQuery( '#max_value' ).attr( 'value', limit);
	});
	jQuery( 'body' ).on( 'keyup', '#min', function () 
	{
		var limit = 'min:' + jQuery(this).val();
		jQuery( '#min_value' ).attr( 'value', limit);
	});
	
	jQuery( 'body' ).on( 'keyup', '.file_types_input', function () 
	{
		var limit = 'file_types:' + jQuery(this).val();
		jQuery( '.file_types_value' ).attr( 'value', limit);
	});
	
	jQuery( 'body' ).on( 'keyup', '.file_size_input', function () 
	{
		var limit = 'file_upload_size:' + jQuery(this).val();
		jQuery( '.file_size_value' ).attr( 'value', limit);
	});
});