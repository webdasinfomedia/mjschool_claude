jQuery(document).ready(function () {
    "use strict";
    jQuery(document).on( 'click', '.check_contribution_marks', function(e) {
        var totalMark = parseFloat(jQuery( '.total_mark' ).val( ) ) || 0;
        var contributionTotal = 0;
        jQuery( "input[name='contributions_mark[]']").each(function() {
            contributionTotal += parseFloat(jQuery(this).val( ) ) || 0;
        });
        if (contributionTotal > totalMark) {
            alert( "Contribution marks total must be less than or equal to Total Marks.");
            e.preventDefault();
        }
    });
    // Add more contributions.
    window.mjschool_add_more_contributions = function() {
        var curr_data = {
            action: 'mjschool_load_more_contributions',
            nonce: mjschool.nonce,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function(response) {
            jQuery( "#cuntribution_div").append(response);
        });
    };
    // Remove contribution.
    window.mjschool_delete_parent_elementConstribution = function(n) {
        if (confirm(language_translate2.delete_record_alert ) ) {
            n.parentNode.parentNode.parentNode.removeChild(n.parentNode.parentNode);
        }
    };
    // Timepicker.
    mdtimepicker( '.timepicker', {
        theme: 'purple',
        readOnly: false
    });
    function mjschool_bind_subject_toggles(){
        jQuery( '.subject-enable-checkbox' ).off( 'change' ).on( 'change', function(){
            var row = jQuery(this).closest( 'tr' );
            var enabled = jQuery(this).is( ':checked' );
            row.find( '.pass_mark, .total_mark' ).prop( 'disabled', !enabled);
            // If enabling and fields were empty, you may choose to auto-clear or leave as is.
        });
    }
    function mjschool_load_university_subjects(class_id, exam_id){
        if(!class_id){
            jQuery( '#university_subjects_container' ).html( '' );
            return;
        }
        jQuery.post(mjschool.ajax, { action: 'mjschool_load_subjects_for_exam_callback', class_id: class_id, exam_id: exam_id }, function(response){
            if(response.success){
                jQuery( '#university_subjects_container' ).html(response.data.html);
                mjschool_bind_subject_toggles();
            } else {
                jQuery( '#university_subjects_container' ).html( '<p>Failed to load subjects.</p>' );
            }
        }, 'json' );
    }
    jQuery( '#mjschool-class-list' ).on( 'change', function(){
        var class_id = jQuery(this).val();
        var exam_id = jQuery( 'input[name="exam_id"]' ).val() || '';
        mjschool_load_university_subjects(class_id, exam_id);
    });
    // Initial load for edit.
    var init_class = jQuery( '#mjschool-class-list' ).val();
    if(init_class){
        var exam_id = mjschool_student_evaluation_data.exam_data_id;
        mjschool_load_university_subjects(init_class, exam_id);
    }
    function mjschool_validate_university_subjects() {
        var isValid = true;
        var errorMessages = [];
        jQuery( '#university_subjects_container table tbody tr' ).each(function(index, row) {
            var $row = jQuery(row);
            var enabled = $row.find( '.subject-enable-checkbox' ).is( ':checked' );
            if (enabled) {
                var passMark = parseFloat($row.find( '.pass_mark' ).val( ) ) || 0;
                var totalMark = parseFloat($row.find( '.total_mark' ).val( ) ) || 0;
                if (totalMark > 100) {
                    isValid = false;
                    errorMessages.push( 'Subject row ' + (index + 1) + ': Total marks cannot be more than 100.' );
                }
                if (passMark > totalMark) {
                    isValid = false;
                    errorMessages.push( 'Subject row ' + (index + 1) + ': Passing marks cannot be greater than total marks.' );
                }
            }
        });
        if (!isValid) {
            alert(errorMessages.join( '\n' ) );
        }
        return isValid;
    }
    // Attach to form submit.
    jQuery( '#exam_form' ).on( 'submit', function(e) {
        if (!mjschool_validate_university_subjects( ) ) {
            e.preventDefault(); // Stop the form from submitting.
        }
    });
    var start = jQuery( "#start").val();
    var end = jQuery( "#end").val();
    jQuery( ".exam_date").datepicker({
        minDate: start,
        maxDate: end,
        changeYear: true,
        changeMonth: true,
        dateFormat: mjschool_student_evaluation_data.date_format,
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({
                marginTop: (-textbox.offsetHeight) + 'px'
            });
        }
    });
    var start = jQuery( "#start_date").val();
    var end = jQuery( "#end_date").val();
    jQuery( ".front_exam_date").datepicker({
        minDate: start,
        maxDate: end,
        changeYear: true,
        changeMonth: true,
        dateFormat: "yy-mm-dd"
    });
    jQuery( '#exam_form, #exam_form2, #hall_form, #receipt_form, #multiple_subject_mark_data, #export_mark_table, #select_data, #marks_form, #grade_form, #migration_index_table, #exam_form_front, #exam_time_table, #category_form_test, #mjschool-Add-marks-form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    jQuery( "#exam_start_date").datepicker({
        dateFormat: mjschool_student_evaluation_data.date_format,
        changeYear: true,
        changeMonth: true,
        minDate: 0
    });
    jQuery( "#exam_end_date").datepicker({
        dateFormat: mjschool_student_evaluation_data.date_format,
        changeYear: true,
        changeMonth: true,
        minDate: 0
    });
    jQuery(document).on( 'keypress', '.mjschool-onlyletter-number-space-validation', function (e) {
        var regex = /^[0-9a-zA-Z \b]+$/;
        var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (!regex.test(key ) ) {
            e.preventDefault();
            return false;
        }
    });
    // ==========================
    // DataTable initialization for exam index.
    // ==========================
    var customCols = Array.isArray(mjschool_student_evaluation_data.module_columns) ? mjschool_student_evaluation_data.module_columns.map(() => ({ bSortable: true })) : [];
    jQuery( '#exam_list' ).DataTable({
        "initComplete": function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        "order": [[6, "desc"]],
        "dom": 'lifrtp',
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            ...customCols,
            { "bSortable": false }
        ],
        language: mjschool_student_evaluation_data.datatable_language
    });
    var customColsHall = Array.isArray(mjschool_student_evaluation_data.module_columns) ? mjschool_student_evaluation_data.module_columns.map(() => ({ bSortable: true })) : [];
    // DataTable initialization for exam hall.
    jQuery( '#hall_list_admin' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        order: [[2, "asc"]],
        dom: 'lifrtp',
        columns: [
            { orderable: false },
            { orderable: false },
            { orderable: true },
            { orderable: true },
            { orderable: false },
            { orderable: true },
            ...customColsHall,
            { orderable: false }
        ],
        language: mjschool_student_evaluation_data.datatable_language
    });
    // DataTable initialization for grade table.
    jQuery( '#grade_list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        order: [[2, "desc"]],
        dom: 'lifrtp',
        columnDefs: [
            { targets: 0, orderable: false },
            { targets: 1, orderable: false },
            { targets: -1, orderable: false }
        ],
        language: mjschool_student_evaluation_data.datatable_language
    });
    // DataTable initialization for grade list at frontend side.
    if (jQuery('#frontend_grade_list').length > 0) {
        var customCols = Array.isArray(mjschool_student_evaluation_data.module_columns) ? mjschool_student_evaluation_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#frontend_grade_list' ).DataTable({
            "initComplete": function (settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "order": [[2, "desc"]],
            "dom": 'lifrtp',
            "aoColumns": [
                mjschool_student_evaluation_data.is_supportstaff ? { "bSortable": false } : null,
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                (mjschool_student_evaluation_data.is_edit_access || mjschool_student_evaluation_data.is_edit_access) ?  { "bSortable": true } : null,
                ...customCols,
                { "bSortable": false }
            ].filter(Boolean),
            language: mjschool_student_evaluation_data.datatable_language
        });
    }
    // Exam list DataTable at frontend side.
    if (jQuery('#front_exam_list').length > 0) {
        var customexamCols = Array.isArray(mjschool_student_evaluation_data.module_columns) ? mjschool_student_evaluation_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery('#front_exam_list').DataTable({
            initComplete: function () {
                jQuery(".mjschool-print-button").css({ "margin-top": "-5%" });
            },
            dom: 'lifrtp',
            order: [
                mjschool_student_evaluation_data.is_supportstaff ? [6, "desc"] : [5, "desc"]
            ],
            aoColumns: [
                mjschool_student_evaluation_data.is_supportstaff ? { bSortable: false } : null,
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customexamCols,
                { bSortable: false }
            ].filter(Boolean),
            language: mjschool_student_evaluation_data.datatable_language
        });
    }
    // Frontend hall list table.
    if (jQuery('#hall_list_frontend').length > 0) {
        var customexamCols = Array.isArray(mjschool_student_evaluation_data.module_columns) ? mjschool_student_evaluation_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery('#hall_list_frontend').DataTable({
            initComplete: function() {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            order: [[2, "asc"]],
            dom: 'lifrtp',
            aoColumns: [
                mjschool_student_evaluation_data.is_supportstaff ? { "bSortable": false } : null,
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customexamCols,
                { "bSortable": false }
            ].filter(Boolean),
            language: mjschool_student_evaluation_data.datatable_language
        });
    }
    jQuery('.dataTables_filter input')
		.attr("placeholder", mjschool_student_evaluation_data.search_placeholder)
		.attr("id", "datatable_search")
        .attr("name", "datatable_search");
    jQuery( '.mjschool-width-200' ).DataTable({
        responsive: true,
        bPaginate: false,
        bFilter: false,
        bInfo: false,
    });
    jQuery(document).on( "click", "#save_exam_time", function (e) {
        var subject_data = jQuery( "#subject_data").val();
        var suj = JSON.parse(subject_data);
        var productIds = [];
        jQuery.each(suj, function (i, val) {
            var exdt = jQuery( "#exam_date_" + val.subid).val();
            var strh = jQuery( ".start_time_" + val.subid).val();
            var endh = jQuery( ".end_time_" + val.subid).val();
            var exsdtfull = exdt + strh;
            var exedtfull = exdt + endh;
            if (jQuery.inArray(exsdtfull, productIds) == -1) {
                productIds.push(exsdtfull);
            }
            if (jQuery.inArray(exedtfull, productIds) == -1) {
                productIds.push(exedtfull);
            }
            var start_time_new = mjschool_convert_time_format_new(strh);
            var end_time_new = mjschool_convert_time_format_new(endh);
            if (strh != "") {
                if (start_time_new >= end_time_new) {
                    alert( mjschool_student_evaluation_data.subject_text + ' ' + val.sub_name + ' ' + mjschool_student_evaluation_data.end_time_must_greater_text );
                    e.preventDefault(e);
                }
            } else {
                jQuery( '#exam_form2' ).validationEngine({
                    promptPosition: "bottomLeft",
                    maxErrorsPerField: 1
                });
            }
        });
    });
    function mjschool_convert_time_format(strfull) {
        var hrs = Number(strfull.match(/^(\d+)/)[1]);
        var mnts = Number(strfull.match(/:(\d+)/)[1]);
        var format = strfull.match(/\s(.*)$/)[1];
        if (format == "pm" && hrs < 12 ) hrs = hrs + 12;
        if (format == "am" && hrs == 12 ) hrs = hrs - 12;
        var hours = hrs.toString();
        var minutes = mnts.toString();
        if (hrs < 10) hours = "0" + hours;
        if (mnts < 10) minutes = "0" + minutes;
        return hours + ":" + minutes;
    }
    function mjschool_convert_time_format_new(strfull) {
        var hrs = Number(strfull.match(/^(\d+)/)[1]);
        var mnts = Number(strfull.match(/:(\d+)/)[1]);
        var format = strfull.match(/\s(.*)$/)[1];
        if (format == "PM" && hrs < 12 ) hrs = hrs + 12;
        if (format == "AM" && hrs == 12 ) hrs = hrs - 12;
        var hours = hrs.toString();
        var minutes = mnts.toString();
        if (hrs < 10) hours = "0" + hours;
        if (mnts < 10) minutes = "0" + minutes;
        return hours + ":" + minutes;
    }
    jQuery( '#exam_timelist' ).DataTable({
        responsive: true,
        bPaginate: false,
        bFilter: false,
        bInfo: false,
        language: mjschool_student_evaluation_data.datatable_language
    });
    jQuery( '.exam_table' ).DataTable({
        responsive: true,
        bPaginate: false,
        bFilter: false,
        bInfo: false,
    });
    // DataTable initialization for exam hall.
    jQuery( '.exam_hall_table' ).DataTable({
        responsive: true,
        paging: false,
        searching: false,
        info: false
    });
    //File Validation for manage marks
    jQuery( '.mjschool-file-validation-for-exam' ).on( 'change', function(e) {
        var val = jQuery(this).val().toLowerCase();
        var regex = new RegExp( "(.*?)\\.(csv)$");
        if (!(regex.test(val ) ) ) {
            jQuery(this).val( '' );
            alert( "<?php esc_html_e( 'Only CSV format are allowed.', 'mjschool' ); ?>");
        }
    });
    // Validate marks row-wise in grade.
    jQuery(document).on( "blur", ".mark_from_input, .mark_upto_input", function () {
        var markFromVal = parseFloat(jQuery( '.mark_from_input' ).val( ) );
        var markUptoVal = parseFloat(jQuery( '.mark_upto_input' ).val( ) );
        // Only check if both fields have values.
        if (!isNaN(markFromVal) && !isNaN(markUptoVal ) ) {
            if (markUptoVal <= markFromVal) {
                alert( "<?php esc_html_e( 'Mark Upto must be greater than Mark From!', 'mjschool' ); ?>");
                jQuery( '.mark_upto_input' ).val( '' ); // Only clear once.
                jQuery( '.mark_from_input' ).val( '' );
            }
        }
    });
    // Exam ID change event for migration.
    jQuery(document).on( "change", "#exam_id", function () {
        var exam_id = jQuery(this).val();
        if (exam_id) {
            jQuery( "#mjschool-migration-passing-mark").removeClass( "mjschool-passing-mark-display-none");
        } else {
            jQuery( "#mjschool-migration-passing-mark").addClass( "mjschool-passing-mark-display-none");
        }
    });
    // File extension validation for exam_syllebus inputs at frontend side.
    jQuery(document).on( "change", ".exam_syllebus", function () {
        var val = jQuery(this).val().toLowerCase();
        var regex = new RegExp( "(.*?)\\.(docx|doc|pdf|ppt|jpg|jpeg|png|xls|xlsx|ppt|pptx|gif)$");
        if (!(regex.test(val ))) {
            jQuery(this).val('');
            alert(mjschool_student_evaluation_data.subject_file_alert_text);
        }
    });
    mdtimepicker( '.mjschool_timepicker', {
        events: {
            timeChanged: function (data) {
                // You can handle time change event here if needed.
            }
        },
        theme: 'purple',
        readOnly: false,
    });
    jQuery( '#attendence_list' ).DataTable({
        responsive: true
    });
});