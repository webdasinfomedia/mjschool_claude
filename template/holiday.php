<?php
/**
 * Grade Management View/Template.
 *
 * This file is responsible for rendering the user interface for managing grades.
 * It initializes necessary objects, such as the custom field handler, and retrieves
 * module-specific data (custom fields for the 'grade' module) based on the current
 * user's role for display or form processing.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
// --------------- Access-wise role. -----------//
$mjschool_role_name   = mjschool_get_user_role( get_current_user_id() );
$user_access = mjschool_get_userrole_wise_access_right_array();
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
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'holiday';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
$table_mjschool_holiday         = 'mjschool_holiday';
// --------------------- Delete holiday. --------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = mjschool_delete_holiday( $table_mjschool_holiday, intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['holiday_id'] ) ) ) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=holiday&tab=holidaylist&message=3' ) );
			exit;
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_holiday_admin_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_holiday( $table_mjschool_holiday, intval( $id ) );
		}
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=holiday&tab=holidaylist&message=3' ) );
		exit;
	}
}
// ------------------- Save holiday. --------------------/
if ( isset( $_POST['save_holiday'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_holiday_admin_nonce' ) ) {
		$start_date = date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_REQUEST['date'])) ) );
		$end_date   = date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_REQUEST['end_date'])) ) );
		$exlude_id  = mjschool_approve_student_list();
		if ( $start_date > $end_date ) { ?>
			<div class="mjschool-date-error-trigger" data-error="1"></div>
			<?php
		} else {
			
			$query_data['exclude'] = $exlude_id;
			
			$results      = get_users( $query_data );
			$haliday_data = array(
				'holiday_title' => sanitize_text_field( wp_unslash( $_POST['holiday_title'] ) ),
				'description'   => sanitize_textarea_field( wp_unslash( $_POST['description'] ) ),
				'date'          => date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_POST['date'])) ) ),
				'end_date'      => date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_POST['end_date'])) ) ),
				'created_by'    => get_current_user_id(),
				'created_date'  => date( 'Y-m-d H:i:s' ),
				'status'        => 1,
			);
			// Table name without prefix.
			$table_mjschool_holiday = 'mjschool_holiday';
			if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
				if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
					$holiday_ids = array( 'holiday_id' => intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['holiday_id'] ) ) ) ) );
					$holiday_id  = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['holiday_id'])) ) );
					$result              = mjschool_update_record( $table_mjschool_holiday, $haliday_data, $holiday_ids );
					$custom_field_obj    = new Mjschool_Custome_Field();
					$module              = 'holiday';
					$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $holiday_id );
					if ( $result ) {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=holiday&tab=holidaylist&message=2' ) );
						exit;
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else {
				$startdate = strtotime( sanitize_text_field(wp_unslash($_POST['date'])) );
				$enddate   = strtotime( sanitize_text_field(wp_unslash($_POST['end_date'])) );
				if ( $startdate === $enddate ) {
					$date = sanitize_text_field( wp_unslash( $_POST['date'] ) );
				} else {
					$date = sanitize_text_field( wp_unslash( $_POST['date'] ) ) . ' To ' . sanitize_text_field( wp_unslash( $_POST['end_date'] ) );
				}
				$AllUsr       = mjschool_get_all_user_in_plugin();
				$device_token = array();
				$to           = array();
				foreach ( $AllUsr as $key => $usr ) {
					$device_token[] = get_user_meta( $usr->ID, 'token_id', true );
					$to[] = $usr->user_email;
				}
				$result                 = mjschool_insert_record( $table_mjschool_holiday, $haliday_data );
				$custom_field_obj       = new Mjschool_Custome_Field();
				$module             = 'holiday';
				$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					if (isset($_POST['mjschool_enable_holiday_mail']) && sanitize_text_field(wp_unslash($_POST['mjschool_enable_holiday_mail'])) === '1' ) {
						foreach ( $to as $email ) {
							$Search['{{holiday_title}}'] = mjschool_strip_tags_and_stripslashes( sanitize_text_field( wp_unslash( $_POST['holiday_title'] ) ) );
							$Search['{{holiday_date}}'] = $date;
							$Search['{{school_name}}'] = get_option( 'mjschool_name' );
							$message = mjschool_string_replacement( $Search, get_option( 'mjschool_holiday_mailcontent' ) );
							mjschool_send_mail( $email, get_option( 'mjschool_holiday_mailsubject' ), $message );
						}
					}
					if ( isset( $_POST['mjschool_enable_holiday_sms'] ) && sanitize_text_field(wp_unslash($_POST['mjschool_enable_holiday_sms'])) === '1' ) {
						foreach ( $AllUsr as $key => $usr ) {
							$SMSCon                     = get_option( 'mjschool_holiday_mjschool_content' );
							$SMSArr['{{student_name}}'] = $usr->display_name;
							$SMSArr['{{title}}']        = mjschool_strip_tags_and_stripslashes( sanitize_text_field( wp_unslash( $_POST['holiday_title'] ) ) );
							$SMSArr['{{school_name}}']  = get_option( 'mjschool_name' );
							$message_content            = mjschool_string_replacement( $SMSArr, $SMSCon );
							$type                       = 'Holiday';
							mjschool_send_mjschool_notification( $usr->ID, $type, $message_content );
						}
					}
					// Send Push Notification. //
					$title             = esc_html__( 'Holiday Announcement', 'mjschool' );
					$notification_data = array(
						'registration_ids' => $device_token,
						'notification'     => array(
							'title' => $title,
							'body'  => mjschool_strip_tags_and_stripslashes( sanitize_text_field( $_POST['holiday_title'] ) ),
							'type'  => 'holiday',
						),
					);
					$json              = json_encode( $notification_data );
					mjschool_send_push_notification( $json );
					// End Send Push Notification.//
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=holiday&tab=holidaylist&message=1') );
					die();
				}
			}
		}
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'holidaylist';
?>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Holiday Added Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Holiday Updated Successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Holiday Deleted Successfully.', 'mjschool' );
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
	?>
	<?php
	if ( $active_tab === 'holidaylist' ) {
		// --------------------- Holday list page.  --------------//
		$user_id = get_current_user_id();
		if ( $school_obj->role === 'supportstaff' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$retrieve_class_data = mjschool_get_all_holiday_created_by( $user_id );
			} else {
				$retrieve_class_data = mjschool_get_all_data( 'mjschool_holiday' );
			}
		} else {
			$retrieve_class_data = mjschool_get_all_data( 'mjschool_holiday' );
		}
		?>
		<div class="mjschool-panel-body">
			<?php
			if ( ! empty( $retrieve_class_data ) ) {
				?>
				<div class="table-responsive">
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<table id="frontend_holiday_list" class="display dataTable" cellspacing="0" width="100%">
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
									<th><?php esc_html_e( 'Holiday Title', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Holiday Start Date', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Holiday End Date', 'mjschool' ); ?></th>
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
									<?php
									if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
										?>
										<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										<?php
									}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 0;
								foreach ( $retrieve_class_data as $retrieved_data ) {
									$color_class_css = mjschool_table_list_background_color( $i );
									if ( $retrieved_data->status === '0' || $retrieved_data->created_by === get_current_user_id() ) {
										?>
										<tr>
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<td class="mjschool-checkbox-width-10px">
													<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->holiday_id ); ?>">
												</td>
												<?php
											}
											?>
											<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
												
												<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-holiday.png"); ?>" height="30px" width="30px" class="mjschool-massage-image">
												</p>
												
											</td>
											<td><?php echo esc_html( $retrieved_data->holiday_title ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Holiday Title', 'mjschool' ); ?>"></i></td>
											<td>
												<?php
												if ( ! empty( $retrieved_data->description ) ) {
													$strlength = strlen( $retrieved_data->description );
													if ( $strlength > 30 ) {
														echo esc_html( substr( $retrieved_data->description, 0, 30 ) ) . '...';
													} else {
														echo esc_html( $retrieved_data->description );
													}
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->description ) ) { echo esc_html( $retrieved_data->description ); } else { esc_html_e( 'Description', 'mjschool' ); } ?>"></i>
											</td>
											<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Holiday Start Date', 'mjschool' ); ?>"></i></td>
											<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Holiday End Date', 'mjschool' ); ?>"></i></td>
											<td>
												<?php
												if ( $retrieved_data->status === 0 ) {
													echo "<span class='mjschool-green-color'>";
													esc_html_e( 'Approve', 'mjschool' );
													echo '</span>';
												} else {
													echo "<span class='mjschool-red-color'>";
													esc_html_e( 'Not Approve', 'mjschool' );
													echo '</span>';
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
											</td>
											<?php
											// Custom Field Values.
											if ( ! empty( $user_custom_field ) ) {
												foreach ( $user_custom_field as $custom_field ) {
													if ( $custom_field->show_in_table === '1' ) {
														$module = 'holiday';
														$custom_field_id    = $custom_field->id;
														$module_record_id   = $retrieved_data->holiday_id;
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
																	<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
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
											if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
												?>
												<td class="action">
													<div class="mjschool-user-dropdown">
														<ul  class="mjschool_ul_style">
															<li >
																<a  href="#" data-bs-toggle="dropdown" aria-expanded="false"> <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>"> </a>
																
																<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																	<?php
																	if ( $user_access['edit'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																			<a href="?dashboard=mjschool_user&page=holiday&tab=addholiday&action=edit&holiday_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->holiday_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																	if ( $user_access['delete'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="?dashboard=mjschool_user&page=holiday&tab=holidaylist&action=delete&holiday_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->holiday_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
									++$i;
								}
								?>
							</tbody>
						</table>
						<?php
						if ( $mjschool_role_name === 'supportstaff' ) {
							?>
							<div class="mjschool-print-button pull-left">
								<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
									<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
									<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
								</button>
								<?php
								if ( $user_access['delete'] === '1' ) {
									 ?>
									<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									<?php
								} ?>
							</div>
							<?php
						}
						?>
					</form>
				</div>
				<?php
			} else {
				if ($user_access['add'] === '1' ) {
					?>
					<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
						<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=holiday&tab=addholiday' ) ); ?>">
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
			?>
		</div>
		<?php
	}
	if ( $active_tab === 'addholiday' ) {
		// --------------------- Holday add page.  --------------//
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$edit         = 1;
			$holiday_id   = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['holiday_id'])) ) );
			$holiday_data = mjschool_get_holiday_by_id( $holiday_id );
		}
		?>
		<div class="mjschool-panel-body">
			<form name="holiday_form" action="" method="post" class="mjschool-form-horizontal" id="holiday_form_template" enctype="multipart/form-data">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="holiday_id" value="<?php if ( $edit ) { echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['holiday_id'])) ); } ?>" />
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Holiday Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mjschool-holiday-title" class="form-control validate[required,custom[description_validation]] text-input" maxlength="100" type="text" value="<?php if ( $edit ) { echo esc_attr( $holiday_data->holiday_title ); } ?>" name="holiday_title">
									<label for="mjschool-holiday-title"><?php esc_html_e( 'Holiday Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="description" class="form-control validate[custom[description_validation]]" maxlength="1000" type="text" value="<?php if ( $edit ) { echo esc_attr( $holiday_data->description ); } ?>" name="description">
									<label  for="description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="s_date" class="datepicker form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $holiday_data->date ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="date" readonly>
									<label  for="s_date"><?php esc_html_e( 'Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<?php wp_nonce_field( 'save_holiday_admin_nonce' ); ?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="end_date" class="datepicker form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $holiday_data->end_date ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="end_date" readonly>
									<label  for="end_date"><?php esc_html_e( 'End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<?php
						if ( ! $edit ) {
							?>
							<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
								<div class="form-group mb-3">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div>
												<label class="mjschool-custom-top-label" for="mjschool_enable_holiday_mail"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
												<input id="mjschool_enable_holiday_mail" type="checkbox" class="mjschool-check-box-input-margin" name="mjschool_enable_holiday_mail" value="1" <?php echo checked( get_option( 'mjschool_enable_holiday_mail' ), 'yes' ); ?> /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
								<div class="form-group mb-3">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div>
												<label class="mjschool-custom-top-label" for="mjschool_enable_holiday_sms"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
												<input id="mjschool_enable_holiday_sms" type="checkbox" class="mjschool-check-box-input-margin" name="mjschool_enable_holiday_sms" value="1" <?php echo checked( get_option( 'mjschool_enable_holiday_sms' ), 'yes' ); ?> /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$custom_field_obj = new Mjschool_Custome_Field();
				$module           = 'holiday';
				$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6">
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Holiday', 'mjschool' ); } else { esc_html_e( 'Add Holiday', 'mjschool' ); } ?>" name="save_holiday" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
	?>
</div>