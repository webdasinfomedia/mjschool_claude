jQuery( document ).ready( function (jQuery) {
	"use strict";
	// ----------- Sidebar dropdown in Responsive -----------//
	jQuery( '#sidebarCollapse' ).on( 'click', function () {
		jQuery( '#sidebar' ).toggleClass( 'active' );
		jQuery( this ).toggleClass( 'active' );
	});
	jQuery( '.has-submenu' ).on( 'click', function () {
		jQuery( '.submenu', this ).toggleClass( 'active' );
		jQuery( this ).toggleClass( 'active' );
	});
	// ----------- Sidebar dropdown in Responsive -----------//
	// ------------- Label Add Active class ---------------//
	jQuery( "label" ).addClass( "active" );
	// ------------- Label Add Active class ---------------//
	jQuery( "body" ).on( "click", "#varify_key", function (event) {
		jQuery( ".mjschool_ajax-img" ).show();
		jQuery( ".mjschool-page-inner" ).css( "opacity", "0.5" );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var res_json;
		var licence_key = jQuery( '#licence_key' ).val();
		var enter_email = jQuery( '#enter_email' ).val();
		var curr_data   = {
			action: 'mjschool_verify_pkey',
			licence_key: licence_key,
			enter_email: enter_email,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			res_json = JSON.parse( response );
			jQuery( '#mjschool-message' ).html( res_json.message );
			jQuery( "#mjschool-message" ).css( "display", "block" );
			jQuery( ".mjschool_ajax-img" ).hide();
			jQuery( ".mjschool-page-inner" ).css( "opacity", "1" );
			if (res_json.smgt_verify == '0' ) {
				window.location.href = res_json.location_url;
			}
			return true;
		});
	});
	jQuery( "body" ).on( "click", ".mjschool-download-csv-log", function (event) {
		event.preventDefault();
		const module = jQuery( this ).attr( "id" ); // get 'student', 'teacher', etc.
		let form = jQuery(
			'<form>',
			{
				method: 'POST',
				action: mjschool.ajax, // WordPress AJAX URL
				nonce: mjschool.nonce,
				style: 'display:none;'
			}
		).append(
			jQuery(
				'<input>',
				{
					type: 'hidden',
					name: 'action',
					value: 'mjschool-download-csv-log'
				}
			)
		).append(
			jQuery(
				'<input>',
				{
					type: 'hidden',
					name: 'module',
					value: module // <-- dynamically pass 'student'
				}
			)
		);
		jQuery( 'body' ).append( form );
		form.trigger( 'submit' );
		form.remove();
	});
	jQuery( ".mjschool-section-id-exam" ).on( 'change', function () {
		jQuery( '#mjschool-subject-list' ).html( '' );
		var class_id   = jQuery( "#mjschool-class-list" ).val();
		var section_id = jQuery( "#class_section" ).val();
		var curr_data  = {
			action: 'mjschool_load_subject_class_id_and_section_id',
			class_id: class_id,
			section_id: section_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#mjschool-subject-list' ).append( response );
		});
	});
	jQuery( "body" ).on( "click", "#pdf", function () {
		var student_id = jQuery( "#student_id" ).val();
		var curr_data  = {
			action: 'mjschool_ajax_result_pdf',
			student_id: student_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			return true;
		});
	});
	jQuery( "body" ).on( "click", ".view-notice", function (event) {
		var notice_id = jQuery( this ).attr( 'id' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_view_notice',
			notice_id: notice_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.mjschool-notice-content' ).html( response );
			return true;
		});
	});
	// POP-UP
	// notice_for_ajax (Add Notice show-hide ajax)
	jQuery( "body" ).on( "change", ".notice_for_ajax", function (event) {
		var selection = jQuery( this ).val();
		if (selection == 'parent' || selection == 'supportstaff' || selection == 'all' ) {
			jQuery( '#mjschool-smgt-select-class' ).hide();
			jQuery( '#smgt_select_section' ).hide();
		} else if (selection == 'teacher' || selection == 'all' ) {
			jQuery( '#mjschool-smgt-select-class' ).show();
			jQuery( '#smgt_select_section' ).hide();
		} else {
			jQuery( '#mjschool-smgt-select-class' ).show();
			jQuery( '#smgt_select_section' ).show();
		}
	});
	jQuery( ".notice_for_ajax" ).trigger( "change" );
	jQuery( "body" ).on( "click", ".show-popup", function (event) {
		var student_id = jQuery( this ).attr( 'idtest' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop(); // grab the px value from the top of the page to where you're scrolling
		var curr_data = {
			action: 'mjschool_ajax_result',
			student_id: student_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.result' ).html( response );
		});
	});
	jQuery( "body" ).on( "click", ".show-popup-teacher-details", function (event) {
		var student_id = jQuery( this ).attr( 'student_id' );
		var class_id   = jQuery( this ).attr( 'class_id' );
		var section_id = jQuery( this ).attr( 'section_id' );
		var exam_id    = jQuery( this ).attr( 'exam_id' );
		var type       = jQuery( this ).attr( 'typeformat' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop(); // grab the px value from the top of the page to where you're scrolling
		var curr_data = {
			action: 'mjschool_ajax_teacher_comment',
			student_id: student_id,
			class_id: class_id,
			section_id: section_id,
			nonce: mjschool.nonce,
			exam_id: exam_id,
			type: type,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.mjschool-category-list' ).html( response );
		});
	});
	jQuery( "body" ).on( "click", ".show-popup-teacher-details-marge", function (event) {
		var student_id = jQuery( this ).attr( 'student_id' );
		var class_id   = jQuery( this ).attr( 'class_id' );
		var section_id = jQuery(this).attr( 'section_id' );
		var merge_id   = jQuery( this ).attr( 'merge_id' );
		var type       = jQuery( this ).attr( 'typeformat' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop(); // grab the px value from the top of the page to where you're scrolling
		var curr_data = {
			action: 'mjschool_ajax_teacher_comment_merge',
			student_id: student_id,
			class_id: class_id,
			section_id: section_id,
			merge_id: merge_id,
			nonce: mjschool.nonce,
			type: type,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.mjschool-category-list' ).html( response );
		});
	});
	jQuery( "body" ).on( "click", ".print-result", function () {
		var student_id      = jQuery( "#popup_student_id" ).val();
		var class_id        = jQuery( "#popup_class_id" ).val();
		var section_id      = jQuery( "#popup_section_id" ).val();
		var exam_id         = jQuery( "#popup_exam_id" ).val();
		var teacher_comment = jQuery( "#teacherComment" ).val();
		var teacher_id      = jQuery( "#teacher_id" ).val();
		if (teacher_id === "" || teacher_comment === "") {
			alert( "Please fill all fields before printing." );
			return;
		}
		var url = "?page=mjschool_student&print=print" +
		"&student=" + encodeURIComponent( student_id ) +
		"&class_id=" + encodeURIComponent( class_id ) +
		"&section_id=" + encodeURIComponent( section_id ) +
		"&exam_id=" + encodeURIComponent( exam_id ) +
		"&teacher_id=" + encodeURIComponent( teacher_id ) +
		"&comment=" + encodeURIComponent( teacher_comment );
		window.open( url, '_blank' );
	});
	jQuery( "body" ).on( "click", ".print-result-pdf", function () {
		var student_id      = jQuery( "#popup_student_id" ).val();
		var class_id        = jQuery( "#popup_class_id" ).val();
		var section_id      = jQuery( "#popup_section_id" ).val();
		var exam_id         = jQuery( "#popup_exam_id" ).val();
		var teacher_comment = jQuery( "#teacherComment" ).val();
		var teacher_id      = jQuery( "#teacher_id" ).val();
		if (teacher_id === "" || teacher_comment === "") {
			alert( "Please fill all fields before printing." );
			return;
		}
		var url = "?page=mjschool_student&print=pdf" +
		"&student=" + encodeURIComponent( student_id ) +
		"&class_id=" + encodeURIComponent( class_id ) +
		"&section_id=" + encodeURIComponent( section_id ) +
		"&exam_id=" + encodeURIComponent( exam_id ) +
		"&teacher_id=" + encodeURIComponent( teacher_id ) +
		"&comment=" + encodeURIComponent( teacher_comment );
		window.open( url, '_blank' );
	});
	jQuery( "body" ).on( "click", ".print-result-marge", function () {
		var student_id      = jQuery( "#popup_student_id" ).val();
		var class_id        = jQuery( "#popup_class_id" ).val();
		var section_id      = jQuery( "#popup_section_id" ).val();
		var merge_id        = jQuery( "#popup_exam_id" ).val();
		var teacher_comment = jQuery( "#teacherComment" ).val();
		var teacher_id      = jQuery( "#teacher_id" ).val();
		if (teacher_id === "" || teacher_comment === "") {
			alert( "Please fill all fields before printing." );
			return;
		}
		// You can store teacher comment and teacher ID temporarily via AJAX if needed
		// Then trigger the print by redirecting to the print URL
		var url = "?page=mjschool_student&print=group_result_print" +
		"&student=" + encodeURIComponent( student_id ) +
		"&class_id=" + encodeURIComponent( class_id ) +
		"&section_id=" + encodeURIComponent( section_id ) +
		"&merge_id=" + encodeURIComponent( merge_id ) +
		"&teacher_id=" + encodeURIComponent( teacher_id ) +
		"&comment=" + encodeURIComponent( teacher_comment );
		window.open( url, '_blank' );
	});
	jQuery( "body" ).on( "click", ".print-result-marge-pdf", function () {
		var student_id      = jQuery( "#popup_student_id" ).val();
		var class_id        = jQuery( "#popup_class_id" ).val();
		var section_id      = jQuery( "#popup_section_id" ).val();
		var merge_id        = jQuery( "#popup_exam_id" ).val();
		var teacher_comment = jQuery( "#teacherComment" ).val();
		var teacher_id      = jQuery( "#teacher_id" ).val();
		if (teacher_id === "" || teacher_comment === "") {
			alert( "Please fill all fields before printing." );
			return;
		}
		// You can store teacher comment and teacher ID temporarily via AJAX if needed
		// Then trigger the print by redirecting to the print URL
		var url = "?page=mjschool_student&print=group_result_pdf" +
		"&student=" + encodeURIComponent( student_id ) +
		"&class_id=" + encodeURIComponent( class_id ) +
		"&section_id=" + encodeURIComponent( section_id ) +
		"&merge_id=" + encodeURIComponent( merge_id ) +
		"&teacher_id=" + encodeURIComponent( teacher_id ) +
		"&comment=" + encodeURIComponent( teacher_comment );
		window.open( url, '_blank' );
	});
	jQuery( "body" ).on( "click", ".show-popup-view-result", function (event) {
		var student_id = jQuery( this ).attr( 'idtest' );
		var exam_id    = jQuery( this ).attr( 'exam_id' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop(); // grab the px value from the top of the page to where you're scrolling
		var curr_data = {
			action: 'mjschool_ajax_view_result',
			student_id: student_id,
			exam_id: exam_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.result' ).html( response );
		});
	});
	jQuery( "body" ).on( "click", ".active-user", function (event) {
		var student_id = jQuery( this ).attr( 'idtest' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop(); // grab the px value from the top of the page to where you're scrolling
		var curr_data = {
			action: 'mjschool_active_student',
			student_id: student_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.result' ).html( response );
		});
	});
	jQuery( "body" ).on( "click", ".close-btn", function () {
		jQuery( ".result" ).empty();
		jQuery( ".view-parent" ).empty();
		jQuery( ".mjschool-popup-bg" ).hide();
		jQuery( ".view_popup" ).empty();
		jQuery( ".mjschool-category-list" ).empty();
	});
	jQuery( "#mjschool-class-list" ).on( 'change', function () {
		jQuery( '#mjschool-subject-list' ).html( '' );
		var selection = jQuery( "#mjschool-class-list" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_subject',
			class_list: jQuery( "#mjschool-class-list" ).val(),
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#mjschool-subject-list' ).append( response );
		});
	});
	jQuery( ".exam_list" ).on( 'change', function () {
		jQuery( '#mjschool-university-subject-list' ).html( '' );
		var selection = jQuery( ".exam_list" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_subject_by_exam',
			class_list: jQuery( "#mjschool-class-list" ).val(),
			exam_list: jQuery( ".exam_list" ).val(),
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#mjschool-university-subject-list' ).append( response );
		});
	});
	jQuery( ".mjschool-class-section-subject" ).on( 'change', function () {
		jQuery( '#mjschool-subject-list' ).html( '' );
		var class_id  = jQuery( "#mjschool-class-list" ).val();
		var optionval = jQuery( this ).val();
		var curr_data = {
			action: 'mjschool_load_subject_by_section',
			class_list: class_id,
			section_list: optionval,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#mjschool-subject-list' ).append( response );
		});
	});
	// --------------- TEACHER BY CLASS ----------//
	jQuery( ".class_by_teacher" ).on( 'change', function () {
		var class_list = jQuery( ".class_by_teacher" ).val();
		jQuery( '#subject_teacher' ).html( '' );
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_teacher_by_class',
			class_list: class_list,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( ".teacher_list option[value='remove']" ).remove();
			jQuery( '.teacher_list' ).append( response );
			jQuery( '.teacher_list' ).multiselect( 'rebuild' );
			return false;
		});
	});
	jQuery( ".mjschool-change-subject" ).on( 'change', function () {
		var subject = jQuery( ".mjschool-change-subject" ).val();
		jQuery( '.teacher_list' ).html( '' );
		var curr_data = {
			action: 'mjschool_load_teacher_by_subject',
			subject: subject,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( ".teacher_list option[value='remove']" ).remove();
			jQuery( '.teacher_list' ).append( response );
			jQuery( '#subject_teacher' ).multiselect( 'rebuild' );
			return false;
		});
	});
	// ------------------- GET EXAM LIST BY CLASS ID --------------//
	jQuery( ".class_id_exam" ).on( 'change', function () {
		jQuery( '.exam_list' ).html( '' );
		var class_id  = jQuery( "#mjschool-class-list" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_exam',
			class_id: class_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.exam_list' ).append( response );
		});
	});
	// ------------------- GET EXAM LIST BY SECTION ID --------------//
	jQuery( ".mjschool-section-id-exam" ).on( 'change', function () {
		jQuery( '.exam_list' ).html( '' );
		var class_id   = jQuery( ".class_id_exam" ).val();
		var section_id = jQuery( "#class_section" ).val();
		var curr_data  = {
			action: 'mjschool_load_exam_by_section',
			class_id: class_id,
			section_id: section_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.exam_list' ).append( response );
		});
	});
	/* Notification Module*/
	jQuery( "#mjschool-notification-class-list-id,#mjschool-notification-class-section-id" ).on( 'change', function () {
		var class_list    = jQuery( "#mjschool-notification-class-list-id" ).val();
		var class_section = jQuery( "#mjschool-notification-class-section-id" ).val();
		var clicked_id    = jQuery( this ).attr( 'id' );
		var curr_data = {
			action: 'mjschool_notification_user_list',
			class_list: class_list,
			class_section: class_section,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			var json_obj = jQuery.parseJSON( response );// parse JSON
			if (clicked_id != 'mjschool-notification-class-section-id' ) {
				jQuery( '#mjschool-notification-class-section-id' ).html( '' );
				jQuery( '#mjschool-notification-class-section-id' ).append( json_obj['section'] );
			}
			jQuery( '.mjschool-notification-user-display-block' ).html( '' );
			jQuery( '.mjschool-notification-user-display-block' ).append( json_obj['users'] );
			return false;
		});
	});
	/* Document Module Start*/
	jQuery( "#document_class_list_id" ).on( 'change', function () {
		var class_list    = jQuery( "#document_class_list_id" ).val();
		var class_section = jQuery( "#document_class_section_id" ).val();
		var clicked_id    = jQuery( this ).attr( 'id' );
		var curr_data     = {
			action: 'mjschool_document_user_list',
			class_list: class_list,
			class_section: class_section,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			var json_obj = jQuery.parseJSON( response );// parse JSON
			if (clicked_id != 'document_class_section_id' ) {
				jQuery( '#document_class_section_id' ).html( '' );
				jQuery( '#document_class_section_id' ).append( json_obj['section'] );
			}
			return false;
		});
	});
	jQuery( "#document_class_section_id" ).on( 'change', function () {
		var class_list    = jQuery( "#document_class_list_id" ).val();
		var class_section = jQuery( "#document_class_section_id" ).val();
		var clicked_id    = jQuery( this ).attr( 'id' );
		var curr_data     = {
			action: 'mjschool_document_user_list',
			class_list: class_list,
			class_section: class_section,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			var json_obj = jQuery.parseJSON( response );// parse JSON
			jQuery( '.document_user_display_block' ).html( '' );
			jQuery( '.document_user_display_block' ).append( json_obj['users'] );
			return false;
		});
	});
	/* Document Module - End*/
	/*-----------------LOAD SECTION WISE STUDENT------------------------------------*/
	jQuery( "body" ).on( "change", "#class_section", function (event) {
		var section_id = jQuery( "#class_section" ).val();
		var class_list = jQuery( "#mjschool-class-list" ).val();
		var curr_data = {
			action: 'mjschool_load_section_student',
			section_id: section_id,
			class_list: class_list,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#demo' ).append( response );
		});
	});
	// START select student class wise
	jQuery( "body" ).on( "change", "#mjschool-class-list", function (event) {
		jQuery( '#student_list' ).html( '' );
		var selection = jQuery( this ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_user',
			class_list: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#student_list' ).append( response );
		});
	});
	jQuery( "body" ).on( "change", "#class_ld_change", function (event) {
		jQuery( '#student_list' ).html( '' );
		var selection = jQuery( this ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_user',
			class_list: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#student_list' ).append( response );
		});
	});
	jQuery( "body" ).on( "change", "#class_ld_change_front", function (event) {
		jQuery( '#student_list' ).html( '' );
		var selection = jQuery( this ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_user',
			class_list: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#student_list_front' ).append( response );
		});
	});
	// START select student class wise
	jQuery( "#class_section" ).on( 'change', function () {
		jQuery( '#student_list' ).html( '' );
		var selection = jQuery( this ).val();
		var class_id  = jQuery( "#mjschool-class-list" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_section_user',
			section_id: selection,
			class_id: class_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#student_list' ).append( response );
		});
	});
	// START select student class wise
	jQuery( "body" ).on( "change", "#mjschool-class-list", function () {
		jQuery( '#class_section' ).html( '' );
		jQuery( '#student_status' ).val( 'active' );
		jQuery( '#class_section' ).append( '<option value="remove">Loading..</option>' );
		var selection = jQuery( "#mjschool-class-list" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_section',
			class_id: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( "#class_section option[value='remove']" ).remove();
			jQuery( '#class_section' ).append( response );
		});
		return false;
	});
	jQuery( "body" ).on( "change", "#student_status", function () {
		jQuery( '#student_list' ).html( '' );
		var student_status = jQuery( "#student_status" ).val();
		if (student_status == 'deactive' ) {
			var curr_data = {
				action: 'mjschool_load_student_with_status',
				student_status: student_status,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				jQuery( '#student_list' ).append( response );
			});
		}
	});
	// START select student class wise For Add Student
	jQuery( "body" ).on( "change", "#class_list_add_student", function () {
		jQuery( '#mjschool-class-section-add-student' ).html( '' );
		jQuery( '#mjschool-class-section-add-student' ).append( '<option value="remove">Loading..</option>' );
		var selection = jQuery( "#class_list_add_student" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_section_add_student',
			class_id: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post(mjschool.ajax, curr_data, function (response) {
			
			jQuery( "#mjschool-class-section-add-student option[value='remove']" ).remove();
			jQuery( '#mjschool-class-section-add-student' ).append( response );
		});
		return false;
	});
	jQuery( "body" ).on( "change", "#approve_class_list", function () {
		jQuery( '#approve_class_section' ).html( '' );
		jQuery( '#approve_class_section' ).append( '<option value="remove">Loading..</option>' );
		var selection = jQuery( "#approve_class_list" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_section',
			class_id: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( "#approve_class_section option[value='remove']" ).remove();
			jQuery( '#approve_class_section' ).append( response );
		});
		return false;
	});
	jQuery( "body" ).on( "change", "#class_ld_change", function () {
		jQuery( '#class_section' ).html( '' );
		jQuery( '#class_section' ).append( '<option value="remove">Loading..</option>' );
		var selection = jQuery( "#class_ld_change" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_section',
			class_id: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( "#class_section option[value='remove']" ).remove();
			jQuery( '#class_section' ).append( response );
		});
	});
	jQuery( "body" ).on( "change", "#class_ld_change_front", function () {
		jQuery( '#class_section_front' ).html( '' );
		jQuery( '#class_section_front' ).append( '<option value="remove">Loading..</option>' );
		var selection = jQuery( "#class_ld_change_front" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_section',
			class_id: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( "#class_section_front option[value='remove']" ).remove();
			jQuery( '#class_section_front' ).append( response );
		});
	});
	jQuery( "body" ).on( "change", ".load_fees", function () {
		var selection = jQuery( "#fees_class_list_id" ).val();
		var curr_data = {
			action: 'mjschool_load_class_fee_type',
			nonce: mjschool.nonce,
			class_list: selection
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			// Replace options in the select
			jQuery( '#fees_data' ).html( response );
			// Re-init multiselect
			if (jQuery.fn.multiselect) {
				jQuery( '#fees_data' ).multiselect( 'destroy' ); // Remove old instance
				jQuery( '#fees_data' ).multiselect( {
					nonSelectedText: "Select Fees",
					includeSelectAllOption: true,
					selectAllText: "Select all",
					templates: {
						button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
					}
				});
			} else {
				console.error( "Multiselect plugin not loaded." );
			}
		});
	});
	jQuery( "body" ).on( "change", ".mjschool-load-fee-type-single", function () {
		jQuery( '#fees_data' ).html( '' );
		var selection = jQuery( "#mjschool-class-list" ).val();
		var curr_data = {
			action: 'mjschool_load_class_fee_type',
			class_list: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#fees_data' ).append( response );
		});
	});
	/*---------------FEE TYPE LOAD SECTION WISE--------------------------*/
	jQuery( "#fees_data" ).on( 'change', function () {
		var selection = jQuery( "#fees_data" ).val();
		var curr_data = {
			action: 'mjschool_load_fee_type_amount',
			fees_id: jQuery( "#fees_data" ).val(),
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( "#fees_amount" ).val( response );
		});
	});
	// END USER LOAD FUNCTION
	// select all checkboxes by select one .............
	jQuery( '#selectall' ).on( 'click', function (event) {
		// on click
		if (this.checked) { // check select status
			jQuery( '.mjschool-checkbox1' ).each( function () {
				// loop through each checkbox
				this.checked = true;  // select all checkboxes with class "checkbox1"
			});
		} else {
			jQuery( '.mjschool-checkbox1' ).each( function () {
				// loop through each checkbox
				this.checked = false; // deselect all checkboxes with class "checkbox1"
			});
		}
	});
	// hide popup when user clicks on close button
	jQuery( '.close-btn' ).on( 'click', function () {
		jQuery( ".view-parent" ).empty();
		jQuery( '.mjschool-popup-bg' ).hide(); // hide the overlay
		jQuery( ".view_popup" ).empty();
		jQuery( ".mjschool-category-list" ).empty();
	});
	// hides the popup if user clicks anywhere outside the container
	// END POPUP
	// START POPUP FOR EDIT OPTION OF PERIOD
	// hide popup when user clicks on close button
	jQuery( '.close-btn' ).on( 'click', function () {
		jQuery( ".edit_perent" ).empty();
		jQuery( '.mjschool-popup-bg' ).hide(); // hide the overlay
		jQuery( ".view_popup" ).empty();
		jQuery( ".mjschool-category-list" ).empty();
	});
	// SMS Message
	jQuery( "input[name=select_serveice]:radio" ).on( 'change', function () {
		var curr_data = {
			action: 'mjschool_service_setting',
			select_serveice: jQuery( this ).val(),
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#mjschool_setting_block' ).html( response );
		});
	});
	jQuery( "#chk_sms_sent" ).on( 'change', function () {
		if (jQuery( this ).is( ":checked" ) ) {
			jQuery( '#mjschool-message-sent' ).addClass( 'hms_message_block' );
		} else {
			jQuery( '#mjschool-message-sent' ).addClass( 'mjschool-message-none' );
			jQuery( '#mjschool-message-sent' ).removeClass( 'hms_message_block' );
		}
	});
	jQuery( "body" ).on( "click", ".close-btn-cat", function () {
		jQuery( ".mjschool-category-list" ).empty();
		jQuery( '.mjschool-popup-bg' ).hide(); // hide the overlay
	});
	jQuery( "body" ).on( "click", ".show-invoice-popup", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight    = jQuery( document ).height(); // grab the height of the page
		var scrollTop    = jQuery( window ).scrollTop();
		var idtest       = jQuery( this ).attr( 'idtest' );
		var invoice_type = jQuery( this ).attr( 'invoice_type' );
		var curr_data    = {
			action: 'mjschool_student_invoice_view',
			idtest: idtest,
			invoice_type: invoice_type,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.invoice_data' ).html( response );
			return true;
		});
	});
	jQuery( "body" ).on( "click", ".show-payment-popup", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight  = jQuery( document ).height(); // grab the height of the page
		var scrollTop  = jQuery( window ).scrollTop();
		var idtest     = jQuery( this ).attr( 'idtest' );
		var view_type  = jQuery( this ).attr( 'view_type' );
		var due_amount = jQuery( this ).attr( 'due_amount' );
		var student_id = jQuery( this ).attr( 'student_id' );
		var curr_data  = {
			action: 'mjschool_student_add_payment',
			idtest: idtest,
			view_type: view_type,
			due_amount: due_amount,
			student_id: student_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.invoice_data').html(response);
			jQuery(document).trigger("mjschool_student_add_payment_loaded");
			return true;
		});
	});
	jQuery( "body" ).on( "click", ".show-view-payment-popup", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var idtest    = jQuery( this ).attr( 'idtest' );
		var view_type = jQuery( this ).attr( 'view_type' );
		var curr_data = {
			action: 'mjschool_student_view_payment_history',
			idtest: idtest,
			view_type1: view_type,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.invoice_data').html(response);
			jQuery(document).trigger("mjschool_student_payment_history_loaded");
			return true;
		});
	});
	// --- select student alert msg in leave Module - start  ----//
	jQuery( "body" ).on( "click", ".save_leave_validate", function () {
		var member_name = jQuery( '.display-members option' ).filter( ':selected' ).val();
		if ( ! member_name) {
			alert( language_translate2.select_member_alert );
			return false;
		}
	});
	// --- select student alert msg in leave Module - End  ----//
	jQuery( "body" ).on( "click", "#addremove", function (event) {
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var class_id  = 0;
		var model     = jQuery( this ).attr( 'model' );
		if (model == 'class_sec' ) {
			class_id = jQuery( this ).attr( 'class_id' );
		}
		var curr_data = {
			action: 'mjschool_add_remove_fee_type',
			model: model,
			class_id: class_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.modal-content' ).html( response );
			return true;
		});
	});
	jQuery( "body" ).on( "click", "#btn-add-cat", function () {
		var fee_type = jQuery( "#fees_type_val" ).val();
		var model    = jQuery( this ).attr( 'model' );
		var class_id = 0;
		if (model == 'class_sec' ) {
			class_id = jQuery( this ).attr( 'class_id' );
		}
		var valid = jQuery( '#fees_type_form' ).validationEngine( 'validate' );
		if (valid == true) {
			var curr_data = {
				action: 'mjschool_add_fee_type',
				model: model,
				class_id: class_id,
				fee_type: fee_type,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				var json_obj = jQuery.parseJSON( response );// parse JSON
				if (json_obj[2] == 1) {
					alert( 'This Section is already exist in this Class' );
				} else {
					jQuery( '.class_detail_append .div_new_1' ).append( json_obj[0] );
					jQuery( '#fees_type_val' ).val( "" );
					if (model == 'rack_type' ) {
						jQuery( "#rack_category_data" ).append( json_obj[1] );
					} else {
						jQuery( "#category_data" ).append( json_obj[1] );
					}
				}
				return false;
			});
		}
	});
	jQuery( "body" ).on( "click", ".btn-delete-cat", function () {
		var mjSmgtSecurity = jQuery( "#mjschool_nonce" ).val();
		var cat_id         = jQuery( this ).attr( 'id' );
		var model          = jQuery( this ).attr( 'model' );
		if (confirm( language_translate2.delete_record_alert ) ) {
			var curr_data = {
				action: 'mjschool_remove_fee_type',
				model: model,
				security: mjSmgtSecurity, // Pass nonce for security
				cat_id: cat_id,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				if (response.success) {
					jQuery( '#cat-' + cat_id ).hide();
					if (model == 'rack_type' ) {
						jQuery( "#rack_category_data" ).find( 'option[value=' + cat_id + ']' ).remove();
					} else {
						jQuery( "#category_data" ).find( 'option[value=' + cat_id + ']' ).remove();
					}
					return true;
				} else {
					alert( response.data.message || "Error deleting category." );
				}
			}, 'json' ).fail( function (jqXHR, textStatus, errorThrown) {
				alert( "Something went wrong. Please try again." );
			});
		}
	});
	jQuery( "body" ).on( "click", ".mjschool-btn-edit-cat", function () {
		var cat_id = jQuery( this ).attr( 'id' );
		var model  = jQuery( this ).attr( 'model' );
		var curr_data = {
			action: 'mjschool_edit_section',
			model: model,
			cat_id: cat_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( ".class_detail_append .div_new_1 #cat-" + cat_id ).html( response );
			return true;
		});
	});
	jQuery( "body" ).on( "click", ".mjschool-btn-cat-update", function () {
		if (jQuery.trim( jQuery( '#section_name' ).val() ) == '' ) {
			alert( 'Input can not be left blank' );
			return false;
		}
		var cat_id       = jQuery( this ).attr( 'id' );
		var model        = jQuery( this ).attr( 'model' );
		var section_name = jQuery( "#section_name" ).val();
		if (confirm( language_translate2.edit_record_alert ) ) {
			var curr_data = {
				action: 'mjschool_update_section',
				model: model,
				cat_id: cat_id,
				section_name: section_name,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				jQuery( ".div_new_1 #cat-" + cat_id ).html( response );
				return true;
			});
		}
	});
	jQuery( "body" ).on( "click", ".mjschool-btn-cat-update-cancel", function () {
		var cat_id       = jQuery( this ).attr( 'id' );
		var model        = jQuery( this ).attr( 'model' );
		var section_name = jQuery( "#section_name" ).val();
		var curr_data    = {
			action: 'mjschool_update_cancel_section',
			model: model,
			cat_id: cat_id,
			section_name: section_name,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( ".class_detail_append .div_new_1 #cat-" + cat_id ).html( response );
			return true;
		});
	});
	jQuery( "body" ).on( "click", "#view_member_bookissue_popup", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var idtest    = jQuery( this ).attr( 'idtest' );
		var curr_data = {
			action: 'mjschool_student_view_library_history',
			student_id: idtest,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show();
			jQuery( '.invoice_data' ).html( response );
			return true;
		});
	});
	// ---------Book return popup----------
	jQuery( "body" ).on( "click", "#accept_returns_book_popup", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var idtest    = jQuery( this ).attr( 'idtest' );
		var curr_data = {
			action: 'mjschool_accept_return_book',
			idtest: idtest,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.invoice_data').html(response);
			jQuery(document).trigger("mjschool_issue_book_popup_loaded");
			return true;
		});
	});
	
	// ---------END Book return popup----------
	// get auto book return date
	jQuery( ".issue_period,#issue_date" ).on( 'change', function () {
		var selection = jQuery( ".issue_period" ).val();
		if (selection == '' ) {
			return false;
		}
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_get_book_return_date',
			issue_period: jQuery( ".issue_period" ).val(),
			nonce: mjschool.nonce,
			issue_date: jQuery( "#issue_date" ).val()
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#return_date' ).val( response );
		});
	});
	jQuery( "#subject_teacher" ).on( 'change', function () {
		jQuery( '#subject_class' ).html( '' );
		var teacher_id = jQuery( "#subject_teacher" ).val();
		var optionval  = jQuery( this );
		var curr_data  = {
			action: 'mjschool_class_by_teacher',
			teacher_id: teacher_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#subject_class' ).append( response );
		});
	});
	jQuery( "#teacher_by_class" ).on( 'change', function () {
		jQuery( '#class_teacher' ).html( '' );
		var class_id  = jQuery( "#teacher_by_class" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_teacher_by_class',
			class_id: class_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#class_teacher' ).append( response );
		});
	});
	// Get All class wise student
	jQuery( "#mjschool-class-list" ).on( 'change', function () {
		var selection = jQuery( "#mjschool-class-list" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_student',
			class_list: jQuery( "#mjschool-class-list" ).val(),
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#class_student_list' ).append( response );
		});
	});
	/* Message Module*/
	jQuery( "#mjschool-message-form #class_list_id,#mjschool-message-form #send_to,#mjschool-message-form #class_section_id" ).on( 'change', function () {
		var current_action       = jQuery( this ).attr( 'id' );
		var send_to              = jQuery( "#send_to" ).val();
		var class_list           = jQuery( "#class_list_id" ).val();
		var class_section        = jQuery( "#class_section_id" ).val();
		var class_selection_type = jQuery( ".class_selection_type" ).val();
		jQuery( '.class_selection_type' ).prop( 'selectedIndex', 0 );
		jQuery( ".mjschool-multiple-class-div" ).hide();
		if (current_action == 'send_to' ) {
			class_section = '';
			jQuery( "#class_section_id" ).html( '' );
		}
		if (current_action == 'class_list_id' ) {
			class_section = '';
			jQuery( "#class_section_id" ).html( '' );
		}
		var curr_data = {
			action: 'mjschool_sender_user_list',
			send_to: send_to,
			class_list: class_list,
			class_section: class_section,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		if (send_to == 'supportstaff' || send_to == 'administrator' || send_to == 'parent' ) {
			jQuery( ".class_section_id" ).hide();
			jQuery( '.class_list_id' ).hide();
			jQuery( '.class_selection' ).hide();
			jQuery( ".mjschool-support-staff-user-div" ).show();
		}
		if (send_to == 'teacher' ) {
			jQuery( ".class_list_id" ).show();
			jQuery( '.class_section_id' ).hide();
			jQuery( '.class_selection' ).show();
			jQuery( ".mjschool-single-class-div" ).show();
		}
		if (send_to == 'student' ) {
			jQuery( ".class_list_id" ).show();
			jQuery( '.class_section_id' ).show();
			jQuery( '.class_selection' ).show();
			jQuery( ".mjschool-single-class-div" ).show();
		}
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			var json_obj = jQuery.parseJSON( response );// parse JSON
			if ((send_to == 'student' || send_to == 'parent' ) && (current_action == 'send_to' || current_action == 'class_list_id' ) ) {
				jQuery( '#class_section_id' ).html( '' );
				jQuery( '#class_section_id' ).append( json_obj['section'] );
			}
			jQuery( '.user_display_block' ).html( '' );
			jQuery( '.user_display_block' ).append( json_obj['users'] );
			jQuery( '#selected_users' ).multiselect( {
				nonSelectedText: language_translate2.select_user_label,
				includeSelectAllOption: true,
				selectAllText: language_translate2.select_all_label,
				templates: {
					button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
				},
				buttonContainer: '<div class="dropdown" />'
			});
			return false;
		});
	});
	jQuery( ".class_selection_type" ).on( 'change', function () {
		var class_selection_type = jQuery( this ).val();
		var send_to              = jQuery( "#send_to" ).val();
		if (class_selection_type == 'multiple' ) {
			jQuery( ".mjschool-multiple-class-div" ).show();
			jQuery( '.mjschool-single-class-div' ).hide();
			jQuery( '.class_section_id' ).hide();
		} else {
			jQuery( ".mjschool-single-class-div" ).show();
			if (send_to == 'teacher' ) {
				jQuery( ".class_section_id" ).hide();
			} else {
				jQuery( ".class_section_id" ).show();
			}
			jQuery( '.mjschool-multiple-class-div' ).hide();
		}
	});
	/* Document Module*/
	jQuery( ".document_for" ).on( 'change', function () {
		var document_for = jQuery( ".document_for" ).val();
		if (document_for != 'student' ) {
			jQuery( ".class_document_div" ).hide();
			jQuery( ".mjschool-class-section-document-div" ).hide();
			jQuery( '.student_list' ).html( '' );
			jQuery( '.student_list' ).append( '<option value="remove">Loading..</option>' );
			var curr_data = {
				action: 'mjschool_load_other_user_homework',
				document_for: document_for,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				jQuery( ".student_list option[value='remove']" ).remove();
				jQuery( '.student_list' ).append( response );
				return false;
			});
		} else {
			jQuery( '.student_list' ).html( '' );
			jQuery( '.student_list' ).append( '<option value="all student">All Student</option>' );
			jQuery( ".class_document_div" ).show();
			jQuery( '.mjschool-class-section-document-div' ).show();
		}
	});
	jQuery( "body" ).on( "click", "#profile_change", function () {
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_change_profile_photo',
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.profile_picture' ).html( response );
		});
	});
	/* ===================  Frant Message Module  =====================  */
	jQuery( ".mjschool-class-in-student" ).on( 'change', function () {
		var class_id = jQuery( ".mjschool-class-in-student" ).val();
		if (class_id != '' ) {
			var curr_data = {
				action: 'mjschool_count_student_in_class',
				class_id: class_id,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				var json_obj = jQuery.parseJSON( response );// parse JSON
				if (json_obj[0] == 'class_full' ) {
					alert( language_translate2.class_limit_alert );
					window.location.reload( true );
				}
				return false;
			});
		}
	});
	// Event And task display model
	jQuery( "body" ).on( "click", ".mjschool-show-task-event", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var id        = jQuery( this ).attr( 'id' );
		var model     = jQuery( this ).attr( 'model' );
		var curr_data = {
			action: 'mjschool_show_event_task',
			id: id,
			model: model,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.mjschool-task-event-list' ).html( response );
			return true;
		});
	});
	jQuery( "body" ).on( "click", ".mjschool-event-close-btn", function () {
		jQuery( '.mjschool-popup-bg' ).hide(); // hide the overlay
	});
	// ------------------- ADDREMOVE CATEGORY -----------------//
	jQuery( "body" ).on( "click", "#mjschool-addremove-cat", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var model     = jQuery( this ).attr( 'model' );
		var curr_data = {
			action: 'mjschool_add_or_remove_category_callback',
			model: model,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show();
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_category_popup_loaded");
			return true;
		});
	});
	// --------------- ADD CATEGORY NAME -------------------//
	jQuery( "body" ).on( "click", "#btn_add_cat_new_test", function () {
		var category_name = jQuery( '#category_name' ).val();
		var model         = jQuery( this ).attr( 'model' );
		var valid = jQuery( '#category_form_test' ).validationEngine( 'validate' );
		if (valid == true) {
			var curr_data = {
				action: 'mjschool_add_category_new',
				model: model,
				category_name: category_name,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				var json_obj = jQuery.parseJSON( response );// parse JSON
				jQuery( '.mjschool-category-listbox_new .div_new' ).append( json_obj[0] );
				jQuery( '#category_name' ).val( "" );
				jQuery( '.' + model ).append( json_obj[1] );
				return false;
			});
		}
	});
	jQuery( "body" ).on( "click", ".btn-delete-cat_new", function () {
		var mjSmgtSecurity = jQuery( "#mjschool_nonce" ).val();
		var cat_id         = jQuery( this ).attr( 'id' );
		var model          = jQuery( this ).attr( 'model' );
		if (confirm( language_translate2.delete_record_alert ) ) {
			var curr_data = {
				action: 'mjschool_remove_category_new',
				model: model,
				security: mjSmgtSecurity, // Pass nonce for security
				nonce: mjschool.nonce,
				cat_id: cat_id
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				if (response.success) {
					jQuery( '#cat_new-' + cat_id ).hide();
					jQuery( '.' + model ).find( 'option[value=' + cat_id + ']' ).remove();
				} else {
					alert( response.data.message || "Error deleting category." );
				}
			}, 'json' ).fail( function (jqXHR, textStatus, errorThrown) {
				alert( "Something went wrong. Please try again." );
			});
		}
	});
	jQuery( "body" ).on( "click", ".show-admission-popup", function (event) {
		var student_id = jQuery( this ).attr( 'student_id' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop(); // grab the px value from the top of the page to where you're scrolling
		var curr_data = {
			action: 'mjschool_admissoin_approved',
			student_id: student_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.mjschool-category-list' ).hide();
			jQuery( '.result' ).html( response );
		});
	});
	jQuery( "#class_id_homework" ).on( 'change', function () {
		jQuery( '#student_list' ).html( '' );
		jQuery( '#mjschool-subject-list' ).html( '' );
		jQuery( '#section_id_homework' ).html( '' );
		var selection = jQuery( "#class_id" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_students_homework',
			class_list: jQuery( "#class_id_homework" ).val(),
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			var json_obj = jQuery.parseJSON( response );// parse JSON
			jQuery( '#section_id_homework' ).append( json_obj[1] );
			jQuery( '#mjschool-subject-list' ).append( json_obj[2] );
			jQuery( '#student_list' ).append( json_obj[0] );
			jQuery( '#student_list' ).multiselect( {
				templates: {
					button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
				}
			});
			jQuery( '#student_list' ).multiselect( { enableClickableOptGroups: true, includeSelectAllOption: true, disableIfEmpty: true } );
		});
	});
	jQuery( "#section_id_homework" ).on( 'change', function () {
		jQuery( '#student_list' ).html( '' );
		jQuery( '#mjschool-subject-list' ).html( '' );
		var selection = jQuery( "#class_id" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_sections_students_homework',
			section_id: jQuery( "#section_id_homework" ).val(),
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			var json_obj = jQuery.parseJSON( response );
			jQuery( '#student_list' ).append( json_obj[0] );
			jQuery( '#mjschool-subject-list' ).append( json_obj[1] );
			jQuery( '#student_list' ).multiselect( {
				templates: {
					button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
				}
			});
			jQuery( '#student_list' ).multiselect( { enableClickableOptGroups: true, includeSelectAllOption: true, disableIfEmpty: true } );
		});
	});
	// ------------- LOAD EXAM HALL RECEIPT --------------//
	jQuery( "body" ).on( "click", ".search_exam", function (event) {
		jQuery( '.mjschool-exam-hall-receipt-div' ).html( '' );
		var exam_id   = jQuery( "#exam_id" ).val();
		var curr_data = {
			action: 'mjschool_load_exam_hall_receipt_div',
			nonce: mjschool.nonce,
			exam_id: exam_id,
			dataType: 'json'
		};
		var valid     = true;
		if (valid == true) {
			jQuery.post(mjschool.ajax, curr_data, function (response) {
				var json_obj = jQuery.parseJSON( response );
				jQuery( '.exam_hall_receipt_main_div' ).html( '' );
				jQuery( '.mjschool-exam-hall-receipt-div' ).append( json_obj[0] );
			});
		}
	});
	// --------------- INSERT RECEIPT --------//
	jQuery( "body" ).on( "click", ".mjschool-assign-exam-hall", function () {
		var exam_hall = jQuery( "#exam_hall" ).val();
		if (jQuery( '#exam_hall' ).val() != '' ) {
			if (jQuery( ".my_check" ).is( ":checked" ) ) {
				var id_array           = jQuery( '.my_check:checked' ).map( function () { return this.attributes.dataid.textContent; } ).get();
				var array_leangth      = id_array.length;
				var exam_hall_capacity = jQuery( "#exam_hall_capacity_" + exam_hall ).attr( "hall_capacity" );
				var rowCount           = jQuery( '#approve_table tbody tr' ).length;
				var total_student      = array_leangth + rowCount;
				if (total_student > exam_hall_capacity) {
					var remaining = exam_hall_capacity - rowCount;
					var alert_1   = language_translate2.exam_hallCapacity_1;
					var alert_2   = language_translate2.exam_hallCapacity_2;
					var alert_3   = language_translate2.exam_hallCapacity_3;
					alert( "" + alert_1 + " " + remaining + " " + alert_2 + " " + exam_hall_capacity + " " + alert_3 + "" );
				} else {
					var exam_id   = jQuery( "#exam_id" ).val();
					var curr_data = {
						action: 'mjschool_add_receipt_record',
						exam_hall: exam_hall,
						exam_id: exam_id,
						id_array: id_array,
						nonce: mjschool.nonce,
						dataType: 'json'
					};
					jQuery.post( mjschool.ajax, curr_data, function (response) {
						var json_obj = jQuery.parseJSON( response );
						jQuery( '#approve_table' ).append( json_obj[0] );
						jQuery( ".no_data_td_remove1" ).hide();
						jQuery.each( id_array, function (key, value) {
							jQuery( '#not_approve_table tr#' + value ).remove();
						});
					});
				}
			} else {
				alert( language_translate2.one_record_alert );
				return false;
			}
			// ---------------Exam Hall Receipt->Not Assign Exam Hall->Select All In Uncheck --------//
			if (jQuery( ".my_all_check" ).is( ":checked" ) ) {
				jQuery( ".my_all_check" ).prop( "checked", false );
			}
		} else {
			alert( language_translate2.select_hall_alert );
			return false;
		}
	});
	// --------------- DELETE RECEIPT --------//
	jQuery( "body" ).on( "click", ".delete_receipt_record", function () {
		var record_id = jQuery( this ).attr( 'id' );
		var exam_id   = jQuery( "#exam_id" ).val();
		if (confirm( language_translate2.delete_record_alert ) == true) {
			var curr_data = {
				action: 'mjschool_delete_receipt_record',
				record_id: record_id,
				exam_id: exam_id,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				var json_obj = jQuery.parseJSON( response );
				jQuery( '#not_approve_table' ).append( json_obj[0] );
				jQuery( '#approve_table tr#' + record_id ).remove();
				jQuery( ".no_data_td_remove" ).hide();
			});
		}
	});
	// ----------------- VIEW PAGE POPUP ----------------//
	jQuery( "body" ).on( "click", ".mjschool-view-details-popup", function (event) {
		var record_id = jQuery( this ).attr( 'id' );
		var type      = jQuery( this ).attr( 'type' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_view_details_popup',
			record_id: record_id,
			type: type,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.view_popup' ).html( response );
			jQuery( '.mjschool-category-list' ).hide();
			return true;
		});
	});
	// ------------------ EDIT POPUP CATEGORY --------------//
	jQuery( "body" ).on( "click", ".mjschool-btn-edit-cat_popup", function () {
		var cat_id    = jQuery( this ).attr( 'id' );
		var model     = jQuery( this ).attr( 'model' );
		var curr_data = {
			action: 'mjschool_edit_popup_value',
			model: model,
			cat_id: cat_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( ".div_new #cat_new-" + cat_id ).html( response );
			return true;
		});
	});
	jQuery( "body" ).on( 'click', ".delete_letter", function (e) {
		e.preventDefault();
		var accId       = jQuery( this ).attr( 'acc' );
		var employee_id = jQuery( this ).attr( 'id' );
		var tab         = jQuery( this ).attr( 'tab' );
		if (confirm( 'Are you sure you want to delete this record?' ) ) {
			var curr_data = {
				action: 'mjschool_delete_letter',
				id: accId,
				tab: tab,
				employee_id: employee_id,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				if (response.success) {
					window.location.href = response.data.redirect_url;
				} else {
					alert( response.data || 'An error occurred.' );
				}
			})
		}
	});
	// ------------ IF CANCEL EDIT POPUP ----------//
	jQuery( "body" ).on( "click", ".mjschool-btn-cat-update-cancel_popup", function () {
		var cat_id        = jQuery( this ).attr( 'id' );
		var model         = jQuery( this ).attr( 'model' );
		var category_name = jQuery( "#category_name" ).val();
		var curr_data     = {
			action: 'mjschool_update_cancel_popup',
			model: model,
			cat_id: cat_id,
			category_name: category_name,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-category-listbox_new .div_new' ).html( response );
			return false;
		});
	});
	// ------------ UPDATE VALUE POPUP CATEGORY -----------------//
	jQuery( "body" ).on( "click", ".mjschool-btn-cat-update_popup", function () {
		if (jQuery.trim( jQuery( '#category_name_edit' ).val() ) == '' ) {
			alert( 'Input can not be left blank' );
			return false;
		}
		var cat_id        = jQuery( this ).attr( 'id' );
		var model         = jQuery( this ).attr( 'model' );
		var category_name = jQuery( "#category_name_edit" ).val();
		if (confirm( language_translate2.edit_record_alert ) ) {
			var curr_data = {
				action: 'mjschool_update_cetogory_popup_value',
				model: model,
				cat_id: cat_id,
				category_name: category_name,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				var json_obj = jQuery.parseJSON( response );// parse JSON
				jQuery( ".div_new #cat_new-" + cat_id ).html( json_obj[0] );
				jQuery( '.' + model + ' option[value=' + cat_id + ']' ).text( "" );
				jQuery( '.' + model ).find( 'option[value=' + cat_id + ']' ).append( json_obj[1] );
				return true;
			});
		}
	});
	jQuery( "body" ).on( "click", ".mjschool-show-virtual-popup", function (event) {
		var route_id = jQuery( this ).attr( 'id' );
		jQuery( '.create_meeting_popup' ).html( '' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop(); // grab the px value from the top of the page to where you're scrolling
		var curr_data = {
			action: 'mjschool_ajax_create_meeting',
			route_id: route_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_ajax_meeting_loaded");
		});
	});
	jQuery( "body" ).on( "click", ".show-popup", function (event) {
		var route_id = jQuery( this ).attr( 'id' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop(); // grab the px value from the top of the page to where you're scrolling
		var curr_data = {
			action: 'mjschool_ajax_create_meeting',
			route_id: route_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.create_meeting_popup').html(response);
			jQuery(document).trigger("mjschool_ajax_meeting_loaded");
		});
	});
	jQuery( "body" ).on( "click", ".show-popup", function (event) {
		var meeting_id = jQuery( this ).attr( 'meeting_id' );
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop(); // grab the px value from the top of the page to where you're scrolling
		var curr_data = {
			action: 'mjschool_ajax_view_meeting_detail',
			meeting_id: meeting_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.view_meeting_detail_popup').html(response);
			jQuery(document).trigger("mjschool_ajax_meeting_detail_loaded");
		});
	});
	jQuery( "body" ).on(
		"click",
		".importdata",
		function () {
			var docHeight = jQuery( document ).height(); // grab the height of the page
			var scrollTop = jQuery( window ).scrollTop();
			var curr_data = {
				action: 'mjschool_import_data',
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post(
				mjschool.ajax,
				curr_data,
				function (response) {
					jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
					jQuery('.mjschool-category-list').html(response);
					jQuery(document).trigger("mjschool_import_data_loaded");
				}
			);
		}
	);
	// ---------- FOR TOOLTIP INFORMATION ----------//
	jQuery( '[data-toggle="tooltip"]' ).tooltip( {
		"html": true,
		"delay": { "show": 20, "hide": 0 },
	});
	// ----------------- Export Student CSV Pop-up ----------------//
	jQuery( "body" ).on( "click", ".view_csv_popup", function () {
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_export_data',
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.mjschool-category-list' ).show();
			jQuery( '.student_list' ).hide();
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_export_data_loaded");
		});
	});
	// ----------------- Import Student CSV Pop-up ----------------//
	jQuery( "body" ).on( "click", ".view_import_student_csv_popup", function () {
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_student_import_data',
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.student_list' ).show();
			jQuery( '.mjschool-category-list' ).hide();
			jQuery('.student_list').html(response);
			jQuery(document).trigger("mjschool_import_student_data_loaded");
		});
	});
	jQuery( "body" ).on( "click", ".mjschool-view-video-popup", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var link = jQuery( this ).attr( 'link' );
		var title = jQuery( this ).attr( 'title' );
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_view_video',
			link: link,
			title: title,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery( '.mjschool-category-list' ).show();
			jQuery( '.mjschool-category-list' ).html( response );
			jQuery( '.mjschool-task-event-list' ).hide();
			jQuery( '.student_list' ).hide();
			jQuery( 'mjschool-overlay-content' ).removeClass( 'mjschool-content-width' );
			return true;
		});
	});
	jQuery( "body" ).on( "click", "#close-popup", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		jQuery( '.mjschool-category-list' ).html( '' );
	});
	// ----------------- Import Teacher CSV Pop-up ----------------//
	jQuery( "body" ).on( "click", ".view_import_teacher_csv_popup", function () {
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_teacher_import_data',
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_import_teacher_data_loaded");
		});
	});
	// ----------------- Import Support Staff CSV Pop-up ----------------//
	jQuery( "body" ).on( "click", ".view_import_support_staff_csv_popup", function () {
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_support_staff_import_data',
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_import_supportstaff_data_loaded");
		});
	});
	// ----------------- Import Parent CSV Pop-up ----------------//
	jQuery( "body" ).on( "click", ".mjschool-view-import-parent-csv-popup", function () {
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_parent_import_data',
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_import_parent_data_loaded");
		});
	});
	// ----------------- Import Subject CSV Pop-up ----------------//
	jQuery( "body" ).on( "click", ".view_import_subject_csv_popup", function () {
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_subject_import_data',
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_import_subject_data_loaded");
		});
	});
	// ----------------- Import Attendance CSV Pop-up ----------------//
	jQuery( "body" ).on( "click", ".import_attendance_popup", function () {
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_import_student_attendance',
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_import_student_attendance_loaded");
		});
	});
	// ------------------ GENERAL SETTING VIRUAL CLASS ON CHANGE EVENT -----------------------//
	jQuery( '#virual_class_checkbox' ).on( 'change', function () {
		jQuery( document ).ready( function (jQuery) {
			jQuery( '#setting_form' ).validationEngine( {
				promptPosition: "bottomLeft",
				maxErrorsPerField: 1
			});
		});
		if (jQuery( '#virual_class_checkbox' ).is( ':checked' ) == true) {
			jQuery( '.virtual_classroom_input' ).addClass( 'validate[required]' );
			jQuery( '#virtual_class_div' ).addClass( 'mjschool-virual-class-div-block' );
			jQuery( '#virtual_class_div' ).removeClass( 'mjschool-virual-class-div-none' );
		} else {
			jQuery( '.virtual_classroom_input' ).removeClass( 'validate[required]' );
			jQuery( '#virtual_class_div' ).removeClass( 'mjschool-virual-class-div-block' );
			jQuery( '#virtual_class_div' ).addClass( 'mjschool-virual-class-div-none' );
		}
	});
	jQuery( '.mjschool-student-email-id' ).on( 'change', function () {
		var email_id  = jQuery( this ).val();
		var curr_data = {
			action: 'mjschool_check_email_exit_or_not',
			nonce: mjschool.nonce,
			email_id: email_id,
			dataType: 'json',
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			if (response == 1) {
				jQuery( ".mjschool-email-validation-div" ).css( { "display": "block" } );
			} else {
				jQuery( ".mjschool-email-validation-div" ).css( { "display": "none" } );
			}
			return true;
		});
	});
	jQuery( '.addmission_email_id' ).on( 'change', function () {
		var email_id  = jQuery( this ).val();
		var type      = jQuery( this ).attr( 'email_tpye' );
		var curr_data = {
			action: 'mjschool_check_email_exit_or_not',
			email_id: email_id,
			nonce: mjschool.nonce,
			dataType: 'json',
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			if (response == 1) {
				if (type == 'student_email' ) {
					jQuery( ".mjschool-email-validation-div-student-email" ).css( { "display": "block" } );
				}
				if (type == 'father_email' ) {
					jQuery( ".mjschool-email-validation-div-father-email" ).css( { "display": "block" } );
				}
				if (type == 'mother_email' ) {
					jQuery( ".mjschool-email-validation-div-mother-email" ).css( { "display": "block" } );
				}
			} else {
				jQuery( ".mjschool-email-validation-div-student-email" ).css( { "display": "none" } );
				jQuery( ".mjschool-email-validation-div-father-email" ).css( { "display": "none" } );
				jQuery( ".mjschool-email-validation-div-mother-email" ).css( { "display": "none" } );
			}
			return true;
		});
	});
	jQuery( '.student_username' ).on( 'change', function () {
		var username = jQuery( this ).val();
		var curr_data = {
			action: 'mjschool_check_username_exit_or_not',
			username: username,
			nonce: mjschool.nonce,
			dataType: 'json',
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			if (response == 1) {
				jQuery( ".mjschool-username-validation-div" ).css( { "display": "block" } );
			} else {
				jQuery( ".mjschool-username-validation-div" ).css( { "display": "none" } );
			}
			return true;
		});
	});
	jQuery( 'body' ).on( 'click', function (e) {
		if (e.target.className == "mjschool-popup-bg") {
			jQuery( ".mjschool-popup-bg" ).hide();
		}
	});
	jQuery( "body" ).on( "click", ".assign_route_popup", function () {
		var record_id = jQuery( this ).attr( 'id' );
		var docHeight = jQuery( document ).height(); // grab the height of the page
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_assign_route',
			record_id: record_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.assign_route').html(response);
			jQuery(document).trigger("mjschool_assign_route_loaded");
		});
	});
	jQuery( "body" ).on( "change", ".date_type", function () {
		
		const date_type = jQuery( this ).val();
		if (date_type === "period") {
			jQuery( "#date_type_div" ).removeClass( "date_type_div_none" ).show();
			var curr_data = {
				action: 'mjschool_admission_repot_load_date',
				date_type: date_type,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				jQuery( '#date_type_div' ).html( response );
				jQuery(document).trigger("mjschool_admission_date_loaded");
			});
		} else {
			jQuery("#date_type_div").addClass("date_type_div_none").html('').hide();
		}
	});
	/*------------ Approve Leave Botton ----------------*/
	// ------------------ Leave - load leave-date -----------------------//
	jQuery(document).on( "change", ".duration", function (event) {
		if (jQuery(this).is( ':checked' ) ) {
			let duration = jQuery(this).val();
			let idset = jQuery(this).attr( 'idset' );
			let curr_data = {
				action: 'mjschool_load_multiple_day',
				duration: duration,
				idset: idset,
				nonce: mjschool.nonce,
				dataType: 'json'
			};
			jQuery.post( mjschool.ajax, curr_data, function (response) {
				jQuery('#leave_date').html(response);
				jQuery(document).trigger("mjschool_multiple_day_loaded");
			});
		}
	});
	jQuery( ".duration").trigger( "change");
	/*------------ Approve Leave Botton. ----------------*/
	jQuery( "body" ).on( "click", ".leave-approve", function (event) {
		var leave_id = jQuery( this ).attr( 'leave_id' );
		event.preventDefault();
		var docHeight = jQuery( document ).height();
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_leave_approve',
			leave_id: leave_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_approve_leave_load");
			return true;
		});
	});
	/*------------ Reject Leave Botton. ----------------*/
	jQuery( "body" ).on( "click", ".leave-reject", function (event) {
		var leave_id = jQuery( this ).attr( 'leave_id' );
		event.preventDefault();
		var docHeight = jQuery( document ).height();
		var scrollTop = jQuery( window ).scrollTop();
		var curr_data = {
			action: 'mjschool_leave_reject',
			leave_id: leave_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_approve_leave_load");
			return true;
		});
	});
	if (jQuery( ".mjschool_admission_fees" ).is( ':checked' ) ) {
		jQuery( ".mjschool_admission_amount" ).css( "display", "block" );
	}
	jQuery( "body" ).on( "change", ".mjschool_admission_fees", function (event) {
		if (jQuery( this ).is( ':checked' ) ) {
			jQuery( ".mjschool_admission_amount" ).css( "display", "block" );
		} else {
			jQuery( ".mjschool_admission_amount" ).css( "display", "none" );
		}
	});
	if (jQuery( ".mjschool_return_option" ).is( ':checked' ) ) {
		jQuery( ".mjschool_return_period_field" ).css( "display", "block" );
	} else {
		jQuery( ".mjschool_return_period_field" ).css( "display", "none" );
	}
	jQuery( "body" ).on( "change", ".mjschool_return_option", function (event) {
		if (jQuery( this ).is( ':checked' ) ) {
			jQuery( ".mjschool_return_period_field" ).css( "display", "block" );
		} else {
			jQuery( ".mjschool_return_period_field" ).css( "display", "none" );
		}
	});
	if (jQuery( ".mjschool_registration_fees" ).is( ':checked' ) ) {
		jQuery( ".mjschool_registration_amount" ).css( "display", "block" );
	}
	jQuery( "body" ).on( "change", ".mjschool_registration_fees", function (event) {
		if (jQuery( this ).is( ':checked' ) ) {
			jQuery( ".mjschool_registration_amount" ).css( "display", "block" );
		} else {
			jQuery( ".mjschool_registration_amount" ).css( "display", "none" );
		}
	});
	jQuery( "body" ).on( "change", ".dashboard_report_value", function (event) {
		var filter_val = jQuery( this ).val();
		jQuery( '#report_append_id' ).html( "" );
		var curr_data = {
			action: 'mjschool_dashboard_append_report_data',
			filter_val: filter_val,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#report_append_id' ).html( response );
			return true;
		});
	});
	jQuery( "body" ).on( "change", ".student_graph", function (event) {
		var filter_val = jQuery( this ).val();
		jQuery( '#student_graph_id' ).html( "" );
		var curr_data = {
			action: 'mjschool_student_attendance_graph_report_data',
			filter_val: filter_val,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#student_graph_id' ).html( response );
			return true;
		});
	});
	jQuery( "body" ).on( "change", ".teacher_graph", function (event) {
		var filter_val = jQuery( this ).val();
		jQuery( '#teacher_graph_id' ).html( "" );
		var curr_data = {
			action: 'mjschool_teacher_attendance_graph_report_data',
			filter_val: filter_val,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#teacher_graph_id' ).html( response );
			return true;
		});
	});
	jQuery( "#mjschool-attendance-class-list-id" ).on( 'change', function () {
		var class_list = jQuery( "#mjschool-attendance-class-list-id" ).val();
		var clicked_id = jQuery( this ).attr( 'id' );
		var curr_data  = {
			action: 'mjschool_attendance_user_list',
			class_list: class_list,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			var json_obj = jQuery.parseJSON( response );// parse JSON
			if (clicked_id != 'attendance_selected_users' ) {
				jQuery( '#attendance_selected_users' ).html( '' );
				jQuery( '#attendance_selected_users' ).append( json_obj['users'] );
			}
			return false;
		});
	});
	// START select student class wise
	jQuery( "body" ).on( "change", ".mjschool-class-list-document", function () {
		jQuery( '.mjschool-class-section-document' ).html( '' );
		jQuery( '.mjschool-class-section-document' ).append( '<option value="remove">Loading..</option>' );
		var selection = jQuery( ".mjschool-class-list-document" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_section_document',
			class_id: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( ".mjschool-class-section-document option[value='remove']" ).remove();
			jQuery( '.mjschool-class-section-document' ).append( response );
		});
	});
	// START select student class wise
	jQuery( "body" ).on( "change", ".mjschool-class-list-document", function () {
		jQuery( '.student_list' ).html( '' );
		jQuery( '.student_list' ).append( '<option value="remove">Loading..</option>' );
		var selection = jQuery( ".mjschool-class-list-document" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_wise_student_document',
			class_id: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( ".student_list option[value='remove']" ).remove();
			jQuery( '.student_list' ).append( response );
		});
		jQuery( '.add-search-single-select-js' ).select2( {
		});
	});
	// START select student class wise
	jQuery( ".mjschool-class-section-document" ).on( 'change', function () {
		jQuery( '.student_list' ).html( '' );
		var selection = jQuery( this ).val();
		var class_id  = jQuery( ".mjschool-class-list-document" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_section_user_list',
			section_id: selection,
			class_id: class_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.student_list' ).append( response );
		});
	});
	jQuery( "#add_custom_field" ).on( 'click', function () {
		var min     = jQuery( '.min_value' ).val();
		var min_val = jQuery( "#min_value" ).val( "min:" + min );
		var max     = jQuery( '.max_value' ).val();
		var min_val = jQuery( "#max_value" ).val( "max:" + max );
		if (max < min && max != '' && min != '' ) {
			alert( 'Minimum value cannot be more than the maximum value.' );
			return false;
		}
	});
	jQuery( ".date_label" ).addClass( "active" );
	jQuery( "body" ).on( "change", ".date_picker", function () {
		jQuery( ".date_label" ).addClass( "active" );
	});
	// IMPORT ROUTINE CSV FILE
	jQuery( "body" ).on( "click", ".mjschool-routine-import-csv", function () {
		jQuery( ".accordion-button" ).addClass( "collapsed" );
		jQuery( ".accordion-collapse" ).addClass( "collapse" );
		var docHeight  = jQuery( document ).height(); // grab the height of the page
		var scrollTop  = jQuery( window ).scrollTop();
		var class_id   = jQuery( this ).attr( 'class_id' );
		var section_id = jQuery( this ).attr( 'section_id' );
		var curr_data  = {
			action: 'mjschool_class_rootine_import',
			class_id: class_id,
			class_section: section_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '.mjschool-popup-bg' ).show().css( { 'height': docHeight } );
			jQuery('.mjschool-category-list').html(response);
			jQuery(document).trigger("mjschool_routine_import_load");
		});
	});
	// PARENT ADDRESS SAME AS STUDENT ADDRESS
	jQuery( "body" ).on( "click", ".same_as_address", function () {
		if (jQuery( this ).prop( "checked" ) == true) {
			var s_address    = jQuery( '.student_address' ).val();
			var s_state_name = jQuery( '.student_state' ).val();
			var s_city       = jQuery( '.student_city' ).val();
			var s_zip_code   = jQuery( '.student_zip' ).val();
			jQuery( '.parent_address' ).val( s_address );
			jQuery( '.parent_state' ).val( s_state_name );
			jQuery( '.parent_city' ).val( s_city );
			jQuery( '.parent_zip' ).val( s_zip_code );
		}
	});
	// SIBLING CLASS, SECTION & STUDENTLIST LOAD
	jQuery( "body" ).on( "change", "#mjschool-sibling-class-change", function (event) {
		jQuery( '#sibling_student_list' ).html( '' );
		var selection = jQuery( this ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_user',
			class_list: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#sibling_student_list' ).append( response );
		});
	});
	jQuery( "body" ).on( "change", "#mjschool-sibling-class-change", function () {
		jQuery( '#sibling_class_section' ).html( '' );
		jQuery( '#sibling_class_section' ).append( '<option value="remove">Loading..</option>' );
		var selection = jQuery( "#mjschool-sibling-class-change" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_section',
			class_id: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( "#sibling_class_section option[value='remove']" ).remove();
			jQuery( '#sibling_class_section' ).append( response );
		});
	});
	jQuery( "#sibling_class_section" ).on( 'change', function () {
		jQuery( '#sibling_student_list' ).html( '' );
		var selection = jQuery( this ).val();
		var class_id  = jQuery( "#mjschool-sibling-class-change" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_section_user',
			section_id: selection,
			class_id: class_id,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#sibling_student_list' ).append( response );
		});
	});
	// ATTENDANCE REPORT FILTER VISE DATA
	jQuery( "body" ).on( "change", ".mjschool-attendance-report-filter", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var type = jQuery( this ).val();
		jQuery( '.mjschool-attendance-report-load' ).html( '' );
		var curr_data ={
			action: 'mjschool_attendance_dashboard_report_content',
			type: type,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery('.mjschool-attendance-report-load').html(response);
			jQuery(document).trigger("mjschool_attendance_dashboard_report_loaded");
			return true;
		});
	});
	// INCOME EXPENCE MONTH & YEAR ONCHANGE EVENT
	jQuery( "body" ).on( "change", ".dash_month_load,.mjschool-dash-year-load", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		jQuery( '#income_expence_report_append' ).html( "" );
		var month     = jQuery( ".dash_month_load" ).val();
		var year      = jQuery( ".mjschool-dash-year-load" ).val();
		var curr_data ={
			action: 'mjschool_load_income_expence_report',
			month_val: month,
			year_val: year,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery('#income_expence_report_append').html(response);
			jQuery(document).trigger("mjschool_income_expence_report_loaded");
			return true;
		});
	});
	// FEES PAYMENT MONTH & YEAR ONCHANGE EVENT
	jQuery( "body" ).on( "change", ".fees_month_load,.fees_year_load", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		jQuery( '#fees_report_append' ).html( "" );
		var month     = jQuery( ".fees_month_load" ).val();
		var year      = jQuery( ".fees_year_load" ).val();
		var curr_data ={
			action: 'mjschool_load_membership_payment_report',
			month_val: month,
			year_val: year,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery('#fees_report_append').html(response);
			jQuery(document).trigger("mjschool_membership_payment_report_loaded");
			return true;
		});
	});
	// PAYMENT REPORT FILTER VISE DATA
	jQuery( "body" ).on( "change", ".payment_report_filter", function (event) {
		event.preventDefault(); // disable normal link function so that it doesn't refresh the page
		var type = jQuery( this ).val();
		jQuery( '.mjschool-payment-report-load' ).html( '' );
		var curr_data ={
			action: 'mjschool_payment_dashboard_report_content',
			type: type,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery('.mjschool-payment-report-load').html(response);
			jQuery(document).trigger("mjschool_payment_dashboard_report_loaded");
			return true;
		});
	});
	jQuery( "#fees_class_list_id" ).on( 'change', function () {
		var class_list    = jQuery( "#fees_class_list_id" ).val();
		var class_section = jQuery( "#fees_class_section_id" ).val();
		var curr_data     = {
			action: 'mjschool_fees_user_list',
			class_list: class_list,
			class_section: class_section,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post(mjschool.ajax, curr_data, function (response) {
			var json_obj = jQuery.parseJSON(response);// parse JSON
			jQuery( "#fees_class_section_id" ).html( '' );
			jQuery( '#fees_class_section_id' ).append( json_obj['section'] );
			jQuery( '.user_display_block' ).html( json_obj['users'] ); // replaces in one go
			// Re-init multiselect on the *new* select
			if (jQuery.fn.multiselect) {
				jQuery( '#selected_users' ).multiselect( {
					nonSelectedText: "Select Student",
					// nonSelectedText: language_translate2.select_user_label,
					includeSelectAllOption: true,
					selectAllText: "Select all",
					// selectAllText: language_translate2.select_all_label,
					templates: {
						button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
					},
					buttonContainer: '<div class="dropdown" />'
				});
			} else {
				console.error( "Bootstrap Multiselect plugin not loaded." );
			}
		});
	});
	jQuery( "#fees_class_section_id" ).on( 'change', function () {
		var class_list    = jQuery( "#fees_class_list_id" ).val();
		var class_section = jQuery( "#fees_class_section_id" ).val();
		var curr_data     = {
			action: 'mjschool_fees_user_list',
			class_list: class_list,
			class_section: class_section,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		console.log(curr_data);
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			var json_obj = jQuery.parseJSON( response );// parse JSON
			jQuery( '.user_display_block' ).html( '' );
			jQuery( '.user_display_block' ).append( json_obj['users'] );
			jQuery( '#selected_users' ).multiselect( {
				nonSelectedText: language_translate2.select_user_label,
				includeSelectAllOption: true,
				selectAllText: language_translate2.select_all_label,
				templates: {
					button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
				},
				buttonContainer: '<div class="dropdown" />'
			});
		});
	});
	jQuery( "body" ).on( "change", ".load_fees_drop", function () {
		jQuery( '#fees_data' ).html( '' );
		var selection = jQuery( "#mjschool-class-list" ).val();
		var optionval = jQuery( this );
		var curr_data = {
			action: 'mjschool_load_class_fee_type',
			class_list: selection,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post( mjschool.ajax, curr_data, function (response) {
			jQuery( '#fees_data' ).append( response );
			jQuery( '#fees_data' ).multiselect( {
				templates: {
					button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
				}
			});
			jQuery( '#fees_data' ).multiselect( 'rebuild' );
		});
	});
	jQuery( ".mjschool-button-reload" ).on( 'click', function (event) {
		event.preventDefault(); // Prevent the default form submit or button action
	});
	jQuery( ".mjschool-sub-chk" ).on( 'click', function (event) {
		event.stopPropagation(); // Prevent event bubbling to the button
	});
	// Optionally, hide the alert on any manual change
	jQuery( '#birth_date' ).on( 'change', function () {
		if (jQuery( this ).val( ) ) {
			jQuery( '.birth_datemjschool-formError ' ).hide();
		}
	});
	// CUSCRIBUTIONS SECTION DISPLAY OPION
	jQuery( "body" ).on( "change", ".contributions_section", function () {
		if (jQuery( this ).is( ':checked' ) == true) {
			jQuery( '#cuntribution_div' ).addClass( 'mjschool-cuntribution-div-block' );
			jQuery( '#cuntribution_div' ).removeClass( 'mjschool-cuntribution-div-none' );
		} else {
			jQuery( '#cuntribution_div' ).removeClass( 'mjschool-cuntribution-div-block' );
			jQuery( '#cuntribution_div' ).addClass( 'mjschool-cuntribution-div-none' );
		}
	});
	// LOAD LIBRARY CARD NUMBER
	jQuery( "body").on( "change", ".change_library_card", function () {
		var user_id   = jQuery( this ).val();
		var card      = jQuery( '#issue_library_card' ).val( '' );
		var curr_data = {
			action: 'mjschool_load_library_card_no',
			user_id: user_id,
			nonce: mjschool.nonce,
			// dataType: 'json'
		};
		jQuery.post(mjschool.ajax, curr_data, function (response) {
			if (response.success && response.data) {
				jQuery( '#issue_library_card' ).val(response.data);
			}
		});
	});
	// Check LIBRARY CARD NUMBER EXITS OR NOT
	// jQuery( "body" ).on( "blur", "#issue_library_card", function () {
	// 	var card      = jQuery( '#issue_library_card' ).val();
	// 	var curr_data = {
	// 		action: 'mjschool_exits_library_card_no',
	// 		card_no: card,
	// 		nonce: mjschool.nonce,
	// 		dataType: 'json'
	// 	};
	// 	console.log(curr_data);
	// 	jQuery.post( mjschool.ajax, curr_data, function (response) {
	// 		if (response == 1) {
	// 			jQuery( ".card_validation_div" ).css( { "display": "block" } );
	// 		} else {
	// 			jQuery( ".card_validation_div" ).css( { "display": "none" } );
	// 		}
	// 		return true;
	// 	});
	// 	// console.log(jQuery.post(mjschool.ajax, curr_data, function (response) { } ) );
	// });
	// Load Class Room On Changing Class
	jQuery( "body").on( "Change", "#mjschool-class-list", function () {
		var class_id = jQuery( "#mjschool-class-list").val();
		jQuery( "#classroom_id").html( '' );
		jQuery( "#classroom_id").append( '<option value="remove">Loading..</option>' );
		var curr_data = {
			action: 'mjschool_load_classroom',
			class_id: class_id,
			dataType: 'json'
		};
		jQuery.POST(mjschool.ajax, curr_data, function (response) {
			jQuery( "#classroom_id").html( '' );
			jQuery( "#classroom_id").append(response);
		})
	})
	// LOAD CLASSROOM FROM CHANGE CLASS
	jQuery( "body").on( "change", "#mjschool-class-list", function () 
	{
		var class_id = jQuery( "#mjschool-class-list").val();
		jQuery( '#classroom_id' ).html( '' );
		jQuery( '#classroom_id' ).append( '<option value="remove">Loading..</option>' );
		var curr_data = {
			action: 'mjschool_load_classroom',
			class_id: class_id,
			dataType: 'json'
		};
		jQuery.post(mjschool.ajax, curr_data, function (response) 
		{
			jQuery( '#classroom_id' ).html( '' );
			jQuery( '#classroom_id' ).append(response);
		});
	});
});
