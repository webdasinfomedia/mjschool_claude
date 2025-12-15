jQuery(document).ready(function () {
    "use strict";
    // Initialize form validation engine.
    jQuery( '#book_form, #issue_book_form, #bookissue_form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    // Datepicker initialization.
    jQuery( '.datepicker' ).datepicker({
        dateFormat: mjschool_library_data.date_format,
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        beforeShow: function (textbox, instance) {
            instance.dpDiv.css({
                marginTop: (-textbox.offsetHeight) + 'px'
            });
        }
    });
    // Multiselect initialization.
    jQuery( '#book_list1' ).multiselect({
        nonSelectedText: mjschool_library_data.select_book,
        includeSelectAllOption: true,
        selectAllText: mjschool_library_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        }
    });
    // Select student class wise (Class → Section).
    jQuery(document).on( "change", "#class_list_lib", function () {
        jQuery( '#class_section_lib' ).html( '<option value="remove">Loading..</option>' );
        var curr_data = {
            action: 'mjschool_load_class_section',
            class_id: jQuery(this).val(),
            nonce: mjschool.nonce,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function (response) {
            jQuery( "#class_section_lib option[value='remove']").remove();
            jQuery( '#class_section_lib' ).append(response);
        });
    });
    // Section → Student list.
    jQuery( "#class_section_lib").on( 'change', function () {
        var selection = jQuery(this).val();
        if (selection !== '' ) {
            jQuery( '#student_list' ).html( '' );
            var curr_data = {
                action: 'mjschool_load_section_user',
                section_id: selection,
                nonce: mjschool.nonce,
                dataType: 'json'
            };
            jQuery.post(mjschool.ajax, curr_data, function (response) {
                jQuery( '#student_list' ).append(response);
            });
        }
    });
    // Book category → Book list.
    jQuery( "#bookcat_list").on( 'change', function () {
        jQuery( "#book_list1 option[value]").remove();
        var curr_data = {
            action: 'mjschool_load_books',
            bookcat_id: jQuery(this).val(),
            nonce: mjschool.nonce,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function (response) {
            jQuery( '#book_list1' ).append(response);
            jQuery( '#book_list1' ).multiselect( 'rebuild' );
        });
    });
    // CSV export button click validation.
    jQuery(document).on( "click", ".book_csv_selected_alert", function(){
        if(jQuery( '.selected_book:checked' ).length === 0){
            alert(language_translate2.one_record_select_alert);
            return false;
        }
    });
    jQuery( '#mjschool-issue-list' ).DataTable({
        initComplete: function () {
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
            { bSortable: false }
        ],
        language: mjschool_library_data.datatable_language
    });
    var table = jQuery( '#user_issue_list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
        },
        dom: 'lifrtp',
        order: [[2, "asc"]],
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
        language: mjschool_library_data.datatable_language
    });
    var customCols = Array.isArray(mjschool_library_data.module_columns) ? mjschool_library_data.module_columns.map(() => ({ bSortable: true })) : [];
    jQuery( '#book_list' ).DataTable({
        initComplete: function () {
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
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            ...customCols,
            { bSortable: false }
        ],
        language: mjschool_library_data.datatable_language
    });
    if (jQuery('#member_list') > 0) {
        jQuery('#member_list').DataTable({
            dom: 'lifrtp',
            order: [[1, "asc"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: false }
            ],
            language: mjschool_library_data.datatable_language
        });
    }
    // DataTable for mjschool-liabrary-book-list at frontend side.
    if (jQuery('#mjschool-liabrary-book-list').length > 0) {
        var customCols = Array.isArray(mjschool_library_data.module_columns) ? mjschool_library_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#mjschool-liabrary-book-list' ).DataTable({
            initComplete: function() {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            //stateSave: true,
            dom: 'lifrtp',
            order: [[2, "asc"]],
            aoColumns: [
                mjschool_library_data.is_supportstaff ? { bSortable: false } : null,
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
            language: mjschool_library_data.datatable_language
        });
    }
    // DataTable initialization user issue list second at frontend side.
    if (jQuery('#user_issue_list_second').length > 0) {
        jQuery('#user_issue_list_second').DataTable({
            "initComplete": function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-5%" });
            },
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
                { "bSortable": false }
            ],
            language: mjschool_library_data.datatable_language
        });
    }
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_library_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    
    var selection = jQuery( ".issue_period").val();
    if (selection === '' ) {
        return false;
    }
    var curr_data = {
        action: 'mjschool_get_book_return_date',
        issue_period: selection,
        nonce: mjschool.nonce,
        issue_date: jQuery( "#issue_date").val()
    };
    jQuery.post(mjschool.ajax, curr_data, function (response) {
        jQuery( '#return_date' ).val(response);
    });

});