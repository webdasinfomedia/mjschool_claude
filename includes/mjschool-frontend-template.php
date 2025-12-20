<?php
/**
 * Frontend Template for MjSchool Plugin
 *
 * Handles rendering of the frontend interface, loading templates,
 * and integrating required scripts and styles for MjSchool.
 *
 * @package    MjSchool
 * @subpackage MjSchool
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
// Render template.//
$obj_feespayment = new Mjschool_Feespayment();
$school_obj      = new MJSchool_Management( get_current_user_id() );
$action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';
$payment_status_sanitize = isset($_REQUEST['payment_status']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_status'])) : '';
$invoice_type_sanitize = isset($_REQUEST['invoice_type']) ? sanitize_text_field(wp_unslash($_REQUEST['invoice_type'])) : '';
$page_name_sanitize = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
if ( isset( $_REQUEST['STATUS'] ) && sanitize_text_field( wp_unslash( $_REQUEST['STATUS'] ) ) === 'TXN_SUCCESS' ) {
	$transaction_id = isset( $_REQUEST['TXNID'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['TXNID'] ) ) : '';
	$order_id = isset( $_REQUEST['ORDERID'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ORDERID'] ) ) : '';
	$custom_array              = explode( '_', $order_id );
	$feedata['fees_pay_id']    = isset( $custom_array[1] ) ? intval( $custom_array[1] ) : 0;
	$feedata['amount']         = isset( $_REQUEST['TXNAMOUNT'] ) ? floatval( wp_unslash( $_REQUEST['TXNAMOUNT'] ) ) : 0;
	$feedata['payment_method'] = 'Paytm';
	$feedata['trasaction_id']  = $transaction_id;
	$PaymentSucces             = $obj_feespayment->mjschool_add_feespayment_history( $feedata );
	if ( $PaymentSucces ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&action=success' ) );
		die();
	}
}
if ( $action === 'paypal_payment' && $payment_status_sanitize === 'Completed' ){
	// 1. Basic Payment Info.
	$transaction_id       = isset( $_POST['txn_id'] ) ? sanitize_text_field( wp_unslash( $_POST['txn_id'] ) ) : '';
	$custom_raw          = isset( $_POST['custom'] ) ? sanitize_text_field( wp_unslash( $_POST['custom'] ) ) : '';
	$custom_array        = explode( '|', $custom_raw );
	$manual_pay_date     = isset( $custom_array[2] ) ? sanitize_text_field( $custom_array[2] ) : '';
	$custom_field_string = isset( $custom_array[3] ) ? sanitize_text_field( $custom_array[3] ) : '';
	// 2. Parse Custom Fields.
	parse_str( $custom_field_string, $custom_field_data );
	// 3. Prepare Fee Payment Data.
	$payment_note     = isset( $custom_field_data['payment_note'] ) ? sanitize_textarea_field( $custom_field_data['payment_note'] ) : '';
	$payment_date_raw = ! empty( $manual_pay_date ) ? $manual_pay_date : date( 'Y-m-d' );
	$feedata          = array(
		'fees_pay_id'    => isset( $custom_array[1] ) ? intval( $custom_array[1] ) : 0,
		'amount'         => isset( $_POST['mc_gross_1'] ) ? floatval( wp_unslash( $_POST['mc_gross_1'] ) ) : 0,
		'payment_method' => 'PayPal',
		'trasaction_id'  => $transaction_id,
		'paid_by_date'   => date( 'Y-m-d', strtotime( sanitize_text_field( $payment_date_raw ) ) ),
		'payment_note'   => $payment_note,
	);
	// 4. Save Payment.
	$PaymentSucces = $obj_feespayment->mjschool_add_feespayment_history( $feedata );
	// 5. Save Custom Fields.
	if ( $PaymentSucces ) {
		$module           = 'fee_transaction';
		$custom_field_obj = new Mjschool_Custome_Field();
		// Merge into $_POST so existing function can pick them up.
		$_POST['custom'] = $custom_field_data;
		// Final call to insert custom field values.
		$custom_insert = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $PaymentSucces );
		// 6. Redirect on success.
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&action=success' ) );
		die();
	} else {
		error_log( 'Payment insert failed — custom fields not saved.' );
	}
}
if ( $action === 'paypal_payment_form' && $payment_status_sanitize === 'Completed' ) {
	global $wpdb;
	$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	$transaction_id               = isset( $_POST['txn_id'] ) ? sanitize_text_field( wp_unslash( $_POST['txn_id'] ) ) : '';
	$custom_post                 = isset( $_POST['custom'] ) ? sanitize_text_field( wp_unslash( $_POST['custom'] ) ) : '';
	$custom_array                = explode( '|', $custom_post );
	$fees_pay_id                 = isset( $custom_array[1] ) ? intval( $custom_array[1] ) : 0;
	$invoice = mjschool_get_single_fees_payment_record($fees_pay_id);
	if ( $invoice && $invoice->invoice_status != 'paid' ) {
		// Generate new invoice_id if missing.
		if ( empty( $invoice->invoice_id ) ) {
			$max_invoice_id  = $wpdb->get_var( "SELECT MAX(invoice_id) FROM $table_mjschool_fees_payment" );
			$next_invoice_id = $max_invoice_id ? $max_invoice_id + 1 : 1;
		} else {
			$next_invoice_id = $invoice->invoice_id;
		}
		// Update status and invoice_id.
		$wpdb->update(
			$table_mjschool_fees_payment,
			array(
				'invoice_status' => 'paid',
				'invoice_id'     => $next_invoice_id,
			),
			array( 'fees_pay_id' => $fees_pay_id )
		);
	}
	// Step 2: Pass to the history logger (it will send mail etc.).
	$feedata['fees_pay_id']    = $fees_pay_id;
	$feedata['amount']         = isset( $_POST['mc_gross_1'] ) ? floatval( wp_unslash( $_POST['mc_gross_1'] ) ) : 0;
	$feedata['payment_method'] = 'PayPal';
	$feedata['trasaction_id']  = $transaction_id;
	$feedata['payment_note']   = 'Registration Fees';
	$PaymentSucces             = $obj_feespayment->mjschool_add_feespayment_history( $feedata );
	if ( $PaymentSucces ) {
		wp_safe_redirect( home_url( '/student-registration-form/?action=pay_success' ) );
		die();
	}
}
$user_role = $school_obj->role;
if ( $user_role != 'teacher' && $user_role != 'student' && $user_role != 'parent' && $user_role != 'supportstaff' ) {
	wp_safe_redirect( esc_url_raw( admin_url() . 'admin.php?page=mjschool' ) );
	die();
}
if ( isset( $_REQUEST['print'] ) && sanitize_text_field(wp_unslash($_REQUEST['print'])) === 'pdf' ) {
	$sudent_id = isset( $_REQUEST['student'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student'] ) ) : '';
	mjschool_download_result_pdf( $sudent_id );
}
$obj_attend            = new Mjschool_Attendence_Manage();
$obj_route             = new Mjschool_Class_Routine();
$obj_event             = new Mjschool_Event_Manage();;
$obj_virtual_classroom = new Mjschool_Virtual_Classroom();
$notive_array          = array();
$cal_array             = array();
// --------- User Student. ---------//
if ( $school_obj->role === 'student' ) {
	$class       = $school_obj->class_info;
	$sectionname = '';
	$section     = 0;
	$section     = get_user_meta( get_current_user_id(), 'class_section', true );
	if ( $section != '' ) {
		$sectionname = mjschool_get_section_name( $section );
	} else {
		$section = 0;
	}
	foreach ( mjschool_day_list() as $daykey => $dayname ) {
		$period = $obj_route->mjschool_get_period( $class->class_id, $section, $daykey );
		if ( ! empty( $period ) ) {
			foreach ( $period as $period_data ) {
				if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
					$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
					if ( ! empty( $meeting_data ) ) {
						$color = 'rgb(46, 138, 194)';
					} else {
						$color = 'rgb(91,112,222 )';
					}
				} else {
					$meeting_data = '';
					$color        = 'rgb(91,112,222 )';
				}
				if ( ! empty( $meeting_data ) ) {
					$meeting_stat_link = $meeting_data->meeting_start_link;
					$meeting_join_link = $meeting_data->meeting_join_link;
					$agenda            = $meeting_data->agenda;
				} else {
					$meeting_stat_link = '';
					$meeting_join_link = '';
					$agenda            = '';
				}
				$teacher_obj = new Mjschool_Teacher();
				$classes     = $teacher_obj->mjschool_get_single_class_teacher( $period_data->class_id );
				$stime       = explode( ':', $period_data->start_time );
				$start_hour  = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
				$start_min   = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
				$start_time      = $start_hour . ':' . $start_min;
				$start_time_data = new DateTime( $start_time );
				$starttime       = date_format( $start_time_data, 'H:i:s' );
				$etime           = explode( ':', $period_data->end_time );
				$end_hour        = str_pad( $etime[0], 2, '0', STR_PAD_LEFT );
				$end_min         = str_pad( $etime[1], 2, '0', STR_PAD_LEFT );
				$end_time       = $end_hour . ':' . $end_min;
				$end_time_data  = new DateTime( $end_time );
				$edittime       = date_format( $end_time_data, 'H:i:s' );
				$user           = get_userdata( $classes->teacher_id );
				$notive_array[] = array(
					'type'               => 'class',
					'title'              => mjschool_get_single_subject_name( $period_data->subject_id ),
					'class_name'         => mjschool_get_class_name( $period_data->class_id ),
					'subject'            => mjschool_get_single_subject_name( $period_data->subject_id ),
					'start'              => $starttime,
					'end'                => $edittime,
					'agenda'             => $agenda,
					'teacher'            => $user->display_name,
					'role'               => 'student',
					'meeting_start_link' => $meeting_stat_link,
					'meeting_join_link'  => $meeting_join_link,
					'dow'                => array( $daykey ),
					'color'              => $color,
				);
			}
		}
	}
	$class_id            = $school_obj->class_info->class_id;
	$class_section       = $school_obj->class_info->class_section;
	$notice_list_student = mjschool_student_notice_dashboard( $class_id, $class_section );
	if ( ! empty( $notice_list_student ) ) {
		foreach ( $notice_list_student as $notice ) {
			$notice_start_date = get_post_meta( $notice->ID, 'start_date', true );
			$notice_end_date   = get_post_meta( $notice->ID, 'end_date', true );
			$notice_comment    = $notice->post_content;
			if ( ! empty( $notice->post_content ) ) {
				$notice_comment = $notice->post_content;
			} else {
				$notice_comment = 'N/A';
			}
			if ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' && get_post_meta( $notice->ID, 'smgt_class_id', true ) === 'all' ) {
				$class_name = esc_html__( 'All', 'mjschool' );
			} elseif ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' ) {
				$class_name = mjschool_get_class_name( get_post_meta( $notice->ID, 'smgt_class_id', true ) );
			} else {
				$class_name = '';
			}
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $notice_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $notice_end_date );
			$notice_for        = ucfirst( get_post_meta( $notice->ID, 'notice_for', true ) );
			$notice_title      = $notice->post_title;
			$i                 = 1;
			$notive_array[]    = array(
				'event_title'       => esc_html__( 'Notice Details', 'mjschool' ),
				'notice_title'      => $notice_title,
				'title'             => $notice->post_title,
				'description'       => 'notice',
				'notice_comment'    => $notice_comment,
				'notice_for'        => $notice_for,
				'start'             => mysql2date( 'Y-m-d', $notice_start_date ),
				'class_name'        => $class_name,
				'end'               => date( 'Y-m-d', strtotime( $notice_end_date . ' +' . $i . ' days' ) ),
				'color'             => '#ffd000',
				'start_to_end_date' => $start_to_end_date,
			);
		}
	}
	$obj_exam = new Mjschool_Exam();
	if ( isset( $class_id ) && $section === '' ) {
		$exam_list = $obj_exam->mjschool_get_all_exam_by_class_id( $class_id );
	} else {
		$exam_list = mjschool_get_all_exam_by_class_id_and_section_id_array( $class_id, $section );
	}
	// Exam List For Student.
	if ( ! empty( $exam_list ) ) {
		foreach ( $exam_list as $exam ) {
			$exam_start_date = $exam->exam_start_date;
			$exam_end_date   = $exam->exam_end_date;
			$i               = 1;
			$exam_title      = $exam->exam_name;
			$exam_term       = get_the_title( $exam->exam_term );
			if ( ! empty( $exam->section_id ) ) {
				$section_name = mjschool_get_section_name( $exam->section_id );
			} else {
				$section_name = 'N/A';
			}
			$class_name = mjschool_get_class_section_name_wise( $exam->class_id, $exam->section_id );
			if ( ! empty( $exam->exam_comment ) ) {
				$comment = $exam->exam_comment;
			} else {
				$comment = 'N/A';
			}
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $exam_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $exam_end_date );
			$total_mark        = $exam->total_mark;
			$passing_mark      = $exam->passing_mark;
			$notive_array[]    = array(
				'exam_title'   => $exam_title,
				'exam_term'    => $exam_term,
				'class_name'   => $class_name,
				'total_mark'   => $total_mark,
				'passing_mark' => $passing_mark,
				'comment'      => $comment,
				'start_date'   => $start_to_end_date,
				'event_title'  => esc_html__( 'Exam Details', 'mjschool' ),
				'title'        => $exam->exam_name,
				'description'  => 'exam',
				'start'        => mysql2date( 'Y-m-d', $exam_start_date ),
				'end'          => date( 'Y-m-d', strtotime( $exam_end_date . ' +' . $i . ' days' ) ),
				'color'        => '#5840bb',
			);
		}
	}
}
// ---------- User parents. -----------//
if ( $school_obj->role === 'parent' ) {
	$chil_array = $school_obj->child_list;
	if ( ! empty( $chil_array ) ) {
		foreach ( $chil_array as $child_id ) {
			$sectionname = '';
			$section     = 0;
			$class       = $school_obj->mjschool_get_user_class_id( $child_id );
			$section     = get_user_meta( $child_id, 'class_section', true );
			if ( $section != '' ) {
				$sectionname = mjschool_get_section_name( $section );
			} else {
				$section = 0;
			}
			foreach ( mjschool_day_list() as $daykey => $dayname ) {
				$period = $obj_route->mjschool_get_period( $class->class_id, $section, $daykey );
				if ( ! empty( $period ) ) {
					foreach ( $period as $period_data ) {
						if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
							$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
							if ( ! empty( $meeting_data ) ) {
								$color = 'rgb(46, 138, 194)';
							} else {
								$color = 'rgb(91,112,222 )';
							}
						} else {
							$meeting_data = '';
							$color        = 'rgb(91,112,222 )';
						}
						if ( ! empty( $meeting_data ) ) {
							$meeting_stat_link = $meeting_data->meeting_start_link;
							$meeting_join_link = $meeting_data->meeting_join_link;
							$agenda            = $meeting_data->agenda;
						} else {
							$meeting_stat_link = '';
							$meeting_join_link = '';
							$agenda            = '';
						}
						$teacher_obj = new Mjschool_Teacher();
						$classes     = $teacher_obj->mjschool_get_single_class_teacher( $period_data->class_id );
						$stime       = explode( ':', $period_data->start_time );
						$start_hour  = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
						$start_min   = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
						$start_time      = $start_hour . ':' . $start_min;
						$start_time_data = new DateTime( $start_time );
						$starttime       = date_format( $start_time_data, 'H:i:s' );
						if ( ! empty( $route_data->end_time ) ) {
							$etime         = explode( ':', $route_data->end_time );
							$end_hour      = str_pad( $etime[0], 2, '0', STR_PAD_LEFT );
							$end_min       = str_pad( $etime[1], 2, '0', STR_PAD_LEFT );
							$end_time      = $end_hour . ':' . $end_min;
							$end_time_data = new DateTime( $end_time );
							$edittime      = date_format( $end_time_data, 'H:i:s' );
						} else {
							$edittime = '';
						}
						$user           = get_userdata( $classes->teacher_id );
						$notive_array[] = array(
							'type'               => 'class',
							'title'              => mjschool_get_single_subject_name( $period_data->subject_id ),
							'class_name'         => mjschool_get_class_name( $period_data->class_id ),
							'subject'            => mjschool_get_single_subject_name( $period_data->subject_id ),
							'start'              => $starttime,
							'end'                => $edittime,
							'agenda'             => $agenda,
							'teacher'            => $user->display_name,
							'role'               => 'parent',
							'meeting_start_link' => $meeting_stat_link,
							'meeting_join_link'  => $meeting_join_link,
							'dow'                => array( $daykey ),
							'color'              => $color,
						);
					}
				}
			}
		}
	}
	$notice_list_parent = mjschool_parent_notice_dashbord();
	if ( ! empty( $notice_list_parent ) ) {
		foreach ( $notice_list_parent as $notice ) {
			$notice_start_date = get_post_meta( $notice->ID, 'start_date', true );
			$notice_end_date   = get_post_meta( $notice->ID, 'end_date', true );
			$notice_title      = $notice->post_title;
			$notice_comment    = $notice->post_content;
			$notice_for        = ucfirst( get_post_meta( $notice->ID, 'notice_for', true ) );
			if ( ! empty( $notice->post_content ) ) {
				$notice_comment = $notice->post_content;
			} else {
				$notice_comment = 'N/A';
			}
			if ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' && get_post_meta( $notice->ID, 'smgt_class_id', true ) === 'all' ) {
				$class_name = esc_html__( 'All', 'mjschool' );
			} elseif ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' ) {
				$class_name = mjschool_get_class_name( get_post_meta( $notice->ID, 'smgt_class_id', true ) );
			} else {
				$class_name = '';
			}
			$i                 = 1;
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $notice_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $notice_end_date );
			$notive_array[]    = array(
				'event_title'       => esc_html__( 'Notice Details', 'mjschool' ),
				'notice_title'      => $notice_title,
				'title'             => $notice->post_title,
				'description'       => 'notice',
				'notice_comment'    => $notice_comment,
				'notice_for'        => $notice_for,
				'start'             => mysql2date( 'Y-m-d', $notice_start_date ),
				'class_name'        => $class_name,
				'end'               => date( 'Y-m-d', strtotime( $notice_end_date . ' +' . $i . ' days' ) ),
				'color'             => '#ffd000',
				'start_to_end_date' => $start_to_end_date,
			);
		}
	}
	$user_id   = get_current_user_id();
	$user_meta = get_user_meta( $user_id, 'child', true );
	foreach ( $user_meta as $c_id ) {
		$classdata[]    = get_user_meta( $c_id, 'class_name', true );
		$section_id[]   = get_user_meta( $c_id, 'class_section', true );
		$section_new_id = implode( ',', $section_id );
		if ( ! empty( $classdata ) && $section_new_id === '' ) {
			$result[] = mjschool_get_all_exam_by_class_id_array( $classdata );
		} else {
			$result[] = mjschool_get_all_exam_by_class_id_and_section_id_array_parent( $classdata, $section_id );
		}
	}
	if ( ! empty( $result ) ) {
		$mergedArray = array_merge( ...$result );
		$exam_list   = array_unique( $mergedArray, SORT_REGULAR );
	} else {
		$exam_list = '';
	}
	// Exam List For Parent.
	if ( ! empty( $exam_list ) ) {
		foreach ( $exam_list as $exam ) {
			$exam_start_date = mjschool_get_date_in_input_box( $exam->exam_start_date );
			$exam_end_date   = mjschool_get_date_in_input_box( $exam->exam_end_date );
			$i               = 1;
			$exam_title      = $exam->exam_name;
			$exam_term       = get_the_title( $exam->exam_term );
			if ( ! empty( $exam->section_id ) ) {
				$section_name = mjschool_get_section_name( $exam->section_id );
			} else {
				$section_name = 'N/A';
			}
			$class_name = mjschool_get_class_section_name_wise( $exam->class_id, $exam->section_id );
			if ( ! empty( $exam->exam_comment ) ) {
				$comment = $exam->exam_comment;
			} else {
				$comment = 'N/A';
			}
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $exam_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $exam_end_date );
			$total_mark        = $exam->total_mark;
			$passing_mark      = $exam->passing_mark;
			$notive_array[]    = array(
				'exam_title'   => $exam_title,
				'exam_term'    => $exam_term,
				'class_name'   => $class_name,
				'total_mark'   => $total_mark,
				'passing_mark' => $passing_mark,
				'comment'      => $comment,
				'start_date'   => $start_to_end_date,
				'event_title'  => esc_html__( 'Exam Details', 'mjschool' ),
				'title'        => $exam->exam_name,
				'description'  => 'exam',
				'start'        => mysql2date( 'Y-m-d', $exam_start_date ),
				'end'          => date( 'Y-m-d', strtotime( $exam_end_date . ' +' . $i . ' days' ) ),
				'color'        => '#5840bb',
			);
		}
	}
}
// --------- User support staff. -----------//
if ( $school_obj->role === 'supportstaff' ) {
	$notice_list_supportstaff = mjschool_supportstaff_notice_dashbord();
	if ( ! empty( $notice_list_supportstaff ) ) {
		foreach ( $notice_list_supportstaff as $notice ) {
			$notice_start_date = get_post_meta( $notice->ID, 'start_date', true );
			$notice_end_date   = get_post_meta( $notice->ID, 'end_date', true );
			$notice_title      = $notice->post_title;
			if ( ! empty( $notice->post_content ) ) {
				$notice_comment = $notice->post_content;
			} else {
				$notice_comment = 'N/A';
			}
			if ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' && get_post_meta( $notice->ID, 'smgt_class_id', true ) === 'all' ) {
				$class_name = esc_html__( 'All', 'mjschool' );
			} elseif ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' ) {
				$class_name = mjschool_get_class_name( get_post_meta( $notice->ID, 'smgt_class_id', true ) );
			} else {
				$class_name = '';
			}
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $notice_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $notice_end_date );
			$notice_for        = ucfirst( get_post_meta( $notice->ID, 'notice_for', true ) );
			$i                 = 1;
			$notive_array[]    = array(
				'event_title'       => esc_html__( 'Notice Details', 'mjschool' ),
				'notice_title'      => $notice_title,
				'title'             => $notice->post_title,
				'description'       => 'notice',
				'notice_comment'    => $notice_comment,
				'notice_for'        => $notice_for,
				'start'             => mysql2date( 'Y-m-d', $notice_start_date ),
				'class_name'        => $class_name,
				'end'               => date( 'Y-m-d', strtotime( $notice_end_date . ' +' . $i . ' days' ) ),
				'color'             => '#ffd000',
				'start_to_end_date' => $start_to_end_date,
			);
		}
	}
	$exam_list = mjschool_get_all_data( 'mjschool_exam' );
	if ( ! empty( $exam_list ) ) {
		foreach ( $exam_list as $exam ) {
			$exam_start_date = $exam->exam_start_date;
			$exam_end_date   = $exam->exam_end_date;
			$i               = 1;
			$exam_title      = $exam->exam_name;
			$exam_term       = get_the_title( $exam->exam_term );
			if ( ! empty( $exam->section_id ) ) {
				$section_name = mjschool_get_section_name( $exam->section_id );
			} else {
				$section_name = 'N/A';
			}
			$class_name = mjschool_get_class_section_name_wise( $exam->class_id, $exam->section_id );
			if ( ! empty( $exam->exam_comment ) ) {
				$comment = $exam->exam_comment;
			} else {
				$comment = 'N/A';
			}
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $exam_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $exam_end_date );
			$total_mark        = $exam->total_mark;
			$passing_mark      = $exam->passing_mark;
			$notive_array[]    = array(
				'exam_title'   => $exam_title,
				'exam_term'    => $exam_term,
				'class_name'   => $class_name,
				'total_mark'   => $total_mark,
				'passing_mark' => $passing_mark,
				'comment'      => $comment,
				'start_date'   => $start_to_end_date,
				'event_title'  => esc_html__( 'Exam Details', 'mjschool' ),
				'title'        => $exam->exam_name,
				'description'  => 'exam',
				'start'        => mysql2date( 'Y-m-d', $exam_start_date ),
				'end'          => date( 'Y-m-d', strtotime( $exam_end_date . ' +' . $i . ' days' ) ),
				'color'        => '#5840bb',
			);
		}
	}
}
// ---------- User teacher. --------//
if ( $school_obj->role === 'teacher' ) {
	if ( ! empty( $school_obj->class_info ) ) {
		$class_name    = $school_obj->class_info->class_id;
		$class_section = $school_obj->class_info->class_section;
	} else {
		$class_name    = '';
		$class_section = '';
	}
	$route_data          = '';
	$notice_list_teacher = mjschool_teacher_notice_dashbord( $class_name );
	foreach ( mjschool_day_list() as $daykey => $dayname ) {
		$period_1 = $obj_route->mjschool_get_period_by_teacher( get_current_user_id(), $daykey );
		$period_2 = $obj_route->mjschool_get_period_by_particular_teacher( get_current_user_id(), $daykey );
		$period   = array();
		if ( ! empty( $period_1 ) && ! empty( $period_2 ) ) {
			$period = array_merge( $period_1, $period_2 );
		} elseif ( ! empty( $period_1 ) && empty( $period_2 ) ) {
			$period = $period_1;
		} elseif ( empty( $period_1 ) && ! empty( $period_2 ) ) {
			$period = $period_2;
		}
		if ( ! empty( $period ) ) {
			foreach ( $period as $period_data ) {
				if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
					$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
					if ( ! empty( $meeting_data ) ) {
						$color = 'rgb(46, 138, 194)';
					} else {
						$color = 'rgb(91,112,222 )';
					}
				} else {
					$meeting_data = '';
					$color        = 'rgb(91,112,222 )';
				}
				if ( ! empty( $meeting_data ) ) {
					$meeting_stat_link = $meeting_data->meeting_start_link;
					$meeting_join_link = $meeting_data->meeting_join_link;
					$agenda            = $meeting_data->agenda;
				} else {
					$meeting_stat_link = '';
					$meeting_join_link = '';
					$agenda            = '';
				}
				if ( ! empty( $route_data ) ) {
					$stime           = explode( ':', $period_data->start_time );
					$start_hour      = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
					$start_min       = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
					$start_am_pm     = $stime[2];
					$start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
					$start_time_data = new DateTime( $start_time );
					$starttime       = date_format( $start_time_data, 'H:i:s' );
					$etime           = explode( ':', $route_data->end_time );
					$end_hour        = str_pad( $etime[0], 2, '0', STR_PAD_LEFT );
					$end_min         = str_pad( $etime[1], 2, '0', STR_PAD_LEFT );
					$end_am_pm       = $etime[2];
					$end_time        = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
					$end_time_data   = new DateTime( $end_time );
					$edittime        = date_format( $end_time_data, 'H:i:s' );
				} else {
					$starttime = '';
					$edittime  = '';
				}
				$user           = get_userdata( get_current_user_id() );
				$notive_array[] = array(
					'type'               => 'class',
					'title'              => mjschool_get_single_subject_name( $period_data->subject_id ),
					'class_name'         => mjschool_get_class_name( $period_data->class_id ),
					'subject'            => mjschool_get_single_subject_name( $period_data->subject_id ),
					'start'              => $starttime,
					'end'                => $edittime,
					'agenda'             => $agenda,
					'teacher'            => $user->display_name,
					'role'               => 'teacher',
					'meeting_start_link' => $meeting_stat_link,
					'dow'                => array( $daykey ),
					'color'              => $color,
				);
			}
		}
	}
	
	if ( ! empty( $notice_list_teacher ) ) {
		foreach ( $notice_list_teacher as $notice ) {
			$notice_start_date = get_post_meta( $notice->ID, 'start_date', true );
			$notice_end_date   = get_post_meta( $notice->ID, 'end_date', true );
			$notice_comment    = $notice->post_content;
			if ( ! empty( $notice->post_content ) ) {
				$notice_comment = $notice->post_content;
			} else {
				$notice_comment = 'N/A';
			}
			if ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' && get_post_meta( $notice->ID, 'smgt_class_id', true ) === 'all' ) {
				$class_name = esc_html__( 'All', 'mjschool' );
			} elseif ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' ) {
				$class_name = mjschool_get_class_name( get_post_meta( $notice->ID, 'smgt_class_id', true ) );
			} else {
				$class_name = '';
			}
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $notice_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $notice_end_date );
			$notice_title      = $notice->post_title;
			$notice_for        = ucfirst( get_post_meta( $notice->ID, 'notice_for', true ) );
			$i                 = 1;
			$notive_array[]    = array(
				'event_title'       => esc_html__( 'Notice Details', 'mjschool' ),
				'notice_title'      => $notice_title,
				'title'             => $notice->post_title,
				'description'       => 'notice',
				'notice_comment'    => $notice_comment,
				'notice_for'        => $notice_for,
				'start'             => mysql2date( 'Y-m-d', $notice_start_date ),
				'class_name'        => $class_name,
				'end'               => date( 'Y-m-d', strtotime( $notice_end_date . ' +' . $i . ' days' ) ),
				'color'             => '#ffd000',
				'start_to_end_date' => $start_to_end_date,
			);
		}
	}
	$obj_exam = new Mjschool_exam();
	$user_id  = get_current_user_id();
	$class_id = get_user_meta( get_current_user_id(), 'class_name', true );
	// Exam Data For.
	$exam_list = $obj_exam->mjschool_get_all_exam_by_class_id_created_by( $class_id, $user_id );
	
	// Exam List For Teacher.
	if ( ! empty( $exam_list ) ) {
		foreach ( $exam_list as $exam ) {
			$exam_start_date = mjschool_get_date_in_input_box( $exam->exam_start_date );
			$exam_end_date   = mjschool_get_date_in_input_box( $exam->exam_end_date );
			$i               = 1;
			$exam_title      = $exam->exam_name;
			$exam_term       = get_the_title( $exam->exam_term );
			if ( ! empty( $exam->section_id ) ) {
				$section_name = mjschool_get_section_name( $exam->section_id );
			} else {
				$section_name = 'N/A';
			}
			
			$class_name = mjschool_get_class_section_name_wise( $exam->class_id, $exam->section_id );
			if ( ! empty( $exam->exam_comment ) ) {
				$comment = $exam->exam_comment;
			} else {
				$comment = 'N/A';
			}
			
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $exam_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $exam_end_date );
			$total_mark        = $exam->total_mark;
			$passing_mark      = $exam->passing_mark;
			$notive_array[]    = array(
				'exam_title'   => $exam_title,
				'exam_term'    => $exam_term,
				'class_name'   => $class_name,
				'total_mark'   => $total_mark,
				'passing_mark' => $passing_mark,
				'comment'      => $comment,
				'start_date'   => $start_to_end_date,
				'event_title'  => esc_html__( 'Exam Details', 'mjschool' ),
				'title'        => $exam->exam_name,
				'description'  => 'exam',
				'start'        => mysql2date( 'Y-m-d', $exam_start_date ),
				'end'          => date( 'Y-m-d', strtotime( $exam_end_date . ' +' . $i . ' days' ) ),
				'color'        => '#5840bb',
			);
		}
	}
}
// --------- Holiday event on calendar. -----------//
$holiday_list = mjschool_get_all_data( 'mjschool_holiday' );
if ( ! empty( $holiday_list ) ) {
	foreach ( $holiday_list as $notice ) {
		if ( $notice->status === 0 ) {
			$notice_start_date = $notice->date;
			$notice_end_date   = $notice->end_date;
			$holiday_title     = $notice->holiday_title;
			$holiday_comment   = $notice->description;
			if ( ! empty( $holiday->description ) ) {
				$holiday_comment = $holiday->description;
			} else {
				$holiday_comment = 'N/A';
			}
			$i                 = 1;
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $notice_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $notice_end_date );
			$notive_array[]    = array(
				'event_title'       => esc_html__( 'Holiday Details', 'mjschool' ),
				'title'             => $notice->holiday_title,
				'description'       => 'holiday',
				'start'             => mysql2date( 'Y-m-d', $notice_start_date ),
				'end'               => date( 'Y-m-d', strtotime( $notice_end_date . ' +' . $i . ' days' ) ),
				'color'             => '#3c8dbc',
				'holiday_title'     => $holiday_title,
				'holiday_comment'   => $holiday_comment,
				'start_to_end_date' => $start_to_end_date,
				'status'            => esc_html__( 'Approve', 'mjschool' ),
			);
		}
	}
}
// ----------- EVENT FOR CALENDAR. -------------//
$event_list = mjschool_get_all_data( 'mjschool_event' );
if ( ! empty( $event_list ) ) {
	foreach ( $event_list as $event ) {
		$event_start_date = $event->start_date;
		$event_end_date   = $event->end_date;
		$i                = 1;
		$notive_array[]   = array(
			'event_title'      => esc_html__( 'Event Details', 'mjschool' ),
			'title'            => $event->event_title,
			'description'      => 'event',
			'start'            => mysql2date( 'Y-m-d', $event_start_date ),
			'end'              => date( 'Y-m-d', strtotime( $event_end_date . ' +' . $i . ' days' ) ),
			'color'            => '#36A8EB',
			'event_heading'    => $event->event_title,
			'event_comment'    => $event->description,
			'event_start_time' => mjschool_time_remove_colon_before_am_pm( $event->start_time ),
			'event_end_time'   => mjschool_time_remove_colon_before_am_pm( $event->end_time ),
			'event_start_date' => $event->start_date,
			'event_end_date'   => $event->end_date,
		);
	}
}
if ( ! is_user_logged_in() ) {
	$page_id = get_option( 'mjschool_login_page' );
	wp_safe_redirect( home_url( '?page_id=' . $page_id ) );
	die();
}
if ( is_super_admin() ) {
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool' ) );
	die();
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_print_styles(); ?>
		<?php wp_print_scripts(); ?>
		<?php
		/*--------Full calendar multilanguage.---------*/
		$lancode = get_locale();
		$code    = substr( $lancode, 0, 2 );
		?>
		<div id="mjschool_calendar_trigger" data-language="<?php echo esc_attr( mjschool_calender_laungage() ); ?>" data-events="<?php echo esc_attr( wp_json_encode( $notive_array ) ); ?>"></div>
	</head>

	<!--------------- NOTICE CALENDAR POP-UP. ---------------->
	<div id="mjschool-event-booked-popup" class="modal-body mjchool_display_none" ><!--Modal body div start.-->
		<div class="penal-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="notice_title"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="start_to_end_date"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Notice For', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="notice_for"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="class_name_111"></span>
				</div>
				<div class="col-md-12 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Comment', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value " id="discription"> </span>
				</div>
			</div>
		</div>
	</div><!--------------- HOLIDAY CALENDAR POP-UP. ---------------->
	<div id="mjschool-holiday-booked-popup" class="modal-body mjchool_display_none" ><!--Modal body div start.-->
		<div class="penal-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="holiday_title"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="start_to_end_date"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Status', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value mjschool_green_color" id="status" ></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Description', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="holiday_comment"></span>
				</div>
			</div>
		</div>
	</div>
	<!--------------- EXAM CALENDAR POP-UP. ---------------->
	<div id="mjschool-exam-booked-popup" class="modal-body mjchool_display_none"><!--Modal body div start.-->
		<div class="penal-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="exam_title"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Term', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="exam_term"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Class', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="class_name_123"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start To End Date', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="start_date"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Total Marks', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="total_mark"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Passing Marks', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="passing_mark"></span>
				</div>
				<div class="col-md-12 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Comment', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="comment"></span>
				</div>
			</div>
		</div>
	</div>
	<!--------------- EVENT CALENDAR POP-UP. ---------------->
	<div id="mjschool-event-list-booked-popup" class="modal-body mjchool_display_none" ><!--Modal body div start.-->
		<div class="penal-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></label><br>
					<span class="mjschool-label-value" id="event_heading"></label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="event_start_date_calender"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'End Date', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="event_end_date_calender"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Time', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="event_start_time_calender"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'End Time', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="event_end_time_calender"></span>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Description', 'mjschool' ); ?></span><br>
					<span class="mjschool-label-value" id="event_comment_calender"></span>
				</div>
			</div>
		</div>
	</div>
	<!-- CLASS BOOK IN CALENDAR POP-UP HTML CODE. -->
	<div id="eventContent" class="modal-body mjschool-display-none mjschool-height-auto"><!--MODAL BODY DIV START-->
		<p class="mjschool-margin-0px"><b><?php esc_html_e( 'Class Name:', 'mjschool' ); ?></b> <span id="class_name"></span></p><br>
		<p class="mjschool-margin-0px"><b><?php esc_html_e( 'Subject:', 'mjschool' ); ?></b> <span id="subject"></span></p><br>
		<p class="mjschool-margin-0px"><b><?php esc_html_e( 'Date:', 'mjschool' ); ?> </b> <span id="date"></span></p><br>
		<p class="mjschool-margin-0px"><b><?php esc_html_e( 'Time:', 'mjschool' ); ?> </b> <span id="time"></span></p><br>
		<p class="mjschool-margin-0px"><b><?php esc_html_e( 'Teacher Name:', 'mjschool' ); ?></b> <span id="teacher_name"></span></p><br>
		<p id="agenda" class="mjschool-class-schedule-topic mjschool-margin-0px"></p><br>
		<p id="meeting_start_link" class="mjschool-margin-0px"></p>
	</div>
	<!--MODAL BODY DIV END.-->
	<body class="mjschool-content-frontend">
		<?php
		$user = wp_get_current_user();
		?>
		<!--Task-event POP-UP code. -->
		<div class="mjschool-popup-bg">
			<div class="mjschool-overlay-content mjschool-content-width">
				<div class="modal-content d-modal-style">
					<div class="mjschool-task-event-list"></div>
				</div>
			</div>
		</div>
		<!-- End task-event POP-UP Code. -->
		<div class="row mjschool-header mjschool-admin-dashboard-main-div mjchool_margin_none">
			<!--HEADER PART IN SET LOGO & TITLE START.-->
			<div class="col-sm-12 col-md-12 col-lg-2 col-xl-2 mjschool-custom-padding-0 mjschool-hide-frontend-navbar-logo-mobile-app">
				
				<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user' )); ?>" class='mjschool-logo'>
					<img src="<?php echo esc_url( get_option( 'mjschool_system_logo' ) ); ?>" class="mjschool-system-logo-height-width" alt="<?php esc_attr_e( 'School Logo', 'mjschool' ); ?>">
				</a>
				
				<!--  Toggle button && design start.-->
				<button type="button" id="sidebarCollapse" class="navbar-btn">
					<span></span>
					<span></span>
					<span></span>
				</button>
				<!--  Toggle button && design end.-->
			</div>
			<?php
			if ( is_rtl() ) {
				$rtl_left_icon_class_css = 'fa-chevron-left';
			} else {
				$rtl_left_icon_class_css = 'fa-chevron-right';
			}
			?>
			<div class="col-sm-12 col-md-12 col-lg-10 col-xl-10 mjschool-right-heder mjschool-with-100-mobile-app">
				<div class="row">
					<div class="col-sm-8 col-md-8 col-lg-8 col-xl-8 mjschool-name-and-icon-dashboard mjschool-align-items-unset-res mjschool-header-width">
						<div class="mjschool-title-add-btn">
							<!-- Page Name.  -->
							<h3 class="mjschool-addform-header-title mjschool-rtl-menu-backarrow-float">
								<?php
								$school_obj         = new MJSchool_Management( get_current_user_id() );
								$mjschool_page_name = '';
								$active_tab         = '';
								$mjschool_action   = '';
								if ( ! empty( $_REQUEST['page'] ) ) {
									$mjschool_page_name = sanitize_text_field(wp_unslash($_REQUEST['page']));
								}
								if ( ! empty( $_REQUEST['tab'] ) ) {
									$active_tab = sanitize_text_field(wp_unslash($_REQUEST['tab']));
								}
								if ( ! empty( $_REQUEST['action'] ) ) {
									$mjschool_action = sanitize_text_field(wp_unslash($_REQUEST['action']));
								}
								$dashboard_param = isset( $_REQUEST['dashboard'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['dashboard'] ) ) : '';
								if ( $dashboard_param === 'mjschool_user' && $mjschool_page_name === '' ) {
									esc_html_e( 'Welcome, ', 'mjschool' );
									echo esc_html( $user->display_name );
								} elseif ( $mjschool_page_name === 'admission' ) {
									if ( $active_tab === 'addadmission' || $active_tab === 'view_admission' ) {
										 ?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=admission' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Admission', 'mjschool' );
										} elseif ($mjschool_action === 'view_admission' ) {
											esc_html_e( 'View Admission', 'mjschool' );
										} else {
											esc_html_e( 'Add Admission', 'mjschool' );
										}
									} else {
										esc_html_e( 'Admission', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'class' ) {
									if ($active_tab === 'addclass' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=class' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Class', 'mjschool' );
										} else {
											esc_html_e( 'Add Class', 'mjschool' );
										}
									} elseif ($active_tab === 'class_details' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=class' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										echo esc_html__( 'Class Details', 'mjschool' );
									} else {
										esc_html_e( 'Class', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'class_room' ) {
									if ($active_tab === 'add_class_room' )
									{
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=class_room&tab=class_room_list' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) 
										{
											esc_html_e( 'Edit Class Room', 'mjschool' );
										} 
										else 
										{
											esc_html_e( 'Add Class Room', 'mjschool' );
										}
									}	
									else
									{
										esc_html_e( 'Class Room', 'mjschool' );
									}		
								} elseif ($mjschool_page_name === 'tax' ) {
									if ($active_tab === 'add_tax' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=tax' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Tax', 'mjschool' );
										} else {
											esc_html_e( 'Add Tax', 'mjschool' );
										}
									} else {
										esc_html_e( 'Tax', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'schedule' ) {
									if ($active_tab === 'addroute' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=schedule' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Class Time Table', 'mjschool' );
										} else {
											esc_html_e( 'Add Class Time Table', 'mjschool' );
										}
									} else {
										esc_html_e( 'Class Time Table', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'subject' ) {
									if ($active_tab === 'addsubject' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=subject' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Subject', 'mjschool' );
										} else {
											esc_html_e( 'Add Subject', 'mjschool' );
										}
									} else {
										esc_html_e( 'Subject', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'student' ) {
									$role_name = mjschool_get_user_role(get_current_user_id( ) );
									if ($active_tab === 'addstudent' || $active_tab === 'view_student' ) {?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=student' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Student', 'mjschool' );
										} elseif ($active_tab === 'view_student' ) {
											if ($role_name === "parent") {
												esc_html_e( 'View Child', 'mjschool' );
											} else {
												esc_html_e( 'View Student', 'mjschool' );
											}
										} else {
											esc_html_e( 'Add Student', 'mjschool' );
										}
									} elseif ($role_name === "parent") {
										esc_html_e( 'Child', 'mjschool' );
									} else {
										esc_html_e( 'Student', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'teacher' ) {
									if ($active_tab === 'addteacher' || $active_tab === 'view_teacher' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=teacher' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Teacher', 'mjschool' );
										} elseif ($active_tab === 'view_teacher' ) {
											esc_html_e( 'View Teacher', 'mjschool' );
										} else {
											esc_html_e( 'Add Teacher', 'mjschool' );
										}
									} else {
										esc_html_e( 'Teacher', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'supportstaff' ) {
									if ($active_tab === 'addsupportstaff' || $active_tab === 'view_supportstaff' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=supportstaff' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Support Staff', 'mjschool' );
										} elseif ($active_tab === 'view_supportstaff' ) {
											esc_html_e( 'View Support Staff', 'mjschool' );
										} else {
											esc_html_e( 'Add Support Staff', 'mjschool' );
										}
									} else {
										esc_html_e( 'Support Staff', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'parent' ) {
									if ($active_tab === 'addparent' || $active_tab === 'view_parent' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=parent' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Parent', 'mjschool' );
										} elseif ($active_tab === 'view_parent' ) {
											esc_html_e( 'View Parent', 'mjschool' );
										} else {
											esc_html_e( 'Add Parent', 'mjschool' );
										}
									} else {
										esc_html_e( 'Parent', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'exam' ) {
									if ($active_tab === 'addexam' || $active_tab === 'exam_time_table' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=exam' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Exam', 'mjschool' );
										} elseif ($active_tab === 'exam_time_table' ) {
											esc_html_e( 'Exam Time Table', 'mjschool' );
										} else {
											esc_html_e( 'Exam', 'mjschool' );
										}
									} else {
										esc_html_e( 'Exam', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'exam_hall' ) {
									if ($active_tab === 'addhall' || $active_tab === 'exam_hall_receipt' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=exam_hall' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Exam Hall', 'mjschool' );
										} elseif ($active_tab === 'exam_hall_receipt' ) {
											esc_html_e( 'Exam Hall Receipt', 'mjschool' );
										} else {
											esc_html_e( 'Exam Hall', 'mjschool' );
										}
									} else {
										esc_html_e( 'Exam Hall', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'manage-marks' ) {
									if ($mjschool_page_name === 'manage-marks' && $active_tab === 'result' ) {
										esc_html_e( 'Manage Marks', 'mjschool' );
									} elseif ($mjschool_page_name === 'manage-marks' && $active_tab === 'export_marks' ) {
										esc_html_e( 'Export Marks', 'mjschool' );
									} elseif ($mjschool_page_name === 'manage-marks' && $active_tab === 'multiple_subject_marks' ) {
										esc_html_e( 'Multiple Subject Marks', 'mjschool' );
									} else {
										esc_html_e( 'Manage Marks', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'grade' ) {
									if ($active_tab === 'addgrade' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=grade' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Grade', 'mjschool' );
										} else {
											esc_html_e( 'Add Grade', 'mjschool' );
										}
									} else {
										esc_html_e( 'Grade', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'virtual_classroom' ) {
									if ($active_tab === 'view_past_participle_list' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=virtual-classroom' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										esc_html_e( 'Participant List', 'mjschool' );
									} elseif ($active_tab === 'edit_meeting' && $mjschool_action === 'edit' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=virtual-classroom' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										esc_html_e( 'Edit Virtual Classroom', 'mjschool' );
									} else {
										esc_html_e( 'Virtual Classroom', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'homework' ) {
									if ($active_tab === 'addhomework' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=homework' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Homework', 'mjschool' );
										} else {
											esc_html_e( 'Add Homework', 'mjschool' );
										}
									} elseif ($active_tab === 'view_homework' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=homework' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										esc_html_e( 'Homework Details', 'mjschool' );
									} elseif ($active_tab === 'view_stud_detail' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=homework' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										esc_html_e( 'View Submission', 'mjschool' );
									} else {
										esc_html_e( 'Homework', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'attendance' ) {
									if ($active_tab === 'student_attendance' ) {
										esc_html_e( 'Student Attendance', 'mjschool' );
									} else {
										esc_html_e( 'Teacher Attendance', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'library' ) {
									if ($active_tab === 'booklist' || $active_tab === 'addbook' ) {
										esc_html_e( 'Book', 'mjschool' );
									} elseif ($active_tab === 'issuelist' ) {
										esc_html_e( 'Issue & Return', 'mjschool' );
									} elseif ($active_tab === 'issue_return' ) {
										$nonce = wp_create_nonce( 'mjschool_library_tab' );
										?>
										<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=library&tab=issuelist&_wpnonce=' . $nonce )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										esc_html_e( 'Issue & Return', 'mjschool' );
									} elseif ($active_tab === 'view_book' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=library&tab=booklist' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										esc_html_e( 'Book Details', 'mjschool' );
									} else {
										esc_html_e( 'Library', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'feepayment' ) {
									if ($active_tab === 'feeslist' || $active_tab === "") {
										if ($user_role === "student" || $user_role === "parent") {
											esc_html_e( 'Fees Payment', 'mjschool' );
										} else {
											esc_html_e( 'Fees Type', 'mjschool' );
										}
									} elseif ($active_tab === 'feepaymentlist' ) {
										esc_html_e( 'Fees Payment', 'mjschool' );
									} elseif ($active_tab === 'recurring_feespaymentlist' ) {
										esc_html_e( 'Recurring Fees Payment', 'mjschool' );
									}elseif ($active_tab === 'view_fessreceipt' ) {
										esc_html_e( 'Payment History', 'mjschool' );
									}
									if ($active_tab === 'addfeetype' ) {
										esc_html_e( 'Fees Type', 'mjschool' );
									} elseif ($active_tab === 'addpaymentfee' ) {
										esc_html_e( 'Fees Payment', 'mjschool' );
									} elseif ($active_tab === 'addrecurringpayment' ) {
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Recurring Fees Payment', 'mjschool' );
										} else {
											esc_html_e( 'Recurring Fees Payment', 'mjschool' );
										}
									} elseif ($active_tab === 'view_fesspayment' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										esc_html_e( 'View Fees Payment Invoice', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'hostel' ) {
									// --- Hostel Module Start. -- //
									if ($mjschool_page_name === 'hostel' && $active_tab === 'hostel_list' ) 
									{
										esc_html_e( 'Hostel', 'mjschool' );
									} 
									elseif ($mjschool_page_name === 'hostel' && $active_tab === 'hostel_details' ) 
									{
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=hostel&tab=hostel_list' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										esc_html_e( 'Hostel Details', 'mjschool' );
									}
									elseif ($mjschool_page_name === 'hostel' && $active_tab === 'add_hostel' ) 
									{
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=hostel&tab=hostel_list' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Hostel', 'mjschool' );
										} else {
											esc_html_e( 'Add Hostel', 'mjschool' );
										}
									} 
									else 
									{
										esc_html_e( 'Hostel', 'mjschool' );
									}
									// --- Hostel Module End. -- //
								} elseif ($mjschool_page_name === 'transport' ) {
									if ($active_tab === 'addtransport' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=transport&tab=transport_list' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Transport', 'mjschool' );
										} else {
											esc_html_e( 'Add Transport', 'mjschool' );
										}
									} else {
										esc_html_e( 'Transport', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'certificate' ) {
									if ($active_tab === 'add_certificate' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=certificate&tab=certificatelist' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Certificate', 'mjschool' );
										} else {
											esc_html_e( 'Add Certificate', 'mjschool' );
										}
									} elseif ( $active_tab === 'certificatelist' ) {
										esc_html_e( 'Certificates', 'mjschool' );
									}
									if ($active_tab === 'assign_certificate' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_list' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Assign Certificate', 'mjschool' );
										} else {
											esc_html_e( 'Assign Certificate', 'mjschool' );
										}
									}
									elseif ( $active_tab === 'assign_list' ){
										esc_html_e( 'Student Certificate', 'mjschool' );
									}
								}
								elseif ($mjschool_page_name === 'leave' ) {
									if ($active_tab === 'add_leave' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=leave&tab=leave_list' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Leave', 'mjschool' );
										} else {
											esc_html_e( 'Add Leave', 'mjschool' );
										}
									} else {
										esc_html_e( 'Leave', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'custom-field' ) {
									if ($active_tab === 'add_custome_field' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=custom-field&tab=custome_field_list' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Custom Field', 'mjschool' );
										} else {
											esc_html_e( 'Add Custom Field', 'mjschool' );
										}
									} else {
										esc_html_e( 'Custom Fields', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'migration' ) {
									esc_html_e( 'Migration', 'mjschool' );
								} elseif ($mjschool_page_name === 'holiday' ) {
									if ($active_tab === 'addholiday' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=holiday&tab=holidaylist' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Holiday', 'mjschool' );
										} else {
											esc_html_e( 'Add Holiday', 'mjschool' );
										}
									} else {
										esc_html_e( 'Holiday', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'notice' ) {
									if ($active_tab === 'addnotice' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notice&tab=noticelist' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Notice', 'mjschool' );
										} else {
											esc_html_e( 'Add Notice', 'mjschool' );
										}
									} else {
										esc_html_e( 'Notice', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'event' ) {
									if ($active_tab === 'add_event' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=event&tab=eventlist' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php 
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Event', 'mjschool' );
										} else {
											esc_html_e( 'Add Event', 'mjschool' );
										}
									} else {
										esc_html_e( 'Event', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_setting' ) {
									esc_html_e( 'SMS Settings', 'mjschool' );
								} elseif ( $mjschool_page_name === 'email_template' ) {
									esc_html_e( 'Email Template', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_template' ) {
									esc_html_e( 'SMS Template', 'mjschool' );
								} elseif ( $mjschool_page_name === 'payment' ) {
									if ( $active_tab === 'paymentlist' ) {
										esc_html_e( 'Other Payment', 'mjschool' );
									} elseif ( $active_tab === 'incomelist' ) {
										if ( $user_role === 'student' || $user_role === 'parent' ) {
											esc_html_e( 'Other Payment', 'mjschool' );
										} else {
											esc_html_e( 'Income', 'mjschool' );
										}
									} elseif ( $active_tab === 'expenselist' ) {
										esc_html_e( 'Expense', 'mjschool' );
									}
									if ( $active_tab === 'addinvoice' ) {
										esc_html_e( 'Other Payment', 'mjschool' );
									} elseif ( $active_tab === 'addincome' ) {
										esc_html_e( 'Income', 'mjschool' );
									} elseif ( $active_tab === 'addexpense' ) {
										esc_html_e( 'Expense', 'mjschool' );
									} elseif ( $active_tab === 'view_invoice' ) {
										if ( $invoice_type_sanitize === 'income' || $invoice_type_sanitize === 'invoice' ) {
											 ?>
											<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=payment&tab=incomelist' )); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
											</a>
											<?php
										} elseif ( $invoice_type_sanitize === 'expense' ) {
											?>
											<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=payment&tab=expenselist' )); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
											</a>
											<?php
										}
										esc_html_e( 'View Payment Invoice', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'message' ) {
									esc_html_e( 'Message', 'mjschool' );
								} elseif ($mjschool_page_name === 'general_settings' ) {
									esc_html_e( 'General Settings', 'mjschool' );
								} elseif ($mjschool_page_name === 'notification' ) {
									if ($active_tab === 'addnotification' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notification' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										esc_html_e( 'Add Notification', 'mjschool' );
									} else {
										esc_html_e( 'Notification', 'mjschool' );
									}
								} elseif ($mjschool_page_name === 'account' ) {
									esc_html_e( 'Account', 'mjschool' );
								} elseif ($mjschool_page_name === 'report' ) {
									esc_html_e( 'Report', 'mjschool' );
								} elseif ($mjschool_page_name === 'document' ) {
									if ($active_tab === 'add_document' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=document&tab=documentlist' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>" alt="<?php esc_attr_e( 'Back Arrow', 'mjschool' ); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Document', 'mjschool' );
										} else {
											esc_html_e( 'Add Document', 'mjschool' );
										}
									} else {
										esc_html_e( 'Documents', 'mjschool' );
									}
								} else {
									echo esc_html( $mjschool_page_name);
								}
								?>
							</h3>
							<div class="mjschool-add-btn1"><!-------- Plus button div. -------->
								<?php
								$user_access = mjschool_get_user_role_wise_access_right_array();
								if ($mjschool_page_name === "admission" && $active_tab != 'addadmission' && $mjschool_action != 'view_admission' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "class" && $active_tab != 'addclass' && $active_tab != 'class_details' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=class&tab=addclass' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								}  elseif ($mjschool_page_name === "class_room" && $active_tab != 'add_class_room' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=class_room&tab=add_class_room' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
							 	} elseif ($mjschool_page_name === "tax" && $active_tab != 'add_tax' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=tax&tab=add_tax' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "schedule" && $active_tab != 'addroute' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=schedule&tab=addroute' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "virtual_classroom" && $active_tab != 'edit_meeting' && $active_tab != 'view_past_participle_list' ) {
									if ($user_role === "teacher" || $user_role === "supportstaff") {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=schedule&tab=addroute' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "subject" && $active_tab != 'addsubject' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=subject&tab=addsubject' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "student" && $active_tab != 'addstudent' && $active_tab != 'view_student' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=student&tab=addstudent' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "teacher" && $active_tab != 'addteacher' && $active_tab != 'view_teacher' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "parent" && $active_tab != 'addparent' && $active_tab != 'view_parent' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=parent&tab=addparent' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "exam" && $active_tab != 'addexam' && $active_tab != 'exam_time_table' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=exam&tab=addexam' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "exam_hall" && $active_tab != 'addhall' && $active_tab != 'exam_hall_receipt' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=exam_hall&tab=addhall' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "grade" && $active_tab != 'addgrade' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=grade&tab=addgrade' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "homework" && $active_tab != 'addhomework' && $active_tab != 'view_stud_detail' && $active_tab != 'view_homework' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=homework&tab=addhomework' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "feepayment") {
									if ($active_tab != 'view_fesspayment' ) {
										if ($mjschool_page_name === 'feepayment' && $active_tab != 'addfeetype' && $active_tab != 'feepaymentlist' && $active_tab != 'recurring_feespaymentlist' && $active_tab != 'addrecurringpayment'  && $active_tab != 'addpaymentfee' &&  $active_tab != 'view_fessreceipt' ) {
											if ($user_access['add'] === '1' ) {
												?>
												<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=feepayment&tab=addfeetype' )); ?>'>
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
												</a>
												<?php
											}
										} elseif ($active_tab === 'feepaymentlist' || $active_tab === 'recurring_feespaymentlist' ) {
											if ($user_access['add'] === '1' ) {
												?>
												<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=feepayment&tab=addpaymentfee' )); ?>'>
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
												</a>
												<?php
											}
										}
									}
								} elseif ($mjschool_page_name === "payment") {
									if ($active_tab === 'paymentlist' ) {
										if ($user_access['add'] === '1' ) {
											?>
											<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=payment&tab=addinvoice' )); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
											</a>
											<?php
										}
									} elseif ($active_tab === 'incomelist' ) {
										if ($user_access['add'] === '1' ) {
											?>
											<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=payment&tab=addincome' )); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
											</a>
											<?php
										}
									} elseif ($active_tab === 'expenselist' ) {
										if ($user_access['add'] === '1' ) {
											?>
											<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=payment&tab=addexpense' )); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
											</a>
											<?php
										}
									}
								}
								elseif ($mjschool_page_name === "hostel") {
									// --- Hostel module Add Btn start.  -----//
									if ($active_tab === 'hostel_list' && $active_tab != 'add_hostel' && $active_tab != 'hostel_details' ) {
										if ($user_access['add'] === '1' ) {
											?>
											<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=hostel&tab=add_hostel&action=insert' )); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
											</a>
											<?php
										}
									} 
									else 
									{
										echo "";
									}
									// --- Hostel module Add Btn End.  -----//
								}
								elseif ($mjschool_page_name === "transport" && $active_tab != 'addtransport' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=transport&tab=addtransport' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} 
								elseif ($mjschool_page_name === "certificate" && $active_tab === 'assign_list' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_certificate&action=new' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} 
								elseif ($mjschool_page_name === "leave" && $active_tab != 'add_leave' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=leave&tab=add_leave' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "custom-field" && $active_tab != 'add_custome_field' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=custom-field&tab=add_custome_field' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "holiday" && $active_tab != 'addholiday' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=holiday&tab=addholiday' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "notice" && $active_tab != 'addnotice' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notice&tab=addnotice' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "event" && $active_tab != 'add_event' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=event&tab=add_event' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "message" && $active_tab != 'compose' ) {
									if ($user_access['add'] === '1' ) {  ?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=message&tab=compose' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "notification" && $active_tab != 'addnotification' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notification&tab=addnotification' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "library" && $active_tab === 'booklist' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=library&tab=addbook' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "document" && $active_tab != 'add_document' ) {
									if ($user_access['add'] === '1' ) {
										?>
										<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=document&tab=add_document' )); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>" alt="<?php esc_attr_e( 'Add Button', 'mjschool' ); ?>">
										</a>
										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
					<!-- Right Header.  -->
					<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4">
						<div class="mjschool-setting-notification">
							<a href='<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notification' )); ?>' class="mjschool-setting-notification-bg">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-bell-notification.png"); ?>" class="mjschool-right-heder-list-link" alt="<?php esc_attr_e( 'Notification', 'mjschool' ); ?>">
								<spna class="mjschool-between-border mjschool-right-heder-list-link"> </span>
							</a>
							<a href='<?php echo esc_url(wp_logout_url(home_url())); ?>' class="mjschool-setting-notification-bg">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-logout.png"); ?>" class="mjschool-right-heder-list-link" alt="<?php esc_attr_e( 'Logout', 'mjschool' ); ?>">
								<spna class="mjschool-between-border mjschool-right-heder-list-link"> </span>
							</a>
							<div class="mjschool-user-dropdown">
								<ul >
									<!-- BEGIN USER LOGIN DROPDOWN. -->
									<li >
										<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
											<?php
											$role_name = mjschool_get_user_role(get_current_user_id( ) );
											$user_info = get_userdata(get_current_user_id( ) );
											$userimage = $user_info->mjschool_user_avatar;
											if ($role_name === "student") {
												$role_img = esc_url( get_option( 'mjschool_student_thumb_new' ) );
											} elseif ($role_name === "teacher") {
												$role_img = esc_url( get_option( 'mjschool_teacher_thumb_new' ) );
											} elseif ($role_name === "supportstaff") {
												$role_img = esc_url( get_option( 'mjschool_supportstaff_thumb_new' ) );
											} elseif ($role_name === "parent") {
												$role_img = esc_url( get_option( 'mjschool_parent_thumb_new' ) );
											}
											?>
											<img src="<?php if ( ! empty( $userimage ) ) { echo esc_url($userimage); } else { echo esc_url($role_img); } ?>" class="mjschool-dropdown-userimg" alt="<?php esc_attr_e( 'User Profile', 'mjschool' ); ?>">
										</a>
										
										<ul class="dropdown-menu extended mjschool-action-dropdawn mjschool-logout-dropdown-menu logout mjschool-header-dropdown-menu" aria-labelledby="dropdownMenuLink">
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="?dashboard=mjschool_user&page=account"><i class="fas fa-user"></i> <?php esc_html_e( 'My Profile', 'mjschool' ); ?></a>
											</li>
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-float-left-width-100px" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><i class="fas fa-sign-out"></i><?php esc_html_e( 'Log Out', 'mjschool' ); ?></a>
											</li>
										</ul>
									</li>
									<!-- END USER LOGIN DROPDOWN. -->
								</ul>
							</div>
						</div>
					</div>
					<!-- Right Header. -->
				</div>
			</div>
		</div>
		<div class="row main_page mjschool-admin-dashboard-menu-rs mjchool_margin_none">
			<div class="col-sm-12 col-md-12 col-lg-2 col-xl-2 mjschool-custom-padding-0 hide_frontend_navbar_mobile_app mjschool-main-sidebar-bgcolor_class" id="mjschool-main-sidebar-bgcolor">
				<?php
				$mjschool_page_name = '';
				if ( ! empty( $_REQUEST['page'] ) ) {
					$mjschool_page_name = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );
				}
				?>
				<!-- Menu sidebar main div start. -->
				<div class="mjschool-main-sidebar">
					<nav class="sidebar_dashboard" id="sidebar">
						<ul class='mjschool-navigation mjschool-frontend-navigation navbar-collapse mjschool-rs-side-menu-bgcolor' id="navbarNav">
							<li class="card-icon">
								
								<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user' )); ?>" class="<?php if ( isset( $_REQUEST['dashboard'] ) && sanitize_text_field( wp_unslash( $_REQUEST['dashboard'] ) ) === 'mjschool_user' && $mjschool_page_name === '' ) { echo esc_attr( 'active' ); } ?>">
									<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-dashboards.png' ); ?>" alt="<?php esc_attr_e( 'Dashboard', 'mjschool' ); ?>">
									<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-dashboards.png' ); ?>" alt="<?php esc_attr_e( 'Dashboard', 'mjschool' ); ?>">
									<span><?php esc_html_e( 'Dashboard', 'mjschool' ); ?></span>
								</a>
							</li>
							<?php
							$page = 'admission';
							$admission = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $admission ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=admission' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'admission' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-admission.png' ); ?>" alt="<?php esc_attr_e( 'Admission', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-admission.png' ); ?>" alt="<?php esc_attr_e( 'Admission', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Admission', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							$class_page = 'class';
							$class_room_page = 'class_room';
							$routine_page = 'schedule';
							$virtual_class = 'virtual_classroom';
							$subject_page = 'subject';
							$class_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $class_page );
							$routine_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $routine_page );
							$virtual_class_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $virtual_class );
							$subject_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $subject_page );
							$class_room_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $class_room_page );
							if ( $class_page_1 === 1 || $routine_page_1 === 1 || $virtual_class_page_1 === 1 || $subject_page_1 === 1 || $class_room_page_1 === 1 ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class="nav-link <?php if ( isset( $mjschool_page_name ) && ( $mjschool_page_name === 'class' || $mjschool_page_name === 'schedule' || $mjschool_page_name === 'virtual_classroom' || $mjschool_page_name === 'subject' ) ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-class.png' ); ?>" alt="<?php esc_attr_e( 'Class', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-class.png' ); ?>" alt="<?php esc_attr_e( 'Class', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Class', 'mjschool' ); ?></span>
										<i class="fas <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fas fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php
										$page = 'class';
										$class = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $class ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=class' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'class' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Class', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$school_type = get_option( 'mjschool_custom_class' );
										if ( $school_type === 'university' )
										{
											if ( get_option( 'mjschool_class_room' ) === 1 )
											{
												$page = 'class_room';
												$class_room = mjschool_page_access_role_wise_access_right_dashboard( $page );
												if ( $class_room ) {
													?>
													<li class=''>
														<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=class_room' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'class_room' ) { echo esc_attr( 'active' ); } ?>">
															<span><?php esc_html_e( 'Class Room', 'mjschool' ); ?></span>
														</a>
													</li>
													<?php
												}
											}
										}
										$page = 'schedule';
										$schedule = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $schedule ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=schedule' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'schedule' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Class Routine', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page = 'virtual_classroom';
										$virtual_classroom = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $virtual_classroom ) {
											if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
												?>
												<li class=''>
													<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=virtual-classroom' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'virtual_classroom' ) { echo esc_attr( 'active' ); } ?>">
														<span><?php esc_html_e( 'Virtual Classroom', 'mjschool' ); ?></span>
													</a>
												</li>
												<?php
											}
										}
										$page = 'subject';
										$subject = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $subject ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=subject' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'subject' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Subject', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							$student_page = 'student';
							$teacher_page = 'teacher';
							$supportstaff_page = 'supportstaff';
							$parent_page = 'parent';
							$student_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $student_page );
							$teacher_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $teacher_page );
							$supportstaff_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $supportstaff_page );
							$parent_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $parent_page );
							if ( $student_page_1 === 1 || $teacher_page_1 === 1 || $supportstaff_page_1 === 1 || $parent_page_1 === 1 ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class="nav-link <?php if ( isset( $mjschool_page_name ) && ( $mjschool_page_name === 'student' || $mjschool_page_name === 'teacher' || $mjschool_page_name === 'supportstaff' || $mjschool_page_name === 'parent' ) ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-user.png' ); ?>" alt="<?php esc_attr_e( 'Users', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-user-white.png' ); ?>" alt="<?php esc_attr_e( 'Users', 'mjschool' ); ?>">
										<span class="margin_left_15px"><?php esc_html_e( 'Users', 'mjschool' ); ?></span>
										<i class="fas <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fas fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									
									<ul class='submenu dropdown-menu'>
										<?php
										$page    = 'student';
										$student = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $student === 1 ) {
											$role_name = mjschool_get_user_role( get_current_user_id() );
											if ( $role_name === 'parent' ) {
												?>
												<li class=''>
													
													<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=student' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'student' ) { echo esc_attr( 'active' ); } ?>">
														<span><?php esc_html_e( 'Child', 'mjschool' ); ?></span>
													</a>
												</li>
												<?php
											} elseif ( $role_name === 'student' ) {
												$student_name = mjschool_get_user_name_by_id( get_current_user_id() );
												?>
												<li class=''>
													<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( get_current_user_id() ) )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'student' ) { echo esc_attr( 'active' ); } ?>">
														<span><?php echo esc_html( $student_name ); ?></span>
													</a>
												</li>
												<?php
											} else {
												?>
												<li class=''>
													<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=student' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'student' ) { echo esc_attr( 'active' ); } ?>">
														<span><?php esc_html_e( 'Student', 'mjschool' ); ?></span>
													</a>
												</li>
												<?php
											}
										}
										$page    = 'teacher';
										$teacher = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $teacher === 1 ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=teacher' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'teacher' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Teacher', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page         = 'supportstaff';
										$supportstaff = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $supportstaff === 1 ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=supportstaff' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'supportstaff' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page   = 'parent';
										$parent = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $parent === 1 ) {
											?>
											<li >
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=parent' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'parent' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Parent', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							$exam_page           = 'exam';
							$exam_hall_page      = 'exam_hall';
							$manage_marks_page   = 'manage-marks';
							$mjschool_grade_page          = 'grade';
							$exam_page_1         = mjschool_page_access_role_wise_access_right_dashboard( $exam_page );
							$exam_hall_page_1    = mjschool_page_access_role_wise_access_right_dashboard( $exam_hall_page );
							$manage_marks_page_1 = mjschool_page_access_role_wise_access_right_dashboard( $manage_marks_page );
							$grade_page_1        = mjschool_page_access_role_wise_access_right_dashboard( $mjschool_grade_page );
							if ( $exam_page_1 === 1 || $exam_hall_page_1 === 1 || $manage_marks_page_1 === 1 || $grade_page_1 === 1 ) {
								?>
								<li class="has-submenu nav-item card-icon">
									
									<a href='#' class="nav-link <?php if ( isset( $mjschool_page_name ) && ( $mjschool_page_name === 'exam' || $mjschool_page_name === 'exam_hall' || $mjschool_page_name === 'manage-marks' || $mjschool_page_name === 'grade' || $mjschool_page_name === 'migration' ) ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-exam.png' ); ?>" alt="<?php esc_attr_e( 'Student Evaluation', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-exam.png' ); ?>" alt="<?php esc_attr_e( 'Student Evaluation', 'mjschool' ); ?>">
										<span ><?php esc_html_e( 'Student Evaluation', 'mjschool' ); ?></span>
										<i class="fas <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fas fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									
									<ul class='submenu dropdown-menu'>
										<?php
										$page      = 'exam';
										$exam_page = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $exam_page === 1 ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=exam' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'exam' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Exam', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page      = 'exam_hall';
										$exam_hall = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $exam_hall === 1 ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=exam_hall' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'exam_hall' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Exam Hall', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page         = 'manage-marks';
										$manage_marks = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $manage_marks === 1 ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=manage-marks' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'manage-marks' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Manage Marks', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page  = 'grade';
										$grade = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $grade === 1 ) {
											?>
											<li >
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=grade' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'grade' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Grade', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page      = 'migration';
										$migration = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $migration === 1 ) {
											?>
											<li >
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=migration' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'migration' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Migration', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							$page     = 'homework';
							$homework = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $homework ) {
								?>
								<li class="card-icon">
									
									<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=homework' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'homework' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-homework.png' ); ?>" alt="<?php esc_attr_e( 'Homework', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png' ); ?>" alt="<?php esc_attr_e( 'Homework', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Homework', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							$page = 'attendance';
							$attendance = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $attendance ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'attendance' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-attendance.png' ); ?>" alt="<?php esc_attr_e( 'Attendance', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png' ); ?>" alt="<?php esc_attr_e( 'Attendance', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Attendance', 'mjschool' ); ?></span>
										<i class="fas <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fas fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php $nonce = wp_create_nonce( 'mjschool_student_attendance_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance&_wpnonce=' . esc_attr( $nonce ) )); ?>' >
												<span><?php esc_html_e( 'Student Attendance', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php
										if ( $school_obj->role === 'supportstaff' || $school_obj->role === 'teacher' ) {
											?>
											<?php $nonce = wp_create_nonce( 'mjschool_teacher_attendance_tab' ); ?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=attendance&tab=teacher_attendance&_wpnonce=' . esc_attr( $nonce ) )); ?>' >
													<span><?php esc_html_e( 'Teacher Attendance', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							$page = 'document';
							$document = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $document ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=document' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'document' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-document.png' ); ?>" alt="<?php esc_attr_e( 'Documents', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-document.png' ); ?>" alt="<?php esc_attr_e( 'Documents', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Documents', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							$page = 'leave';
							$leave = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $leave ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=leave' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'leave' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-leave.png' ); ?>" alt="<?php esc_attr_e( 'Leave', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-leave.png' ); ?>" alt="<?php esc_attr_e( 'Leave', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Leave', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							$feepayment = 'feepayment';
							$payment = 'payment';
							$tax = 'tax';
							$feepayment_1 = mjschool_page_access_role_wise_access_right_dashboard( $feepayment );
							$payment_1 = mjschool_page_access_role_wise_access_right_dashboard( $payment );
							$tax_1 = mjschool_page_access_role_wise_access_right_dashboard( $tax );
							if ( $tax_1 === 1 || $feepayment_1 === 1 ||  $payment_1 === 1 ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class="nav-link <?php if ( isset( $mjschool_page_name ) && ( $mjschool_page_name === 'feepayment' || $mjschool_page_name === 'payment' || $mjschool_page_name === 'tax' ) ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-payment.png' ); ?>" alt="<?php esc_attr_e( 'Payment', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png' ); ?>" alt="<?php esc_attr_e( 'Payment', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Payment', 'mjschool' ); ?></span>
										<i class="fas <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fas fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php
										$page = 'feepayment';
										$feepayment = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $feepayment ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=feepayment' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'feepayment' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Fees payment', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page = 'payment';
										$payment = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $payment ) {
											$nonce = wp_create_nonce( 'mjschool_payment_tab' );
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=payment&tab=incomelist&_wpnonce=' . esc_attr( $nonce ) )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'payment' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Other Payment', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page = 'tax';
										$tax = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $tax ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=tax' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'tax' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Tax', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							$page = 'library';
							$library = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $library ) {
								$nonce = wp_create_nonce( 'mjschool_library_tab' );
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=library&tab=booklist&_wpnonce=' . esc_attr( $nonce ) )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'library' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-library.png' ); ?>" alt="<?php esc_attr_e( 'Library', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-library.png' ); ?>" alt="<?php esc_attr_e( 'Library', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Library', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							$page = 'hostel';
							$hostel = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $hostel ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=hostel&tab=hostel_list' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'hostel' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-hostel.png' ); ?>" alt="<?php esc_attr_e( 'Hostel', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-hostel.png' ); ?>" alt="<?php esc_attr_e( 'Hostel', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Hostel', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							$page = 'transport';
							$transport = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $transport ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=transport' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'transport' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-transportation.png' ); ?>" alt="<?php esc_attr_e( 'Transport', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-transportation.png' ); ?>" alt="<?php esc_attr_e( 'Transport', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Transport', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							$page = 'certificate';
							$certificate = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $certificate ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_list' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'certificate' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-certificate-icon-dark.png' ); ?>" alt="<?php esc_attr_e( 'Certificate', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-certificate-icon-light.png' ); ?>" alt="<?php esc_attr_e( 'Certificate', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Certificate', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							$page = 'report';
							$report = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $report ) 
							{
								?>
								<li class="has-submenu nav-item card-icon report">
									<a href='#' class="<?php if ( isset( $_REQUEST['page'] ) && sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) === 'report' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-report.png' ); ?>" alt="<?php esc_attr_e( 'Report', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-report.png' ); ?>" alt="<?php esc_attr_e( 'Report', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Report', 'mjschool' ); ?></span>
										<i class="fas <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fas fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									
									<ul class='submenu dropdown-menu'>
										<?php $nonce = wp_create_nonce( 'mjschool_student_infomation_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=report&tab=student_information_report&_wpnonce=' . esc_attr( $nonce ) )); ?>' class="<?php if ( $page_name_sanitize === 'report' ) { echo esc_attr( 'active' ); } ?>">
												<span><?php esc_html_e( 'Student Information', 'mjschool' ); ?></span>
												</a>
										</li>
										<?php $nonce1 = wp_create_nonce( 'mjschool_finance_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=report&tab=finance_report&_wpnonce=' . esc_attr( $nonce1 ) )); ?>' class="<?php if ( $page_name_sanitize === 'report' ) { echo esc_attr( 'active' ); } ?>">
												<span><?php esc_html_e( 'Finance/Payment', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce2 = wp_create_nonce( 'mjschool_attendance_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=report&tab=attendance_report&_wpnonce=' . esc_attr( $nonce2 ) )); ?>' class="<?php if ( $page_name_sanitize === 'report' ) { echo esc_attr( 'active' ); } ?>">
												<span><?php esc_html_e( 'Attendance', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce3 = wp_create_nonce( 'mjschool_examination_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=report&tab=examinations_report&_wpnonce=' . esc_attr( $nonce3 ) )); ?>' class="<?php if ( $page_name_sanitize === 'report' ) { echo esc_attr( 'active' ); } ?>">
												<span><?php esc_html_e( 'Examinations', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce4 = wp_create_nonce( 'mjschool_library_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=report&_wpnonce=' . esc_attr( $nonce4 ) )); ?>' class="<?php if ( $page_name_sanitize === 'report' ) { echo esc_attr( 'active' ); } ?>">
												<span><?php esc_html_e( 'Library', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce5 = wp_create_nonce( 'mjschool_hostel_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=report&tab=hostel_report&_wpnonce=' . esc_attr( $nonce5 ) )); ?>' class="<?php if ( $page_name_sanitize === 'report' ) { echo esc_attr( 'active' ); } ?>">
												<span><?php esc_html_e( 'Hostel', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce6 = wp_create_nonce( 'mjschool_user_log_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=report&tab=user_log_report&_wpnonce=' . esc_attr( $nonce6 )) ); ?>' class="<?php if ( $page_name_sanitize === 'report' ) { echo esc_attr( 'active' ); } ?>">
												<span><?php esc_html_e( 'User Log', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce7 = wp_create_nonce( 'mjschool_audit_trail_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=report&tab=audit_log_report&_wpnonce=' . esc_attr( $nonce7 )) ); ?>' class="<?php if ( $page_name_sanitize === 'report' ) { echo esc_attr( 'active' ); } ?>">
												<span><?php esc_html_e( 'Audit Trail Report', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce8 = wp_create_nonce( 'mjschool_migration_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=report&tab=migration_report&_wpnonce=' . esc_attr( $nonce8 )) ); ?>' class="<?php if ( $page_name_sanitize === 'report' ) { echo esc_attr( 'active' ); } ?>">
												<span><?php esc_html_e( 'Migration Report', 'mjschool' ); ?></span>
											</a>
										</li>
									</ul>
								</li>
								<?php
							}
							$notice         = 'notice';
							$message        = 'message';
							$holiday        = 'holiday';
							$notification   = 'notification';
							$event          = 'event';
							$notification_1 = mjschool_page_access_role_wise_access_right_dashboard( $notification );
							$notice_1       = mjschool_page_access_role_wise_access_right_dashboard( $notice );
							$event_1        = mjschool_page_access_role_wise_access_right_dashboard( $event );
							$message_1      = mjschool_page_access_role_wise_access_right_dashboard( $message );
							$holiday_1      = mjschool_page_access_role_wise_access_right_dashboard( $holiday );
							if ( $notice_1 === 1 || $event_1 === 1 || $message_1 === 1 || $holiday_1 === 1 || $notification_1 === 1 ) {
								?>
								<li class="has-submenu nav-item card-icon mjschool-notification-hovor">
									
									<a href='#' class="nav-link <?php if ( isset( $mjschool_page_name ) && ( $mjschool_page_name === 'notice' || $mjschool_page_name === 'message' || $mjschool_page_name === 'event' || $mjschool_page_name === 'notification' || $mjschool_page_name === 'holiday' ) ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-notifications.png' ); ?>" alt="<?php esc_attr_e( 'Notification', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-notifications.png' ); ?>" alt="<?php esc_attr_e( 'Notification', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Notification', 'mjschool' ); ?></span>
										<i class="fas <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fas fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									
									<ul class='submenu dropdown-menu mjschool-notification-hovor-dropdown'>
										<?php
										$page   = 'notice';
										$notice = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $notice ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=notice' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'notice' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Notice', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page  = 'event';
										$event = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $event ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=event' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'event' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Event', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page    = 'message';
										$message = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $message ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=message' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'message' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Message', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page         = 'notification';
										$notification = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $notification ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=notification' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'notification' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Notification', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page    = 'holiday';
										$holiday = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $holiday ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=holiday' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'holiday' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Holiday', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							$custom_field        = 'custom-field';
							$mjschool_setting    = 'mjschool_setting';
							$general_settings    = 'general_settings';
							$email_template      = 'email_template';
							$mjschool_template   = 'mjschool_template';
							$custom_field_1      = mjschool_page_access_role_wise_access_right_dashboard( $custom_field );
							$mjschool_setting_1  = mjschool_page_access_role_wise_access_right_dashboard( $mjschool_setting );
							$email_template_1    = mjschool_page_access_role_wise_access_right_dashboard( $email_template );
							$mjschool_template_1 = mjschool_page_access_role_wise_access_right_dashboard( $mjschool_template );
							$general_settings_1  = mjschool_page_access_role_wise_access_right_dashboard( $general_settings );
							if ( $custom_field_1 === 1 || $mjschool_setting_1 === 1 || $email_template_1 === 1 || $mjschool_template_1 === 1 ) {
								?>
								<li class="has-submenu nav-item card-icon">
									
									<a href='#' class="nav-link <?php if ( isset( $mjschool_page_name ) && ( $mjschool_page_name === 'custom-field' || $mjschool_page_name === 'mjschool_setting' || $mjschool_page_name === 'email_template' ) ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-setting.png' ); ?>" alt="<?php esc_attr_e( 'System Settings', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-setting.png' ); ?>" alt="<?php esc_attr_e( 'System Settings', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'System Settings', 'mjschool' ); ?></span>
										<i class="fas <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fas fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									
									<ul class='submenu dropdown-menu'>
										<?php
										$page         = 'custom-field';
										$custom_field = mjschool_page_access_role_wise_access_right_dashboard( $page );

										
										if ( $custom_field ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=custom-field' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'custom_field' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Custom Fields', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page             = 'sms_setting';
										$mjschool_setting = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $mjschool_setting ) {
											
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=mjschool-setting' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'mjschool_setting' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'SMS Settings', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page           = 'email_template';
										$email_template = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $email_template ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=email-template' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'email_template' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'Email Template', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page              = 'sms_template';
										$mjschool_template = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $mjschool_template ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=sms-template' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'sms_template' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'SMS Template', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$page             = 'general_settings';
										$general_settings = mjschool_page_access_role_wise_access_right_dashboard( $page );
										if ( $general_settings ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=general-settings' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'general_settings' ) { echo esc_attr( 'active' ); } ?>">
													<span><?php esc_html_e( 'General Settings', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							$page    = 'account';
							$account = mjschool_page_access_role_wise_access_right_dashboard( $page );
							if ( $account ) {
								?>
								<li class="card-icon">
									
									<a href='<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=account' )); ?>' class="<?php if ( isset( $mjschool_page_name ) && $mjschool_page_name === 'account' ) { echo esc_attr( 'active' ); } ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-account.png' ); ?>" alt="<?php esc_attr_e( 'Account', 'mjschool' ); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-account-white.png' ); ?>" alt="<?php esc_attr_e( 'Account', 'mjschool' ); ?>">
										<span><?php esc_html_e( 'Account', 'mjschool' ); ?></span>
									</a>
									
								</li>
								<?php
							}
							?>
						</ul>
					</nav>
				</div>
				<!-- End menu sidebar main div.  -->
			</div>
			<!-- Dashboard content div start. -->
			<div class="col col-sm-12 col-md-12 col-lg-10 col-xl-10 mjschool-dashboard-margin mjschool-padding-left-0 mjschool-padding-right-0 mjschool-with-100-mobile-app">
				<div class="mjschool-page-inner mjschool-min-height-1088 mjschool-frontend-homepage-padding-top">
					<!-- main-wrapper div START-->
					<div id="main-wrapper" class="main-wrapper-div mjschool-label-margin-top-15px mjschool-admin-dashboard mjschool_new_main_warpper">
						<?php
						if ( isset( $_REQUEST['page'] ) ) {
							// Ensure the user is logged in.
							if ( ! is_user_logged_in() ) {
								http_response_code( 403 );
								wp_die( 'Access Denied: Unauthorized access' );
							}
							// Validate user role.
							$role          = mjschool_get_user_role( get_current_user_id() );
							$allowed_roles = array( 'management', 'administrator', 'supportstaff', 'teacher', 'student', 'parent' );
							if ( ! in_array( $role, $allowed_roles ) ) {
								wp_die( 'Permission denied' );
							}
							$allowed_pages      = array(
								'dashboard',
								'account',
								'admission',
								'class',
								'schedule',
								'student',
								'teacher',
								'supportstaff',
								'parent',
								'exam',
								'exam_hall',
								'manage-marks',
								'grade',
								'migration',
								'homework',
								'attendance',
								'document',
								'leave',
								'feepayment',
								'payment',
								'tax',
								'library',
								'hostel',
								'transport',
								'report',
								'notice',
								'event',
								'message',
								'notification',
								'holiday',
								'custom-field',
								'certificate',
								'sms-setting',
								'email-template',
								'sms-template',
								'general-settings',
								'virtual-classroom',
								'subject',
								'class_room',
							); // Define allowed pages.
							$mjschool_page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
							if ( in_array( $mjschool_page_name, $allowed_pages, true ) ) {
								if ( defined( 'MJSCHOOL_PLUGIN_DIR' ) ) {
									require_once MJSCHOOL_PLUGIN_DIR . '/template/' . $mjschool_page_name . '.php';
								} else {
									http_response_code( 500 );
									wp_die( 'Configuration Error: Plugin directory not defined.' );
								}
							} else {
								http_response_code( 403 );
								wp_die( 'Access Denied: Invalid Page' );
							}
						}
						if ( isset( $_REQUEST['dashboard'] ) && sanitize_text_field(wp_unslash($_REQUEST['dashboard'])) === 'mjschool_user' && $mjschool_page_name === '' ) {
							$dashboard_result = mjschool_frontend_dashboard_card_access();
							?><!-- Four Card , Chart and Fees Payment Row Div.  -->
							<div class="row mjschool-menu-row mjschool-dashboard-content-rs mjschool-user-dashdoard-responsive mjschool-first-row-padding-top">
								<!-- USER REPORT CARD START. -->
								<?php if ( $dashboard_result['mjschool_user_chart'] === 'yes' ) { ?>
									<div class="col-lg-4 col-md-4 col-xs-12 col-sm-12 mjschool-responsive-div-dashboard">
										<div class="panel mjschool-panel-white mjschool-line-chat mjchool_height_400px">
											<div class="mjschool-panel-heading mb-2 mjschool-line-chat-p" id="mjschool-line-chat-p">
												<h3 class="mjschool-panel-title mjschool_float_left" ><?php esc_html_e( 'Users', 'mjschool' ); ?></h3>
											</div>
											<div class="mjschool-member-chart">
												<div class="outer">
													<!-- <canvas id="userContainer" width="300" height="250"></canvas> -->
													<p class="percent">
														<?php
														$user_id     = get_current_user_id();
														$studentdata = mjschool_student_count_for_dashboard_card( $user_id, $user_role );
														if ( ! empty( $studentdata ) ) {
															$student_count = count( $studentdata );
														} else {
															$student_count = 0;
														}
														$parentdata = mjschool_parent_count_for_dashboard_card( $user_id, $user_role );
														if ( ! empty( $parentdata ) ) {
															$parent_count = count( $parentdata );
														} else {
															$parent_count = 0;
														}
														$teacher = mjschool_teacher_count_for_dashboard_card( get_current_user_id(), $user_role );
														if ( ! empty( $teacher ) ) {
															$teacher_count = count( $teacher );
														} else {
															$teacher_count = '0';
														}
														$page        = 'supportstaff';
														$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
														if ( $user_role === 'supportstaff' ) {
															$staff_count = '1';
														} else {
															$user_query  = new WP_User_Query( array( 'role' => 'supportstaff' ) );
															$staff_count = (int) $user_query->get_total();
														}
														$total_student_parent = $parent_count + $student_count + $teacher_count + $staff_count;
														echo (int) $total_student_parent;
														?>
													</p>
													<p class="percent_report"> <?php esc_html_e( 'Users', 'mjschool' ); ?> </p>
													<canvas id="userContainer" width="300" height="250" data-student-count = '<?php echo esc_js($student_count); ?>' data-parent-count = '<?php echo esc_js($parent_count); ?>' data-teacher-count = '<?php echo esc_js($teacher_count); ?>' data-staff-count = '<?php echo esc_js($staff_count); ?>'></canvas>
												</div>
											</div>
											<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
												<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
													<p class="mjschool-users-report-dot-color mjschool_bule_color" ></p>
													<p class="mjschool-user-report-label"><?php esc_html_e( 'Students', 'mjschool' ); ?></p>
												</div>
												<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
													<p class="mjschool-users-report-dot-color mjschool_green_color" ></p>
													<p class="mjschool-user-report-label"><?php esc_html_e( 'Parents', 'mjschool' ); ?></p>
												</div>
												<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
													<p class="mjschool-users-report-dot-color mjschool_dark_orange_color"></p>
													<p class="mjschool-user-report-label"><?php esc_html_e( 'Teachers', 'mjschool' ); ?></p>
												</div>
												<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
													<p class="mjschool-users-report-dot-color mjschool_yellow_color"></p>
													<p class="mjschool-user-report-label"><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></p>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								?>
								<!-- USER REPORT CARD END. -->
								<!-- STUDENT STATUS REPORT CARD START. -->
								<?php
								if ( $user_role === 'supportstaff' || $user_role === 'teacher' ) {
									if ( $dashboard_result['mjschool_student_status_chart'] === 'yes' ) {
										?>
										<div class="col-lg-4 col-md-4 col-xs-12 col-sm-12 mjschool-responsive-div-dashboard">
											<div class="panel mjschool-panel-white mjschool-line-chat mjchool_height_400px">
												<div class="mjschool-panel-heading mb-2 mjschool-line-chat-p" id="mjschool-line-chat-p">
													<h3 class="mjschool-panel-title mjschool_float_left"><?php esc_html_e( 'Student Status', 'mjschool' ); ?></h3>
													<?php
													$page        = 'student';
													$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
													if ( isset( $user_access['view'] ) && $user_access['view'] === '1' ) {
														 ?>
														<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=student' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
														<?php 
													}
													?>
												</div>
												<!-- Lol. -->
												<div class="mjschool-member-chart">
													<div class="outer">
														<?php
														$user_query      = mjschool_approve_student_list();
														$inactive        = ! empty( $user_query ) ? count( $user_query ) : 0;
														$approve_student = mjschool_get_all_student_list();
														$approve         = ! empty( $approve_student ) ? count( $approve_student ) : 0;
														?>
														<canvas id="studentContainer" width="300" height="250" data-inactive="<?php echo esc_js($inactive); ?>" data-active="<?php echo esc_js($approve); ?>"></canvas>
														<p class="percent">
															<?php
															$total_student   = $inactive + $approve;
															echo (int) $total_student;
															?>
														</p>
														<p class="percent_report"> <?php esc_html_e( 'Student Status', 'mjschool' ); ?> </p>
													</div>
												</div>
												<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
													<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_dark_orange_color"></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Inactive Students', 'mjschool' ); ?></p>
													</div>
													<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_lime_color" ></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Active Students', 'mjschool' ); ?></p>
													</div>
												</div>
											</div>
										</div>
										<?php
									}
								}
								?>
								<!-- PAYMENT STATUS REPORT CARD START. -->
								<?php
								if ( $user_role != 'teacher' ) {
									if ( $dashboard_result['mjschool_payment_status_chart'] === 'yes' ) {
										?>
										<div class="col-lg-4 col-md-4 col-xs-12 col-sm-12 mjschool-responsive-div-dashboard">
											<div class="panel mjschool-panel-white mjschool-line-chat mjchool_height_400px">
												<div class="mjschool-panel-heading mb-2 mjschool-line-chat-p" id="mjschool-line-chat-p">
													<h3 class="mjschool-panel-title mjschool_float_left"><?php esc_html_e( 'Payment Status', 'mjschool' ); ?></h3>
													<?php
													$page        = 'feepayment';
													$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
													if ( isset( $user_access['view'] ) && $user_access['view'] === '1' ) {
														 ?>
														<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
														<?php 
													}
													?>
												</div>
												<div class="mjschool-member-chart">
													<div class="outer">
														<?php
														$total           = mjschool_get_payment_amout_by_payment_status( 'total' );
														$paid            = mjschool_get_payment_amout_by_payment_status( 'Fully Paid' );
														$unpaid          = $total - $paid;
														?>
														<canvas id="paymentstatusContainer" width="300" height="250" data-paid="<?php echo esc_attr( number_format( $paid, 2, '.', '' ) ); ?>" data-unpaid="<?php echo esc_attr( number_format( $unpaid, 2, '.', '' ) ); ?>" data-symbol="<?php echo esc_attr( html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) ) ); ?>"></canvas>
														<p class="percent">
															<?php
															$currency_symbol = html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) );
															echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $total, 2, '.', '' ) ) );
															?>
														</p>
														<p class="percent_report"> <?php esc_html_e( 'Payment Status', 'mjschool' ); ?> </p>
													</div>
												</div>
												<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
													<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_green_colors"></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Paid', 'mjschool' ); ?></p>
													</div>
													<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_unpaid_color" ></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Unpaid', 'mjschool' ); ?></p>
													</div>
												</div>
											</div>
										</div>
										<?php
									}
								}
								?>
								<!-- PAYMENT STATUS REPORT CARD END. -->
								<!-- ATTENDANCE REPORT CARD START. -->
								<?php
								if ( $user_role === 'supportstaff' || $user_role === 'teacher' ) {
									if ( $dashboard_result['mjschool_attendance_chart'] === 'yes' ) {
										?>
										<div class="col-lg-4 col-md-4 col-xl-4 col-sm-4 mjschool-responsive-div-dashboard">
											<div class="panel mjschool-panel-white mjschool-line-chat mjchool_height_400px">
												<div class="row mb-3">
													<div class="col-6 col-lg-6 col-md-6 col-xl-6 mjschool-attendance-report-title">
														<h3 class="mjschool-panel-title mjschool_font_20px"><?php esc_html_e( 'Attendance', 'mjschool' ); ?></h3>
													</div>
													<div class="col-6 col-lg-6 col-md-6 col-xl-6 mjschool-padding-right-25">
														<select class="form-control mjschool-attendance-report-filter mjschool-dash-report-filter" name="date_type" autocomplete="off">
															<option value="today"><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
															<option value="this_week"><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
															<option value="last_week"><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
															<option value="this_month" selected><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
															<option value="last_month"><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
															<option value="last_3_month"><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
															<option value="last_6_month"><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
															<option value="last_12_month"><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
															<option value="this_year"><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
															<option value="last_year"><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
														</select>
													</div>
												</div>
												<div class="mjschool-member-chart">
													<div class="outer mjschool-attendance-report-load">
														
														<?php
														$result     = mjschool_all_date_type_value( 'this_month' );
														$response   = json_decode( $result );
														$start_date = $response[0];
														$end_date   = $response[1];
														$present    = mjschool_attendance_data_by_status( $start_date, $end_date, 'Present' );
														$absent     = mjschool_attendance_data_by_status( $start_date, $end_date, 'Absent' );
														$late       = mjschool_attendance_data_by_status( $start_date, $end_date, 'Late' );
														$halfday    = mjschool_attendance_data_by_status( $start_date, $end_date, 'Half Day' );
														?>
														<canvas id="chartJSContainerattendance" width="300" height="250" data-present="<?php echo esc_js($present); ?>" data-absent="<?php echo esc_js($absent); ?>" data-late="<?php echo esc_js($late); ?>" data-halfday="<?php echo esc_js($halfday); ?>"></canvas>
														<p class="percent">
															<?php
															$attendance = $present + $absent + $late + $halfday;
															echo esc_html( $attendance );
															?>
														</p>
														<p class="percent_report"> <?php esc_html_e( 'Attendance', 'mjschool' ); ?> </p>
													</div>
												</div>
												<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
													<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-6 col-xs-6 mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_green_colors"></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Present', 'mjschool' ); ?></p>
													</div>
													<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-6 col-xs-6 mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_unpaid_color"></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Absent', 'mjschool' ); ?></p>
													</div>
													<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-6 col-xs-6 mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_yellow_color"></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Late', 'mjschool' ); ?></p>
													</div>
													<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-6 col-xs-6 mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_bule_color" ></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Half Day', 'mjschool' ); ?></p>
													</div>
												</div>
											</div>
										</div>
										<?php
									}
								}
								?>
								<!-- ATTENDANCE REPORT CARD END. -->
								<!-- ATTENDANCE REPORT CARD START. -->
								<?php
								if ( $user_role === 'supportstaff' ) {
									if ( $dashboard_result['mjschool_payment_report'] === 'yes' ) {
										?>
										<div class="col-lg-4 col-md-4 col-xl-4 col-sm-4 mjschool-responsive-div-dashboard">
											<div class="panel mjschool-panel-white mjschool-line-chat mjchool_height_400px">
												<div class="row mb-3">
													<div class="col-6 col-lg-6 col-md-6 col-xl-6 mjschool-attendance-report-title">
														<h3 class="mjschool-panel-title mjschool_font_20px"><?php esc_html_e( 'Payment', 'mjschool' ); ?></h3>
													</div>
													<div class="col-6 col-lg-6 col-md-6 col-xl-6 mjschool-padding-right-25">
														<select class="form-control payment_report_filter mjschool-dash-report-filter" name="date_type" autocomplete="off">
															<option value="today"><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
															<option value="this_week"><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
															<option value="last_week"><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
															<option value="this_month" selected><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
															<option value="last_month"><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
															<option value="last_3_month"><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
															<option value="last_6_month"><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
															<option value="last_12_month"><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
															<option value="this_year"><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
															<option value="last_year"><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
														</select>
													</div>
												</div>
												<div class="mjschool-member-chart">
													<div class="outer mjschool-payment-report-load">
														
														<?php
														$result       = mjschool_all_date_type_value( 'this_month' );
														$response     = json_decode( $result );
														$start_date   = $response[0];
														$end_date     = $response[1];
														$cash_payment = mjschool_get_payment_paid_data_by_date_method( 'Cash', $start_date, $end_date );
														if ( ! empty( $cash_payment ) ) {
															$cashAmount = 0;
															foreach ( $cash_payment as $cash ) {
																$cashAmount += $cash->amount;
															}
														} else {
															$cashAmount = 0;
														}
														$Cheque_payment = mjschool_get_payment_paid_data_by_date_method( 'Cheque', $start_date, $end_date );
														if ( ! empty( $Cheque_payment ) ) {
															$chequeAmount = 0;
															foreach ( $Cheque_payment as $cheque ) {
																$chequeAmount += $cheque->amount;
															}
														} else {
															$chequeAmount = 0;
														}
														$bank_payment = mjschool_get_payment_paid_data_by_date_method( 'Bank Transfer', $start_date, $end_date );
														if ( ! empty( $bank_payment ) ) {
															$bankAmount = 0;
															foreach ( $bank_payment as $bank ) {
																$bankAmount += $bank->amount;
															}
														} else {
															$bankAmount = 0;
														}
														$paypal_payment = mjschool_get_payment_paid_data_by_date_method( 'PayPal', $start_date, $end_date );
														if ( ! empty( $paypal_payment ) ) {
															$paypalAmount = 0;
															foreach ( $paypal_payment as $paypal ) {
																$paypalAmount += $paypal->amount;
															}
														} else {
															$paypalAmount = 0;
														}
														$stripe_payment = mjschool_get_payment_paid_data_by_date_method( 'Stripe', $start_date, $end_date );
														if ( ! empty( $stripe_payment ) ) {
															$stripeAmount = 0;
															foreach ( $stripe_payment as $stripe ) {
																$stripeAmount += $stripe->amount;
															}
														} else {
															$stripeAmount = 0;
														}
														$Total_amount    = $cashAmount + $chequeAmount + $bankAmount + $paypalAmount + $stripeAmount;
														?>
														<canvas id="chartJSContainerpayment" width="300" height="250" data-cash="<?php echo esc_js($cashAmount); ?>" data-cheque="<?php echo esc_js($chequeAmount); ?>" data-bank="<?php echo esc_js($bankAmount); ?>" data-paypal="<?php echo esc_js($paypalAmount); ?>" data-stripe="<?php echo esc_js($stripeAmount); ?>" data-symbol="<?php echo esc_js(html_entity_decode(mjschool_get_currency_symbol(get_option('mjschool_currency_code')))); ?>"></canvas>
														<p class="percent">
															<?php
															$currency_symbol = html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) );
															echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Total_amount, 2, '.', '' ) ) );
															?>
														</p>
														<p class="percent_report"> <?php esc_html_e( 'Payment Report', 'mjschool' ); ?> </p>
													</div>
												</div>
												<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
													<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-4 col-xs-4 mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_gray_color"></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'PayPal', 'mjschool' ); ?></p>
													</div>
													<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-4 col-xs-4 mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_purple_color"></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Stripe', 'mjschool' ); ?></p>
													</div>
													<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-4 col-xs-4 mjschool-users-report-label ps-1">
														<p class="mjschool-users-report-dot-color mjschool_light_orange_color" ></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Cash', 'mjschool' ); ?></p>
													</div>
													<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-4 col-xs-4 mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_sky_color"></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Cheque', 'mjschool' ); ?></p>
													</div>
													<div class="col-8 col-sm-4 col-md-6 col-lg-6 col-xl-8 col-xs-8 mjschool-users-report-label ps-2">
														<p class="mjschool-users-report-dot-color mjschool_yellow_color"></p>
														<p class="mjschool-user-report-label"><?php esc_html_e( 'Bank Transfer', 'mjschool' ); ?></p>
													</div>
												</div>
											</div>
										</div>
										<?php
									}
								}
								?>
								<!-- ATTENDANCE REPORT CARD END. -->
								<!-- STUDENT STATUS REPORT CARD END. -->
								<?php
								if ( $dashboard_result['mjschool_invoice_chart'] === 'yes' ) {
									if ( $user_role != 'teacher' ) {
										?>
										<div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
											<div class="panel mjschool-panel-white mjschool-admmision-div mjchool_height_400px">
												<div class="mjschool-panel-heading mjschool-line-chat-p" id="mjschool-line-chat-p">
													<h3 class="mjschool-panel-title"><?php esc_html_e( 'Fees Payment', 'mjschool' ); ?></h3>
													<?php
													$page       = 'feepayment';
													$feepayment = mjschool_page_access_role_wise_access_right_dashboard( $page );
													if ( $feepayment === '1' ) {
														?>
														
														<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist' )); ?>">
															<img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>">
														</a>
														<?php 
													}
													?>
												</div>
												<div class="mjschool-panel-body">
													<div class="events1">
														<?php
														$obj_feespayment         = new Mjschool_Feespayment();
														$i                       = 0;
														$user_id                 = get_current_user_id();
														$feespayment_data        = mjschool_user_wise_fees_payment_for_dashboard( $user_id, $user_role );
														$page                    = 'feepayment';
														$feepayment_access_right = mjschool_get_user_role_wise_filter_access_right_array( $page );
														if ( ! empty( $feespayment_data ) ) {
															foreach ( $feespayment_data as $retrieved_data ) {
																if ( $i === 0 ) {
																	$color_class_css = 'mjschool-assign-bed-color0';
																} elseif ( $i === 1 ) {
																	$color_class_css = 'mjschool-assign-bed-color1';
																} elseif ( $i === 2 ) {
																	$color_class_css = 'mjschool-assign-bed-color2';
																} elseif ( $i === 3 ) {
																	$color_class_css = 'mjschool-assign-bed-color3';
																} elseif ( $i === 4 ) {
																	$color_class_css = 'mjschool-assign-bed-color4';
																}
																?>
																<div class="mjschool-fees-payment-height calendar-event">
																	<p class="mjschool-remainder-title Bold viewbedlist mjschool-show-task-event mjschool-date-font-size" id="<?php echo esc_attr( $retrieved_data->fees_pay_id ); ?>" model="Feespayment Details">
																		<span class="mjschool-date-assign-bed-label">
																			<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->total_amount, 2, '.', '' ) ) ); ?>
																		</span>
																		<span class=" <?php echo esc_attr( $color_class_css ); ?>"></span>
																	</p>
																	<p class="mjschool-remainder-date mjschool-assign-bed-name mjschool-assign-bed-name-size">
																		<?php
																		$student_data = get_userdata( $retrieved_data->student_id );
																		if ( ! empty( $student_data ) ) {
																			echo esc_html( $student_data->display_name );
																		} else {
																			esc_html_e( 'N/A', 'mjschool' );
																		}
																		?>
																	</p>
																	<p class="mjschool-remainder-date mjschool-assign-bed-date mjschool-assign-bed-name-size">
																		<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?>
																	</p>
																</div>
																<?php
																++$i;
															}
														} elseif ( $feepayment_access_right['add'] === '1' ) {
															 ?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																<div class="col-md-12 mjschool-dashboard-btn">
																	<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=feepayment&tab=addpaymentfee' )); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'Fees Payment', 'mjschool' ); ?></a>
																</div>
															</div>
															<?php
														} else {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
															</div>
															<?php
														}
														?>
													</div>
												</div>
											</div>
										</div>
										<?php
									}
								}
								?>
							</div>
							<!-- Four Card , Chart and Fees Payment Row Div.  -->
							<!-- Celendar And Chart Row.  -->
							<div class="row calander-chart-div">
								<?php
								if ($user_role === 'student' ) {
									$class_id = get_user_meta(get_current_user_id(), 'class_name', true);
									$section_name = get_user_meta(get_current_user_id(), 'class_section', true);
									?>
									<div class="col-lg-6 col-md-6 col-xs-12 col-sm-12">
										<div class="mjschool-qr-code panel">
											<div class="mjschool-qr-code-card mjschool-student-qr">
												<div class="mjschool-qr-main-div">
													<h3><?php esc_html_e( 'Scan Below QR For Attendance', 'mjschool' ); ?></h3>
													<div class="mjschool-qr-image-div"><img class="mjschool-id-card-barcode qr_width" id='barcode' src=''></div>
												</div>
												
											</div>
										</div>
									</div>
									<?php
								}
								?>
								<!-- Calendar div start.  -->
								<div <?php if ( $user_role === 'teacher' || $user_role === 'supportstaff' || $user_role === 'parent' ) { ?> class="col-lg-12 col-md-12 col-xs-12 col-sm-12" <?php } else { ?> class="col-lg-6 col-md-6 col-xs-12 col-sm-12" <?php } ?>>
									<div class="mjschool-calendar panel">
										<div class="row mjschool-panel-heading activities">
											<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
												<h3 class="mjschool-panel-title mjschool-calander-heading-title-width"><?php esc_html_e( 'Calendar', 'mjschool' ); ?></h3>
											</div>
											<div class="mjschool-cal-py col-sm-12 col-md-8 col-lg-8 col-xl-8 mjschool-celender-dot-div">
												<div class="mjschool-card-head">
													<ul class="mjschool-cards-indicators mjschool-right">
														<!--Set calendar-header event-list Start. -->
														<li><span class="mjschool-indic mjschool-blue-indic"></span> <?php esc_html_e( 'Holiday', 'mjschool' ); ?></li>
														<li><span class="mjschool-indic mjschool-yellow-indic"></span> <?php esc_html_e( 'Notice', 'mjschool' ); ?></li>
														<li><span class="mjschool-indic mjschool-perple-indic"></span> <?php esc_html_e( 'Exam', 'mjschool' ); ?></li>
														<li><span class="mjschool-indic mjschool-light-blue-indic"></span> <?php esc_html_e( 'Event', 'mjschool' ); ?></li>
														<!--Set calendar-header event-list End. -->
													</ul>
												</div>
											</div>
										</div>
										<div class="mjschool-cal-py mjschool-calender-margin-top">
											<div id="calendar"></div>
										</div>
									</div>
								</div>
								<?php
								if ( $user_role === 'student' ) {
									$page     = 'schedule';
									$schedule = mjschool_get_user_role_wise_filter_access_right_array( $page );
									if ( $schedule['view'] === '1' ) {
										$class       = $school_obj->class_info;
										$sectionname = '';
										$section     = 0;
										$section     = get_user_meta( get_current_user_id(), 'class_section', true );
										if ( $section != '' ) {
											$sectionname = mjschool_get_section_name( $section );
										} else {
											$section = 0;
										}
										?>
										<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
											<div class="panel mjschool-panel-white event operation mjschool_height_auto">
												<div class="mjschool-panel-heading">
													
													<h3 class="mjschool-panel-title"><?php esc_html_e( 'My Class Timetable', 'mjschool' ); ?></h3>
													<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=schedule' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
													
												</div>
												<div class="mjschool-panel-body">
													<table class="table table-bordered" cellspacing="0" cellpadding="0" border="0">
														<?php
														foreach ( mjschool_day_list() as $daykey => $dayname ) {
															?>
															<tr>
																<th><?php echo esc_html( $dayname ); ?></th>
																<td>
																	<?php
																	$period = $obj_route->mjschool_get_period( $class->class_id, $section, $daykey );
																	if ( ! empty( $period ) ) {
																		foreach ( $period as $period_data ) {
																			$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
																			if ( ! empty( $meeting_data ) ) {
																				$data_toggle = 'data-bs-toggle="dropdown"';
																			} else {
																				$data_toggle = '';
																			}
																			echo '<div class="btn-group m-b-sm">';
																			echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" aria-expanded="false" ' . esc_attr( $data_toggle ) . '><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) );
																			$start_time_data = explode( ':', $period_data->start_time );
																			$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
																			$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
																			$end_time_data   = explode( ':', $period_data->end_time );
																			$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
																			$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
																			$class_room_acc = get_option( 'mjschool_class_room' );
																			if ($class_room_acc === 1) {	
																				$class_room = mjschool_get_class_room_name($period_data->room_id);
																				if( ! empty( $class_room ) )
																				{
																					echo '<span class="time"> ( ' . esc_html( $class_room->room_name) . ' ) </span>';
																				}
																			}
																			echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ) </span>';
																			$virtual_classroom_page_name    = 'virtual_classroom';
																			$virtual_classroom_access_right = mjschool_get_user_role_wise_filter_access_right_array( $virtual_classroom_page_name );
																			if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
																				if ( $virtual_classroom_access_right['view'] === '1' ) {
																					if ( ! empty( $meeting_data ) ) {
																						$meeting_join_link = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . $meeting_data->meeting_join_link . '" target="_blank">' . esc_attr__( 'Join Virtual Class', 'mjschool' ) . '</a></li>';
																					} else {
																						$meeting_join_link = '';
																					}
																				}
																			}
																			echo "<span class='caret'></span></button>";
																			echo '<ul role="menu" class="dropdown-menu schedule_menu"> ' . esc_url( $meeting_join_link ) . ' </ul>';
																			echo '</div>';
																		}
																	}
																	?>
																</td>
															</tr>
															<?php
														}
														?>
													</table>
												</div>
											</div>
										</div>
										<?php
									}
								}
								?>
								<?php
								// ---------- Attendance report access right. ------------//
								$page               = 'class';
								$class_access_right = mjschool_get_user_role_wise_filter_access_right_array( $page );
								if ( ! empty( $class_access_right ) ) {
									if ( $class_access_right['view'] === '1' ) {
										?>
										<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
											<div class="panel mjschool-panel-white event priscription">
												<div class="mjschool-panel-heading">
													
													<h3 class="mjschool-panel-title"><?php esc_html_e( 'Class', 'mjschool' ); ?></h3>
													<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=class' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
													
												</div>
												<div class="mjschool-panel-body class_padding">
													<div class="events1">
														<?php
														$page        = 'class';
														$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
														$tablename   = 'mjschool_class';
														$user_id     = get_current_user_id();
														$own_data    = $user_access['own_data'];
														if ( $school_obj->role === 'teacher' ) {
															if ( $own_data === '1' ) {
																$class_id = get_user_meta( get_current_user_id(), 'class_name', true );
																$result   = mjschool_get_all_class_data_by_class_array( $class_id );
															} else {
																$result = mjschool_get_all_data( $tablename );
															}
														}
														// ------- Exam data for support staff. ---------//
														elseif ( $own_data === '1' ) {
															$result = mjschool_get_all_class_created_by_user( $user_id );
														} else {
															$result = mjschool_get_all_data( $tablename );
														}
														$class_data = array_slice( $result, 0, 5 );
														$i          = 0;
														if ( ! empty( $class_data ) ) {
															foreach ( $class_data as $retrieved_data ) {
																$class_id = $retrieved_data->class_id;
																
																$user = count(get_users(array(
																	'meta_key' => 'class_name',
																	'meta_value' => $class_id
																 ) ) );
																
																if ( $i === 0 ) {
																	$color_class_css = 'mjschool-class-color0';
																} elseif ( $i === 1 ) {
																	$color_class_css = 'mjschool-class-color1';
																} elseif ( $i === 2 ) {
																	$color_class_css = 'mjschool-class-color2';
																} elseif ( $i === 3 ) {
																	$color_class_css = 'mjschool-class-color3';
																} elseif ( $i === 4 ) {
																	$color_class_css = 'mjschool-class-color4';
																}
																 ?>
																<div class="row mjschool-group-list-record mjschool-profile-image-class mjschool-class-record-height">
																	<div class="mjschool-cursor-pointer col-sm-2 col-md-2 col-lg-2 col-xl-2 <?php echo esc_attr($color_class_css); ?> mjschool-remainder-title mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment mjschool-class-color0" id="<?php echo esc_attr($retrieved_data->class_id); ?>" model="Class Details">
																		<img class="mjschool-class-image-1 " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-class.png"); ?>">
																	</div>
																	<div class="d-flex align-items-center col-sm-7 col-md-7 col-lg-7 col-xl-7 mjschool-group-list-record-col-img">
																		<div class="mjschool-cursor-pointer mjschool-class-font-color mjschool-group-list-group-name mjschool-remainder-title-pr Bold viewdetail mjschool-show-task-event" id="<?php echo esc_attr($retrieved_data->class_id); ?>" model="Class Details">
																			<span><?php echo esc_html( $retrieved_data->class_name); ?></span>
																		</div>
																	</div>
																	<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 justify-content-end d-flex align-items-center mjschool-group-list-record-col-count">
																		<div class="mjschool-group-list-total-group">
																			<?php echo esc_html( $user) . ' ';
																			esc_html_e( 'Out Of', 'mjschool' );
																			echo ' ' . esc_html( $retrieved_data->class_capacity);?>
																		</div>
																	</div>
																</div>
																<?php
																$i++;
															}
														} else {
															if ($class_access_right['add'] === '1') {
																?>
																<div class="mjschool-calendar-event-new">
																	<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																	<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
																		<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=class&tab=addclass' )); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Class', 'mjschool' ); ?></a>
																	</div>
																</div>
																<?php
															} else {
																?>
																<div class="mjschool-calendar-event-new">
																	<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																</div>
																<?php
															}
														}
														?>
													</div>
												</div>
											</div>
										</div>
										<?php
									}
								}
								$page = 'homework';
								$homework_access_right = mjschool_get_user_role_wise_filter_access_right_array($page);
								if ($homework_access_right['view'] === '1') {
									?>
									<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
										<div class="panel mjschool-panel-white event operation">
											<div class="mjschool-panel-heading">
												<?php
												if ($user_role === 'parent' || $user_role === 'student' ) {
													?>
													<h3 class="mjschool-panel-title"><?php esc_html_e( 'My Homework', 'mjschool' ); ?></h3>
													<?php
												} else {
													?>
													<h3 class="mjschool-panel-title"><?php esc_html_e( 'Homework List', 'mjschool' ); ?></h3>
													<?php
												}
												?>
												<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=homework' )); ?>"><img class="mjschool-vertical-align-unset " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											</div>
											<div class="mjschool-panel-body">
												<div class="events mjschool-rtl-notice-css">
													<?php
													$homework_data = mjschool_get_homework_data_for_frontend_dashboard();
													$i = 0;
													if ( ! empty( $homework_data ) ) {
														foreach ($homework_data as $retrieved_data) {
															if ($i === 0) {
																$color_class_css = 'mjschool-class-color0';
															} elseif ($i === 1) {
																$color_class_css = 'mjschool-class-color1';
															} elseif ($i === 2 ) {
																$color_class_css = 'mjschool-class-color2';
															} elseif ($i === 3) {
																$color_class_css = 'mjschool-class-color3';
															} elseif ($i === 4) {
																$color_class_css = 'mjschool-class-color4';
															}
															?>
															<div class="calendar-event mjschool-profile-image-class">
																<p class="mjschool-cursor-pointer mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment <?php echo esc_attr($color_class_css); ?>" id="<?php echo esc_attr($retrieved_data->homework_id); ?>" model="homework Details">
																	<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png"); ?>">
																</p>
																<p class="mjschool-cursor-pointer mjschool-padding-top-5px-res mjschool-remainder-title-pr mjschool-card-content-width mjschool-show-task-event mjschool-padding-top-card-content mjschool-view-priscription mjschool-class-width mjschool-homework-dashboard-rtl mjschool_color_dark"  id="<?php echo esc_attr($retrieved_data->homework_id); ?>" model="homework Details">
																	<?php echo esc_html( $retrieved_data->title); ?>
																</p>
																<p class="mjschool-remainder-date-pr mjschool-date-background mjschool-class-width mjschool-homework-date-rtl"> <span class="mjschool-label-for-date"><?php echo esc_attr( mjschool_get_date_in_input_box($retrieved_data->submition_date ) ); ?></span> </p>
																<p class="mjschool-remainder-title-pr mjschool-view-priscription mjschool-card-content-width mjschool-class-width mjschool-assign-bed-name1 mjschool-card-margin-top mjschool-homework-dashboard-rtl">
																	<?php echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_name, $retrieved_data->section_id ) ); ?>
																</p>
															</div>
															<?php
															$i++;
														}
													} else {
														if ($homework_access_right['add'] === '1') {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
																	<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=homework&tab=addhomework' )); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'Add Homework', 'mjschool' ); ?></a>
																</div>
															</div>
															<?php
														} else {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
															</div>
															<?php
														}
													}
													?>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								//------------ Exam Page Access Right. ------------//
								$page = 'exam';
								$exam_access_right = mjschool_get_user_role_wise_filter_access_right_array($page);
								if ($exam_access_right['view'] === '1') {
									?>
									<!-- Exam div start.  -->
									<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
										<div class="panel mjschool-panel-white event operation">
											<div class="mjschool-panel-heading">
												<h3 class="mjschool-panel-title"><?php esc_html_e( 'Exam List', 'mjschool' ); ?></h3>
												<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=exam' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											</div>
											<div class="mjschool-panel-body">
												<div class="events">
													<?php
													$exam = new Mjschool_Exam();;
													$examdata = mjschool_exam_list_data_with_access_for_dashboard($user_role);
													$i = 0;
													if ( ! empty( $examdata ) ) {
														foreach ($examdata as $retrieved_data) {
															$cid = $retrieved_data->class_id;
															if ($i === 0) {
																$color_class_css = 'mjschool-class-color0';
															} elseif ($i === 1) {
																$color_class_css = 'mjschool-class-color1';
															} elseif ($i === 2 ) {
																$color_class_css = 'mjschool-class-color2';
															} elseif ($i === 3) {
																$color_class_css = 'mjschool-class-color3';
															} elseif ($i === 4) {
																$color_class_css = 'mjschool-class-color4';
															}
															?>
															<div class="mjschool-calendar-event-p calendar-event view-complaint">
																<p class="mjschool-cursor-pointer mjschool-exam-list-img mjschool-show-task-event <?php echo esc_attr($color_class_css); ?>" id="<?php echo esc_attr($retrieved_data->exam_id); ?>" model="Exam Details">
																	<img class="mjschool-class-image-1 mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png"); ?>">
																</p>
																<p class="mjschool-cursor-pointer mjschool-exam-remainder-title-pr mjschool-remainder-title-pr Bold mjschool-view-priscription mjschool-show-task-event" id="<?php echo esc_attr($retrieved_data->exam_id); ?>" model="Exam Details">
																	<?php echo esc_html( $retrieved_data->exam_name); ?>&nbsp;&nbsp;<span class="smgt_exam_start_date">
																	<?php echo esc_html( get_the_title($retrieved_data->exam_term ) ); ?>&nbsp;|&nbsp;<?php echo esc_html( mjschool_get_class_name($cid ) ); ?></span>
																</p>
																<p class="mjschool-exam-remainder-title-pr mjschool-description-line">
																	<span class="smgt_activity_date" id="smgt_start_date_end_date"><?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->exam_start_date ) ); ?>&nbsp;|&nbsp;<?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->exam_end_date ) ); ?></span>
																</p>
															</div>
															<?php
															$i++;
														}
													} else {
														if ($exam_access_right['add'] === '1') {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
																	<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=exam&tab=addexam' )); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Exam', 'mjschool' ); ?></a>
																</div>
															</div>
															<?php
														} else {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
															</div>
															<?php 
														}
													}
													?>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								?>
								<!-- Class and Exam list Row End. -->
								<!-- Notice and Massage Row Div Start.  -->
								<?php
								// ------------ Notice Page Access Right. ------------//
								$page                = 'notice';
								$notice_access_right = mjschool_get_user_role_wise_filter_access_right_array( $page );
								if ( $notice_access_right['view'] === '1' ) {
									?>
									<div class="col-sm-12 col-md-6 col-lg-6 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
										<div class="panel mjschool-panel-white event">
											<div class="mjschool-panel-heading">
												
												<h3 class="mjschool-panel-title"><?php esc_html_e( 'Notice', 'mjschool' ); ?></h3>
												<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notice' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											</div>
											<div class="mjschool-panel-body">
												<div class="events">
													<?php
													$retrieve_class_data = mjschool_notice_list_with_user_access_right($user_role);
													$format = get_option( 'date_format' );
													$i = 0;
													if ( ! empty( $retrieve_class_data ) ) {
														foreach ($retrieve_class_data as $retrieved_data) {
															if ($i === 0) {
																$color_class_css = 'mjschool-notice-color0';
															} elseif ($i === 1) {
																$color_class_css = 'mjschool-notice-color1';
															} elseif ($i === 2 ) {
																$color_class_css = 'mjschool-notice-color2';
															} elseif ($i === 3) {
																$color_class_css = 'mjschool-notice-color3';
															} elseif ($i === 4) {
																$color_class_css = 'mjschool_notice_color4';
															}
															?>
															<div class="calendar-event mjschool-notice-div <?php echo esc_attr($color_class_css); ?>">
																<div class="mjschool-notice-div-contant mjschool-profile-image-prescription">
																	<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 notice_description_div">
																		<p class="mjschool-cursor-pointer mjschool-remainder-title Bold viewdetail mjschool-notice-descriptions mjschool-show-task-event notice_heading mjschool-notice-content-rs mjschool_width_100px" id="<?php echo esc_attr($retrieved_data->ID); ?>" model="Noticeboard Details" >
																			<span class="notice_heading_label notice_heading">
																				<?php echo esc_html( $retrieved_data->post_title); ?>
																				<a href="#" class="notice_date_div">
																					<?php echo esc_html( mjschool_get_date_in_input_box(get_post_meta($retrieved_data->ID, 'start_date', true ) ) ); ?> &nbsp;|&nbsp; <?php echo esc_html( mjschool_get_date_in_input_box(get_post_meta($retrieved_data->ID, 'end_date', true ) ) ); ?>
																				</a>
																			</span>
																		</p>
																		<p class="mjschool-cursor-pointer mjschool-remainder-title viewdetail mjschool-notice-descriptions mjschool_width_100px" ><?php echo esc_html( $retrieved_data->post_content); ?></p>
																	</div>
																</div>
															</div>
															<?php
															$i++;
														}
													} else {
														if ($notice_access_right['add'] === '1') {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
																	<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notice&tab=addnotice' )); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Notice', 'mjschool' ); ?></a>
																</div>
															</div>
															<?php
														} else {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
															</div>
															<?php
														}
													}
													?>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								//------------ Event Page Access Right. ------------//
								$page = 'event';
								$event_access_right = mjschool_get_user_role_wise_filter_access_right_array($page);
								if ($event_access_right['view'] === '1') {?>
									<div class="col-sm-12 col-md-6 col-lg-6 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
										<div class="panel mjschool-panel-white massage">
											<div class="mjschool-panel-heading">
												<h3 class="mjschool-panel-title"><?php esc_html_e( 'Event List', 'mjschool' ); ?></h3>
												<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=event&tab=eventlist' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											</div>
											<div class="mjschool-panel-body">
												<div class="events mjschool-notice-content-div">
													<?php
													$event_data = $obj_event->mjschool_get_all_event_for_dashboard();
													$i = 0;
													if ( ! empty( $event_data ) ) {
														foreach ($event_data as $retrieved_data) {
															if ($i === 0) {
																$color_class_css = 'mjschool-class-color0';
															} elseif ($i === 1) {
																$color_class_css = 'mjschool-class-color1';
															} elseif ($i === 2 ) {
																$color_class_css = 'mjschool-class-color2';
															} elseif ($i === 3) {
																$color_class_css = 'mjschool-class-color3';
															} elseif ($i === 4) {
																$color_class_css = 'mjschool-class-color4';
															}
															?>
															<div class="calendar-event mjschool-profile-image-class">
																<p class="mjschool-cursor-pointer mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment <?php echo esc_attr($color_class_css); ?>" id="<?php echo esc_attr($retrieved_data->event_id); ?>" model="Event Details">
																	<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-notice.png"); ?>">
																</p>
																<p class="mjschool-cursor-pointer mjschool-padding-top-5px-res mjschool-remainder-title-pr mjschool-card-content-width mjschool-show-task-event mjschool-padding-top-card-content mjschool-view-priscription mjschool-class-width mjschool_color_dark" id="<?php echo esc_attr($retrieved_data->event_id); ?>" model="Event Details">
																	<?php echo esc_html( $retrieved_data->event_title); ?>
																</p>
																<p class="mjschool-remainder-date-pr mjschool-date-background mjschool-class-width"> <span class="mjschool-label-for-date"><?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->start_date ) ); ?></span> </p>
																<p class="mjschool-remainder-title-pr mjschool-view-priscription mjschool-card-content-width mjschool-class-width mjschool-assign-bed-name1 mjschool-card-margin-top">
																	<?php
																	$strlength = strlen($retrieved_data->description);
																	if ($strlength > 90) {
																		echo esc_html( substr( $retrieved_data->description, 10, 90 ) ) . '...';
																	} else {
																		echo esc_html( $retrieved_data->description);
																	}
																	?>
																</p>
															</div>
															<?php
															$i++;
														}
													} else {
														if ($event_access_right['add'] === '1') {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
																	<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=event&tab=add_event' )); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'Add Event', 'mjschool' ); ?></a>
																</div>
															</div>
															<?php
														} else {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
															</div>
															<?php 
														}
													}
													?>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								// ------------ Notification Page Access Right. ------------//
								$page                      = 'notification';
								$notification_access_right = mjschool_get_user_role_wise_filter_access_right_array( $page );
								if ( $notification_access_right['view'] === '1' ) {
									?><!-- Holiday And Notification Row Div Start.  -->
									<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
										<div class="panel mjschool-panel-white event priscription">
											<div class="mjschool-panel-heading">
												
												<h3 class="mjschool-panel-title"><?php esc_html_e( 'Notification', 'mjschool' ); ?></h3>
												<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notification' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
												
											</div>
											<div class="mjschool-panel-body mjschool-message-rtl-css">
												<div class="events1">
													<?php
													$user_id = get_current_user_id();
													if ( $school_obj->role === 'student' ) {
														$notification_data = mjschool_get_student_own_notification_created_by_for_dashboard( $user_id );
													} elseif ( $school_obj->role === 'teacher' ) {
														$notification_data = mjschool_get_all_notification_created_by_for_dashboard( $user_id );
													} elseif ( $school_obj->role === 'parent' ) {
														$notification_data = mjschool_get_all_notification_for_parent_for_dashboard( $user_id );
													} else {
														$notification_data = mjschool_get_all_notification_created_by( $user_id );
													}
													$i = 0;
													if ( ! empty( $notification_data ) ) {
														foreach ( $notification_data as $retrieved_data ) {
															if ( $i === 0 ) {
																$color_class_css = 'mjschool-class-color0';
															} elseif ( $i === 1 ) {
																$color_class_css = 'mjschool-class-color1';
															} elseif ( $i === 2 ) {
																$color_class_css = 'mjschool-class-color2';
															} elseif ( $i === 3 ) {
																$color_class_css = 'mjschool-class-color3';
															} elseif ( $i === 4 ) {
																$color_class_css = 'mjschool-class-color4';
															}
															?>
															<div class="calendar-event mjschool-profile-image-class">
																<p class="mjschool-cursor-pointer mjschool-remainder-title-pr Bold mjschool-view-priscription mjschool-show-task-event mjschool-class-tag <?php echo esc_attr( $color_class_css ); ?>" id="<?php echo esc_attr( $retrieved_data->notification_id ); ?>" model="Notification Details">
																	<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-notification.png"); ?>">
																</p>
																<p class="mjschool-cursor-pointer mjschool-padding-top-5px-res mjschool-card-content-width mjschool-remainder-title-pr mjschool-view-priscription mjschool-show-task-event mjschool-class-width mjschool-padding-top-card-content mjschool_color_dark" id="<?php echo esc_attr($retrieved_data->notification_id); ?>" model="Notification Details" >
																	<?php echo esc_html( $retrieved_data->title); ?>
																</p>
																<p class="mjschool-remainder-date-pr mjschool-date-background mjschool-class-width"> <span class="mjschool-label-for-date"><?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->created_date ) ); ?></span> </p>
																<p class="mjschool-remainder-title-pr mjschool-card-content-width mjschool-view-priscription mjschool-class-width mjschool-assign-bed-name1 mjschool-card-margin-top">
																	<?php echo esc_html( $retrieved_data->message); ?>
																</p>
															</div>
															<?php
															$i++;
														}
													} else {
														if ($notification_access_right['add'] === '1') {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
																	<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notification&tab=addnotification' )); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Notification', 'mjschool' ); ?></a>
																</div>
															</div>
															<?php
														} else {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
															</div>
															<?php
														}
													}
													?>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								//------------ Holiday Page Access Right. ------------//
								$page = 'holiday';
								$holiday_access_right = mjschool_get_user_role_wise_filter_access_right_array($page);
								if ($holiday_access_right['view'] === '1') {
									?><!------------ Notifincation div start.  ----------->
									<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
										<div class="panel mjschool-panel-white event operation">
											<div class="mjschool-panel-heading">
												<h3 class="mjschool-panel-title"><?php esc_html_e( 'Holiday List', 'mjschool' ); ?></h3>
												<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=holiday' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											</div>
											<div class="mjschool-panel-body">
												<div class="events">
													<?php
													$holidaydata = mjschool_holiday_dashboard();
													$i = 0;
													if ( ! empty( $holidaydata ) ) {
														foreach ($holidaydata as $retrieved_data) {
															if ($i === 0) {
																$color_class_css = 'mjschool-class-color0';
															} elseif ($i === 1) {
																$color_class_css = 'mjschool-class-color1';
															} elseif ($i === 2 ) {
																$color_class_css = 'mjschool-class-color2';
															} elseif ($i === 3) {
																$color_class_css = 'mjschool-class-color3';
															} elseif ($i === 4) {
																$color_class_css = 'mjschool-class-color4';
															}
															if ($retrieved_data->status === 0) {
																?>
																<div class="calendar-event mjschool-profile-image-class">
																	<p class="mjschool-cursor-pointer mjschool-remainder-title mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment <?php echo esc_attr($color_class_css); ?>" id="<?php echo esc_attr($retrieved_data->holiday_id); ?>" model="holiday Details">
																		<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-holiday.png"); ?>">
																	</p>
																	<p class="mjschool-cursor-pointer mjschool-holiday-list-description-res mjschool-remainder-title-pr mjschool-show-task-event mjschool-padding-top-card-content mjschool-view-priscription mjschool-holiday-width mjschool_color_dark"  id="<?php echo esc_html( $retrieved_data->holiday_id); ?>" model="holiday Details">
																		<?php echo esc_html( $retrieved_data->holiday_title); ?> <span class="date_div_color"><?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->date ) ); ?> | <?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->end_date ) ); ?></span>
																	</p>
																	<p class="mjschool-remainder-title-pr mjschool-holiday-list-description-res mjschool-view-priscription mjschool-holiday-width mjschool-assign-bed-name1 mjschool-card-margin-top">
																		<?php echo esc_html( $retrieved_data->description); ?>
																	</p>
																</div>
																<?php
															}
															$i++;
														}
													} else {
														if ($holiday_access_right['add'] === '1') {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
																	<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=holiday&tab=addholiday' )); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Holiday', 'mjschool' ); ?></a>
																</div>
															</div>
															<?php
														} else {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
															</div>
															<?php
														}
													}
													?>
												</div>
											</div>
										</div>
									</div>
									<!-- Notification Div End. -->
									<?php
								}
								//------------ Message Page Access Right. ------------//
								$page = 'message';
								$message_access_right = mjschool_get_user_role_wise_filter_access_right_array($page);
								if ($message_access_right['view'] === '1') {
									?><!-- Message Div start.  -->
									<div class="col-sm-12 col-md-6 col-lg-6 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
										<div class="panel mjschool-panel-white massage">
											<div class="mjschool-panel-heading">
												<h3 class="mjschool-panel-title"><?php esc_html_e( 'Message', 'mjschool' ); ?></h3>
												<a class="mjschool-page-link" href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=message' )); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											</div>
											<div class="mjschool-panel-body">
												<div class="events mjschool-notice-content-div">
													<?php
													$max = 5;
													if ( isset( $_GET['pg'] ) ) {
														$p = wp_unslash($_GET['pg']);
													} else {
														$p = 1;
													}
													$limit = ($p - 1) * $max;
													$message_data = mjschool_get_inbox_message(get_current_user_id(), $limit, $max);
													$i = 0;
													if ( ! empty( $message_data ) ) {
														foreach ($message_data as $retrieved_data) {
															if ($i === 0) {
																$color_class_css = 'mjschool-class-color0';
															} elseif ($i === 1) {
																$color_class_css = 'mjschool-class-color1';
															} elseif ($i === 2 ) {
																$color_class_css = 'mjschool-class-color2';
															} elseif ($i === 3) {
																$color_class_css = 'mjschool-class-color3';
															} elseif ($i === 4) {
																$color_class_css = 'mjschool-class-color4';
															}
															?>
															<div class="calendar-event mjschool-profile-image-class">
																<p class="mjschool-cursor-pointer mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment <?php echo esc_attr($color_class_css); ?>" id="<?php echo esc_attr($retrieved_data->message_id); ?>" model="Message Details">
																	<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-message-chat.png"); ?>">
																</p>
																<p class="mjschool-cursor-pointer mjschool-padding-top-5px-res mjschool-remainder-title-pr mjschool-card-content-width mjschool-show-task-event mjschool-padding-top-card-content mjschool-view-priscription mjschool-class-width mjschool_color_dark" id="<?php echo esc_attr($retrieved_data->message_id); ?>" model="Message Details">
																	<?php echo esc_html( $retrieved_data->subject); ?>
																</p>
																<p class="mjschool-remainder-date-pr mjschool-date-background mjschool-class-width"> <span class="mjschool-label-for-date"><?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->date ) ); ?></span> </p>
																<p class="mjschool-remainder-title-pr mjschool-view-priscription mjschool-card-content-width mjschool-class-width mjschool-assign-bed-name1 mjschool-card-margin-top">
																	<?php
																	$strlength = strlen($retrieved_data->message_body);
																	if ($strlength > 90) {
																		echo esc_html( substr( $retrieved_data->message_body, 10, 90 ) ) . '...';
																	} else {
																		echo esc_html( $retrieved_data->message_body);
																	}
																	?>
																</p>
															</div>
															<?php
															$i++;
														}
													} else {
														if ($message_access_right['add'] === '1') {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
																<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
																	<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=message&tab=compose' )); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Message', 'mjschool' ); ?></a>
																</div>
															</div>
															<?php
														} else {
															?>
															<div class="mjschool-calendar-event-new">
																<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
															</div>
															<?php 
														}
													}
													?>
												</div>
											</div>
										</div>
									</div>
									<!-- Notice and Massage Row Div End.  -->
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<!-- End dashboard content div. -->
		</div>
		<footer class='mjschool-footer'>
			<p> <?php echo esc_html( get_option( 'mjschool_footer_description' ) ); ?> </p>
		</footer>
	</body>
</html>