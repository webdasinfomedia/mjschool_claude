<?php
/**
 * Admin Holiday Management Page.
 *
 * Handles the CRUD operations (Create, Read, Update, Delete) for holidays within the MJSchool plugin.
 * This file manages access control, form validation, database operations, and rendering of
 * the holiday list in the WordPress admin area.
 *
 * Key Features:
 * - Access control based on user roles.
 * - Add/Edit/Delete/Approve holidays with validation.
 * - AJAX form submission and DataTables integration.
 * - Email, SMS, and push notifications on holiday creation.
 * - Support for custom fields using Mjschool_Custome_Field class.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/holiday
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role == 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'holiday' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
	if ( isset( $_REQUEST ['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			if ( 'holiday' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'holiday' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'holiday' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'holiday';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );

$tablename = 'mjschool_holiday';
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'delete_action' ) ) {
		$result = mjschool_delete_holiday( $tablename, intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['holiday_id'] ) ) ) ) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_holiday&tab=holidaylist&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'approve' ) {
	$holiday_data = mjschool_get_holiday_by_id( intval( sanitize_text_field( wp_unslash( $_REQUEST['holiday_id'] ) ) ) );
	$tablename    = 'mjschool_holiday';
	$haliday_data = array(
		'holiday_title' => $holiday_data->holiday_title,
		'description'   => $holiday_data->description,
		'date'          => $holiday_data->date,
		'end_date'      => $holiday_data->end_date,
		'created_by'    => $holiday_data->created_by,
		'created_date'  => $holiday_data->created_date,
		'status'        => 0,
	);
	$holiday_id   = array( 'holiday_id' => intval( sanitize_text_field( wp_unslash( $_REQUEST['holiday_id'] ) ) ) );
	$result       = mjschool_update_record( $tablename, $haliday_data, $holiday_id );
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_holiday&tab=holidaylist&message=4' ) );
		die();
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $ids as $id ) {
			$result = mjschool_delete_holiday( $tablename, $id );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_holiday&tab=holidaylist&message=3' ) );
			die();
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_holiday&tab=holidaylist&message=3' ) );
		die();
	}
}
if ( isset( $_POST['save_holiday'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_holiday_admin_nonce' ) ) {
		$start_date = date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_REQUEST['date'] ) ) ) );
		$end_date   = date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) ) ) );
		if ( $start_date > $end_date ) {
			?>
			<div class="mjschool-date-error-trigger" data-error="1"></div>
			<?php
		} else {
			$haliday_data = array(
				'holiday_title' => sanitize_textarea_field( wp_unslash( $_POST['holiday_title'] ) ),
				'description'   => sanitize_textarea_field( wp_unslash( $_POST['description'] ) ),
				'date'          => date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['date'] ) ) ) ),
				'end_date'      => date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) ) ),
				'created_by'    => get_current_user_id(),
				'created_date'  => date( 'Y-m-d H:i:s' ),
				'status'        => intval( $_POST['status'] ),
			);
			// table name without prefix.
			$tablename = 'mjschool_holiday';
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
				if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'edit_action' ) ) {
					$holiday_ids         = array( 'holiday_id' => intval( sanitize_text_field( wp_unslash( $_REQUEST['holiday_id'] ) ) ) );
					$holiday_id          = intval( sanitize_text_field( wp_unslash( $_REQUEST['holiday_id'] ) ) );
					$result              = mjschool_update_record( $tablename, $haliday_data, $holiday_ids );
					$custom_field_obj    = new Mjschool_Custome_Field();
					$module              = 'holiday';
					$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $holiday_id );
					if ( $result ) {
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_holiday&tab=holidaylist&message=2' ) );
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else {
				$startdate = strtotime( sanitize_text_field( wp_unslash( $_POST['date'] ) ) );
				$enddate   = strtotime( sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) );
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
					$to[]           = $usr->user_email;
				}
				$result             = mjschool_insert_record( $tablename, $haliday_data );
				$custom_field_obj   = new Mjschool_Custome_Field();
				$module             = 'holiday';
				$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					if ( isset( $_POST['mjschool_enable_holiday_mail'] ) && intval( $_POST['mjschool_enable_holiday_mail'] ) === 1 ) {
						foreach ( $to as $email ) {
							$Search['{{holiday_title}}'] = mjschool_strip_tags_and_stripslashes( sanitize_text_field( wp_unslash( $_POST['holiday_title'] ) ) );
							$Search['{{holiday_date}}']  = $date;
							$Search['{{school_name}}']   = get_option( 'mjschool_name' );
							$message                     = mjschool_string_replacement( $Search, get_option( 'mjschool_holiday_mailcontent' ) );
							mjschool_send_mail( $email, get_option( 'mjschool_holiday_mailsubject' ), $message );
						}
					}
					if ( isset( $_POST['mjschool_enable_holiday_sms'] ) && intval( $_POST['mjschool_enable_holiday_sms'] ) === 1 ) {
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
					// Send Push Notification.
					$title             = esc_html__( 'Holiday Announcement.', 'mjschool' );
					$notification_data = array(
						'registration_ids' => $device_token,
						'data'             => array(
							'title' => $title,
							'body'  => mjschool_strip_tags_and_stripslashes( sanitize_text_field( wp_unslash( $_POST['holiday_title'] ) ) ),
							'type'  => 'holiday',
						),
					);
					$json              = json_encode( $notification_data );
					mjschool_send_push_notification( $json );
					// End Send Push Notification.
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_holiday&tab=holidaylist&message=1' ) );
					die();
				}
			}
		}
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'holidaylist';
?>
<div class="mjschool-page-inner"><!-- mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
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
			case '4':
				$message_string = esc_html__( 'Holiday Approved Successfully.', 'mjschool' );
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
		<div class="row"><!-- row. -->
			<div class="col-md-12 mjschool-custom-padding-0"><!-- col-md-12. -->
				<div class="mjschool-main-list-page"><!-- mjschool-main-list-page. -->
					<?php
					if ( $active_tab === 'holidaylist' ) {
						$retrieve_class_data = mjschool_get_all_holiday_data();
						if ( ! empty( $retrieve_class_data ) ) {
							?>
							<div class="mjschool-panel-body"><!-- mjschool-panel-body. -->
								<div class="table-responsive">
									<form id="mjschool-common-form" name="mjschool-common-form" method="post">
										<table id="holiday_list" class="display" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
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
													<?php if ( $user_access_edit === '1' || $user_access_delete === '1' ) { ?>         
														<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
													<?php } ?>
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
														<td class="mjschool-checkbox-width-10px">
															<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->holiday_id ); ?>">
														</td>
														<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
															<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
																
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-holiday.png")?>" height= "30px" width ="30px" class="mjschool-massage-image">
																
															</p>
														</td>
														<td>
															<?php echo esc_html( $retrieved_data->holiday_title ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Holiday Title', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( ! empty( $retrieved_data->description ) ) {
																$strlength = strlen( $retrieved_data->description );
																if ( $strlength > 50 ) {
																	echo esc_html( substr( $retrieved_data->description, 0, 50 ) ) . '...';
																} else {
																	echo esc_html( $retrieved_data->description );
																}
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->description ) ) { echo esc_attr( $retrieved_data->description ); } else { esc_attr_e( 'Description', 'mjschool' ); } ?>"></i>
														</td>
														<td>
															<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Holiday Start Date', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Holiday End Date', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( $retrieved_data->status === 0 ) {
																echo "<span class='mjschool-green-color'>";
																esc_html_e( 'Approved', 'mjschool' );
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
																	$module             = 'holiday';
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
																				<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile">
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
														<?php if ( $user_access_edit === '1' || $user_access_delete === '1' ) { ?>
															<td class="action">
																<div class="mjschool-user-dropdown">
																	<ul  class="mjschool_ul_style">
																		<li >
																			<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																				
																				<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-more.png")?>">
																				
																			</a>
																			<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																				<?php
																				if ( $retrieved_data->status === 1 ) {
																					?>
																					<li class="mjschool-float-left-width-100px">
																						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday&tab=holidaylist&action=approve&holiday_id=' . intval( $retrieved_data->holiday_id ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-thumbs-up"> </i><?php esc_html_e( 'Approve', 'mjschool' ); ?></a>
																					</li>
																					<?php
																				}
																				if ( $user_access_edit === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday&tab=addholiday&action=edit&holiday_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->holiday_id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																					</li>
																					<?php
																				}
																				?>
																				<?php
																				if ( $user_access_delete === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px">
																						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday&tab=holidaylist&action=delete&holiday_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->holiday_id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i><?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
																					</li>
																					<?php
																				}
																				?>
																			</ul>
																		</li>
																	</ul>
																</div>
															</td>
														<?php } ?>
													</tr>
													<?php
													++$i;
												}
												?>
											</tbody>
										</table>
										<div class="mjschool-print-button pull-left">
											<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
												<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
												<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
											</button>
											<?php
											if ( $user_access_delete === '1' ) {
												 ?>
												<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected','mjschool' );?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
												<?php 
											}
											?>
										</div>
									</form>
								</div>
							</div><!-- mjschool-panel-body. -->
							<?php
						} elseif ( $user_access_add === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday&tab=addholiday' ) ); ?>">
									
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
								
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
								
							</div>		
							<?php
						}
					}
					if ( $active_tab === 'addholiday' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/holiday/add-holiday.php';
					}
					?>
				</div><!-- mjschool-main-list-page. -->
			</div><!-- col-md-12. -->
		</div><!-- row. -->
	</div><!-- mjschool-main-list-margin-15px. -->
</div><!-- mjschool-page-inner. -->