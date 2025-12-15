jQuery(document).ready(function () {
    "use strict";
    // Initialize validation engine.
    jQuery( '#transport_form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    var customCols = Array.isArray(mjschool_transport_data.module_columns) ? mjschool_transport_data.module_columns.map(() => ({ bSortable: true })) : [];
    // DataTable initialization.
    jQuery( '#transport_list' ).DataTable({
        initComplete: function(settings, json) {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        //stateSave: true,
        responsive: true,
        order: [[2, "asc"]],
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
            ...customCols,
            { bSortable: false }
        ],
        language: mjschool_transport_data.datatable_language
    });
    var customCols = Array.isArray(mjschool_transport_data.module_columns) ? mjschool_transport_data.module_columns.map(() => ({ bSortable: true })) : [];
    // Transport list at frontend side.
    jQuery( '#frontend_transport_list' ).DataTable({
        initComplete: function(settings, json) {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
        },
        // stateSave: true,
        dom: 'lifrtp',
        aoColumns: [
            // <? php if($role_name == 'supportstaff') { ?> { "bSortable": false }, <? php } ?>
            mjschool_transport_data.is_supportstaff ? { "bSortable": false } : null,
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            ...customCols,
            { "bSortable": false }
        ].filter(Boolean),
        language: mjschool_transport_data.datatable_language
    });
    // Add placeholder to search box.
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_transport_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
});