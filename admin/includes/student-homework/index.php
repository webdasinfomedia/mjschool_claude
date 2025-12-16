<?php
/**
 * Manage Homework Records (Admin Page).
 *
 * This file is responsible for managing homework functionality within the MJSchool
 * plugin's admin panel. It allows administrators and teachers to add, edit, delete,
 * and view homework entries for students. The page dynamically loads different tabs
 * for upcoming and closed homework lists, and includes form validation, access
 * control, and secure CRUD operations.
 *
 * Key Features:
 * - Implements role-based access control for homework add, edit, delete, and view actions.
 * - Displays upcoming and closed homework with DataTables integration for dynamic search and sorting.
 * - Handles homework creation and updates with document upload functionality.
 * - Validates file uploads using secure WordPress upload APIs and nonce verification.
 * - Supports bulk deletion of homework records with confirmation prompts.
 * - Includes jQuery validation, DataTables, and localized JavaScript strings for multi-language support.
 * - Displays admin notices for success or error messages (Add, Update, Delete, etc.).
 * - Ensures compliance with WordPress coding standards and best practices.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/student-homework
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'homework';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
$mjschool_role              = mjschool_get_user_role( get_current_user_id() );
$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
if ( $mjschool_role === 'administrator') {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'homework' );
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
			if ( 'homework' === $user_access['page_link'] && ( $action === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'homework' === $user_access['page_link'] && ( $action === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'homework' === $user_access['page_link'] && ( $action === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
?>
<?php
$obj_feespayment = new Mjschool_Homework();
// Save homeWork.
if ( isset( $_POST['Save_Homework'] ) ) {
	$nonce = $_POST['_wpnonce'];
	if ( wp_verify_nonce( $nonce, 'save_homework_admin_nonce' ) ) {

		$nonce = wp_create_nonce( 'mjschool_homework_tab' );
		$insert = new Mjschool_Homework();
		if ( isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( $_GET['_wpnonce_action'], 'edit_action' ) ) {
				if ( isset( $_FILES['homework_document'] ) && ! empty( $_FILES['homework_document'] ) && $_FILES['homework_document']['size'] != 0 ) {
					if ( $_FILES['homework_document']['size'] > 0 ) {
						$upload_docs1 = mjschool_load_documets_new( $_FILES['homework_document'], $_FILES['homework_document'], sanitize_text_field(wp_unslash($_POST['document_name'])) );
					}
				} elseif ( isset( $_REQUEST['old_hidden_homework_document'] ) ) {
					$upload_docs1 = sanitize_text_field(wp_unslash($_REQUEST['old_hidden_homework_document']));
				}
				$document_data = array();
				if ( ! empty( $upload_docs1 ) ) {
					$document_data[] = array(
						'title' => sanitize_text_field(wp_unslash($_POST['document_name'])),
						'value' => $upload_docs1,
					);
				} else {
					$document_data[] = '';
				}
				$homework_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['homework_id'])) ) );
				$update_data = $insert->mjschool_add_homework( wp_unslash($_POST), $document_data );
				// Update custom field data.
				$custom_field_obj    = new Mjschool_Custome_Field();
				$module              = 'homework';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $homework_id );
				if ( $update_data ) {
					wp_redirect( admin_url() . 'admin.php?page=mjschool_student_homewrok&tab=homeworklist&_wpnonce='.esc_attr( $nonce ).'&message=2' );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			
			$args = array( 'meta_query' => array(array( 'key' => 'class_name', 'value' => sanitize_text_field(wp_unslash($_POST['class_name'])), 'compare' => '=' ) ), 'count_total' => true);
			
			$users = new WP_User_Query( $args );
			if ( $users->get_total() === 0 ) {
				wp_redirect( admin_url() . 'admin.php?page=mjschool_student_homewrok&tab=homeworklist&_wpnonce='.esc_attr( $nonce ).'&message=4' );
				die();
			} else {
				if ( isset( $_FILES['homework_document'] ) && ! empty( $_FILES['homework_document'] ) && $_FILES['homework_document']['size'] != 0 ) {
					if ( $_FILES['homework_document']['size'] > 0 ) {
						$upload_docs1 = mjschool_load_documets_new( $_FILES['homework_document'], $_FILES['homework_document'], sanitize_text_field(wp_unslash($_POST['document_name'])) );
					}
				} else {
					$upload_docs1 = '';
				}
				$document_data = array();
				if ( ! empty( $upload_docs1 ) ) {
					$document_data[] = array(
						'title' => sanitize_text_field(wp_unslash($_POST['document_name'])),
						'value' => $upload_docs1,
					);
				} else {
					$document_data[] = '';
				}
				$insert_data        = $insert->mjschool_add_homework( wp_unslash($_POST), $document_data );
				$custom_field_obj   = new Mjschool_Custome_Field();
				$module             = 'homework';
				$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $insert_data );
				if ( $insert_data ) {
					wp_redirect( admin_url() . 'admin.php?page=mjschool_student_homewrok&tab=homeworklist&_wpnonce='.esc_attr( $nonce ).'&message=1' );
					die();
				}
			}
		}
	}
}
$tablename = 'mjschool_homework';
/* Delete selected Subject. */
if ( isset( $_REQUEST['delete_selected'] ) ) {
	$ojc = new Mjschool_Homework();
	$nonce = wp_create_nonce( 'mjschool_homework_tab' );
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$delete = $ojc->mjschool_get_delete_records( $tablename, $id );
		}
		if ( $delete ) {
			wp_redirect( admin_url() . 'admin.php?page=mjschool_student_homewrok&tab=homeworklist&_wpnonce='.esc_attr( $nonce ).'&message=3' );
			die();
		}
	}
}
if ( $action === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( $_GET['_wpnonce_action'], 'delete_action' ) ) {
		$delete = new Mjschool_Homework();
		$nonce = wp_create_nonce( 'mjschool_homework_tab' );
		$dele   = $delete->mjschool_get_delete_record( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['homework_id'])) ) );
		if ( $dele ) {
			wp_redirect( admin_url() . 'admin.php?page=mjschool_student_homewrok&tab=homeworklist&_wpnonce='.esc_attr( $nonce ).'&message=3' );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'homeworklist';
?>
<!-- Start POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="invoice_data">
			</div>
		</div>
	</div>
</div>
<!-- End POP-UP code. -->
<div class="mjschool-page-inner"><!-- Mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- Mjschool-main-list-margin-15px. -->
		<div class="row"><!-- Row. -->
			<?php
			$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
			switch ( $message ) {
				case '1':
					$message_string = esc_html__( 'Homework Added Successfully.', 'mjschool' );
					break;
				case '2':
					$message_string = esc_html__( 'Homework Updated Successfully.', 'mjschool' );
					break;
				case '3':
					$message_string = esc_html__( 'Homework Deleted Successfully.', 'mjschool' );
					break;
				case '4':
					$message_string = esc_html__( 'No Student Available In This Class.', 'mjschool' );
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
			<div class="col-md-12 mjschool-custom-padding-0"><!-- Col-md-12. -->
				<!-- Nav-tabs start. -->
				<?php
				$mjschool_action = '';
				if ( ! empty( $_REQUEST['action'] ) ) {
					$mjschool_action = $action;
				}
				if ( $active_tab != 'view_homework' ) {
					?>
					<?php $nonce = wp_create_nonce( 'mjschool_homework_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'homeworklist' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_student_homewrok&tab=homeworklist&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'homeworklist' ? 'nav-tab-active' : ''; ?>">
								<?php echo esc_attr__( 'Upcoming Homework', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab === 'closed_homework' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_student_homewrok&tab=closed_homework&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'closed_homework' ? 'nav-tab-active' : ''; ?>">
								<?php echo esc_attr__( 'Closed Homework', 'mjschool' ); ?>
							</a>
						</li>
						<?php
						if ( $active_tab === 'addhomework' ) {
							if ( $action === 'edit' ) { ?>
								<li class="<?php if ( $active_tab === 'addhomework' || $action === 'edit' ) { ?> active<?php } ?>">
									<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addhomework' ? 'nav-tab-active' : ''; ?>">
										<?php esc_html_e( 'Edit Homework', 'mjschool' ); ?>
									</a>
								</li>
							<?php } else {
								if ( $user_access_add === '1' ) { ?>
									<li class="<?php if ( $active_tab === 'addhomework' || $action === 'edit' ) {?>active<?php } ?>">
										<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addhomework' ? 'nav-tab-active' : ''; ?>">
											<?php echo esc_attr__( 'Add Homework', 'mjschool' ); ?>
										</a>
									</li>
									<?php
								}
							}
						}
						?>
					</ul>
					<?php
				}
				if ( $active_tab === 'homeworklist' ) {
					// Check nonce for homework list tab.
					if ( isset( $_GET['tab'] ) ) {
						if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_homework_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}
					$obj            = new Mjschool_Homework();
					$retrieve_class_data = $obj->mjschool_get_all_upcoming_homework();
					if ( ! empty( $retrieve_class_data ) ) { ?>
						<div><!-- Start div. -->
							<div class="table-responsive"><!-- Table responsive div. -->
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="homework_list" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Marks', 'mjschool' ); ?></th>
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
												$homework_id = mjschool_encrypt_id( $retrieved_data->homework_id );
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
														<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->homework_id ); ?>">
													</td>
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
														
														<a  href="?page=mjschool_student_homewrok&tab=view_homework&id=<?php echo esc_attr($homework_id); ?>">
															<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
															</p>
														</a>
														
													</td>
													<td >
														<a class="mjschool-color-black" href="?page=mjschool_student_homewrok&tab=view_homework&id=<?php echo esc_attr( $homework_id ); ?>" type="Homework_view">
															<?php echo esc_html( $retrieved_data->title ); ?>
														</a> 
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Title', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_name, $retrieved_data->section_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_subject_by_id( $retrieved_data->subject ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Homework Date', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submission Date', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->marks ) ) {
															echo esc_html( $retrieved_data->marks );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
													</td>
													<?php
													// Custom Field Values.
													if ( ! empty( $user_custom_field ) ) {
														foreach ( $user_custom_field as $custom_field ) {
															if ( $custom_field->show_in_table === '1' ) {
																$module             = 'homework';
																$custom_field_id    = $custom_field->id;
																$module_record_id   = $retrieved_data->homework_id;
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
																<li >
																	
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<?php
																		$doc_data = json_decode($retrieved_data->homework_document);
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="?page=mjschool_student_homewrok&tab=view_homework&id=<?php echo esc_attr($homework_id);?>" class="mjschool-float-left-width-100px"	 type="Homework_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View','mjschool' );?></a>
																		</li>
																		<?php
																		if ( ! empty( $doc_data[0]->value ) ) {
																			echo "";
																		}
																		if ($user_access_edit === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																				<a href="?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=<?php echo esc_attr($homework_id);?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) );?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit','mjschool' );?></a>
																			</li>
																			<?php
																		}
																		if ($user_access_delete === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="?page=mjschool_student_homewrok&tab=homeworklist&action=delete&homework_id=<?php echo esc_attr($homework_id);?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) );?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?','mjschool' );?>' );"><i class="fas fa-trash"> </i> <?php esc_html_e( 'Delete','mjschool' );?></a>
																			</li>
																			<?php
																		} ?>
																	</ul>
																</li>
															</ul>
														</div>
													</td>
												</tr>
												<?php
												$i++;
											} ?>
										</tbody>
									</table>
									<div class="mjschool-print-button pull-left">
										<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr($retrieved_data->homework_id); ?>">
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									</div>
								</form>
							</div><!--------- Table responsive div end. ------->
						</div>
						<?php
					} else {
						if ($user_access_add === '1' ) {
							?>
							<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
								<a href="<?php echo esc_url( admin_url() . 'admin.php?page=mjschool_student_homewrok&tab=addhomework' ); ?>">
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
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php  
						}
					}
				}
				if ( $active_tab === 'closed_homework' ) {
					// Check nonce for closed homework list tab.
					if ( isset( $_GET['tab'] ) ) {
						if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_homework_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}
					$obj            = new Mjschool_Homework();
					$retrieve_class_data = $obj->mjschool_get_all_closed_homework();
					if ( ! empty( $retrieve_class_data ) ) {
						?>
						<div><!-- Start div. -->
							<div class="table-responsive"><!-- Table responsive div. -->
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="closed_homework_list" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Marks', 'mjschool' ); ?></th>
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
													<td class="mjschool-checkbox-width-10px">
														<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->homework_id ); ?>">
													</td>
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
														<a  href="?page=mjschool_student_homewrok&tab=view_homework&id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>">
															<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
															</p>
														</a>
													</td>
													<td >
														<a class="mjschool-color-black" href="?page=mjschool_student_homewrok&tab=view_homework&id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>" type="Homework_view">
															<?php echo esc_attr( $retrieved_data->title ); ?>
														</a> 
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Title', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_name, $retrieved_data->section_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_subject_by_id( $retrieved_data->subject ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Homework Date', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submission Date', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->marks ) ) {
															echo esc_html( $retrieved_data->marks );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														} ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
													</td>
													<?php
													// Custom Field Values.
													if ( ! empty( $user_custom_field ) ) {
														foreach ( $user_custom_field as $custom_field ) {
															if ( $custom_field->show_in_table === '1' ) {
																$module             = 'homework';
																$custom_field_id    = $custom_field->id;
																$module_record_id   = $retrieved_data->homework_id;
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
																<li >
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<?php
																		$doc_data = json_decode($retrieved_data->homework_document);
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="?page=mjschool_student_homewrok&tab=view_homework&id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->homework_id ) );?>" class="mjschool-float-left-width-100px" type="Homework_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View','mjschool' );?></a>
																		</li>
																		<?php
																		if ($user_access_edit === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																				<a href="?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->homework_id ) );?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) );?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit','mjschool' );?></a>
																			</li>
																			<?php
																		} ?>
																		<?php
																		if ($user_access_delete === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="?page=mjschool_student_homewrok&tab=homeworklist&action=delete&homework_id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->homework_id ) );?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) );?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?','mjschool' );?>' );"><i class="fas fa-trash"> </i> <?php esc_html_e( 'Delete','mjschool' );?></a>
																			</li>
																			<?php
																		} ?>
																	</ul>
																</li>
															</ul>
														</div>
													</td>
												</tr>
												<?php
												$i++;
											} ?>
										</tbody>
									</table>
									<div class="mjschool-print-button pull-left">
										<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr($retrieved_data->homework_id); ?>">
											<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									</div>
								</form>
							</div><!--------- Table responsive div end. ------->
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
				if ( $active_tab === 'addhomework' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/student-homework/add-student-homework.php';
				}
				if ( $active_tab === 'view_homework' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/student-homework/homework-detail.php';
				}
				?>
			</div><!-- Col-md-12. -->
		</div><!-- Row. -->
	</div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner. -->