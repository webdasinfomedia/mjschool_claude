jQuery(document).ready(function () {
    "use strict";
    var validationForms = [
        '#failed_report',
        '#student_attendance',
        '#student_book_issue_report',
        '#fee_payment_report',
        '#student_expence_payment',
        '#student_income_expence_payment',
        '#student_income_payment'
    ];
    jQuery.each(validationForms, function (_, selector) {
        var $el = jQuery(selector);
        if ($el.length) {
            $el.validationEngine({
                promptPosition: "bottomLeft",
                maxErrorsPerField: 1
            });
        }
    });
    jQuery( "#report_sdate, #sdate").datepicker({
        dateFormat: mjschool_report_data.date_format,
        changeYear: true,
        changeMonth: true,
        maxDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate());
            jQuery( "#report_edate, #edate").datepicker( "option", "minDate", dt);
        }
    });
    jQuery( "#report_edate, #edate").datepicker({
        dateFormat: mjschool_report_data.date_format,
        changeYear: true,
        changeMonth: true,
        maxDate: 0,
        onSelect: function(selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate());
            jQuery( "#report_sdate, #sdate").datepicker( "option", "maxDate", dt);
        }
    });
    var admission_report_table;
    if (jQuery('#mjschool-admission-list-report').length) {
        admission_report_table = jQuery('#mjschool-admission-list-report').DataTable({
            order: [[2, "desc"]],
            dom: 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.admission_report_text
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.admission_report_text
                }
            ],
            aoColumns: [
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            language: mjschool_report_data.datatable_language
        });
    }
    var audit_log_table;
    if (jQuery('#tble_audit_log_').length) {
        audit_log_table = jQuery('#tble_audit_log_').DataTable({
            initComplete: function (settings, json) {
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
            },
            order: [[2, "desc"]],
            dom: 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.audit_trail_report_text
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.audit_trail_report_text
                }
            ],
            aoColumns: [
                { bSortable: false },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true }
            ],
            language: mjschool_report_data.datatable_language
        });
    }
    var class_section_table;
    if (jQuery('#class_section_report').length) {
        class_section_table = jQuery('#class_section_report').DataTable({
            order: [[1, "desc"]],
            dom: 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.class_section_report_text
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.class_section_report_text
                }
            ],
            aoColumns: [
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            language: mjschool_report_data.datatable_language
        });
    }
    var student_report_table;
    if (jQuery('#student_report').length) {
        student_report_table = jQuery('#student_report').DataTable({
            "order": [[1, "Desc"]],
            "dom": 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.student_report_text
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.student_report_text
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
            language: mjschool_report_data.datatable_language
        });
    }
    var guardian_report_table;
    if (jQuery('#guardian_report').length) {
        guardian_report_table = jQuery('#guardian_report').DataTable({
            order: [[1, "desc"]],
            dom: 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.guardian_report_text
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.guardian_report_text
                }
            ],
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
                { bSortable: true }
            ],
            language: mjschool_report_data.datatable_language
        });
    }
    var sibling_report_table;
    if (jQuery('#sibling_report').length) {
        sibling_report_table = jQuery('#sibling_report').DataTable({
            order: [[1, "desc"]],
            dom: 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.sibling_report_text
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.sibling_report_text
                }
            ],
            aoColumns: [
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true }
            ],
            language: mjschool_report_data.datatable_language
        });
    }
    var fees_payment_table;
    if (jQuery('.fees_payment_report').length) {
        fees_payment_table = jQuery('.fees_payment_report').DataTable({
            responsive: true,
            order: [[1, "asc"]],
            dom: 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.fees_payment_report_text,
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7]
                    }
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.fees_payment_report_text,
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7]
                    }
                }
            ],
            aoColumns: [
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true, width: '180px' },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            language: mjschool_report_data.datatable_language
        });
    }
    var $tableEl = jQuery('#income_payment_report');
    if ($tableEl.length) {
        var income_payment_table = $tableEl.DataTable({
            "order": [[2, "desc"]],
            "dom": 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.income_report_text
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.income_report_text
                }
            ],
            "aoColumns": [
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true }
            ],
            language: mjschool_report_data.datatable_language
        });
    }
    var expense_table;
    if (jQuery('#tblexpence').length) {
        expense_table = jQuery('#tblexpence').DataTable({
            order: [[2, "desc"]],
            dom: 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.expense_report_text,
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.expense_report_text,
                }
            ],
            aoColumns: [
                { bSortable: true },
                { bSortable: true },
                { bSortable: true }
            ],
            language: mjschool_report_data.datatable_language
        });
    }
    var income_expense_table;
    if (jQuery('#table_income_expense').length) {
        income_expense_table = jQuery('#table_income_expense').DataTable({
            order: [[2, "desc"]],
            dom: 'lifrtp',
            buttons: [
                {
                    extend: 'csv',
                    text: mjschool_report_data.csv_text,
                    title: mjschool_report_data.income_expense_report_text,
                },
                {
                    extend: 'print',
                    text: mjschool_report_data.print_text,
                    title: mjschool_report_data.income_expense_report_text,
                }
            ],
            aoColumns: [
                { "bSortable": false },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true }
            ],
            language: mjschool_report_data.datatable_language
        });
    }
    // Set placeholder for search.
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_report_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    jQuery('.btn-place').empty(); // clear
    // Place buttons container inside btn-place.
    if (typeof admission_report_table !== "undefined") {
        jQuery('.btn-place').html(admission_report_table.buttons().container());
    }
    if (typeof audit_log_table !== "undefined") {
        jQuery('.btn-place').html(audit_log_table.buttons().container());
    }
    if (typeof class_section_table !== "undefined") {
        jQuery('.btn-place').html(class_section_table.buttons().container());
    }
    if (typeof student_report_table !== "undefined") {
        jQuery('.btn-place').html(student_report_table.buttons().container());
    }
    if (typeof guardian_report_table !== "undefined") {
        jQuery('.btn-place').html(guardian_report_table.buttons().container());
    }
    if (typeof sibling_report_table !== "undefined") {
        jQuery('.btn-place').html(sibling_report_table.buttons().container());
    }
    if (typeof fees_payment_table !== "undefined") {
        jQuery('.btn-place').html(fees_payment_table.buttons().container());
    }
    if (typeof income_payment_table !== "undefined") {
        jQuery('.btn-place').html(income_payment_table.buttons().container());
    }
    if (typeof expense_table !== "undefined") {
        jQuery('.btn-place').html(expense_table.buttons().container());
    }
    if (typeof income_expense_table!== "undefined") {
        jQuery('.btn-place').html(income_expense_table.buttons().container());
    }

    // Student failed report & teacher performance report.
    const chartDiv = document.getElementById("chart_div");
    if (chartDiv) {
        let chartData = chartDiv.getAttribute("data-chart");
        let chartOptions = chartDiv.getAttribute("data-options");
        try {
            chartData = JSON.parse(chartData);
            chartOptions = JSON.parse(chartOptions);
        } catch (e) {
            console.error("Chart JSON parse error:", e);
            return;
        }
        google.charts.load("current", { packages: ["corechart"] });
        google.charts.setOnLoadCallback(function () {
            let data = google.visualization.arrayToDataTable(chartData);
            let chart = new google.visualization.ColumnChart(chartDiv);
            chart.draw(data, chartOptions);
        });
    }

    // Fees payment report.
    const feeshartDiv = document.getElementById("mjschool-barchart-material-fees");
    // If no chart div exists, exit
    if (feeshartDiv) {
        let feesChartData = feeshartDiv.getAttribute("data-chart");
        let colorCode = feeshartDiv.getAttribute("data-color");

        try {
            feesChartData = JSON.parse(feesChartData);
        } catch (e) {
            console.error("Chart data JSON parse error:", e);
            return;
        }
        google.charts.load("current", { packages: ["bar"] });
        google.charts.setOnLoadCallback(function () {
            let data = google.visualization.arrayToDataTable(feesChartData);
            let options = {
                bars: 'vertical',
                colors: [colorCode]
            };
            let chart = new google.charts.Bar(feeshartDiv);
            chart.draw(data, google.charts.Bar.convertOptions(options));
        });
    }
    // Income Report Graph.

    const canvas = document.getElementById("mjschool-barchart-material-income");

    if (canvas) {

        console.log("Canvas found:", canvas);

        let labels = canvas.dataset.labels;
        let values = canvas.dataset.values;
        let currency = canvas.dataset.currency;

        console.log("Raw dataset:", labels, values, currency);

        try {
            labels = JSON.parse(labels);
            values = JSON.parse(values);
        } catch (e) {
            console.error("JSON Parse Error:", e);
            return;
        }

        console.log("Parsed:", labels, values, currency);

        if (window.incomeChart instanceof Chart) {
            window.incomeChart.destroy();
        }

        const ctx = canvas.getContext("2d");

        window.incomeChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Income",
                    data: values,
                    backgroundColor: "#58c058"
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: "Yearly Income Report"
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: "Month"
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Amount (" + currency + ")"
                        }
                    }
                }
            }
        });

    }
    const container = document.getElementById("mjschool-barchart-material-expence");
    if (container) {
        let chartData = container.getAttribute("data-chart");

        try {
            chartData = JSON.parse(chartData);
        } catch (e) {
            console.error("Expense chart JSON parse error:", e);
            return;
        }

        google.charts.load("current", { packages: ["corechart", "bar"] });
        google.charts.setOnLoadCallback(function () {

            const data = google.visualization.arrayToDataTable(chartData);

            const options = {
                title: "Monthly Expenses",
                chartArea: { width: "60%" },
                hAxis: {
                    title: "Total Expense",
                    minValue: 0,
                },
                vAxis: {
                    title: "Month"
                },
                colors: ["#e64c4c"]
            };

            const chart = new google.visualization.BarChart(container);
            chart.draw(data, options);

        });
    }
    var income_expense_graph = document.getElementById('barChartIncomeExpense');
    if (income_expense_graph) {
         const labels = JSON.parse(income_expense_graph.getAttribute("data-labels"));
        const incomeData = JSON.parse(income_expense_graph.getAttribute("data-income"));
        const expenseData = JSON.parse(income_expense_graph.getAttribute("data-expense"));
        const profitData = JSON.parse(income_expense_graph.getAttribute("data-profit"));
        const currency = income_expense_graph.getAttribute("data-currency");

        const ctx = income_expense_graph.getContext("2d");

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Income",
                        data: incomeData,
                        backgroundColor: '#104B73'
                    },
                    {
                        label: "Expense",
                        data: expenseData,
                        backgroundColor: '#FF9054'
                    },
                    {
                        label: "Net Profit",
                        data: profitData,
                        backgroundColor: '#70ad46'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ": " + currency + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }


});