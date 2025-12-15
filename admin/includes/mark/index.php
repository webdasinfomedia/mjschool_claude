<?php
/**
 * Marks Management Interface.
 *
 * Handles all backend operations related to managing student marks within the MJSchool plugin.
 * This file allows administrators, teachers, and authorized users to add, edit, import, and export
 * marks for students. It supports multiple grading structures, contributions (component marks),
 * and secure data handling through WordPress standards.
 *
 * Key Features:
 * - Provides CRUD (Create, Read, Update, Delete) operations for student marks.
 * - Supports CSV file import for bulk mark entry.
 * - Allows per-student and batch mark updates.
 * - Exports marks to downloadable CSV reports with total calculations.
 * - Validates and sanitizes all inputs using WordPress and jQuery ValidationEngine.
 * - Enforces user access rights based on roles and permissions.
 * - Includes contribution-based grading (e.g., assignment, viva, project components).
 * - Supports dynamic filtering by class, section, and exam.
 * - Uses WordPress nonces and capability checks for enhanced security.
 * - Ensures fully translatable UI text using WordPress i18n functions.
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
// ADD SINGLE MARK ENTRY
// -----------------------------------------------------------------------------
if ( isset( $_REQUEST['add_mark'] ) ) {
	$user_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['add_mark'] ) );
	$contributions = isset( $_REQUEST['contributions'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contributions'] ) ) : '';
	if ( $contributions === 'yes' ) {
		$class_marks_raw = isset( $_REQUEST['class_marks_'][ $user_id ] ) ? wp_unslash( $_REQUEST['class_marks_'][ $user_id ] ) : array();
		$class_marks_arr = is_array( $class_marks_raw ) ? $class_marks_raw : array( $class_marks_raw );
		$class_marks_sanitized = array_map( 'sanitize_text_field', $class_marks_arr );
		$class_marks = json_encode( $class_marks_sanitized );
		$marks = 0;
	} else {
		$marks = isset( $_REQUEST[ 'marks_' . $user_id ] ) ? (int) sanitize_text_field( wp_unslash( $_REQUEST[ 'marks_' . $user_id ] ) ) : 0;
		$class_marks = '';
	}
	$comment = isset( $_REQUEST[ 'comment_' . $user_id ] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST[ 'comment_' . $user_id ] ) ) : '';
	$exam_id    = isset( $_REQUEST['exam_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_id'] ) ) : '';
	$class_id   = isset( $_REQUEST['class_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) : '';
	$subject_id = isset( $_REQUEST['subject_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_id'] ) ) : '';
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
	// SAVE
	if ( isset( $_REQUEST[ 'save_' . $user_id ] ) ) {
		$mjschool_obj_marks->mjschool_save_marks( $mark_data );
		$nonce = wp_create_nonce( 'mjschool_exam_result_tab' );
		wp_redirect( admin_url() . 'admin.php?page=mjschool_result&tab=result&_wpnonce=' . esc_attr( $nonce ) . '&message=4' );
		die();
	}
	// UPDATE
	$mark_id_val = isset( $_REQUEST[ 'mark_id_' . $user_id ] ) ? (int) sanitize_text_field( wp_unslash( $_REQUEST[ 'mark_id_' . $user_id ] ) ) : 0;
	$mark_id = array( 'mark_id' => $mark_id_val );
	$result = $mjschool_obj_marks->mjschool_update_marks( $mark_data, $mark_id );
	if ( $result ) {
		$nonce = wp_create_nonce( 'mjschool_exam_result_tab' );
		wp_redirect( admin_url() . 'admin.php?page=mjschool_result&tab=result&_wpnonce=' . esc_attr( $nonce ) . '&message=3' );
		die();
	}
}
// -----------------------------------------------------------------------------
// SAVE ALL MARKS
// -----------------------------------------------------------------------------
if ( isset( $_REQUEST['save_all_marks'] ) ) {
	$exam_id    = sanitize_text_field( wp_unslash( $_REQUEST['exam_id'] ) );
	$class_id   = sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) );
	$subject_id = sanitize_text_field( wp_unslash( $_REQUEST['subject_id'] ) );
	$contributions = isset( $_REQUEST['contributions'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contributions'] ) ) : '';
	$flag = 0;
	// Load students
	if ( $school_type === "university" ) {
		$student = mjschool_get_students_assigned_to_subject( $subject_id );
	} else {
		$section_id = isset( $_REQUEST['section_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['section_id'] ) ) : '';
		$exclude_id = mjschool_approve_student_list();
		if ( $section_id ) {
			$student = get_users(
				array(
					'meta_key'   => 'class_section',
					'meta_value' => $section_id,
					'meta_query' => array(
						array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ),
					),
					'role'    => 'student',
					'exclude' => $exclude_id,
				)
			);
		} else {
			$student = get_users(
				array(
					'meta_key'   => 'class_name',
					'meta_value' => $class_id,
					'role'       => 'student',
					'exclude'    => $exclude_id,
				)
			);
		}
	}
	foreach ( $student as $user ) {
		$user_id = (int) $user->ID;
		// Contributions?
		if ( $contributions === 'yes' ) {
			$class_marks_raw = $_REQUEST['class_marks_'][ $user_id ] ?? array();
			$class_marks_arr = is_array( $class_marks_raw ) ? wp_unslash( $class_marks_raw ) : array( $class_marks_raw );
			$class_marks_arr = array_map( 'sanitize_text_field', $class_marks_arr );
			$class_marks = json_encode( $class_marks_arr );
			$marks = 0;
		} else {
			$marks = isset( $_REQUEST[ 'marks_' . $user_id ] ) ? (int) sanitize_text_field( wp_unslash( $_REQUEST[ 'marks_' . $user_id ] ) ) : 0;
			$class_marks = '';
		}
		$comment = isset( $_REQUEST[ 'comment_' . $user_id ] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST[ 'comment_' . $user_id ] ) ) : '';
		$grade_id = $mjschool_obj_marks->mjschool_get_grade_id( $marks );
		$grade_id = $grade_id ? $grade_id : 0;
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

			$mark_id_raw = $_REQUEST[ 'mark_id_' . $user_id ] ?? 0;
			$mark_id     = array( 'mark_id' => (int) sanitize_text_field( wp_unslash( $mark_id_raw ) ) );
			$result = $mjschool_obj_marks->mjschool_update_marks( $mark_data, $mark_id );
			if ( $result ) {
				$flag = 0;
			}
		} else {
			global $wpdb;
			$table_name = $wpdb->prefix . 'mjschool_marks';
			$result = $wpdb->insert( $table_name, $mark_data );
			if ( $result ) {
				$flag = 1;
			}
		}
	}
	$nonce = wp_create_nonce( 'mjschool_exam_result_tab' );
	$message = ( $flag === 1 ) ? 4 : 3;
	wp_redirect( admin_url() . 'admin.php?page=mjschool_result&tab=result&_wpnonce=' . esc_attr( $nonce ) . '&message=' . $message );
	die();
}

if ( isset( $_POST['export_marks'] ) ) {
	$exam_id       = intval( wp_unslash($_REQUEST['exam_id']) );
	$class_id      = intval( wp_unslash($_REQUEST['class_id']) );
	$class_section = isset( $_REQUEST['class_section'] ) ? intval( wp_unslash($_REQUEST['class_section']) ) : 0;
	$subject_list  = $mjschool_obj_marks->mjschool_student_subject_export( $class_id, $class_section );
	$exlude_id     = mjschool_approve_student_list();
	
	$user_args = [
		'role'       => 'student',
		'exclude'    => $exlude_id,
		'meta_query' => [['key' => 'class_name', 'value' => $class_id, 'compare' => '=']]
	];
	if ($class_section) {
		$user_args['meta_key'] = 'class_section';
		$user_args['meta_value'] = $class_section;
	}
	
	$students                 = get_users( $user_args );
	$exam_data                = $mjschool_exam_obj->mjschool_exam_data( $exam_id );
	$contributions            = $exam_data->contributions;
	$contributions_data_array = $contributions === 'yes' ? json_decode( $exam_data->contributions_data, true ) : array();
	$header                   = array( 'Roll No', 'Student Name', 'Class', 'Section Name' );
	$subject_array            = array();
	
	if ( $school_type === 'university' )
	{
		$enabled_subject_ids = [];
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
				$header[] = $subject->sub_name . '( ' . esc_attr( $con_value['label'] ) . ' )';
			}
		} else {
			$header[] = $subject->sub_name;
		}
		$subject_array[] = $subject->subid;
	}
	$header[]  = 'Total';
	$file_path = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-marks.csv';
	if ( ( $fh = fopen( $file_path, 'w' ) ) === false ) {
		wp_die( 'Cannot open file for writing.' );
	}
	fputcsv( $fh, $header );
	foreach ( $students as $student ) {
		$row         = array(
			get_user_meta( $student->ID, 'roll_id', true ),
			mjschool_get_user_name_by_id( $student->ID ),
			mjschool_get_class_name( $class_id ),
			mjschool_get_section_name( $class_section ),
		);
		$total_marks = 0;
		foreach ( $subject_array as $sub_id ) {
			$ob_marks = $mjschool_obj_marks->mjschool_get_marks( $exam_id, $class_id, $sub_id, $student->ID ) ?? 0;
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
	// Force the download of CSV files.
	if ( file_exists( $file_path ) ) {
		ob_clean();
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . basename( $file_path ) . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		readfile( $file_path );
		die();
	} else {
		wp_die( 'File not found.' );
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'result';
if ( isset( $_REQUEST['exam_id'] ) ) {
	$exam_id = sanitize_text_field(wp_unslash($_REQUEST['exam_id']));
}
if ( isset( $_REQUEST['class_id'] ) ) {
	$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
}
if ( isset( $_REQUEST['subject_id'] ) ) {
	$subject_id = sanitize_text_field(wp_unslash($_REQUEST['subject_id']));
}
?>
<div>
	<div class="mjschool-marks-list mjschool-list-padding-5px">
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
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
		if ( $message ) {
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
					<?php
					if ( $user_access_add === '1' ) {
						?>
						<li class="<?php if ( $active_tab === 'result' ) { ?>active<?php } ?>">
							<a href="?page=mjschool_result&tab=result&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'result' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Manage Marks', 'mjschool' ); ?>
							</a>
						</li>
						<?php
					}
					if ( $user_access_add === '1' ) {
						?>
						<li class="<?php if ( $active_tab === 'multiple_subject_marks' ) { ?>active<?php } ?>">
							<a href="?page=mjschool_result&tab=multiple_subject_marks&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'multiple_subject_marks' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Add Multiple Subject Marks', 'mjschool' ); ?>
							</a>
						</li>
						<?php
					}
					?>
					<li class="<?php if ( $active_tab === 'export_marks' ) { ?>active<?php } ?>">
						<a href="?page=mjschool_result&tab=export_marks&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'export_marks' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Export Marks', 'mjschool' ); ?>
						</a>
					</li>
				</ul>
				<?php
				$tablename = 'mjschool_marks';
				if ($active_tab === 'result' ) {
					// Check nonce for exam result tab.
					if ( isset( $_GET['tab'] ) ) {
						if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_exam_result_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}
					?>
					<div class="mjschool-popup-bg">
						<div class="mjschool-overlay-content mjschool-admission-popup">
							<div class="modal-content">
								<div class="mjschool-category-list">
								</div>
							</div>
						</div>
					</div>
					<?php
					if (get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
						?>
						<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo 'https://www.youtube.com/embed/CZQzPhCPIr4?si=Hg16bHUL2gzi9xLA'; ?>" title="Marksheet Generation">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-youtube-icon.png"); ?>" alt="<?php esc_html_e( 'YouTube', 'mjschool' ); ?>">
						</a>
						<?php
					}
					?>
					<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res">
						<form method="post" id="select_data">
							<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_manage_marks_nonce' ) ); ?>">
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select name="class_id" id="mjschool-class-list" class="form-control class_id_exam validate[required] text-input">
											<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
											<?php
											foreach (mjschool_get_all_class() as $classdata) {
												?>
												<option value="<?php echo esc_attr($classdata['class_id']); ?>" <?php selected($classdata['class_id'], $class_id); ?>><?php echo esc_html( $classdata['class_name']); ?></option>
											<?php } ?>
										</select>
									</div>
									<?php
									if ( $school_type === 'school' ){
										?>
										<div class="col-md-6 input">
											<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
											<?php
											$class_section = '';
											if ( isset( $_REQUEST['class_section'] ) ) {
												$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
											}
											?>
											<select name="class_section" class="form-control mjschool-section-id-exam" id="class_section">
												<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
												<?php
												if ( isset( $_REQUEST['class_section'] ) ) {
													$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
													foreach (mjschool_get_class_sections($_REQUEST['class_id']) as $sectiondata) {
														?>
														<option value="<?php echo esc_attr($sectiondata->id); ?>" <?php selected($class_section, $sectiondata->id); ?>><?php echo esc_html( $sectiondata->section_name); ?></option>
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
											if ( isset( $_POST['exam_id'] ) ) {
												$exam_data = mjschool_get_all_exam_by_class_id_all(sanitize_text_field(wp_unslash($_POST['class_id'])));
												if ( ! empty( $exam_data ) ) {
													foreach ($exam_data as $retrieved_data) {
														$exam_id_sanitize = isset($_POST['exam_id']) ? sanitize_text_field(wp_unslash($_POST['exam_id'])) : '';
														?>
														<option value="<?php echo esc_attr($retrieved_data->exam_id); ?>" <?php selected($exam_id_sanitize, $retrieved_data->exam_id); ?>><?php echo esc_html( $retrieved_data->exam_name); ?></option>
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
									<?php
									if ( $school_type === 'university' )
									{ ?>
										<div class="col-md-6 input mjschool-error-msg-left-margin">
											<label class="ml-1 mjschool-custom-top-label top" for="mjschool-university-subject-list"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="subject_id" id="mjschool-university-subject-list" class="form-control validate[required] text-input">
												<?php
												global $wpdb;
												$exam_id = intval(wp_unslash($_POST['exam_id'])); // Sanitize the exam ID.
												$subject_table_name = $wpdb->prefix . 'mjschool_subject';
												$mjschool_exam_obj = new Mjschool_exam();
												$exam_data = $mjschool_exam_obj->mjschool_exam_data($exam_id);
												$exam_subject_ids = [];
												if ( isset( $exam_data->subject_data ) ) {
													$all_exam_ids = json_decode($exam_data->subject_data, true);
													if (is_array($all_exam_ids ) ) {
														$exam_subject_ids = array_column($all_exam_ids, 'subject_id' );
													}
												}
												// Default option.
												echo '<option value="">' . esc_html__( 'Select Subject', 'mjschool' ) . '</option>';
												if ( ! empty( $exam_subject_ids ) ) {
													$exam_subject_ids = array_map( 'intval', $exam_subject_ids);
													$placeholders = implode( ',', array_fill(0, count($exam_subject_ids), '%d' ) );
													$query = "SELECT * FROM $subject_table_name WHERE subid IN ($placeholders)";
													// Prepare and execute the query.
													// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
													$subjects_for_exam = $wpdb->get_results($wpdb->prepare($query, $exam_subject_ids ) );

													if ( ! empty( $subjects_for_exam ) ) {
														foreach ($subjects_for_exam as $subject_data) {
															$subject_id_sanitize = isset($_POST['subject_id']) ? sanitize_text_field(wp_unslash($_POST['subject_id'])) : '';
															?>
															<option value="<?php echo esc_attr($subject_data->subid); ?>" <?php selected($subject_id_sanitize, $subject_data->subid); ?>>
																<?php echo esc_html( $subject_data->sub_name . '-' . $subject_data->subject_code); ?>
															</option>
															<?php
														}
													}	
												} else {
													// This will show if no exam was selected yet (e.g., on a fresh page load).
													?>
													<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
													<?php
												}
												?>
											</select>
										</div>
										<?php 
									} else{ ?>
										<div class="col-md-6 input mjschool-error-msg-left-margin">
											<label class="ml-1 mjschool-custom-top-label top" for="mjschool-subject-list"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="subject_id" id="mjschool-subject-list" class="form-control validate[required] text-input">
												<?php
												if ( isset( $_POST['subject_id'] ) ) {
													$subject = mjschool_get_subject(sanitize_text_field(wp_unslash($_POST['subject_id'])));
													$subject = mjschool_get_subject_by_class_id(sanitize_text_field(wp_unslash($_POST['class_id'])));
													if ( ! empty( $subject ) ) {
														foreach ($subject as $ubject_data) {
															$subject_id_sanitize = isset($_POST['subject_id']) ? sanitize_text_field(wp_unslash($_POST['subject_id'])) : '';
															?>
															<option value="<?php echo esc_attr($ubject_data->subid); ?>" <?php selected($subject_id_sanitize, $ubject_data->subid); ?>><?php echo esc_html( $ubject_data->sub_name); ?></option>
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
										<input type="submit" value="<?php esc_html_e( 'Manage Marks', 'mjschool' ); ?>" name="manage_mark" class="btn btn-info mjschool-save-btn" />
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="clearfix"> </div>
					<?php
					if ( isset( $_REQUEST['manage_mark']) || isset($_REQUEST['add_mark']) || isset($_REQUEST['save_all_marks']) || isset($_REQUEST['upload_csv_file'] ) ) {
						if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_manage_marks_nonce')) {
							wp_die(esc_html__('Security check failed.', 'mjschool'));
						}
						$class_id      = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
						$subject_id    = sanitize_text_field(wp_unslash($_REQUEST['subject_id']));
						$exam_id       = sanitize_text_field(wp_unslash($_REQUEST['exam_id']));
						$error_message = '';
						if ($subject_id === ' ' ) {
							$error_message = esc_attr__( 'Select Subject ID', 'mjschool' );
						}
						if ($class_id === ' ' ) {
							$error_message = esc_attr__( 'Select Class ID', 'mjschool' );
						}
						if ($exam_id === ' ' ) {
							$error_message = esc_attr__( 'Select Exam ID', 'mjschool' );
						}
						if ($error_message != '' ) {
							echo esc_html( $error_message);
							die();
						}
						if ( $school_type === 'university' )
						{
							$student = $student= mjschool_get_students_assigned_to_subject($subject_id);
						}
						else
						{
							if ( isset( $_REQUEST['class_section']) && $_REQUEST['class_section'] != "") {
								$exlude_id = mjschool_approve_student_list();
								$student = get_users(array(
									'meta_key' => 'class_section',
									'meta_value' => sanitize_text_field(wp_unslash($_REQUEST['class_section'])),
									'meta_query' => array(array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ),
									'role' => 'student',
									'exclude' => $exlude_id
								 ) );
							} else {
								$exlude_id = mjschool_approve_student_list();
								$student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
							}
						}
						
						$exam_data       = $mjschool_exam_obj->mjschool_exam_data($exam_id);
						$row_data = json_decode($exam_data->subject_data, true);
						$sub_max_marks = null;
						foreach($row_data as $row_datas)
						{
							if ( $subject_id === $row_datas['subject_id'])
							{
								$sub_max_marks = $row_datas['max_marks'];
								break;
							}
						}
						$contributions   = $exam_data->contributions;
						if ($contributions === 'yes' ) {
							$contributions_data       = $exam_data->contributions_data;
							$contributions_data_array = json_decode($contributions_data, true);
						}
						?>
						<div class="mjschool-panel-body clearfix mjschool-margin-top-20px">
							<form method="post" class="form-inline" id="marks_form" enctype="multipart/form-data">
								<input type="hidden" name="exam_id" value="<?php echo esc_attr($exam_id); ?>" />
								<input type="hidden" name="subject_id" value="<?php echo esc_attr($subject_id); ?>" />
								<input type="hidden" name="class_id" value="<?php echo esc_attr($class_id); ?>" />
								<input type="hidden" name="section_id" value="<?php if( isset( $_REQUEST['class_section'] ) ){ echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['class_section']))); }?>" />
								<input type="hidden" name="class_section" value="<?php if( isset( $_REQUEST['class_section'] ) ){ echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['class_section']))); }?>" />
								<input type="hidden" name="contributions" value="<?php echo esc_attr($contributions); ?>" />
								<?php
								if ( ! empty( $student ) ) {
									?>
									<div class="form-body mjschool-user-form mjschool-margin-top-20px mjschool-padding-top-25px-res">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
														<label for="csv_file" class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px"><?php esc_html_e( 'Select CSV file', 'mjschool' ); ?></label>
														<div class="col-sm-12">
															<input type="file" name="csv_file" id="csv_file" class="d-inline form-control file mjschool-file-validation-for-exam" />
														</div>
													</div>
												</div>
											</div>
											<span class="mjschool-padding-top-25px-res whitespace_initial">
												<?php esc_html_e( 'CSV file Must have headers as follows', 'mjschool' ); ?> : <br>
												<?php echo esc_attr__( '1) Not contribution exam header', 'mjschool' ) . ' => ' . esc_attr( 'roll_no, name, marks, comment', 'mjschool' ); ?><br>
												<?php echo esc_attr__( '2 ) Contribution exam header', 'mjschool' ) . ' => ' . esc_attr( 'roll_no, name, class_marks_0, class_marks_1, comment', 'mjschool' ); ?>
											</span>
										</div>
									</div>
									<input type="submit" name="upload_csv_file" value="<?php esc_html_e( 'Fill data from CSV File', 'mjschool' ); ?>" class="fill_data btn mjschool-save-btn_1 mjschool-margin-top-20px" />
									<br/>
									<p></p>
									<div class="table-responsive">
										<table class="table col-md-12">
											<tr>
												<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
												<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Name', 'mjschool' ); ?></th>
												<?php
												if ($contributions === 'yes' && !empty($contributions_data_array ) ) {
													foreach ($contributions_data_array as $con_id => $con_value) {
														?>
														<th class="mjschool-multiple-subject-mark"><?php esc_html( '' . $con_value['label'] . ' ( ' . $con_value['mark'] . ' )' ); ?></th>
														<?php
													}
													?>
													<?php
												} else {
													if ( $school_type === 'university' )
													{?>
														<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Mark Obtained( '.$sub_max_marks.' )', 'mjschool' ); //phpcs:ignore ?></th>
														<?php		
													}
													else
													{
														?>
														<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Mark Obtained(100)', 'mjschool' ); ?></th>
														<?php
													}
												}
												?>
												<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
												<th>&nbsp;</th>
											</tr>
											<?php
											if ( isset( $_REQUEST['upload_csv_file'] ) ) {
												if ( isset( $_FILES['csv_file'] ) ) {
													$errors    = array();
													$file_name = $_FILES['csv_file']['name'];
													$file_size = $_FILES['csv_file']['size'];
													$file_tmp  = $_FILES['csv_file']['tmp_name'];
													$file_type = $_FILES['csv_file']['type'];
													$value     = explode( '.', $_FILES['csv_file']['name']);
													$file_ext  = strtolower(array_pop($value ) );
													$extensions = array( 'csv' );
													$upload_dir = wp_upload_dir();
													if ($file_size > 2097152 ) {
														$errors[] = 'File size limit : 2 MB';
														$msg      = '2';
													}
													if (empty($file_name) && empty($file_size ) ) {
														$errors[] = 'Please enter CSV File.';
														$msg      = '5';
													}
													if (empty($errors) === true) {
														$rows   = array_map( 'str_getcsv', file($file_tmp ) );
														$header = array_map( 'strtolower', array_shift($rows ) );
														$csv    = array();
														foreach ($rows as $row) {
															$csv[] = array_combine($header, $row);
														}
													} else {
														$nonce = wp_create_nonce( 'mjschool_exam_result_tab' );
														wp_redirect(admin_url() . "admin.php?page=mjschool_result&_wpnonce=".esc_attr( $nonce )."&message={$msg}");
														die();
													}
												}
											}
											function mjschool_get_csv_row_id($array, $roll_no)
											{
												if ( ! empty( $array ) ) {
													$marks_array = array();
													foreach ($array as $key => $value) {
														if ($roll_no === $value['roll_no']) {
															return $key;
														}
													}
													return null;
												}
											}
											function mjschool_array_map($element)
											{
												return $element['roll_no'];
											}
											if (!function_exists( 'mjschool_array_column' ) ) {
												function mjschool_array_column($array, $column_name)
												{
													return array_map( 'mjschool_array_map', $array, $column_name);
												}
											}
											$i = 0;
											foreach ($student as $user) {
												$mark_detail = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser($exam_id, $class_id, $subject_id, $user->ID);
												$button_text = esc_attr__( 'Add', 'mjschool' );
												if ( isset( $csv ) ) {
													$key = mjschool_get_csv_row_id($csv, $user->roll_id);
												}
												if ($mark_detail) {
													$mark_id       = $mark_detail->mark_id;
													$marks         = $mark_detail->marks;
													$class_marks   = json_decode($mark_detail->class_marks);
													$marks_comment = $mark_detail->marks_comment;
													$button_text   = esc_attr__( 'Update', 'mjschool' );
													$mjschool_action = 'edit';
												} else {
													$marks         = 0;
													$class_marks   = 0;
													$attendance    = 0;
													$marks_comment = '';
													$mjschool_action = 'save';
													$mark_id       = '0';
												}
												echo '<tr>';
												echo '<td><span ' . (isset($csv) && !(isset($key ) ) ? '>' : '>' ) . esc_html( $user->roll_id) . '</span></td>';
												echo '<td><span>' . esc_html( $user->first_name) . ' ' . esc_html( $user->last_name) . '</span></td>';
												if ($contributions === 'yes' ) {
													foreach ($contributions_data_array as $con_id => $con_value) {
														echo '<td id="mjschool-position-relative">';
														echo '<div class="form-group input mjschool-width-60px mjschool-margin-bottom-0px">';
														echo '<div class="col-md-12 form-control">';
														if ( $class_marks === 0 ) {
															echo '<input type="text" name="class_marks_[' . esc_attr( $user->ID ) . '][' . esc_attr( $con_id ) . ']" placeholder=' . esc_attr__( 'Mark', 'mjschool' ) . ' value="' . ( isset( $key ) ? esc_attr( $csv[ $key ] )[ 'class_marks_' . esc_attr( $con_id ) ] : esc_attr( $class_marks ) ) . '" class="form-control validate[required,custom[onlyNumberSp],min[0],max[' . esc_attr( $con_value['mark'] ) . ']] text-input">';
														} else {
															echo '<input type="text" name="class_marks_[' . esc_attr( $user->ID ) . '][' . esc_attr( $con_id ) . ']" placeholder=' . esc_attr__( 'Mark', 'mjschool' ) . ' value="' . ( isset( $key ) ? esc_attr( $csv[ $key ] )[ 'class_marks_' . esc_attr( $con_id ) ] : esc_attr( $class_marks[ $con_id ] ) ) . '" class="form-control validate[required,custom[onlyNumberSp],min[0],max[' . esc_attr( $con_value['mark'] ) . ']] text-input">';
														}
														echo '</div>';
														echo '</div>';
														echo '</td>';
													}
												} else {
													echo '<td id="mjschool-position-relative">';
													echo '<div class="form-group input mjschool-margin-bottom-0px">';
													echo '<div class="col-md-12 form-control">';
													echo '<input type="text" name="marks_' . esc_attr( $user->ID ) . '" placeholder="' . esc_attr__( 'Mark', 'mjschool' ) . '" value="' . ( isset( $key ) && isset( $csv[ $key ]['marks'] ) ? esc_attr( $csv[ $key ]['marks'] ) : esc_attr( $marks ) ) . '" class="form-control validate[required,custom[phone_number],max['.esc_attr($sub_max_marks).'],minSize[0],maxSize[5]] text-input">';
													echo '</div>';
													echo '</div>';
													echo '</td>';
												}
												echo '<td>';
												echo '<div class="form-group input mjschool-margin-bottom-0px">';
												echo '<div class="col-md-12 form-control">';
												echo '<input type="text" name="comment_' . esc_attr($user->ID) . '" placeholder=' . esc_attr__( 'Comment', 'mjschool' ) . ' value="' . (isset($key) && isset($csv[$key]['comment']) ? esc_attr($csv[$key]['comment']) : esc_attr($marks_comment ) ) . '" maxlength="50" class="form-control">';
												echo '</div>';
												echo '</div>';
												echo '</td>';
												echo '<td>';
												echo '<input type="hidden" name="' . esc_attr($mjschool_action) . '_' . esc_attr($user->ID) . '" value="' . esc_attr($marks_comment) . '" class="form-control">';
												echo '<input type="hidden" name="mark_id_' . esc_attr($user->ID) . '" value="' . esc_attr($mark_id) . '">';
												echo '<button type="submit" name="add_mark" value="' . esc_attr($user->ID) . '" class="btn-success mjschool-save-btn p-2 font_size_12px_res">' . esc_html( $button_text) . '</button>';
												echo '</td>';
												echo '</tr>';
											}
											?>
										</table>
									</div>
									<div class="col-sm-6 mjschool-margin-top-15px">
										<input type="submit" class="btn btn-success mjschool-save-btn" name="save_all_marks" value="<?php esc_html_e( 'Update All Marks', 'mjschool' ); ?>">
									</div>
									<?php
								} else {
									?>
									<div>
										<h4><?php echo esc_attr__( 'No Student Available In This Class.', 'mjschool' ); ?></h3>
									</div>
									<?php
								}
								?>
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