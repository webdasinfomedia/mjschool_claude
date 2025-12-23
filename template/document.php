<?php
/**
 * School Document Management Page.
 *
 * This file serves as the main administrative view and controller for managing
 * **documents, study materials, and files** uploaded to the Mjschool system.
 * It is a core page for the document management module, handling the entire
 * CRUD (Create, Read, Update, Delete) lifecycle for files shared across the school.
 *
 * It is primarily responsible for:
 *
 * 1. **Access Control**: Implementing robust **role-based access control** by checking
 * the current user's role and specific rights ('view', 'add', 'edit', 'delete')
 * for the 'document' module.
 * 2. **Tab Navigation**: Handling different views via tabs (implied by typical Mjschool
 * structure) such as 'document_list' and 'adddocument'.
 * 3. **Form Handling**: Displaying the 'Add/Edit Document' form, which captures:
 * - Document Title.
 * - File Upload field.
 * - Document Description.
 * - Visibility/Sharing settings (e.g., sharing with all users/roles/classes).
 * 4. **Document List Display**: Rendering a tabular list of existing documents.
 * 5. **Custom Fields**: Integrating the `Mjschool_Custome_Field` object to fetch
 * and display any custom fields associated with the 'document' module.
 * 6. **CRUD Operations**: Processing form submissions (e.g., `save_document`) for
 * inserting/updating records and handling file uploads.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */ 
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
$mjschool_obj_document = new Mjschool_Document();
$school_type = get_option( "mjschool_custom_class");
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = 1;
	$user_access_edit   = 1;
	$user_access_delete = 1;
	$user_access_view   = 1;
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'document' );
	$user_access_add    = isset( $user_access['add'] ) ? $user_access['add'] : 0;
	$user_access_edit   = isset( $user_access['edit'] ) ? $user_access['edit'] : 0;
	$user_access_delete = isset( $user_access['delete'] ) ? $user_access['delete'] : 0;
	$user_access_view   = isset( $user_access['view'] ) ? $user_access['view'] : 0;
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			if ( isset( $user_access['page_link'] ) && $user_access['page_link'] === 'document' && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( isset( $user_access['page_link'] ) && $user_access['page_link'] === 'document' && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( isset( $user_access['page_link'] ) && $user_access['page_link'] === 'document' && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'insert' ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$active_tab = sanitize_text_field( wp_unslash( isset( $_GET['tab'] ) ? $_GET['tab'] : 'documentlist' ) );
if ( isset( $_POST['save_document'] ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
	if ( wp_verify_nonce( $nonce, 'save_document_nonce' ) ) {
		$upload_docs_array = array();
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			$doc_id = sanitize_text_field( wp_unslash( $_REQUEST['document_id'] ) );
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'edit_action' ) ) {
				if ( isset( $_FILES['upload_file'] ) && ! empty( $_FILES['upload_file'] ) && intval( $_FILES['upload_file']['size'] ) !== 0 ) {
					if ( $_FILES['upload_file']['size'] > 0 ) {
						$upload_docs1 = mjschool_load_documets_new(
							$_FILES['upload_file'],
							$_FILES['upload_file'],
							sanitize_text_field( wp_unslash( $_POST['doc_title'] ) )
						);
					}
				} elseif ( isset( $_REQUEST['old_hidden_document'] ) ) {
					$upload_docs1 = sanitize_text_field( wp_unslash( $_REQUEST['old_hidden_document'] ) );
				}
				$document_data = array();
				if ( ! empty( $upload_docs1 ) ) {
					$document_data[] = array(
						'title' => sanitize_text_field( wp_unslash( $_POST['doc_title'] ) ),
						'value' => $upload_docs1,
					);
				} else {
					$document_data[] = '';
				}
					$result                    = $mjschool_obj_document->mjschool_add_document( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ), $document_data );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'document';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $doc_id );
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=document&tab=documentlist&message=2' ) );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			if ( isset( $_FILES['upload_file'] ) && ! empty( $_FILES['upload_file'] ) && intval( $_FILES['upload_file']['size'] ) !== 0 ) {
				if ( $_FILES['upload_file']['size'] > 0 ) {
					$upload_docs1 = mjschool_load_documets_new(
						$_FILES['upload_file'],
						$_FILES['upload_file'],
						sanitize_text_field( wp_unslash( $_POST['doc_title'] ) )
					);
				}
			} else {
				$upload_docs1 = '';
			}
			$document_data = array();
			if ( ! empty( $upload_docs1 ) ) {
				$document_data[] = array(
					'title' => sanitize_text_field( wp_unslash( $_POST['doc_title'] ) ),
					'value' => $upload_docs1,
				);
			} else {
				$document_data[] = '';
			}
				$result                    = $mjschool_obj_document->mjschool_add_document( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ), $document_data );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'document';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=document&tab=documentlist&message=1' ) );
				die();
			}
		}
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'delete_action' ) ) {
		$result = $mjschool_obj_document->mjschool_delete_document(
			mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['document_id'] ) ) )
		);
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=document&tab=documentlist&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'save_document_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['selected_id'] ) ) {
		foreach ( $_REQUEST['selected_id'] as $id ) {
			$delete = $mjschool_obj_document->mjschool_delete_document( intval( sanitize_text_field( wp_unslash( $id ) ) ) );
		}

		if ( isset( $delete ) && $delete ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=document&tab=documentlist&message=3' ) );
			die();
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'document';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
	$active_tab                = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'documentlist';
$mjschool_obj_document     = new Mjschool_Document();
?>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
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
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
			
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	// Document list tab.
	if ( $active_tab === 'documentlist' ) {
		$user_id  = get_current_user_id();
		$own_data = $user_access['own_data'];
		if ( $own_data === '1' ) {
			$documentdata = mjschool_get_user_document_list( $user_id, $school_obj->role );
		} else {
			$documentdata = $mjschool_obj_document->mjschool_get_all_documents();
		}
		// ------- Exam data for teacher.. ---------//
		if ( ! empty( $documentdata ) ) {
			?>
			<div class="mjschool-panel-body">
				<div class="table-responsive">
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<?php wp_nonce_field( 'save_document_nonce' ); ?>
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $user_access['delete'] === '1' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" id="select_all"></th>
										<?php
									}
									?>
									<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Document For', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Class Section', 'mjschool' ); ?></th>
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
									$color_class_css = mjschool_table_list_background_color( $i );
									?>
									<tr>
										<?php
										if ( $user_access['delete'] === '1' ) {
											?>
											<td class="mjschool-checkbox-width-10px">
												<input type="checkbox" name="selected_id[]" class="mjschool-sub-chk select-checkbox" value="<?php echo esc_attr( $retrieved_data->document_id ); ?>">
											</td>
											<?php
										}
										?>
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
											<?php echo esc_html( mjschool_show_document_for( $retrieved_data->document_for ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php mjschool_show_document_for( $retrieved_data->document_for ); ?>"></i>
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
										// Custom Field Values.
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
																echo esc_url( mjschool_get_date_in_input_box( $custom_field_value ) );
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
																	<a target="blank" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value )); ?>" class="mjschool-float-left-width-100px" record_id="<?php echo esc_attr( $retrieved_data->homework_id ); ?>"><i class="fa fa-eye"> </i><?php esc_html_e( 'View Document', 'mjschool' ); ?></a>
																</li>
																<?php
															}
															if ( $user_access_edit === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=document&tab=add_document&action=edit&document_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->document_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																</li>
																<?php
															}
															if ( $user_access_delete === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=document&tab=documentlist&action=delete&document_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->document_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
						if ( $user_access['delete'] === '1' ) {
							?>
							<div class="mjschool-print-button pull-left">
								<button class="mjschool-btn-sms-color mjschool-button-reload">
									<input type="checkbox" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
									<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
								</button>
								<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
							</div>
							<?php
						}
						?>
					</form>
				</div>
			</div>
			<?php
		} else {
			if ($user_access_add === '1' ) {
				?>
				<div class="mjschool-no-data-list-div">
					<a href="<?php echo esc_url(home_url( '/?dashboard=mjschool_user&page=document&tab=add_document') ); ?>">
						<img class="col-md-12 mjschool-no-img-width-100px mjschool-document-img" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
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
	if ( $active_tab === 'add_document' ) {
		?>
		<?php
		$document_id = 0;
		if ( isset( $_REQUEST['document_id'] ) ) {
			$document_id = mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['document_id'])) );
		}
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'edit' ) {
			$edit   = 1;
			$result = $mjschool_obj_document->mjschool_get_single_document( $document_id );
		}
		?>
		<div class="mjschool-panel-body mjschool-custom-padding-0"><!--Panel body.-->
			<!--Document form.-->
			<form name="document_form" action="" method="post" class="mjschool-form-horizontal" id="document_form" enctype="multipart/form-data">
				<?php $mjschool_action = sanitize_text_field( isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert' ); ?>
				<input id="action" type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="document_id" value="<?php echo esc_attr( $document_id ); ?>" />
				<div class="header">
					<h3 class="mjschool-first-header document_info"><?php esc_html_e( 'Document Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<div class="row"><!--Row Div start.-->
						<?php
						if ( $edit ) {
							$document_for = $result->document_for;
							if ( $document_for !== 'student' ) {
								$display_class = 'display:none;';
							} else {
								$display_class = 'display:block;';
							}
						} elseif ( isset( $_POST['document_for'] ) ) {
							$document_for = sanitize_text_field(wp_unslash($_POST['document_for']));
						} else {
							$document_for = '';
						}
						?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="document_for"><?php esc_html_e( 'Document For', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<select name="document_for" class="mjschool-line-height-30px form-control validate[required] text-input mjschool-min-width-100px document_for" id="document_for">
								<option value="student" <?php selected( 'student', $document_for ); ?>><?php esc_html_e( 'Students', 'mjschool' ); ?></option>
								<option value="teacher" <?php selected( 'teacher', $document_for ); ?>><?php esc_html_e( 'Teachers', 'mjschool' ); ?></option>
								<option value="parent" <?php selected( 'parent', $document_for ); ?>><?php esc_html_e( 'Parents', 'mjschool' ); ?></option>
								<option value="supportstaff" <?php selected( 'supportstaff', $document_for ); ?>><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></option>
							</select>
						</div>
						<div class="col-md-6 input class_document_div" style="<?php echo esc_attr( $display_class ); ?>">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-id">
								<?php esc_html_e( 'Select Class', 'mjschool' ); 
								if ( $mjschool_role === 'teacher' ) {
									?>
									<span class="mjschool-require-field">*</span>
								<?php } ?>
							</label>
							<?php
							if ( $edit ) {
								$classval = $result->class_id;
							} elseif ( isset( $_POST['class_id'] ) ) {
								$classval = sanitize_text_field(wp_unslash($_POST['class_id']));
							} else {
								$classval = '';
							}
							?>
							<select id="mjschool-class-id" name="class_id" class="mjschool-line-height-30px form-control mjschool-max-width-100px mjschool-class-list-document <?php if ( $mjschool_role === 'teacher' ) { echo 'validate[required]'; } ?>">
								<?php
								if ( $mjschool_role === 'teacher' ) {
									?>
									<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
									<?php
								} else {
									?>
									<option value="all class"><?php esc_html_e( 'All Class', 'mjschool' ); ?></option>
									<?php
								}
								?>
								<?php
								foreach ( mjschool_get_all_class() as $classdata ) {
									?>
									<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>> <?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php
								}
								?>
							</select>
						</div>
						<?php if ( $school_type === 'school' ){ ?>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin mjschool-class-section-document-div" style="<?php echo esc_attr( $display_class ); ?>">
								<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
								<?php
								if ( $edit ) {
									$sectionval = $result->section_id;
								} elseif ( isset( $_POST['class_section'] ) ) {
									$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
								} else {
									$sectionval = '';
								}
								?>
								<select id="mjschool-class-section" name="class_section" class="mjschool-line-height-30px form-control mjschool-max-width-100px mjschool-class-section-document">
									<option value="all section"><?php esc_html_e( 'All Section', 'mjschool' ); ?> </option>
									<?php
									if ( $edit ) {
										foreach ( mjschool_get_class_sections( $result->class_id ) as $sectiondata ) {
											?>
											<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?> </option>
											<?php
										}
									}
									?>
								</select>
							</div>
						<?php }?>
						<div class="col-md-6 input select_Student_div">
							<label for="mjschool-select-user" class="ml-1 mjschool-custom-top-label top"><?php esc_html_e( 'Select User', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<span class="document_user_display_block">
								<?php
								if ( $edit ) {
									$student_val = $result->student_id;
								} elseif ( isset( $_POST['selected_users'] ) ) {
								$student_val = array_map( 'intval', (array) $_POST['selected_users'] );
								} else {
									$student_val = 'all student';
								}
								?>
								<select id="mjschool-select-user" name="selected_users" class="form-control validate[required] mjschool-max-width-100px student_list mjschool_heights_47px" >
									<?php
									if ( $student_val === 'all student' ) {
										?>
										<option value="all student"><?php esc_html_e( 'All Student', 'mjschool' ); ?> </option>
										<?php
									} elseif ( $student_val === 'all teacher' ) {
										?>
										<option value="all teacher"><?php esc_html_e( 'All Teacher', 'mjschool' ); ?> </option>
										<?php
									} elseif ( $student_val === 'all supportstaff' ) {
										?>
										<option value="all supportstaff"> <?php esc_html_e( 'All Supoprt Staff', 'mjschool' ); ?></option>
										<?php
									} elseif ( $student_val === 'all parent' ) {
										?>
										<option value="all parent"><?php esc_html_e( 'All Parent', 'mjschool' ); ?> </option>
										<?php
									} else {
										echo '<option value="' . esc_attr( $result->student_id ) . '" ' . selected( $result->student_id, $result->student_id ) . '>' . esc_html( mjschool_user_display_name( $result->student_id ) ) . '</option>';
									}
									?>
								</select>
							</span>
						</div>
					</div>
				</div>
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Upload Document', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<div class="row"><!--Row Div start.-->
						<?php
						if ( $edit ) {
							$doc_data = json_decode( $result->document_content );
							?>
							<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="doc_title" maxlength="50" name="doc_title" class="form-control validate[required,custom[description_validation]] text-input" type="text" value="<?php if ( ! empty( $doc_data[0]->title ) ) { echo esc_attr( $doc_data[0]->title ); } elseif ( isset( $_POST['doc_title'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['doc_title'])) ); } ?>">
										<label  for="doc_title"><?php esc_html_e( 'Document Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-res-rtl-height-75px">
										<div class="col-sm-12">
											<input type="file" name="document_content" class="form-control file mjschool-file-validation input-file" />
											<input type="hidden" name="old_hidden_document" value="<?php if ( ! empty( $doc_data[0]->value ) ) { echo esc_attr( $doc_data[0]->value ); } elseif ( isset( $_POST['document_content'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['document_content'])) ); } ?>">
										</div>
										<?php
										if ( ! empty( $doc_data[0]->value ) ) {
											?>
											<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
												<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value )); ?>" record_id="<?php echo esc_attr( $result->document_id ); ?>"> <i class="fa fa-download"></i>&nbsp;&nbsp;<?php esc_html_e( 'Download', 'mjschool' ); ?></a>
											</div>
											<?php
										}
										?>
									</div>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="doc_title" maxlength="50" class="form-control validate[required,custom[description_validation]] text-input" type="text" value="" name="doc_title">
										<label  for="doc_title"><?php esc_html_e( 'Document Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
										<span class="ustom-control-label mjschool-profile-rtl-css mjschool-custom-top-label ml-2" for="photo"><?php esc_html_e( 'Upload Document', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
										<div class="col-sm-12 mjschool-display-flex">
											<input id="upload_file" onchange="mjschool_file_check(this);" name="upload_file" type="file" <?php if ( $edit ) { ?> class="margin_left_15_res form-control file" <?php } else { ?> class="form-control file validate[required] margin_left_15_res margin_top_5_res" <?php } ?> />
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea id="mjschool-description" name="description" maxlength="150" class="mjschool-textarea-height-47px form-control validate[custom[description_validation]] text-input resize"><?php if ( $edit ) { echo esc_textarea( $result->description );} ?></textarea>
										<span class="mjschool-txt-title-label"></span>
										<label class="text-area address active" for="mjschool-description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data.--------------//
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'document';
				$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<!---------- save btn. -------------->
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<div class="row"><!--Row Div start.-->
						<div class="col-md-6 col-sm-6 col-xs-12">
							<?php wp_nonce_field( 'save_document_nonce' ); ?>
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Edit Document', 'mjschool' ); } else { esc_html_e( 'Add Document', 'mjschool' ); } ?>" name="save_document" class="btn mjschool-save-btn" />
						</div>
					</div><!--Row Div End.-->
				</div><!-- Mjschool-user-form End.-->
			</form><!--End document form..-->
		</div><!--End panel body.-->
		<?php
	}
	?>
</div>