jQuery(document).ready(function (jQuery) {
	"use strict";
	jQuery( '#upload_user_avatar_button' ).on( 'click', function ( event ) {
		var file_frame;
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media( {
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function () {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get( 'selection' ).first().toJSON();
			file                   = attachment.url
			var get_file_extension = file.substr( (file.lastIndexOf( '.' ) + 1) );
			if (jQuery.inArray( get_file_extension, ['jpg','jpeg','png'] ) == -1) {
				alert( language_translate1.allow_file_alert );
				return false;
			} else {
				jQuery( "#smgt_user_avatar_url" ).val( attachment.url );
				jQuery( '#mjschool-upload-user-avatar-preview img' ).attr( 'src',attachment.url );
				// Do something with attachment.id and/or attachment.url here
			}
		});
		// Finally, open the modal
		file_frame.open();
	});
	jQuery( '#upload_system_logo_button' ).on( 'click', function ( event ) {
		var file_frame;
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media( {
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function () {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get( 'selection' ).first().toJSON();
			file                   = attachment.url
			var get_file_extension = file.substr( (file.lastIndexOf( '.' ) + 1) );
			if (jQuery.inArray( get_file_extension, ['jpg','jpeg','png'] ) == -1) {
				alert( language_translate1.allow_file_alert );
				return false;
			} else {
				jQuery( "#mjschool_system_logo_url" ).val( attachment.url );
				jQuery( '#upload_system_logo_preview img' ).attr( 'src',attachment.url );
				// Do something with attachment.id and/or attachment.url here
			}
		});
		// Finally, open the modal
		file_frame.open();
	});
	jQuery( '#app_upload_image_button' ).on( 'click', function ( event ) {
		var file_frame;
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media( {
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function () {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get( 'selection' ).first().toJSON();
			file                   = attachment.url
			var get_file_extension = file.substr( (file.lastIndexOf( '.' ) + 1) );
			if (jQuery.inArray( get_file_extension, ['jpg','jpeg','png'] ) == -1) {
				alert( language_translate1.allow_file_alert );
				return false;
			} else {
				jQuery( "#smgt_app_logo_image_url" ).val( attachment.url );
				jQuery( '#upload_mjschool_app_logo_preview img' ).attr( 'src',attachment.url );
				// Do something with attachment.id and/or attachment.url here
			}
		});
		// Finally, open the modal
		file_frame.open();
	});
	jQuery( '.upload_user_cover_button' ).on( 'click', function ( event ) {
		var file_frame;
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media( {
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function () {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get( 'selection' ).first().toJSON();
			jQuery( "#mjschool_background_image" ).val( attachment.url );
			jQuery( '#upload_school_cover_preview img' ).attr( 'src',attachment.url );
			// Do something with attachment.id and/or attachment.url here
		});
		// Finally, open the modal
		file_frame.open();
	});
	jQuery( '#upload_principal_signature' ).on( 'click', function ( event ) {
		var file_frame;
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media( {
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function () {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get( 'selection' ).first().toJSON();
			file                   = attachment.url
			var get_file_extension = file.substr( (file.lastIndexOf( '.' ) + 1) );
			if (jQuery.inArray( get_file_extension, ['jpg','jpeg','png'] ) == -1) {
				alert( language_translate1.allow_file_alert );
				return false;
			} else {
				jQuery( "#mjschool_principal_signature" ).val( attachment.url );
				jQuery( '#upload_user_aprincipal_signature img' ).attr( 'src',attachment.url );
				// Do something with attachment.id and/or attachment.url here
			}
		});
		// Finally, open the modal
		file_frame.open();
	});
});