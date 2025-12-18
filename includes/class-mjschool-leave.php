<?php
/**
 * School Management Leave Management Class.
 *
 * This file contains the Mjschool_Leave class, which handles
 * CRUD operations using custom database tables.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Mjschool_Leave Class
 *
 * Handles all leave-related operations for the mjschool plugin,
 * including adding, editing, fetching, approving, rejecting, and deleting leave records.
 * It also manages email, SMS, and push notifications for these actions.
 *
 * @since 1.0.0
 */
class Mjschool_Leave {
	/**
	 * Handles adding a new leave request or editing an existing one.
	 *
	 * @since  1.0.0
	 * @param  array $data The leave data submitted via $_POST or similar.
	 * @return int|bool The ID of the inserted leave record or the result of the update/insert query.
	 */
	public function mjschool_add_leave( $data ) {
		global $wpdb;
		$table_hrmgt_leave           = $wpdb->prefix . 'mjschool_leave';
		$leavedata['student_id']     = isset( $data['student_id'] ) ? sanitize_text_field( wp_unslash( $data['student_id'] ) ) : '';
		$leavedata['leave_type']     = isset( $data['leave_type'] ) ? sanitize_text_field( wp_unslash( $data['leave_type'] ) ) : '';
		$leavedata['leave_duration'] = isset( $data['leave_duration'] ) ? sanitize_text_field( wp_unslash( $data['leave_duration'] ) ) : '';
		$leavedata['start_date']     = isset( $data['start_date'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $data['start_date'] ) ) ) ) : gmdate( 'Y-m-d' );
		$leavedata['end_date']       = isset( $data['end_date'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $data['end_date'] ) ) ) ) : '';
		$leavedata['status']         = isset( $data['status'] ) ? sanitize_text_field( wp_unslash( $data['status'] ) ) : '';
		$leavedata['reason']         = isset( $data['reason'] ) ? sanitize_textarea_field( wp_unslash( $data['reason'] ) ) : '';
		$leavedata['created_by']     = get_current_user_id();
		$leavedata['status_comment'] = '';
		$action                      = isset( $data['action'] ) ? sanitize_text_field( wp_unslash( $data['action'] ) ) : '';
		$page_name                   = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( $action === 'edit' ) {
			$whereid['id'] = intval( $data['leave_id'] );
			if ( $leavedata['leave_duration'] !== 'more_then_day' ) {
				$leavedata['end_date'] = '';
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result  = $wpdb->update( $table_hrmgt_leave, $leavedata, $whereid );
			$student = mjschool_get_user_name_by_id( $leavedata['student_id'] );
			mjschool_append_audit_log( '' . esc_html__( 'Leave Updated', 'mjschool' ) . '( ' . esc_html( $student ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', $page_name );
			return $result;
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$resultdata = $wpdb->insert( $table_hrmgt_leave, $leavedata );
			$leave_id   = $wpdb->insert_id;
			$student    = mjschool_get_user_name_by_id( $leavedata['student_id'] );
			mjschool_append_audit_log( '' . esc_html__( 'Leave Added', 'mjschool' ) . '( ' . esc_html( $student ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', $page_name );
			if ( $resultdata ) {
				$start_date_sanitized = isset( $data['start_date'] ) ? sanitize_text_field( wp_unslash( $data['start_date'] ) ) : '';
				$end_date_sanitized   = isset( $data['end_date'] ) ? sanitize_text_field( wp_unslash( $data['end_date'] ) ) : '';
				if ( ! empty( $end_date_sanitized ) ) {
					$date = mjschool_get_date_in_input_box( $start_date_sanitized ) . ' ' . esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( $end_date_sanitized );
				} else {
					$date = mjschool_get_date_in_input_box( $start_date_sanitized );
				}
				$enable_mail    = isset( $data['mjschool_enable_leave_mail'] ) && $data['mjschool_enable_leave_mail'] === '1';
				$enable_student = isset( $data['mjschool_enable_leave_mjschool_student'] ) && $data['mjschool_enable_leave_mjschool_student'] === '1';
				$enable_parent  = isset( $data['mjschool_enable_leave_mjschool_parent'] ) && $data['mjschool_enable_leave_mjschool_parent'] === '1';
				if ( $enable_mail || $enable_student || $enable_parent ) {
					$student_id_sanitized     = isset( $data['student_id'] ) ? sanitize_text_field( wp_unslash( $data['student_id'] ) ) : '';
					$leave_type_sanitized     = isset( $data['leave_type'] ) ? sanitize_text_field( wp_unslash( $data['leave_type'] ) ) : '';
					$leave_duration_sanitized = isset( $data['leave_duration'] ) ? sanitize_text_field( wp_unslash( $data['leave_duration'] ) ) : '';
					$reason_sanitized         = isset( $data['reason'] ) ? sanitize_textarea_field( wp_unslash( $data['reason'] ) ) : '';

					if ( $enable_mail ) {
						// Leave request mail for student start. //
						$arr['{{date}}']           = esc_html( $date );
						$arr['{{leave_type}}']     = esc_html( get_the_title( $leave_type_sanitized ) );
						$arr['{{leave_duration}}'] = esc_html( mjschool_leave_duration_label( $leave_duration_sanitized ) );
						$arr['{{reason}}']         = esc_html( $reason_sanitized );
						$arr['{{student_name}}']   = esc_html( mjschool_get_display_name( $student_id_sanitized ) );
						$arr['{{school_name}}']    = esc_html( get_option( 'mjschool_name' ) );
						$message                   = get_option( 'mjschool_addleave_email_template_student' );
						$replace_message           = mjschool_string_replacement( $arr, $message );  /* Student Leave Mail Content */
						if ( $replace_message ) {
							$to      = mjschool_get_email_id_by_user_id( $student_id_sanitized );
							$subject = get_option( 'mjschool_add_leave_subject_for_student' );  /* Student Leave Mail Subject */
							$result  = mjschool_send_mail( $to, $subject, $replace_message );
						}
						// Leave request mail for student end. //
						// Leave request mail for parent start. //
						$parent = get_user_meta( $student_id_sanitized, 'parent_id', true );
						if ( ! empty( $parent ) ) {
							foreach ( $parent as $p ) {
								$user_info                   = get_userdata( intval( $p ) );
								$arr_1['{{date}}']           = esc_html( $date );
								$arr_1['{{leave_type}}']     = esc_html( get_the_title( $leave_type_sanitized ) );
								$arr_1['{{leave_duration}}'] = esc_html( mjschool_leave_duration_label( $leave_duration_sanitized ) );
								$arr_1['{{reason}}']         = esc_html( $reason_sanitized );
								$arr_1['{{student_name}}']   = esc_html( mjschool_get_display_name( $student_id_sanitized ) );
								$arr_1['{{parent_name}}']    = esc_html( $user_info->display_name );
								$arr_1['{{school_name}}']    = esc_html( get_option( 'mjschool_name' ) );
								$message_1                   = get_option( 'mjschool_addleave_email_template_parent' );
								$replace_message_1           = mjschool_string_replacement( $arr_1, $message_1 );  /* Parent Leave Mail Content */
								if ( $replace_message_1 ) {
									$to      = $user_info->user_email;
									$subject = get_option( 'mjschool_add_leave_subject_for_parent' );  /* Parent Leave Mail Subject */
									$result  = mjschool_send_mail( $to, $subject, $replace_message_1 );
								}
							}
						}
						// Leave request mail for parent end. //
					}
					// Leave SMS notification for student. //
					if ( $enable_student ) {
						$SMSCon                     = get_option( 'mjschool_leave_student_mjschool_content' );
						$SMSArr['{{student_name}}'] = esc_html( mjschool_get_display_name( $student_id_sanitized ) );
						$SMSArr['{{date}}']         = esc_html( $date );
						$SMSArr['{{school_name}}']  = esc_html( get_option( 'mjschool_name' ) );
						$message_content            = mjschool_string_replacement( $SMSArr, $SMSCon );
						$type                       = 'Leave';
						$userdata                   = get_userdata( intval( $student_id_sanitized ) );
						if ( $userdata ) {
							mjschool_send_mjschool_notification( $userdata->ID, $type, $message_content );
						}
					}
					// Leave SMS notification for parent. //
					if ( $enable_parent ) {
						$parent = get_user_meta( $student_id_sanitized, 'parent_id', true );
						if ( ! empty( $parent ) ) {
							foreach ( $parent as $p ) {
								$user_info                  = get_userdata( intval( $p ) );
								$email_to                   = $user_info->user_email;
								$parerntdata                = get_user_by( 'email', $email_to );
								$SMSCon                     = get_option( 'mjschool_leave_parent_mjschool_content' );
								$SMSArr['{{parent_name}}']  = esc_html( $parerntdata->display_name );
								$SMSArr['{{student_name}}'] = esc_html( mjschool_get_display_name( $student_id_sanitized ) );
								$SMSArr['{{date}}']         = esc_html( $date );
								$SMSArr['{{school_name}}']  = esc_html( get_option( 'mjschool_name' ) );
								$message_content            = mjschool_string_replacement( $SMSArr, $SMSCon );
								$type                       = 'Leave';
								mjschool_send_mjschool_notification( intval( $p ), $type, $message_content );
							}
						}
					}
				}
				// Leave request mail for admin start. //
				$admin_data = get_users( array( 'role' => 'administrator' ) );
				if ( ! empty( $admin_data ) ) {
					$student_id_sanitized     = isset( $data['student_id'] ) ? sanitize_text_field( wp_unslash( $data['student_id'] ) ) : '';
					$leave_type_sanitized     = isset( $data['leave_type'] ) ? sanitize_text_field( wp_unslash( $data['leave_type'] ) ) : '';
					$leave_duration_sanitized = isset( $data['leave_duration'] ) ? sanitize_text_field( wp_unslash( $data['leave_duration'] ) ) : '';
					$reason_sanitized         = isset( $data['reason'] ) ? sanitize_textarea_field( wp_unslash( $data['reason'] ) ) : '';
					foreach ( $admin_data as $admin ) {
						$arr['{{date}}']           = esc_html( $date );
						$arr['{{leave_type}}']     = esc_html( get_the_title( $leave_type_sanitized ) );
						$arr['{{leave_duration}}'] = esc_html( mjschool_leave_duration_label( $leave_duration_sanitized ) );
						$arr['{{reason}}']         = esc_html( $reason_sanitized );
						$arr['{{student_name}}']   = esc_html( mjschool_get_display_name( $student_id_sanitized ) );
						$arr['{{school_name}}']    = esc_html( get_option( 'mjschool_name' ) );
						$message                   = get_option( 'mjschool_addleave_email_template_of_admin' );
						$replace_message           = mjschool_string_replacement( $arr, $message );  /* Admin Leave Mail Content */
						if ( $replace_message ) {
							$to      = mjschool_get_email_id_by_user_id( $admin->ID );
							$subject = get_option( 'mjschool_add_leave_subject_of_admin' );  /* Admin Leave Mail Subject */
							$result  = mjschool_send_mail( $to, $subject, $replace_message );
						}
					}
				}
				// Leave request mail for admin end. //
				$student_id_int = intval( $data['student_id'] );
				$empdata        = get_userdata( $student_id_int );
				$device_token[] = get_user_meta( $student_id_int, 'token_id', true );
				/* Start send push notification. */
				$leave_duration_sanitized = isset( $data['leave_duration'] ) ? sanitize_text_field( wp_unslash( $data['leave_duration'] ) ) : '';
				$start_date_sanitized     = isset( $data['start_date'] ) ? sanitize_text_field( wp_unslash( $data['start_date'] ) ) : '';
				$end_date_sanitized       = isset( $data['end_date'] ) ? sanitize_text_field( wp_unslash( $data['end_date'] ) ) : '';
				if ( $leave_duration_sanitized === 'more_then_day' ) {
					$end_date = esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( $end_date_sanitized );
				} else {
					$end_date = '';
				}
				$title             = esc_html__( 'Request For Leave', 'mjschool' );
				$text              = esc_html( $start_date_sanitized ) . ' ' . $end_date;
				$notification_data = array(
					'registration_ids' => $device_token,
					'data'             => array(
						'title' => $title,
						'body'  => $text,
						'type'  => 'Message',
					),
				);
				$json              = wp_json_encode( $notification_data );
				$result            = mjschool_send_push_notification( $json );
				/* End send push notification. */
			}
			return $leave_id;
		}
	}
	/**
	 * Retrieves all leave records from the database.
	 *
	 * @since  1.0.0
	 * @return array Array of all leave objects.
	 */
	public function mjschool_get_all_leaves() {
		global $wpdb;
		$table_hrmgt_leave = $wpdb->prefix . 'mjschool_leave';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM $table_hrmgt_leave" );
		return $result;
	}
	/**
	 * Retrieves all leave records for a specific student ID.
	 *
	 * @since  1.0.0
	 * @param  int $id The student ID.
	 * @return array Array of leave objects for the specified student.
	 */
	public function mjschool_get_single_user_leaves( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_leave';
		$student_id = intval( $id ); // Sanitize input
		$query      = $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id = %d", $student_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $query );
		return $result;
	}
	/**
	 * Retrieves all leave records with a specific status (e.g., 'Pending', 'Approved', 'Rejected').
	 *
	 * @since  1.0.0
	 * @param  string $status The leave status to filter by.
	 * @return array Array of leave objects matching the status.
	 */
	public function mjschool_get_leave_by_status( $status ) {
		global $wpdb;
		$table_hrmgt_leave = $wpdb->prefix . 'mjschool_leave';
		$status            = sanitize_text_field( $status ); // Sanitize input.
		// Use a prepared statement to prevent SQL injection.
		$query = $wpdb->prepare( "SELECT * FROM $table_hrmgt_leave WHERE status = %s", $status );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $query );
		return $result;
	}
	/**
	 * Retrieves all leave records that start on a specific date.
	 *
	 * @since  1.0.0
	 * @param  string $date The start date to filter by (e.g., 'YYYY-MM-DD').
	 * @return array Array of leave objects matching the start date.
	 */
	public function mjschool_get_leave_by_date( $date ) {
		global $wpdb;
		$table_hrmgt_leave = $wpdb->prefix . 'mjschool_leave';
		// Validate and sanitize the date before using it in the query.
		$sanitized_date = $this->mjschool_sanitize_wp_date( $date );
		// Check if the sanitized date is valid.
		if ( $sanitized_date === null ) {
			return array(); // Return an empty array if the date is invalid
		}
		// Use a prepared statement to prevent SQL injection.
		$query = $wpdb->prepare( "SELECT * FROM $table_hrmgt_leave WHERE start_date = %s", $sanitized_date );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $query );
		return $result;
	}
	/**
	 * Retrieves leave records for a specific employee within a date range for reporting.
	 * NOTE: The column name is `student_id` in other methods, but `employee_id` here.
	 * Assuming `employee_id` is an alias or placeholder for `student_id` in this context.
	 *
	 * @since  1.0.0
	 * @param  int    $employee_id The student/employee ID.
	 * @param  string $start_date  The start of the date range (YYYY-MM-DD).
	 * @param  string $end_date    The end of the date range (YYYY-MM-DD).
	 * @return array Array of leave objects within the range.
	 */
	public function mjschool_get_single_user_leaves_for_report( $employee_id, $start_date, $end_date ) {
		global $wpdb;
		$employee_id       = intval( $employee_id );
		$start_date        = sanitize_text_field( $start_date );
		$end_date          = sanitize_text_field( $end_date );
		$table_hrmgt_leave = $wpdb->prefix . 'mjschool_leave';
		// Prepare the SQL query using placeholders.
		$sql = $wpdb->prepare( "SELECT * FROM $table_hrmgt_leave WHERE start_date BETWEEN %s AND %s AND employee_id = %d", $start_date, $end_date, $employee_id );
		// Execute the query and get the results.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $sql );
		// Return the results.
		return $result;
	}
	/**
	 * Retrieves a single leave record by its ID.
	 *
	 * @since  1.0.0
	 * @param  int $id The ID of the leave record.
	 * @return object|null The leave object or null if not found.
	 */
	public function mjschool_get_single_leave( $id ) {
		global $wpdb;
		$table_hrmgt_leave = $wpdb->prefix . 'mjschool_leave';
		// Validate and sanitize the ID.
		$id = intval( $id ); // Ensure the ID is a positive integer.
		// Use a prepared statement to prevent SQL injection.
		$query = $wpdb->prepare( "SELECT * FROM $table_hrmgt_leave WHERE id = %d", $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $query );
		return $result;
	}
	/**
	 * Approves a leave request and sends notifications.
	 *
	 * @since  1.0.0
	 * @param  array $data The data including 'leave_id' and 'comment'.
	 * @return bool True on successful update, false otherwise.
	 */
	public function mjschool_approve_leave( $data ) {
		global $wpdb;
		$id                = intval( $data['leave_id'] );
		$comment           = sanitize_text_field( wp_unslash( $data['comment'] ) );
		$table_hrmgt_leave = $wpdb->prefix . 'mjschool_leave';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_hrmgt_leave WHERE id = %d", $id ) );
		if ( $row ) {
			// Use prepared statements for the UPDATE query.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$update = $wpdb->update(
				$table_hrmgt_leave,
				array(
					'status'         => 'Approved',
					'status_comment' => $comment,
				), // Data to update.
				array( 'id' => $id ), // Where clause.
				array( '%s', '%s' ), // Data format for the update.
				array( '%d' ) // Where format.
			);
		}
		$empdata = get_userdata( (int) $row->student_id );
		if ( $update ) {
			$data['start_date']     = $row->start_date;
			$data['end_date']       = $row->end_date;
			$data['student_id']     = $row->student_id;
			$data['leave_duration'] = $row->leave_duration;
			$leave_data             = $this->mjschool_get_single_leave( $id );
			$arr                    = array();
			if ( ! empty( $leave_data->end_date ) ) {
				$date = mjschool_change_dateformat( $leave_data->start_date ) . ' To ' . mjschool_change_dateformat( $leave_data->end_date );
			} else {
				$date = mjschool_change_dateformat( $leave_data->start_date );
			}
			$arr['{{date}}']        = esc_html( $date );
			$arr['{{system_name}}'] = esc_html( get_option( 'mjschool_name' ) );
			$arr['{{user_name}}']   = esc_html( mjschool_get_display_name( $leave_data->student_id ) );
			$arr['{{comment}}']     = esc_html( $comment );
			$message                = get_option( 'mjschool_leave_approve_email_template' );
			$replace_message        = mjschool_string_replacement( $arr, $message );
			if ( $replace_message ) {
				$subject = get_option( 'mjschool_leave_approve_subject' );
				$to[]    = mjschool_get_email_id_by_user_id( $leave_data->student_id );
				$emails  = get_option( 'mjschool_leave_approveemails' );
				$emails  = explode( ',', $emails );
				foreach ( $emails as $email ) {
					$to[] = sanitize_email( trim( $email ) );
				}
				$mail = mjschool_send_mail( $to, $subject, $replace_message );
			}
			/* Start send push notification. */
			$device_token[]    = get_user_meta( $row->student_id, 'token_id', true );
			$title             = esc_html__( 'Your leave approved', 'mjschool' );
			$text              = esc_html( $date );
			$notification_data = array(
				'registration_ids' => $device_token,
				'data'             => array(
					'title' => $title,
					'body'  => $text,
					'type'  => 'Message',
				),
			);
			$json              = wp_json_encode( $notification_data );
			$result            = mjschool_send_push_notification( $json );
			/* End send push notification. */
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Rejects a leave request and sends an email notification.
	 *
	 * @since  1.0.0
	 * @param  array $data The data including 'leave_id' and 'comment'.
	 * @return bool True on successful update, false otherwise.
	 */
	public function mjschool_reject_leave( $data ) {
		global $wpdb;
		$id                = intval( $data['leave_id'] );
		$comment           = sanitize_text_field( wp_unslash( $data['comment'] ) );
		$table_hrmgt_leave = $wpdb->prefix . 'mjschool_leave';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_hrmgt_leave WHERE id = %d", $id ) );
		if ( $row ) {
			// Use prepared statements for the UPDATE query.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$update = $wpdb->update(
				$table_hrmgt_leave,
				array(
					'status'         => 'Rejected',
					'status_comment' => $comment,
				), // Data to update
				array( 'id' => $id ), // Where clause.
				array( '%s', '%s' ), // Data format for the update.
				array( '%d' ) // Where format.
			);
		}
		$empdata = get_userdata( (int) $row->student_id );
		if ( $update ) {
			$leave_data = $this->mjschool_get_single_leave( $id );
			$arr        = array();
			if ( ! empty( $leave_data->end_date ) ) {
				$date = mjschool_get_date_in_input_box( $leave_data->start_date ) . ' To ' . mjschool_get_date_in_input_box( $leave_data->end_date );
			} else {
				$date = mjschool_get_date_in_input_box( $leave_data->start_date );
			}
			// Leave reject mail start.
			$arr['{{date}}']         = esc_html( $date );
			$arr['{{school_name}}']  = esc_html( get_option( 'mjschool_name' ) );
			$arr['{{student_name}}'] = esc_html( mjschool_student_display_name_with_roll( $leave_data->student_id ) );
			$arr['{{comment}}']      = esc_html( $comment );
			$message                 = get_option( 'mjschool_leave_reject_email_template' );
			$replace_message         = mjschool_string_replacement( $arr, $message );
			$subject                 = get_option( 'mjschool_leave_reject_subject' );
			$to                      = mjschool_get_email_id_by_user_id( $leave_data->student_id );
			$mail                    = mjschool_send_mail( $to, $subject, $replace_message );
			// Leave reject mail end.
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Deletes a leave record by its ID.
	 *
	 * @since  1.0.0
	 * @param  int $leave_id The ID of the leave record to delete.
	 * @return int|bool Number of rows deleted on success, false otherwise.
	 */
	public function mjschool_delete_leave( $leave_id ) {
		global $wpdb;
		$table_hrmgt_leave = $wpdb->prefix . 'mjschool_leave';
		// Sanitize and validate the leave_id.
		$leave_id = intval( $leave_id ); // Ensure leave_id is a positive integer.
		// Fetch leave data securely.
		$leave_data = $this->mjschool_get_single_leave( $leave_id );
		// Use a prepared statement to fetch the event data.
		$query = $wpdb->prepare( "SELECT * FROM $table_hrmgt_leave WHERE id = %d", $leave_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$event = $wpdb->get_row( $query );
		if ( $event ) {
			// Get student name securely.
			$student   = mjschool_get_user_name_by_id( intval( $event->student_id ) );
			$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			// Log the action.
			mjschool_append_audit_log(
				esc_html__( 'Leave Deleted', 'mjschool' ) . ' ( ' . esc_html( $student ) . ' )',
				get_current_user_id(),
				get_current_user_id(),
				'delete',
				$page_name
			);
			// Use a prepared statement to delete the leave.
			$delete_query = $wpdb->prepare( "DELETE FROM $table_hrmgt_leave WHERE id = %d", $leave_id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->query( $delete_query );
			return $result;
		} else {
			return false; // Return false if the event is not found.
		}
	}
	/**
	 * Helper function to validate and sanitize a date string into 'YYYY-MM-DD' format.
	 *
	 * @since  1.0.0
	 * @param  string $date The date string to sanitize.
	 * @return string|null The sanitized date string or null if the date is invalid.
	 */
	private function mjschool_sanitize_wp_date( $date ) {
		try {
			$datetime = new DateTime( $date );
			return $datetime->format( 'Y-m-d' ); // Format as 'YYYY-MM-DD'.
		} catch ( Exception $e ) {
			return null; // Return null for invalid dates.
		}
	}
}