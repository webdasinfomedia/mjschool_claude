<?php

/**
 * Exam Management Page.
 *
 * This file serves as the main administrative view and controller for managing
 * all **Examinations and Assessments** within the Mjschool system. It allows
 * administrators and authorized users to define, schedule, and manage exams
 * across different classes and sections.
 *
 * It is primarily responsible for:
 *
 * 1. **Access Control**: Implementing **role-based access control** by checking the
 * current user's role and specific rights ('view', 'add', 'edit', 'delete')
 * for the 'exam' module.
 * 2. **Tab Navigation**: Handling different views via tabs, including:
 * - `examlist`: A list of all scheduled examinations.
 * - `addexam`: The form for creating or editing an exam.
 * - `addexamtime`: The form for setting up the time table and subjects for a specific exam.
 * 3. **Form Handling (Exam)**: Displaying the 'Add/Edit Exam' form, which captures:
 * - Exam Name.
 * - Related Class and Section.
 * - Exam Date (Start Date).
 * - Exam Result Date.
 * 4. **Form Handling (Time Table)**: Providing an interface to set up the subject-wise
 * exam schedule, including:
 * - Subject Name.
 * - Exam Date.
 * - Start and End Times.
 * - Passing Marks and Maximum Marks.
 * 5. **Custom Fields**: Integrating the `Mjschool_Custome_Field` object to fetch
 * and display any custom fields associated with the 'exam' module.
 * 6. **CRUD Operations**: Processing form submissions (e.g., `save_exam`, `save_exam_table`)
 * for inserting/updating records in the exam and exam time table databases.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role_name                 = mjschool_get_user_role( get_current_user_id() );
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'exam';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
$active_tab                = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'examlist';
$mjschool_obj_exam         = new Mjschool_exam();
require_once MJSCHOOL_INCLUDES_DIR . '/class-mjschool-management.php';
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
$tablename = 'mjschool_exam';
// ----------------- Delete exam. ----------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = mjschool_delete_exam( $tablename, mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) );
		if ( $result ) {
			$nonce = wp_create_nonce( 'mjschool_exam_module_tab' );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam&tab=examlist&_wpnonce='.esc_attr( $nonce ).'&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ----------------- Delete multiple exams. ----------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_exam_admin_nonce' ) ) {
		if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
			foreach ( $_REQUEST['id'] as $id ) {
				$sanitized_id = intval( sanitize_text_field( wp_unslash( $id ) ) );
				$result = mjschool_delete_exam( $tablename, $sanitized_id );
			}
			if ( $result ) {
				$nonce = wp_create_nonce( 'mjschool_exam_module_tab' );
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam&tab=examlist&_wpnonce='.esc_attr( $nonce ).'&message=3' ) );
				die();
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// -----------Save exam. -------------------------//
if ( isset( $_POST['save_exam'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	$custribution_data = '';
	$custributions     = sanitize_text_field( wp_unslash( $_POST['contributions_section_option'] ) );
	if ( isset( $_POST['contributions_section_option'] ) && ( sanitize_text_field(wp_unslash($_POST['contributions_section_option'])) === 'yes' ) ) {
		$custribution_data = mjschool_get_costribution_data_jason( wp_unslash($_POST) );
	}
	$subject_data_array = [];
    if ( isset( $_POST['university_subjects']) && is_array($_POST['university_subjects'] ) ) {
        foreach ($_POST['university_subjects'] as $subid => $info) {
            $enabled = !empty($info['enabled']);
            // Only include if at least subject exists; you can choose to skip disabled ones if desired
			if ( $enabled === 'yes' )
			{
				$subject_data_array[] = [
					'subject_id'     => intval($subid),
					'max_marks'      => isset($info['total_mark']) ? sanitize_text_field(sanitize_text_field(wp_unslash($info['total_mark']))) : '',
					'passing_marks'  => isset($info['passing_mark']) ? sanitize_text_field(sanitize_text_field(wp_unslash($info['passing_mark']))) : '',
					'enable'         => $enabled ? 'yes' : 'no',
				];
			}
        }
    }
	$subject_data_json = wp_json_encode($subject_data_array);
	if ( wp_verify_nonce( $nonce, 'save_exam_admin_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_exam_module_tab' );
		$created_date = date( 'Y-m-d H:i:s' );
		$examdata     = array(
			'exam_name'          => sanitize_text_field( stripslashes( sanitize_text_field(wp_unslash($_POST['exam_name'])) ) ),
			'class_id'           => sanitize_text_field( wp_unslash($_POST['class_id']) ),
			'section_id'         => sanitize_text_field( wp_unslash($_POST['class_section']) ),
			'exam_term'          => sanitize_text_field( wp_unslash($_POST['exam_term']) ),
			'passing_mark'       => sanitize_text_field( wp_unslash($_POST['passing_mark']) ),
			'total_mark'         => sanitize_text_field( wp_unslash($_POST['total_mark']) ),
			'exam_start_date'    => date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['exam_start_date'] ) ) ) ),
			'exam_end_date'      => date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_POST['exam_end_date'])) ) ),
			'exam_comment'       => sanitize_textarea_field( stripslashes( sanitize_text_field(wp_unslash($_POST['exam_comment'])) ) ),
			'exam_creater_id'    => get_current_user_id(),
			'contributions'      => $custributions,
			'subject_data'		 => $subject_data_json,
			'contributions_data' => $custribution_data,
			'created_date'       => $created_date,
		);
		if ( intval( sanitize_text_field( wp_unslash( $_POST['passing_mark'] ) ) ) >= intval( sanitize_text_field( wp_unslash( $_POST['total_mark'] ) ) ) && $school_type === 'school' ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam&tab=examlist&_wpnonce='.esc_attr( $nonce ).'&message=6' ) );
			die();
		} else {
			// Table name without prefix.
			$tablename = 'mjschool_exam';
			if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
				if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
					if ( isset( $_FILES['exam_syllabus'] ) && ! empty( $_FILES['exam_syllabus'] ) && $_FILES['exam_syllabus']['size'] != 0 ) {
						if ( $_FILES['exam_syllabus']['size'] > 0 ) {
							$upload_docs1 = mjschool_load_documets_new( $_FILES['exam_syllabus'], $_FILES['exam_syllabus'], sanitize_text_field(wp_unslash($_POST['document_name'])) );
						}
					} elseif ( isset( $_REQUEST['old_hidden_exam_syllabus'] ) ) {
						$upload_docs1 = sanitize_text_field(wp_unslash($_REQUEST['old_hidden_exam_syllabus']));
					}
					$document_data = array();
					if ( ! empty( $upload_docs1 ) ) {
						$document_data[] = array(
							'title' => sanitize_text_field(wp_unslash($_POST['document_name'])),
							'value' => $upload_docs1,
						);
					} else {
						$document_data[] = '';
					}
					$exam_id                   = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) );
					$grade_id                  = array( 'exam_id' => intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) ) );
					$modified_date_date        = date( 'Y-m-d H:i:s' );
					$examdata['modified_date'] = $modified_date_date;
					$examdata['exam_syllabus'] = json_encode( $document_data );
					$result                    = mjschool_update_record( $tablename, $examdata, $grade_id );
					// Update custom field data.
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'exam';
					$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $exam_id );
					$exam                      = $examdata['exam_name'];
					mjschool_append_audit_log( '' . esc_html__( 'Exam Updated', 'mjschool' ) . '( ' . $exam . ' )' . '', mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ), get_current_user_id(), 'edit', sanitize_text_field(wp_unslash($_REQUEST['page'])) );
					if ( $result ) {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam&tab=examlist&_wpnonce='.esc_attr( $nonce ).'&message=2' ) );
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else {
				if ( isset( $_FILES['exam_syllabus'] ) && ! empty( $_FILES['exam_syllabus'] ) && $_FILES['exam_syllabus']['size'] != 0 ) {
					if ( $_FILES['exam_syllabus']['size'] > 0 ) {
						$upload_docs1 = mjschool_load_documets_new( $_FILES['exam_syllabus'], $_FILES['exam_syllabus'], sanitize_text_field(wp_unslash($_POST['document_name'])) );
					}
				} else {
					$upload_docs1 = '';
				}
				$document_data = array();
				if ( ! empty( $upload_docs1 ) ) {
					$document_data[] = array(
						'title' => sanitize_text_field(wp_unslash($_POST['document_name'])),
						'value' => $upload_docs1,
					);
				} else {
					$document_data[] = '';
				}
				$examdata['exam_syllabus'] = json_encode( $document_data );
				global $wpdb;
				$result         = mjschool_insert_record( $tablename, $examdata );
				$last_insert_id = $wpdb->insert_id;
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'exam';
				$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $last_insert_id );
				$exam_name                 = $examdata['exam_name'];
				mjschool_append_audit_log( '' . esc_html__( 'Exam Added', 'mjschool' ) . '( ' . $exam_name . ' )' . '', $result, get_current_user_id(), 'insert', sanitize_text_field(wp_unslash($_REQUEST['page'])) );
				if ( $result ) {
					if ( isset( $_POST['mjschool_enable_exam_mail'] ) === '1' ) {
						
						if (empty($_POST['class_section'] ) ) {
							$class_id = sanitize_text_field(wp_unslash($_POST['class_id']));
							$studentdata = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student' ) );
						} else {
							$studentdata = get_users(array(
								'meta_key' => 'class_section',
								'meta_value' => sanitize_text_field(wp_unslash($_POST['class_section'])),
								'meta_query' => array(array( 'key' => 'class_name', 'value' => sanitize_text_field(wp_unslash($_POST['class_id'])), 'compare' => '=' ) ),
								'role' => 'student'
							 ) );
						}
						
						if ( ! empty( $studentdata ) ) {
							foreach ( $studentdata as $userdata ) {
								$student_id   = $userdata->ID;
								$student_name = $userdata->display_name;
								if ( isset( $_POST['mjschool_enable_exam_mail'] ) && ( sanitize_text_field(wp_unslash($_POST['mjschool_enable_exam_mail'])) === '1' ) ) {
									$student_email                 = $userdata->user_email;
									$mjschool_add_exam_mailcontent = get_option( 'mjschool_add_exam_mailcontent' );
									$mjschool_add_exam_mail_title  = get_option( 'mjschool_add_exam_mail_title' );
									$parent                        = get_user_meta( $student_id, 'parent_id', true );
									if ( sanitize_text_field( wp_unslash( $_POST['exam_start_date'] ) ) === sanitize_text_field( wp_unslash( $_POST['exam_end_date'] ) ) ) {
										$start_end_date = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['exam_start_date'])) );
									} else {
										$start_end_date = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['exam_start_date'])) ) . ' ' . esc_html__( 'TO', 'mjschool' ) . ' ' . mjschool_get_date_in_input_box( $_POST['exam_end_date'] );
									}
									// Add Exam Mail Send To Parent.
									if ( ! empty( $parent ) ) {
										foreach ( $parent as $p ) {
											$user_info                            = get_userdata( $p );
											$email_to                             = $user_info->user_email;
											$searchArr                            = array();
											$parerntdata                          = get_user_by( 'email', $email_to );
											$searchArr['{{user_name}}']           = $parerntdata->display_name;
											$searchArr['{{exam_name}}']           = sanitize_text_field( stripslashes( $_POST['exam_name'] ) );
											$searchArr['{{exam_start_end_date}}'] = $start_end_date;
											$searchArr['{{exam_comment}}']        = sanitize_textarea_field( stripslashes( $_POST['exam_comment'] ) );
											$searchArr['{{school_name}}']         = get_option( 'mjschool_name' );
											$message                              = mjschool_string_replacement( $searchArr, $mjschool_add_exam_mailcontent );
											if ( ! empty( $document_data ) && is_array( $document_data ) && isset( $document_data[0]['value'] ) && ! empty( trim( $document_data[0]['value'] ) ) ) {
												$attachment = WP_CONTENT_DIR . '/uploads/school_assets/' . $document_data[0]['value'];
											} else {
												$attachment = null; // Handle the case where there's no valid value.
											}
											$mail = mjschool_send_mail_for_homework( $email_to, $mjschool_add_exam_mail_title, $message, $attechment );
										}
									}
									// Add Exam Mail Send To Student.
									$string                            = array();
									$string['{{user_name}}']           = $student_name;
									$string['{{exam_name}}']           = sanitize_textarea_field( stripslashes( $_POST['exam_name'] ) );
									$string['{{exam_start_end_date}}'] = $start_end_date;
									$string['{{exam_comment}}']        = sanitize_textarea_field( stripslashes( $_POST['exam_comment'] ) );
									$string['{{school_name}}']         = get_option( 'mjschool_name' );
									$message                           = mjschool_string_replacement( $string, $mjschool_add_exam_mailcontent );
									if ( ! empty( $document_data ) && is_array( $document_data ) && isset( $document_data[0]['value'] ) && ! empty( trim( $document_data[0]['value'] ) ) ) {
										$attachment = WP_CONTENT_DIR . '/uploads/school_assets/' . $document_data[0]['value'];
									} else {
										$attachment = null; // Handle the case where there's no valid value.
									}
									$mail = mjschool_send_mail_for_homework( $student_email, $mjschool_add_exam_mail_title, $message, $attechment );
								}
								if ( isset( $_POST['smgt_enable_exam_mjschool_student'] ) && ( sanitize_text_field(wp_unslash($_POST['smgt_enable_exam_mjschool_student'])) === '1' ) ) {
									$SMSArr                    = array();
									$SMSCon                    = get_option( 'mjschool_exam_student_mjschool_content' );
									$SMSArr['{{exam_name}}']   = sanitize_textarea_field( stripslashes( $_POST['exam_name'] ) );
									$SMSArr['{{date}}']        = $start_end_date;
									$SMSArr['{{school_name}}'] = get_option( 'mjschool_name' );
									$message_content           = mjschool_string_replacement( $SMSArr, $SMSCon );
									$type                      = 'Add Exam';
									mjschool_send_mjschool_notification( $student_id, $type, $message_content );
								}
								if ( isset( $_POST['mjschool_enable_exam_mjschool_parent'] ) && ( sanitize_text_field(wp_unslash($_POST['mjschool_enable_exam_mjschool_parent'])) === '1' ) ) {
									$parent = get_user_meta( $student_id, 'parent_id', true );
									if ( ! empty( $parent ) ) {
										foreach ( $parent as $p ) {
											$SMSArr                     = array();
											$SMSCon                     = get_option( 'mjschool_exam_parent_mjschool_content' );
											$SMSArr['{{student_name}}'] = $student_name;
											$SMSArr['{{exam_name}}']    = sanitize_textarea_field( stripslashes( $_POST['exam_name'] ) );
											$SMSArr['{{date}}']         = $start_end_date;
											$SMSArr['{{school_name}}']  = get_option( 'mjschool_name' );
											$message_content            = mjschool_string_replacement( $SMSArr, $SMSCon );
											$type                       = 'Add Exam';
											mjschool_send_mjschool_notification( $p, $type, $message_content );
										}
									}
								}
							}
						}
					}
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam&tab=examlist&_wpnonce='.esc_attr( $nonce ).'&message=1' ) );
					die();
				}
			}
		}
	}
}
// ------------- Save exam time table. -----------------//
if ( isset( $_POST['save_exam_table'] ) ) {
	$mjschool_obj_exam = new Mjschool_exam();
	$class_id          = sanitize_text_field(wp_unslash($_POST['class_id']));
	$section_id        = sanitize_text_field(wp_unslash($_POST['section_id']));
	$exam_id           = sanitize_text_field(wp_unslash($_POST['exam_id']));
	if ( isset( $_POST['section_id'] ) && sanitize_text_field(wp_unslash($_POST['section_id'])) != 0 ) {
		$subject_data = $mjschool_obj_exam->mjschool_get_subject_by_section_id( $class_id, $section_id );
	} else {
		$subject_data = $mjschool_obj_exam->mjschool_get_subject_by_class_id( $class_id );
	}
	if ( ! empty( $subject_data ) ) {
		foreach ( $subject_data as $subject ) {
			if ( isset( $_POST[ 'subject_name_' . $subject->subid ] ) ) {
				$save_data = $mjschool_obj_exam->mjschool_insert_sub_wise_time_table( $class_id, $exam_id, $subject->subid, sanitize_text_field(wp_unslash($_POST[ 'exam_date_' . $subject->subid ])), sanitize_text_field(wp_unslash($_POST[ 'start_time_' . $subject->subid ])), sanitize_text_field(wp_unslash($_POST[ 'end_time_' . $subject->subid ])) );
			}
		}
		if ( $save_data ) {
			$nonce = wp_create_nonce( 'mjschool_exam_module_tab' );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam&tab=exam_time_table&_wpnonce='.esc_attr( $nonce ).'&message=5' ) );
			die();
		}
	}
}
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="view_popup"></div>
		</div>
	</div>
</div>
<!-- End POP-UP code. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!------------ PANEL BODY ------------>
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Exam Added Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Exam Updated Successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Exam Deleted Successfully.', 'mjschool' );
			break;
		case '4':
			$message_string = esc_html__( 'This File Type Is Not Allowed, Please Upload Only Pdf File.', 'mjschool' );
			break;
		case '5':
			$message_string = esc_html__( 'Exam Time Table Saved Successfully .', 'mjschool' );
			break;
		case '6':
			$message_string = esc_html__( 'Enter Total Marks Greater than Passing Marks.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
			</button>
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	?>
	<!-------------- Tabbing start. --------------->
	<?php $nonce = wp_create_nonce( 'mjschool_exam_module_tab' ); ?>
	<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
		<li class="<?php if ( $active_tab === 'examlist' ) { ?> active<?php } ?>">
			<a href="?dashboard=mjschool_user&page=exam&tab=examlist&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'examlist' ? 'active' : ''; ?>">
				<?php esc_html_e( 'Exam List', 'mjschool' ); ?>
			</a>
		</li>
		<?php
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			?>
			<li class="<?php if ( $active_tab === 'addexam' ) { ?> active<?php } ?>">
				<a href="?dashboard=mjschool_user&page=exam&tab=addexam" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addexam' ? 'active' : ''; ?>">
					<?php esc_html_e( 'Edit Exam', 'mjschool' ); ?>
				</a>
			</li>
			<?php
		} elseif ( $active_tab === 'addexam' ) {
			?>
			<li class="<?php if ( $active_tab === 'addexam' ) { ?> active<?php } ?>">
				<a href="?dashboard=mjschool_user&page=exam&tab=addexam" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addexam' ? 'active' : ''; ?>">
					<?php esc_html_e( 'Add Exam', 'mjschool' ); ?>
				</a>
			</li>
			<?php
		}
		if ( $user_access['add'] === '1' ) {
			?>
			<li class="<?php if ( $active_tab === 'exam_time_table' ) { ?> active<?php } ?>">
				<a href="?dashboard=mjschool_user&page=exam&tab=exam_time_table&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'exam_time_table' ? 'active' : ''; ?>">
					<?php esc_html_e( 'Exam Time Table', 'mjschool' ); ?>
				</a>
			</li>
			<?php
		}
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'view' ) {
			?>
			<li class="<?php if ( $active_tab === 'view_exam_time_table' ) { ?> active<?php } ?>">
				<a href="?dashboard=mjschool_user&page=exam&tab=view_exam_time_table&action=view&exam_id=<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'view_exam_time_table' ? 'active' : ''; ?>">
					<?php esc_html_e( 'View Exam Time Table', 'mjschool' ); ?>
				</a>
			</li>
			<?php
		}
		?>
	</ul>
	<!-------------- Tabbing end. ----------------->
	<?php
	// --------------- Exam list tab start. ---------------//
	if ( $active_tab === 'examlist' ) {

		// Check nonce for examlist tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_exam_module_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		$user_id             = get_current_user_id();
		$mjschool_obj = new MJSchool_Management( $user_id );
		$obj_exam = new Mjschool_Exam();
		// ------- Exam data for student. ---------//
		if ( $mjschool_obj->role === 'student' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$class_id   = get_user_meta( get_current_user_id(), 'class_name', true );
				$section_id = get_user_meta( get_current_user_id(), 'class_section', true );
				if ( isset( $class_id ) && $section_id === '' ) {
					$retrieve_class_data = $obj_exam->mjschool_get_all_exam_by_class_id( $class_id );
				} else {
					$retrieve_class_data = mjschool_get_all_exam_by_class_id_and_section_id_array( $class_id, $section_id );
				}
			} else {
				$retrieve_class_data = mjschool_get_all_data( $tablename );
			}
		}
		// ------- Exam data for teacher.. ---------//
		elseif ( $mjschool_obj->role === 'teacher' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$class_id       = get_user_meta( get_current_user_id(), 'class_name', true );
				$retrieve_class_data = $mjschool_obj_exam->mjschool_get_all_exam_by_class_id_created_by( $class_id, $user_id );
			} else {
				$retrieve_class_data = mjschool_get_all_data( $tablename );
			}
		}
		// ------- Exam data for parent.. ---------//
		elseif ( $mjschool_obj->role === 'parent' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$user_meta = get_user_meta( $user_id, 'child', true );
				if ( ! empty( $user_meta ) ) {
					foreach ( $user_meta as $student_id ) {
						$result[] = mjschool_get_exam_data_for_parent( $student_id );
					}
					$mergedArray    = array_merge( ...$result );
					$retrieve_class_data = array_unique( $mergedArray, SORT_REGULAR );
				}
			} else {
				$retrieve_class_data = mjschool_get_all_data( $tablename );
			}
		}
		// ------- Exam data for support staff.. ---------//
		else {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$retrieve_class_data = $mjschool_obj_exam->mjschool_get_all_exam_created_by( $user_id );
			} else {
				$retrieve_class_data = mjschool_get_all_data( $tablename );
			}
		}
		if ( ! empty( $retrieve_class_data ) ) {
			?>
			<div class="mjschool-panel-body"><!---------- Panel body. -------------->
				<div class="table-responsive"><!--------------- Table responsive. ------------>
					<!--------------- Exam list form. --------------->
					<form name="wcwm_report" action="" method="post">
						<?php wp_nonce_field( 'save_exam_admin_nonce' ); ?>
						<table id="front_exam_list" class="display dataTable mjschool-exam-datatable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $mjschool_role_name === 'supportstaff' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
										<?php
									}
									?>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?> </th>
									<th><?php esc_html_e( 'Exam Name', 'mjschool' ); ?> </th>
									<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?> </th>
									<th><?php esc_html_e( 'Exam Term', 'mjschool' ); ?> </th>
									<th><?php esc_html_e( 'Exam Start Date', 'mjschool' ); ?> </th>
									<th><?php esc_html_e( 'Exam End Date', 'mjschool' ); ?> </th>
									<th><?php esc_html_e( 'Exam Comment', 'mjschool' ); ?> </th>
									<?php
									if ( ! empty( $user_custom_field ) ) {
										foreach ( $user_custom_field as $custom_field ) {
											if ( $custom_field->show_in_table === '1' ) {
												?>
												<th><?php echo esc_html( $custom_field->field_label ); ?> </th>
												<?php
											}
										}
									}
									?>
									<th class="mjschool-text-align-end"> <?php esc_html_e( 'Action', 'mjschool' ); ?> </th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 0;
								foreach ( $retrieve_class_data as $retrieved_data ) {
									$color_class_css = mjschool_table_list_background_color( $i );
									?>
									<tr>
										<?php
										if ( $mjschool_role_name === 'supportstaff' ) {
											?>
											<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>"></td>
											<?php
										}
										?>
										<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
											<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" type="Exam_view">
												<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png"); ?>" class="mjschool-massage-image">
												</p>
											</a>
										</td>
										<td>
											<a href="#" class="mjschool-color-black mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" type="Exam_view">
												<?php echo esc_html( $retrieved_data->exam_name ); ?>
											</a>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Name', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_id, $retrieved_data->section_id ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php
											if ( ! empty( $retrieved_data->exam_term ) ) {
												echo esc_html( get_the_title( $retrieved_data->exam_term ) );
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Term', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_start_date ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Date', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_end_date ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'End Date', 'mjschool' ); ?>"></i>
										</td>
										<?php
										$comment      = $retrieved_data->exam_comment;
										$exam_comment = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
										?>
										<td>
											<?php
											if ( $retrieved_data->exam_comment ) {
												echo esc_html( stripslashes( $exam_comment ) );
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_html( $comment ); } else { esc_html_e( 'Exam Comment', 'mjschool' ); } ?>"></i>
										</td>
										<?php
										// Custom Field Values.
										if ( ! empty( $user_custom_field ) ) {
											foreach ( $user_custom_field as $custom_field ) {
												if ( $custom_field->show_in_table === '1' ) {
													$module             = 'exam';
													$custom_field_id    = $custom_field->id;
													$module_record_id   = $retrieved_data->exam_id;
													$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
													if ( $custom_field->field_type === 'date' ) {
														?>
														<td>
															<?php
															if ( ! empty( $custom_field_value ) ) {
																echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</td>
														<?php
													} elseif ( $custom_field->field_type === 'file' ) {
														?>
														<td>
															<?php
															if ( ! empty( $custom_field_value ) ) {
																?>
																<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile">
																	<button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?> </button>
																</a>
																<?php
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</td>
														<?php
													} else {
														?>
														<td>
															<?php
															if ( ! empty( $custom_field_value ) ) {
																echo esc_html( $custom_field_value );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</td>
														<?php
													}
												}
											}
										}
										?>
										<td class="action">
											<div class="mjschool-user-dropdown">
												<ul  class="mjschool_ul_style">
													<?php
													if ( ! empty( $retrieved_data->exam_syllabus ) ) {
														$doc_data = json_decode( $retrieved_data->exam_syllabus );
													}
													?>
													<li >
														<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
														</a>
														<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
															<?php
															if ( $mjschool_obj->role === 'student' ) {
																$hallticket  = mjschool_hall_ticket_by_exam_id( get_current_user_id(), $retrieved_data->exam_id );
																$check_reult = mjschool_check_result( get_current_user_id(), $retrieved_data->exam_id );
																$count       = $check_reult[0]->{'COUNT(*)'};
																if ( ! empty( $hallticket ) ) {
																	if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
																		$pdf_name = get_current_user_id() . '_' . $retrieved_data->exam_id;
																		if ( isset( $_POST['download_app_pdf'] ) ) {
																			$file_path = esc_url(content_url( '/uploads/exam_receipt/' . $pdf_name . '.pdf'));
																			if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
																				unlink( $file_path ); // Delete the file.
																			}
																			$generate_pdf = mjschool_generate_exam_receipt_mobile_app( get_current_user_id(), $retrieved_data->exam_id, $pdf_name );
																			wp_safe_redirect( $file_path );
																			die();
																		}
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<form name="app_pdf" action="" method="post" class="mjschool-float-left-width-100px">
																				<button type="submit" name="download_app_pdf" class="mjschool-float-left-width-100px mjschool-hall-ticket-pdf-button">
																					<span class="mjschool-hall-ticket-pdf-button-span"><i class="fa fa-print mjschool-hall-ticket-pdf-icon"></i> <?php esc_html_e( 'Hall Ticket PDF', 'mjschool' ); ?> </span>
																				</button>
																			</form>
																		</li>
																		<?php
																	} else {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="?page=mjschool_student&student_exam_receipt_pdf=student_exam_receipt_pdf&student_id=<?php echo esc_attr( mjschool_encrypt_id( get_current_user_id() ) ); ?>&exam_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->exam_id ) ); ?>" target="_blank" class="mjschool-float-left-width-100px"><i class="fa fa-print"> </i> <?php esc_html_e( 'Hall Ticket PDF', 'mjschool' ); ?> </a>
																		</li>
																		<?php
																	}
																}
																if ( $count > 0 ) {
																	if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
																		$pdf_name  = get_current_user_id() . '_' . $retrieved_data->exam_id;
																		$file_path = esc_url(content_url( '/uploads/result/' . $pdf_name . '.pdf'));
																		if ( isset( $_POST['download_app_pdf'] ) ) {
																			$file_path = esc_url(content_url( '/uploads/result/' . $pdf_name . '.pdf'));
																			if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
																				unlink( $file_path ); // Delete the file.
																			}
																			$generate_pdf = mjschool_generate_result_for_mobile_app( get_current_user_id(), $retrieved_data->exam_id, $pdf_name );
																			wp_safe_redirect( $file_path );
																			die();
																		}
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<form name="app_pdf" action="" method="post" class="mjschool-float-left-width-100px">
																				<button type="submit" name="download_app_pdf" class="mjschool-float-left-width-100px mjschool-hall-ticket-pdf-button">
																					<span class="mjschool-hall-ticket-pdf-button-span"><i class="fa fa-print mjschool-hall-ticket-pdf-icon"></i> <?php esc_html_e( 'Result PDF', 'mjschool' ); ?> </span>
																				</button>
																			</form>
																		</li>
																		<?php
																	} else {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="?page=mjschool_student&print=pdf&student=<?php echo esc_attr( mjschool_encrypt_id( get_current_user_id() ) ); ?>&exam_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->exam_id ) ); ?>" target="_blank" class="mjschool-float-left-width-100px"><i class="fa fa-print"> </i> <?php esc_html_e( 'Result PDF', 'mjschool' ); ?> </a>
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="?page=mjschool_student&print=print&student=<?php echo esc_attr( mjschool_encrypt_id( get_current_user_id() ) ); ?>&exam_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->exam_id ) ); ?>" target="_blank" class="mjschool-float-left-width-100px"><i class="fa fa-print"> </i> <?php esc_html_e( 'Print Result', 'mjschool' ); ?> </a>
																		</li>
																		<?php
																	}
																}
															}
															?>
															<li class="mjschool-float-left-width-100px">
																<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" type="Exam_view"><i class="fas fa-eye" aria-hidden="true"></i> <?php esc_html_e( 'View Exam Detail', 'mjschool' ); ?> </a>
															</li>
															<li class="mjschool-float-left-width-100px">
																
																<a href="?dashboard=mjschool_user&page=exam&tab=view_exam_time_table&action=view&exam_id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->exam_id ) ); ?>" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-timetable-icon.png"); ?>" class="mjschool_height_15px">&nbsp;&nbsp;
																	<?php esc_html_e( 'Time Table Detail', 'mjschool' ); ?>
																</a>
																
															</li>
															<?php
															if ( ! empty( $doc_data[0]->value ) ) {
																?>
																<li class="mjschool-float-left-width-100px">
																	<a target="blank" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value )); ?>" class="mjschool-status-read mjschool-float-left-width-100px" record_id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>">
																		<i class="fa fa-eye"></i>
																		<?php esc_html_e( 'View Syllabus', 'mjschool' ); ?>
																	</a>
																</li>
																<?php
															}
															if ( $user_access['edit'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																	<a href="?dashboard=mjschool_user&page=exam&tab=addexam&action=edit&exam_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->exam_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px">
																		<i class="fa fa-edit"> </i>
																		<?php esc_html_e( 'Edit', 'mjschool' ); ?>
																	</a>
																</li>
																<?php
															}
															if ( $user_access['delete'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px">
																	<a href="?dashboard=mjschool_user&page=exam&tab=examlist&action=delete&exam_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->exam_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																		<i class="fas fa-trash"></i>
																		<?php esc_html_e( 'Delete', 'mjschool' ); ?>
																	</a>
																</li>
																<?php
															}
															?>
														</ul>
													</li>
												</ul>
											</div>
										</td>
									</tr>
									<?php
									++$i;
								}
								?>
							</tbody>
						</table>
						<?php
						if ( $mjschool_role_name === 'supportstaff' ) {
							?>
							<div class="mjschool-print-button pull-left">
								<button class="mjschool-btn-sms-color mjschool-button-reload">
									<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>" >
									<label for="select_all" class="mjschool-margin-right-5px"> <?php esc_html_e( 'Select All', 'mjschool' ); ?> </label>
								</button>
								<?php
								if ( $user_access['delete'] === '1' ) {
									 ?>
									<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</form><!--------------- Exam list form. --------------->
				</div><!--------------- Table responsive. ------------>
			</div><!---------- Panel body. -------------->
			<?php
		} else {
			if ($user_access['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=exam&tab=addexam') ); ?>">
						<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
					</a>
					<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
						<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
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
	}
	// --------------- Add exam tab start. ---------------//
	if ( $active_tab === 'addexam' ) {
		?>
		<!--Group POP-UP code. -->
		<div class="mjschool-popup-bg">
			<div class="mjschool-overlay-content mjschool-admission-popup">
				<div class="modal-content">
					<div class="mjschool-category-list">
					</div>
				</div>
			</div>
		</div>
		<?php
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$edit      = 1;
			$exam_data = mjschool_get_exam_by_id( mjschool_decrypt_id( $_REQUEST['exam_id'] ) );
		}
		?>
		<div class="mjschool-panel-body"><!------------ Panel body. ------------->
			<!------------ Exam add form. ------------->
			<form name="exam_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="exam_form_front">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<div class="header">
					<h3 class="mjschool-first-header"> <?php esc_html_e( 'Exam Information', 'mjschool' ); ?> </h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="exam_name" class="form-control validate[required,custom[popup_category_validation]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $exam_data->exam_name ); } ?>" name="exam_name">
									<label for="exam_name"> <?php esc_html_e( 'Exam Name', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<div class="col-md-6 input mjschool-error-msg-left-margin">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"> <?php esc_html_e( 'Class Name', 'mjschool' ); ?><span class="required">*</span> </label>
							<select name="class_id" class="mjschool-line-height-30px form-control validate[required] mjschool-width-100px" id="mjschool-class-list">
								<option value=""> <?php esc_html_e( 'Select Class', 'mjschool' ); ?> </option>
								<?php
								$classval = '';
								if ( $edit ) {
									$classval = $exam_data->class_id;
									foreach ( mjschool_get_all_class() as $class ) {
										?>
										<option value="<?php echo esc_attr( $class['class_id'] ); ?>" <?php selected( $class['class_id'], $classval ); ?>>
											<?php echo esc_html( mjschool_get_class_name( $class['class_id'] ) ); ?>
										</option>
										<?php
									}
								} else {
									foreach ( mjschool_get_all_class() as $classdata ) {
										?>
										<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $classval ); ?>>
											<?php echo esc_html( $classdata['class_name'] ); ?>
										</option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<?php if ( $school_type === 'school' ){ ?>
							<div class="col-md-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="class_section"> <?php esc_html_e( 'Section Name', 'mjschool' ); ?> </label>
								<?php
								if ( $edit ) {
									$sectionval = $exam_data->section_id;
								} elseif ( isset( $_POST['class_section'] ) ) {
									$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
								} else {
									$sectionval = '';
								}
								?>
								<select name="class_section" class="mjschool-line-height-30px form-control mjschool-width-100px" id="class_section">
									<option value=""> <?php esc_html_e( 'All Section', 'mjschool' ); ?> </option>
									<?php
									if ( $edit ) {
										foreach ( mjschool_get_class_sections( $exam_data->class_id ) as $sectiondata ) {
											?>
											<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>>
												<?php echo esc_html( $sectiondata->section_name ); ?>
											</option>
											<?php
										}
									}
									?>
								</select>
							</div>
						<?php }
						if ( $school_type === 'university' )
						{	?>
							<div id="university_subjects_container"></div>
							<?php
						}
						?>
						<div class="col-md-5 input mjschool-width-75">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-term"> <?php esc_html_e( 'Exam Term', 'mjschool' ); ?><span class="required">*</span> </label>
							<?php
							if ( $edit ) {
								$sectionval1 = $exam_data->exam_term;
							} elseif ( isset( $_POST['exam_term'] ) ) {
								$sectionval1 = sanitize_text_field(wp_unslash($_POST['exam_term']));
							} else {
								$sectionval1 = '';
							}
							?>
							<select id="mjschool-exam-term" class="mjschool-line-height-30px form-control validate[required] term_category mjschool-width-100px" name="exam_term">
								<option value=""> <?php esc_html_e( 'Select Term', 'mjschool' ); ?> </option>
								<?php
								$activity_category = mjschool_get_all_category( 'term_category' );
								if ( ! empty( $activity_category ) ) {
									foreach ( $activity_category as $retrive_data ) {
										?>
										<option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $sectionval1 ); ?>>
											<?php echo esc_html( $retrive_data->post_title ); ?>
										</option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<div class="col-md-1 col-sm-1 input mjschool-res-width-25">
							<input type="button" id="mjschool-addremove-cat" value="<?php esc_attr_e( 'ADD', 'mjschool' ); ?>" model="term_category" class="btn btn-success mjschool-save-btn mjschool-margin-top-0px-rtl" />
						</div>
						<?php 
						if ( $school_type === 'school' ) { ?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="mjschool_passing_mark" class="form-control text-input mjschool-onlyletter-number-space-validation validate[required]" type="number" value="<?php if ( $edit ) { echo esc_attr( $exam_data->passing_mark ); } ?>" name="passing_mark">
										<label for="mjschool_passing_mark"> <?php esc_html_e( 'Passing Marks', 'mjschool' ); ?><span class="required">*</span> </label>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group input mjschool-error-msg-left-margin">
									<div class="col-md-12 form-control">
										<input id="mjschool_total_mark" class="form-control validate[required] total_mark mjschool-onlyletter-number-space-validation text-input" type="number" value="<?php if ( $edit ) { echo esc_attr( $exam_data->total_mark ); } ?>" name="total_mark">
										<label for="mjschool_total_mark"> <?php esc_html_e( 'Total Marks', 'mjschool' ); ?><span class="required">*</span> </label>
									</div>
								</div>
							</div>
						<?php }?>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="exam_start_date" class="form-control date_picker validate[required] text-input" type="text" name="exam_start_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" readonly>
									<label for="exam_start_date" class="date_label"> <?php esc_html_e( 'Exam Start Date', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="exam_end_date" class="form-control date_picker validate[required] text-input" type="text" name="exam_end_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" readonly>
									<label for="exam_end_date" class="date_label"> <?php esc_html_e( 'Exam End Date', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<?php wp_nonce_field( 'save_exam_admin_nonce' ); ?>
						<div class="col-md-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea name="exam_comment" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150" id="exam_comment"><?php if ( $edit ) { echo esc_html( $exam_data->exam_comment ); } ?> </textarea>
										<span class="mjschool-txt-title-label"></span>
										<label for="exam_comment" class="text-area address active"><?php esc_html_e( 'Exam Comment', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
						<?php
						if ( $edit ) {
							$doc_data = json_decode( $exam_data->exam_syllabus );
							?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
										<span class="mjschool-custom-control-label  mjschool-custom-top-label ml-2 mjschool-margin-left-30px"> <?php esc_html_e( 'Exam Syllabus', 'mjschool' ); ?> </span>
										<div class="col-sm-12">
											<input type="file" name="exam_syllabus" class="form-control file form-control exam_syllebus mjschool-file-validation input-file" />
											<input type="hidden" name="old_hidden_exam_syllabus" value="<?php if ( ! empty( $doc_data[0]->value ) ) { echo esc_attr( $doc_data[0]->value ); } elseif ( isset( $_POST['exam_syllabus'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['exam_syllabus'])) ); } ?>">
										</div>
										<?php
										if ( ! empty( $doc_data[0]->value ) ) {
											?>
											<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
												<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value )); ?>" record_id="<?php echo esc_attr( $exam_data->exam_id ); ?>">
													<i class="fas fa-download"></i>
													<?php esc_html_e( 'Download', 'mjschool' ); ?>
												</a>
											</div>
											<?php
										}
										?>
									</div>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
										<span class="mjschool-custom-control-label  mjschool-custom-top-label ml-2 mjschool-margin-left-30px"> <?php esc_html_e( 'Exam Syllabus', 'mjschool' ); ?> </span>
										<div class="col-sm-12">
											<input type="file" name="exam_syllabus" class="form-control file col-md-2 col-sm-2 col-xs-12 exam_syllebus mjschool-file-validation input-file">
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						<?php 
						if ( $school_type === 'school' ) { ?>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-margin-15px-rtl rtl_margin_bottom_0px">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio mjschool-rtl-relative-position">
											<div>
												<label class="mjschool-custom-top-label mjschool-label-right-position" for="contributions_section_option"> <?php esc_html_e( 'Contributions for Class Score and Exam Score', 'mjschool' ); ?> </label>
												<input id="contributions_section_option" type="checkbox" class="contributions_section mjschool-check-box-input-margin" name="contributions_section_option" <?php if ( $edit ) { if ( $exam_data->contributions === 'yes' ) { echo 'checked'; } } ?> value="yes"/>&nbsp;
												<?php esc_html_e( 'Enable', 'mjschool' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php }?>
					</div>
				</div>
				<?php
				if ( $edit ) {
					if ( ! empty( $exam_data->contributions_data ) ) {
						?>
						<div id="cuntribution_div" class="<?php if ( $exam_data->contributions === 'yes' ) { ?> mjschool-cuntribution-div-block <?php } else { ?> mjschool-cuntribution-div-none <?php } ?>">
							<?php
							$contributions_data = json_decode( $exam_data->contributions_data );
							foreach ( $contributions_data as $key => $value ) {
								?>
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group input">
												<div class="col-md-12 form-control">
													<input id="contributions_label" class="form-control" maxlength="50" type="text" value="<?php echo esc_attr( $value->label ); ?>" name="contributions_label[]">
													<label for="contributions_label"> <?php esc_html_e( 'Contributions Label', 'mjschool' ); ?> </label>
												</div>
											</div>
										</div>
										<div class="col-md-5 col-10">
											<div class="form-group input mjschool-error-msg-left-margin">
												<div class="col-md-12 form-control">
													<input id="contributions_mark" class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="<?php echo esc_attr( $value->mark ); ?>" name="contributions_mark[]">
													<label for="contributions_mark"> <?php esc_html_e( 'Contributions Marks', 'mjschool' ); ?> </label>
												</div>
											</div>
										</div>
										<?php
										if ( $key === 0 ) {
											 ?>
											<div class="col-md-1 col-2 col-sm-3 col-xs-12">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_contributions()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
											</div>
											<?php
										} else {
											?>
											<div class="col-md-1 col-2 col-sm-3 col-xs-12">
												<input type="image" onclick="mjschool_delete_parent_elementConstribution(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-input-btn-height-width mjschool_float_right" >
											</div>
											<?php
										}
										?>
									</div>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					} else {
						?>
						<div id="cuntribution_div" class="<?php if ($exam_data->contributions === "yes") { ?> mjschool-cuntribution-div-block <?php } else { ?> mjschool-cuntribution-div-none <?php } ?>">
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="contributions_label" class="form-control" maxlength="50" type="text" value="" name="contributions_label[]">
												<label for="contributions_label"> <?php esc_html_e( 'Contributions Label', 'mjschool' ); ?> </label>
											</div>
										</div>
									</div>
									<div class="col-md-5 col-10">
										<div class="form-group input mjschool-error-msg-left-margin">
											<div class="col-md-12 form-control">
												<input class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="" name="contributions_mark[]">
												<span for="userinput1"> <?php esc_html_e( 'Contributions Marks', 'mjschool' ); ?> </span>
											</div>
										</div>
									</div>
									<div class="col-md-1 col-2 col-sm-3 col-xs-12">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_contributions()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
									</div>
								</div>
							</div>
						</div>
						<?php
					}
				} else {
					?>
					<div id="cuntribution_div" class="mjschool-cuntribution-div-none">
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="contributions_label" class="form-control" maxlength="50" type="text" value="" name="contributions_label[]">
											<label for="contributions_label"> <?php esc_html_e( 'Contributions Label', 'mjschool' ); ?> </label>
										</div>
									</div>
								</div>
								<div class="col-md-5 col-10">
									<div class="form-group input mjschool-error-msg-left-margin">
										<div class="col-md-12 form-control">
											<input id="contributions_mark" class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="" name="contributions_mark[]">
											<label for="contributions_mark"> <?php esc_html_e( 'Contributions Marks', 'mjschool' ); ?> </label>
										</div>
									</div>
								</div>
								<div class="col-md-1 col-2 col-sm-3 col-xs-12">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_contributions()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
								</div>
								
							</div>
						</div>
					</div>
					<?php
				}
				if ( ! $edit ) {
					?>
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-margin-15px-rtl">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div>
												<label class="mjschool-custom-top-label" for="mjschool_enable_exam_mail"> <?php esc_html_e( 'Send Mail To Parents & Students', 'mjschool' ); ?> </label>
												<input id="mjschool_enable_exam_mail" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_exam_mail" value="1" <?php echo checked( get_option( 'mjschool_enable_exam_mail' ), 'yes' ); ?> />
												<?php esc_html_e( 'Enable', 'mjschool' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-margin-15px-rtl">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div>
												<label class="mjschool-custom-top-label" for="mjschool_enable_exam_mjschool_student"> <?php esc_html_e( 'Send SMS To Students', 'mjschool' ); ?> </label>
												<input id="mjschool_enable_exam_mjschool_student" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_exam_mjschool_student" value="1" />
												<?php esc_html_e( 'Enable', 'mjschool' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-margin-15px-rtl">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div>
												<label class="mjschool-custom-top-label" for="mjschool_enable_exam_mjschool_parent"> <?php esc_html_e( 'Send SMS To Parents', 'mjschool' ); ?> </label>
												<input id="mjschool_enable_exam_mjschool_parent" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_exam_mjschool_parent" value="1" />
												<?php esc_html_e( 'Enable', 'mjschool' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				// --------- Get module-wise custom field data. --------------//
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'exam';
				$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6">
							<input type="submit" id="save_exam" value="<?php if ( $edit ) { esc_html_e( 'Save Exam', 'mjschool' ); } else { esc_html_e( 'Add Exam', 'mjschool' ); } ?>" name="save_exam" class="btn btn-success check_contribution_marks mjschool-save-btn" />
						</div>
					</div>
				</div>
				<div class="offset-sm-2 col-sm-8">
				</div>
			</form><!------------ Exam add form. ------------->
		</div> <!------------ Panel body. ------------->
		<?php
	}
	// --------------- View exam time table tab. ---------------//
	if ( $active_tab === 'view_exam_time_table' ) {

		// Check nonce for exam time table tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_exam_module_tab' ) ) {
			wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}

		if ( $_REQUEST['action'] === 'view' ) {
			$exam_data         = mjschool_get_exam_by_id( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) );
			$start_date        = $exam_data->exam_start_date;
			$end_date          = $exam_data->exam_end_date;
			$mjschool_obj_exam = new Mjschool_exam();
			$exam_time_table   = $mjschool_obj_exam->mjschool_get_exam_time_table_by_exam( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) );
		}
		?>
		<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res"> <!--------- Panel body. ----------->
			<div class="form-group">
				<div class="col-md-12">
					<div class="mjschool-exam-table-res mjschool-view-exam-timetable-div">
						<table class="mjschool-width-100px mjschool_examhall_border_1px_center">
							<thead>
								<tr>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium"> <?php esc_html_e( 'Exam', 'mjschool' ); ?> </th>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" > <?php esc_html_e( 'Class', 'mjschool' ); ?> </th>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" > <?php esc_html_e( 'Section', 'mjschool' ); ?> </th>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" > <?php esc_html_e( 'Term', 'mjschool' ); ?> </th>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" > <?php esc_html_e( 'Start Date', 'mjschool' ); ?> </th>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool-rtl-border-right-1px mjchool_receipt_table_head" > <?php esc_html_e( 'End Date', 'mjschool' ); ?> </th>
								</tr>
							</thead>
							<tfoot></tfoot>
							<tbody>
								<tr>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" > <?php echo esc_html( $exam_data->exam_name ); ?> </td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" > <?php echo esc_html( mjschool_get_class_name( $exam_data->class_id ) ); ?> </td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
										<?php
										if ( $exam_data->section_id != 0 ) {
											echo esc_html( mjschool_get_section_name( $exam_data->section_id ) );
										} else {
											esc_html_e( 'No Section', 'mjschool' );
										}
										?>
									</td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" > <?php echo esc_html( get_the_title( $exam_data->exam_term ) ); ?> </td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" > <?php echo esc_html( mjschool_get_date_in_input_box( $start_date ) ); ?> </td>
									<td class="mjschool-exam-hall-receipt-table-value mjschool-rtl-border-right-1px mjschool_border_right_1px" > <?php echo esc_html( mjschool_get_date_in_input_box( $end_date ) ); ?> </td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<?php
			$obj_subject = new Mjschool_Subject();
			if ( ! empty( $exam_time_table ) ) {
				?>
				<div class="col-md-12 mjschool-margin-top-40">
					<div class="mjschool-exam-table-res mjschool-view-exam-timetable-div">
						<table class="mjschool-width-100px mjschool_examhall_border_1px_center">
							<thead>
								<tr>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium_no_center"> <?php esc_html_e( 'Subject Code', 'mjschool' ); ?> </th>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium_no_border_top" > <?php esc_html_e( 'Subject Name', 'mjschool' ); ?> </th>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium_no_border_top" > <?php esc_html_e( 'Exam Date', 'mjschool' ); ?> </th>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium_no_border_top"> <?php esc_html_e( 'Exam Start Time', 'mjschool' ); ?> </th>
									<th class="mjschool-exam-hall-receipt-table-heading mjschool-rtl-border-right-1px mjschool_border_1px_white" > <?php esc_html_e( 'Exam End Time', 'mjschool' ); ?> </th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ( ! empty( $exam_time_table ) ) {
									foreach ( $exam_time_table as $retrieved_data ) {
										?>
										<tr class="mjschool_border_1px_white" >
											<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" > <?php echo esc_html( $obj_subject->mjschool_get_single_subject_code( $retrieved_data->subject_id ) ); ?> </td>
											<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" > <?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject_id ) ); ?> </td>
											<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" > <?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_date ) ); ?> </td>
											<?php
											$start_time_data = explode( ':', $retrieved_data->start_time );
											$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
											$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
											$start_am_pm     = $start_time_data[2];
											$start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
											$end_time_data   = explode( ':', $retrieved_data->end_time );
											$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
											$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
											$end_am_pm       = $end_time_data[2];
											$end_time        = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
											?>
											<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" > <?php echo esc_html( $start_time ); ?> </td>
											<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" > <?php echo esc_html( $end_time ); ?> </td>
										</tr>
										<?php
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				<?php
			} else {
				?>
				<div id="mjschool-message" class="mjschool-message_class mjschool-rtl-message-display-inline-block mjschool-alert-msg alert alert-success alert-dismissible mjschool_margin_20px" role="alert">
					
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
					
					<?php esc_html_e( 'No Any Time Table', 'mjschool' ); ?>
				</div>
				<?php
			}
			?>
		</div><!--------- Panel body. ----------->
		<?php
	}
	// --------------- exam time table tab. ---------------//
	if ( $active_tab === 'exam_time_table' ) {
		?>
		<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res"><!-----  Panel body. ------->
			<!----------- Exam time table form. ---------->
			<form name="exam_form" action="" method="post" class="mb-3 mjschool-form-horizontal" enctype="multipart/form-data" id="exam_time_table">
				<div class="form-body mjschool-user-form mjschool-padding-top-25px-res">
					<div class="row">
						<div class="col-md-9 input mjschool-exam-time-table-error-msg">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="required">*</span></label>
							<?php
							$own_data            = $user_access['own_data'];
							$user_id             = get_current_user_id();
							$mjschool_obj = new MJSchool_Management( $user_id );
							if ( $own_data === '1' ) {
								if ( $mjschool_obj->role === 'teacher' ) {
									$class_id       = get_user_meta( get_current_user_id(), 'class_name', true );
									$retrieve_class_data = $mjschool_obj_exam->mjschool_get_all_exam_by_class_id_created_by( $class_id, $user_id );
								} else {
									$retrieve_class_data = $mjschool_obj_exam->mjschool_get_all_exam_created_by( $user_id );
								}	
							} else {
								$tablename      = 'mjschool_exam';
								$retrieve_class_data = mjschool_get_all_data( $tablename );
							}
							$exam_id = '';
							if ( isset( $_REQUEST['exam_id'] ) ) {
								$exam_id = sanitize_text_field(wp_unslash($_REQUEST['exam_id']));
							}
							?>
							<select id="mjschool-exam-id" name="exam_id" class="mjschool-line-height-30px form-control validate[required] mjschool-width-100px">
								<option value=""><?php esc_html_e( 'Select Exam Name', 'mjschool' ); ?></option>
								<?php
								foreach ( $retrieve_class_data as $retrieved_data ) {
									$cid      = $retrieved_data->class_id;
									$clasname = mjschool_get_class_name( $cid );
									if ( $retrieved_data->section_id != 0 ) {
										$section_name = mjschool_get_section_name( $retrieved_data->section_id );
									} else {
										$section_name = esc_html__( 'No Section', 'mjschool' );
									}
									?>
									<option value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" <?php selected( $retrieved_data->exam_id, $exam_id ); ?>>
										<?php echo esc_html( $retrieved_data->exam_name ) . '( ' . esc_html( mjschool_get_class_section_name_wise( $cid, $retrieved_data->section_id ) ) . ' )'; ?>
									</option>
									<?php
								}
								?>
							</select>
						</div>
						<div class="col-md-3 col-sm-3 col-xs-12">
							<input type="submit" id="save_exam_time_table" value="<?php esc_attr_e( 'Manage Exam Time', 'mjschool' ); ?>" name="save_exam_time_table" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form><!----------- Exam time table form. ---------->
			<?php
			if ( isset( $_POST['save_exam_time_table'] ) ) {
				$exam_data    = mjschool_get_exam_by_id( sanitize_text_field(wp_unslash($_POST['exam_id'])) );
				$mjschool_obj = new MJSchool_Management();
				if ( $exam_data->section_id != 0 ) {
					$subject_data = $mjschool_obj->mjschool_subject_list_with_calss_and_section( $exam_data->class_id, $exam_data->section_id );
				} else {
					$subject_data = $mjschool_obj->mjschool_subject_list( $exam_data->class_id );
					if ( $school_type === 'university' )
					{
						// Step 2: Decode the exam subject_data JSON field.
						$exam_subjects = json_decode($exam_data->subject_data);
						//Get subject_ids from exam data.
						$exam_subject_ids = array_column($exam_subjects, 'subject_id' );
						// Filter only matching subjects.
						$subject_data = array_filter($subject_data, function($subject) use ($exam_subject_ids) {
							return in_array((int)$subject->subid, $exam_subject_ids);
						});
					}
				}
				$start_date = $exam_data->exam_start_date;
				$end_date   = $exam_data->exam_end_date;
				?>
				<input type="hidden" id="start_date" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( $start_date ) ) ); ?>">
				<input type="hidden" id="end_date" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( $end_date ) ) ); ?>">
				<div class="form-group"><!-------- Form Body. -------->
					<div class="col-md-12">
						<div class="mjschool-exam-table-res">
							<table class="table mjschool_examhall_border_1px_center" >
								<thead>
									<tr>
										<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium" ><?php esc_html_e( 'Exam', 'mjschool' ); ?></th>
										<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
										<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Section', 'mjschool' ); ?></th>
										<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Term', 'mjschool' ); ?></th>
										<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Start Date', 'mjschool' ); ?></th>
										<th class="mjschool-exam-hall-receipt-table-heading mjchool_receipt_table_head" ><?php esc_html_e( 'End Date', 'mjschool' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( $exam_data->exam_name ); ?></td>
										<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_class_name( $exam_data->class_id ) ); ?></td>
										<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
											<?php
											if ( $exam_data->section_id != 0 ) {
												echo esc_html( mjschool_get_section_name( $exam_data->section_id ) );
											} else {
												esc_html_e( 'No Section', 'mjschool' );
											}
											?>
										</td>
										<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( get_the_title( $exam_data->exam_term ) ); ?></td>
										<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_date_in_input_box( $start_date ) ); ?></td>
										<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_date_in_input_box( $end_date ) ); ?></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div><!-------- Form Body. -------->
				<?php
				if ( isset( $subject_data ) ) {
					$mjschool_obj_exam = new Mjschool_exam();
					foreach ( $subject_data as $retrieved_data ) {
						$exam_time_table_data = $mjschool_obj_exam->mjschool_check_exam_time_table( $exam_data->class_id, $exam_data->exam_id, $retrieved_data->subid );
					}
					if ( ! empty( $subject_data ) ) {
						?>
						<div class="col-md-12 mjschool-margin-top-40">
							<div class="mjschool-exam-table-res">
								<form id="exam_form2" name="exam_form2" method="post"> <!-------- Exam Form -------->
									<input type='hidden' name='subject_data' id="subject_data" value='<?php echo json_encode( $subject_data ); ?>'>
									<input type="hidden" name="class_id" value="<?php echo esc_attr( $exam_data->class_id ); ?>">
									<input type="hidden" name="section_id" value="<?php echo esc_attr( $exam_data->section_id ); ?>">
									<input type="hidden" name="exam_id" value="<?php echo esc_attr( $exam_data->exam_id ); ?>">
									<div class="mjschool-exam-time-table-main-div">
										<table class="exam_timelist_admin mjschool-width-100px mjschool_examhall_border_1px_center">
											<thead>
												<tr>
													<th class="exam_hall_receipt_add_table_heading mjschool_examhall_heading_medium" ><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
													<th class="exam_hall_receipt_add_table_heading mjschool_library_table" ><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></th>
													<th class="exam_hall_receipt_add_table_heading mjschool_library_table" ><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></th>
													<th class="exam_hall_receipt_add_table_heading mjschool_library_table" ><?php esc_html_e( 'Exam Start Time', 'mjschool' ); ?></th>
													<th class="exam_hall_receipt_add_table_heading mjschool-rtl-border-right-1px mjchool_receipt_table_head" ><?php esc_html_e( 'Exam End Time', 'mjschool' ); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php
												$mjschool_obj_exam = new Mjschool_exam();
												$i                 = 1;
												foreach ( $subject_data as $retrieved_data ) {
													// ------- View exam time table data. ------------//
													$exam_time_table_data = $mjschool_obj_exam->mjschool_check_exam_time_table( $exam_data->class_id, $exam_data->exam_id, $retrieved_data->subid );
													?>
													<tr class="mjschool_border_1px_white">
														<input type="hidden" name="subject_id" value="<?php echo esc_attr( $retrieved_data->subid ); ?>">
														<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
															<input type="hidden" name="subject_code_<?php echo esc_attr( $retrieved_data->subid ); ?>" value="<?php echo esc_attr( $retrieved_data->subject_code ); ?>"><?php echo esc_attr( $retrieved_data->subject_code ); ?>
														</td>
														<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
															<input type="hidden" name="subject_name_<?php echo esc_attr( $retrieved_data->subid ); ?>" value="<?php echo esc_attr( $retrieved_data->sub_name ); ?>"><?php echo esc_attr( $retrieved_data->sub_name ); ?>
														</td>
														<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
															<input id="exam_date_<?php echo esc_attr( $retrieved_data->subid ); ?>" class="datepicker form-control datepicker_icon validate[required] text-input front_exam_date mjschool-min-width-160 " placeholder="<?php esc_html_e( 'Select Date', 'mjschool' ); ?>" type="text" name="exam_date_<?php echo esc_attr( $retrieved_data->subid ); ?>" value="<?php if ( ! empty( $exam_time_table_data->exam_date ) ) { echo esc_attr( mjschool_get_date_in_input_box( $exam_time_table_data->exam_date ) ); } ?>" readonly>
														</td>
														<?php
														if ( ! empty( $exam_time_table_data->start_time ) ) {
															// ------------ Start time convert. --------------//
															$stime       = explode( ':', $exam_time_table_data->start_time );
															$start_hour  = $stime[0];
															$start_min   = $stime[1];
															$shours      = str_pad( $start_hour, 2, '0', STR_PAD_LEFT );
															$smin        = str_pad( $start_min, 2, '0', STR_PAD_LEFT );
															$start_am_pm = $stime[2];
															$start_time  = $shours . ':' . $smin . ':' . $start_am_pm;
														}
														if ( ! empty( $exam_time_table_data->end_time ) ) {
															// -------------------- End time convert. -----------------//
															$etime     = explode( ':', $exam_time_table_data->end_time );
															$end_hour  = $etime[0];
															$end_min   = $etime[1];
															$ehours    = str_pad( $end_hour, 2, '0', STR_PAD_LEFT );
															$emin      = str_pad( $end_min, 2, '0', STR_PAD_LEFT );
															$end_am_pm = $etime[2];
															$end_time  = $ehours . ':' . $emin . ':' . $end_am_pm;
														}
														?>
														<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
															<input type="text" name="start_time_<?php echo esc_attr( $retrieved_data->subid ); ?>" class="form-control mjschool_timepicker text-input start_time_<?php echo esc_attr( $retrieved_data->subid ); ?>" placeholder="<?php esc_html_e( 'Start Time', 'mjschool' ); ?>" value="<?php if ( ! empty( $exam_time_table_data->start_time ) ) { echo esc_attr( $start_time ); } ?>" />
														</td>
														<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" >
															<input type="text" name="end_time_<?php echo esc_attr( $retrieved_data->subid ); ?>" class="form-control mjschool_timepicker text-input end_time_<?php echo esc_attr( $retrieved_data->subid ); ?> " placeholder="<?php esc_html_e( 'End Time', 'mjschool' ); ?>" value="<?php if ( ! empty( $exam_time_table_data->end_time ) ) { echo esc_attr( $end_time ); } ?>" />
														</td>
													</tr>
													<?php
													++$i;
												}
												?>
											</tbody>
										</table>
									</div>
									<?php
									if ( ! empty( $subject_data ) ) {
										?>
										<div class="col-md-3 mjschool-margin-top-20px mjschool-padding-top-25px-res mjschool-rtl-custom-padding-0px">
											<input type="submit" id="save_exam_time" value="<?php esc_attr_e( 'Save Time Table', 'mjschool' ); ?>" name="save_exam_table" class="btn btn-success mjschool-save-btn" />
										</div>
										<?php
									}
									?>
								</form><!-------- Exam Form. -------->
							</div>
						</div>
						<?php
					} else {
						?>
						<div id="mjschool-message" class="mjschool-message_class mjschool-rtl-message-display-inline-block mjschool-alert-msg alert alert-success alert-dismissible mjschool_margin_20px" role="alert">
							
							<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
							
							<?php esc_html_e( 'No Any Subject', 'mjschool' ); ?>
						</div>
						<?php
					}
				}
			}
			?>
		</div><!-------------  Panel body. ----------------->
		<?php
	}
	?>
</div>