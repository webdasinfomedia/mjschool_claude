<?php
/**
 * AJAX Handlers for the MjSchool Plugin
 *
 * This file contains all AJAX callback functions used throughout the MjSchool
 * WordPress plugin. These functions handle various dynamic operations.
 *
 * Security:
 * - Every AJAX function validates a security nonce using `wp_verify_nonce()`.
 * - Most operations require users to be authenticated via `is_user_logged_in()`.
 * - Data is sanitized using `sanitize_text_field()`, `intval()`, and other proper sanitizers.
 *
 * @package MjSchool
 * @subpackage MjSchool
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Hook for AJAX request to view a video.
 * Handles both logged-in and non-logged-in users.
 * @since      1.0.0
 */
add_action( 'wp_ajax_nopriv_mjschool_view_video', 'mjschool_view_video' );
add_action( 'wp_ajax_mjschool_view_video', 'mjschool_view_video' );
/**
 * Displays a video in a modal popup.
 *
 * This function verifies the nonce and user login, then outputs HTML for a modal
 * containing an iframe with the video. It also includes JavaScript to stop the video
 * when the modal is closed.
 *
 * @since 1.0.0
 * @return void Outputs HTML and JavaScript directly.
 */
function mjschool_view_video() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$link  = isset( $_REQUEST['link'] ) ? esc_url_raw( wp_unslash( $_REQUEST['link'] ) ) : '';
	$title = isset( $_REQUEST['title'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['title'] ) ) : '';
	?>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
		
		<a href="javascript:void(0);" id="close-popup" onclick="mjschool_stop_video()" class="mjschool-event-close-btn badge badge-success pull-right mjschool-dashboard-popup-design">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>">
		</a>
		
		<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( $title ); ?></h4>
	</div>
	<div class="mjschool-border-panel-body mjschool-pop-header-p-20px mjschool-exercise-detail-popup">
		<div class="row">
			<div class="col-sm-12 col-md-12 col-xl-12 col-xs-12 mb-3">
				<?php echo '<iframe id="video-frame" class="mjschool-video-width-height mjschool-video-frame-class" src="' . esc_url( $link ) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'; ?>
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
/**
 * Generates HTML email content with a predefined template design.
 *
 * This function creates an HTML email template using the system's logo, color, and footer description.
 *
 * @since 1.0.0
 * @param string $message The message content to include in the email body.
 * @return string The complete HTML email template as a string.
 */
function mjschool_get_mail_content_with_template_design( $message ) {
	$logo         = esc_url( get_option( 'mjschool_system_logo' ) );
	$system_color = sanitize_hex_color( get_option( 'mjschool_system_color_code' ) );
	$footer_desc  = wp_kses_post( get_option( 'mjschool_footer_description' ) );
	$message      = wp_kses_post( $message );
	 
	$email_template = '
	<html>
		<body>
			<div bgcolor="#ffffff" style="font-family: \'Poppins\',Helvetica,Arial sans-serif;height:100%;width:100%!important;background-color:#ffffff">
				<div style="background-color: aliceblue !important;font-family: \'Poppins\',Helvetica,Arial sans-serif;margin:0 auto;max-width:525px">
					<table style="font-family: \'Poppins\',Helvetica,Arial sans-serif;float: left;width: 100%;">
					<tbody>
						<tr style="font-family: \'Poppins\',Helvetica,Arial sans-serif;background:' . esc_attr( $system_color ) . ';">
							<td style="font-family: \'Poppins\',Helvetica,Arial sans-serif;text-align:center ; padding-top: 12px;">
								<p style="font-family: \'Poppins\',Helvetica,Arial sans-serif">
									<img alt="WP-GYM" src="' . esc_url( $logo ) . '" style="font-family: \'Poppins\',Helvetica,Arial sans-serif;max-width: 50%;" class="">
								</p>
							</td>
						</tr>
					</tbody>
					</table>
					<table style="font-family: \'Poppins\',Helvetica,Arial sans-serif;float:left;width:100%;background-color:aliceblue;">
						<tbody>
							<tr style="font-family: \'Poppins\',Helvetica,Arial sans-serif">
								<td style="font-family: \'Poppins\',Helvetica,Arial sans-serif;color:#333333;padding-top:15px !important;padding: 0px 20px;">
									<p>
										<pre style="white-space: pre-wrap;word-wrap: break-word; overflow-wrap: break-word; max-width: 100%;font-family: Poppins;">' . $message . '</pre>
									</p>
								</td>
							</tr>
							<tr style="font-family: \'Poppins\',Helvetica,Arial sans-serif;background:' . esc_attr( $system_color ) . ';">
								<td style="font-family: \'Poppins\',Helvetica,Arial sans-serif;color:#ffffff;">
									<p style="font-family: \'Poppins\',Helvetica,Arial sans-serif;font-size:12px;text-align:center;margin-top:10px">
									' . $footer_desc . '
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</body>
	</html>';
	 
	return $email_template;
}
add_action( 'wp_ajax_mjschool_load_subject_class_id_and_section_id', 'mjschool_load_subject_class_id_and_section_id' );
/**
 * Loads and outputs subject options based on class and section IDs.
 *
 * This function handles AJAX requests to retrieve subjects filtered by class and section,
 * considering user roles (teacher, admin) and access rights.
 *
 * @since 1.0.0
 * @return void Outputs HTML option tags directly.
 */
function mjschool_load_subject_class_id_and_section_id() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id   = isset( $_POST['class_id'] ) ? intval( wp_unslash( $_POST['class_id'] ) ) : 0;
	$section_id = isset( $_POST['section_id'] ) ? intval( wp_unslash( $_POST['section_id'] ) ) : 0;
	global $wpdb;
	$table_name  = $wpdb->prefix . 'mjschool_subject';
	$table_name2 = $wpdb->prefix . 'mjschool_teacher_subject';
	$user_id     = get_current_user_id();
	// ------------------------TEACHER ACCESS.---------------------------------//
	$teacher_access      = get_option( 'mjschool_access_right_teacher' );
	$teacher_access_data = $teacher_access['teacher'];
	$data                = array();
	foreach ( $teacher_access_data as $key => $value ) {
		if ( 'subject' === $key ) {
			$data = $value;
		}
	}
	if ( 'teacher' === mjschool_get_roles( $user_id ) && isset( $data['own_data'] ) && $data['own_data'] === 1 ) {
		if ( $section_id === 0 ) {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE teacher_id = %d AND class_id = %d", $user_id, $class_id );
		} else {
			$query = $wpdb->prepare( "SELECT p1.*, p2.* FROM $table_name p1 INNER JOIN $table_name2 p2 ON (p1.subid = p2.subject_id) WHERE p2.teacher_id = %d AND p1.class_id = %d AND p1.section_id = %d", $user_id, $class_id, $section_id );
		}
	} elseif ( mjschool_get_roles( $user_id ) === 'teacher' ) {
		if ( $section_id !== 0 ) {
			$query = $wpdb->prepare( "SELECT p1.*, p2.* FROM $table_name p1 INNER JOIN $table_name2 p2 ON (p1.subid = p2.subject_id) WHERE p2.teacher_id = %d AND p1.class_id = %d AND p1.section_id = %d", $user_id, $class_id, $section_id );
		} else {
			$query = $wpdb->prepare( "SELECT p1.*, p2.* FROM $table_name p1 INNER JOIN $table_name2 p2 ON (p1.subid = p2.subject_id) WHERE p2.teacher_id = %d AND p1.class_id = %d", $user_id, $class_id );
		}
	} elseif ( is_admin() ) {
		if ( $section_id !== 0 ) {
			$query = $wpdb->prepare( "SELECT p1.* FROM $table_name p1 WHERE p1.class_id = %d AND p1.section_id = %d", $class_id, $section_id );
		} else {
			$query = $wpdb->prepare( "SELECT p1.* FROM $table_name p1 WHERE p1.class_id = %d ", $class_id );
		}
	} else {
		$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d", $class_id );
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_results( $query );
	$defaultmsg       = esc_html__( 'Select subject', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	foreach ( $retrieve_subject as $retrieved_data ) {
		echo "<option value='" . esc_attr( $retrieved_data->subid ) . "'> " . esc_html( $retrieved_data->sub_name . '-' . $retrieved_data->subject_code ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_subject', 'mjschool_load_subject' );
add_action( 'wp_ajax_nopriv_mjschool_load_subject', 'mjschool_load_subject' );
/**
 * Loads and outputs subject options based on class ID.
 *
 * This function retrieves subjects for a given class, considering user roles (teacher or others).
 *
 * @since 1.0.0
 * @return void Outputs HTML option tags directly.
 */
function mjschool_load_subject() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id = isset( $_POST['class_list'] ) ? intval( wp_unslash( $_POST['class_list'] ) ) : 0;
	global $wpdb;
	$table_name  = $wpdb->prefix . 'mjschool_subject';
	$table_name2 = $wpdb->prefix . 'mjschool_teacher_subject';
	$user_id     = get_current_user_id();
	if ( mjschool_get_roles( $user_id ) === 'teacher' ) {
		// Prepare query for teacher role.
		$query = $wpdb->prepare( "SELECT p1.*, p2.* FROM $table_name p1 INNER JOIN $table_name2 p2 ON (p1.subid = p2.subject_id) WHERE p2.teacher_id = %d AND p1.class_id = %d", $user_id, $class_id );
	} else {
		// Prepare query for non-teacher role.
		$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d", $class_id );
	}
	// Get the results from the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_results( $query );
	// Default message.
	$defaultmsg = esc_html__( 'Select subject', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	// Loop through the results and display them.
	foreach ( $retrieve_subject as $retrieved_data ) {
		echo "<option value='" . esc_attr( $retrieved_data->subid ) . "'> " . esc_html( $retrieved_data->sub_name . '-' . $retrieved_data->subject_code ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_subject_by_exam', 'mjschool_load_subject_by_exam' );
add_action( 'wp_ajax_nopriv_mjschool_load_subject_by_exam', 'mjschool_load_subject_by_exam' );
/**
 * Loads and outputs subject options based on class and exam IDs.
 *
 * This function retrieves subjects associated with a specific exam for a given class.
 *
 * @since 1.0.0
 * @return void Outputs HTML option tags directly.
 */
function mjschool_load_subject_by_exam() {
	global $wpdb;
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id           = isset( $_POST['class_list'] ) ? intval( wp_unslash( $_POST['class_list'] ) ) : 0;
	$exam_id            = isset( $_POST['exam_list'] ) ? intval( wp_unslash( $_POST['exam_list'] ) ) : 0;
	$subject_table_name = $wpdb->prefix . 'mjschool_subject';
	$mjschool_exam_obj  = new Mjschool_exam();
	$exam_data          = $mjschool_exam_obj->mjschool_exam_data( $exam_id );
	$all_exam_ids       = array(); // Default to an empty array.
	$exam_subject_ids   = array();
	if ( isset( $exam_data ) && ! empty( $exam_data->subject_data ) ) {
		$all_exam_ids     = json_decode( $exam_data->subject_data, true );
		$exam_subject_ids = array_column( $all_exam_ids, 'subject_id' );
	}
	
	if ( empty( $exam_subject_ids ) ) {
		$defaultmsg = esc_html__( 'Select subject', 'mjschool' );
		echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
		die();
	}
	
	$exam_subject_ids = array_map( 'intval', $exam_subject_ids );
	$placeholders     = implode( ',', array_fill( 0, count( $exam_subject_ids ), '%d' ) );
	$query            = "SELECT * FROM $subject_table_name WHERE subid IN ($placeholders)";
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$retrieve_subject = $wpdb->get_results( $wpdb->prepare( $query, ...$exam_subject_ids ) );

	// Default message.
	$defaultmsg = esc_html__( 'Select subject', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	// Loop through the results and display them.
	foreach ( $retrieve_subject as $retrieved_data ) {
		echo "<option value='" . esc_attr( $retrieved_data->subid ) . "'> " . esc_html( $retrieved_data->sub_name . '-' . $retrieved_data->subject_code ) . '</option>';
	}
	die();

}
add_action( 'wp_ajax_mjschool_load_subject_by_section', 'mjschool_load_subject_by_section' );
add_action( 'wp_ajax_nopriv_mjschool_load_subject_by_section', 'mjschool_load_subject_by_section' );
/**
 * Loads and outputs subject options based on class and section IDs.
 *
 * This function retrieves subjects for a given class and section, considering user roles.
 *
 * @since 1.0.0
 * @return void Outputs HTML option tags directly.
 */
function mjschool_load_subject_by_section() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id   = isset( $_POST['class_list'] ) ? intval( wp_unslash( $_POST['class_list'] ) ) : 0;
	$section_id = isset( $_POST['section_list'] ) ? intval( wp_unslash( $_POST['section_list'] ) ) : 0;
	global $wpdb;
	$table_name  = $wpdb->prefix . 'mjschool_subject';
	$table_name2 = $wpdb->prefix . 'mjschool_teacher_subject';
	$user_id     = get_current_user_id();
	if ( mjschool_get_roles( $user_id ) === 'teacher' ) {
		// Prepare query for teacher role.
		if ( ! empty( $section_id ) ) {
			$query = $wpdb->prepare( "SELECT p1.*, p2.* FROM $table_name p1 INNER JOIN $table_name2 p2 ON (p1.subid = p2.subject_id) WHERE p2.teacher_id = %d AND p1.class_id = %d AND p1.section_id = %d", $user_id, $class_id, $section_id );
		} else {
			$query = $wpdb->prepare( "SELECT p1.*, p2.* FROM $table_name p1 INNER JOIN $table_name2 p2 ON (p1.subid = p2.subject_id) WHERE p2.teacher_id = %d AND p1.class_id = %d", $user_id, $class_id );
		}
	} else {
		// Prepare query for non-teacher role.
		if ( ! empty( $section_id ) ) {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d AND section_id = %d", $class_id, $section_id );
		} else {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d", $class_id );
		}
	}
	// Get the results from the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_results( $query );
	// Default message.
	$defaultmsg = esc_html__( 'Select subject', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	// Loop through the results and display them.
	foreach ( $retrieve_subject as $retrieved_data ) {
		echo "<option value='" . esc_attr( $retrieved_data->subid ) . "'> " . esc_html( $retrieved_data->sub_name . '-' . $retrieved_data->subject_code ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_membership_payment_report', 'mjschool_load_membership_payment_report' );
add_action( 'wp_ajax_nopriv_mjschool_load_membership_payment_report', 'mjschool_load_membership_payment_report' );
/**
 * Loads and outputs a payment report chart based on month and year.
 *
 * This function generates a bar chart for payment data, either for all months in a year
 * or for each day in a specific month. If no data is found, it displays a no-data image.
 *
 * @since 1.0.0
 * @return void Outputs HTML and JavaScript for the chart directly.
 */
function mjschool_load_membership_payment_report() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$month_val = isset( $_REQUEST['month_val'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['month_val'] ) ) : '';
	$year_val  = isset( $_REQUEST['year_val'] ) ? intval( wp_unslash( $_REQUEST['year_val'] ) ) : 0;
	global $wpdb;
	$table_name      = $wpdb->prefix . 'mjschool_fee_payment_history';
	$payment_array   = array();
	$currency_symbol = mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) );
	
	if ( $month_val === 'all_month' ) {
		$month               = array(
			'1'  => esc_html__( 'January', 'mjschool' ),
			'2'  => esc_html__( 'February', 'mjschool' ),
			'3'  => esc_html__( 'March', 'mjschool' ),
			'4'  => esc_html__( 'April', 'mjschool' ),
			'5'  => esc_html__( 'May', 'mjschool' ),
			'6'  => esc_html__( 'June', 'mjschool' ),
			'7'  => esc_html__( 'July', 'mjschool' ),
			'8'  => esc_html__( 'August', 'mjschool' ),
			'9'  => esc_html__( 'September', 'mjschool' ),
			'10' => esc_html__( 'October', 'mjschool' ),
			'11' => esc_html__( 'November', 'mjschool' ),
			'12' => esc_html__( 'December', 'mjschool' ),
		);
		$result              = array();
		$data_points_payment = array();
		array_push( $data_points_payment, array( esc_html__( 'Month', 'mjschool' ), esc_html__( 'Payment', 'mjschool' ) ) );
		foreach ( $month as $key => $value ) {
			$q = $wpdb->prepare( "SELECT * FROM $table_name WHERE YEAR(paid_by_date) = %d AND MONTH(paid_by_date) = %d", $year_val, $key );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $q );
			$amount = 0;
			foreach ( $result as $payment_entry ) {
				$amount += $payment_entry->amount;
			}
			$payment_amount  = $amount;
			$payment_array[] = $payment_amount;
			array_push( $data_points_payment, array( $value, $payment_amount ) );
		}
	} else {
		$select_month        = isset( $_REQUEST['month_val'] ) ? intval( wp_unslash( $_REQUEST['month_val'] ) ) : 0;
		$data_points_payment = array();
		$date_list           = array();
		$day_date            = array();
		
		if ( $select_month === 2 ) {
			$max_d = 29;
		} elseif ( $select_month === 4 || $select_month === 6 || $select_month === 9 || $select_month === 11 ) {
			$max_d = 30;
		} else {
			$max_d = 31;
		}
		for ( $d = 1; $d <= $max_d; $d++ ) {
			$time = mktime( 12, 0, 0, $select_month, $d, $year_val );
			if ( (int) gmdate( 'm', $time ) === $select_month ) {
				$date_list[] = gmdate( 'Y-m-d', $time );
			}
			$day_date[] = gmdate( 'd', $time );
		}
		$month_val_arr = array();
		$i             = 1;
		foreach ( $day_date as $value ) {
			$month_val_arr[ $i ] = $value;
			++$i;
		}
		array_push( $data_points_payment, array( esc_html__( 'Day', 'mjschool' ), esc_html__( 'Payment', 'mjschool' ) ) );
		foreach ( $month_val_arr as $key => $value ) {
			// GET INCOME EXPENCE DATA.
			$q = $wpdb->prepare( "SELECT * FROM $table_name WHERE YEAR(paid_by_date) = %d AND MONTH(paid_by_date) = %d AND DAY(paid_by_date) = %d", $year_val, $select_month, $value );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $q );
			$amount = 0;
			foreach ( $result as $payment_entry ) {
				$amount += $payment_entry->amount;
			}
			$payment_amount  = $amount;
			$payment_array[] = $payment_amount;
			array_push( $data_points_payment, array( $value, $payment_amount ) );
		}
	}
	$payment_filtered = array_filter( $payment_array );
	$new_array        = $data_points_payment;
	if ( ! empty( $payment_filtered ) ) {
		$labels = array_column( $new_array, 0 );
		$values = array_column( $new_array, 1 );

		// Remove header
		array_shift( $labels );
		array_shift( $values );
		?>
		<canvas id="mjschool-payment-bar-material" class="mjschool-payment-bar-material mjschool_chart_430pxmjschool_chart_430px" data-labels='<?php echo esc_attr( wp_json_encode( $labels ) ); ?>' data-values='<?php echo esc_attr( wp_json_encode( $values ) ); ?>' data-currency="<?php echo esc_attr( $currency_symbol ); ?>" data-color="<?php echo esc_attr( sanitize_hex_color( get_option( 'mjschool_system_color_code' ) ) ); ?>"></canvas>
		<?php
	} else {
		?>
		<div class="mjschool-calendar-event-new">
			<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
		</div>
		<?php
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_income_expence_report', 'mjschool_load_income_expence_report' );
add_action( 'wp_ajax_nopriv_mjschool_load_income_expence_report', 'mjschool_load_income_expence_report' );
/**
 * Loads and outputs an income and expense report chart based on month and year.
 *
 * This function generates a bar chart for income, expense, and net profit data,
 * either for all months in a year or for each day in a specific month.
 * If no data is found, it displays a no-data image.
 *
 * @since 1.0.0
 * @return void Outputs HTML and JavaScript for the chart directly.
 */
function mjschool_load_income_expence_report() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$month_val = isset( $_REQUEST['month_val'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['month_val'] ) ) : '';
	$year_val  = isset( $_REQUEST['year_val'] ) ? intval( wp_unslash( $_REQUEST['year_val'] ) ) : 0;
	global $wpdb;
	$table_name      = $wpdb->prefix . 'mjschool_income_expense';
	$income_array    = array();
	$expense_array   = array();
	$currency_symbol = mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) );
	
	// REPORT FOR PARTICULAR YEAR.
	if ( $month_val === 'all_month' ) {
		$month        = array(
			'1'  => esc_html__( 'January', 'mjschool' ),
			'2'  => esc_html__( 'February', 'mjschool' ),
			'3'  => esc_html__( 'March', 'mjschool' ),
			'4'  => esc_html__( 'April', 'mjschool' ),
			'5'  => esc_html__( 'May', 'mjschool' ),
			'6'  => esc_html__( 'June', 'mjschool' ),
			'7'  => esc_html__( 'July', 'mjschool' ),
			'8'  => esc_html__( 'August', 'mjschool' ),
			'9'  => esc_html__( 'September', 'mjschool' ),
			'10' => esc_html__( 'October', 'mjschool' ),
			'11' => esc_html__( 'November', 'mjschool' ),
			'12' => esc_html__( 'December', 'mjschool' ),
		);
		$result       = array();
		$dataPoints_2 = array();
		array_push( $dataPoints_2, array( esc_html__( 'Month', 'mjschool' ), esc_html__( 'Income', 'mjschool' ), esc_html__( 'Expense', 'mjschool' ), esc_html__( 'Net Profit', 'mjschool' ) ) );
		foreach ( $month as $key => $value ) {
			// GET INCOME EXPENCE DATA.
			$q  = $wpdb->prepare( "SELECT * FROM $table_name WHERE YEAR(income_create_date) = %d AND MONTH(income_create_date) = %d AND invoice_type = %s", $year_val, $key, 'income' );
			$q1 = $wpdb->prepare( "SELECT * FROM $table_name WHERE YEAR(income_create_date) = %d AND MONTH(income_create_date) = %d AND invoice_type = %s", $year_val, $key, 'expense' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $q );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result1               = $wpdb->get_results( $q1 );
			$expense_yearly_amount = 0;
			foreach ( $result1 as $expense_entry ) {
				$all_entry = json_decode( $expense_entry->entry );
				$amount    = 0;
				foreach ( $all_entry as $entry ) {
					$amount += $entry->amount;
				}
				$expense_yearly_amount += $amount;
			}
			$expense_amount       = $expense_yearly_amount;
			$income_yearly_amount = 0;
			foreach ( $result as $income_entry ) {
				$all_entry = json_decode( $income_entry->entry );
				$amount    = 0;
				foreach ( $all_entry as $entry ) {
					$amount += $entry->amount;
				}
				$income_yearly_amount += $amount;
			}
			$income_amount    = $income_yearly_amount;
			$expense_array[]  = $expense_amount;
			$income_array[]   = $income_amount;
			$net_profit_array = $income_amount - $expense_amount;
			array_push( $dataPoints_2, array( $value, $income_amount, $expense_amount, $net_profit_array ) );
		}
	} else {
		// REPORT FOR PARTICULAR MONTH WISE.
		$select_month = isset( $_REQUEST['month_val'] ) ? intval( wp_unslash( $_REQUEST['month_val'] ) ) : 0;
		$dataPoints_2 = array();
		$date_list    = array();
		$day_date     = array();
		
		if ( $select_month === 2 ) {
			$max_d = 29;
		} elseif ( $select_month === 4 || $select_month === 6 || $select_month === 9 || $select_month === 11 ) {
			$max_d = 30;
		} else {
			$max_d = 31;
		}
		for ( $d = 1; $d <= $max_d; $d++ ) {
			$time = mktime( 12, 0, 0, $select_month, $d, $year_val );
			if ( (int) gmdate( 'm', $time ) === $select_month ) {
				$date_list[] = gmdate( 'Y-m-d', $time );
			}
			$day_date[] = gmdate( 'd', $time );
		}
		$month_val_arr = array();
		$i             = 1;
		foreach ( $day_date as $value ) {
			$month_val_arr[ $i ] = $value;
			++$i;
		}
		array_push( $dataPoints_2, array( esc_html__( 'Day', 'mjschool' ), esc_html__( 'Income', 'mjschool' ), esc_html__( 'Expense', 'mjschool' ), esc_html__( 'Net Profit', 'mjschool' ) ) );
		foreach ( $month_val_arr as $key => $value ) {
			// GET INCOME EXPENCE DATA.
			$q  = $wpdb->prepare( "SELECT * FROM $table_name WHERE YEAR(income_create_date) = %d AND MONTH(income_create_date) = %d AND DAY(income_create_date) = %d AND invoice_type = %s", $year_val, $select_month, $value, 'income' );
			$q1 = $wpdb->prepare( "SELECT * FROM $table_name WHERE YEAR(income_create_date) = %d AND MONTH(income_create_date) = %d AND DAY(income_create_date) = %d AND invoice_type = %s", $year_val, $select_month, $value, 'expense' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $q );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result1               = $wpdb->get_results( $q1 );
			$expense_yearly_amount = 0;
			foreach ( $result1 as $expense_entry ) {
				$all_entry = json_decode( $expense_entry->entry );
				$amount    = 0;
				foreach ( $all_entry as $entry ) {
					$amount += $entry->amount;
				}
				$expense_yearly_amount += $amount;
			}
			$expense_amount       = $expense_yearly_amount;
			$income_yearly_amount = 0;
			foreach ( $result as $income_entry ) {
				$all_entry = json_decode( $income_entry->entry );
				$amount    = 0;
				foreach ( $all_entry as $entry ) {
					$amount += $entry->amount;
				}
				$income_yearly_amount += $amount;
			}
			$income_amount    = $income_yearly_amount;
			$expense_array[]  = $expense_amount;
			$income_array[]   = $income_amount;
			$net_profit_array = $income_amount - $expense_amount;
			array_push( $dataPoints_2, array( $value, $income_amount, $expense_amount, $net_profit_array ) );
		}
	}
	$income_filtered  = array_filter( $income_array );
	$expense_filtered = array_filter( $expense_array );
	$new_array        = $dataPoints_2;
	if ( ! empty( $income_filtered ) || ! empty( $expense_filtered ) ) :
		$labels       = array();
		$income_data  = array();
		$expense_data = array();
		$profit_data  = array();
		foreach ( $new_array as $index => $row ) {
			if ( $index === 0 ) {
				continue; // Skip header row.
			}
			$labels[]       = $row[0];
			$income_data[]  = $row[1];
			$expense_data[] = $row[2];
			$profit_data[]  = $row[3];
		}
		$chart_data = array(
			'labels'   => $labels,
			'income'   => $income_data,
			'expense'  => $expense_data,
			'profit'   => $profit_data,
			'currency' => $currency_symbol,
		);
		?>
		<canvas id="mjschool-barchart-material" class="mjschool-barchart-material mjschool_chart_430pxmjschool_chart_430px" data-chart='<?php echo esc_attr( wp_json_encode( $chart_data ) ); ?>'></canvas>
	<?php else : ?>
		<div class="mjschool-calendar-event-new">
			<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
		</div>
	<?php endif;
	die();
}
add_action( 'wp_ajax_mjschool_payment_dashboard_report_content', 'mjschool_payment_dashboard_report_content' );
add_action( 'wp_ajax_nopriv_mjschool_payment_dashboard_report_content', 'mjschool_payment_dashboard_report_content' );
/**
 * Loads and outputs payment dashboard report content with a doughnut chart.
 *
 * This function calculates payment amounts by different methods (Cash, Cheque, Bank Transfer, PayPal, Stripe)
 * within a specified date range and displays a doughnut chart along with the total amount.
 *
 * @since 1.0.0
 * @return void Outputs HTML and JavaScript for the chart directly.
 */
function mjschool_payment_dashboard_report_content() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$type = isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';
	?>
		<?php
		$result       = mjschool_all_date_type_value( $type );
		$response     = json_decode( $result );
		$start_date   = $response[0];
		$end_date     = $response[1];
		$cash_payment = mjschool_get_payment_paid_data_by_date_method( 'Cash', $start_date, $end_date );
		if ( ! empty( $cash_payment ) ) {
			$cash_amount = 0;
			foreach ( $cash_payment as $cash ) {
				$cash_amount += $cash->amount;
			}
		} else {
			$cash_amount = 0;
		}
		$cheque_payment = mjschool_get_payment_paid_data_by_date_method( 'Cheque', $start_date, $end_date );
		if ( ! empty( $cheque_payment ) ) {
			$cheque_amount = 0;
			foreach ( $cheque_payment as $cheque ) {
				$cheque_amount += $cheque->amount;
			}
		} else {
			$cheque_amount = 0;
		}
		$bank_payment = mjschool_get_payment_paid_data_by_date_method( 'Bank Transfer', $start_date, $end_date );
		if ( ! empty( $bank_payment ) ) {
			$bank_amount = 0;
			foreach ( $bank_payment as $bank ) {
				$bank_amount += $bank->amount;
			}
		} else {
			$bank_amount = 0;
		}
		$paypal_payment = mjschool_get_payment_paid_data_by_date_method( 'PayPal', $start_date, $end_date );
		if ( ! empty( $paypal_payment ) ) {
			$paypal_amount = 0;
			foreach ( $paypal_payment as $paypal ) {
				$paypal_amount += $paypal->amount;
			}
		} else {
			$paypal_amount = 0;
		}
		$stripe_payment = mjschool_get_payment_paid_data_by_date_method( 'Stripe', $start_date, $end_date );
		if ( ! empty( $stripe_payment ) ) {
			$stripe_amount = 0;
			foreach ( $stripe_payment as $stripe ) {
				$stripe_amount += $stripe->amount;
			}
		} else {
			$stripe_amount = 0;
		}
		?>
	<canvas id="chartJSContainerpayment" width="300" height="250" data-cash="<?php echo esc_attr( floatval( $cash_amount ) ); ?>" data-cheque="<?php echo esc_attr( floatval( $cheque_amount ) ); ?>" data-bank="<?php echo esc_attr( floatval( $bank_amount ) ); ?>" data-paypal="<?php echo esc_attr( floatval( $paypal_amount ) ); ?>" data-stripe="<?php echo esc_attr( floatval( $stripe_amount ) ); ?>" data-symbol="<?php echo esc_attr( html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) ) ); ?>"></canvas>
	<p class="percent">
		<?php
		$Total_amount    = $cash_amount + $cheque_amount + $bank_amount + $paypal_amount + $stripe_amount;
		$currency_symbol = html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) );
		echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Total_amount, 2, '.', '' ) ) );
		?>
	</p>
	<p class="percent_report"> <?php esc_html_e( 'Payment Report', 'mjschool' ); ?> </p>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_attendance_dashboard_report_content', 'mjschool_attendance_dashboard_report_content' );
add_action( 'wp_ajax_nopriv_mjschool_attendance_dashboard_report_content', 'mjschool_attendance_dashboard_report_content' );
/**
 * Loads and outputs attendance dashboard report content with a doughnut chart.
 *
 * This function calculates attendance counts by status (Present, Absent, Late, Half Day)
 * within a specified date range and displays a doughnut chart along with the total attendance.
 *
 * @since 1.0.0
 * @return void Outputs HTML and JavaScript for the chart directly.
 */
function mjschool_attendance_dashboard_report_content() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$type = isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';
	?>
		<?php
		$result     = mjschool_all_date_type_value( $type );
		$response   = json_decode( $result );
		$start_date = $response[0];
		$end_date   = $response[1];
		$present    = mjschool_attendance_data_by_status( $start_date, $end_date, 'Present' );
		$absent     = mjschool_attendance_data_by_status( $start_date, $end_date, 'Absent' );
		$late       = mjschool_attendance_data_by_status( $start_date, $end_date, 'Late' );
		$halfday    = mjschool_attendance_data_by_status( $start_date, $end_date, 'Half Day' );
		?>
	<canvas id="chartJSContainerattendance" width="300" height="250" data-present="<?php echo esc_attr( intval( $present ) ); ?>" data-absent="<?php echo esc_attr( intval( $absent ) ); ?>" data-late="<?php echo esc_attr( intval( $late ) ); ?>" data-halfday="<?php echo esc_attr( intval( $halfday ) ); ?>"></canvas>
	<p class="percent">
		<?php
		$attendance = $present + $absent + $late + $halfday;
		echo esc_html( $attendance );
		?>
	</p>
	<p class="percent_report"> <?php esc_html_e( 'Attendance', 'mjschool' ); ?> </p>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_teacher_by_subject', 'mjschool_load_teacher_by_subject' );
add_action( 'wp_ajax_nopriv_mjschool_load_teacher_by_subject', 'mjschool_load_teacher_by_subject' );
/**
 * Loads and outputs teacher options based on the selected subject.
 *
 * This function retrieves teachers assigned to a specific subject and outputs
 * HTML option tags, considering user roles for access control.
 *
 * @since 1.0.0
 * @return void Outputs HTML option tags directly.
 */
function mjschool_load_teacher_by_subject() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$subject = isset($_POST['subject']) ? intval( wp_unslash($_POST['subject']) ) : 0;
	
	// Get current user role and ID (these were referenced but not defined)
	$current_user_id = get_current_user_id();
	$user = wp_get_current_user();
	$current_role = ! empty( $user->roles ) ? $user->roles[0] : '';
	
	global $wpdb;
	$mjschool_teacher_class = $wpdb->prefix . 'mjschool_teacher_subject';
	// Fetch results from the database.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT teacher_id FROM $mjschool_teacher_class WHERE subject_id = %d", $subject ) );
	if ( ! empty( $result ) ) {
		foreach ( $result as $value ) {
			if ( ! empty( $value->teacher_id ) ) {
				// Check role and conditionally output options.
				if ( $current_role === 'teacher' && (int) $value->teacher_id === (int) $current_user_id ) {
					echo '<option value="' . esc_attr( $value->teacher_id ) . '"> ' . esc_html( mjschool_get_display_name( $value->teacher_id ) ) . '</option>';
				} elseif ( $current_role !== 'teacher' ) {
					echo '<option value="' . esc_attr( $value->teacher_id ) . '"> ' . esc_html( mjschool_get_display_name( $value->teacher_id ) ) . '</option>';
				}
			}
		}
	}
	die();
}
add_action( 'wp_ajax_mjschool_fees_user_list', 'mjschool_fees_user_list' );
add_action( 'wp_ajax_nopriv_mjschool_fees_user_list', 'mjschool_fees_user_list' );
/**
 * Loads and outputs user lists for generating fees invoices.
 *
 * This function retrieves sections and users based on class and section filters,
 * excluding certain users, and returns JSON-encoded HTML for sections and user select options.
 *
 * @since 1.0.0
 * @return void Outputs JSON-encoded data directly.
 */
function mjschool_fees_user_list() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$school_obj         = new MJSchool_Management( get_current_user_id() );
	$login_user_role    = $school_obj->role;
	$class_list         = isset( $_REQUEST['class_list'] ) ? sanitize_text_field( wp_unslash($_REQUEST['class_list']) ) : '';
	$class_section      = isset( $_REQUEST['class_section'] ) ? sanitize_text_field( wp_unslash($_REQUEST['class_section']) ) : '';
	$exlude_id          = mjschool_approve_student_list();
	$html_class_section = '';
	$user_list          = array();
	global $wpdb;
	$defaultmsg         = esc_attr__( 'All Section', 'mjschool' );
	$html_class_section = "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	if ( $class_list != '' && $class_list != 'all_class' ) {
		$retrieve_data = mjschool_get_class_sections( $class_list );
		if ( $retrieve_data ) {
			foreach ( $retrieve_data as $section ) {
				$html_class_section .= "<option value='" . esc_attr( $section->id ) . "'>" . esc_html( $section->section_name ) . '</option>';
			}
		}
	}
	$return_results['section'] = $html_class_section;
	
	$query_data['exclude'] = $exlude_id;
	if ($class_section) {
		$query_data['meta_key'] = 'class_section';
		$query_data['meta_value'] = $class_section;
		$query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' ) );
		$results = get_users($query_data);
	} elseif ($class_list === 'all_class' ) {
		$results = mjschool_get_all_student_list();
	} elseif ($class_list != '' && $class_list != 'all_class' ) {
		$query_data['meta_key'] = 'class_name';
		$query_data['meta_value'] = $class_list;
		$results = get_users($query_data);
	}
	
	if ( isset( $results ) ) {
		foreach ( $results as $user_datavalue ) {
			$user_list[] = $user_datavalue->ID;
		}
	}
	$user_data_list          = array_unique( $user_list );
	$return_results['users'] = '';
	$user_string             = '<select name="selected_users[]" id="selected_users" class="form-control validate[required]" multiple="true">';
	if ( ! empty( $user_data_list ) ) {
		foreach ( $user_data_list as $retrive_data ) {
			if ( $retrive_data != get_current_user_id() ) {
				$check_data = mjschool_get_user_name_by_id( $retrive_data );
				if ( $check_data != '' ) {
					$user_string .= "<option value='" . esc_attr( $retrive_data ) . "'>" . esc_html( mjschool_get_user_name_by_id( $retrive_data ) ) . '</option>';
				}
			}
		}
	}
	$user_string            .= '</select>';
	$return_results['users'] = $user_string;
	echo json_encode( $return_results );
	die();
}
add_action( 'wp_ajax_mjschool_load_siblings_dropdown', 'mjschool_load_siblings_dropdown' );
add_action( 'wp_ajax_nopriv_mjschool_load_siblings_dropdown', 'mjschool_load_siblings_dropdown' );
/**
 * Loads and outputs a siblings dropdown form with dynamic class, section, and student selection.
 *
 * This function generates HTML for a form allowing selection of class, section (if not university),
 * and student for siblings, along with JavaScript to handle dynamic loading of options via AJAX.
 *
 * @since 1.0.0
 * @return void Outputs HTML and JavaScript directly.
 */
function mjschool_load_siblings_dropdown() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	// if ( ! is_user_logged_in() ) {
	// 	wp_die( 'You must be logged in.' );
	// }
	$x = isset($_REQUEST['click_val']) ? intval( wp_unslash($_REQUEST['click_val']) ) : 0;
	$school_type = get_option( 'mjschool_custom_class' );
	?>
	<div class="form-body mjschool-user-form-for-sibling" data-sibling-id="<?php echo esc_attr($x); ?>">
	<div class="form-body mjschool-user-form">
		<div class="row">
			<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select mb-3">
				<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
				<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px mjschool_height_44px" id="sibling_class_change_<?php echo esc_attr( $x ); ?>" >
					<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
					<?php
					foreach ( mjschool_get_all_class() as $classdata ) {
						?>
						<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"> <?php echo esc_html( $classdata['class_name'] ); ?></option>
						<?php
					}
					?>
				</select>
			</div>
			<?php if ( $school_type != 'university' ) {?>
				<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select mb-3">
					<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
					<select name="siblingssection[]" class="form-control mjschool-max-width-100px mjschool_height_44px"  id="sibling_class_section_<?php echo esc_attr( $x ); ?>">
						<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
					</select>
				</div>
			<?php }?>
			<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide mb-3">
				<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list_<?php echo esc_attr( $x ); ?>"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
				<select name="siblingsstudent[]" id="sibling_student_list_<?php echo esc_attr( $x ); ?>" class="form-control mjschool-max-width-100px validate[required] mjschool_height_44px">
					<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
				</select>
			</div>
			<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
				<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-input-btn-height-width">
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_class_rootine_import', 'mjschool_class_rootine_import' );
add_action( 'wp_ajax_nopriv_mjschool_class_rootine_import', 'mjschool_class_rootine_import' );
/**
 * Loads and outputs a form for importing class routine data via CSV.
 * 
 * This function generates a modal form with file upload for CSV import, 
 * including JavaScript for form validation and file type checking.
 * 
 * @since 1.0.0 
 * @return void Outputs HTML and JavaScript directly. 
 */
function mjschool_class_rootine_import() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-import-csv-popup">
		
		<a href="#" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Import Data', 'mjschool' ); ?></h4>
		
	</div>
	<div class="mjschool-panel-body">
		<form name="mjschool-upload-form" action="#" method="post" class="mjschool-form-horizontal" id="import_csv" enctype="multipart/form-data"><!--form div-->
			<input type="hidden" name="class_id" value="<?php echo esc_attr( intval( wp_unslash($_REQUEST['class_id']) ) ); ?>">
			<input type="hidden" name="class_section" value="<?php echo esc_attr( sanitize_text_field( wp_unslash($_REQUEST['class_section']) ) ); ?>">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-9 input mt-0">
						<div class="form-group input mjschool-rtl-margin-top-0px-popup">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Select CSV File', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<div class="col-sm-12">
									<input id="csv_file_class" type="file" class="form-control file validate[required] csvfile_width d-inline mjschool-file-validation" name="csv_file">
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 mjschool-margin-bottom-15px">
						<input type="submit" value="<?php esc_attr_e( 'Import CSV', 'mjschool' ); ?>" name="save_import_csv" class="btn mjschool-rtl-margin-0px mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_child_dropdown', 'mjschool_load_child_dropdown' );
add_action( 'wp_ajax_nopriv_mjschool_load_child_dropdown', 'mjschool_load_child_dropdown' );
/**
 * Loads and outputs a child selection dropdown for parents.
 * 
 * This function retrieves students grouped by class and generates a select dropdown
 * with optgroups for each class, allowing parents to select their children.
 * 
 * @since 1.0.0
 * @return void Outputs HTML directly. 
 * 
 */
function mjschool_load_child_dropdown() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$students = mjschool_get_student_group_by_class();
	?>
	<div class="form-body mjschool-user-form">
		<div id="mjschool-parents-child" class="row mjschool-parents-child">
			<div class="col-md-6 input mjschool-width-80px-res">
				<span class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Child', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
				<select name="chield_list[]" id="student_list" class="form-control validate[required] mjschool-max-width-100px mjschool_heights_47px">
					<option value=""><?php esc_html_e( 'Select Child', 'mjschool' ); ?></option>
					<?php foreach ( $students as $label => $opt ) { ?>
						<optgroup label="<?php echo 'Class : ' . esc_attr( $label ); ?>">
							<?php foreach ( $opt as $id => $name ) : ?>
								<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php } ?>
				</select>
			</div>
			<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
				<input type="image" onclick="mjschool_delete_parent_elementChild(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-input-btn-height-width">
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_nopriv_mjschool_import_student_attendance', 'mjschool_import_student_attendance' );
add_action( 'wp_ajax_mjschool_import_student_attendance', 'mjschool_import_student_attendance' );
/** 
 * Loads and outputs a form for importing student attendance data via CSV.
 * 
 * This function generates a modal form with file upload for CSV import of attendance data, 
 * including JavaScript for form validation and file type checking. 
 * 
 * @since 1.0.0 
 * @return void Outputs HTML and JavaScript directly. 
 * 
 */
function mjschool_import_student_attendance() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-import-csv-popup">
		
		<a href="#" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Import Attendance Data', 'mjschool' ); ?></h4>
		
	</div>
	<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
		<!-------- Import Teacher Form. ---------->
		<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data"><!--form div-->
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl" for="city_name"><?php esc_html_e( 'Select CSV file', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<div class="col-sm-12">
									<input id="csv_file" type="file" class="col-md-12 form-control file validate[required] mjschool-file-validation csvfile_width" name="csv_file">
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<input type="submit" value="<?php esc_attr_e( 'Upload CSV File', 'mjschool' ); ?>" name="upload_attendance_csv_file" class="col-sm-6 width-auto mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form><!--Form div.-->
	</div><!--Mjschool-panel-body.-->
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_class_section_document', 'mjschool_load_class_section_document' );
add_action( 'wp_ajax_nopriv_mjschool_load_class_section_document', 'mjschool_load_class_section_document' );
/**
 * Loads and outputs section options based on the selected class for documents.
 * 
 * This function retrieves class sections and outputs HTML option tags for selection.
 * 
 * @since 1.0.0
 * @return void Outputs HTML option tags directly. 
 * 
 */ 
function mjschool_load_class_section_document() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id   = isset($_POST['class_id']) ? sanitize_text_field(wp_unslash($_POST['class_id'])) : '';
	$defaultmsg = esc_attr__( 'All Section', 'mjschool' );
	if ( $class_id === 'all class' ) {
		echo "<option value='all section'>" . esc_html( $defaultmsg ) . '</option>';
	} else {
		echo "<option value='all section'>" . esc_html( $defaultmsg ) . '</option>';
		$retrieve_data = mjschool_get_class_sections( sanitize_text_field(wp_unslash($_POST['class_id'])) );
		foreach ( $retrieve_data as $section ) {
			echo "<option value='" . esc_attr( $section->id ) . "'>" . esc_html( $section->section_name ) . '</option>';
		}
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_class_wise_student_document', 'mjschool_load_class_wise_student_document' );
add_action( 'wp_ajax_nopriv_mjschool_load_class_wise_student_document', 'mjschool_load_class_wise_student_document' );
/**
 * 
 * Loads and outputs student options based on the selected class for documents.
 * 
 * This function retrieves students for a given class and outputs HTML option tags,
 * excluding certain users and including an "All Student" option.
 * 
 * @since 1.0.0
 * 
 * @return void Outputs HTML option tags directly.
 * 
 */
function mjschool_load_class_wise_student_document() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$exlude_id  = mjschool_approve_student_list();
	$class_id   = isset($_POST['class_id']) ? sanitize_text_field( wp_unslash($_POST['class_id']) ) : '';
	$defaultmsg = esc_attr__( 'All Student', 'mjschool' );
	if ( $class_id === 'all class' ) {
		echo "<option value='all section'>" . esc_html( $defaultmsg ) . '</option>';
	} else {
		global $wpdb;
		echo "<option value='all student'>" . esc_html( $defaultmsg ) . '</option>';
		
		$retrieve_data = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
		
		foreach ( $retrieve_data as $users ) {
			echo '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( mjschool_student_display_name_with_roll( $users->ID ) ) . '</option>';
		}
		die();
	}
}
add_action( 'wp_ajax_mjschool_load_section_user_list', 'mjschool_load_section_user_list' );
add_action( 'wp_ajax_nopriv_mjschool_load_section_user_list', 'mjschool_load_section_user_list' );
/**
 * Loads and outputs student options based on the selected section.
 * 
 * This function retrieves students for a given section and class, outputting HTML option tags,
 * with handling for "all section" and empty section cases.
 * 
 * @since 1.0.0
 * @return void Outputs HTML option tags directly. 
 */
function mjschool_load_section_user_list() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$section_id = isset($_POST['section_id']) ? sanitize_text_field( wp_unslash($_POST['section_id']) ) : '';
	$class_id   = isset($_POST['class_id']) ? sanitize_text_field( wp_unslash($_POST['class_id']) ) : '';
	$defaultmsg = esc_attr__( 'All Student', 'mjschool' );
	if ( $section_id === 'all section' ) {
		echo "<option value='all student'>" . esc_html( $defaultmsg ) . '</option>';
		global $wpdb;
		$exlude_id = mjschool_approve_student_list();
		
		$retrieve_data = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
		
		foreach ( $retrieve_data as $users ) {
			echo '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( mjschool_student_display_name_with_roll( $users->ID ) ) . '</option>';
		}
		die();
	} elseif ( empty( $section_id ) ) {
		global $wpdb;
		$exlude_id = mjschool_approve_student_list();
		
		$retrieve_data = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
		
		echo "<option value='all student'>" . esc_html( $defaultmsg ) . '</option>';
		foreach ( $retrieve_data as $users ) {
			echo '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( mjschool_student_display_name_with_roll( $users->ID ) ) . '</option>';
		}
		die();
	} else {
		global $wpdb;
		$exlude_id = mjschool_approve_student_list();
		
		$retrieve_data = get_users(array( 'meta_key' => 'class_section', 'meta_value' => $section_id, 'role' => 'student', 'exclude' => $exlude_id ) );
		
		echo "<option value='all student'>" . esc_html( $defaultmsg ) . '</option>';
		foreach ( $retrieve_data as $users ) {
			echo '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( mjschool_student_display_name_with_roll( $users->ID ) ) . '</option>';
		}
		die();
	}
	die();
}
add_action( 'wp_ajax_nopriv_mjschool_qr_code_take_attendance', 'mjschool_qr_code_take_attendance' );
add_action( 'wp_ajax_mjschool_qr_code_take_attendance', 'mjschool_qr_code_take_attendance' );
/**
 * Processes QR code-based attendance for students.
 *
 * This function decodes the QR code data, validates the user, and inserts attendance
 * into the database using the attendance management class.
 *
 * @since 1.0.0
 * @return void Outputs a result code (1 for success, 3 for invalid user) and exits.
 */
function mjschool_qr_code_take_attendance() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$attendance_url         = isset( $_REQUEST['attendance_url'] ) ? esc_url_raw( wp_unslash( $_REQUEST['attendance_url'] ) ) : '';
	$obj_attend             = new Mjschool_Attendence_Manage();
	$qrcode_attendance      = explode( '_', $attendance_url );
	$user_id                = isset($qrcode_attendance[0]) ? intval($qrcode_attendance[0]) : 0;
	$user_class_id          = isset($qrcode_attendance[1]) ? intval($qrcode_attendance[1]) : 0;
	$curr_date              = isset($qrcode_attendance[2]) ? sanitize_text_field($qrcode_attendance[2]) : '';
	$user_section_id        = isset($qrcode_attendance[3]) ? intval($qrcode_attendance[3]) : 0;
	$selected_class_id      = isset($qrcode_attendance[4]) ? intval($qrcode_attendance[4]) : 0;
	$selected_class_subject = isset($qrcode_attendance[5]) ? intval($qrcode_attendance[5]) : 0;
	$selected_class_section = isset($qrcode_attendance[6]) ? intval($qrcode_attendance[6]) : 0;
	$userdata               = get_userdata( $user_id );
	$status                 = 'Present';
	$attend_by              = get_current_user_id();
	$attendence_type        = 'QR';
	$comment                = '';
	if ( ! empty( $userdata ) ) {
		$savedata = $obj_attend->mjschool_insert_subject_wise_attendance( $curr_date, $user_class_id, $user_id, $attend_by, $status, $selected_class_subject, $comment, $attendence_type, $selected_class_section );
		$result   = '1';
	} else {
		$result = '3';
	}
	echo esc_html( $result );
	die();
}
add_action( 'wp_ajax_mjschool_teacher_attendance_graph_report_data', 'mjschool_teacher_attendance_graph_report_data' );
add_action( 'wp_ajax_nopriv_mjschool_teacher_attendance_graph_report_data', 'mjschool_teacher_attendance_graph_report_data' );
/**
 * Generates a graph report for teacher attendance based on filter criteria.
 *
 * This function queries attendance data for teachers within a specified date range,
 * prepares chart data, and outputs a Google Chart or a no-data image.
 *
 * @since 1.0.0
 * @return void Outputs HTML and JavaScript for the chart or no-data message.
 */
function mjschool_teacher_attendance_graph_report_data() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$filter_val = isset($_REQUEST['filter_val']) ? sanitize_text_field( wp_unslash($_REQUEST['filter_val']) ) : '';
	global $wpdb;
	$table_attendance = $wpdb->prefix . 'mjschool_attendence';
	if ( $filter_val === 'today' ) {
		$start_date = date( 'Y-m-d' );
		$end_date   = date( 'Y-m-d' );
		$value      = 'Today';
	} elseif ( $filter_val === 'this_week' ) {
		// Check the current day.
		if ( date( 'D' ) != 'Mon' ) {
			// Take the last monday.
			$start_date = date( 'Y-m-d', strtotime( 'last sunday' ) );
		} else {
			$start_date = date( 'Y-m-d' );
		}
		// Always next saturday.
		if ( date( 'D' ) != 'Sat' ) {
			$end_date = date( 'Y-m-d', strtotime( 'next saturday' ) );
		} else {
			$end_date = date( 'Y-m-d' );
		}
		$value = 'This Week';
	} elseif ( $filter_val === 'last_week' ) {
		$previous_week = strtotime( '-1 week +1 day' );
		$start_week    = strtotime( 'last sunday midnight', $previous_week );
		$end_week      = strtotime( 'next saturday', $start_week );
		$start_date    = date( 'Y-m-d', $start_week );
		$end_date      = date( 'Y-m-d', $end_week );
		$value         = 'Last Week';
	} elseif ( $filter_val === 'this_month' ) {
		$start_date = date( 'Y-m-d', strtotime( 'first day of this month' ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$value      = 'This Month';
	} elseif ( $filter_val === 'last_month' ) {
		$start_date = date( 'Y-m-d', strtotime( 'first day of previous month' ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of previous month' ) );
		$value      = 'Last Month';
	} elseif ( $filter_val === 'last_3_month' ) {
		$month_date = date( 'Y-m-d', strtotime( '-2 month' ) );
		$start_date = date( 'Y-m-01', strtotime( $month_date ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$value      = 'Last 3 Month';
	} elseif ( $filter_val === 'last_6_month' ) {
		$month_date = date( 'Y-m-d', strtotime( '-5 month' ) );
		$start_date = date( 'Y-m-01', strtotime( $month_date ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$value      = 'Last 6 Month';
	} elseif ( $filter_val === 'last_12_month' ) {
		$month_date = date( 'Y-m-d', strtotime( '-11 month' ) );
		$start_date = date( 'Y-m-01', strtotime( $month_date ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$value      = 'Last 12 Month';
	} elseif ( $filter_val === 'this_year' ) {
		$start_date = date( 'Y-01-01', strtotime( '0 year' ) );
		$end_date   = date( 'Y-12-t', strtotime( $start_date ) );
		$value      = 'This Year';
	} elseif ( $filter_val === 'last_year' ) {
		$start_date = date( 'Y-01-01', strtotime( '-1 year' ) );
		$end_date   = date( 'Y-12-t', strtotime( $start_date ) );
		$value      = 'Last Year';
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$report_2      = $wpdb->get_results( $wpdb->prepare( "SELECT  at.user_id, SUM(case when `status` ='Present' then 1 else 0 end) as Present, SUM(case when `status` ='Absent' then 1 else 0 end) as Absent from $table_attendance as at where `attendence_date` BETWEEN %s AND %s AND at.user_id AND at.role_name = 'teacher' GROUP BY at.user_id", $start_date, $end_date ) );
	$chart_array   = array();
	$chart_array[] = array( esc_html__( 'teacher', 'mjschool' ), esc_html__( 'Present', 'mjschool' ), esc_html__( 'Absent', 'mjschool' ) );
	if ( ! empty( $report_2 ) ) {
		foreach ( $report_2 as $result ) {
			$class_id      = mjschool_get_user_name_by_id( $result->user_id );
			$chart_array[] = array( "$class_id", (int) $result->Present, (int) $result->Absent );
		}
	}
	$options = array(
		'title'          => esc_html( $value . ' ' . 'Attendance Report' ),
		'titleTextStyle' => array(
			'color'    => '#4e5e6a',
			'fontSize' => 16,
			'bold'     => false,
			'italic'   => false,
			'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
		),
		'legend'         => array(
			'position'  => 'right',
			'textStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 13,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
		),
		'hAxis'          => array(
			'title'          => esc_html__( 'Teacher', 'mjschool' ),
			'titleTextStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 16,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'textStyle'      => array(
				'color'    => '#4e5e6a',
				'fontSize' => 13,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'maxAlternation' => 2,
		),
		'vAxis'          => array(
			'title'          => esc_html__( 'No of Days', 'mjschool' ),
			'minValue'       => 0,
			'maxValue'       => 4,
			'format'         => '#',
			'titleTextStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 16,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'textStyle'      => array(
				'color'    => '#4e5e6a',
				'fontSize' => 13,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
		),
		'colors'         => array( '#5840bb', '#f25656' ),
	);
	
	$google_charts = new GoogleCharts();
	if ( ! empty( $report_2 ) ) {
		$chart = $google_charts->load( 'column', 'mjschool-chart-div-last-month' )->get( $chart_array, $options );
	} else {
		 ?>
		<div class="mjschool-calendar-event-new">
			<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG);?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
		</div>
		<?php 
	}
	if ( isset( $report_2 ) && count( $report_2 ) > 0 ) {
		?>
		<div id="mjschool-chart-div-last-month" class="w-100 h-500-px"></div>
		<!-- Javascript. -->
		<script type="text/javascript">
			"use strict";
			<?php echo wp_kses_post( $chart ); ?> 
		</script>
		<?php
	}
	die();
}
add_action( 'wp_ajax_mjschool_student_attendance_graph_report_data', 'mjschool_student_attendance_graph_report_data' );
add_action( 'wp_ajax_nopriv_mjschool_student_attendance_graph_report_data', 'mjschool_student_attendance_graph_report_data' );
/** 
 * Generates a graph report for student attendance based on filter criteria.
 * 
 * This function queries student attendance data within a specified date range,
 * prepares chart data based on user role (teacher or admin), and outputs a Google Chart * or a no-data image.
 * 
 * @since 1.0.0
 * @return void Outputs HTML and JavaScript for the chart or no-data message.
 * 
 */
function mjschool_student_attendance_graph_report_data() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$filter_val = isset($_REQUEST['filter_val']) ? sanitize_text_field( wp_unslash($_REQUEST['filter_val']) ) : '';
	global $wpdb;
	$table_attendance = $wpdb->prefix . 'mjschool_sub_attendance';
	$table_class      = $wpdb->prefix . 'mjschool_class';
	if ( $filter_val === 'today' ) {
		$start_date = date( 'Y-m-d' );
		$end_date   = date( 'Y-m-d' );
		$value      = 'Today';
	} elseif ( $filter_val === 'this_week' ) {
		// Check the current day.
		if ( date( 'D' ) != 'Mon' ) {
			// Take the last monday.
			$start_date = date( 'Y-m-d', strtotime( 'last sunday' ) );
		} else {
			$start_date = date( 'Y-m-d' );
		}
		// Always next saturday.
		if ( date( 'D' ) != 'Sat' ) {
			$end_date = date( 'Y-m-d', strtotime( 'next saturday' ) );
		} else {
			$end_date = date( 'Y-m-d' );
		}
		$value = 'This Week';
	} elseif ( $filter_val === 'last_week' ) {
		$previous_week = strtotime( '-1 week +1 day' );
		$start_week    = strtotime( 'last sunday midnight', $previous_week );
		$end_week      = strtotime( 'next saturday', $start_week );
		$start_date    = date( 'Y-m-d', $start_week );
		$end_date      = date( 'Y-m-d', $end_week );
		$value         = 'Last Week';
	} elseif ( $filter_val === 'this_month' ) {
		$start_date = date( 'Y-m-d', strtotime( 'first day of this month' ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$value      = 'This Month';
	} elseif ( $filter_val === 'last_month' ) {
		$start_date = date( 'Y-m-d', strtotime( 'first day of previous month' ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of previous month' ) );
		$value      = 'Last Month';
	} elseif ( $filter_val === 'last_3_month' ) {
		$month_date = date( 'Y-m-d', strtotime( '-2 month' ) );
		$start_date = date( 'Y-m-01', strtotime( $month_date ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$value      = 'Last 3 Month';
	} elseif ( $filter_val === 'last_6_month' ) {
		$month_date = date( 'Y-m-d', strtotime( '-5 month' ) );
		$start_date = date( 'Y-m-01', strtotime( $month_date ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$value      = 'Last 6 Month';
	} elseif ( $filter_val === 'last_12_month' ) {
		$month_date = date( 'Y-m-d', strtotime( '-11 month' ) );
		$start_date = date( 'Y-m-01', strtotime( $month_date ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$value      = 'Last 12 Month';
	} elseif ( $filter_val === 'this_year' ) {
		$start_date = date( 'Y-01-01', strtotime( '0 year' ) );
		$end_date   = date( 'Y-12-t', strtotime( $start_date ) );
		$value      = 'This Year';
	} elseif ( $filter_val === 'last_year' ) {
		$start_date = date( 'Y-01-01', strtotime( '-1 year' ) );
		$end_date   = date( 'Y-12-t', strtotime( $start_date ) );
		$value      = 'Last Year';
	}
	$school_obj = new MJSchool_Management( get_current_user_id() );
	if ( $school_obj->role === 'teacher' ) {
		$teacher_id   = get_current_user_id();
		$classes      = mjschool_get_class_by_teacher_id( $teacher_id );
		$unique_array = array();
		if ( ! empty( $classes ) ) {
			foreach ( $classes as $class ) {
				$class_id = intval( $class->class_id );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result       = $wpdb->get_results( $wpdb->prepare( "SELECT at.class_id, SUM(CASE WHEN `status` ='Present' THEN 1 ELSE 0 END) AS Present, SUM(CASE WHEN `status` ='Absent' THEN 1 ELSE 0 END) AS Absent FROM $table_attendance AS at JOIN $table_class AS cl ON at.class_id = cl.class_id WHERE `attendance_date` BETWEEN %s AND %s AND at.class_id = %d AND at.role_name = 'student' GROUP BY at.class_id", $start_date, $end_date, $class_id ) );
				$unique_array = array_merge( $unique_array, $result );
			}
		}
		$report_2 = array_unique( $unique_array, SORT_REGULAR );
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_2 = $wpdb->get_results( $wpdb->prepare( "SELECT  at.class_id, SUM(case when `status` ='Present' then 1 else 0 end) as Present, SUM(case when `status` ='Absent' then 1 else 0 end) as Absent from $table_attendance as at,$table_class as cl where `attendance_date` BETWEEN %s AND %s AND at.class_id = cl.class_id AND at.role_name = 'student' GROUP BY at.class_id", $start_date, $end_date ) );
	}
	$chart_array   = array();
	$chart_array[] = array( esc_html__( 'Class', 'mjschool' ), esc_html__( 'Present', 'mjschool' ), esc_html__( 'Absent', 'mjschool' ) );
	if ( ! empty( $report_2 ) ) {
		foreach ( $report_2 as $result ) {
			$class_id      = mjschool_get_class_name( $result->class_id );
			$chart_array[] = array( "$class_id", (int) $result->Present, (int) $result->Absent );
		}
	}
	$options = array(
		'title'          => esc_html( $value . ' ' . 'Attendance Report' ),
		'titleTextStyle' => array(
			'color'    => '#4e5e6a',
			'fontSize' => 16,
			'bold'     => false,
			'italic'   => false,
			'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
		),
		'legend'         => array(
			'position'  => 'right',
			'textStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 13,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
		),
		'hAxis'          => array(
			'title'          => esc_html__( 'Class', 'mjschool' ),
			'titleTextStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 16,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'textStyle'      => array(
				'color'    => '#4e5e6a',
				'fontSize' => 13,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'maxAlternation' => 2,
		),
		'vAxis'          => array(
			'title'          => esc_html__( 'No. of Students', 'mjschool' ),
			'minValue'       => 0,
			'maxValue'       => 4,
			'format'         => '#',
			'titleTextStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 16,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'textStyle'      => array(
				'color'    => '#4e5e6a',
				'fontSize' => 13,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
		),
		'colors'         => array( '#5840bb', '#f25656' ),
	);
	
	$google_charts = new GoogleCharts();
	if ( ! empty( $report_2 ) ) {
		$chart = $google_charts->load( 'column', 'mjschool-chart-div-last-month' )->get( $chart_array, $options );
	} else {
		 ?>
		<div class="mjschool-calendar-event-new">
			<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
		</div>
		<?php 
	}
	if ( isset( $report_2 ) && count( $report_2 ) > 0 ) {
		?>
		<div id="mjschool-chart-div-last-month" class="w-100 h-500-px"></div>
		<!-- Javascript. -->
		<script type="text/javascript">
			"use strict";
			<?php echo wp_kses_post( $chart ); ?>
		</script>
		<?php
	}
	die();
}
add_action( 'wp_ajax_mjschool_check_username_exit_or_not', 'mjschool_check_username_exit_or_not' );
add_action( 'wp_ajax_nopriv_mjschool_check_username_exit_or_not', 'mjschool_check_username_exit_or_not' );
/**
 * Handles AJAX request to check whether a username already exists.
 *
 * This function validates the request nonce, ensures the user is logged in,
 * checks the provided username in the database, and returns a response
 * indicating whether the username is already taken.
 *
 * @since 1.0.0
 * @return void Outputs `1` if username exists, otherwise `0`.
 */
function mjschool_check_username_exit_or_not() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$username = isset($_POST['username']) ? sanitize_text_field(wp_unslash($_POST['username'])) : '';
	if ( username_exists( $username ) ) {
		$response = 1;
	} else {
		$response = 0;
	}
	echo esc_html( $response );
	die();
}
add_action( 'wp_ajax_mjschool_check_roll_exit_or_not', 'mjschool_check_roll_exit_or_not' );
add_action( 'wp_ajax_nopriv_mjschool_check_roll_exit_or_not', 'mjschool_check_roll_exit_or_not' );
/**
 * Handles AJAX request to check whether a roll number already exists.
 *
 * This function validates security nonce, ensures the user is authenticated,
 * searches users with the given roll number (stored in usermeta), and returns
 * the result to the browser.
 *
 * @since 1.0.0
 * @return void Outputs `1` if roll number exists, otherwise `0`.
 */
function mjschool_check_roll_exit_or_not() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$roll = isset($_POST['roll']) ? sanitize_text_field( wp_unslash($_POST['roll']) ) : '';
	
	$user = get_users(array(
		'meta_key' => 'roll_id',
		'meta_value' => $roll
	 ) );
	
	if ( $user ) {
		$response = 1;
	} else {
		$response = 0;
	}
	echo esc_html( $response );
	die();
}
add_action( 'wp_ajax_mjschool_check_email_exit_or_not', 'mjschool_check_email_exit_or_not' );
add_action( 'wp_ajax_nopriv_mjschool_check_email_exit_or_not', 'mjschool_check_email_exit_or_not' );
/**
 * Handles AJAX request to check whether an email address already exists.
 *
 * This function verifies the AJAX nonce, checks user authentication,
 * validates the email, and determines whether the email is already registered
 * in the WordPress user table.
 *
 * @since 1.0.0
 * @return void Outputs `1` if email exists, otherwise `0`.
 */
function mjschool_check_email_exit_or_not() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$email = isset($_POST['email_id']) ? sanitize_email(wp_unslash($_POST['email_id'])) : '';
	if ( email_exists( $email ) ) {
		$response = 1;
	} else {
		$response = 0;
	}
	echo esc_html( $response );
	die();
}
add_action( 'wp_ajax_mjschool_load_exam', 'mjschool_load_exam' );
/**
 * Loads exam list based on the selected class.
 *
 * This function validates the AJAX nonce and authentication status,
 * retrieves exam data from the database for the given class ID,
 * and returns a list of `<option>` tags used in dropdown menus.
 *
 * @since 1.0.0
 * @return void Outputs HTML `<option>` elements.
 */
function mjschool_load_exam() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id = sanitize_text_field( wp_unslash($_POST['class_id']) );
	global $wpdb;
	$table_name_exam = $wpdb->prefix . 'mjschool_exam';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_exam = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name_exam WHERE class_id = %d", $class_id ) );
	$defaultmsg    = esc_attr__( 'Select Exam', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	foreach ( $retrieve_exam as $retrieved_data ) {
		echo '<option value=' . esc_attr( $retrieved_data->exam_id ) . '> ' . esc_html( $retrieved_data->exam_name ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_exam_by_section', 'mjschool_load_exam_by_section' );
/**
 * Loads exam list based on selected class and section.
 *
 * This function verifies nonce and user login, checks class and optionally
 * section filters, queries the exam table accordingly, and returns an updated
 * dropdown option list for the frontend.
 *
 * @since 1.0.0
 * @return void Outputs HTML `<option>` elements.
 */
function mjschool_load_exam_by_section() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id   = sanitize_text_field( wp_unslash($_POST['class_id']) );
	$section_id = sanitize_text_field( wp_unslash($_POST['section_id']) );
	global $wpdb;
	$table_name_exam = $wpdb->prefix . 'mjschool_exam';
	if ( ! empty( $section_id ) ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_exam = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name_exam WHERE class_id = %d AND section_id = %d", $class_id, $section_id ) );
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_exam = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name_exam WHERE class_id = %d", $class_id ) );
	}
	$defaultmsg = esc_attr__( 'Select Exam', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	if ( ! empty( $retrieve_exam ) ) {
		foreach ( $retrieve_exam as $retrieved_data ) {
			echo '<option value=' . esc_attr( $retrieved_data->exam_id ) . '> ' . esc_html( $retrieved_data->exam_name ) . '</option>';
		}
	}
	die();
}
add_action( 'wp_ajax_mjschool_ajax_teacher_comment', 'mjschool_ajax_teacher_comment' );
/**
 * Displays teacher comment popup for a specific exam.
 *
 * This function validates security nonce, ensures user is logged in,
 * retrieves student/class/exam parameters, and renders a modal popup
 * allowing teachers to enter comments and select a signature for the result.
 *
 * Outputs HTML directly (used in AJAX response).
 *
 * @since 1.0.0
 * @return void Prints HTML for the teacher comment popup.
 */
function mjschool_ajax_teacher_comment() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$selected_teacher = '';
	$uid              = isset($_REQUEST['student_id']) ? sanitize_text_field(wp_unslash($_REQUEST['student_id'])) : '';
	$class_id         = isset($_REQUEST['class_id']) ? sanitize_text_field(wp_unslash($_REQUEST['class_id'])) : '';
	$section_id       = isset($_REQUEST['section_id']) ? sanitize_text_field(wp_unslash($_REQUEST['section_id'])) : '';
	$exam_id          = isset($_REQUEST['exam_id']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) : '';
	$type             = isset($_REQUEST['type']) ? sanitize_text_field(wp_unslash($_REQUEST['type'])) : '';
	?>
	<input type="hidden" id="popup_student_id" value="<?php echo esc_attr( $uid ); ?>">
	<input type="hidden" id="popup_class_id" value="<?php echo esc_attr( $class_id ); ?>">
	<input type="hidden" id="popup_section_id" value="<?php echo esc_attr( $section_id ); ?>">
	<input type="hidden" id="popup_exam_id" value="<?php echo esc_attr( $exam_id ); ?>">
	<div class="row">
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php echo esc_html( mjschool_get_user_name_by_id( mjschool_decrypt_id( $uid ) ) ); ?>'s <?php esc_html_e( 'Result', 'mjschool' ); ?></h4>
		</div>
	</div>
	<div class="modal-body">
		<div class="row">
			<div class="col-md-6 mjschool-note-text-notice">
				<div class="form-group input">
					<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
						<div class="form-field">
							<textarea id="teacherComment" name="exam_comment" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150"></textarea>
							<span class="mjschool-txt-title-label"></span>
							<label class="text-area address active"><?php esc_html_e( 'Teacher Comment', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 input mjschool-single-select">
				<label class="ml-1 mjschool-custom-top-label top" for="student_id"><?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?></label>
				<select name="teacher_id" id="teacher_id" class="form-control mjschool-max-width-100px validate[required]">
					<option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></option>
					<?php mjschool_get_teacher_list_selected( $selected_teacher ); ?>
				</select>
			</div>
			<?php if ( $type === 'print' ) { ?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
					<input type="submit" value="<?php esc_attr_e( 'Print', 'mjschool' ); ?>" name="print-result" class="mjschool-save-btn print-result" />
				</div>
			<?php } else { ?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
					<input type="submit" value="<?php esc_attr_e( 'Pdf', 'mjschool' ); ?>" name="print-result-pdf" class="mjschool-save-btn print-result-pdf" />
				</div>
			<?php } ?>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_ajax_teacher_comment_merge', 'mjschool_ajax_teacher_comment_merge' );
/**
 * Displays teacher comment popup for merged exam results.
 *
 * Similar to the single exam popup, this function validates the request,
 * loads student, class, section, and merged exam data, and generates the
 * modal interface for submitting teacher comments and selecting signatures.
 *
 * @since 1.0.0
 * @return void Prints HTML for merged exam comment popup.
 */
function mjschool_ajax_teacher_comment_merge() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$selected_teacher = '';
	$uid              = sanitize_text_field(wp_unslash( $_REQUEST['student_id']) );
	$class_id         = sanitize_text_field(wp_unslash( $_REQUEST['class_id']) );
	$section_id       = sanitize_text_field(wp_unslash( $_REQUEST['section_id']) );
	$merge_id         = sanitize_text_field(wp_unslash( $_REQUEST['merge_id']) );
	$type             = sanitize_text_field(wp_unslash($_REQUEST['type']));
	?>
	<input type="hidden" id="popup_student_id" value="<?php echo esc_attr( $uid ); ?>">
	<input type="hidden" id="popup_class_id" value="<?php echo esc_attr( $class_id ); ?>">
	<input type="hidden" id="popup_section_id" value="<?php echo esc_attr( $section_id ); ?>">
	<input type="hidden" id="popup_exam_id" value="<?php echo esc_attr( $merge_id ); ?>">
	<div class="row">
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php echo esc_html( mjschool_get_user_name_by_id( mjschool_decrypt_id( $uid ) ) ); ?>'s <?php esc_html_e( 'Result', 'mjschool' ); ?></h4>
		</div>
	</div>
	<div class="modal-body">
		<div class="row">
			<div class="col-md-6 mjschool-note-text-notice">
				<div class="form-group input">
					<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
						<div class="form-field">
							<textarea id="teacherComment" name="exam_comment" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150"></textarea>
							<span class="mjschool-txt-title-label"></span>
							<label class="text-area address active"><?php esc_html_e( 'Teacher Comment', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 input mjschool-single-select">
				<label class="ml-1 mjschool-custom-top-label top" for="student_id"><?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?></label>
				<select name="teacher_id" id="teacher_id" class="form-control mjschool-max-width-100px validate[required]">
					<option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></option>
					<?php mjschool_get_teacher_list_selected( $selected_teacher ); ?>
				</select>
			</div>
			<?php if ( $type === 'print' ) { ?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
					<input type="submit" value="<?php esc_attr_e( 'Print', 'mjschool' ); ?>" name="print-result-marge" class="mjschool-save-btn print-result-marge" />
				</div>
			<?php } else { ?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
					<input type="submit" value="<?php esc_attr_e( 'Pdf', 'mjschool' ); ?>" name="print-result-marge-pdf" class="mjschool-save-btn print-result-marge-pdf" />
				</div>
			<?php } ?>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_ajax_result', 'mjschool_ajax_result' );
/**
 * Handles AJAX requests to fetch and display a student's exam results.
 *
 * This function validates the AJAX nonce, checks user authentication, retrieves
 * student details, exam lists, subjects, marks, grades, and prepares the HTML
 * output for the result popup including tables, percentages, GPA, and PDF/print options.
 *
 * It supports both school and university modes, handles exams with or without
 * contributions (sub-marks), and calculates totals, GPA, grade points, and pass/fail status.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML directly for the results modal via an AJAX response.
 */
function mjschool_ajax_result() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
    if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
        wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
    }

    // 2. CHECK IF USER IS LOGGED IN.
    if ( ! is_user_logged_in() ) {
        wp_die( 'You must be logged in.' );
    }
	$school_type   = get_option( 'mjschool_custom_class' );
	$roles         = mjschool_get_user_role( get_current_user_id() );
	$obj_mark      = new Mjschool_Marks_Manage();
	$exam_obj      = new Mjschool_exam();
	$uid           = sanitize_text_field( wp_unslash($_REQUEST['student_id']) );
	$user          = get_userdata( $uid );
	$user_meta     = get_user_meta( $uid );
	$class_id      = $user_meta['class_name'][0];
	$section_id    = $user_meta['class_section'][0];
	$subject       = $obj_mark->mjschool_student_subject_list( $class_id, $section_id );
	$total_subject = count( $subject );
	$total         = 0;
	$grade_point   = 0;
	$all_exam      = mjschool_get_all_exam_by_class_id_all( $class_id );
	?>

	<?php if ( $school_type != 'university' ) {?>
		<div class="mjschool-panel-white">
			<div class="modal-header modal_header_height mjschool-model-header-padding mjschool-dashboard-model-header">
				<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
				<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( mjschool_get_user_name_by_id($uid ) ); ?>'s <?php esc_html_e( 'Result', 'mjschool' ); ?></h4>
				
			</div>
			<?php
			if ( ! empty( $all_exam ) ) {
				?>
				<div class="clearfix"></div>
				<div id="mjschool-accordion" class="accordion student_accordion" aria-multiselectable="true" role="tablist">
					<?php
					$i = 0;
					foreach ( $all_exam as $exam ) { 
						/* ALL EXAM LOOP STARTS.  */
						$exam_id = $exam->exam_id;
						// Get class and section for this specific exam.
						$exam_data       = $exam_obj->mjschool_exam_data( $exam_id );
						$exam_class_id   = $exam_data->class_id;
						$exam_section_id = $exam_data->section_id;
						// Now get subject based on this exam's class & section.
						global $wpdb;
						if ( $exam_section_id === 0 ) {
							// All sections, get all subjects of the class.
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mjschool_subject WHERE class_id = %d", $exam_class_id ) );
						} else {
							// Specific section.
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mjschool_subject WHERE class_id = %d AND section_id = %d", $exam_class_id, $exam_section_id ) );
						}
						$total_subject = count( $subject );
						?>
						<div class="mt-2 accordion-item">
							<h4 class="accordion-header" id="heading_<?php echo esc_attr( $i ); ?>">
								<button class="accordion-button mjschool-student-result-collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?php echo esc_attr( $i ); ?>" aria-expanded="true" aria-controls="heading_<?php echo esc_attr( $i ); ?>">
									<div class="col-md-10 col-7">
										<span class="mjschool-student-exam-result"><?php esc_html_e( 'Exam Results : ', 'mjschool' ); ?></span>
										&nbsp;
										<span class="mjschool-student-exam-name"><?php echo esc_html( $exam->exam_name ); ?></span>
									</div>
									<?php
									$new_marks = '';
									foreach ( $subject as $sub ) {
										$marks = $obj_mark->mjschool_get_marks( $exam_id, $exam_class_id, $sub->subid, $uid );
										if ( ! empty( $marks ) ) {
											$new_marks = $marks;
										}
									}
									if ( ! empty( $new_marks ) ) {
										?>
										<div class="col-md-2 row justify-content-end mjschool-view-result">
											
											<div class="col-md-5 mjschool-width-50px">
												<?php
												if ($roles != "parent" && $roles != "student") { ?>
													<a href="#" student_id="<?php echo esc_attr(mjschool_encrypt_id($uid ) ); ?>" class_id="<?php echo esc_attr(mjschool_encrypt_id($class_id ) ); ?>" section_id="<?php echo esc_attr(mjschool_encrypt_id($section_id ) ); ?>" exam_id="<?php echo esc_attr(mjschool_encrypt_id($exam_id ) ); ?>" typeformat="pdf" class="mjschool-float-right show-popup-teacher-details" target="_blank">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-pdf.png"); ?>">
													</a>
												<?php } else { ?>
													<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'mjschool_student', 'print' => 'pdf', 'student' => mjschool_encrypt_id($uid), 'exam_id' => mjschool_encrypt_id($exam_id) ), admin_url() ) ); ?>" class="mjschool-float-right" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-pdf.png"); ?>"></a>
												<?php } ?>
											</div>
											<div class="col-md-4 mjschool-width-50px mjschool-rtl-margin-left-20px mjschool-exam-result-pdf-margin mjschool_margin_right_22px" >
												<?php if ($roles != "parent" && $roles != "student") { ?>
													<a href="#" student_id="<?php echo esc_attr(mjschool_encrypt_id($uid ) ); ?>" class_id="<?php echo esc_attr(mjschool_encrypt_id($class_id ) ); ?>" section_id="<?php echo esc_attr(mjschool_encrypt_id($section_id ) ); ?>" exam_id="<?php echo esc_attr(mjschool_encrypt_id($exam_id ) ); ?>" typeformat="print" class="mjschool-float-right show-popup-teacher-details" target="_blank">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-print.png"); ?>">
													</a>
												<?php } else { ?>
													<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'mjschool_student', 'print' => 'print', 'student' => mjschool_encrypt_id($uid), 'exam_id' => mjschool_encrypt_id($exam_id) ), admin_url() ) ); ?>" class="mjschool-float-right" target="_blank">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-print.png"); ?>">
													</a>
												<?php } ?>
											</div>
											
										</div>
										<?php
									} else {
										?>
										<span class="mjschool-student-exam-name"> <?php esc_html_e( 'No Result', 'mjschool' ); ?> </span>
										<?php
									}
									?>
								</button>
							</h4>
							<div id="collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse" aria-labelledby="heading_<?php echo esc_attr( $i ); ?>" data-bs-parent="#mjschool-accordion">
								<div class="clearfix"></div>
								<div class="clearfix"></div>
								<div class="view_result">
									<?php
									if ( ! empty( $new_marks ) ) {
										$exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
										$exam_marks    = $exam_data->total_mark;
										$contributions = $exam_data->contributions;
										if ( $contributions === 'yes' ) {
											$contributions_data       = $exam_data->contributions_data;
											$contributions_data_array = json_decode( $contributions_data, true );
										}
										?>
										<div class="table-responsive mjschool-view-result-table-responsive">
											<table class="table table-bordered no-scroll-mobile-table">
												<tr>
													<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
													<?php
													if ( $contributions === 'yes' ) {
														foreach ( $contributions_data_array as $con_id => $con_value ) {
															?>
															<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php echo esc_html( $con_value['label'] ) . ' ( ' . esc_html( $con_value['mark'] ) . ' )'; ?> </th>
															<?php
														}
														?>
														<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( $exam_marks ) . ' )'; ?> </th>
														<?php
													} else {
														?>
														<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( $exam_marks ) . ' )'; ?> </th>
														<?php
													}
													?>
													<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
												</tr>
												<?php
												$total       = 0;
												$grade_point = 0;
												$total_marks = 0;
												foreach ( $subject as $sub ) {
													$ob_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid ) ?? 0;
													?>
													<tr>
														<td class="mjschool-view-result-table-value"><?php echo esc_html( $sub->sub_name ); ?> </td>
														<?php
														if ( $contributions === 'yes' ) {
															$subject_total = 0;
															foreach ( $contributions_data_array as $con_id => $con_value ) {
																$mark_value     = is_array( $ob_marks ) ? ( $ob_marks[ $con_id ] ?? 0 ) : $ob_marks;
																$subject_total += $mark_value;
																?>
																<td class="mjschool-view-result-table-value"><?php echo esc_html( $mark_value ); ?></td>
																<?php
															}
															?>
															<td class="mjschool-view-result-table-value"><?php echo esc_html( $subject_total ); ?></td>
															<?php
														} else {
															?>
															<td class="mjschool-view-result-table-value"><?php echo esc_html( $ob_marks ); ?></td>
														<?php } ?>
														<td class="mjschool-view-result-table-value"> <?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?> </td>
													</tr>
													<?php
													// Calculate total marks.
													if ( $contributions === 'yes' ) {
														foreach ( $contributions_data_array as $con_id => $con_value ) {
															$total_marks += is_array( $ob_marks ) ? ( $ob_marks[ $con_id ] ?? 0 ) : $ob_marks;
														}
													} else {
														$total_marks += $ob_marks;
													}
													// Accumulate grade points.
													$grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
												}
												$exam_total_marks = $exam_marks * $total_subject;
												$total           += $total_marks;
												$GPA              = $grade_point / $total_subject;
												$percentage       = $total / $exam_total_marks * 100;
												?>
											</table>
											<div class="table-responsive scroll-table-mobile">
												<table class="table table-bordered">
													<tr>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'Marks Obtainable', 'mjschool' ); ?></th>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'Percentage(%)', 'mjschool' ); ?></th>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'GPA', 'mjschool' ); ?></th>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
													</tr>
													<tr>
														<td class="mjschool-view-result-table-value"> <?php echo esc_html( $exam_total_marks ); ?></td>
														<td class="mjschool-view-result-table-value"><?php echo esc_html( $total ); ?></td>
														<td class="mjschool-view-result-table-value">
															<?php
															if ( ! empty( $percentage ) ) {
																echo esc_html( number_format( $percentage, 2, '.', '' ) );
															} else {
																echo '-';
															}
															?>
														</td>
														<td class="mjschool-view-result-table-value"><?php echo esc_html( round( $GPA, 2 ) ); ?> </td>
														<td class="mjschool-view-result-table-value">
															<?php
															$result = array();
															$result1  = array();
															foreach ( $subject as $sub ) {
																$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
																if ( $contributions === 'yes' ) {
																	$subject_total = 0;
																	foreach ( $contributions_data_array as $con_id => $con_value ) {
																		$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
																		$subject_total += $mark_value;
																	}
																	$marks_total = $subject_total;
																} else {
																	$marks_total = $obtain_marks;
																}
																if ( $marks_total >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
																	$result[] = 'pass';
																} else {
																	$result1[] = 'fail';
																}
															}
															if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
																esc_html_e( 'Fail', 'mjschool' );
															} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
																esc_html_e( 'Pass', 'mjschool' );
															} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
																esc_html_e( 'Fail', 'mjschool' );
															} else {
																echo '-';
															}
															?>
														</td>
													</tr>
												</table>
											</div>
										</div>
										<?php
									} else {
										?>
										<div class="col-md-12  mjschool_center_align_10px" >
											<span class="mjschool-student-exam-name">
												<?php esc_html_e( 'No Result Available.', 'mjschool' ); ?>
											</span>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>
						<?php
						++$i;
					}  /* ALL EXAM LOOP ENDS.  */
					?>
				</div>
				<?php
			} else {
				?>
				<div class="modal-header modal_header_height mjschool-model-header-padding mjschool-dashboard-model-header">
					<h6 id="myLargeModalLabel"><?php esc_html_e( 'No Result Found', 'mjschool' ); ?></h6>
				</div>
				<?php
			}
			?>
		</div>
	<?php }elseif ( $school_type === 'university' ) {
		?>
		<div class="mjschool-panel-white">
			<div class="modal-header modal_header_height mjschool-model-header-padding mjschool-dashboard-model-header">
				<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
				<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( mjschool_get_user_name_by_id($uid ) ); ?>'s <?php esc_html_e( 'Result', 'mjschool' ); ?></h4>
				
			</div>
			<?php
			if ( ! empty( $all_exam ) ) {
				?>
				<div class="clearfix"></div>
				<div id="mjschool-accordion" class="accordion student_accordion" aria-multiselectable="true" role="tablist">
					<?php
					$i = 0;
					// Removed $total_max_mark initialization here, moved inside loop.
					foreach ($all_exam as $exam) {
						/* ALL EXAM LOOP STARTS.  */
						$exam_id = $exam->exam_id;
						// Get class and section for this specific exam.
						$exam_data       = $exam_obj->mjschool_exam_data($exam_id);
						$exam_class_id   = $exam_data->class_id;
						$exam_section_id = $exam_data->section_id;
						$total_max_mark  = 0; // Initialize for each exam.
						$total_obtained  = 0; // Initialize for each exam.

						// Now get subject based on this exam's class & section.
						global $wpdb;
						if ($exam_section_id === 0) {
							// All sections, get all subjects of the class
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query
							$subjects_for_exam = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mjschool_subject WHERE class_id = %d", $exam_class_id ) );
						} else {
							// Specific section
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query
							$subjects_for_exam = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mjschool_subject WHERE class_id = %d AND section_id = %d", $exam_class_id, $exam_section_id ) );
						}
						// Removed $total_subject count here as it wasn't used correctly later.

						// Check if results exist.
						$results_found = false;
						if ( ! empty( $subjects_for_exam ) ) {
							foreach ($subjects_for_exam as $sub) {
								$marks = $obj_mark->mjschool_get_marks($exam_id, $exam_class_id, $sub->subid, $uid);
								if ( ! empty( $marks) || (isset($marks) && $marks === '0' ) ) {
									$results_found = true;
									break;
								}
							}
						}

						?>
						<div class="mt-2 accordion-item">
							<h4 class="accordion-header" id="heading_<?php echo esc_attr($i); ?>">
								<button class="accordion-button mjschool-student-result-collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?php echo esc_attr($i); ?>" aria-expanded="true" aria-controls="heading_<?php echo esc_attr($i); ?>">
									<div class="col-md-10 col-7">
										<span class="mjschool-student-exam-result"><?php esc_html_e( 'Exam Results : ', 'mjschool' ); ?></span>
										&nbsp;
										<span class="mjschool-student-exam-name"><?php echo esc_html( $exam->exam_name); ?></span>
									</div>
									<?php
									if ($results_found) {
									?>
										<div class="col-md-2 row justify-content-end mjschool-view-result">
											<?php  
											?>
											<div class="col-md-5 mjschool-width-50px">
												<?php if ($roles != "parent" && $roles != "student") { ?>
													<a href="#" student_id="<?php echo esc_attr(mjschool_encrypt_id($uid ) ); ?>" class_id="<?php echo esc_attr(mjschool_encrypt_id($exam_class_id ) ); ?>" section_id="<?php echo esc_attr(mjschool_encrypt_id($exam_section_id ) ); ?>" exam_id="<?php echo esc_attr(mjschool_encrypt_id($exam_id ) ); ?>" typeformat="pdf" class="mjschool-float-right show-popup-teacher-details" target="_blank">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-pdf.png"); ?>">
													</a>
												<?php } else { ?>
													<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'mjschool_student', 'print' => 'pdf', 'student' => mjschool_encrypt_id($uid), 'exam_id' => mjschool_encrypt_id($exam_id) ), admin_url() ) ); ?>" class="mjschool-float-right" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-pdf.png"); ?>"></a>
												<?php } ?>
											</div>
											<div class="col-md-4 mjschool-width-50px mjschool-rtl-margin-left-20px mjschool-exam-result-pdf-margin mjschool_margin_right_22px" >
												<?php if ($roles != "parent" && $roles != "student") { ?>
													<a href="#" student_id="<?php echo esc_attr(mjschool_encrypt_id($uid ) ); ?>" class_id="<?php echo esc_attr(mjschool_encrypt_id($exam_class_id ) ); ?>" section_id="<?php echo esc_attr(mjschool_encrypt_id($exam_section_id ) ); ?>" exam_id="<?php echo esc_attr(mjschool_encrypt_id($exam_id ) ); ?>" typeformat="print" class="mjschool-float-right show-popup-teacher-details" target="_blank">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-print.png"); ?>">
													</a>
												<?php } else { ?>
													<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'mjschool_student', 'print' => 'print', 'student' => mjschool_encrypt_id($uid), 'exam_id' => mjschool_encrypt_id($exam_id) ), admin_url() ) ); ?>" class="mjschool-float-right" target="_blank">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-print.png"); ?>">
													</a>
												<?php } ?>
											</div>
											<?php  
											?>
										</div>
									<?php
									} else {
									?>
										<span class="mjschool-student-exam-name"> <?php esc_html_e( 'No Result', 'mjschool' ); ?> </span>
									<?php
									}
									?>
								</button>
							</h4>
							<div id="collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse" aria-labelledby="heading_<?php echo esc_attr($i); ?>" data-bs-parent="#mjschool-accordion">
								<div class="clearfix"></div>
								<div class="clearfix"></div>
								<div class="view_result">
									<?php
									if ($results_found) {
										// Removed redundant fetching of $exam_data.
										$exam_marks                 = $exam_data->total_mark; // Max marks for the whole exam (might not be needed if summing subject max).
										$contributions              = $exam_data->contributions;
										$contributions_data_array   = ($contributions === 'yes' ) ? json_decode($exam_data->contributions_data, true) : [];
									?>
										<div class="table-responsive mjschool-view-result-table-responsive">
											<table class="table table-bordered no-scroll-mobile-table">
												<tr>
													<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
													<?php
													if ($contributions === 'yes' && !empty($contributions_data_array ) ) {
														foreach ($contributions_data_array as $con_value) {
															?>
															<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php echo esc_html( $con_value['label']) . ' ( ' . esc_html( $con_value['mark']) . ' )'; ?> </th>
															<?php
														}
														?>
														<!-- Total header might need adjustment based on how max marks are stored for contributions. -->
														<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php esc_html_e( 'Total', 'mjschool' ); ?> </th>
														<?php
													} else {
														?>
														<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php esc_html_e( 'Total', 'mjschool' ); ?> </th>
														<?php
													}
													?>
													<th class="mjschool-view-result-table-heading mjschool-view-result-table-heading-responsive"> <?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
												</tr>
												<?php
												// Re-initialize calculation variables for *this exam*.
												$grade_point_total   = 0;
												$pass_fail_results   = [];
												$exam_subject_lookup = [];
												$exam_subjects       = json_decode($exam_data->subject_data, true);
												foreach ($exam_subjects as $exam_sub) {
													$exam_subject_lookup[$exam_sub['subject_id']] = $exam_sub;
												}
												$valid_subject_count = 0; // Count subjects the student is actually in.

												// Build filtered subject list: only subjects that are in exam AND assigned to the student.
												$filtered_subjects_for_exam = [];
												$current_student_id = (int) $uid;

												foreach ($subjects_for_exam as $sub) {
													$assigned_student_ids = !empty($sub->selected_students)
														? array_map( 'intval', explode( ',', $sub->selected_students ) )
														: [];

													// Check if subject is in the exam's subject list.
													$is_in_exam = isset($exam_subject_lookup[$sub->subid]);

													// Check if student is assigned to subject.
													$is_assigned_to_student = in_array($current_student_id, $assigned_student_ids, true);

													if ($is_in_exam && $is_assigned_to_student) {
														$filtered_subjects_for_exam[] = $sub;
													}
												}
												
												foreach ($filtered_subjects_for_exam as $sub) {
													$ob_marks             = $obj_mark->mjschool_get_marks($exam_id, $exam_class_id, $sub->subid, $uid); // Fetch marks for this subject.
													$max_marks            = isset($exam_subject_lookup[$sub->subid]) ? (int)$exam_subject_lookup[$sub->subid]['max_marks'] : 0; // Get max marks for subject.

													// --- FIX 1: Correct student ID check. ---
													$assigned_student_ids = !empty($sub->selected_students) ? array_map( 'intval', explode( ',', $sub->selected_students ) ) : [];
													$current_student_id   = (int) $uid; // Use $uid here.

													// Skip subject if student not assigned AND marks are empty (allows showing subjects with 0 marks if assigned).
													if (!in_array($current_student_id, $assigned_student_ids, true) && empty($ob_marks) && (!isset($ob_marks) || $ob_marks !== '0' ) ) {
														continue;
													}
													$valid_subject_count++; // Increment count for GPA calculation.

												?>
													<tr>
														<td class="mjschool-view-result-table-value"><?php echo esc_html( $sub->sub_name); ?> </td>
														<?php
														$subject_total = 0; // Initialize subject total for contributions.
														if ($contributions === 'yes' && !empty($contributions_data_array ) ) {
															foreach ($contributions_data_array as $con_id => $con_value) {
																$mark_value = is_array($ob_marks) ? ($ob_marks[$con_id] ?? 0) : 0;
																$subject_total += $mark_value;
																?>
																<td class="mjschool-view-result-table-value"><?php echo esc_html( $mark_value); ?></td>
																<?php
															}
															// --- FIX 2: Accumulate total obtained for contributions. ---
															$total_obtained += $subject_total;
															$total_max_mark += $max_marks; // Assuming max_marks in exam_subject_lookup is the total for the subject.
															?>
															<td class="mjschool-view-result-table-value"><?php echo esc_html( $subject_total); ?></td>
														<?php
														} else { // Not contributions.
															$marks_to_display = $ob_marks ?? 0;
															$total_obtained += $marks_to_display;
															$total_max_mark += $max_marks;
														?>
															<td class="mjschool-view-result-table-value"><?php echo esc_html( $marks_to_display) . ' / ' . esc_html( $max_marks); ?></td>
														<?php } ?>
														<td class="mjschool-view-result-table-value"> <?php echo esc_html( $obj_mark->mjschool_get_grade($exam_id, $exam_class_id, $sub->subid, $uid ) ); ?> </td>
													</tr>
												<?php
													// Calculate pass/fail status.
													$final_subject_mark = ($contributions === 'yes' ) ? $subject_total : ($ob_marks ?? 0);
														$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid ) ?? 0;
														if ($obtain_marks >= $exam_subject_lookup[$sub->subid]['passing_marks']) {
															$pass_fail_results[] = "pass";
														} else {
															$pass_fail_results[] = "fail";
														}
													// Accumulate grade points.
													$grade_point_total += $obj_mark->mjschool_get_grade_point($exam_id, $exam_class_id, $sub->subid, $uid);
												} // End foreach $subjects_for_exam.
												// Calculate GPA and Percentage using counts of valid subjects.
												$GPA = ($valid_subject_count > 0) ? ($grade_point_total / $valid_subject_count) : 0;
												$percentage = ($total_max_mark > 0) ? (($total_obtained / $total_max_mark) * 100) : 0;
												?>
											</table>
											<div class="table-responsive scroll-table-mobile">
												<table class="table table-bordered">
													<tr>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'Marks Obtainable', 'mjschool' ); ?></th>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'Percentage(%)', 'mjschool' ); ?></th>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'GPA', 'mjschool' ); ?></th>
														<th class="mjschool-view-result-table-heading"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
													</tr>
													<tr>
														<td class="mjschool-view-result-table-value"> <?php echo esc_html( $total_max_mark); ?></td>
														<td class="mjschool-view-result-table-value"><?php echo esc_html( $total_obtained); ?></td>
														<td class="mjschool-view-result-table-value">
															<?php
															if ( ! empty( $percentage ) ) {
																echo esc_html( number_format($percentage, 2, '.', '' ) );
															} else {
																echo '-';
															}
															?>
														</td>
														<td class="mjschool-view-result-table-value"><?php echo esc_html( round($GPA, 2 ) ); ?> </td>
														<td class="mjschool-view-result-table-value">
															<?php
															if (in_array( "fail", $pass_fail_results ) ) {
																esc_html_e( 'Fail', 'mjschool' );
															} else {
																esc_html_e( 'Pass', 'mjschool' );
															}
															?>
														</td>
													</tr>
												</table>
											</div>
										</div>
									<?php
									} else { // If results_found is false inside the collapse body.
									?>
										<div class="col-md-12  mjschool_center_align_10px" >
											<span class="mjschool-student-exam-name">
												<?php esc_html_e( 'No Result Available.', 'mjschool' ); ?>
											</span>
										</div>
									<?php
									}
									?>
								</div><!-- End view_result. -->
							</div><!-- End accordion-collapse. -->
						</div><!-- End accordion-item. -->
						<?php
						$i++;
					} // <!-- ALL EXAM LOOP ENDS. -->
					?>
				</div><!-- End mjschool-accordion. -->
				<?php
        	} else {
				?>
				<div class="modal-header modal_header_height mjschool-model-header-padding mjschool-dashboard-model-header">
					<h6 id="myLargeModalLabel"><?php esc_html_e( 'No Result Found', 'mjschool' ); ?></h6>
				</div>
				<?php
			}
			?>
		</div>
	<?php	
	}
	wp_die();
}
add_action( 'wp_ajax_mjschool_ajax_create_meeting', 'mjschool_ajax_create_meeting' );
function mjschool_ajax_create_meeting() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
    if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
        wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
    }

    // 2. CHECK IF USER IS LOGGED IN.
    if ( ! is_user_logged_in() ) {
        wp_die( 'You must be logged in.' );
    }
	$obj_mark        = new Mjschool_Class_Routine();
	$route_id        = isset($_REQUEST['route_id']) ? intval( wp_unslash($_REQUEST['route_id']) ) : 0;
	$route_data      = mjschool_get_route_by_id( $route_id );
	
	// Validate route_data exists
	if ( empty( $route_data ) ) {
		wp_die( 'Invalid route data.' );
	}
	
	$start_time_data = explode( ':', $route_data->start_time );
	$end_time_data   = explode( ':', $route_data->end_time );
	
	// Validate time data structure
	if ( count( $start_time_data ) < 3 || count( $end_time_data ) < 3 ) {
		wp_die( 'Invalid time format.' );
	}
	
	if ( $start_time_data[1] === '0' || $end_time_data[1] === '0' ) {
		$start_time_minit = '00';
		$end_time_minit   = '00';
	} else {
		$start_time_minit = $start_time_data[1];
		$end_time_minit   = $end_time_data[1];
	}
	$start_time = date( 'h:i A', strtotime( sanitize_text_field( $start_time_data[0] ) . ':' . sanitize_text_field( $start_time_minit ) . ' ' . sanitize_text_field( $start_time_data[2] ) ) );
	$end_time   = date( 'h:i A', strtotime( sanitize_text_field( $end_time_data[0] ) . ':' . sanitize_text_field( $end_time_minit ) . ' ' . sanitize_text_field( $end_time_data[2] ) ) );
	
	// Initialize $duration variable that is used but not defined
	$duration = '';
	
	?>
	<div class="modal-header modal_header_height mjschool-import-csv-popup">
		
		<a href="#" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Create Virtual Class', 'mjschool' ); ?></h4>
		
	</div>
	<div class="mjschool-panel-white">
		<div class="mjschool-panel-body mjschool-padding-18px-top-0px">
			<form name="route_form" action="" method="post" class="mjschool-form-horizontal" id="meeting_form">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="route_id" value="<?php echo esc_attr( $route_id ); ?>">
				<input type="hidden" name="class_id" value="<?php echo esc_attr( $route_data->class_id ); ?>">
				<input type="hidden" name="subject_id" value="<?php echo esc_attr( $route_data->subject_id ); ?>">
				<input type="hidden" name="class_section_id" value="<?php echo esc_attr( $route_data->section_name ); ?>">
				<input type="hidden" name="duration" value="<?php echo esc_attr( $duration ); ?>">
				<input type="hidden" name="weekday" value="<?php echo esc_attr( $route_data->weekday ); ?>">
				<input type="hidden" name="start_time" value="<?php echo esc_attr( $start_time ); ?>">
				<input type="hidden" name="end_time" value="<?php echo esc_attr( $end_time ); ?>">
				<input type="hidden" name="teacher_id" value="<?php echo esc_attr( $route_data->teacher_id ); ?>">
				<div class="form-body mjschool-user-form"><!--Mjschool-user-form div.-->
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="class_name" class="form-control" maxlength="50" type="text" value="<?php echo esc_attr( mjschool_get_class_name( $route_data->class_id ) ); ?>" name="class_name" disabled>
									<label class="active" for="username"><?php esc_html_e( 'Class Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="class_section" class="form-control" maxlength="50" type="text" value="<?php echo esc_attr( mjschool_get_section_name( $route_data->section_name ) ); ?>" name="class_section" disabled>
									<label class="active" for="username"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="subject" class="form-control" type="text" value="<?php echo esc_attr( mjschool_get_single_subject_name( $route_data->subject_id ) ); ?>" name="class_section" disabled>
									<label class="active" for="username"><?php esc_html_e( 'Subject', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="start_time" class="form-control" type="text" value="<?php echo esc_attr( $start_time ); ?>" name="start_time" disabled>
									<label class="active" for="username"><?php esc_html_e( 'Start Time', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="end_time" class="form-control" type="text" value="<?php echo esc_attr( $end_time ); ?>" name="end_time" disabled>
									<label class="active" for="username"><?php esc_html_e( 'End Time', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 virtual_mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="start_date" class="form-control validate[required] text-input" type="text" name="start_date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
									<label class="active" for="username"><?php esc_html_e( 'Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="end_date" class="form-control validate[required] text-input" type="text" name="end_date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
									<label class="active" for="username"><?php esc_html_e( 'End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea name="agenda" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="250"></textarea>
										<span class="mjschool-txt-title-label"></span>
										<label class="text-area address active"><?php esc_html_e( 'Topic', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
						<?php wp_nonce_field( 'create_meeting_admin_nonce' ); ?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="password" class="form-control validate[required,minSize[8],maxSize[12]]" type="password" value="" name="password">
									<label class="active" for="username"><?php esc_html_e( 'Password', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
					</div>
				</div><!--Mjschool-user-form div.-->
				<div class="form-body mjschool-user-form"><!--Mjschool-user-form div.-->
					<div class="row">
						<div class="col-sm-6">
							<?php
							// Initialize $edit variable if not already set
							$edit = isset( $edit ) ? $edit : false;
							?>
							<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Virtual Class', 'mjschool' ); } else { esc_attr_e( 'Create Virtual Class', 'mjschool' ); } ?>" name="create_meeting" class="mjschool-save-btn btn btn-success" />
						</div>
					</div>
				</div><!--Mjschool-user-form div.-->
			</form>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_ajax_view_meeting_detail', 'mjschool_ajax_view_meeting_detail' );
/**
 * Handles the AJAX request to display the "Create Virtual Class" modal.
 *
 * This function validates the AJAX nonce and user authentication, retrieves  
 * class routine details (route, class, section, subject, time range), formats  
 * time values, initializes jQuery datepickers & validation, and outputs the  
 * full HTML structure for the virtual class creation popup form.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML markup and inline JavaScript for the modal form.
 */
function mjschool_ajax_view_meeting_detail() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
    if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
        wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
    }

    // 2. CHECK IF USER IS LOGGED IN.
    if ( ! is_user_logged_in() ) {
        wp_die( 'You must be logged in.' );
    }
	$obj_virtual_classroom = new Mjschool_Virtual_Classroom();
	$meeting_id            = isset($_REQUEST['meeting_id']) ? intval( wp_unslash($_REQUEST['meeting_id']) ) : 0;
	$class_data            = $obj_virtual_classroom->mjschool_get_single_meeting_data_in_zoom( $meeting_id );
	
	// Validate class_data exists
	if ( empty( $class_data ) ) {
		wp_die( 'Invalid meeting data.' );
	}
	
	?>
	<div class="modal-header modal_header_height">
		
		<a href="#" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Virtual Class Details', 'mjschool' ); ?></h4>
		
	</div>
	<div class="mjschool-panel-white mjschool-form-horizontal mjschool-view-notice-overflow mjschool-padding-20px">
		<div class="row">
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Meeting ID', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value"><?php echo esc_html( $class_data->zoom_meeting_id ); ?></label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Meeting Title', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value"><?php echo esc_html( $class_data->title ); ?></label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Class Name', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_class_section_name_wise( $class_data->class_id, $class_data->section_id ) ); ?></label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value">
					<?php
					if ( ! empty( $class_data->subject_id ) ) {
						echo esc_html( mjschool_get_single_subject_name( $class_data->subject_id ) );
					} else {
						esc_html_e( 'N/A', 'mjschool' );
					}
					?>
				</label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></label><br>
					<label class="mjschool-label-value">
					<?php
					if ( ! empty( $class_data->teacher_id ) ) {
						echo esc_html( mjschool_get_teacher( $class_data->teacher_id ) );
					} else {
						esc_html_e( 'N/A', 'mjschool' );
					}
					?>
				</label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Day', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value">
					<?php
					$weekday_id = isset( $class_data->weekday_id ) ? intval( $class_data->weekday_id ) : 0;
					if ( $weekday_id === 1 ) {
						$day = esc_html__( 'Monday', 'mjschool' );
					} elseif ( $weekday_id === 2 ) {
						$day = esc_html__( 'Tuesday', 'mjschool' );
					} elseif ( $weekday_id === 3 ) {
						$day = esc_html__( 'Wednesday', 'mjschool' );
					} elseif ( $weekday_id === 4 ) {
						$day = esc_html__( 'Thursday', 'mjschool' );
					} elseif ( $weekday_id === 5 ) {
						$day = esc_html__( 'Friday', 'mjschool' );
					} elseif ( $weekday_id === 6 ) {
						$day = esc_html__( 'Saturday', 'mjschool' );
					} elseif ( $weekday_id === 7 ) {
						$day = esc_html__( 'Sunday', 'mjschool' );
					} else {
						$day = esc_html__( 'N/A', 'mjschool' );
					}
					echo esc_html( $day );
					?>
				</label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Start To End Time', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value">
					<?php
					$route_data  = mjschool_get_route_by_id( $class_data->route_id );
					if ( ! empty( $route_data ) && ! empty( $route_data->start_time ) && ! empty( $route_data->end_time ) ) {
						$stime       = explode( ':', $route_data->start_time );
						if ( count( $stime ) >= 3 ) {
							$start_hour  = str_pad( sanitize_text_field( $stime[0] ), 2, '0', STR_PAD_LEFT );
							$start_min   = str_pad( sanitize_text_field( $stime[1] ), 2, '0', STR_PAD_LEFT );
							$start_am_pm = sanitize_text_field( $stime[2] );
							$start_time  = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
						} else {
							$start_time = esc_html__( 'N/A', 'mjschool' );
						}
						
						$etime       = explode( ':', $route_data->end_time );
						if ( count( $etime ) >= 3 ) {
							$end_hour    = str_pad( sanitize_text_field( $etime[0] ), 2, '0', STR_PAD_LEFT );
							$end_min     = str_pad( sanitize_text_field( $etime[1] ), 2, '0', STR_PAD_LEFT );
							$end_am_pm   = sanitize_text_field( $etime[2] );
							$end_time    = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
						} else {
							$end_time = esc_html__( 'N/A', 'mjschool' );
						}
						
						echo esc_html( mjschool_time_remove_colon_before_am_pm( $start_time ) );
						echo ' ';
						esc_html_e( 'To', 'mjschool' );
						echo ' ';
						echo esc_html( mjschool_time_remove_colon_before_am_pm( $end_time ) );
					} else {
						esc_html_e( 'N/A', 'mjschool' );
					}
					?>
				</label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Start To End Date', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $class_data->start_date ) ) . ' ' . esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( mjschool_get_date_in_input_box( $class_data->end_date ) ); ?></label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Password', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value"><?php echo esc_html( $class_data->password ); ?></label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Join Virtual Class Link', 'mjschool' ); ?></label><br>
				<div class="copy_text">
					<label class="mjschool-label-value mjschool-word-break"><?php echo esc_url( $class_data->meeting_join_link ); ?></label>
				</div>
			</div>
			<div class="col-md-12 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Agenda', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value">
					<?php
					if ( ! empty( $class_data->agenda ) ) {
						echo esc_html( $class_data->agenda );
					} else {
						esc_html_e( 'N/A', 'mjschool' );
					}
					?>
				</label>
			</div>
			<div class="col-md-3">
				<button type="button" onclick="copy_text();" class="mjschool-save-btn btn btn-success"><?php esc_html_e( 'Copy Link', 'mjschool' ); ?></button>
			</div>
			<span class="copy_link_text mjchool_display_none"><?php esc_html_e( 'Link Copied Successfully', 'mjschool' ); ?></span>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_active_student', 'mjschool_active_student' );
/**
 * Handles AJAX request to display the "Activate Student" form popup.
 *
 * Validates nonce, ensures the user is logged in, retrieves student details,
 * and outputs an HTML form allowing administrators to activate a student
 * and optionally enable email/SMS notifications.
 *
 * @since 1.0.0
 * @return void Outputs HTML content for the activation modal and terminates execution.
 */
function mjschool_active_student() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$uid = isset($_REQUEST['student_id']) ? intval( wp_unslash($_REQUEST['student_id']) ) : 0;
	?>
	<div class="form-group mjschool-popup-header-marging">
		
		<a href="#" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title" id="myLargeModalLabel"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></h4>
		
	</div>
	<div class="mjschool-panel-body mjschool-padding-15px">
		<div class="mjschool-panel-heading"> <h4 class="mjschool-panel-title"><?php echo esc_html( mjschool_get_user_name_by_id( $uid ) ); ?></h4> </div>
		<form name="expense_form" action="" method="post" class="mjschool-margin-top-15px mjschool-form-horizontal" id="expense_form">
			<input type="hidden" name="act_user_id" value="<?php echo esc_attr( $uid ); ?>">
			<div class="form-body mjschool-user-form"> <!--Form Body div.-->
				<div class="row"><!--Row Div.-->
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="roll_id" class="form-control validate[required,custom[integer]] text-input" maxlength="10" type="text" value="" name="roll_id">
								<label  for="roll_id"><?php esc_html_e( 'Roll Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-checkbox-height-47px">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label" for="mjschool_enable_homework_sms"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
										<input id="chk_mjschool_sent1" class=" mjschool-check-box-input-margin" type="checkbox" <?php $mjschool_student_mail_service_enable = 0; if ( $mjschool_student_mail_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_student_mail_service_enable"> <?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-checkbox-height-47px">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label" for="mjschool_enable_homework_sms"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
										<input id="chk_mjschool_sent2"  type="checkbox" <?php $mjschool_student_sms_service_enable = 0; if ( $mjschool_student_sms_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_student_sms_service_enable"> &nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-body mjschool-user-form mjschool-margin-top-15px"> <!--form Body div-->
				<div class="row"><!--Row Div-->
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Active Student', 'mjschool' ); ?>" name="active_user" class="btn mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_ajax_result_pdf', 'mjschool_ajax_result_pdf' );
/**
 * Generates a PDF result sheet for a student via AJAX.
 *
 * This function collects student details, subjects, marks, grade points,
 * attendance, and comments. It renders an HTML result layout, then converts
 * it into a PDF using mPDF and saves it to the server.
 *
 * @since 1.0.0
 * @return void Outputs PDF to file system and terminates execution.
 */
function mjschool_ajax_result_pdf() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	
	$obj_mark    = new Mjschool_Marks_Manage();
	$uid         = isset($_REQUEST['student_id']) ? intval( wp_unslash( $_REQUEST['student_id'] ) ) : 0;
	
	// Validate user exists
	if ( empty( $uid ) ) {
		wp_die( 'Invalid student ID.' );
	}
	
	$user        = get_userdata( $uid );
	
	// Validate user data
	if ( ! $user ) {
		wp_die( 'Invalid user data.' );
	}
	
	$user_meta   = get_user_meta( $uid );
	$class_id    = isset( $user_meta['class_name'][0] ) ? intval( $user_meta['class_name'][0] ) : 0;
	
	// Validate class ID
	if ( empty( $class_id ) ) {
		wp_die( 'Invalid class data.' );
	}
	
	$subject     = $obj_mark->mjschool_student_subject( $class_id );
	$exam_data   = mjschool_get_exam_id();
	
	// Validate exam data exists
	if ( empty( $exam_data ) || ! isset( $exam_data->exam_id ) ) {
		wp_die( 'Invalid exam data.' );
	}
	
	$exam_id     = $exam_data->exam_id;
	$total       = 0;
	$grade_point = 0;
	$total_subject = is_array( $subject ) || is_object( $subject ) ? count( $subject ) : 0;
	
	ob_start();
	?>
	<div class="panel mjschool-panel-white">
		<form method="post">
			<input type="hidden" name="student_id" value="<?php echo esc_attr( $uid ); ?>">
			<button id="pdf" type="button"><?php esc_html_e( 'PDF', 'mjschool' ); ?> </button>
		</form>
		<p class="student_name">
			<?php esc_html_e( 'Result', 'mjschool' ); ?>
		</p>
		<div class="clearfix mjschool-panel-heading">
			<h4 class="mjschool-panel-title"><?php echo esc_html( mjschool_get_user_name_by_id( $uid ) ); ?></h4>
		</div>
		<div class="mjschool-panel-body">
			<div class="table-responsive">
				<table class="table table-bordered">
					<tr>
						<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Obtain Mark', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
					</tr>
					<?php
					if ( ! empty( $subject ) ) {
						foreach ( $subject as $sub ) {
							$obtained_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
							$grade = $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid );
							$attendance = $obj_mark->mjschool_get_attendance( $exam_id, $class_id, $sub->subid, $uid );
							$comment = $obj_mark->mjschool_get_marks_comment( $exam_id, $class_id, $sub->subid, $uid );
							$grade_point_value = $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
							?>
							<tr>
								<td><?php echo esc_html( $sub->sub_name ); ?></td>
								<td><?php echo esc_html( $obtained_marks ); ?></td>
								<td><?php echo esc_html( $grade ); ?></td>
								<td><?php echo esc_html( $attendance ); ?></td>
								<td><?php echo esc_html( $comment ); ?></td>
							</tr>
							<?php
							$total       += floatval( $obtained_marks );
							$grade_point += floatval( $grade_point_value );
						}
					}
					$GPA = ( $total_subject > 0 ) ? ( $grade_point / $total_subject ) : 0;
					?>
				</table>
			</div>
		</div>
		<hr />
		<?php echo esc_html( 'GPA is ' . round( $GPA, 2 ) ); ?>
		<p class="result_total"><?php echo esc_html__( 'Total Marks', 'mjschool' ) . ' => ' . esc_html( $total ); ?></p>
		<hr />
		<p class="result_point">
			<?php echo esc_html__( 'GPA(grade point average)', 'mjschool' ) . ' => ' . esc_html( $grade_point ); ?>
		</p>
		<hr />
	</div>
	<?php
	$out_put = ob_get_contents();
	ob_end_clean();
	require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
	$mpdf = new Mpdf\Mpdf();
	$mpdf->WriteHTML( $out_put );
	$mpdf->Output( 'filename.pdf', 'F' );
	unset( $out_put );
	unset( $mpdf );
	die();
}
add_action( 'wp_ajax_mjschool_load_user', 'mjschool_load_user' );
add_action( 'wp_ajax_nopriv_mjschool_load_user', 'mjschool_load_user' );
/**
 * Loads student list based on selected class via AJAX.
 *
 * Validates nonce, retrieves students assigned to a specific class,
 * excludes approved students, and returns `<option>` elements for the UI.
 *
 * @since 1.0.0
 * @return void Echoes HTML <option> tags and terminates execution.
 */
function mjschool_load_user() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	// if ( ! is_user_logged_in() ) {
	// 	wp_die( 'You must be logged in.' );
	// }
	$class_id = isset($_REQUEST['class_list']) ? sanitize_text_field( wp_unslash($_REQUEST['class_list']) ) : '';
	if ( empty( $class_id ) ) {
		$defaultmsg = esc_attr__( 'Select Student', 'mjschool' );
		echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	} else {
		global $wpdb;
		$exlude_id = mjschool_approve_student_list();
		
		$retrieve_data = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
		
		$defaultmsg = esc_attr__( 'Select Student', 'mjschool' );
		echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
		foreach ( $retrieve_data as $users ) {
			echo '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( mjschool_student_display_name_with_roll( $users->ID ) ) . '</option>';
		}
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_section_user', 'mjschool_load_section_user' );
add_action( 'wp_ajax_nopriv_mjschool_load_section_user', 'mjschool_load_section_user' );
/**
 * Loads student list filtered by section or class via AJAX.
 *
 * If a section is selected, fetches users by section; otherwise fetches
 * all students belonging to a class. The function outputs a list of 
 * <option> tags for a dropdown selector.
 *
 * @since 1.0.0
 * @return void Outputs HTML <option> tags and terminates execution.
 */
function mjschool_load_section_user() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	// if ( ! is_user_logged_in() ) {
	// 	wp_die( 'You must be logged in.' );
	// }
	$section_id = isset( $_POST['section_id'] ) ? intval( wp_unslash( $_POST['section_id'] ) ) : 0;
	$class_id   = isset( $_POST['class_id'] ) ? sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) : '';

	if ( empty( $section_id ) ) {
		global $wpdb;
		$exlude_id = mjschool_approve_student_list();
		
		$retrieve_data = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
		
		$defaultmsg = esc_attr__( 'Select Student', 'mjschool' );
		echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
		foreach ( $retrieve_data as $users ) {
			echo '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( mjschool_student_display_name_with_roll( $users->ID ) ) . '</option>';
		}
		die();
	} else {
		global $wpdb;
		$exlude_id = mjschool_approve_student_list();
		
		$retrieve_data = get_users(array( 'meta_key' => 'class_section', 'meta_value' => $section_id, 'role' => 'student', 'exclude' => $exlude_id ) );
		
		$defaultmsg = esc_attr__( 'Select student', 'mjschool' );
		echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
		foreach ( $retrieve_data as $users ) {
			echo '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( mjschool_student_display_name_with_roll( $users->ID ) ) . '</option>';
		}
		die();
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_books', 'mjschool_load_books' );
/**
 * Loads available books from the library for a selected category via AJAX.
 *
 * Ensures request security and authentication, fetches books that have
 * remaining quantity, and returns them as dropdown options.
 *
 * @since 1.0.0
 * @return void Outputs <option> tags and terminates execution.
 */
function mjschool_load_books() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$cat_id = isset($_POST['bookcat_id']) ? intval( wp_unslash($_POST['bookcat_id']) ) : 0;
	global $wpdb;
	$table_book = $wpdb->prefix . 'mjschool_library_book';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_book WHERE cat_id = %d AND quentity != %d", $cat_id, 0 ) );
	foreach ( $retrieve_data as $book ) {
		echo '<option value=' . esc_attr( $book->id ) . '>' . esc_html( stripslashes( $book->book_name ) ) . '( ' . esc_html( $book->quentity ) . ' )' . '</option>';
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_class_fee_type', 'mjschool_load_class_fee_type' );
/**
 * Loads fee types associated with a class via AJAX.
 *
 * Validates the AJAX request, retrieves the fee types for the selected class,
 * and generates an HTML dropdown list for UI usage.
 *
 * @since 1.0.0
 * @return void Outputs <option> tags and terminates.
 */
function mjschool_load_class_fee_type() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_list = isset($_POST['class_list']) ? sanitize_text_field( wp_unslash($_POST['class_list']) ) : '';
	global $wpdb;
	$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees WHERE class_id = %s", $class_list ) );
	if ( ! empty( $result ) ) {
		foreach ( $result as $retrive_data ) {
			echo '<option value="' . esc_attr( $retrive_data->fees_id ) . '">' . esc_html( get_the_title( $retrive_data->fees_title_id ) ) . '</option>';
		}
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_section_fee_type', 'mjschool_load_section_fee_type' );
/**
 * Loads fee types based on section ID via AJAX.
 *
 * Validates the security nonce, checks user login status,
 * retrieves fees registered for a particular section,
 * and outputs them as <option> tags in a dropdown.
 *
 * @since 1.0.0
 * @return void Echoes option tags and terminates execution.
 */
function mjschool_load_section_fee_type() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$section_id = isset($_POST['section_id']) ? intval( wp_unslash($_POST['section_id']) ) : 0;
	global $wpdb;
	$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees where section_id =%d", $section_id ) );
	$defaultmsg = esc_attr__( 'Select Fee Type', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	if ( ! empty( $result ) ) {
		foreach ( $result as $retrive_data ) {
			echo '<option value="' . esc_attr( $retrive_data->fees_id ) . '">' . esc_html( get_the_title( $retrive_data->fees_title_id ) ) . '</option>';
		}
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_fee_type_amount', 'mjschool_load_fee_type_amount' );
add_action( 'wp_ajax_nopriv_mjschool_load_fee_type_amount', 'mjschool_load_fee_type_amount' );
/**
 * Calculates the total fee amount based on selected fee types via AJAX.
 *
 * Validates the request, retrieves fee amounts for each selected fee type,
 * sums them, and returns the total amount for real-time display.
 *
 * @since 1.0.0
 * @return void Outputs the total amount as a number and terminates execution.
 */
function mjschool_load_fee_type_amount() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$fees_id_array = isset($_POST['fees_id']) ? $_POST['fees_id'] : array();
	
	// Validate that fees_id is an array
	if ( ! is_array( $fees_id_array ) ) {
		$fees_id_array = array();
	}
	
	global $wpdb;
	$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
	$fees_amount         = array();
	
	if ( ! empty( $fees_id_array ) ) {
		foreach ( $fees_id_array as $id ) {
			$fees_id = intval( $id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees where fees_id =%d", $fees_id ) );
			if ( ! empty( $result ) && isset( $result->fees_amount ) ) {
				$fees_amount[] = floatval( $result->fees_amount );
			}
		}
	}
	echo esc_html( array_sum( $fees_amount ) );
	die();
}
add_action( 'wp_ajax_mjschool_verify_pkey', 'mjschool_verify_pkey' );
/**
 * Handles AJAX request to verify the Envato purchase key.
 *
 * Validates nonce and user login status, checks remote licensing server status,
 * verifies the purchase key with the license server, updates plugin options on success,
 * and returns a JSON response with message and status.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON response and terminates execution.
 */
function mjschool_verify_pkey() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$api_server   = 'license.dasinfomedia.com';
	$fp           = fsockopen( $api_server, 80, $errno, $errstr, 2 );
	$location_url = esc_url( admin_url() . 'admin.php?page=mjschool' );
	if ( ! $fp ) {
		$server_rerror = 'Down';
	} else {
		$server_rerror = 'up';
		fclose( $fp ); // Close the connection
	}
	if ( $server_rerror === 'up' ) {
		$domain_name         = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '';
		$licence_key         = isset($_REQUEST['mjschool_licence_key']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_licence_key'])) : '';
		$email               = isset($_REQUEST['enter_email']) ? sanitize_email(wp_unslash($_REQUEST['enter_email'])) : '';
		$data['domain_name'] = $domain_name;
		$data['mjschool_licence_key'] = $licence_key;
		$data['enter_email'] = $email;
		$result              = mjschool_check_product_key( $domain_name, $licence_key, $email );
		
		// Initialize session variables safely
		if ( ! isset( $_SESSION ) ) {
			session_start();
		}
		
		if ( $result === 1 ) {
			$message                 = esc_html__( 'Please provide correct Envato purchase key.', 'mjschool' );
			$_SESSION['mjschool_verify'] = '1';
		} elseif ( $result === 2 ) {
			$message                 = esc_html__( 'This purchase key is already registered with a different domain. If you have any issue please contact us at sales@mojoomla.com', 'mjschool' );
			$_SESSION['mjschool_verify'] = '2';
		} elseif ( $result === 3 ) {
			$message                 = esc_html__( 'There seems to be some problem. Please try after sometime or contact us at sales@mojoomla.com', 'mjschool' );
			$_SESSION['mjschool_verify'] = '3';
		} elseif ( $result === 4 ) {
			$message                 = esc_html__( 'Please provide correct Envato purchase key for this plugin.', 'mjschool' );
			$_SESSION['mjschool_verify'] = '4';
		} else {
			update_option( 'domain_name', $domain_name, true );
			update_option( 'mjschool_licence_key', $licence_key, true );
			update_option( 'mjschool_setup_email', $email, true );
			$message                 = esc_html__( 'Successfully registered', 'mjschool' );
			$_SESSION['mjschool_verify'] = '0';
		}
		$result_array = array(
			'message'      => $message,
			'mjschool_verify'  => isset( $_SESSION['mjschool_verify'] ) ? sanitize_text_field( $_SESSION['mjschool_verify'] ) : '',
			'location_url' => $location_url,
		);
		echo wp_json_encode( $result_array );
	} else {
		if ( ! isset( $_SESSION ) ) {
			session_start();
		}
		$message                 = esc_html__( 'Server is down. Please wait some time.', 'mjschool' );
		$_SESSION['mjschool_verify'] = '3';
		$result_array            = array(
			'message'      => $message,
			'mjschool_verify'  => isset( $_SESSION['mjschool_verify'] ) ? sanitize_text_field( $_SESSION['mjschool_verify'] ) : '',
			'location_url' => $location_url,
		);
		echo wp_json_encode( $result_array );
	}
	die();
}
add_action( 'wp_ajax_mjschool_view_notice', 'mjschool_ajax_view_notice' );
/**
 * Handles AJAX request to display notice details in a popup.
 *
 * Validates nonce and user login status, fetches the notice post data,
 * and outputs HTML markup for modal display of notice details.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML and terminates execution.
 */
function mjschool_ajax_view_notice() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$notice_id = isset($_REQUEST['notice_id']) ? intval( wp_unslash($_REQUEST['notice_id']) ) : 0;
	$notice = get_post( $notice_id );
	?>
	<div class="form-group mjschool-popup-header-marging">
		
		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-notice.png"); ?>" class="mjschool-popup-image-before-name">
		<a href="#" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title" id="myLargeModalLabel"><?php esc_html_e( 'Notice Detail', 'mjschool' ); ?></h4>
		
	</div>
	<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
		<div class="row">
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value"><?php echo esc_html( $notice->post_title ); ?></label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $notice->ID, 'start_date', true ) ) ); ?>
					<?php esc_html_e( 'To', 'mjschool' ); ?>
					<?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $notice->ID, 'end_date', true ) ) ); ?>
				</label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Notice For', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value"><?php echo esc_html( ucfirst( get_post_meta( $notice->ID, 'notice_for', true ) ) ); ?></label>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Class Name', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value">
					<?php
					$class_id_meta = get_post_meta( $notice->ID, 'smgt_class_id', true );
					if ( ! empty( $class_id_meta ) && $class_id_meta === 'all' ) {
						esc_html_e( 'All', 'mjschool' );
					} elseif ( ! empty( $class_id_meta ) ) {
						echo esc_html( mjschool_get_class_name( $class_id_meta ) );
					}
					?>
				</label>
			</div>
			<div class="col-md-12 mjschool-popup-padding-15px">
				<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Comment', 'mjschool' ); ?></label><br>
				<label class="mjschool-label-value">
					<?php
					if ( ! empty( $notice->post_content ) ) {
						echo esc_html( $notice->post_content );
					} else {
						esc_html_e( 'N/A', 'mjschool' );
					}
					?>
				</label>
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_service_setting', 'mjschool_service_setting' );
/**
 * Handles AJAX request for loading SMS service settings fields dynamically.
 *
 * Based on the selected SMS provider (Clickatell or MSG91), this function
 * returns the appropriate HTML form fields with saved option values.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML and terminates execution.
 */
function mjschool_service_setting() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$select_serveice = sanitize_email(wp_unslash($_POST['select_serveice']));
	if ( $select_serveice === 'clickatell' ) {
		$clickatell = get_option( 'mjschool_clickatell_mjschool_service' );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="api_key" class="form-control validate[required]" type="text" value="<?php if ( isset( $clickatell['api_key'] ) ) { echo esc_attr( $clickatell['api_key'] );} ?>" name="api_key">
							<label class="active" for="api_key"><?php esc_html_e( 'API Key', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	if ( $select_serveice === 'msg91' ) {
		$msg91 = get_option( 'mjschool_msg91_mjschool_service' );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="mjschool_auth_key" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $msg91['mjschool_auth_key'] ); ?>" name="mjschool_auth_key">
							<label class="active" for="mjschool_auth_key"><?php esc_html_e( 'Authentication Key', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="msg91_senderID" class="form-control validate[required] text-input" type="text" name="msg91_senderID" value="<?php echo esc_attr( $msg91['msg91_senderID'] ); ?>">
							<label class="active" for="msg91_senderID"><?php esc_html_e( 'SenderID ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="wpnc_mjschool_route" class="form-control validate[required] text-input" type="text" name="wpnc_mjschool_route" value="<?php echo esc_attr( $msg91['wpnc_mjschool_route'] ); ?>">
							<label class="active" for="wpnc_mjschool_route"><?php esc_html_e( 'Route', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	die();
}
add_action( 'wp_ajax_mjschool_student_invoice_view', 'mjschool_student_invoice_view' );
/**
 * Handles AJAX request to display invoice, income, or expense details in a popup.
 *
 * Validates nonce and user login status, retrieves invoice-related data,
 * formats invoice details, and outputs the full invoice HTML preview.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML and terminates execution.
 */
function mjschool_student_invoice_view() {
	echo 'hello'; die;
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$obj_invoice = new Mjschool_Invoice();
	if ( sanitize_email(wp_unslash($_POST['invoice_type'])) === 'invoice' ) {
		$invoice_data = mjschool_get_payment_by_id( intval(wp_unslash($_POST['idtest'])) );
	}
	if ( sanitize_email(wp_unslash($_POST['invoice_type'])) === 'income' ) {
		$income_data = $obj_invoice->mjschool_get_income_data( intval(wp_unslash($_POST['idtest'])) );
	}
	if ( sanitize_email(wp_unslash($_POST['invoice_type'])) === 'expense' ) {
		$expense_data = $obj_invoice->mjschool_get_income_data( intval(wp_unslash($_POST['idtest'])) );
	}
	?>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
		
		<a href="javascript:void(0);" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </h4>
	</div>
	<div class="modal-body mjschool-invoice-model-body mjschool-float-left-width-100px mjschool_height_380px">
		<img class="mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url(plugins_url( '/mjschool/assets/images/listpage-icon/mjschool-invoice.png' ) ); ?>" width="100%">
		<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
			<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
				<div class="row">
					<div class="col-md-1 col-sm-2 col-xs-3">
						<div class="width_1">
							<img class="system_logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
						</div>
					</div>
					
					<div class="col-md-11 col-sm-10 col-xs-9 mjschool-invoice-address mjschool-invoice-address-css">
						<div class="row">
							<div class="col-md-1 col-sm-2 col-xs-3 mjschool-address-css mjschool-padding-right-0">
								<label class="mjschool-popup-label-heading">
									<?php
									esc_html_e( 'Address :', 'mjschool' );
									$address_length = strlen( get_option( 'mjschool_address' ) );
									if ( $address_length > 120 ) {
										?>
										<BR><BR><BR><BR><BR>
										<?php
									} elseif ( $address_length > 90 ) {
										?>
										<BR><BR><BR><BR>
										<?php
									} elseif ( $address_length > 60 ) {
										?>
										<BR><BR><BR>
										<?php
									} elseif ( $address_length > 30 ) {
										?>
										<BR><BR>
										<?php
									}
									?>
								</label>
							</div>
							<div class="col-md-9 col-sm-8 col-xs-7">
								<label class="mjschool-label-value"> 
									<?php
									echo wp_kses_post( nl2br( esc_html( chunk_split( get_option( 'mjschool_address' ), 42 ) ) ) );
									?>
								</label>
							</div>
						</div>
						<div class="row mjschool-invoice-padding-bottom-15px">
							<div class="col-md-1 col-sm-2 col-xs-3 mjschool-address-css mjschool-padding-right-0">
								<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Email :', 'mjschool' ); ?> </label>
							</div>
							<div class="col-md-10 col-sm-8 col-xs-7">
								<label class="mjschool-label-value"><?php echo esc_html( get_option( 'mjschool_email' ) ), '<BR>'; ?></label>
							</div>
						</div>
						<div class="row mjschool-invoice-padding-bottom-15px">
							<div class="col-md-1 col-sm-2 col-xs-3 mjschool-address-css mjschool-padding-right-0">
								<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Phone :', 'mjschool' ); ?> </label>
							</div>
							<div class="col-md-10 col-sm-8 col-xs-7">
								<label class="mjschool-label-value"><?php echo esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>'; ?></label>
							</div>
						</div>
						<div align="right" class="mjschool-width-24px"></div>
					</div>
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xl-12 mjschool-mozila-display-css">
				<div class="row">
					<div class="mjschool-width-50px mjschool-float-left-width-100px">
						<div class="col-md-8 col-sm-8 col-xs-5 mjschool-custom-padding-0 mjschool-float-left mjschool-display-grid mjschool-margin-bottom-20px">
							<div class="mjschool-billed-to mjschool-display-flex mjschool-invoice-address-heading">
								<?php
								$issue_date = 'DD-MM-YYYY';
								if ( ! empty( $income_data ) ) {
									$issue_date     = $income_data->income_create_date;
									$payment_status = $income_data->payment_status;
								}
								if ( ! empty( $invoice_data ) ) {
									$issue_date     = $invoice_data->date;
									$payment_status = $invoice_data->payment_status;
								}
								if ( ! empty( $expense_data ) ) {
									$issue_date     = $expense_data->income_create_date;
									$payment_status = $expense_data->payment_status;
								}
								?>
								<h3 class="mjschool-billed-to-lable mjschool-invoice-model-heading mjschool_20px"> <?php esc_html_e( 'Bill To', 'mjschool' ); ?> : </h3>
								<?php
								if ( ! empty( $expense_data ) ) {
									$party_name         = $expense_data->supplier_name;
									$escaped_party_name = esc_html( ucwords( $party_name ) );
									$split_party_name   = chunk_split( $escaped_party_name, 30, '<br>' );
									echo "<h3 class='display_name mjschool-invoice-width-100px'>" . wp_kses_post( $split_party_name ) . '</h3>';
								} else {
									if ( ! empty( $income_data ) ) {
										$student_id = $income_data->supplier_name;
									}
									if ( ! empty( $invoice_data ) ) {
										$student_id = $invoice_data->student_id;
									}
									$patient              = get_userdata( $student_id );
									$escaped_display_name = esc_html( ucwords( $patient->display_name ) );
									$split_display_name   = chunk_split( $escaped_display_name, 30, '<br>' );
									echo "<h3 class='display_name mjschool-invoice-width-100px'>" . wp_kses_post( $split_display_name ) . '</h3>';
								}
								?>
							</div>
							<div class="mjschool-width-60px mjschool-address-information-invoice">
								<?php
								if ( ! empty( $expense_data ) ) {
									$party_name           = $expense_data->supplier_name;
									$escaped_display_name = esc_html( ucwords( $party_name ) );
									$split_display_name   = chunk_split( $escaped_display_name, 30, '<br>' );
									echo "<h3 class='display_name mjschool-invoice-width-100px'>" . wp_kses_post( $split_display_name ) . '</h3>';
								} else {
									if ( ! empty( $income_data ) ) {
										$student_id = $income_data->supplier_name;
									}
									if ( ! empty( $invoice_data ) ) {
										$student_id = $invoice_data->student_id;
									}
									$patient         = get_userdata( $student_id );
									$address         = get_user_meta( $student_id, 'address', true );
									$escaped_address = esc_html( $address );
									$split_address   = chunk_split( $escaped_address, 30, '<br>' );
									echo wp_kses_post( $split_address );
									echo esc_html( get_user_meta( $student_id, 'city', true ) ) . ',' . '<BR>';
									echo esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . ',<BR>';
								}
								?>
							</div>
						</div>
						<div class="col-md-3 col-sm-4 col-xs-7 mjschool-float-left">
							<div class="mjschool-width-50px">
								<div class="mjschool-width-20px" align="center">
									<?php
									if ( ! empty( $invoice_data ) ) {
									}
									?>
									<h5 class="mjschool-align-left"> 
										<label class="mjschool-popup-label-heading text-transfer-upercase"><?php echo esc_html__( 'Date :', 'mjschool' ); ?> </label>&nbsp; 
										<label class="mjschool-invoice-model-value"><?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?></label>
									</h5>
									<h5 class="mjschool-align-left"><label class="mjschool-popup-label-heading text-transfer-upercase"><?php echo esc_html__( 'Status :', 'mjschool' ); ?>
										</label> &nbsp;<label class="mjschool-invoice-model-value">
										<?php
										if ( $payment_status === 'Paid' ) {
											echo '<span class="mjschool-green-color">' . esc_attr__( 'Fully Paid', 'mjschool' ) . '</span>';
										}
										if ( $payment_status === 'Part Paid' ) {
											echo '<span class="mjschool-purpal-color">' . esc_attr__( 'Partially Paid', 'mjschool' ) . '</span>';
										}
										if ( $payment_status === 'Unpaid' ) {
											echo '<span class="mjschool-red-color">' . esc_attr__( 'Not Paid', 'mjschool' ) . '</span>';
										}
										?>
									</h5>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<table class="mjschool-width-100px">
				<tbody>
					<tr>
						<td>
							<?php
							if ( ! empty( $invoice_data ) ) {
								?>
								<h3 class="display_name"><?php esc_html_e( 'Invoice Entries', 'mjschool' ); ?></h3>
								<?php
							} elseif ( ! empty( $income_data ) ) {
								?>
								<h3 class="display_name"><?php esc_html_e( 'Income Entries', 'mjschool' ); ?></h3>
								<?php
							} elseif ( ! empty( $expense_data ) ) {
								?>
								<h3 class="display_name"><?php esc_html_e( 'Expense Entries', 'mjschool' ); ?></h3>
								<?php
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="table mjschool-model-invoice-table">
				<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading">
					<tr>
						<th class="mjschool-entry-table-heading mjschool-align-center">#</th>
						<th class="mjschool-entry-table-heading mjschool-align-center"> <?php esc_html_e( 'Date', 'mjschool' ); ?></th>
						<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Entry', 'mjschool' ); ?></th>
						<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Price', 'mjschool' ); ?></th>
						<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Issue By', 'mjschool' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$id           = 1;
					$total_amount = 0;
					if ( ! empty( $income_data ) || ! empty( $expense_data ) ) {
						if ( ! empty( $expense_data ) ) {
							$income_data = $expense_data;
						}
						$patient_all_income = $obj_invoice->mjschool_get_onepatient_income_data( $income_data->supplier_name );
						foreach ( $patient_all_income as $result_income ) {
							$income_entries = json_decode( $result_income->entry );
							foreach ( $income_entries as $each_entry ) {
								$total_amount += $each_entry->amount;
								?>
								<tr>
									<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $id ); ?></td>
									<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( $result_income->income_create_date ); ?></td>
									<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $each_entry->entry ); ?> </td>
									<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $each_entry->amount, 2, '.', '' ) ) ); ?> </td>
									<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( mjschool_get_display_name( $result_income->create_by ) ); ?></td>
								</tr>
								<?php
								$id += 1;
							}
						}
					}
					if ( ! empty( $invoice_data ) ) {
						$total_amount = $invoice_data->amount
						?>
						<tr>
							<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $id ); ?></td>
							<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( date( 'Y-m-d', strtotime( $invoice_data->date ) ) ); ?></td>
							<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $invoice_data->payment_title ); ?> </td>
							<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $invoice_data->amount, 2, '.', '' ) ) ); ?> </td>
							<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_get_display_name( $invoice_data->payment_reciever_id ) ); ?> </td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php
			if ( ! empty( $invoice_data ) ) {
				$grand_total = $total_amount;
			}
			if ( ! empty( $income_data ) ) {
				$grand_total = $total_amount;
			}
			?>
			<div class="row col-md-12 grand_total_main_div mjschool-margin-top-20px">
				<div class="row col-md-6 col-sm-6 col-xs-6 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
					
					<div class="col-md-2 mjschool-print-btn-rs">
						<a href="?page=mjschool_payment&print=print&invoice_id=<?php echo esc_attr(intval(wp_unslash($_POST['idtest']))); ?>&invoice_type=<?php echo esc_attr(sanitize_text_field(wp_unslash($_POST['invoice_type']))); ?>" target="_blank" class="btn mjschool-color-white btn mjschool-save-btn mjschool-invoice-btn-div"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-print.png"); ?>"> </a>
					</div>
					<div class="col-md-3 mjschool-pdf-btn-rs">
						<a href="?page=mjschool_payment&print=pdf&invoice_id=<?php echo esc_attr(intval(wp_unslash($_POST['idtest']))); ?>&invoice_type=<?php echo esc_attr(sanitize_text_field(wp_unslash($_POST['invoice_type']))); ?>" target="_blank" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-pdf.png"); ?>"></a>
					</div>
					
				</div>
				<div class="row col-md-6 col-sm-6 col-xs-6 mjschool-view-invoice-lable-css mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total mjschool_float_right" >
					<div class="mjschool-align-right col-md-6 col-sm-6 col-xs-6 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
						<h3 class="padding mjschool-color-white margin mjschool-invoice-total-label"> <?php esc_html_e( 'Grand Total', 'mjschool' ); ?> </h3>
					</div>
					<div class="mjschool-align-right col-md-6 col-sm-6 col-xs-6 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
						<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value mjschool_float_right" >
							<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $grand_total, 2, '.', '' ) ) ); ?>
						</h3>
					</div>
				</div>
			</div>
			<div class="mjschool-margin-top-20px"></div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_student_add_payment', 'mjschool_student_add_payment' );
/**
 * Handle student payment add form (AJAX).
 *
 * Validates nonce, ensures user login, fetches fee details, 
 * and returns the payment popup form with dynamic fields.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML for the modal and terminates with wp_die().
 */
function mjschool_student_add_payment() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$obj_feespayment = new Mjschool_Feespayment();
	$fees_pay_id     = intval(wp_unslash($_POST['idtest']));
	$due_amount      = sanitize_text_field(wp_unslash($_POST['due_amount']));
	$student_id      = sanitize_text_field(wp_unslash($_POST['student_id']));
	$max_due_amount  = str_replace( ',', '', sanitize_text_field(wp_unslash($_POST['due_amount'])) );
	$fee_data        = $obj_feespayment->mjschool_get_single_fee_mjschool_payment( $fees_pay_id );
	$fees_id         = explode( ',', $fee_data->fees_id );
	$fees_type       = array();
	foreach ( $fees_id as $id ) {
		$fees_type[] = mjschool_get_fees_term_name( $id );
	}
	$fees_types = esc_html( implode( ' , ', $fees_type ) );
	?>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header mjschool_margin_bottom_20px" >
		
		<a href="javascript:void(0);" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></h4>
		
	</div>
	<div class="mjschool-panel-white mjschool-padding-20px">
		<form name="expense_form" action="" method="post" class="mjschool-form-horizontal" id="expense_form">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="student_id" value="<?php echo esc_attr( $student_id ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="fees_pay_id" value="<?php echo esc_attr( $fees_pay_id ); ?>">
			<input type="hidden" name="payment_status" value="paid">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<?php
					$generated_transaction_id = mjschool_generate_transaction_id();
					$show_transaction         = in_array( $payment_method, array( 'Cheque', 'Bank Transfer', 'Cash' ) );
					?>
					<input id="transaction_id" class="form-control" type="hidden" name="trasaction_id" value="<?php echo esc_attr( $generated_transaction_id ); ?>" readonly>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="amount" class="form-control validate[required,min[0],max[<?php echo esc_attr( $max_due_amount ); ?>],maxSize[10]] text-input" type="number" step="0.01" value="<?php echo esc_attr( $max_due_amount ); ?>" name="amount">
								<label for="amount" class="active"><?php esc_html_e( 'Paid Amount', 'mjschool' ); ?>(<?php echo esc_html( mjschool_get_currency_symbol() ); ?>)<span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="payment_method"><?php esc_html_e( 'Payment By', 'mjschool' ); ?><span class="required">*</span></label>
						<?php
						global $current_user;
						$user_roles = $current_user->roles;
						$user_role  = array_shift( $user_roles );
						?>
						<select name="payment_method" id="payment_method" class="font_transform_capitalization form-control select_height_47px">
							<?php
							if ( $user_role != 'student' and $user_role != 'parent' ) {
								?>
								<option value="Cash"><?php esc_html_e( 'Cash', 'mjschool' ); ?></option>
								<option value="Cheque"><?php esc_html_e( 'Cheque', 'mjschool' ); ?></option>
								<option value="Bank Transfer"><?php esc_html_e( 'Bank Transfer', 'mjschool' ); ?></option>
								<?php
							} elseif ( is_plugin_active( 'paymaster/paymaster.php' ) && get_option( 'mjschool_paymaster_pack' ) === 'yes' ) {
								$payment_method = get_option( 'pm_payment_method' );
								print '<option value="' . esc_attr( $payment_method ) . '" class="font_transform_capitalization">' . esc_html( $payment_method ) . '</option>';
							} else {
								?>
								<option value="PayPal"><?php esc_html_e( 'PayPal', 'mjschool' ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
								<div class="form-field">
									<textarea name="payment_note" cols="50" rows="2" class="mjschool-textarea-height-47px form-control validate[required,custom[address_description_validation]]" maxlength="250"><?php echo esc_textarea( $fees_types ); ?></textarea>
									<span class="mjschool-txt-title-label"></span>
									<label class="text-area address active"> <?php esc_html_e( 'Note', 'mjschool' ); ?><span class="mjschool-require-field">*</span> </label>
								</div>
							</div>
						</div>
					</div>
					<?php
					$date_option = get_option( 'mjschool_past_pay' );
					if ( $date_option === 'yes' ) {
						?>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="start_date_event" class="form-control" type="text" name="payment_date" value="<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); ?>">
									<label for="payment_date" class="active"> <?php esc_html_e( 'Payment Date', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php
			// --------- Get module-wise custom field data. --------------//
			$custom_field_obj = new Mjschool_Custome_Field();
			$module           = 'fee_transaction';
			$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
			?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Add Payment', 'mjschool' ); ?>" name="add_feetype_payment" class="btn btn-success mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_student_view_payment_history', 'mjschool_student_view_payment_history' );
/**
 * Display student payment history with invoice preview (AJAX).
 *
 * Validates nonce, checks user authentication, retrieves payment 
 * and invoice details, and displays a printable invoice along 
 * with payment history.
 *
 * @since 1.0.0
 *
 * @return void Outputs invoice HTML and terminates with wp_die().
 */
function mjschool_student_view_payment_history() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<?php
	if ( isset( $_REQUEST['idtest'] ) ) {
	   $fees_pay_id                = intval(wp_unslash($_REQUEST['idtest']));
	}
	$fees_detail_result         = mjschool_get_single_fees_payment_record( $fees_pay_id );
	$fees_history_detail_result = mjschool_get_payment_history_by_fees_pay_id( $fees_pay_id );
	$obj_feespayment            = new Mjschool_Feespayment();
	?>
	<div class="mjschool-background-image-print" style="background-image: url(<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-Invoice-bg.png' ); ?>);">
		<div class="modal-body">
			<div class="modal-header">
				
				<a href="#" class="close-btn-cat badge badge-success float-end"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
				<h4 class="modal-title"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></h4>
				
			</div>
			<div id="mjschool-invoice-print" class="print-box" width="100%">
				<table width="100%" border="0">
					<tbody>
						<tr>
							<td width="70%">
								
								<img class="mjschool_max_height_80px" src="<?php echo esc_html( get_option( 'mjschool_logo' ) ); ?>">
								
							</td>
							<td align="right" width="24%">
								<h5>
									<?php
									$issue_date = 'DD-MM-YYYY';
									$issue_date = $fees_detail_result->paid_by_date;
									echo esc_html__( 'Issue Date', 'mjschool' ) . ' : ' . esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) );
									?>
								</h5>
								<h5>
									<?php
									echo esc_html__( 'Status', 'mjschool' ) . ' : ';
									$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
									if ( $payment_status === 'Fully Paid' ) {
										echo "<span class='btn btn-success btn-xs' style='color: green;'>";
										echo esc_html__( 'Fully Paid', 'mjschool' );
									}
									if ( $payment_status === 'Partially Paid' ) {
										echo "<span class='btn partially_paid_button_color btn-xs' style='color: purple;'>";
										echo esc_html__( 'Partially Paid', 'mjschool' );
									}
									if ( $payment_status === 'Not Paid' ) {
										echo "<span class='btn btn-danger btn-xs' style='color: red;'>";
										echo esc_html__( 'Not Paid', 'mjschool' );
									}
									echo '</span>';
									?>
								</h5>
							</td>
						</tr>
					</tbody>
				</table>
				<hr class="mjschool-hr-margin-new mjschool-color-black">
				<table width="100%" border="0">
					<tbody>
						<tr>
							<td class="col-md-6"><h4><?php esc_html_e( 'Payment From', 'mjschool' ); ?></h4></td>
							<td class="col-md-6 pull-right mjchool_text_align_right mjchool_text_align_right" ><h4><?php esc_html_e( 'Bill To', 'mjschool' ); ?></h4></td>
						</tr>
						<tr>
							<td valign="top" class="col-md-6">
								<?php
								echo esc_html( get_option( 'mjschool_name' ) ) . '<br>';
								echo esc_html( get_option( 'mjschool_address' ) ) . ',';
								echo esc_html( get_option( 'mjschool_contry' ) ) . '<br>';
								echo esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>';
								?>
							</td>
							<td valign="top" class="col-md-6 mjchool_text_align_right">
								<?php
								$student_id = $fees_detail_result->student_id;
								$patient    = get_userdata( $student_id );
								if ( $patient ) {
									echo esc_html( $patient->display_name ) . '<br>';
									echo 'Student ID' . ' <b>' . esc_html( get_user_meta( $student_id, 'roll_id', true ) ) . '</b><br>';
									echo esc_html( get_user_meta( $student_id, 'address', true ) ) . ',';
									echo esc_html( get_user_meta( $student_id, 'city', true ) ) . ',' . '<BR>';
									echo esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . ',<BR>';
									echo esc_html( get_user_meta( $student_id, 'state', true ) ) . ',';
									echo esc_html( get_option( 'mjschool_contry' ) ) . ',';
									echo esc_html( get_user_meta( $student_id, 'mobile', true ) ) . '<br>';
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>
				<hr class="mjschool-hr-margin-new mjschool-color-black">
				<div class="table-responsive">
					<div>
						<h4 class="mjschool-invoice-entries-css"><?php esc_html_e( 'Invoice Entries', 'mjschool' ); ?></h4>
					</div>
					<table class="table table-bordered mjschool_border_collapse" width="100%" border="1" >
						<thead>
							<tr>
								<th class="text-center mjschool-padding-10px">#</th>
								<th class="text-center mjschool-padding-10px"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
								<th class="text-center mjschool-padding-10px"> <?php esc_html_e( 'Fees Type', 'mjschool' ); ?></th>
								<th class="mjschool-padding-10px"><?php esc_html_e( 'Total', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<?php
						$fees_id = explode( ',', $fees_detail_result->fees_id );
						$x       = 1;
						foreach ( $fees_id as $id ) {
							?>
							<tbody>
								<td class="text-center"> <?php echo esc_html( $x ); ?></td>
								<td class="text-center">
									<?php echo esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ); ?>
								</td>
								<td class="text-center"> <?php echo esc_html( mjschool_get_fees_term_name( $id ) ); ?></td>
								<td>
									<?php
									$amount = $obj_feespayment->mjschool_feetype_amount_data( $id );
									echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $amount, 2, '.', '' ) ) );
									?>
								</td>
							</tbody>
							<?php
							++$x;
						}
						?>
					</table>
				</div>
				<table width="100%" border="0">
					<tbody>
						<tr>
							<td align="right"><?php esc_html_e( 'Sub Total :', 'mjschool' ); ?></td>
							<td align="right"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->total_amount, 2, '.', '' ) ) ); ?> </td>
						</tr>
						<tr>
							<td width="80%" align="right"><?php esc_html_e( 'Payment Made :', 'mjschool' ); ?></td>
							<td align="right"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->fees_paid_amount, 2, '.', '' ) ) ); ?> </td>
						</tr>
						<tr>
							<td width="80%" align="right"><?php esc_html_e( 'Due Amount :', 'mjschool' ); ?></td>
							<?php $Due_amount = $fees_detail_result->total_amount - $fees_detail_result->fees_paid_amount; ?>
							<td align="right"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Due_amount, 2, '.', '' ) ) ); ?> </td>
						</tr>
					</tbody>
				</table>
				<hr class="mjschool-hr-margin-new mjschool-color-black">
				<?php if ( ! empty( $fees_history_detail_result ) ) { ?>
					<h4><?php esc_html_e( 'Payment History', 'mjschool' ); ?></h4>
					<table class="table table-bordered mjschool_border_collapse" width="100%" border="1" >
						<thead>
							<tr>
								<th class="text-center mjschool-padding-10px"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
								<th class="text-center mjschool-padding-10px"> <?php esc_html_e( 'Amount', 'mjschool' ); ?></th>
								<th class="mjschool-padding-10px"><?php esc_html_e( 'Method', 'mjschool' ); ?> </th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $fees_history_detail_result as $retrive_date ) {
								?>
								<tr>
									<td class="text-center">
										<?php echo esc_html( mjschool_get_date_in_input_box( $retrive_date->paid_by_date ) ); ?>
									</td>
									<td class="text-center">
										<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrive_date->amount, 2, '.', '' ) ) ); ?>
									</td>
									<td>
										<?php
										$data = $retrive_date->payment_method;
										echo esc_html( $data );
										?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } ?>
			</div>
			<div class="mjschool-print-button align-center">
				<?php
				$payment_id = '';

				if ( isset( $_POST['idtest'] ) ) {
					$payment_id = mjschool_encrypt_id(
						absint( wp_unslash( $_POST['idtest'] ) )
					);
				}
				?>
				<input type="button" value="<?php esc_attr_e( 'Print', 'mjschool' ); ?>" class="btn btn-success" onclick="mjschool_print_element( '#mjschool-invoice-print' )" />
				&nbsp;&nbsp;&nbsp;
				<a href="?page=mjschool_fees_payment&print=pdf&payment_id=<?php echo esc_attr( $payment_id); ?>&fee_paymenthistory=<?php echo 'fee_paymenthistory'; ?>" target="_blank" class="btn btn-success"><?php esc_html_e( 'PDF', 'mjschool' ); ?></a>
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_student_view_library_history', 'mjschool_student_view_library_history' );
/**
 * Display library book issue history for a student (AJAX).
 *
 * Validates security nonce, checks login status, retrieves 
 * library issue records for the student, and displays them in 
 * a formatted modal popup.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML for the library history modal and terminates with wp_die().
 */
function mjschool_student_view_library_history() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( isset( $_REQUEST['student_id'] ) ) {
	    $student_id = sanitize_text_field( wp_unslash($_REQUEST['student_id']) );
	}
	$booklist   = mjschool_get_student_library_book_list( $student_id );
	$student    = get_userdata( $student_id );
	$mjschool_obj_lib = new Mjschool_Library();
	?>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
		<a href="javascript:void(0);" class="mjschool-event-close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( $student->display_name ); ?></h4>
	</div>
	<div class="mjschool-panel-white mjschool-library-history-panal-white-div"><!----------  Mjschool-panel-white div.------------>
		<div class="modal-body"><!----------  Model Body div.------------>
			<div id="mjschool-invoice-print" class="table-responsive">
				<?php
				if ( ! empty( $booklist ) ) {
					?>
					<table class="table table-bordered mjschool_examhall_border_1px_center">
						<thead>
							<tr>
								<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium" ><?php esc_html_e( 'Book Title', 'mjschool' ); ?></th>
								<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Issue Date', 'mjschool' ); ?></th>
								<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Return Date', 'mjschool' ); ?></th>
								<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Accept Return Date', 'mjschool' ); ?></th>
								<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Period', 'mjschool' ); ?> </th>
								<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Overdue By', 'mjschool' ); ?> </th>
								<th class="mjschool-exam-hall-receipt-table-heading mjchool_receipt_table_head" ><?php esc_html_e( 'Fine', 'mjschool' ); ?> </th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $booklist as $retrieved_data ) {
								?>
								<tr class="mjschool_border_1px_white">
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
										<?php echo esc_html( stripslashes( $mjschool_obj_lib->mjschool_get_book_name( $retrieved_data->book_id ) ) ); ?>
									</td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
										<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->issue_date ) ); ?>
									</td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
										<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?>
									</td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
										<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->actual_return_date ) ); ?>
									</td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
										<?php echo esc_html( get_the_title( $retrieved_data->period ) ) . esc_html__( ' Days', 'mjschool' ); ?>
									</td>
									<?php
									$date1 = date_create( date( 'Y-m-d' ) );
									$date2 = date_create( date( 'Y-m-d', strtotime( $retrieved_data->end_date ) ) );
									$diff  = date_diff( $date2, $date1 );
									?>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
										<?php
										if ( $retrieved_data->actual_return_date === '' && $date1 < $date2 ) {
											echo esc_html__( '0 Days', 'mjschool' );
										} elseif ( $date2 > $date3 && $retrieved_data->actual_return_date != '' ) {
											echo esc_html__( '0 Days', 'mjschool' );
										} elseif ( $date1 > $date2 ) {
											echo esc_html( $diff->format( '%a' ) ) . esc_html__( ' Days', 'mjschool' );
										}
										?>
									</td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
										<?php echo ( $retrieved_data->fine != '' || $retrieved_data->fine != 0 ) ? esc_html( mjschool_get_currency_symbol() ) . esc_html( $retrieved_data->fine ) : 'N/A'; ?>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					<?php
				}
				?>
			</div>
		</div><!----------  Model Body div.------------>
	</div><!----------  Mjschool-panel-white div.------------>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_add_remove_fee_type', 'mjschool_add_remove_fee_type' );
add_action( 'wp_ajax_nopriv_mjschool_add_remove_fee_type', 'mjschool_add_remove_fee_type' );
/**
 * Handles adding or removing fee types based on the AJAX request.
 *
 * Validates nonce, checks login status, sanitizes inputs, and calls the handler
 * that performs the insert/delete operation for the given model and class ID.
 *
 * @since 1.0.0
 * @return void Outputs JSON response or terminates on security failure.
 */
function mjschool_add_remove_fee_type() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['model'], $_REQUEST['class_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$model    = sanitize_text_field( wp_unslash($_REQUEST['model']) );
	$class_id = sanitize_text_field( wp_unslash($_REQUEST['class_id']) );
	mjschool_add_category_type( $model, $class_id );
}
add_action( 'wp_ajax_mjschool_add_fee_type', 'mjschool_add_fee_type' );
/**
 * Adds a new fee type, book category, rack type, period type, or class section
 * depending on the requested model. Returns generated HTML row and <option>
 * markup for dynamically updating UI via AJAX.
 *
 * Includes security checks, sanitization, DB insertion, duplicate checks,
 * and constructs UI fragments for the response.
 *
 * @since 1.0.0
 * @return void Outputs JSON encoded UI data and terminates execution.
 */
function mjschool_add_fee_type() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['model'], $_REQUEST['class_id'] ,$_REQUEST['fee_type'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	global $wpdb;
	$model                 = sanitize_text_field( wp_unslash($_REQUEST['model']) );
	$class_id              = sanitize_text_field( wp_unslash($_REQUEST['class_id']) );
	$array_var             = array();
	$data['category_name'] = mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['fee_type'])) );
	$dlt_image             = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' );
	$edit_image            = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-edit.png' );
	if ( $model === 'feetype' ) {
		$obj_fees = new Mjschool_Fees();
		$obj_fees->mjschool_add_feetype( $data );
		$id = $wpdb->insert_id;
		 
		$row1 = '<div class="row mjschool-new-popup-padding" id="cat-' . $id . '"><div class="col-md-11">' . sanitize_text_field(wp_unslash($_REQUEST['fee_type'])) . '</div><div class="row col-md-1"><div class="col-md-12"><a href="#" class="btn-delete-cat" model="' . $model . '" id="' . $id . '"><img src="' . $dlt_image . '"></a></a></div></div></div>';
		 
		$option = "<option value='$id'>" . sanitize_text_field(wp_unslash( $_REQUEST['fee_type'] )) . '</option>';
	}
	if ( $model === 'book_cat' ) {
		$obj_lib    = new Mjschool_Library();
		$cat_result = $obj_lib->mjschool_add_bookcat( $data );
		$id         = $wpdb->insert_id;
		$row1       = '<tr id="cat-' . $id . '"><td>' . sanitize_text_field(wp_unslash( $_REQUEST['fee_type'] )) . '</td><td><a class="btn-delete-cat badge badge-delete" href="#" id=' . $id . '>X</a></td></tr>';
		$option     = "<option value='$id'>" . sanitize_text_field(wp_unslash( $_REQUEST['fee_type'] )) . '</option>';
	}
	if ( $model === 'rack_type' ) {
		$obj_lib    = new Mjschool_Library();
		$cat_result = $obj_lib->mjschool_add_rack( $data );
		$id         = $wpdb->insert_id;
		 
		$row1 = '<div class="row mjschool-new-popup-padding" id="cat-' . $id . '"><div class="col-md-11">' . sanitize_text_field(wp_unslash($_REQUEST['fee_type'])) . '</div><div class="row col-md-1"><div class="col-md-12"><a href="#" class="btn-delete-cat" model="' . $model . '" id="' . $id . '"><img src="' . $dlt_image . '"></a></a></div></div></div>';
		$option = "<option value='$id'>" . sanitize_text_field(wp_unslash($_REQUEST['fee_type'])) . "</option>";
	}
	if ($model === 'period_type' ) {
		$obj_lib = new Mjschool_Library();
		$cat_result = $obj_lib->mjschool_add_period($data);
		$id = $wpdb->insert_id;
		$row1 = '<div class="row mjschool-new-popup-padding" id="cat-' . $id . '"><div class="col-md-11">' . sanitize_text_field(wp_unslash($_REQUEST['fee_type']) ). ' ' . esc_attr__( 'Days', 'mjschool' ) . '</div><div class="row col-md-1"><div class="col-md-12"><a href="#" class="btn-delete-cat" model="' . $model . '" id="' . $id . '"><img src="' . $dlt_image . '"></a></a></div></div></div>';
		$option = "<option value='$id'>" . sanitize_text_field(wp_unslash($_REQUEST['fee_type'])) . " " . esc_attr__( 'Days', 'mjschool' ) . "</option>";
		 
	}
	if ( $model === 'class_sec' ) {
		$error    = '';
		$class_id = isset($_REQUEST['class_id']) ? sanitize_text_field(wp_unslash( $_REQUEST['class_id'])) : '';
		$section  = isset($_REQUEST['fee_type']) ? sanitize_text_field(wp_unslash($_REQUEST['fee_type'])) : '';
		global $wpdb;
		$class_section_table = $wpdb->prefix . 'mjschool_class_section';
		// Use prepared statement to prevent SQL injection.
		$prepared_statement = $wpdb->prepare( "SELECT * FROM $class_section_table WHERE class_id = %d AND section_name = %s", $class_id, $section );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$section_list = $wpdb->get_results( $prepared_statement );
		if ( empty( $section_list ) ) {
			$sectiondata['class_id']     = $class_id;
			$sectiondata['section_name'] = $section;
			$tablename                   = 'mjschool_class_section';
			$result                      = mjschool_mjschool_add_class_section( $tablename, $sectiondata );
			$id                          = $wpdb->insert_id;
			 
			$row1 = '<div class="row mjschool-new-popup-padding" id="cat-' . $id . '"><div class="col-md-10 mjschool-width-70px">' . sanitize_text_field(wp_unslash($_REQUEST['fee_type'])) . '</div>
			<div class="row col-md-2 mjschool-padding-left-0-res mjschool-width-30px"><div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0"><a href="#" class="btn-delete-cat" model="' . $model . '" id="' . $id . '"><img src="' . $dlt_image . '"></a></div>
			<div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-right-0"><a class="mjschool-btn-edit-cat"  model="' . $model . '" href="#" id="' . $id . '"><img src="' . $edit_image . '"></a></div>
			</div></div>';
			 
			$option = "<option value='$id'>" . sanitize_text_field(wp_unslash( $_REQUEST['fee_type'] )) . '</option>';
		} else {
			$error = 1;
		}
	}
	$array_var[]  = $row1;
	$array_var[]  = $option;
	$array_var[2] = $error;
	echo json_encode( $array_var );
	die();
}
add_action( 'wp_ajax_mjschool_remove_fee_type', 'mjschool_remove_fee_type' );
add_action( 'wp_ajax_nopriv_mjschool_remove_fee_type', 'mjschool_remove_fee_type' );
/**
 * Removes a fee type, book category, rack type, period, or class section.
 *
 * Performs security validations including login, role permissions, nonce
 * verification, and ID validation. Uses appropriate model methods to delete
 * entries and returns JSON success or error response.
 *
 * @since 1.0.0
 * @return void Sends JSON success or error response and terminates.
 */
function mjschool_remove_fee_type() {
	// Check if user is logged in.
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Unauthorized access' ), 403 );
		die();
	}
	// Check user role.
	$role          = mjschool_get_user_role( get_current_user_id() );
	$allowed_roles = array( 'management', 'administrator', 'supportstaff', 'teacher' );
	if ( ! in_array( $role, $allowed_roles ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
		die();
	}
	// Verify nonce.
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'mjschool_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid security token' ), 403 );
		die();
	}
	// Validate and sanitize inputs.
	if ( ! isset( $_POST['cat_id'] ) || ! is_numeric( $_POST['cat_id'] ) ) {
		wp_send_json_error( array( 'message' => 'Invalid category ID' ), 400 );
		die();
	}
	$cat_id = intval( wp_unslash($_POST['cat_id']) );
	$model  = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash($_POST['model']) ) : '';
	// Perform deletion based on model type.
	switch ( $model ) {
		case 'feetype':
			$obj_fees = new Mjschool_Fees();
			$deleted  = $obj_fees->mjschool_delete_fee_type( $cat_id );
			break;
		case 'book_cat':
		case 'rack_type':
		case 'period_type':
			$obj_lib = new Mjschool_Library();
			$method  = 'mjschool_delete_' . str_replace( '_', '', $model );
			$deleted = method_exists( $obj_lib, $method ) ? $obj_lib->$method( $cat_id ) : false;
			break;
		case 'class_sec':
			$deleted = mjschool_delete_class_section( $cat_id );
			break;
		default:
			wp_send_json_error( array( 'message' => 'Invalid model type' ), 400 );
			die();
	}
	// Return response.
	if ( $deleted ) {
		wp_send_json_success( array( 'message' => 'Deleted successfully' ) );
	} else {
		wp_send_json_error( array( 'message' => 'Failed to delete item' ), 500 );
	}
	die();
}
add_action( 'wp_ajax_mjschool_update_section', 'mjschool_update_section' );
/**
 * Updates a class section name based on user input from the AJAX request.
 *
 * Validates nonce, checks user authentication, updates the section in the
 * database, retrieves updated data, and outputs refreshed HTML markup.
 *
 * @since 1.0.0
 * @return void Outputs updated HTML block for the modified section.
 */
function mjschool_update_section() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_POST['section_name'], $_POST['cat_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$model = '';
	global $wpdb;
	$mjschool_class_section = $wpdb->prefix . 'mjschool_class_section';
	$data['section_name']   = sanitize_text_field(wp_unslash($_POST['section_name']));
	$data_id['id']          = sanitize_text_field(wp_unslash($_POST['cat_id']));
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result         = $wpdb->update( $mjschool_class_section, $data, $data_id );
	$retrieved_data = mjschool_single_section( sanitize_text_field(wp_unslash($_POST['cat_id'])) );
	?>
	<div class="col-md-10 mjschool-width-70px">
		<?php
		echo esc_html( $retrieved_data->section_name );
		?>
	</div>
	<div class="row col-md-2 mjschool-padding-left-0-res mjschool-width-30px" id="<?php echo esc_attr( $retrieved_data->id ); ?>">
		
		<div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0">
			<a href="#" class="btn-delete-cat" model="<?php echo esc_attr($model); ?>" id="<?php echo esc_attr($retrieved_data->id); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></a>
		</div>
		<div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-right-0">
			<a class="mjschool-btn-edit-cat" model="<?php echo esc_attr($model); ?>" href="#" id="<?php echo esc_attr($retrieved_data->id); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>"></a>
		</div>
		
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_update_cancel_section', 'mjschool_update_cancel_section' );
/**
 * Restores the original section display when a user cancels the edit action.
 *
 * Performs security validations, fetches the section data again, and outputs
 * the unchanged HTML markup for UI reset.
 *
 * @since 1.0.0
 * @return void Outputs original HTML content for the section row.
 */
function mjschool_update_cancel_section() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_POST['cat_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	global $wpdb;
	$mjschool_class_section = $wpdb->prefix . 'mjschool_class_section';
	$retrieved_data         = mjschool_single_section( sanitize_text_field(wp_unslash($_POST['cat_id'])) );
	?>
	<div class="col-md-10 mjschool-width-70px">
		<?php
		echo esc_html( $retrieved_data->section_name );
		?>
	</div>
	<div class="row col-md-2 mjschool-padding-left-0-res mjschool-width-30px" id="<?php echo esc_attr( $retrieved_data->id ); ?>">
		<div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0">
			<a href="#" class="btn-delete-cat" model="<?php echo esc_attr($model); ?>" id="<?php echo esc_attr($retrieved_data->id); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></a>
		</div>
		<div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-right-0">
			<a class="mjschool-btn-edit-cat" model="<?php echo esc_attr($model); ?>" href="#" id="<?php echo esc_attr($retrieved_data->id); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>"></a>
			
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_get_book_return_date', 'mjschool_get_book_return_date' );
/**
 * Calculates the expected return date for a library book based on the selected
 * issue period and issue date.
 *
 * Validates nonce and login, retrieves period days, adds them to the issue date,
 * and returns the formatted resulting date.
 *
 * @since 1.0.0
 * @return void Outputs formatted return date string.
 */
function mjschool_get_book_return_date() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['issue_period'], $_REQUEST['issue_date'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$period_days = get_the_title( sanitize_text_field(wp_unslash($_REQUEST['issue_period'])) );
	$date        = date_create( sanitize_text_field(wp_unslash($_REQUEST['issue_date'])) );
	$olddate     = date_format( $date, 'Y-m-d' );
	$new_date    = date( 'Y-m-d', strtotime( $olddate . ' + ' . $period_days . 'Days' ) );
	echo esc_html( mjschool_get_date_in_input_box( $new_date ) );
	die();
}
add_action( 'wp_ajax_mjschool_accept_return_book', 'mjschool_accept_return_book' );
/**
 * Displays the Return Book modal form for a library-issued book.
 *
 * Performs authentication and nonce checks, retrieves issued book details,
 * loads validation and datepicker scripts, and renders the return-book form
 * inside the AJAX response.
 *
 * @since 1.0.0
 * @return void Outputs modal HTML and terminates.
 */
function mjschool_accept_return_book() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<?php
	$id = isset($_REQUEST['idtest']) ? sanitize_text_field(wp_unslash($_REQUEST['idtest'])) : '';
	global $wpdb;
	$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$booklist = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_issuebook WHERE id = %d AND status = %s", $id, 'Issue' ) );
	?>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
		
		<a href="javascript:void(0);" class="mjschool-event-close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html__( "Confirm Return Books", "mjschool" ) ?></h4>
		
	</div>
	<div class="mjschool-panel-white mjschool-library-history-panal-white-div"><!----------  Mjschool-panel-white div.------------>
		<div class="modal-body"><!----------  Model Body div.------------>
			<div id="mjschool-invoice-print" class="mjschool-exam-table-res table-responsive">
				<?php
				if ( ! empty( $booklist ) ) {
					?>
					<form name="issue_book_return" method="post" id="mjschool-issue-book-return">
						<input type="hidden" name="issue_book_id" value="<?php echo esc_attr( $id ); ?>">
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="return_date" class="datepicker form-control validate[required] text-input" type="text" name="return_date" value="<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); ?>" readonly>
											<label class="active" for="return_date"><?php esc_html_e( 'Return Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input mjschool-rtl-margin-0px">
										<div class="col-md-12 form-control">
											<input type="number" min="0" class="validate[required,min[0],maxSize[5]] number form-control" onkeypress="return mjschool_isNumberKey(event)" name="fine" value="">
											<label class="active" for="Fine"><?php esc_html_e( 'Fine', 'mjschool' ); ?>(<?php echo esc_html( mjschool_get_currency_symbol() ); ?>)<span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group input">
										<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
											<div class="form-field">
												<textarea name="comment" cols="50" rows="2" class="mjschool-textarea-height-47px form-control validate[required,custom[address_description_validation]]" maxlength="250"></textarea>
												<span class="mjschool-txt-title-label"></span>
												<label class="text-area address active"><?php esc_html_e( 'Comment', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-6">
									<input type="submit" value="<?php esc_attr_e( 'Return Book', 'mjschool' ); ?>" name="return_book" class="btn btn-success mjschool-save-btn" />
								</div>
							</div>
						</div>
					</form>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_class_section', 'mjschool_load_class_section' );
add_action( 'wp_ajax_nopriv_mjschool_load_class_section', 'mjschool_load_class_section' );
/**
 * Loads all sections for a selected class and returns <option> HTML.
 *
 * Validates nonce and user authentication, fetches sections from DB, and prints
 * an <option> list for dynamic form population.
 *
 * @since 1.0.0
 * @return void Outputs HTML <option> tags for class sections.
 */
function mjschool_load_class_section() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	// if ( ! is_user_logged_in() ) {
	// 	wp_die( 'You must be logged in.' );
	// }
	if ( ! isset( $_POST['class_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$class_id = sanitize_text_field( wp_unslash($_POST['class_id']) );
	global $wpdb;
	$retrieve_data = mjschool_get_class_sections( sanitize_text_field(wp_unslash($_POST['class_id'])) );
	$defaultmsg    = esc_html__( 'All Section', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	foreach ( $retrieve_data as $section ) {
		echo "<option value='" . esc_attr( $section->id ) . "'>" . esc_html( $section->section_name ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_mjschool_load_student_with_status', 'mjschool_load_student_with_status' );
add_action( 'wp_ajax_nopriv_mjschool_load_student_with_status', 'mjschool_load_student_with_status' );
/**
 * Returns a default student option list for status-based student loading.
 *
 * Performs nonce and login checks, and outputs "All Student" as default option.
 *
 * @since 1.0.0
 * @return void Outputs a default <option> tag.
 */
function mjschool_load_student_with_status() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$defaultmsg = esc_html__( 'All Student', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	die();
}
add_action( 'wp_ajax_mjschool_load_class_section_add_student', 'mjschool_load_class_section_add_student' );
add_action( 'wp_ajax_nopriv_mjschool_load_class_section_add_student', 'mjschool_load_class_section_add_student' );
/**
 * Loads class sections when adding a student and returns <option> HTML.
 *
 * Validates request nonce and user authentication, retrieves class sections,
 * and prints formatted <option> elements.
 *
 * @since 1.0.0
 * @return void Outputs HTML <option> tags for class sections.
 */
function mjschool_load_class_section_add_student() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_POST['class_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$class_id = sanitize_text_field( wp_unslash($_POST['class_id']) );
	global $wpdb;
	$retrieve_data = mjschool_get_class_sections( sanitize_text_field(wp_unslash($_POST['class_id'])) );
	$defaultmsg    = esc_html__( 'Select Section', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	foreach ( $retrieve_data as $section ) {
		echo "<option value='" . esc_attr( $section->id ) . "'>" . esc_html( $section->section_name ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_nopriv_mjschool_load_section_subject', 'mjschool_load_section_subject' );
add_action( 'wp_ajax_mjschool_load_section_subject', 'mjschool_load_section_subject' );
/**
 * Loads subjects for a selected section, applying teacher access restrictions
 * if required.
 *
 * Performs security checks, determines teacher access permissions, fetches
 * subjects accordingly, and outputs an <option> list for subject dropdowns.
 *
 * @since 1.0.0
 * @return void Outputs HTML <option> tags for subjects.
 */
function mjschool_load_section_subject() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_POST['section_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$section_id = sanitize_text_field( wp_unslash($_POST['section_id']) );
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$user_id    = get_current_user_id();
	// ------------------------TEACHER ACCESS.---------------------------------//
	$teacher_access      = get_option( 'mjschool_access_right_teacher' );
	$teacher_access_data = $teacher_access['teacher'];
	foreach ( $teacher_access_data as $key => $value ) {
		if ( $key === 'subject' ) {
			$data = $value;
		}
	}
	if ( mjschool_get_roles( $user_id ) === 'teacher' && $data['own_data'] === 1 ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE teacher_id = %d AND section_id = %d", $user_id, $section_id ) );
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE section_id = %d", $section_id ) );
	}
	$defaultmsg = esc_html__( 'Select subject', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg ) . '</option>';
	foreach ( $retrieve_subject as $retrieved_data ) {
		echo '<option value=' . esc_attr( $retrieved_data->subid ) . '> ' . esc_html( $retrieved_data->sub_name ) . '-' . esc_html( $retrieved_data->subject_code ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_nopriv_mjschool_load_class_student', 'mjschool_load_class_student' );
add_action( 'wp_ajax_mjschool_load_class_student', 'mjschool_load_class_student' );
/**
 * Loads students belonging to a given class using user meta filtering.
 *
 * Validates nonce and login, retrieves users with role 'student' based on class
 * meta value, and outputs a simple response for each matched entry.
 *
 * @since 1.0.0
 * @return void Outputs text response for each student found.
 */
function mjschool_load_class_student() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['class_list'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$class_list = sanitize_text_field( wp_unslash($_REQUEST['class_list']) );
	
	$args = array(
		'role' => 'student',
		'meta_key' => 'class_name',
		'meta_value' => $class_list
	);
	
	$result = get_users( $args );
	foreach ( $result as $key => $value ) {
		print 'Yes';
	}
	die();
}
add_action( 'wp_ajax_mjschool_notification_user_list', 'mjschool_notification_user_list' );
/**
 * Handles AJAX request to retrieve user list and class sections for notifications.
 *
 * Validates nonce, ensures the user is logged in, then returns class sections
 * and user options based on selected class and section.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON response and terminates script.
 */
function mjschool_notification_user_list() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$school_obj                = new MJSchool_Management( get_current_user_id() );
	$class_list                = isset( $_REQUEST['class_list'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_list'])) : '';
	$class_section             = isset( $_REQUEST['class_section'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_section'])) : '';
	$exlude_id                 = mjschool_approve_student_list();
	$html_class_section        = '';
	$return_results['section'] = '';
	$user_list                 = array();
	global $wpdb;
	$defaultmsg         = esc_html__( 'All', 'mjschool' );
	$html_class_section = "<option value='All'>" . esc_html( $defaultmsg ) . '</option>';
	if ( $class_list != '' ) {
		$retrieve_data = mjschool_get_class_sections( $class_list );
		if ( $retrieve_data ) {
			foreach ( $retrieve_data as $section ) {
				$html_class_section .= "<option value='" . esc_attr( $section->id ) . "'>" . esc_html( $section->section_name ) . '</option>';
			}
		}
	}
	
	$query_data['exclude'] = $exlude_id;
	if ($class_section != 'All' && $class_section != '' ) {
		$query_data['meta_key'] = 'class_section';
		$query_data['meta_value'] = $class_section;
		$query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' ) );
		$results = get_users($query_data);
	} elseif ($class_list != '' ) {
		$query_data['meta_key'] = 'class_name';
		$query_data['meta_value'] = $class_list;
		$results = get_users($query_data);
	}
	if ( ! isset( $_POST['section_name'], $_POST['cat_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	if ( isset( $results ) ) {
		foreach ( $results as $user_datavalue ) {
			$user_list[] = $user_datavalue->ID;
		}
	}
	$user_data_list            = array_unique( $user_list );
	$return_results['section'] = $html_class_section;
	$return_results['users']   = '';
	$user_string               = '<select name="selected_users" id="mjschool-notification-selected-users" class="mjschool-line-height-30px form-control mjschool-max-width-100px">';
	$user_string              .= '<option value="All">' . esc_html__( 'All', 'mjschool' ) . '</option>';
	if ( ! empty( $user_data_list ) ) {
		foreach ( $user_data_list as $retrive_data ) {
			$user_string .= "<option value='" . esc_attr( $retrive_data ) . "'>" . esc_html( mjschool_student_display_name_with_roll( $retrive_data ) ) . '</option>';
		}
	}
	$user_string            .= '</select>';
	$return_results['users'] = $user_string;
	echo json_encode( $return_results );
	die();
}
add_action( 'wp_ajax_mjschool_document_user_list', 'mjschool_document_user_list' );
/**
 * Handles AJAX request to retrieve document-related user lists and sections.
 *
 * Returns HTML option lists for class sections and users based on selected
 * class and section parameters.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON response and terminates.
 */
function mjschool_document_user_list() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$school_obj                = new MJSchool_Management( get_current_user_id() );
	$class_list                = isset( $_REQUEST['class_list'] ) ? sanitize_text_field( wp_unslash($_REQUEST['class_list']) ) : '';
	$class_section             = isset( $_REQUEST['class_section'] ) ? sanitize_text_field( wp_unslash($_REQUEST['class_section']) ) : '';
	$exlude_id                 = mjschool_approve_student_list();
	$html_class_section        = '';
	$return_results['section'] = '';
	$user_list                 = array();
	global $wpdb;
	$defaultmsg         = esc_attr__( 'All Section', 'mjschool' );
	$html_class_section = "<option value='all section'>" . $defaultmsg . '</option>';
	if ( $class_list != '' ) {
		$retrieve_data = mjschool_get_class_sections( $class_list );
		if ( $retrieve_data ) {
			foreach ( $retrieve_data as $section ) {
				$html_class_section .= "<option value='" . esc_attr( $section->id ) . "'>" . esc_html( $section->section_name ) . '</option>';
			}
		}
	}
	
	$query_data['exclude'] = $exlude_id;
	if ($class_section != 'All' && $class_section != '' ) {
		$query_data['meta_key'] = 'class_section';
		$query_data['meta_value'] = $class_section;
		$query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' ) );
		$results = get_users($query_data);
	} elseif ($class_list != '' ) {
		$query_data['meta_key'] = 'class_name';
		$query_data['meta_value'] = $class_list;
		$results = get_users($query_data);
	}
	
	if ( isset( $results ) ) {
		foreach ( $results as $user_datavalue ) {
			$user_list[] = $user_datavalue->ID;
		}
	}
	$user_data_list            = array_unique( $user_list );
	$return_results['section'] = $html_class_section;
	$return_results['users']   = '';
	$user_string               = '<select name="selected_users" id="mjschool-notification-selected-users" class="mjschool-line-height-30px form-control mjschool-max-width-100px">';
	$user_string              .= '<option value="all student">' . esc_html__( 'All Student', 'mjschool' ) . '</option>';
	if ( ! empty( $user_data_list ) ) {
		foreach ( $user_data_list as $retrive_data ) {
			$user_string .= "<option value='" . esc_attr( $retrive_data ) . "'>" . esc_html( mjschool_student_display_name_with_roll( $retrive_data ) ) . '</option>';
		}
	}
	$user_string            .= '</select>';
	$return_results['users'] = $user_string;
	echo json_encode( $return_results );
	die();
}
add_action( 'wp_ajax_mjschool_class_by_teacher', 'mjschool_class_by_teacher' );
/**
 * AJAX handler to fetch classes assigned to a specific teacher.
 *
 * Validates request and returns class dropdown options for the given teacher ID.
 *
 * @since 1.0.0
 *
 * @return void Echoes option tags and terminates.
 */
function mjschool_class_by_teacher() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$teacher_id  = isset($_REQUEST['teacher_id']) ? sanitize_text_field( wp_unslash($_REQUEST['teacher_id']) ) : '';
	$teacher_obj = new Mjschool_Teacher();
	$classes     = $teacher_obj->mjschool_get_class_by_teacher( $teacher_id );
	foreach ( $classes as $class ) {
		$classdata = mjschool_get_class_by_id( $class['class_id'] );
		echo '<option value="' . esc_attr( $class['class_id'] ) . '">' . esc_html( $classdata->class_name ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_mjschool_teacher_by_class', 'mjschool_teacher_by_class' );
/**
 * Handles AJAX request to fetch teachers assigned to a specific class.
 *
 * Returns <option> tags of all teachers mapped with the provided class ID.
 *
 * @since 1.0.0
 *
 * @return void Echoes option list and exits.
 */
function mjschool_teacher_by_class() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id    = isset($_REQUEST['class_id']) ? sanitize_text_field( wp_unslash($_REQUEST['class_id'] )) : '';
	$teacher_obj = new Mjschool_Teacher();
	$classes     = $teacher_obj->mjschool_get_class_teacher( $class_id );
	foreach ( $classes as $class ) {
		echo '<option value="' . esc_attr( $class['teacher_id'] ) . '">' . esc_html( mjschool_get_user_name_by_id( $class['teacher_id'] ) ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_mjschool_sender_user_list', 'mjschool_sender_user_list' );
/**
 * AJAX handler to generate user list based on selected sender role.
 *
 * Handles multiple role types (student, teacher, parent, staff, admin) and
 * applies class/section filters where applicable.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON containing section list and user list HTML.
 */
function mjschool_sender_user_list() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$school_obj                = new MJSchool_Management( get_current_user_id() );
	$login_user_role           = $school_obj->role;
	$role                      = isset($_REQUEST['send_to']) ? sanitize_text_field(wp_unslash($_REQUEST['send_to'])) : '';
	$login_user_role           = $school_obj->role;
	$class_list                = isset( $_REQUEST['class_list'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_list'])) : '';
	$class_section             = isset( $_REQUEST['class_section'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_section'])) : '';
	$query_data['role']        = $role;
	$exlude_id                 = mjschool_approve_student_list();
	$html_class_section        = '';
	$return_results['section'] = '';
	$user_list                 = array();
	global $wpdb;
	$defaultmsg         = esc_html__( 'All Section', 'mjschool' );
	$html_class_section = "<option value=''>" . $defaultmsg . '</option>';
	if ( $class_list != '' ) {
		$retrieve_data = mjschool_get_class_sections( $class_list );
		if ( $retrieve_data ) {
			foreach ( $retrieve_data as $section ) {
				$html_class_section .= "<option value='" . esc_attr( $section->id ) . "'>" . esc_html( $section->section_name ) . '</option>';
			}
		}
	}
	
	if ($role === 'student' ) {
		$query_data['exclude'] = $exlude_id;
		if ($class_section) {
			$query_data['meta_key'] = 'class_section';
			$query_data['meta_value'] = $class_section;
			$query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' ) );
			$results = get_users($query_data);
		} elseif ($class_list != '' ) {
			$query_data['meta_key'] = 'class_name';
			$query_data['meta_value'] = $class_list;
			$results = get_users($query_data);
		} else {
			if ($login_user_role === "parent") {
				$parentdata = get_user_meta(get_current_user_id(), 'child', true);
				foreach ($parentdata as $key => $val) {
					$studentdata[] = get_userdata($val);
				}
				$results = $studentdata;
			}
			if ($login_user_role === "teacher") {
				$teacher_class_data = mjschool_get_all_teacher_data(get_current_user_id( ) );
				foreach ($teacher_class_data as $data_key => $data_val) {
					$course_id[] = $data_val->class_id;
					$query_data['meta_key'] = 'class_name';
					$query_data['meta_value'] = $course_id;
					$result = get_users($query_data);
				}
				$results = $result;
			}
		}
	}
	
	if ( $role === 'teacher' ) {
		if ( $class_list != '' ) {
			global $wpdb;
			$table_mjschool_teacher_class = $wpdb->prefix . 'mjschool_teacher_class';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$teacher_list = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM $table_mjschool_teacher_class WHERE class_id = %d", $class_list )
			);
			if ( $teacher_list ) {
				foreach ( $teacher_list as $teacher ) {
					$user_list[] = $teacher->teacher_id;
				}
			}
		} else {
			$results = get_users( $query_data );
		}
	}
	if ( $role === 'supportstaff' || $role === 'administrator' ) {
		$results = get_users( $query_data );
	}
	if ( $role === 'parent' ) {
		if ( $class_list === '' ) {
			$results = get_users( $query_data );
		} else {
			
			$query_data['role'] = 'student';
			$query_data['exclude'] = $exlude_id;
			if ($class_section) {
				$query_data['meta_key'] = 'class_section';
				$query_data['meta_value'] = $class_section;
				$query_data['meta_query'] = array(
					array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' )
				);
			} elseif ($class_list != '' ) {
				$query_data['meta_key'] = 'class_name';
				$query_data['meta_value'] = $class_list;
			}
			
			$userdata = get_users( $query_data );
			foreach ( $userdata as $users ) {
				$parent = get_user_meta( $users->ID, 'parent_id', true );
				if ( ! empty( $parent ) ) {
					foreach ( $parent as $p ) {
						$user_list[] = $p;
					}
				}
			}
		}
	}
	if ( isset( $results ) ) {
		foreach ( $results as $user_datavalue ) {
			$user_list[] = $user_datavalue->ID;
		}
	}
	$user_data_list            = array_unique( $user_list );
	$return_results['section'] = $html_class_section;
	$return_results['users']   = '';
	$user_string               = '<select name="selected_users[]" id="selected_users" class="form-control" multiple="true">';
	if ( ! empty( $user_data_list ) ) {
		foreach ( $user_data_list as $retrive_data ) {
			if ( $retrive_data != get_current_user_id() ) {
				$check_data = mjschool_get_user_name_by_id( $retrive_data );
				if ( $check_data != '' ) {
					$user_string .= "<option value='" . esc_attr( $retrive_data ) . "'>" . esc_html( mjschool_get_user_name_by_id( $retrive_data ) ) . '</option>';
				}
			}
		}
	}
	$user_string            .= '</select>';
	$return_results['users'] = $user_string;
	echo json_encode( $return_results );
	die();
}
add_action( 'wp_ajax_mjschool_frontend_sender_user_list', 'mjschool_frontend_sender_user_list' );
add_action( 'wp_ajax_mjschool_change_profile_photo', 'mjschool_change_profile_photo' );
/**
 * Displays a modal form allowing a user to upload and change profile photo.
 *
 * Ensures nonce and login checks before rendering the upload form HTML.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML modal and terminates.
 */
function mjschool_change_profile_photo() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	 ?>
	<div class="modal-header mb-4"> <a href="#" class="close-btn-cat badge badge-danger pull-right">
		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title update_profile_title"><?php esc_html_e( 'Update Profile Picture', 'mjschool' ); ?></h4>
	</div>
	
	<form class="mjschool-form-horizontal" action="#" method="post" enctype="multipart/form-data">
		<div class="form-body mjschool-user-form"> <!--Form Body div.-->
			<div class="row"><!--Row Div.-->
				<div class="col-md-8">
					<div class="form-group input">
						<div class="col-md-12 form-control mjschool-image-upload-popup-account mjschool-res-rtl-height-50px">
							<label for="inputEmail" class="mjschool-label-margin-left-10px mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px"><?php esc_html_e( 'Select Profile Picture', 'mjschool' ); ?></label>
							<div class="col-sm-12">
								<input id="input-1" name="profile" type="file" onchange="mjschool_mjschool_file_check(this);" class="mjschool-line-height-26px file profile_file d-inline">
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<button type="submit" class="btn btn-success mjschool-save-upload-profile-btn mjschool-save-btn" name="save_profile_pic"><?php esc_html_e( 'Save', 'mjschool' ); ?></button>
				</div>
			</div>
		</div>
	</form>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_assign_route', 'mjschool_assign_route' );
/**
 * AJAX handler for assigning transport routes to users.
 *
 * Loads assigned users and displays a form with multi-select options to
 * assign/update transportation routes.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML modal content and exits.
 */
function mjschool_assign_route() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$transport_id          = isset($_REQUEST['record_id']) ? sanitize_text_field(wp_unslash($_REQUEST['record_id'])) : '';
	$assign_transport_data = mjschool_get_assign_transport_by_id( $transport_id );
	$teacher_obj           = new Mjschool_Teacher();
	?>
	<div class="form-group mjschool-popup-header-marging">
		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-transportation.png"); ?>" class="mjschool-popup-image-before-name">
		<a href="#" class="close-btn-cat badge badge-danger pull-right"> <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title" id="myLargeModalLabel"> <?php esc_html_e( 'Assign Route', 'mjschool' ); ?> </h4>
	</div>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!--------- Panel body. ------->
		<form name="assign_transport_form" action="#" method="post" enctype="multipart/form-data" class="mjschool-form-horizontal" id="assign_transport_form">
			<input type="hidden" value="<?php echo esc_attr( $transport_id ); ?>" name="transport_id">
			<div class="form-body mjschool-user-form"><!--User form. -->
				<div class="row"><!--Row. -->
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-single-class-div mjschool-support-staff-user-div input">
						<div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
							<span class="user_display_block">
								<select name="selected_users[]" id="selected_multiple_users" class="form-control mjschool-min-width-250px" multiple="multiple">
									<?php
									if ( ! empty( $assign_transport_data ) ) {
										$users = json_decode( $assign_transport_data->route_user );
									}
									$student_list = mjschool_get_all_student_list();
									foreach ( $student_list as $retrive_data ) {
										?>
										<option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php echo esc_attr( $teacher_obj->mjschool_in_array_r( $retrive_data->ID, $users ) ) ? 'selected' : ''; ?>>
											<?php echo esc_html( $retrive_data->display_name ); ?>
										</option>
										<?php
									}
									?>
								</select>
							</span>
							<span class="mjschool-multiselect-label">
								<label class="ml-1 mjschool-custom-top-label top" for="selected_multiple_users"><?php esc_html_e( 'Select Users', 'mjschool' ); ?><span class="required">*</span></label>
							</span>
						</div>
					</div>
					<?php wp_nonce_field( 'save_assign_transpoat_admin_nonce' ); ?>
					<div class="col-sm-3">
						<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Assign Route', 'mjschool' ); } else { esc_html_e( 'Assign Route', 'mjschool' ); } ?>" name="save_assign_route" class="btn btn-success mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_count_student_in_class', 'mjschool_count_student_in_class' );
add_action( 'wp_ajax_mjschool_count_student_in_class', 'mjschool_count_student_in_class' );
/**
 * Counts the total number of students assigned to a specific class.
 *
 * Compares student count with class capacity and returns JSON indicating
 * whether the class is full or available.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON response and terminates.
 */
function mjschool_count_student_in_class() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_POST['class_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class';
	$class_id   = sanitize_text_field(wp_unslash($_POST['class_id']));
	$student_list = count(get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student' ) ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$class_capacity_data = $wpdb->get_row( $wpdb->prepare( "SELECT class_capacity FROM $table_name WHERE class_id = %d", $class_id ) );
	$class_capacity      = intval( $class_capacity_data->class_capacity );
	$class_data          = array();
	if ( $class_capacity > $student_list ) {
		echo 'class_empt';
		$class_data[0] = 'class_empt';
	} else {
		$class_data[0] = 'class_full';
		$class_data[1] = $class_capacity;
		$class_data[2] = $student_list;
	}
	echo json_encode( $class_data );
	die();
}
add_action( 'wp_ajax_mjschool_show_event_task', 'mjschool_show_event_task' );
add_action( 'wp_ajax_nopriv_mjschool_show_event_task', 'mjschool_show_event_task' );
/**
 * Handles AJAX request to show details for different dashboard items
 * such as events, notices, exams, homework, transport, messages, etc.
 *
 * Validates nonce, checks login, retrieves details based on "model" type,
 * and renders corresponding modal HTML.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML for modal display and terminates.
 */
function mjschool_show_event_task() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['class_id'], $_REQUEST['model'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$role  = mjschool_get_user_role( get_current_user_id() );
	$id    = sanitize_text_field( wp_unslash($_REQUEST['id'] ));
	
	$model = sanitize_text_field( wp_unslash($_REQUEST['model'] ));
	
	if ( $model === 'Notification Details' ) {
		$notification_data = mjschool_get_single_notification_by_id( $id );
	}
	if ( $model === 'Noticeboard Details' ) {
		$retrieve_class_data = get_post( $id );
		if ( ! $retrieve_class_data || get_post_type( $id ) !== 'notice' ) {
			wp_send_json_error( esc_html__( 'Invalid request.', 'mjschool' ) );
		}
		// Get class restriction from post meta.
		$class_id_raw = get_post_meta( $id, 'smgt_class_id', true );
		$class_id     = ! empty( $class_id_raw ) ? $class_id_raw : 'all';
		// Get user info.
		$current_user = wp_get_current_user();
		$user_roles   = (array) $current_user->roles;
		$user_class   = get_user_meta( get_current_user_id(), 'class_name', true );
		$is_admin     = in_array( 'administrator', $user_roles );
		// Only restrict by class — unless admin.
		if ( $class_id !== 'all' && $user_class != $class_id && ! $is_admin ) {
			wp_send_json_error( esc_html__( 'You are not allowed to view this class notice.', 'mjschool' ) );
		}
	}
	if ( $model === 'Exam Details' ) {
		$exam_data = mjschool_get_exam_by_id( $id );
	}
	if ( $model === 'holiday Details' ) {
		$holiday_data = mjschool_get_holiday_by_id( $id );
	}
	if ( $model === 'Feespayment Details' ) {
		$feespayment_data  = mjschool_get_feespayment_by_id( $id );
		$page              = 'feepayment';
		$feepayment_access = mjschool_page_access_role_wise_access_right_dashboard( $page );
	}
	if ( $model === 'Class Details' ) {
		$class_data = mjschool_get_class_by_id( $id );
	}
	if ( $model === 'Message Details' ) {
		$message_data = mjschool_get_message_by_id( $id );
	}
	if ( $model === 'Event Details' ) {
		$obj_event  = new Mjschool_Event_Manage();;
		$event_data = $obj_event->mjschool_get_single_event( $id );
	}
	if ( $model === 'transport Details' ) {
		$transport_data = mjschool_get_transport_by_id( $id );
	}
	if ( $model === 'homework Details' ) {
		$homework_data = mjschool_get_homework_by_id( $id );
	}
	?>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
		<?php 
		if ($model === 'homework Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-homework.png");
		}
		if ($model === 'transport Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-transportation.png");
		}
		if ($model === 'Event Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-notice.png");
		}
		if ($model === 'Notification Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-notifications.png");
		} elseif ($model === 'Noticeboard Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-notice.png");
		} elseif ($model === 'Exam Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-exam.png");
		} elseif ($model === 'holiday Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-holiday.png");
		} elseif ($model === 'Feespayment Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-fees-payment.png");
		} elseif ($model === 'Class Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-class.png");
		} elseif ($model === 'Message Details' ) {
			$details_img_url = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-message.png");
		} ?>
		<img src="<?php echo esc_url($details_img_url); ?>" class="mjschool-popup-image-before-name">
		<a href="javascript:void(0);" class="mjschool-event-close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<?php
		if ($role === "administrator" || $role === "management") {
			if ($model === 'homework Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'transport Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_transport' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Event Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_event' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Notification Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notification' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Noticeboard Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Exam Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'holiday Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Feespayment Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=view_fesspayment&idtest=' . rawurlencode( mjschool_encrypt_id($feespayment_data->fees_pay_id ) ) . '&view_type=view_payment' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Class Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Message Details' ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message' ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			}
		} else {
			if ($model === 'homework Details' ) {
				?>
				<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=homework" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'transport Details' ) {
				?>
				<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=transport" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Event Details' ) {
				?>
				<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=event" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Notification Details' ) {
				?>
				<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=notification" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Noticeboard Details' ) {
				?>
				<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=notice" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Exam Details' ) {
				?>
				<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=exam" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'holiday Details' ) {
				?>
				<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=holiday" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Feespayment Details' ) {
				if ($feepayment_access === 1 ) {
					?>
					<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=feepayment&tab=view_fesspayment&idtest=" . rawurlencode( mjschool_encrypt_id($feespayment_data->fees_pay_id ) ) . "&view_type=view_payment" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
					<?php
				}
			} elseif ($model === 'Class Details' ) {
				?>
				<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=class" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php
			} elseif ($model === 'Message Details' ) {
				?>
				<a href="<?php echo esc_url(home_url( "?dashboard=mjschool_user&page=message" ) ); ?>" class="badge badge-success pull-right mjschool-dashboard-popup-design"><img class="redirect_img_css" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
				<?php 
			}
		}
		?>
		<h4 id="myLargeModalLabel" class="modal-title">
			<?php
			if ( $model === 'homework Details' ) {
				esc_html_e( 'Homework Details', 'mjschool' );
			} elseif ( $model === 'transport Details' ) {
				esc_html_e( 'Transport Details', 'mjschool' );
			}
			if ( $model === 'Event Details' ) {
				esc_html_e( 'Event Details', 'mjschool' );
			}
			if ( $model === 'Notification Details' ) {
				esc_html_e( 'Notification Details', 'mjschool' );
			} elseif ( $model === 'Noticeboard Details' ) {
				esc_html_e( 'Notice Details', 'mjschool' );
			} elseif ( $model === 'Exam Details' ) {
				esc_html_e( 'Exam Details', 'mjschool' );
			} elseif ( $model === 'holiday Details' ) {
				esc_html_e( 'Holiday Details', 'mjschool' );
			} elseif ( $model === 'Feespayment Details' ) {
				esc_html_e( 'Fees Payment Details', 'mjschool' );
			} elseif ( $model === 'Class Details' ) {
				esc_html_e( 'Class Details', 'mjschool' );
			} elseif ( $model === 'Message Details' ) {
				esc_html_e( 'Message Details', 'mjschool' );
			}
			?>
		</h4>
	</div>
	<div class="mjschool-panel-white">
		<?php
		if ( $model === 'Notification Details' ) {
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Student Name', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_user_name_by_id( $notification_data->student_id ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $notification_data->title ); ?></label>
					</div>
					<div class="col-md-12 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Message', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $notification_data->message ); ?></label>
					</div>
				</div>
			</div>
			<?php
		}
		if ( $model === 'Class Details' ) {
			$class_id = $class_data->class_id;
			
			$user = count(get_users(array(
				'meta_key' => 'class_name',
				'meta_value' => $class_id
			 ) ) );
			
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Name', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $class_data->class_name ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Create Date', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $class_data->created_date ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Numeric Name', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $class_data->class_num_name ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Student Capacity', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							echo esc_html( $user ) . ' ';
							esc_html_e( 'Out Of', 'mjschool' );
							echo ' ' . esc_html( $class_data->class_capacity );
							?>
						</label>
					</div>
				</div>
			</div>
			<?php
		}
		if ( $model === 'Message Details' ) {
			$message_for = get_post_meta( $message_data->post_id, 'message_for', true );
			$attchment   = get_post_meta( $message_data->post_id, 'message_attachment', true );
			$auth        = get_post( $message_data->post_id );
			$authid      = $auth->post_author;
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Message For', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							$check_message_single_or_multiple = mjschool_send_message_check_single_user_or_multiple( $message_data->post_id );
							if ( $check_message_single_or_multiple === 1 ) {
								global $wpdb;
								$tbl_name = $wpdb->prefix . 'mjschool_message';
								$post_id  = $message_data->post_id;
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
								$get_single_user = $wpdb->get_row( "SELECT * FROM $tbl_name where post_id = $post_id" );
								$role            = mjschool_get_display_name( $get_single_user->receiver );
								echo esc_html( $role );
							} else {
								$role = get_post_meta( $message_data->post_id, 'message_for', true );
								echo esc_html( $role );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Message From', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							$author = mjschool_get_display_name( $authid );
							echo esc_html( $author );
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Subject', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $message_data->subject ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Attachment', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							if ( ! empty( $attchment ) ) {
								$attchment_array = explode( ',', $attchment );
								foreach ( $attchment_array as $attchment_data ) {
									?>
									<a target="blank" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $attchment_data ) ); ?>" class="btn message_popup_button btn-default"><i class="fas fa-eye"></i> <?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a>
									<?php
								}
							} else {
								esc_html_e( 'No Attachment', 'mjschool' );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Message Date', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $message_data->date ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Description', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $message_data->message_body ); ?></label>
					</div>
				</div>
			</div>
			<?php
		}
		if ( $model === 'Feespayment Details' ) {
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Class Name', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_class_section_name_wise( $feespayment_data->class_id, $feespayment_data->section_id ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Student Name', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							$student_data = get_userdata( $feespayment_data->student_id );
							if ( ! empty( $student_data ) ) {
								echo esc_html( $student_data->display_name );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Fees Title', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							$fees_id   = explode( ',', $feespayment_data->fees_id );
							$fees_type = array();
							foreach ( $fees_id as $id ) {
								$fees_type[] = mjschool_get_fees_term_name( $id );
							}
							echo esc_html( implode( ' , ', $fees_type ) );
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Invoice Date', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $feespayment_data->paid_by_date ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $feespayment_data->total_amount, 2, '.', '' ) ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<?php
						$total_amount = $feespayment_data->total_amount;
						$paid_amount  = $feespayment_data->fees_paid_amount;
						$due_amount   = $total_amount - $paid_amount;
						?>
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Due Amount', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $due_amount, 2, '.', '' ) ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Paid Amount', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $feespayment_data->fees_paid_amount, 2, '.', '' ) ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Payment Status', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							$mjschool_get_payment_status = mjschool_get_payment_status( $feespayment_data->fees_pay_id );
							if ( $mjschool_get_payment_status === 'Not Paid' ) {
								echo "<span class='mjschool-red-color'>";
							} elseif ( $mjschool_get_payment_status === 'Partially Paid' ) {
								echo "<span class='mjschool-purpal-color'>";
							} else {
								echo "<span class='mjschool-green-color'>";
							}
							echo esc_html( $mjschool_get_payment_status );
							echo '</span>';
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $feespayment_data->start_year ) . ' ' . esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( $feespayment_data->end_year ); ?></label>
					</div>
					<div class="col-md-12 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Description', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							$description = ltrim( $feespayment_data->description );
							if ( ! empty( $description ) ) {
								echo esc_html( $description );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
				</div>
			</div>
			<?php
		}
		if ($model === 'Noticeboard Details' ) {
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $retrieve_class_data->post_title ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $retrieve_class_data->ID, 'start_date', true ) ) ); ?>
							<?php esc_html_e( 'To', 'mjschool' ); ?>
							<?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $retrieve_class_data->ID, 'end_date', true ) ) ); ?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Notice For', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							$role = get_post_meta( $retrieve_class_data->ID, 'notice_for', true );
							if ( $role === 'all' ) {
								esc_html_e( 'All', 'mjschool' );
							} else {
								echo esc_html( $role );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Class Name', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							if ( get_post_meta( $retrieve_class_data->ID, 'smgt_class_id', true ) != '' && get_post_meta( $retrieve_class_data->ID, 'smgt_class_id', true ) == 'all' ) {
								esc_html_e( 'All', 'mjschool' );
							} elseif ( get_post_meta( $retrieve_class_data->ID, 'smgt_class_id', true ) != '' ) {
								echo esc_html( mjschool_get_class_name( get_post_meta( $retrieve_class_data->ID, 'smgt_class_id', true ) ) );
							}
							?>
						</label>
					</div>
					<div class="col-md-12 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Comment', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							if ( ! empty( $retrieve_class_data->post_content ) ) {
								echo esc_html( $retrieve_class_data->post_content );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
				</div>
			</div>
			<?php
		}
		if ( $model === 'Exam Details' ) {
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $exam_data->exam_name ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Term', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( get_the_title( $exam_data->exam_term ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Class', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_class_section_name_wise( $exam_data->class_id, $exam_data->section_id ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date', 'mjschool' ); ?>
							<?php esc_html_e( 'To', 'mjschool' ); ?>
							<?php esc_html_e( 'End Date', 'mjschool' ); ?>
						</label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) ); ?>
							<?php esc_html_e( 'To', 'mjschool' ); ?>
							<?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) ); ?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Total Marks', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $exam_data->total_mark ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Passing Marks', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $exam_data->passing_mark ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Download File', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value">
							<?php
							$doc_data = json_decode( $exam_data->exam_syllabus );
							if ( ! empty( $doc_data[0]->value ) ) {
								?>
								<a download href="<?php print esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value ) ); ?>" class="btn mjschool-custom-padding-0 popup_download_btn" record_id="<?php echo esc_html( $exam_data->exam_id ); ?>"><i class="fas fa-download" id="mjschool-download-icon"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
								<?php
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<div class="col-md-12 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Comment', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							if ( ! empty( $exam_data->exam_comment ) ) {
								echo esc_html( $exam_data->exam_comment );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
				</div>
			</div>
			<?php
		}
		if ( $model === 'holiday Details' ) {
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Holiday Title', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( $holiday_data->holiday_title ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $holiday_data->date ) ) . ' ' . esc_attr__( 'To', 'mjschool' ) . ' ' . esc_html( mjschool_get_date_in_input_box( $holiday_data->end_date ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Status', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value mjschool_green_colors"><?php esc_html_e( 'approve', 'mjschool' ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Description', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value">
							<?php
							if ( ! empty( $holiday_data->description ) ) {
								echo esc_html( $holiday_data->description );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
				</div>
			</div>
			<?php
		}
		if ( $model === 'Event Details' ) {
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value">
							<?php
							if ( ! empty( $event_data->event_title ) ) {
								echo esc_html( stripslashes( $event_data->event_title ) );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Download File', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value">
							<?php
							if ( ! empty( $event_data->event_doc ) ) {
								?>
								<a download href="<?php print esc_url( content_url( '/uploads/school_assets/' . $event_data->event_doc ) ); ?>" class="btn mjschool-custom-padding-0 popup_download_btn" record_id="<?php echo esc_attr( $exam_data->exam_id ); ?>"><i class="fas fa-download" id="mjschool-download-icon"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
								<?php
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $event_data->start_date ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $event_data->end_date ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Time', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_time_remove_colon_before_am_pm( $event_data->start_time ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'End Time', 'mjschool' ); ?></label><br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_time_remove_colon_before_am_pm( $event_data->end_time ) ); ?></label>
					</div>
					<div class="col-md-12 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value">
							<?php
							if ( ! empty( $event_data->description ) ) {
								echo esc_html( stripslashes( $event_data->description ) );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
				</div>
			</div>
			<?php
		}
		if ( $model === 'transport Detailss' ) {
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Route Name', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( $transport_data->route_name ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Vehicle Identifier', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( $transport_data->number_of_vehicle ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Vehicle Registration Number', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( $transport_data->vehicle_reg_num ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Driver Name', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( $transport_data->driver_name ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Driver Phone Number', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( $transport_data->driver_phone_num ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Driver Address', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( $transport_data->driver_address ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Route Fare', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $transport_data->route_fare, 2, '.', '' ) ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Route Description', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value">
							<?php
							if ( ! empty( $transport_data->route_description ) ) {
								echo esc_html( $transport_data->route_description );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
				</div>
			</div>
			<?php
		}
		if ( $model === 'homework Details' ) {
			?>
			<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
				<div class="row">
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( $homework_data->title ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Subject', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_subject_by_id( $homework_data->subject ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Class', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_class_section_name_wise( $homework_data->class_name, $homework_data->section_id ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $homework_data->created_date ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value"><?php echo esc_html( mjschool_get_date_in_input_box( $homework_data->submition_date ) ); ?></label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Documents Title', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value">
							<?php
							$doc_data = json_decode( $homework_data->homework_document );
							if ( ! empty( $doc_data[0]->title ) ) {
								echo esc_attr( $doc_data[0]->title );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Download File', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value">
							<?php
							$doc_data = json_decode( $homework_data->homework_document );
							if ( ! empty( $doc_data[0]->value ) ) {
								?>
								<a download href="<?php print esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value ) ); ?>" class="btn mjschool-custom-padding-0 popup_download_btn" record_id="<?php echo esc_attr( $homework_data->homework_id ); ?>"><i class="fas fa-download" id="mjschool-download-icon"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
								<?php
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<?php
					global $current_user;
					$user_roles = $current_user->roles;
					$user_role  = array_shift( $user_roles );
					if ( $user_role === 'student' ) {
						?>
						<div class="col-md-6 mjschool-popup-padding-15px mjschool-margin-top-15px">
							<a href="?dashboard=mjschool_user&page=homework&tab=Viewhomework&action=view&homework_id=<?php echo esc_attr( $homework_data->homework_id ); ?>&student_id=<?php echo esc_attr( get_current_user_id() ); ?>" class="mjschool-save-btn mjschool-list-padding-5px"> <?php esc_html_e( 'Upload Homework', 'mjschool' ); ?> </a>
						</div>
						<?php
					}
					?>
					<div class="col-md-12 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Homework Content', 'mjschool' ); ?></label>
						<br>
						<label class="mjschool-label-value">
							<?php
							if ( ! empty( $homework_data->content ) ) {
								echo esc_html( $homework_data->content );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_add_or_remove_category_callback', 'mjschool_add_or_remove_category_callback' );
add_action( 'wp_ajax_nopriv_mjschool_add_or_remove_category_callback', 'mjschool_add_or_remove_category_callback' );
/**
 * Handles showing the add/remove category popup modal and rendering category listing.
 *
 * This function loads category forms, validates nonce & login status,
 * prepares dynamic labels/titles based on category model, and returns
 * the HTML + JS for the popup modal.
 *
 * @since 1.0.0
 * 
 * @return void Outputs HTML content and terminates execution using wp_die().
 */
function mjschool_add_or_remove_category_callback() {
	wp_enqueue_script( 'mjschool-ajax-function', plugins_url( '/assets/js/mjschool-ajax-function.js', __FILE__ ) );	
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['model'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$model              = sanitize_text_field( wp_unslash($_REQUEST['model']) );
	$title              = esc_html__( 'title', 'mjschool' );
	$table_header_title = esc_html__( 'header', 'mjschool' );
	$button_text        = esc_html__( 'Add', 'mjschool' );
	$label_text         = esc_html__( 'category Name', 'mjschool' );
	if ( $model === 'school_category' ) {
		$title              = esc_html__( 'Add School Name', 'mjschool' );
		$table_header_title = esc_html__( 'School Name', 'mjschool' );
		$button_text        = esc_html__( 'Add', 'mjschool' );
		$label_text         = esc_html__( 'School Name', 'mjschool' );
	}
	if ( $model === 'mjschool-standard-category' ) {
		$title              = esc_html__( 'Add Standard Name', 'mjschool' );
		$table_header_title = esc_html__( 'Standard Name', 'mjschool' );
		$button_text        = esc_html__( 'Add', 'mjschool' );
		$label_text         = esc_html__( 'Standard Name', 'mjschool' );
	}
	if ( $model === 'term_category' ) {
		$title              = esc_html__( 'Add Term Category', 'mjschool' );
		$table_header_title = esc_html__( 'Term Category Name', 'mjschool' );
		$button_text        = esc_html__( 'Add', 'mjschool' );
		$label_text         = esc_html__( 'Term Category Name', 'mjschool' );
	}
	if ( $model === 'designation' ) {
		$title              = esc_html__( 'Add Designation', 'mjschool' );
		$table_header_title = esc_html__( 'Designation Category Name', 'mjschool' );
		$button_text        = esc_html__( 'Add', 'mjschool' );
		$label_text         = esc_html__( 'Designation Category Name', 'mjschool' );
	}
	if ( $model === 'room_category' ) {
		$title              = esc_html__( 'Add Room Type', 'mjschool' );
		$table_header_title = esc_html__( 'Room Type Name', 'mjschool' );
		$button_text        = esc_html__( 'Add', 'mjschool' );
		$label_text         = esc_html__( 'Room Type Name', 'mjschool' );
	}
	if ( $model === 'leave_type' ) {
		$title              = esc_attr__( 'Add Leave Type', 'mjschool' );
		$table_header_title = esc_attr__( 'Leave Type Name', 'mjschool' );
		$button_text        = esc_attr__( 'Add', 'mjschool' );
		$label_text         = esc_attr__( 'Leave Type Name', 'mjschool' );
	}
	if ( $model === 'smgt_feetype' ) {
		$title              = esc_html__( 'Add Fees', 'mjschool' );
		$table_header_title = esc_html__( 'Fees Category Name', 'mjschool' );
		$button_text        = esc_html__( 'Add', 'mjschool' );
		$label_text         = esc_html__( 'Fees Category Name', 'mjschool' );
	}
	if ( $model === 'smgt_bookcategory' ) {
		$title              = esc_html__( 'Add Book Category', 'mjschool' );
		$table_header_title = esc_html__( 'Book Category Name', 'mjschool' );
		$button_text        = esc_html__( 'Add', 'mjschool' );
		$label_text         = esc_html__( 'Book Category Name', 'mjschool' );
	}
	if ( $model === 'smgt_rack' ) {
		$title              = esc_html__( 'Add Rack Location', 'mjschool' );
		$table_header_title = esc_html__( 'Rack Location Name', 'mjschool' );
		$button_text        = esc_html__( 'Add', 'mjschool' );
		$label_text         = esc_html__( 'Rack Location Name', 'mjschool' );
	}
	if ( $model === 'period_type' ) {
		$title              = esc_attr__( 'Issue Period', 'mjschool' );
		$table_header_title = esc_attr__( 'Period Time', 'mjschool' );
		$button_text        = esc_attr__( 'Add', 'mjschool' );
		$label_text         = esc_attr__( 'Period Time', 'mjschool' );
	}
	if ( $model === 'period_type' ) {
		$obj_lib     = new Mjschool_Library();
		$cat_result1 = $obj_lib->mjschool_get_period_list();
	} else {
		$cat_result = mjschool_get_all_category( $model );
	}
	?>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
		
		<a href="javascript:void(0);" class="mjschool-event-close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( $title); ?></h4>
		
	</div>
	<div class="mjschool-padding-15px"><!---PANEL-WHITE.--->
		<form name="category_form" action="" method="post" class="mjschool-category-popup-float mjschool-form-horizontal mjschool-admission-form-popup" id="category_form_test">
			<!---CATEGORY_FORM.----->
			<input type="hidden" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<?php
					if ( $model === 'period_type' ) {
						?>
						<div class="col-md-9">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="category_name" maxlength="50" min="1" class="mjschool-cat-value validate[required] form-control text-input onlyletter_number" type="number" value="" name="category_name" placeholder="<?php esc_html_e( 'Must Be Enter Number of Days', 'mjschool' ); ?>">
									<label for="category_name" class="active"> <?php echo esc_html( $label_text ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="col-md-9">
							<div class="form-group input mjschool-rtl-margin-0px">
								<div class="col-md-12 form-control">
									<input id="category_name" maxlength="50" class="mjschool-cat-value form-control validate[required,custom[description_validation]] text-input" type="text" maxlength="50" value="" name="category_name">
									<label for="category_name"> <?php echo esc_html( $label_text ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					<div class="col-sm-3 mjschool-list-padding-10px">
						<input type="button" value="<?php echo esc_attr( $button_text ); ?>" name="save_category_test" class="btn mjschool-save-btn btn-success" model="<?php echo esc_attr( $model ); ?>" id="btn_add_cat_new_test">
					</div>
				</div>
			</div>
		</form>
		<div class="mjschool-category-listbox_new mjschool-admission-pop-up-new"><!---CATEGORY_LISTBOX.----->
			<div class="col-lg-12 col-md-12 col-xs-12 col-sm-12"><!---TABLE-RESPONSIVE.----->
				<?php
				$i = 1;
				?>
				<div class="div_new">
					<?php
					if ( $model === 'period_type' ) {
						foreach ( $cat_result1 as $retrieved_data ) {
							?>
							<div class="row mjschool-new-popup-padding" id="<?php echo 'cat_new-' . esc_attr( $retrieved_data->ID ) . ''; ?>">
								<div class="col-md-11 mjschool-width-80px mjschool-mt-7px">
									<?php
									echo esc_html( $retrieved_data->post_title );
									echo esc_html__( 'Days', 'mjschool' );
									?>
								</div>
								<div class="row col-md-1 mjschool-rs-popup-width-20px" id="<?php echo esc_attr( $retrieved_data->ID ); ?>">
									<div class="col-md-12">
										
										<a href="#" class="btn-delete-cat_new" model="<?php echo esc_attr($model); ?>" id="<?php echo esc_attr($retrieved_data->ID); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></a>
									</div>
								</div>
							</div>
							<?php
							$i++;
						}
					} else {
						foreach ($cat_result as $retrieved_data) {
							?>
							<div class="row mjschool-new-popup-padding" id="<?php echo "cat_new-" . esc_attr($retrieved_data->ID) . ""; ?>">
								<div class="col-md-10 mjschool-width-70px">
									<?php echo esc_html( $retrieved_data->post_title); ?>
								</div>
								<div class="row col-md-2 mjschool-padding-left-0-res mjschool-width-30px" id="<?php echo esc_attr($retrieved_data->ID); ?>">
									<div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0">
										<a href="#" class="btn-delete-cat_new" model="<?php echo esc_attr($model); ?>" id="<?php echo esc_attr($retrieved_data->ID); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></a>
									</div>
									<div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-right-0">
										<a class="mjschool-btn-edit-cat_popup" model="<?php echo esc_attr($model); ?>" href="#" id="<?php echo esc_attr($retrieved_data->ID); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>"></a>
									</div>
								</div>
							</div>
							<?php
							$i++;
						}
					}
					?>
				</div>
			</div><!---END TABLE-RESPONSIVE.----->
		</div><!---END CATEGORY_LISTBOX.----->
	</div><!---END PANEL-WHITE.--->
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_add_category_new', 'mjschool_add_category_new' );
add_action( 'wp_ajax_nopriv_mjschool_add_category_new', 'mjschool_add_category_new' );
/**
 * Adds a new category via AJAX request.
 *
 * Validates AJAX nonce, ensures the user is logged in, inserts a new
 * category post type, and returns HTML row + option HTML for frontend update.
 *
 * @since 1.0.0
 *
 * @param array $data Optional. AJAX submitted data.
 * 
 * @return void Outputs JSON response and dies().
 */
function mjschool_add_category_new($data) {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_POST['category_name'], $_POST['model'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	global $wpdb;
	$model = sanitize_text_field($_REQUEST['model']);
	$array_var = array();
	$data = array();
	$data['category_name'] = sanitize_text_field(wp_unslash($_POST['category_name']));
	$data['category_type'] = sanitize_text_field(wp_unslash($_POST['model']));
	$dlt_image = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png");
	$edit_image = esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png");
	$id = mjschool_add_categorytype($data);
	if ($model === 'period_type' ) {
		$row1 = '<div class="row mjschool-new-popup-padding" id="cat_new-' . $id . '"><div class="col-md-11 mjschool-width-80px mjschool-mt-7px">' . $_REQUEST['category_name'] . ' ' . esc_attr__( "Days", "mjschool" ) . '</div><div class="row col-md-1 mjschool-rs-popup-width-20px"><div class="col-md-12"><a href="#" class="btn-delete-cat_new" model="' . $model . '" id="' . $id . '"><img src="' . $dlt_image . '"></a></a></div></div></div>';
		$option = "<option value='$id'>" . sanitize_text_field(wp_unslash($_REQUEST['category_name'])) . ' ' . esc_attr__( 'Days', 'mjschool' ) . '' . "</option>";
	} else {
		$row1 = '<div class="row mjschool-new-popup-padding" id="cat_new-' . $id . '"><div class="col-md-10 mjschool-width-70px">' . $_REQUEST['category_name'] . '</div><div class="row col-md-2 mjschool-padding-left-0-res mjschool-width-30px"><div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0"><a href="#" class="btn-delete-cat_new" model="' . $model . '" id="' . $id . '"><img src="' . $dlt_image . '"></a></div><div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-right-0"><a class="mjschool-btn-edit-cat_popup" model="' . $model . '" href="#" id="' . $id . '"><img src="' . $edit_image . '"></a></div></div></div>';
		$option = "<option value='$id'>" . sanitize_text_field(wp_unslash($_REQUEST['category_name'])) . "</option>";
	}
	 
	$array_var[] = $row1;
	$array_var[] = $option;
	echo json_encode( $array_var );
	die();
}
add_action( 'wp_ajax_mjschool_remove_category_new', 'mjschool_remove_category_new' );
/**
 * Removes a category via AJAX request.
 *
 * Performs nonce validation, checks user authentication, deletes the
 * category post, and returns JSON success or failure response.
 *
 * @since 1.0.0
 *
 * @return void Outputs a JSON response (success/error).
 */
function mjschool_remove_category_new() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
    if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'mjschool_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed' ), 403 );
    }
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Unauthorized access' ), 401 );
    }
	if ( ! isset( $_POST['cat_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
    $cat_id = intval( $_POST['cat_id'] );
    $delete = wp_delete_post( $cat_id, true );
    if ( $delete ) {
        wp_send_json_success( array( 'message' => 'Deleted successfully' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Failed to delete category' ), 500 );
    }
}
add_action( 'wp_ajax_mjschool_admissoin_approved', 'mjschool_admissoin_approved' );
/**
 * Loads and displays the admission approval modal for a student.
 *
 * Validates nonce and logged-in user, fetches student data, and returns
 * the HTML for the approval form including JS validations.
 *
 * @since 1.0.0
 *
 * @return void Outputs modal HTML content and dies().
 */
function mjschool_admissoin_approved() {

	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['student_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$uid       = sanitize_text_field(wp_unslash($_REQUEST['student_id']) );
	$user_info = get_userdata( $uid );
	?>
	<div class="modal-header modal_header_height mjschool-dashboard-model-header">
		
		<a href="#" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<img class="mjschool_float_left_important" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="img-circle head_logo mjschool_float_left" width="40" height="40"    />
		<h4 class="modal-title">&nbsp; <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </h4>
		
	</div>
	<div class="mjschool-panel-white mjschool-admission-div-responsive">
		<div class="padding_20px padding_bottom_0px">
			<h4 class="mjschool-panel-title"><i class="fas fa-user"></i>
				<?php echo esc_html( mjschool_get_user_name_by_id( $uid ) ); ?>
			</h4>
		</div>
		<form name="mjschool-admission-form" action="" method="post" class="padding_20px mjschool-form-horizontal mjschool-admission-form" id="mjschool-admission-form">
			<input type="hidden" name="act_user_id" value="<?php echo esc_attr( $uid ); ?>">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="email" class="form-control validate[required,custom[email]] text-input email" maxlength="100" value="<?php echo esc_attr( $user_info->user_email ); ?>" type="text" name="email" readonly>
								<label for="email" class="active"> <?php esc_html_e( 'Email', 'mjschool' ); ?><span class="required">*</span> </label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="password" class="form-control <?php if ( ! $edit ) { echo 'validate[required,minSize[8],maxSize[12]]'; } else { echo 'validate[minSize[8],maxSize[12]]'; } ?>" type="password" name="password" autocomplete="current-password">
								<label  for="password"><?php esc_html_e( 'Password', 'mjschool' ); ?>
									<?php if ( ! $edit ) { ?>
										<span class="mjschool-require-field">*</span>
									<?php } ?>
								</label>
								<!-- Use class + Data-target. -->
								<i class="fas fa-eye-slash togglePassword" data-target="#password"></i>
							</div>
						</div>
					</div>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="approve_class_list"> <?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span> </label>
						<select name="class_name" class="mjschool-line-height-30px form-control validate[required] width_515" id="approve_class_list">
							<option value=""> <?php esc_html_e( 'Select Class', 'mjschool' ); ?> </option>
							<?php
							$class_value = get_user_meta( $uid, 'class_name', true ); // Get the class ID assigned to user
							foreach ( mjschool_get_all_class() as $classdata ) {
								$selected = selected( $class_value, $classdata['class_id'], false ); // compare values
								echo '<option value="' . esc_attr( $classdata['class_id'] ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $classdata['class_name'] ) . '</option>';
							}
							?>
						</select>
					</div>
					<?php if ( $school_type === 'school' ) {?>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="approve_class_section"> <?php esc_html_e( 'Class Section', 'mjschool' ); ?> </label>
							<select name="class_section" class="mjschool-line-height-30px form-control width_515" id="approve_class_section">
								<option value=""> <?php esc_html_e( 'All Section', 'mjschool' ); ?> </option>
								<?php
								if ( ! empty( $class_value ) ) {
									$retrieve_data = mjschool_get_class_sections( $class_value );
									if ( ! empty( $retrieve_data ) ) {
										foreach ( $retrieve_data as $section ) {
											printf( '<option value="%s" %s>%s</option>', esc_attr( $section->id ), esc_attr( selected( $section_value, $section->id, false ) ), esc_html( $section->section_name ) );
										}
									}
								}
								?>
							</select>
						</div>
					<?php }?>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="student_roll" class="form-control validate[required,maxSize[6],custom[integer]] student_roll text-input" maxlength="50" type="text" value="" name="roll_id">
								<label for="student_roll"> <?php esc_html_e( 'Roll No.', 'mjschool' ); ?><span class="required">*</span> </label>
							</div>
						</div>
					</div>
					<?php
					$obj_fees = new Mjschool_Fees();
					if ( get_option( 'mjschool_admission_fees' ) === 'yes' && get_option( 'mjschool_combine' ) === 1 ) {
						$fees_id   = get_option( 'mjschool_admission_amount' );
						$fee_label = esc_html__( 'Admission Fees', 'mjschool' );
						$amount    = $obj_fees->mjschool_get_single_feetype_data_amount( $fees_id );
						$fees      = $amount ? $amount : 0;
						?>
						<div class="col-md-6 mjschool-error-msg-left-margin mb-3">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="admission_fees" class="form-control" type="text" readonly value="<?php echo esc_attr( mjschool_get_currency_symbol() ) . ' ' . esc_attr( $fees ); ?>">
									<label for="admission_fees"><?php echo esc_html( $fee_label ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<input class="form-control" type="hidden" name="admission_fees" value="<?php echo esc_attr( $fees_id ); ?>">
						<?php
					}
					?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-checkbox-height-47px">
								<div class="row mjschool-padding-radio responsive_label_position">
									<div class="mjschool-display-flex">
										<label class="mjschool-custom-top-label" for="student_approve_mail"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
										<input id="chk_mjschool_sent1" class=" mjschool-check-box-input-margin" type="checkbox" value="1" name="student_approve_mail">
										&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px mb-3 mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-checkbox-height-47px">
								<div class="row mjschool-padding-radio responsive_label_position">
									<div class="mjschool-display-flex">
										<label class="mjschool-custom-top-label" for="student_approve_sms"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
										<input id="chk_mjschool_sent1"  type="checkbox" value="1" name="student_approve_sms">
										&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php wp_nonce_field( 'save_active_student_admission_nonce' ); ?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Active Student', 'mjschool' ); ?>" name="active_user_admission" class="btn btn-success activate_student mjschool-save-btn mjschool-margin-top-20" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_view_all_relpy', 'mjschool_view_all_relpy' );
add_action( 'wp_ajax_nopriv_mjschool_view_all_relpy', 'mjschool_view_all_relpy' );
/**
 * Handles AJAX request to fetch and display all message replies.
 *
 * Performs security checks (nonce + login), processes search filters,
 * fetches data from the `mjschool_message_replies` table, builds the
 * response array for DataTables, and returns JSON output.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void Outputs JSON and terminates script.
 */
function mjschool_view_all_relpy() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	global $wpdb;
	$sTable          = $wpdb->prefix . 'mjschool_message_replies';
	$sTable_wp_users = $wpdb->prefix . 'users';
	$sLimit          = '10';
	if ( isset( $_REQUEST['iDisplayStart'] ) && $_REQUEST['iDisplayLength'] != '-1' ) {
		$sLimit = 'LIMIT ' . intval( wp_unslash($_REQUEST['iDisplayStart'] )) . ', ' . intval( wp_unslash($_REQUEST['iDisplayLength'] ));
	}
	$ssearch = isset($_REQUEST['sSearch']) ? sanitize_text_field(wp_unslash($_REQUEST['sSearch'])) : '';
	if ( $ssearch ) {
		$sQuery = "SELECT * FROM  $sTable INNER JOIN $sTable_wp_users ON ($sTable.sender_id = $sTable_wp_users.ID OR $sTable.receiver_id = $sTable_wp_users.ID) WHERE sender_id LIKE '%$ssearch%' OR $sTable_wp_users.display_name LIKE '%$ssearch%' OR receiver_id LIKE '%$ssearch%' OR message_comment LIKE '%$ssearch%' OR created_date LIKE '%$ssearch%' ORDER BY $sTable.created_date DESC $sLimit";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$rResult = $wpdb->get_results( $sQuery, ARRAY_A );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->get_results( "SELECT * FROM  $sTable INNER JOIN $sTable_wp_users ON ($sTable.sender_id = $sTable_wp_users.ID OR $sTable.receiver_id = $sTable_wp_users.ID) WHERE sender_id LIKE '%$ssearch%' OR $sTable_wp_users.display_name LIKE '%$ssearch%' OR receiver_id LIKE '%$ssearch%' OR message_comment LIKE '%$ssearch%' OR created_date LIKE '%$ssearch%' ORDER BY $sTable.created_date DESC" );
		$iFilteredTotal = $wpdb->num_rows;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->get_results( "SELECT * FROM  $sTable INNER JOIN $sTable_wp_users ON ($sTable.sender_id = $sTable_wp_users.ID OR $sTable.receiver_id = $sTable_wp_users.ID) WHERE sender_id LIKE '%$ssearch%' OR $sTable_wp_users.display_name LIKE '%$ssearch%' OR receiver_id LIKE '%$ssearch%' OR message_comment LIKE '%$ssearch%' OR created_date LIKE '%$ssearch%' ORDER BY $sTable.created_date DESC" );
		$iTotal = $wpdb->num_rows;
	} else {
		$sQuery = "SELECT * FROM $sTable ORDER BY created_date DESC $sLimit";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$rResult = $wpdb->get_results( $sQuery, ARRAY_A );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->get_results( "SELECT * FROM $sTable Group BY id , id DESC" );
		$iFilteredTotal = $wpdb->num_rows;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->get_results( " SELECT * FROM $sTable Group BY id , id DESC" );
		$iTotal = $wpdb->num_rows;
	}
	$output = array(
		'sEcho'                => intval( wp_unslash($_REQUEST['sEcho']) ),
		'iTotalRecords'        => $iTotal,
		'iTotalDisplayRecords' => $iFilteredTotal,
		'aaData'               => array(),
	);
	$i      = 0;
	foreach ( $rResult as $aRow ) {
		if ( $i === 10 ) {
			$i = 0;
		}
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
		} elseif ( $i === 5 ) {
			$color_class_css = 'mjschool-class-color5';
		} elseif ( $i === 6 ) {
			$color_class_css = 'mjschool-class-color6';
		} elseif ( $i === 7 ) {
			$color_class_css = 'mjschool-class-color7';
		} elseif ( $i === 8 ) {
			$color_class_css = 'mjschool-class-color8';
		} elseif ( $i === 9 ) {
			$color_class_css = 'mjschool-class-color9';
		}
		$sender_info       = get_userdata( $aRow['sender_id'] );
		$receiver_info     = get_userdata( $aRow['receiver_id'] );
		$image_src         = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' );
		$profile_image_src = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-message-chat.png' );
		$row[0]            = '<td class="mjschool-checkbox-width-10px">
			<input type="checkbox" class="mjschool-sub-chk select-checkbox sub_chk" name="id[]" value="' . $aRow['id'] . '">
		</td>';
		 
		$row[1] = '<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
			<p class="mjschool_message_profile mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px ' . $color_class_css . '">
				<img src="' . $profile_image_src . '" height= "30px" width ="30px" class="mjschool-massage-image">
			</p>
		</td>';
		 
		$row[2]    = '<td>' . $sender_info->display_name . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Sender', 'mjschool' ) . '"></i></td>';
		$row[3]    = '<td>' . $receiver_info->display_name . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Receiver', 'mjschool' ) . '"></i><td>';
		$body_char = strlen( $msg->message_body );
		$body_char = strlen( $aRow['message_comment'] );
		if ( $body_char <= 60 ) {
			$row[4] = '<td>' . $aRow['message_comment'] . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Description', 'mjschool' ) . '"></i></td>';
		} else {
			$char_limit = 60;
			$msg_body   = substr( strip_tags( $aRow['message_comment'] ), 0, $char_limit ) . '...';
			$row[4]     = '<td>' . $msg_body . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Description', 'mjschool' ) . '"></i></td>';
		}
		$attchment = $aRow['message_attachment'];
		if ( ! empty( $attchment ) ) {
			$attchment_array = explode( ',', $attchment );
			$view_attchment  = '';
			foreach ( $attchment_array as $attchment_data ) {
				$view_attchment .= '<a target="blank" href="' . content_url() . '/uploads/school_assets/' . $attchment_data . '" class="btn btn-default"><i class="fas fa-download"></i>' . esc_html__( 'View Attachment', 'mjschool' ) . '</a></br>';
			}
			$row[5] = '<td>' . $view_attchment . '</td>';
		} else {
			$row[5] = '<td>' . esc_attr__( 'No Attachment', 'mjschool' ) . '</td>';
		}
		$row[6] = '<td>' . mjschool_convert_date_time( $aRow['created_date'] ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Date & Time', 'mjschool' ) . '"></i></td>';
		
		$row[7] = '<td class="action">
			<div class="mjschool-user-dropdown">
				<ul  class="mjschool_ul_style">
					<li >
						<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
							<img src="' . $image_src . '">
						</a>
						<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
							<li class="mjschool-float-left-width-100px">
								<a href="?page=mjschool_message&tab=view_all_message_reply&action=delete_users_reply_message&users_reply_message_id=' . $aRow['id'] . '" class="mjschool-float-left-width-100px mjschool_light_orange_color" onclick="return confirm(language_translate2.delete_record_alert)"><i class="fas fa-trash"></i> ' . esc_attr__( 'Delete', 'mjschool' ) . '</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</td>';
		
		$output['aaData'][] = $row;
		++$i;
	}
	echo json_encode( $output );
	die();
}
add_action( 'wp_ajax_mjschool_view_all_message', 'mjschool_view_all_message' );
add_action( 'wp_ajax_nopriv_mjschool_view_all_message', 'mjschool_view_all_message' );
/**
 * Handles AJAX request to fetch and display all messages.
 *
 * Validates security (nonce + user login), processes DataTables search,
 * pulls sender/receiver/class/post details, prepares message rows,
 * and returns the structured JSON output for display.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void Outputs JSON and terminates script.
 */
function mjschool_view_all_message() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	global $wpdb;
	$sTable          = $wpdb->prefix . 'mjschool_message';
	$sTable_wp_users = $wpdb->prefix . 'users';
	$tablename       = 'mjschool_class';
	$retrieve_class_data  = mjschool_get_all_data( $tablename );
	$sLimit          = '10';
	if ( isset( $_REQUEST['iDisplayStart'] ) && $_REQUEST['iDisplayLength'] != '-1' ) {
		$sLimit = '' . intval( wp_unslash($_REQUEST['iDisplayStart']) ) . ', ' . intval( wp_unslash($_REQUEST['iDisplayLength']) );
	}
	$ssearch = isset($_REQUEST['sSearch']) ? sanitize_text_field(wp_unslash($_REQUEST['sSearch'])) : '';
	if ( $ssearch ) {
		$sQuery = "SELECT * FROM  $sTable INNER JOIN $sTable_wp_users ON ($sTable.sender = $sTable_wp_users.ID OR $sTable.receiver = $sTable_wp_users.ID) WHERE sender LIKE '%$ssearch%' OR $sTable_wp_users.display_name LIKE '%$ssearch%' OR receiver LIKE '%$ssearch%' OR subject LIKE '%$ssearch%' OR message_body LIKE '%$ssearch%' ORDER BY $sTable.date DESC $sLimit";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$rResult = $wpdb->get_results( $sQuery, ARRAY_A );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->get_results( "SELECT * FROM  $sTable INNER JOIN $sTable_wp_users ON ($sTable.sender = $sTable_wp_users.ID OR $sTable.receiver = $sTable_wp_users.ID) WHERE sender LIKE '%$ssearch%' OR $sTable_wp_users.display_name LIKE '%$ssearch%' OR receiver LIKE '%$ssearch%' OR subject LIKE '%$ssearch%' OR message_body LIKE '%$ssearch%' ORDER BY $sTable.date DESC" );
		$iFilteredTotal = $wpdb->num_rows;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->get_results( "SELECT * FROM  $sTable INNER JOIN $sTable_wp_users ON ($sTable.sender = $sTable_wp_users.ID OR $sTable.receiver = $sTable_wp_users.ID) WHERE sender LIKE '%$ssearch%' OR $sTable_wp_users.display_name LIKE '%$ssearch%' OR receiver LIKE '%$ssearch%' OR subject LIKE '%$ssearch%' OR message_body LIKE '%$ssearch%' ORDER BY $sTable.date DESC" );
		$iTotal = $wpdb->num_rows;
	} else {
		$sQuery = "SELECT * FROM $sTable ORDER BY date DESC limit $sLimit";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$rResult = $wpdb->get_results( $sQuery, ARRAY_A );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->get_results( "SELECT * FROM $sTable Group BY message_id , message_id DESC" );
		$iFilteredTotal = $wpdb->num_rows;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->get_results( " SELECT * FROM $sTable Group BY message_id , message_id DESC" );
		$iTotal = $wpdb->num_rows;
	}
	$output = array(
		'sEcho'                => intval( wp_unslash($_REQUEST['sEcho']) ),
		'iTotalRecords'        => $iTotal,
		'iTotalDisplayRecords' => $iFilteredTotal,
		'aaData'               => array(),
	);
	$i      = 0;
	foreach ( $rResult as $aRow ) {
		if ( $i === 10 ) {
			$i = 0;
		}
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
		} elseif ( $i === 5 ) {
			$color_class_css = 'mjschool-class-color5';
		} elseif ( $i === 6 ) {
			$color_class_css = 'mjschool-class-color6';
		} elseif ( $i === 7 ) {
			$color_class_css = 'mjschool-class-color7';
		} elseif ( $i === 8 ) {
			$color_class_css = 'mjschool-class-color8';
		} elseif ( $i === 9 ) {
			$color_class_css = 'mjschool-class-color9';
		}
		$user_id           = $aRow['receiver'];
		$school_obj        = new MJSchool_Management( $user_id );
		$attchment         = get_post_meta( $aRow['post_id'], 'message_attachment', true );
		$sender_info       = get_userdata( $aRow['sender'] );
		$receiver_info     = get_userdata( $aRow['receiver'] );
		$image_src         = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' );
		$profile_image_src = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-message-chat.png' );
		$row[0]            = '<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox sub_chk" name="id[]" value="' . $aRow['message_id'] . '"></td>';
		$message_for       = get_post_meta( $aRow['post_id'], 'message_for', true );
		 
		$row[1] = '<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
			<p class="mjschool_message_profile mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px ' . $color_class_css . '">
				<img src="' . $profile_image_src . '" height= "30px" width ="30px" class="mjschool-massage-image">
			</p>
		</td>';
		 
		$row[2] = '<td>' . esc_html( $message_for ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Message For', 'mjschool' ) . '"></i></td>';
		$row[3] = '<td>' . $sender_info->display_name . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Sender', 'mjschool' ) . '"></i></td>';
		$row[4] = '<td>' . $receiver_info->display_name . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Receiver', 'mjschool' ) . '"></i></td>';
		if ( get_post_meta( $aRow['post_id'], 'smgt_class_id', true ) != '' && get_post_meta( $aRow['post_id'], 'smgt_class_id', true ) === 'all' ) {
			$row[5] = '<td>' . esc_html_e( 'All', 'mjschool' ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Class', 'mjschool' ) . '"></i></td>';
		} elseif ( get_post_meta( $aRow['post_id'], 'smgt_class_id', true ) != '' ) {
			$mjschool_class_id    = get_post_meta( $aRow['post_id'], 'smgt_class_id', true );
			$class_id_array   = explode( ',', $mjschool_class_id );
			$class_name_array = array();
			foreach ( $class_id_array as $data ) {
				$class_name_array[] = mjschool_get_class_name( $data );
			}
			$row[5] = '<td>' . implode( ',', $class_name_array ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Class', 'mjschool' ) . '"></i></td>';
		} else {
			$row[5] = '<td>' . esc_html__( 'All', 'mjschool' ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Class', 'mjschool' ) . '"></i></td>';
		}
		$subject_char = strlen( get_the_title( $aRow['post_id'] ) );
		if ( $subject_char <= 10 ) {
			$row[6] = '<td>' . get_the_title( $aRow['post_id'] ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Subject', 'mjschool' ) . '"></i></td>';
		} else {
			$char_limit   = 10;
			$subject_body = substr( strip_tags( get_the_title( $aRow['post_id'] ) ), 0, $char_limit ) . '...';
			$row[6]       = '<td>' . $subject_body . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Subject', 'mjschool' ) . '"></i></td>';
		}
		$content_post = get_post( $aRow['post_id'] );
		$body_char    = strlen( $content_post->post_content );
		if ( $body_char <= 60 ) {
			$row[7] = '<td>' . $content_post->post_content . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Description', 'mjschool' ) . '"></i></td>';
		} else {
			$char_limit = 60;
			$msg_body   = substr( strip_tags( $content_post->post_content ), 0, $char_limit ) . '...';
			$row[7]     = '<td>' . $msg_body . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Description', 'mjschool' ) . '"></i></td>';
		}
		// $row[6] = $content_post->post_content;
		if ( ! empty( $attchment ) ) {
			$attchment_array = explode( ',', $attchment );
			$view_attchment  = '';
			foreach ( $attchment_array as $attchment_data ) {
				$view_attchment .= '<a target="blank" href="' . content_url() . '/uploads/school_assets/' . $attchment_data . '" class="btn btn-default"><i class="fas fa-download"></i>' . esc_html__( 'View Attachment', 'mjschool' ) . '</a>';
			}
			$row[8] = '<td>' . $view_attchment . '</td>';
		} else {
			$row[8] = '<td>' . esc_html__( 'No Attachment', 'mjschool' ) . '</td>';
		}
		$created_date = $content_post->post_date_gmt;
		$row[9]       = '<td>' . mjschool_convert_date_time( $created_date ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="' . esc_html__( 'Date & Time', 'mjschool' ) . '"></i></td>';
		 
		$row[10] = '<td class="action">
			<div class="mjschool-user-dropdown">
				<ul  class="mjschool_ul_style">
					<li >
						<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
							<img src="' . $image_src . '">
						</a>
						<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
							<li class="mjschool-float-left-width-100px">
								<a href="?page=mjschool_message&tab=view_all_message&action=delete_users_message&users_message_id=' . $aRow['message_id'] . '" class="mjschool-float-left-width-100px mjschool_light_orange_color" onclick="return confirm(language_translate2.delete_record_alert)"><i class="fas fa-trash"></i>' . esc_html__( 'Delete', 'mjschool' ) . '</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</td>';
		
		$output['aaData'][] = $row;
		++$i;
	}
	echo json_encode( $output );
	die();
}
add_action( 'wp_ajax_nopriv_mjschool_generate_access_token', 'mjschool_generate_access_token' );
add_action( 'wp_ajax_mjschool_generate_access_token', 'mjschool_generate_access_token' );
/**
 * Generates and redirects to Zoom OAuth authorization URL.
 *
 * Validates the AJAX nonce and user login status, retrieves Zoom API credentials
 * from WordPress options, and redirects user to Zoom authorization page for
 * generating access tokens.
 *
 * @since 1.0.0
 *
 * @return void Redirects to Zoom OAuth URL and terminates script.
 */
function mjschool_generate_access_token() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$CLIENT_ID    = get_option( 'mjschool_virtual_classroom_client_id' );
	$REDIRECT_URI = site_url() . '/?page=mjschoolcallback';
	wp_safe_redirect( 'https://zoom.us/oauth/authorize?response_type=code&client_id=' . $CLIENT_ID . '&redirect_uri=' . $REDIRECT_URI );
	die();
}
add_action( 'wp_ajax_nopriv_mjschool_import_data', 'mjschool_import_data' );
add_action( 'wp_ajax_mjschool_import_data', 'mjschool_import_data' );
/**
 * Loads and displays the import CSV popup modal for AJAX requests.
 *
 * Validates security using nonce & user login, prints the HTML markup for
 * the import modal, initializes JavaScript validation, and handles CSV 
 * file format validation. Does not process file upload here—only UI.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML/JS for import modal and terminates script.
 */
function mjschool_import_data() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-import-csv-popup">
		<a href="#" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Import Data', 'mjschool' ); ?></h4>
		
	</div>
	<form class="mjschool-form-horizontal import_csv_popup_form" id="inport_csv" action="#" method="post" enctype="multipart/form-data">
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6 input mt-0">
					<div class="form-group input mjschool-rtl-margin-top-0px-popup">
						<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
							<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Select CSV File', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<div class="col-sm-12">
								<input id="input-1" name="csv_file" type="file" class="form-control file validate[required] mjschool-file-validation">
							</div>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'upload_csv_nonce' ); ?>
				<div class="col-lg-6 col-md-6 col-sm-4 col-xs-6 mjschool-margin-bottom-15px">
					<button type="submit" class="btn width-auto mjschool-save-btn mjschool-rtl-margin-0px" name="upload_csv_file"><?php esc_html_e( 'Upload CSV File', 'mjschool' ); ?></button>
				</div>
				<p><?php esc_html_e( 'Instruction : For Import Image First add image To /wp-content/uploads/ folder after that in your csv file add one column user_profile', 'mjschool' ); ?> </p>
			</div>
		</div>
	</form>
	<?php
	wp_die();
}
add_action( 'wp_ajax_nopriv_mjschool_export_data', 'mjschool_export_data' );
add_action( 'wp_ajax_mjschool_export_data', 'mjschool_export_data' );
/**
 * Render and handle the Export Data modal via AJAX.
 *
 * Validates the request using nonce and ensures the user is logged in
 * before outputting the CSV export form. This form allows selection of
 * class and section to export student data in CSV format.
 *
 * @since 1.0.0
 * 
 * @return void Outputs HTML and terminates execution using wp_die().
 */
function mjschool_export_data() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-import-csv-popup">
		<a href="#" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Export Data', 'mjschool' ); ?></h4>
		
	</div>
	<div class="mjschool-panel-body export_csv_padding_18px"><!------- Panel Body. ---------->
		<!------- Export Student CSV Form. ---------->
		<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
			<div class="form-body mjschool-user-form"> <!--Form Body div.-->
				<div class="row"><!--Row Div.-->
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
						<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="class_name" class="form-control validate[required]" id="class_list">
							<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"> <?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
						<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
						<select name="class_section" class="form-control" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
						</select>
					</div>
				</div> <!--Row Div.-->
			</div><!--Form Body div.-->
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Export IN CSV', 'mjschool' ); ?>" name="exportstudentin_csv" class="mjschool-save-btn btn btn-success mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form><!------- Export Student CSV Form. ---------->
	</div><!------- Panel Body. ---------->
	<?php
	wp_die();
}
add_action( 'wp_ajax_nopriv_mjschool_student_import_data', 'mjschool_student_import_data' );
add_action( 'wp_ajax_mjschool_student_import_data', 'mjschool_student_import_data' );
/**
 * Render the Student Import CSV modal via AJAX.
 *
 * Performs nonce validation and login checks before displaying 
 * the CSV import form. Includes validation scripts and instructions 
 * for importing users, custom fields, and profile images.
 *
 * @since 1.0.0
 * 
 * @return void Outputs HTML content and terminates with wp_die().
 */
function mjschool_student_import_data() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-import-csv-popup">
		<a href="#" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Import Data', 'mjschool' ); ?></h4>
	</div>
	<div class="mjschool-panel-body"><!-------- Panel Body. ---------->
		<!-------- Import Student Form. ---------->
		<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
			<div class="form-body mjschool-user-form"> <!--Form Body div.-->
				<div class="row"><!--Row Div.-->
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
						<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Select Class', 'mjschool' ); ?></label>
						<select name="class_name" class="form-control" id="class_list_add_student">
							<option value=""><?php esc_html_e( 'All Class', 'mjschool' ); ?></option>
							<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"> <?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
						<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
						<select name="class_section" class="form-control" id="mjschool-class-section-add-student">
							<option value=""><?php esc_html_e( 'Select Section', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Select CSV file', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<div class="col-sm-12">
									<input id="csv_file" type="file" class="validate[required] form-control file csvfile_width d-inline mjschool-file-validation csv_file" name="csv_file">
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div class="mjschool-rtl-relative-position">
										<label class="mjschool-custom-top-label " for="mjschool_import_student_mail"><?php esc_html_e( 'Send Email', 'mjschool' ); ?></label>
										<input type="checkbox" class="mjschool-check-box-input-margin" name="mjschool_import_student_mail" value="1" <?php echo checked( get_option( 'mjschool_import_student_mail' ), 'yes' ); ?> />
										&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div> <!--Row Div.-->
			</div><!--Form Body div.-->
			<?php wp_nonce_field( 'upload_teacher_admin_nonce' ); ?>
			<p>
				<strong>1. <?php esc_html_e( 'Instruction for Profile image :', 'mjschool' ); ?></strong><br>
				1)
				<?php esc_html_e( 'Add ', 'mjschool' ); ?><strong><?php esc_html_e( 'user_profile', 'mjschool' ); ?></strong><?php esc_html_e( ' folder in ', 'mjschool' ); ?><strong><?php esc_html_e( '/wp-content/uploads/', 'mjschool' ); ?></strong><?php esc_html_e( ' Path', 'mjschool' ); ?><br>
				2 )
				<?php esc_html_e( 'Upload the User Profile photo in ', 'mjschool' ); ?><strong><?php esc_html_e( 'user_profile', 'mjschool' ); ?></strong><?php esc_html_e( ' folder', 'mjschool' ); ?><br>
				3)
				<?php esc_html_e( 'Add your image path in ', 'mjschool' ); ?><strong><?php esc_html_e( 'user_profile', 'mjschool' ); ?></strong><?php esc_html_e( ' column in CSV. for example : ', 'mjschool' ); ?><strong><?php esc_html_e( 'user_profile/image.png', 'mjschool' ); ?></strong><br>
				<strong>2. <?php esc_html_e( 'Instruction for Import Custom-Field :', 'mjschool' ); ?></strong><br>
				=>
				<?php esc_html_e( 'Add your custom-field label in ', 'mjschool' ); ?><strong><?php esc_html_e( 'custom-field', 'mjschool' ); ?></strong><?php esc_html_e( ' column in CSV.', 'mjschool' ); ?><br>
				=> <?php esc_html_e( 'How to add Custom Field Value? ', 'mjschool' ); ?><br>
				1)
				<?php esc_html_e( 'Add your text-field value like : ', 'mjschool' ); ?><strong><?php esc_html_e( 'hello world', 'mjschool' ); ?></strong><br>
				2 )
				<?php esc_html_e( 'Add your textarea-field value like : ', 'mjschool' ); ?><strong><?php esc_html_e( 'hello world', 'mjschool' ); ?></strong><br>
				3)
				<?php esc_html_e( 'Add your dropdown-field value like : ', 'mjschool' ); ?><strong><?php esc_html_e( 'dropdown option 1', 'mjschool' ); ?></strong><br>
				4)
				<?php esc_html_e( 'Add your date-field value like : ', 'mjschool' ); ?><strong><?php esc_html_e( '2024-01-01', 'mjschool' ); ?></strong><br>
				5)
				<?php esc_html_e( 'Add your radio-field value like : ', 'mjschool' ); ?><strong><?php esc_html_e( 'redio option 1', 'mjschool' ); ?></strong><br>
				6)
				<?php esc_html_e( 'Add your checkbox-field value like : ', 'mjschool' ); ?><strong><?php esc_html_e( 'option 1,option 2,option 3	', 'mjschool' ); ?></strong><br>
				<strong>3. <?php esc_html_e( 'Instruction for Import Custom-Field Document :', 'mjschool' ); ?></strong><br>
				1)
				<?php esc_html_e( 'Add your document in ', 'mjschool' ); ?><strong><?php esc_html_e( '/wp-content/uploads/school_assets/', 'mjschool' ); ?></strong><?php esc_html_e( ' Path', 'mjschool' ); ?><br>
				2 )
				<?php esc_html_e( 'Add your document name in ', 'mjschool' ); ?><strong><?php esc_html_e( 'custom-field', 'mjschool' ); ?></strong><?php esc_html_e( ' column in CSV. for example : ', 'mjschool' ); ?><strong><?php esc_html_e( 'hello.pdf', 'mjschool' ); ?></strong><br>
			</p>
			<div class="form-body mjschool-user-form"> <!--Form Body div.-->
				<div class="row"><!--Row Div.-->
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Upload CSV File', 'mjschool' ); ?>" name="upload_csv_file" class="btn btn-success mjschool-save-btn" />
					</div>
				</div> <!--Row Div.-->
			</div><!--Form Body div.-->
		</form><!-------- Import Student Form. ---------->
	</div><!-------- Panel Body. ---------->
	<?php
	wp_die();
}
add_action( 'wp_ajax_nopriv_mjschool_teacher_import_data', 'mjschool_teacher_import_data' );
add_action( 'wp_ajax_mjschool_teacher_import_data', 'mjschool_teacher_import_data' );
/**
 * Render the Teacher Import CSV modal via AJAX.
 *
 * Validates nonce and login status before rendering the form to 
 * upload teacher data via CSV. Initializes form validation and 
 * CSV file-type checks via JavaScript.
 *
 * @since 1.0.0
 * 
 * @return void Outputs HTML content and terminates with wp_die().
 */
function mjschool_teacher_import_data() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-import-csv-popup">
		<a href="#" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Import Data', 'mjschool' ); ?></h4>
	</div>
	<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
		<!-------- Import Teacher Form. ---------->
		<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data"><!--form div-->
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
			<div class="form-body mjschool-user-form"> <!--Form Body div.-->
				<div class="row"><!--Row Div.-->
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl" for="city_name"><?php esc_html_e( 'Select CSV file', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<div class="col-sm-12">
									<input id="csv_file" type="file" class="validate[required] form-control file csvfile_width d-inline mjschool-file-validation csv_file" name="csv_file">
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-rtl-relative-position">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="mjschool_import_teacher_mail"> <?php esc_html_e( 'Send Email', 'mjschool' ); ?></label>
										<input type="checkbox" class="mjschool-check-box-input-margin" name="mjschool_import_teacher_mail" value="1" <?php echo checked( get_option( 'mjschool_import_teacher_mail' ), 'yes' ); ?> />
										<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php wp_nonce_field( 'upload_csv_nonce' ); ?>
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Upload CSV File', 'mjschool' ); ?>" name="upload_teacher_csv_file" class="btn btn-success mjschool-save-btn" />
					</div>
				</div> <!--Row Div.-->
			</div><!--Form Body div.-->
		</form><!--Form div.-->
	</div><!--Mjschool-panel-body.-->
	<?php
	wp_die();
}
add_action( 'wp_ajax_nopriv_mjschool_support_staff_import_data', 'mjschool_support_staff_import_data' );
add_action( 'wp_ajax_mjschool_support_staff_import_data', 'mjschool_support_staff_import_data' );
/**
 * Render the Support Staff Import CSV modal via AJAX.
 *
 * Ensures nonce and login validation before generating the form
 * for uploading support staff details via CSV. Adds JavaScript-based
 * CSV validation and form handling.
 * 
 * @since 1.0.0
 *
 * @return void Outputs HTML content and terminates with wp_die().
 */
function mjschool_support_staff_import_data() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-import-csv-popup">
		<a href="#" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Import Data', 'mjschool' ); ?></h4>
	</div>
	<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
		<!-------- Import Teacher Form. ---------->
		<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data"><!--Form div.-->
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
			<div class="form-body mjschool-user-form"> <!--Form Body div.-->
				<div class="row"><!--Row Div.-->
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px mjschool-rtl-relative-position">
								<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl" for="city_name"><?php esc_html_e( 'Select CSV file', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<div class="col-sm-12">
									<input id="csv_file" type="file" class="validate[required] form-control file csvfile_width mjschool-file-validation csv_file" name="csv_file">
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-rtl-relative-position">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="mjschool_import_staff_mail"><?php esc_html_e( 'Send Email', 'mjschool' ); ?></label>
										<input type="checkbox" class="mjschool-check-box-input-margin" name="mjschool_import_staff_mail" value="1" <?php echo checked( get_option( 'mjschool_import_staff_mail' ), 'yes' ); ?> /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php wp_nonce_field( 'upload_csv_nonce' ); ?>
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Upload CSV File', 'mjschool' ); ?>" name="upload_staff_csv_file" class="btn btn-success mjschool-save-btn" />
					</div>
				</div> <!--Row Div.-->
			</div><!--Form Body div.-->
		</form><!--Form div.-->
	</div><!--Mjschool-panel-body.-->
	<?php
	wp_die();
}
add_action( 'wp_ajax_nopriv_mjschool_parent_import_data', 'mjschool_parent_import_data' );
add_action( 'wp_ajax_mjschool_parent_import_data', 'mjschool_parent_import_data' );
/**
 * Render the Parent Import CSV modal via AJAX.
 *
 * Checks nonce and login requirements before displaying the form
 * to import parent information using a CSV file. Includes JavaScript 
 * for validating file type and handling form interactions.
 *
 * @since 1.0.0
 * 
 * @return void Outputs HTML content and terminates with wp_die().
 */
function mjschool_parent_import_data() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-import-csv-popup">
		<a href="#" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Import Data', 'mjschool' ); ?></h4>
	</div>
	<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
		<!-------- Import Teacher Form. ---------->
		<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data"><!--Form div.-->
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
			<div class="form-body mjschool-user-form"> <!--Form Body div.-->
				<div class="row"><!--Row Div.-->
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl" for="city_name"><?php esc_html_e( 'Select CSV file', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<div class="col-sm-12">
									<input id="csv_file" type="file" class="validate[required] form-control file csvfile_width mjschool-file-validation csv_file" name="csv_file">
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-rtl-relative-position">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="mjschool_import_parent_mail"> <?php esc_html_e( 'Send Email', 'mjschool' ); ?></label>
										<input type="checkbox" class="check_box_input_	margin" name="mjschool_import_parent_mail" value="1" <?php echo checked( get_option( 'mjschool_import_parent_mail' ), 'yes' ); ?> />&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php wp_nonce_field( 'upload_csv_nonce' ); ?>
					<p>
						<strong><?php esc_html_e( 'Instruction for Profile image :', 'mjschool' ); ?></strong><br>
						1)
						<?php esc_html_e( 'Add ', 'mjschool' ); ?><strong><?php esc_html_e( 'user_profile', 'mjschool' ); ?></strong><?php esc_html_e( ' folder in ', 'mjschool' ); ?><strong><?php esc_html_e( '/wp-content/uploads/', 'mjschool' ); ?></strong><?php esc_html_e( ' Path', 'mjschool' ); ?><br>
						2)
						<?php esc_html_e( 'Upload the User Profile photo in ', 'mjschool' ); ?><strong><?php esc_html_e( 'user_profile', 'mjschool' ); ?></strong><?php esc_html_e( ' folder', 'mjschool' ); ?><br>
						3)
						<?php esc_html_e( 'Add your image path in ', 'mjschool' ); ?><strong><?php esc_html_e( 'user_profile', 'mjschool' ); ?></strong><?php esc_html_e( ' column in CSV. for example : ', 'mjschool' ); ?><strong><?php esc_html_e( 'user_profile/image.png', 'mjschool' ); ?></strong>
					</p>
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Upload CSV File', 'mjschool' ); ?>" name="upload_parent_csv_file" class="btn btn-success mjschool-save-btn" />
					</div>
				</div> <!--Row Div.-->
			</div><!--Form Body div.-->
		</form><!--Form div.-->
	</div><!--Mjschool-panel-body.-->
	<?php
	wp_die();
}
add_action( 'wp_ajax_nopriv_mjschool_subject_import_data', 'mjschool_subject_import_data' );
add_action( 'wp_ajax_mjschool_subject_import_data', 'mjschool_subject_import_data' );
/**
 * Renders the Subject Import CSV popup and initializes related scripts.
 *
 * This function validates nonce and user authentication before outputting
 * HTML + JavaScript for the subject import modal, including form validation,
 * teacher selection, file validation, and teacher list loading via AJAX.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML, JavaScript, and terminates execution with wp_die().
 */
function mjschool_subject_import_data() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$teacher_obj = new Mjschool_Teacher();
	?>
	<div class="modal-header mjschool-import-csv-popup">
		
		<a href="#" class="close-btn-cat badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 class="modal-title"><?php esc_html_e( 'Import Data', 'mjschool' ); ?></h4>
		
	</div>
	<div class="mjschool-panel-body"><!-------- Panel Body. ---------->
		<!-------- Import Student Form. ---------->
		<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data" class="mjschool_padding_10px">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
			<div class="form-body mjschool-user-form"> <!--Form Body div.-->
				<div class="row"><!--Row Div.-->
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
						<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="class_name" class="form-control validate[required] class_by_teacher" id="class_list">
							<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"> <?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
						<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
						<select name="class_section" class="form-control" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-md-6 mjschool-rs-mb-15px mjschool-rtl-margin-top-15px">
						<div id="mjschool-res-rtl-width-100px" class="mjschool-res-rtl-width-100px mjschool-rtl-subject-import-data-multiple col-sm-12 mjschool-rtl-padding-left-right-0px mjschool-multiselect-validation-teacher mjschool-multiple-select teacher_list">
							<?php
							$teachval          = array();
							$teacherdata_array = mjschool_get_users_data( 'teacher' );
							?>
							<select name="subject_teacher[]" multiple="multiple" id="subject_teacher" class="form-control validate[required] teacher_list">
								<?php
								foreach ( $teacherdata_array as $teacherdata ) {
									?>
									<option value="<?php echo esc_attr( $teacherdata->ID ); ?>" <?php echo esc_attr( $teacher_obj->mjschool_in_array_r( $teacherdata->ID, $teachval ) ) ? 'selected' : ''; ?>><?php echo esc_html( $teacherdata->display_name ); ?></option>
									<?php
								}
								?>
							</select>
							<span class="mjschool-multiselect-label">
								<label class="ml-1 mjschool-custom-top-label top" for="subject_teacher"><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?><span class="required">*</span></label>
							</span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Select CSV file', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<div class="col-sm-12">
									<input id="csv_file" type="file" class="validate[required] form-control file csvfile_width d-inline mjschool-file-validation csv_file" name="csv_file">
								</div>
							</div>
						</div>
					</div>
				</div> <!--Row Div.-->
			</div><!--Form Body div.-->
			<?php wp_nonce_field( 'upload_subject_admin_nonce' ); ?>
			<div class="form-body mjschool-user-form"> <!--Form Body div.-->
				<div class="row"><!--Row Div.-->
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Upload CSV File', 'mjschool' ); ?>" name="upload_csv_file" class="btn btn-success mjschool-save-btn" />
					</div>
				</div> <!--Row Div.-->
			</div><!--Form Body div.-->
		</form><!-------- Import Student Form. ---------->
	</div><!-------- Panel Body. ---------->
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_multiple_day', 'mjschool_load_multiple_day' );
add_action( 'wp_ajax_nopriv_mjschool_load_multiple_day', 'mjschool_load_multiple_day' );
/**
 * Loads AJAX markup and datepickers for single or multiple day leave requests.
 *
 * Validates the AJAX nonce and user authentication, then returns dynamic 
 * HTML + JavaScript based on the leave duration type (single day or multiple days).
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML/JS content and terminates using wp_die().
 */
function mjschool_load_multiple_day() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$obj_leave = new Mjschool_Leave();
	$duration  = isset($_REQUEST['duration']) ? sanitize_text_field(wp_unslash($_REQUEST['duration'])) : '';
	$leave_id  = isset($_REQUEST['duration']) ? sanitize_text_field(wp_unslash($_REQUEST['idset'])) : '';
	$edit      = 0;
	if ( $leave_id != '' ) {
		$edit   = 1;
		$result = $obj_leave->mjschool_get_single_leave( $leave_id );
	}
	?>
	<?php
	if ( $duration === 'more_then_day' ) {
		?>
		<div class="row">
			<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
				<div class="form-group input">
					<div class="col-md-12 form-control">
						<input id="leave_start_date" class="form-control validate[required] leave_start_date datepicker1" autocomplete="off" type="text" name="start_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $result->start_date ) ); } elseif ( isset( $_POST['start_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
						<label class="active" for="leave_start_date"><?php esc_html_e( 'Leave Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					</div>
				</div>
			</div>
			<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
				<div class="form-group input">
					<div class="col-md-12 form-control">
						<input id="leave_end_date" class="form-control validate[required] leave_end_date datepicker2" type="text" name="end_date" autocomplete="off" value="<?php if ( $edit ) { echo esc_attr( date( 'Y-m-d', strtotime( $result->end_date ) ) ); } elseif ( isset( $_POST['end_date'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['end_date'])) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
						<label class="active" for="leave_end_date"><?php esc_html_e( 'Leave End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					</div>
				</div>
			</div>
		</div>
		<?php
	} else {
		?>
		<div class="form-group input">
			<div class="col-md-12 form-control">
				<input id="leave_start_date" class="form-control validate[required] leave_start_date start_date datepicker1" autocomplete="off" type="text" name="start_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $result->start_date ) ); } elseif ( isset( $_POST['start_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
				<label class="active" for="leave_start_date"><?php esc_html_e( 'Leave Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
			</div>
		</div>
		<?php
	}
	die();
}
add_action( 'wp_ajax_mjschool_admission_repot_load_date', 'mjschool_admission_repot_load_date' );
add_action( 'wp_ajax_nopriv_mjschool_admission_repot_load_date', 'mjschool_admission_repot_load_date' );
/**
 * Loads date filter fields for the admission report via AJAX.
 *
 * Validates request nonce and login status, then renders datepicker inputs
 * for either a custom period or a default date type.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML/JavaScript for date filters and ends execution.
 */
function mjschool_admission_repot_load_date() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$date_type = isset($_REQUEST['date_type']) ? sanitize_text_field( wp_unslash($_REQUEST['date_type']) ) : '';
	?>
	<?php
	if ( $date_type === 'period' ) {
		?>
		<div class="row">
			<div class="col-md-6 mb-2">
				<div class="form-group input">
					<div class="col-md-12 form-control">
						<input type="text" id="report_sdate" class="form-control" name="start_date" value="<?php if ( isset( $_REQUEST['start_date'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['start_date'])) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>
						<label for="report_sdate" class="active"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
					</div>
				</div>
			</div>
			<div class="col-md-6 mb-2">
				<div class="form-group input">
					<div class="col-md-12 form-control">
						<input type="text" id="report_edate" class="form-control" name="end_date" value="<?php if ( isset( $_REQUEST['edate'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['end_date'])) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>
						<label for="report_edate" class="active"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	die();
}
add_action( 'wp_ajax_mjschool_edit_section', 'mjschool_edit_section' );
add_action( 'wp_ajax_nopriv_mjschool_edit_section', 'mjschool_edit_section' );
/**
 * Loads the Edit Section popup form via AJAX.
 *
 * Validates nonce and user login, fetches section details, 
 * and renders the editable form to update section information.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML for the section edit form and ends execution.
 */
function mjschool_edit_section() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$model          = isset($_REQUEST['model']) ? sanitize_text_field(wp_unslash($_REQUEST['model'])) : '';
	$cat_id         = isset($_REQUEST['cat_id']) ? sanitize_text_field(wp_unslash($_REQUEST['cat_id'])) : '';
	$retrieved_data = mjschool_single_section( $cat_id );
	?>
	<div class="form-body mjschool-user-form">
		<div class="row">
			<div class="col-md-10 mjschool-width-70px mjschool-margin-right-10px-res">
				<div class="form-group input mjschool-rtl-margin-0px">
					<div class="col-md-12 form-control">
						<input type="text" class="validate[required,custom[popup_category_validation]]" name="section_name" maxlength="50" value="<?php echo esc_attr( $retrieved_data->section_name ); ?>" id="section_name">
					</div>
				</div>
			</div>
			<div class="row col-md-2 mjschool-margin-top-10px-web mjschool-padding-left-0-res mjschool-width-30px mjschool-margin-top-13px-res" id="<?php echo esc_attr( $retrieved_data->id ); ?>">
				
				<div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0">
					<a class="mjschool-btn-cat-update-cancel" model="<?php echo esc_attr($model); ?>" href="#" id="<?php echo esc_attr($retrieved_data->id); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-cancel.png"); ?>"></a>
				</div>
				<div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-left-0">
					<a class="mjschool-btn-cat-update" model="<?php echo esc_attr($model); ?>" href="#" id="<?php echo esc_attr($retrieved_data->id); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-save.png"); ?>"> </a>
				</div>
				
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_teacher_by_class', 'mjschool_load_teacher_by_class' );
add_action( 'wp_ajax_nopriv_mjschool_load_teacher_by_class', 'mjschool_load_teacher_by_class' );
/**
 * Loads teachers based on selected class via AJAX.
 *
 * After validating request integrity, it fetches teachers mapped to a class ID
 * and prints <option> elements for a dropdown/select field.
 *
 * @since 1.0.0
 *
 * @return void Outputs <option> tags for teachers and ends execution.
 */
function mjschool_load_teacher_by_class() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id    = sanitize_text_field(wp_unslash($_POST['class_list']));
	$teacherdata = mjschool_get_teacher_by_class_id( $class_id );
	foreach ( $teacherdata as $retrieved_data ) {
		if ( $retrieved_data->ID != '' ) {
			echo '<option value=' . esc_attr( $retrieved_data->ID ) . '> ' . esc_html( $retrieved_data->display_name ) . '</option>';
		}
	}
	die();
}
add_action( 'wp_ajax_mjschool_update_cetogory_popup_value', 'mjschool_update_cetogory_popup_value' );
add_action( 'wp_ajax_nopriv_mjschool_update_cetogory_popup_value', 'mjschool_update_cetogory_popup_value' );
/**
 * Updates a category name inside a popup and returns updated HTML rows.
 *
 * Validates nonce and login status, updates the post title of the category,
 * and returns new row markup and updated <option> for dropdowns.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON containing updated HTML rows and options.
 */
function mjschool_update_cetogory_popup_value() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$model         = sanitize_text_field( wp_unslash($_REQUEST['model']) );
	$cat_id        = sanitize_text_field( wp_unslash($_REQUEST['cat_id']) );
	$category_name = sanitize_text_field( wp_unslash($_REQUEST['category_name']) );
	$dlt_image     = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' );
	$edit_image    = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-edit.png' );
	$edited_post   = array(
		'ID'         => $cat_id,
		'post_title' => $category_name,
	);
	$result        = wp_update_post( $edited_post );
	 
	if ($model === 'mjschool_bookperiod' ) {
		$row1 = '<div class="col-md-10 mjschool-width-70px">' . get_the_title($cat_id) . '' . esc_html__( 'Days', 'mjschool' ) . '</div>';
		$row1 .= '<div class="row col-md-2 mjschool-padding-left-0-res mjschool-width-30px" id=' . $cat_id . '><div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0"><a href="#" class="btn-delete-cat_new" model="' . $model . '" id="' . $cat_id . '"><img src="' . $dlt_image . '"></a></div><div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-right-0"><a class="mjschool-btn-edit-cat_popup" model="' . $model . '" href="#" id="' . $cat_id . '"><img src="' . $edit_image . '" class="mjschool_height_width_40px"></a></div></div>';
		$option = "<option value='$cat_id'>" . sanitize_text_field(wp_unslash($_REQUEST['category_name'])) . '' . esc_html__( 'Days', 'mjschool' ) . '' . "</option>";
	} else {
		$row1 = '<div class="col-md-10 mjschool-width-70px">' . get_the_title($cat_id) . '</div>';
		$row1 .= '<div class="row col-md-2 mjschool-padding-left-0-res mjschool-width-30px" id=' . $cat_id . '><div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0"><a href="#" class="btn-delete-cat_new" model="' . $model . '" id="' . $cat_id . '"><img src="' . $dlt_image . '"></a></div><div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-right-0"><a class="mjschool-btn-edit-cat_popup" model="' . $model . '" href="#" id="' . $cat_id . '"><img src="' . $edit_image . '"></a></div></div>';
		$option = "<option value='$cat_id'>" . sanitize_text_field(wp_unslash($_REQUEST['category_name'])) . "</option>";
	}
	 
	$array_var[] = $row1;
	$array_var[] = $option;
	echo json_encode( $array_var );
	die();
}
add_action( 'wp_ajax_mjschool_update_cancel_popup', 'mjschool_update_cancel_popup' );
add_action( 'wp_ajax_nopriv_mjschool_update_cancel_popup', 'mjschool_update_cancel_popup' );
/**
 * Restores the category list when a popup update action is cancelled.
 *
 * Validates security checks, retrieves all categories of the given model,
 * and outputs the original category listing markup.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML for restored category rows and terminates execution.
 */
function mjschool_update_cancel_popup() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$model      = sanitize_text_field( sanitize_text_field(wp_unslash($_REQUEST['model'])) );
	$cat_result = mjschool_get_all_category( $model );
	$i          = 1;
	if ( ! empty( $cat_result ) ) {
		foreach ( $cat_result as $retrieved_data ) {
			?>
			<div class="row mjschool-new-popup-padding" id="<?php echo 'cat_new-' . esc_attr( $retrieved_data->ID ) . ''; ?>">
				<div class="col-md-10 mjschool-width-70px">
					<?php echo esc_html( $retrieved_data->post_title ); ?>
				</div>
				<div class="row col-md-2 mjschool-padding-left-0-res mjschool-width-30px" id="<?php echo esc_attr( $retrieved_data->ID ); ?>">
					
					<div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0">
						<a href="#" class="btn-delete-cat_new" model="<?php echo esc_attr($model); ?>" id="<?php echo esc_attr($retrieved_data->ID); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></a>
					</div>
					<div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-right-0">
						<a class="mjschool-btn-edit-cat_popup" model="<?php echo esc_attr($model); ?>" href="#" id="<?php echo esc_attr($retrieved_data->ID); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>"></a>
					</div>
					
				</div>
			</div>
			<?php
			++$i;
		}
	}
	die();
}
add_action( 'wp_ajax_mjschool_edit_popup_value', 'mjschool_edit_popup_value' );
add_action( 'wp_ajax_nopriv_mjschool_edit_popup_value', 'mjschool_edit_popup_value' );
/**
 * Load and display the edit popup HTML for category/module editing.
 *
 * Validates the AJAX nonce, checks if the user is logged in, 
 * then prints a form containing editable category data.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML and terminates execution with wp_die().
 */
function mjschool_edit_popup_value() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$model          = sanitize_text_field( wp_unslash($_REQUEST['model']) );
	$cat_id         = sanitize_text_field( wp_unslash($_REQUEST['cat_id']) );
	$category_value = get_the_title( $cat_id );
	?>
	<div class="form-body mjschool-user-form">
		<div class="row">
			<div class="col-md-10 mjschool-width-70px mjschool-margin-right-10px-res">
				<div class="form-group input mjschool-rtl-padding-top">
					<div class="col-md-12 form-control">
						<input type="text" class="validate[required,custom[popup_category_validation]]" name="category_name" maxlength="50" value="<?php echo esc_attr( $category_value ); ?>" id="category_name_edit">
					</div>
				</div>
			</div>
			<div class="row col-md-2 mjschool-margin-top-10px-web mjschool-padding-left-0-res mjschool-width-30px mjschool-margin-top-13px-res" id="<?php echo esc_attr( $cat_id ); ?>">
				
				<div class="col-md-6 mjschool-padding-left-0 mjschool-width-50-res">
					<a class="mjschool-btn-cat-update-cancel_popup" model="<?php echo esc_attr($model); ?>" href="#" id="<?php echo esc_attr($cat_id); ?>">
						<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-cancel.png"); ?>">
					</a>
				</div>
				<div class="col-md-6 mjschool-width-50-res mjschool-edit-btn-padding-left-25px-res">
					<a class="mjschool-btn-cat-update_popup" model="<?php echo esc_attr($model); ?>" href="#" id="<?php echo esc_attr($cat_id); ?>">
						<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-save.png"); ?>"> 
					</a>
				</div>
				
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_more_document', 'mjschool_load_more_document' );
add_action( 'wp_ajax_nopriv_mjschool_load_more_document', 'mjschool_load_more_document' );
/**
 * AJAX callback to dynamically add more document input fields.
 *
 * Validates the AJAX nonce. (Login check is optional and currently disabled.)
 * Outputs the HTML structure for additional document title/file fields.
 *
 * @since 1.0.0
 *
 * @return void Prints HTML and terminates execution with wp_die().
 */
function mjschool_load_more_document() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	// if ( ! is_user_logged_in() ) {
	// 	wp_die( 'You must be logged in.' );
	// }
	?>
	<div class="form-body mjschool-user-form">
		<div class="row">
			<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
				<div class="form-group input">
					<div class="col-md-12 form-control">
						<input class="form-control text-input" maxlength="50" type="text" value="" name="document_title[]">
						<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
					</div>
				</div>
			</div>
			<div class="col-md-5 col-10 col-sm-1">
				<div class="form-group input">
					<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
						<label for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></label>
						<div class="col-sm-12 mjschool-display-flex">
							<input name="document_file[]" type="file" class="p-1 form-control mjschool-file-validation file" value="<?php esc_attr_e( 'Upload image', 'mjschool' ); ?>" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-1 col-2 col-sm-3 col-xs-12">
				<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-float-right mjschool-remove-certificate mjschool-input-btn-height-width">
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_more_subject_information', 'mjschool_load_more_subject_information' );
add_action( 'wp_ajax_nopriv_mjschool_load_more_subject_information', 'mjschool_load_more_subject_information' );
/**
 * AJAX callback to load additional subject input fields dynamically.
 *
 * Checks nonce and login status, initializes dynamic JS for subject drop-downs,
 * and prints HTML for subject code, class, section, and teacher selection.
 *
 * @since 1.0.0
 *
 * @return void Outputs dynamic form fields and terminates via wp_die().
 */
function mjschool_load_more_subject_information() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$x = isset($_REQUEST['click_val']) ? sanitize_text_field( wp_unslash($_REQUEST['click_val']) ) : '';
	?>
	<!-- Trigger Js. -->
	<div class="form-body mjschool-user-form-for-subject" data-subject-id="<?php echo esc_attr($x); ?>">
	<div class="form-body mjschool-user-form">
		<div class="row">
			<hr>
			<div class="col-md-6">
				<div class="form-group input">
					<div class="col-md-12 form-control">
						<input id="subject_code_<?php echo esc_attr( $x ); ?>" class="form-control validate[required,custom[onlyLetterNumber],maxSize[8],min[0]] text-input" type="text" maxlength="50" value="" name="subject_code[]">
						<label for="subject_code_<?php echo esc_attr( $x ); ?>"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?><span class="required">*</span></label>
					</div>
				</div>
			</div>
			<div class="col-md-6 input mjschool-error-msg-left-margin">
				<label class="ml-1 mjschool-custom-top-label top" for="class_list_subject_<?php echo esc_attr( $x ); ?>"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label>
				<select name="subject_class[]" class="form-control validate[required] mjschool-width-100px class_by_teacher_subject_<?php echo esc_attr( $x ); ?> mjschool_heights_47px" id="class_list_subject_<?php echo esc_attr( $x ); ?>">
					<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
						<?php 
						foreach ( mjschool_get_all_class() as $classdata ) { 
							?>
							<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?> </option>
							<?php 
						} 
						?>
				</select>
			</div>
			<div class="col-md-6 input">
				<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-section-subject_<?php echo esc_attr( $x ); ?>"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
				<select name="class_section[]" class="form-control mjschool-width-100px mjschool_heights_47px" id="mjschool-class-section-subject_<?php echo esc_attr( $x ); ?>">
					<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
				</select>
			</div>
			<?php
			$school_obj = new MJSchool_Management( get_current_user_id() );
			if ( $school_obj->role === 'teacher' ) {
				$user_id = get_current_user_id();
				?>
				<div class="col-md-5 input">
					<input type="hidden" name="subject_teacher[0][]" value="<?php echo esc_attr( $user_id ); ?>">
				</div>
				<?php
			} else {
				?>
				<div class="col-md-5 col-10 mjschool-rtl-margin-top-15px mjschool-teacher-list-multiselect">
					<div class="col-sm-12 mjschool-multiselect-validation-teacher mjschool-multiple-select mjschool-rtl-padding-left-right-0px mjschool-res-rtl-width-100px">
						<?php $teacherdata_array = mjschool_get_users_data( 'teacher' ); ?>
						<select name="subject_teacher[<?php echo esc_attr( $x ); ?>][]" multiple="multiple" id="subject_teacher_subject_<?php echo esc_attr( $x ); ?>" class="form-control validate[required]">
							<?php
							foreach ( $teacherdata_array as $teacherdata ) {
								?>
								<option value="<?php echo esc_attr( $teacherdata->ID ); ?>"> <?php echo esc_html( $teacherdata->display_name ); ?></option>
							<?php } ?>
						</select>
						<span class="mjschool-multiselect-label">
							<label class="ml-1 mjschool-custom-top-label top" for="subject_teacher_subject_<?php echo esc_attr( $x ); ?>"><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?><span class="required">*</span></label>
						</span>
					</div>
				</div>
				<?php
			}
			?>
			<div class="col-md-1 col-2 col-sm-3 col-xs-12">
				<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-float-right mjschool-remove-certificate mjschool-input-btn-height-width">
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_other_user_homework', 'mjschool_load_other_user_homework' );
add_action( 'wp_ajax_nopriv_mjschool_load_other_user_homework', 'mjschool_load_other_user_homework' );
/**
 * AJAX callback to load user dropdown options based on role (student/teacher/etc.).
 *
 * Validates the AJAX nonce and ensures the user is logged in.
 * Fetches all users with the given role and outputs <option> elements.
 *
 * @since 1.0.0
 *
 * @return void Outputs dropdown <options> and ends execution using die().
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 */
function mjschool_load_other_user_homework() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_POST['document_for'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	global $wpdb;
	$document_for = sanitize_text_field(wp_unslash($_POST['document_for']));
	$defaultmsg   = esc_html( 'All ' . $document_for );
	global $wpdb;
	echo "<option value='all " . esc_attr( $document_for ) . "'>" . esc_html( $defaultmsg ) . '</option>';
	$retrieve_data = get_users( array( 'role' => $document_for ) );
	foreach ( $retrieve_data as $users ) {
		echo '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( $users->first_name ) . ' ' . esc_html( $users->last_name ) . '</option>';
	}
	die();
}
add_action( 'wp_ajax_mjschool_ajax_view_result', 'mjschool_ajax_view_result' );
/**
 * AJAX handler for displaying a student's exam result popup.
 *
 * Validates nonce and user login status, fetches student marks, 
 * calculates totals, percentages, and GPA, then outputs a result table.
 *
 * @since 1.0.0
 *
 * @return void Prints result modal HTML and terminates with die().
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 */
function mjschool_ajax_view_result() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['student_id'], $_REQUEST['exam_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$obj_mark      = new Mjschool_Marks_Manage();
	$uid           = intval( wp_unslash($_REQUEST['student_id'] ));
	$exam_id       = intval( wp_unslash($_REQUEST['exam_id'] ));
	$user          = get_userdata( $uid );
	$user_meta     = get_user_meta( $uid );
	$class_id      = $user_meta['class_name'][0];
	$section_id    = $user_meta['class_section'][0];
	$subject       = $obj_mark->mjschool_student_subject_list( $class_id, $section_id );
	$total_subject = count( $subject );
	$total         = 0;
	$grade_point   = 0;
	?>
	<div class="mjschool-panel-white">
		<div class="modal-header modal_header_height mjschool-model-header-padding mjschool-dashboard-model-header">
			
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design">
				<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>">
			</a>
			<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( mjschool_get_user_name_by_id($uid ) ); ?>'s <?php esc_html_e( 'Result', 'mjschool' ) ?></h4>
		</div>
		<?php
		if ( ! empty( $exam_id ) ) {
			$exam_name = mjschool_get_exam_name_id($exam_id);
			?>
			<div class="clearfix"></div>
			<div id="mjschool-accordion" class="accordion student_accordion " aria-multiselectable="true" role="tablist">
				<div class="mt-2 accordion-item">
					<h4 class="accordion-header" id="heading_<?php echo esc_attr($i); ?>">
						<button class="accordion-button mjschool-student-result-collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?php echo esc_attr($i); ?>" aria-expanded="true" aria-controls="collapse_<?php echo esc_attr($i); ?>">
							<div class="col-md-10 col-7">
								<span class="mjschool-student-exam-result"><?php esc_html_e( 'Exam Results : ', 'mjschool' ); ?></span>
								&nbsp;
								<span class="mjschool-student-exam-name"><?php echo esc_html( $exam_name); ?></span>
							</div>
							<?php
							$new_marks = [];
							foreach ($subject as $sub) {
								$marks = $obj_mark->mjschool_get_marks($exam_id, $class_id, $sub->subid, $uid);
								if ( ! empty( $marks ) ) {
									$new_marks[$sub->subid] = $marks;
								}
							}
							if ( ! empty( $new_marks ) ) {
								?>
								<div class="col-md-2 row justify-content-end mjschool-view-result">
									<div class="col-md-5 mjschool-width-50px">
										<a href="?page=mjschool_student&print=pdf&student=<?php echo esc_attr( mjschool_encrypt_id($uid ) ); ?>&exam_id=<?php echo esc_attr( mjschool_encrypt_id($exam_id ) ); ?>" class="mjschool-float-right" target="_blank">
											<img src="<?php echo esc_url(esc_url(MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-pdf.png" ) ); ?>">
										</a>
									</div>
									<div class="col-md-4 mjschool-width-50px mjschool-rtl-margin-left-20px mjschool-exam-result-pdf-margin mjschool_margin_right_22px" >
										<a href="?page=mjschool_student&print=print&student=<?php echo esc_attr( mjschool_encrypt_id($uid ) ); ?>&exam_id=<?php echo esc_attr( mjschool_encrypt_id($exam_id ) ); ?>" class="mjschool-float-right" target="_blank">
											<img src="<?php echo esc_url(esc_url(MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-print.png" ) ); ?>">
										</a>
										
									</div>
								</div>
							<?php } else { ?>
								<span class="mjschool-student-exam-name"> <?php esc_html_e( 'No Result', 'mjschool' ); ?> </span>
							<?php } ?>
						</button>
					</h4>
					<div id="collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse show" aria-labelledby="heading_<?php echo esc_attr( $i ); ?>" data-bs-parent="#mjschool-accordion">
						<div class="view_result">
							<?php if ( ! empty( $new_marks ) ) { ?>
								<div class="table-responsive mjschool-view-result-table-responsive">
									<table class="table table-bordered">
										<tr>
											<th class="mjschool-view-result-table-heading"> <?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
											<th class="mjschool-view-result-table-heading"> <?php esc_html_e( 'Max Marks', 'mjschool' ); ?></th>
											<th class="mjschool-view-result-table-heading"> <?php esc_html_e( 'Pass Marks', 'mjschool' ); ?></th>
											<th class="mjschool-view-result-table-heading"> <?php esc_html_e( 'Obtain Mark', 'mjschool' ); ?></th>
											<th class="mjschool-view-result-table-heading"> <?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
											<th class="mjschool-view-result-table-heading"> <?php esc_html_e( 'Marks Comment', 'mjschool' ); ?></th>
										</tr>
										<?php
										$total           = 0;
										$total_max_mark  = 0;
										$grade_point     = 0;
										$total_pass_mark = 0;
										$result          = array();
										$result1         = array();
										foreach ( $subject as $sub ) {
											if ( $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid ) >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
												$result[] = 'pass';
											} else {
												$result1[] = 'fail';
											}
											$marks = isset( $new_marks[ $sub->subid ] ) ? $new_marks[ $sub->subid ] : 0;
											?>
											<tr>
												<td class="mjschool-view-result-table-value"><?php echo esc_html( $sub->sub_name ); ?></td>
												<td class="mjschool-view-result-table-value"> <?php echo esc_html( $obj_mark->mjschool_get_max_marks( $exam_id ) ); ?></td>
												<td class="mjschool-view-result-table-value"> <?php echo esc_html( $obj_mark->mjschool_get_pass_marks( $exam_id ) ); ?> </td>
												<td class="mjschool-view-result-table-value"> <?php echo esc_html( $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid ) ); ?> </td>
												<td class="mjschool-view-result-table-value"> <?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?> </td>
												<td class="mjschool-view-result-table-value"> <?php echo esc_html( $obj_mark->mjschool_get_marks_comment( $exam_id, $class_id, $sub->subid, $uid ) ); ?> </td>
											</tr>
											<?php
											$grade_point     += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
											$total           += $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
											$total_max_mark  += $obj_mark->mjschool_get_max_marks( $exam_id );
											$total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
										}
										?>
										<tfoot>
											<tr class="table_color tfoot_border mt_10 mjschool_border_bottom_1px_solid" >
												<th><?php esc_html_e( 'TOTAL MARKS', 'mjschool' ); ?></th>
												<th>
													<?php
													if ( ! empty( $total_max_mark ) ) {
														echo esc_html( $total_max_mark );
													} else {
														echo '-';
													}
													?>
												</th>
												<th>
													<?php
													if ( ! empty( $total_pass_mark ) ) {
														echo esc_html( $total_pass_mark );
													} else {
														echo '-';
													}
													?>
												</th>
												<th>
													<?php
													if ( ! empty( $total ) ) {
														echo esc_html( $total );
													} else {
														echo '-';
													}
													?>
												</th>
												<th></th>
												<th></th>
											</tr>
										</tfoot>
									</table>
									<div class="row col-md-12">
										<div class="col-md-4 view_result_total">
											<?php
											esc_html_e( 'Percentage', 'mjschool' );
											echo ' : ';
											?>
											<span class="view_result_total_int">
												<?php
												$percentage = $total / $total_max_mark * 100;
												if ( ! empty( $percentage ) ) {
													echo number_format( $percentage, 2, '.', '' );
												} else {
													echo '-';
												}
												?>
											</span>
										</div>
										<div class="col-md-4 view_result_total">
											<?php
											esc_html_e( 'GPA(grade point average)', 'mjschool' );
											echo ' : ';
											?>
											<span class="view_result_total_int"><?php echo esc_html( round( $grade_point / max( count( $subject ), 1 ), 2 ) ); ?></span>
										</div>
										<div class="col-md-4 view_result_total">
											<?php
											esc_html_e( 'Result', 'mjschool' );
											echo ' : ';
											?>
											<span class="view_result_total_int <?php if ( ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) || ( isset( $result1 ) && in_array( 'fail', $result1 ) ) ) { echo 'text-danger'; } elseif ( isset( $result ) && in_array( 'pass', $result ) ) { echo 'text-success'; } ?>">
												<?php
												if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
													esc_html_e( 'Fail', 'mjschool' );
												} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
													esc_html_e( 'Pass', 'mjschool' );
												} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
													esc_html_e( 'Fail', 'mjschool' );
												} else {
													echo '-';
												}
												?>
											</span>
										</div>
									</div>
								</div>
							<?php } else { ?>
								<div class="col-md-12 text-center p-3">
									<span class="mjschool-student-exam-name">
										<?php esc_html_e( 'No Result Available.', 'mjschool' ); ?>
									</span>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="modal-header modal_header_height mjschool-model-header-padding mjschool-dashboard-model-header">
				<h6 id="myLargeModalLabel"><?php esc_html_e( 'No Result Found', 'mjschool' ); ?></h6>
			</div>
			<?php
		}
		die();
		?>
	</div>
	<?php
}
add_action( 'wp_ajax_mjschool_load_more_contributions', 'mjschool_load_more_contributions' );
add_action( 'wp_ajax_nopriv_mjschool_load_more_contributions', 'mjschool_load_more_contributions' );
/**
 * AJAX callback to dynamically add additional contribution fields.
 *
 * Validates nonce, checks user login, and prints input fields 
 * for contribution label and marks.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML and terminates with wp_die().
 */
function mjschool_load_more_contributions() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="form-body mjschool-user-form">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group input">
					<div class="col-md-12 form-control">
						<input class="form-control" id="contributions_label" maxlength="50" type="text" value="" name="contributions_label[]">
						<label for="contributions_label"><?php esc_html_e( 'Contributions Label', 'mjschool' ); ?></label>
					</div>
				</div>
			</div>
			<div class="col-md-5 col-10">
				<div class="form-group input mjschool-error-msg-left-margin">
					<div class="col-md-12 form-control">
						<input class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="" id="contributions_mark" name="contributions_mark[]">
						<label for="contributions_mark"><?php esc_html_e( 'Contributions Marks', 'mjschool' ); ?></label>
					</div>
				</div>
			</div>
			<div class="col-md-1 col-2 col-sm-3 col-xs-12">
				<input type="image" onclick="mjschool_delete_parent_elementConstribution(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-input-btn-height-width">
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
add_action( 'wp_ajax_mjschool_load_library_card_no', 'mjschool_load_library_card_no' );
add_action( 'wp_ajax_nopriv_mjschool_load_library_card_no', 'mjschool_load_library_card_no' );
/**
 * Load the library card number for a specific student via AJAX.
 *
 * @since 1.0.0
 *
 * @return void Sends JSON response containing the student's library card number.
 */
function mjschool_load_library_card_no() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['user_id']) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	$user_id           = intval( wp_unslash($_REQUEST['user_id']) );
	$obj_lib           = new Mjschool_Library();
	$library_card_no   = $obj_lib->mjschool_get_library_card_for_student( $user_id );
	$library_card_name = '';
	if ( ! empty( $library_card_no ) ) {
		$library_card = $library_card_no[0]->library_card_no;
		if ( ! empty( $library_card ) ) {
			$library_card_name = trim( $library_card );
		}
	}
	wp_send_json_success( $library_card_name );
}
add_action( 'wp_ajax_mjschool_add_more_merge_result', 'mjschool_add_more_merge_result' );
add_action( 'wp_ajax_nopriv_mjschool_add_more_merge_result', 'mjschool_add_more_merge_result' );
/**
 * Load additional merge exam result rows via AJAX.
 *
 * Outputs an HTML block for exam selection and weightage input.
 * 
 * @since 1.0.0
 *
 * @return void Outputs HTML and terminates execution.
 */
function mjschool_add_more_merge_result() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_name   =isset($_REQUEST['class_name']) ? sanitize_text_field(wp_unslash($_REQUEST['class_name'])) : '';
	$section_name =isset($_REQUEST['section_name']) ? sanitize_text_field(wp_unslash($_REQUEST['section_name'])) : '';
	?>
	<div class="form-body mjschool-user-form">
		<div class="row">
			<div class="col-md-4 input">
				<label class="ml-1 mjschool-custom-top-label top" for="exam_id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
				<select name="exam_id[]" id="exam_id" class="mjschool-line-height-30px form-control exam_list validate[required] text-input">
					<option value=""><?php esc_html_e( 'Select Exam', 'mjschool' ); ?></option>
					<?php
					$exam_data = mjschool_get_all_exam_by_class_id_and_section_id_array( $class_name, $section_name );
					if ( ! empty( $exam_data ) ) {
						foreach ( $exam_data as $retrieved_data ) {
							?>
							<option value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" <?php selected( sanitize_text_field(wp_unslash($_POST['exam_id'])), $retrieved_data->exam_id ); ?>><?php echo esc_html( $retrieved_data->exam_name ); ?></option>
							<?php
						}
					}
					?>
				</select>
			</div>
			<div class="col-md-4 col-10">
				<div class="form-group input mjschool-error-msg-left-margin">
					<div class="col-md-12 form-control">
						<input class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="" name="weightage[]">
						<label for="userinput1" class="ms-2"><?php esc_html_e( 'Weightage of the exam(%)', 'mjschool' ); ?></label>
					</div>
				</div>
			</div>
			<div class="col-md-1 col-2 col-sm-3 col-xs-12">
				<input type="image" onclick="mjschool_delete_parent_elementExamMergeSettings(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-input-btn-height-width">
			</div>
		</div>
	</div>
	<?php
	echo esc_html( $library_card_name );
	die();
}
add_action( 'wp_ajax_mjschool_student_list', 'mjschool_student_list' );
add_action( 'wp_ajax_nopriv_mjschool_student_list', 'mjschool_student_list' );
/**
 * Fetch and return the filtered student list for DataTables via AJAX.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON formatted data for DataTables.
 */
function mjschool_student_list() {
	if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'mjschool_student_list_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce', 403 );
	}
	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$role = mjschool_get_user_role( get_current_user_id() );
	if ( $role === 'administrator' ) {
		$user_access_add    = '1';
		$user_access_edit   = '1';
		$user_access_delete = '1';
		$user_access_view   = '1';
	} else {
		$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'student' );
		$user_access_add    = $user_access['add'];
		$user_access_edit   = $user_access['edit'];
		$user_access_delete = $user_access['delete'];
		$user_access_view   = $user_access['view'];
	}
	global $wpdb, $school_obj;
	$start    = isset( $_REQUEST['iDisplayStart'] ) ? intval( wp_unslash($_REQUEST['iDisplayStart'] )) : 0;
	$length   = isset( $_REQUEST['iDisplayLength'] ) ? intval( wp_unslash($_REQUEST['iDisplayLength'] ) ): 10;
	$sSearch  = isset( $_REQUEST['sSearch'] ) ? sanitize_text_field( wp_unslash($_REQUEST['sSearch'] ) ): '';
	$students = array();
	if ( $role === 'student' ) {
		$own_data = $user_access['own_data'];
		if ( $own_data === '1' ) {
			$user_id    = get_current_user_id();
			$students[] = get_userdata( $user_id );
		} else {
			$students = mjschool_get_users_data( 'student' );
		}
	} elseif ( $role === 'teacher' ) {
		$own_data = $user_access['own_data'];
		if ( $own_data === '1' ) {
			$user_id   = get_current_user_id();
			$class_id  = get_user_meta( $user_id, 'class_name', true );
			$exlude_id = mjschool_approve_student_list();
			
			$students = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
			
		} else {
			$students = mjschool_get_users_data( 'student' );
		}
	} elseif ( $role === 'parent' ) {
		$students = $school_obj->child_list;
	} elseif ( $role === 'supportstaff' ) {
		$own_data = $user_access['own_data'];
		$user_id  = get_current_user_id();
		if ( $own_data === '1' ) {
			
			$students = get_users([
				'role' => 'student',
				'meta_query' => [
					[
						'key' => 'created_by',
						'value' => $user_id,
						'compare' => '='
					]
				]
			]);
			
		} else {
			$students = mjschool_get_users_data( 'student' );
		}
	} elseif ( $role === 'administrator' ) {
		// Admin role already handled by get_users with limit.
		$students = get_users(
			array(
				'role'    => 'student',
				'orderby' => 'ID',
				'order'   => 'DESC',
			)
		);
	} elseif( $role === 'management' ){
        $own_data = $user_access['own_data'];
        $user_id  = get_current_user_id();
        if ( $own_data === '1' ) {
            
            $students = get_users([
                'role' => 'student',
                'meta_query' => [
                    [
                        'key' => 'created_by',
                        'value' => $user_id,
                        'compare' => '='
                    ]
                ]
            ]);
            
        } else {
            $students = mjschool_get_users_data( 'student' );
        }
    }
	// If not admin, we apply manual search and pagination.
	if ( ! empty( $sSearch ) ) {
		$students = array_filter(
			$students,
			function ( $student ) use ( $sSearch ) {
				$uid       = $student->ID;
				$name      = $student->display_name;
				$email     = $student->user_email;
				$phone     = get_user_meta( $uid, 'mobile_number', true );
				$class     = get_user_meta( $uid, 'class_name', true );
				$section   = get_user_meta( $uid, 'class_section', true );
				$classname = mjschool_get_class_section_name_wise( $class, $section );
				$roll_id   = get_user_meta( $uid, 'roll_id', true );
				$gender    = get_user_meta( $uid, 'gender', true );
				$admission = get_user_meta( $uid, 'admission_no', true );
				$status    = get_user_meta( $uid, 'hash', true ) ? 'Deactive' : 'Active';
				$haystack  = strtolower(
					$name . ' ' . $email . ' ' . $phone . ' ' . $classname . ' ' .
					$roll_id . ' ' . $gender . ' ' . $admission . ' ' . $status
				);
				return strpos( $haystack, strtolower( $sSearch ) ) !== false;
			}
		);
	}
	$total_records  = count( $students );
	$students       = array_slice( $students, $start, $length );
	$iTotal         = count( get_users( array( 'role' => 'student' ) ) ); // Full count for pagination
	$iFilteredTotal = $total_records;
	$output         = array(
		'sEcho'                => intval( wp_unslash($_REQUEST['sEcho']) ),
		'iTotalRecords'        => $iTotal,
		'iTotalDisplayRecords' => $iFilteredTotal,
		'aaData'               => array(),
	);
	foreach ( $students as $user_info ) {
		$uid        = $user_info->ID;
		$student_id = mjschool_encrypt_id( $uid );
		// Lol.
		$class_name = get_user_meta( $uid, 'class_name', true );
		$phone      = $user_info->mobile_number ?? get_user_meta( $uid, 'mobile_number', true );
		$gender     = get_user_meta( $uid, 'gender', true );
		$view_nonce = mjschool_get_nonce( 'view_action' );
		$row        = array();
		if ( $role === 'administrator' || $role === 'management' || $role === 'supportstaff' ) {
			$row[0] = '<input type="checkbox" name="id[]" class="mjschool-sub-chk check_for_id" value="' . esc_attr( $uid ) . '">';
		} else {
			$row[0] = ''; // Still assign a value to keep columns aligned.
		}
		$user_img  = mjschool_get_user_image( $uid );
		$image_url = empty( $user_img ) ? get_option( 'mjschool_student_thumb_new' ) : $user_img;
		if ( $role != 'administrator' ) {
			
			$row[1] = '<a href="?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . $student_id . '"> <img src="' . esc_url($image_url) . '" class="img-circle" /> </a>';
			$row[2] = '<a class="mjschool-color-black" href="?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . $student_id . '">' . esc_html( $user_info->display_name) . '</a> <br> <label class="mjschool-list-page-email">' . esc_html( $user_info->user_email) . '</label>';
		} else {
			$row[1] = '<a href="?page=mjschool_student&tab=view_student&action=view_student&student_id=' . $student_id . '&_wpnonce=' . $view_nonce . '">
				<img src="' . esc_url($image_url) . '" class="img-circle" />
			</a>';
			$row[2] = '<a class="mjschool-color-black" href="?page=mjschool_student&tab=view_student&action=view_student&student_id=' . $student_id . '&_wpnonce=' . $view_nonce . '">' . esc_html( $user_info->display_name) . '</a>
			<br>
			<label class="mjschool-list-page-email">' . esc_html( $user_info->user_email) . '</label>';
		}
		$country_code = mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) );
		$mobile = !empty($phone) ? $phone : 'N/A';
		$row[3] = '+' . $country_code . ' ' . $mobile . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="' . esc_attr__( 'Mobile No.', 'mjschool' ) . '"></i>';
		$section_id = get_user_meta($uid, 'class_section', true);
		$classname = mjschool_get_class_section_name_wise( $class_name, $section_id);
		$row[4] = ( ! empty( $classname) ? $classname : 'N/A' ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="' . esc_attr__( 'Class & Section', 'mjschool' ) . '"></i>';
		$admission = get_user_meta($uid, 'admission_no', true);
		$row[5] = ( ! empty( $admission) ? $admission : 'N/A' ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="' . esc_attr__( 'Student ID', 'mjschool' ) . '"></i>';
		$roll_id = get_user_meta($uid, 'roll_id', true);
		$row[6] = ( ! empty( $roll_id) ? $roll_id : 'N/A' ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="' . esc_attr__( 'Roll No.', 'mjschool' ) . '"></i>';
		$row[7] = ( ! empty( $gender) ? ucfirst($gender) : 'N/A' ) . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="' . esc_attr__( 'Gender', 'mjschool' ) . '"></i>';
		$hash = get_user_meta($uid, 'hash', true);
		if ($hash) {
			$status = '<span class="mjschool_unpaid_color">' . esc_html__( 'Deactive', 'mjschool' ) . '</span>';
		} else {
			$status = '<span class="mjschool_green_colors">' . esc_html__( 'Active', 'mjschool' ) . '</span>';
		}
		$row[8] = $status . ' <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="' . esc_attr__( 'Status', 'mjschool' ) . '"></i>';
		if ($role === 'administrator' || $role === 'management' ) {
			$action = '<div class="mjschool-user-dropdown">
			<ul  class="mjschool_ul_style">
				<li >
					<a href="#" data-bs-toggle="dropdown" aria-expanded="false">
						<img src="' . esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"). '">
					</a>
					<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn">
					<li class="mjschool-float-left-width-100px">
					<a href="?page=mjschool_student&tab=view_student&action=view_student&student_id=' . esc_attr($student_id) . '&_wpnonce=' . mjschool_get_nonce( 'view_action' ) . '" class="mjschool-float-left-width-100px">
						<i class="fas fa-eye"></i> ' . esc_html__( 'View', 'mjschool' ) . '
					</a>
				</li>
				<li class="mjschool-float-left-width-100px">
					<a href="?page=mjschool_student&tab=studentlist&action=result&student_id=' . esc_attr($student_id) . '" class="show-popup mjschool-float-left-width-100px" idtest="' . esc_attr($uid) . '">
						<i class="fas fa-bar-chart"></i> ' . esc_html__( 'View Result', 'mjschool' ) . '
					</a>
				</li>';
			$hash = get_user_meta($uid, 'hash', true);
			if ($hash) {
				$action .= '<li class="mjschool-float-left-width-100px">
					<a href="#" class="mjschool-float-left-width-100px active-user" idtest="' . esc_attr($uid) . '">
						<i class="fas fa-thumbs-up"></i> ' . esc_html__( 'Activate', 'mjschool' ) . '
					</a>
				</li>';
			} else {
				$action .= '<li class="mjschool-float-left-width-100px">
					<a href="?page=mjschool_student&tab=studentlist&action=deactivate&student_id=' . esc_attr($student_id) . '&_wpnonce=' . mjschool_get_nonce( 'deactive_action' ) . '" class="mjschool-float-left-width-100px">
						<i class="fas fa-thumbs-down"></i> ' . esc_html__( 'Deactivate', 'mjschool' ) . '
					</a>
				</li>';
			}
			if ($user_access_edit === '1' ) {
				$action .= '<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
					<a href="?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr($student_id) . '&_wpnonce=' . mjschool_get_nonce( 'edit_action' ) . '" class="mjschool-float-left-width-100px">
						<i class="fas fa-edit"></i> ' . esc_html__( 'Edit', 'mjschool' ) . '
					</a>
				</li>';
			}
			if ($user_access_delete === '1' ) {
				$action .= '<li class="mjschool-float-left-width-100px">
					<a href="?page=mjschool_student&tab=studentlist&action=delete&student_id=' . esc_attr($student_id) . '&_wpnonce=' . mjschool_get_nonce( 'delete_action' ) . '" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm(\'' . esc_js(esc_html__( 'Are you sure you want to delete this record?', 'mjschool' ) ) . '\' );">
						<i class="fas fa-trash"></i> ' . esc_html__( 'Delete', 'mjschool' ) . '
					</a>
				</li>';
			}
		} else {
			$action = '<div class="mjschool-user-dropdown">
			<ul  class="mjschool_ul_style">
			<li >
				<a href="#" data-bs-toggle="dropdown" aria-expanded="false">
					<img src="' . esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"). '">
				</a>
				<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn">
				<li class="mjschool-float-left-width-100px">
				<a href="?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . esc_attr($student_id) . '" class="mjschool-float-left-width-100px">
					<i class="fas fa-eye"></i> ' . esc_html__( 'View', 'mjschool' ) . '
				</a>
			</li>
			<li class="mjschool-float-left-width-100px">
				<a href="?dashboard=mjschool_user&page=student&action=result&student_id=' . esc_attr($student_id) . '" class="show-popup mjschool-float-left-width-100px" idtest="' . esc_attr($uid) . '">
					<i class="fas fa-bar-chart"></i> ' . esc_html__( 'View Result', 'mjschool' ) . '
				</a>
			</li>';
			
			if ( $user_access_edit === '1' ) {
				$action .= '<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
					<a href="?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( $student_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) . '" class="mjschool-float-left-width-100px">
						<i class="fas fa-edit"></i> ' . esc_html__( 'Edit', 'mjschool' ) . '
					</a>
				</li>';
			}
			if ( $user_access_delete === '1' ) {
				$action .= '<li class="mjschool-float-left-width-100px">
					<a href="?dashboard=mjschool_user&page=student&tab=studentlist&action=delete&student_id=' . esc_attr( $student_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) . '" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm(\'' . esc_js( esc_html__( 'Are you sure you want to delete this record?', 'mjschool' ) ) . '\' );">
						<i class="fas fa-trash"></i> ' . esc_html__( 'Delete', 'mjschool' ) . '
					</a>
				</li>';
			}
		}
		$action            .= '</ul></li></ul></div>';
		$row[9]             = $action;
		$output['aaData'][] = $row;
	}
	echo json_encode( $output );
	die();
}
add_action( 'wp_ajax_mjschool_create_transfer_letter', 'mjschool_create_transfer_letter' );
/**
 * Render the Transfer Certificate creation/editing page.
 *
 * Performs:
 * - Role-based permission checks.
 * - Loads TinyMCE editor assets.
 * - Fetches student, teacher, and parent metadata.
 * - Validates required signatures for certificate creation.
 * 
 * @since 1.0.0
 *
 * @return void Outputs HTML content and stops execution.
 */
function mjschool_create_transfer_letter() {
	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['student_id'], $_REQUEST['teacher_id'], $_REQUEST['teacher_new_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	global $wpdb;
	$obj_attend = new Mjschool_Attendence_Manage();
	$obj_marks  = new Mjschool_Marks_Manage();
	$obj_exam   = new Mjschool_exam();
	wp_enqueue_editor(); // Ensures editor assets are available.
	wp_enqueue_script( 'editor' );
	wp_enqueue_script( 'quicktags' );
	wp_enqueue_script( 'wp-tinymce' );
	wp_enqueue_style( 'editor-buttons' );
	// Manually print any styles/scripts (optional if already in header).
	wp_print_styles( 'editor-buttons' );
	wp_print_scripts( 'editor' );
	wp_print_scripts( 'quicktags' );
	wp_print_scripts( 'wp-tinymce' );
	?>
	
	<?php
	$certificate_id = isset($_REQUEST['certificate_id']) ? intval(wp_unslash($_REQUEST['certificate_id'])) : 0;
	$curr_role      = mjschool_get_user_role( get_current_user_id() );
	$action_view    = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'N/A';
	if ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' || sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'view' ) {
		$id         = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['acc'])) ) );
		$student_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) ) );
	} elseif ( $_REQUEST['action'] === 'new' ) {
		$student_id     = intval( wp_unslash($_REQUEST['student_id'] ));
		$teacher_id     = intval( wp_unslash($_REQUEST['teacher_id'] ));
		$teacher_new_id = intval( wp_unslash($_REQUEST['teacher_new_id'] ));
	}
	if ( $curr_role === 'administrator' || $curr_role === 'management' ) {
		$action_url = admin_url( 'admin.php?page=mjschool_certificate&tab=assign_list' );
	} else {
		$action_url = home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_list' );
	}
	$letter_type = isset($_REQUEST['certificate_type']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_type'])) : '';
	$c_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['acc'])) ?? '' ) );
	$data        = get_userdata( $student_id );
	$teacher_id     = isset($_REQUEST['teacher_id']) ? absint(wp_unslash($_REQUEST['teacher_id'])) : 0;
	$teacher_new_id = isset($_REQUEST['teacher_new_id']) ? absint(wp_unslash($_REQUEST['teacher_new_id'])) : 0;
	$data2     = get_userdata($teacher_id);
	$data3     = get_userdata($teacher_new_id);
	$metadata  = get_user_meta($student_id);
	$metadata1 = get_user_meta($teacher_id);
	$metadata2 = get_user_meta($teacher_new_id);
	$parentdata = mjschool_get_users_data( 'parent' );
	$parent_ids = array();
	if ( isset( $metadata['parent_id'][0] ) ) {
		$parent_ids = unserialize( $metadata['parent_id'][0] );
	}
	$mother = '';
	$father = '';
	if ( isset( $metadata['parent_id'][0] ) ) {
		$parent_ids = unserialize( $metadata['parent_id'][0] );
		$parentdata = mjschool_get_users_data( 'parent' );
		foreach ( $parentdata as $parent ) {
			if ( in_array( $parent->ID, $parent_ids ) ) {
				$relation = get_user_meta( $parent->ID, 'relation', true );
				$name     = $parent->data->display_name;
				if ( strtolower( $relation ) === 'mother' ) {
					$mother = $name;
				} elseif ( strtolower( $relation ) === 'father' ) {
					$father = $name;
				}
			}
		}
	}
	$designation_id     = isset( $metadata1['designation'][0] ) ? $metadata1['designation'][0] : '';
	$designation_new_id = isset( $metadata2['designation'][0] ) ? $metadata2['designation'][0] : '';
	$designation_name   = '';
	if ( ! empty( $designation_id ) ) {
		$designation_post = get_post( $designation_id );
		if ( $designation_post && $designation_post->post_type === 'designation' ) {
			$designation_name = $designation_post->post_title;
		}
	}
	if ( ! empty( $designation_new_id ) ) {
		$designation_post = get_post( $designation_new_id );
		if ( $designation_post && $designation_post->post_type === 'designation' ) {
			$designation_check_name = $designation_post->post_title;
		}
	}
	$signature_path     = isset( $metadata1['signature'][0] ) ? $metadata1['signature'][0] : '';
	$signature_url      = $signature_path ? content_url( $signature_path ) : '';
	$signature_path_new = isset( $metadata2['signature'][0] ) ? $metadata2['signature'][0] : '';
	$signature_url_new  = $signature_path_new ? content_url( $signature_path_new ) : '';
	if ( $curr_role === 'administrator' ) {
		$user_access_add    = '1';
		$user_access_edit   = '1';
		$user_access_delete = '1';
		$user_access_view   = '1';
	} else {
		$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'teacher' );
		$user_access_add    = $user_access['add'];
		$user_access_edit   = $user_access['edit'];
		$user_access_delete = $user_access['delete'];
		$user_access_view   = $user_access['view'];
	}
	if ( $action_view === 'new' ) {
		$has_error = false;
		// Prepare role.
		$is_admin_or_management = ( $curr_role === 'administrator' || $curr_role === 'management' );
		// Class Teacher Signature.
		if ( empty( $signature_path ) ) {
			echo '<div class="alert alert-danger d-flex justify-content-between align-items-center mt-2">';
			echo '<span>' . esc_html__( 'Class Teacher signature is not added. Please upload the signature.', 'mjschool' ) . '</span>';
			if ( $user_access_edit === '1' ) {
				$teacher_id_encrypted = mjschool_encrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) );
				$nonce                = mjschool_get_nonce( 'edit_action' );
				$edit_url             = $is_admin_or_management
					? admin_url( "admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id={$teacher_id_encrypted}&_wpnonce={$nonce}" )
					: home_url( "?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id={$teacher_id_encrypted}&_wpnonce_action={$nonce}" );
				echo '<a href="' . esc_url( $edit_url ) . '" class="btn btn-warning btn-sm mjchool_margin_left_autom" target="_blank" >' . esc_html__( 'Edit Teacher', 'mjschool' ) . '</a>';
			}
			echo '</div>';
			$has_error = true;
		}
		// Checked By Teacher Signature.
		if ( empty( $signature_path_new ) ) {
			echo '<div class="alert alert-danger d-flex justify-content-between align-items-center mt-2">';
			echo '<span>' . esc_html__( 'Checked By (Teacher) signature is not added. Please upload the signature.', 'mjschool' ) . '</span>';
			if ( $user_access_edit === '1' ) {
				$teacher_new_id_encrypted = mjschool_encrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_new_id'])) );
				$nonce                    = mjschool_get_nonce( 'edit_action' );
				$edit_url                 = $is_admin_or_management
					? admin_url( "admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id={$teacher_new_id_encrypted}&_wpnonce={$nonce}" )
					: home_url( "?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id={$teacher_new_id_encrypted}&_wpnonce_action={$nonce}" );
				echo '<a href="' . esc_url( $edit_url ) . '" class="btn btn-warning btn-sm mjchool_margin_left_autom" target="_blank" >' . esc_html__( 'Edit Teacher', 'mjschool' ) . '</a>';
			}
			echo '</div>';
			$has_error = true;
		}
		if ( $has_error ) {
			return; // Stop further execution.
		}
	}
	$arr                                     = array();
	$arr['{{mother_name}}']                  = $mother;
	$arr['{{father_name}}']                  = $father;
	$arr['{{teacher_designation}}']          = $designation_name;
	$designation_check_name = $designation_check_name ?? ''; // or a default like 'N/A'.
	$arr['{{checking_teacher_designation}}'] = $designation_check_name;
	$arr['{{check_by_signature}}']           = $signature_url_new;
	$arr['{{teacher_signature}}']            = $signature_url;
	$arr['{{place}}']                        = get_option( 'mjschool_city' );
	$arr['{{date}}']                         = date( 'Y-m-d' );
	$roll_no                                 = isset( $metadata['roll_id'][0] ) ? $metadata['roll_id'][0] : '';
	$raw_birth_date                          = isset( $metadata['birth_date'][0] ) ? $metadata['birth_date'][0] : '';
	$admission_date                          = isset( $metadata['admission_date'][0] ) ? $metadata['admission_date'][0] : '';
	$formatted_birth_date                    = ! empty( $raw_birth_date ) ? date( 'd-m-Y', strtotime( str_replace( '/', '-', $raw_birth_date ) ) ) : '';
	$birth_date_in_words                     = ! empty( $raw_birth_date ) ? mjschool_date_in_words( $raw_birth_date ) : '';
	$class_name                              = get_user_meta( $student_id, 'class_name', true );
	$section_id                              = get_user_meta( $student_id, 'class_section', true );
	$classname                               = mjschool_get_class_section_name_wise( $class_name, $section_id );
	// Get fails.
	$marks          = $obj_marks->mjschool_subject_makrs_by_student_id( $student_id );
	$fail_count     = 0;
	$last_exam_name = '';
	if ( ! empty( $marks ) ) {
		foreach ( $marks as $mark ) {
			$exam_id      = (int) $mark->exam_id;
			$student_mark = (float) $mark->marks;
			// Get passing mark for this exam.
			$passing_mark = $obj_marks->mjschool_get_pass_marks( $exam_id );
			// If student mark is less than passing mark, count as fail.
			if ( $student_mark < (float) $passing_mark ) {
				++$fail_count;
			}
		}
	}
	$last_exam_id = '';
	if ( ! empty( $marks ) ) {
		// Sort marks by created_date descending.
		usort(
			$marks,
			function ( $a, $b ) {
				return strtotime( $b->created_date ) - strtotime( $a->created_date );
			}
		);
		foreach ( $marks as $index => $mark ) {
			$exam_id      = (int) $mark->exam_id;
			$student_mark = (float) $mark->marks;
			$passing_mark = (float) $obj_marks->mjschool_get_pass_marks( $exam_id );
			$last_exam_id = $marks[0]->exam_id;
			if ( $student_mark < $passing_mark ) {
				++$fail_count;
			}
			// Only check the first record (latest).
			if ( $index === 0 ) 
			{
				$last_result_status = ( $student_mark < $passing_mark ) ? 'Fail' : 'Pass';
			}
		}
	}
	$last_exam_names           = $obj_exam->mjschool_exam_name_by_id( $last_exam_id );
	$last_exam_name            = isset( $last_exam_names->exam_name ) ? $last_exam_names->exam_name : '';
	$arr['{{last_exam_name}}'] = $last_exam_name;
	if ( $fail_count === 1 ) {
		$arr['{{fails}}'] = 'once';
	} elseif ( $fail_count > 1 ) {
		$arr['{{fails}}'] = 'twice';
	} else {
		$arr['{{fails}}'] = '';
	}
	$presents      = '';
	$presents      = $obj_attend->mjschool_get_students( $student_id );
	$total_present = 0;
	if ( ! empty( $presents ) ) {
		foreach ( $presents as $att ) {
			if ( isset( $att->status ) && strtolower( $att->status ) === 'present' ) {
				++$total_present;
			}
		}
	}
	$class_id = get_user_meta( $student_id, 'class_name', true );
	if ( ! empty( $class_id ) ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$subjects = $wpdb->get_col( $wpdb->prepare( "SELECT sub_name FROM {$wpdb->prefix}mjschool_subject WHERE class_id = %d", $class_id ) );
		// Join subject names into a comma-separated string.
		$subject_list = implode( ', ', $subjects );
		// Assign to template variable.
		$arr['{{subject}}'] = $subject_list;
	} else {
		$arr['{{subject}}'] = '';
	}
	$table_name = $wpdb->prefix . 'mjschool_migration_log';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$logs       = $wpdb->get_results( "SELECT * FROM $table_name" );
	$last_class = '';
	foreach ( $logs as $log ) {
		$pass_students = json_decode( $log->pass_students, true );
		if ( ! empty( $pass_students ) ) {
			foreach ( $pass_students as $student ) {
				if ( $student['student_id'] === $student_id ) {
					$last_class = $log->current_class; // Found!
					break 2; // exit both loops
				}
			}
		}
	}
	$last_class_name = mjschool_get_class_section_name_wise( $last_class, $section_id );
	$table_name      = $wpdb->prefix . 'mjschool_fees_payment';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$total_fees_pay = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(fees_paid_amount) FROM $table_name WHERE student_id = %d", $student_id ) );
	$total_fees_pay = $total_fees_pay ? $total_fees_pay : 0;
	if ( $total_fees_pay && $total_fees_pay > 0 ) {
		$fees = mjschool_get_currency_symbol() . $total_fees_pay;
	} else {
		$fees = '';
	}
	$arr['{{fees_pay}}']              = $fees;
	$arr['{{last_class}}']            = $last_class_name;
	$arr['{{total_present}}']         = $total_present;
	$last_result_status = $last_result_status ?? ''; 
	$arr['{{last_result}}'] = $last_result_status;
	$arr['{{class_name}}']            = $classname;
	$arr['{{birth_date}}']            = $formatted_birth_date;
	$arr['{{birth_date_words}}']      = $birth_date_in_words;
	$admission_no                     = isset( $metadata['admission_no'][0] ) ? $metadata['admission_no'][0] : '';
	$arr['{{admission_no}}']          = $admission_no;
	$arr['{{roll_no}}']               = $roll_no;
	$arr['{{admission_date}}']        = $admission_date;
	$arr['{{principal_signature}}']   = get_option( 'mjschool_principal_signature' );
	$arr['{{student_name}}']          = $data->display_name;
	$arr['{{teacher_name}}'] = ($data2 && isset($data2->display_name ) ) ? $data2->display_name : '';
	$arr['{{checking_teacher_name}}'] = $data3->display_name;
	if ( $letter_type === 'transfer_static' ) {
		// Static certificate.
		$content   = get_option( 'mjschool_transfer_certificate_template' );
		$presentto = get_option( 'mjschool_transfer_certificate_to' );
	} elseif ( $action_view === 'edit' || $action_view === 'view' ) {
		// Dynamic certificate from DB.
		$table = $wpdb->prefix . 'mjschool_certificate';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$cert = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $c_id ) );
		if ( $cert ) {
			$content   = $cert->certificate_content;
			$presentto = ''; // if you plan to store this in future.
		} else {
			wp_send_json_error( array( 'message' => 'Certificate not found.' ) );
			die();
		}
	} else {
		$table = $wpdb->prefix . 'mjschool_daynamic_certificate';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$cert = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE certificate_name = %s", $letter_type ) );
		if ( $cert ) {
			$content   = $cert->certificate_content;
			$presentto = ''; // If you plan to store this in future.
		} else {
			wp_send_json_error( array( 'message' => 'Certificate not found.' ) );
			die();
		}
	}
	$replace_content = wpautop( mjschool_string_replacemnet( $arr, $content ) );
	$replace_to      = wpautop( mjschool_string_replacemnet( $arr, $presentto ) );
	$result          = null;
	global $wpdb;
	$table_exprience_letter = $wpdb->prefix . 'mjschool_certificate';
	if ( $action_view === 'edit' || $action_view === 'view' ) {
		$sql = $wpdb->prepare( "SELECT * FROM $table_exprience_letter WHERE id = %d", $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $sql );
	}
	?>

	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
		<h4 id="myLargeModalLabel" class="modal-title"> <?php print esc_html( get_option( 'mjschool_transfer_certificate_title' ) ); ?></h4>
	</div>
	<div class="div">
		<div id="printcontent" class="mjschool_exprience">
			<form action="<?php echo esc_url( $action_url ); ?>" id="exp_letter" name="exp_letter" method="post">
				<input type="hidden" name="student_id" value="<?php echo esc_attr( isset( $result ) && ! empty( $result->student_id ) ? $result->student_id : $student_id ); ?>">
				<input type="hidden" name="certificate_type" value="<?php echo esc_attr( $letter_type ); ?>">
				<input type="hidden" name="_wpnonce" value="<?php echo isset($_REQUEST['_wpnonce']) ? esc_attr($_REQUEST['_wpnonce']) : ''; ?>">
				<input type="hidden" name="certificate_id" value="<?php echo esc_attr( $certificate_id ); ?>">
				<?php
				if ( $action_view === 'edit' ) {
					?>
					<input type="hidden" name="edit" value="edit">
					<input type="hidden" name="id" value="<?php print esc_attr( $result->id ); ?>">
					<?php
				}
				?>
				<div class="col-md-12">
					<div class="div">
						<h4><?php print wp_kses_post($replace_to); ?></h4>
					</div>
					<?php
					if ( $action_view === 'view' ) {
						?>
						<p> <?php print wp_kses_post( $result->certificate_content ); ?> </p>
						<?php
					} else {
						?>
						<div>
							<div>
								<textarea class="form-control textarea experiance_area" id="lett_content" name="lett_content" rows="8" data-readonly="<?php echo $action_view === 'view' ? 'true' : 'false'; ?>" <?php if ( $action_view === 'view' ) { ?> readonly <?php } ?>>
									<?php
									if ( $action_view === 'edit' ) {
										print wp_kses_post( $result->certificate_content );
									} else {
										print wp_kses_post( $replace_content );
									}
									?>
								</textarea>
							</div>
						</div>
					<?php } ?>
				</div>
				<div class="col-md-offset-5 col-md-7 mt-2">
					<h1>
						<?php
						if ( $action_view != 'view' ) {
							?>
							<input type="submit" name="create_exprience_latter" class="btn btn-primary btn-primary-prints" value="Save">
							<?php
						}
						?>
					</h1>
				</div>
				<?php if ( $action_view === 'view' ) { ?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px mb-3 mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio mjschool-rtl-relative-position">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-right-position" for="mjschool_enable_homework_mail"><?php esc_html_e( 'Print Certificate With Header', 'mjschool' ); ?></label>
										<input type="checkbox" class="mjschool-check-box-input-margin" id="certificate_header" name="certificate_header" value="1" />
										<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			</form>
		</div>
		<?php if ( $action_view === 'view' ) { ?>
			<div class="col-md-offset-5 col-md-7 mt-2">
				<a id="exprience_latter" href="?page=mjschool_certificate&print=print&print_certificate_id=<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['acc'])) ); ?>" class="btn btn-primary btn-primary-prints" target="_blank">
					<?php esc_html_e( 'Print', 'mjschool' ); ?>
				</a>
				<a id="download_pdf" href="?page=mjschool_certificate&print=pdf&certificate_id=<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['acc'])) ); ?>" class="btn btn-primary btn-primary-prints" target="_blank">
					<?php esc_html_e( 'Download PDF', 'mjschool' ); ?>
				</a>
			</div>
		<?php } ?>
	</div>
	<div id="mjschool-transfer-letter-trigger" data-trigger="1"></div>
	<?php
	die;
}
add_action( 'wp_ajax_mjschool_delete_letter', 'mjschool_delete_letter' );
/**
 * Deletes a certificate/letter record from the database via AJAX.
 *
 * Behavior:
 * - Deletes entry from the `mjschool_certificate` table.
 * - Returns a redirect URL based on admin or frontend context.
 * 
 * @since 1.0.0
 *
 * @return void Outputs JSON response and terminates execution.
 */
function mjschool_delete_letter() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	if ( ! isset( $_REQUEST['id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
	global $wpdb;
	$acc_id = intval( wp_unslash($_REQUEST['id']) );
	$tab    = isset($_REQUEST['tab']) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : '';
	if ( ! $acc_id ) {
		wp_send_json_error( 'Invalid input.' );
	}
	$table_name = $wpdb->prefix . 'mjschool_certificate';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->delete(
		$table_name,
		array( 'id' => $acc_id ),
		array( '%d' )
	);
	if ( $tab === 'latters' ) {
		wp_send_json_success( array( 'redirect_url' => admin_url( 'admin.php?page=mjschool_certificate&tab=assign_list&message=cret_crt_delete' ) ) );
	} else {
		wp_send_json_success( array( 'redirect_url' => home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_list&message=cret_crt_delete' ) ) );
	}
	die();
}
/**
 * Converts a given date string into a fully written, human-readable format.
 *
 * Example: "2024-02-16" → "16th February Two Thousand Twenty Four"
 *
 * @since 1.0.0
 * @param string $date_string Date in `Y-m-d` or mixed format.
 * @return string Human-readable date in words, or empty string on failure.
 */
function mjschool_date_in_words( $date_string ) {
	$timestamp = strtotime( str_replace( '/', '-', $date_string ) );
	if ( ! $timestamp ) {
		return '';
	}
	$day        = date( 'j', $timestamp );
	$month      = date( 'F', $timestamp );
	$year       = date( 'Y', $timestamp );
	$day_words  = date( 'jS', $timestamp ); // e.g., 16th.
	$year_words = date( 'Y', $timestamp );
	// Convert year to words.
	$f             = new NumberFormatter( 'en', NumberFormatter::SPELLOUT );
	$year_in_words = ucwords( $f->format( $year ) );
	return $day_words . ' ' . $month . ' ' . $year_in_words;
}
add_action( 'wp_ajax_download_csv_log', 'mjschool_download_csv_log' );
/**
 * Downloads CSV logs for a given module via AJAX.
 *
 * @since 1.0.0
 *
 * @return void Outputs CSV headers and content, then terminates execution.
 */
function mjschool_download_csv_log() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	global $wpdb;
	$module = isset( $_POST['module'] ) ? sanitize_text_field( wp_unslash($_POST['module']) ) : '';
	// Assuming you store logs in a custom table (adjust table name as needed).
	$table = $wpdb->prefix . 'mjschool_csv_log';
	if ( ! empty( $module ) ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE module = %s ORDER BY id DESC", $module ), ARRAY_A );
	}
	if ( empty( $results ) ) {
		wp_die( 'No logs found.' );
	}
	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment; filename="' . $module . '_csv_logs.csv"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );
	$output = fopen( 'php://output', 'w' );
	// Output headers.
	fputcsv( $output, array_keys( $results[0] ) );
	// Output data.
	foreach ( $results as $row ) {
		fputcsv( $output, $row );
	}
	fclose( $output );
	die();
}
add_action( 'wp_ajax_nopriv_mjschool_load_classroom',  'mjschool_load_classroom' );
add_action( 'wp_ajax_mjschool_load_classroom',  'mjschool_load_classroom' );
/**
 * Loads classroom options for a selected class via AJAX.
 *
 * Retrieves assigned classrooms using a helper function and prints
 * `<option>` tags for a dropdown.
 *
 * @since 1.0.0
 * @return void Outputs HTML option elements and exits.
 */
function mjschool_load_classroom() {
	$class_id = isset($_REQUEST['class_id']) ? sanitize_text_field(wp_unslash($_REQUEST['class_id'])) : '';
	$classroom = mjschool_get_assign_class_room_for_single_class($class_id);
	$defaultmsg = esc_attr__( 'Select Classroom', 'mjschool' );
	echo "<option value=''>" . esc_html( $defaultmsg) . "</option>";
	if( ! empty( $classroom ) )
	{
		foreach ($classroom as $room_data) 
		{
			echo "<option value='" . esc_attr($room_data->room_id) . "'> " . esc_html( $room_data->room_name) . "</option>";
		}
	}
	exit;
}
add_action( 'wp_ajax_mjschool_get_students_by_class', 'mjschool_get_students_by_class' );
/**
 * Fetches all students for a given class ID and returns them as <option> elements.
 *
 * Used to populate student dropdowns dynamically via AJAX.
 *
 * @since 1.0.0
 * @return void Outputs HTML option list and terminates execution.
 */
function mjschool_get_students_by_class() {

	if ( ! isset( $_POST['class_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
	}
    $class_id = intval(wp_unslash($_POST['class_id']));
    $students = mjschool_get_users_by_class_id($class_id); // your helper

    if ( ! empty( $students ) ) {
        foreach ($students as $student) {
            echo '<option value="' . esc_attr($student->ID) . '">' . esc_html( mjschool_student_display_name_with_roll($student->ID ) ) . '</option>';
        }
    } else {
        echo '<option value="">' . esc_html__( 'No students found', 'mjschool' ) . '</option>';
    }

    wp_die();
}
add_action( 'wp_ajax_mjschool_load_subjects_for_exam_callback', 'mjschool_load_subjects_for_exam_callback' );
/**
 * Loads subject rows for exam creation/editing via AJAX.
 *
 * Features:
 * - Retrieves subjects for the given class.
 * - Detects existing exam values (subject_data or legacy university_subjects).
 * - Generates a full HTML table with enable checkbox, passing marks, and total marks.
 *
 * @since 1.0.0
 * @return void Returns JSON containing rendered HTML table.
 */
function mjschool_load_subjects_for_exam_callback(){
  $class_id = isset($_POST['class_id']) ? intval(wp_unslash($_POST['class_id'])) : 0;
  $exam_id = isset($_POST['exam_id']) ? intval(wp_unslash($_POST['exam_id'])) : 0;

  $subjects = mjschool_get_subject_by_class_id($class_id);

  // Normalize existing data for edit: prefer subject_data, fallback to university_subjects.
  $existing = [];
  if ($exam_id) {
    $exam_data = mjschool_get_exam_by_id($exam_id);

    // First, try subject_data (new format).
    if ( ! empty( $exam_data->subject_data ) ) {
      $decoded = json_decode($exam_data->subject_data, true);
      if (is_array($decoded ) ) {
        foreach ($decoded as $item) {
          $sid = isset($item['subject_id']) ? intval($item['subject_id']) : 0;
          $existing[$sid] = [
            'enabled'      => (isset($item['enable']) && $item['enable'] === 'yes' ),
            'passing_mark' => isset($item['passing_marks']) ? $item['passing_marks'] : '',
            'total_mark'   => isset($item['max_marks']) ? $item['max_marks'] : '',
          ];
        }
      }
    }
    // Fallback to old university_subjects structure if subject_data empty.
    if (empty($existing) && !empty($exam_data->university_subjects ) ) {
      $decoded_old = json_decode($exam_data->university_subjects, true);
      if (is_array($decoded_old ) ) {
        foreach ($decoded_old as $subid => $info) {
          $existing[intval($subid)] = [
            'enabled'      => !empty($info['enabled']),
            'passing_mark' => isset($info['passing_mark']) ? $info['passing_mark'] : '',
            'total_mark'   => isset($info['total_mark']) ? $info['total_mark'] : '',
          ];
        }
      }
    }
  }

  ob_start();
  if ( ! empty( $subjects ) ) {
    echo '<table class="table table-bordered mjschool_margin_bottom_20px">';
    echo '<thead><tr><th>Enable</th><th>Subject Name</th><th>Passing Marks</th><th>Total Marks</th></tr></thead><tbody>';
    foreach ($subjects as $sub) {
      $subid = isset($sub->subid) ? intval($sub->subid) : (isset($sub['subid']) ? intval($sub['subid']) : 0);
      $sub_name = isset($sub->sub_name) ? $sub->sub_name : (isset($sub['sub_name']) ? $sub['sub_name'] : '' );
      $data = isset($existing[$subid]) ? $existing[$subid] : [
        'enabled' => false,
        'passing_mark' => '',
        'total_mark' => ''
      ];
      $checked = !empty($data['enabled']) ? 'checked' : '';
      $disabled_attr = !empty($data['enabled']) ? '' : 'disabled';
      $passing = esc_attr($data['passing_mark']);
      $total = esc_attr($data['total_mark']);

      echo '<tr>';
      echo '<td><input type="checkbox" class="subject-enable-checkbox" name="university_subjects['.esc_attr($subid).'][enabled]" value="1" '.esc_attr($checked).'></td>';
      echo '<td>'.esc_html( $sub_name).'<input type="hidden" name="university_subjects['.esc_attr($subid).'][name]" value="'.esc_attr($sub_name).'"></td>';
      echo '<td><input type="number" class="form-control pass_mark" name="university_subjects['.esc_attr($subid).'][passing_mark]" value="'.esc_attr($passing).'" '.esc_attr($disabled_attr).'></td>';
      echo '<td><input type="number" class="form-control total_mark" name="university_subjects['.esc_attr($subid).'][total_mark]" value="'.esc_attr($total).'" '.esc_attr($disabled_attr).'></td>';
      echo '</tr>';
    }
    echo '</tbody></table>';
  } else {
    echo '<p>No subjects found for selected class.</p>';
  }

  $html = ob_get_clean();
  wp_send_json_success(['html' => $html]);
}
?>