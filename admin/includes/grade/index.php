<?php
/**
 * Admin Grade Management Interface.
 *
 * Handles backend functionality for adding, editing, viewing, and deleting grades 
 * within the MJSchool plugin. This file includes access control, form validation, 
 * and secure CRUD operations for grade records.
 *
 * Key Features:
 * - Implements role-based access restrictions (Add, Edit, Delete, View).
 * - Displays dynamic grade lists using DataTables with search and filter support.
 * - Provides a secure form with WordPress nonces for grade creation and updates.
 * - Includes JavaScript validation for mark range (Mark From / Mark Upto).
 * - Supports bulk deletion of selected grade records.
 * - Integrates with the custom field module for additional metadata.
 * - Displays contextual success and error messages with dismissible alerts.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/grade
 * @since      1.0.0
 */ 
defined( 'ABSPATH' ) || exit;
// -------- Check Browser Javascript.----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'grade' );
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
			if ( 'grade' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'grade' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'grade' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'grade';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'gradelist';
?>
<div class="penal-body"><!-------- Panel body. -------->
	<div id="mjschool-res-ml-0px" class="mjschool-res-ml-0px_class mjschool_grade_page mjschool-main-list-margin-5px mjschool-margin-left-0px-res">
		<!-------- Grade List page. -------->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Grade Added successfully.', 'mjschool' );
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
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
		// This Class at admin side!.
		$tablename = 'mjschool_grade';
		if ( isset( $_POST['save_grade'] ) ) {
			$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
			if ( wp_verify_nonce( $nonce, 'save_grade_admin_nonce' ) ) {
				$created_date = date( 'Y-m-d H:i:s' );
				$mark_from    = sanitize_text_field( wp_unslash($_POST['mark_from']));
				$mark_upto    = sanitize_text_field( wp_unslash($_POST['mark_upto']));
				if ( $mark_upto < $mark_from ) {
					$gradedata = array(
						'grade_name'    => sanitize_textarea_field( wp_unslash( $_POST['grade_name'] ) ),
						'grade_point'   => sanitize_text_field( wp_unslash($_POST['grade_point']) ),
						'mark_from'     => sanitize_text_field( wp_unslash($_POST['mark_from']) ),
						'mark_upto'     => sanitize_text_field( wp_unslash($_POST['mark_upto']) ),
						'grade_comment' => sanitize_textarea_field( wp_unslash( $_POST['grade_comment'] ) ),
						'creater_id'    => get_current_user_id(),
						'created_date'  => $created_date,
					);
					// table name without prefix.
					$tablename = 'mjschool_grade';
					if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
						if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce'])), 'edit_action' ) ) {
							$gid      = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['grade_id']) ) );
							$grade_id = array( 'grade_id' => intval( mjschool_decrypt_id( wp_unslash($_REQUEST['grade_id']) ) ) );
							$result   = mjschool_update_record( $tablename, $gradedata, $grade_id );
							// UPDATE CUSTOM FIELD DATA.
							$mjschool_custom_field_obj = new Mjschool_Custome_Field();
							$module                    = 'grade';
							$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $gid );
							if ( $result ) {
								wp_safe_redirect( admin_url( 'admin.php?page=mjschool_grade&tab=gradelist&message=2' ) );
								die();
							}
						} else {
							wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
						}
					} else {
						$grade_name = mjschool_get_grade_by_name( sanitize_text_field( wp_unslash($_POST['grade_name'])) );
						if ( empty( $grade_name ) ) {
							$result = mjschool_insert_record( $tablename, $gradedata );
							global $wpdb;
							$last_insert_id            = $wpdb->insert_id;
							$mjschool_custom_field_obj = new Mjschool_Custome_Field();
							$module                    = 'grade';
							$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $last_insert_id );
							if ( $result ) {
								wp_safe_redirect( admin_url( 'admin.php?page=mjschool_grade&tab=gradelist&message=1' ) );
								die();
							}
						} else {
							?>
							<div id="mjschool-message" class="mjschool-message_class alert updated_top mjschool-below-h2 notice is-dismissible alert-dismissible mjschool_margin_left0_bottom_20px" >
								<p><?php esc_html_e( 'Grade Name All Ready Exist.', 'mjschool' ); ?></p>
								<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
							</div>
							<?php
						}
					}
				} else {
					?>
					<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
						<p><?php esc_html_e( 'You can not add a Mark upto higher than the Mark from.', 'mjschool' ); ?></p>
						<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
					</div>
					<?php
				}
			}
		}
		if ( isset( $_REQUEST['delete_selected'] ) ) {
			if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
				$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
				foreach ( $ids as $id ) {
					$result = mjschool_delete_grade( $tablename, $id );
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_grade&tab=gradelist&message=3' ) );
					die();
				}
			}
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_grade&tab=gradelist&message=3' ) );
				die();
			}
		}
		$tablename = 'mjschool_grade';
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce'])), 'delete_action' ) ) {
				$result = mjschool_delete_grade( $tablename, intval( mjschool_decrypt_id( wp_unslash($_REQUEST['grade_id']) ) ) );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_grade&tab=gradelist&message=3' ) );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		}
		// End Save Data.
		?>
		<div class="mjschool-panel-white"><!-------- Panel White. -------->
			<div class="mjschool-panel-body"> <!-------- Panel Body. -------->
				<?php
				if ( $active_tab === 'gradelist' ) {
					$retrieve_class_data = mjschool_get_all_data( $tablename );
					if ( ! empty( $retrieve_class_data ) ) {
						?>
						<div class="mjschool-panel-body">
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="grade_list" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
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
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->grade_id ); ?>"></td>
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
														<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
															
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-grade.png"); ?>" class="mjschool-massage-image">
															
														</p>
													</td>
													<td><?php echo esc_html( $retrieved_data->grade_name ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Grade Name', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( $retrieved_data->grade_point ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Grade point', 'mjschool' ); ?>"></i></td>
													<td>
														<?php echo esc_html( $retrieved_data->mark_upto ) . ' ' . esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( $retrieved_data->mark_from ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mark From/Upto', 'mjschool' ); ?>"></i>
													</td>
													<?php
													$comment       = $retrieved_data->grade_comment;
													$grade_comment = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
													?>
													<td>
														<?php
														if ( $retrieved_data->grade_comment ) {
															echo esc_html( stripslashes( $grade_comment ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_attr( $comment ); } else { esc_attr_e( 'Comment', 'mjschool' ); } ?>"></i>
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
																			<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile">
																				<button class="btn btn-default view_document" type="button">
																					<i class="fas fa-download"></i>
																					<?php esc_html_e( 'Download', 'mjschool' ); ?>
																				</button>
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
																		
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																		
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<?php
																		if ( $user_access_edit === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_grade&tab=addgrade&action=edit&grade_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->grade_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																			<?php
																		}
																		if ( $user_access_delete === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_grade&tab=gradelist&action=delete&grade_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->grade_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																					<i class="fas fa-trash"></i><?php esc_html_e( 'Delete', 'mjschool' ); ?>
																				</a>
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
											<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<?php
										if ( $user_access_delete === '1' ) {
											 ?>
											<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>">
											</button>
											<?php 
										}
										?>
									</div>
									<?php if ( $user_access_delete === '1' ) { ?>
										<div class="mjschool-print-button pull-left">
										</div>
									<?php } ?>
								</form>
							</div>
						</div>
						<?php
					} elseif ( $user_access_add === '1' ) {
						?>
						<div class="mjschool-no-data-list-div">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_grade&tab=addgrade' ) ); ?>">
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
				if ( $active_tab === 'addgrade' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/grade/add-grade.php';
				}
				?>
			</div><!-------- Panel Body. -------->
		</div><!-------- Panel White. -------->
	</div><!-------- Grade List page. -------->
</div><!-------- Panel body. -------->