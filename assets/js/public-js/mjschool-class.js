jQuery(document).ready(function () {
    "use strict";
    jQuery( '#class_form , #class_Section_form , #mjschool-class-room-form , #subject_form , #rout_form, #import_class_csv, #export_class_csv, #meeting_form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    // Virtual classroom toggle.
    jQuery( ".create_virtual_classroom").on( 'click', function() {
        var isChecked = jQuery( 'input:checkbox[name=create_virtual_classroom]' ).is( ':checked' );
        jQuery( ".mjschool-create-virtual-classroom-div").toggleClass( "create_virtual_classroom_div_block", isChecked).toggleClass( "mjschool-create-virtual-classroom-div-none", !isChecked);
    });
    // Start date picker initialization.
    jQuery( "#start_date_new").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        onSelect: function(selected) {
            var dt = new Date(selected);
            jQuery( ".end_date").datepicker( "option", "minDate", dt);
        }
    });
    // End date picker initialization.
    jQuery( "#end_date_new").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        onSelect: function(selected) {
            var dt = new Date(selected);
            jQuery( ".start_date").datepicker( "option", "maxDate", dt);
        }
    });
    jQuery( '.mjschool-multiple-select-day' ).multiselect({
        nonSelectedText: mjschool_class_data.select_days,
        includeSelectAllOption: true,
        selectAllText: mjschool_class_data.select_all,   
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        },
        buttonContainer: '<div class="dropdown" />'
    });
    // Multiselect initialization for teachers.
    jQuery( '#subject_teacher' ).multiselect({
        nonSelectedText: mjschool_class_data.select_teacher,
        includeSelectAllOption: true,
        selectAllText: mjschool_class_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        },
        buttonContainer: '<div class="dropdown" />'
    });
    // Timepickers initialization.
    mdtimepicker( '#mjschool-start-timepicker , #mjschool-end-timepicker', {
        theme: 'purple',
        readOnly: false
    });
    // Timepicker initialization at frontend side.
    mdtimepicker( '.timepicker', {
        events: {
            timeChanged: function(data) {}
        },
        theme: 'purple',
        readOnly: false,
    });
    // Floating label fix for filled inputs.
    jQuery( '.form-control input' ).each(function() {
        if (jQuery(this).val( ) ) {
            jQuery(this).next( 'label' ).addClass( 'top' );
        }
    });
    jQuery( '.form-control input' ).on( 'focus blur', function() {
        if (jQuery(this).val() !== '' ) {
            jQuery(this).next( 'label' ).addClass( 'top' );
        } else {
            jQuery(this).next( 'label' ).removeClass( 'top' );
        }
    });
    jQuery( '#section_list' ).DataTable({
        initComplete: function(settings, json) {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        dom: 'lifrtp',
        order: [[2, "asc"]],
        aoColumns: [
            { bSortable: true },
            { bSortable: true },
            { bSortable: false }
        ],
        language: mjschool_class_data.datatable_language
    });
    if (jQuery('#class_wise_student_list,#front_class_wise_student_list').length > 0) {
        jQuery('#class_wise_student_list,#front_class_wise_student_list').DataTable({
            initComplete: function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
            },
            responsive: true,
            order: [[2, "desc"]],
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                mjschool_class_data.is_school ? { bSortable: true } : null,
                { bSortable: false }
            ].filter(Boolean),
            language: mjschool_class_data.datatable_language
        });
    }
    // Initialize Meeting DataTable meeting list in view meeting.
    var meetingTable = jQuery( '#meeting_list' ).DataTable({
        // stateSave: true,
        order: [1, 'asc'],
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
            { bSortable: false }
        ],
        language: mjschool_class_data.datatable_language
    });
    // Initialize past participle DataTable.
    var pastParticipleTable = jQuery( '#past_participle_list' ).DataTable({
        order: [1, 'asc'],
        dom: 'lifrtp',
        aoColumns: [
            { bSortable: true },
            { bSortable: true },
            { bSortable: true }
        ],
        language: mjschool_class_data.datatable_language
    });
    // Initialize Meeting DataTable on index page .
    var meetingTable = jQuery( '#index_meeting_list' ).DataTable({
        initComplete: function() {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        order: [[2, 'asc']],
        dom: 'lifrtp',
        aoColumns: [
            { bSortable: false },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: false }
        ],
        language: mjschool_class_data.datatable_language
    });
    if (jQuery('#mjschool-classroom-list').length > 0) {
        jQuery( '#mjschool-classroom-list' ).DataTable({
            "initComplete": function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
                jQuery( '#mjschool-classroom-list th:first-child' ).removeClass( 'sorting_asc' );
            },
            responsive: true,
            "ordering": true,
            dom: 'Qlifrtp',
            "aoColumns": [
                { "bSortable": false, "className": 'sorting_disabled' },
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": false }
            ],
            language: mjschool_class_data.datatable_language
        });
    }
    var customCols = Array.isArray(mjschool_class_data.module_columns) ? mjschool_class_data.module_columns.map(() => ({ bSortable: true })) : [];
    // DataTable initialization for subject.
     if (jQuery('#mjschool-subject-list-admin').length > 0) {
        jQuery( '#mjschool-subject-list-admin' ).DataTable({
            "initComplete": function () {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            },
            //stateSave: true,
            responsive: true,
            "order": [[2, "DESC"]],
            "dom": 'lifrtp',
            "aoColumns": [
                { "bSortable": false },
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                mjschool_class_data.is_university ? { bSortable: true }:null,
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customCols,
                { "bSortable": false }
            ].filter(Boolean),
            language: mjschool_class_data.datatable_language
        });
    }
    // Initialize DataTable for subject list at frontend side.
    var customCols = Array.isArray(mjschool_class_data.module_columns) ? mjschool_class_data.module_columns.map(() => ({ bSortable: true })) : [];
    if(jQuery( '#mjschool-subject-list-frontend' ).length > 0) {
        jQuery( '#mjschool-subject-list-frontend' ).DataTable({
            initComplete: function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({"margin-top": "-5%"});
            },
            order: [2, 'asc'],
            dom: 'lifrtp',
            aoColumns: [
                mjschool_class_data.is_supportstaff ? { bSortable: false } : null, // Checkbox.
                { bSortable: false }, // Image.
                { bSortable: false }, // Subject code.
                { bSortable: true },  // Subject name.
                { bSortable: true },  // Teacher name.
                mjschool_class_data.is_university ? { bSortable: true } : null, // Student name.
                { bSortable: true }, // Class name.
                { bSortable: true }, // Author name.
                { bSortable: true }, // Edition.
                ...customCols,
                { bSortable: false } // Action column.
            ].filter(Boolean),
            language: mjschool_class_data.datatable_language
        });
    }
    // Initialize DataTable for class list at admin side.
    if (jQuery('#mjschool-class-list').length > 0) {
        var customCols = Array.isArray(mjschool_class_data.module_columns) ? mjschool_class_data.module_columns.map(() => ({ bSortable: true })) : [];
        var admin_class_list = jQuery( '#mjschool-class-list' ).DataTable({
            initComplete: function() {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
                jQuery( '#mjschool-class-list th:first-child' ).removeClass( 'sorting_asc' );
            },
            ordering: true,
            dom: 'Qlifrtp',
            aoColumns: [
                { bSortable: false, className: 'sorting_disabled' },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                mjschool_class_data.is_cust_class_room ? { bSortable: true } :null,
                ...customCols,
                { bSortable: false }
            ].filter(Boolean),
            language: mjschool_class_data.datatable_language
        });
        // Update search builder button text after draw.
        admin_class_list.on( 'draw', function() { jQuery( '.dtsb-button' ).text( 'Add filter' ); });
    }
    // Initialize DataTable for class list at frontend side.
    if (jQuery('#mjschool-class-list-frontend').length > 0) {
        var customCols = Array.isArray(mjschool_class_data.module_columns) ? mjschool_class_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#mjschool-class-list-frontend' ).DataTable({
            initComplete: function() {
                jQuery( ".mjschool-print-button" ).css( "margin-top", "-5%" );
            },
            ordering: true,
            dom: 'lifrtp',
            aoColumns: [
                mjschool_class_data.is_supportstaff ? { bSortable: false } : null,
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                mjschool_class_data.is_cust_class_room ? { bSortable: true } :null,
                ...customCols,
                { bSortable: false }
            ].filter(Boolean),
            language: mjschool_class_data.datatable_language
        });
    }
    // DataTable initialization.
    if(jQuery( '#frontend_section_list' ).length > 0) {
        var table = jQuery( '#frontend_section_list' ).DataTable({
            initComplete: function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            },
            dom: 'lifrtp',
            order: [[(mjschool_class_data.is_edit_access || mjschool_class_data.is_delete_access ) ? 2 : 1, "asc"]],
            aoColumns: [
                { "bSortable": true },
                { "bSortable": true },
                (mjschool_class_data.is_edit_access || mjschool_class_data.is_delete_access) ? { "bSortable": false } : null,
            ].filter(Boolean),
            language: mjschool_class_data.datatable_language
        });
    }
    // DataTable initialization for clasroom list at frontend side.
    if(jQuery( '#mjschool-classroom-list-front' ).length > 0) {
        jQuery( '#mjschool-classroom-list-front' ).DataTable({
            "initComplete": function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
                jQuery( '#mjschool-classroom-list-front th:first-child' ).removeClass( 'sorting_asc' );
            },
            responsive: true,
            "ordering": true,
            dom: 'Qlifrtp',
            "aoColumns": [
                { "bSortable": false, "className": 'sorting_disabled' },
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": false }
            ],
            language: mjschool_class_data.datatable_language
        });
    }
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_class_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    function mjschool_add_more_entry() {
        var click_val = jQuery( ".click_value").val();
        var curr_data = {
            action: 'mjschool_load_more_subject_information',
            click_val: click_val,
            nonce: mjschool.nonce,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function(response) {
            var value = parseInt(click_val) + 1;
            jQuery( ".click_value").val(value);
            jQuery(".more_info").append(response);
            jQuery(document).trigger("mjschool_subject_information_loaded");
        });
    }
    // Delete document AJAX.
    function mjschool_delete_parent_element(n) {
        var alertResult = confirm(language_translate2.delete_record_alert);
        if (alertResult) {
            n.parentNode.parentNode.parentNode.removeChild(n.parentNode.parentNode);
        }
    }
    jQuery(document).on( "change", "#class_list_subject", function() {
        jQuery( '#mjschool-class-section-subject' ).html( '' );
        jQuery( '#mjschool-class-section-subject' ).append( '<option value="remove">Loading..</option>' );
        var selection = jQuery( "#class_list_subject").val();
        var curr_data = {
            action: 'mjschool_load_class_section',
            class_id: selection,
            nonce: mjschool.nonce,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function(response) {
            jQuery( "#mjschool-class-section-subject option[value='remove']").remove();
            jQuery( '#mjschool-class-section-subject' ).append(response);
        });
        return false;
    });
    jQuery( "#class_list_subject").on( 'change', function () {
        var class_id = jQuery(this).val();
        jQuery( '#subject_student_subject' ).html( '<option value=""><?php esc_html_e( "Loading...", "mjschool" ); ?></option>' );
        jQuery.ajax({
            type: 'POST',
            url: mjschool.ajax, // Already available in your script.
            data: {
                action: 'mjschool_get_students_by_class',
                class_id: class_id
            },
            success: function (response) {
                jQuery( '#subject_student_subject' ).html(response);
                jQuery( '#subject_student_subject' ).multiselect( 'rebuild' ); // Rebuild multiselect UI.
            }
        });
    });
    jQuery( ".class_by_teacher_subject").on( 'click', function() {
        var class_list = jQuery( ".class_by_teacher_subject").val();
        jQuery( '#subject_teacher_subject' ).html( '' );
        var curr_data = {
            action: 'mjschool_load_teacher_by_class',
            class_list: class_list,
            nonce: mjschool.nonce,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function(response) {
            jQuery( "#subject_teacher_subject option[value='remove']").remove();
            jQuery( '#subject_teacher_subject' ).append(response);
            jQuery( '#subject_teacher_subject' ).multiselect( 'rebuild' );
            return false;
        });
    });
    jQuery( "#subject_teacher_subject").multiselect({
        nonSelectedText: mjschool_class_data.select_teacher,
        includeSelectAllOption: true,
        selectAllText: mjschool_class_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
        },
    });
    jQuery( "#subject_student_subject").multiselect({
        nonSelectedText: mjschool_class_data.select_student,
        includeSelectAllOption: true,
        selectAllText: mjschool_class_data.select_all,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        filterPlaceholder: mjschool_class_data.search_placeholder,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
        },
    });
    jQuery( ".mjschool-teacher-for-alert").on( 'click', function() {
        var checked = jQuery( ".form-check-input:checked").length;
        if (!checked) {
            alert(language_translate2.one_teacher_alert);
            return false;
        }
    });
    // Expose functions globally if needed.
    window.mjschool_add_more_entry = mjschool_add_more_entry;
    window.mjschool_delete_parent_element = mjschool_delete_parent_element;
    // Datepickers for meeting start and end dates for virtual classroom.
    jQuery( "#start_date").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() + 0);
            jQuery( "#end_date").datepicker( "option", "minDate", dt);
        }
    });
    jQuery( "#end_date").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() + 0);
            jQuery( "#start_date").datepicker( "option", "maxDate", dt);
        }
    });
    // Subject syllabus file validation at frontend side.
    jQuery('#subject_syllabus-frontend').on('change', function () {
        var val = jQuery(this).val().toLowerCase();
        var regex = /(.*?)\.(docx|doc|pdf|ppt|jpg|jpeg|png|xls|xlsx|pptx|gif)jQuery/;
        if (!regex.test(val)) {
            jQuery(this).val('');
            alert(mjschool_class_data.subject_file_alert_text);
        }
    });
    // Initialize multiselect.
    jQuery( "#subject_teacher_subject_front").multiselect({
        nonSelectedText: mjschool_class_data.select_class,
        includeSelectAllOption: true,
        selectAllText: mjschool_class_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
        },
    });
    jQuery( "#mjschool-subject-list-front").multiselect({
        nonSelectedText: mjschool_class_data.select_subject,
        includeSelectAllOption: true,
        selectAllText: mjschool_class_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
        },
    });
    jQuery( '#class_name' ).multiselect({
        nonSelectedText: mjschool_class_data.select_class,
        includeSelectAllOption: true,
        selectAllText: mjschool_class_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
        },
    });
    // jQuery( "#mjschool-subject-list").multiselect({
    //     nonSelectedText: mjschool_class_data.select_subject,
    //     includeSelectAllOption: true,
    //     selectAllText: mjschool_class_data.select_all,
    //     templates: {
    //         button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
    //     },
    // });

});
