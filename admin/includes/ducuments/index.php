<?php
/**
 * Document Management Admin Page.
 *
 * Handles document add, edit, delete, and list functionalities for the MjSchool plugin.
 * Includes access control, nonce verification, and file upload handling.
 *
 * @since      1.0.0
 *
 * @package    MjSchool
 * @subpackage MjSchool/admin/includes/documents
 */
defined( 'ABSPATH' ) || exit;
// -------- Browser JavaScript Check. ----------//
mjschool_browser_javascript_check();
$school_type=get_option( 'mjschool_custom_class' );
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'document' );
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
			if ( 'document' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'document' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'document' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'document';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
$active_tab                = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'documentlist';
$mjschool_obj_document     = new Mjschool_Document();
if ( isset( $_POST['save_document'] ) ) {
	$nonce = sanitize_text_field( wp_unslash($_POST['_wpnonce']) );
	if ( wp_verify_nonce( $nonce, 'save_document_nonce' ) ) {
		$upload_docs_array = array();
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$doc_id = intval( $_REQUEST['document_id'] );
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				if ( isset( $_FILES['document_content'] ) && ! empty( $_FILES['document_content'] ) && $_FILES['document_content']['size'] != 0 ) {
					if ( $_FILES['document_content']['size'] > 0 ) {
						$upload_docs1 = mjschool_load_documets_new( $_FILES['document_content'], $_FILES['document_content'], $_POST['doc_title'] );
					}
				} elseif ( isset( $_REQUEST['old_hidden_document'] ) ) {
					$upload_docs1 = sanitize_text_field( wp_unslash($_REQUEST['old_hidden_document']));
				}
				$document_data = array();
				if ( ! empty( $upload_docs1 ) ) {
					$document_data[] = array(
						'title' => sanitize_text_field( wp_unslash($_POST['doc_title'])),
						'value' => $upload_docs1,
					);
				} else {
					$document_data[] = '';
				}
				$result = $mjschool_obj_document->mjschool_add_document( wp_unslash($_POST), $document_data );
				// Update custom field data.
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'document';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $doc_id );
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_document&tab=documentlist&message=2' ) );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			if ( isset( $_FILES['upload_file'] ) && ! empty( $_FILES['upload_file'] ) && $_FILES['upload_file']['size'] != 0 ) {
				if ( $_FILES['upload_file']['size'] > 0 ) {
					$upload_docs1 = mjschool_load_documets_new( $_FILES['upload_file'], $_FILES['upload_file'], sanitize_text_field( wp_unslash($_POST['doc_title'])) );
				}
			} else {
				$upload_docs1 = '';
			}
			$document_data = array();
			if ( ! empty( $upload_docs1 ) ) {
				$document_data[] = array(
					'title' => sanitize_text_field( wp_unslash($_POST['doc_title'])),
					'value' => $upload_docs1,
				);
			} else {
				$document_data[] = '';
			}
			$result                    = $mjschool_obj_document->mjschool_add_document( wp_unslash($_POST), $document_data );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'document';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_document&tab=documentlist&message=1' ) );
				die();
			}
		}
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = $mjschool_obj_document->mjschool_delete_document( intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash($_REQUEST['document_id']) ) ) ) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_document&tab=documentlist&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_POST['delete_selected'] ) ) {
	// Verify nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_documents' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( ! empty( $_POST['selected_id'] ) ) {
		foreach ( $_POST['selected_id'] as $id ) {
			$delete = $mjschool_obj_document->mjschool_delete_document( intval( $id ) );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_document&tab=documentlist&message=3' ) );
		exit;
	}
}
?>
<div class="mjschool-page-inner"><!-- mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- mjschool-main-list-margin-15px. -->
		<div class="row"><!-- row. -->
			<div class="col-md-12 mjschool-custom-padding-0"><!-- col-md-12. -->
				<?php
				$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
				switch ( $message ) {
					case '1':
						$message_string = esc_html__( 'Document Inserted Successfully.', 'mjschool' );
						break;
					case '2':
						$message_string = esc_html__( 'Document Updated Successfully.', 'mjschool' );
						break;
					case '3':
						$message_string = esc_html__( 'Document Deleted Successfully.', 'mjschool' );
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
				<div class="mjschool-main-list-page"><!-- mjschool-main-list-page. -->
					<?php
					// Document List Tab
					if ( $active_tab === 'documentlist' ) {
						$documentdata = $mjschool_obj_document->mjschool_get_all_documents();
						if ( ! empty( $documentdata ) ) {
							?>
							<div class="mjschool-panel-body">
								<div class="table-responsive">
									<form id="mjschool-common-form" name="mjschool-common-form" method="post">
										<?php wp_nonce_field( 'bulk_delete_documents' ); ?>
										<table id="document_list" class="display mjschool-admin-transport-datatable" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
													<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Document For', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
													<?php if ( $school_type != 'university' ) {?>
														<th><?php esc_html_e( 'Class Section', 'mjschool' ); ?></th>
													<?php }?>
													<th><?php esc_html_e( 'User Name', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
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
												foreach ( $documentdata as $retrieved_data ) {
													$document_id = mjschool_encrypt_id( $retrieved_data->document_id );
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
															<input type="checkbox" name="selected_id[]" class="mjschool-sub-chk select-checkbox" value="<?php echo esc_attr( $retrieved_data->document_id ); ?>">
														</td>
														<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
															<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
																
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
																
															</p>
														</td>
														<td class="title">
															<?php
															$doc_data = json_decode( $retrieved_data->document_content );
															echo esc_html( $doc_data[0]->title );
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Document Title', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															echo esc_html( mjschool_show_document_for( $retrieved_data->document_for ) );
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_attr(mjschool_show_document_for( $retrieved_data->document_for ) ); ?>"></i>
														</td>
														<td>
															<?php
															if ( $retrieved_data->class_id === 'all class' ) {
																esc_html_e( 'All Class', 'mjschool' );
															} else {
																echo esc_html( mjschool_get_class_name( $retrieved_data->class_id ) );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
														</td>
														<?php if ( $school_type != 'university' ) {?>
															<td>
																<?php
																if ( $retrieved_data->section_id === 'all section' ) {
																	esc_html_e( 'All Section', 'mjschool' );
																} elseif ( $retrieved_data->section_id === '' ) {
																	esc_html_e( 'N/A', 'mjschool' );
																} else {
																	echo esc_html( mjschool_get_section_name( $retrieved_data->section_id ) );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Section', 'mjschool' ); ?>"></i>
															</td>
														<?php }?>
														<td>
															<?php echo esc_html( mjschool_show_document_user( $retrieved_data->student_id ) ); ?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( ! empty( $retrieved_data->created_date ) ) {
																echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															$description   = $retrieved_data->description;
															$grade_comment = strlen( $description ) > 30 ? substr( $description, 0, 30 ) . '...' : $description;
															if ( ! empty( $retrieved_data->description ) ) {
																echo esc_html( $grade_comment );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $description ) ) { echo esc_html( $description ); } else { esc_html_e( 'Description', 'mjschool' ); } ?>"></i>
														</td>
														<?php
														if ( ! empty( $user_custom_field ) ) {
															foreach ( $user_custom_field as $custom_field ) {
																if ( $custom_field->show_in_table === '1' ) {
																	$module             = 'document';
																	$custom_field_id    = $custom_field->id;
																	$module_record_id   = $retrieved_data->document_id;
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
																				<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button">
																					<i class="fas fa-download"></i>
																					<?php esc_html_e( 'Download', 'mjschool' ); ?></button>
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
																			$doc_data = json_decode( $retrieved_data->document_content );
																			if ( ! empty( $doc_data[0]->value ) ) {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a target="blank" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value ) ); ?>" class="mjschool-float-left-width-100px" record_id="<?php echo esc_attr( $retrieved_data->document_id ); ?>"><i class="fa fa-eye">
																					</i><?php esc_html_e( 'View Document', 'mjschool' ); ?></a>
																				</li>
																				<?php
																			}
																			if ( $user_access_edit === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_document&tab=add_document&action=edit&document_id='.  rawurlencode( $document_id ) .'&_wpnonce_action='.  rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																				</li>
																				<?php
																			}
																			if ( $user_access_delete === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_document&tab=documentlist&action=delete&document_id='.  rawurlencode( $document_id ) ) .'&_wpnonce_action='.  rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																						<i class="fas fa-trash"></i>
																						<?php esc_html_e( 'Delete', 'mjschool' ); ?>
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
												<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->document_id ); ?>" >
												<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
											</button>
											<?php
											if ( $user_access_delete === '1' ) {
												 ?>
												<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
												<?php  
											}
											?>
										</div>
									</form>
								</div>
							</div>
							<?php
						} elseif ( $user_access_add === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_document&tab=add_document' ) ); ?>">
									
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
									
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
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
					if ( $active_tab === 'add_document' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/ducuments/add-document.php';
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>