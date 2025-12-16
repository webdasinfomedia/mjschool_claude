<?php

/**
 * Exam Hall Management Page.
 *
 * This file serves as the administrative view and controller for managing
 * **Exam Halls (Seating Arrangements)** within the Mjschool system. It allows
 * administrators to define and manage the allocation of exam venues and seating
 * arrangements for scheduled examinations.
 *
 * It is primarily responsible for:
 *
 * 1. **Access Control**: Implementing **role-based access control** to ensure
 * only authorized users can 'view', 'add', 'edit', or 'delete' exam hall configurations.
 * 2. **Navigation/Tabs**: Handling different operational modes, typically `halllist`
 * for viewing all halls and `addhall` for creating or editing an exam hall record.
 * 3. **Form Handling**: Displaying the 'Add/Edit Exam Hall' form, which captures:
 * - Hall Name/Number.
 * - Hall Capacity (number of students it can accommodate).
 * - Association with a specific Exam (retrieved via `mjschool_get_all_exam_data()`).
 * 4. **List Display**: Rendering a tabular list of existing exam halls/arrangements.
 * 5. **Search/Filter**: Providing search functionality to find specific exam halls
 * based on the exam name (using `search_exam` action).
 * 6. **Custom Fields**: Integrating the `Mjschool_Custome_Field` object to fetch
 * and display any custom fields associated with the 'examhall' module.
 * 7. **CRUD Operations**: Processing form submissions and URL actions for managing
 * the exam hall data in the database table (`mjschool_hall`).
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role_name = mjschool_get_user_role( get_current_user_id() );
// Table name without prefix.
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'examhall';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
$tablename         = 'mjschool_hall';
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
// ------- Send mail for exam receipt. ---------------//
if ( isset( $_POST['send_mail_exam_receipt'] ) ) {
	$exam_id = sanitize_text_field(wp_unslash($_POST['exam_id']));
	$student_data_asigned = mjschool_get_assigned_students_by_exam($exam_id);
	$nonce = wp_create_nonce( 'mjschool_exam_hall_tab' );
	// ------- Send mail for exam receipt generated. ---------------//
	if ( ! empty( $student_data_asigned ) ) {
		foreach ( $student_data_asigned as $student_id ) {
			$headers                    = '';
			$headers                   .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
			$headers                   .= "MIME-Version: 1.0\r\n";
			$headers                   .= "Content-Type: text/html; charset=iso-8859-1\r\n";
			$userdata                   = get_userdata( $student_id->user_id );
			$exam_data                  = mjschool_get_exam_by_id( $exam_id );
			$student_email              = $userdata->user_email;
			$string                     = array();
			$string['{{student_name}}'] = $userdata->display_name;
			$string['{{school_name}}']  = get_option( 'mjschool_name' );
			$msgcontent                 = get_option( 'mjschool_exam_receipt_content' );
			$msgsubject                 = get_option( 'mjschool_exam_receipt_subject' );
			$message                    = mjschool_string_replacement( $string, $msgcontent );
			$student_id_new             = $student_id->user_id;
			mjschool_send_mail_receipt_pdf( $student_email, $msgsubject, $message, $student_id_new, $exam_id );
		}
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam_hall&tab=exam_hall_receipt&_wpnonce='.esc_attr( $nonce ).'&message=4' ) );
		die();
	}
}
// This is class at admin side.
// ----------------- Delete hall. --------------------//
$tablename = 'mjschool_hall';
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$nonce = wp_create_nonce( 'mjschool_exam_hall_tab' );
		$result = mjschool_delete_hall( $tablename, mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['hall_id'])) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam_hall&tab=hall_list&_wpnonce='.esc_attr( $nonce ).'&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// --------------- Multiple hall delete. ----------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		$nonce = wp_create_nonce( 'mjschool_exam_hall_tab' );
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_hall( $tablename, intval( $id ) );
		}
	}
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam_hall&tab=hall_list&_wpnonce='.esc_attr( $nonce ).'&message=3' ) );
		die();
	}
}
// ------------- Insert and update. ----------------//
if ( isset( $_POST['save_hall'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_hall_admin_nonce' ) ) {
		$created_date = date( 'Y-m-d H:i:s' );
		$hall_data    = array(
			'hall_name'      => sanitize_text_field( wp_unslash($_POST['hall_name']) ),
			'number_of_hall' => sanitize_text_field( wp_unslash($_POST['number_of_hall']) ),
			'hall_capacity'  => sanitize_text_field( wp_unslash($_POST['hall_capacity']) ),
			'description'    => sanitize_textarea_field( wp_unslash($_POST['description']) ),
			'date'           => $created_date,
			'created_by'     => get_current_user_id(),
		);
		// Table name without prefix.
		$tablename = 'mjschool_hall';
		$nonce = wp_create_nonce( 'mjschool_exam_hall_tab' );
		if ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$hall_id      = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['hall_id'])) ) );
				$transport_id = array( 'hall_id' => mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['hall_id'])) ) );
				$result       = mjschool_update_record( $tablename, $hall_data, $transport_id );
				// Update custom field data.
				$custom_field_obj    = new Mjschool_Custome_Field();
				$module              = 'examhall';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $hall_id );
				if ( $result ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam_hall&tab=hall_list&_wpnonce='.esc_attr( $nonce ).'&message=2' ) );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result = mjschool_insert_record( $tablename, $hall_data );
			global $wpdb;
			$last_insert_id     = $wpdb->insert_id;
			$custom_field_obj   = new Mjschool_Custome_Field();
			$module             = 'examhall';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $last_insert_id );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=exam_hall&tab=hall_list&_wpnonce='.esc_attr( $nonce ).'&message=1' ) );
				die();
			}
		}
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'hall_list';
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
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!----------- PENAL BODY ----------->
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Hall Added Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Hall Updated Successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Hall Deleted Successfully.', 'mjschool' );
			break;
		case '4':
			$message_string = esc_html__( 'Mail Send Successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		 ?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
			<?php echo esc_html( $message_string); ?>
		</div>
		<?php 
	}
	?>
	<?php $nonce = wp_create_nonce( 'mjschool_exam_hall_tab' ); ?>
	<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
		<li class="<?php if ( $active_tab === 'hall_list' ) { ?> active<?php } ?>">
			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=exam_hall&tab=hall_list&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'hall_list' ? 'active' : ''; ?>"> <?php esc_html_e( 'Exam Hall List', 'mjschool' ); ?></a>
		</li>
		<?php
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			?>
			<li class="<?php if ( $active_tab === 'addhall' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=exam_hall&tab=addhall&action=edit&hall_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['hall_id'] ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addhall' ? 'active' : ''; ?>"> <?php esc_html_e( 'Edit Exam Hall', 'mjschool' ); ?></a>
			</li>
			<?php
		} elseif ( $active_tab === 'addhall' ) {
			?>
			<li class="<?php if ( $active_tab === 'addhall' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=exam_hall&tab=addhall' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addhall' ? 'active' : ''; ?>"> <?php esc_html_e( 'Add Exam Hall', 'mjschool' ); ?></a>
			</li>
			<?php
		}
		?>
		<li class="<?php if ( $active_tab === 'exam_hall_receipt' ) { ?> active<?php } ?>">
			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=exam_hall&tab=exam_hall_receipt&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'exam_hall_receipt' ? 'active' : ''; ?>"> <?php esc_html_e( 'Exam Hall Receipt', 'mjschool' ); ?></a>
		</li>
	</ul>
	<?php
	// -------------- Exam hall list tab. -------------//
	if ( $active_tab === 'hall_list' ) {

		// Check nonce for exam hall list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_exam_hall_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}

		$user_id = get_current_user_id();
		if ( $school_obj->role === 'supportstaff' || $school_obj->role === 'teacher' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$retrieve_class_data = mjschool_get_all_exam_hall_by_user_id( $tablename );
			} else {
				$retrieve_class_data = mjschool_get_all_data( $tablename );
			}
		} else {
			$retrieve_class_data = mjschool_get_all_data( $tablename );
		}
		if ( ! empty( $retrieve_class_data ) ) {
			?>
			<div class="mjschool-panel-body"><!--------------- Panel body. -------------->
				<div class="table-responsive"><!--------------- Table responsive. -------------->
					<!---------------- Exam hall list form. ---------------->
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<table id="hall_list_frontend" class="display dataTable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $mjschool_role_name === 'supportstaff' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
										<?php
									}
									?>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Exam Hall', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Hall Numeric Value', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Hall Capacity', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
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
										<?php
										if ( $mjschool_role_name === 'supportstaff' ) {
											?>
											<td class="mjschool-checkbox-width-10px"><input type="checkbox" name="id[]" class="mjschool-sub-chk select-checkbox" value="<?php echo esc_attr( $retrieved_data->hall_id ); ?>"></td>
											<?php
										}
										?>
										<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
											<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->hall_id ); ?>" type="examhall_view">
												
												<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png"); ?>" class="mjschool-massage-image">
												</p>
												
											</a>
										</td>
										<td class="mjschool-width-25px">
											<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->hall_id ); ?>" type="examhall_view"><?php echo esc_html( stripslashes( $retrieved_data->hall_name ) ); ?></a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Hall', 'mjschool' ); ?>"></i>
										</td>
										<td class="mjschool-width-10px"><?php echo esc_attr( $retrieved_data->number_of_hall ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Hall Numeric Value', 'mjschool' ); ?>"></i></td>
										<td class="mjschool-width-10px"><?php echo esc_attr( $retrieved_data->hall_capacity ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Hall Capacity', 'mjschool' ); ?>"></i></td>
										<?php
										$Description     = $retrieved_data->description;
										$description_msg = strlen( $Description ) > 50 ? substr( $Description, 0, 50 ) . '...' : $Description;
										?>
										<td>
											<?php
											if ( $retrieved_data->description ) {
												echo esc_html( stripslashes( $description_msg ) );
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $Description ) ) { echo esc_attr( $Description ); } else { esc_html_e( 'Description', 'mjschool' ); } ?>"></i>
										</td>
										<?php
										// Custom Field Values.
										if ( ! empty( $user_custom_field ) ) {
											foreach ( $user_custom_field as $custom_field ) {
												if ( $custom_field->show_in_table === '1' ) {
													$module             = 'examhall';
													$custom_field_id    = $custom_field->id;
													$module_record_id   = $retrieved_data->hall_id;
													$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
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
																<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
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
															<li class="mjschool-float-left-width-100px">
																<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->hall_id ); ?>" type="examhall_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View exam hall', 'mjschool' ); ?></a>
															</li>
															<?php
															if ( $user_access['edit'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=exam_hall&tab=addhall&action=edit&hall_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->hall_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																</li>
																<?php
															}
															if ( $user_access['delete'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=exam_hall&tab=hall_list&action=delete&hall_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->hall_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
									<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
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
					</form><!---------------- Exam hall list form. ---------------->
				</div><!--------------- Table responsive. -------------->
			</div><!--------------- Panel body. -------------->
			<?php
		} else {
			if ($user_access['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=exam_hall&tab=addhall') ); ?>">
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
	// ---------------- Add exam all tab. ---------------//
	if ( $active_tab === 'addhall' ) {
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$edit      = 1;
			$hall_data = mjschool_get_hall_by_id( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['hall_id'])) ) );
		}
		?>
		<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
			<form name="hall_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="hall_form">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<div class="form-body mjschool-user-form"><!-------- Form body. -------->
					<div class="row"><!-------- Row div. -------->
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="hall_name" class="form-control validate[required,custom[popup_category_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $hall_data->hall_name ); } ?>" name="hall_name">
									<label for="hall_name"><?php esc_html_e( 'Hall Name', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="number_of_hall" class="form-control validate[required,custom[onlyNumberSp]]" maxlength="5" type="text" value="<?php if ( $edit ) { echo esc_attr( $hall_data->number_of_hall ); } ?>" name="number_of_hall">
									<label for="number_of_hall"><?php esc_html_e( 'Hall Numeric Value', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<?php wp_nonce_field( 'save_hall_admin_nonce' ); ?>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="hall_capacity" class="form-control validate[required,custom[onlyNumberSp]]" maxlength="5" type="text" value="<?php if ( $edit ) { echo esc_attr( $hall_data->hall_capacity ); } ?>" name="hall_capacity">
									<label for="hall_capacity"><?php esc_html_e( 'Hall Capacity', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea name="description" id="description" maxlength="150" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]"><?php if ( $edit ) { echo esc_attr( $hall_data->description ); } ?></textarea>
										<span class="mjschool-txt-title-label"></span>
										<label for="description" class="text-area address active"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div><!-------- Row Div. -------->
				</div><!-------- Form body. -------->
				<?php
				// --------- Get module-wise custom field data. --------------//
				$custom_field_obj = new Mjschool_Custome_Field();
				$module           = 'examhall';
				$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6">
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Hall', 'mjschool' ); } else { esc_html_e( 'Add Hall', 'mjschool' ); } ?>" name="save_hall" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
	if ( $active_tab === 'exam_hall_receipt' ) {
		// Check nonce for exam hall list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_exam_hall_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		?>
		<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res"><!-------- Panel body. -------->
			<form name="receipt_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="receipt_form">
				<div class="form-body mjschool-user-form"><!-------- Form body. -------->
					<div class="row">
						<div class="col-md-9 input">
							<label class="ml-1 mjschool-custom-top-label top" for="exam_id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<?php
							$page        = 'exam';
							$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
							$own_data    = $user_access['own_data'];
							$obj_exam    = new Mjschool_exam();
							$user_id     = get_current_user_id();
							if ( $own_data === '1' ) {
								if ( $school_obj->role === 'teacher' ) {
									$class_id = get_user_meta( get_current_user_id(), 'class_name', true );
									$retrieve_class_data = $obj_exam->mjschool_get_all_exam_by_class_id_created_by( $class_id, $user_id );
								} else {
									$retrieve_class_data = $obj_exam->mjschool_get_all_exam_created_by( $user_id );
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
							<select name="exam_id" class="mjschool-line-height-30px form-control validate[required] exam_hall_receipt" id="exam_id">
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
									<option value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" <?php selected( $retrieved_data->exam_id, $exam_id ); ?>><?php echo esc_html( $retrieved_data->exam_name ) . '( ' . esc_html( mjschool_get_class_section_name_wise( $cid, $retrieved_data->section_id ) ) . ' )'; ?></option>
									<?php
								}
								?>
							</select>
						</div>
						<div class="form-group col-md-3">
							<input type="button" value="<?php esc_html_e( 'Search Exam', 'mjschool' ); ?>" name="search_exam" id="search_exam" class="btn btn-info search_exam mjschool-save-btn" />
						</div>
					</div>
				</div><!-------- Form body. -------->
			</form>
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="mjschool-exam-hall-receipt-div"></div>
			</div>
		</div> <!-------- Panel body. -------->
		<?php
	}
	?>
</div><!----------- Panel body. ----------->