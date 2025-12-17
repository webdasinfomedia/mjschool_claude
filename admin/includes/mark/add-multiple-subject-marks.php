<?php
/**
 * MJSchool - Multiple Subject Marks Management.
 *
 * @package MJSchool
 * @subpackage MJSchool/admin/includes/mark
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );

// Check nonce for add multiple subject marks tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_exam_result_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res">
	<form method="post" id="multiple_subject_mark_data">
		<?php wp_nonce_field( 'mjschool_multiple_subject_marks', '_wpnonce' ); ?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<?php
				if ( $school_type === 'university' ) {
					?>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control class_id_exam validate[required] text-input">
							<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
							<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php } ?>
						</select>
					</div>
					<?php
				} else {
					?>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php
						$class_id = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : '';
						?>
						<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control class_id_exam validate[required] text-input">
							<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
							<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
						<?php
						$class_section = '';
						if ( isset( $_REQUEST['class_section'] ) ) {
							$class_section = intval( wp_unslash( $_REQUEST['class_section'] ) );
						} elseif ( isset( $_REQUEST['section_id'] ) ) {
							$class_section = intval( wp_unslash( $_REQUEST['section_id'] ) );
						}
						?>
						<select name="class_section" class="mjschool-line-height-30px form-control mjschool-section-id-exam" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( isset( $_REQUEST['class_section'] ) && isset( $_REQUEST['class_id'] ) ) {
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
					<?php
				}
				?>
				<div class="col-md-3 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select id="mjschool-exam-id" name="exam_id" class="mjschool-line-height-30px form-control exam_list validate[required] text-input">
						<?php
						if ( isset( $_POST['exam_id'] ) && isset( $_POST['class_id'] ) ) {
							$posted_class_id = intval( wp_unslash( $_POST['class_id'] ) );
							$exam_data = mjschool_get_all_exam_by_class_id_all( $posted_class_id );
							if ( ! empty( $exam_data ) ) {
								$posted_exam_id = intval( wp_unslash( $_POST['exam_id'] ) );
								foreach ( $exam_data as $retrieved_data ) {
									?>
									<option value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" <?php selected( $posted_exam_id, $retrieved_data->exam_id ); ?>><?php echo esc_html( $retrieved_data->exam_name ); ?></option>
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
				$mjschool_obj = new MJSchool_Management( get_current_user_id() );
				if ( $mjschool_obj->role === 'teacher' || $mjschool_obj->role === 'supportstaff' ) {
					$access = ( $user_access['add'] === '1' ) ? 1 : 0;
				} else {
					$access = 1;
				}
				if ( $access === 1 ) {
					?>
					<div class="form-group col-md-3">
						<input type="submit" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" name="add_multiple_subject_marks" class="btn height-auto btn-info mjschool-save-btn" />
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</form>
</div>
<?php
$current_date = current_time( 'Y-m-d H:i:s' );
$mjschool_obj = new MJSchool_Management( get_current_user_id() );

if ( isset( $_REQUEST['add_single_student_mark'] ) ) {
	// Verify nonce.
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'mjschool_multiple_subject_marks' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	
	if ( $school_type === 'university' ) {
		$add_single_mark = wp_unslash( $_REQUEST['add_single_student_mark'] );
		if ( is_array( $add_single_mark ) ) {
			$keys = array_keys( $add_single_mark );
			if ( ! empty( $keys[0] ) ) {
				$key_parts = explode( '_', sanitize_text_field( $keys[0] ) );
				$user_id    = isset( $key_parts[0] ) ? intval( $key_parts[0] ) : 0;
				$subject_id = isset( $key_parts[1] ) ? intval( $key_parts[1] ) : 0;

				$section_id    = isset( $_REQUEST['section_id'] ) ? intval( wp_unslash( $_REQUEST['section_id'] ) ) : '';
				$class_id      = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : '';
				$exam_id       = isset( $_REQUEST['exam_id'] ) ? intval( wp_unslash( $_REQUEST['exam_id'] ) ) : '';
				$contributions = isset( $_REQUEST['contributions'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contributions'] ) ) : '';

				$comment_key = 'marks_' . $user_id . '_' . $subject_id . '_comment';
				$mark_key    = 'marks_' . $user_id . '_' . $subject_id . '_mark';
				$mark_id_key = 'marks_' . $user_id . '_' . $subject_id . '_mark_id';

				$comment     = isset( $_REQUEST[ $comment_key ] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST[ $comment_key ] ) ) : '';
				$marks       = isset( $_REQUEST[ $mark_key ] ) ? intval( wp_unslash( $_REQUEST[ $mark_key ] ) ) : 0;
				$mark_id_val = isset( $_REQUEST[ $mark_id_key ] ) ? intval( wp_unslash( $_REQUEST[ $mark_id_key ] ) ) : 0;

				if ( $contributions === 'yes' ) {
					$class_marks_key = 'class_marks_' . $user_id . '_' . $subject_id . '_mark';
					$class_marks_raw = isset( $_REQUEST[ $class_marks_key ] ) ? wp_unslash( $_REQUEST[ $class_marks_key ] ) : array();
					$class_marks_array = is_array( $class_marks_raw ) ? $class_marks_raw : array( $class_marks_raw );
					$class_marks_sanitized = array_map( 'sanitize_text_field', $class_marks_array );
					$class_marks = wp_json_encode( $class_marks_sanitized );
					$marks = 0;
				} else {
					$class_marks = '';
				}

				$grade_id = $mjschool_obj_marks->mjschool_get_grade_id( $marks );
				$grade_id = $grade_id ? $grade_id : 0;

				$mark_detail = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $subject_id, $user_id );
				$mark_data = array(
					'exam_id'       => $exam_id,
					'class_id'      => $class_id,
					'section_id'    => $section_id,
					'subject_id'    => $subject_id,
					'marks'         => $marks,
					'class_marks'   => $class_marks,
					'contributions' => $contributions,
					'grade_id'      => $grade_id,
					'student_id'    => $user_id,
					'marks_comment' => $comment,
					'created_date'  => current_time( 'mysql' ),
					'created_by'    => get_current_user_id(),
				);

				$flag = 0;
				if ( $mark_detail ) {
					$flag   = 1;
					$result = $mjschool_obj_marks->mjschool_update_marks( $mark_data, array( 'mark_id' => $mark_id_val ) );
				} else {
					$flag   = 0;
					$result = $mjschool_obj_marks->mjschool_save_marks( $mark_data );
				}
				if ( $result ) {
					$nonce = wp_create_nonce( 'mjschool_exam_result_tab' );
					if ( is_super_admin() ) {
						$msg = ( $flag === 1 ) ? '3' : '4';
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_result&tab=multiple_subject_marks&_wpnonce=' . $nonce . '&message=' . $msg ) );
					} else {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=manage-marks&tab=multiple_subject_marks&_wpnonce=' . $nonce . '&message=4' ) );
					}
					exit;
				}
			}
		}
	} else {
		// Non-university flow
		$user_id      = intval( wp_unslash( $_REQUEST['add_single_student_mark'] ) );
		$section_id   = isset( $_REQUEST['section_id'] ) ? intval( wp_unslash( $_REQUEST['section_id'] ) ) : '';
		$class_id     = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : '';
		$exam_id      = isset( $_REQUEST['exam_id'] ) ? intval( wp_unslash( $_REQUEST['exam_id'] ) ) : '';
		$subject_list = $mjschool_obj_marks->mjschool_student_subject( $class_id, $section_id );
		$current_date = current_time( 'Y-m-d H:i:s' );
		$flag = 0;
		$contrib_value = isset( $_POST['contributions'] ) ? sanitize_text_field( wp_unslash( $_POST['contributions'] ) ) : '';
		
		foreach ( $subject_list as $sub_id ) {
			$comment_key = 'marks_' . $user_id . '_' . $sub_id->subid . '_comment';
			$marks_key   = 'marks_' . $user_id . '_' . $sub_id->subid . '_mark';
			$markid_key  = 'marks_' . $user_id . '_' . $sub_id->subid . '_mark_id';
			$comment = isset( $_REQUEST[ $comment_key ] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST[ $comment_key ] ) ) : '';
			
			if ( $contrib_value === 'yes' ) {
				$class_marks_raw = isset( $_REQUEST[ 'class_marks_' . $user_id . '_' . $sub_id->subid . '_mark' ] ) ? wp_unslash( $_REQUEST[ 'class_marks_' . $user_id . '_' . $sub_id->subid . '_mark' ] ) : array();
				$class_marks_array = is_array( $class_marks_raw ) ? $class_marks_raw : array( $class_marks_raw );
				$class_marks_sanitized = array_map( 'sanitize_text_field', $class_marks_array );
				$class_marks = wp_json_encode( $class_marks_sanitized );
				$marks = 0;
			} else {
				$marks = isset( $_REQUEST[ $marks_key ] ) ? intval( wp_unslash( $_REQUEST[ $marks_key ] ) ) : 0;
				$class_marks = '';
			}
			
			$grade_id = $mjschool_obj_marks->mjschool_get_grade_id( $marks );
			$grade_id = $grade_id ? $grade_id : 0;
			
			$mark_detail = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $sub_id->subid, $user_id );
			$mark_data = array(
				'exam_id'       => $exam_id,
				'class_id'      => $class_id,
				'section_id'    => $section_id,
				'subject_id'    => $sub_id->subid,
				'marks'         => $marks,
				'class_marks'   => $class_marks,
				'contributions' => $contrib_value,
				'grade_id'      => $grade_id,
				'student_id'    => $user_id,
				'marks_comment' => $comment,
				'created_date'  => $current_date,
				'created_by'    => get_current_user_id(),
			);
			
			if ( $mark_detail ) {
				$mark_id_raw = isset( $_REQUEST[ $markid_key ] ) ? intval( wp_unslash( $_REQUEST[ $markid_key ] ) ) : 0;
				$mark_id     = array( 'mark_id' => $mark_id_raw );
				$result      = $mjschool_obj_marks->mjschool_update_marks( $mark_data, $mark_id );
				if ( $result === 1 && is_super_admin() ) {
					$flag = 1;
				}
			} else {
				$result = $mjschool_obj_marks->mjschool_save_marks( $mark_data );
				if ( $result && is_super_admin() ) {
					$flag = 11;
				}
			}
		}
		$nonce = wp_create_nonce( 'mjschool_exam_result_tab' );
		$msg = ( $flag === 1 ) ? '3' : '4';
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_result&tab=multiple_subject_marks&_wpnonce=' . $nonce . '&message=' . $msg ) );
		exit;
	}
}

// Save multiple subject marks.
if ( isset( $_POST['save_all_multiple_subject_marks'] ) ) {
	// Verify nonce.
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'mjschool_multiple_subject_marks' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	
	$section_id   = isset( $_REQUEST['section_id'] ) ? intval( wp_unslash( $_REQUEST['section_id'] ) ) : '';
	$class_id     = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : '';
	$exam_id      = isset( $_REQUEST['exam_id'] ) ? intval( wp_unslash( $_REQUEST['exam_id'] ) ) : '';
	$contributions = isset( $_REQUEST['contributions'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contributions'] ) ) : '';
	$subject_list = $mjschool_obj_marks->mjschool_student_subject( $class_id, $section_id );
	
	$exlude_id = mjschool_approve_student_list();
	if ( $section_id ) {
		$student = get_users( array(
			'meta_key'    => 'class_section',
			'meta_value'  => $section_id,
			'meta_query'  => array( array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ),
			'role'        => 'student',
			'exclude'     => $exlude_id
		) );
	} else {
		$student = get_users( array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
	}
		
	foreach ( $student as $mjschool_user ) {
		foreach ( $subject_list as $sub_id ) {
			$mark_detail = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $sub_id->subid, $mjschool_user->ID );
			$comment_key = 'marks_' . $mjschool_user->ID . '_' . $sub_id->subid . '_comment';
			$comment     = isset( $_REQUEST[ $comment_key ] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST[ $comment_key ] ) ) : '';
			
			if ( $contributions === 'yes' ) {
				$class_marks_key = 'class_marks_' . $mjschool_user->ID . '_' . $sub_id->subid . '_mark';
				$class_marks_raw = isset( $_REQUEST[ $class_marks_key ] ) ? wp_unslash( $_REQUEST[ $class_marks_key ] ) : array();
				$class_marks_sanitized = is_array( $class_marks_raw ) ? array_map( 'sanitize_text_field', $class_marks_raw ) : array();
				$class_marks = wp_json_encode( $class_marks_sanitized );
				$marks       = 0;
			} else {
				$marks_key   = 'marks_' . $mjschool_user->ID . '_' . $sub_id->subid . '_mark';
				$marks       = isset( $_REQUEST[ $marks_key ] ) ? intval( wp_unslash( $_REQUEST[ $marks_key ] ) ) : 0;
				$class_marks = '';
			}
			
			$grade_id = $mjschool_obj_marks->mjschool_get_grade_id( $marks );
			$grade_id = $grade_id ? $grade_id : 0;
			
			$mark_data = array(
				'exam_id'       => $exam_id,
				'class_id'      => $class_id,
				'section_id'    => $section_id,
				'subject_id'    => $sub_id->subid,
				'marks'         => $marks,
				'class_marks'   => $class_marks,
				'contributions' => $contributions,
				'grade_id'      => $grade_id,
				'student_id'    => $mjschool_user->ID,
				'marks_comment' => $comment,
				'created_date'  => $current_date,
				'created_by'    => get_current_user_id(),
			);			
			if ( $mark_detail ) {
				$markid_key = 'marks_' . $mjschool_user->ID . '_' . $sub_id->subid . '_mark_id';
				$mark_id = isset( $_REQUEST[ $markid_key ] ) ? intval( wp_unslash( $_REQUEST[ $markid_key ] ) ) : 0;
				$result  = $mjschool_obj_marks->mjschool_update_marks( $mark_data, array( 'mark_id' => $mark_id ) );
			} else {
				$result = $mjschool_obj_marks->mjschool_save_marks( $mark_data );
			}
		}
	}
	$nonce = wp_create_nonce( 'mjschool_exam_result_tab' );
	if ( is_super_admin() ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_result&tab=multiple_subject_marks&_wpnonce=' . $nonce . '&message=3' ) );
		exit;
	} else {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=manage-marks&tab=multiple_subject_marks&_wpnonce=' . $nonce . '&message=4' ) );
		exit;
	}
}

if ( isset( $_POST['add_multiple_subject_marks'] ) || isset( $_POST['add_single_student_mark'] ) || isset( $_POST['save_all_multiple_subject_marks'] ) ) {
	$class_teacher = 0;
	$mjschool_role = $mjschool_obj->role;
	$teacher_id    = get_current_user_id();
	$class_name    = get_user_meta( $teacher_id, 'class_name', true );
	
	$class_id      = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : '';
	$class_section = isset( $_REQUEST['class_section'] ) ? intval( wp_unslash( $_REQUEST['class_section'] ) ) : '';
	$exam_id       = isset( $_REQUEST['exam_id'] ) ? intval( wp_unslash( $_REQUEST['exam_id'] ) ) : '';
	
	if ( $class_section ) {
		$subject_list = $mjschool_obj_marks->mjschool_student_subject_for_list( $class_id, $class_section );
		$exlude_id = mjschool_approve_student_list();
		$student = get_users( array( 'meta_key' => 'class_section', 'meta_value' => $class_section, 'meta_query' => array( array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ), 'role' => 'student', 'exclude' => $exlude_id ) );
	} else {
		$subject_list = $mjschool_obj_marks->mjschool_student_subject_for_list( $class_id );
		$exlude_id = mjschool_approve_student_list();
		$student = get_users( array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
	}
	
	$exam_data     = $mjschool_exam_obj->mjschool_exam_data( $exam_id );
	$contributions = $exam_data->contributions;
	if ( $contributions === 'yes' ) {
		$contributions_data       = $exam_data->contributions_data;
		$contributions_data_array = json_decode( $contributions_data, true );
	}
	
	if ( $class_teacher === 1 ) {
		?>
		<div class="mjschool-panel-heading">
			<h4 class="mjschool-panel-title"><?php esc_html_e( 'You cant change marks of other subjects', 'mjschool' ); ?></h4>
		</div>
		<?php
	} else {
		if ( $school_type === 'university' ) {
			?>
			<div class="clearfix panel-body p table_overflow_scroll">
				<form method="post" class="form-inline add_multiple_subject_mark_form" id="marks_form" enctype="multipart/form-data">
					<?php wp_nonce_field( 'mjschool_multiple_subject_marks', '_wpnonce' ); ?>
					<input type="hidden" name="exam_id" value="<?php echo esc_attr( $exam_id ); ?>" />
					<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
					<input type="hidden" name="section_id" value="<?php echo esc_attr( $class_section ); ?>" />
					<input type="hidden" name="contributions" value="<?php echo esc_attr( $contributions ); ?>" />
					<div class="table-responsive">
						<?php
						$posted_exam_id = isset( $_POST['exam_id'] ) ? intval( wp_unslash( $_POST['exam_id'] ) ) : 0;
						$exam_data = $mjschool_exam_obj->mjschool_exam_data( $posted_exam_id );
						$all_subjects_max_marks_data = array();
						if ( $exam_data && ! empty( $exam_data->subject_data ) ) {
							$all_subjects_max_marks_data = json_decode( $exam_data->subject_data, true );
							$exam_subject_ids = array_column( $all_subjects_max_marks_data, 'subject_id' );
							$subject_list = array_filter( $subject_list, function( $subject ) use ( $exam_subject_ids ) {
								return in_array( (int) $subject->subid, $exam_subject_ids );
							});
						}
						
						if ( ! empty( $subject_list ) ) {
							foreach ( $subject_list as $sub_id ) {
								$current_subject_max_marks = null;
								if ( ! empty( $all_subjects_max_marks_data ) ) {
									foreach ( $all_subjects_max_marks_data as $subject_marks_info ) {
										if ( $sub_id->subid === $subject_marks_info['subject_id'] ) {
											$current_subject_max_marks = $subject_marks_info['max_marks'];
											break;
										}
									}
								}
								?>
								<h3 class="subject-title mt-4"><?php echo esc_html( $sub_id->sub_name ); ?></h3>
								<table class="table table-bordered col-md-12">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Name', 'mjschool' ); ?></th>
											<th><?php echo esc_html( sprintf( __( 'Mark( %s )', 'mjschool' ), $current_subject_max_marks ) ); ?></th>
											<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ( $student as $mjschool_user ) {
											$assigned_student_ids = array_map( 'intval', explode( ',', $sub_id->selected_students ) );
											$current_student_id   = (int) $mjschool_user->ID;
											if ( ! in_array( $current_student_id, $assigned_student_ids, true ) ) {
												continue;
											}
											$button_text   = esc_html__( 'Add', 'mjschool' );
											$mark_detail   = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $sub_id->subid, $mjschool_user->ID );
											$marks         = '0';
											$marks_comment = '';
											$mark_id       = '0';
											$class_ob_marks = '';
											if ( $mark_detail ) {
												$mark_id        = $mark_detail->mark_id;
												$marks          = $mark_detail->marks;
												$class_ob_marks = json_decode( $mark_detail->class_marks );
												$marks_comment  = $mark_detail->marks_comment;
												$button_text    = esc_html__( 'Update', 'mjschool' );
											}
											?>
											<tr>
												<td><?php echo esc_html( $mjschool_user->roll_id ); ?></td>
												<td><?php echo esc_html( mjschool_get_user_name_by_id( $mjschool_user->ID ) ); ?></td>
												<td>
													<input type="text" name="marks_<?php echo esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ); ?>_mark" value="<?php echo esc_attr( $marks ); ?>" class="form-control validate[required,custom[onlyNumberSp],min[0],max[<?php echo esc_attr( $current_subject_max_marks ); ?>]] text-input" placeholder="<?php esc_attr_e( 'Mark', 'mjschool' ); ?>">
												</td>
												<td>
													<input type="text" maxlength="50" name="marks_<?php echo esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ); ?>_comment" value="<?php echo esc_attr( $marks_comment ); ?>" class="form-control text-input" placeholder="<?php esc_attr_e( 'Comment', 'mjschool' ); ?>">
													<input type="hidden" value="<?php echo esc_attr( $mark_id ); ?>" name="marks_<?php echo esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ); ?>_mark_id">
												</td>
												<td>
													<button type="submit" name="add_single_student_mark[<?php echo esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ); ?>]" class="btn btn-success mjschool-save-btn"><?php echo esc_html( $button_text ); ?></button>
												</td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
								<?php
							}
						}
						?>
					</div>
					<?php
					$mjschool_obj = new MJSchool_Management( get_current_user_id() );
					if ( $mjschool_obj->role === 'teacher' || $mjschool_obj->role === 'supportstaff' ) {
						$access = ( $user_access['edit'] === '1' ) ? 1 : 0;
					} else {
						$access = 1;
					}
					if ( $access === 1 ) {
						?>
						<div class="col-sm-6 mt-4">
							<input type="submit" class="btn btn-success mjschool-save-btn" name="save_all_multiple_subject_marks" value="<?php esc_attr_e( 'Update All Marks', 'mjschool' ); ?>">
						</div>
						<?php
					}
					?>
				</form>
			</div>
			<?php
		} else {
			?>
			<div class="clearfix mjschool-panel-body p table_overflow_scroll">
				<form method="post" class="form-inline add_multiple_subject_mark_form" id="marks_form" enctype="multipart/form-data">
					<?php wp_nonce_field( 'mjschool_multiple_subject_marks', '_wpnonce' ); ?>
					<input type="hidden" name="exam_id" value="<?php echo esc_attr( $exam_id ); ?>" />
					<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
					<input type="hidden" name="section_id" value="<?php echo esc_attr( $class_section ); ?>" />
					<input type="hidden" name="contributions" value="<?php echo esc_attr( $contributions ); ?>" />
					<div class="table-responsive">
						<table class="table col-md-12">
							<?php
							if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
								?>
								<tr>
									<th class="mjschool-multiple-subject-mark mjschool_border_subject_mark"  rowspan="2"><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
									<th class="mjschool-multiple-subject-mark mjschool_border_subject_mark"  rowspan="2"><?php esc_html_e( 'Name', 'mjschool' ); ?></th>
									<?php
									if ( ! empty( $subject_list ) ) {
										foreach ( $subject_list as $sub_id ) {
											if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
												$count_array = count( $contributions_data_array );
												echo "<th class='multiple_subject_mark mjschool_border_subject_mark'  colspan=" . esc_html( $count_array ) . '> ' . esc_html( $sub_id->sub_name ) . ' </th>';
											}
										}
									}
									?>
									<th rowspan="2">&nbsp;</th>
								</tr>
								<tr>
									<?php
									
									if ( ! empty( $subject_list ) ) {
										foreach ( $subject_list as $sub_id ) {
											if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
												var_dump($contributions_data_array);
									die;
												foreach ( $contributions_data_array as $con_id => $con_value ) {
													?>
													<th class="mjschool-multiple-subject-mark mjschool_border_subject_mark" ><?php echo esc_html( '' . $con_value['label'] . '( ' . $con_value['mark'] . ' )' ); ?></th>
													<?php
												}
											}
										}
									}
									?>
								</tr>
								<?php
							} else {
								?>
								<tr>
									<th class="mjschool-multiple-subject-mark mjschool_border_subject_mark" ><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
									<th class="mjschool-multiple-subject-mark mjschool_border_subject_mark" ><?php esc_html_e( 'Name', 'mjschool' ); ?></th>
									<?php
									if ( ! empty( $subject_list ) ) {
										foreach ( $subject_list as $sub_id ) {
											echo "<th class='multiple_subject_mark mjschool_border_subject_mark' > " . esc_html( $sub_id->sub_name ) . ' </th>';
										}
									}
									?>
									<th>&nbsp;</th>
								</tr>
								<?php
							}
							foreach ( $student as $mjschool_user ) {
								$button_text = esc_attr__( 'Add', 'mjschool' );
								$mark_detail = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $subject_id, $mjschool_user->ID );
								if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
									echo '<tr>';
									echo '<td class="multiple_mark_value mjschool_border_subject_mark" rowspan="2" >' . esc_attr( $mjschool_user->roll_id ) . '</td>';
									echo '<td rowspan="2" class="mjschool_border_subject_mark" ><span class="multiple_mark_value">' . esc_html( mjschool_get_user_name_by_id( $mjschool_user->ID ) ) . '</span></td>';
								} else {
									echo '<tr>';
									echo '<td class="multiple_mark_value mjschool_border_subject_mark" >' . esc_html( $mjschool_user->roll_id ) . '</td>';
									echo '<td class="mjschool_border_subject_mark" ><span class="multiple_mark_value">' . esc_html( mjschool_get_user_name_by_id( $mjschool_user->ID ) ) . '</span></td>';
								}
								if ( ! empty( $subject_list ) ) {
									foreach ( $subject_list as $sub_id ) {
										$mark_detail = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $sub_id->subid, $mjschool_user->ID );
										if ( $mark_detail ) {
											$mark_id          = $mark_detail->mark_id;
											$marks            = $mark_detail->marks;
											$class_ob_marks   = json_decode( $mark_detail->class_marks );
											$marks_comment    = $mark_detail->marks_comment;
											$button_text      = esc_attr__( 'Update', 'mjschool' );
											$mjschool_action = 'edit';
										} else {
											$marks          = '0';
											$attendance     = 0;
											$class_ob_marks = '';
											$marks_comment  = '';
											$mark_id        = '0';
										}
										if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
											foreach ( $contributions_data_array as $con_id => $con_value ) {
												if ( ! empty( $class_ob_marks ) && is_array( $class_ob_marks ) ) {
													$class_marks = $class_ob_marks[ $con_id ];
												} else {
													$class_marks = 0;
												}
												echo '<td id="mjschool-position-relative" class="mjschool_border_subject_mark" >
													<div class="form-group input mjschool-width-60px margin_bottom_10px">
														<div class="col-md-12 form-control">
															<input type="text" name="class_marks_' . esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ) . '_mark[' . esc_attr( $con_id ) . ']" value="' . esc_attr( $class_marks ) . '" class="w-auto form-control validate[required,custom[onlyNumberSp],min[0],max[' . esc_attr( $con_value['mark'] ) . ']] text-input mjschool-width-100px" placeholder=' . esc_attr__( 'Mark', 'mjschool' ) . ' >
														</div>
													</div>
												</td>';
											}
										} else {
											echo '<td id="mjschool-position-relative mjschool_border_subject_mark" >
												<div class="form-group input margin_bottom_10px">
													<div class="col-md-12 form-control">
														<input type="text" name="marks_' . esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ) . '_mark" value="' . esc_attr( $marks ) . '" class="w-auto form-control validate[required,custom[onlyNumberSp],min[0],max[100]] text-input mjschool-width-100px" placeholder=' . esc_attr__( 'Mark', 'mjschool' ) . '>
													</div>
												</div>
												<div class="form-group input mjschool-margin-bottom-0px">
													<div class="col-md-12 form-control mjschool-margin-15px-rtl">
														<input type="text" maxlength="50" name="marks_' . esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ) . '_comment" value="' . esc_attr( $marks_comment ) . '" class="w-auto form-control text-input mjschool-width-100px" placeholder=' . esc_attr__( 'Comment', 'mjschool' ) . '">
													</div>
												</div>
												<input type="hidden" value="' . esc_attr( $mark_id ) . '" name="marks_' . esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ) . '_mark_id">
											</td>';
										}
									}
								}
								if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
									echo '<td rowspan="2"><button type="submit" name="add_single_student_mark" value="' . esc_attr( $mjschool_user->ID ) . '" class="p-2 btn btn-success height-auto mjschool-save-btn_multiple_mark mjschool-save-btn">' . esc_attr( $button_text ) . '</button></td>';
								} else {
									echo '<td><button type="submit" name="add_single_student_mark" value="' . esc_attr( $mjschool_user->ID ) . '" class="p-2 btn btn-success height-auto mjschool-save-btn_multiple_mark mjschool-save-btn">' . esc_html( $button_text ) . '</button></td>';
								}
								echo '</tr>';
								if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
									$count_array = count( $contributions_data_array );
									if ( ! empty( $subject_list ) ) {
										echo '<tr>';
										foreach ( $subject_list as $sub_id ) {
											$mark_detail = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $sub_id->subid, $mjschool_user->ID );
											if ( $mark_detail ) {
												$mark_id       = $mark_detail->mark_id;
												$marks_comment = $mark_detail->marks_comment;
											} else {
												$mark_id       = 0;
												$marks_comment = '';
											}
											echo '<td id="mjschool-position-relative" colspan="' . esc_attr( $count_array ) . '" class="mjschool_border_subject_mark" >
												<div class="form-group input mjschool-margin-bottom-0px">
													<div class="col-md-12 form-control mjschool-margin-15px-rtl">
														<input type="text" maxlength="50" name="marks_' . esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ) . '_comment" value="' . esc_attr( $marks_comment ) . '" class="w-auto form-control text-input mjschool-width-100px" placeholder=' . esc_attr__( 'Comment', 'mjschool' ) . ' >
													</div>
												</div>
												<input type="hidden" value="' . esc_attr( $mark_id ) . '" name="marks_' . esc_attr( $mjschool_user->ID ) . '_' . esc_attr( $sub_id->subid ) . '_mark_id">
											</td>';
										}
										echo '</tr>';
									}
								}
							}
						echo '</table>';
						?>
					</div>
					<?php
					$mjschool_obj = new MJSchool_Management( get_current_user_id() );
					if ( $mjschool_obj->role === 'teacher' || $mjschool_obj->role === 'supportstaff' ) {
						if ( $user_access['edit'] === '1' ) {
							$access = 1;
						} else {
							$access = 0;
						}
					} else {
						$access = 1;
					}
					if ( $access === 1 ) {
						?>
						<div class="col-sm-6 mjschool-margin-top-20px mjschool-padding-top-25px-res">
							<input type="submit" class="btn btn-success mjschool-save-btn" name="save_all_multiple_subject_marks" value="<?php esc_attr_e( 'Update All Marks', 'mjschool' ); ?>">
						</div>
						<?php
					}
					?>
				</form>
			</div>
			<?php
		}
	}
}
?>