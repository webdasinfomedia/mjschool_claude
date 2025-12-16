<?php
/**
 * Admin Exam Hall Management Interface.
 *
 * This file handles the backend management of exam halls within the MJSchool plugin. 
 * It provides full CRUD (Create, Read, Update, Delete) functionality, access control, 
 * and automated email and push notification features for exam hall receipts.
 *
 * Key Features:
 * - Manages adding, editing, deleting, and listing exam halls.
 * - Implements WordPress nonces and sanitization for secure form submissions.
 * - Integrates role-based access permissions for administrators and other user roles.
 * - Supports custom fields specific to the "examhall" module.
 * - Handles bulk deletion of records with confirmation prompts.
 * - Sends exam hall receipt emails and push notifications to assigned students.
 * - Displays dynamic tables with DataTables (responsive, sortable, searchable).
 * - Integrates AJAX validation and jQuery event handling for better UX.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/hall
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// Check Browser Javascript.
mjschool_browser_javascript_check();
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'examhall';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
$mjschool_role              = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role == 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'exam_hall' );
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
			if ( 'exam_hall' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'exam_hall' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'exam_hall' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
// Send Mail For exam receipt.
if ( isset( $_POST['send_mail_exam_receipt'] ) ) {
	$exam_id = intval( sanitize_text_field(wp_unslash($_POST['exam_id'])) );
	global $wpdb;
	$table_name_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
	 // phpcs:ignore
    $student_data_asigned = $wpdb->get_results($wpdb->prepare( "SELECT user_id FROM $table_name_mjschool_exam_hall_receipt where exam_id=%d", $exam_id ) );
	if ( ! empty( $student_data_asigned ) ) {
		$device_token = array();
		foreach ( $student_data_asigned as $student_id ) {
			$device_token[] = get_user_meta( $student_id->user_id, 'token_id', true );
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
		// Start Send Push Notification.
		$title             = esc_html__( 'New Notification For Exam Receipt', 'mjschool' );
		$text              = esc_html__( 'Your Exam Hall Receipt has been generated.', 'mjschool' );
		$notification_data = array(
			'registration_ids' => $device_token,
			'data'             => array(
				'title' => $title,
				'body'  => $text,
				'type'  => 'Message',
			),
		);
		$json              = json_encode( $notification_data );
		$message           = mjschool_send_push_notification( $json );
		$nonce = wp_create_nonce( 'mjschool_exam_hall_tab' );
		// End Send Push Notification.
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hall&tab=exam_hall_receipt&_wpnonce=' . rawurlencode( $nonce ) . '&message=4' ) );
		die();
	}
}
// Delete record.
$tablename = 'mjschool_hall';
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'delete_action' ) ) {
		$result = mjschool_delete_hall( $tablename, intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['hall_id'])) ) ) );
		if ( $result ) {
			$nonce = wp_create_nonce( 'mjschool_exam_hall_tab' );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hall&tab=hall_list&_wpnonce=' . rawurlencode( $nonce ) . '&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	$nonce = wp_create_nonce( 'mjschool_exam_hall_tab' );
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $ids as $id ) {
			$result = mjschool_delete_hall( $tablename, $id );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hall&tab=hall_list&_wpnonce=' . rawurlencode( $nonce ) . '&message=3' ) );
			die();
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hall&tab=hall_list&_wpnonce=' . rawurlencode( $nonce ) . '&message=3' ) );
		die();
	}
}
// Insert and update.
if ( isset( $_POST['save_hall'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
	if ( wp_verify_nonce( $nonce, 'save_hall_admin_nonce' ) ) {
		$created_date = date( 'Y-m-d H:i:s' );
		$hall_data    = array(
			'hall_name'      => sanitize_textarea_field(wp_unslash($_POST['hall_name'])),
			'number_of_hall' => sanitize_text_field(wp_unslash($_POST['number_of_hall'])),
			'hall_capacity'  => sanitize_text_field(wp_unslash($_POST['hall_capacity'])),
			'description'    => sanitize_textarea_field(wp_unslash($_POST['description'])),
			'date'           => $created_date,
			'created_by'     => get_current_user_id(),
		);
		$tablename    = 'mjschool_hall';
		$nonce = wp_create_nonce( 'mjschool_exam_hall_tab' );
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'edit_action' ) ) {
				$hall_id             = intval( sanitize_text_field(wp_unslash($_REQUEST['hall_id'])) );
				$transport_id        = array( 'hall_id' => intval( sanitize_text_field(wp_unslash($_REQUEST['hall_id'])) ) );
				$result              = mjschool_update_record( $tablename, $hall_data, $transport_id );
				$custom_field_obj    = new Mjschool_Custome_Field();
				$module              = 'examhall';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $hall_id );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hall&tab=hall_list&_wpnonce=' . rawurlencode( $nonce ) . '&message=2' ) );
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
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hall&tab=hall_list&_wpnonce=' . rawurlencode( $nonce ) . '&message=1' ) );
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
<!-- End POP-UP Code. -->
<div class="mjschool-page-inner"><!-------- Page Inner. -------->
	<div class="mjschool-class-list mjschool-main-list-margin-5px">
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Exam Hall Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Exam Hall Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Exam Hall Deleted Successfully.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Exam Hall Mail Send Successfully.', 'mjschool' );
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
		<div class="mjschool-panel-white"><!-------- Panel White. -------->
			<div>
				<?php $nonce = wp_create_nonce( 'mjschool_exam_hall_tab' ); ?>
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
					<li class="<?php if ( $active_tab === 'hall_list' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hall&tab=hall_list&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'hall_list' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Exam Hall List', 'mjschool' ); ?>
						</a>
					</li>
					<?php
					$mjschool_action = '';
					if ( ! empty( $_REQUEST['action'] ) ) {
						$mjschool_action = sanitize_text_field(wp_unslash($_REQUEST['action']));
					}
					if ( $active_tab === 'addhall' && $mjschool_action === 'edit' ) {
						?>
						<li class="<?php if ( $active_tab === 'addhall' ) { ?> active<?php } ?>">
							<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addhall' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Edit Exam Hall', 'mjschool' ); ?>
							</a>
						</li>
						<?php
					} elseif ( $active_tab === 'addhall' ) {
						?>
						<li class="<?php if ( $active_tab === 'addhall' ) { ?> active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hall&tab=addhall' ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addhall' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Add Exam Hall', 'mjschool' ); ?>
							</a>
						</li>
						<?php
					}
					?>
					<li class="<?php if ( $active_tab === 'exam_hall_receipt' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hall&tab=exam_hall_receipt&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'exam_hall_receipt' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Exam Hall Receipt', 'mjschool' ); ?>
						</a>
					</li>
				</ul>
				<?php
				if ( $active_tab === 'hall_list' ) {

					// Check nonce for exam hall list tab.
					if ( isset( $_GET['tab'] ) ) {
						if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_exam_hall_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}

					$retrieve_class_data = mjschool_get_all_data( $tablename );
					if ( ! empty( $retrieve_class_data ) ) {
						?>
						<div class="table-responsive mjschool-margin-top-20px">
							<form id="mjschool-common-form" name="mjschool-common-form" method="post">
								<table id="hall_list_admin" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Hall Name', 'mjschool' ); ?></th>
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
												<td class="mjschool-checkbox-width-10px"><input type="checkbox" name="id[]" class="mjschool-sub-chk select-checkbox" value="<?php echo esc_attr( $retrieved_data->hall_id ); ?>"></td>
												<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
													<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->hall_id ); ?>" type="examhall_view">
														<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">	
															
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png")?>" class="mjschool-massage-image">
															
														</p>
													</a>
												</td>
												<td>
													<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->hall_id ); ?>" type="examhall_view">
														<?php echo esc_html( wp_unslash( $retrieved_data->hall_name ) ); ?>
													</a><i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Hall Name', 'mjschool' ); ?>"></i>
												</td>
												<td><?php echo esc_html( $retrieved_data->number_of_hall ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Hall Numeric Value', 'mjschool' ); ?>"></i></td>
												<td><?php echo esc_html( $retrieved_data->hall_capacity ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Hall Capacity', 'mjschool' ); ?>"></i></td>
												<?php
												$Description     = $retrieved_data->description;
												$description_msg = strlen( $Description ) > 30 ? substr( $Description, 0, 30 ) . '...' : $Description;
												?>
												<td>
													<?php
													if ( $retrieved_data->description ) {
														echo esc_html( wp_unslash( $description_msg ) );
													} else {
														esc_html_e( 'N/A', 'mjschool' ); 
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $Description ) ) { echo esc_attr( $Description ); } else { esc_attr_e( 'Description', 'mjschool' );} ?>"></i>
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
																		<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>" download="CustomFieldfile">
																			<button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
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
																	
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-more.png")?>">
																	
																</a>
																<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																	<li class="mjschool-float-left-width-100px">
																		<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->hall_id ); ?>" type="examhall_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View Exam Hall', 'mjschool' ); ?></a>
																	</li>
																	<?php
																	if ( $user_access_edit === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hall&tab=addhall&action=edit&hall_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->hall_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																	if ( $user_access_delete === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hall&tab=hall_list&action=delete&hall_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->hall_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																			<i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
								<div class="mjschool-print-button pull-left">
									<button class="mjschool-btn-sms-color mjschool-button-reload">
										<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->hall_id ); ?>" >
										<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
									</button>
									<?php
									if ( $user_access_delete === '1' ) {
										 ?>
										<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected','mjschool' );?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
										<?php 
									}
									?>
								</div>
							</form>
						</div>
						<?php
					} elseif ( $user_access_add === '1' ) {
						?>
						<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hall&tab=addhall' ) ); ?>">
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
				if ( $active_tab === 'addhall' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/hall/add-hall.php';
				}
				if ( $active_tab === 'exam_hall_receipt' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/hall/exam-hall-receipt.php';
				}
				?>
			</div>
		</div><!-------- Panel White. -------->
	</div>
</div>