jQuery(document).ready(function () {
    "use strict";
    window.mjschool_stop_video = function() {
        var iframe = document.querySelector( '.mjschool-video-frame-class' ); // Select iframe by class.
        if (iframe) {
            var currentSrc = iframe.src;
            iframe.src = ''; // Temporarily remove the src.
            setTimeout(function() {
                iframe.src = currentSrc; // Reset the src.
            }, 100);
        }
    };

    jQuery(document).on("mjschool_issue_book_popup_loaded", function () {
        function mjschool_isNumberKey(evt) {
			"use strict";
			var charCode = (evt.which) ? evt.which : event.keyCode
			if (charCode > 31 && (charCode < 48 || charCode > 57 ) )
				return false;
			return true;
		}
		// Now run the extra JS you need
		jQuery('#mjschool-issue-book-return').validationEngine({
			promptPosition: "bottomLeft",
			maxErrorsPerField: 1
        });
        jQuery( '.datepicker' ).datepicker({
            dateFormat: mjschool_ajax_function_data.date_format,
            minDate: 0,
            changeMonth: true,
            changeYear: true,
            beforeShow: function (textbox, instance) {
                instance.dpDiv.css({
                    marginTop: (-textbox.offsetHeight) + 'px'
                });
            }
        });
    });

    jQuery(document).on("mjschool_routine_import_load", function () {
        // Initialize validation engine.
        jQuery( '#import_csv' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        // File validation for CSV.
        jQuery(document).on( 'change', '.mjschool-file-validation', function () {
            var val = jQuery(this).val().toLowerCase();
            var regex = new RegExp( "(.*?)\\.(csv)$");
            if (!regex.test(val ) ) {
                jQuery(this).val( '' );
                alert(mjschool_ajax_function_data.csv_file_alert_text);
            }
        });
    });
    
    jQuery(document).on("mjschool_category_popup_loaded", function () {
        jQuery( '#category_form_test' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        jQuery( '.mjschool-onlyletter-number-space-validation' ).on( 'keypress', function (e) {
            var regex = /^[0-9a-zA-Z \b]+$/;
            var key = String.fromCharCode(e.which || e.charCode);
            if (!regex.test(key ) ) {
                e.preventDefault();
                return false;
            }
        });
        jQuery( '.onlyletter_number' ).on( 'keypress', function (e) {
            var regex = /^[0-9\b]+$/;
            var key = String.fromCharCode(e.which || e.charCode);
            if (!regex.test(key ) ) {
                e.preventDefault();
                return false;
            }
        });
    });

    jQuery(document).on("mjschool_import_data_loaded", function () {
        jQuery( '#inport_csv' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        jQuery(document).on( 'change', '.mjschool-file-validation', function() {
            var val = jQuery(this).val().toLowerCase();
            var regex = new RegExp( "(.*?)\\.(csv)$");
            if (!regex.test(val ) ) {
                jQuery(this).val( '' );
                alert( "<?php esc_html_e( 'Only CSV format is allowed.', 'mjschool' ); ?>");
            }
        });
    });

    jQuery(document).on("mjschool_export_data_loaded", function () {
        jQuery( '#mjschool-upload-form' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
    });

    jQuery(document).on("mjschool_import_student_data_loaded", function () {
        // Initialize validation.
        jQuery( '#mjschool-upload-form' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        // CSV file type validation.
        jQuery(document).on( 'change', '.mjschool-file-validation', function() {
            var val = jQuery(this).val().toLowerCase();
            var regex = new RegExp( "(.*?)\\.(csv)$");
            if (!regex.test(val ) ) {
                jQuery(this).val( '' );
                alert( mjschool_ajax_function_data.csv_file_alert_text);
            }
        });
        // Hide error message when file is selected.
        jQuery(document).on( 'change', '.csv_file', function() {
            if (jQuery(this).val( ) ) {
                jQuery( '.csv_filemjschool-formError' ).hide();
            }
        });
    });

    jQuery(document).on("mjschool_import_teacher_data_loaded, mjschool_import_supportstaff_data_loaded, mjschool_import_parent_data_loaded", function () {
        // Initialize validation engine.
        jQuery( '#mjschool-upload-form' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        // CSV file validation.
        jQuery(document).on( 'change', '.mjschool-file-validation', function() {
            var val = jQuery(this).val().toLowerCase();
            var regex = new RegExp( "(.*?)\\.(csv)$"); // Properly escape the dot.
            if (!regex.test(val ) ) {
                jQuery(this).val( '' );
                alert( mjschool_ajax_function_data.csv_file_alert_text);
            }
        });
        // Hide error message on valid file selection.
        jQuery(document).on( 'change', '.csv_file', function() {
            if (jQuery(this).val( ) ) {
                jQuery( '.csv_filemjschool-formError' ).hide();
            }
        });
    });

    jQuery(document).on("mjschool_import_subject_data_loaded", function () {
        // Initialize validation engine.
        jQuery( '#mjschool-upload-form' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        // CSV file validation.
        jQuery(document).on( 'change', '.mjschool-file-validation', function() {
            var val = jQuery(this).val().toLowerCase();
            var regex = new RegExp( "(.*?)\\.(csv)$"); // Escape dot properly.
            if (!regex.test(val ) ) {
                jQuery(this).val( '' );
                alert( "<?php esc_html_e( 'Only CSV format is allowed.', 'mjschool' ); ?>");
            }
        });
        // Teacher by class selection.
        jQuery(document).on( 'change', '.class_by_teacher', function() {
            var class_list = jQuery(this).val();
            jQuery( '#subject_teacher' ).html( '' );
            var curr_data = {
                action: 'mjschool_load_teacher_by_class',
                class_list: class_list,
                nonce: mjschool.nonce,
                dataType: 'json'
            };
            jQuery.post(mjschool.ajax, curr_data, function(response) {
                jQuery( "#subject_teacher option[value='remove']").remove();
                jQuery( '#subject_teacher' ).append(response);
                jQuery( '#subject_teacher' ).multiselect( 'rebuild' );
            });
        });
        // Initialize teacher multiselect.
        jQuery( "#subject_teacher").multiselect({
            nonSelectedText: mjschool_ajax_function_data.select_teacher,
            includeSelectAllOption: true,
            selectAllText: mjschool_ajax_function_data.select_all,
            templates: {
                button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
            },
        });
    });
    jQuery(document).on("mjschool_multiple_day_loaded", function () {
        var start = new Date();
        var end = new Date(new Date().setFullYear(start.getFullYear() + 1 ) );
        jQuery( ".leave_start_date").datepicker({
            dateFormat: mjschool_ajax_function_data.date_format,
            changeYear: true,
            changeMonth: true,
            minDate: 0,
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate() + 0);
                jQuery( ".leave_end_date").datepicker( "option", "minDate", dt);
            },
            beforeShow: function (textbox, instance) {
                instance.dpDiv.css({
                    marginTop: (-textbox.offsetHeight) + 'px'
                });
            }
        });
        jQuery( ".leave_end_date").datepicker({
            dateFormat: mjschool_ajax_function_data.date_format,
            changeYear: true,
            changeMonth: true,
            minDate: 0,
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate() - 0);
                jQuery( ".leave_start_date").datepicker( "option", "maxDate", dt);
            },
            beforeShow: function (textbox, instance) {
                instance.dpDiv.css({
                    marginTop: (-textbox.offsetHeight) + 'px'
                });
            }
        });
    });
    jQuery(document).on("mjschool_admission_date_loaded", function () {  
        jQuery( "#report_sdate").datepicker({
            dateFormat: mjschool_ajax_function_data.date_format,
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate( ) );
                jQuery( "#report_edate").datepicker( "option", "minDate", dt);
            }
        });
        jQuery( "#report_edate").datepicker({
            dateFormat: mjschool_ajax_function_data.date_format,
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate( ) );
                jQuery( "#report_sdate").datepicker( "option", "maxDate", dt);
            }
        });
    });

    jQuery(document).on("mjschool_assign_route_loaded", function () {
        jQuery( '#mjschool-message-form' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        jQuery( '#selected_multiple_users' ).multiselect({
            nonSelectedText: mjschool_ajax_function_data.select_user,
            includeSelectAllOption: true,
            selectAllText: mjschool_ajax_function_data.select_all,
            templates: {
                button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>',
            },
        });
    });

    jQuery(document).on("mjschool_membership_payment_report_loaded", function () {
        const payment_bar_canvas = document.getElementById("mjschool-payment-bar-material");
        const payment_ctx = payment_bar_canvas.getContext("2d");
        const labels = JSON.parse(payment_bar_canvas.dataset.labels || "[]");
        const values = JSON.parse(payment_bar_canvas.dataset.values || "[]");
        const currency = payment_bar_canvas.dataset.currency || "";
        const barColor = payment_bar_canvas.dataset.color || "#2196F3";

        new Chart(payment_ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Payment",
                    data: values,
                    backgroundColor: barColor
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: "Fees Payment Report"
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.label}: ${currency}${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: "Day"
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: `Amount (${currency})`
                        }
                    }
                }
            }
        });
    });

    jQuery(document).on("mjschool_income_expence_report_loaded", function () {
        const chartEl = document.getElementById("mjschool-barchart-material");
        if (chartEl) {

            const dataset = chartEl.dataset.chart ? JSON.parse(chartEl.dataset.chart) : null;
            if (!dataset) return;

            const ctx_2d = chartEl.getContext("2d");

            new Chart(ctx_2d, {
                type: "bar",
                data: {
                    labels: dataset.labels,
                    datasets: [
                        {
                            label: "Income",
                            data: dataset.income,
                            backgroundColor: "#104B73"
                        },
                        {
                            label: "Expense",
                            data: dataset.expense,
                            backgroundColor: "#FF9054"
                        },
                        {
                            label: "Net Profit",
                            data: dataset.profit,
                            backgroundColor: "#70ad46"
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: "Income-Expense Report"
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: "Day"
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: `Amount (${dataset.currency})`
                            }
                        }
                    }
                }
            });
        }
    });
    jQuery(document).on("mjschool_payment_dashboard_report_loaded", function () {
        const paymentEl = document.getElementById( 'chartJSContainerpayment' );
        if (paymentEl) {
            const ctx = paymentEl.getContext('2d');
            const cash = parseFloat(paymentEl.dataset.cash);
            const cheque = parseFloat(paymentEl.dataset.cheque);
            const bank = parseFloat(paymentEl.dataset.bank);
            const paypal = parseFloat(paymentEl.dataset.paypal);
            const stripe = parseFloat(paymentEl.dataset.stripe);
            const symbol1 = paymentEl.dataset.symbol;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        mjschool_dashboard_data.cash_text,
                        mjschool_dashboard_data.cheque_text,
                        mjschool_dashboard_data.bank_transfer_text,
                        mjschool_dashboard_data.paypal_text,
                        mjschool_dashboard_data.stripe_text
                    ],
                    datasets: [{
                        label: '# of Payments',
                        data: [ cash, cheque, bank, paypal, stripe ],
                        backgroundColor: ['#CD6155', '#00BCD4', '#F5B041', '#99A3A4', '#9B59B6'],
                        borderColor: ['#fff', '#fff', '#fff', '#fff', '#fff'],
                        borderWidth: 1
                    }]
                },
                options: {
                    rotation: Math.PI,
                    cutout: '85%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw;
                                    const symbol = symbol1;
                                    return label + ': ' + symbol + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    jQuery(document).on("mjschool_attendance_dashboard_report_loaded", function () {
        const attendanceEl = document.getElementById( 'chartJSContainerattendance' );
        if (attendanceEl) {
            // Read values from data attributes.
            const present = parseInt(attendanceEl.dataset.present);
            const absent = parseInt(attendanceEl.dataset.absent);
            const late = parseInt(attendanceEl.dataset.late);
            const halfday = parseInt(attendanceEl.dataset.halfday);
            const ctx = attendanceEl.getContext( '2d' );
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        mjschool_dashboard_data.present_text,
                        mjschool_dashboard_data.absent_text,
                        mjschool_dashboard_data.late_text,
                        mjschool_dashboard_data.half_day_text
                    ],
                    datasets: [{
                        label: '# of Students',
                        data: [ present, absent, late, halfday ],
                        backgroundColor: ['#28A745', '#DC3545', '#FFC107', '#007BFF'],
                        borderColor: ['#fff', '#fff', '#fff', '#fff'],
                        borderWidth: 1
                    }]
                },
                options: {
                    rotation: Math.PI,
                    cutout: '85%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    return (context.label || '' ) + ': ' + context.raw;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
    jQuery(document).on("mjschool_import_student_attendance_loaded", function () {
        // Initialize validation engine.
        jQuery( '#mjschool-upload-form' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        // File validation for CSV.
        jQuery(document).on( 'change', '.mjschool-file-validation', function() {
            var val = jQuery(this).val().toLowerCase();
            var regex = new RegExp( "(.*?)\\.(csv)$");
            if (!regex.test(val ) ) {
                jQuery(this).val( '' );
                alert(mjschool_ajax_function_data.csv_file_alert_text);
            }
        });
    });
    jQuery(document).on("mjschool_ajax_meeting_loaded", function () {
        jQuery( '#meeting_form' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        jQuery( "#start_date").datepicker({
            dateFormat: mjschool_ajax_function_data.date_format,
            minDate: 0,
            changeYear: true,
            changeMonth: true,
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate( ) );
                jQuery( "#end_date").datepicker( "option", "minDate", dt);
            }
        });
        jQuery( "#end_date").datepicker({
            dateFormat: mjschool_ajax_function_data.date_format,
            minDate: 0,
            changeYear: true,
            changeMonth: true,
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate( ) );
                jQuery( "#start_date").datepicker( "option", "maxDate", dt);
            }
        });
    });
    jQuery(document).on("mjschool_ajax_meeting_detail_loaded", function () {
        // Define function inside safe scope.
        window.copy_text = function() {
            const textToCopy = jQuery( '.copy_text' ).text();
            navigator.clipboard.writeText(textToCopy).then(function () {
                // Show success message with green color.
                jQuery( ".copy_link_text" ).css({ "display": "block", "color": "green" });
            }).catch(function (err) {
                console.error( 'Error copying text: ', err);
            });
        };
    });
    jQuery(document).on("mjschool_student_add_payment_loaded", function () {
        jQuery( '#expense_form' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
        jQuery( "#start_date_event").datepicker({
            dateFormat: mjschool_ajax_function_data.date_format,
            maxDate: 0,
            changeMonth: true,
            changeYear: true,
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate() + 0);
                jQuery( "#end_date_event").datepicker( "option", "minDate", dt);
            },
            beforeShow: function (textbox, instance) {
                instance.dpDiv.css({
                    marginTop: (-textbox.offsetHeight) + 'px'
                });
            }
        });
        function mjschool_toggleTransactionField() {
            const paymentMethod = jQuery( '#payment_method' ).val();
            if (paymentMethod === 'Cheque' || paymentMethod === 'Bank Transfer' || paymentMethod === 'Cash' ) {
                jQuery( '.transaction_id_box' ).show();
            } else {
                jQuery( '.transaction_id_box' ).hide();
            }
        }
        // Run on page load.
        mjschool_toggleTransactionField();
        // Run when payment method changes.
        jQuery(document).on( 'change', '#payment_method', function () {
            mjschool_toggleTransactionField();
        });
    });

    jQuery(document).on("mjschool_student_payment_history_loaded", function () {
        // Clone an element and send it to the popup for printing.
        function mjschool_PrintElem(elem) {
            mjschool_Popup($( '<div/>' ).append($(elem).clone( ) ).html( ) );
        }
        // Open a new window and print the content.
        function mjschool_Popup(data) {
            var mywindow = window.open( '', 'my div', 'height=500,width=700' );
            mywindow.document.write( '<html><head><title>Fees Payment Invoice</title></head><body class="test_print">' );
            mywindow.document.write(data);
            mywindow.document.write( '</body></html>' );
            mywindow.document.close();
            mywindow.focus();
            mywindow.print();
            mywindow.focus();
            return true;
        }
        // Expose functions globally if needed.
        window.mjschool_PrintElem = mjschool_PrintElem;
        window.mjschool_Popup = mjschool_Popup;
    });

    jQuery(document).on("mjschool_sibling_dropdown_loaded", function () {

        var lastBlock = jQuery("#mjschool-sibling-div .mjschool-user-form-for-sibling").last();
        var siblingID = lastBlock.data("sibling-id");

        // CLASS CHANGE.
        jQuery(document).on("change", "#sibling_class_change_" + siblingID, function () {

            let classID = jQuery(this).val();
            let studentBox = jQuery("#sibling_student_list_" + siblingID);
            studentBox.html("");

            jQuery.post(mjschool.ajax, {
                action: "mjschool_load_user",
                class_list: classID,
                nonce: mjschool.nonce
            }, function (response) {
                studentBox.append(response);
            });

            let sectionBox = jQuery("#sibling_class_section_" + siblingID);
            sectionBox.html('<option value="remove">Loading...</option>');

            jQuery.post(mjschool.ajax, {
                action: "mjschool_load_class_section",
                class_id: classID,
                nonce: mjschool.nonce
            }, function (response) {
                sectionBox.find("option[value='remove']").remove();
                sectionBox.append(response);
            });
        });

        // SECTION CHANGE
        jQuery(document).on("change", "#sibling_class_section_" + siblingID, function () {

            let sectionID = jQuery(this).val();
            let classID = jQuery("#sibling_class_change_" + siblingID).val();
            let studentBox = jQuery("#sibling_student_list_" + siblingID);

            studentBox.html("");

            jQuery.post(mjschool.ajax, {
                action: "mjschool_load_section_user",
                section_id: sectionID,
                class_id: classID,
                nonce: mjschool.nonce
            }, function (response) {
                studentBox.append(response);
            });
        });
    });

    jQuery(document).on("mjschool_subject_information_loaded", function () {

        // Get the last added subject block.
        var block = jQuery(".mjschool-user-form-for-subject").last();
        var subjectID = block.data("subject-id");

        //CLASS CHANGE → load sections.
        jQuery(document).on("change", "#class_list_subject_" + subjectID, function () {

            let classID = jQuery(this).val();
            let sectionBox = jQuery("#mjschool-class-section-subject_" + subjectID);

            sectionBox.html('<option value="remove">Loading...</option>');

            jQuery.post(mjschool.ajax, {
                action: "mjschool_load_class_section",
                class_id: classID,
                nonce: mjschool.nonce
            }, function (response) {
                sectionBox.find("option[value='remove']").remove();
                sectionBox.append(response);
            });
        });

        // TEACHER LOAD → from class.
        jQuery(document).on("change", ".class_by_teacher_subject_" + subjectID, function () {

            let classID = jQuery(this).val();
            let teacherBox = jQuery("#subject_teacher_subject_" + subjectID);

            teacherBox.html("");

            jQuery.post(mjschool.ajax, {
                action: "mjschool_load_teacher_by_class",
                class_list: classID,
                nonce: mjschool.nonce
            }, function (response) {
                teacherBox.append(response);
                teacherBox.multiselect("rebuild");
            });
        });

        // Init multiselect.
        jQuery("#subject_teacher_subject_" + subjectID).multiselect({
            nonSelectedText: mjschool.select_teacher_text,
            includeSelectAllOption: true,
            selectAllText: mjschool.select_all_text,
            templates: {
                button: '<button class="multiselect btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span><b class="caret"></b></button>'
            }
        });

    });

    if (jQuery("#mjschool-transfer-letter-trigger").length > 0) {
        // ==========================
        // TINYMCE INITIALISE.
        // ==========================
        if (jQuery(".experiance_area").length) {
            tinymce.init({
                selector: '.experiance_area',
                height: 580,
                toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image',
                language: 'en',
                readonly: jQuery(".experiance_area").attr("data-readonly") === "true",
                setup: function (editor) {
                    editor.on('init', function () {
                        // Custom logic here
                    });
                }
            });
        }

        const checkbox = document.getElementById( "certificate_header");
        const printBtn = document.getElementById( "exprience_latter");
        const pdfBtn = document.getElementById( "download_pdf");
        function mjschool_updateLinkWithCheckbox(link) {
            if (!link) return;
            const url = new URL(link.href, window.location.origin);
            if (checkbox && checkbox.checked) {
                url.searchParams.set( "certificate_header", "1");
            } else {
                url.searchParams.delete( "certificate_header");
            }
            link.href = url.toString();
        }
        if (printBtn) {
            printBtn.addEventListener( "click", function () {
                mjschool_updateLinkWithCheckbox(printBtn);
            });
        }
        if (pdfBtn) {
            pdfBtn.addEventListener( "click", function () {
                mjschool_updateLinkWithCheckbox(pdfBtn);
            });
        }
    }



});