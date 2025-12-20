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
$mjschool_role_name                 = mjschool_get_user_role( get_current_user_id() );
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'grade';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'gradelist';
$tablename  = 'mjschool_grade';
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
if ( isset( $_REQUEST['message'] ) ) {
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Grade Added successfully', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Grade Updated Successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Grade Deleted Successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
			<p><?php echo esc_html( $message_string ); ?></p>
		</div>
		<?php
	}
}
// --------------- Save grade form. --------------//
if ( isset( $_POST['save_grade'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_grade_admin_nonce' ) ) {
		$created_date = date( 'Y-m-d H:i:s' );
		$mark_from    = sanitize_text_field(wp_unslash($_POST['mark_from']));
		$mark_upto    = sanitize_text_field(wp_unslash($_POST['mark_upto']));
		$obj_mark = new Mjschool_Marks_Manage();
		if ( $mark_upto < $mark_from ) {
			$gradedata = array(
				'grade_name'    => sanitize_textarea_field( stripslashes( sanitize_text_field(wp_unslash($_POST['grade_name'])) ) ),
				'grade_point'   => sanitize_text_field( sanitize_text_field(wp_unslash($_POST['grade_point'])) ),
				'mark_from'     => sanitize_text_field( sanitize_text_field(wp_unslash($_POST['mark_from'])) ),
				'mark_upto'     => sanitize_text_field( sanitize_text_field(wp_unslash($_POST['mark_upto'])) ),
				'grade_comment' => sanitize_textarea_field( stripslashes( sanitize_text_field(wp_unslash($_POST['grade_comment'])) ) ),
				'creater_id'    => get_current_user_id(),
				'created_date'  => $created_date,
			);
			// Table name without prefix.
			if ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
				if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
					$gid      = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['grade_id'])) ) );
					$grade_id = array( 'grade_id' => mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['grade_id'])) ) );
					$result   = mjschool_update_record( $tablename, $gradedata, $grade_id );
					// Update custom field data.
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'grade';
					$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $gid );
					if ( $result ) {
						wp_safe_redirect( home_url( ' dashboard=mjschool_user&page=grade&tab=gradelist&message=2') );
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else {
				$grade_name = $obj_mark->mjschool_get_grade_by_name( sanitize_text_field(wp_unslash($_POST['grade_name'])) );
				if ( empty( $grade_name ) ) {
					$result = mjschool_insert_record( $tablename, $gradedata );
					global $wpdb;
					$last_insert_id            = $wpdb->insert_id;
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'grade';
					$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $last_insert_id );
					if ( $result ) {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=grade&tab=gradelist&message=1') );
						die();
					}
				} else {
					?>
					<div id="mjschool-message" class="mjschool-message_class alert updated_top mjschool-below-h2 notice is-dismissible alert-dismissible">
						<p><?php esc_html_e( 'Grade Name All Ready Exist.', 'mjschool' ); ?></p>
						
						<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
						
					</div>
					<?php
				}
			}
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert updated mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php esc_html_e( 'You can not add a Mark upto higher than the Mark from.', 'mjschool' ); ?></p>
				
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
				
			</div>
			<?php
		}
	}
}
// -------------- Multiple grade delete. ---------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_grade( $tablename, intval( $id ) );
		}
	}
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=grade&tab=gradelist&message=3') );
		die();
	}
}
// --------------- Grade delete action. ---------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = mjschool_delete_grade( $tablename, mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['grade_id'])) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=grade&tab=gradelist&message=3') );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
?>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!------------- Panel body. ---------------->
	<?php
	// ---------------- Grade list tab. ----------------//
	if ( $active_tab === 'gradelist' ) {
		$tablename = 'mjschool_grade';
		$own_data  = $user_access['own_data'];
		if ( $own_data === '1' ) {
			$grade_data = mjschool_get_all_grade_data_by_user_id( $tablename );
		} else {
			$grade_data = mjschool_get_all_data( $tablename );
		}
		if ( ! empty( $grade_data ) ) {
			?>
			<div class="mjschool-panel-body"><!------------ Panel body. ---------------->
				<div class="table-responsive"><!--------------- Table responsive. --------------->
					<!---------------- Grade list page form. ------------->
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<table id="frontend_grade_list" class="display dataTable mjschool-student-datatable" cellspacing="0" width="100%">
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
									<th><?php esc_html_e( 'Grade Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Grade Point', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Mark From/Upto', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
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
								foreach ( $grade_data as $retrieved_data ) {
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
											<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->grade_id ); ?>"> </td>
											<?php
										}
										?>
										<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
											
											<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-grade.png"); ?>" class="mjschool-massage-image">
											</p>
											
										</td>
										<td>
											<?php echo esc_html( $retrieved_data->grade_name ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Grade Name', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php echo esc_html( $retrieved_data->grade_point ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Grade point', 'mjschool' ); ?>"></i>
										</td>
										<td><?php echo esc_html( $retrieved_data->mark_upto ) . ' ' . esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( $retrieved_data->mark_from ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mark From/Upto', 'mjschool' ); ?>"></i>
										</td>
										<?php
										$comment       = $retrieved_data->grade_comment;
										$grade_comment = strlen( $comment ) > 60 ? substr( $comment, 0, 60 ) . '...' : $comment;
										?>
										<td>
											<?php
											if ( ! empty( $grade_comment ) ) {
												echo esc_html( $grade_comment );
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_html( $comment ); } else { esc_html_e( 'Comment', 'mjschool' ); } ?>"></i>
										</td>
										<?php
										// Custom Field Values.
										if ( ! empty( $user_custom_field ) ) {
											foreach ( $user_custom_field as $custom_field ) {
												if ( $custom_field->show_in_table === '1' ) {
													$module             = 'grade';
													$custom_field_id    = $custom_field->id;
													$module_record_id   = $retrieved_data->grade_id;
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
										<?php
										if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
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
																<?php
																if ( $user_access['edit'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=grade&tab=addgrade&action=edit&grade_id=' . mjschool_encrypt_id( $retrieved_data->grade_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																	</li>
																	<?php
																}
																if ( $user_access['delete'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=grade&tab=gradelist&action=delete&grade_id=' . mjschool_encrypt_id( $retrieved_data->grade_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
									<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
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
					</form><!---------------- Grade list page form. ------------->
				</div><!--------------- Table responsive. --------------->
			</div><!------------ Panel body. ---------------->
			<?php
		} else {
			if ($user_access['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=grade&tab=addgrade') ); ?>">
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
	// ------------------ Grade add. ----------------//
	if ( $active_tab === 'addgrade' ) {
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$edit       = 1;
			$obj_manage_marks = new Mjschool_Marks_Manage();
			$grade_data = $obj_manage_marks->mjschool_get_grade_by_id( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['grade_id'])) ) );
		}
		?>
		<div class="mjschool-panel-body mjschool-padding-top-25px-res"><!---------------- Panel body. ----------------->
			<!------------------- Grade form start. ----------------->
			<form name="grade_form" action="" method="post" enctype="multipart/form-data" class="mjschool-form-horizontal" id="grade_form">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<div class="header">
					<h3 class="mjschool-first-header">
						<?php esc_html_e( 'Grade Information', 'mjschool' ); ?>
					</h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="grade_name" class="form-control validate[required,custom[address_description_validation]]" type="text" value="<?php if ( $edit ) { echo esc_attr( $grade_data->grade_name ); } ?>" maxlength="50" name="grade_name">
									<label for="grade_name"> <?php esc_html_e( 'Grade Name', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="grade_point" class="form-control validate[required,max[100]] text-input" type="number" value="<?php if ( $edit ) { echo esc_attr( $grade_data->grade_point ); } ?>" name="grade_point" step="any">
									<label for="grade_point"> <?php esc_html_e( 'Grade Point', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<?php wp_nonce_field( 'save_grade_admin_nonce' ); ?>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mark_upto" class="form-control validate[required,max[100]] text-input mark_from_input" type="number" value="<?php if ( $edit ) { echo esc_attr( $grade_data->mark_upto ); } ?>" name="mark_upto" step="any">
									<label for="mark_upto"> <?php esc_html_e( 'Mark From', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mark_from" class="form-control validate[required,max[100]] text-input mark_upto_input" type="number" value="<?php if ( $edit ) { echo esc_attr( $grade_data->mark_from ); } ?>" name="mark_from" step="any">
									<label for="mark_from"> <?php esc_html_e( 'Mark Upto', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea name="grade_comment" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150" id="grade_comment"><?php if ( $edit ) { echo esc_attr( $grade_data->grade_comment ); } ?></textarea>
										<span class="mjschool-txt-title-label"></span>
										<label for="grade_comment" class="text-area address active"> <?php esc_html_e( 'Comment', 'mjschool' ); ?> </label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'grade';
				$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6">
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Grade', 'mjschool' ); } else { esc_html_e( 'Add Grade', 'mjschool' ); } ?>" name="save_grade" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form><!------------------- Grade form end. ----------------->
		</div><!---------------- Panel body. ----------------->
		<?php
	}
	?>
</div>