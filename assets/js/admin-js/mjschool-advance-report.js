jQuery(document).ready(function () {
    "use strict";
    // Admission report
    var currentYear = new Date().getFullYear().toString(); // e.g., "2025".
    var admission_table = jQuery( '#mjschool-admission-list-report' ).DataTable({
        "order": [[2, "Desc"]],
        "dom": 'Qlfrtip',
        layout: {
            top1: 'searchBuilder'
        },
        searchBuilder: {
            preDefined: {
                criteria: [
                    {
                        data: 'Status', // Must match <th>Status</th> exactly.
                        condition: '=',
                        value: ['Rejected']
                    },
                    {
                        data: 'Date of Status', // Must match <th>Date of Status</th> exactly.
                        condition: 'contains', 
                        value: [currentYear]
                    }
                ],
                logic: 'AND'
            }
        },
        buttons: [
            {
                extend: 'csv',
                text: mjschool_advance_report_data.csv_text,
                title: mjschool_advance_report_data.admission_report_text
            },
            {
                extend: 'print',
                text: mjschool_advance_report_data.print_text,
                title: mjschool_advance_report_data.admission_report_text
            }
        ],
        "aoColumns": [
            {"bSortable": true},
            {"bSortable": true},
            {"bSortable": true},
            {"bSortable": true},
            {"bSortable": true},
            {"bSortable": true},
            {"bSortable": true},
            // {"bSortable": true},
            {"bSortable": true}
        ],
        language: mjschool_advance_report_data.datatable_language
    });
    //fees payment report
    var class_name = mjschool_advance_report_data.class_name_list;
    // Function to build dynamic filter based on class_name, payment_status, and late_time_value.
    function mjschool_build_class_filter(class_name, payment_status = 'Not Paid ', late_time_value = '1 month(s) late' ) {
        var new_class_var = class_name + ' ';
        return {
            logic: 'AND',
            criteria: [
                {
                    data: 'Class Name', // Match the Class Name column in the table.
                    condition: '=',
                    value: [new_class_var]
                },
                {
                    data: 'Payment Status', // Match the Payment Status column in the table.
                    condition: '=',
                    value: [payment_status]
                },
                {
                    data: 'Late Time', // Match the Late Time column in the table.
                    condition: 'contains',
                    value: [late_time_value]
                }
            ]
        };
    }
    // Define filters for each class dynamically based on the class names.
    var classFilters = class_name.map(function(class_item) {
        return mjschool_build_class_filter(class_item);
    });
    var table = jQuery( '#mjschool-fees-payment-advance-report' ).DataTable({
        responsive: true,
        "order": [[1, "asc"]],
        "dom": 'QlBfrtip',
        searchBuilder: {
            preDefined: {
                criteria: classFilters, // Apply all the class filters.
                logic: 'OR'
            }
        },
        buttons: [
            {
                extend: 'csv',
                text: mjschool_advance_report_data.csv_text,
                title: mjschool_advance_report_data.fees_payment_report_text,
                exportOptions: { columns: [1,2,3,4,5,6,7] }
            },
            {
                extend: 'print',
                text: mjschool_advance_report_data.print_text,
                title: mjschool_advance_report_data.fees_payment_report_text,
                exportOptions: { columns: [1,2,3,4,5,6,7] }
            }
        ],
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true, 'width': '180px' },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true }
        ],
        language: mjschool_advance_report_data.datatable_language
    });
    //Attendance Report
    var class_name = mjschool_advance_report_data.class_name_list;
    // Function to build dynamic filter based on class_name, payment_status, and late_time_value.
    function mjschool_build_class_filter(class_name) {
        var new_class_var = class_name + ' ';
        return {
            logic: 'AND',
            criteria: [
                {
                    data: 'Class Name', // Match the Class Name column in the table.
                    condition: '=',
                    value: [new_class_var]
                },
                {
                    data: 'Attendance %', // Match the Attendance % column in the table.
                    condition: '>',
                    value: ['50']
                }
            ]
        };
    }
    // Define filters for each class dynamically based on the class names.
    var classFilters = class_name.map(function(class_item) {
        return mjschool_build_class_filter(class_item);
    });
    var attendance_table = jQuery( '#advance_attendance_report' ).DataTable({
        responsive: true,
        "order": [[1, "asc"]],
        "dom": 'QlBfrtip',
        searchBuilder: {
            preDefined: {
                criteria: classFilters, // Apply all the class filters.
                logic: 'OR'
            }
        },
        buttons: [
            {
                extend: 'csv',
                text: mjschool_advance_report_data.csv_text,
                title: mjschool_advance_report_data.attendance_report_text,
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'print',
                text: mjschool_advance_report_data.print_text,
                title: mjschool_advance_report_data.attendance_report_text,
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6]
                }
            }
        ],
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true, 'width': '180px' },
            { "bSortable": true },
            { "bSortable": true }
        ],
        language: mjschool_advance_report_data.datatable_language
    });
    var class_name = mjschool_advance_report_data.class_name_list;
    function mjschool_build_class_filter(class_name) {
        var new_class_var = class_name + ' ';
        return {
            logic: 'AND',
            criteria: [
                {
                    data: 'Class Name', // Match the Class Name column in the table.
                    condition: '=',
                    value: [new_class_var]
                },
                {
                    data: 'Status', // Match the Status column in the table.
                    condition: '=',
                    value: [' Not Approved  ']
                }
            ]
        };
    }
    var classFilters = class_name.map(function(class_item) {
        return mjschool_build_class_filter(class_item);
    });
    var leave_table = jQuery( '#leave_list_advance_report' ).DataTable({
        responsive: true,
        "order": [[1, "asc"]],
        "dom": 'QlBfrtip',
        searchBuilder: {
            preDefined: {
                criteria: classFilters,
                logic: 'OR'
            }
        },
        buttons: [
            {
                extend: 'csv',
                text: mjschool_advance_report_data.csv_text,
                title: mjschool_advance_report_data.leave_report_text,
                exportOptions: { columns: [1,2,3,4,5,6,7] }
            },
            {
                extend: 'print',
                text: mjschool_advance_report_data.print_text,
                title: mjschool_advance_report_data.leave_report_text,
                exportOptions: { columns: [1,2,3,4,5,6,7] }
            }
        ],
        "aoColumns": [
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true, 'width': '180px' },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true }
        ],
        language: mjschool_advance_report_data.datatable_language
    });
    var guardian_report = jQuery( '#guardian_report' ).DataTable({
        "order": [[1, "Desc"]],
        "dom": 'Qlfrtip',
        buttons: [
            {
                extend: 'csv',
                text: mjschool_advance_report_data.csv_text,
                title: mjschool_advance_report_data.guardian_report_text,
            },
            {
                extend: 'print',
                text: mjschool_advance_report_data.print_text,
                title: mjschool_advance_report_data.guardian_report_text,
            }
        ],
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
            { "bSortable": true }
        ],
        language: mjschool_advance_report_data.datatable_language
    });
    var currentYear = new Date().getFullYear().toString(); // e.g., "2025".
    var student_table = jQuery( '#student_report' ).DataTable({
        "order": [[1, "desc"]],
        "dom": 'Qlfrtip',
        language: mjschool_advance_report_data.datatable_language,
        searchBuilder: {
            preDefined: {
                criteria: [
                    {
                        data: 'Status',
                        condition: '=',
                        value: ['Left']
                    },
                    {
                        data: 'Left Date',
                        condition: 'contains',
                        value: [currentYear]
                    }
                ],
                logic: 'AND'
            }
        },
        buttons: [
            {
                extend: 'csv',
                text: mjschool_advance_report_data.csv_text,
                title: mjschool_advance_report_data.student_report_text,
            },
            {
                extend: 'print',
                text: mjschool_advance_report_data.print_text,
                title: mjschool_advance_report_data.student_report_text,
            }
        ],
        "aoColumns": [
            { "bSortable": true }, // Class.
            { "bSortable": true }, // Roll No.
            { "bSortable": true }, // Student Name & Email.
            { "bSortable": true }, // Parent Name.
            { "bSortable": true }, // Date of Birth.
            { "bSortable": true }, // Gender.
            { "bSortable": true }, // Mobile Number.
            { "bSortable": true }, // Status.
            { "bSortable": true }  // Left Date.
        ]
    });
    var class_name = mjschool_advance_report_data.class_name_list;
    function mjschool_build_class_filter(class_name) {
    var new_class_var = class_name + ' ';
    return {
        logic: 'AND',
        criteria: [
            {
                data: 'Class Name', // Match the Class Name column in the table.
                condition: '=',
                value: [new_class_var]
            },
            {
                data: 'Average Mark', // Match the Average Mark column in the table.
                condition: '>',
                value: ['80']
            }
        ]
    };
    }
    var classFilters = class_name.map(function(class_item) {
    return mjschool_build_class_filter(class_item);
    });
    // teacher performance_report.
    var teacher_table = jQuery( '#teacher_advance_report' ).DataTable({
        responsive: true,
        "order": [[1, "asc"]],
        "dom": 'QlBfrtip',
        searchBuilder: {
            preDefined: {
                criteria: classFilters, // Apply all the class filters.
                logic: 'OR'
            }
        },
        buttons: [
            {
                extend: 'csv',
                text: mjschool_advance_report_data.csv_text,
                title: mjschool_advance_report_data.leave_report_text,
                exportOptions: { columns: [1,2,3,4,5,6] }
            },
            {
                extend: 'print',
                text: mjschool_advance_report_data.print_text,
                title: mjschool_advance_report_data.leave_report_text,
                exportOptions: { columns: [1,2,3,4,5,6] }
            }
        ],
        "aoColumns": [
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true },
            { "bSortable": true }
        ],
        language: mjschool_advance_report_data.datatable_language
    });
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_advance_report_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    jQuery('.btn-place').empty(); // clear

    jQuery('.btn-place').append(admission_table.buttons().container());
    jQuery('.btn-place').append(table.buttons().container());
    jQuery('.btn-place').append(attendance_table.buttons().container());
    jQuery('.btn-place').append(leave_table.buttons().container());
    jQuery('.btn-place').append(guardian_report.buttons().container());
    jQuery('.btn-place').append(student_table.buttons().container());
    jQuery('.btn-place').append(teacher_table.buttons().container());
});