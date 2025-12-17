<?php
/**
 * Marks Management Interface.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/mark
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );

if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access = mjschool_get_user_role_wise_filter_access_right_array( 'manage_marks' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			if ( 'manage_marks' === $user_access['page_link'] && $action === 'edit' && $user_access_edit === '0' ) {
				mjschool_access_right_page_not_access_message_admin_side();
				die();
			}
			if ( 'manage_marks' === $user_access['page_link'] && $action === 'delete' && $user_access_delete === '0' ) {
				mjschool_access_right_page_not_access_message_admin_side();
				die();
			}
			if ( 'manage_marks' === $user_access['page_link'] && $action === 'insert' && $user_access_add === '0' ) {
				mjschool_access_right_page_not_access_message_admin_side();
				die();
			}
		}
	}
}
$mjschool_obj_marks = new Mjschool_Marks_Manage();
$mjschool_exam_obj  = new Mjschool_exam();

// -----------------------------------------------------------------------------
// ADD SINGLE MARK ENTRY.
// -----------------------------------------------------------------------------
if ( isset( $_REQUEST['add_mark'] ) ) {
	// Verify nonce.
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_manage_marks_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	
	$user_id       = intval( wp_unslash( $_REQUEST['add_mark'] ) );
	$contributions = isset( $_REQUEST['contributions'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contributions'] ) ) : '';
	
	if ( $contributions === 'yes' ) {
		$class_marks_raw = isset( $_REQUEST['class_marks_'][ $user_id ] ) ? wp_unslash( $_REQUEST['class_marks_'][ $user_id ] ) : array();
		$class_marks_arr = is_array( $class_marks_raw ) ? $class_marks_raw : array( $class_marks_raw );
		$class_marks_sanitized = array_map( 'sanitize_text_field', $class_marks_arr );
		$class_marks = wp_json_encode( $class_marks_sanitized );
		$marks = 0;
	} else {
		$marks = isset( $_REQUEST[ 'marks_' . $user_id ] ) ? intval( wp_unslash( $_REQUEST[ 'marks_' . $user_id ] ) ) : 0;
		$class_marks = '';
	}
	
	$comment      = isset( $_REQUEST[ 'comment_' . $user_id ] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST[ 'comment_' . $user_id ] ) ) : '';
	$exam_id      = isset( $_REQUEST['exam_id'] ) ? intval( wp_unslash( $_REQUEST['exam_id'] ) ) : 0;
	$class_id     = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
	$subject_id   = isset( $_REQUEST['subject_id'] ) ? intval( wp_unslash( $_REQUEST['subject_id'] ) ) : 0;
	$current_date = current_time( 'mysql' );
	$grade_id     = $mjschool_obj_marks->mjschool_get_grade_id( $marks );
	$grade_id     = $grade_id ? $grade_id : 0;
	
	$mark_data = array(
		'exam_id'       => $exam_id,
		'class_id'      => $class_id,
		'subject_id'    => $subject_id,
		'marks'         => $marks,
		'class_marks'   => $class_marks,
		'contributions' => $contributions,
		'grade_id'      => $grade_id,
		'student_id'    => $user_id,
		'marks_comment' => $comment,
		'created_date'  => $current_date,
		'created_by'    => get_current_user_id(),
	);
	// SAVE.
	if ( isset( $_REQUEST[ 'save_' . $user_id ] ) ) {
		$mjschool_obj_marks->mjschool_save_marks( $mark_data );
		$nonce = wp_create_nonce( 'mjschool_exam_result_tab' );
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_result&tab=result&_wpnonce=' . $nonce . '&message=4' ) );
		exit;
	}
	
	// UPDATE.
	$mark_id_val = isset( $_REQUEST[ 'mark_id_' . $user_id ] ) ? intval( wp_unslash( $_REQUEST[ 'mark_id_' . $user_id ] ) ) : 0;
	$mark_id = array( 'mark_id' => $mark_id_val );
	$result = $mjschool_obj_marks->mjschool_update_marks( $mark_data, $mark_id );
	if ( $result ) {
		$nonce = wp_create_nonce( 'mjschool_exam_result_tab' );
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_result&tab=result&_wpnonce=' . $nonce . '&message=3' ) );
		exit;
	}
}

// -----------------------------------------------------------------------------
// SAVE ALL MARKS.
// -----------------------------------------------------------------------------
if ( isset( $_REQUEST['save_all_marks'] ) ) {
	// Verify nonce.
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_manage_marks_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	
	$exam_id       = isset( $_REQUEST['exam_id'] ) ? intval( wp_unslash( $_REQUEST['exam_id'] ) ) : 0;
	$class_id      = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
	$subject_id    = isset( $_REQUEST['subject_id'] ) ? intval( wp_unslash( $_REQUEST['subject_id'] ) ) : 0;
	$contributions = isset( $_REQUEST['contributions'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contributions'] ) ) : '';
	$flag = 0;
	
	// Load students.
	if ( $school_type === 'university' ) {
		$student = mjschool_get_students_assigned_to_subject( $subject_id );
	} else {
		$section_id = isset( $_REQUEST['section_id'] ) ? intval( wp_unslash( $_REQUEST['section_id'] ) ) : '';
		$exclude_id = mjschool_approve_student_list();
		if ( $section_id ) {
			$student = get_users( array(
				'meta_key'   => 'class_section',
				'meta_value' => $section_id,
				'meta_query' => array( array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ),
				'role'       => 'student',
				'exclude'    => $exclude_id,
			) );
		} else {
			$student = get_users( array(
				'meta_key'   => 'class_name',
				'meta_value' => $class_id,
				'role'       => 'student',
				'exclude'    => $exclude_id,
			) );
		}
	}
	
	foreach ( $student as $user ) {
		$user_id = intval( $user->ID );
		
		if ( $contributions === 'yes' ) {
			$class_marks_raw = isset( $_REQUEST['class_marks_'][ $user_id ] ) ? wp_unslash( $_REQUEST['class_marks_'][ $user_id ] ) : array();
			$class_marks_arr = is_array( $class_marks_raw ) ? $class_marks_raw : array( $class_marks_raw );
			$class_marks_arr = array_map( 'sanitize_text_field', $class_marks_arr );
			$class_marks = wp_json_encode( $class_marks_arr );
			$marks = 0;
		} else {
			$marks = isset( $_REQUEST[ 'marks_' . $user_id ] ) ? intval( wp_unslash( $_REQUEST[ 'marks_' . $user_id ] ) ) : 0;
			$class_marks = '';
		}
		
		$comment      = isset( $_REQUEST[ 'comment_' . $user_id ] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST[ 'comment_' . $user_id ] ) ) : '';
		$grade_id     = $mjschool_obj_marks->mjschool_get_grade_id( $marks );
		$grade_id     = $grade_id ? $grade_id : 0;
		$current_date = current_time( 'mysql' );
		
		$mark_data = array(
			'exam_id'       => $exam_id,
			'class_id'      => $class_id,
			'subject_id'    => $subject_id,
			'marks'         => $marks,
			'class_marks'   => $class_marks,
			'contributions' => $contributions,
			'grade_id'      => $grade_id,
			'student_id'    => $user_id,
			'marks_comment' => $comment,
			'created_date'  => $current_date,
			'created_by'    => get_current_user_id(),
		);
		
		$mark_detail = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $subject_id, $user_id );
		if ( $mark_detail ) {
			$mark_id_raw = isset( $_REQUEST[ 'mark_id_' . $user_id ] ) ? intval( wp_unslash( $_REQUEST[ 'mark_id_' . $user_id ] ) ) : 0;
			$mark_id     = array( 'mark_id' => $mark_id_raw );
			$result = $mjschool_obj_marks->mjschool_update_marks( $mark_data, $mark_id );
			if ( $result ) {
				$flag = 0;
			}
		} else {
			global $wpdb;
			$table_name = $wpdb->prefix . 'mjschool_marks';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$result = $wpdb->insert( $table_name, $mark_data );
			if ( $result ) {
				$flag = 1;
			}
		}
	}
	$nonce   = wp_create_nonce( 'mjschool_exam_result_tab' );
	$message = ( $flag === 1 ) ? 4 : 3;
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_result&tab=result&_wpnonce=' . $nonce . '&message=' . $message ) );
	exit;
}

// -----------------------------------------------------------------------------
// EXPORT MARKS.
// -----------------------------------------------------------------------------
if ( isset( $_POST['export_marks'] ) ) {
	// Verify nonce.
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'mjschool_export_marks' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	
	$exam_id       = isset( $_REQUEST['exam_id'] ) ? intval( wp_unslash( $_REQUEST['exam_id'] ) ) : 0;
	$class_id      = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
	$class_section = isset( $_REQUEST['class_section'] ) ? intval( wp_unslash( $_REQUEST['class_section'] ) ) : 0;
	$subject_list  = $mjschool_obj_marks->mjschool_student_subject_export( $class_id, $class_section );
	$exlude_id     = mjschool_approve_student_list();
	
	$user_args = array(
		'role'       => 'student',
		'exclude'    => $exlude_id,
		'meta_query' => array( array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) )
	);
	if ( $class_section ) {
		$user_args['meta_key']   = 'class_section';
		$user_args['meta_value'] = $class_section;
	}
	
	$students                 = get_users( $user_args );
	$exam_data                = $mjschool_exam_obj->mjschool_exam_data( $exam_id );
	$contributions            = $exam_data->contributions;
	$contributions_data_array = $contributions === 'yes' ? json_decode( $exam_data->contributions_data, true ) : array();
	$header                   = array( 'Roll No', 'Student Name', 'Class', 'Section Name' );
	$subject_array            = array();
	
	if ( $school_type === 'university' ) {
		$enabled_subject_ids = array();
		if ( ! empty( $exam_data->subject_data ) ) {
			$subject_data = json_decode( $exam_data->subject_data, true );
			if ( is_array( $subject_data ) ) {
				foreach ( $subject_data as $item ) {
					if ( isset( $item['subject_id'], $item['enable'] ) && $item['enable'] === 'yes' ) {
						$enabled_subject_ids[] = intval( $item['subject_id'] );
					}
				}
			}
		}
		$subject_list = array_filter( $subject_list, function( $subject ) use ( $enabled_subject_ids ) {
			return in_array( intval( $subject->subid ), $enabled_subject_ids, true );
		});
	}
	
	foreach ( $subject_list as $subject ) {
		if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
			foreach ( $contributions_data_array as $con_value ) {
				$header[] = $subject->sub_name . '( ' . sanitize_text_field( $con_value['label'] ) . ' )';
			}
		} else {
			$header[] = $subject->sub_name;
		}
		$subject_array[] = $subject->subid;
	}
	$header[]  = 'Total';
	$file_path = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-marks.csv';
	$fh = fopen( $file_path, 'w' );
	if ( $fh === false ) {
		wp_die( esc_html__( 'Cannot open file for writing.', 'mjschool' ) );
	}
	fputcsv( $fh, $header );
	
	foreach ( $students as $student ) {
		$row = array(
			get_user_meta( $student->ID, 'roll_id', true ),
			mjschool_get_user_name_by_id( $student->ID ),
			mjschool_get_class_name( $class_id ),
			mjschool_get_section_name( $class_section ),
		);
		$total_marks = 0;
		foreach ( $subject_array as $sub_id ) {
			$ob_marks = $mjschool_obj_marks->mjschool_get_marks( $exam_id, $class_id, $sub_id, $student->ID );
			$ob_marks = $ob_marks ?? 0;
			if ( $contributions === 'yes' ) {
				$subject_total = 0;
				foreach ( $contributions_data_array as $con_id => $con_value ) {
					$mark_value     = is_array( $ob_marks ) ? ( $ob_marks[ $con_id ] ?? 0 ) : $ob_marks;
					$subject_total += $mark_value;
					$row[]          = $mark_value;
				}
				$total_marks += $subject_total;
			} else {
				$row[]        = $ob_marks;
				$total_marks += $ob_marks;
			}
		}
		$row[] = $total_marks;
		fputcsv( $fh, $row );
	}
	fclose( $fh );
	
	if ( file_exists( $file_path ) ) {
		ob_clean();
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . basename( $file_path ) . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		readfile( $file_path );
		exit;
	} else {
		wp_die( esc_html__( 'File not found.', 'mjschool' ) );
	}
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'result';
$exam_id    = isset( $_REQUEST['exam_id'] ) ? intval( wp_unslash( $_REQUEST['exam_id'] ) ) : 0;
$class_id   = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
$subject_id = isset( $_REQUEST['subject_id'] ) ? intval( wp_unslash( $_REQUEST['subject_id'] ) ) : 0;
?>
<div>
	<div class="mjschool-marks-list mjschool-list-padding-5px">
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		$message_string = '';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'This file type is not allowed, please upload CSV file.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'File size limit : 2 MB', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Marks Updated Successfully', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Marks Added Successfully', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'Please enter CSV File.', 'mjschool' );
				break;
		}
		if ( $message && ! empty( $message_string ) ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
		?>
		<div class="mjschool-panel-white">
			<div class="mjschool-panel-body">
				<?php $nonce = wp_create_nonce( 'mjschool_exam_result_tab' ); ?>
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
					<?php if ( $user_access_add === '1' ) { ?>
						<li class="<?php echo ( $active_tab === 'result' ) ? 'active' : ''; ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_result&tab=result&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo ( $active_tab === 'result' ) ? 'active' : ''; ?>">
								<?php esc_html_e( 'Manage Marks', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php echo ( $active_tab === 'multiple_subject_marks' ) ? 'active' : ''; ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_result&tab=multiple_subject_marks&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo ( $active_tab === 'multiple_subject_marks' ) ? 'active' : ''; ?>">
								<?php esc_html_e( 'Add Multiple Subject Marks', 'mjschool' ); ?>
							</a>
						</li>
					<?php } ?>
					<li class="<?php echo ( $active_tab === 'export_marks' ) ? 'active' : ''; ?>">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_result&tab=export_marks&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo ( $active_tab === 'export_marks' ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'Export Marks', 'mjschool' ); ?>
						</a>
					</li>
				</ul>
				<?php
				$tablename = 'mjschool_marks';
				if ( $active_tab === 'result' ) {
					// Check nonce for exam result tab.
					if ( isset( $_GET['tab'] ) ) {
						if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_exam_result_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}
					?>
					<div class="mjschool-popup-bg">
						<div class="mjschool-overlay-content mjschool-admission-popup">
							<div class="modal-content">
								<div class="mjschool-category-list"></div>
							</div>
						</div>
					</div>
					<?php if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) { ?>
						<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url( 'https://www.youtube.com/embed/CZQzPhCPIr4?si=Hg16bHUL2gzi9xLA' ); ?>" title="Marksheet Generation">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-youtube-icon.png' ); ?>" alt="<?php esc_attr_e( 'YouTube', 'mjschool' ); ?>">
						</a>
					<?php } ?>
					<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res">
						<form method="post" id="select_data">
							<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_manage_marks_nonce' ) ); ?>">
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select name="class_id" id="mjschool-class-list" class="form-control class_id_exam validate[required] text-input">
											<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
											<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
												<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
											<?php } ?>
										</select>
									</div>
									<?php if ( $school_type === 'school' ) { ?>
										<div class="col-md-6 input">
											<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
											<?php $class_section = isset( $_REQUEST['class_section'] ) ? intval( wp_unslash( $_REQUEST['class_section'] ) ) : ''; ?>
											<select name="class_section" class="form-control mjschool-section-id-exam" id="class_section">
												<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
												<?php
												if ( $class_section && isset( $_REQUEST['class_id'] ) ) {
													$req_class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
													foreach ( mjschool_get_class_sections( $req_class_id ) as $sectiondata ) {
														?>
														<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
														<?php
													}
												}
												?>
											</select>
										</div>
									<?php } ?>
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select id="mjschool-exam-id" name="exam_id" class="form-control exam_list validate[required] text-input">
											<?php
											if ( isset( $_POST['exam_id'] ) && isset( $_POST['class_id'] ) ) {
												$posted_class_id = intval( wp_unslash( $_POST['class_id'] ) );
												$exam_data = mjschool_get_all_exam_by_class_id_all( $posted_class_id );
												if ( ! empty( $exam_data ) ) {
													$exam_id_sanitize = intval( wp_unslash( $_POST['exam_id'] ) );
													foreach ( $exam_data as $retrieved_data ) {
														?>
														<option value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" <?php selected( $exam_id_sanitize, $retrieved_data->exam_id ); ?>><?php echo esc_html( $retrieved_data->exam_name ); ?></option>
														<?php
													}
												}
											} else {
												?>
												<option value=""><?php esc_html_e( 'Select Exam', 'mjschool' ); ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<?php if ( $school_type === 'university' ) { ?>
										<div class="col-md-6 input mjschool-error-msg-left-margin">
											<label class="ml-1 mjschool-custom-top-label top" for="mjschool-university-subject-list"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="subject_id" id="mjschool-university-subject-list" class="form-control validate[required] text-input">
												<?php
												global $wpdb;
												$posted_exam_id = isset( $_POST['exam_id'] ) ? intval( wp_unslash( $_POST['exam_id'] ) ) : 0;
												$subject_table_name = $wpdb->prefix . 'mjschool_subject';
												$mjschool_exam_obj_local = new Mjschool_exam();
												$exam_data_local = $mjschool_exam_obj_local->mjschool_exam_data( $posted_exam_id );
												$exam_subject_ids = array();
												if ( isset( $exam_data_local->subject_data ) ) {
													$all_exam_ids = json_decode( $exam_data_local->subject_data, true );
													if ( is_array( $all_exam_ids ) ) {
														$exam_subject_ids = array_column( $all_exam_ids, 'subject_id' );
													}
												}
												echo '<option value="">' . esc_html__( 'Select Subject', 'mjschool' ) . '</option>';
												if ( ! empty( $exam_subject_ids ) ) {
													$exam_subject_ids = array_map( 'intval', $exam_subject_ids );
													$placeholders = implode( ',', array_fill( 0, count( $exam_subject_ids ), '%d' ) );
													// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
													$subjects_for_exam = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $subject_table_name WHERE subid IN ($placeholders)", $exam_subject_ids ) );
													if ( ! empty( $subjects_for_exam ) ) {
														$subject_id_sanitize = isset( $_POST['subject_id'] ) ? intval( wp_unslash( $_POST['subject_id'] ) ) : '';
														foreach ( $subjects_for_exam as $subject_data ) {
															?>
															<option value="<?php echo esc_attr( $subject_data->subid ); ?>" <?php selected( $subject_id_sanitize, $subject_data->subid ); ?>>
																<?php echo esc_html( $subject_data->sub_name . '-' . $subject_data->subject_code ); ?>
															</option>
															<?php
														}
													}
												}
												?>
											</select>
										</div>
									<?php } else { ?>
										<div class="col-md-6 input mjschool-error-msg-left-margin">
											<label class="ml-1 mjschool-custom-top-label top" for="mjschool-subject-list"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="subject_id" id="mjschool-subject-list" class="form-control validate[required] text-input">
												<?php
												if ( isset( $_POST['subject_id'] ) && isset( $_POST['class_id'] ) ) {
													$posted_class_id = intval( wp_unslash( $_POST['class_id'] ) );
													$subject = mjschool_get_subject_by_class_id( $posted_class_id );
													if ( ! empty( $subject ) ) {
														$subject_id_sanitize = intval( wp_unslash( $_POST['subject_id'] ) );
														foreach ( $subject as $ubject_data ) {
															?>
															<option value="<?php echo esc_attr( $ubject_data->subid ); ?>" <?php selected( $subject_id_sanitize, $ubject_data->subid ); ?>><?php echo esc_html( $ubject_data->sub_name ); ?></option>
															<?php
														}
													}
												} else {
													?>
													<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
													<?php
												}
												?>
											</select>
										</div>
									<?php } ?>
									<div class="col-md-6">
										<input type="submit" value="<?php esc_attr_e( 'Manage Marks', 'mjschool' ); ?>" name="manage_mark" class="btn btn-info mjschool-save-btn" />
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="clearfix"></div>
					<?php
					if ( isset( $_REQUEST['manage_mark'] ) || isset( $_REQUEST['add_mark'] ) || isset( $_REQUEST['save_all_marks'] ) || isset( $_REQUEST['upload_csv_file'] ) ) {
						if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_manage_marks_nonce' ) ) {
							wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
						}
						
						$class_id      = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
						$subject_id    = isset( $_REQUEST['subject_id'] ) ? intval( wp_unslash( $_REQUEST['subject_id'] ) ) : 0;
						$exam_id       = isset( $_REQUEST['exam_id'] ) ? intval( wp_unslash( $_REQUEST['exam_id'] ) ) : 0;
						$error_message = '';
						
						if ( empty( $subject_id ) ) {
							$error_message = esc_html__( 'Select Subject ID', 'mjschool' );
						}
						if ( empty( $class_id ) ) {
							$error_message = esc_html__( 'Select Class ID', 'mjschool' );
						}
						if ( empty( $exam_id ) ) {
							$error_message = esc_html__( 'Select Exam ID', 'mjschool' );
						}
						if ( ! empty( $error_message ) ) {
							echo esc_html( $error_message );
							die();
						}
						
						if ( $school_type === 'university' ) {
							$student = mjschool_get_students_assigned_to_subject( $subject_id );
						} else {
							$class_section = isset( $_REQUEST['class_section'] ) ? intval( wp_unslash( $_REQUEST['class_section'] ) ) : '';
							$exlude_id = mjschool_approve_student_list();
							if ( $class_section ) {
								$student = get_users( array(
									'meta_key'   => 'class_section',
									'meta_value' => $class_section,
									'meta_query' => array( array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ),
									'role'       => 'student',
									'exclude'    => $exlude_id
								) );
							} else {
								$student = get_users( array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
							}
						}
						
						$exam_data = $mjschool_exam_obj->mjschool_exam_data( $exam_id );
						$row_data  = json_decode( $exam_data->subject_data, true );
						$sub_max_marks = null;
						if ( is_array( $row_data ) ) {
							foreach ( $row_data as $row_datas ) {
								if ( intval( $subject_id ) === intval( $row_datas['subject_id'] ) ) {
									$sub_max_marks = $row_datas['max_marks'];
									break;
								}
							}
						}
						$contributions = $exam_data->contributions;
						$contributions_data_array = array();
						if ( $contributions === 'yes' ) {
							$contributions_data_array = json_decode( $exam_data->contributions_data, true );
						}
						?>
						<div class="mjschool-panel-body clearfix mjschool-margin-top-20px">
							<form method="post" class="form-inline" id="marks_form" enctype="multipart/form-data">
								<input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_manage_marks_nonce' ) ); ?>">
								<input type="hidden" name="exam_id" value="<?php echo esc_attr( $exam_id ); ?>" />
								<input type="hidden" name="subject_id" value="<?php echo esc_attr( $subject_id ); ?>" />
								<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
								<input type="hidden" name="section_id" value="<?php echo isset( $_REQUEST['class_section'] ) ? esc_attr( intval( wp_unslash( $_REQUEST['class_section'] ) ) ) : ''; ?>" />
								<input type="hidden" name="class_section" value="<?php echo isset( $_REQUEST['class_section'] ) ? esc_attr( intval( wp_unslash( $_REQUEST['class_section'] ) ) ) : ''; ?>" />
								<input type="hidden" name="contributions" value="<?php echo esc_attr( $contributions ); ?>" />
								<!-- Rest of form content continues... -->
								<?php if ( ! empty( $student ) ) { ?>
									<div class="col-sm-6 mjschool-margin-top-15px">
										<input type="submit" class="btn btn-success mjschool-save-btn" name="save_all_marks" value="<?php esc_attr_e( 'Update All Marks', 'mjschool' ); ?>">
									</div>
								<?php } ?>
							</form>
						</div>
						<?php
					}
				}
				if ( $active_tab === 'export_marks' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/mark/export-marks.php';
				}
				if ( $active_tab === 'multiple_subject_marks' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/mark/add-multiple-subject-marks.php';
				}
				?>
			</div>
		</div>
	</div>
</div>