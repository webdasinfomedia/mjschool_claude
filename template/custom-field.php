<?php

/**
 * Custom Field Management Page.
 *
 * This file serves as the main administrative view and controller for managing
 * the **Custom Fields** functionality within the Mjschool system. It allows
 * administrators to create, read, update, and delete custom data fields for
 * various modules (e.g., student, class, teacher, etc.).
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'custom_field';
$obj_admission = new Mjschool_admission();
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
$obj_custome_field = new Mjschool_Custome_Field();
// Save custom field data.
if ( isset( $_POST['add_custom_field'] ) ) {
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'insert' ) {
		// Add custom field data.
		$result = $obj_custome_field->mjschool_add_custom_field( sanitize_text_field( wp_unslash($_POST ) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=custome-field&tab=custome_field_list&message=1' ) );
			die();
		}
	} elseif ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'edit_action' ) ) {
			$result = $obj_custome_field->mjschool_add_custom_field( sanitize_text_field( wp_unslash( $_POST ) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=custome-field&tab=custome_field_list&message=2' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'delete_action' ) ) {
		$result = $obj_custome_field->mjschool_delete_custome_field( mjschool_decrypt_id( intval( wp_unslash( $_REQUEST['id'] ) ) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=custome-field&tab=custome_field_list&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_POST['custome_delete_selected'] ) ) {
	if ( isset( $_POST['selected_id'] ) ) {
		foreach ( $_POST['selected_id'] as $custome_id ) {
			$record_id = $custome_id;
			$result    = $obj_custome_field->mjschool_delete_selected_custome_field( $record_id );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=custome-field&tab=custome_field_list&message=3' ) );
				die();
			}
		}
	}
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=custome-field&tab=custome_field_list&message=3' ) );
		die();
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'custome_field_list';
if ( isset( $_REQUEST['message'] ) ) {
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Custom Field Added Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Custom Field Updated Successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Custom Field Deleted Successfully.', 'mjschool' );
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
?>
<!-- Nav tabs. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<?php
	if ( $active_tab === 'custome_field_list' ) {
		if ( $school_obj->role === 'supportstaff' || $school_obj->role === 'teacher' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$custom_field_data = $obj_custome_field->mjschool_get_all_custom_field_data_own();
			} else {
				$custom_field_data = $obj_custome_field->mjschool_get_all_custom_field_data();
			}
		} else {
			$custom_field_data = $obj_custome_field->mjschool_get_all_custom_field_data();
		}
		?>
		<div class="mjschool-panel-body mjschool-margin-top-40">
			<?php
			if ( ! empty( $custom_field_data ) ) {
				?>
				<div class="table-responsive">
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<table id="frontend_custome_field_list" class="display dataTable mjschool-student-datatable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Form Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Lable', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Type', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Custom Field Id', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Validation', 'mjschool' ); ?></th>
									<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 0;
								foreach ( $custom_field_data as $retrieved_data ) {
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
											<input type="checkbox" name="selected_id[]" class="mjschool-sub-chk sub_chk" value="<?php echo esc_attr( $retrieved_data->id ); ?>">
										</td>
										<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
											<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-custome-field.png"); ?>" height="30px" width="30px" class="mjschool-massage-image">
											</p>
										</td>
										<td class="added"><?php echo esc_html( $retrieved_data->form_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Form Name', 'mjschool' ); ?>"></i></td>
										<td class="added"><?php echo esc_html( $retrieved_data->field_label); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Lable', 'mjschool' ); ?>"></i></td>
										<td class="added"><?php echo esc_html( $retrieved_data->field_type ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Type', 'mjschool' ); ?>"></i></td>
										<td class="added"><?php echo esc_html( $retrieved_data->id ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Custom Field Id', 'mjschool' ); ?>"></i></td>
										<td class="added"><?php echo esc_html( $retrieved_data->field_validation ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Validation', 'mjschool' ); ?>"></i></td>
										<td class="action">
											<div class="mjschool-user-dropdown">
												<ul  class="mjschool_ul_style">
													<li >
														<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
														</a>
														<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
															<?php
															if ( $user_access['edit'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=custome-field&tab=add_custome_field&action=edit&id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																</li>
																<?php
															}
															if ( $user_access['delete'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=custome-field&tab=custome_field_list&action=delete&id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"> </i><?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
							<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
								<input type="checkbox" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
								<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
							</button>
							<?php
							if ( $user_access['delete'] === '1' ) {
								 ?>
								<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="custome_delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
								<?php
							}
							?>
						</div>
					</form>
				</div>
				<?php
			} else {
				if ($user_access['add'] === '1' ) {
					?>
					<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
						<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=custome-field&tab=add_custome_field') ); ?>">
							<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
						</a>
						<div class="col-md-12 dashboard_btn mjschool-margin-top-20px">
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
	if ( $active_tab === 'add_custome_field' ) {
		$obj_custome_field = new Mjschool_Custome_Field();
		$file_type_find    = '';
		$file_type_value   = '';
		$edit              = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			$edit              = 1;
			$custom_field_id   = mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) );
			$custom_field_data = $obj_custome_field->mjschool_get_single_custom_field_data( $custom_field_id );
		}
		$document_option     = get_option( 'mjschool_upload_document_type' ); // Get saved allowed types.
		$document_type_array = explode( ', ', $document_option ); // Convert to array.
		?>
		<div class="mjschool-panel-body mjschool-margin-top-40">
			<form class="form mjschool-form-horizontal" name="custom_field_form" enctype="multipart/form-data" method="post" id="custom_field_form">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="custom_field_id" value="<?php if ( $edit ) { echo esc_attr( $custom_field_id ); } ?>"/>
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Custom Field Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6 input" id="smgt_select_class">
							<label class="ml-1 mjschool-custom-top-label top" for="case_link"><?php esc_html_e( 'Form Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<select id="module_name" class="form-control validate[required]"  name="form_name" <?php if ( $edit ) { ?> disabled <?php } ?>>
								<option value=""><?php esc_html_e( 'Select Form', 'mjschool' ); ?></option>
								<option value="admission" <?php if ( $edit ) { selected( 'admission', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Admission', 'mjschool' ); ?></option>
								<option value="student" <?php if ( $edit ) { selected( 'student', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Student', 'mjschool' ); ?></option>
								<option value="teacher" <?php if ( $edit ) { selected( 'teacher', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Teacher', 'mjschool' ); ?></option>
								<option value="parent" <?php if ( $edit ) { selected( 'parent', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Parent', 'mjschool' ); ?></option>
								<option value="supportstaff" <?php if ( $edit ) { selected( 'supportstaff', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Supportstaff', 'mjschool' ); ?></option>
								<option value="class" <?php if ( $edit ) { selected( 'class', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Class', 'mjschool' ); ?></option>
								<option value="subject" <?php if ( $edit ) { selected( 'subject', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Subject', 'mjschool' ); ?></option>
								<option value="exam" <?php if ( $edit ) { selected( 'exam', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Exam', 'mjschool' ); ?></option>
								<option value="examhall" <?php if ( $edit ) { selected( 'examhall', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Exam Hall', 'mjschool' ); ?></option>
								<option value="grade" <?php if ( $edit ) { selected( 'grade', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Grade', 'mjschool' ); ?></option>
								<option value="homework" <?php if ( $edit ) { selected( 'homework', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Homework', 'mjschool' ); ?></option>
								<option value="document" <?php if ( $edit ) { selected( 'document', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Document', 'mjschool' ); ?></option>
								<option value="library" <?php if ( $edit ) { selected( 'library', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Library', 'mjschool' ); ?></option>
								<option value="leave" <?php if ( $edit ) { selected( 'leave', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Leave', 'mjschool' ); ?></option>
								<option value="fee_pay" <?php if ( $edit ) { selected( 'fee_pay', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Fees Payment', 'mjschool' ); ?></option>
								<option value="fee_list" <?php if ( $edit ) { selected( 'fee_list', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Fees Payment List', 'mjschool' ); ?></option>
								<option value="income" <?php if ( $edit ) { selected( 'income', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Income', 'mjschool' ); ?></option>
								<option value="expense" <?php if ( $edit ) { selected( 'expense', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Expense', 'mjschool' ); ?></option>
								<option value="tax" <?php if ( $edit ) { selected( 'tax', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Tax', 'mjschool' ); ?></option>
								<option value="hostel" <?php if ( $edit ) { selected( 'hostel', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Hostel', 'mjschool' ); ?></option>
								<option value="transport" <?php if ( $edit ) { selected( 'transport', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Transport', 'mjschool' ); ?></option>
								<option value="holiday" <?php if ( $edit ) { selected( 'holiday', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Holiday', 'mjschool' ); ?></option>
								<option value="notice" <?php if ( $edit ) { selected( 'notice', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Notice', 'mjschool' ); ?></option>
								<option value="event" <?php if ( $edit ) { selected( 'event', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Event', 'mjschool' ); ?></option>
								<option value="notification" <?php if ( $edit ) { selected( 'notification', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Notification', 'mjschool' ); ?></option>
								<option value="message" <?php if ( $edit ) { selected( 'message', $custom_field_data->form_name );} ?> ><?php esc_html_e( 'Message', 'mjschool' ); ?></option>
							</select>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input type="text" id="field_label" maxlength="30" class="mjschool-placeholder-color form-control  validate[required,custom[address_description_validation]]" name="field_label" placeholder="<?php esc_html_e( 'Enter Name', 'mjschool' ); ?>" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $custom_field_data->field_label ); ?>" <?php } ?>>
									<label  for="case_link"><?php esc_html_e( 'Label', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="case_link"><?php esc_html_e( 'Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<select id="field_type" class="form-control validate[required] dropdown_change"  name="field_type" <?php if ( $edit ) { ?> disabled <?php } ?>>
								<option value=""><?php esc_html_e( 'Select Input Type', 'mjschool' ); ?></option>
								<option value="text" <?php if ( $edit ) { selected( 'text', $custom_field_data->field_type );} ?> ><?php esc_html_e( 'Text Box', 'mjschool' ); ?></option>
								<option value="textarea" <?php if ( $edit ) { selected( 'textarea', $custom_field_data->field_type );} ?> ><?php esc_html_e( 'Textarea', 'mjschool' ); ?></option>
								<option value="dropdown" <?php if ( $edit ) { selected( 'dropdown', $custom_field_data->field_type );} ?> ><?php esc_html_e( 'Dropdown', 'mjschool' ); ?></option>
								<option value="date" <?php if ( $edit ) { selected( 'date', $custom_field_data->field_type );} ?> ><?php esc_html_e( 'Date Field', 'mjschool' ); ?></option>
								<option value="checkbox" <?php if ( $edit ) { selected( 'checkbox', $custom_field_data->field_type );} ?> ><?php esc_html_e( 'Checkbox', 'mjschool' ); ?></option>
								<option value="radio" <?php if ( $edit ) { selected( 'radio', $custom_field_data->field_type );} ?> ><?php esc_html_e( 'Radio', 'mjschool' ); ?></option>
								<option value="file" <?php if ( $edit ) { selected( 'file', $custom_field_data->field_type );} ?> ><?php esc_html_e( 'File', 'mjschool' ); ?></option>
							</select>
							<?php
							if ( $edit ) {
								$validation = explode( '|', $custom_field_data->field_validation );
								$min        = '';
								$max        = '';
								$file_type  = '';
								$file_size  = '';
								$Tclass     = $Dclass = null;
								foreach ( $validation as $key => $value ) {
									if ( strpos( $value, 'min' ) !== false ) {
										$min = $value;
									} elseif ( strpos( $value, 'max' ) !== false ) {
										$max = $value;
									} elseif ( strpos( $value, 'file_types' ) !== false ) {
										$file_type = $value;
									} elseif ( strpos( $value, 'file_upload_size' ) !== false ) {
										$file_size = $max;
									}
								}
								// ------------ Value checked in checkbox edit time. -----------//
								$input      = preg_quote( 'max', '~' ); // Don't forget to quote the input string!.
								$result_max = preg_grep( '~' . $input . '~', $validation );
								$input      = preg_quote( 'min', '~' ); // Don't forget to quote the input string!.
								$result_min = preg_grep( '~' . $input . '~', $validation );
								$exa             = $custom_field_data->field_validation;
								$max_find        = $max;
								$min_find        = $min;
								$file_type_find  = $file_type;
								$file_size_find  = $file_size;
								$limit_max       = substr( $max_find, 0, 3 );
								$limit_min       = substr( $min_find, 0, 3 );
								$limit_value_max = substr( $max_find, 4 );
								$limit_value_min = substr( $min_find, 4 );
								$file_type_value = substr( $file_type_find, 11 );
								$file_size_value = substr( $file_size_find, 17 );
								if ( $custom_field_data->field_type === 'dropdown' || $custom_field_data->field_type === 'checkbox' || $custom_field_data->field_type === 'radio' ) {
									$Tclass = 'disabled';
									$Dclass = 'disabled';
								} elseif ( $custom_field_data->field_type === 'text' || $custom_field_data->field_type === 'textarea' ) {
									$Dclass = 'disabled';
									$Tclass = null;
								} elseif ( $custom_field_data->field_type === 'date' ) {
									$Tclass = 'disabled';
									$Dclass = null;
								}
							}
							?>
						</div>
						<div class="col-md-6 mb-3 mjschool-main-custome-field mjschool-rtl-margin-top-15px">
							<div class="form-group">
								<div class="col-md-12 form-control" id="validation_msg">
									<div class="row mjschool-padding-radio">
										<div>
											<label class="mjschool-custom-top-label mjschool-margin-left-0" for="case_link"><?php esc_html_e( 'Validation', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<div class="row custom-control custom-checkbox mr-1 margin_left_custom_field mjschool-Validation-label">
												<label class="mjschool-rtl-margin-left-10px col-lg-6 col-md-6 col-sm-6 col-xs-12 margin_left_custom_field_new checkbox-inline mr-2">
													<input type="checkbox" name="validation[]"  value="nullable" class="nullable_rule mjschool-margin-top-0" <?php if ( $edit ) { if ( in_array( 'nullable', $validation ) ) { echo 'checked'; } } else { echo 'checked'; } ?> >
													<span class="mjschool-span-left-custom mjschool_margin_bottom_negetive_5px" ><?php esc_html_e( 'Nullable', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2">
													<input type="checkbox" name="validation[]"  value="required" class="required_rule mjschool-margin-top-0" <?php if ( $edit ) { if ( in_array( 'required', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( 'Required', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox"  name="validation[]" <?php if ( $edit ) { echo esc_attr( $Tclass ); } ?> value="numeric" id="only_number_id" class="only_number mjschool-margin-top-0" <?php if ( $edit ) { if ( in_array( 'numeric', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( 'Only Number', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" name="validation[]" <?php if ( $edit ) { echo esc_attr( $Tclass ); } ?> value="alpha" id="only_char_id" class="only_char mjschool-margin-top-0" <?php if ( $edit ) { if ( in_array( 'alpha', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( 'Only Character', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" name="validation[]" <?php if ( $edit ) { echo esc_attr( $Tclass ); } ?> value="alpha_space" id="char_space_id" class="char_space mjschool-margin-top-0" <?php if ( $edit ) { if ( in_array( 'alpha_space', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( 'Character with Space', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" name="validation[]" <?php if ( $edit ) { echo esc_attr( $Tclass ); } ?> value="alpha_num" id="char_num_id" class="char_num mjschool-margin-top-0" <?php if ( $edit ) { if ( in_array( 'alpha_num', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( 'Number & Character', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" id="email_id" class="email mjschool-margin-top-0" <?php if ( $edit ) { echo esc_attr( $Tclass ); } ?> name="validation[]"  value="email" <?php if ( $edit ) { if ( in_array( 'email', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( 'Email', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" name="validation[]" <?php if ( $edit ) { echo esc_attr( $Tclass ); } ?> class="opentext max mjschool-margin-top-0" id="max_value" value="max" <?php if ( $edit ) { if ( $result_max ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( 'Maximum', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" name="validation[]" <?php if ( $edit ) { echo esc_attr( $Tclass ); } ?> class="opentext min mjschool-margin-top-0" id="min_value" value="min" <?php if ( $edit ) { if ( $result_min ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( 'Minimum', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" class="url mjschool-margin-top-0" name="validation[]" <?php if ( $edit ) { echo esc_attr( $Tclass ); } ?> value="url" <?php if ( $edit ) { if ( in_array( 'url', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( 'URL', 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" name="validation[]" <?php if ( $edit ) { echo esc_attr( $Dclass ); } ?> id="date0" class="date mjschool-margin-top-0" value="before_or_equal:today" <?php if ( $edit ) { if ( in_array( 'before_or_equal:today', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( "Before Or Equal(Today's Date)", 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" name="validation[]" <?php if ( $edit ) { echo esc_attr( $Dclass ); } ?> id="date1"  class="date mjschool-margin-top-0"  value="date_equals:today" <?php if ( $edit ) { if ( in_array( 'date_equals:today', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( "Today's Date", 'mjschool' ); ?></span>
												</label>
												<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12 checkbox-inline mr-2 file_disable">
													<input type="checkbox" name="validation[]" <?php if ( $edit ) { echo esc_attr( $Dclass ); } ?> id="date2"  class="date mjschool-margin-top-0"   value="after_or_equal:today" <?php if ( $edit ) { if ( in_array( 'after_or_equal:today', $validation ) ) { echo 'checked'; } } ?> >
													<span class="mjschool-span-left-custom"><?php esc_html_e( "After Or Equal(Today's Date)", 'mjschool' ); ?></span>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
						if ( $edit ) {
							$custom_meta = $obj_custome_field->mjschool_get_single_custom_field_dropdown_meta_data( $custom_field_id );
							if ( $custom_field_data->field_type === 'dropdown' ) {
								?>
								<div class="sub_cat">
									<div class="form-group row">
										<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
											<div class="form-group input">
												<div class="col-md-6 form-control">
													<input type="text" maxlength="30" class="form-control validate[custom[popup_category_validation]] d_label" placeholder="">
													<label  for="case_link"><?php esc_html_e( 'Dropdown Label', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												</div>
											</div>
										</div>
										<div class="col-md-2">
											<input type="button"  name="menu_web" class="btn btn-primary mjschool-save-btn add_more_drop" value="<?php esc_attr_e( 'Add More', 'mjschool' ); ?>">
										</div>
									</div>
								</div>
								<div class="row mb-3">
									<div class="col-md-12 drop_label">
										<?php
										if ( ! empty( $custom_meta ) ) {
											foreach ( $custom_meta as $custom_metas ) {
												?>
												<div class="badge badge-danger label_data custom-margin">
													<input type="hidden" value="<?php echo esc_attr( $custom_metas->option_label ); ?>" name="d_label[]"><span><?php echo esc_html( $custom_metas->option_label ); ?></span ><a href="#"><i label_id="<?php echo esc_attr( $custom_metas->id ); ?>" class="fa fa-trash font-medium-2 delete_d_label" aria-hidden="true"></i></a>
												</div>
												&nbsp;
												<?php
											}
										}
										?>
									</div>
								</div>
								<?php
							} elseif ( $custom_field_data->field_type === 'checkbox' ) {
								?>
								<div class="checkbox_cat">
									<div class="form-group row">
										<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
											<div class="form-group input">
												<div class="col-md-6 form-control">
													<input type="text" maxlength="30" class="form-control validate[custom[popup_category_validation]] c_label" placeholder="">
													<label  for="case_link"><?php esc_html_e( 'Checkbox Label', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												</div>
											</div>
										</div>
										<div class="col-md-2">
											<input type="button"  name="menu_web" class="btn btn-primary mjschool-save-btn add_more_checkbox" value="<?php esc_attr_e( 'Add More', 'mjschool' ); ?>">
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12 checkbox_label mb-4">
										<?php
										if ( ! empty( $custom_meta ) ) {
											foreach ( $custom_meta as $custom_metas ) {
												?>
												<div class="badge badge-danger label_data label_checkbox custom-margin"  >
													<input type="hidden" value="<?php echo esc_attr( $custom_metas->option_label ); ?>"  name="c_label[]"><span><?php echo esc_html( $custom_metas->option_label ); ?></span><a href="#"><i label_id="<?php echo esc_attr( $custom_metas->id ); ?>" class="fa fa-trash font-medium-2 delete_c_label" aria-hidden="true"></i></a>
												</div>
												&nbsp;
												<?php
											}
										}
										?>
									</div>
								</div>
								<?php
							} elseif ( $custom_field_data->field_type === 'radio' ) {
								?>
								<div class="radio_cat">
									<div class="form-group row">
										<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
											<div class="form-group input">
												<div class="col-md-6 form-control">
													<input type="text" maxlength="30" class="form-control r_label validate[custom[popup_category_validation]]" placeholder="">
													<label  for="case_link"><?php esc_html_e( 'Radio Label', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												</div>
											</div>
										</div>
										<div class="col-md-2">
											<input type="button"  name="menu_web" class="btn btn-primary mjschool-save-btn add_more_radio" value="<?php esc_attr_e( 'Add More', 'mjschool' ); ?>">
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12 radio_label">
										<?php
										if ( ! empty( $custom_meta ) ) {
											foreach ( $custom_meta as $custom_metas ) {
												?>
												<div class="badge badge-danger label_radio custom-margin mjschool-custom-css">
													<input type="hidden" value="<?php echo esc_attr( $custom_metas->option_label ); ?>"  name="r_label[]"><span><?php echo esc_html( $custom_metas->option_label ); ?></span><a href="#" class="ml_5"><i class="fas fa-trash font-medium-2 delete_r_label" label_id="<?php echo esc_attr( $custom_metas->id ); ?>" aria-hidden="true"></i></a>
												</div>
												&nbsp;
												<?php
											}
										}
										?>
									</div>
								</div>
								<?php
							}
							?>
							<div class="file_type_and_size">
								<?php
								if ( strpos( $file_type_find, 'file_types' ) !== false ) {
									?>
									<style>
										.file_disable
										{
											opacity: 0.6;
											cursor: not-allowed;
											pointer-events: none;
										}
									</style>
									<div class="form-group row mb-3 margin_top_custome">
										<input type="hidden" name="validation[]" value="<?php echo esc_attr( $file_type_find ); ?>" class="file_types_value">
										<div class="col-md-6 input">
											<label class="ml-1 mjschool-custom-top-label top" for="userinput11"><?php esc_html_e( 'File Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span> </label>
											<select class="form-control file_types_input validate[required]" id="userinput11" name="file_type">
												<option value=""><?php esc_html_e( 'Select File Type', 'mjschool' ); ?></option>
												<?php
												foreach ( $document_type_array as $type ) {
													$type_trimmed = trim( $type );
													echo '<option value="' . esc_attr( $type_trimmed ) . '" ' . selected( $file_type_value, $type_trimmed, false ) . '>' . esc_html( strtoupper( $type_trimmed ) ) . '</option>';
												}
												?>
											</select>
										</div>
									</div>
									<?php
								}
								if ( strpos( $file_size_find, 'file_upload_size' ) !== false ) {
									?>
									<input type="hidden" name="validation[]" value="<?php echo esc_attr( $file_size_find ); ?>" class="file_size_value">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input">
											<div class="col-md-6 form-control">
												<input class="form-control file_size_input validate[required]" maxlength="30" type="text" id="userinput9" value="<?php echo esc_attr( $file_size_value ); ?>">
												<label class="mjschool-custom-top-label top" for="case_link"><?php esc_html_e( 'File Upload Size(kb', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
									<?php
								}
								?>
							</div>
							<?php
							if ( strpos( $max_find, 'max' ) !== false ) {
								?>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-6 form-control"  id="max_limit">
											<input type="number" class="form-control max_value validate[required,custom[onlyNumberSp]]" value="<?php echo esc_attr( $limit_value_max ); ?>"  id="max">
											<label  for="case_link"><?php esc_html_e( 'Maximum Limit', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<?php
							} else {
								?>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-6 form-control mjchool_display_none" id="max_limit">
											<input type="number" class="form-control max_value validate[required,custom[onlyNumberSp]]"  id="max" value="">
											<label  for="case_link"><?php esc_html_e( 'Maximum Limit', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<?php
							}
							if ( strpos( $min_find, 'min' ) !== false ) {
								?>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-6 form-control"  id="min_limit">
											<input type="number" class="form-control min_value validate[required,custom[onlyNumberSp]]" value="<?php echo esc_attr( $limit_value_min ); ?>"  id="min">
											<label  for="case_link"><?php esc_html_e( 'Minimum Limit', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<?php
							} else {
								?>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-6 form-control mjchool_display_none"  id="min_limit">
											<input type="number" class="form-control min_value validate[required,custom[onlyNumberSp]]"  id="min" value="">
											<label  for="case_link"><?php esc_html_e( 'Minimum Limit', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<?php
							}
						} else {
							?>
							<div class="sub_cat mjchool_display_none">
								<div class="form-group row mb-3">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input">
											<div class="col-md-6 form-control">
												<input type="text" maxlength="30" class="form-control validate[custom[popup_category_validation]] d_label d_label_new" placeholder="">
												<label  for="case_link"><?php esc_html_e( 'Dropdown Label', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<input type="button"  name="menu_web" class="btn btn-primary mjschool-save-btn add_more_drop" value="<?php esc_attr_e( 'Add More', 'mjschool' ); ?>">
									</div>
								</div>
							</div>
							<div class="row sub_cat">
								<div class="col-md-12 drop_label">
								</div>
							</div>
							<div class="checkbox_cat mjchool_display_none" >
								<div class="form-group row mb-3">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input">
											<div class="col-md-6 form-control">
												<input type="text" maxlength="30" class="form-control c_label validate[custom[popup_category_validation]]" placeholder="">
												<label  for="case_link"><?php esc_html_e( 'Checkbox Label', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<input type="button"  name="menu_web" class="btn btn-primary mjschool-save-btn add_more_checkbox" value="<?php esc_attr_e( 'Add More', 'mjschool' ); ?>">
									</div>
								</div>
							</div>
							<div class="row checkbox_cat mb-4">
								<div class="col-md-12 checkbox_label"></div>
							</div>
							<div class="radio_cat mjchool_display_none">
								<div class="form-group row mb-3">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input">
											<div class="col-md-6 form-control">
												<input type="text" maxlength="30" class="form-control r_label validate[custom[popup_category_validation]]" placeholder="">
												<label  for="case_link"><?php esc_html_e( 'Radio Label', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<input type="button"  name="menu_web" class="btn btn-primary mjschool-save-btn add_more_radio" value="<?php esc_attr_e( 'Add More', 'mjschool' ); ?>">
									</div>
								</div>
							</div>
							<div class="row radio_cat mb-4">
								<div class="col-md-12 radio_label"></div>
							</div>
							<div class="file_type_and_size mjchool_display_none" >
								<div class="form-group row mb-3 margin_top_custome">			
									<input type="hidden" name="validation[]" value="<?php echo esc_attr( $file_type_find ); ?>" class="file_types_value"> 
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="userinput11"> <?php esc_html_e( 'File Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span> </label>
										<select class="form-control file_types_input validate[required]" id="userinput11" name="file_type">
											<option value=""><?php esc_html_e( 'Select File Type', 'mjschool' ); ?></option>
											<?php
											foreach ( $document_type_array as $type ) {
												$type_trimmed = trim( $type );
												echo '<option value="' . esc_attr( $type_trimmed ) . '" ' . selected( $file_type_value, $type_trimmed, false ) . '>' . esc_html( strtoupper( $type_trimmed ) ) . '</option>';
											}
											?>
										</select>
									</div>
									<input type="hidden" name="validation[]" value="" class="file_size_value">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input">
											<div class="col-md-6 form-control">
												<input class="form-control file_size_input validate[required]" maxlength="30" type="text" id="userinput9">
												<label class="mjschool-custom-top-label top" for="case_link"><?php esc_html_e( 'File Upload Size(kb)', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-6 form-control mjchool_display_none" id="min_limit">
										<input type="number" class="form-control min_value validate[required,custom[onlyNumberSp]]"  id="min">
										<label  for="case_link"><?php esc_html_e( 'Minimum Limit', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-6 form-control mjchool_display_none" id="max_limit">
										<input type="number" class="form-control max_value validate[required,custom[onlyNumberSp]]"  id="max">
										<label  for="case_link"><?php esc_html_e( 'Maximum Limit', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
							<div class="form-group">
								<div class="col-md-12 form-control">
									<div class="row mjschool-padding-radio">
										<div>
											<label class="mjschool-custom-top-label" for="case_link"><?php esc_html_e( 'Visibility', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<input type="checkbox"  value="1" <?php if ( $edit ) { echo checked( $custom_field_data->field_visibility, '1' ); } else { echo 'checked'; } ?> class="mjschool-custom-control-input hideattar" name="field_visibility">
											<label class="mjschool_margin_bottom_negetive_5px" for="colorCheck1"><?php esc_html_e( 'Yes', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 mb-3">
							<div class="form-group">
								<div class="col-md-12 form-control mjschool-input-height-50px">
									<div class="row mjschool-padding-radio">
										<div class="input-group mjschool-input-checkbox">
											<label class="mjschool-custom-top-label"><?php esc_html_e( 'Show field in list', 'mjschool' ); ?></label>
											<div class="checkbox mjschool-checkbox-label-padding-8px">
												<label>
													<input type="checkbox" class="margin_right_checkbox mjschool-margin-right-5px_checkbox mjschool-margin-right-checkbox-css" name="show_in_table" value="1" <?php if ( $edit ) { echo checked( $custom_field_data->show_in_table, '1' );} ?> />&nbsp;<?php esc_html_e( 'Yes', 'mjschool' ); ?>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6">
							<input type="submit" id="add_custom_field" value="<?php if ( $edit ) { esc_html_e( 'Submit', 'mjschool' ); } else { esc_html_e( 'Add Custom Field', 'mjschool' );} ?>" name="add_custom_field" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
	?>
</div>