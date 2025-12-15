jQuery(document).ready(function () {
    "use strict";
    jQuery.validationEngineLanguage.allRules["roll_id_format"] = {
        "regex": /^[A-Za-z0-9_/-]+$/,
        "alertText": "* Only letters, numbers, and ' _  - ' characters are allowed"
    };
    // ===== Validation rules for mobile number. =====
    jQuery.validationEngineLanguage.allRules["user_mobile"] = {
        regex: /^(\+?\d{10,15})$/,
        alertText: "* Only numbers are allowed"
    };
    // ---------- Prevent Spaces. ----------
    jQuery( '.space_validation' ).on( 'keypress', function(e){ if(e.which === 32 ) return false; });
    // DataTable initialization.
    jQuery( '#attendance_list' ).DataTable({
        responsive: true,
        order: [[0, "DESC"]],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'print', title: 'View Attendance' },
            { extend: 'pdf', title: 'View Attendance' },
            'csv'
        ],
        aoColumns: [
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: false }
        ],
        language: mjschool_users_data.datatable_language
    });
    // Initialize validation engine.
    jQuery( '#mjschool-upload-form , #mjschool-student-form , #teacher_form, #mjschool-upload-form, #parent_form, #mjschool_setting_form, #failed_report, #student_attendance' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    jQuery( '#birth_date' ).datepicker({
        dateFormat: mjschool_users_data.date_format,
        maxDate: 0,
        changeMonth: true,
        changeYear: true,
        yearRange: '-65:+25',
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        },
        onChangeMonthYear: function(year, month, inst) {
            jQuery(this).val(month + "/" + year);
        }
    });
    // Redundant mjschool-upload-form validationEngine call removed for clarity.
    jQuery( '#exam_list' ).DataTable({
        responsive: true,
        aoColumns: [
            { bSortable: true },
            { bSortable: false }
        ],
        language: mjschool_users_data.datatable_language
    });
    // ===== View more details toggle for student and parent. =====
    jQuery( ".view_more_details_div").on( "click", ".view_more_details", function() {
        jQuery( '.view_more_details_div' ).removeClass( "d-block").addClass( "d-none");
        jQuery( '.view_more_details_less_div' ).removeClass( "d-none").addClass( "d-block");
        jQuery( '.mjschool-user-more-details' ).removeClass( "d-none").addClass( "d-block");
    });
    jQuery( ".view_more_details_less_div").on( "click", ".view_more_details_less", function() {
        jQuery( '.view_more_details_div' ).removeClass( "d-none").addClass( "d-block");
        jQuery( '.view_more_details_less_div' ).removeClass( "d-block").addClass( "d-none");
        jQuery( '.mjschool-user-more-details' ).removeClass( "d-block").addClass( "d-none");
    });
    // DataTable initialization.
    jQuery( '#parents_list' ).DataTable({
        responsive: true,
        order: [[0, "DESC"]],
        aoColumns: [
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true }
        ],
        language: mjschool_users_data.datatable_language
    });
    if (jQuery('#students_list').length > 0) {
        jQuery('#students_list').DataTable({
            bProcessing: true,
            bServerSide: true,
            sAjaxSource: ajaxurl + '?action=mjschool_student_list&nonce=' + mjschool_users_data.datatable_nonce,
            bDeferRender: true,
            initComplete: function (settings, json) {
                jQuery('.table-responsive').show();
                jQuery(".loader").hide();
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
                jQuery('#students_list th:first-child').removeClass('sorting_asc');
            },
            dom: 'lifrtp',
            ordering: true,
            aoColumns: [
                { bSortable: false }, // Checkbox.
                { bSortable: false }, // User image.
                { bSortable: true },  // Name & Email.
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            language: mjschool_users_data.datatable_language
        });
    }
    jQuery( '#mjschool-parents-list-detail-page' ).DataTable({
        //stateSave: true,
        responsive: true,
        order: [[1, "asc"]],
        dom: 'lifrtp',
        aoColumns: [
            { bSortable: false },
            { bSortable: false },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true }
        ],
        language: mjschool_users_data.datatable_language
    });
    jQuery( '#mjschool-feespayment-list-detailpage , #mjschool-attendance-list-detail-page' ).DataTable({
        order: [[1, "desc"]],
        aoColumns: [
            { bSortable: false },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true },
            { bSortable: true }
        ],
        dom: 'lifrtp',
        language: mjschool_users_data.datatable_language
    });
    jQuery( '#leave_list' ).DataTable({
        order: [[6, "desc"]],
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
        dom: 'lifrtp',
        language: mjschool_users_data.datatable_language
    });
    if (jQuery('#mjschool-hall-ticket-detailpage, #mjschool-hall-ticket-detailpage-front').length > 0) {
        jQuery('#mjschool-hall-ticket-detailpage, #mjschool-hall-ticket-detailpage-front').DataTable({
            order: [[1, "desc"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            dom: 'lifrtp',
            language: mjschool_users_data.datatable_language
        });
    }
    if (jQuery('#mjschool-homework-detailpage, #mjschool-homework-detailpage-front').length > 0) {
        jQuery('#mjschool-homework-detailpage, #mjschool-homework-detailpage-front').DataTable({
            order: [[1, "desc"]],
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
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            dom: 'lifrtp',
            language: mjschool_users_data.datatable_language
        });
    }
    if (jQuery('#mjschool-issuebook-detailpage, #mjschool-issuebook-detailpage-front').length > 0) {
        jQuery('#mjschool-issuebook-detailpage, #mjschool-issuebook-detailpage-front').DataTable({
            order: [[1, "desc"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            dom: 'lifrtp',
            language: mjschool_users_data.datatable_language
        });
    }
    if (jQuery('#mjschool-messages-detailpage-for-exam, #mjschool-messages-detailpage-for-exam-front').length > 0) {
        jQuery('#mjschool-messages-detailpage-for-exam, #mjschool-messages-detailpage-for-exam-front').DataTable({
            stateSave: true,
            order: [[1, "desc"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: false }
            ],
            dom: 'lifrtp',
            language: mjschool_users_data.datatable_language
        });
    }
    if (jQuery('#mjschool-messages-detailpage, #mjschool-messages-detailpage-front').length > 0) {
        jQuery('#mjschool-messages-detailpage, #mjschool-messages-detailpage-front').DataTable({
            order: [[1, "desc"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            dom: 'lifrtp',
            language: mjschool_users_data.datatable_language
        });
    }
    jQuery( '#mjschool-attendance-list-detail-page-for-teacher' ).DataTable({
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true }
        ],
        dom: 'lifrtp',
        language: mjschool_users_data.datatable_language
    });
    jQuery( '#mjschool-class-list-detail-page' ).DataTable({
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": false }
        ],
        dom: 'lifrtp',
        language: mjschool_users_data.datatable_language
    });
    // DataTable initialization for child list in view parent.
    if (jQuery('#mjschool-child-list-for-parent, #mjschool-parents-child-list-detail-page-front').length > 0) {
        jQuery('#mjschool-child-list-for-parent, #mjschool-parents-child-list-detail-page-front').DataTable({
            responsive: true,
            "order": [[1, "asc"]],
            dom: 'lifrtp',
            "aoColumns": [
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true }
            ],
            language: mjschool_users_data.datatable_language
        });
    }
    var customCols = Array.isArray(mjschool_users_data.module_columns) ? mjschool_users_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#teacher_list').length > 0) {
        var teacherTable = jQuery( '#teacher_list' ).DataTable({
            initComplete: function(settings, json){
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            },
            responsive: true,
            dom: 'lifrtp',
            ordering: true,
            aoColumns: [
                { bSortable: false },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                { bSortable: false }
            ],
            language: mjschool_users_data.datatable_language
        });
    }
    // Initialize dataTable.
    var customCols = Array.isArray(mjschool_users_data.module_columns) ? mjschool_users_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#supportstaff_list').length > 0) {
        jQuery( '#supportstaff_list' ).DataTable({
            initComplete: function(settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
            },
            responsive: true,
            dom: 'lifrtp',
            order: [[2, "DESC"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                { bSortable: false }
            ],
            language: mjschool_users_data.datatable_language
        });
    }
    var customCols = Array.isArray(mjschool_users_data.module_columns) ? mjschool_users_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#parent_list').length > 0) {
        var table = jQuery( '#parent_list' ).DataTable({
            initComplete: function(settings, json) {
                jQuery( '.table-responsive' ).show();
                jQuery( ".loader").hide();
                jQuery( ".mjschool-print-button" ).css( "margin-top", "-55px");
                jQuery( '#parent_list th:first-child' ).removeClass( 'sorting_asc' );
            },
            responsive: true,
            dom: 'lifrtp',
            ordering: true,
            aoColumns: [
                { bSortable: false },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                { bSortable: false }
            ],
            language: mjschool_users_data.datatable_language
        });
    }
    // Attendance Teacher List DataTable at frontend side.
    if (jQuery('#attendance_teacher_list').length > 0) {
        jQuery('#attendance_teacher_list').DataTable({
            //stateSave: true,
            order: [[0,'asc']],
            dom: 'lifrtp',
            aoColumns: [
                { bSortable:false },
                { bSortable:true },
                { bSortable:true },
                { bSortable:true },
                { bSortable:false }
            ],
            language: mjschool_users_data.datatable_language
        });
    }
    // Teacher List DataTable at front side.
    var customCols = Array.isArray(mjschool_users_data.module_columns) ? mjschool_users_data.module_columns.map(() => ({ bSortable: true })) : [];
    if (jQuery('#front_teacher_list').length > 0) {
        jQuery('#front_teacher_list').DataTable({
            initComplete: function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-5%" });
            },
            ordering: true,
            dom: 'lifrtp',
            aoColumns: [
                mjschool_users_data.is_supportstaff ? { bSortable: false } : [],
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                ...(mjschool_users_data.is_supportstaff || mjschool_users_data.is_teacher ? [{ bSortable: true }, { bSortable: false }] : []),
            ].filter(Boolean),
            language: mjschool_users_data.datatable_language
        });
    }
    // DataTable initialization for attendance list at front side.
    if(jQuery('#mjschool-attendance-list-detail-page-front').length > 0) {
        jQuery('#mjschool-attendance-list-detail-page-front').DataTable({
            "aoColumns": [
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true }
            ],
            dom: 'lifrtp',
            language: mjschool_users_data.datatable_language
        });
    }
    // DataTable initialization for class list at front side.
    jQuery( '#mjschool-class-list-detail-page-front' ).DataTable({
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": false }
        ],
        dom: 'lifrtp',
        language: mjschool_users_data.datatable_language
    });
    // Initialize DataTable.
    if (jQuery('#supportstaff_list_front').length > 0) {
        var customCols = Array.isArray(mjschool_users_data.module_columns) ? mjschool_users_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#supportstaff_list_front' ).DataTable({
            order: [[2, "asc"]],
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                ...customCols,
                ...(mjschool_users_data.is_supportstaff || mjschool_users_data.is_teacher ? [{ bSortable: true }, { bSortable: false }] : []),
            ].filter(Boolean),
            language: mjschool_users_data.datatable_language
        });
    }
    function initDataTable(selector, columns, order=[[0,'asc']], dom='lifrtp', buttons=null) {
        var options = {
            "initComplete": function (settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "order": order,
            "dom": dom,
            "aoColumns": columns,
            language: mjschool_users_data.datatable_language
        };
        if(buttons) options.buttons = buttons;
        return jQuery(selector).DataTable(options);
    }
    var customstudentCols = Array.isArray(mjschool_users_data.module_columns) ? mjschool_users_data.module_columns.map(() => ({ bSortable: true })) : [];
    initDataTable('#students_list_front', [
        mjschool_users_data.is_supportstaff ? {"bSortable": false} :[],
        {"bSortable": false}, {"bSortable": true}, {"bSortable": true}, {"bSortable": true},
        {"bSortable": true}, {"bSortable": true}, {"bSortable": true}, {"bSortable": true},
        ...customstudentCols,
        {"bSortable": false}
    ]);
    // Initialize DataTable parent list at front in student view.
    if(jQuery( '#mjschool-parents-list-detail-page-front' ).length > 0) {
        jQuery( '#mjschool-parents-list-detail-page-front' ).DataTable({
            //stateSave: true,
            order: [[1, "asc"]],
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            language: mjschool_users_data.datatable_language
        });
    }
    // Initialize DataTable fees payment at front in student view.
    if(jQuery( '#mjschool-feespayment-list-detailpage-front, #mjschool-attendance-list-detail-page' ).length > 0) {
        jQuery( '#mjschool-feespayment-list-detailpage-front, #mjschool-attendance-list-detail-page' ).DataTable({
            order: [[1, "desc"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            dom: 'lifrtp',
            language: mjschool_users_data.datatable_language
        });
    }
    // Initialize DataTable for leave list in student view at front side.
    if(jQuery( '#leave_list_front' ).length > 0) {
        jQuery( '#leave_list_front' ).DataTable({
            order: [[6, "desc"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            dom: 'lifrtp',
            language: mjschool_users_data.datatable_language
        });
    }
    // Parent list datatable at front side.
    if (jQuery('#parent_list_front') > 0) {
        alert('sdfsf');
        var customCols = Array.isArray(mjschool_users_data.module_columns) ? mjschool_users_data.module_columns.map(() => ({ bSortable: true })) : [];
        jQuery( '#parent_list_front' ).DataTable({
            "initComplete": function() {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "dom": 'lifrtp',
            "ordering": true,
            "aoColumns": [
                mjschool_users_data.is_supportstaff ? { "bSortable": false } :null,
                { "bSortable": false },
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                ...customCols,
                { "bSortable": false }
            ].filter(Boolean),
            language: mjschool_users_data.datatable_language
        });
    }
    // Add placeholder to search box.
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_users_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    // ===== Custom search for class pattern. =====
    jQuery.fn.dataTable.ext.search.push(function(settings, data){
        const searchValue = jQuery( '.dataTables_filter input' ).val()?.toLowerCase().trim();
        if(!searchValue) return true;
        let matchFound = false;
        const classMatch = searchValue.match(/^class\s+(\d+)$/);
        if(classMatch){
            const classToMatch = `class ${classMatch[1]}`;
            const classColumn = data[3].toLowerCase().split( ',' ).map(cls => cls.trim( ) );
            matchFound = classColumn.includes(classToMatch);
        } else {
            for(let i=0;i<data.length;i++){
                if(data[i].toLowerCase().includes(searchValue ) ){
                    matchFound = true;
                    break;
                }
            }
        }
        return matchFound;
    });
    // Print ID card handlers.
    jQuery(document).on( "click", ".mjschool-print-id-card, .print_standard_id_card", function() {
        if (jQuery( '.check_for_id:checked' ).length === 0) {
            alert(language_translate2.one_record_select_alert);
            return false;
        }
    });
    // Add more document AJAX.
    window.mjschool_add_more_document = function() {
        var curr_data = {
            action: 'mjschool_load_more_document',
            nonce: mjschool.nonce,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function(response) {
            jQuery( ".mjschool-more-document").append(response);
        });
    };
    // Add more siblings AJAX.
    window.mjschool_add_more_siblings = function() {
        var click_val = jQuery( ".click_value").val();
        var curr_data = {
            action: 'mjschool_load_siblings_dropdown',
            nonce: mjschool.nonce,
            click_val: click_val,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function(response) {
            var value = parseInt(click_val, 10) + 1;
            jQuery( ".click_value").val(value);
            jQuery("#mjschool-sibling-div").append(response);
            jQuery(document).trigger("mjschool_sibling_dropdown_loaded");
        });
    };
    //Delete document AJAX.
    window.mjschool_delete_parent_element = function(n) {
        if (confirm(language_translate2.delete_record_alert ) ) {
            n.parentNode.parentNode.parentNode.removeChild(n.parentNode.parentNode);
        }
    };
    // File validation for allowed types and size.
    var allowedTypes = mjschool_common_data.document_type_json;
    var maxFileSizeMB = mjschool_common_data.document_size; // Maximum file size in MB.
    var maxFileSizeBytes = maxFileSizeMB * 1024 * 1024;
    jQuery(document).on( "change", ".file_validation[type=file]", function() {
        var val = jQuery(this).val().toLowerCase();
        var regexPattern = "(.*?)\\.( " + allowedTypes.join( "|") + ")$";
        var regex = new RegExp(regexPattern);
        var fileInput = jQuery(this)[0];
        var file = fileInput.files[0];
        if (!regex.test(val ) ) {
            jQuery(this).val( '' );
            alert( 'Only ' + allowedTypes.join( ', ' ) + ' formats are allowed.' );
        } else if (file && file.size > maxFileSizeBytes) {
            jQuery(this).val( '' );
            alert( 'Too large file size. Only files smaller than ' + maxFileSizeMB + 'MB can be uploaded.' );
        }
    });
    // Alert if no class selected in multiselect.
    jQuery(document).on( "click", ".mjschool-class-for-alert", function() {
        const checked = jQuery( ".mjschool-multiselect-validation-class .dropdown-menu input:checked").length;
        if (!checked) {
            alert(language_translate2.one_class_select_alert);
            return false;
        }
    });

    // ===== Multiselect Class. =====
    jQuery( '#class_name' ).multiselect({
        nonSelectedText: mjschool_users_data.select_class,
        includeSelectAllOption: true,
        selectAllText: mjschool_users_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        }
    });
    // CSV export button click validation for teacher.
    jQuery(document).on( "click", ".teacher_csv_export_alert", function(){
        if(jQuery( '.selected_teacher:checked' ).length === 0){
            alert(language_translate2.one_record_select_alert);
            return false;
        }
    });
    // CSV export button click validation for staff.
    jQuery(document).on( "click", ".staff_csv_selected", function() {
        if (jQuery( '.selected_staff:checked' ).length === 0) {
            alert(language_translate2.one_record_select_alert);
            return false;
        }
    });
    // CSV export button click validation for parent.
    jQuery(document).on( "click", ".mjschool-parent-csv-selected", function() {
        if (jQuery( '.mjschool-selected-parent:checked' ).length === 0) {
            alert(language_translate2.one_record_select_alert);
            return false;
        }
    });
    // ===== View more details toggle for teacher. =====
    jQuery( ".view_more_details_div").on( "click", ".view_more_details", function(){
        jQuery( '.view_more_details_div' ).addClass( "d-none").removeClass( "d-block");
        jQuery( '.view_more_details_less_div' ).addClass( "d-block").removeClass( "d-none");
        jQuery( '.mjschool-user-more-details' ).addClass( "d-block").removeClass( "d-none");
    });
    jQuery( ".view_more_details_less_div").on( "click", ".view_more_details_less", function(){
        jQuery( '.view_more_details_div' ).addClass( "d-block").removeClass( "d-none");
        jQuery( '.view_more_details_less_div' ).addClass( "d-none").removeClass( "d-block");
        jQuery( '.mjschool-user-more-details' ).addClass( "d-none").removeClass( "d-block");
    });
    // Function to check duplicate child.
    function mjschool_check_duplicates(changedSelect) {
        let selectedValues = [];
        jQuery( ".mjschool-parents-child select").each(function() {
            let val = jQuery(this).val();
            if (val) {
                selectedValues.push(val);
            }
        });
        jQuery( ".mjschool-parents-child select").each(function() {
            let currentSelect = jQuery(this);
            let currentValue = currentSelect.val();
            if ( currentValue && selectedValues.filter(value => value === currentValue).length > 1 ) {
                if (currentSelect.is(changedSelect ) ) {
                    alert(mjschool_users_data.select_child_alert_text);
                    currentSelect.val( "");
                }
            }
        });
    }
    // When a new child is selected.
    jQuery(document).on( "click", ".mjschool-parents-child select", function() {
        mjschool_check_duplicates(jQuery(this ) );
    });
    // ADD CHILD — keep this function globally accessible.
    window.mjschool_add_Child = function() {
        "use strict";
        var curr_data = {
            action: 'mjschool_load_child_dropdown',
            nonce: mjschool.nonce,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function(response) {
            jQuery( "#mjschool-parents-child").append(response);
        });
    };
    // REMOVE CHILD — keep this function globally accessible.
    window.mjschool_delete_parent_elementChild = function(n) {
        "use strict";
        var alertConfirm = confirm(language_translate2.delete_record_alert);
        if (alertConfirm === true) {
            n.closest( '.row' ).remove(); // Cleaner removal of parent row.
        }
    };
    // Prepare QR code data.
    var qr_code_data = JSON.stringify({
        user_id: mjschool_users_data.student_id,
        class_id: mjschool_users_data.class_id,
        section_id: mjschool_users_data.section_name,
        qr_type: 'schoolqr'
    });
    // Generate QR code URL.
    var url = 'https://api.qrserver.com/v1/create-qr-code/?data=' + qr_code_data + '&amp;size=50x50';
    // Set QR code image src.
    jQuery('.mjschool-id-card-barcode').attr('src', url);
    
    // // Datepickers.
    // jQuery( '.sdate, .edate' ).datepicker({
    //     dateFormat: mjschool_users_data.date_format,
    //     changeMonth: true,
    //     changeYear: true,
    //     maxDate: 0,
    //     beforeShow: function(textbox, instance){
    //         instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
    //     }
    // });
    // Multiselect.
    jQuery( '#class_id' ).multiselect({
        nonSelectedText: mjschool_users_data.select_class,
        includeSelectAllOption:true,
        selectAllText: mjschool_users_data.select_all,
        templates: {
            button:'<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        }
    });
    jQuery( '#subject_teacher' ).multiselect({
        nonSelectedText: mjschool_users_data.select_teacher,
        includeSelectAllOption:true,
        selectAllText: mjschool_users_data.select_all,
        templates: {
            button:'<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        }
    });
    // File check at front side.
    window.mjschool_file_check = function(obj){
        var fileExtension = ['jpeg','jpg','png','bmp',''];
        if(jQuery.inArray(jQuery(obj).val().split( '.' ).pop().toLowerCase(),fileExtension)==-1){
            alert(language_translate2.image_forame_alert);
            jQuery(obj).val('');
        }
    };
    function toggleSiblingDiv() {
        if (jQuery( '#chkIsTeamLead' ).is( ':checked' ) ) {
            jQuery( '.mjschool-sibling-div_clss' ).removeClass( 'mjschool-sibling-div-none' ).addClass( 'mjschool-sibling-div_block' );
        } else {
            jQuery( '.mjschool-sibling-div_clss' ).removeClass( 'mjschool-sibling-div_block' ).addClass( 'mjschool-sibling-div-none' );
        }
    }
    toggleSiblingDiv();
    jQuery('#chkIsTeamLead').on('change', toggleSiblingDiv);
    jQuery( '.sdate, #sdate' ).datepicker({
        dateFormat: mjschool_users_data.date_format,
        changeMonth: true,
        changeYear: true,
        maxDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate( ) );
            jQuery( '.edate' ).datepicker( "option", "minDate", dt);
        }
    });
    jQuery( '.edate, #edate' ).datepicker({
        dateFormat: mjschool_users_data.date_format,
        changeMonth: true,
        changeYear: true,
        maxDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate( ) );
            jQuery( '.sdate' ).datepicker( "option", "maxDate", dt);
        }
    });
    jQuery( '.after_or_equal, .date_equals, .before_or_equal' ).datepicker({
        dateFormat: mjschool_users_data.date_format,
        changeMonth: true,
        changeYear: true,
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({marginTop: (-textbox.offsetHeight) + 'px'});
        }
    });
    // Hide remove button if only 1 item at front.
    var numItems = jQuery( '.mjschool-parents-child' ).length;
    if (numItems === 1) {
        jQuery( '#revove_item' ).hide();
    }
    $(".mjschool-sibling-trigger").each(function () {

        const id = $(this).data("id");

        // Prevent duplicate binding
        $(document).off("change", "#sibling_class_change_" + id);
        $(document).off("change", "#sibling_class_section_" + id);

        // CLASS → Load Students + Sections
        $(document).on("change", "#sibling_class_change_" + id, function () {

            let classID = $(this).val();
            let studentBox = $("#sibling_student_list_" + id);
            let sectionBox = $("#sibling_class_section_" + id);

            studentBox.html("");

            // --- load students (expects HTML <option> list) ---
            $.post(
                mjschool.ajax,
                {
                    action: "mjschool_load_user",
                    class_list: classID,
                    nonce: mjschool.nonce
                },
                function (response) {
                    try {
                        // response is HTML (options); append directly
                        studentBox.append(response);
                    } catch (err) {
                        console.error("Error appending students response:", err, response);
                    }
                },
                'html' // IMPORTANT: force HTML, not JSON
            );

            // --- load sections (expects HTML <option> list) ---
            sectionBox.html('<option value="remove">Loading...</option>');
            $.post(
                mjschool.ajax,
                {
                    action: "mjschool_load_class_section",
                    class_id: classID,
                    nonce: mjschool.nonce
                },
                function (response) {
                    try {
                        sectionBox.find("option[value='remove']").remove();
                        sectionBox.append(response);
                    } catch (err) {
                        console.error("Error appending sections response:", err, response);
                    }
                },
                'html' // IMPORTANT
            );
        });

        // SECTION → Load students
        $(document).on("change", "#sibling_class_section_" + id, function () {

            let sectionID = $(this).val();
            let classID = $("#sibling_class_change_" + id).val();
            let studentBox = $("#sibling_student_list_" + id);
            studentBox.html("");

            $.post(
                mjschool.ajax,
                {
                    action: "mjschool_load_section_user",
                    section_id: sectionID,
                    class_id: classID,
                    nonce: mjschool.nonce
                },
                function (response) {
                    try {
                        studentBox.append(response);
                    } catch (err) {
                        console.error("Error appending section-students response:", err, response);
                    }
                },
                'html' // IMPORTANT
            );

        });
    });
});