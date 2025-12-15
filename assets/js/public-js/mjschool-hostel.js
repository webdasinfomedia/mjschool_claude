jQuery(document).ready(function () {
    "use strict";
    jQuery( '#room_form, #mjschool-bed-form, #bed_form_new, #hostel_form, #mjschool-hostel-form-fornt' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    jQuery( '#room_list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({"margin-top": "-55px"});
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
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: false }
        ],
        language: mjschool_hostel_data.datatable_language
    });
    jQuery( '#mjschool-bed-list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        // stateSave: true,
        responsive: true,
        dom: 'lifrtp',
        order: [[2, "asc"]],
        aoColumns: [
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": false }
        ],
        language: mjschool_hostel_data.datatable_language
    });
    var customCols = Array.isArray(mjschool_hostel_data.module_columns) ? mjschool_hostel_data.module_columns.map(() => ({ bSortable: true })) : [];
    jQuery( '#hostel_list' ).DataTable({
        "initComplete": function (settings, json) {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        "dom": 'lifrtp',
        "order": [[2, "asc"]],
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            ...customCols,
           (mjschool_hostel_data.is_edit_access || mjschool_hostel_data.is_delete_access) ? { bSortable: false } : null
        ].filter(Boolean),
        language: mjschool_hostel_data.datatable_language
    });
    // DataTable initialization for hostel list at frontend side.
    if (jQuery('#mjschool-hostel-list-frontend').length > 0) {
        var customCols = Array.isArray(mjschool_hostel_data.module_columns) ? mjschool_hostel_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#mjschool-hostel-list-frontend' ).DataTable({
            "initComplete": function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            //stateSave: true,
            "dom": 'lifrtp',
            "order": [[2, "asc"]],
            "aoColumns": [
                mjschool_hostel_data.is_supportstaff ? { "bSortable": false } : null,
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customCols,
                { "bSortable": false }
            ],
            language: mjschool_hostel_data.datatable_language
        });
    }
    // DataTable initialization for room list at frontend side.
    if(jQuery( '#frontend_room_list' ).length > 0) {
        jQuery( '#frontend_room_list' ).DataTable({
            "initComplete": function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            },
            // responsive: true,
            "dom": 'lifrtp',
            "order": [[2, "asc"]],
            "aoColumns": [
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                (mjschool_hostel_data.is_supportstaff || mjschool_hostel_data.is_teacher) ? { "bSortable": true } : null,
                (mjschool_hostel_data.is_add_access || mjschool_hostel_data.is_edit_access || mjschool_hostel_data.is_delete_access) ? { "bSortable": false } : null,
            ].filter(Boolean),
            language: mjschool_hostel_data.datatable_language
        });
    }
    // DataTable initialization for bed list at frontend side.
    if (jQuery('#mjschool-bed-list-frontend').length > 0) {
        jQuery('#mjschool-bed-list-frontend').DataTable({
            "initComplete": function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
            },
            "dom": 'lifrtp',
            "order": [[2, "asc"]],
            "aoColumns": [
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": false }
            ],
            language: mjschool_hostel_data.datatable_language
        });
    }
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_hostel_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    // Datepicker initialization.
    jQuery( '.datepicker' ).datepicker({
        defaultDate: null,
        changeMonth: true,
        changeYear: true,
        yearRange: '-75:+10',
        dateFormat: mjschool_hostel_data.date_format
    });
    // Function for check select value.
    function mjschool_check_select_value(value, i) {
        jQuery( '#assigndate_' + i).hide();
        jQuery( '.students_list_' + i).removeClass( 'student_check' );
        jQuery( ".student_check").each(function () {
            var valueSelected = jQuery(this).val();
            if (valueSelected == value) {
                alert(language_translate2.select_different_student_alert);
                jQuery( '.students_list_' + i).val( '0' );
                return false;
            }
        });
        var selectedVal = jQuery( '.students_list_' + i).val();
        if (selectedVal === '0' ) {
            jQuery( '#assigndate_' + i).hide();
            var name = 0;
            jQuery( ".new_class_var").each(function () {
                if (jQuery(this).val() !== '0' ) {
                    name++;
                }
            });
            if (name < 1) {
                jQuery( "#Assign_bed").prop( "disabled", true);
            }
        } else {
            jQuery( '#assigndate_' + i).show();
            jQuery( "#Assign_bed").prop( "disabled", false);
        }
        jQuery( '.students_list_' + i).addClass( 'student_check' );
    }
    // Add/remove validation dynamically on student select change.
    jQuery(document).on( 'change', '.student_check', function () {
        let index = jQuery(this).data( 'index' );
        if (jQuery( '#students_list_' + index).val() != 0) {
            jQuery( '#assign_date_' + index).addClass( 'validate[required]' );
        } else {
            jQuery( '#assign_date_' + index).removeClass( 'validate[required]' );
        }
    });
    // Expose function to global scope if needed elsewhere.
    window.mjschool_check_select_value = mjschool_check_select_value;
    jQuery( ".mjschool-assign-room-for-alert").on( 'click', function () {
        var select_student = jQuery( ".select_student").val();
        if (select_student === "0") {
            alert(language_translate2.one_assign_room__alert);
            return false;
        }
        return true;
    });
});