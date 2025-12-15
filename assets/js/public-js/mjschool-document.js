jQuery(document).ready(function () {
    "use strict";
    jQuery( '#document_form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    jQuery( '.mjschool-onlyletter-number-space-validation' ).on( 'keypress', function (e) {
        var regex = /^[0-9a-zA-Z \b]+$/;
        var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (!regex.test(key ) ) {
            e.preventDefault();
            return false;
        }
    });
    // ==========================
    // DataTable Initialization.
    // ==========================
    var customCols = Array.isArray(mjschool_document_data.module_columns) ? mjschool_document_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#document_list').length > 0) {
        jQuery( '#document_list' ).DataTable({
            "initComplete": function () {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            },
            responsive: true,
            "order": [[2, "asc"]],
            "dom": 'lifrtp',
            "aoColumns": [
                { "bSortable": false },
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                mjschool_document_data.is_school ? { bSortable: true }:null,
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customCols,
                { "bSortable": false }
            ].filter(Boolean),
            language: mjschool_document_data.datatable_language
        });
    }
    // DataTable initialization.
    if (jQuery('#frontend_document_list').length > 0) {
        var customCols = Array.isArray(mjschool_document_data.module_columns) ? mjschool_document_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#frontend_document_list' ).DataTable({
            initComplete: function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            // stateSave: true,
            order: [[2, "asc"]],
            dom: 'lifrtp',
            aoColumns: [
                mjschool_document_data.is_delete_access ? { "bSortable": false } : null,
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
            ].filter(Boolean),
            language: mjschool_document_data.datatable_language
        });
    }
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_document_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    
    // File type validation at frontend.
    function mjschool_file_check(obj) {
        var allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
        var fileVal = jQuery(obj).val().split( '.' ).pop().toLowerCase();
        if (jQuery.inArray(fileVal, allowedExtensions) === -1) {
            alert( mjschool_document_data.front_doc_alert_text);
            jQuery(obj).val('');
        }
    }
    // Expose file check function globally (so it works on `onchange` attributes in HTML).
    window.mjschool_file_check = mjschool_file_check;
});