jQuery(document).ready(function () {
    "use strict";
    jQuery( '#curr_date' ).datepicker({
        maxDate: '0',
        dateFormat: mjschool_attendance_data.date_format,
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({
                marginTop: (-textbox.offsetHeight) + 'px'
            });
        }
    });
    // Datepicker Initialization Function.
    function initDatepicker(selector) {
        jQuery(selector).datepicker({
            maxDate: '0',
            dateFormat: mjschool_attendance_data.date_format,
            changeYear: true,
            changeMonth: true,
            beforeShow: function (textbox, instance) {
                instance.dpDiv.css({
                    marginTop: (-textbox.offsetHeight) + 'px'
                });
            }
        });
    }
    initDatepicker( '#curr_date_for_index' );
    initDatepicker( '#curr_date_subject' );
    initDatepicker('#curr_date_teacher');
    // Start date picker.
    jQuery( "#report_sdate").datepicker({
        dateFormat: mjschool_attendance_data.date_format,
        changeYear: true,
        changeMonth: true,
        maxDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate( ) );
            jQuery( "#report_edate").datepicker( "option", "minDate", dt);
        }
    });
    // End date picker.
    jQuery( "#report_edate").datepicker({
        dateFormat: mjschool_attendance_data.date_format,
        changeYear: true,
        changeMonth: true,
        maxDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate( ) );
            jQuery( "#report_sdate").datepicker( "option", "maxDate", dt);
        }
    });
    jQuery( '#curr_date_sub' ).datepicker({
        maxDate: 0,
        changeYear: true,
        changeMonth: true,
        dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>"
    });
    jQuery( '#curr_date_teacher_front' ).datepicker({
        maxDate: 0,
        dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
        changeYear: true,
        changeMonth: true,
        beforeShow: function(input, inst) {
            inst.dpDiv.css({ marginTop: -input.offsetHeight + 'px' });
        }
    });
    // Validation Engine Initializations.
    jQuery( '#student_attendance, #subject_attendance, #teacher_attendance, #class_form, #mjschool-upload-form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });

    // Multiselect Initialization.
    jQuery( "#subject_teacher").multiselect({
        nonSelectedText: mjschool_attendance_data.select_teacher,
        includeSelectAllOption: true,
        selectAllText: mjschool_attendance_data.select_all,
        templates: {
            button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
        }
    });
    function onQRCodeScanned(result) {
        const result_obj = JSON.parse(result);
        var user_id = result_obj.user_id;
        var user_class_id = result_obj.class_id;
        
        var user_section_id = result_obj.section_id;
        var qr_code_name = result_obj.qr_type;

        let scanner = jQuery("#scanner");
        const ajaxURL = scanner.data("ajax-url");
        const msgSuccess = scanner.data("success-text");
        const msgSuccessText = scanner.data("success-msg");
        const msgErrorClass = scanner.data("error-class");
        const msgErrorStudent = scanner.data("error-student");
        const msgErrorCommon = scanner.data("error-common");
        const msgWarnDate = scanner.data("warning-date");
        const msgWarnClass = scanner.data("warning-class");
        const msgWarnClassEmpty = scanner.data("warning-class-empty");
        const msgErrorCamera = scanner.data("error-camera");
        const msgInvalidQR = scanner.data("error-invalid");
        
        if (qr_code_name === 'schoolqr' )
        {
            var selected_class_id = jQuery( ".mjschool_qr_class_id").val();
            var selected_class_section = jQuery( ".mjschool-qr-class-section").val();
            var selected_class_subject = jQuery( ".mjschool-qr-class-subject").val();
            var qr_date = jQuery( ".qr_date").val();
            var attendance_url=user_id+'_'+user_class_id+'_'+qr_date+'_'+user_section_id+'_'+selected_class_id+'_'+selected_class_subject+'_'+selected_class_section;
            var serch = attendance_url.search( "data");
            if(user_class_id !== " ")
            {
                if(user_class_id === selected_class_id && selected_class_id !== "")
                {
                    if(qr_date !== " ")
                    {
                        var myString = attendance_url.substr(attendance_url.indexOf( "=") + 1)
                        jQuery.ajax({
                            type: "POST",  
                            url: ajaxURL,
                            data: { action: 'mjschool_qr_code_take_attendance',attendance_url:myString},
                            dataType: "json",
                            complete: function (e)
                            {
                                if(e.responseText === 1)
                                {
                                    swal( msgSuccess, msgSuccessText, "success");
                                    return true;
                                }
                                else if(e.responseText === 2 )
                                {
                                    swal( "Oops!", msgErrorClass, "error");
                                    return true;
                                }
                                else if(e.responseText === 3)
                                {
                                    swal( "Oops!", msgErrorStudent, "error");
                                    return true;
                                }
                                else
                                {
                                    swal( "Oops!", msgErrorCommon, "error");
                                    return true;
                                }
                            }
                        });	
                    }
                    else
                    {
                    
                        swal( "Warning!", msgWarnDate, "warning");
                        return true;
                    }
                }
                else
                {
                    swal( "Warning!", msgWarnClass, "warning");
                    return true;
                }
            }
            else
            {
                swal( "Warning!", msgWarnClassEmpty, "warning");
                return true;
            }
        
        }
        else if (result === 'Invalid constraint' )
        {
        }
        else if (result === 'Requested device not found' )
        {
            swal( "Oops!", msgErrorCamera, "error"); 
            return true;
            
        }
        else
        {
            swal( "Oops!", msgInvalidQR, "error"); 
            return true;
        }
    }
    jQuery(document).ready(function() {
        const html5QrCode = new Html5Qrcode( "scanner");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            onQRCodeScanned
        );
    });
    // Initialize DataTable for student attendance.
    jQuery( '#attend_list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
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
            { bSortable: true }
        ],
        language: mjschool_attendance_data.datatable_language
    });
    // Initialize DataTable for teacher attendance.
    var teacher_attendance_table;
    teacher_attendance_table = jQuery( '#teacher_attendance_list' ).DataTable({
        initComplete: function () {
            jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
        },
        dom: 'lifrtp',
        aoColumns: [
            { bSortable: false },
            { bSortable: false },
            { bSortable: true },
            { bSortable: true },
            { bSortable: false },
            { bSortable: true },
            { bSortable: true },
            { bSortable: false }
        ],
        language: mjschool_attendance_data.datatable_language
    });
    // DataTable initialization at frontend side.
    if(jQuery( '#mjschool-attendance-list-detail-page' ).length > 0) {
        jQuery( '#mjschool-attendance-list-detail-page' ).DataTable({
            "order": [[1, "desc"]],
            "dom": 'lifrtp',
            "aoColumns": [
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true }
            ],
            language: mjschool_attendance_data.datatable_language
        });
    }
    // DataTable initialization at frontend side.
    if(jQuery('#mjschool-attendance-list-detail-page-teacher').length > 0) {
        jQuery('#mjschool-attendance-list-detail-page-teacher').DataTable({
            "initComplete": function (settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "order": [[1, "desc"]],
            "aoColumns": [
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true }
            ],
            dom: '<"float-right"f>rt<"row"<"col-sm-1"l><"col-sm-8"i><"col-sm-3"p>>',
            language: mjschool_attendance_data.datatable_language
        });
    }
    // DataTable initialization for student attendance list at frontend side.
    if(jQuery('#front_student_attendance_list').length > 0) {
        const table = jQuery('#front_student_attendance_list').DataTable({
            "initComplete": function (settings, json) {
                jQuery( ".mjschool-print-button" ).css({ "margin-top": "-5%" });
            },
            "ordering": true,
            dom: 'lifrtp',
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
                { "bSortable": true }
            ],
            language: mjschool_attendance_data.datatable_language
        });
        // Place buttons in custom container.
        jQuery('.btn-place').html(table.buttons().container());
    }
    // DataTable initialization.
    if(jQuery('#mjschool-attendance-list-detail-page-second').length > 0) {
        jQuery('#mjschool-attendance-list-detail-page-second').DataTable({
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
            dom: '<"float-right"f>rt<"row"<"col-sm-1"l><"col-sm-8"i><"col-sm-3"p>>',
            language: mjschool_attendance_data.datatable_language
        });
    }
    // DataTable initialization.
    if (jQuery('#front_teacher_attendance_list').length > 0) {
        const table = jQuery('#front_teacher_attendance_list').DataTable({
            order: [[3, "desc"]],
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            language: mjschool_attendance_data.datatable_language
        });
        // Place buttons in custom container.
        jQuery('.btn-place').html(table.buttons().container());
    }
    // DataTable initialization.
    if (jQuery('#mjschool-attendance-list-detail-page-third').length > 0) {
        jQuery('#mjschool-attendance-list-detail-page-third').DataTable({
            order: [[1, "desc"]],
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: false },
                { bSortable: false },
                { bSortable: false },
                { bSortable: false }
            ],
            dom: '<"float-right"f>rt<"row"<"col-sm-1"l><"col-sm-8"i><"col-sm-3"p>>',
            language: mjschool_attendance_data.datatable_language
        });
    }
    // Placeholder for search input.
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_attendance_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    
    // Place DataTable buttons.
    jQuery('.btn-place').html(teacher_attendance_table.buttons().container());
    // Initialize datepickers.
    jQuery('.sdate, .edate, #curr_date').datepicker({ dateFormat: "yy-mm-dd" });
    // Initialize Attendance DataTable.
    if (jQuery('#attendance_list').length > 0) {
        var table = jQuery('#attendance_list').DataTable({
            // stateSave: true,
            order: [[0, "asc"]],
            aoColumns: [
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: false }
            ]
        });
    }
});