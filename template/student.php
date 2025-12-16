<?php
/**
 * Student Profile Management and Enrollment Form.
 *
 * This file contains the logic and HTML for displaying a student's profile or the
 * comprehensive form for adding/editing a student record. It includes user role and
 * access control checks, retrieval of custom fields, and extensive client-side
 * validation setup for various student data fields.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$school_type               = get_option( 'mjschool_custom_class' );
$mjschool_role_name        = mjschool_get_user_role( get_current_user_id() );
$student_id                = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['student_id']) ) );
$class_id                  = get_user_meta( $student_id, 'class_name', true );
$section_name              = get_user_meta( $student_id, 'class_section', true );
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'student';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );

?>
<?php
$obj_mark   = new Mjschool_Marks_Manage();
$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : 'studentlist';
$mjschool_role       = 'student';
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

// --------------- SAVE STUDENT. -------------------//
if ( isset( $_POST['save_student'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_student_frontend_nonce' ) ) {
		$firstname  = sanitize_text_field( wp_unslash($_POST['first_name']) );
		$middlename = sanitize_text_field( wp_unslash($_POST['middle_name']) );
		$lastname   = sanitize_text_field( wp_unslash($_POST['last_name']) );
		$userdata   = array(
			'user_login'    => sanitize_email( wp_unslash($_POST['email']) ),
			'user_nicename' => null,
			'user_email'    => sanitize_email( wp_unslash($_POST['email']) ),
			'user_url'      => null,
			'display_name'  => $firstname . ' ' . $middlename . ' ' . $lastname,
		);
		if ( $_POST['password'] != '' ) {
			$userdata['user_pass'] = strip_tags( $_POST['password'] );
		}
		if ( isset( $_FILES['upload_user_avatar_image'] ) && ! empty( $_FILES['upload_user_avatar_image'] ) && $_FILES['upload_user_avatar_image']['size'] != 0 ) {
			if ( $_FILES['upload_user_avatar_image']['size'] > 0 ) {
				$member_image = mjschool_load_documets( $_FILES['upload_user_avatar_image'], 'upload_user_avatar_image', 'pimg' );
			}
			$photo = esc_url(content_url( '/uploads/school_assets/' . $member_image));
		} else {
			if ( isset( $_REQUEST['hidden_upload_user_avatar_image'] ) ) {
				$member_image = sanitize_text_field(wp_unslash($_REQUEST['hidden_upload_user_avatar_image']));
			}
			$photo = $member_image;
		}
		// DOCUMENT UPLOAD FILE CODE START.
		$document_content = array();
		if ( ! empty( $_FILES['document_file']['name'] ) ) {
			$count_array = count( $_FILES['document_file']['name'] );
			for ( $a = 0; $a < $count_array; $a++ ) {
				if ( ( $_FILES['document_file']['size'][ $a ] > 0 ) && ( ! empty( $_POST['document_title'][ $a ] ) ) ) {
					$document_title = sanitize_text_field(wp_unslash($_POST['document_title'][ $a ]));
					$document_file  = mjschool_upload_document_user_multiple( $_FILES['document_file'], $a, sanitize_text_field(wp_unslash($_POST['document_title'][ $a ])) );
				} elseif ( ! empty( $_POST['user_hidden_docs'][ $a ] ) && ! empty( $_POST['document_title'][ $a ] ) ) {
					$document_title = sanitize_text_field(wp_unslash($_POST['document_title'][ $a ]));
					$document_file  = sanitize_text_field(wp_unslash($_POST['user_hidden_docs'][ $a ]));
				}
				if ( ! empty( $document_file ) && ! empty( $document_title ) ) {
					$document_content[] = array(
						'document_title' => $document_title,
						'document_file'  => $document_file,
					);
				}
			}
		}
		if ( ! empty( $document_content ) ) {
			$final_document = json_encode( $document_content );
		} else {
			$final_document = '';
		}
		// Add Sibling details. //
		$sibling_value = array();
		if ( ! empty( $_POST['siblingsclass'] ) ) {
			foreach ( $_POST['siblingsclass'] as $key => $value ) {
				$sibling_value[] = array(
					'siblingsclass'   => $value,
					'siblingssection' => sanitize_text_field(wp_unslash($_POST['siblingssection'][ $key ])),
					'siblingsstudent' => sanitize_text_field(wp_unslash($_POST['siblingsstudent'][ $key ])),
				);
			}
		}
		// DOCUMENT UPLOAD FILE CODE END.
		$usermetadata = array(
			'admission_no'           => sanitize_text_field( wp_unslash($_POST['admission_no']) ),
			'roll_id'                => mjschool_strip_tags_and_stripslashes( wp_unslash($_POST['roll_id']) ),
			'middle_name'            => mjschool_strip_tags_and_stripslashes( sanitize_text_field( wp_unslash($_POST['middle_name'])) ),
			'gender'                 => sanitize_text_field( wp_unslash($_POST['gender'])),
			'birth_date'             => sanitize_text_field( wp_unslash($_POST['birth_date'])),
			'address'                => mjschool_strip_tags_and_stripslashes( sanitize_text_field( wp_unslash($_POST['address'])) ),
			'city'                   => mjschool_strip_tags_and_stripslashes( sanitize_text_field( wp_unslash($_POST['city_name'])) ),
			'state'                  => mjschool_strip_tags_and_stripslashes( sanitize_text_field( wp_unslash($_POST['state_name'])) ),
			'zip_code'               => mjschool_strip_tags_and_stripslashes( wp_unslash($_POST['zip_code']) ),
			'class_name'             => sanitize_text_field( wp_unslash($_POST['class_name'])),
			'class_section'          => sanitize_text_field( wp_unslash($_POST['class_section'])),
			'phone'                  => sanitize_text_field( wp_unslash($_POST['phone'])),
			'mobile_number'          => sanitize_text_field( wp_unslash($_POST['mobile_number'])),
			'user_document'          => $final_document,
			'sibling_information'    => json_encode( $sibling_value ),
			'alternet_mobile_number' => sanitize_text_field( wp_unslash($_POST['alternet_mobile_number'])),
			'mjschool_user_avatar'   => $photo,
			'created_by'             => get_current_user_id(),
		);
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$userbyroll_no = get_users(
			array(
				'meta_query' =>
				array(
					'relation' => 'AND',
					array(
						'key'   => 'class_name',
						'value' => sanitize_text_field( wp_unslash($_POST['class_name'])),
					),
					array(
						'key'   => 'roll_id',
						'value' => mjschool_strip_tags_and_stripslashes( wp_unslash($_POST['roll_id']) ),
					),
				),
				'role'       => 'student',
			)
		);
		$is_rollno     = count( $userbyroll_no );
		if ( $_REQUEST['action'] === 'edit' ) {
			// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			$args       = array(
				'meta_key'   => 'admission_no',
				'meta_value' => sanitize_text_field( wp_unslash($_POST['admission_no'])),
				'number'     => 1,
				'fields'     => 'ID',
			);
			$user_query = new WP_User_Query( $args );
			// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			$admission_user_id = '';
			if ( ! empty( $user_query->get_results() ) ) {
				$admission_user_id = $user_query->get_results()[0];
			}
			if ( ! empty( $admission_user_id ) && $admission_user_id != $student_id ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&message=16') );
				die();
			}
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$userdata['ID'] = $student_id;
				$result         = mjschool_update_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $mjschool_role );
				if ( ! empty( $sibling_value ) ) {
					foreach ( $sibling_value as $sibling ) {
						$sibling_student_id = intval( $sibling['siblingsstudent'] );
						if ( $sibling_student_id > 0 && $sibling_student_id != $student_id ) {
							$existing_siblings = get_user_meta( $sibling_student_id, 'sibling_information', true );
							if ( ! empty( $existing_siblings ) ) {
								$existing_siblings = json_decode( $existing_siblings, true );
							} else {
								$existing_siblings = array();
							}
							// Check if already added to avoid duplicates.
							$already_exists = false;
							foreach ( $existing_siblings as $sibling_info ) {
								if ( isset( $sibling_info['siblingsstudent'] ) && $sibling_info['siblingsstudent'] === $student_id ) {
									$already_exists = true;
									break;
								}
							}
							if ( ! $already_exists ) {
								$existing_siblings[] = array(
									'siblingsclass'   => sanitize_text_field( wp_unslash($_POST['class_name']) ),
									'siblingssection' => sanitize_text_field( wp_unslash($_POST['class_section']) ),
									'siblingsstudent' => $student_id,
								);
								update_user_meta( $sibling_student_id, 'sibling_information', json_encode( $existing_siblings ) );
							}
						}
					}
				}
				// Custom Field File Update. //
				$module              = 'student';
				$custom_field_update = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&&message=2') );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			$args       = array(
				'meta_key'   => 'admission_no',
				'meta_value' => sanitize_text_field( wp_unslash($_POST['admission_no'])),
				'number'     => 1,
				'fields'     => 'ID',
			);
			$user_query = new WP_User_Query( $args );
			// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			$admission_user_id = '';
			if ( ! empty( $user_query->get_results() ) ) {
				$admission_user_id = $user_query->get_results()[0];
			}
			if ( ! empty( $admission_user_id ) ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&message=16') );
				die();
			}
			if ( ! email_exists( $_POST['email'] ) ) {
				if ( $is_rollno ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&&message=3') );
					die();
				} else {
					$result     = mjschool_add_new_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $mjschool_role );
					$student_id = $result;
					if ( ! empty( $sibling_value ) ) {
						foreach ( $sibling_value as $sibling ) {
							$sibling_student_id = intval( $sibling['siblingsstudent'] );
							if ( $sibling_student_id > 0 && $sibling_student_id != $student_id ) {
								$existing_siblings = get_user_meta( $sibling_student_id, 'sibling_information', true );
								if ( ! empty( $existing_siblings ) ) {
									$existing_siblings = json_decode( $existing_siblings, true );
								} else {
									$existing_siblings = array();
								}
								// Check if already added to avoid duplicates.
								$already_exists = false;
								foreach ( $existing_siblings as $sibling_info ) {
									if ( isset( $sibling_info['siblingsstudent'] ) && $sibling_info['siblingsstudent'] === $student_id ) {
										$already_exists = true;
										break;
									}
								}
								if ( ! $already_exists ) {
									$existing_siblings[] = array(
										'siblingsclass'   => sanitize_text_field( wp_unslash($_POST['class_name']) ),
										'siblingssection' => sanitize_text_field( wp_unslash($_POST['class_section']) ),
										'siblingsstudent' => $student_id,
									);
									update_user_meta( $sibling_student_id, 'sibling_information', json_encode( $existing_siblings ) );
								}
							}
						}
					}
					// Custom Field File Insert. //
					$module             = 'student';
					$insert_custom_data = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
					if ( $result ) {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&&message=1') );
						die();
					}
				}
			} else {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&message=4') );
				die();
			}
		}
	}
}

// --------- Save Active User. ------------//
if ( isset( $_POST['active_user'] ) ) {
	$class = get_user_meta( intval( wp_unslash($_REQUEST['act_user_id']) ), 'class_name', true );
	// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	$args          = array(
		'meta_query' =>
		array(
			'relation' => 'AND',
			array(
				'key'   => 'class_name',
				'value' => $class,
			),
			array(
				'key'   => 'roll_id',
				'value' => sanitize_text_field( wp_unslash($_REQUEST['roll_id']) ),
			),
		),
		'role'       => 'student',
	);
	$userbyroll_no = get_users( $args );
	// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	$is_rollno = count( $userbyroll_no );
	if ( $is_rollno ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&message=3') );
		die();
	} else {
		update_user_meta( wp_unslash($_POST['act_user_id']), 'roll_id', wp_unslash($_POST['roll_id']) );
		if ( isset( $_POST['mjschool_student_mail_service_enable'] ) || isset( $_POST['mjschool_student_sms_service_enable'] ) ) {
			if ( isset( $_POST['mjschool_student_mail_service_enable'] ) ) {
				$active_user_id            = intval( wp_unslash($_REQUEST['act_user_id']) );
				$class_name                = get_user_meta( $active_user_id, 'class_name', true );
				$user_info                 = get_userdata( sanitize_text_field( wp_unslash($_POST['act_user_id']) ) );
				$to                        = $user_info->user_email;
				$subject                   = get_option( 'mjschool_student_activation_title' );
				$Seach['{{student_name}}'] = $user_info->display_name;
				$Seach['{{user_name}}']    = $user_info->user_login;
				$Seach['{{class_name}}']   = mjschool_get_class_name( $class_name );
				$Seach['{{email}}']        = $to;
				$Seach['{{school_name}}']  = get_option( 'mjschool_name' );
				$MsgContent                = mjschool_string_replacement( $Seach, get_option( 'mjschool_student_activation_mailcontent' ) );
				mjschool_send_mail( $to, $subject, $MsgContent );
				// ----------- STUDENT ASSIGNED TEACHER MAIL. ------------//
				$TeacherIDs                 = mjschool_check_class_exits_in_teacher_class( $class_name );
				$TeacherEmail               = array();
				$string['{{school_name}}']  = get_option( 'mjschool_name' );
				$string['{{student_name}}'] = mjschool_get_display_name( wp_unslash($_POST['act_user_id']) );
				$subject                    = get_option( 'mjschool_student_assign_teacher_mail_subject' );
				$MessageContent             = get_option( 'mjschool_student_assign_teacher_mail_content' );
				foreach ( $TeacherIDs as $teacher ) {
					$TeacherData                = get_userdata( $teacher );
					$string['{{teacher_name}}'] = mjschool_get_display_name( $TeacherData->ID );
					$message                    = mjschool_string_replacement( $string, $MessageContent );
					mjschool_send_mail( $TeacherData->user_email, $subject, $message );
				}
			}
			/* Approved SMS Notification. */
			if ( isset( $_POST['mjschool_student_sms_service_enable'] ) ) {
				$SMSCon                    = get_option( 'mjschool_student_approve_mjschool_content' );
				$SMSArr['{{school_name}}'] = get_option( 'mjschool_name' );
				$message_content           = mjschool_string_replacement( $SMSArr, $SMSCon );
				$type                      = 'Approved';
				$sms                       = mjschool_send_mjschool_notification( wp_unslash($_POST['act_user_id']), $type, $message_content );
			}
		}
		$active_user_id = wp_unslash($_REQUEST['act_user_id']);
		if ( get_user_meta( $active_user_id, 'hash', true ) ) {
			delete_user_meta( $active_user_id, 'hash' );
		}
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&message=7') );
		die();
	}
}
// DEACTIVATE STUDENT FLOW.
if ( isset( $_REQUEST['action'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'deactivate' ) ) {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'deactive_action' ) ) {
		$student_id = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['student_id'] )) );
		$hash       = md5( rand( 0, 1000 ) );
		delete_user_meta( $student_id, 'roll_id' );
		$result = update_user_meta( $student_id, 'hash', $hash );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&message=8' ));
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ----------------- MULTIPLE STUDENT DELETED. ----------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_books' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$childs = get_user_meta( $id, 'parent_id', true );
			if ( ! empty( $childs ) ) {
				foreach ( $childs as $key => $childvalue ) {
					$parents = get_user_meta( $childvalue, 'child', true );
					if ( ! empty( $parents ) ) {
						if ( ( $key = array_search( $id, $parents ) ) !== false ) {
							unset( $parents[ $key ] );
							update_user_meta( $childvalue, 'child', $parents );
						}
					}
				}
			}
			$result = mjschool_delete_usedata( $id );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&tab=studentlist&message=5') );
				die();
			}
		}
	}
}
// -----------Delete Student. -------- //
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$childs = get_user_meta( $student_id, 'parent_id', true );
		if ( ! empty( $childs ) ) {
			foreach ( $childs as $key => $childvalue ) {
				$parents = get_user_meta( $childvalue, 'child', true );
				if ( ! empty( $parents ) ) {
					if ( ( $key = array_search( $student_id, $parents ) ) !== false ) {
						unset( $parents[ $key ] );
						update_user_meta( $childvalue, 'child', $parents );
					}
				}
			}
		}
		$result = mjschool_delete_usedata( $student_id );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&tab=studentlist&message=5' ));
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
switch ( $message ) {
	case '1':
		$message_string = esc_html__( 'Student Added Successfully.', 'mjschool' );
		break;
	case '2':
		$message_string = esc_html__( 'Student Updated Successfully.', 'mjschool' );
		break;
	case '3':
		$message_string = esc_html__( 'Roll No Already Exist.', 'mjschool' );
		break;
	case '4':
		$message_string = esc_html__( 'Student Username Or Emailid Already Exist.', 'mjschool' );
		break;
	case '5':
		$message_string = esc_html__( 'Student Deleted Successfully.', 'mjschool' );
		break;
	case '6':
		$message_string = esc_html__( 'Student CSV Uploaded Successfully .', 'mjschool' );
		break;
	case '7':
		$message_string = esc_html__( 'Student Activated Successfully.', 'mjschool' );
		break;
	case '8':
		$message_string = esc_html__( 'Student Deactivated Successfully.', 'mjschool' );
		break;
	case '16':
		$message_string = esc_html__( 'Student Admission No. Already Exist.', 'mjschool' );
		break;
}
if ( $message ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
		<?php echo esc_html( $message_string ); ?>
	</div>
	<?php
}
?>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!------------ Panel body. ------------>
	<div>
		<?php
		// --------------- STUDENT LIST TAB. ------------//
		if ( $active_tab === 'studentlist' ) {
			
			?>
			<div class="mjschool-popup-bg">
				<div class="mjschool-overlay-content mjschool-max-height-overflow">
					<div class="result"></div>
					<div class="view-parent"></div>
					<div class="mjschool-category-list"></div>
				</div>
			</div>
			<?php
			
			if ( isset( $_REQUEST['filter_class'] ) ) {
				$exlude_id = mjschool_approve_student_list();
				
				if ( empty( $_REQUEST['class_id'] ) && empty( $_REQUEST['class_section'] ) ) {
					// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					$studentdata = get_users( array( 'role' => 'student' ) );
					// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				} elseif ( isset( $_REQUEST['class_section'] ) && sanitize_text_field(wp_unslash($_REQUEST['class_section'])) != '' ) {
					$class_id      = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
					$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
					// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value, WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					$studentdata = get_users(
						array(
							'meta_key'   => 'class_section',
							'meta_value' => $class_section,
							'meta_query' => array(
								array(
									'key'     => 'class_name',
									'value'   => $class_id,
									'compare' => '=',
								),
							),
							'role'       => 'student',
							'exclude'    => $exlude_id,
						)
					);
					// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value, WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				} elseif ( isset( $_REQUEST['class_id'] ) && sanitize_text_field(wp_unslash($_REQUEST['class_section'])) === '' ) {
					$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
					// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					$studentdata = get_users(
						array(
							'meta_key'   => 'class_name',
							'meta_value' => $class_id,
							'role'       => 'student',
							'exclude'    => $exlude_id,
						)
					);
					// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				}
			} else {
				// ------- STUDENT DATA FOR STUDENT. ---------//
				if ( $school_obj->role === 'student' ) {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$user_id       = get_current_user_id();
						$studentdata[] = get_userdata( $user_id );
					} else {
						$studentdata = mjschool_get_users_data( 'student' );
					}
				}
				// ------- STUDENT DATA FOR TEACHER. ---------//
				elseif ( $school_obj->role === 'teacher' ) {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$user_id     = get_current_user_id();
						$class_id    = get_user_meta( $user_id, 'class_name', true );
						$studentdata = $school_obj->mjschool_get_teacher_student_list( $class_id );
					} else {
						$studentdata = mjschool_get_users_data( 'student' );
					}
				}
				// ------- STUDENT DATA FOR PARENT. ---------//
				elseif ( $school_obj->role === 'parent' ) {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$child_data = $school_obj->child_list;
					} else {
						$studentdata = mjschool_get_users_data( 'student' );
					}
				} else {
					$studentdata = array();
					$own_data = $user_access['own_data'];
					$user_id  = get_current_user_id();
					
					if ( $own_data === '1' ) {
						
						// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						$studentdata = get_users(
							array(
								'role'       => 'student',
								'meta_query' => array(
									array(
										'key'     => 'created_by',
										'value'   => $user_id,
										'compare' => '=',
									),
								),
							)
						);
						// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					} else {
						
						$studentdata = mjschool_get_users_data( 'student' );
					}
				}
				
			}
			
			if ( ! empty( $studentdata ) || ! empty( $child_data ) ) {
				?>
				<div class="mjschool-panel-body"><!------------ Panel body. ----------->
					<div class="table-responsive"><!------------ Table responsive. ----------->
						<!----------- Student list form start. ---------->
						<form id="mjschool-common-form" name="mjschool-common-form" method="post">
							<?php wp_nonce_field( 'bulk_delete_books' ); ?>
							<table id="students_list_front" class="display dataTable mjschool-student-datatable" cellspacing="0" width="100%">
								<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
									<tr>
										<?php
										if ( $mjschool_role_name === 'supportstaff' ) {
											?>
											<th class="mjschool-custom-padding-0"><input type="checkbox" class="mjschool-sub-chk select_all" name="select_all"></th>
											<?php
										}
										?>
										<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Student Name & Email', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Student ID', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
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
									if ( ! empty( $studentdata ) ) {
										foreach ( $studentdata as $retrieved_data ) {
											?>
											<tr>
												<?php
												if ( $mjschool_role_name === 'supportstaff' ) {
													?>
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" name="id[]" class="mjschool-sub-chk" value="<?php echo esc_attr( $retrieved_data->id ); ?>"></td>
													<?php
												}
												?>
												<td class="mjschool-user-image mjschool-width-50px-td">
													<a  href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>">
														<?php
														$uid       = $retrieved_data->ID;
														$umetadata = mjschool_get_user_image( $uid );
														if ( empty( $umetadata ) ) {
															echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
														} else {
															echo '<img src=' . esc_url( $umetadata ) . ' class="img-circle" />';
														}
														?>
													</a>
												</td>
												<td class="name">
													<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>"><?php echo esc_html( $retrieved_data->display_name ); ?></a>
													<br>
													<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></span>
												</td>
												<td class="name">
													+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>
													<?php
													if ( ! empty( $retrieved_data->mobile_number ) ) {
														echo esc_html( $retrieved_data->mobile_number );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile No.', 'mjschool' ); ?>"></i>
												</td>
												<td class="name">
													<?php
													$class_id   = get_user_meta( $retrieved_data->ID, 'class_name', true );
													$section_id = get_user_meta( $retrieved_data->ID, 'class_section', true );
													$classname  = mjschool_get_class_section_name_wise( $class_id, $section_id );
													if ( ! empty( $classname ) ) {
														echo esc_html( $classname );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class & Section', 'mjschool' ); ?>"></i>
												</td>
												<td class="roll_no">
													<?php
													if ( get_user_meta( $retrieved_data->ID, 'admission_no', true ) ) {
														echo esc_html( get_user_meta( $retrieved_data->ID, 'admission_no', true ) );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student ID', 'mjschool' ); ?>"></i>
												</td>
												<td class="roll_no">
													<?php
													if ( get_user_meta( $retrieved_data->ID, 'roll_id', true ) ) {
														echo esc_html( get_user_meta( $retrieved_data->ID, 'roll_id', true ) );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Roll No.', 'mjschool' ); ?>"></i>
												</td>
												<td class="gender">
													<?php
														echo esc_html( ucfirst( $retrieved_data->gender ) );
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
												</td>
												<td class="status">
													<?php
													$hash = get_user_meta( $retrieved_data->ID, 'hash', true );
													if ( $hash ) {
														$status = '<span class="mjschool_unpaid_color">' . esc_html__( 'Deactive', 'mjschool' ) . '</span>';
													} else {
														$status = '<span class="mjschool_green_colors">' . esc_html__( 'Active', 'mjschool' ) . '</span>';
													}
													echo wp_kses_post( $status );
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
												</td>
												<?php
												// Custom Field Values.
												if ( ! empty( $user_custom_field ) ) {
													foreach ( $user_custom_field as $custom_field ) {
														if ( $custom_field->show_in_table === '1' ) {
															$module             = 'student';
															$custom_field_id    = $custom_field->id;
															$module_record_id   = $retrieved_data->ID;
															$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
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
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																</a>
																<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?> </a>
																	</li>
																	<?php
																	if ( $school_obj->role === 'student' || $school_obj->role === 'supportstaff' || $school_obj->role === 'teacher' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&action=result&student_id=' . $retrieved_data->ID ); ?>" class="show-popup mjschool-float-left-width-100px" idtest="<?php echo esc_attr( $retrieved_data->ID ); ?>"><i class="fas fa-bar-chart"> </i><?php esc_html_e( 'View Result', 'mjschool' ); ?></a>
																		</li>
																		<?php
																		if ( $school_obj->role === 'supportstaff' ) {
																			$hash = get_user_meta( $retrieved_data->ID, 'hash', true );
																			if ( $hash ) {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a href="#" class="mjschool-float-left-width-100px active-user" idtest="<?php echo esc_attr( $retrieved_data->ID ); ?>">
																						<i class="fas fa-thumbs-up"></i> <?php echo esc_html__( 'Activate', 'mjschool' ); ?>
																					</a>
																				</li>
																				<?php
																			} else {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=studentlist&action=deactivate&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'deactive_action' ) ); ?>" class="mjschool-float-left-width-100px">
																						<i class="fas fa-thumbs-down"></i> <?php echo esc_html__( 'Deactivate', 'mjschool' ); ?>
																					</a>
																				</li>
																				<?php
																			}
																		}
																		if ( $user_access['edit'] === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i> <?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																			<?php
																		}
																		if ( $user_access['delete'] === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=studentlist&action=delete&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i><?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
																			</li>
																			<?php
																		}
																	}
																	?>
																</ul>
															</li>
														</ul>
													</div>
												</td>
											</tr>
											<?php
										}
									}
									if ( ! empty( $child_data ) ) {
										foreach ( $school_obj->child_list as $child_id ) {
											$retrieved_data = get_userdata( $child_id );
											if ( $retrieved_data ) {
												?>
												<tr>
													<?php
													if ( $mjschool_role_name === 'supportstaff' ) {
														?>
														<td class="mjschool-checkbox-width-10px"><input type="checkbox" name="id[]" class="mjschool-sub-chk" value="<?php echo esc_attr( $retrieved_data->id ); ?>"></td>
														<?php
													}
													?>
													<td class="mjschool-user-image mjschool-width-50px-td">
														<a  href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>">
															<?php
															$uid       = $retrieved_data->ID;
															$umetadata = mjschool_get_user_image( $uid );
															if ( empty( $umetadata ) ) {
																echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
															} else {
																echo '<img src=' . esc_url( $umetadata ) . ' class="img-circle" />';
															}
															?>
														</a>
													</td>
													<td class="name">
														<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>"><?php echo esc_html( $retrieved_data->display_name ); ?></a>
														<br>
														<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></span>
													</td>
													<td class="name">
														+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>
														<?php
														if ( ! empty( $retrieved_data->mobile_number ) ) {
															echo esc_html( $retrieved_data->mobile_number );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile No.', 'mjschool' ); ?>"></i>
													</td>
													<td class="name">
														<?php
														$class_id   = get_user_meta( $retrieved_data->ID, 'class_name', true );
														$section_id = get_user_meta( $retrieved_data->ID, 'class_section', true );
														$classname  = mjschool_get_class_section_name_wise( $class_id, $section_id );
														if ( ! empty( $classname ) ) {
															echo esc_html( $classname );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class & Section', 'mjschool' ); ?>"></i>
													</td>
													<td class="admission_no">
														<?php
														if ( get_user_meta( $retrieved_data->ID, 'admission_no', true ) ) {
															echo esc_html( get_user_meta( $retrieved_data->ID, 'admission_no', true ) );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student ID', 'mjschool' ); ?>"></i>
													</td>
													<td class="roll_no">
														<?php
														if ( get_user_meta( $retrieved_data->ID, 'roll_id', true ) ) {
															echo esc_html( get_user_meta( $retrieved_data->ID, 'roll_id', true ) );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Roll No.', 'mjschool' ); ?>"></i>
													</td>
													<td class="gender">
														<?php
														echo esc_html( ucfirst( $retrieved_data->gender ) );
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
													</td>
													<td class="status">
														<?php
														$hash = get_user_meta( $retrieved_data->ID, 'hash', true );
														if ( $hash ) {
															$status = '<span class="mjschool_unpaid_color">' . esc_html__( 'Deactive', 'mjschool' ) . '</span>';
														} else {
															$status = '<span class="mjschool_green_colors" >' . esc_html__( 'Active', 'mjschool' ) . '</span>';
														}
														echo wp_kses_post( $status );
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
													</td>
													<td class="action">
														<div class="mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li >
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?> </a>
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&action=result&student_id=' . $retrieved_data->ID ); ?>" class="show-popup mjschool-float-left-width-100px" idtest="<?php echo esc_attr( $retrieved_data->ID ); ?>"><i class="fas fa-bar-chart"> </i><?php esc_html_e( 'View Result', 'mjschool' ); ?></a>
																		</li>
																		<?php
																		if ( $user_access['edit'] === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i> <?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																			<?php
																		}
																		if ( $user_access['delete'] === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=studentlist&action=delete&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i><?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
											}
										}
									}
									?>
								</tbody>
							</table>
							<!-------- Delete and select all button. ----------->
							<?php
							if ( $mjschool_role_name === 'supportstaff' ) {
								?>
								<div class="mjschool-print-button pull-left">
									<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
										<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>" >
										<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
									</button>
									<?php
									if ( $user_access['delete'] === '1' ) {
										?>
										<button data-toggle="tooltip"  id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
										<?php
									}
									?>
								</div>
								<?php
							}
							?>
							<!-------- Delete and select all button. ----------->
						</form><!----------- Student list form end. ---------->
					</div><!------------ Table responsive. ----------->
				</div><!------------ Panel body. ----------->
				<?php
			} elseif ( $user_access['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
					<a href="<?php echo esc_html( home_url( '?dashboard=mjschool_user&page=student&tab=addstudent' )); ?>">
						<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_html( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
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
		if ( $active_tab == 'view_student' ) {
			$student_id                 = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['student_id']) ) );
			$active_tab1                = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
			$student_data               = get_userdata( $student_id );
			$sibling_information_value  = str_replace( '"[', '[', $student_data->sibling_information );
			$sibling_information_value1 = str_replace( ']"', ']', $sibling_information_value );
			$sibling_information        = json_decode( $sibling_information_value1 );
			$user_meta                  = get_user_meta( $student_id, 'parent_id', true );
			$parent_list                = mjschool_get_student_parent_id( $student_id );
			$mjschool_custom_field_obj  = new Mjschool_Custome_Field();
			// $student_id = $_REQUEST['student_id'];
			?>
			<!-- POP-UP code. -->
			<div class="mjschool-popup-bg">
				<div class="mjschool-overlay-content mjschool-content-width">
					<div class="modal-content d-modal-style">
						<div class="mjschool-task-event-list">
						</div>
					</div>
				</div>
			</div>
			<!-- POP-UP code. -->
			<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start panel body div.-->
				<div class="content-body">
					<!-- Detail Page Header Start. -->
					<section id="mjschool-user-information">
						<div class="mjschool-view-page-header-bg">
							<div class="row">
								<div class="col-xl-10 col-md-9 col-sm-10">
									<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
										<?php
										$umetadata = mjschool_get_user_image( $student_data->ID );
										if ( empty( $umetadata ) ) {
											echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="mjschool-user-view-profile-image" />';
										} else {
											echo '<img src=' . esc_url( $umetadata ) . ' class="mjschool-user-view-profile-image" />';
										}
										?>
										<div class="row mjschool-profile-user-name">
											<div class="mjschool-float-left mjschool-view-top1">
												<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
													<span class="mjschool-view-user-name-label"><?php echo esc_html( $student_data->display_name ); ?></span>
													<div class="mjschool-view-user-edit-btn">
														<?php
														if ( $user_access['edit'] === '1' ) {
															?>
															<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . mjschool_encrypt_id( $student_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-edit.png' ); ?>">
															</a>
															<?php
														}
														?>
													</div>
												</div>
												<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
													<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-phone.png' ); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<span class="mjschool-color-white-rs"><?php echo esc_html( $student_data->mobile_number ); ?></span>
													</div>
												</div>
											</div>
										</div>
										<div class="row mjschool-padding-top-15px-res">
											<div class="col-xl-12 col-md-12 col-sm-12">
												<div class="mjschool-view-top2">
													<div class="row mjschool-view-user-doctor-label">
														<div class="col-md-12 mjschool-address-student-div">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-location.png' ); ?>">&nbsp;&nbsp;<span class="mjschool-address-detail-page"><?php echo esc_html( $student_data->address ); ?></span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2">
									<div class="mjschool-group-thumbs">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-group.png' ); ?>" class="rtl_detail_page_img">
									</div>
								</div>
							</div>
						</div>
					</section>
				<!-- Detail Page Tabing Start. -->
				<section id="body_area" class="body_areas">
					<div class="row">
						<div class="col-xl-12 col-md-12 col-sm-12">
							<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
								<li class="<?php if ( $active_tab1 === 'general' ) { ?>active<?php } ?>">
									<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=general&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>">
									<?php esc_html_e( 'GENERAL', 'mjschool' ); ?></a>
								</li>
									<?php
									$mjschool_role_name = mjschool_get_user_role( get_current_user_id() );
									$page      = 'parent';
									$parent    = mjschool_page_access_role_wise_access_right_dashboard( $page );
									if ( $parent === 1 ) {
										?>
										<li class="<?php if ( $active_tab1 === 'parent' ) { ?>active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=parent&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'parent' ? 'active' : ''; ?>"> <?php esc_html_e( 'Parent List', 'mjschool' ); ?></a>
										</li>
										<?php
									}
									?>
									<li class="<?php if ( $active_tab1 === 'hallticket' ) { ?>active<?php } ?>">
										<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=hallticket&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'hallticket' ? 'active' : ''; ?>"> <?php esc_html_e( 'Hall Ticket', 'mjschool' ); ?></a>
									</li>
									<?php
									if ( $mjschool_role_name === 'student' || $mjschool_role_name === 'teacher' || $mjschool_role_name === 'supportstaff' || $mjschool_role_name === 'parent' ) {
										?>
										<li class="<?php if ( $active_tab1 === 'exam_result' ) { ?>active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=exam_result&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'message' ? 'active' : ''; ?>"> <?php esc_html_e( 'Exam Results', 'mjschool' ); ?></a>
										</li>
										<?php
									}
									$page4    = 'homework';
									$homework = mjschool_page_access_role_wise_access_right_dashboard( $page4 );
									if ( $homework === 1 ) {
										?>
										<li class="<?php if ( $active_tab1 === 'homework' ) { ?>active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=homework&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'homework' ? 'active' : ''; ?>"> <?php esc_html_e( 'HomeWork', 'mjschool' ); ?></a>
										</li>
										<?php
									}
									$page2      = 'attendance';
									$attendance = mjschool_page_access_role_wise_access_right_dashboard( $page2 );
									if ( $attendance === 1 ) {
										?>
										<li class="<?php if ( $active_tab1 === 'attendance' ) { ?>active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=attendance&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'attendance' ? 'active' : ''; ?>"> <?php esc_html_e( 'Attendance', 'mjschool' ); ?></a>
										</li>
										<?php
									}
									$leave_page = 'leave';
									$leave      = mjschool_page_access_role_wise_access_right_dashboard( $leave_page );
									if ( $leave === 1 ) {
										?>
										<li class="<?php if ( $active_tab1 === 'leave_list' ) { ?>active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=leave_list&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'leave_list' ? 'active' : ''; ?>"> <?php esc_html_e( 'Leave', 'mjschool' ); ?></a>
										</li>
										<?php
									}
									$page1       = 'feepayment';
									$feespayment = mjschool_page_access_role_wise_access_right_dashboard( $page1 );
									if ( $feespayment === 1 ) {
										?>
										<li class="<?php if ( $active_tab1 === 'feespayment' ) { ?>active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=feespayment&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'feespayment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Fees Payment', 'mjschool' ); ?></a>
										</li>
										<?php
									}
									$page5   = 'library';
									$library = mjschool_page_access_role_wise_access_right_dashboard( $page5 );
									if ( $library === 1 ) {
										?>
										<li class="<?php if ( $active_tab1 === 'issuebook' ) { ?>active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=issuebook&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'issuebook' ? 'active' : ''; ?>"> <?php esc_html_e( 'Issue Book', 'mjschool' ); ?></a>
										</li>
										<?php
									}
									$page6   = 'message';
									$message = mjschool_page_access_role_wise_access_right_dashboard( $page6 );
									if ( $message === 1 ) {
										if ( $mjschool_role_name === 'student' ) {
											?>
											<li class="<?php if ( $active_tab1 === 'message' ) { ?>active<?php } ?>">
												<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&tab1=message&student_id=' . wp_unslash( $_REQUEST['student_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'message' ? 'active' : ''; ?>"> <?php esc_html_e( 'Messages', 'mjschool' ); ?></a>
											</li>
											<?php
										}
									}

									?>
								</ul>
							</div>
						</div>
					</section>
					<!-- Detail Page Tabbing End. -->
					<!-- Detail Page Body Content Section.  -->
					<section id="mjschool-body-content-area">
						<div class="mjschool-panel-body"><!-- Start panel body div.-->
							<?php
							// general tab start.
							if ( $active_tab1 === 'general' ) {
								?>
								<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
									<div class="col-xl-4 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
										<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br />
										<label class="mjschool-view-page-content-labels"> <?php echo esc_html( $student_data->user_email ); ?> </label>
									</div>
									<div class="col-xl-2 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
										<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Student ID', 'mjschool' ); ?> </label><br />
										<?php
										if ( $user_access['edit'] === '1' && empty( $student_data->admission_no ) ) {
											$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<label class="mjschool-view-page-content-labels">
												<?php
												if ( ! empty( $student_data->admission_no ) ) {
													echo esc_attr( $student_data->admission_no );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</label>
										<?php } ?>
									</div>
									<div class="col-xl-2 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
										<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Roll Number', 'mjschool' ); ?> </label><br />
										<?php
										if ( $user_access['edit'] === '1' && empty( $student_data->roll_id ) ) {
											$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<label class="mjschool-view-page-content-labels"><?php echo esc_html( $student_data->roll_id ); ?></label>
										<?php } ?>
									</div>
									<div class="col-xl-2 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
										<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Class Name', 'mjschool' ); ?> </label><br />
										<?php
										if ( $user_access['edit'] === '1' && empty( $student_data->class_name ) ) {
											$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<label class="mjschool-view-page-content-labels">
												<?php
												$class_name = mjschool_get_class_name( $student_data->class_name );
												if ( $class_name === ' ' ) {
													esc_html_e( 'Not Provided', 'mjschool' );
												} else {
													echo esc_html( $class_name );
												}
												?>
											</label>
										<?php } ?>
									</div>
									<?php
									if ( $school_type === 'school' ) {
										?>
										<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
											<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Section Name', 'mjschool' ); ?> </label><br />
											<?php
											if ( $user_access['edit'] === '1' && empty( $student_data->class_section ) ) {
												$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels">
													<?php
													if ( ! empty( $student_data->class_section ) ) {
														echo esc_html( mjschool_get_section_name( $student_data->class_section ) );
													} else {
														esc_html_e( 'No Section', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
									<?php } ?>
								</div>
								<!-- student Information div start . -->
								<div class="row mjschool-margin-top-20px">
									<div class="col-xl-8 col-md-8 col-sm-12 mjschool-rtl-custom-padding-0px">
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px">
											<div class="mjschool-guardian-div">
												<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Student Information', 'mjschool' ); ?> </label>
												<div class="row">
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Full Name', 'mjschool' ); ?> </label> <br>
														<?php
														if ( $user_access['edit'] === '1' && empty( $student_data->display_name ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-view-page-content-labels"><?php echo esc_html( $student_data->display_name ); ?></label>
														<?php } ?>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?> </label><br>
														<?php
														if ( $user_access['edit'] === '1' && empty( $student_data->alternet_mobile_number ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->alternet_mobile_number ) ) {
																	?>
																	+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;
																	<?php
																	echo esc_html( $student_data->alternet_mobile_number );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														<?php } ?>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br>
														<?php
														if ( $user_access['edit'] === '1' && empty( $student_data->gender ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-view-page-content-labels">
																<?php
																if ( $student_data->gender === 'male' ) {
																	echo esc_attr__( 'Male', 'mjschool' );
																} elseif ( $student_data->gender === 'female' ) {
																	echo esc_attr__( 'Female', 'mjschool' );
																}
																?>
															</label>
														<?php } ?>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br>
														<?php
														$birth_date      = $student_data->birth_date;
														$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
														if ( $user_access['edit'] === '1' && $is_invalid_date ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->birth_date ) ) {
																	echo esc_html( mjschool_get_date_in_input_box( $student_data->birth_date ) );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														<?php } ?>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'City', 'mjschool' ); ?> </label><br>
														<?php
														if ( $user_access['edit'] === '1' && empty( $student_data->city ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-view-page-content-labels"><?php echo esc_html( $student_data->city ); ?></label>
														<?php } ?>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
														<?php
														if ( $user_access['edit'] === '1' && empty( $student_data->state ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->state ) ) {
																	echo esc_html( $student_data->state );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														<?php } ?>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Zipcode', 'mjschool' ); ?> </label><br>
														<label class="mjschool-view-page-content-labels"><?php echo esc_html( $student_data->zip_code ); ?></label>
													</div>
												</div>
												<?php
												if ( ! empty( $student_data->user_document ) ) {
													?>
													<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Document Information', 'mjschool' ); ?> </label>
													<div class="row">
														<?php
														$document_array = json_decode( $student_data->user_document );
														foreach ( $document_array as $key => $value ) {
															?>
															<div class="col-xl-3 col-md-3 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php echo esc_html( $value->document_title ); ?> </label><br>
																<label class="mjschool-label-value">
																	<?php
																	if ( ! empty( $value->document_file ) ) {
																		?>
																		<a target="blank" class="mjschool-status-read btn btn-default mjschool-download-btn-syllebus" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $value->document_file )); ?>" record_id="<?php echo esc_attr( $key ); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a> 
																		<?php
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																</label>
															</div>
															<?php
														}
														?>
													</div>
													<?php
												}
												?>
											</div>
										</div>
										<?php
										$has_sibling = false;
										// First, check if there's at least one valid sibling.
										foreach ( $sibling_information as $sibling ) {
											if ( ! empty( $sibling->siblingsstudent ) ) {
												$has_sibling = true;
												break;
											}
										}
										if ( $has_sibling ) {
											?>
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
												<div class="mjschool-guardian-div">
													<label class="mjschool-view-page-label-heading"><?php esc_html_e( 'Siblings Information', 'mjschool' ); ?></label>
													<?php
													foreach ( $sibling_information as $value ) {
														if ( empty( $value->siblingsstudent ) ) {
															continue;
														}
														$sibling_data = get_userdata( $value->siblingsstudent );
														if ( ! empty( $sibling_data ) ) {
															?>
															<div class="row">
																<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
																	<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Sibling Name', 'mjschool' ); ?></label><br>
																	<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( mjschool_student_display_name_with_roll( $sibling_data->ID ) ); ?></label>
																</div>
																<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
																	<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Sibling Email', 'mjschool' ); ?></label><br>
																	<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $sibling_data->user_email ); ?></label>
																</div>
																<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
																	<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Class', 'mjschool' ); ?></label><br>
																	<label class="mjschool-word-break mjschool-text-style-capitalization mjschool-view-page-content-labels">
																		<?php echo esc_html( mjschool_get_class_section_name_wise( $value->siblingsclass, $value->siblingssection ) ); ?>
																	</label>
																</div>
																<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
																	<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label><br>
																	<label class="mjschool-word-break mjschool-view-page-content-labels">
																		<?php
																		if ( ! empty( $sibling_data->mobile_number ) ) {
																			echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $sibling_data->mobile_number );
																		} else {
																			echo esc_html__( 'Not Provided', 'mjschool' );
																		}
																		?>
																	</label>
																</div>
															</div>
															<?php
														}
													}
													?>
												</div>
											</div>
											<?php
										}
										?>
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
											<div class="mjschool-guardian-div mjschool-parent-information-div-overflow">
												<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Parent Information', 'mjschool' ); ?> </label>
												<?php
												if ( ! empty( $user_meta ) ) {
													foreach ( $user_meta as $parentsdata ) {
														$parent = get_userdata( $parentsdata );
														if ( ! empty( $parent ) ) {
															?>
															<div class="row">
																<div class="col-xl-3 col-md-3 col-sm-12">
																	<p class="mjschool-view-page-header-labels"><?php esc_html_e( 'Name', 'mjschool' ); ?></p>
																	<p class="mjschool-view-page-content-labels"><a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=parent&tab=view_parent&action=view_parent&parent_id=' . mjschool_encrypt_id( $parent->ID ) ); ?>"><?php echo esc_attr( mjschool_get_parent_name_by_id( $parent->ID ) ); ?></a></p>
																</div>
																<div class="col-xl-4 col-md-4 col-sm-12">
																	<p class="mjschool-view-page-header-labels"><?php esc_html_e( 'Email', 'mjschool' ); ?></p>
																	<p class="mjschool-view-page-content-labels"><?php echo esc_html( $parent->user_email ); ?></p>
																</div>
																<div class="col-xl-4 col-md-4 col-sm-12">
																	<p class="mjschool-view-page-header-labels"><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?></p>
																	<p class="mjschool-view-page-content-labels">
																		<?php if ( $parent->mobile_number ) : ?>
																			+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $parent->mobile_number ); ?>
																		<?php else : ?>
																			Not Provided
																		<?php endif; ?>
																	</p>
																</div>
																<div class="col-xl-2 col-md-2 col-sm-12">
																	<p class="mjschool-view-page-header-labels"><?php esc_html_e( 'Relation', 'mjschool' ); ?></p>
																	<p class="mjschool-view-page-content-labels">
																		<?php
																		if ( $parent->relation === 'Father' ) {
																			echo esc_attr__( 'Father', 'mjschool' );
																		} elseif ( $parent->relation === 'Mother' ) {
																			echo esc_attr__( 'Mother', 'mjschool' );
																		}
																		?>
																	</p>
																</div>
															</div>
															<?php
														}
													}
												} else {
													?>
													<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px-rtl mjschool-margin-top-15px mjschool_text_align_center" >
														<p class="mjschool-view-page-content-labels"><?php echo esc_attr__( 'No Any Parent.', 'mjschool' ); ?></p>
													</div>
													<?php
												}
												?>
											</div>
										</div>
										<?php
										$hostel_data = mjschool_student_assign_bed_data_by_student_id( $student_id );
										$room_data   = '';
										if ( ! empty( $hostel_data ) ) {
											$room_data = mjschool_get_room__data_by_room_id( $hostel_data->room_id );
										}
										if ( ! empty( $hostel_data ) ) {
											?>
											<div class="col-xl-12 col-md-12 col-sm-12">
												<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-rtl-custom-padding-0px">
													<div class="mjschool-guardian-div">
														<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Hostel Information', 'mjschool' ); ?> </label>
														<div class="row">
															<div class="col-xl-4 col-md-4 col-sm-12">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Hostel Name', 'mjschool' ); ?> </label> <br>
																<label class="mjschool-view-page-content-labels">
																	<?php
																	if ( ! empty( $hostel_data ) ) {
																		if ( $hostel_data->hostel_id ) {
																			echo esc_html( mjschool_hostel_name_by_id( $hostel_data->hostel_id ) );
																		} else {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		}
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																</label>
															</div>
															<div class="col-xl-4 col-md-4 col-sm-12">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Room Unique ID', 'mjschool' ); ?> </label> <br>
																<label class="mjschool-view-page-content-labels">
																	<?php
																	if ( ! empty( $room_data ) ) {
																		if ( $room_data->room_unique_id ) {
																			echo esc_html( $room_data->room_unique_id );
																		} else {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		}
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																</label>
															</div>
															<div class="col-xl-4 col-md-4 col-sm-12">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Bed Unique ID', 'mjschool' ); ?> </label> <br>
																<label class="mjschool-view-page-content-labels">
																	<?php
																	if ( ! empty( $hostel_data ) ) {
																		if ( $hostel_data->bed_unique_id ) {
																			echo esc_html( $hostel_data->bed_unique_id );
																		} else {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		}
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																</label>
															</div>
															<div class="col-xl-4 col-md-4 col-sm-12">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Bed Charge', 'mjschool' ); ?> </label> <br>
																<label class="mjschool-view-page-content-labels">
																	<?php
																	if ( ! empty( $hostel_data ) ) {
																		if ( $hostel_data->bed_id ) {
																			echo esc_html( mjschool_get_currency_symbol() ) . '' . number_format( mjschool_get_bed_charge_by_id( $hostel_data->bed_id ), 2, '.', '' );
																		} else {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		}
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																</label>
															</div>
															<div class="col-xl-4 col-md-4 col-sm-12">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Bed Assign Date', 'mjschool' ); ?> </label> <br>
																<label class="mjschool-view-page-content-labels">
																	<?php
																	if ( ! empty( $hostel_data ) ) {
																		if ( $hostel_data->assign_date ) {
																			echo esc_html( mjschool_get_date_in_input_box( $hostel_data->assign_date ) );
																		} else {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		}
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																</label>
															</div>
														</div>
													</div>
												</div>
											</div>
											<?php
										}
										$module = 'student';
										$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
										?>
									</div>
									<!-- Fees Payment Card Div Start.  -->
									<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs mjschool_fix_card_rtl">
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-rtl-custom-padding-0px">
											<div class="mjschool-id-page-card mjschool-card-margin-bottom">
												<img class="mjschool-icard-logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
												<div class="mjschool-card-heading mjschool-card-title-position mjschool_70px">
													<label class="mjschool-id-card-label"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </label>
												</div>
												<div class="mjschool-id-card-body">
													<div class="row">
														<div class="col-md-3 col-3 mjschool-id-margin">
															<p class="mjschool-id-card-image">
																<img class="mjschool-id-card-user-image" src="
																<?php
																if ( ! empty( $umetadata ) ) {
																	echo esc_url( $umetadata );
																} else {
																	echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); }
																?>
																">
															</p>
															<p class="mjschool-id-card-image mjschool-card-code">
																<img class="mjschool-id-card-barcode attendance_barcode" id='barcode' src=''>
															</p>
														</div>
														<div class="col-md-9 col-9 mjschool-id-card-info row">
															<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
																<h5 class="mjschool-student-info"><?php esc_html_e( 'Student Name', 'mjschool' ); ?></h5>
															</div>
															<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
																<p class="mjschool-icard-dotes">:&nbsp;</p>
																<h5 class="mjschool-user-info"><?php echo esc_html( $student_data->display_name ); ?></h5>
															</div>
															<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
																<h5 class="mjschool-student-info"><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></h5>
															</div>
															<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
																<p class="mjschool-icard-dotes">:&nbsp;</p>
																<h5 class="mjschool-user-info">
																	<?php
																	if ( ! empty( $student_data->roll_id ) ) {
																		echo esc_html( $student_data->roll_id );
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																</h5>
															</div>
															<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
																<h5 class="mjschool-student-info"><?php esc_html_e( 'Contact No', 'mjschool' ); ?></h5>
															</div>
															<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
																<p class="mjschool-icard-dotes">:&nbsp;</p>
																<h5 class="mjschool-user-info">
																	<label >+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;<?php echo esc_html( $student_data->mobile_number ); ?>
																</h5>
															</div>
															<div class="p-0 col-md-6 col-6">
																<h5 class="mjschool-student-info"><?php esc_html_e( 'Class', 'mjschool' ); ?></h5>
															</div>
															<div class="p-0 col-md-6 col-6">
																<p class="mjschool-icard-dotes">:&nbsp;</p>
																<h5 class="mjschool-user-info">
																	<?php
																	$class_name = mjschool_get_class_section_name_wise( $student_data->class_name, $student_data->class_section );
																	if ( $class_name === ' ' ) {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	} else {
																		echo esc_html( $class_name );
																	}
																	?>
																</h5>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="mjschool-qr-code-card">
												<div class="mjschool-qr-main-div">
													<h3><?php esc_html_e( 'Scan Below QR For Attendance', 'mjschool' ); ?></h3>
													<div class="mjschool-qr-image-div"><img class="mjschool-id-card-barcode qr_width" id='barcode' src=''></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
							} elseif ( $active_tab1 === 'parent' ) {
								if ( ! empty( $user_meta ) ) {
									?>
									<div>
										<div id="Section1" class="mjschool_new_sections">
											<div class="row">
												<div class="col-lg-12">
													<div>
														<div class="card-content">
															<div class="table-responsive">
																<table id="mjschool-parents-list-detail-page-front" class="display table" cellspacing="0" width="100%">
																	<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
																		<tr>
																			<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
																			<th><?php esc_html_e( 'Parent Name & Email', 'mjschool' ); ?></th>
																			<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
																			<th><?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?></th>
																			<th><?php esc_html_e( 'Relation', 'mjschool' ); ?></th>
																			<th><?php esc_html_e( 'Address', 'mjschool' ); ?></th>
																		</tr>
																	</thead>
																	<tbody>
																		<?php
																		if ( ! empty( $user_meta ) ) {
																			foreach ( $user_meta as $parentsdata ) {
																				if ( ! empty( $parentsdata->errors ) ) {
																					$parent = '';
																				} else {
																					$parent = get_userdata( $parentsdata );
																				}
																				if ( ! empty( $parent ) ) {
																					?>
																					<tr>
																						<td class="mjschool-width-50px-td">
																							<?php
																							if ( $parentsdata ) {
																								$umetadata = mjschool_get_user_image( $parentsdata );
																							}
																							if ( empty( $umetadata ) ) {
																								echo '<img src=' . esc_url( get_option( 'mjschool_parent_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
																							} else {
																								echo '<img src=' . esc_url( $umetadata ) . ' height="50px" width="50px" class="img-circle"/>';
																							}
																							?>
																						</td>
																						<td class="name">
																							<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=parent&tab=view_parent&action=view_parent&parent_id=' . mjschool_encrypt_id( $parent->ID ) ); ?>">
																								<?php echo esc_html( mjschool_get_parent_name_by_id( $parent->ID ) ); ?>
																							</a>
																							<br>
																							<span class="mjschool-list-page-email"><?php echo esc_html( $parent->user_email ); ?></span>
																						</td>
																						<td>+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $parent->mobile_number ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i></td>
																						<td>
																							<?php
																							if ( ! empty( $parent->phone ) ) {
																								echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) );
																								?>
																								&nbsp;&nbsp;
																								<?php
																								echo esc_html( $parent->phone );
																							} else {
																								esc_html_e( 'Not Provided', 'mjschool' );
																							}
																							?>
																							<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Alt. Mobile Number', 'mjschool' ); ?>"></i>
																						</td>
																						<td>
																							<?php
																							if ( $parent->relation === 'Father' ) {
																								echo esc_attr__( 'Father', 'mjschool' );
																							} elseif ( $parent->relation === 'Mother' ) {
																								echo esc_attr__( 'Mother', 'mjschool' );
																							}
																							?>
																							<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Relation', 'mjschool' ); ?>"></i>
																						</td>
																						<td>
																							<?php
																							$task_subject = esc_html( $parent->address );
																							$max_length   = 25; // Adjust this value to your desired maximum length.
																							if ( $parent->address ) {
																								if ( strlen( $task_subject ) > $max_length ) {
																									echo esc_html( substr( $task_subject, 0, $max_length ) ) . '...';
																								} else {
																									echo esc_html( $task_subject );
																								}
																							} else {
																								esc_html_e( 'Not Provided', 'mjschool' );
																							}
																							?>
																							<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="
																							<?php
																							if ( $parent->address ) {
																								echo esc_html( $parent->address );
																							} else {
																								echo esc_attr__( 'Address', 'mjschool' ); }
																							?>
																							"></i>
																						</td>
																					</tr>
																					<?php
																				}
																			}
																		}
																		?>
																	</tbody>
																</table>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
								} else {
									$page_1   = 'parent';
									$parent_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
									if ( $parent_1['add'] === '1' ) {
										?>
										<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
											<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=parent&tab=addparent' )); ?>">
												<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
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
							} elseif ( $active_tab1 === 'feespayment' ) {
								$fees_payment = mjschool_get_fees_payment_detailpage( $student_id );
								if ( ! empty( $fees_payment ) ) {
									?>
									<div class="mjschool-popup-bg">
										<div class="mjschool-overlay-content">
											<div class="modal-content">
												<div class=" invoice_data"></div>
												<div class="mjschool-category-list">
												</div>
											</div>
										</div>
									</div>
									<div class="table-div"><!-- Start panel body div.. -->
										<div class="table-responsive"><!-- Table responsive div start. -->
											<table id="mjschool-feespayment-list-detailpage-front" class="display" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Fees Type', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Total Amount', 'mjschool' ); ?> </th>
														<th><?php esc_html_e( 'Paid Amount', 'mjschool' ); ?> </th>
														<th><?php esc_html_e( 'Due Amount', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Payment Status', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Start Year To End Year', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													$i = 0;
													if ( ! empty( $fees_payment ) ) {
														foreach ( $fees_payment as $retrieved_data ) {
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
															?>
															<tr>
																<td class="mjschool-cursor-pointer mjschool-user-image show-view-payment-popup mjschool-width-50px-td mjschool-profile-image-prescription" idtest="<?php echo esc_attr( $retrieved_data->fees_pay_id ); ?>" view_type="view_payment">
																	<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr( $color_class_css ); ?>">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png' ); ?>" class="mjschool-massage-image">
																	</p>
																</td>
																<td class="mjschool-cursor-pointer">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=view_fesspayment&idtest=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&view_type=view_payment' ); ?>">
																		<?php
																		$fees_id   = explode( ',', $retrieved_data->fees_id );
																		$fees_type = array();
																		foreach ( $fees_id as $id ) {
																			$fees_type[] = mjschool_get_fees_term_name( $id );
																		}
																		echo esc_html( implode( ' , ', $fees_type ) );
																		?>
																	</a> 
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Type', 'mjschool' ); ?>"></i>
																</td>
																<td><?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
																<td class="name">
																	<?php
																	if ( $retrieved_data->class_id ) {
																		echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_id, $retrieved_data->section_id ) );
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
																</td>
																<td><?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . number_format( $retrieved_data->total_amount, 2, '.', '' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i></td>
																<td class="department"><?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . number_format( $retrieved_data->fees_paid_amount, 2, '.', '' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Paid Amount', 'mjschool' ); ?>"></i></td>
																<?php
																$Due_amt    = $retrieved_data->total_amount - $retrieved_data->fees_paid_amount;
																$due_amount = number_format( $Due_amt, 2, '.', '' );
																?>
																<td><?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . esc_html( $due_amount ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Due Amount', 'mjschool' ); ?>"></i></td>
																<td>
																	<?php
																	$mjschool_get_payment_status = mjschool_get_payment_status( $retrieved_data->fees_pay_id );
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
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Status', 'mjschool' ); ?>"></i>
																</td>
																<td><?php echo esc_html( $retrieved_data->start_year ) . '-' . esc_html( $retrieved_data->end_year ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Year To End Year', 'mjschool' ); ?>"></i></td>
															</tr>
															<?php
															++$i;
														}
													}
													?>
												</tbody>
											</table>
										</div><!-- Table responsive div end. -->
									</div>
									<?php
								} else {
									$page_1       = 'feepayment';
									$feepayment_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
									if ( $feepayment_1['add'] === '1' ) {
										?>
										<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
											<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=feepayment&tab=addpaymentfee' )); ?>">
												<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
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
							} elseif ( $active_tab1 === 'attendance' ) {
								$attendance_list = mjschool_monthly_attendence( $student_id );
								if ( ! empty( $attendance_list ) ) {
									?>
									<div class="table-div"><!-- Start panel body div.. -->
										<div class="table-responsive"><!-- Table responsive div start. -->
											<table id="mjschool-attendance-list-detail-page" class="display dataTable" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Attendance Date', 'mjschool' ); ?> </th>
														<th><?php esc_html_e( 'Day', 'mjschool' ); ?> </th>
														<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Attendance With QR Code', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													$i    = 0;
													$srno = 1;
													if ( ! empty( $attendance_list ) ) {
														foreach ( $attendance_list as $retrieved_data ) {
															$class_section_sub_name = mjschool_get_class_section_subject( $retrieved_data->class_id, $retrieved_data->section_id, $retrieved_data->sub_id );
															$created_by             = get_userdata( $retrieved_data->attend_by );
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
															?>
															<tr>
																<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance' ); ?>">
																		<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr( $color_class_css ); ?>">
																			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png' ); ?>" class="mjschool-massage-image">
																		</p>
																	</a>
																</td>
																<td class="department"><a href="<?php echo esc_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance' ); ?>"><?php echo esc_attr( mjschool_student_display_name_with_roll( $retrieved_data->user_id ) ); ?></a><i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
																<td >
																	<?php echo wp_kses_post( $class_section_sub_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
																</td>
																<?php
																$curremt_date = esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendance_date ) );
																$day          = date( 'D', strtotime( $curremt_date ) );
																?>
																<td class="name"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendance_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendence Date', 'mjschool' ); ?>"></i></td>
																<td class="department">
																	<?php
																	if ( $day === 'Mon' ) {
																		esc_html_e( 'Monday', 'mjschool' );
																	} elseif ( $day === 'Sun' ) {
																		esc_html_e( 'Sunday', 'mjschool' );
																	} elseif ( $day === 'Tue' ) {
																		esc_html_e( 'Tuesday', 'mjschool' );
																	} elseif ( $day === 'Wed' ) {
																		esc_html_e( 'Wednesday', 'mjschool' );
																	} elseif ( $day === 'Thu' ) {
																		esc_html_e( 'Thursday', 'mjschool' );
																	} elseif ( $day === 'Fri' ) {
																		esc_html_e( 'Friday', 'mjschool' );
																	} elseif ( $day === 'Sat' ) {
																		esc_html_e( 'Saturday', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i></td>
																<td> <?php $status_color = mjschool_attendance_status_color( $retrieved_data->status ); ?>
																	<span style="color:<?php echo esc_attr( $status_color ); ?>;">
																		<?php echo esc_html( $retrieved_data->status ); ?>
																	</span>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
																</td>
																<td class="name">
																	<?php echo esc_html( $created_by->display_name ); ?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
																</td>
																<td class="mjschool-width-20px">
																	<?php
																	if ( $retrieved_data->attendence_type === 'QR' ) {
																		esc_html_e( 'Yes', 'mjschool' );
																	} else {
																		esc_html_e( 'No', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance With QR Code', 'mjschool' ); ?>"></i>
																</td>
																<td class="name">
																		<?php
																		if ( ! empty( $retrieved_data->comment ) ) {
																			$comment       = $retrieved_data->comment;
																			$grade_comment = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
																			echo esc_html( $grade_comment );
																		} else {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		}
																		?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="
																	<?php
																	if ( ! empty( $retrieved_data->comment ) ) {
																		echo esc_html( $retrieved_data->comment );
																	} else {
																		esc_html_e( 'Comment', 'mjschool' );}
																	?>
																	"></i>
																</td>
															</tr>
															<?php
															++$i;
															++$srno;
														}
													}
													?>
												</tbody>
											</table>
										</div><!-- Table responsive div end. -->
									</div>
									<?php
								} else {
									$page_1        = 'attendance';
									$fattendance_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
									if ( $fattendance_1['add'] === '1' ) {
										?>
										<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
											<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance&tab1=subject_attendence' ) ); ?>">
												<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
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
							} elseif ( $active_tab1 === 'leave_list' ) {
								$obj_leave  = new Mjschool_Leave();
								$leave_data = $obj_leave->mjschool_get_single_user_leaves( $student_id );
								if ( ! empty( $leave_data ) ) {
									?>
									<div class="table-responsive"><!-- Table-responsive. -->
										<form id="mjschool-common-form" name="mjschool-common-form" method="post">
											<table id="leave_list_front" class="display mjschool-admin-transport-datatable" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Leave Type', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Leave Duration', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Start Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'End Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Reason', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													$i = 0;
													foreach ( $leave_data as $retrieved_data ) {
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
														?>
														<tr>
															<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
																<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-leave.png' ); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
																</p>
															</td>
															<td>
																<?php
																$sname = mjschool_student_display_name_with_roll( $retrieved_data->student_id );
																if ( $sname != '' ) {
																	echo esc_html( $sname );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
															</td>
															<td class="name">
																<?php
																$class_id   = get_user_meta( $retrieved_data->student_id, 'class_name', true );
																$section_id = get_user_meta( $retrieved_data->student_id, 'class_section', true );
																$classname  = mjschool_get_class_section_name_wise( $class_id, $section_id );
																if ( ! empty( $classname ) ) {
																	echo esc_html( $classname );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class & Section', 'mjschool' ); ?>"></i>
															</td>
															<td><?php echo esc_html( get_the_title( $retrieved_data->leave_type ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Type', 'mjschool' ); ?>"></i></td>
															<td>
																<?php
																$duration = mjschool_leave_duration_label( $retrieved_data->leave_duration );
																echo esc_html( $duration );
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Duration', 'mjschool' ); ?>"></i>
															</td>
															<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Start Date', 'mjschool' ); ?>"></i></td>
															<td>
																<?php
																if ( ! empty( $retrieved_data->end_date ) ) {
																	echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave End Date', 'mjschool' ); ?>"></i>
															</td>
															<td>
																<?php
																$status = $retrieved_data->status;
																if ( $status === 'Approved' ) {
																	echo "<span class='mjschool-green-color'> " . esc_html( $status ) . ' </span>';
																} else {
																	echo "<span class='mjschool-red-color'> " . esc_html( $status ) . ' </span>';
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="
																<?php
																if ( ! empty( $retrieved_data->status_comment ) ) {
																	echo esc_html( $retrieved_data->status_comment );
																} else {
																	esc_html_e( 'Status', 'mjschool' ); }
																?>
																"></i>
															</td>
															<td>
																<?php
																$comment = $retrieved_data->reason;
																$reason  = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
																echo esc_html( $reason );
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="
																<?php
																if ( ! empty( $comment ) ) {
																	echo esc_html( $comment );
																} else {
																	esc_html_e( 'Reason', 'mjschool' ); }
																?>
																"></i>
															</td>
														</tr>
														<?php
														++$i;
													}
													?>
												</tbody>
											</table>
										</form>
									</div><!--------- Table Responsive. ------->
									<?php
								} else {
									$page_1        = 'leave';
									$fattendance_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
									if ( $fattendance_1['add'] === '1' ) {
										?>
										<div class="mjschool-no-data-list-div">
											<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=leave&tab=add_leave' )); ?>">
												<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
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
							} elseif ( $active_tab1 === 'hallticket' ) {
								$hall_ticket = mjschool_hall_ticket_list( $student_id );
								if ( ! empty( $hall_ticket ) ) {
									?>
									<div class="table-div"><!-- Start panel body div.. -->
										<div class="table-responsive"><!-- Table responsive div start. -->
											<table id="mjschool-hall-ticket-detailpage-front" class="display" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Hall Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Exam Term', 'mjschool' ); ?> </th>
														<th><?php esc_html_e( 'Exam Start To End Date', 'mjschool' ); ?> </th>
														<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													$i = 0;
													if ( ! empty( $hall_ticket ) ) {
														foreach ( $hall_ticket as $retrieved_data ) {
															$exam_data  = mjschool_get_exam_by_id( $retrieved_data->exam_id );
															$start_date = $exam_data->exam_start_date;
															$end_date   = $exam_data->exam_end_date;
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
															?>
															<tr>
																<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
																	<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr( $color_class_css ); ?>">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png' ); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
																	</p>
																</td>
																<td><?php echo esc_html( mjschool_get_hall_name( $retrieved_data->hall_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Hall Name', 'mjschool' ); ?>"></i></td>
																<td class="department"><?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->user_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
																<td class="name"><?php echo esc_html( mjschool_get_exam_name_id( $retrieved_data->exam_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Name', 'mjschool' ); ?>"></i></td>
																<td class="department"><?php echo esc_html( get_the_title( $exam_data->exam_term ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Term', 'mjschool' ); ?>"></i></td>
																<td class="department"><?php echo esc_html( mjschool_get_date_in_input_box( $start_date ) ); ?><?php esc_html_e( ' To ', 'mjschool' ); ?><?php echo esc_html( mjschool_get_date_in_input_box( $end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Start To End Date', 'mjschool' ); ?>"></i></td>
																<td class="action">
																	<div class="mjschool-user-dropdown">
																		<ul  class="mjschool_ul_style">
																			<li >
																				<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																				</a>
																				<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																					
																					<?php
																					if ( isset( $_REQUEST['web_type'] ) && wp_unslash($_REQUEST['web_type']) == 'wpschool_app' ) {
																						$pdf_name = $retrieved_data->user_id . '_' . $retrieved_data->exam_id;
																						if ( isset( $_POST['download_app_pdf'] ) ) {
																							$file_path = esc_url(content_url( '/uploads/exam_receipt/' . $pdf_name . '.pdf'));
																							if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
																								unlink( $file_path ); // Delete the file.
																							}
																							$generate_pdf = mjschool_generate_exam_receipt_mobile_app( $retrieved_data->user_id, $retrieved_data->exam_id, $pdf_name );
																							wp_safe_redirect( $file_path );
																							die();
																						}
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<form name="app_pdf1" action="" method="post" class="mjschool-float-left-width-100px">
																								<button type="submit" name="download_app_pdf" class="mjschool-float-left-width-100px mjschool-hall-ticket-pdf-button">
																									<span class="mjschool-hall-ticket-pdf-button-span"><i class="fas fa-print mjschool-hall-ticket-pdf-icon"></i> <?php esc_html_e( 'Hall Ticket PDF', 'mjschool' ); ?></spna>
																								</button>
																							</form>
																						</li>
																						<?php
																					} else {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( '?page=mjschool_student&student_exam_receipt=student_exam_receipt&student_id=' . mjschool_encrypt_id( $retrieved_data->user_id ) . '&exam_id=' . mjschool_encrypt_id( $retrieved_data->exam_id ) ); ?>" target="_blank" class="mjschool-float-left-width-100px"><i class="fas fa-print"> </i><?php esc_html_e( 'Hall Ticket Print', 'mjschool' ); ?> </a>
																						</li>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( '?page=mjschool_student&student_exam_receipt_pdf=student_exam_receipt_pdf&student_id=' . mjschool_encrypt_id( $retrieved_data->user_id ) . '&exam_id=' . mjschool_encrypt_id( $retrieved_data->exam_id ) ); ?>" target="_blank" class="mjschool-float-left-width-100px"><i class="fas fa-print"> </i><?php esc_html_e( 'Hall Ticket PDF', 'mjschool' ); ?></a>
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
													}
													?>
												</tbody>
											</table>
										</div><!-- Table responsive div end. -->
									</div>
									<?php
								} elseif ( $mjschool_role_name != 'student' ) {
									$page_1      = 'exam_hall';
									$exam_hall_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
									if ( $exam_hall_1['add'] === '1' ) {
										?>
										<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
											<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=exam_hall&tab=exam_hall_receipt' )); ?>">
												<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
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
								} else {
									?>
									<div class="mjschool-calendar-event-new">
										<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
									</div>
									<?php
								}
							} elseif ( $active_tab1 === 'homework' ) {
								$student_homework = mjschool_student_homework_detail( $student_id );
								if ( ! empty( $student_homework ) ) {
									?>
									<div class="table-div"><!-- Start panel body div.. -->
										<div class="table-responsive"><!-- Table responsive div start. -->
											<table id="mjschool-homework-detailpage-front" class="display" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Submitted Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Evaluate Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Marks', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													$i = 0;
													if ( ! empty( $student_homework ) ) {
														foreach ( $student_homework as $retrieved_data ) {
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
															?>
															<tr>
																<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
																	<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr( $color_class_css ); ?>">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png' ); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
																	</p>
																</td>
																<td>
																	<?php
																	if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
																		?>
																		<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=upload_homework&action=view&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) . '&student_id=' . mjschool_encrypt_id( $retrieved_data->student_id ) ); ?>">
																		<?php
																	} else {
																		?>
																		<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>">
																		<?php
																	}
																	?>
																		<?php echo esc_html( $retrieved_data->title ); ?>
																	</a> 
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Title', 'mjschool' ); ?>"></i>
																</td>
																<td><?php echo esc_html( mjschool_get_class_name( $retrieved_data->class_name ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i></td>
																<td><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Name', 'mjschool' ); ?>"></i></td>
																<td>
																	<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Homework Date', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submission Date', 'mjschool' ); ?>"></i>
																</td>
																<?php
																if ( $retrieved_data->uploaded_date === 0000 - 00 - 00 ) {
																	?>
																	<td><?php esc_html_e( 'Not Provided', 'mjschool' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td>
																	<?php
																} else {
																	?>
																	<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->uploaded_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td>
																	<?php
																}
																?>
																<td>
																	<?php
																	if ( ! empty( $retrieved_data->evaluate_date ) ) {
																		echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->evaluate_date ) );
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Evaluate Date', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php
																	if ( ! empty( $retrieved_data->marks ) ) {
																		echo esc_html( $retrieved_data->marks );
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php
																	if ( ! empty( $retrieved_data->obtain_marks ) ) {
																		echo esc_html( $retrieved_data->obtain_marks );
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
																</td>
																<?php
																if ( $retrieved_data->status === 1 ) {
																	if ( date( 'Y-m-d', strtotime( $retrieved_data->uploaded_date ) ) <= $retrieved_data->submition_date ) {
																		?>
																		<td>
																			<label class="mjschool-homework-submitted">
																				<?php esc_html_e( 'Submitted', 'mjschool' ); ?>
																			</label>
																			<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
																		</td>
																		<?php
																	} else {
																		?>
																		<td>
																			<label class="mjschool-purpal-color">
																				<?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?>
																			</label>
																			<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
																		</td>
																		<?php
																	}
																} elseif ( $retrieved_data->status === 2 ) {
																	?>
																	<td><label class="mjschool-homework-evaluated"><?php esc_html_e( 'Evaluated', 'mjschool' ); ?></label> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
																	<?php
																} else {
																	?>
																	<td>
																		<label class="mjschool-homework-pending">
																			<?php esc_html_e( 'Pending', 'mjschool' ); ?>
																		</label>
																		<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
																	</td>
																	<?php
																}
																?>
																<td class="action">
																	<div class="mjschool-user-dropdown">
																		<ul  class="mjschool_ul_style">
																			<li >
																				<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																				</a>
																				<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																					<?php
																					$doc_data = json_decode( $retrieved_data->homework_document );
																					if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>" class="mjschool-float-left-width-100px" type="Homework_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																						</li>
																						<?php
																					}
																					if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=upload_homework&action=view&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) . '&student_id=' . mjschool_encrypt_id( $retrieved_data->student_id ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'Upload Homework', 'mjschool' ); ?></a>
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
													}
													?>
												</tbody>
											</table>
										</div><!-- Table responsive div end. -->
									</div>
									<?php
								} else {
									$page_1     = 'homework';
									$homework_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
									if ( $homework_1['add'] === '1' ) {
										?>
										<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
											<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=homework&tab=addhomework') ); ?>">
												<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
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
							} elseif ( $active_tab1 === 'issuebook' ) {
								$student_issuebook = mjschool_student_issuebook_detail( $student_id );
								if ( ! empty( $student_issuebook ) ) {
									?>
									<div class="table-div"><!-- Start panel body div.. -->
										<div class="table-responsive"><!-- Table responsive div start. -->
											<table id="mjschool-issuebook-detailpage-front" class="display" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Book Title', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Issue Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Expected Return Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Time Period', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													$i = 0;
													if ( ! empty( $student_issuebook ) ) {
														foreach ( $student_issuebook as $retrieved_data ) {
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
															?>
															<tr>
																<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
																	<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr( $color_class_css ); ?>">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-library.png' ); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
																	</p>
																</td>
																<td class="department"><?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) ); ?><i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name & Roll No.', 'mjschool' ); ?>"></i></td>
																<td><?php echo esc_html( stripslashes( mjschool_get_book_name( $retrieved_data->book_id ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Book Title', 'mjschool' ); ?>"></i></td>
																<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->issue_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Issue Date', 'mjschool' ); ?>"></i></td>
																<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Expected Return Date', 'mjschool' ); ?>"></i></td>
																<td><?php echo esc_html( get_the_title( $retrieved_data->period ) ); ?><?php echo ' ' . esc_attr__( 'Days', 'mjschool' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Time Period', 'mjschool' ); ?>"></i></td>
															</tr>
															<?php
															++$i;
														}
													}
													?>
												</tbody>
											</table>
										</div><!-- Table responsive div end. -->
									</div>
									<?php
								} else {
									$page_1    = 'library';
									$library_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
									if ( $library_1['add'] === '1' ) {
										?>
										<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
											<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=library&tab=issuebook') ); ?>">
												<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
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
							if ( $active_tab1 === 'exam_result' ) {
								$roles = mjschool_get_user_role( get_current_user_id() );
								?>
								<div class="mjschool-popup-bg">
									<div class="mjschool-overlay-content mjschool-max-height-overflow">
										<div class="modal-content">
											<div class="mjschool-category-list">
											</div>
										</div>
									</div>
								</div>
								<form method="post">
									<div class="row">
										<div class="col-md-3 input mjschool-responsive-months mjschool-dashboard-payment-report-padding">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool_year"><?php esc_html_e( 'Exam Year', 'mjschool' ); ?></label>
										<select id="mjschool_year" name="year" class="mjschool-line-height-30px form-control mjschool-dash-year-load mjschool_heights_47px">
											<?php
											$current_year  = date( 'Y' );
											$min_year      = $current_year - 10;
											$selected_year = isset( $_POST['year'] ) ? intval( wp_unslash($_POST['year']) ) : date( 'Y' );
											for ( $i = $current_year; $i >= $min_year; $i-- ) {
												$year_array[ $i ] = $i;
												$selected         = ( $selected_year === $i ? ' selected' : '' );
												echo '<option value="' . esc_attr( $i ) . '"' . esc_attr( $selected ) . '>' . esc_html( $i ) . '</option>' . "\n";
											}
											?>
										</select>
										</div>
											<div class="col-md-2">        	
											<input type="submit" value="<?php esc_html_e( 'GO', 'mjschool' ); ?>" name="save_latter" class="btn btn-success mjschool-save-btn" />
										</div> 
									</div>
								</form>
								<?php
								$obj_mark    = new Mjschool_Marks_Manage();
								$uid         = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['student_id']) ) );
								$mjschool_user        = get_userdata( $uid );
								$user_meta   = get_user_meta( $uid );
								$total       = 0;
								$grade_point = 0;
								$exam_ids = mjschool_get_manage_marks_exam_id_using_student_id($uid);
								$class_ids = mjschool_get_manage_marks_class_id_using_student_id($uid);
								$subject_ids = mjschool_get_manage_marks_subject_id_using_student_id($uid);
								// Yearly report.
								$all_exam = array();
								if ( ! empty( $exam_ids ) ) {
									$exam_results = mjschool_get_exam_details_by_ids($exam_ids);
									$merge_exam_results = array();
									$class_section_pairs = mjschool_get_class_section_pairs_by_student($uid);
									foreach ( $class_section_pairs as $pair ) {
										$class_id   = intval( $pair->class_id );
										$section_id = intval( $pair->section_id );
										$results = mjschool_get_exam_merge_settings($class_id, $section_id, 'enable');
										if ( ! empty( $results ) ) {
											$merge_exam_results = array_merge( $merge_exam_results, $results );
										}
									}
									// 3. Merge both.
									$all_exam = array_merge( $exam_results, $merge_exam_results );
									$all_exam = array_filter(
										$all_exam,
										function ( $exam ) use ( $selected_year ) {
											if ( $exam->source_table === 'mjschool_exam' ) {
												$exam_year = (int) date( 'Y', strtotime( $exam->exam_start_date ) );
											} else {
												$exam_year = (int) date( 'Y', strtotime( $exam->created_at ) );
											}
											return $exam_year === (int) $selected_year;
										}
									);
								}
								$all_subjects = array();
								if ( ! empty( $class_ids ) ) {
									foreach ( $class_ids as $class_id ) {
										$subjects = $obj_mark->mjschool_student_subject_by_class( $class_id );
										if ( ! empty( $subjects ) ) {
											$all_subjects = array_merge( $all_subjects, $subjects );
										}
									}
								}
								if ( ! empty( $all_exam ) ) {
									?>
									<div class="table-div"><!-- Start panel body div.. -->
										<div class="table-responsive"><!-- Table responsive div start. -->
											<table id="mjschool-messages-detailpage-for-exam-front" class="display" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Start Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'End Date', 'mjschool' ); ?></th>
														<th class="mjschool-exam-exam"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													$i = 0;
													if ( ! empty( $all_exam ) ) {
														foreach ( $all_exam as $retrieved_data ) {
															if ( $retrieved_data->source_table === 'mjschool_exam' ) {
																$exam_id         = $retrieved_data->exam_id;
																$exam_name       = $retrieved_data->exam_name;
																$exam_start_date = mjschool_get_date_in_input_box( $retrieved_data->exam_start_date );
																$exam_end_date   = mjschool_get_date_in_input_box( $retrieved_data->exam_end_date );
																$class_id = mjschool_get_class_id_by_exam_and_student($exam_id, $uid);
																// Get subject list for this class.
																$subjects = $obj_mark->mjschool_student_subject_by_class( $class_id );
															} else {
																$exam_name       = $retrieved_data->merge_name;
																$exam_start_date = 'Not Provided';
																$exam_end_date   = 'Not Provided';
																$class_id        = $retrieved_data->class_id;
																$section_id      = $retrieved_data->section_id;
															}
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
															?>
															<tr>
																<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
																	<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr( $color_class_css ); ?>">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png' ); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
																	</p>
																</td>
																<td class="subject_name">
																	<?php
																	$max_length      = 30;
																	$full_exam_name  = esc_attr( $exam_name );
																	$short_exam_name = ( strlen( $exam_name ) > $max_length ) ? substr( $exam_name, 0, $max_length ) . '...' : $exam_name;
																	?>
																	<label  data-toggle="tooltip" title="<?php echo esc_html( $full_exam_name ); ?>">
																		<?php echo esc_html( $short_exam_name ); ?>
																		<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip"></i>
																	</label>
																</td>
																<td class="department mjschool-width-15px">
																	<label ><?php echo esc_attr( $exam_start_date ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Date', 'mjschool' ); ?>"></i></label>
																</td>
																<td class="department mjschool-width-15px">
																	<label ><?php echo esc_html( $exam_end_date ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'End Date', 'mjschool' ); ?>"></i></label>
																</td>
																<td class="department">
																	<?php
																	if ( $retrieved_data->source_table === 'mjschool_exam' ) {
																		$main_marks = array();
																		foreach ( $subjects as $sub ) {
																			$subject_id   = $sub->subid;
																			$subject_name = $sub->sub_name;
																			// Now call with single class_id, subject_id, exam_id.
																			$new_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $subject_id, $uid );
																			if ( $new_marks != '0' ) {
																				$main_marks[] = $new_marks;
																			}
																		}
																		if ( ! empty( $main_marks ) ) {
																			?>
																			<div class="col-md-12 row mjschool-padding-left-50px mjschool-view-result">
																				<?php
																				if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) == 'wpschool_app' ) {
																					$pdf_name  = $uid . '_' . $exam_id;
																					$file_path = esc_url(content_url( '/uploads/result/' . $pdf_name . '.pdf'));
																					if ( isset( $_POST['download_app_pdf'] ) ) {
																						$file_path = esc_url(content_url( '/uploads/result/' . $pdf_name . '.pdf'));
																						if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
																							unlink( $file_path ); // Delete the file.
																						}
																						$generate_pdf = mjschool_generate_result_for_mobile_app( $uid, $exam_id, $pdf_name, $class_id, $section_id );
																						wp_safe_redirect( $file_path );
																						die();
																					}
																					?>
																					<div class="col-md-2 mjschool-width-50px mjschool-marks-block">
																						<form name="app_pdf2" action="" method="post">
																							<button data-toggle="tooltip" name="download_app_pdf"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-pdf.png' ); ?>"></button>
																						</form>
																					</div>
																					<?php
																				} else {
																					?>
																					<div class="col-md-2 mjschool-width-50px mjschool-marks-block  mjschool_margin_right_15px">
																						<?php
																						if ( $roles != 'parent' && $roles != 'student' ) {
																							?>
																							<a href="#" student_id="<?php echo esc_js( mjschool_encrypt_id( $uid ) ); ?>" class_id="<?php echo esc_js( mjschool_encrypt_id( $class_id ) ); ?>" section_id="<?php echo esc_js( mjschool_encrypt_id( $section_id ) ); ?>" exam_id="<?php echo esc_js( mjschool_encrypt_id( $exam_id ) ); ?>" typeformat="pdf" class="mjschool-float-right show-popup-teacher-details" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-pdf.png' ); ?>"></a>
																						<?php } else { ?>
																							<a href="<?php echo esc_url( '?page=mjschool_student&print=pdf&student=' . mjschool_encrypt_id( $uid ) . '&exam_id=' . mjschool_encrypt_id( $exam_id ) . '&class_id=' . mjschool_encrypt_id( $class_id ) . '&section_id=' . mjschool_encrypt_id( $section_id ) ); ?>" class="mjschool-float-right" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-pdf.png' ); ?>"></a>
																						<?php } ?>
																					</div>
																					<?php
																				}
																				?>
																				<div class="col-md-2 mjschool-width-50px mjschool-rtl-margin-left-20px">
																					<?php
																					if ( $roles != 'parent' && $roles != 'student' ) {
																						?>
																						<a href="#" student_id="<?php echo esc_js( mjschool_encrypt_id( $uid ) ); ?>" class_id="<?php echo esc_js( mjschool_encrypt_id( $class_id ) ); ?>" section_id="<?php echo esc_js( mjschool_encrypt_id( $section_id ) ); ?>" exam_id="<?php echo esc_js( mjschool_encrypt_id( $exam_id ) ); ?>" typeformat="print" class="mjschool-float-right show-popup-teacher-details">
																							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-print.png' ); ?>">
																						</a>
																					<?php } else { ?>
																						<a href="<?php echo esc_url( '?page=mjschool_student&print=print&student=' . mjschool_encrypt_id( $uid ) . '&exam_id=' . mjschool_encrypt_id( $exam_id ) . '&class_id=' . mjschool_encrypt_id( $class_id ) . '&section_id=' . mjschool_encrypt_id( $section_id ) ); ?>" class="mjschool-float-right" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-print.png' ); ?>"></a>
																					<?php } ?>
																				</div>
																			</div>
																			<?php
																		} else {
																			esc_html_e( 'No Result Available.', 'mjschool' );
																		}
																	} else {
																		?>
																		<div class="col-md-12 row mjschool-padding-left-50px  mjschool-view-result">
																			<div class="col-md-2 mjschool-width-50px mjschool-marks-block mjschool_margin_right_15px">
																				<?php
																				if ( $roles != 'parent' && $roles != 'student' ) {
																					?>
																					<a student_id="<?php echo esc_js( mjschool_encrypt_id( $uid ) ); ?>" class_id="<?php echo esc_js( mjschool_encrypt_id( $class_id ) ); ?>" section_id="<?php echo esc_js( mjschool_encrypt_id( $section_id ) ); ?>" merge_id="<?php echo esc_js( mjschool_encrypt_id( $retrieved_data->id ) ); ?>" typeformat="pdf" href="#" class="mjschool-float-right show-popup-teacher-details-marge" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-pdf.png' ); ?>"></a>
																				<?php } else { ?>
																					<a href="<?php echo esc_url( '?page=mjschool_student&print=group_result_pdf&student=' . mjschool_encrypt_id( $uid ) . '&merge_id=' . mjschool_encrypt_id( $retrieved_data->id ) . '&class_id=' . mjschool_encrypt_id( $class_id ) . '&section_id=' . mjschool_encrypt_id( $section_id ) ); ?>" class="mjschool-float-right" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-pdf.png' ); ?>"></a>
																				<?php } ?>
																			</div>
																			<div class="col-md-2 mjschool-width-50px mjschool-rtl-margin-left-20px">
																				<?php
																				if ( $roles != 'parent' && $roles != 'student' ) {
																					?>
																					<a student_id="<?php echo esc_js( mjschool_encrypt_id( $uid ) ); ?>" class_id="<?php echo esc_js( mjschool_encrypt_id( $class_id ) ); ?>" section_id="<?php echo esc_js( mjschool_encrypt_id( $section_id ) ); ?>" merge_id="<?php echo esc_js( mjschool_encrypt_id( $retrieved_data->id ) ); ?>" typeformat="print" href="#" class="mjschool-float-right show-popup-teacher-details-marge" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-print.png' ); ?>"></a>
																				<?php } else { ?>
																					<a href="<?php echo esc_url( '?page=mjschool_student&print=group_result_print&student=' . mjschool_encrypt_id( $uid ) . '&merge_id=' . mjschool_encrypt_id( $retrieved_data->id ) . '&class_id=' . mjschool_encrypt_id( $class_id ) . '&section_id=' . mjschool_encrypt_id( $section_id ) ); ?>" class="mjschool-float-right" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-print.png' ); ?>"></a>
																				<?php } ?>
																			</div>
																		</div>
																		<?php
																	}
																	?>
																</td>
															</tr>
															<?php
															++$i;
														}
													}
													?>
												</tbody>
											</table>
										</div><!-- Table responsive div end. -->
										<div class="mjschool-panel-white mjschool_table_transform_translate" id="printPopupModal">
											<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
												<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
												<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( mjschool_get_user_name_by_id( $uid ) ); ?>'s <?php esc_html_e( 'Result', 'mjschool' ); ?></h4>
											</div>
											<h4>Enter Teacher Comment</h4>
											<textarea id="teacherComment" rows="4" class="mjschool_width_100px" ></textarea>
											<br><br>
											<div class="col-md-12 input mjschool-single-select">
												<label class="ml-1 mjschool-custom-top-label top" for="student_id"><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></label>
												<select name="teacher_id" id="teacher_id" class="form-control mjschool-max-width-100px validate[required]">
													<option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></option>
													<?php mjschool_get_teacher_list_selected( $selected_teacher ); ?>
												</select>
											</div>
											<button onclick="submitPrint()">Print</button>
											<button onclick="closePrintPopup()">Cancel</button>
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
							// Message Tab Start.
							if ( $active_tab1 === 'message' ) {
								$student_message = mjschool_message_detail( $student_id );
								if ( ! empty( $student_message ) ) {
									?>
									<div class="table-div"><!-- Start panel body div.. -->
										<div class="table-responsive"><!-- Table responsive div start. -->
											<table id="mjschool-messages-detailpage-front" class="display" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Sender', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													$i = 0;
													if ( ! empty( $student_message ) ) {
														foreach ( $student_message as $retrieved_data ) {
															$sender_id = $retrieved_data->sender;
															$sender    = mjschool_get_display_name( $sender_id );
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
															?>
															<tr>
																<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
																	<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr( $color_class_css ); ?>">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-message-chat.png' ); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
																	</p>
																</td>
																<td class="subject_name">
																	<label ><?php echo esc_html( $sender ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Sender', 'mjschool' ); ?>"></i></label>
																</td>
																<td class="department">
																	<label ><?php echo esc_html( $retrieved_data->subject ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i></label>
																</td>
																<?php
																$massage     = $retrieved_data->message_body;
																$massage_out = strlen( $massage ) > 30 ? substr( $massage, 0, 30 ) . '...' : $massage;
																?>
																<td class="specialization">
																	<label ><?php echo esc_html( $massage_out ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Description', 'mjschool' ); ?>"></i></label>
																</td>
																<td class="department mjschool-width-15px">
																	<label ><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i></label>
																</td>
															</tr>
															<?php
															++$i;
														}
													}
													?>
												</tbody>
											</table>
										</div><!-- Table responsive div end. -->
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
							// Message Tab End.
							?>
						</div><!-- End Panel body div.-->
					</section>
					<!-- Detail Page Body Content Section End. -->
				</div>
			</div>
			<?php
		}
		if ( $active_tab === 'addstudent' ) {
			$mjschool_role = 'student';
			$edit = 0;
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
				$edit      = 1;
				$user_info = get_userdata( mjschool_decrypt_id( wp_unslash($_REQUEST['student_id']) ) );
			}
			$document_option    = get_option( 'mjschool_upload_document_type' );
			$document_type      = explode( ', ', $document_option );
			$document_type_json = $document_type;
			$document_size      = get_option( 'mjschool_upload_document_size' );
			?>
			<div class="mjschool-panel-body"><!-------- Panel body. ----------->
				<!---------------- STUDENT ADD FORM START. ----------------->
				<form name="mjschool-student-form" action="" method="post" class="mt-3 mjschool-form-horizontal" id="mjschool-student-form" enctype="multipart/form-data">
					<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
					<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
					<input type="hidden" name="role" value="<?php echo esc_attr( $mjschool_role ); ?>" />
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Personal Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"> <!--Form Body div.-->
						<div class="row"><!--Row Div.-->
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="admission_no" class="form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->admission_no ); } elseif ( isset( $_POST['admission_no'] ) ) { echo esc_attr( mjschool_generate_admission_number() ); } else { echo esc_attr( mjschool_generate_admission_number() ); } ?>"  name="admission_no">
										<label for="admission_no"><?php esc_html_e( 'Student ID', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
								<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_list_add_student"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<?php
								if ( $edit ) {
									$classval = $user_info->class_name;
								} elseif ( isset( $_POST['class_name'] ) ) {
									$classval = sanitize_text_field( wp_unslash($_POST['class_name']));
								} else {
									$classval = '';
								}
								?>
								<select name="class_name" class="mjschool-line-height-30px form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="class_list_add_student">
									<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
									<?php
									foreach ( mjschool_get_all_class() as $classdata ) {
										?>
										<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<?php if ( $school_type === 'school' ) { ?>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
									<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-class-section-add-student"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
									<?php
									if ( $edit ) {
										$sectionval = $user_info->class_section;
									} elseif ( isset( $_POST['class_section'] ) ) {
										$sectionval = sanitize_text_field( wp_unslash($_POST['class_section']));
									} else {
										$sectionval = '';
									}
									?>
									<select name="class_section" class="mjschool-line-height-30px form-control mjschool-max-width-100px" id="mjschool-class-section-add-student">
										<option value=""><?php esc_html_e( 'Select Section', 'mjschool' ); ?></option>
										<?php
										if ( $edit ) {
											foreach ( mjschool_get_class_sections( $user_info->class_name ) as $sectiondata ) {
												?>
												<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
												<?php
											}
										}
										?>
									</select>
								</div>
							<?php } ?>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="roll_id" class="form-control validate[required,custom[integer]]" maxlength="10" type="text" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->roll_id ); } elseif ( isset( $_POST['roll_id'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash($_POST['roll_id'])) );} ?>" name="roll_id">
										<label  for="roll_id"><?php esc_html_e( 'Roll Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="first_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->first_name ); } elseif ( isset( $_POST['first_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash($_POST['first_name'])) );} ?>" autocomplete="first_name" name="first_name">
										<label  for="first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="middle_name" class="form-control validate[custom[onlyLetter_specialcharacter]]" maxlength="50" type="text" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->middle_name ); } elseif ( isset( $_POST['middle_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash($_POST['middle_name'])) );} ?>" name="middle_name">
										<label  for="middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="last_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->last_name ); } elseif ( isset( $_POST['last_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['last_name'])) );} ?>" name="last_name">
										<label  for="last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-res-margin-bottom-20px mjschool-rtl-margin-top-15px">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div class="input-group">
												<span class="mjschool-custom-top-label" for="gender"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
												<div class="d-inline-block">
													<?php
													$genderval = 'male';
													if ( $edit ) {
														$genderval = $user_info->gender;
													} elseif ( isset( $_POST['gender'] ) ) {
														$genderval = sanitize_text_field(wp_unslash($_POST['gender']));
													}
													?>
													<label class="radio-inline custom_radio">
														<input type="radio" value="male" class="tog validate[required]" name="gender" <?php checked( 'male', $genderval ); ?> /><?php esc_html_e( 'Male', 'mjschool' ); ?>
													</label>
													<label class="radio-inline custom_radio">
														<input type="radio" value="female" class="tog validate[required]" name="gender" <?php checked( 'female', $genderval ); ?> /><?php esc_html_e( 'Female', 'mjschool' ); ?>
													</label>
													<label class="radio-inline custom_radio">
														<input type="radio" value="other" class="tog validate[required]" name="gender" <?php checked( 'other', $genderval ); ?> /><?php esc_html_e( 'Other', 'mjschool' ); ?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="birth_date" class="form-control date_picker validate[required]" type="text" name="birth_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $user_info->birth_date ) ); } elseif ( isset( $_POST['birth_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['birth_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) );} ?>" readonly>
										<label class="col-form-label date_label text-md-end col-sm-2 control-label" for="birth_date"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Contact Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"> <!--Card Body div-->
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="address" class="form-control validate[required,custom[address_description_validation]]" maxlength="120" type="text" autocomplete="address" name="address" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->address ); } elseif ( isset( $_POST['address'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['address'])) );} ?>">
										<label  for="address"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="city_name" class="form-control validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->city ); } elseif ( isset( $_POST['city_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['city_name'])) );} ?>">
										<label  for="city_name"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="state_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->state ); } elseif ( isset( $_POST['state_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['state_name'])) );} ?>">
										<label  for="state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<?php wp_nonce_field( 'save_student_frontend_nonce' ); ?>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="zip_code" class="form-control validate[required,custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="zip_code" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->zip_code ); } elseif ( isset( $_POST['zip_code'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['zip_code'])) );} ?>">
										<label  for="zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="row">
									<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
										<div class="form-group input mjschool-margin-bottom-0">
											<div class="col-md-12 form-control mjschool-mobile-input">
												<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
												<input id="phonecode" name="phonecode" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
												<input id="mobile_number" class="form-control mjschool-margin-top-10px_res text-input validate[required],minSize[6],maxSize[15]]" type="text" name="mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mobile_number'])) );} ?>">
												<label for="mobile_number" class="mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="required red">*</span></label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="row">
									<div class="col-md-12">
										<div class="form-group input mjschool-margin-bottom-0">
											<div class="col-md-12 form-control mjschool-mobile-input">
												<input id="phonecode" name="alter_mobile_number" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
												<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
												<input id="alternet_mobile_number" class="form-control mjschool-margin-top-10px_res text-input validate[minSize[6],maxSize[15]]" type="text" name="alternet_mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->alternet_mobile_number ); } elseif ( isset( $_POST['alternet_mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['alternet_mobile_number'])) );} ?>">
												<label for="alternet_mobile_number" class="mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Siblings Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12 form-control mjschool-input-height-50px">
										<div class="row mjschool-padding-radio">
											<div class="input-group mjschool-input-checkbox">
												<span class="mjschool-custom-top-label"><?php esc_html_e( 'Siblings', 'mjschool' ); ?></span>
												<div class="checkbox mjschool-checkbox-label-padding-8px">
													<label>
														<input type="checkbox" id="chkIsTeamLead" <?php if ( $edit ) { $sibling_data = $user_info->sibling_information; $sibling      = json_decode( $sibling_data ); if ( ! empty( $user_info->sibling_information ) ) { foreach ( $sibling as $value ) { if ( ! empty( $value->siblingsclass ) && ! empty( $value->siblingsstudent ) ) { ?> checked <?php } } } } ?> />
														&nbsp;&nbsp;<?php esc_html_e( 'In case of any sibling ? click here ', 'mjschool' ); ?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<br>
					<?php
					if ( $edit ) {
						$sibling_data = $user_info->sibling_information;
						$sibling      = json_decode( $sibling_data );
						if ( ! empty( $sibling ) ) {
							$count_array = count( $sibling );
						} else {
							$count_array = 0;
						}
						$i = 1;
						?>
						<div id="mjschool-sibling-div" class="mjschool-sibling-div-none mjschool-sibling-div_clss">
							<?php
							if ( ! empty( $sibling ) ) {
								foreach ( $sibling as $value ) {
									?>
									<script type="text/javascript">
										(function(jQuery){
											"use strict";
											jQuery(document).ready(function(){
												// On class change – load students.
												jQuery(document).on( "change", "#sibling_class_change_<?php echo esc_js( $i ); ?>", function() {
													var selection = jQuery(this).val();
													var $studentList = jQuery( '#sibling_student_list_<?php echo esc_js( $i ); ?>' );
													$studentList.html( '' );
													jQuery.post(mjschool.ajax, {
														action: 'mjschool_load_user',
														class_list: selection,
														nonce: mjschool.nonce,
														dataType: 'json'
													}, function(response){
														$studentList.append(response);
													});
												});
												// On class change – load sections.
												jQuery(document).on( "change", "#sibling_class_change_<?php echo esc_js( $i ); ?>", function(){
													var $sectionSelect = jQuery( '#sibling_class_section_<?php echo esc_js( $i ); ?>' );
													$sectionSelect.html( '' ).append( '<option value="remove">Loading..</option>' );
													var selection = jQuery(this).val();
													jQuery.post(mjschool.ajax, {
														action: 'mjschool_load_class_section',
														class_id: selection,
														nonce: mjschool.nonce,
														dataType: 'json'
													}, function(response){
														$sectionSelect.find( "option[value='remove']").remove();
														$sectionSelect.append(response);
													});
												});
												// On section change – load students.
												jQuery( "#sibling_class_section_<?php echo esc_js( $i ); ?>").on( 'change', function() {
													var selection = jQuery(this).val();
													var class_id = jQuery( "#sibling_class_change_<?php echo esc_js( $i ); ?>").val();
													var $studentList = jQuery( '#sibling_student_list_<?php echo esc_js( $i ); ?>' );
													$studentList.html( '' );
													jQuery.post(mjschool.ajax, {
														action: 'mjschool_load_section_user',
														section_id: selection,
														class_id: class_id,
														nonce: mjschool.nonce,
														dataType: 'json'
													}, function(response){
														$studentList.append(response);
													});
												});
											});
										})(jQuery);
									</script>
									<input type="hidden" id="admission_sibling_id" name="admission_sibling_id" value="<?php echo esc_attr( $count_array ); ?>"  />
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
												<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_change_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px mjschool_45px" id="sibling_class_change_<?php echo esc_attr( $i ); ?>">
													<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
													<?php
													foreach ( mjschool_get_all_class() as $classdata ) {
														?>
														<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $value->siblingsclass, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
														<?php
													}
													?>
												</select>
											</div>
											<?php if ( $school_type === 'school' ) { ?>
												<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
													<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
													<select name="siblingssection[]" class="form-control mjschool-max-width-100px mjschool_45px" id="sibling_class_section_<?php echo esc_attr( $i ); ?>" >
														<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
														<?php
														if ( $edit ) {
															foreach ( mjschool_get_class_sections( $value->siblingsclass ) as $sectiondata ) {
																?>
																<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $value->siblingssection, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
																<?php
															}
														}
														?>
													</select>
												</div>
											<?php } ?>
											<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
												<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Student', 'mjschool' ); ?></label>
												<select name="siblingsstudent[]" id="sibling_student_list_<?php echo esc_attr( $i ); ?>" class="form-control mjschool-max-width-100px mjschool_45px">
													<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
													<?php
													if ( $edit ) {
														if ( mjschool_student_display_name_with_roll( $value->siblingsstudent ) != 'Not Provided' ) {
															echo '<option value="' . esc_attr( $value->siblingsstudent ) . '" ' . selected( $value->siblingsstudent, $value->siblingsstudent ) . '>' . esc_html( mjschool_student_display_name_with_roll( $value->siblingsstudent ) ) . '</option>';
														}
													}
													?>
												</select>
											</div>
											<input type="hidden"  class="click_value" name="" value="<?php echo esc_attr( $count_array + 1 ); ?>">
											<?php
											if ( $i === 1 ) {
												?>
												<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
												</div>
												<?php
											} else {
												?>
												<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
													<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-float-right mjschool-input-btn-height-width">
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<?php
									++$i;
								}
							} else {
								?>
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
											<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-sibling-class-change"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px mjschool_45px" id="mjschool-sibling-class-change">
												<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
												<?php
												foreach ( mjschool_get_all_class() as $classdata ) {
													?>
													<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
													<?php
												}
												?>
											</select>
										</div>
										<?php if ( $school_type === 'school' ) { ?>
											<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
												<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
												<select name="siblingssection[]" class="form-control mjschool-max-width-100px mjschool_45px" id="sibling_class_section" >
													<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
												</select>
											</div>
										<?php } ?>
										<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
											<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="siblingsstudent[]" id="sibling_student_list" class="form-control mjschool-max-width-100px validate[required] mjschool_45px" >
												<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
											</select>
										</div>
										<input type="hidden"  class="click_value" name="" value="1">
										<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
										</div>
									</div>
								</div>
								<?php
							}
							?>
						</div>
					<?php } else { ?>
						<div id="mjschool-sibling-div" class="mjschool-sibling-div_clss">
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
										<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-sibling-class-change"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select name="siblingsclass[]" class="mjschool-line-height-30px form-control validate[required] mjschool-class-in-student mjschool-max-width-100px mjschool_45px" id="mjschool-sibling-class-change" >
											<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
											<?php
											foreach ( mjschool_get_all_class() as $classdata ) {
												?>
												<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<?php if ( $school_type === 'school' ) { ?>
										<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
											<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
											<select name="siblingssection[]" class="mjschool-line-height-30px form-control mjschool-max-width-100px mjschool_45px" id="sibling_class_section" >
												<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
											</select>
										</div>
									<?php } ?>
									<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
										<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select name="siblingsstudent[]" id="sibling_student_list" class="mjschool-line-height-30px form-control mjschool-max-width-100px validate[required] mjschool_45px" >
											<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
										</select>
									</div>
									<input type="hidden" class="click_value" name="" value="1">
									<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Login Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"> <!--Card Body div.-->
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="email" class="form-control validate[required,custom[email]] text-input mjschool-student-email-id" maxlength="100" type="text" autocomplete="email" name="email" 
										<?php
										if ( $edit ) {
											?>value="<?php echo esc_attr( $user_info->user_email ); } elseif ( isset( $_POST['email'] ) ) {echo esc_attr( sanitize_text_field(wp_unslash($_POST['email'])) );}?>">
										<label  for="email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="password" class="form-control <?php if ( ! $edit ) { echo 'validate[required,minSize[8],maxSize[12]]'; } else { echo 'validate[minSize[8],maxSize[12]]'; } ?>" type="password" name="password" autocomplete="current-password">
										<label  for="password">
											<?php esc_html_e( 'Password', 'mjschool' ); ?> <?php
												if ( ! $edit ) {
												?>
												<span class="mjschool-require-field">*</span> <?php } ?> 
										</label>
										<!-- Use class + data-target. -->
										<i class="fas fa-eye-slash togglePassword" data-target="#password"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Profile Image', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
										<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Image', 'mjschool' ); ?></span>
										<div class="col-sm-12 mjschool-display-flex">
											<input type="hidden" id="smgt_user_avatar_url" class="mjschool-image-path-dots form-control" name="smgt_user_avatar" value="
											<?php
											if ( $edit ) {
												echo esc_url( $user_info->smgt_user_avatar );
											} elseif ( isset( $_POST['mjschool_user_avatar'] ) ) {
												echo esc_url( sanitize_text_field(wp_unslash($_POST['mjschool_user_avatar'])) );}
											?>
											" readonly />
											<input id="upload_user_avatar_button" type="file" class="form-control file mjchool_border_0px" onchange="mjschool_file_check(this);" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
										</div>
									</div>
									<div class="clearfix"></div>
									<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
										<div id="mjschool-upload-user-avatar-preview">
											<?php
											if ( $edit ) {
												if ( $user_info->smgt_user_avatar === '' ) {
													?>
													<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>">
													<?php
												} else {
													?>
													<img class="mjschool-image-preview-css" src="<?php if ( $edit ) { echo esc_url( $user_info->smgt_user_avatar );} ?>" />
													<?php
												}
											} else {
												?>
												<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>">
												<?php
											}
											?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- DOCUMENT UPLOAD FIELD START. -->
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Documnt Details', 'mjschool' ); ?></h3>
					</div>
					<div class="mjschool-more-document">
						<?php
						if ( $edit ) {
							// CHECK USER DOCUMENT EXISTS OR NOT.
							if ( ! empty( $user_info->user_document ) ) {
								$document_array = json_decode( $user_info->user_document );
								foreach ( $document_array as $key => $value ) {
									?>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="document_title" class="form-control text-input" maxlength="50" type="text" value="<?php echo esc_attr( $value->document_title ); ?>" name="document_title[]">
														<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div class="col-md-5 col-10 col-sm-1">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
														<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
														<div class="col-sm-12 row">
															<input type="hidden" id="user_hidden_docs" class="mjschool-image-path-dots form-control" name="user_hidden_docs[]" value="<?php echo esc_attr( $value->document_file ); ?>" readonly />
															<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 mt-1">
																<input id="upload_user_avatar_button" name="document_file[]" type="file" class="p-1 form-control mjschool-file-validation file" />
															</div>
															<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 p-0">
																<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $value->document_file )); ?>" record_id="<?php echo esc_attr( $key ); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
															</div>
														</div>
													</div>
												</div>
											</div>
											<?php
											if ( $key === 0 ) {
												?>
												<div class="col-md-1 col-2 col-sm-1 col-xs-12">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
												</div>
												<?php
											} else {
												?>
												<div class="col-md-1 col-2 col-sm-3 col-xs-12 mjschool-width-20px-res">
													<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-float-right mjschool-remove-certificate mjschool-input-btn-height-width">
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<?php
								}
							} else {
								?>
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
											<div class="form-group input">
												<div class="col-md-12 form-control">
													<input id="document_title" class="form-control text-input" maxlength="50" type="text" value="" name="document_title[]">
													<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
												</div>
											</div>
										</div>
										<div class="col-md-5 col-10 col-sm-1">
											<div class="form-group input">
												<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
													<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
													<div class="col-sm-12 mjschool-display-flex">
														<input id="upload_user_avatar_button" name="document_file[]" type="file" class="p-1 form-control mjschool-file-validation file" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-1 col-2 col-sm-1 col-xs-12">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
										</div>
									</div>
								</div>
								<?php
							}
						} else {
							?>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="document_title" class="form-control  text-input" maxlength="50" type="text" value="" name="document_title[]">
												<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-5 col-10 col-sm-1">
										<div class="form-group input">
											<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
												<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
												<div class="col-sm-12 mjschool-display-flex">
													<input id="upload_user_avatar_button" name="document_file[]" type="file" class="p-1 form-control file mjschool-file-validation" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-1 col-2 col-sm-1 col-xs-12">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<?php
					// --------- Get module-wise custom field data. --------------//
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'student';
					$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
					?>
					<!------- Save Student Button. ---------->
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" value="
								<?php
								if ( $edit ) {
									esc_html_e( 'Save Student', 'mjschool' );
								} else {
									esc_html_e( 'Add Student', 'mjschool' ); }
								?>
								" name="save_student" class="btn btn-success mjschool-save-btn" />
							</div>
						</div>
					</div>
				</form>
			</div>
			<?php
		}
		?>
	</div>
</div>