jQuery(document).ready(function () {
    "use strict";
    const ctx = document.getElementById( 'userContainer' );
    if (ctx) {
        var userContainerData = document.getElementById('userContainer');
        const student_count = userContainerData.getAttribute('data-student-count');
        const parent_count = userContainerData.getAttribute('data-parent-count');
        const staff_count = userContainerData.getAttribute('data-staff-count');
        const teacher_count = userContainerData.getAttribute('data-teacher-count');
        const data = {
            labels: [
                mjschool_dashboard_data.student_text,
                mjschool_dashboard_data.parent_text,
                mjschool_dashboard_data.teacher_text,
                mjschool_dashboard_data.support_staff_text
            ],
            datasets: [{
                label: '# of Users',
                data: [
                    student_count,
                    parent_count,
                    teacher_count,
                    staff_count
                ],
                backgroundColor: ['#1E90FF', '#32CD32', '#FF4500', '#FFA500'],
                borderColor: ['#fff', '#fff', '#fff', '#fff'],
                borderWidth: 1
            }]
        };
        const options = {
            rotation: Math.PI,
            cutout: '85%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function (context) {
                            return context.label + ': ' + context.raw;
                        }
                    }
                }
            }
        };
        new Chart(ctx.getContext('2d'), { type: 'doughnut', data: data, options: options });
    }
    const Student_container = document.getElementById( 'studentContainer' );
    if (Student_container) {
        // Read values from data attributes.
        const inactive = Student_container.getAttribute('data-inactive');
        const active   = Student_container.getAttribute('data-active');
        new Chart(Student_container.getContext( '2d' ), {
            type: 'doughnut',
            data: {
                labels: [
                    mjschool_dashboard_data.inactive_student_text,
                    mjschool_dashboard_data.active_student_text
                ],
                datasets: [{
                    label: '# of Students',
                    data: [
                        inactive,
                        active
                    ],
                    backgroundColor: ['#FF5722', '#8BC34A'],
                    borderColor: ['#fff', '#fff'],
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
                                return context.label + ': ' + context.raw;
                            }
                        }
                    }
                }
            }
        });
    }
    const paymentStatusEl = document.getElementById('paymentstatusContainer');
    if (paymentStatusEl) {
        const paymentStatusCtx = paymentStatusEl.getContext('2d');
        const paid = parseFloat(paymentStatusEl.dataset.paid);
        const unpaid = parseFloat(paymentStatusEl.dataset.unpaid);
        const symbol = paymentStatusEl.dataset.symbol;
        new Chart(paymentStatusCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    mjschool_dashboard_data.paid_text, 
                    mjschool_dashboard_data.unpaid_text
                ],
                datasets: [{
                    label: '# of Payments',
                    data: [ paid, unpaid ],
                    backgroundColor: ['#40A415', '#BA170B'],
                    borderColor: ['#fff', '#fff'],
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
                                return context.label + ': ' + symbol + context.raw;
                            }
                        }
                    }
                }
            }
        });
    }
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
    const payment_bar_canvas = document.getElementById("mjschool-payment-bar-material");
    if (payment_bar_canvas) {
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
    }
    if (jQuery( '.mjschool-id-card-barcode' ).length > 0) {
        // Prepare QR code data.
        var qrData = JSON.stringify({
            user_id: mjschool_dashboard_data.current_user_id,
            class_id: mjschool_dashboard_data.class_id,
            section_id: mjschool_dashboard_data.section_name,
            qr_type: "schoolqr"
        });
        // Generate QR code URL.
        var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent(qrData) + '&amp;size=50x50';
        // Set QR code image.
        jQuery('.mjschool-id-card-barcode').attr('src', qrUrl);
    }
    if (jQuery( '#mjschool_calendar_trigger' ).length > 0) {
        // 🔹 Trigger element
		const trigger = document.getElementById('mjschool_calendar_trigger');
		if (!trigger) {
			return; // ❌ Do nothing if PHP didn't trigger it
		}

		const calendarEl = document.getElementById('calendar');
		if (!calendarEl || typeof FullCalendar === 'undefined') {
			return;
		}

		// 🔹 Get data from PHP
		const calendarLanguage = trigger.dataset.language;
		const events = JSON.parse(trigger.dataset.events || '[]');

		const calendar = new FullCalendar.Calendar(calendarEl, {
			initialView: 'dayGridMonth',
			dayMaxEventRows: 1,
			locale: calendarLanguage,
			headerToolbar: {
				left: 'prev,today,next',
				center: 'title',
				right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
			},
			events: events,

			eventClick: function (event) {
                // ---------- Notice section. ---------- //
                if (event.event._def.extendedProps.description == 'notice' ) {
                    jQuery( "#mjschool-event-booked-popup #notice_title").html(event.event._def.extendedProps.notice_title);
                    jQuery( "#mjschool-event-booked-popup #start_to_end_date").html(event.event._def.extendedProps.start_to_end_date);
                    jQuery( "#mjschool-event-booked-popup #discription").html(event.event._def.extendedProps.notice_comment);
                    jQuery( "#mjschool-event-booked-popup #notice_for").html(event.event._def.extendedProps.notice_for);
                    jQuery( "#mjschool-event-booked-popup #class_name_111").html(event.event._def.extendedProps.class_name);
                    jQuery( "#mjschool-event-booked-popup").removeClass( "mjschool-display-none").dialog({
                        modal: true,
                        title: event.event._def.extendedProps.event_title,
                        width: 550,
                        height: 300
                    });
                }
                // ---------- Holiday section.---------- //
                if (event.event._def.extendedProps.description == 'holiday' ) {
                    jQuery( "#mjschool-holiday-booked-popup #holiday_title").html(event.event._def.extendedProps.holiday_title);
                    jQuery( "#mjschool-holiday-booked-popup #start_to_end_date").html(event.event._def.extendedProps.start_to_end_date);
                    jQuery( "#mjschool-holiday-booked-popup #status").html(event.event._def.extendedProps.status);
                    jQuery( "#mjschool-holiday-booked-popup #holiday_comment").html(event.event._def.extendedProps.holiday_comment);
                    jQuery( "#mjschool-holiday-booked-popup").removeClass( "mjschool-display-none").dialog({
                        modal: true,
                        title: event.event._def.extendedProps.event_title,
                        width: 550,
                        height: 250
                    });
                }
                // ---------- Exam section.---------- //
                if (event.event._def.extendedProps.description == 'exam' ) {
                    jQuery( "#mjschool-exam-booked-popup #exam_title").html(event.event._def.extendedProps.exam_title);
                    jQuery( "#mjschool-exam-booked-popup #start_date").html(event.event._def.extendedProps.start_date);
                    jQuery( "#mjschool-exam-booked-popup #end_date").html(event.event._def.extendedProps.end_date);
                    jQuery( "#mjschool-exam-booked-popup #section_name_123").html(event.event._def.extendedProps.section_name);
                    jQuery( "#mjschool-exam-booked-popup #class_name_123").html(event.event._def.extendedProps.class_name);
                    jQuery( "#mjschool-exam-booked-popup #passing_mark").html(event.event._def.extendedProps.passing_mark);
                    jQuery( "#mjschool-exam-booked-popup #total_mark").html(event.event._def.extendedProps.total_mark);
                    jQuery( "#mjschool-exam-booked-popup #exam_term").html(event.event._def.extendedProps.exam_term);
                    jQuery( "#mjschool-exam-booked-popup #comment").html(event.event._def.extendedProps.comment);
                    jQuery( "#mjschool-exam-booked-popup").removeClass( "mjschool-display-none").dialog({
                        modal: true,
                        title: event.event._def.extendedProps.event_title,
                        width: 550,
                        height: 350
                    });
                }
                // ---------- Event section.---------- //
                if (event.event._def.extendedProps.description == 'event' ) {
                    jQuery( "#mjschool-event-list-booked-popup #event_heading").html(event.event._def.extendedProps.event_heading);
                    jQuery( "#mjschool-event-list-booked-popup #event_start_date_calender").html(event.event._def.extendedProps.event_start_date);
                    jQuery( "#mjschool-event-list-booked-popup #event_end_date_calender").html(event.event._def.extendedProps.event_end_date);
                    jQuery( "#mjschool-event-list-booked-popup #event_comment_calender").html(event.event._def.extendedProps.event_comment);
                    jQuery( "#mjschool-event-list-booked-popup #event_start_time_calender").html(event.event._def.extendedProps.event_start_time);
                    jQuery( "#mjschool-event-list-booked-popup #event_end_time_calender").html(event.event._def.extendedProps.event_end_time);
                    jQuery( "#mjschool-event-list-booked-popup").removeClass( "mjschool-display-none").dialog({
                        modal: true,
                        title: event.event._def.extendedProps.event_title,
                        width: 550,
                        height: 350
                    });
                }
            }
		});

		calendar.render();
    }
});