<?php
/**
 * Admin Exam Management Interface.
 *
 * This file manages the backend functionality for adding, editing, viewing, and deleting exams 
 * within the Mjschool plugin. It provides role-based access control, secure form handling, 
 * and dynamic user interface components for managing exams effectively.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/exam
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type=get_option( 'mjschool_custom_class' );
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'exam' );
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
			if ( 'exam' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'exam' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'exam' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'exam';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
$tablename = 'mjschool_exam';
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'delete' ) {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce']) ), 'delete_action' ) ) {
		$result = mjschool_delete_exam( $tablename, mjschool_decrypt_id( sanitize_text_field( wp_unslash($_REQUEST['exam_id']) ) ) );
		if ( $result ) {
			$nonce = wp_create_nonce( 'mjschool_exam_module_tab' );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_exam&tab=examlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $ids as $id ) {
			$result = mjschool_delete_exam( $tablename, $id );
		}
	}
	$nonce = wp_create_nonce( 'mjschool_exam_module_tab' );
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_exam&tab=examlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=3' ) );
		die();
	}
}
// -----------SAVE EXAM. -------------------------//
if ( isset( $_POST['save_exam'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	$custribution_data = '';
	$custributions     = isset( $_POST['contributions_section_option'] ) ? sanitize_text_field( wp_unslash($_POST['contributions_section_option']) ) : '';
	if ( isset( $_POST['contributions_section_option'] ) && ( sanitize_text_field( wp_unslash($_POST['contributions_section_option']) ) === 'yes' ) ) {
		$custribution_data = mjschool_get_costribution_data_jason( wp_unslash($_POST) );
	}
	$subject_data_array = [];
    if ( isset( $_POST['university_subjects']) && is_array($_POST['university_subjects'] ) ) {
        foreach ($_POST['university_subjects'] as $subid => $info) {
            $enabled = !empty($info['enabled']);
			if ( $enabled === 'yes' )
			{
				$subject_data_array[] = [
					'subject_id'     => intval($subid),
					'max_marks'      => isset($info['total_mark']) ? sanitize_text_field($info['total_mark']) : '',
					'passing_marks'  => isset($info['passing_mark']) ? sanitize_text_field($info['passing_mark']) : '',
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
			'exam_name'          => sanitize_text_field( wp_unslash( $_POST['exam_name'] ) ),
			'class_id'           => sanitize_text_field( wp_unslash($_POST['class_id']) ),
			'section_id'         => isset( $_POST['class_section'] ) ? sanitize_text_field( wp_unslash($_POST['class_section']) ) : '',
			'exam_term'          => sanitize_text_field( wp_unslash($_POST['exam_term']) ),
			'passing_mark'       => isset( $_POST['passing_mark'] ) ? sanitize_text_field( wp_unslash($_POST['passing_mark']) ) : '',
			'total_mark'         => isset( $_POST['total_mark'] ) ? sanitize_text_field( wp_unslash($_POST['total_mark']) ) : '',
			'exam_start_date'    => date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash($_POST['exam_start_date']) ) ) ),
			'exam_end_date'      => date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash($_POST['exam_end_date'] ) ) ) ),
			'exam_comment'       => sanitize_textarea_field( wp_unslash( $_POST['exam_comment'] ) ),
			'exam_creater_id'    => get_current_user_id(),
			'contributions'      => $custributions,
			'subject_data'		 => $subject_data_json,
			'contributions_data' => $custribution_data,
			'created_date'       => $created_date,
		);
		$passing_mark = isset( $_POST['passing_mark'] ) ? intval( $_POST['passing_mark'] ) : 0;
		$total_mark = isset( $_POST['total_mark'] ) ? intval( $_POST['total_mark'] ) : 0;
		if ( $passing_mark >= $total_mark && $school_type === 'school' ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_exam&tab=examlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=6' ) );
			die();
		} else {
			$tablename = 'mjschool_exam';
			if ( isset( $_REQUEST['action'] ) && 'edit' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) {
				if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'edit_action' ) ) {
					$exam = sanitize_text_field( wp_unslash( $_REQUEST['exam_name'] ) );
					if ( isset( $_FILES['exam_syllabus'] ) && ! empty( $_FILES['exam_syllabus'] ) && $_FILES['exam_syllabus']['size'] != 0 ) {
						if ( $_FILES['exam_syllabus']['size'] > 0 ) {
							$upload_docs1 = mjschool_load_documets_new( $_FILES['exam_syllabus'], $_FILES['exam_syllabus'], sanitize_text_field( wp_unslash($_POST['document_name']) ) );
						}
					} elseif ( isset( $_REQUEST['old_hidden_exam_syllabus'] ) ) {
						$upload_docs1 = sanitize_text_field( wp_unslash($_REQUEST['old_hidden_exam_syllabus']) );
					}
					$document_data = array();
					if ( ! empty( $upload_docs1 ) ) {
						$document_data[] = array(
							'title' => isset( $_POST['document_name'] ) ? sanitize_text_field( wp_unslash($_POST['document_name']) ) : '',
							'value' => $upload_docs1,
						);
					} else {
						$document_data[] = '';
					}
					$exam_id                   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash($_REQUEST['exam_id']) ) ) );
					$grade_id                  = array( 'exam_id' => intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash($_REQUEST['exam_id']) ) ) ) );
					$modified_date_date        = date( 'Y-m-d H:i:s' );
					$examdata['modified_date'] = $modified_date_date;
					$examdata['exam_syllabus'] = wp_json_encode( $document_data );
					$result                    = mjschool_update_record( $tablename, $examdata, $grade_id );
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'exam';
					$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $exam_id );
					$exam                      = $examdata['exam_name'];
					mjschool_append_audit_log( '' . esc_html__( 'Exam Updated', 'mjschool' ) . '( ' . $exam . ' )' . '', mjschool_decrypt_id( sanitize_text_field( wp_unslash($_REQUEST['exam_id']) ) ), get_current_user_id(), 'edit', sanitize_text_field( wp_unslash($_REQUEST['page']) ) );
					if ( $result ) {
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_exam&tab=examlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=2' ) );
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else {
				if ( isset( $_FILES['exam_syllabus'] ) && ! empty( $_FILES['exam_syllabus'] ) && $_FILES['exam_syllabus']['size'] != 0 ) {
					if ( $_FILES['exam_syllabus']['size'] > 0 ) {
						$upload_docs1 = mjschool_load_documets_new( $_FILES['exam_syllabus'], $_FILES['exam_syllabus'], isset( $_POST['document_name'] ) ? sanitize_text_field( wp_unslash($_POST['document_name']) ) : '' );
					}
				} else {
					$upload_docs1 = '';
				}
				$document_data = array();
				if ( ! empty( $upload_docs1 ) ) {
					$document_data[] = array(
						'title' => isset( $_POST['document_name'] ) ? sanitize_text_field( wp_unslash($_POST['document_name']) ) : '',
						'value' => $upload_docs1,
					);
				} else {
					$document_data[] = '';
				}
				$examdata['exam_syllabus'] = wp_json_encode( $document_data );
				global $wpdb;
				$result         = mjschool_insert_record( $tablename, $examdata );
				$last_insert_id = $wpdb->insert_id;
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'exam';
				$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $last_insert_id );
				$exam_name                 = $examdata['exam_name'];
				mjschool_append_audit_log( '' . esc_html__( 'Exam Added', 'mjschool' ) . '( ' . $exam_name . ' )' . '', $result, get_current_user_id(), 'insert', sanitize_text_field( wp_unslash($_REQUEST['page']) ) );
				if ( $result ) {
					$class_section = isset( $_POST['class_section'] ) ? sanitize_text_field( wp_unslash($_POST['class_section']) ) : '';
					if ( empty( $class_section ) ) {
						$class_id = sanitize_text_field( wp_unslash($_POST['class_id']) );
						$studentdata = mjschool_get_student_name_with_class($class_id);
					} else {
						$studentdata = mjschool_get_student_name_with_class_and_section(sanitize_text_field( wp_unslash($_POST['class_id']) ), $class_section );
					}
					
					if ( ! empty( $studentdata ) ) {
						foreach ( $studentdata as $userdata ) {
							$student_id   = $userdata->ID;
							$student_name = $userdata->display_name;
							if ( isset( $_POST['mjschool_enable_exam_mail'] ) && ( sanitize_text_field( wp_unslash($_POST['mjschool_enable_exam_mail']) ) === '1' ) ) {
								$student_email                 = $userdata->user_email;
								$mjschool_add_exam_mailcontent = get_option( 'mjschool_add_exam_mailcontent' );
								$mjschool_add_exam_mail_title  = get_option( 'mjschool_add_exam_mail_title' );
								$parent                        = get_user_meta( $student_id, 'parent_id', true );
								$exam_start_date_san = sanitize_text_field( wp_unslash($_POST['exam_start_date']) );
								$exam_end_date_san = sanitize_text_field( wp_unslash($_POST['exam_end_date']) );
								if ( $exam_start_date_san === $exam_end_date_san ) {
									$start_end_date = mjschool_get_date_in_input_box( $exam_start_date_san );
								} else {
									$start_end_date = mjschool_get_date_in_input_box( $exam_start_date_san ) . ' ' . esc_html__( 'TO', 'mjschool' ) . ' ' . mjschool_get_date_in_input_box( $exam_end_date_san );
								}
								if ( ! empty( $parent ) ) {
									foreach ( $parent as $p ) {
										$user_info                            = get_userdata( $p );
										$email_to                             = $user_info->user_email;
										$searchArr                            = array();
										$parerntdata                          = get_user_by( 'email', $email_to );
										$searchArr['{{user_name}}']           = $parerntdata->display_name;
										$searchArr['{{exam_name}}']           = sanitize_textarea_field( wp_unslash( $_POST['exam_name'] ) );
										$searchArr['{{exam_start_end_date}}'] = $start_end_date;
										if ( ! empty( $_POST['exam_comment'] ) ) {
											$comment = sanitize_textarea_field( wp_unslash( $_POST['exam_comment']) );
										} else {
											$comment = 'N/A';
										}
										$searchArr['{{exam_comment}}'] = $comment;
										$searchArr['{{school_name}}']  = get_option( 'mjschool_name' );
										$message                       = mjschool_string_replacement( $searchArr, $mjschool_add_exam_mailcontent );
										if ( ! empty( $document_data[0] ) ) {
											$attechment = WP_CONTENT_DIR . '/uploads/school_assets/' . $document_data[0]['value'];
										} else {
											$attechment = '';
										}
										$mail = mjschool_send_mail_for_homework( $email_to, $mjschool_add_exam_mail_title, $message, $attechment );
									}
								}
								$string                            = array();
								$string['{{user_name}}']           = $student_name;
								$string['{{exam_name}}']           = sanitize_textarea_field( wp_unslash( $_POST['exam_name'] ) );
								$string['{{exam_start_end_date}}'] = $start_end_date;
								if ( ! empty( $_POST['exam_comment'] ) ) {
									$comment = sanitize_textarea_field( wp_unslash( $_POST['exam_comment'] ) );
								} else {
									$comment = 'N/A';
								}
								$string['{{exam_comment}}'] = $comment;
								$string['{{school_name}}']  = get_option( 'mjschool_name' );
								$message                    = mjschool_string_replacement( $string, $mjschool_add_exam_mailcontent );
								if ( ! empty( $document_data[0] ) ) {
									$attechment = WP_CONTENT_DIR . '/uploads/school_assets/' . $document_data[0]['value'];
								} else {
									$attechment = '';
								}
								$mail = mjschool_send_mail_for_homework( $student_email, $mjschool_add_exam_mail_title, $message, $attechment );
							}
							if ( isset( $_POST['smgt_enable_exam_mjschool_student'] ) && ( intval( $_POST['smgt_enable_exam_mjschool_student'] ) === 1 ) ) {
								$SMSArr                    = array();
								$SMSCon                    = get_option( 'mjschool_exam_student_mjschool_content' );
								$SMSArr['{{exam_name}}']   = sanitize_textarea_field( wp_unslash( $_POST['exam_name'] ) );
								$SMSArr['{{date}}']        = $start_end_date;
								$SMSArr['{{school_name}}'] = get_option( 'mjschool_name' );
								$message_content           = mjschool_string_replacement( $SMSArr, $SMSCon );
								$type                      = 'Add Exam';
								mjschool_send_mjschool_notification( $student_id, $type, $message_content );
							}
							if ( isset( $_POST['mjschool_enable_exam_mjschool_parent'] ) && ( sanitize_text_field( wp_unslash($_POST['mjschool_enable_exam_mjschool_parent']) ) === '1' ) ) {
								$parent = get_user_meta( $student_id, 'parent_id', true );
								if ( ! empty( $parent ) ) {
									foreach ( $parent as $p ) {
										$SMSArr                     = array();
										$SMSCon                     = get_option( 'mjschool_exam_parent_mjschool_content' );
										$SMSArr['{{student_name}}'] = $student_name;
										$SMSArr['{{exam_name}}']    = sanitize_textarea_field( wp_unslash( $_POST['exam_name'] ) );
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
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_exam&tab=examlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=1') );
					die();
				}
			}
		}
	}
}
// save Exam Time Table.
if ( isset( $_POST['save_exam_table'] ) ) {
	$mjschool_obj_exam = new Mjschool_exam();
	$class_id          = sanitize_text_field( wp_unslash($_POST['class_id']) );
	$section_id        = sanitize_text_field( wp_unslash($_POST['section_id']) );
	$exam_id           = sanitize_text_field( wp_unslash($_POST['exam_id']) );
	if ( isset( $_POST['section_id'] ) && intval( $_POST['section_id'] ) != 0 ) {
		$subject_data = $mjschool_obj_exam->mjschool_get_subject_by_section_id( $class_id, $section_id );
	} else {
		$subject_data = $mjschool_obj_exam->mjschool_get_subject_by_class_id( $class_id );
	}
	$nonce = wp_create_nonce( 'mjschool_exam_module_tab' );
	if ( ! empty( $subject_data ) ) {
		foreach ( $subject_data as $subject ) {
			if ( isset( $_POST[ 'subject_name_' . $subject->subid ] ) ) {
				$save_data = $mjschool_obj_exam->mjschool_insert_sub_wise_time_table( $class_id, $exam_id, $subject->subid, sanitize_text_field( wp_unslash($_POST[ 'exam_date_' . $subject->subid ]) ), sanitize_text_field( wp_unslash($_POST[ 'start_time_' . $subject->subid ]) ), sanitize_text_field( wp_unslash($_POST[ 'end_time_' . $subject->subid ]) ) );
			}
		}
		if ( $save_data ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_exam&tab=exam_time_table&_wpnonce=' . rawurlencode( $nonce ) . '&message=5' ) );
			die();
		}
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'examlist';
?>
<div class="mjschool-page-inner">
	<div class="mjschool_grade_page mjschool-main-list-margin-5px">
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
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
				$message_string = esc_html__( 'Exam Time Table Saved Successfully.', 'mjschool' );
				break;
			case '6':
				$message_string = esc_html__( 'Enter Total Marks Greater than Passing Marks.', 'mjschool' );
				break;
		}
		if ( $message ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class mjschool-rtl-message-display-inline-block alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
		<?php } ?>
		<div class="mjschool-panel-white">
			<div class="mjschool-panel-body">
				<?php $nonce = wp_create_nonce( 'mjschool_exam_module_tab' ); ?>
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
					<li class="<?php if ( $active_tab === 'examlist' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=examlist&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'examlist' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Exam List', 'mjschool' ); ?>
						</a>
					</li>
					<?php
					$mjschool_action = '';
					if ( ! empty( $_REQUEST['action'] ) ) {
						$mjschool_action = sanitize_text_field( wp_unslash($_REQUEST['action']) );
					}
					if ( $active_tab === 'addexam' ) {
						if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'edit' ) {
							?>
							<li class="<?php if ( $active_tab === 'addexam' || $mjschool_action === 'edit' ) { ?> active<?php } ?>">
								<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addexam' ? 'nav-tab-active' : ''; ?>">
									<?php esc_html_e( 'Edit Exam', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						} else {
							?>
							<li class="<?php if ( $active_tab === 'addexam' ) { ?> active<?php } ?>">
								<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addexam' ? 'nav-tab-active' : ''; ?>">
									<?php esc_html_e( 'Add Exam', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
					}
					?>
					<li class="<?php if ( $active_tab === 'exam_time_table' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=exam_time_table&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'exam_time_table' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Exam Time Table', 'mjschool' ); ?>
						</a>
					</li>
					<?php
					if ( $mjschool_action === 'view' ) {
						?>
						<li class="<?php if ( $active_tab === 'viewexam' ) { ?> active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=viewexam&action=view&exam_id=' . rawurlencode( sanitize_text_field( wp_unslash($_REQUEST['exam_id']) ) ) . '&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'viewexam' ? 'active' : ''; ?>">
								<?php esc_html_e( 'View Exam Time Table', 'mjschool' ); ?>
							</a>
						</li>
						<?php
					}
					?>
				</ul>
				<?php
				if ( $active_tab === 'examlist' ) {
					if ( isset( $_GET['tab'] ) ) {
						if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_exam_module_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}
					?>
					<div class="mjschool-popup-bg">
						<div class="mjschool-overlay-content">
							<div class="modal-content">
								<div class="view_popup"></div>
								<div class="mjschool-category-list"></div>
							</div>
						</div>
					</div>
					<?php
					if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
						?>
						<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url( 'https://www.youtube.com/embed/AqXYwh_8o04?si=w1NY42aZWl8eOvtd' ); ?>" title="<?php esc_attr_e( 'Conduct School Examination', 'mjschool' ); ?>">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-youtube-icon.png"); ?>" alt="<?php esc_attr_e( 'YouTube', 'mjschool' ); ?>">
						</a>
						<?php
					}
					$retrieve_class_data = mjschool_get_all_data( $tablename );
					if ( ! empty( $retrieve_class_data ) ) {
						?>
						<div>
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="exam_list" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Exam Term', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Exam Start Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Exam End Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Exam Comment', 'mjschool' ); ?></th>
												<?php
												if ( ! empty( $user_custom_field ) ) {
													foreach ( $user_custom_field as $custom_field ) {
														if ( $custom_field->show_in_table === '1' ) {
															?>
															<th><?php echo esc_html( $custom_field->field_label ); ?></th>
															<?php
														}
													}
												}
												?>
												<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 0;
											foreach ( $retrieve_class_data as $retrieved_data ) {
												$exam_id = mjschool_encrypt_id( $retrieved_data->exam_id );
												if ( $i === 10 ) { $i = 0; }
												$color_class_css = 'mjschool-class-color' . $i;
												?>
												<tr>
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>"></td>
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
														<a href="#" class="mjschool-color-black mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" type="Exam_view">
															<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png"); ?>" class="mjschool-massage-image">
															</p>
														</a>
													</td>
													<td>
														<a href="#" class="mjschool-color-black mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" type="Exam_view"><?php echo esc_html( $retrieved_data->exam_name ); ?></a>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Name', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_id, $retrieved_data->section_id ) ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( ! empty( get_the_title( $retrieved_data->exam_term ) ) ) {
															echo esc_html( get_the_title( $retrieved_data->exam_term ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Term', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_start_date ) ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Start Date', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_end_date ) ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam End Date', 'mjschool' ); ?>"></i>
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
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_attr( $comment ); } else { esc_attr_e( 'Exam Comment', 'mjschool' ); } ?>"></i>
													</td>
													<?php
													if ( ! empty( $user_custom_field ) ) {
														foreach ( $user_custom_field as $custom_field ) {
															if ( $custom_field->show_in_table === '1' ) {
																$module             = 'exam';
																$custom_field_id    = $custom_field->id;
																$module_record_id   = $retrieved_data->exam_id;
																$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
																if ( $custom_field->field_type === 'date' ) {
																	?>
																	<td><?php if ( ! empty( $custom_field_value ) ) { echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) ); } else { esc_html_e( 'N/A', 'mjschool' ); } ?></td>
																	<?php
																} elseif ( $custom_field->field_type === 'file' ) {
																	?>
																	<td>
																		<?php if ( ! empty( $custom_field_value ) ) { ?>
																			<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile">
																				<button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i><?php esc_html_e( 'Download', 'mjschool' ); ?></button>
																			</a>
																		<?php } else { esc_html_e( 'N/A', 'mjschool' ); } ?>
																	</td>
																	<?php
																} else {
																	?>
																	<td><?php if ( ! empty( $custom_field_value ) ) { echo esc_html( $custom_field_value ); } else { esc_html_e( 'N/A', 'mjschool' ); } ?></td>
																	<?php
																}
															}
														}
													}
													?>
													<td class="action">
														<div class="mjschool-user-dropdown">
															<ul class="mjschool_ul_style">
																<?php if ( ! empty( $retrieved_data->exam_syllabus ) ) { $doc_data = json_decode( $retrieved_data->exam_syllabus ); } ?>
																<li>
																	<a href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<li class="mjschool-float-left-width-100px">
																			<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" type="Exam_view"><i class="fa fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View Exam Detail', 'mjschool' ); ?></a>
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=viewexam&action=view&exam_id=' . rawurlencode( $exam_id ) . '&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-float-left-width-100px">
																				<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-timetable-icon.png"); ?>" class="mjschool_height_15px">&nbsp;&nbsp;<?php esc_html_e( 'Time Table Detail', 'mjschool' ); ?>
																			</a>
																		</li>
																		<?php if ( ! empty( $doc_data[0]->value ) ) { ?>
																			<li class="mjschool-float-left-width-100px">
																				<a target="blank" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value ) ); ?>" class="mjschool-status-read mjschool-float-left-width-100px" record_id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>"><i class="fa fa-eye"></i><?php esc_html_e( 'View Syllabus', 'mjschool' ); ?></a>
																			</li>
																		<?php } ?>
																		<?php if ( $user_access_edit === '1' ) { ?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=addexam&action=edit&exam_id=' . rawurlencode( $exam_id ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																		<?php } ?>
																		<?php if ( $user_access_delete === '1' ) { ?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=examlist&action=delete&exam_id=' . rawurlencode( $exam_id ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i><?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
																			</li>
																		<?php } ?>
																	</ul>
																</li>
															</ul>
														</div>
													</td>
												</tr>
												<?php ++$i; }
											?>
										</tbody>
									</table>
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" >
											<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<?php if ( $user_access_delete === '1' ) { ?>
											<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
										<?php } ?>
									</div>
								</form>
							</div>
						</div>
						<?php
					} elseif ( $user_access_add === '1' ) {
						?>
						<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=addexam' ) ); ?>">
								<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
							</a>
							<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
								<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="mjschool-calendar-event-new">
							<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
						</div>
						<?php
					}
				}
				if ( $active_tab === 'viewexam' ) {
					if ( isset( $_GET['tab'] ) ) {
						if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_exam_module_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}
					if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'view' ) {
						$exam_data         = mjschool_get_exam_by_id( mjschool_decrypt_id( sanitize_text_field( wp_unslash($_REQUEST['exam_id']) ) ) );
						$start_date        = $exam_data->exam_start_date;
						$end_date          = $exam_data->exam_end_date;
						$mjschool_obj_exam = new Mjschool_exam();
						$exam_time_table   = $mjschool_obj_exam->mjschool_get_exam_time_table_by_exam( mjschool_decrypt_id( sanitize_text_field( wp_unslash($_REQUEST['exam_id']) ) ) );
					}
					?>
					<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res">
						<div class="form-group">
							<div class="col-md-12 mjschool-rtl-padding-left-right-0px-for-btn">
								<div class="mjschool-exam-table-res mjschool-view-exam-timetable-div">
									<table class="mjschool-width-100px mjschool_examhall_border_1px_center">
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
												<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php if ( $exam_data->section_id != 0 ) { echo esc_html( mjschool_get_section_name( $exam_data->section_id ) ); } else { esc_html_e( 'No Section', 'mjschool' ); } ?></td>
												<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php if ( ! empty( get_the_title( $exam_data->exam_term ) ) ) { echo esc_html( get_the_title( $exam_data->exam_term ) ); } else { esc_html_e( 'N/A', 'mjschool' ); } ?></td>
												<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_date_in_input_box( $start_date ) ); ?></td>
												<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_date_in_input_box( $end_date ) ); ?></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<?php
						if ( ! empty( $exam_time_table ) ) {
							?>
							<div class="col-md-12 mjschool-margin-top-40">
								<div class="mjschool-exam-table-res mjschool-view-exam-timetable-div">
									<table class="mjschool-width-100px mjschool_examhall_border_1px_center">
										<thead>
											<tr>
												<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium_no_center"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
												<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium_no_border_top" ><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></th>
												<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium_no_border_top" ><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></th>
												<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium_no_border_top" ><?php esc_html_e( 'Exam Start Time', 'mjschool' ); ?></th>
												<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium_no_border_top" ><?php esc_html_e( 'Exam End Time', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											foreach ( $exam_time_table as $retrieved_data ) {
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
												$obj_subject = new Mjschool_Subject();
												?>
												<tr>
													<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px mjschool_border_1px_white" ><?php echo esc_html( $obj_subject->mjschool_get_single_subject_code( $retrieved_data->subject_id ) ); ?></td>
													<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject_id ) ); ?></td>
													<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_date ) ); ?></td>
													<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( $start_time ); ?></td>
													<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( $end_time ); ?></td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
							</div>
							<?php
						} else {
							?>
							<div id="mjschool-message" class="mjschool-message_class mjschool-rtl-message-display-inline-block alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible mjschool_margin_20px">
								<p><?php esc_html_e( 'No Any Time Table', 'mjschool' ); ?></p>
								<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
							</div>
						<?php } ?>
					</div>
				<?php }
				if ( $active_tab === 'addexam' ) { require_once MJSCHOOL_ADMIN_DIR . '/exam/add-exam.php'; }
				if ( $active_tab === 'exam_time_table' ) { require_once MJSCHOOL_ADMIN_DIR . '/exam/exam-time-table.php'; }
				?>
			</div>
		</div>
	</div>
</div>