<?php

/**
 * Student Admission Management Page.
 *
 * This file is responsible for managing the student admission lifecycle within the Mjschool
 * dashboard environment. It includes logic for:
 *
 * 1. Displaying the Admission List (admission_list tab).
 * 2. Rendering the Add/Edit Admission Form (addadmission tab).
 * 3. Handling form submissions for creating new student records and editing existing ones.
 * 4. Processing document uploads for parents (father/mother).
 * 5. Implementing role-based access control for 'view', 'edit', 'add', and 'delete' actions.
 * 6. Managing the deletion of single or multiple admission records.
 * 7. Activating (approving) pending admissions, which involves:
 * - Assigning a roll number, class, and section.
 * - Setting a password for the student's account.
 * - Optionally generating and recording an admission fees invoice.
 * - Sending approval notifications via email and SMS to the student and their parents.
 * - Updating the user's role to 'student' and status to 'Approved'.
 * - Updating sibling information.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-admission-popup">
		<div class="modal-content">
			<div class="result"></div>
		</div>
	</div>
</div>
<!-- POP-UP code end. -->
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role_name              = mjschool_get_user_role( get_current_user_id() );
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'admission_list';
$mjschool_obj_admission = new Mjschool_admission();
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
// ------------- Save student admission form. ------------------//
if ( isset( $_POST['student_admission'] ) ) {

    $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

    if ( wp_verify_nonce( $nonce, 'save_mjschool-admission-form' ) ) {

        // Role
        $mjschool_role = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '';

        /*--------------------------------------
        Father Document Upload
        ---------------------------------------*/
        if ( isset( $_FILES['father_doc'] ) && ! empty( $_FILES['father_doc'] ) && $_FILES['father_doc']['size'] > 0 ) {

            $father_document_name = sanitize_text_field( wp_unslash( $_POST['father_document_name'] ) );

            $upload_docs = mjschool_load_documets_new(
                $_FILES['father_doc'],
                $_FILES['father_doc'],
                $father_document_name
            );

        } else {
            $upload_docs = '';
        }

        $father_document_data = array();
        if ( ! empty( $upload_docs ) ) {
            $father_document_data[] = array(
                'title' => sanitize_text_field( wp_unslash( $_POST['father_document_name'] ) ),
                'value' => $upload_docs,
            );
        } else {
            $father_document_data[] = '';
        }

        /*--------------------------------------
        Mother Document Upload
        ---------------------------------------*/
        if ( isset( $_FILES['mother_doc'] ) && ! empty( $_FILES['mother_doc'] ) && $_FILES['mother_doc']['size'] > 0 ) {

            $mother_document_name = sanitize_text_field( wp_unslash( $_POST['mother_document_name'] ) );

            $upload_docs1 = mjschool_load_documets_new(
                $_FILES['mother_doc'],
                $_FILES['mother_doc'],
                $mother_document_name
            );

        } else {
            $upload_docs1 = '';
        }

        $mother_document_data = array();
        if ( ! empty( $upload_docs1 ) ) {
            $mother_document_data[] = array(
                'title' => sanitize_text_field( wp_unslash( $_POST['mother_document_name'] ) ),
                'value' => $upload_docs1,
            );
        } else {
            $mother_document_data[] = '';
        }

        /*--------------------------------------
        EDIT MODE
        ---------------------------------------*/
        $action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

        if ( $action === 'edit' ) {

            $nonce_action = isset( $_GET['_wpnonce_action'] )
                ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) )
                : '';

            if ( wp_verify_nonce( $nonce_action, 'edit_action' ) ) {

                $result = $mjschool_obj_admission->mjschool_add_admission( wp_unslash( $_POST ), $father_document_data, $mother_document_data, $mjschool_role );

                // Custom fields
                $mjschool_custom_field_obj = new Mjschool_Custome_Field();
                $module = 'admission';
                $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $result );

                if ( $result ) {
                    wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=admission&tab=admission_list&message=9' ) );
                    exit;
                }

            } else {
                wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
            }

        } else {

            /*--------------------------------------
            Email validation (sanitized)
            ---------------------------------------*/
            $email        = sanitize_email( wp_unslash( $_POST['email'] ) );
            $father_email = sanitize_email( wp_unslash( $_POST['father_email'] ) );
            $mother_email = sanitize_email( wp_unslash( $_POST['mother_email'] ) );

            if ( email_exists( $email ) ) {
                wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&message=2' ) );
                exit;
            }

            if ( email_exists( $father_email ) ) {
                wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&message=3' ) );
                exit;
            }

            if ( email_exists( $mother_email ) ) {
                wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&message=4' ) );
                exit;
            }

            /*--------------------------------------
            ADD MODE
            ---------------------------------------*/
            $result = $mjschool_obj_admission->mjschool_add_admission( wp_unslash( $_POST ), $father_document_data, $mother_document_data, $mjschool_role );

            $mjschool_custom_field_obj = new Mjschool_Custome_Field();
            $module = 'admission';
            $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );

            if ( $result ) {
                wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=admission&tab=admission_list&message=1' ) );
                exit;
            }
        }
    }
}
// ------------- Delete admission.  ------------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_usedata( intval( sanitize_text_field( wp_unslash( $id ) ) ) );
		}
	}
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=admission&tab=admission_list&message=8' ) );
		die();
	}
}
// -----------Delete code. --------
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'delete_action' ) ) {
		$result = mjschool_delete_usedata( intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=admission&tab=admission_list&message=8' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ------------ Active admission. ------------//
if ( isset( $_POST['active_user_admission'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_active_student_admission_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	
	$class_name = isset( $_POST['class_name'] ) 
		? intval( wp_unslash( $_POST['class_name'] ) ) 
		: 0;

	$roll_id = isset( $_POST['roll_id'] ) 
		? mjschool_strip_tags_and_stripslashes( sanitize_text_field( wp_unslash( $_POST['roll_id'] ) ) ) 
		: '';
		
	$userbyroll_no = get_users(
		array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => 'class_name',
					'value' => $class_name,
				),
				array(
					'key'   => 'roll_id',
					'value' => $roll_id,
				),
			),
			'role' => 'student',
		)
	);
	
	$is_rollno = count( $userbyroll_no );
	if ( $is_rollno ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=admission&tab=admission_list&message=6' ) );
		die();
	} else {

	$active_user_id = isset($_REQUEST['act_user_id'])
		? intval( wp_unslash( $_REQUEST['act_user_id'] ) )
		: 0;

	$roll_id = isset($_REQUEST['roll_id'])
		? sanitize_text_field( wp_unslash( $_REQUEST['roll_id'] ) )
		: '';

	$class_name = isset($_REQUEST['class_name'])
		? intval( wp_unslash( $_REQUEST['class_name'] ) )
		: 0;

	$class_section = isset($_REQUEST['class_section'])
		? intval( wp_unslash( $_REQUEST['class_section'] ) )
		: 0;

	$email_req = isset($_REQUEST['email'])
		? sanitize_email( wp_unslash( $_REQUEST['email'] ) )
		: '';

	$password_req = isset($_REQUEST['password'])
		? sanitize_text_field( wp_unslash( $_REQUEST['password'] ) )
		: '';

	update_user_meta( $active_user_id, 'roll_id', $roll_id );
	update_user_meta( $active_user_id, 'class_name', $class_name );
	update_user_meta( $active_user_id, 'class_section', $class_section );

	if ( email_exists( $email_req ) ) {
		if ( ! empty( $password_req ) ) {
			wp_set_password( $password_req, $active_user_id );
		}
	}

	if ( get_option( 'mjschool_combine' ) == 1 && get_option( 'mjschool_admission_fees' ) === 'yes' ) {

		$admission_fees_id = isset($_REQUEST['admission_fees'])
			? intval( wp_unslash( $_REQUEST['admission_fees'] ) )
			: 0;

		$mjschool_obj_fees     = new Mjschool_Fees();
		$admission_fees_amount = $mjschool_obj_fees->mjschool_get_single_feetype_data_amount( $admission_fees_id );

		$generated = mjschool_generate_admission_fees_invoice(
			$admission_fees_amount,
			$active_user_id,
			$admission_fees_id,
			$class_ids,
			0,
			'Admission Fees'
		);
	}

	$user_info = get_userdata( intval( wp_unslash( $_POST['act_user_id'] ) ) );

	if ( ! empty( $user_info ) ) {


		if ( isset($_POST['student_approve_mail']) && intval( wp_unslash($_POST['student_approve_mail']) ) === 1 ) {

			$string = array(
				'{{user_name}}'   => $user_info->display_name,
				'{{school_name}}' => get_option( 'mjschool_name' ),
				'{{role}}'        => 'student',
				'{{login_link}}'  => site_url() . '/index.php/mjschool-login-page',
				'{{username}}'    => $user_info->user_login,
				'{{class_name}}'  => mjschool_get_class_section_name_wise( $class_name, $class_section ),
				'{{roll_no}}'     => $roll_id,
				'{{email}}'       => $user_info->user_email,
				'{{Password}}'    => $password_req,
			);

			$MsgContent = get_option( 'mjschool_add_approve_admission_mail_content' );
			$MsgSubject = get_option( 'mjschool_add_approve_admisson_mail_subject' );

			$message    = mjschool_string_replacement( $string, $MsgContent );
			$subject    = mjschool_string_replacement( $string, $MsgSubject );

			$email      = $user_info->user_email;

			if ( get_option('mjschool_combine') == 1 &&
				get_option('mjschool_admission_fees') === 'yes' &&
				get_option('mjschool_mail_notification') == 1
			) {
				mjschool_send_mail_paid_invoice_pdf(
					$email,
					get_option( 'mjschool_fee_payment_title' ),
					$message,
					$generated
				);
			} else {
				mjschool_send_mail( $email, $subject, $message );
			}

			if ( ! empty( $user_info->father_email ) && ! empty( $user_info->father_first_name ) ) {

				$string_parent = array(
					'{{parent_name}}'  => trim( $user_info->father_first_name . ' ' . $user_info->father_middle_name . ' ' . $user_info->father_last_name ),
					'{{student_name}}' => $user_info->display_name,
					'{{school_name}}'  => get_option( 'mjschool_name' ),
					'{{role}}'         => 'student',
					'{{login_link}}'   => site_url() . '/index.php/mjschool-login-page',
					'{{username}}'     => $user_info->user_login,
					'{{class_name}}'   => mjschool_get_class_section_name_wise( $class_name, $class_section ),
					'{{roll_no}}'      => $roll_id,
					'{{email}}'        => $user_info->user_email,
					'{{Password}}'     => $password_req,
				);

				$MsgContent_parent = get_option( 'mjschool_admission_mailtemplate_content_for_parent' );
				$MsgSubject_parent = get_option( 'mjschool_admissiion_approve_subject_for_parent' );

				$message_parent = mjschool_string_replacement( $string_parent, $MsgContent_parent );
				$subject_parent = mjschool_string_replacement( $string_parent, $MsgSubject_parent );

				$email_parent = $user_info->father_email;

				if ( get_option('mjschool_combine') == 1 &&
					get_option('mjschool_admission_fees') === 'yes' &&
					get_option('mjschool_mail_notification') == 1
				) {
					mjschool_send_mail_paid_invoice_pdf(
						$email_parent,
						get_option( 'mjschool_fee_payment_title' ),
						$message_parent,
						$generated
					);
				} else {
					mjschool_send_mail( $email_parent, $subject_parent, $message_parent );
				}
			}

			if ( ! empty( $user_info->mother_email ) && ! empty( $user_info->mother_first_name ) ) {

				$string_parent = array(
					'{{parent_name}}'  => trim( $user_info->mother_first_name . ' ' . $user_info->mother_middle_name . ' ' . $user_info->mother_last_name ),
					'{{student_name}}' => $user_info->display_name,
					'{{school_name}}'  => get_option( 'mjschool_name' ),
					'{{role}}'         => 'student',
					'{{login_link}}'   => site_url() . '/index.php/mjschool-login-page',
					'{{username}}'     => $user_info->user_login,
					'{{class_name}}'   => mjschool_get_class_section_name_wise( $class_name, $class_section ),
					'{{roll_no}}'      => $roll_id,
					'{{email}}'        => $user_info->user_email,
					'{{Password}}'     => $password_req,
				);

				$MsgContent_parent = get_option( 'mjschool_admission_mailtemplate_content_for_parent' );
				$MsgSubject_parent = get_option( 'mjschool_admissiion_approve_subject_for_parent' );

				$message_parent = mjschool_string_replacement( $string_parent, $MsgContent_parent );
				$subject_parent = mjschool_string_replacement( $string_parent, $MsgSubject_parent );

				$email_parent = $user_info->mother_email;

				if ( get_option('mjschool_combine') == 1 &&
					get_option('mjschool_admission_fees') === 'yes' &&
					get_option('mjschool_mail_notification') == 1
				) {
					mjschool_send_mail_paid_invoice_pdf(
						$email_parent,
						get_option( 'mjschool_fee_payment_title' ),
						$message_parent,
						$generated
					);
				} else {
					mjschool_send_mail( $email_parent, $subject_parent, $message_parent );
				}
			}
		}

		if ( isset($_POST['student_approve_sms']) && intval( wp_unslash( $_POST['student_approve_sms'] ) ) === 1 ) {
			$SMSCon = get_option( 'mjschool_student_admission_approve_mjschool_content' );

			$SMSArr = array(
				'{{student_name}}' => $user_info->display_name,
				'{{school_name}}'  => get_option( 'mjschool_name' ),
			);

			$message_content = mjschool_string_replacement( $SMSArr, $SMSCon );
			mjschool_send_mjschool_notification( $user_info->ID, 'Approved', $message_content );
		}
	}

	(new WP_User( $active_user_id ))->set_role( 'student' );

	update_user_meta( $active_user_id, 'role', 'student' );
	update_user_meta( $active_user_id, 'status', 'Approved' );

	$sibling_data = $user_info->sibling_information;
	update_user_meta( $active_user_id, 'sibling_information', $sibling_data );

	if ( ! empty( $sibling_data ) ) {
		$sibling_data_array = json_decode( $sibling_data, true );
		if ( is_array( $sibling_data_array ) ) {
			foreach ( $sibling_data_array as $sibling_entry ) {

				$sibling_id = intval( $sibling_entry['siblingsstudent'] );

				if ( $sibling_id > 0 && $sibling_id != $active_user_id ) {

					$existing_sibling_info = get_user_meta( $sibling_id, 'sibling_information', true );
					$existing_sibling_array = $existing_sibling_info ? json_decode( $existing_sibling_info, true ) : array();

					$already_exists = false;
					foreach ( $existing_sibling_array as $info ) {
						if ( isset( $info['siblingsstudent'] ) && $info['siblingsstudent'] == $active_user_id ) {
							$already_exists = true;
							break;
						}
					}

					if ( ! $already_exists ) {
						$existing_sibling_array[] = array(
							'siblingsclass'   => $class_name,
							'siblingssection' => $class_section,
							'siblingsstudent' => $active_user_id,
						);

						update_user_meta( $sibling_id, 'sibling_information', json_encode( $existing_sibling_array ) );
					}
				}
			}
		}
	}

	$mjschool_obj_admission->mjschool_add_parent( $active_user_id, 'parent' );

	if ( get_user_meta( $active_user_id, 'hash', true ) ) {
		delete_user_meta( $active_user_id, 'hash' );
	}

	wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=student&message=7' ) );
	die();
}
}
if ( isset( $_REQUEST['message'] ) ) {
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
	
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Admission Added Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Student Email-id Already Exist.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Father Email-id Already Exist.', 'mjschool' );
			break;
		case '4':
			$message_string = esc_html__( 'Mother Email-id Already Exist.', 'mjschool' );
			break;
		case '5':
			$message_string = esc_html__( 'Student Admission Added Successfully.', 'mjschool' );
			break;
		case '6':
			$message_string = esc_html__( 'Student Roll No. Already Exist.', 'mjschool' );
			break;
		case '7':
			$message_string = esc_html__( 'Student Record Approved Successfully.', 'mjschool' );
			break;
		case '8':
			$message_string = esc_html__( 'Student Admission Deleted Successfully.', 'mjschool' );
			break;
		case '9':
			$message_string = esc_html__( 'Admission Updated Successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
			</button>
			<p><?php echo esc_html( $message_string ); ?></p>
		</div>
		<?php
	}
}
?>
<!-- Nav tabs. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<!-- Tab panes. -->
	<?php
	$mjschool_custom_field_obj = new Mjschool_Custome_Field();
	$module                    = 'admission';
	$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
	// ---------------- Admission list tab.  -----------------//
	if ( $active_tab === 'admission_list' ) {
		if ( $school_obj->role === 'supportstaff' || $school_obj->role === 'teacher' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$user_id = get_current_user_id();
				$studentdata = get_users(
					array(
						'role' => 'student_temp',
						'meta_query' => array(
							array(
								'key' => 'created_by',
								'value' => $user_id,
								'compare' => '='
							)
						)
					)
				);
			} else {
				$studentdata = get_users( array( 'role' => 'student_temp' ) );
			}
		} else {
			$studentdata = get_users( array( 'role' => 'student_temp' ) );
		}
		if ( ! empty( $studentdata ) ) {
			?>
			<div class="mjschool-panel-body"><!--------- Panel body div. --------->
				<div class="table-responsive"><!---------Table responsive div. --------->
					<!----------- Admission list form start. ---------->
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<table id="mjschool-admission-list-front" class="display admin_student_datatable display" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $mjschool_role_name === 'supportstaff' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
										<?php
									}
									?>
									<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Name & Email', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Admission No.', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Admission Date', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
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
										$admission_id = mjschool_encrypt_id( $retrieved_data->ID );
										$user_info    = get_userdata( $retrieved_data->ID );
										?>
										<tr>
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<td class="mjschool-checkbox-width-10px"><input type="checkbox" name="id[]" class="mjschool-sub-chk select-checkbox" value="<?php echo esc_attr( $retrieved_data->ID ); ?>"></td>
												<?php
											}
											?>
											<td class="mjschool-user-image mjschool-width-50px-td">
												<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=admission&tab=view_admission&action=view_admission&id=' . esc_attr( $admission_id ) ); ?>">
													<?php
													$uid = $retrieved_data->ID;
													$umetadata = mjschool_get_user_image($uid);
													if (empty($umetadata ) ) {
														echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
													} else {
														echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
													}
													?>
												</a>
											</td>
											<td class="name">
												<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=admission&tab=view_admission&action=view_admission&id=' . esc_attr( $admission_id ) ); ?>"><?php echo esc_attr($retrieved_data->first_name) . ' ' . esc_html( $retrieved_data->middle_name) . ' ' . esc_html( $retrieved_data->last_name); ?></a><br>
												<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email); ?></span>
											</td>
											<td >
												+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>
												<?php echo esc_html( $user_info->mobile_number); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile No.', 'mjschool' ); ?>"></i>
											</td>
											<td >
												<?php echo esc_html( $user_info->admission_no); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Admission No.', 'mjschool' ); ?>"></i>
											</td>
											<td >
												<?php echo esc_html( mjschool_get_date_in_input_box($user_info->admission_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Admission Date', 'mjschool' ); ?>"></i>
											</td>
											<td >
												<?php echo esc_html( ucfirst($user_info->gender ) ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
											</td>
											<td >
												<?php echo esc_html( mjschool_get_date_in_input_box($user_info->birth_date ) ); ?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date of Birth', 'mjschool' ); ?>"></i>
											</td>
											<td >
												<span class="mjschool-not-approved"><?php if ( ! empty( $user_info->status ) ) { echo esc_html( $user_info->status); } else { esc_html_e( 'Not Approved', 'mjschool' ); } ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></span>
											</td>
											<?php
											// Custom Field Values.
											if ( ! empty( $user_custom_field ) ) {
												foreach ($user_custom_field as $custom_field) {
													if ($custom_field->show_in_table === "1") {
														$module = 'admission';
														$custom_field_id = $custom_field->id;
														$module_record_id = $retrieved_data->ID;
														$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value($module, $module_record_id, $custom_field_id);
														if ($custom_field->field_type === 'date' ) {
															?>
															<td>
																<?php if ( ! empty( $custom_field_value ) ) {
																	echo esc_html( mjschool_get_date_in_input_box($custom_field_value ) );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																} ?>
															</td>
															<?php
														} elseif ($custom_field->field_type === 'file' ) {
															?>
															<td>
																<?php
																if ( ! empty( $custom_field_value ) ) {
																	?>
																	<a target="" href="<?php echo esc_url(content_url( '/uploads/school_assets/' . $custom_field_value)); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
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
																<?php if ( ! empty( $custom_field_value ) ) {
																	echo esc_html( $custom_field_value);
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																} ?> 
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
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=admission&tab=view_admission&action=view_admission&id=' . esc_attr( $admission_id ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																</li>
																<?php
																if ($user_info->role === "student_temp" and $user_access['add'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=admission&tab=view_admission&action=view_admission&id=' . esc_attr( $admission_id ) ); ?>" class="mjschool-float-left-width-100px show-admission-popup" student_id="<?php echo esc_attr($retrieved_data->ID); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-admission-approve.png"); ?>" class="mjschool_height_15px">&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Approve', 'mjschool' ); ?></a>
																	</li>
																	<?php 
																}
																if ( $user_access['edit'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&action=edit&student_id=' . esc_attr( $admission_id ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"> <i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
																		</a>
																	</li>
																	<?php
																}
																if ( $user_access['delete'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=admission&tab=admission_list&action=delete&student_id=' . esc_attr( $admission_id ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
								?>
							</tbody>
						</table>
						<?php
						if ( $mjschool_role_name === 'supportstaff' ) {
							?>
							<div class="mjschool-print-button pull-left">
								<button class="mjschool-btn-sms-color mjschool-button-reload">
									<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
									<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
								</button>
								<?php
								if ( $user_access['delete'] === '1' ) {
									 ?>
									<button data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									<?php
								} ?>
							</div>
							<?php
						}
						?>
					</form><!----------- Admission list form end. ---------->
				</div><!---------Table responsive div. --------->
			</div><!--------- Panel body div. --------->
			<?php
		} else {
			if ($user_access['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission') ); ?>">
						<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
					</a>
					<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
						<label class="mjschool-no-data-list-label">
							<?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?>
						</label>
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
	//----------- Admission view page tab.  ----------------//
	if ($active_tab === 'view_admission' ) {
		$admission_id = intval(mjschool_decrypt_id( wp_unslash($_REQUEST['id']) ) );
		$active_tab1 = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'general';
		$student_data = get_userdata($admission_id);
		$user_meta = get_user_meta($admission_id, 'parent_id', true);
		$mjschool_custom_field_obj = new mjschool_custome_field;
		$sibling_information_value = str_replace( '"[', '[', $student_data->sibling_information);
		$sibling_information_value1 = str_replace( ']"', ']', $sibling_information_value);
		$sibling_information = json_decode($sibling_information_value1);
		?>
		<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start panel body div.-->
			<div class="content-body">
				<!-- Detail page header start. -->
				<section id="mjschool-user-information" class="mjschool-view-page-header-bg">
					<div class="mjschool-view-page-header-bg">
						<div class="row">
							<div class="col-xl-10 col-md-9 col-sm-10">
								<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
									<?php
									$umetadata = mjschool_get_user_image($student_data->ID);
									if (empty($umetadata ) ) {
										echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="mjschool-user-view-profile-image" />';
									} else {
										echo '<img src=' . esc_url($umetadata) . ' class="mjschool-user-view-profile-image" />';
									}
									?>
									<div class="row mjschool-profile-user-name">
										<div class="mjschool-float-left mjschool-view-top1">
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
												<label class="mjschool-view-user-name-label"><?php echo esc_html( $student_data->display_name); ?></label>
												<div class="mjschool-view-user-edit-btn">
													<?php
													if ($user_access['edit'] === '1' ) {
														?>
														<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
														</a>
													<?php } ?>
													<a class="mjschool-color-white mjschool-margin-left-2px show-admission-popup" href="<?php echo esc_url( '?dashboard=mjschool_user&page=mjsmgt_admission&tab=admission_list&action=approve&id=' . esc_attr( $student_data->ID ) ); ?>" student_id="<?php echo esc_attr( $student_data->ID ); ?>">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-approve.png"); ?>">
													</a>
												</div>
												<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
													<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;
														<label> <?php echo esc_html( $student_data->mobile_number); ?></label>
													</div>
												</div>
											</div>
										</div>
										<div id="mjschool-res-add-width" class="row">
											<div class="col-xl-12 col-md-12 col-sm-12">
												<div class="mjschool-view-top2">
													<div class="row mjschool-view-user-doctor-label">
														<div class="col-md-12 mjschool-address-student-div">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;
															<label class="mjschool-address-detail-page"> <?php echo esc_html( $student_data->address); ?></label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-2 col-md-3 col-sm-2 mjschool-group-thumbs">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>" class="mjschool-group-img-rtl">
							</div>
							
						</div>
					</div>
				</section>
				<!-- Detail page header end. -->
				<!-- Detail page body content section.  -->
				<section id="body_area" class="body_areas">
					<div class="mjschool-panel-body"><!-- Start panel body div.-->
						<?php
						// General tab start.
						if ( $active_tab1 === 'general' ) {
							?>
							<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br />
									<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php echo esc_html( $student_data->user_email ); ?> </label>
								</div>
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Admission Number', 'mjschool' ); ?> </label><br />
									<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->admission_no ); ?> </label>
								</div>
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Admission Date', 'mjschool' ); ?> </label><br />
									<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php echo esc_html( mjschool_get_date_in_input_box( $student_data->admission_date ) ); ?> </label>
								</div>
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Previous School', 'mjschool' ); ?> </label><br />
									<label class="mjschool-word-break mjschool-view-page-content-labels">
										<?php
										if ( ! empty( $student_data->preschool_name ) ) {
											echo esc_html( $student_data->preschool_name );
										} else {
											esc_html_e( 'Not Provided', 'mjschool' );
										}
										?>
									</label>
								</div>
							</div>
							<!-- Student Information div start.  -->
							<div class="row mjschool-margin-top-20px">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs mjschool-rtl-custom-padding-0px">
										<div class="mjschool-guardian-div">
											<label class="mjschool-view-page-label-heading"><?php esc_html_e( 'Student Information', 'mjschool' ); ?> </label>
											<div class="row">
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Full Name', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access['edit'] === '1' && empty( $student_data->display_name ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->display_name ); ?></label>
													<?php } ?>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
														<?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?>
													</label><br>
													<?php
													if ( $user_access['edit'] === '1' && empty( $student_data->alternet_mobile_number ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-word-break mjschool-view-page-content-labels">
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
														$edit_url = home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels">
															<?php
															if ( $student_data->gender === 'male' ) {
																echo esc_html__( 'Male', 'mjschool' );
															} elseif ( $student_data->gender === 'female' ) {
																echo esc_html__( 'Female', 'mjschool' );
															} elseif ( $student_data->gender === 'other' ) {
																echo esc_html__( 'Other', 'mjschool' );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
														<?php esc_html_e( 'Date of Birth', 'mjschool' ); ?>
													</label><br>
													<?php
													$birth_date      = $student_data->birth_date;
													$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
													if ( $user_access['edit'] === '1' && $is_invalid_date ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_date_in_input_box( $student_data->birth_date ) ); ?></label>
													<?php } ?>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'City', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access['edit'] === '1' && empty( $student_data->city ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->city ); ?></label>
													<?php } ?>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access['edit'] === '1' && empty( $student_data->state ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-word-break mjschool-view-page-content-labels">
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
													<?php
													if ( $user_access['edit'] === '1' && empty( $student_data->zip_code ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=admission&tab=addadmission&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->zip_code ); ?></label>
													<?php } ?>
												</div>
											</div>
										</div>
									</div>
									<?php
									$mjschool_custom_field_obj = new Mjschool_Custome_Field();
									$module                    = 'admission';
									$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
									?>
									<!-- Sibling information. -->
									<?php
									if ( ! empty( $sibling_information[0]->siblingsstudent ) ) {
										?>
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
											<div class="mjschool-guardian-div">
												<label class="mjschool-view-page-label-heading"><?php esc_html_e( 'Siblings Information', 'mjschool' ); ?> </label>
												<?php
												foreach ( $sibling_information as $value ) {
													$sibling_data = get_userdata( $value->siblingsstudent );
													if ( ! empty( $sibling_data ) ) {
														?>
														<div class="row">
															<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																	<?php esc_html_e( 'Sibling Name', 'mjschool' ); ?> 
																</label> <br>
																<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( mjschool_student_display_name_with_roll( $sibling_data->ID ) ); ?></label>
															</div>
															<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																	<?php esc_html_e( 'Sibling Email', 'mjschool' ); ?> 
																</label> <br>
																<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $sibling_data->user_email ); ?></label>
															</div>
															<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																	<?php esc_html_e( 'Class', 'mjschool' ); ?> 
																</label><br>
																<label class="mjschool-word-break mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_class_section_name_wise( $value->siblingsclass, $value->siblingssection ) ); ?></label>
															</div>
															<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																	<?php esc_html_e( 'Mobile Number', 'mjschool' ); ?>
																</label><br>
																<label class="mjschool-word-break mjschool-view-page-content-labels">
																	<?php
																	if ( ! empty( $sibling_data->mobile_number ) ) {
																		echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $sibling_data->mobile_number );
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
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
									<!-- Other information div start. -->
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
										<?php
										if ( $student_data->parent_status === 'Father' || $student_data->parent_status === 'Both' ) {
											if ( ! empty( $student_data->father_first_name ) ) {
												?>
												<div class="mjschool-guardian-div">
													<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Father Information', 'mjschool' ); ?> </label>
													<div class="row">
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Name', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																echo esc_html( $student_data->fathersalutation ) . ' ' . esc_html( $student_data->father_first_name ) . ' ' . esc_html( $student_data->father_middle_name ) . ' ' . esc_html( $student_data->father_last_name );
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Email', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_email ) ) {
																	echo esc_html( $student_data->father_email );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Gender', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels font_transfer_capitalize">
																<?php
																if ( ! empty( $student_data->fathe_gender ) ) {
																	echo esc_html( $student_data->fathe_gender );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Date of Birth', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_birth_date ) ) {
																	echo esc_html( mjschool_get_date_in_input_box( $student_data->father_birth_date ) );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Address', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_address ) ) {
																	echo esc_html( $student_data->father_address );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'State', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_state_name ) ) {
																	echo esc_html( $student_data->father_state_name );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'City', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_city_name ) ) {
																	echo esc_html( $student_data->father_city_name );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Zip Code', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_zip_code ) ) {
																	echo esc_html( $student_data->father_zip_code );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Mobile No.', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_mobile ) ) {
																	echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) );
																	?>
																	&nbsp;
																	<?php
																	echo esc_html( $student_data->father_mobile );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'School Name', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_school ) ) {
																	echo esc_html( $student_data->father_school );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_medium ) ) {
																	echo esc_html( $student_data->father_medium );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Qualification', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_education ) ) {
																	echo esc_html( $student_data->father_education );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Annual Income', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->fathe_income ) ) {
																	echo esc_html( mjschool_get_currency_symbol() ) . '' . esc_html( $student_data->fathe_income );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Occupation', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->father_occuption ) ) {
																	echo esc_html( $student_data->father_occuption );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<span class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?>
															</span><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																$father_doc      = str_replace( '"[', '[', $student_data->father_doc );
																$father_doc1     = str_replace( ']"', ']', $father_doc );
																$father_doc_info = json_decode( $father_doc1 );
																?>
																<p class="user-info">
																	<?php
																	if ( ! empty( $father_doc_info[0]->value ) ) {
																		?>
																		<a download href="<?php print esc_url( content_url( '/uploads/school_assets/') ) . '$father_doc_info[0]->value;'; ?>" class="mjschool-status-read btn btn-default">
																			<i class="fa fa-download"></i>
																			<?php
																			if ( ! empty( $father_doc_info[0]->title ) ) {
																				echo esc_url( $father_doc_info[0]->title );
																			} else {
																				esc_html_e( ' Download', 'mjschool' );
																			}
																			?>
																		</a>
																		<?php
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																</p>
															</label>
														</div>
													</div>
												</div>
												<br>
												<?php
											}
										}
										if ( $student_data->parent_status === 'Mother' || $student_data->parent_status === 'Both' ) {
											if ( ! empty( $student_data->mother_first_name ) ) {
												?>
												<div class="mjschool-guardian-div">
													<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Mother Information', 'mjschool' ); ?> </label>
													<div class="row">
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Name', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->mothersalutation ) . ' ' . esc_html( $student_data->mother_first_name ) . ' ' . esc_html( $student_data->mother_middle_name ) . ' ' . esc_html( $student_data->mother_last_name ); ?></label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Email', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_email ) ) {
																	echo esc_html( $student_data->mother_email );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Gender', 'mjschool' ); ?> 
															</label><br>
															<label
																class="mjschool-word-break mjschool-view-page-content-labels font_transfer_capitalize">
																<?php
																if ( ! empty( $student_data->mother_gender ) ) {
																	echo esc_html( $student_data->mother_gender );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Date of Birth', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_birth_date ) ) {
																	echo esc_html( mjschool_get_date_in_input_box( $student_data->mother_birth_date ) );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Address', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_address ) ) {
																	echo esc_html( $student_data->mother_address );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'State', 'mjschool' ); ?> 
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_state_name ) ) {
																	echo esc_html( $student_data->mother_state_name );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'City', 'mjschool' ); ?> </label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $student_data->mother_city_name ) ) {
																echo esc_html( $student_data->mother_city_name );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
															?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Zip Code', 'mjschool' ); ?> </label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_zip_code ) ) {
																	echo esc_html( $student_data->mother_zip_code );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Mobile No.', 'mjschool' ); ?>
															</label><br>
															<label
																class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_mobile ) ) {
																	echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) );
																	?>
																	&nbsp;
																	<?php
																	echo esc_html( $student_data->mother_mobile );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'School Name', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_school ) ) {
																	echo esc_html( $student_data->mother_school );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_medium ) ) {
																	echo esc_html( $student_data->mother_medium );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Qualification', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_education ) ) {
																	echo esc_html( $student_data->mother_education );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Annual Income', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_income ) ) {
																	echo esc_html( mjschool_get_currency_symbol() ) . '' . esc_html( $student_data->mother_income );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Occupation', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $student_data->mother_occuption ) ) {
																	echo esc_html( $student_data->mother_occuption );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels">
																<?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels">
																<?php
																$mother_doc      = str_replace( '"[', '[', $student_data->mother_doc );
																$mother_doc1     = str_replace( ']"', ']', $mother_doc );
																$mother_doc_info = json_decode( $mother_doc1 );
																?>
																<p class="user-info">
																	<?php
																	if ( ! empty( $mother_doc_info[0]->value ) ) {
																		?>
																		<a download href="<?php print esc_url( content_url( '/uploads/school_assets/' )) . '$mother_doc_info[0]->value;'; ?>" class=" btn btn-default" <?php if ( empty( $mother_doc_info[0] ) ) { ?> disabled <?php } ?>><i class="fas fa-download"></i> <?php if ( ! empty( $mother_doc_info[0]->title ) ) { echo esc_url( $mother_doc_info[0]->title ); } else { esc_html_e( ' Download', 'mjschool' ); } ?> </a>
																		<?php
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																</p>
															</label>
														</div>
													</div>
												</div>
												<?php
											}
										}
										?>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div><!-- End panel body div.-->
				</section>
				<!-- Detail page body content section end. -->
			</div>
		</div>
		<?php
	}
	// -------------- Add admission tab. ---------------//
	if ( $active_tab === 'addadmission' ) {
		$mjschool_role = 'student_temp';
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			$edit         = 1;
			$student_id   = intval( mjschool_decrypt_id( wp_unslash( $_REQUEST['student_id'] ) ) );
			$student_data = get_userdata( $student_id );
			$user_ID      = $student_id;
			$key          = 'status';
			$single       = true;
			$user_status  = get_user_meta( $user_ID, $key, $single );
			$sibling_data = $student_data->sibling_information;
			$sibling      = json_decode( $sibling_data );
		}
		?>
		<!--Group POP-UP code. -->
		<div class="mjschool-popup-bg">
			<div class="mjschool-overlay-content mjschool-admission-popup">
				<div class="modal-content">
					<div class="mjschool-category-list"> </div>
				</div>
			</div>
		</div>
		<!--Group POP-UP code. -->
		<!----------- Addadmission form design.  ------------->
		<div class="mjschool-panel-body mjschool-margin-top-40">
			<form name="mjschool-admission-form" action="" method="post" class="mjschool-form-horizontal mjschool-admission-form" enctype="multipart/form-data" id="mjschool-admission-form">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="role" value="<?php echo esc_attr( $mjschool_role ); ?>" />
				<input type="hidden" name="user_id" value="<?php if ( $edit ) { echo esc_attr( $student_id ); } ?>" />
				<input type="hidden" name="status" value="<?php if ( $edit ) { echo esc_attr( $user_status ); } ?>" />
				<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
				<!--- Hidden user and password. --------->
				<input id="username" type="hidden" name="username">
				<input id="password" type="hidden" name="password">
				<div class="header">
					<h3 class="mjschool-first-header"> <?php esc_html_e( 'Admission Information', 'mjschool' ); ?> </h3>
				</div>
				<div class="form-body mjschool-user-form"> <!------  Form body. -------->
					<div class="row">
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="admission_no" class="form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( $student_data->admission_no ); } elseif ( isset( $_POST['admission_no'] ) ) { echo esc_attr( mjschool_generate_admission_number() ); } else { echo esc_attr( mjschool_generate_admission_number() ); } ?>" name="admission_no">
									<label for="admission_no"><?php esc_html_e( 'Admission Number', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="admission_date" class="form-control validate[required]" type="text" name="admission_date" readonly value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $student_data->admission_date ) ); } elseif ( isset( $_POST['admission_date'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['admission_date'] ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
									<label for="admission_date"><?php esc_html_e( 'Admission Date', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<?php
						if ( get_option( 'mjschool_admission_fees' ) === 'yes' ) {
							$fees_id           = get_option( 'mjschool_admission_amount' );
							$mjschool_obj_fees = new Mjschool_Fees();
							$amount            = $mjschool_obj_fees->mjschool_get_single_feetype_data_amount( $fees_id );
							?>
							<div class="col-md-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="admission_fees" name="admission_fees" disabled class="form-control" type="text" readonly value="<?php echo esc_attr( mjschool_get_currency_symbol() ) . ' ' . esc_attr( $amount ); ?>">
										<label for="admission_fees" class="active"><?php esc_html_e( 'Admission Fees', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<input id="admission_fees" class="form-control" type="hidden" name="admission_fees_id" value="<?php echo esc_attr( $fees_id ); ?>">
							<input class="form-control" type="hidden" name="admission_fees_amount" value="<?php echo esc_attr( $amount ); ?>">
							<?php
						}
						?>
					</div>
				</div> <!------  Form body. -------->
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Student Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="first_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" autocomplete="first_name" name="first_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->first_name ); } elseif ( isset( $_POST['first_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) ); } ?>">
									<label for="first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="middle_name" class="form-control validate[custom[onlyLetter_specialcharacter]]" maxlength="50" type="text" name="middle_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->middle_name ); } elseif ( isset( $_POST['middle_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['middle_name'] ) ) ); } ?>">
									<label for="middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="last_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="last_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->last_name ); } elseif ( isset( $_POST['last_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) ); } ?>">
									<label for="last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="birth_date" class="form-control date_picker validate[required] birth_date" type="text" name="birth_date" readonly value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $student_data->birth_date ) ); } elseif ( isset( $_POST['birth_date'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['birth_date'] ) ) ); } ?>">
									<label for="birth_date" class="date_label"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-res-margin-bottom-20px mjschool-rtl-margin-top-15px">
							<div class="form-group">
								<div class="col-md-12 form-control">
									<div class="row mjschool-padding-radio">
										<div class="input-group">
											<span class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="required">*</span></span>
											<div class="d-inline-block">
												<?php
												$genderval = 'male';
												if ( $edit ) {
													$genderval = $student_data->gender;
												} elseif ( isset( $_POST['gender'] ) ) {
													$genderval = sanitize_text_field( $_POST['gender'] );
												}
												?>
												<input type="radio" value="male" name="gender" class="mjschool-custom-control-input" <?php checked( 'male', $genderval ); ?> id="male">
												<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>
												&nbsp;&nbsp;<input type="radio" value="female" name="gender" <?php checked( 'female', $genderval ); ?> class="mjschool-custom-control-input" id="female">
												<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>
												&nbsp;&nbsp;<input type="radio" value="other" name="gender" <?php checked( 'other', $genderval ); ?> class="mjschool-custom-control-input" id="other">
												<label class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
									<div class="form-group input mjschool-margin-bottom-0">
										<div class="col-md-12 form-control mjschool-mobile-input">
											<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
											<input id="phonecode1" name="phonecode" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
											<input id="mobile_number" class="form-control validate[required,custom[phone_number],minSize[6],maxSize[15]] text-input" type="text" name="mobile_number" value="<?php if ( $edit ) { echo esc_attr( $student_data->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mobile_number'] ) ) ); } ?>">
											<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mobile_number"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="required red">*</span></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
									<div class="form-group input mjschool-margin-bottom-0">
										<div class="col-md-12 form-control mjschool-mobile-input">
											<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
											<input id="alter_mobile_number" name="alter_mobile_number" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
											<input id="alternet_mobile_number" class="form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]]" type="text" name="alternet_mobile_number" value="<?php if ( $edit ) { echo esc_attr( $student_data->alternet_mobile_number ); } elseif ( isset( $_POST['alternet_mobile_number'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['alternet_mobile_number'] ) ) ); } ?>">
											<label class="mjschool-custom-control-label mjschool-custom-top-label" for="alternet_mobile_number"><?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="email" email_tpye="student_email" class="addmission_email_id form-control validate[required,custom[email]] text-input email" maxlength="100" type="text" autocomplete="email" name="email" value="<?php if ( $edit ) { echo esc_attr( $student_data->user_email ); } elseif ( isset( $_POST['user_email'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['user_email'] ) ) ); } ?>">
									<label for="email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="preschool_name" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="preschool_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->preschool_name ); } elseif ( isset( $_POST['preschool_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['preschool_name'] ) ) ); } ?>">
									<label for="preschool_name"><?php esc_html_e( 'Previous School', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Address Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="address" class="form-control student_address validate[required,custom[address_description_validation]]" maxlength="120" type="text" autocomplete="address" name="address" value="<?php if ( $edit ) { echo esc_attr( $student_data->address ); } elseif ( isset( $_POST['address'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['address'] ) ) ); } ?>">
									<label for="address"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="city_name" class="form-control student_city validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->city ); } elseif ( isset( $_POST['city_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['city_name'] ) ) ); } ?>">
									<label for="city_name"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="state_name" class="form-control student_state validate[custom[city_state_country_validation" maxlength="50" type="text" name="state_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->state ); } elseif ( isset( $_POST['state_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['state_name'] ) ) ); } ?>">
									<label for="state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="zip_code" class="form-control student_zip validate[required,custom[phone_number],minSize[4],maxSize[8]]" maxlength="15" type="text" name="zip_code" value="<?php if ( $edit ) { echo esc_attr( $student_data->zip_code ); } elseif ( isset( $_POST['zip_code'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['zip_code'] ) ) ); } ?>">
									<label for="zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-margin-15px-rtl">
							<div class="form-group">
								<div class="col-md-12 form-control">
									<div class="row mjschool-padding-radio">
										<div>
											<label class="mjschool-custom-top-label" for="mjschool_enable_exam_mail"><?php esc_html_e( 'Parent Address Same as Student Address', 'mjschool' ); ?></label>
											<input id="mjschool_enable_exam_mail" class="same_as_address" type="checkbox" name="same_as_address" value="1">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_mjschool-admission-form' ); ?>
				<!--------------------- Siblings div start. ------------------------>
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
													<input type="checkbox" id="chkIsTeamLead" <?php if ( $edit ) { $sibling_data = $student_data->sibling_information; $sibling = json_decode( $sibling_data ); if ( ! empty( $student_data->sibling_information ) ) { foreach ( $sibling as $value ) { if ( ! empty( $value->siblingsclass ) && ! empty( $value->siblingsstudent ) ) { ?> checked <?php } } } } ?> />&nbsp;&nbsp;<?php esc_html_e( 'In case of any sibling ? click here', 'mjschool' ); ?>
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
					if ( ! empty( $student_data->sibling_information ) ) {
						$sibling_data = $student_data->sibling_information;
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
										(function(jQuery) {
											"use strict";
											jQuery(document).ready(function () {
												jQuery(document).on( "change", "#sibling_class_change_<?php echo esc_js( $i ); ?>", function () {
													// Clear sibling student list.
													jQuery( '#sibling_student_list_<?php echo esc_js( $i ); ?>' ).html( '' );
													var selection = jQuery(this).val();
													// Load users for selected class.
													jQuery.post(mjschool.ajax, {
														action: 'mjschool_load_user',
														class_list: selection,
														nonce: mjschool.nonce,
														dataType: 'json'
													}, function (response) {
														jQuery( '#sibling_student_list_<?php echo esc_js( $i ); ?>' ).append(response);
													});
												});
												jQuery(document).on( "change", "#sibling_class_change_<?php echo esc_js( $i ); ?>", function () {
													// Clear and show loading on class sections dropdown.
													var $section = jQuery( '#sibling_class_section_<?php echo esc_js( $i ); ?>' );
													$section.html( '' );
													$section.append( '<option value="remove">Loading..</option>' );
													var selection = jQuery( "#sibling_class_change_<?php echo esc_js( $i ); ?>").val();
													// Load class sections.
													jQuery.post(mjschool.ajax, {
														action: 'mjschool_load_class_section',
														class_id: selection,
														nonce: mjschool.nonce,
														dataType: 'json'
													}, function (response) {
														$section.find( "option[value='remove']").remove();
														$section.append(response);
													});
												});
												jQuery( "#sibling_class_section_<?php echo esc_js( $i ); ?>").on( 'change', function () {
													// Clear sibling student list.
													jQuery( '#sibling_student_list_<?php echo esc_js( $i ); ?>' ).html( '' );
													var selection = jQuery(this).val();
													var class_id = jQuery( "#sibling_class_change_<?php echo esc_js( $i ); ?>").val();
													// Load section users.
													jQuery.post(mjschool.ajax, {
														action: 'mjschool_load_section_user',
														section_id: selection,
														class_id: class_id,
														nonce: mjschool.nonce,
														dataType: 'json'
													}, function (response) {
														jQuery( '#sibling_student_list_<?php echo esc_js( $i ); ?>' ).append(response);
													});
												});
											});
										})(jQuery);
									</script>
									<input type="hidden" id="admission_sibling_id" name="admission_sibling_id" value="<?php echo esc_attr( $count_array ); ?>" />
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
												<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_change_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												<select name="siblingsclass[]" class="mjschool-line-height-30px form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="sibling_class_change_<?php echo esc_attr( $i ); ?>">
													<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
													<?php
													foreach ( mjschool_get_all_class() as $classdata ) {
														?>
														<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $value->siblingsclass, $classdata['class_id'] ); ?>> <?php echo esc_html( $classdata['class_name'] ); ?></option>
														<?php
													}
													?>
												</select>
											</div>
											<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
												<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
												<select name="siblingssection[]" class="mjschool-line-height-30px form-control mjschool-max-width-100px" id="sibling_class_section_<?php echo esc_attr( $i ); ?>">
													<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
													<?php
													if ( $edit ) {
														foreach ( mjschool_get_class_sections( $value->siblingsclass ) as $sectiondata ) {
															?>
															<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $value->siblingssection, $sectiondata->id ); ?>> <?php echo esc_html( $sectiondata->section_name ); ?></option>
															<?php
														}
													}
													?>
												</select>
											</div>
											<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
												<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Student', 'mjschool' ); ?></label>
												<select name="siblingsstudent[]" id="sibling_student_list_<?php echo esc_attr( $i ); ?>" class="mjschool-line-height-30px form-control mjschool-max-width-100px">
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
											<input type="hidden" class="click_value" name="" value="<?php echo esc_attr( $count_array + 1 ); ?>">
											<?php
											if ( $i === 1 ) {
												 ?>
												<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
												</div>
												<?php
											} else {
												?>
												<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
													<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-input-btn-height-width">
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<?php
									$i++;
								}
							} else {
								?>
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
											<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-sibling-class-change"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="mjschool-sibling-class-change">
												<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
												<?php
												foreach (mjschool_get_all_class() as $classdata) {
													?>
													<option value="<?php echo esc_attr($classdata['class_id']); ?>"> <?php echo esc_html( $classdata['class_name']); ?></option>
													<?php
												}
												?>
											</select>
										</div>
										<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
											<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
											<select name="siblingssection[]" class="form-control mjschool-max-width-100px" id="sibling_class_section">
												<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
											</select>
										</div>
										<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
											<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="siblingsstudent[]" id="sibling_student_list" class="form-control mjschool-max-width-100px validate[required]">
												<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
											</select>
										</div>
										<input type="hidden" class="click_value" name="" value="1">
										<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
										</div>
									</div>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					}
				} else {
					?>
					<div id="mjschool-sibling-div" class="mjschool-sibling-div_clss mjschool-sibling-div_clss">
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
									<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-sibling-class-change"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									<select name="siblingsclass[]" class="mjschool-line-height-30px form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="mjschool-sibling-class-change">
										<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
										<?php
										foreach (mjschool_get_all_class() as $classdata) {
											?>
											<option value="<?php echo esc_attr($classdata['class_id']); ?>"> <?php echo esc_html( $classdata['class_name']); ?></option>
											<?php
										}
										?>
									</select>
								</div>
								<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
									<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
									<select name="siblingssection[]" class="mjschool-line-height-30px form-control mjschool-max-width-100px" id="sibling_class_section">
										<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
									</select>
								</div>
								<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
									<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									<select name="siblingsstudent[]" id="sibling_student_list" class="mjschool-line-height-30px form-control mjschool-max-width-100px validate[required]">
										<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
									</select>
								</div>
								<input type="hidden" class="click_value" name="" value="1">
								<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
								</div>
								
							</div>
						</div>
					</div>
					<?php
				}
				?>
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Family Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6 mjschool-margin-bottom-20px mjschool-rtl-margin-top-15px">
							<div class="form-group">
								<div class="col-md-12 form-control">
									<div class="row mjschool-padding-radio">
										<div class="input-group">
											<span class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Parental Status', 'mjschool' ); ?></span>
											<div class="d-inline-block mjschool-family-information">
												<?php
												$pstatus = 'Both';
												if ( $edit ) {
													$pstatus = $student_data->parent_status;
													if ( $pstatus === '' )
													{
														$pstatus = 'Both';
													}
												} elseif ( isset( $_POST['pstatus'] ) ) {
												
													$pstatus = sanitize_text_field( wp_unslash( $_POST['pstatus'] ) );
												}
												if ( $edit ) {
													$genderval = $value->siblinggender;
												} elseif ( isset( $_POST['siblinggender'] ) ) {
									
													$genderval = sanitize_text_field( wp_unslash( $_POST['siblinggender'] ) );
												}
												?>
												<input type="radio" name="pstatus" class="tog" value="Father" id="sinfather" <?php checked( 'Father', $pstatus ); ?>>
												<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="sinfather"><?php esc_html_e( 'Father', 'mjschool' ); ?></label>
												&nbsp;&nbsp; <input type="radio" name="pstatus" id="sinmother" class="tog" value="Mother" <?php checked( 'Mother', $pstatus ); ?>>
												<label class="mjschool-custom-control-label" for="sinmother"><?php esc_html_e( 'Mother', 'mjschool' ); ?></label>
												&nbsp;&nbsp; <input type="radio" name="pstatus" id="boths" class="tog" value="Both" <?php checked( 'Both', $pstatus ); ?>>
												<label class="mjschool-custom-control-label" for="boths"><?php esc_html_e( 'Both', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
					if ( $edit ) {
						$pstatus = $student_data->parent_status;
						if ( $pstatus === 'Father' ) {
							$m_display_none = 'mjschool-display-none';
						} elseif ( $pstatus === 'Mother' ) {
							$f_display_none = 'mjschool-display-none';
						}
					}
					?>
					<div class="row father_div <?php echo esc_attr( $f_display_none ); ?>">
						<div class="header" id="fatid">
							<h3 class="mjschool-first-header"><?php esc_html_e( 'Father Information', 'mjschool' ); ?></h3>
						</div>
						<div id="fatid1" class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="fathersalutation"><?php esc_html_e( 'Salutation', 'mjschool' ); ?></label>
							<select class="form-control validate[required] mjschool-line-height-30px" name="fathersalutation" id="fathersalutation">
								<option value="Mr"><?php esc_html_e( 'Mr', 'mjschool' ); ?></option>
							</select>
						</div>
						<div id="fatid2" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_first_name" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_first_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_first_name ); } elseif ( isset( $_POST['father_first_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_first_name'] ) ) ); } ?>">
									<label for="father_first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid3" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_middle_name" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_middle_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_middle_name ); } elseif ( isset( $_POST['father_middle_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_middle_name'] ) ) ); } ?>">
									<label for="father_middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid4" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_last_name" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_last_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_last_name ); } elseif ( isset( $_POST['father_last_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_last_name'] ) ) ); } ?>">
									<label for="father_last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid13" class="col-md-6 mjschool-rtl-margin-top-15px mjschool-res-margin-bottom-20px">
							<div class="form-group">
								<div class="col-md-12 form-control">
									<div class="row mjschool-padding-radio">
										<div class="input-group">
											<span class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Gender', 'mjschool' ); ?></span>
											<div class="d-inline-block">
												<?php
												$father_gender = 'male';
												if ( $edit ) {
													$father_gender = $student_data->fathe_gender;
												} elseif ( isset( $_POST['fathe_gender'] ) ) {
													$father_gender = sanitize_text_field( wp_unslash( $_POST['fathe_gender'] ) );
													
												}
												?>
												<input type="radio" value="male" class="tog" name="fathe_gender" <?php checked( 'male', $father_gender ); ?> />
												<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>
												<input type="radio" value="female" class="tog" name="fathe_gender" <?php checked( 'female', $father_gender ); ?> />
												<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>
												<input type="radio" value="other" class="tog" name="fathe_gender" <?php checked( 'other', $father_gender ); ?> />
												<label class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="fatid14" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_birth_date" class="form-control date_picker birth_date" type="text" name="father_birth_date" value="<?php if ( $edit ) { if ( $student_data->father_birth_date === '' ) { echo ''; } else { echo esc_attr( mjschool_get_date_in_input_box( $student_data->father_birth_date ) ); } } elseif ( isset( $_POST['father_birth_date'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_birth_date'] ) ) ); } ?>" readonly>
									<label for="father_birth_date" class="date_label"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid15" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_address" class="form-control parent_address date_picker validate[custom[address_description_validation]]" maxlength="120" type="text" name="father_address" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_address ); } elseif ( isset( $_POST['father_address'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_address'] ) ) ); } ?>">
									<label for="father_address" class="date_label"><?php esc_html_e( 'Address', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid17" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_city_name" class="form-control parent_city validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="father_city_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_city_name ); } elseif ( isset( $_POST['father_city_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_city_name'] ) ) ); } ?>">
									<label for="father_city_name"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid16" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_state_name" class="form-control parent_state validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="father_state_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_state_name ); } elseif ( isset( $_POST['father_state_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_state_name'] ) ) ); } ?>">
									<label for="father_state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid18" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_zip_code" class="form-control parent_zip validate[custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="father_zip_code" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_zip_code ); } elseif ( isset( $_POST['father_zip_code'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_zip_code'] ) ) ); } ?>">
									<label for="father_zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid5" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_email" email_tpye="father_email" class="addmission_email_id form-control validate[custom[email]] text-input father_email" maxlength="100" type="text" name="father_email" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_email ); } elseif ( isset( $_POST['father_email'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_email'] ) ) ); } ?>">
									<label for="father_email"><?php esc_html_e( 'Email', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid6" class="col-md-6">
							<div class="row">
								<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
									<div class="form-group input mjschool-margin-bottom-0">
										<div class="col-md-12 form-control mjschool-mobile-input">
											<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
											<input id="phone_code" name="phone_code" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
											<input id="father_mobile" class="form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]]" type="text" name="father_mobile" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_mobile ); } elseif ( isset( $_POST['father_mobile'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_mobile'] ) ) ); } ?>">
											<label class="mjschool-custom-control-label mjschool-custom-top-label" for="father_mobile"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="fatid7" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_school" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_school" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_school ); } elseif ( isset( $_POST['father_school'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_school'] ) ) ); } ?>">
									<label for="father_school"><?php esc_html_e( 'School Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid8" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_medium" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_medium" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_medium ); } elseif ( isset( $_POST['father_medium'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_medium'] ) ) ); } ?>">
									<label for="father_medium"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid9" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_education" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_education" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_education ); } elseif ( isset( $_POST['father_education'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_education'] ) ) ); } ?>">
									<label for="father_education"><?php esc_html_e( 'Educational Qualification', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid10" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="fathe_income" class="form-control validate[custom[onlyNumberSp],maxSize[8],min[0]] text-input" maxlength="50" type="text" name="fathe_income" value="<?php if ( $edit ) { echo esc_attr( $student_data->fathe_income ); } elseif ( isset( $_POST['fathe_income'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['fathe_income'] ) ) ); } ?>">
									<label for="fathe_income"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="fatid9" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="father_occuption" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_occuption" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_occuption ); } elseif ( isset( $_POST['father_occuption'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_occuption'] ) ) ); } ?>">
									<label for="father_occuption"><?php esc_html_e( 'Occupation', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6" id="mjschool-fatid12">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></span>
									<div class="col-sm-12"> <input type="file" name="father_doc" class="col-md-12 form-control file mjschool-file-validation input-file" value="<?php if ( $edit ) { echo esc_url( $student_data->father_doc ); } elseif ( isset( $_POST['father_doc'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['father_doc'] ) ) ); } ?>"> </div>
								</div>
							</div>
						</div>
					</div>
					<div class="row mother_div <?php echo esc_attr( $m_display_none ); ?>">
						<div class="header" id="motid">
							<h3 class="mjschool-first-header"><?php esc_html_e( 'Mother Information', 'mjschool' ); ?></h3>
						</div>
						<div id="motid1" class="col-md-6 mother_info">
							<label class="ml-1 mjschool-custom-top-label mjschool-res-margin-bottom-20px top" for="mothersalutation"><?php esc_html_e( 'Salutation', 'mjschool' ); ?></label>
							<select class="form-control validate[required] mjschool-line-height-30px" name="mothersalutation" id="mothersalutation">
								<option value="Ms"><?php esc_html_e( 'Ms', 'mjschool' ); ?></option>
								<option value="Mrs"><?php esc_html_e( 'Mrs', 'mjschool' ); ?></option>
								<option value="Miss"><?php esc_html_e( 'Miss', 'mjschool' ); ?></option>
							</select>
						</div>
						<div id="motid2" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_first_name" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_first_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_first_name ); } elseif ( isset( $_POST['mother_first_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_first_name'] ) ) ); } ?>">
									<label for="mother_first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid3" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_middle_name" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_middle_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_middle_name ); } elseif ( isset( $_POST['mother_middle_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_middle_name'] ) ) ); } ?>">
									<label for="mother_middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid4" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_last_name" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_last_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_last_name ); } elseif ( isset( $_POST['mother_last_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_last_name'] ) ) ); } ?>">
									<label for="mother_last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid13" class="col-md-6 mjschool-rtl-margin-top-15px mjschool-res-margin-bottom-20px">
							<?php
							$mother_gender = 'female';
							if ( $edit ) {
								$mother_gender = $student_data->mother_gender;
							} elseif ( isset( $_POST['mother_gender'] ) ) {
								$mother_gender = sanitize_text_field( wp_unslash( $_POST['mother_gender'] ) );
						
							}
							?>
							<div class="form-group">
								<div class="col-md-12 form-control">
									<div class="row mjschool-padding-radio">
										<div class="input-group">
											<span class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Gender', 'mjschool' ); ?></span>
											<div class="d-inline-block">
												<?php
												$father_gender = 'male';
												if ( $edit ) {
													$father_gender = $student_data->fathe_gender;
												} elseif ( isset( $_POST['fathe_gender'] ) ) {
													
													$father_gender = sanitize_text_field( wp_unslash( $_POST['fathe_gender'] ) );
												}
												?>
												<input type="radio" value="male" class="tog" name="mother_gender" <?php checked( 'male', $mother_gender ); ?> />
												<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>
												<input type="radio" value="female" class="tog" name="mother_gender" <?php checked( 'female', $mother_gender ); ?> />
												<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>
												<input type="radio" value="other" class="tog" name="mother_gender" <?php checked( 'other', $mother_gender ); ?> />
												<label class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="motid14" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_birth_date" class="form-control date_picker birth_date" type="text" name="mother_birth_date" value="<?php if ( $edit ) { if ( $student_data->mother_birth_date === '' ) { echo ''; } else { echo esc_attr( mjschool_get_date_in_input_box( $student_data->mother_birth_date ) ); } } elseif ( isset( $_POST['mother_birth_date'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_birth_date'] ) ) ); } ?>" readonly>
									<label for="mother_birth_date" class="date_label"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid15" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_address" class="form-control parent_address validate[custom[address_description_validation]]" maxlength="120" type="text" name="mother_address" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_address ); } elseif ( isset( $_POST['mother_address'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_address'] ) ) ); } ?>">
									<label for="mother_address"><?php esc_html_e( 'Address', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid17" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_city_name" class="form-control parent_city validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="mother_city_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_city_name ); } elseif ( isset( $_POST['mother_city_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_city_name'] ) ) ); } ?>">
									<label for="mother_city_name"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid16" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_state_name" class="form-control parent_state validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="mother_state_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_state_name ); } elseif ( isset( $_POST['mother_state_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_state_name'] ) ) ); } ?>">
									<label for="mother_state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid18" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_zip_code" class="form-control parent_zip validate[custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="mother_zip_code" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_zip_code ); } elseif ( isset( $_POST['mother_zip_code'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_zip_code'] ) ) ); } ?>">
									<label for="mother_zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid5" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_email" email_tpye="mother_email" class="addmission_email_id form-control  validate[custom[email]]  text-input mother_email" maxlength="100" type="text" name="mother_email" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_email ); } elseif ( isset( $_POST['mother_email'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_email'] ) ) ); } ?>">
									<label for="mother_email"><?php esc_html_e( 'Email', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid6" class="col-md-6">
							<div class="row">
								<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
									<div class="form-group input mjschool-margin-bottom-0">
										<div class="col-md-12 form-control mjschool-mobile-input">
											<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
											<input id="phone_code" name="phone_code" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
											<input id="mother_mobile" class="form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]]" type="text" name="mother_mobile" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_mobile ); } elseif ( isset( $_POST['mother_mobile'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_mobile'] ) ) ); } ?>">
											<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mother_mobile"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="motid7" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_school" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_school" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_school ); } elseif ( isset( $_POST['mother_school'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_school'] ) ) ); } ?>">
									<label for="mother_school"><?php esc_html_e( 'School Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid8" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_medium" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_medium" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_medium ); } elseif ( isset( $_POST['mother_medium'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_medium'] ) ) ); } ?>">
									<label for="mother_medium"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid9" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_education" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_education" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_education ); } elseif ( isset( $_POST['mother_education'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_education'] ) ) ); } ?>">
									<label for="mother_education"><?php esc_html_e( 'Educational Qualification', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid10" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_income" class="form-control validate[custom[onlyNumberSp],maxSize[8],min[0]] text-input" type="text" name="mother_income" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_income ); } elseif ( isset( $_POST['mother_income'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_income'] ) ) ); } ?>">
									<label for="mother_income"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="motid9" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mother_occuption" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_occuption" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_occuption ); } elseif ( isset( $_POST['mother_occuption'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['mother_occuption'] ) ) ); } ?>">
									<label for="mother_occuption"><?php esc_html_e( 'Occupation', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div id="mjschool-motid12" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></span>
									<div class="col-sm-12">
										<input type="file" name="mother_doc" class="col-md-12 form-control file mjschool-file-validation input-file">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'admission';
				$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Admission', 'mjschool' ); } else { esc_attr_e( 'New Admission', 'mjschool' ); } ?>" name="student_admission" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form><!------ Form end. ----->
		</div><!-------- Panel body. -------->
		<script type="text/javascript">
			(function(jQuery){
				"use strict";
				// Initialize `value` depending on PHP $edit variable.
				var value;
				<?php if ( $edit ) { ?>
					value = jQuery( '#admission_sibling_id' ).val();
				<?php } else { ?>
					value = 0;
				<?php } ?>
				// Add more sibling div function.
				window.mjschool_add_sibling = function() {
					value++;
					jQuery( ".mjschool-sibling-div_clss").append(
						'<div class="form-body mjschool-user-form">' +
							'<div class="row">' +
								'<div class="col-md-3 col-sm-3 col-xs-12 mjschool-res-margin-bottom-20px mjschool-rtl-margin-top-15px">' +
									'<div class="form-group">' +
										'<div class="col-md-12 form-control">' +
											'<div class="row mjschool-padding-radio">' +
												'<div class="input-group">' +
													'<span class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( "Relation", "mjschool" ); ?></span>' +
													'<div class="d-inline-block">' +
														'<input type="radio" name="siblinggender[' + value + ']" value="Brother" id="txtNumHours2" checked>' +
														'<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( "Brother", "mjschool" ); ?></label>&nbsp;&nbsp;' +
														'<input type="radio" name="siblinggender[' + value + ']" value="Sister" id="txtNumHours2">' +
														'<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( "Sister", "mjschool" ); ?></label>' +
													'</div>' +
												'</div>' +
											'</div>' +
										'</div>' +
									'</div>' +
								'</div>' +
								'<div class="col-md-2 col-sm-3 col-xs-12">' +
									'<div class="form-group input">' +
										'<div class="col-md-12 form-control">' +
											'<input id="txtNumHours" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="siblingsname[]" value="">' +
											'<label for="txtNumHours"><?php esc_html_e( "Full Name", "mjschool" ); ?></label>' +
										'</div>' +
									'</div>' +
								'</div>' +
								'<div class="col-md-1 col-sm-3 col-xs-12">' +
									'<div class="form-group input">' +
										'<div class="col-md-12 form-control mjschool-input-height-47px">' +
											'<input id="txtNumHours1" class="form-control age_padding_left_right_0 validate[custom[onlyNumberSp],maxSize[3],max[100]] text-input" type="number" maxlength="3" name="siblingage[]" value="">' +
											'<label for="txtNumHours1"><?php esc_html_e( "Age", "mjschool" ); ?></label>' +
										'</div>' +
									'</div>' +
								'</div>' +
								'<div class="col-md-3 col-sm-3 col-xs-12 input">' +
									'<label class="ml-1 mjschool-custom-top-label top" for="txtNumHours3"><?php esc_html_e( "Standard", "mjschool" ); ?><span class="required">*</span></label>' +
									'<select class="form-control mjschool-standard-category validate[required] mjschool-line-height-30px" name="sibling_standard[]" id="txtNumHours3">' +
										'<option value=""><?php esc_html_e( "Select Standard", "mjschool" ); ?></option>' +
										<?php
										$activity_category = mjschool_get_all_category( 'mjschool-standard-category' );
										if ( ! empty( $activity_category ) ) {
											foreach ( $activity_category as $retrive_data ) {
												echo "'<option value=\"" . esc_attr( $retrive_data->ID ) . "\">" . esc_html( $retrive_data->post_title ) . "</option>' +";
											}
										}
										?>
									'</select>' +
								'</div>' +
								'<div class="col-md-2 col-sm-3 col-xs-12">' +
									'<div class="form-group input">' +
										'<div class="col-md-12 form-control mjschool-input-height-47px">' +
											'<input id="txtNumHours4" class="form-control validate[custom[onlyNumberSp],maxSize[6]] text-input" value="" type="number" name="siblingsid[]">' +
											'<label for="txtNumHours4"><?php esc_html_e( "Enter SID Number", "mjschool" ); ?></label>' +
										'</div>' +
									'</div>' +
								'</div>' +
								'<div class="col-md-1 col-sm-3 col-xs-12">' +
									'<input type="image" onclick="mjschool_mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-float-right mjschool-input-btn-height-width">' +
								'</div>' +
							'</div>' +
						'</div>'
					);
				};
				// Delete sibling div function.
				window.mjschool_mjschool_delete_parent_element = function(n) {
					if(confirm( "<?php esc_html_e( 'Do you really want to delete this ?', 'mjschool' ); ?>" ) ) {
						n.parentNode.parentNode.parentNode.removeChild(n.parentNode.parentNode);
					}
				};
			})(jQuery);
		</script>
		<?php
	}
	?>
</div>