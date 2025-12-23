<?php
/**
 * Subject Management Page
 *
 * This file handles the display, creation, editing, and deletion of subjects
 * within the mjschool management system dashboard. It manages user access
 * control, data validation, database operations (insert, update, delete),
 * and handles syllabus file uploads and teacher assignment logic.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$role_name         = mjschool_get_user_role( get_current_user_id() );
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'subject';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
$school_type = get_option( 'mjschool_custom_class' );
?>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$obj_subject = new Mjschool_Subject();
$active_tab  = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'subjectlist';
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
// =============== SAVE SUBJECT. =================//
if ( isset( $_POST['subject'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'add_subject_front_nonce' ) ) {
		$syllabus = '';
		if ( isset( $_FILES['subject_syllabus'] ) && ! empty( $_FILES['subject_syllabus']['name'] ) ) {
			$value      = explode( '.', $_FILES['subject_syllabus']['name'] );
			$file_ext   = strtolower( array_pop( $value ) );
			$extensions = array( 'pdf' );
			if ( in_array( $file_ext, $extensions ) === false ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=subject&message=3') );
				die();
			}
			if ( $_FILES['subject_syllabus']['size'] > 0 ) {
				$syllabus = mjschool_inventory_image_upload( $_FILES['subject_syllabus'] );
			} else {
				$syllabus = sanitize_text_field(wp_unslash($_POST['sylybushidden']));
			}
			// ------TEMPORARY ADD RECORD FOR SET SYLLABUS. ----------
		}
		// UPDATE SUBJECT DATA CODE.
		if ( isset( $_REQUEST['action'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$student_ids_array = isset($_POST['student_id'][0]) ? sanitize_text_field(wp_unslash($_POST['student_id'][0])) : array();
				$student_ids_string = implode( ',', array_map( 'intval', $student_ids_array ) );
				$subjects = array(
					'subject_code' => sanitize_text_field( wp_unslash($_POST['subject_code']) ),
					'sub_name'     => sanitize_textarea_field( stripslashes( $_POST['subject_name'] ) ),
					'class_id'     => sanitize_text_field( wp_unslash($_POST['subject_class']) ),
					'section_id'   => sanitize_text_field( wp_unslash($_POST['class_section']) ),
					'teacher_id'   => 0,
					'edition'      => sanitize_textarea_field( stripslashes( $_POST['subject_edition'] ) ),
					'author_name'  => sanitize_text_field( wp_unslash($_POST['subject_author']) ),
					'syllabus'     => $syllabus,
					'created_by'   => get_current_user_id(),
					'selected_students' => $student_ids_string,
					'subject_credit' => sanitize_text_field(wp_unslash($_POST['subject_credit'])),
				);
				if ( isset( $_FILES['subject_syllabus'] ) && empty( $_FILES['subject_syllabus']['name'] ) ) {
					unset( $subjects['syllabus'] );
				}
				$tablename         = 'mjschool_subject';
				$selected_teachers = isset( $_REQUEST['subject_teacher'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_teacher'])) : array();
				// ------------ SUBJECT CODE CHECK. ------------//
				$subject_code = sanitize_text_field( wp_unslash($_POST['subject_code']) );
				$class_id     = intval( wp_unslash($_POST['subject_class'] ) );
				global $wpdb;
				$table_name_subject = $wpdb->prefix . 'mjschool_subject';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result_sub = mjschool_get_subject_by_class_and_code($class_id, $subject_code);
					if ( $result_sub->subid != wp_unslash($_REQUEST['subject_id']) ) {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=subject&tab=addsubject&action=edit&subject_id=' . $sub_id . '&message=5') );
						die();
					}
				}
				global $wpdb;
				$table_mjschool_subject = $wpdb->prefix . 'mjschool_teacher_class';
				$subject_id         = intval( wp_unslash($_REQUEST['subject_id']) );
				$subid              = array( 'subid' => intval( wp_unslash($_REQUEST['subject_id']) ) );
				// UPDATE CUSTOM FIELD DATA.
				$custom_field_obj    = new Mjschool_Custome_Field();
				$module              = 'subject';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $subject_id );
				$result              = mjschool_update_record( $tablename, $subjects, $subid );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$wpdb->delete( $table_mjschool_subject, array( 'subject_id' => wp_unslash($_REQUEST['subject_id']) ), array( '%s' ) );
				if ( ! empty( $selected_teachers ) ) {
					$teacher_subject = $wpdb->prefix . 'mjschool_teacher_class';
					foreach ( $selected_teachers as $teacher_id ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$wpdb->insert(
							$teacher_subject,
							array(
								'teacher_id'   => $teacher_id,
								'subject_id'   => sanitize_text_field( wp_unslash($_REQUEST['subject_id']) ),
								'created_date' => time(),
								'created_by'   => get_current_user_id(),
							)
						);
					}
				}
				/* Send Assign Subject Mail. */
				if ( isset( $_POST['mjschool_mail_service_enable'] ) ) {
					foreach ( $_POST['subject_teacher'] as $teacher_id ) {
						$smgt_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_mail_service_enable']));
						if ( $smgt_mail_service_enable ) {
							$search['{{teacher_name}}'] = mjschool_get_teacher( $teacher_id );
							$search['{{subject_name}}'] = sanitize_text_field( wp_unslash($_POST['subject_name']) );
							$search['{{school_name}}']  = get_option( 'mjschool_name' );
							$message                    = mjschool_string_replacement( $search, get_option( 'mjschool_assign_subject_mailcontent' ) );
							if ( ! empty( $syllabus ) ) {
								$attechment = WP_CONTENT_DIR . '/uploads/school_assets/' . $syllabus;
							} else {
								$attechment = '';
							}
							if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
								mjschool_send_mail_for_homework( mjschool_get_email_id_by_user_id( $teacher_id ), get_option( 'mjschool_assign_subject_title' ), $message, $attechment );
							}
						}
					}
				}
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=subject&message=2' ) );
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			// INSERT SUBJECT DATA CODE.
			/* Setup Wizard. */
			if ( ! empty( $_POST['subject_class'] ) ) {
				foreach ( $_POST['subject_class'] as $key => $value ) {
					$subject_code = sanitize_text_field( wp_unslash($_POST['subject_code'][ $key ]) );
					$class_id     = sanitize_text_field( wp_unslash($value) );
					global $wpdb;
					$table_name_subject = $wpdb->prefix . 'mjschool_subject';
					$result_sub = mjschool_get_subject_by_class_and_code($class_id, $subject_code);
					if ( ! empty( $result_sub ) ) {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=subject&message=5' ) );
						die();
					} else {
						// INSERT IN SUBJECT TABLE.
						$student_ids_array = isset($_POST['student_id'][0]) ? wp_unslash($_POST['student_id'][0]) : array();
						$student_ids_string = implode( ',', array_map( 'intval', $student_ids_array ) );
						$subjects  = array(
							'subject_code' => sanitize_text_field( wp_unslash($_POST['subject_code'][ $key ]) ),
							'sub_name'     => sanitize_textarea_field( stripslashes( $_POST['subject_name'] ) ),
							'class_id'     => sanitize_text_field( wp_unslash($value) ),
							'section_id'   => sanitize_text_field( wp_unslash($_POST['class_section'][ $key ]) ),
							'teacher_id'   => 0,
							'edition'      => sanitize_textarea_field( stripslashes( $_POST['subject_edition'] ) ),
							'selected_students' => $student_ids_string,
							'author_name'  => sanitize_text_field( wp_unslash($_POST['subject_author']) ),
							'syllabus'     => $syllabus,
							'created_by'   => get_current_user_id(),
							'subject_credit' => sanitize_text_field(wp_unslash($_POST['subject_credit'])),
						);
						$tablename = 'mjschool_subject';
						$result    = mjschool_insert_record( $tablename, $subjects );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$lastid             = $wpdb->insert_id;
						$custom_field_obj   = new Mjschool_Custome_Field();
						$module             = 'subject';
						$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $lastid );
						$selected_teachers  = isset( $_POST['subject_teacher'][ $key ] ) ? wp_unslash($_POST['subject_teacher'][ $key ]) : array();
						if ( ! empty( $selected_teachers ) ) {
							$teacher_subject = $wpdb->prefix . 'mjschool_teacher_subject';
							$device_token    = array();
							foreach ( $selected_teachers as $teacher_id ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
								$wpdb->insert(
									$teacher_subject,
									array(
										'teacher_id'   => $teacher_id,
										'subject_id'   => $lastid,
										'created_date' => time(),
										'created_by'   => get_current_user_id(),
									)
								);
								$device_token[] = get_user_meta( $teacher_id, 'token_id', true );
							}
							/* Send Push Notification. */
							$title             = esc_html__( 'New Notification For Assign Subject', 'mjschool' );
							$text              = esc_html__( 'New subject', 'mjschool' ) . ' ' . sanitize_text_field( wp_unslash($_POST['subject_name']) ) . ' ' . esc_html__( 'has been assigned to you.', 'mjschool' );
							$notification_data = array(
								'registration_ids' => $device_token,
								'data'             => array(
									'title' => $title,
									'body'  => $text,
									'type'  => 'notification',
								),
							);
							$json              = json_encode( $notification_data );
							$message           = mjschool_send_push_notification( $json );
							/* Send Push Notification. */
						}
						if ( $result ) {
							/* Send Assign Subject Mail. */
							if ( isset( $_POST['mjschool_mail_service_enable'] ) ) {
								foreach ( $selected_teachers as $teacher_id ) {
									$smgt_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_mail_service_enable']));
									if ( $smgt_mail_service_enable ) {
										$search['{{teacher_name}}'] = mjschool_get_teacher( $teacher_id );
										$search['{{subject_name}}'] = sanitize_text_field( wp_unslash($_POST['subject_name']) );
										$search['{{school_name}}']  = get_option( 'mjschool_name' );
										$message                    = mjschool_string_replacement( $search, get_option( 'mjschool_assign_subject_mailcontent' ) );
										if ( ! empty( $syllabus ) ) {
											$attechment = WP_CONTENT_DIR . '/uploads/school_assets/' . $syllabus;
										} else {
											$attechment = '';
										}
										if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
											mjschool_send_mail_for_homework( mjschool_get_email_id_by_user_id( $teacher_id ), get_option( 'mjschool_assign_subject_title' ), $message, $attechment );
										}
									}
								}
							}
						}
					}
				}
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=subject&message=1' ) );
			}
		}
	}
$subject_obj = new Mjschool_Subject();
// --------------- MULTIPLE SELECTED SUBJECT DELETE. -----------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_books' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $subject_id ) {
			$tablename = 'mjschool_subject';
			$result    = $subject_obj->mjschool_delete_subject( $tablename, $subject_id );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=subject&message=4') );
			die();
		}
	}
}
// -------------- Delete SUBJECT. -------------------//
$teacher_obj = new Mjschool_Teacher();
$tablename   = 'mjschool_subject';
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = $subject_obj->mjschool_delete_subject( $tablename, mjschool_decrypt_id( wp_unslash($_REQUEST['subject_id']) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=subject&message=4') );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
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
<!-- End POP-UP Code. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!----------- PENAL BODY ------------->
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Subject Added Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Subject Updated Successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'This File Type Is Not Allowed, Please Upload Only Pdf File.', 'mjschool' );
			break;
		case '4':
			$message_string = esc_html__( 'Subject Deleted Successfully.', 'mjschool' );
			break;
		case '5':
			$message_string = esc_html__( 'Please Enter Unique Subject Code', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	// ---------------- SUBJECT LIST TAB. ----------------//
	if ( $active_tab === 'subjectlist' ) {
		$user_id = get_current_user_id();
		// ------- SUBJECT DATA FOR STUDENT. ---------//
		if ( $school_obj->role === 'student' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				if ( $school_type === "university"){ 
					$class_id = get_user_meta($user_id, 'class_name', true);
					$subjects = mjschool_subject_list_univercity($class_id);
				}
				else
				{
					$subjects = $school_obj->subject;
				}
			} else {
				$subjects = mjschool_get_all_data( 'mjschool_subject' );
			}
		}
		// ------- SUBJECT DATA FOR TEACHER. ---------//
		elseif ( $school_obj->role === 'teacher' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$subjects      = array();
				$subjects_data = $obj_subject->mjschool_get_teacher_own_subject( $user_id );
				foreach ( $subjects_data as $s_id ) {
					$subjects[] = mjschool_get_subject( $s_id->subject_id );
				}
			} else {
				$subjects = mjschool_get_all_data( 'mjschool_subject' );
			}
		}
		// ------- SUBJECT DATA FOR PARENT. ---------//
		elseif ( $school_obj->role === 'parent' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				if ( $school_type === "university") {
					$child_array = $school_obj->child_list;
					$result = array();
					foreach ($child_array as $child_id) {
						// Get child's class ID.
						$class_id = get_user_meta($child_id, 'class_name', true);
						// Get subject list for this child using a modified version of the subject function.
						$subject_list = mjschool_subject_list_univercity_for_child($class_id, $child_id);
						if ( ! empty( $subject_list) && is_array($subject_list ) ) {
							$result[] = $subject_list;
						}
					}
					$subjects = call_user_func_array( 'array_merge', $result); // Flatten array.
				}
				else
				{
					$chid_array = $school_obj->child_list;
					$result     = array();
					foreach ( $chid_array as $child_id ) {
						$class_info   = $school_obj->mjschool_get_user_class_id( $child_id );
						$subject_list = $school_obj->mjschool_subject_list( $class_info->class_id );
						// Ensure it's a non-empty array.
						if ( ! empty( $subject_list ) && is_array( $subject_list ) ) {
							$result[] = $subject_list;
						}
					}
					if ( ! empty( $result ) ) {
						$mergedArray = array_merge( ...$result );
						$subjects    = array_unique( $mergedArray, SORT_REGULAR );
					} else {
						$subjects = array(); // Avoid undefined variable.
					}
				}
			} else {
				$subjects = mjschool_get_all_data( 'mjschool_subject' );
			}
		}
		// ------- SUBJECT DATA FOR SUPPORT STAFF. ---------//
		else {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$subjects = mjschool_get_all_own_subject_data( 'mjschool_subject', );
			} else {
				$subjects = mjschool_get_all_data( 'mjschool_subject' );
			}
		}
		if ( ! empty( $subjects ) ) {
			?>
			<div class="mjschool-panel-body"><!------------ Panel body. ---------------->
				<div class="table-responsive"><!---------------- Table responsive. ------------------>
					<!----------- Subject list form start. ---------->
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<?php wp_nonce_field( 'bulk_delete_books' ); ?>
						<table id="mjschool-subject-list-frontend" class="display dataTable dataTable1" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $role_name === 'supportstaff' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
										<?php
									}
									?>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
									<?php if ( $school_type === 'university' ) {?>
										<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
									<?php } ?>
									<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Author Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Edition', 'mjschool' ); ?></th>
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
								foreach ( $subjects as $retrieved_data ) {
									$teacher_group   = array();
									$teacher_display = array();
									$teacher_ids     = mjschool_teacher_by_subject( $retrieved_data );
									$ti              = 0;
									foreach ( $teacher_ids as $teacher_id ) {
										$teacher_group[] = mjschool_get_teacher( $teacher_id );
										if ( $ti < 3 ) {
											$teacher_display[] = mjschool_get_teacher( $teacher_id );
										}
										++$ti;
									}
									$teachers         = implode( ',', $teacher_group );
									$teacher_displays = implode( ',', $teacher_display );
									$student_group = array();
									$student_display = array();
									$student_ids = explode( ',', $retrieved_data->selected_students);
									$ti = 0;
									foreach ($student_ids as $student_id) {
										$student_group[] = mjschool_get_teacher($student_id);
										if ($ti < 3) {
											$student_display[] = mjschool_get_teacher($student_id);
										}
										$ti++;
									}
									$student = implode( ',', $student_group);
									$student_displays = implode( ',', $student_display);
									$color_class_css = mjschool_table_list_background_color( $i );
									?>
									<tr>
										<?php
										if ( $role_name === 'supportstaff' ) {
											?>
											<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->subid ); ?>"></td>
											<?php
										}
										?>
										<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
											<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->subid ); ?>" type="subject_view">
												<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
													
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-subject.png"); ?>" class="mjschool-massage-image">
													
												</p>
											</a>
										</td>
										<td>
											<?php
											if ( ! empty( $retrieved_data->subject_code ) ) {
												echo esc_html( $retrieved_data->subject_code );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Code', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->subid ); ?>" type="subject_view">
												<?php echo esc_html( $retrieved_data->sub_name ); ?>
											</a> 
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Name', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php
											if ( $teacher_displays ) {
												echo esc_html( $teacher_displays );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $teachers ) ) { echo esc_html( $teachers ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?>"></i>
										</td>
										<?php
										if ( $school_type === 'university' )
										{
											?>
											<td>
												<?php 
												if ( ! empty( $student_displays ) ) {
													echo esc_html( $student_displays);
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												}
												?>
												<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $student ) ) { echo esc_html( $student); } else { esc_html_e( 'N/A', 'mjschool' ); } ?>"> </i>
											</td>
											<?php
										}
										?>
										<td>
											<?php
											$cid = $retrieved_data->class_id;
											if ( ! empty( $cid ) ) {
												$clasname = mjschool_get_class_section_name_wise( $cid, $retrieved_data->section_id );
												echo esc_html( $clasname );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php
											if ( ! empty( $retrieved_data->author_name ) ) {
												// Truncate the author name to 30 characters.
												$author_name = mb_strimwidth( $retrieved_data->author_name, 0, 30, '...' );
												echo esc_html( $author_name );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $retrieved_data->author_name ) ) { echo esc_html( $retrieved_data->author_name ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?>"> </i>
										</td>
										<td>
											<?php
											if ( ! empty( $retrieved_data->edition ) ) {
												// Truncate the edition to 30 characters.
												$edition_chunk = mb_strimwidth( $retrieved_data->edition, 0, 20, '...' );
												echo esc_html( $edition_chunk );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $retrieved_data->edition ) ) { echo esc_attr( $retrieved_data->edition ); } else { echo esc_attr( 'Not Provided' ); } ?>"> </i>
										</td>
										<?php
										// Custom Field Values.
										if ( ! empty( $user_custom_field ) ) {
											foreach ( $user_custom_field as $custom_field ) {
												if ( $custom_field->show_in_table === '1' ) {
													$module             = 'subject';
													$custom_field_id    = $custom_field->id;
													$module_record_id   = $retrieved_data->subid;
													$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
													if ( $custom_field->field_type === 'date' ) {
														?>
														<td>
															<?php
															if ( ! empty( $custom_field_value ) ) {
																echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
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
																<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
																<?php
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
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
																esc_html_e( 'Not Provided', 'mjschool' );
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
													<li >
														
														<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
														</a>
														
														<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
															<li class="mjschool-float-left-width-100px">
																<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->subid ); ?>" type="subject_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
															</li>
															<?php
															if ( $user_access['edit'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu"> <a href="<?php echo esc_url( '?dashboard=mjschool_user&page=subject&tab=addsubject&action=edit&subject_id=' . mjschool_encrypt_id( $retrieved_data->subid ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a> </li>
																<?php
															}
															if ( $user_access['delete'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=subject&tab=Subject&action=delete&subject_id=' . mjschool_encrypt_id( $retrieved_data->subid ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
						if ( $role_name === 'supportstaff' ) {
							?>
							<div class="mjschool-print-button pull-left mjschool-padding-top-25px-res">
								<button class="mjschool-btn-sms-color mjschool-button-reload">
									<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
									<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
								</button>
								<?php
								if ( $user_access['delete'] === '1' ) {
									 ?>
									<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</form>
				</div>
			</div>
			<?php
		} else {
			if ($user_access['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=subject&tab=addsubject') ); ?>">
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
	// ----------------- Add subject tab. ------------------//
	if ( $active_tab === 'addsubject' ) {
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$edit    = 1;
			$subject = mjschool_get_subject( mjschool_decrypt_id( wp_unslash($_REQUEST['subject_id']) ) );
		}
		?>
		<div class="mjschool-panel-body"><!------------ Panel body. ------------>
			<!----------- Subject form start. ---------------->
			<form name="mjschool-student-form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="subject_form">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="subject_id" value="<?php if ( $edit ) { echo esc_attr( mjschool_decrypt_id( wp_unslash($_REQUEST['subject_id']) ) ); } ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Subject Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="subject_name" class="form-control validate[required,custom[address_description_validation]] mjschool-margin-top-10px_res" type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $subject->sub_name );} ?>" name="subject_name">
									<label for="subject_name"><?php esc_html_e( 'Subject Name', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-padding-top-15px-res">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="subject_edition" class="form-control validate[custom[address_description_validation]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $subject->edition );} ?>" name="subject_edition">
									<label for="subject_edition"><?php esc_html_e( 'Edition', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="subject_author" class="form-control validate[custom[onlyLetter_specialcharacter]]" maxlength="100" type="text" value="<?php if ( $edit ) { echo esc_attr( $subject->author_name );} ?>" name="subject_author">
									<label for="subject_author"><?php esc_html_e( 'Author Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<?php if ( $school_type === "university"){ ?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="subject_credit" class="form-control validate[required] mjschool-margin-top-10-res" type="number" maxlength="50" value="<?php if ($edit) { echo esc_attr($subject->subject_credit); } ?>" name="subject_credit">
										<label for="subject_credit"><?php esc_html_e( 'Subject Credit', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<div class="col-md-6 input mjschool-error-msg-left-margin">
								<label class="ml-1 mjschool-custom-top-label top" for="class_list_subject"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label><?php
								if ( $edit ) {
									$classval = $subject->class_id;
								} else {
									$classval = '';
								}
								$name_attr = $edit ? 'subject_class' : 'subject_class[]';
								?>
								<select name="<?php echo esc_attr($name_attr); ?>" class="mjschool-line-height-30px form-control validate[required] class_by_teacher_subject" id="class_list_subject">
									<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
									<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
										<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-md-6 mjschool-rtl-margin-top-15px mb-3 mjschool-teacher-list-multiselect">
								<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
									<?php
									if ( $edit){

										$teacherdata_array = mjschool_get_users_by_class_id($subject->class_id);
									}
									else
									{
										$teacherdata_array = mjschool_get_users_data( 'student' );
									}
									$selected_students = array();
									if ( isset( $subject->selected_students ) && !empty( $subject->selected_students ) ) {
										$selected_students = explode( ',', $subject->selected_students );
									}
									?>
									<select name="student_id[0][]" multiple="multiple" id="subject_student_subject" class="form-control validate[required] teacher_list">
										<?php foreach ( $teacherdata_array as $teacherdata ) { ?>
											<option value="<?php echo esc_attr( $teacherdata->ID ); ?>" <?php selected( in_array( $teacherdata->ID, $selected_students ), true ); ?>>
												<?php echo esc_html( mjschool_student_display_name_with_roll($teacherdata->ID ) ); ?>
											</option>
										<?php } ?>
									</select>
									<span class="mjschool-multiselect-label">
										<label class="ml-1 mjschool-custom-top-label top" for="subject_student_subject"><?php esc_html_e( 'Select Students', 'mjschool' ); ?><span class="required">*</span></label>
									</span>
								</div>
							</div>
							<?php
						}
						if ( $edit ) {
							$syllabus = $subject->syllabus;
							?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
										<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px"><?php esc_html_e( 'Syllabus', 'mjschool' ); ?></label>
										<div class="col-sm-12">
											<input type="file" class="form-control file" accept=".pdf" name="subject_syllabus" id="subject_syllabus-frontend" />
											<input type="hidden" name="sylybushidden" value="<?php if ( $edit ) { echo esc_attr( $subject->syllabus ); } else { echo '';} ?>">
										</div>
										<?php
										if ( ! empty( $syllabus ) ) {
											?>
											<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
												<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $syllabus )); ?>" record_id="<?php echo esc_attr( $subject->subject ); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
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
									<div class="col-md-12 form-control mjschool-res-rtl-height-50px mjschool-file-height-padding">
										<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 margin_left_30px"><?php esc_html_e( 'Syllabus', 'mjschool' ); ?></span>
										<div class="col-sm-12">
											<input type="file" accept=".pdf" class="form-control file col-md-12" name="subject_syllabus" id="subject_syllabus-frontend" />
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'More Information', 'mjschool' ); ?></h3>
				</div>
				<?php
				if ( $edit ) {
					?>
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="subject_code" class="form-control validate[required,custom[popup_category_validation],maxSize[8],min[0]] text-input" type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $subject->subject_code );} ?>" name="subject_code">
										<label for="subject_code"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<?php if ( $school_type === 'school' ) {?>
								<div class="col-md-6 input mjschool-error-msg-left-margin">
									<label class="ml-1 mjschool-custom-top-label top" for="class_list_subject"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label>
									<?php
									if ( $edit ) {
										$classval = $subject->class_id;
									} else {
										$classval = '';
									}
									?>
									<select name="subject_class" class="mjschool-line-height-30px form-control validate[required] class_by_teacher_subject" id="class_list_subject">
										<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
										<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
											<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col-md-6 input">
									<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-section-subject"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
									<?php
									if ( $edit ) {
										$sectionval = $subject->section_id;
									} elseif ( isset( $_POST['class_section'] ) ) {
										$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
									} else {
										$sectionval = '';
									}
									?>
									<select name="class_section" class="mjschool-line-height-30px form-control" id="mjschool-class-section-subject">
										<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
										<?php
										if ( $edit ) {
											foreach ( mjschool_get_class_sections( $subject->class_id ) as $sectiondata ) {
												?>
												<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
												<?php
											}
										}
										?>
									</select>
								</div>
								<?php
							}
							if ( $school_obj->role === 'teacher' ) {
								$user_id = get_current_user_id();
								?>
								<div class="col-md-5 input">
									<input type="hidden" name="subject_teacher[0][]" value="<?php echo esc_attr( $user_id ); ?>">
								</div>
								<?php
							} else {
								?>
								<div class="col-md-6 mjschool-rtl-margin-top-15px mjschool-teacher-list-multiselect">
									<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
										<?php
										$teachval = array();
										if ( $edit ) {
											$teachval          = mjschool_teacher_by_subject( $subject );
											$teacherdata_array = mjschool_get_teacher_by_class_id( $subject->class_id );
										} else {
											$teacherdata_array = mjschool_get_users_data( 'teacher' );
										}
										?>
										<select name="subject_teacher[]" multiple="multiple" id="subject_teacher_subject" class="form-control validate[required] teacher_list">
											<?php foreach ( $teacherdata_array as $teacherdata ) { ?>
												<option value="<?php echo esc_attr( $teacherdata->ID ); ?>" <?php echo $teacher_obj->mjschool_in_array_r( $teacherdata->ID, $teachval ) ? 'selected' : ''; ?>><?php echo esc_html( $teacherdata->display_name ); ?></option>
											<?php } ?>
										</select>
										<span class="mjschool-multiselect-label">
											<label class="ml-1 mjschool-custom-top-label top" for="subject_teacher_subject"><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?><span class="required">*</span></label>
										</span>
									</div>
								</div>
								<?php
							}
							?>
						</div>
					</div>
					<?php
				} else {
					?>
					<div class="more_info">
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="subject_code" class="form-control validate[required,custom[popup_category_validation],maxSize[8],min[0]] text-input" type="text" maxlength="50" value="" name="subject_code[]">
											<label for="subject_code"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?><span class="required">*</span></label>
										</div>
									</div>
								</div>
								<?php if ( $school_type === 'school' ) {?>
									<div class="col-md-6 input mjschool-error-msg-left-margin">
										<label class="ml-1 mjschool-custom-top-label top" for="class_list_subject"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label>
										<select name="subject_class[]" class="form-control validate[required] mjschool-width-100px class_by_teacher_subject mjschool_heights_47px" id="class_list_subject" >
											<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
											<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
												<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
											<?php } ?>
										</select>
									</div>
								<?php } ?>
								<?php if ( $school_type === 'school' ) {?>
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-section-subject"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
										<select name="class_section[]" class="form-control mjschool-width-100px mjschool_heights_47px" id="mjschool-class-section-subject" >
											<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
										</select>
									</div>
									<?php
								}
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
											<select name="subject_teacher[0][]" multiple="multiple" id="subject_teacher_subject" class="form-control validate[required]">
												<?php
												foreach ( $teacherdata_array as $teacherdata ) {
													?>
													<option value="<?php echo esc_attr( $teacherdata->ID ); ?>"><?php echo esc_html( $teacherdata->display_name ); ?></option>
												<?php } ?>
											</select>
											<span class="mjschool-multiselect-label">
												<label class="ml-1 mjschool-custom-top-label top" for="subject_teacher_subject"><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?><span class="required">*</span></label>
											</span>
										</div>
									</div>
									<?php
								}
								?>
								<input type="hidden" class="click_value" name="" value="1">
								<?php if ( $school_type === 'school' ) {?>
									<div class="col-md-1 col-2 col-sm-1 col-xs-12">
										
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_entry()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
										
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php
				}
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<?php
						if ( ! $edit ) {
							if ( $role_name === 'supportstaff' ) {
								?>
								<div class="col-md-6 mjschool-rtl-margin-top-15px mjschool_margin_bottom_15px" >
									<div class="form-group">
										<div class="col-md-12 form-control mjschool-input-height-50px">
											<div class="row mjschool-padding-radio">
												<div class="input-group mjschool-input-checkbox">
													<span class="mjschool-custom-top-label"><?php esc_html_e( 'Send Email to Teacher', 'mjschool' ); ?></span>
													<div class="checkbox mjschool-checkbox-label-padding-8px">
														<label>
															<input id="chk_subject_mail" type="checkbox" <?php $smgt_mail_service_enable = 0; if ( $smgt_mail_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_mail_service_enable">
														</label>
													</div>
													&nbsp;&nbsp;<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$custom_field_obj = new Mjschool_Custome_Field();
				$module           = 'subject';
				$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<?php wp_nonce_field( 'add_subject_front_nonce' ); ?>
				<div class="form-body mjschool-user-form mjschool-padding-top-15px-res">
					<div class="row">
						<div class="col-sm-6">
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Subject', 'mjschool' ); } else { esc_html_e( 'Add Subject', 'mjschool' ); } ?>" name="subject" class="btn btn-success mjschool-save-btn <?php if ( $school_obj->role !== 'teacher' ) { echo 'mjschool-teacher-for-alert'; } ?>" />
						</div>
					</div>
				</div>
			</form>
			<!----------- Subject form end. ---------------->
		</div><!------------ Panel body. ------------>
		<?php
	}
	?>
</div><!----------- Panel body. ------------->