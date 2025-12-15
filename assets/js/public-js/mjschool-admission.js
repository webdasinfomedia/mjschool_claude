jQuery( document ).ready( function (jQuery) {
	"use strict";
	// Form validation for the admission form using ValidationEngine.
	jQuery( '#mjschool-admission-form' ).validationEngine({
		promptPosition: "bottomLeft",
		maxErrorsPerField: 1
	});
	// Datepickers.
	jQuery( '.birth_date, .sdate, .edate' ).datepicker({
		dateFormat: mjschool_admission_data.date_format,
		maxDate: 0,
		changeMonth: true,
		changeYear: true,
		yearRange: '-65:+25',
		beforeShow: function(textbox, instance) {
			instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
		},
		onChangeMonthYear: function(year, month, inst) {
			jQuery(this).val(month + "/" + year);
		}
	});
	jQuery( '#admission_date' ).datepicker({
		dateFormat: mjschool_admission_data.date_format,
		changeMonth: true,
		changeYear: true,
		yearRange: '-10:+10',
		beforeShow: function(textbox, instance) {
			instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
		},
		onChangeMonthYear: function(year, month, inst) {
			jQuery(this).val(month + "/" + year);
		}
	});
	// Expose functions globally for HTML onclick calls.
	window.mjschool_add_more_siblings = function() {
		var click_val = jQuery( ".click_value").val();
		var curr_data = {
			action: 'mjschool_load_siblings_dropdown',
			click_val: click_val,
			nonce: mjschool.nonce,
			dataType: 'json'
		};
		jQuery.post(mjschool.ajax, curr_data, function(response) {
			var value = parseInt(click_val) + 1;
			jQuery( ".click_value").val(value);
			jQuery(".mjschool-sibling-div_clss").append(response);
			jQuery(document).trigger("mjschool_sibling_dropdown_loaded");
		});
	};
	window.mjschool_delete_parent_element = function(n) {
		if (confirm(language_translate2.delete_record_alert ) ) {
			n.parentNode.parentNode.parentNode.removeChild(n.parentNode.parentNode);
		}
	};
	jQuery('.mjschool-sibling-div_clss').each(function () {
		// On class change - load users.
		let index = $(this).data('sibling-index');
		if (!index) return; // skip if not found.
		jQuery(document).on("change", "#sibling_class_change_" + index, function () {
			var classSelection = jQuery(this).val();
			var targetStudentList = jQuery('#sibling_student_list_' + index);
			targetStudentList.html(''); // Clear current list.
			jQuery.post(mjschool.ajax, {
				action: 'mjschool_load_user',
				class_list: classSelection,
				nonce: mjschool.nonce,
				dataType: 'json'
			}, function (response) {
				targetStudentList.append(response);
			});
			// Load class sections.
			var targetClassSection = jQuery('#sibling_class_section_' + index);
			targetClassSection.html('<option value="remove">Loading..</option>');
			jQuery.post(mjschool.ajax, {
				action: 'mjschool_load_class_section',
				class_id: classSelection,
				nonce: mjschool.nonce,
				dataType: 'json'
			}, function (response) {
				targetClassSection.find("option[value='remove']").remove();
				targetClassSection.append(response);
			});
		});
		// On section change - load users.
		jQuery(document).on("change", "#sibling_class_section_" + index, function () {
			var sectionSelection = jQuery(this).val();
			var classSelection = jQuery("#sibling_class_change_" + index).val();
			var targetStudentList = jQuery('#sibling_student_list_' + index);
			targetStudentList.html(''); // Clear current list.
			jQuery.post(mjschool.ajax, {
				action: 'mjschool_load_section_user',
				section_id: sectionSelection,
				class_id: classSelection,
				nonce: mjschool.nonce,
				dataType: 'json'
			}, function (response) {
				targetStudentList.append(response);
			});
		});
	});
	// Check if at least one admission record is selected.
	jQuery(document).on( "click", ".admission_csv_selected", function () {
		if (jQuery( '.selected_admission:checked' ).length === 0) {
			alert(language_translate2.one_record_select_alert);
			return false;
		}
	});
	// Initialize DataTable.
	if (jQuery('#admission_list').length) {
		var customCols = Array.isArray(mjschool_admission_data.module_columns) ? mjschool_admission_data.module_columns.map(() => ({ bSortable: true })) : [];
		jQuery('#admission_list').DataTable({
			initComplete: function (settings, json) {
				jQuery(".mjschool-print-button").css({ "margin-top": "-5%" });
				jQuery('#admission_list th:first-child').removeClass('sorting_asc');
			},
			ordering: true,
			dom: 'lifrtp',
			aoColumns: [
				{ bSortable: false },
				{ bSortable: false },
				{ bSortable: true },
				{ bSortable: true },
				{ bSortable: true },
				{ bSortable: true },
				{ bSortable: true },
				{ bSortable: true },
				{ bSortable: true },
				...customCols,
				{ bSortable: false }
			],
			language: mjschool_admission_data.datatable_language
		});
	}
	// DataTable initialization for admission list at frontend side.
	if (jQuery('#mjschool-admission-list-front').length > 0) {
		var customCols = Array.isArray(mjschool_admission_data.module_columns) ? mjschool_admission_data.module_columns.map(() => ({ bSortable: true })) : [];
		jQuery( '#mjschool-admission-list-front' ).DataTable({
			"initComplete": function (settings, json) {
				jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
			},
			"ordering": true,
			"dom": 'lifrtp',
			"aoColumns": [
				mjschool_admission_data.is_supportstaff ? { "bSortable": false } : null,
				{ "bSortable": false },
				{ "bSortable": true },
				{ "bSortable": true },
				{ "bSortable": true },
				{ "bSortable": true },
				{ "bSortable": true },
				{ "bSortable": true },
				{ "bSortable": true },
				...customCols,
				{ "bSortable": false }
			],
			language: mjschool_admission_data.datatable_language
		});
	}
	jQuery('.dataTables_filter input')
		.attr("placeholder", mjschool_admission_data.search_placeholder)
		.attr("id", "datatable_search")
		.attr("name", "datatable_search");
	jQuery( "#sinfather" ).on( 'click', function () {
		jQuery( "#motid,#motid1,#motid2,#motid3,#motid4,#motid5,#motid6,#motid7,#motid8,#motid9,#motid10,#motid11,#mjschool-motid12,#motid13,#motid14,#motid15,#motid16,#motid17,#motid18" ).hide();
		jQuery( '.father_div' ).removeClass( 'family_display_none' );
	});
	jQuery( "#sinfather" ).on( 'click', function () {
		jQuery( "#fatid,#fatid1,#fatid2,#fatid3,#fatid4,#fatid5,#fatid6,#fatid7,#fatid8,#fatid9,#fatid10,#fatid11,#mjschool-fatid12,#fatid13,#fatid14,#fatid15,#fatid16,#fatid17,#fatid18" ).show();
	});
	jQuery( "#sinmother" ).on( 'click', function () {
		jQuery( "#motid,#motid1,#motid2,#motid3,#motid4,#motid5,#motid6,#motid7,#motid8,#motid9,#motid10,#motid11,#mjschool-motid12,#motid13,#motid14,#motid15,#motid16,#motid17,#motid18" ).show();
		jQuery( '.mother_div' ).css( 'clear','both' );
		jQuery( '.mother_div' ).removeClass( 'family_display_none' );
	});
	jQuery( "#sinmother" ).on( 'click', function () {
		jQuery( "#fatid,#fatid1,#fatid2,#fatid3,#fatid4,#fatid5,#fatid6,#fatid7,#fatid8,#fatid9,#fatid10,#fatid11,#mjschool-fatid12,#fatid13,#fatid14,#fatid15,#fatid16,#fatid17,#fatid18" ).hide();
	});
	jQuery( "#boths" ).on( 'click', function () {
		jQuery( "#motid,#motid1,#motid2,#motid3,#motid4,#motid5,#motid6,#motid7,#motid8,#motid9,#motid10,#motid11,#mjschool-motid12,#motid13,#motid14,#motid15,#motid16,#motid17,#motid18" ).show();
		jQuery( '.mother_div' ).css( 'clear','unset' );
	});
	jQuery( "#boths" ).on( 'click', function () {
		jQuery( "#fatid,#fatid1,#fatid2,#fatid3,#fatid4,#fatid5,#fatid6,#fatid7,#fatid8,#fatid9,#fatid10,#fatid11,#mjschool-fatid12,#fatid13,#fatid14,#fatid15,#fatid16,#fatid17,#fatid18" ).show();
	});
	jQuery( '#mjschool-admission-form' ).validationEngine( {promptPosition : "bottomRight",maxErrorsPerField: 1} );
	jQuery( '.email' ).on( 'change', function () {
		var father_email  = jQuery( ".father_email" ).val();
		var student_email = jQuery( ".email" ).val();
		var mother_email  = jQuery( ".mother_email" ).val();
		if (student_email == father_email) {
			alert( language_translate2.same_email_alert );
			jQuery( '.email' ).val( '' );
		} else if (student_email == mother_email) {
			alert( language_translate2.same_email_alert );
			jQuery( '.email' ).val( '' );
		} else {
			return true;
		}
	});
	jQuery( '.father_email' ).on( 'change', function () {
		var father_email  = jQuery( ".father_email" ).val();
		var student_email = jQuery( ".email" ).val();
		var mother_email  = jQuery( ".mother_email" ).val();
		if (student_email == father_email) {
			alert( language_translate2.same_email_alert );
			jQuery( '.father_email' ).val( '' );
		} else if (father_email == mother_email) {
			alert( language_translate2.same_email_alert );
			jQuery( '.father_email' ).val( '' );
		} else {
			return true;
		}
	});
	jQuery( '.mother_email' ).on( 'change', function () {
		var father_email  = jQuery( ".father_email" ).val();
		var student_email = jQuery( ".email" ).val();
		var mother_email  = jQuery( ".mother_email" ).val();
		if (student_email == mother_email) {
			alert( language_translate2.same_email_alert );
			jQuery( '.mother_email' ).val( '' );
		} else if (father_email == mother_email) {
			alert( language_translate2.same_email_alert );
			jQuery( '.mother_email' ).val( '' );
		} else {
			return true;
		}
	});
	jQuery( '#chkIsTeamLead' ).on( 'change', function () {
		if (jQuery('#chkIsTeamLead').is(':checked') == true) {
			jQuery( '.mjschool-sibling-div_clss' ).addClass( 'mjschool-sibling-div_block' );
			jQuery( '.mjschool-sibling-div_clss' ).removeClass( 'mjschool-sibling-div-none' );
		} else {
			jQuery( '.mjschool-sibling-div_clss' ).removeClass( 'mjschool-sibling-div_block' );
			jQuery( '.mjschool-sibling-div_clss' ).addClass( 'mjschool-sibling-div-none' );
		}
	});
	// File input validation on change.
	jQuery(document).on( "change", ".input-file[type=file]", function () {
		var elmId = jQuery(this).attr( "name");
		var file = this.files[0];
		var ext = jQuery(this).val().split( '.' ).pop().toLowerCase();
		// Extension Check.
		if (jQuery.inArray(ext, ['pdf', 'doc', 'docx', 'gif', 'png', 'jpg', 'jpeg']) === -1) {
			alert( 'Only pdf, doc, docx, gif, png, jpg, jpeg formats are allowed. ' + ext + ' format is not allowed.' );
			jQuery(this).replaceWith( '<input class="col-md-2 col-sm-2 col-xs-12 form-control mjschool-file-validation input-file" name="' + elmId + '" value="" type="file" />' );
			return false;
		}
		// File Size Check (20 MB).
		if (file.size > 20480000) {
			alert(language_translate2.large_file_size_alert);
			jQuery(this).replaceWith( '<input class="col-md-2 col-sm-2 col-xs-12 form-control mjschool-file-validation input-file" name="' + elmId + '" value="" type="file" />' );
			return false;
		}
	});
});
