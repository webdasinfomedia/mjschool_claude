<?php
/**
 * Admin Event Management Page.
 *
 * This file provides the complete administrative interface for managing events 
 * within the Mjschool plugin. It enables administrators to create, edit, view, 
 * and delete events with built-in access-right validation, nonce security, 
 * file uploads, and custom field integration.
 *
 * Key Features:
 * - Role-based access control for event operations (add, edit, delete, view).
 * - Secure event creation and update using WordPress nonces.
 * - File upload handling with size and format validation.
 * - Event listing with DataTables, including search, sort, and bulk selection.
 * - Support for custom fields displayed dynamically in event tables.
 * - AJAX-based modal view for detailed event information.
 * - User-friendly UI with tooltips, icons, and accessibility enhancements.
 * - Validation and date/time consistency checks before saving.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/event
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
mjschool_browser_javascript_check();
$mjschool_role               = mjschool_get_user_role( get_current_user_id() );
$mjschool_obj_event = new Mjschool_Event_Manage();
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'event' );
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
			if ( 'event' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'event' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'event' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'event';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'eventlist';
// ------------------ SAVE EVENT. --------------------//
if ( isset( $_POST['save_event'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_event_nonce' ) ) {
		if ( $_FILES['upload_file']['name'] != '' && $_FILES['upload_file']['size'] > 0 ) {
			if ( $_FILES['upload_file']['size'] > 0 ) {
				$file_name = mjschool_load_documets_new( $_FILES['upload_file'], $_FILES['upload_file'], sanitize_text_field( wp_unslash( $_POST['upload_file'] ) ) );
			}
		} elseif ( isset( $_REQUEST['hidden_upload_file'] ) ) {
			$file_name = sanitize_text_field( wp_unslash($_REQUEST['hidden_upload_file']));
		}
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$event_id = intval( wp_unslash($_REQUEST['event_id']) );
				$result   = $mjschool_obj_event->mjschool_insert_event(  wp_unslash($_POST), $file_name );
				// Update Custom Field Data.
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'event';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $event_id );
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_event&tab=eventlist&message=2' ) );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$start_am_pm = '';
			$end_am_pm   = '';
			$start_date = sanitize_text_field( wp_unslash($_POST['start_date']) );
			$end_date = sanitize_text_field( wp_unslash($_POST['end_date']) );
			$start_time_1    = sanitize_text_field( wp_unslash($_POST['start_time']) );
			$end_time_1      = sanitize_text_field( wp_unslash($_POST['end_time']) );
			$start_time_data = explode( ':', $start_time_1 );
			$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
			$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
			$start_am_pm     = $start_time_data[2];
			$start_time_new  = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
			$start_time_in_24_hour_format = date( 'H:i', strtotime( $start_time_new ) );
			$end_time_data                = explode( ':', $end_time_1 );
			$end_hour                     = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
			$end_min                      = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
			$end_am_pm                    = $end_time_data[2];
			$end_time_new                 = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
			$end_time_in_24_hour_format   = date( 'H:i', strtotime( $end_time_new ) );
			if ( $start_date === $end_date && $start_time_in_24_hour_format >= $end_time_in_24_hour_format ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_event&tab=eventlist&message=4' ) );
				die();
			} else {
				$result                    = $mjschool_obj_event->mjschool_insert_event(  wp_unslash($_POST) , $file_name );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'event';
				$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_event&tab=eventlist&message=1' ) );
					die();
				}
			}
		}
	}
}
// --------------- Delete a single event. ----------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = $mjschool_obj_event->mjschool_delete_event( intval( mjschool_decrypt_id( wp_unslash($_REQUEST['event_id']) ) ) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_event&tab=eventlist&message=3' ) );
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// --------------- Delete Multiple Events. -----------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $ids as $id ) {
			$result = $mjschool_obj_event->mjschool_delete_event( $id );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_event&tab=eventlist&message=3' ) );
		}
	}
}
?>
<!-- View Popup Code. -->	
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">   
		<div class="mjschool-notice-content"></div>  
		<div class="modal-content">
			<div class="view_popup"></div>     
		</div>  
	</div>     
</div>
<div class="mjschool-page-inner"><!-- mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Event Inserted successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Event Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Event Deleted Successfully.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'End time must be greater than start time.', 'mjschool' );
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
					if ( $active_tab === 'eventlist' ) {
						$retrieve_event = $mjschool_obj_event->mjschool_get_all_event();
						if ( ! empty( $retrieve_event ) ) {
							?>
							<div>
								<div class="table-responsive"><!-------- Table Responsive. --------->
									<!-------- Exam List Form. --------->
									<form id="mjschool-common-form" name="mjschool-common-form" method="post">
										<table id="event_list" class="display" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
													<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Event Title', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Start Date', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'End Date', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Start Time', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'End Time', 'mjschool' ); ?></th>
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
												foreach ( $retrieve_event as $retrieved_data ) {
													$color_class_css = mjschool_table_list_background_color( $i );
													?>
													<tr>
														<td class="mjschool-checkbox-width-10px">
															<input type="checkbox" class="mjschool-sub-chk sub_chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->event_id ); ?>">
														</td>
														<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
															<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->event_id ); ?>" type="event_view">
																<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">	
                                                                    
                                                                    <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-notice.png")?>" height= "30px" width ="30px" class="mjschool-massage-image">
                                                                    
																</p>
															</a>
														</td>
														<td>
															<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->event_id ); ?>" type="event_view">
																<?php echo esc_html( $retrieved_data->event_title ); ?>
															</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Event Title', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Start Date', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'End Date', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( mjschool_time_remove_colon_before_am_pm( $retrieved_data->start_time ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Start Time', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( mjschool_time_remove_colon_before_am_pm( $retrieved_data->end_time ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'End Time', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( ! empty( $retrieved_data->description ) ) {
																$comment       = $retrieved_data->description;
																$grade_comment = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
																echo esc_html( $grade_comment );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->description ) ) { echo esc_attr( $retrieved_data->description ); } else { esc_attr_e( 'Description', 'mjschool' );} ?>"></i>
														</td>
														<?php
														// Custom Field Values.
														if ( ! empty( $user_custom_field ) ) {
															foreach ( $user_custom_field as $custom_field ) {
																if ( $custom_field->show_in_table === '1' ) {
																	$module             = 'event';
																	$custom_field_id    = $custom_field->id;
																	$module_record_id   = $retrieved_data->event_id;
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
																				<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
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
																				<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->event_id ); ?>" type="event_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																			</li>
																			<?php
																			if ( ! empty( $retrieved_data->event_doc ) ) {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a target="blank" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $retrieved_data->event_doc ) ); ?>" class="mjschool-status-read mjschool-float-left-width-100px" record_id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>"><i class="fas fa-eye"></i><?php esc_html_e( 'View Document', 'mjschool' ); ?></a>
																				</li>
																				<?php
																			}
																			if ( $user_access_edit === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_event&tab=add_event&action=edit&event_id='. rawurlencode( mjschool_encrypt_id( $retrieved_data->event_id ) ) .'&_wpnonce_action='. rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																				</li>
																				<?php
																			}
																			if ( $user_access_delete === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_event&tab=eventlist&action=delete&event_id='. rawurlencode( mjschool_encrypt_id( $retrieved_data->event_id ) ) .'&_wpnonce_action='. rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
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
												<input type="checkbox" id="select_all" name="" class="sub_chk select_all mjschool-sub-chk mjchool_margin_top_0px" value="">
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
									</form><!-------- Exam List Form. --------->
								</div><!-------- Table Responsive. --------->
							</div>
							<?php
						} else {
                            
                            if ( $user_access_add==='1' )
							{
                                ?>
                                <div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px"> 
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_event&tab=add_event' ) );?>">
                                        <img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
                                    </a>
                                    <div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
                                        <label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.','mjschool' ); ?> </label>
                                    </div> 
                                </div>		
                                <?php
                            }
                            else
                            {
                                ?>
                                <div class="mjschool-calendar-event-new"> 
                                    <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
                                </div>		
                                <?php
                            }
                            
						}
					}
					if ( $active_tab === 'add_event' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/event/add-event.php';
					}
					?>
				</div><!-- mjschool-main-list-page. -->
			</div><!-- col-md-12. -->
		</div><!-- row. -->
	</div><!-- mjschool-main-list-margin-15px. -->
</div><!-- mjschool-page-inner. -->