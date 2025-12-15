jQuery(document).ready(function () {
    "use strict";
    jQuery( '#leave_form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    // CSV Export Validation.
    jQuery(document).on( "click", ".leave_csv_selected", function () {
        if (jQuery( '.selected_leave:checked' ).length === 0) {
            alert(language_translate2.one_record_select_alert);
            return false;
        }
    });
    // Datepicker initialization.
    jQuery( '#leave_date' ).datepicker({
        dateFormat: mjschool_leave_data.date_format,
        changeMonth: true,
        changeYear: true,
        yearRange: '-65:+0'
    });
    const dateFormat = mjschool_leave_data.date_format;
    const $start = jQuery( "#report_sdate");
    const $end = jQuery( "#report_edate");
    $start.datepicker({
        dateFormat: dateFormat,
        changeYear: true,
        changeMonth: true,
        maxDate: 0,
        onSelect: function(selected) {
            const dt = new Date(selected);
            dt.setDate(dt.getDate( ) );
            $end.datepicker( "option", "minDate", dt);
        }
    });
    $end.datepicker({
        dateFormat: dateFormat,
        changeYear: true,
        changeMonth: true,
        maxDate: 0,
        onSelect: function(selected) {
            const dt = new Date(selected);
            dt.setDate(dt.getDate( ) );
            $start.datepicker( "option", "maxDate", dt);
        }
    });
    var customCols = Array.isArray(mjschool_leave_data.module_columns) ? mjschool_leave_data.module_columns.map(() => ({ bSortable: true })) : [];
    // DataTable initialization.
    jQuery( '#leave_list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        order: [[6, "desc"]],
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
            { bSortable: true },
            ...customCols,
            { bSortable: false }
        ],
        language: mjschool_leave_data.datatable_language
    });
    // DataTable initialization for leave list at frontend side.
    if (jQuery('#frontend_leave_list').length > 0) {
        var customCols = Array.isArray(mjschool_leave_data.module_columns) ? mjschool_leave_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#frontend_leave_list' ).DataTable({
            "order": [[5, "desc"]],
            "dom": 'lifrtp',
            "aoColumns": [
                {"bSortable": false},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                ...customCols,
                (mjschool_leave_data.is_edit_access || mjschool_leave_data.is_delete_access) ? {"bSortable": false} : null,
            ].filter(Boolean),
            language: mjschool_leave_data.datatable_language
        });
    }
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_leave_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    // Datepicker initialization at frontend side.
    var start = new Date();
    var end = new Date(new Date().setYear(start.getFullYear() + 1 ) );
    jQuery( ".leave_start_date").datepicker({
        dateFormat: dateFormat,
        changeYear: true,
        changeMonth: true,
        minDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() + 0);
            jQuery( ".leave_end_date").datepicker( "option", "minDate", dt);
        },
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        }
    });
    jQuery( ".leave_end_date").datepicker({
        dateFormat: dateFormat,
        changeYear: true,
        changeMonth: true,
        minDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() - 0);
            jQuery( ".leave_start_date").datepicker( "option", "maxDate", dt);
        },
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        }
    });
});