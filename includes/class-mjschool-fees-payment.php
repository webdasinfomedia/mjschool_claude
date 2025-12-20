<?php
/**
 * Fees Payment Management Class
 *
 * This class handles all database operations and business logic related to
 * managing fees payments, invoices, recurring fees, and payment history
 * within the mjschool system.
 *
 * @package    Mjschool
 * @subpackage Mjschool/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Core class for Mjschool Fees Payment.
 *
 * Defines methods for fees CRUD operations, payment processing, status updates,
 * and notification handling.
 *
 * @since 1.0.0
 */
class Mjschool_Feespayment {
	/**
	 * Deletes a fees category/type post from the WordPress database.
	 *
	 * Note: This appears to delete a post type rather than a custom table entry
	 * for fee types, using a native WordPress function.
	 *
	 * @param  int $cat_id The ID of the post (fee category) to delete.
	 * @return bool|WP_Post|null True on success, false on failure, or null if post not found.
	 * @since  1.0.0
	 */
	public function mjschool_delete_fee_type( $cat_id ) {
		$cat_id = isset( $cat_id ) ? intval( $cat_id ) : 0;
		$result = wp_delete_post( $cat_id, true );
		return $result;
	}
	/**
	 * Handles the creation or update of a fees payment (invoice) for one or more students.
	 *
	 * This method manages non-recurring and recurring fees, calculates discounts/taxes,
	 * creates audit logs, and sends various notifications (email, SMS/in-app) to students/parents.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  array $data Array of data submitted from the fees form.
	 * @return int The ID of the newly inserted fees payment/invoice, or the result of the update query.
	 * @since  1.0.0
	 */
	public function mjschool_add_feespayment( $data ) {
		global $wpdb;
		$table_mjschool_fees_payment           = $wpdb->prefix . 'mjschool_fees_payment';
		$table_mjschool_fees_payment_recurring = $wpdb->prefix . 'mjschool_fees_payment_recurring';
		$table_income                          = $wpdb->prefix . 'mjschool_income_expense';
		if ( isset( $_POST['class_id'] ) && sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) !== 'all_class' ) {
			$feedata['class_id']   = sanitize_text_field( wp_unslash( $_POST['class_id'] ) );
			$feedata['section_id'] = sanitize_text_field( wp_unslash( $_POST['class_section'] ) );
		}
		$fees_ids = isset( $_POST['fees_id'] ) && is_array( $_POST['fees_id'] ) ? array_map( 'intval', wp_unslash( $_POST['fees_id'] ) ) : array();
		$feedata['fees_id']      = implode( ',', $fees_ids );
		$feedata['fees_amount']  = isset( $_POST['fees_amount'] ) ? floatval( wp_unslash( $_POST['fees_amount'] ) ) : 0;
		$feedata['description']  = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$feedata['start_year']   = isset( $_POST['start_year'] ) ? date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['start_year'] ) ) ) ) : date( 'Y-m-d' );
		$feedata['end_year']     = isset( $_POST['end_year'] ) ? date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['end_year'] ) ) ) ) : date( 'Y-m-d' );
		$feedata['paid_by_date'] = date( 'Y-m-d' );
		$feedata['created_date'] = date( 'Y-m-d H:i:s' );
		$feedata['created_by']   = get_current_user_id();
		if ( isset( $data['discount'] ) ) {
			$feedata['discount']        = floatval( $data['discount'] );
			$feedata['discount_type']   = sanitize_text_field( $data['discount_type'] );
			$feedata['discount_amount'] = mjschool_discount_amount( floatval( $data['fees_amount'] ), floatval( $data['discount'] ), sanitize_text_field( $data['discount_type'] ) );
		} else {
			$feedata['discount']        = null;
			$feedata['discount_type']   = null;
			$feedata['discount_amount'] = 0;
		}
		if ( isset( $data['tax'] ) ) {
			$fees_amount           = floatval( $data['fees_amount'] ) - $feedata['discount_amount'];
			$tax_ids               = is_array( $data['tax'] ) ? array_map( 'intval', $data['tax'] ) : array();
			$feedata['tax']        = implode( ',', $tax_ids );
			$feedata['tax_amount'] = mjschool_get_tax_amount( $fees_amount, $tax_ids );
		} else {
			$feedata['tax']        = null;
			$feedata['tax_amount'] = 0;
		}
		$feedata['total_amount'] = $feedata['fees_amount'] - $feedata['discount_amount'] + $feedata['tax_amount'];
		$email_subject           = get_option( 'mjschool_fee_payment_title' );
		$SchoolName              = get_option( 'mjschool_name' );
		if ( isset( $data['action'] ) && sanitize_text_field( $data['action'] ) === 'edit' ) {
			$feedata['student_id']  = intval( $data['student_id'] );
			$fees_id['fees_pay_id'] = intval( $data['fees_pay_id'] );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result  = $wpdb->update( $table_mjschool_fees_payment, $feedata, $fees_id );
			$student = mjschool_get_user_name_by_id( $feedata['student_id'] );
			$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			mjschool_append_audit_log( '' . esc_html__( 'Fees Payment Updated', 'mjschool' ) . '( ' . esc_html( $student ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', $page_name );
			return $result;
		} else {
			/* Add Recurring Payment Data. */
			$recurrence_type = isset( $_POST['recurrence_type'] ) ? sanitize_text_field( wp_unslash( $_POST['recurrence_type'] ) ) : 'one_time';
			if ( $recurrence_type !== 'one_time' ) {
				$recurring_feedata['class_id']    = sanitize_text_field( wp_unslash( $_POST['class_id'] ) );
				$recurring_feedata['section_id']  = sanitize_text_field( wp_unslash( $_POST['class_section'] ) );
				$recurring_fees_ids               = isset( $_POST['fees_id'] ) && is_array( $_POST['fees_id'] ) ? array_map( 'intval', wp_unslash( $_POST['fees_id'] ) ) : array();
				$recurring_feedata['fees_id']     = implode( ',', $recurring_fees_ids );
				$selected_users                   = isset( $_POST['selected_users'] ) && is_array( $_POST['selected_users'] ) ? array_map( 'intval', wp_unslash( $_POST['selected_users'] ) ) : array();
				$recurring_feedata['student_id']  = implode( ',', $selected_users );
				$recurring_feedata['fees_amount'] = isset( $_POST['fees_amount'] ) ? floatval( wp_unslash( $_POST['fees_amount'] ) ) : 0;
				if ( isset( $data['tax'] ) ) {
					$tax_ids                         = is_array( $data['tax'] ) ? array_map( 'intval', $data['tax'] ) : array();
					$recurring_feedata['tax']        = implode( ',', $tax_ids );
					$recurring_feedata['tax_amount'] = mjschool_get_tax_amount( floatval( $data['fees_amount'] ), $tax_ids );
				} else {
					$recurring_feedata['tax']        = null;
					$recurring_feedata['tax_amount'] = 0;
				}
				$recurring_feedata['total_amount']   = $recurring_feedata['fees_amount'] + $recurring_feedata['tax_amount'];
				$recurring_feedata['description']    = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
				$start_year_raw                      = isset( $_POST['start_year'] ) ? sanitize_text_field( wp_unslash( $_POST['start_year'] ) ) : date( 'Y-m-d' );
				$recurring_feedata['start_year']     = date( 'Y-m-d', strtotime( $start_year_raw ) );
				$recurring_feedata['recurring_type'] = $recurrence_type;
				if ( $recurrence_type === 'monthly' ) {
					$recurring_enddate = date( 'Y-m-d', strtotime( '+1 months', strtotime( $start_year_raw ) ) );
				} elseif ( $recurrence_type === 'weekly' ) {
					$recurring_enddate = date( 'Y-m-d', strtotime( '+1 week', strtotime( $start_year_raw ) ) );
				} elseif ( $recurrence_type === 'quarterly' ) {
					$recurring_enddate = date( 'Y-m-d', strtotime( '+3 months', strtotime( $start_year_raw ) ) );
				} elseif ( $recurrence_type === 'half_yearly' ) {
					$recurring_enddate = date( 'Y-m-d', strtotime( '+6 months', strtotime( $start_year_raw ) ) );
				} else {
					$end_year_raw      = isset( $_POST['end_year'] ) ? sanitize_text_field( wp_unslash( $_POST['end_year'] ) ) : date( 'Y-m-d' );
					$recurring_enddate = date( 'Y-m-d', strtotime( $end_year_raw ) );
				}
				$end_year_raw                           = isset( $_POST['end_year'] ) ? sanitize_text_field( wp_unslash( $_POST['end_year'] ) ) : date( 'Y-m-d' );
				$recurring_feedata['end_year']          = date( 'Y-m-d', strtotime( $end_year_raw ) );
				$recurring_feedata['recurring_enddate'] = $recurring_enddate;
				$recurring_feedata['status']            = 'yes';
				$recurring_feedata['created_date']      = date( 'Y-m-d H:i:s' );
				$recurring_feedata['created_by']        = get_current_user_id();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result_recurring    = $wpdb->insert( $table_mjschool_fees_payment_recurring, $recurring_feedata );
				$feedata['end_year'] = $recurring_enddate;
			}
			/* End Add Recurring Payment Data. */
			$students     = isset( $data['selected_users'] ) && is_array( $data['selected_users'] ) ? array_map( 'intval', $data['selected_users'] ) : array();
			$table_income = $wpdb->prefix . 'mjschool_income_expense';
			$fees_type    = array();
			$fees_ids     = isset( $_POST['fees_id'] ) && is_array( $_POST['fees_id'] ) ? array_map( 'intval', wp_unslash( $_POST['fees_id'] ) ) : array();
			foreach ( $fees_ids as $id ) {
				$fees_type[] = mjschool_get_fees_term_name( $id );
			}
			$fee_title     = implode( ' , ', $fees_type );
			$entry_array[] = array(
				'entry'  => $fee_title,
				'amount' => floatval( $data['fees_amount'] ),
			);
			$entry_value   = wp_json_encode( $entry_array );
			foreach ( $students as $student_id ) {
				$feedata['student_id'] = intval( $student_id );
				$student_info          = get_userdata( $student_id );
				$parent                = get_user_meta( $student_id, 'parent_id', true );
				$roll_id               = get_user_meta( $student_id, 'roll_id', true );
				$class_name            = get_user_meta( $student_id, 'class_name', true );
				$class_section         = get_user_meta( $student_id, 'class_section', true );
				$class_id_post         = isset( $_POST['class_id'] ) ? sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) : '';
				if ( $class_id_post === 'all_class' ) {
					$feedata['class_id']   = $class_name;
					$feedata['section_id'] = $class_section;
				}
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$max_invoice_id        = $wpdb->get_var( "SELECT MAX(invoice_id) FROM {$table_mjschool_fees_payment}" );
				$next_invoice_id       = $max_invoice_id ? intval( $max_invoice_id ) + 1 : 1;
				$feedata['invoice_id'] = $next_invoice_id;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result  = $wpdb->insert( $table_mjschool_fees_payment, $feedata );
				$fees_id = $wpdb->insert_id;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$fees_pay_id  = $wpdb->insert_id;
				$student_name = mjschool_get_user_name_by_id( $student_id );
				$page_name    = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
				mjschool_append_audit_log( '' . esc_html__( 'Fees Payment Added', 'mjschool' ) . '( ' . esc_html( $student_name ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', $page_name );
				/* END Add Fees Payment Data. */
				$enable_mail    = isset( $_POST['mjschool_enable_feesalert_mail'] ) ? sanitize_text_field( wp_unslash( $_POST['mjschool_enable_feesalert_mail'] ) ) : '';
				$enable_student = isset( $_POST['mjschool_enable_feesalert_mjschool_student'] ) ? sanitize_text_field( wp_unslash( $_POST['mjschool_enable_feesalert_mjschool_student'] ) ) : '';
				$enable_parent  = isset( $_POST['mjschool_enable_feesalert_mjschool_parent'] ) ? sanitize_text_field( wp_unslash( $_POST['mjschool_enable_feesalert_mjschool_parent'] ) ) : '';
				if ( $enable_mail === '1' || $enable_student === '1' || $enable_parent === '1' ) {
					if ( $enable_mail === '1' ) {
						// Send Mail Notification to student.
						$Cont                          = get_option( 'mjschool_fee_payment_mailcontent' );
						$email                         = $student_info->user_email;
						$fees_amount_value             = isset( $_POST['fees_amount'] ) ? floatval( wp_unslash( $_POST['fees_amount'] ) ) : 0;
						$SearchArr['{{student_name}}'] = $student_info->display_name;
						$SearchArr['{{school_name}}']  = get_option( 'mjschool_name' );
						$SearchArr['{{date}}']         = mjschool_get_date_in_input_box( date( 'Y-m-d' ) );
						$SearchArr['{{amount}}']       = mjschool_currency_symbol_position_language_wise( number_format( $fees_amount_value, 2, '.', '' ) );
						$MessageContent                = mjschool_string_replacement( $SearchArr, get_option( 'mjschool_fee_payment_mailcontent' ) );
						if ( get_option( 'mjschool_mail_notification' ) === '1' ) {
							mjschool_send_mail_paid_invoice_pdf( $email, get_option( 'mjschool_fee_payment_title' ), $MessageContent, $fees_pay_id );
						}
						// End Send Mail Notification to student.
						if ( ! empty( $parent ) ) {
							// Send Mail To Parent code start.
							foreach ( $parent as $parent_id ) {
								$parent_info                  = get_userdata( intval( $parent_id ) );
								$Cont                         = get_option( 'mjschool_fee_payment_title_for_parent' );
								$email                        = $parent_info->user_email;
								$SearchArr['{{parent_name}}'] = $parent_info->display_name;
								$SearchArr['{{school_name}}'] = get_option( 'mjschool_name' );
								$SearchArr['{{date}}']        = mjschool_get_date_in_input_box( date( 'Y-m-d' ) );
								$SearchArr['{{amount}}']      = mjschool_currency_symbol_position_language_wise( number_format( $fees_amount_value, 2, '.', '' ) );
								$SearchArr['{{child_name}}']  = $student_info->display_name;
								$MessageContent               = mjschool_string_replacement( $SearchArr, get_option( 'mjschool_fee_payment_mailcontent_for_parent' ) );
								if ( get_option( 'mjschool_mail_notification' ) === '1' ) {
									mjschool_send_mail_paid_invoice_pdf( $email, get_option( 'mjschool_fee_payment_title' ), $MessageContent, $fees_pay_id );
								}
							}
						}
					}
					// SEND SMS NOTIFICATION TO STUDENT.
					if ( $enable_student === '1' ) {
						$SMSArr                     = array();
						$SMSCon                     = get_option( 'mjschool_fees_payment_mjschool_content_for_student' );
						$SMSArr['{{student_name}}'] = $student_info->display_name;
						$SMSArr['{{school_name}}']  = get_option( 'mjschool_name' );
						$message_content            = mjschool_string_replacement( $SMSArr, $SMSCon );
						$type                       = 'Feespayment';
						mjschool_send_mjschool_notification( $student_id, $type, $message_content );
					}
					// SEND SMS NOTIFICATION TO PARENT.
					if ( $enable_parent === '1' ) {
						if ( ! empty( $parent ) ) {
							foreach ( $parent as $parent_id ) {
								$SMSArr                     = array();
								$parent_info                = get_userdata( intval( $parent_id ) );
								$SMSCon                     = get_option( 'mjschool_fees_payment_mjschool_content_for_parent' );
								$SMSArr['{{parent_name}}']  = $parent_info->display_name;
								$SMSArr['{{student_name}}'] = mjschool_get_display_name( $student_id );
								$SMSArr['{{school_name}}']  = get_option( 'mjschool_name' );
								$message_content            = mjschool_string_replacement( $SMSArr, $SMSCon );
								$type                       = 'Feespayment';
								mjschool_send_mjschool_notification( $parent_info->ID, $type, $message_content );
							}
						}
					}
				}
			}
		}
		return $fees_id;
	}
	/**
	 * Handles the update of a recurring fees payment setup record.
	 *
	 * This method is strictly for editing the recurring schedule itself, not creating new invoices.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  array $data Array of recurring fees data submitted for update.
	 * @return int|bool The number of rows updated on success, or false on error.
	 * @since  1.0.0
	 */
	public function mjschool_add_recurring_feespayment( $data ) {
		global $wpdb;
		$table_mjschool_fees_payment_recurring = $wpdb->prefix . 'mjschool_fees_payment_recurring';
		if ( isset( $data['action'] ) && sanitize_text_field( $data['action'] ) === 'edit' ) {
			$recurring_feedata['class_id']       = sanitize_text_field( wp_unslash( $_POST['class_id'] ) );
			$recurring_feedata['section_id']     = sanitize_text_field( wp_unslash( $_POST['class_section'] ) );
			$fees_ids                            = isset( $_POST['fees_id'] ) && is_array( $_POST['fees_id'] ) ? array_map( 'intval', wp_unslash( $_POST['fees_id'] ) ) : array();
			$recurring_feedata['fees_id']        = implode( ',', $fees_ids );
			$selected_users                      = isset( $_POST['selected_users'] ) && is_array( $_POST['selected_users'] ) ? array_map( 'intval', wp_unslash( $_POST['selected_users'] ) ) : array();
			$recurring_feedata['student_id']     = implode( ',', $selected_users );
			$recurring_feedata['total_amount']   = isset( $_POST['fees_amount'] ) ? floatval( wp_unslash( $_POST['fees_amount'] ) ) : 0;
			$recurring_feedata['description']    = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
			$start_year_raw                      = isset( $_POST['start_year'] ) ? sanitize_text_field( wp_unslash( $_POST['start_year'] ) ) : date( 'Y-m-d' );
			$recurring_feedata['start_year']     = date( 'Y-m-d', strtotime( $start_year_raw ) );
			$end_year_raw                        = isset( $_POST['end_year'] ) ? sanitize_text_field( wp_unslash( $_POST['end_year'] ) ) : date( 'Y-m-d' );
			$recurring_feedata['end_year']       = date( 'Y-m-d', strtotime( $end_year_raw ) );
			$recurring_feedata['recurring_type'] = isset( $_POST['recurrence_type'] ) ? sanitize_text_field( wp_unslash( $_POST['recurrence_type'] ) ) : '';
			$recurring_feedata['status']         = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
			$recurring_feedata['created_date']   = date( 'Y-m-d H:i:s' );
			$recurring_feedata['created_by']     = get_current_user_id();
			// Update Recurring END DATE.
			$last_recurrence_date                   = isset( $_POST['last_recurrence_date'] ) ? sanitize_text_field( wp_unslash( $_POST['last_recurrence_date'] ) ) : '';
			$recurring_feedata['recurring_enddate'] = $last_recurrence_date;
			$recurring_fees_id['recurring_id']      = isset( $_POST['recurring_fees_id'] ) ? intval( wp_unslash( $_POST['recurring_fees_id'] ) ) : 0;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->update( $table_mjschool_fees_payment_recurring, $recurring_feedata, $recurring_fees_id );
			return $result;
		}
	}
	/**
	 * Retrieves all fees payment records (invoices) for a specific student.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $std_id The student ID.
	 * @return array Array of fees payment objects.
	 * @since  1.0.0
	 */
	public function mjschool_get_all_student_fees_data( $std_id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment WHERE student_id=%d", intval( $std_id ) ) );
		return $result;
	}
	/**
	 * Retrieves fees payment records that are due on a specific reminder date.
	 *
	 * Filters for records where payment_status is 0 (unpaid) or 1 (partially paid),
	 * and the due date (`end_year`) matches the provided reminder date.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  string $reminder_date The date to check for fees due (format 'Y-m-d').
	 * @return array Array of fees payment objects for which a reminder is needed.
	 * @since  1.0.0
	 */
	public function mjschool_get_all_student_fees_data_for_reminder( $reminder_date ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		$reminder_date               = sanitize_text_field( $reminder_date );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment WHERE (payment_status = %d OR payment_status = %d) AND (end_year = %s)", 0, 1, $reminder_date ) );
		return $result;
	}
	/**
	 * Retrieves all payment history records associated with a single fees payment ID (invoice).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $fees_pay_id The ID of the fees payment (invoice).
	 * @return array Array of payment history objects.
	 * @since  1.0.0
	 */
	public function mjschool_get_payment_histry_data( $fees_pay_id ) {
		global $wpdb;
		$table_mjschool_fee_payment_history = $wpdb->prefix . 'mjschool_fee_payment_history';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fee_payment_history WHERE fees_pay_id=%d", intval( $fees_pay_id ) ) );
		return $result;
	}
	/**
	 * Records a manual payment entry into the fees payment history and updates the invoice status.
	 *
	 * This method calculates the new `fees_paid_amount` and updates the overall `payment_status`
	 * of the main fees payment record (`mjschool_fees_payment`). It also handles notifications.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  array $data Array of payment data (amount, method, transaction ID, etc.).
	 * @return int The ID of the newly inserted payment history record.
	 * @since  1.0.0
	 */
	public function mjschool_add_feespayment_history( $data ) {
		global $wpdb;
		$table_mjschool_fee_payment_history = $wpdb->prefix . 'mjschool_fee_payment_history';
		$tbl_payment                        = $wpdb->prefix . 'mjschool_fees_payment';
		// -------Usersmeta table data. --------------
		$fees_pay_id               = isset( $data['fees_pay_id'] ) ? intval( $data['fees_pay_id'] ) : 0;
		$feedata['fees_pay_id']    = $fees_pay_id;
		$feedata['amount']         = isset( $data['amount'] ) ? floatval( $data['amount'] ) : 0;
		$feedata['payment_note']   = isset( $data['payment_note'] ) ? sanitize_textarea_field( $data['payment_note'] ) : '';
		$feedata['payment_method'] = isset( $data['payment_method'] ) ? sanitize_text_field( $data['payment_method'] ) : '';
		if ( isset( $data['trasaction_id'] ) ) {
			$feedata['trasaction_id'] = sanitize_text_field( $data['trasaction_id'] );
		}
		if ( ! empty( $data['paid_by_date'] ) ) {
			$feedata['paid_by_date'] = date( 'Y-m-d', strtotime( sanitize_text_field( $data['paid_by_date'] ) ) );
		} else {
			$feedata['paid_by_date'] = date( 'Y-m-d' );
		}
		$feedata['created_by']           = get_current_user_id();
		$paid_amount                     = $this->mjschool_get_paid_amount_by_feepayid( $feedata['fees_pay_id'] );
		$uddate_data['fees_paid_amount'] = floatval( $paid_amount ) + floatval( $feedata['amount'] );
		$uddate_data['payment_status']   = $this->mjschool_get_payment_status_name( intval( $data['fees_pay_id'] ) );
		$uddate_data['fees_pay_id']      = $fees_pay_id;
		$this->mjschool_update_paid_fees_amount( $uddate_data );
		$uddate_data1['payment_status'] = $this->mjschool_get_payment_status_name( $fees_pay_id );
		$uddate_data1['fees_pay_id']    = $fees_pay_id;
		$this->mjschool_update_payment_status_fees_amount( $uddate_data1 );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result                        = $wpdb->insert( $table_mjschool_fee_payment_history, $feedata );
		$ids                           = $wpdb->insert_id;
		$email_subject                 = get_option( 'mjschool_payment_recived_mailsubject' );
		$MailCont                      = get_option( 'mjschool_payment_recived_mailcontent' );
		$feespaydata                   = $this->mjschool_get_single_fee_mjschool_payment( $fees_pay_id );
		$StudentData                   = get_userdata( $feespaydata->student_id );
		$SearchArr['{{student_name}}'] = mjschool_get_display_name( $feespaydata->student_id );
		$SearchArr['{{invoice_no}}']   = $feespaydata->fees_pay_id;
		$SearchArr['{{school_name}}']  = get_option( 'mjschool_name' );
		$email_to                      = $StudentData->user_email;
		$search['{{school_name}}']     = get_option( 'mjschool_name' );
		$email_message                 = mjschool_string_replacement( $SearchArr, get_option( 'mjschool_payment_recived_mailcontent' ) );
		if ( get_option( 'mjschool_mail_notification' ) === '1' ) {
			mjschool_send_mail_paid_invoice_pdf( $email_to, $email_subject, $email_message, $fees_pay_id );
		}
		/* Start Send Push Notification. */
		$student_id        = $feespaydata->student_id;
		$device_token[]    = get_user_meta( $student_id, 'token_id', true );
		$title             = esc_html__( 'New Notification For Payment', 'mjschool' );
		$text              = esc_html__( 'Your have successfully paid your invoice', 'mjschool' );
		$notification_data = array(
			'registration_ids' => $device_token,
			'data'             => array(
				'title' => $title,
				'body'  => $text,
				'type'  => 'Message',
			),
		);
		$json              = wp_json_encode( $notification_data );
		$message           = mjschool_send_push_notification( $json );
		/* End Send Push Notification. */
		return $ids;
	}
	/**
	 * Records a payment from the PayFast gateway into the fees payment history and updates the invoice status.
	 *
	 * Similar to `mjschool_add_feespayment_history` but tailored for external payment gateway data.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  array $data Array of payment data received from PayFast (or similar gateway).
	 * @return int|bool The result of the database insert.
	 * @since  1.0.0
	 */
	public function mjschool_add_feespayment_history_For_payfast( $data ) {
		global $wpdb;
		$table_mjschool_fee_payment_history = $wpdb->prefix . 'mjschool_fee_payment_history';
		$tbl_payment                        = $wpdb->prefix . 'mjschool_fees_payment';
		$fees_pay_id                        = isset( $data['fees_pay_id'] ) ? intval( $data['fees_pay_id'] ) : 0;
		$feedata['fees_pay_id']             = $fees_pay_id;
		$feedata['amount']                  = isset( $data['amount'] ) ? floatval( $data['amount'] ) : 0;
		$feedata['payment_method']          = isset( $data['payment_method'] ) ? sanitize_text_field( $data['payment_method'] ) : '';
		$feedata['trasaction_id']           = isset( $data['trasaction_id'] ) ? sanitize_text_field( $data['trasaction_id'] ) : '';
		$feedata['paid_by_date']            = date( 'Y-m-d' );
		$feedata['created_by']              = isset( $data['created_by'] ) ? intval( $data['created_by'] ) : 0;
		$paid_amount                        = $this->mjschool_get_paid_amount_by_feepayid( $feedata['fees_pay_id'] );
		$uddate_data['fees_paid_amount']    = floatval( $paid_amount ) + floatval( $feedata['amount'] );
		$uddate_data['payment_status']      = $this->mjschool_get_payment_status_name( intval( $data['fees_pay_id'] ) );
		$uddate_data['fees_pay_id']         = $fees_pay_id;
		$this->mjschool_update_paid_fees_amount( $uddate_data );
		$uddate_data1['payment_status'] = $this->mjschool_get_payment_status_name( $fees_pay_id );
		$uddate_data1['fees_pay_id']    = $fees_pay_id;
		$this->mjschool_update_payment_status_fees_amount( $uddate_data1 );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result                        = $wpdb->insert( $table_mjschool_fee_payment_history, $feedata );
		$email_subject                 = get_option( 'mjschool_payment_recived_mailsubject' );
		$MailCont                      = get_option( 'mjschool_payment_recived_mailcontent' );
		$feespaydata                   = $this->mjschool_get_single_fee_mjschool_payment( $fees_pay_id );
		$name_first                    = isset( $data['name_first'] ) ? sanitize_text_field( $data['name_first'] ) : '';
		$name_last                     = isset( $data['name_last'] ) ? sanitize_text_field( $data['name_last'] ) : '';
		$SearchArr['{{student_name}}'] = $name_first . ' ' . $name_last;
		$SearchArr['{{invoice_no}}']   = $feespaydata->fees_pay_id;
		$SearchArr['{{school_name}}']  = get_option( 'mjschool_name' );
		$email_to                      = isset( $data['email_address'] ) ? sanitize_email( $data['email_address'] ) : '';
		$search['{{school_name}}']     = get_option( 'mjschool_name' );
		$email_message                 = mjschool_string_replacement( $SearchArr, get_option( 'mjschool_payment_recived_mailcontent' ) );
		if ( get_option( 'mjschool_mail_notification' ) === '1' ) {
			mjschool_send_mail_paid_invoice_pdf( $email_to, $email_subject, $email_message, $fees_pay_id );
		}
		return $result;
	}
	/**
	 * Calculates and returns the payment status code for a given invoice ID.
	 *
	 * Status Codes:
	 * - 0: No result found (Error/Unknown)
	 * - 1: Unpaid or Partially Paid
	 * - 2: Fully Paid
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $fees_pay_id The ID of the fees payment (invoice).
	 * @return int The payment status code (0, 1, or 2).
	 * @since  1.0.0
	 */
	public function mjschool_get_payment_status_name( $fees_pay_id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment WHERE fees_pay_id=%d", intval( $fees_pay_id ) ) );
		if ( ! empty( $result ) ) {
			if ( floatval( $result->fees_paid_amount ) == 0 ) {
				return 1;
			} elseif ( floatval( $result->fees_paid_amount ) < floatval( $result->total_amount ) ) {
				return 1;
			} else {
				return 2;
			}
		} else {
			return 0;
		}
	}
	/**
	 * Retrieves the current total paid amount for a specific fees payment ID.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $fees_pay_id The ID of the fees payment (invoice).
	 * @return string The total paid amount as a string (from the database column).
	 * @since  1.0.0
	 */
	public function mjschool_get_paid_amount_by_feepayid( $fees_pay_id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT fees_paid_amount FROM $table_mjschool_fees_payment where fees_pay_id = %d", intval( $fees_pay_id ) ) );
		return $result->fees_paid_amount;
	}
	/**
	 * Updates the `fees_paid_amount` and `payment_status` fields of a main fees payment record.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  array $data Array containing `fees_paid_amount`, `payment_status`, and `fees_pay_id`.
	 * @return int|bool The number of rows updated on success, or false on error.
	 * @since  1.0.0
	 */
	public function mjschool_update_paid_fees_amount( $data ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		$feedata['fees_paid_amount'] = floatval( $data['fees_paid_amount'] );
		$feedata['payment_status']   = intval( $data['payment_status'] );
		$fees_id['fees_pay_id']      = intval( $data['fees_pay_id'] );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->update( $table_mjschool_fees_payment, $feedata, $fees_id );
		return $result;
	}
	/**
	 * Updates only the `payment_status` field of a main fees payment record.
	 *
	 * Note: This function is redundant as its logic is fully contained within
	 * `mjschool_update_paid_fees_amount`.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  array $data Array containing `payment_status` and `fees_pay_id`.
	 * @return int|bool The number of rows updated on success, or false on error.
	 * @since  1.0.0
	 */
	public function mjschool_update_payment_status_fees_amount( $data ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		$feedata['payment_status']   = intval( $data['payment_status'] );
		$fees_id['fees_pay_id']      = intval( $data['fees_pay_id'] );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->update( $table_mjschool_fees_payment, $feedata, $fees_id );
		return $result;
	}
	/**
	 * Retrieves all payment history records for a given fees payment ID (invoice).
	 *
	 * Identical in function to `mjschool_get_payment_histry_data`.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $id The fees payment ID (invoice ID).
	 * @return array Array of payment history objects.
	 * @since  1.0.0
	 */
	public function mjschool_get_all_fees_payments( $id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fee_payment_history';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment WHERE fees_pay_id=%d", intval( $id ) ) );
		return $result;
	}
	/**
	 * Retrieves all fees payment records (invoices) excluding those marked as 'draft'.
	 *
	 * Ordered by creation date descending.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @return array Array of fees payment objects.
	 * @since  1.0.0
	 */
	public function mjschool_get_all_fees() {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment WHERE invoice_status != %s OR invoice_status IS NULL Order By created_date DESC", 'draft' ) );
		return $result;
	}
	/**
	 * Retrieves all fees payment records created by the currently logged-in user (teacher/admin).
	 *
	 * Excludes drafts and orders by creation date descending.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @return array Array of fees payment objects created by the current user.
	 * @since  1.0.0
	 */
	public function mjschool_get_all_fees_own() {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		$get_current_user_id         = get_current_user_id();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_mjschool_fees_payment 
				WHERE created_by = %d 
				AND (invoice_status != %s OR invoice_status IS NULL) 
				ORDER BY created_date DESC",
				$get_current_user_id,
				'draft'
			)
		);
		return $result;
	}
	/**
	 * Retrieves a single fees payment record by its ID.
	 *
	 * Identical in function to `mjschool_get_single_fee_mjschool_payment`.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $fees_pay_id The ID of the fees payment (invoice).
	 * @return object|null Database row object or null.
	 * @since  1.0.0
	 */
	public function mjschool_get_single_fee_payment( $fees_pay_id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment where fees_pay_id =%d", intval( $fees_pay_id ) ) );
		return $result;
	}
	/**
	 * Retrieves a single fees payment record by its ID.
	 *
	 * Identical in function to `mjschool_get_single_fee_payment`.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $fees_pay_id The ID of the fees payment (invoice).
	 * @return object|null Database row object or null.
	 * @since  1.0.0
	 */
	public function mjschool_get_single_fee_mjschool_payment( $fees_pay_id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment where fees_pay_id =%d", intval( $fees_pay_id ) ) );
		return $result;
	}
	/**
	 * Retrieves a single fees type record by its ID from the `mjschool_fees` table.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $fees_id The ID of the fees type.
	 * @return object|null Database row object or null.
	 * @since  1.0.0
	 */
	public function mjschool_get_single_feetype_data( $fees_id ) {
		global $wpdb;
		$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees where fees_id =%d", intval( $fees_id ) ) );
		return $result;
	}
	/**
	 * Deletes a fees type record by its ID from the `mjschool_fees` table and logs the action.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $fees_id The ID of the fees type to delete.
	 * @return int|bool The number of rows deleted on success, or false on error.
	 * @since  1.0.0
	 */
	public function mjschool_delete_feetype_data( $fees_id ) {
		$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Fees Type Deleted', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		global $wpdb;
		$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_mjschool_fees where fees_id=%d", intval( $fees_id ) ) );
		return $result;
	}
	/**
	 * Deletes a fees payment record (invoice) and logs the action with the associated student's name.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $fees_pay_id The ID of the fees payment (invoice) to delete.
	 * @return int|bool The number of rows deleted on success, or false on error.
	 * @since  1.0.0
	 */
	public function mjschool_delete_fee_payment_data( $fees_pay_id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$payment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment where fees_pay_id=%d", intval( $fees_pay_id ) ) );
		if ( $payment ) {
			$student   = mjschool_get_user_name_by_id( $payment->student_id );
			$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			mjschool_append_audit_log( '' . esc_html__( 'Fees Payment Deleted', 'mjschool' ) . '( ' . esc_html( $student ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_mjschool_fees_payment where fees_pay_id=%d", intval( $fees_pay_id ) ) );
			return $result;
		}
	}
	/**
	 * Retrieves the 3 most recently created fees payment records for a dashboard summary.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @return array Array of the 3 most recent fees payment objects.
	 * @since  1.0.0
	 */
	public function mjschool_get_fees_payment_dashboard() {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment ORDER BY fees_pay_id DESC LIMIT %d", 3 ) );
		return $result;
	}
	/**
	 * Retrieves the base amount for a specific fees type ID.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $fees_id The ID of the fees type.
	 * @return string The fees amount as a string, or '0.00' if not found.
	 * @since  1.0.0
	 */
	public function mjschool_feetype_amount_data( $fees_id ) {
		global $wpdb;
		$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees where fees_id=%d", intval( $fees_id ) ) );
		if ( ! empty( $result->fees_amount ) ) {
			$fees_amount = $result->fees_amount;
		} else {
			$fees_amount = '0.00';
		}
		return $fees_amount;
	}
	/**
	 * Retrieves the 5 most recent fees payment records (invoices).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @return array Array of the 5 most recent fees payment objects.
	 * @since  1.0.0
	 */
	public function mjschool_get_five_fees() {
		global $wpdb;
		$table_mjschool_fees_payment = esc_sql( $wpdb->prefix . 'mjschool_fees_payment' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_mjschool_fees_payment} ORDER BY fees_id DESC LIMIT %d", 5 ) );
		return $result;
	}
	/**
	 * Retrieves the 5 most recent fees payment records (invoices) for a specific frontend user (student).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $id The student ID.
	 * @return array Array of the student's 5 most recent fees payment objects.
	 * @since  1.0.0
	 */
	public function mjschool_get_five_fees_users( $id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment where student_id =%d ORDER BY fees_id DESC LIMIT 5", intval( $id ) ) );
		return $result;
	}
	/**
	 * Retrieves all recurring fees setup records.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @return array Array of all recurring fees setup objects.
	 * @since  1.0.0
	 */
	public function mjschool_get_all_recurring_fees() {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment_recurring';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM $table_mjschool_fees_payment Order By created_date DESC" );
		return $result;
	}
	/**
	 * Retrieves all active recurring fees setup records that are due to be processed.
	 *
	 * An active record is one where:
	 * 1. `status` is 'yes'.
	 * 2. `recurring_enddate` (the next due date) is yesterday relative to the provided `$date`.
	 * 3. `end_year` (the final end date) is later than the next due date.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  string $date The current date (format 'Y-m-d').
	 * @return array Array of recurring fees setup objects that require processing.
	 * @since  1.0.0
	 */
	public function mjschool_get_all_recurring_fees_active( $date ) {
		$date              = sanitize_text_field( $date );
		$recurring_enddate = date( 'Y-m-d', strtotime( '-1 day', strtotime( $date ) ) );
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment_recurring';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment WHERE status = %s AND recurring_enddate = %s AND end_year > %s", 'yes', $recurring_enddate, $recurring_enddate ) );
		return $result;
	}
	/**
	 * Deletes a single recurring fees setup record and logs the action.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $recurring_id The ID of the recurring fees setup record to delete.
	 * @return int|bool The number of rows deleted on success, or false on error.
	 * @since  1.0.0
	 */
	public function mjschool_delete_recurring_fees( $recurring_id ) {
		$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Recurring Fees Deleted', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		global $wpdb;
		$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees_payment_recurring';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_mjschool_fees where recurring_id =%d", intval( $recurring_id ) ) );
		return $result;
	}
	/**
	 * Retrieves a single recurring fees setup record by its ID.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $recurring_id The ID of the recurring fees setup record.
	 * @return object|null Database row object or null.
	 * @since  1.0.0
	 */
	public function mjschool_get_single_recurring_fees( $recurring_id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment_recurring';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment where recurring_id =%d", intval( $recurring_id ) ) );
		return $result;
	}
	/**
	 * Retrieves all recurring fees setup records associated with a specific class ID.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  int $class_id The ID of the class.
	 * @return array Array of recurring fees setup objects for the specified class.
	 * @since  1.0.0
	 */
	public function mjschool_get_recurring_fees_by_class( $class_id ) {
		global $wpdb;
		$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment_recurring';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment where class_id =%d", intval( $class_id ) ) );
		return $result;
	}

	/**
	 * Get a single payment history record.
	 *
	 * @since 1.0.0
	 * @param int $id Payment history ID.
	 * @return array Payment history details.
	 */
	public function mjschool_get_single_payment_history( $id ) {
		global $wpdb;
		$table_mjschool_fee_payment_history = $wpdb->prefix . 'mjschool_fee_payment_history';
		$id                                 = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fee_payment_history WHERE payment_history_id=%d", $id ) );
		return $result;
	}
}