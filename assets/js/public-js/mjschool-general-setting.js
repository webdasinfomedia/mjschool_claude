jQuery(document).ready(function () {
    "use strict";
    jQuery.validationEngineLanguage.allRules["custom_school_name_validation"] = {
        "regex": /^[A-Za-z0-9\s_',.`\-^&]+$/,
        "alertText": "* Only letters, numbers, and ' _ , ` . ^ - & characters are allowed"
    };
    jQuery( '#mjschool_setting_form, #mjschool-email-template-form, #app_verification_form, #setting_form, #class_Section_form, #document_setting_form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    // Check total weightage does not exceed 100% in general setting.
    jQuery(document).on( 'click', '.check_total_per', function(e) {
        var totalMark = 100;
        var contributionTotal = 0;
        jQuery( "input[name='weightage[]']").each(function() {
            var value = parseFloat(jQuery(this).val( ) ) || 0; // Treat empty as 0.
            contributionTotal += value;
        });
        if (contributionTotal > totalMark) {
            alert( "Error: Total weightage must not exceed 100%.");
            e.preventDefault(); // Prevent form submission.
        }
    });
    jQuery(document).on( 'submit', 'form', function(e) {
        var selectedFileType = jQuery( '.file_types_input' ).val(); // Get the selected dropdown value.
        if (selectedFileType) {
            // Remove old "file_types" if exists.
            jQuery(this).find( 'input[name="validation[]"]' ).each(function () {
                if (jQuery(this).val().startsWith( 'file_types' ) ) {
                    jQuery(this).remove();
                }
            });
            // Inject the correct format into validation[].
            jQuery( '<input>' ).attr({
                type: 'hidden',
                name: 'validation[]',
                value: 'file_types:' + selectedFileType
            }).appendTo(this);
        }
    });
    jQuery(document).on( "click", "#add_custom_field", function() {
		// Check if any checkbox inside #validation_msg is checked.
		if (jQuery( "#validation_msg input:checked").length === 0) {
			alert(language_translate2.one_select_Validation_alert);
			return false;
		}
    });
    // Initialize DataTable.
    jQuery( '#custome_field_list' ).DataTable({
        initComplete: function (settings, json) {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        dom: 'lifrtp',
        order: [[2, "asc"]],
        aoColumns: [
            { bSortable: false },
            { bSortable: false },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: false }
        ],
        language: mjschool_general_setting_data.datatable_language
    });
    // DataTable initialization.
    var table = jQuery( '#exam_merge_list' ).DataTable({
        initComplete: function(settings, json) {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        dom: 'lifrtp',
        order: [[2, "asc"]],
        aoColumns: [
            { bSortable: false },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: false },
            { bSortable: false }
        ],
        language: mjschool_general_setting_data.datatable_language
    });
    // DataTable initialization.
    if(jQuery( '#frontend_custome_field_list' ).length > 0) {
        jQuery( '#frontend_custome_field_list' ).DataTable({
            initComplete: function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({"margin-top": "-5%"});
            },
            //stateSave: true,
            dom: 'lifrtp',
            order: [[2, "asc"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: false }
            ],
            language: mjschool_general_setting_data.datatable_language
        });
    }
    // Add placeholder to search input.
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_general_setting_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    // Add more contributions in general setting.
    window.mjschool_add_more_merge_result = function() {
        var class_name = jQuery( "#mjschool-class-list").val().trim();
        var section_name = jQuery( "#class_section").val();
        if (class_name !== "") {
            var curr_data = {
                action: 'mjschool_add_more_merge_result',
                class_name: class_name,
                section_name: section_name,
                nonce: mjschool.nonce,
                dataType: 'json'
            };
            jQuery.post(mjschool.ajax, curr_data, function(response) {
                jQuery( "#mjschool-merge-settings-div").append(response);
            });
        } else {
            alert( 'Please select a class.' );
        }
    };
    // Remove contribution row.
    window.mjschool_delete_parent_elementExamMergeSettings = function(n) {
        if (confirm(language_translate2.delete_record_alert ) ) {
            n.parentNode.parentNode.parentNode.removeChild(n.parentNode.parentNode);
        }
    };
    jQuery( '.document_type, .profile_extention' ).multiselect({
        nonSelectedText: mjschool_general_setting_data.select_document_type_text,
        includeSelectAllOption: true,
        allSelectedText: mjschool_general_setting_data.all_selected,
        selectAllText: mjschool_general_setting_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        },
        buttonContainer: '<div class="dropdown" />'
    });
    // Document type validation on click.
    jQuery(document).on( 'click', '.mjschool-document-type-validation', function() {
        var checkedDocs = jQuery( ".multiselect_validation_document .dropdown-menu input:checked").length;
        if (!checkedDocs) {
            alert( mjschool_general_setting_data.one_document_alert_text);
            return false;
        }
        var checkedProfiles = jQuery( ".mjschool-multiselect-validation-profile .dropdown-menu input:checked").length;
        if (!checkedProfiles) {
            alert( mjschool_general_setting_data.profile_alert_text);
            return false;
        }
    });
});