jQuery(document).ready(function () {
    "use strict";
    // Initialize validation engine.
    jQuery('#mjschool-homework-form-admin, #homework_form_tempalte, #class_form, #view_submition_form_front, #class_form_second').validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    // DataTable initialization for submission list in homework details.
    jQuery('#submission_list').DataTable({
        initComplete: function (settings, json) {
            jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
        },
        responsive: true,
        dom: 'lifrtp',
        ordering: true,
        aoColumns: [
            { bSortable: true },
            { bSortable: true },
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
        language: mjschool_homework_data.datatable_language
    });
    // Homework dataTable initialization.
    var customCols = Array.isArray(mjschool_homework_data.module_columns) ? mjschool_homework_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#homework_list').length > 0) {
        jQuery('#homework_list').DataTable({
            initComplete: function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
            },
            responsive: true,
            order: [[7, "desc"]],
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
                ...customCols,
                { bSortable: false }
            ],
            language: mjschool_homework_data.datatable_language
        });
    }
    var customCols = Array.isArray(mjschool_homework_data.module_columns) ? mjschool_homework_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#closed_homework_list').length > 0) {
        jQuery('#closed_homework_list').DataTable({
            initComplete: function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
            },
            responsive: true,
            order: [[7, "desc"]],
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
                ...customCols,
                { bSortable: false }
            ],
            language: mjschool_homework_data.datatable_language
        });
    }
    // DataTable initialization homework list at frontend side.
    if (jQuery('#mjschool-homework-list-front').length > 0) {
        var customCols = Array.isArray(mjschool_homework_data.module_columns) ? mjschool_homework_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery('#mjschool-homework-list-front').DataTable({
            "initComplete": function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-5%" });
            },
            "dom": 'lifrtp',
            "aoColumns": [
                mjschool_homework_data.is_supportstaff ? { "bSortable": false } : null,
                mjschool_homework_data.is_student || mjschool_homework_data.is_parent ? [{ "bSortable": false }, { "bSortable": true }, { "bSortable": true }, { "bSortable": true }] : null,
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
            language: mjschool_homework_data.datatable_language
        });
    }
    // DataTable initialization submission list at frontend side.
    if (jQuery('#frontend_submission_list').length > 0) {
        jQuery('#frontend_submission_list').DataTable({
            "initComplete": function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
            },
            "dom": 'lifrtp',
            "ordering": true,
            "aoColumns": [
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": false }
            ],
            language: mjschool_homework_data.datatable_language
        });
    }
    // DataTable initialization for homework list at frontend side.
    if (jQuery( '#homework_list_1' ).length > 0) {
        jQuery( '#homework_list_1' ).DataTable({
            "initComplete": function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({"margin-top": "-5%"});
            },
            "order": [[1, "asc"]],
            "aoColumns": [
                mjschool_homework_data.is_supportstaff ? {"bSortable": false} : null,
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": false}
            ].filter(Boolean),
            language: mjschool_homework_data.datatable_language
        });
    }
    // Add placeholder to search box.
        jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_homework_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    // Datepicker initialization.
    jQuery( '.datepicker' ).datepicker({
        minDate: 0,
        dateFormat: mjschool_homework_data.date_format,
        changeYear: true,
        changeMonth: true,
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        }
    });
    // Close admin notice message.
    jQuery( '.notice-dismiss' ).on( 'click', function() {
        jQuery( '#mjschool-message' ).hide();
    });
    // This stays outside the document.ready but still inside the file.
    jQuery(document).on( "change", ".input-file", function() {
        "use strict";
        var file = this.files[0];
        var ext = jQuery(this).val().split( '.' ).pop().toLowerCase();
        // Extension Check.
        if (jQuery.inArray(ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'gif', 'png', 'jpg', 'jpeg']) == -1) {
            alert( mjschool_homework_data.subject_file_alert_text + ext + mjschool_homework_data.not_format_alert_text );
            jQuery(this).replaceWith( '<input type="file" name="file" class="form-control validate[required] input-file">' );
            return true;
        }
        // File Size Check.
        if (file.size > 20480000) {
            alert(language_translate2.large_file_size_alert);
            jQuery(this).replaceWith( '<input type="file" name="file" class="form-control validate[required]">' );
            return false;
        }
    });
    jQuery(document).on( 'click', '.save_homework', function() {
        var val = jQuery( ".file-validation").val();
        if (val === '' ) {
            alert( 'Upload Your Homework' );
            return false;
        }
    });
});