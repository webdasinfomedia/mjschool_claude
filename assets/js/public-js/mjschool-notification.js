jQuery(document).ready(function () {
    "use strict";
    // Initialize validation engine.
    jQuery( "#notice_form, #mjschool-message-form, #mjschool-message-replay, #notification_form, #event_form, #holiday_form, #holiday_form_template").validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    // Start Datepicker initialization.
    jQuery("#notice_Start_date, #start_date_event, #date").datepicker({
        dateFormat: mjschool_notification_data.date_format,
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        onSelect: function (selected) {
            var dt = new Date(selected);
            jQuery( "#notice_end_date, #end_date_event").datepicker( "option", "minDate", dt);
        },
        beforeShow: function (textbox, instance) {
            instance.dpDiv.css({
                marginTop: (-textbox.offsetHeight) + "px"
            });
        }
    });
    // End Datepicker initialization.
    jQuery( "#notice_end_date, #end_date_event, #start_date_event, #end_date_new").datepicker({
        dateFormat: mjschool_notification_data.date_format,
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        onSelect: function (selected) {
            var dt = new Date(selected);
            jQuery( "#notice_Start_date").datepicker( "option", "maxDate", dt);
        },
        beforeShow: function (textbox, instance) {
            instance.dpDiv.css({
                marginTop: (-textbox.offsetHeight) + "px"
            });
        }
    });
    // Hover binding for datepicker arrows.
    jQuery(document).on( 'mouseenter mouseleave', ".ui-datepicker-next, .ui-datepicker-prev", function(e) {
        jQuery(this).toggleClass( "hover", e.type === "mouseenter");
    });
    // DataTable initialization for notice.
    var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
    jQuery( '#notice_list' ).DataTable({
        "initComplete": function(settings, json) {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        "dom": 'lifrtp',
        "order": [[4, "DESC"]],
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
        language: mjschool_notification_data.datatable_language
    });
    // DataTable initialization for message inbox.
    jQuery( "#inbox_list").DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        dom: "lifrtp",
        order: [[1, "asc"]],
        aoColumns: [
            { bSortable: false },
            { bSortable: false },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true }
        ],
        language: mjschool_notification_data.datatable_language
    });
    // Initialize DataTable for message sent.
    var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
    jQuery( "#sent_list").DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        responsive: true,
        dom: "lifrtp",
        order: [[1, "asc"]],
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
        ],
        language: mjschool_notification_data.datatable_language
    });
    // DataTable initialization for sent list at frontend side.
    var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#frontend_sent_list').length > 0) {
        jQuery( '#frontend_sent_list' ).DataTable({
            // stateSave: true,
            order: [[1, "asc"]],
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
            ],
            language: mjschool_notification_data.datatable_language
        });
    }
    // DataTable initialization for notce at frontend side.
    var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#frontend_notice_list').length > 0) {
        jQuery( '#frontend_notice_list' ).DataTable({
            "initComplete": function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "order": [[4, "DESC"]],
            "dom": 'lifrtp',
            "aoColumns": [
                mjschool_notification_data.is_supportstaff ? { "bSortable": false } : null,
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
            language: mjschool_notification_data.datatable_language
        });
    }
    // DataTable initialization for notification.
    var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
    jQuery( '#notification_list' ).DataTable({
        "initComplete": function(settings, json) {
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
            ...customCols,
            (mjschool_notification_data.is_delete_access ? { "bSortable": false } : null)
        ].filter(Boolean),
        language: mjschool_notification_data.datatable_language
    });
    // DataTable initialization for notification at frontend side.
    var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#frontend_notification_list').length > 0) {
        jQuery( '#frontend_notification_list' ).DataTable({
            "initComplete": function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "dom": 'lifrtp',
            "order": [[2, "asc"]],
            "aoColumns": [
                mjschool_notification_data.is_supportstaff ? { "bSortable": false } : null,
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customCols,
                ((mjschool_notification_data.is_edit_access || mjschool_notification_data.is_delete_access) ? { "bSortable": false } : null)
            ],
            language: mjschool_notification_data.datatable_language
        });
    }
    // Event list DataTable for event list at frontend side.
    var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#frontend_event_list').length > 0) {
        jQuery( '#frontend_event_list' ).DataTable({
            initComplete: function() {
                jQuery( ".mjschool-print-button" ).css({"margin-top": "-5%"});
            },
            stateSave: true,
            dom: 'lifrtp',
            aoColumns: [
                mjschool_notification_data.is_supportstaff ? { "bSortable": false } : null,
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
            language: mjschool_notification_data.datatable_language
        });
    }
    var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
    jQuery( '#event_list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            jQuery( '#event_list th:first-child' ).removeClass( 'sorting_asc' );
        },
        dom: 'lifrtp',
        aoColumns: [
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
        language: mjschool_notification_data.datatable_language
    });
    // Extend DataTables with custom UK date sort.
    var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
        "date-uk-pre": function (a) {
            return moment(a, mjschool_notification_data.date_format_for_sorting ).unix();
        },
        "date-uk-asc": function (a, b) {
            return a - b;
        },
        "date-uk-desc": function (a, b) {
            return b - a;
        }
    });
    // Initialize DataTable.
    jQuery( '#holiday_list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        // stateSave: true,
        responsive: true,
        columnDefs: [
            {
                targets: 4, // Apply sorting to 5th column.
                type: "date-uk",
                render: function (data) {
                    return moment(data, mjschool_notification_data.date_format_for_sorting ) .format( mjschool_notification_data.date_format_for_sorting );
                }
            }
        ],
        order: [[4, 'asc']],
        dom: 'lifrtp',
        aoColumns: [
            { "bSortable": false },
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            ...customCols,
           (mjschool_notification_data.is_edit_access || mjschool_notification_data.is_delete_access) ? { bSortable: false } : null
        ].filter(Boolean),
        language: mjschool_notification_data.datatable_language
    });
    // DataTable initialization inbox list at frontend side.
    jQuery( '#frontend_inbox_list' ).DataTable({
        dom: 'lifrtp',
        order: [[1, "asc"]],
        sSearch: "<i class='fa fa-search'></i>",
        aoColumns: [
            { bSortable: false },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: false }
        ],
        language: mjschool_notification_data.datatable_language
    });
    // DataTable initialization for holiday list at frontend side.
    if (jQuery('#frontend_holiday_list').length > 0) {
        var customCols = Array.isArray(mjschool_notification_data.module_columns) ? mjschool_notification_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#frontend_holiday_list' ).DataTable({
            "initComplete": function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "columnDefs": [{
                "targets": 4,
                "type": "date-uk",
                "render": function(data) {
                    return moment(data, mjschool_notification_data.date_format_for_sorting ) .format( mjschool_notification_data.date_format_for_sorting );
                }
            }],
            "dom": 'lifrtp',
            "order": [[4, "asc"]],
            "aoColumns": [
                mjschool_notification_data.is_supportstaff ? { "bSortable": false } : null,
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customCols,
                (mjschool_notification_data.is_edit_access || mjschool_notification_data.is_delete_access) ? { bSortable: false } : null
            ].filter(Boolean),
            language: mjschool_notification_data.datatable_language
        });
    }
    // Add placeholder to search box.
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_notification_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    // User Multiselect.
    jQuery( "#selected_users").multiselect({
        nonSelectedText: mjschool_notification_data.select_user,
        includeSelectAllOption: true,
        selectAllText: mjschool_notification_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        }
    });
    // Class Multiselect.
    jQuery( "#selected_class").multiselect({
        nonSelectedText: mjschool_notification_data.select_class,
        includeSelectAllOption: true,
        selectAllText: mjschool_notification_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        }
    });
    // Validation for save message selected user.
    jQuery(document).on( "click", ".mjschool-save-message-selected-user", function () {
        var class_selection_type = jQuery( ".class_selection_type").val();
        if (class_selection_type === "multiple") {
            var checked = jQuery( ".mjschool-multiselect-validation1 .dropdown-menu input:checked").length;
            if (!checked) {
                alert(language_translate2.one_class_select_alert);
                return false;
            }
        }
    });
    jQuery("span.timeago").timeago();
    // Remove Bootstrap form-control from multiselect search.
    jQuery( '.multiselect-search' ).removeClass( 'form-control', 0);
    // Global helper functions.
    window.mjschool_add_new_attachment = function () {
        jQuery( ".mjschool-attachment-div").append(
            '<div class="row">' +
                '<div class="col-md-10">' +
                    '<div class="form-group input">' +
                        '<div class="col-md-12 form-control mjschool-res-rtl-height-50px">' +
                            '<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" for="photo">'+mjschool_notification_data.attachment_text+'</label>' +
                            '<div class="col-sm-12">' +
                                '<input class="col-md-12 form-control file mjschool-file-validation" name="message_attachment[]" type="file" />' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-sm-2">' +
                    '<input type="image" onclick="mjschool_delete_attachment(this)" src="'+mjschool_notification_data.delete_icon+'" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate doc_label mjschool-float-right mjschool-input-btn-height-width">' +
                '</div>' +
            '</div>'
        );
    };
    window.mjschool_delete_attachment = function (el) {
        jQuery(el).closest( ".row").remove();
    };
    // Check if at least one user is selected before reply.
    jQuery(document).on( "click", "#check_reply_user", function () {
        if (jQuery( ".dropdown-menu input:checked").length === 0) {
            alert(mjschool_notification_data.reply_user_alert);
            return false;
        }
    });
    // Show reply box & hide button.
    jQuery(document).on( "click", "#replay_message_btn", function () {
        jQuery( ".replay_message_div").show();
        jQuery( ".replay_message_btn").hide();
    });
    // Attachment validation
    window.mjschool_add_new_attachment_view = function() {
    var attachmentHTML = '<div class="row">' +
            '<div class="col-md-10">' +
                '<div class="form-group input">' +
                    '<div class="col-md-12 form-control mjschool-res-rtl-height-50px">' +
                        '<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" for="photo">'+mjschool_notification_data.attachment_text+'</label>' +
                        '<div class="col-sm-12">' +
                            '<input class="col-md-12 input-file mjschool-file-validation file" name="message_attachment[]" type="file" />' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-sm-2">' +
                '<input type="image" onclick="mjschool_delete_attachment(this)" src="'+mjschool_notification_data.delete_icon+'" class="mjschool-remove-certificate mjschool-rtl-margin-top-15px doc_label mjschool-float-right mjschool-input-btn-height-width">' +
            '</div>' +
        '</div>';
        jQuery( ".mjschool-attachment-div").append(attachmentHTML);
    };
    // Initialize timepicker.
    mdtimepicker( '.timepicker', {
        events: {
            timeChanged: function (data) {
                // You can handle time change here if needed.
            }
        },
        theme: 'purple',
        readOnly: false
    });
     // File input validation for message docs at frontend side.
    jQuery(document).on( "change", ".input-file[type=file]", function() {
        var file = this.files[0];
        var ext  = jQuery(this).val().split( '.' ).pop().toLowerCase();
        // Extension Check.
        if (jQuery.inArray(ext, ['pdf','doc','docx','xls','xlsx','ppt','pptx','gif','png','jpg','jpeg']) === -1) {
            alert( mjschool_notification_data.subject_file_alert_text + ext + mjschool_notification_data.not_format_alert_text );
            jQuery(this).replaceWith( '<input class="mjschool-btn-top input-file" name="message_attachment[]" type="file" />' );
            return false;
        }
        // File Size Check.
        if (file.size > 20480000) {
            alert(language_translate2.large_file_size_alert);
            jQuery(this).replaceWith( '<input class="mjschool-btn-top input-file" name="message_attachment[]" type="file" />' );
            return false;
        }
    });
    var dateFormat = mjschool_notification_data.date_format;
    jQuery( '#s_date' ).datepicker({
        dateFormat: dateFormat,
        changeMonth: true,
        changeYear: true,
        minDate: 0,
        onSelect: function(selected) {
            var dt = jQuery.datepicker.parseDate(dateFormat, selected);
            jQuery( "#end_date").datepicker( "option", "minDate", dt);
        }
    });
    jQuery( '#end_date' ).datepicker({
        dateFormat: dateFormat,
        changeMonth: true,
        changeYear: true,
        minDate: 0,
        onSelect: function(selected) {
            var dt = jQuery.datepicker.parseDate(dateFormat, selected);
            jQuery( "#s_date").datepicker( "option", "maxDate", dt);
        }
    });
    // Timepicker initialization at frontend.
    if (jQuery( '.mjschool-timepicker' ).length) {
        mdtimepicker( '.mjschool-timepicker', {
            events: {
                timeChanged: function(data) {
                    // You can hook custom logic here.
                }
            },
            theme: 'purple',
            readOnly: false
        });
    }
    // File validation engine at frontend side.
    window.mjschool_file_check = function(obj) {
        var fileExtension = ['pdf', 'doc', 'jpg', 'jpeg', 'png'];
        var ext = jQuery(obj).val().split( '.' ).pop().toLowerCase();
        if (jQuery.inArray(ext, fileExtension) === -1) {
            alert( mjschool_notification_data.front_doc_alert_text);
            jQuery(obj).val( '' );
        }
    };
    if (jQuery(".mjschool-date-error-trigger").length > 0) {
        alert(mjschool_notification_data.start_end_date_alert_text);
    }
});