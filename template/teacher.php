<?php
/**
 * Teacher Management Page
 *
 * This file handles the full CRUD (Create, Read, Update, Delete) functionality
 * for teacher user profiles, including standard fields, custom fields,
 * and optional teacher documents/certificates.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$role_name         = mjschool_get_user_role( get_current_user_id() );
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'teacher';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
$document_option    = get_option( 'mjschool_upload_document_type' );
$document_type      = explode( ', ', $document_option );
$document_type_json = $document_type;
$document_size      = get_option( 'mjschool_upload_document_size' );
?>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$active_tab  = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'teacherlist';
$teacher_obj = new Mjschool_Teacher();
$role        = 'teacher';
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
// ------------- SAVE TEACHER. -------------//
if ( isset( $_POST['save_teacher'] ) ) {
	if ( isset( $_FILES['signature'] ) && ! empty( $_FILES['signature']['name'] ) ) {
		if ( $_FILES['signature']['size'] > 0 ) {
			$signature = mjschool_upload_teacher_signature( $_FILES['signature'] );
		}
	} else {
		// Always fallback to existing signature if no new file is uploaded.
		$signature = isset( $_POST['signaturehidden'] ) ? sanitize_text_field(wp_unslash($_POST['signaturehidden'])) : '';
	}
	$firstname  = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
	$middlename = sanitize_text_field( wp_unslash( $_POST['middle_name'] ) );
	$lastname   = sanitize_text_field( wp_unslash( $_POST['last_name'] ) );
	$userdata   = array(
		'user_login'    => sanitize_email( $_POST['email'] ),
		'user_nicename' => null,
		'user_email'    => sanitize_email( $_POST['email'] ),
		'user_url'      => null,
		'display_name'  => $firstname . ' ' . $middlename . ' ' . $lastname,
	);
	if ( sanitize_text_field(wp_unslash($_POST['password'])) !== '' ) {
		$userdata['user_pass'] = mjschool_password_validation( sanitize_text_field(wp_unslash($_POST['password'])) );
	}
	if ( isset( $_POST['mjschool_user_avatar'] ) && sanitize_text_field(wp_unslash($_POST['mjschool_user_avatar'])) !== '' ) {
		$photo = sanitize_text_field(wp_unslash($_POST['mjschool_user_avatar']));
	} else {
		$photo = '';
	}
	$attechment = '';
	if ( ! empty( $_POST['attachment'] ) ) {
		$attechment = implode( ',', sanitize_text_field(wp_unslash($_POST['attachment'])) );
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
	// DOCUMENT UPLOAD FILE CODE END.
	$usermetadata = array(
		'middle_name'            => sanitize_text_field( wp_unslash($_POST['middle_name']) ),
		'gender'                 => sanitize_text_field( wp_unslash($_POST['gender']) ),
		'birth_date'             => sanitize_text_field(wp_unslash($_POST['birth_date'])),
		'address'                => sanitize_textarea_field( $_POST['address'] ),
		'city'                   => sanitize_text_field( wp_unslash( $_POST['city_name'] ) ),
		'state'                  => sanitize_text_field( wp_unslash( $_POST['state_name'] ) ),
		'designation'            => sanitize_text_field( wp_unslash( $_POST['designation'] ) ),
		'zip_code'               => sanitize_text_field( wp_unslash( $_POST['zip_code'] ) ),
		'class_name'             => sanitize_text_field(wp_unslash($_POST['class_name'])),
		'signature'              => $signature,
		'phone'                  => sanitize_text_field( wp_unslash( $_POST['phone'] ) ),
		'mobile_number'          => sanitize_text_field( wp_unslash( $_POST['mobile_number'] ) ),
		'user_document'          => $final_document,
		'alternet_mobile_number' => sanitize_text_field( wp_unslash( $_POST['alternet_mobile_number'] ) ),
		'working_hour'           => sanitize_text_field( wp_unslash( $_POST['working_hour'] ) ),
		'possition'              => sanitize_textarea_field( wp_unslash( $_POST['possition'] ) ),
		'mjschool_user_avatar'       => $photo,
		'attachment'             => $attechment,
		'created_by'             => get_current_user_id(),
	);
	if ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
		if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
			$userdata['ID']      = mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) );
			$result              = mjschool_update_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $role );
			$custom_field_obj    = new Mjschool_Custome_Field();
			$module              = 'teacher';
			$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $result );
			$result1             = $teacher_obj->mjschool_update_multi_class( sanitize_text_field(wp_unslash($_POST['class_name'])), mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) ) );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=teacher&tab=teacherlist&message=2') );
			die();
		} else {
			wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
		}
	} else {
		/* Setup Wizard */
		$wizard = mjschool_setup_wizard_steps_updates( 'step3_teacher' );
		if ( ! email_exists( $_POST['email'] ) ) {
			$result             = mjschool_add_new_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $role );
			$custom_field_obj   = new Mjschool_Custome_Field();
			$module             = 'teacher';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			$result1            = $teacher_obj->mjschool_add_multi_class( sanitize_text_field(wp_unslash($_POST['class_name'])), mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_POST['email'])) ) );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=teacher&tab=teacherlist&message=1') );
			die();
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert updated_top mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php esc_html_e( 'Username Or Emailid All Ready Exist.', 'mjschool' ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
	}
}
// -------------------- DELETE TEACHER. ---------------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = mjschool_delete_usedata( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=teacher&tab=teacherlist&message=5') );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ------------------ MULTIPLE DELETE TEACHER. -------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_books' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_usedata( $id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=teacher&tab=teacherlist&message=5') );
		die();
	}
}
$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
// -------------- MESSAGES. --------------//
switch ( $message ) {
	case '1':
		$message_string = esc_html__( 'Teacher Added Successfully.', 'mjschool' );
		break;
	case '2':
		$message_string = esc_html__( 'Teacher Updated Successfully.', 'mjschool' );
		break;
	case '3':
		$message_string = esc_html__( 'Roll No Already Exist.', 'mjschool' );
		break;
	case '4':
		$message_string = esc_html__( 'Teacher Username Or Emailid Already Exist.', 'mjschool' );
		break;
	case '5':
		$message_string = esc_html__( 'Teacher Deleted Successfully.', 'mjschool' );
		break;
	case '6':
		$message_string = esc_html__( 'Teacher CSV Uploaded Successfully .', 'mjschool' );
		break;
	case '7':
		$message_string = esc_html__( 'Student Activated Auccessfully.', 'mjschool' );
		break;
}
if ( $message ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Using a static plugin asset ?>
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
		<?php echo esc_html( $message_string ); ?>
	</div>
	<?php
}
?>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<?php
	// ------------ TEACHER LIST. ---------------//
	if ( $active_tab === 'teacherlist' ) {
		$user_id = get_current_user_id();
		// ------- TEACHER DATA FOR STUDENT. ---------//
		if ( $school_obj->role === 'student' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$class_id    = get_user_meta( get_current_user_id(), 'class_name', true );
				$teacherdata = mjschool_get_teacher_by_class_id( $class_id );
			} else {
				$teacherdata = mjschool_get_users_data( 'teacher' );
			}
		}
		// ------- TEACHER DATA FOR TEACHER. ---------//
		elseif ( $school_obj->role === 'teacher' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$user_id                = get_current_user_id();
				$teacher_own            = array();
				$teacherdata_created_by = array();
				$teacher_own[]          = get_userdata( $user_id );
				$created_by_sanitized = intval( $user_id ); // Sanitize as integer.
				$teacher_ids = mjschool_get_teachers_created_by_user( $created_by_sanitized )
				// Optionally cache here with wp_cache_get/wp_cache_set.
				$teacherdata_created_by[] = array_map( 'get_userdata', $teacher_ids );
				
				$teacherdata1 = array_merge( $teacher_own, $teacherdata_created_by );
				$teacherdata  = array_unique( $teacherdata1, SORT_NUMERIC );
			} else {
				$teacherdata = mjschool_get_users_data( 'teacher' );
			}
		}
		// ------- TEACHER DATA FOR PARENT. ---------//
		elseif ( $school_obj->role === 'parent' ) {
			$teacherdata_data = array();
			$child            = get_user_meta( get_current_user_id(), 'child', true );
			foreach ( $child as $c_id ) {
				$class_id          = get_user_meta( $c_id, 'class_name', true );
				$teacherdata_data1 = mjschool_get_teacher_by_class_id( $class_id );
				if ( ! empty( $teacherdata_data1 ) ) {
					$teacherdata_data = array_merge( $teacherdata_data, $teacherdata_data1 );
				} else {
					$teacherdata_data = '';
				}
			}
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				
				$teacherdata_created_by = get_users(
					array(
						'role' => 'teacher',
						'meta_query' => array(
							array(
								'key' => 'created_by',
								'value' => $user_id,
								'compare' => '='
							)
						)
					)
				);
				
				if ( ! empty( $teacherdata_data ) ) {
					$teacherdata_array = array_merge( $teacherdata_data, $teacherdata_created_by );
				} else {
					$teacherdata_array = $teacherdata_created_by;
				}
			} else {
				$teacherdata_array = mjschool_get_users_data( 'teacher' );
			}
			$teacherdata = array_unique( $teacherdata_array, SORT_REGULAR );
		}
		// ------- TEACHER DATA FOR SUPPORT STAFF. ---------//
		else {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				
				$teacherdata_created_by = get_users(
					array(
						'role' => 'teacher',
						'meta_query' => array(
							array(
								'key' => 'created_by',
								'value' => $user_id,
								'compare' => '='
							)
						)
					)
				);
				
				$teacherdata = $teacherdata_created_by;
			} else {
				$teacherdata = mjschool_get_users_data( 'teacher' );
			}
		}
		if ( ! empty( $teacherdata ) ) {
			?>
			<div class="mjschool-panel-body"><!--------- Panel body. ----------->
				<div class="table-responsive"><!--------- Table responsive. ----------->
					<!----------- TEACHER LIST FORM START. ---------->
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<?php wp_nonce_field( 'bulk_delete_books' ); ?>
						<table id="front_teacher_list" class="display dataTable mjschool-teacher-datatable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $role_name === 'supportstaff' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="mjschool-sub-chk select_all" name="select_all"></th>
										<?php
									}
									?>
									<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Teacher Name & Email', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
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
									if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
										?>
										<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
										<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										<?php
									}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
								$obj_subject = new Mjschool_Subject();
								foreach ( $teacherdata as $retrieved_data ) {
									if ( ! username_exists( $retrieved_data->user_login ) ) {
										continue;
									} /* If the teacher does not exist, then we dont want to print an empty row. */
									?>
									<tr>
										<?php
										if ( $role_name === 'supportstaff' ) {
											?>
											<td class="mjschool-checkbox-width-10px">
												<input type="checkbox" class="mjschool-sub-chk selected_teacher" name="id[]" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
											</td>
											<?php
										}
										?>
										<td class="mjschool-user-image mjschool-width-50px-td">
											<?php
											if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
												?>
												<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=view_teacher&action=view_teacher&teacher_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>">
													<?php
													$uid       = $retrieved_data->ID;
													$umetadata = mjschool_get_user_image( $uid );
													
													if (empty($umetadata ) ) {
														echo '<img src=' . esc_url( get_option( 'mjschool_teacher_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
													} else {
														echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
													}
													 ?>
												</a>
												<?php
											} else {
												?>
												<a  href="#">
													<?php
													$uid       = $retrieved_data->ID;
													$umetadata = mjschool_get_user_image( $uid );
													
													if (empty($umetadata ) ) {
														echo '<img src=' . esc_url( get_option( 'mjschool_teacher_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
													} else {
														echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
													}
													 ?>
												</a>
												<?php
											}
											?>
										</td>
										<td class="name">
											<?php
											if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
												?>
												<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=view_teacher&action=view_teacher&teacher_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>"><?php echo esc_html( $retrieved_data->display_name ); ?> </a>
												<?php
											} else {
												?>
												<a  href="#"><?php echo esc_html( $retrieved_data->display_name ); ?> </a>
												<?php
											}?>
											<br>
											<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></span>
										</td>
										<td >
											<?php
											$classes   = '';
											$classes   = $teacher_obj->mjschool_get_class_by_teacher( $retrieved_data->ID );
											$classname = '';
											foreach ( $classes as $class ) {
												$classname .= mjschool_get_class_name( $class['class_id'] ) . ',';
											}
											$classname_rtrim = rtrim( $classname, ', ' );
											$classname_ltrim = ltrim( $classname_rtrim, ', ' );
											if ( ! empty( $classname_ltrim ) ) {
												echo esc_html( $classname_ltrim );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
										</td>
										<td >
											<?php
											$subjectname = $obj_subject->mjschool_get_subject_name_by_teacher( $uid );
											if ( ! empty( $subjectname ) ) {
												echo esc_html( rtrim( $subjectname, ', ' ) );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i>
										</td>
										<td >
											<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->birth_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date Of Birth', 'mjschool' ); ?>"></i>
										</td>
										<?php
										// Custom Field Values.
										if ( ! empty( $user_custom_field ) ) {
											foreach ( $user_custom_field as $custom_field ) {
												if ( $custom_field->show_in_table === '1' ) {
													$module             = 'teacher';
													$custom_field_id    = $custom_field->id;
													$module_record_id   = $retrieved_data->ID;
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
										if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
											?>
											<td >
												<?php $uid = $retrieved_data->ID; ?>
												+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( get_user_meta( $uid, 'mobile_number', true ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
											</td>
											<td class="action">
												<div class="mjschool-user-dropdown">
													<ul  class="mjschool_ul_style">
														<li >
															<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																
															</a>
															<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																<li class="mjschool-float-left-width-100px">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=view_teacher&action=view_teacher&teacher_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																</li>
																<?php
																if ( $user_access['edit'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																	</li>
																	<?php
																}
																if ( $user_access['delete'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=teacherlist&action=delete&teacher_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
																	</li>
																	<?php
																}
																?>
															</ul>
														</li>
													</ul>
												</div>
											</td>
											<?php
										}
										?>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
						<?php
						if ( $role_name === 'supportstaff' ) {
							?>
							<div class="mjschool-print-button pull-left">
								<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
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
					</form><!----------- TEACHER LIST FORM END. ---------->
				</div><!--------- Table responsive. ----------->
			</div><!--------- Panel body. ----------->
			<?php
		} elseif ( $user_access['add'] === '1' ) {
			 ?>
			<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
				<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher') ); ?>">
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
	// ------------ Teacher add form. ---------------//
	if ( $active_tab === 'addteacher' ) {
		$role = 'teacher';
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$edit            = 1;
			$user_info       = get_userdata( intval( mjschool_decrypt_id( wp_unslash($_REQUEST['teacher_id']) ) ) );
			$user_deligation = get_user_meta( intval( mjschool_decrypt_id( wp_unslash($_REQUEST['teacher_id']) ) ), 'designation', true );
		}
		$document_option    = get_option( 'mjschool_upload_document_type' );
		$document_type      = explode( ', ', $document_option );
		$document_type_json = $document_type;
		$document_size      = get_option( 'mjschool_upload_document_size' );
		?>

		<div class="mjschool-panel-body"><!----------- Panel body. ------------->
			<!------------------ Teacher form. --------------------->
			<form name="teacher_form" action="" method="post" class="mt-3 mjschool-form-horizontal" id="teacher_form" enctype="multipart/form-data">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden"  name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Personal Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form"><!-- Mjschool-user-form. -->
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="first_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->first_name ); } elseif ( isset( $_POST['first_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['first_name'])) ); } ?>" autocomplete="first_name" name="first_name">
									<label  for="first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="middle_name" class="form-control validate[custom[onlyLetter_specialcharacter]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->middle_name ); } elseif ( isset( $_POST['middle_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['middle_name'])) ); } ?>" name="middle_name">
									<label  for="middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="last_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->last_name ); } elseif ( isset( $_POST['last_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['last_name'])) ); } ?>" name="last_name">
									<label  for="last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
							<div class="form-group">
								<div class="col-md-12 form-control">
									<div class="row mjschool-padding-radio">
										<div class="input-group">
											<span class="mjschool-custom-top-label mjschool-margin-left-0" for="gender"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
											<div class="d-inline-block">
												<?php
												$genderval = 'male';
												if ( $edit ) {
													$genderval = $user_info->gender;
												} elseif ( isset( $_POST['gender'] ) ) {
													$genderval = sanitize_text_field(wp_unslash($_POST['gender']));
												}
												?>
												<label class="radio-inline">
													<input type="radio" value="male" class="tog validate[required]" name="gender" <?php checked( 'male', $genderval ); ?> /><?php esc_html_e( 'Male', 'mjschool' ); ?>
												</label>
												&nbsp;&nbsp;
												<label class="radio-inline">
													<input type="radio" value="female" class="tog validate[required]" name="gender" <?php checked( 'female', $genderval ); ?> /><?php esc_html_e( 'Female', 'mjschool' ); ?>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="birth_date" class="form-control date_picker validate[required]" type="text" name="birth_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $user_info->birth_date ) ); } elseif ( isset( $_POST['birth_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['birth_date'])) ) ); } ?>" readonly>
									<label class="date_label" for="birth_date"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-4 input mjschool-width-70px">
							<label class="ml-1 mjschool-custom-top-label top" for="designation"><?php esc_html_e( 'Designation', 'mjschool' ); ?><span class="required">*</span></label>
							<?php
							if ( $edit ) {
								$sectionval1 = $user_deligation;
							} elseif ( isset( $_POST['designation'] ) ) {
								$sectionval1 = sanitize_text_field(wp_unslash($_POST['designation']));
							} else {
								$sectionval1 = '';
							}
							?>
							<select id="designation" class="form-control validate[required] designation mjschool-width-100px" name="designation">
								<option value=""><?php esc_html_e( 'Select Designation', 'mjschool' ); ?></option>
								<?php
								$activity_category = mjschool_get_all_category( 'designation' );
								if ( ! empty( $activity_category ) ) {
									foreach ( $activity_category as $retrive_data ) {
										?>
										<option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $sectionval1 ); ?>><?php echo esc_html( $retrive_data->post_title ); ?> </option>
										<?php
									}
								}
								?>
							</select>	                           
						</div>
						<div class="col-md-2 col-sm-1 mjschool-res-width-30px">
							<input type="button" id="mjschool-addremove-cat" value="<?php esc_attr_e( 'ADD', 'mjschool' ); ?>" model="designation" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div><!-- Mjschool-user-form. -->
				<div class="header"><!-- Header. -->
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Contact Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form"> <!--Mjschool-user-form div.-->
					<div class="row"><!--Row div.-->
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="address" class="form-control validate[required,custom[address_description_validation]]" maxlength="120" type="text" autocomplete="address" name="address" value="<?php if ( $edit ) { echo esc_attr( $user_info->address ); } elseif ( isset( $_POST['address'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['address'])) ); } ?>">
									<label  for="address"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="city_name" class="form-control validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->city ); } elseif ( isset( $_POST['city_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['city_name'])) ); } ?>">
									<label  for="city_name"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="state_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->state ); } elseif ( isset( $_POST['state_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['state_name'])) ); } ?>">
									<label  for="state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="zip_code" class="form-control  validate[required,custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="zip_code" value="<?php if ( $edit ) { echo esc_attr( $user_info->zip_code ); } elseif ( isset( $_POST['zip_code'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['zip_code'])) ); } ?>">
									<label  for="zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
							<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
								<?php
								if ( $edit ) {
									$classval = $user_info->class_name;
								} elseif ( isset( $_POST['class_name'] ) ) {
									$classval = sanitize_text_field(wp_unslash($_POST['class_name']));
								} else {
									$classval = '';
								}
								$classes = array();
								if ( isset( $_REQUEST['teacher_id'] ) ) {
									$classes = $teacher_obj->mjschool_get_class_by_teacher( mjschool_decrypt_id( wp_unslash($_REQUEST['teacher_id']) ) );
								}
								?>
								<select name="class_name[]" multiple="multiple" id="class_id" class="form-control validate[required]">
									<?php
									foreach ( mjschool_get_all_class() as $classdata ) {
										?>
										<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php echo esc_attr( $teacher_obj->mjschool_in_array_r( $classdata['class_id'], $classes ) ) ? 'selected' : ''; ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
										<?php
									}
									?>
								</select>
								<span class ="mjschool-multiselect-label">
									<label class="ml-1 mjschool-custom-top-label top" for="class_id"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="required">*</span></label>
								</span>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res">
							<div class="row">
								<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
									<div class="form-group input mjschool-margin-bottom-0">
										<div class="col-md-12 form-control mjschool-mobile-input">
											<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="form-control phonecode" name="phonecode">
											<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
											<input id="mobile_number" class="form-control validate[required],minSize[6],maxSize[15]] text-input" type="text" name="mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mobile_number'])) ); } ?>">
											<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mobile_number"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group input mjschool-margin-bottom-0">
										<div class="col-md-12 form-control mjschool-mobile-input">
											<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="form-control phonecode" name="alter_mobile_number">
											<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
											<input id="alternet_mobile_number" class="form-control text-input validate[minSize[6],maxSize[15]]" type="text" name="alternet_mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->alternet_mobile_number ); } elseif ( isset( $_POST['alternet_mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['alternet_mobile_number'])) ); } ?>">
											<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mobile_number"><?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
							<label class="ml-1 mjschool-custom-top-label top" for="working_hour"><?php esc_html_e( 'Working Hour', 'mjschool' ); ?></label>
							<?php
							if ( $edit ) {
								$workrval = $user_info->working_hour;
							} elseif ( isset( $_POST['working_hour'] ) ) {
								$workrval = sanitize_text_field(wp_unslash($_POST['working_hour']));
							} else {
								$workrval = '';
							}
							?>
							<select name="working_hour" class="mjschool-line-height-30px form-control" id="working_hour">
								<option value=""><?php esc_html_e( 'Select Job Time', 'mjschool' ); ?></option>
								<option value="full_time" <?php selected( $workrval, 'full_time' ); ?>><?php esc_html_e( 'Full Time', 'mjschool' ); ?></option>
								<option value="half_day" <?php selected( $workrval, 'half_day' ); ?>><?php esc_html_e( 'Part time', 'mjschool' ); ?></option>
							</select>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="possition" class="form-control validate[custom[address_description_validation]]" maxlength="50" type="text" name="possition" value="<?php if ( $edit ) { echo esc_attr( $user_info->possition ); } elseif ( isset( $_POST['possition'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['possition'])) ); } ?>">
									<label  for="possition"><?php esc_html_e( 'Position', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
					</div><!--Row div.-->
				</div><!--Mjschool-user-form div.-->
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Login Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form"> <!--Mjschool-user-form div.-->
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="email" class="mjschool-student-email-id form-control validate[required,custom[email]] text-input" maxlength="100" type="text" autocomplete="email" name="email" value="<?php if ( $edit ) { echo esc_attr( $user_info->user_email ); } elseif ( isset( $_POST['email'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['email'])) ); } ?>">
									<label  for="email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="password" class="form-control <?php if ( ! $edit ) { echo 'validate[required,minSize[8],maxSize[12]]'; } else { echo 'validate[minSize[8],maxSize[12]]'; } ?>" type="password" name="password" value="">
									<label  for="password"><?php esc_html_e( 'Password', 'mjschool' ); ?> <?php if ( ! $edit ) { ?> <span class="mjschool-require-field">*</span><?php } ?></label>
									<i class="fas fa-eye-slash togglepassword_class" id="togglePassword"></i>
								</div>
							</div>
						</div>
					</div>
				</div><!--Mjschool-user-form div.-->
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Profile Image', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form"><!--Mjschool-user-form div.-->
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 mjschool-upload-profile-image-patient p-0">
									<div class="col-md-12 form-control mjschool-upload-profile-image-frontend mjschool-res-rtl-height-50px">
										<span for="mjschool_membershipimage" class="mjschool-custom-control-label mjschool-profile-rtl-css mjschool-custom-top-label ml-2"><?php esc_html_e( 'Profile Image', 'mjschool' ); ?></span>
										<div class="col-sm-12">
											<input type="hidden" id="mjschool_user_avatar_url" class="form-control" name="smgt_user_avatar" value="<?php if ( $edit ) { echo esc_html( $user_info->smgt_user_avatar ); } elseif ( isset( $_POST['mjschool_user_avatar'] ) ) { echo esc_url( sanitize_text_field(wp_unslash($_POST['mjschool_user_avatar'])) ); } ?>" readonly />
											<input type="hidden" name="hidden_upload_user_avatar_image" value="<?php if ( $edit ) { echo esc_html( $user_info->smgt_user_avatar ); } elseif ( isset( $_POST['hidden_upload_user_avatar_image'] ) ) { echo esc_url( sanitize_text_field(wp_unslash($_POST['hidden_upload_user_avatar_image'])) ); } ?>">
											<input id="upload_user_avatar" name="upload_user_avatar_image" type="file" class="form-control file mjchool_border_0px" onchange="mjschool_file_check(this);" value="<?php esc_attr_e( 'Upload image', 'mjschool' ); ?>" />
										</div>
									</div>
								</div>
								<div class="clearfix"></div>
								<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
									<div id="mjschool-upload-user-avatar-preview">
										<?php
										if ( $edit ) {
											
											if ($user_info->smgt_user_avatar === "") { ?>
												<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_teacher_thumb_new' ) ) ?>">
												<?php
											} else {
												?>
												<img class="mjschool-image-preview-css" src="<?php if ($edit) echo esc_url($user_info->smgt_user_avatar); ?>" />
												<?php
											}
										} else {
											?>
											<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_teacher_thumb_new' ) ) ?>">
											<?php 
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div><!--Mjschool-user-form div.-->
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
												
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
												
											</div>
											<?php
										} else {
											?>
											<div class="col-md-1 col-2 col-sm-3 col-xs-12">
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
													<input id="upload_user_avatar_button" name="document_file[]" type="file" class="p-1 form-control mjschool-file-validation file" value="<?php esc_attr_e( 'Upload image', 'mjschool' ); ?>"/>
												</div>
											</div>
										</div>
									</div>
									
									<div class="col-md-1 col-2 col-sm-1 col-xs-12">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
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
												<input id="upload_user_avatar_button" name="document_file[]" type="file" class="p-1 form-control file mjschool-file-validation" value="<?php esc_attr_e( 'Upload image', 'mjschool' ); ?>" />
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-1 col-2 col-sm-1 col-xs-12">
									
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
									
								</div>
							</div>
						</div>
						<?php
					}
					if ( $edit ) {
						$signature_file = get_user_meta( intval( mjschool_decrypt_id( wp_unslash($_REQUEST['teacher_id']) ) ), 'signature', true );
						?>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Signature', 'mjschool' ); ?></span>
									<div class="col-sm-12">
										<input type="file" name="signature" class='form-control' id="signature" />
										<input type="hidden" name="signaturehidden" value="<?php if ( $edit ) { echo esc_attr( $signature_file ); } else { echo '';} ?>">
									</div>
									<?php
									if ( ! empty( $signature_file ) ) {
										?>
										<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
											<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url( '/' . $signature_file )); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
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
									<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Signature', 'mjschool' ); ?></span>
									<div class="col-sm-12">
										<input type="file" class="col-md-12 form-control" name="signature" id="signature" />
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$custom_field_obj = new Mjschool_Custome_Field();
				$module           = 'teacher';
				$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form"><!--Mjschool-user-form div.-->
					<div class="row">
						<div class="col-md-6 col-sm-6 col-xs-12 mt-3"><!--Save btn.-->
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Teacher', 'mjschool' ); } else { esc_html_e( 'Add Teacher', 'mjschool' ); } ?>" name="save_teacher" class="btn btn-success mjschool-class-for-alert mjschool-save-btn" />
						</div><!--Save btn.-->
					</div>
				</div>
			</form><!------------------ Teacher form. --------------------->
		</div><!----------- Panel body. ------------->
		<?php
	}
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'view_teacher' ) {
		$active_tab1      = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
		$teacher_obj      = new Mjschool_Teacher();
		$obj_route        = new Mjschool_Class_Routine();
		$custom_field_obj = new Mjschool_Custome_Field();
		$teacher_id       = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['teacher_id']) ) );
		$teacher_data     = get_userdata( $teacher_id );
		?>
		<div class="mjschool-panel-body mjschool-view-page-main"><!--  Start panel body div.-->
			<div class="content-body"><!--  Start content body div.-->
				<!-- Detail Page Header Start. -->
				<section id="mjschool-user-information">
					<div class="mjschool-view-page-header-bg">
						<div class="row">
							<div class="col-xl-10 col-md-9 col-sm-10">
								<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
									<?php
									$umetadata = mjschool_get_user_image( $teacher_data->ID );
									 ?>
									<img class="mjschool-user-view-profile-image" src="<?php if ( ! empty( $umetadata ) ) { echo esc_url($umetadata); } else { echo esc_url( get_option( 'mjschool_teacher_thumb_new' ) ); } ?>">
									<div class="row mjschool-profile-user-name">
										<div class="mjschool-float-left mjschool-view-top1">
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
												<label class="mjschool-view-user-name-label"><?php echo esc_html( $teacher_data->display_name); ?></label>
												<div class="mjschool-view-user-edit-btn">
													<?php
													if ($user_access['edit'] === '1' ) {
														?>
														<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . mjschool_encrypt_id( $teacher_data->ID ) . '&_wpnonce_action_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
														</a>
														<?php
													}
													?>
												</div>
											</div>
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
												<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<label><?php echo esc_html( $teacher_data->mobile_number); ?></label>
												</div>
											</div>
										</div>
									</div>
									<div class="row mjschool-padding-top-15px-res mjschool-view-user-teacher-label">
										<div class="col-xl-12 col-md-12 col-sm-12">
											<div class="mjschool-view-top2">
												<div class="row mjschool-view-user-teacher-label">
													<div class="col-md-12 mjschool-address-student-div">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page"><?php echo esc_html( $teacher_data->address); ?></label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2">
								<div class="mjschool-group-thumbs">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
								</div>
							</div>
							
						</div>
					</div>
				</section>
				<!-- Detail Page Header End. -->
				<!-- Detail Page Tabbing Start. -->
				<section id="body_area" class="body_areas">
					<div class="row">
						<div class="col-xl-12 col-md-12 col-sm-12">
							<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
								<li class="<?php if ( $active_tab1 === 'general' ) { ?> active<?php } ?>">
									<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=view_teacher&action=view_teacher&tab1=general&teacher_id=' . wp_unslash( $_REQUEST['teacher_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>"> <?php esc_html_e( 'GENERAL', 'mjschool' ); ?></a>
								</li>
								<?php
								$page1 = 'class';
								$class = mjschool_page_access_role_wise_access_right_dashboard( $page1 );
								if ( $class === 1 ) {
									?>
									<li class="<?php if ( $active_tab1 === 'mjschool-class-list' ) { ?> active<?php } ?>">
										<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=view_teacher&action=view_teacher&tab1=mjschool-class-list&teacher_id=' . wp_unslash( $_REQUEST['teacher_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'mjschool-class-list' ? 'active' : ''; ?>"> <?php esc_html_e( 'Class List', 'mjschool' ); ?></a>
									</li>
									<?php
								}
								$page2    = 'schedule';
								$schedule = mjschool_page_access_role_wise_access_right_dashboard( $page2 );
								if ( $schedule === 1 ) {
									if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
										?>
										<li class="<?php if ( $active_tab1 === 'schedule' ) { ?> active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=view_teacher&action=view_teacher&tab1=schedule&teacher_id=' . $_REQUEST['teacher_id'] ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'schedule' ? 'active' : ''; ?>"> <?php esc_html_e( 'Class Schedule', 'mjschool' ); ?></a>
										</li>
										<?php
									}
								}
								$page       = 'attendance';
								$attendance = mjschool_page_access_role_wise_access_right_dashboard( $page );
								if ( $attendance === 1 ) {
									?>
									<li class="<?php if ( $active_tab1 === 'attendance' ) { ?> active<?php } ?>">
										<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=teacher&tab=view_teacher&action=view_teacher&tab1=attendance&teacher_id=' . wp_unslash( $_REQUEST['teacher_id'] ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'attendance' ? 'active' : ''; ?>"> <?php esc_html_e( 'Attendance', 'mjschool' ); ?></a>
									</li>
									<?php
								}
								?>
							</ul>
						</div>
					</div>
				</section>
				<!-- Detail Page Tabbing End. -->
				<!-- Detail Page Body Content Section.  -->
				<section id="mjschool-body-content-area">
					<div class="mjschool-panel-body"><!--  Start panel body div.-->
						<?php
						// --- General tab start. ----//
						if ( $active_tab1 === 'general' ) {
							$obj_subject = new Mjschool_Subject();
							?>
							<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br />
									<label class="mjschool-view-page-content-labels"> <?php echo esc_html( $teacher_data->user_email ); ?> </label>
								</div>
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Mobile Number', 'mjschool' ); ?> </label><br />
									<?php
									if ( $user_access['edit'] === '1' && empty( $teacher_data->mobile_number ) ) {
										$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
										echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
									} else {
										?>
										<label class="mjschool-view-page-content-labels">
											+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $teacher_data->mobile_number ); ?>
										</label>
									<?php } ?>
								</div>
								<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br />
									<?php
									if ( $user_access['edit'] === '1' && empty( $teacher_data->gender ) ) {
										$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
										echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
									} else {
										?>
										<label class="mjschool-view-page-content-labels"> <?php echo esc_html( ucfirst( $teacher_data->gender ) ); ?></label>
									<?php } ?>
								</div>
								<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br />
									<?php
									$birth_date      = $teacher_data->birth_date;
									$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
									if ( $user_access['edit'] === '1' && $is_invalid_date ) {
										$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
										echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
									} else {
										?>
										<label class="mjschool-view-page-content-labels"> 
											<?php
											if ( ! empty( $birth_date ) && $birth_date !== '1970-01-01' && $birth_date !== '0000-00-00' ) {
												echo esc_html( mjschool_get_date_in_input_box( $birth_date ) );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' ); // Only shown to users without edit access.
											}
											?>
										</label>
									<?php } ?>
								</div>
								<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Position', 'mjschool' ); ?> </label><br />
									<?php
									if ( $user_access['edit'] === '1' && empty( $teacher_data->possition ) ) {
										$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
										echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
									} else {
										?>
										<label class="mjschool-view-page-content-labels">
											<?php
											if ( ! empty( $teacher_data->possition ) ) {
												echo esc_html( $teacher_data->possition );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
											?>
										</label>
									<?php } ?>
								</div>
							</div>
							<!-- Student Information div start.  -->
							<div class="row mjschool-margin-top-20px">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
										<div class="mjschool-guardian-div">
											<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Contact Information', 'mjschool' ); ?> </label>
											<div class="row">
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'City', 'mjschool' ); ?> </label> <br>
													<?php
													if ( $user_access['edit'] === '1' && empty( $teacher_data->city ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels"><?php echo esc_html( $teacher_data->city ); ?></label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access['edit'] === '1' && empty( $teacher_data->state ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $teacher_data->state ) ) {
																echo esc_html( $teacher_data->state );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Zip Code', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access['edit'] === '1' && empty( $teacher_data->zip_code ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels"><?php echo esc_html( $teacher_data->zip_code ); ?></label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access['edit'] === '1' && empty( $teacher_data->alternet_mobile_number ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $teacher_data->alternet_mobile_number ) ) {
																?>
																+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;
																<?php
																echo esc_html( $teacher_data->alternet_mobile_number );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Working Hour', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access['edit'] === '1' && empty( $teacher_data->working_hour ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $teacher_data->working_hour ) ) {
																$working_data = $teacher_data->working_hour;
																if ( $working_data === 'full_time' ) {
																	esc_html_e( 'Full Time', 'mjschool' );
																} else {
																	esc_html_e( 'Part Time', 'mjschool' );
																}
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Class Name', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														$classes   = '';
														$classes   = $teacher_obj->mjschool_get_class_by_teacher( $teacher_data->ID );
														$classname = '';
														foreach ( $classes as $class ) {
															$classname .= mjschool_get_class_name( $class['class_id'] ) . ',';
														}
														$classname_rtrim = rtrim( $classname, ', ' );
														$classname_ltrim = ltrim( $classname_rtrim, ', ' );
														echo esc_html( $classname_ltrim );
														?>
													</label>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Subject', 'mjschool' ); ?> </label><br>
													<?php
													$subjectname = $obj_subject->mjschool_get_subject_name_by_teacher( $teacher_data->ID );
													if ( $user_access['edit'] === '1' && empty( $subjectname ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $subjectname ) ) {
																echo esc_html( rtrim( $subjectname, ', ' ) );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Designation', 'mjschool' ); ?> </label><br>
													<?php
													$user_designation_id = get_user_meta( intval( $teacher_data->ID ), 'designation', true );
													if ( $user_access['edit'] === '1' && empty( $user_designation_id ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $user_designation_id ) ) {
																$designation_post = get_post( $user_designation_id );
																if ( $designation_post && $designation_post->post_type === 'designation' ) {
																	echo esc_html( $designation_post->post_title );
																} else {
																	echo esc_html__( 'Not Provided', 'mjschool' );
																}
															} else {
																echo esc_html__( 'Not Provided', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Signature', 'mjschool' ); ?></label><br>
													<?php
													$signature_file = get_user_meta( intval( $teacher_data->ID ), 'signature', true );
													if ( $user_access['edit'] === '1' && empty( $signature_file ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $signature_file ) ) {
																$signature_url = esc_url( content_url( '/' . ltrim( $signature_file, '/' )) );
																echo '<a class="btn btn-default" href="' . esc_url( $signature_url ) . '" target="_blank"><i class="fas fa-download"></i> ' . esc_html__( 'Download', 'mjschool' ) . '</a>';
															} else {
																echo esc_html__( 'Not Provided', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
											</div>
											<?php
											if ( ! empty( $teacher_data->user_document ) ) {
												?>
												<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Document Information', 'mjschool' ); ?> </label>
												<div class="row">
													<?php
													$document_array = json_decode( $teacher_data->user_document );
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
									$module = 'teacher';
									$custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
									?>
								</div>
							</div>
							<?php
						}
						// --- General tab End. ----//
						// ---  Attendance tab start. --//
						elseif ( $active_tab1 === 'attendance' ) {
							$attendance_list = mjschool_monthly_attendence_teacher( $teacher_id );
							if ( ! empty( $attendance_list ) ) {
								?>
								<div class="table-div"><!--  Start panel body div. -->
									<div class="table-responsive"><!-- Table responsive div start. -->
										<table id="mjschool-attendance-list-detail-page-front" class="display" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Attendance Date', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Day', 'mjschool' ); ?> </th>
													<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?> </th>
													<th><?php esc_html_e( 'Status', 'mjschool' ); ?> </th>
													<th><?php esc_html_e( 'Comment', 'mjschool' ); ?> </th>
												</tr>
											</thead>
											<tbody>
												<?php
												$attendance_list = mjschool_monthly_attendence_teacher( $teacher_id );
												$i               = 0;
												$srno            = 1;
												if ( ! empty( $attendance_list ) ) {
													foreach ( $attendance_list as $retrieved_data ) {
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
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>" class="mjschool-massage-image">
																</p>
															</td>
															<td ><?php echo esc_html( mjschool_get_user_name_by_id( $retrieved_data->user_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i></td>
															<td class="name"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendence_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendence Date', 'mjschool' ); ?>"></i></td>
															<td >
																<?php
																$curremt_date = $retrieved_data->attendence_date;
																$day          = date( 'D', strtotime( $curremt_date ) );
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
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
															</td>
															<td class="name">
																<?php echo esc_html( mjschool_get_display_name( $retrieved_data->attend_by ) ); ?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
															</td>
															<td> 
																<?php $status_color = mjschool_attendance_status_color( $retrieved_data->status ); ?>
																<span style="color:<?php echo esc_attr( $status_color ); ?>;">
																	<?php echo esc_html( $retrieved_data->status ); ?>
																</span>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
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
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->comment ) ) { echo esc_html( $retrieved_data->comment ); } else { esc_html_e( 'Comment', 'mjschool' ); } ?>"></i>
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
								if ( $fattendance_1['add'] === '1' && ( $role_name === 'supportstaff' ) ) {
									?>
									
									<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
										<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=attendance&tab=teacher_attendance&tab1=teacher_attendences') ); ?>">
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
						// ---  Attendance tab End. --//
						// ---  Class List tab Start. --//
						elseif ( $active_tab1 === 'mjschool-class-list' ) {
							$classes = $teacher_obj->mjschool_get_class_by_teacher( $teacher_id );
							if ( $classes ) {
								?>
								<div class="table-div"><!--  Start panel body div. -->
									<div class="table-responsive"><!-- Table responsive div start. -->
										<table id="mjschool-class-list-detail-page-front" class="display" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Section', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Class Numeric Value', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Student Capacity', 'mjschool' ); ?> </th>
													<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?> </th>
												</tr>
											</thead>
											<tbody>
												<?php
												$i = 0;
												if ( ! empty( $classes ) ) {
													foreach ( $classes as $class_id ) {
														$section_id     = mjschool_get_section_by_class_id( $class_id->class_id );
														$section_name   = '';
														$retrieved_data = mjschool_get_class_data_by_class_id( $class_id );
														if ( ! empty( $retrieved_data ) ) {
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
																<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ) ?>" class="img-circle" /></td>
																<td>
																	<?php
																	if ( $retrieved_data->class_name ) {
																		echo esc_html( $retrieved_data->class_name );
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php
																	foreach ( $section_id as $section ) {
																		$section_name .= $section->section_name . ', ';
																	}
																	$section_name_rtrim = rtrim( $section_name, ', ' );
																	$section_name_ltrim = ltrim( $section_name_rtrim, ', ' );
																	if ( ! empty( $section_name_ltrim ) ) {
																		echo esc_html( $section_name_ltrim );
																	} else {
																		esc_html_e( 'No Section', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Section', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php
																	if ( $retrieved_data->class_num_name ) {
																		echo esc_html( $retrieved_data->class_num_name );
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Numeric Name', 'mjschool' ); ?>"></i>
																</td>
																<?php
																$class_id = $retrieved_data->class_id;
																
																$mjschool_user = count(get_users(array(
																	'meta_key' => 'class_name',
																	'meta_value' => $class_id
																 ) ) );
																
																?>
																<td>
																	<?php
																	echo esc_html( $mjschool_user ) . ' ';
																	esc_html_e( 'Out Of', 'mjschool' );
																	echo ' ' . esc_html( $retrieved_data->class_capacity );
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student Capacity', 'mjschool' ); ?>"></i>
																</td>
																<td class="action">
																	<div class="mjschool-user-dropdown">
																		<ul  class="mjschool_ul_style">
																			<li >
																				
																				<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																				</a>
																				<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																					<li class="mjschool-float-left-width-100px">
																						<a class="mjschool-float-left-width-100px" href="<?php echo esc_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=student_list&class_id=' . mjschool_encrypt_id( $retrieved_data->class_id ) ); ?>"><i class="fas fa-list"></i><?php esc_html_e( 'Student List', 'mjschool' ); ?></a>
																					</li>
																				</ul>
																			</li>
																		</ul>
																	</div>
																</td>
															</tr>
															<?php
															$i++;
														}
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
						// ---  Class List tab End. --//
						// ---- Class schedule tab start. ----//
						elseif ( $active_tab1 === 'schedule' ) {
							$schedule_available = false; // Flag to check if any schedule exists.
							// Check if at least one schedule exists.
							foreach ( mjschool_day_list() as $daykey => $dayname ) {
								$period_1 = $obj_route->mjschool_get_period_by_teacher( $teacher_data->ID, $daykey );
								$period_2 = $obj_route->mjschool_get_period_by_particular_teacher( $teacher_data->ID, $daykey );
								if ( ! empty( $period_1 ) || ! empty( $period_2 ) ) {
									$schedule_available = true;
									break; // Exit loop early if a schedule is found.
								}
							}
							// If schedule is available, display table.
							if ( $schedule_available ) {
								?>
								<div id="Section1" class="mjschool_new_sections">
									<div class="row">
										<div class="col-lg-12">
											<div>
												<div class="mjschool-class-border-div card-content">
													<table class="table table-bordered">
														<?php foreach ( mjschool_day_list() as $daykey => $dayname ) { ?>
															<tr>
																<th><?php echo esc_html( $dayname ); ?></th>
																<td>
																	<?php
																	$period_1 = $obj_route->mjschool_get_period_by_teacher( $teacher_data->ID, $daykey );
																	$period_2 = $obj_route->mjschool_get_period_by_particular_teacher( $teacher_data->ID, $daykey );
																	if ( ! empty( $period_1 ) && ! empty( $period_2 ) ) {
																		$period = array_merge( $period_1, $period_2 );
																	} elseif ( ! empty( $period_1 ) ) {
																		$period = $period_1;
																	} elseif ( ! empty( $period_2 ) ) {
																		$period = $period_2;
																	} else {
																		$period = array();
																	}
																	if ( ! empty( $period ) ) {
																		// Sorting function.
																		usort(
																			$period,
																			function ( $a, $b ) {
																				$startA = DateTime::createFromFormat( 'h:i A', trim( $a->start_time ) );
																				$startB = DateTime::createFromFormat( 'h:i A', trim( $b->start_time ) );
																				if ( $startA === $startB ) {
																					$endA = DateTime::createFromFormat( 'h:i A', trim( $a->end_time ) );
																					$endB = DateTime::createFromFormat( 'h:i A', trim( $b->end_time ) );
																					return $endA <=> $endB;
																				}
																				return $startA <=> $startB;
																			}
																		);
																		foreach ( $period as $period_data ) {

																		echo '<div class="btn-group m-b-sm">';

																		echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" aria-expanded="false" data-toggle="dropdown">
																			<span class="mjschool-period-box" id="' . esc_attr( $period_data->route_id ) . '">'
																			. esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) );

																		$start_time_data = explode( ':', $period_data->start_time );
																		$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
																		$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
																		$start_am_pm     = $start_time_data[2];

																		$end_time_data = explode( ':', $period_data->end_time );
																		$end_hour      = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
																		$end_min       = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
																		$end_am_pm     = $end_time_data[2];

																		echo '<span class="time"> ( '
																			. esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' ' . esc_html( $start_am_pm )
																			. ' - '
																			. esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ' . esc_html( $end_am_pm )
																			. ' ) </span>';

																		echo '<span>' . esc_html( mjschool_get_class_name( $period_data->class_id ) ) . '</span>';
																		echo '</span><span class="caret"></span></button>';

																		echo '<ul role="menu" class="dropdown-menu">
																			<li>
																				<a href="' . esc_url(
																					'?page=mjschool_route&tab=addroute&action=edit&route_id=' . $period_data->route_id
																				) . '">' . esc_html__( 'Edit', 'mjschool' ) . '</a>
																			</li>
																			<li>
																				<a href="' . esc_url(
																					'?page=mjschool_route&tab=route_list&action=delete&route_id=' . $period_data->route_id
																				) . '">' . esc_html__( 'Delete', 'mjschool' ) . '</a>
																			</li>
																		</ul>';

																		echo '</div>';
																	}

																	} else {
																		echo '<span class="text-muted">' . esc_html__( 'No Schedule Available', 'mjschool' ) . '</span>';
																	}
																	?>
																</td>
															</tr>
														<?php } ?>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
							} else {
								$schedule_access = mjschool_get_user_role_wise_filter_access_right_array( 'schedule' );
								if ( $schedule_access['add'] === '1' && ( $role_name === 'supportstaff' || $role_name === 'teacher' ) ) {
									 ?>
									<div class="mjschool-no-data-list-div">
										<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=schedule&tab=addroute') ); ?>">
											<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
										</a>
										<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
											<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
										</div>
									</div>
									<?php
								} else { ?>
									<div class="mjschool-calendar-event-new">
										<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
									</div>
									<?php 
								}
							}
						}
						// ---- Class schedule tab end. ----//
						?>
					</div><!-- End panel body div.-->
				</section>
				<!-- Detail Page Body Content Section End. -->
			</div><!-- End content body div.-->
		</div><!-- End panel body div.-->
		<?php
	}
	?>
</div>