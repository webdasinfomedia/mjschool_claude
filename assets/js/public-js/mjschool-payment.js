jQuery(document).ready(function () {
    "use strict";
    // User multiselect.
    jQuery( '#selected_users' ).multiselect({
        nonSelectedText: mjschool_payment_data.select_user,
        includeSelectAllOption: true,
        selectAllText: mjschool_payment_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
        },
    });
    // Tax multiselect.
    jQuery( '.tax_charge' ).multiselect({
        nonSelectedText: mjschool_payment_data.select_tax,
        includeSelectAllOption: true,
        allSelectedText: mjschool_payment_data.all_selected,
        selectAllText: mjschool_payment_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
        },
        buttonContainer: '<div class="dropdown" />'
    });
     // Start datepicker.
    jQuery( "#start_date_event").datepicker({
        dateFormat: mjschool_payment_data.date_format,
        changeMonth: true,
        changeYear: true,
        onSelect: function (selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() + 0);
            jQuery( "#end_date_event").datepicker( "option", "minDate", dt);
        },
        beforeShow: function (textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        }
    });
    // End datepicker.
    jQuery( "#end_date_event").datepicker({
        dateFormat: mjschool_payment_data.date_format,
        changeMonth: true,
        changeYear: true,
        onSelect: function (selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() - 0);
            jQuery( "#start_date_event").datepicker( "option", "maxDate", dt);
        },
        beforeShow: function (textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        }
    });
    // Multiselect.
    jQuery( "#fees_data").multiselect({
        nonSelectedText: mjschool_payment_data.select_fees_type,
        includeSelectAllOption: true,
        selectAllText: mjschool_payment_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
        }
    });
    // Year check.
    jQuery( '#end_year' ).on( 'change', function () {
        var end_value = parseInt(jQuery( '#end_year' ).val(), 10);
        var start_value = parseInt(jQuery( '#start_year option:selected' ).attr( "id"), 10);
        if (start_value > end_value) {
            jQuery( '#end_year' ).prop( 'selectedIndex', 0); // Reset selection.
            alert(language_translate2.starting_year_alert);
            return false;
        }
    });
    // -------------------------
    // Year validation.
    // -------------------------
    jQuery(document).on( "change", "#end_date_event", function () {
        let end_value   = parseInt(jQuery( '#end_date_event option:selected' ).val( ) );
        let start_value = parseInt(jQuery( '#start_date_event option:selected' ).attr( "id" ) );
        if (start_value > end_value) {
            jQuery( "#end_date_event").val( '' );
            alert(language_translate2.lower_starting_year_alert);
        }
    });
    const checkbox = document.getElementById( "certificate_header");
    const printBtn = document.getElementById( "exprience_latter");
    const pdfBtn = document.getElementById( "download_pdf");

    function getUpdatedURL(link) {
        const url = new URL(link.href, window.location.origin);
        if (checkbox && checkbox.checked) {
            url.searchParams.set( "certificate_header", "1");
        } else {
            url.searchParams.delete( "certificate_header");
        }
        return url.toString();
    }
    if (printBtn) {
        printBtn.addEventListener( "click", function (e) {
            e.preventDefault(); // Stop default <a> behavior.
            const newUrl = getUpdatedURL(printBtn);
            window.open(newUrl, "_blank"); // Open updated URL.
        });
    }
    if (pdfBtn) {
        pdfBtn.addEventListener( "click", function (e) {
            e.preventDefault(); // Stop default <button> behavior if needed.
            const newUrl = getUpdatedURL(pdfBtn);
            window.open(newUrl, "_blank");
        });
    }
    // DataTable initialization.
    jQuery( '#recurring_fees_paymnt_list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            jQuery( '#recurring_fees_paymnt_list th:first-child' ).removeClass( 'sorting_asc' );
        },
        responsive: true,
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
            { bSortable: false }
        ],
        language: mjschool_payment_data.datatable_language
    });
    // DataTable initialization.
    var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
    if(jQuery( '#feetype_list' ).length > 0) {
        jQuery( '#feetype_list' ).DataTable({
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
                { "bSortable": true },
                ...customCols,
                { "bSortable": false }
            ],
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for tax list.
    var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#tax_list').length > 0) {
        jQuery('#tax_list').DataTable({
            initComplete: function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
                jQuery('#mjschool-class-list th:first-child').removeClass('sorting_asc');
            },
            ordering: true,
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false, className: 'sorting_disabled' },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                { bSortable: false }
            ],
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for tax list at frontend side.
    if (jQuery('#frontend_tax_list').length > 0) {
        var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#frontend_tax_list' ).DataTable({
            initComplete: function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({"margin-top": "-5%"});
            },
            ordering: true,
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false, className: 'sorting_disabled' },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                (mjschool_payment_data.is_edit_access || mjschool_payment_data.is_delete_access) ? { bSortable: false } : null,
            ].filter(Boolean),
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for income table at frontend side.
    if (jQuery('#tblincome').length > 0) {
        var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#tblincome' ).DataTable({
            "initComplete": function() {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "order": [[4, "Desc"]],
            "dom": 'lifrtp',
            "aoColumns": [
                mjschool_payment_data.is_supportstaff ? { "bSortable": false } : null,
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customCols,
                { "bSortable": false }
            ].filter(Boolean),
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for expence list at frontend side.
    if (jQuery('#tblexpence-frontend').length > 0) {
        var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#tblexpence-frontend' ).DataTable({
            "initComplete": function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "order": [[2, "Desc"]],
            "dom": 'lifrtp',
            "aoColumns": [
                mjschool_payment_data.is_supportstaff ? { "bSortable": false } : null,
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customCols,
                { "bSortable": false }
            ].filter(Boolean),
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for expense at admin side.
    if (jQuery('#tblexpence').length > 0) {
        var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#tblexpence' ).DataTable({
            initComplete: function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            },
            order: [[2, "desc"]],
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                { bSortable: false }
            ],
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for income at admin side.
    if (jQuery('#mjschool-tbl-income-admin').length > 0) {
        var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#mjschool-tbl-income-admin' ).DataTable({
            initComplete: function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            },
            responsive: true,
            order: [[4, "desc"]],
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                { bSortable: false }
            ],
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for income at admin side.
    if (jQuery('#paymentt_list_receipt').length > 0) {
        var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#paymentt_list_receipt' ).DataTable({
            initComplete: function () {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            dom: 'lifrtp',
            aoColumns: [
                mjschool_payment_data.is_supportstaff ? { bSortable: false } : null,
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
            language: mjschool_payment_data.datatable_language
        });
    }
    // -------------------------
    // Fee type list DataTable at frontend side.
    // -------------------------
    if (jQuery('#frontend_feetype_list').length > 0) {
        var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#frontend_feetype_list' ).DataTable({
            initComplete: function () {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            order: [[2, "asc"]],
            dom: 'lifrtp',
            aoColumns: [
                mjschool_payment_data.is_supportstaff ? { bSortable: false } : null,
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                (mjschool_payment_data.is_edit_access || mjschool_payment_data.is_delete_access) ? { bSortable: false } : null,
            ].filter(Boolean),
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for recurring fees paymnt list at frontend side.
    if (jQuery('#frontend_recurring_fees_paymnt_list').length > 0) {
        var customCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery('#frontend_recurring_fees_paymnt_list').DataTable({
            initComplete: function () {
                jQuery(".mjschool-print-button").css({ "margin-top": "-5%" });
            },
            responsive: true,
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                (mjschool_payment_data.is_edit_access || mjschool_payment_data.is_delete_access) ? { bSortable: false } : null
            ].filter(Boolean),
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for payment receipt at frontend side.
    if (jQuery('#feetype_list_receipt').length > 0) {
        var customreceiptCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#feetype_list_receipt' ).DataTable({
            "initComplete": function () {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            },
            responsive: true,
            "order": [[2, "asc"]],
            "dom": 'lifrtp',
            "aoColumns": [
                { "bSortable": false },
                { "bSortable": false },
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customreceiptCols,
                { "bSortable": false }
            ].filter(Boolean),
            language: mjschool_payment_data.datatable_language
        });
    }
    // DataTable initialization for payment list at admin side.
    if (jQuery('#fee_paymnt').length > 0) {
        var custompaymentCols = Array.isArray(mjschool_payment_data.module_columns) ? mjschool_payment_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery('#fee_paymnt').DataTable({
            "initComplete": function () {
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
                jQuery('#fee_paymnt th:first-child').removeClass( 'sorting_asc' );
            },
            responsive: true,
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
                { "bSortable": true },
                { "bSortable": true },
                ...custompaymentCols,
                { "bSortable": false }
            ],
            language: mjschool_payment_data.datatable_language
        });
    }
    // Search placeholder.
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_payment_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    // Reminder confirmation.
    jQuery( '#fees_reminder' ).on( 'click', function () {
        if (jQuery( '.select-checkbox:checked' ).length === 0) {
            alert(language_translate2.one_record_select_alert);
            return false;
        }
        return confirm( "<?php esc_html_e( 'Are you sure you want to send a mail reminder?', 'mjschool' ); ?>");
    });
    // Single reminder alert.
    jQuery( '#fees_reminder_single' ).on( 'click', function () {
        alert(language_translate2.mail_reminder);
        return true;
    });
    // Validation.
    jQuery( '#expense_form, #income_form, #payment_form, #tax_form, #invoice_form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    // Datepicker.
    jQuery( '#invoice_date' ).datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: mjschool_payment_data.date_format,
        yearRange: '-65:+25',
        beforeShow: function (textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        },
        onChangeMonthYear: function (year, month) {
            jQuery(this).val(month + "/" + year);
        }
    });

    let blank_expense_entry = '';
    // Create blank expense entry template.
    blank_expense_entry = '<div class="mjschool-padding-top-15px-res form-body mjschool-user-form mjschool-income-feild">' +
        '<div class="row">' +
            '<div class="col-md-3">' +
                '<div class="form-group input">' +
                    '<div class="col-md-12 form-control">' +
                        '<input id="income_amount" class="form-control mjschool-btn-top validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="" name="income_amount[]">' +
                        '<label for="userinput1" class="active">' + mjschool_payment_data.expense_amount_label + '<span class="required">*</span></label>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-md-3">' +
                '<div class="form-group input">' +
                    '<div class="col-md-12 form-control">' +
                        '<input id="income_entry" class="form-control mjschool-btn-top validate[required,custom[description_validation]] text-input" maxlength="50" type="text" value="" name="income_entry[]">' +
                        '<label for="userinput1" class="active">' + mjschool_payment_data.expense_entry_label + '<span class="required">*</span></label>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-md-2 mjschool-symptoms-dropdown-div">' +
                '<img src="' + mjschool_payment_data.delete_icon + '" onclick="mjschool_delete_parent_element(this)" class="mjschool-rtl-margin-top-15px">' +
            '</div>' +
        '</div>' +
    '</div>';
    
    let blank_income_entry = '';
    blank_income_entry = '' +
    '<div class="mjschool-padding-top-15px-res form-body mjschool-user-form mjschool-income-feild">' +
        '<div class="row">' +
            '<div class="col-md-3">' +
                '<div class="form-group input">' +
                    '<div class="col-md-12 form-control">' +
                        '<input id="income_amount" class="form-control mjschool-btn-top validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="" name="income_amount[]">' +
                        '<label for="userinput1" class="active">' + mjschool_payment_data.income_amount_label + '<span class="required">*</span></label>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-md-3 col-9">' +
                '<div class="form-group input">' +
                    '<div class="col-md-12 form-control">' +
                        '<input id="income_entry" class="form-control mjschool-btn-top validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="" name="income_entry[]">' +
                        '<label for="userinput1" class="active">' + mjschool_payment_data.income_entry_label + '<span class="required">*</span></label>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-md-2 col-3 mjschool-symptoms-dropdown-div">' +
                '<img src="' + mjschool_payment_data.delete_icon + '" onclick="mjschool_delete_parent_element(this)" class="mjschool-rtl-margin-top-15px">' +
            '</div>' +
        '</div>' +
    '</div>';

    // Make the add and delete functions globally accessible.
    window.mjschool_add_entry = function() {
        jQuery( "#expense_entry_main").append(blank_expense_entry);
    };
    // Add new income entry.
    window.mjschool_add_entry = function() {
        jQuery( "#income_entry_main").append(blank_income_entry);
    };
    window.mjschool_delete_parent_element = function(el) {
        var confirmDelete = confirm(language_translate2.delete_record_alert);
        if (confirmDelete) {
            jQuery(el).closest( '.mjschool-income-feild' ).remove();
        }
    };

    window.blank_expense_entry = jQuery('#expense_entry').html();
    // -------------------------
    // Global functions at frontend side.
    // -------------------------
    window.mjschool_add_entry = function(){
        jQuery( "#expense_entry").append(window.blank_expense_entry);
    };
    window.mjschool_delete_parent_element = function(n){
        alert(language_translate2.do_delete_record);
        n.closest( 'tr' ).remove();
    };
    // -------------------------
    // Load fees on class change.
    // -------------------------
    jQuery(document).on( "change", ".load_fees_front", function(){
        jQuery( '#fees_data' ).html( '' );
        jQuery.post(mjschool.ajax, {
            action: 'mjschool_load_class_fee_type',
            class_list: jQuery( "#fees_class_list_id").val(),
            nonce: mjschool.nonce,
            dataType: 'json'
        }, function(response){
            jQuery( '#fees_data' ).html(response);
            initMultiselect( '#fees_data' );
            jQuery( '#fees_data' ).multiselect( 'rebuild' );
        });
    });

    // -------------------------
    // Load fee amount at frontend.
    // -------------------------
    jQuery(document).on( 'change', '#fees_data', function(){
        jQuery.post(mjschool.ajax, {
            action: 'mjschool_load_fee_type_amount',
            fees_id: jQuery(this).val(),
            nonce: mjschool.nonce,
            dataType: 'json'
        }, function(response){
            jQuery( "#fees_amount").val(response);
        });
    });
    
});