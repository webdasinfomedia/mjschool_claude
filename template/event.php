<?php
/**
 * School Event Management Page.
 *
 * This file serves as the main administrative view and controller for managing
 * **School Events and Holidays** within the Mjschool system. It provides the interface
 * for creating, viewing, editing, and deleting scheduled school events.
 *
 * It is primarily responsible for:
 *
 * 1. **Access Control**: Implementing **role-based access control** by checking the
 * current user's role and specific rights ('view', 'add', 'edit', 'delete')
 * for the 'event' module.
 * 2. **Tab Navigation**: Handling different views via tabs, likely including a list view
 * (`eventlist`) and an add/edit form view (`addevent`).
 * 3. **Form Handling**: Displaying the 'Add/Edit Event' form, which captures:
 * - Event Title and Description.
 * - Start and End Dates (date and time).
 * - Target Audience/Recipients (e.g., all students, specific roles, specific classes).
 * - Optional features like SMS/Email notifications (`smgt_enable_event_mail`, `smgt_enable_event_sms`).
 * 4. **Event List Display**: Rendering a list of scheduled events, likely integrating
 * a full calendar view or a tabular list for easy management.
 * 5. **Custom Fields**: Integrating the `Mjschool_Custome_Field` object to fetch
 * and display any custom fields associated with the 'event' module.
 * 6. **CRUD Operations**: Processing form submissions and URL actions for managing
 * event data in the database.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
mjschool_browser_javascript_check();
$mjschool_role_name          = mjschool_get_user_role( get_current_user_id() );
$mjschool_obj_event = new Mjschool_Event_Manage();;
$active_tab         = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'eventlist';
require_once MJSCHOOL_INCLUDES_DIR . '/class-mjschool-management.php';
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
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'event';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
// ------------------ Save event. --------------------//
if ( isset( $_POST['save_event'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_event_nonce' ) ) {
		if ( $_FILES['upload_file']['name'] != '' && $_FILES['upload_file']['size'] > 0 ) {
			if ( $_FILES['upload_file']['size'] > 0 ) {
				$file_name = mjschool_load_documets_new( $_FILES['upload_file'], $_FILES['upload_file'], sanitize_text_field(wp_unslash($_POST['upload_file'])) );
			}
		} elseif ( isset( $_REQUEST['hidden_upload_file'] ) ) {
			$file_name = sanitize_text_field(wp_unslash($_REQUEST['hidden_upload_file']));
		}
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$event_id = intval( wp_unslash($_REQUEST['event_id'] ) );
				$result   = $mjschool_obj_event->mjschool_insert_event( wp_unslash($_POST), $file_name );
				// Update custom field data.
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'event';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $event_id );
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=event&tab=eventlist&message=2' ) );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$start_date                   = sanitize_text_field(wp_unslash($_POST['start_date']));
			$end_date                     = sanitize_text_field(wp_unslash($_POST['end_date']));
			$start_time_1                 = sanitize_text_field(wp_unslash($_POST['start_time']));
			$end_time_1                   = sanitize_text_field(wp_unslash($_POST['end_time']));
			$start_time_data              = explode( ':', $start_time_1 );
			$start_hour                   = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
			$start_min                    = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
			$start_am_pm                  = $start_time_data[2];
			$start_time_new               = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
			$start_time_in_24_hour_format = date( 'H:i', strtotime( $start_time_new ) );
			$end_time_data                = explode( ':', $end_time_1 );
			$end_hour                     = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
			$end_min                      = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
			$end_am_pm                    = $end_time_data[2];
			$end_time_new                 = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
			$end_time_in_24_hour_format   = date( 'H:i', strtotime( $end_time_new ) );
			if ( $start_date === $end_date && $start_time_in_24_hour_format >= $end_time_in_24_hour_format ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=event&tab=eventlist&message=4' ) );
				die();
			} else {
				$result                    = $mjschool_obj_event->mjschool_insert_event( wp_unslash($_POST), $file_name );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'event';
				$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=event&tab=eventlist&message=1' ) );
					die();
				}
			}
		}
	}
}
// --------------- Delete single event. ----------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = $mjschool_obj_event->mjschool_delete_event( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['event_id'])) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=event&tab=eventlist&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// --------------- Delete multiple event. -----------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_event_nonce' ) ) {
		if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
			foreach ( $_REQUEST['id'] as $id ) {
				$sanitized_id = intval( sanitize_text_field( wp_unslash( $id ) ) );
				$result = $mjschool_obj_event->mjschool_delete_event( $sanitized_id );
			}
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=event&tab=eventlist&message=3' ) );
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
		<div class="mjschool-notice-content"></div>
		<div class="modal-content">
			<div class="view_popup"></div>
		</div>
	</div>
</div>
<!-- End POP-UP code. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!------------ PANEL BODY ------------>
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
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
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
            
            <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
            
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	if ( $active_tab === 'eventlist' ) {
		$user_id = get_current_user_id();
		// ------- Exam data for student. ---------//
		if ( $school_obj->role === 'student' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$retrieve_event = $mjschool_obj_event->mjschool_get_all_event();
			} else {
				$retrieve_event = $mjschool_obj_event->mjschool_get_all_event();
			}
		}
		// ------- Exam data for teacher.. ---------//
		elseif ( $school_obj->role === 'teacher' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$retrieve_event = $mjschool_obj_event->mjschool_get_own_event_list( $user_id );
			} else {
				$retrieve_event = $mjschool_obj_event->mjschool_get_all_event();
			}
		}
		// ------- Exam data for parent.. ---------//
		elseif ( $school_obj->role === 'parent' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$retrieve_event = $mjschool_obj_event->mjschool_get_all_event();
			} else {
				$retrieve_event = $mjschool_obj_event->mjschool_get_all_event();
			}
		}
		// ------- Exam data for support staff.. ---------//
		else {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$retrieve_event = $mjschool_obj_event->mjschool_get_own_event_list( $user_id );
			} else {
				$retrieve_event = $mjschool_obj_event->mjschool_get_all_event();
			}
		}
		if ( ! empty( $retrieve_event ) ) {
			?>
			<div>
				<div class="table-responsive"><!-------- Table responsive. --------->
					<!-------- Exam list form. --------->
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<?php wp_nonce_field( 'save_event_nonce' ); ?>
						<table id="frontend_event_list" class="display" cellspacing="0" width="100%">
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
										<?php
										if ( $mjschool_role_name === 'supportstaff' ) {
											?>
											<td class="mjschool-checkbox-width-10px">
												<input type="checkbox" class="mjschool-sub-chk sub_chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->event_id ); ?>">
											</td>
											<?php
										}
										?>
										<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
											<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->event_id ); ?>" type="event_view">
                                                <p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
                                                    <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-notice.png"); ?>" height="30px" width="30px" class="mjschool-massage-image">
                                                </p>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr($retrieved_data->event_id); ?>" type="event_view">
                                                <?php echo esc_attr($retrieved_data->event_title); ?>
                                            </a>
                                            <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Event Title', 'mjschool' ); ?>"></i>
                                        </td>
                                        <td>
                                            <?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->start_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Start Date', 'mjschool' ); ?>"></i>
                                        </td>
                                        <td>
                                            <?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'End Date', 'mjschool' ); ?>"></i>
                                        </td>
                                        <td>
                                            <?php echo esc_html( $retrieved_data->start_time); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Start Time', 'mjschool' ); ?>"></i>
                                        </td>
                                        <td>
                                            <?php echo esc_html( $retrieved_data->end_time); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'End Time', 'mjschool' ); ?>"></i>
                                        </td>
                                        <td>
                                            <?php
                                            if ( ! empty( $retrieved_data->description ) ) {
                                                $comment = $retrieved_data->description;
                                                $grade_comment = strlen($comment) > 30 ? substr( $comment, 0, 30) . "..." : $comment;
                                                echo esc_html( $grade_comment);
                                            } else {
                                                esc_html_e( 'N/A', 'mjschool' );
                                            }
                                            ?> 
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->description ) ) { echo esc_html( $retrieved_data->description); } else { esc_html_e( 'Description', 'mjschool' ); } ?>"></i>
                                    	</td>
                                        <?php
                                        // Custom field values.
                                        if ( ! empty( $user_custom_field ) ) {
                                            foreach ($user_custom_field as $custom_field) {
                                                if ($custom_field->show_in_table === "1") {
                                                    $module = 'event';
                                                    $custom_field_id = $custom_field->id;
                                                    $module_record_id = $retrieved_data->event_id;
                                                    $custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value($module, $module_record_id, $custom_field_id);
                                                    if ($custom_field->field_type === 'date' ) {
                                                    	?>
                                                        <td><?php 
															if ( ! empty( $custom_field_value ) ) {
                                                                echo esc_html( mjschool_get_date_in_input_box($custom_field_value ) );
                                                            } else {
                                                                esc_html_e( 'N/A', 'mjschool' );
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
																echo esc_html( $custom_field_value);
															} else {
																esc_html_e( 'N/A', 'mjschool' );
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
                                                    <?php
                                                    if ( ! empty( $retrieved_data->exam_syllabus ) ) {
                                                        $doc_data = json_decode($retrieved_data->exam_syllabus);
                                                    }
                                                    ?>
                                                    <li >
                                                        <a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
                                                        </a>
														<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
															<li class="mjschool-float-left-width-100px">
																<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->event_id ); ?>" type="event_view"><i class="fas fa-eye" aria-hidden="true"></i>
																	<?php esc_html_e( 'View', 'mjschool' ); ?>
																</a>
															</li>
															<?php
															if ( ! empty( $retrieved_data->event_doc ) ) {
																?>
																<li class="mjschool-float-left-width-100px">
																	<a target="blank" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $retrieved_data->event_doc )); ?>" class="mjschool-status-read mjschool-float-left-width-100px" record_id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>"><i class="fas fa-eye"></i><?php esc_html_e( 'View Document', 'mjschool' ); ?></a>
																</li>
																<?php
															}
															if ( $user_access['edit'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=event&tab=add_event&action=edit&event_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->event_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																</li>
																<?php
															}
															if ( $user_access['delete'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=event&tab=eventlist&action=delete&event_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->event_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
									<input type="checkbox" name="" class="sub_chk select_all mjschool-sub-chk mjchool_margin_top_0px" value="" >
									<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
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
                    </form><!-------- Exam list form. --------->
                </div><!-------- Table responsive. --------->
            </div>
            <?php
        } else {
            if ($user_access['add'] === '1' ) {
            	?>
                <div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
                    <a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=event&tab=add_event') ); ?>">
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
	if ( $active_tab === 'add_event' ) {
		?>
		<?php
		$event_id = 0;
		$edit     = 0;
		if ( isset( $_REQUEST['event_id'] ) ) {
			$event_id = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['event_id'] ) ) ) );
			$edit     = 0;
		}
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			$edit   = 1;
			$result = $mjschool_obj_event->mjschool_get_single_event( $event_id );
		}
		?>
		<div class="mjschool-panel-body mjschool-custom-padding-0"><!--Panel body.-->
			<form name="event_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="event_form"><!--ADD EVENT FORM-->
				<?php $mjschool_action = sanitize_text_field( isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert' ); ?>
				<input id="action" type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Event Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<div class="row"><!--Row Div start.-->
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="event_title" maxlength="100" class="form-control text-input validate[required,custom[description_validation]]" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->event_title ); } elseif ( isset( $_POST['event_title'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['event_title'])) ); } ?>" name="event_title">
									<label  for="event_title"><?php esc_html_e( 'Event Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea name="description" id="description" maxlength="1000" class="mjschool-textarea-height-60px form-control validate[required,custom[description_validation]] text-input"><?php if ( $edit ) { echo esc_attr( $result->description ); } elseif ( isset( $_POST['description'] ) ) { echo esc_textarea( sanitize_text_field(wp_unslash($_POST['description'])) );} ?></textarea>
										<span class="mjschool-txt-title-label"></span>
										<label class="text-area address active" for="description"><?php esc_html_e( 'Description', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
						</div>
					
						<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="start_date_event" class="form-control validate[required] start_date datepicker1" autocomplete="off" type="text" name="start_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->start_date ) ) ) ); } elseif ( isset( $_POST['start_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
									<label class="active" for="start_date_event"><?php esc_html_e( 'Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input placeholder="<?php esc_html_e( 'Start Time', 'mjschool' ); ?>" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->start_time ); } elseif ( isset( $_POST['start_time'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['start_time'])) );} ?>" class="form-control mjschool-timepicker validate[required]" name="start_time" />
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="end_date_event" class="form-control validate[required] start_date datepicker2" type="text" name="end_date" autocomplete="off" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->end_date ) ) ) ); } elseif ( isset( $_POST['end_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['end_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
									<label for="end_date_event"><?php esc_html_e( 'End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input placeholder="<?php esc_html_e( 'End Time', 'mjschool' ); ?>" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->end_time ); } elseif ( isset( $_POST['end_time'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['end_time'])) );} ?>" class="form-control mjschool-timepicker validate[required]" name="end_time" />
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
									<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-profile-rtl-css" for="Document"><?php esc_html_e( 'Document', 'mjschool' ); ?></span>
									<div class="col-sm-12 mjschool-display-flex">
										<input type="hidden" name="hidden_upload_file" value="<?php if ( $edit ) { echo esc_attr( $result->event_doc ); } elseif ( isset( $_POST['upload_file'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['upload_file'])) );} ?>">
										<input id="upload_file" name="upload_file" type="file" onchange="mjschool_file_check(this);"  />
									</div>
								</div>
							</div>
						</div>
						<?php
						if ( ! $edit ) {
							?>
							<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div>
												<label class="mjschool-custom-top-label" for="mjschool_enable_event_mail"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
												<input id="mjschool_enable_event_mail" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_event_mail" value="1" /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div>
												<label class="mjschool-custom-top-label" for="mjschool_enable_event_sms"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
												<input id="mjschool_enable_event_sms" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_event_sms" value="1" /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
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
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'event';
				$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<!---------- Save btn. -------------->
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<div class="row"><!--Row Div start.-->
						<div class="col-md-6 col-sm-6 col-xs-12">
							<?php wp_nonce_field( 'save_event_nonce' ); ?>
							<input id="save_event_btn" type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Event', 'mjschool' ); } else { esc_html_e( 'Add Event', 'mjschool' ); } ?>" name="save_event" class="btn mjschool-save-btn event_time_validation" />
						</div>
					</div><!--Row Div End.-->
				</div><!-- Mjschool-user-form End.-->
			</form><!--End add event form.-->
		</div><!--End panel body.-->
		<?php
	}
	?>
</div>