<?php
/**
 * Certificate and Letter Generation Page.
 *
 * This file serves as the main view/controller for generating various academic
 * certificates and letters (e.g., Transfer Certificate, Leaving Certificate)
 * within the Mjschool dashboard. It is responsible for:
 *
 * 1. Performing necessary access checks and browser validation.
 * 2. Implementing **role-based access control** for 'view', 'add', 'edit', and
 * 'delete' permissions specific to the 'certificate' module.
 * 3. Displaying and managing the list of available certificate/letter templates.
 * 4. Providing a form to select a student and the type of letter to generate.
 * 5. Processing the letter generation logic, including calling a function to
 * create the content (e.g., `mjschool_create_transfer_letter()`).
 * 6. Includes a script to handle URL parameters for pre-selecting a letter type.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
mjschool_browser_javascript_check();
$mjschool_role         = mjschool_get_user_role( get_current_user_id() );
$obj_document = new Mjschool_Document();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
if ( isset( $_POST['create_exprience_latter'] ) || isset( $_POST['save_and_print'] ) ) {

	$certificate_type = isset( $_POST['certificate_type'] ) ? sanitize_text_field( wp_unslash( $_POST['certificate_type'] ) ) : '';

	if ( isset( $_POST['edit'] ) ) {

		$l_type = $certificate_type;

		$emp_id = isset( $_POST['student_id'] ) ? intval( $_POST['student_id'] ) : 0;

		$result = $obj_document->mjschool_create_experience_letter( wp_unslash( $_POST ) );

		$latter_id = isset( $_POST['id'] ) ? intval( wp_unslash( $_POST['id'] ) ) : 0;

		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_list&message=cret_crt_edt' ) );
			exit;
		}

	} else {

		$emp_id = isset( $_POST['student_id'] ) ? intval( $_POST['student_id'] ) : 0;

		$result = $obj_document->mjschool_create_experience_letter( wp_unslash( $_POST ) );

		$l_type = $certificate_type;

		$certificate_id = isset( $_POST['certificate_id'] ) ? intval( $_POST['certificate_id'] ) : 0;

		$latter_id = $wpdb->insert_id;

		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_list&message=cret_crt' ) );
			exit;
		}
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$sanitized_id = intval( $id );
			$result = mjschool_delete_letter_table_by_id( $sanitized_id );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_list&message=1' ) );
				die();
			}
		}
	}
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'certificatelist';
$changed    = 0;
if ( isset( $_REQUEST['save_transfer'] ) ) {
	$changed = 1;
	update_option( 'mjschool_transfer_certificate_title', isset( $_REQUEST['mjschool_transfer_certificate_title'] ) ? wp_kses_post( wp_unslash( $_REQUEST['mjschool_transfer_certificate_title'] ) ) : '' );
	$result = update_option( 'mjschool_transfer_certificate_template', isset( $_REQUEST['mjschool_transfer_certificate_template'] ) ? wp_kses_post( wp_unslash( $_REQUEST['mjschool_transfer_certificate_template'] ) ) : '' );
	wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=certificate&page=certificate&tab=certificatelist&message=1' ) );
	die();
}
?>
<div class="mjschool-panel-white"> <!------- Panel white.  -------->
	<div class="mjschool-panel-body"> <!-------- Panel body. --------->
		<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
			<?php if ( $mjschool_role === 'administrator' || $mjschool_role === 'management' ) { ?>
				<li class="<?php if ( $active_tab === 'certificatelist' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=certificate&tab=certificatelist' ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'certificatelist' ? 'active' : ''; ?>">
						<?php esc_html_e( 'Certificate List', 'mjschool' ); ?>
					</a>
				</li>
				<?php
			}
			$mjschool_action = '';
			if ( ! empty( $_REQUEST['action'] ) ) {
				$mjschool_action = isset( $_POST['edate'] ) ? sanitize_text_field( wp_unslash( $_POST['edate'] ) ) : '';
			}
			?>
			<li class="<?php if ( $active_tab === 'assign_list' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_list' ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'assign_list' ? 'active' : ''; ?>">
					<?php esc_html_e( 'issued Certificate', 'mjschool' ); ?>
				</a>
			</li>
			<?php
			if ( $active_tab === 'assign_certificate' ) {
				if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
					?>
					<li class="<?php if ( $active_tab === 'assign_certificate' || ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) ) { ?> active<?php } ?>">
						<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addexam' ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e( 'Edit Assign Certificate', 'mjschool' ); ?>
						</a>
					</li>
					<?php
				} else {
					?>
					<li class="<?php if ( $active_tab === 'assign_certificate' ) { ?> active<?php } ?>">
						<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'assign_certificate' ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e( 'Assign Certificate', 'mjschool' ); ?>
						</a>
					</li>
					<?php
				}
			}
			?>
		</ul>
		<?php
		if ( $active_tab === 'certificatelist' ) {
			
			$latter_access_edit = 1;
			$changed            = 0;
			?>
			<?php $i = 1;
			$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
			switch ( $message ) {
				case '1':
					$message_string = esc_html__( 'Letter Template Updated Successfully.', 'mjschool' );
					break;
			}
			if ( $message ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class updated new_msg_mx mjschool-below-h2">
					<p> <?php echo esc_html( $message_string ); ?> </p>
					<button type="button" class="close_btn_new" onclick="closeMessage()">x</button>
				</div>
				<?php
			}
			?>
			<div class="header">
				<h4 class="mjschool-first-header">
					<?php esc_html_e( 'Certificate List', 'mjschool' ); ?>
				</h4>
			</div>
			<?php
			$certificate_list = $obj_document->mjschool_get_all_certificate_template();
			if ( $certificate_list ) {
				?>
				<div class="mjschool-calendar-event-new">
					<table class="table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Certificate Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Certificate Title', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 1;
							foreach ( $certificate_list as $retrieved_data ) {
								?>
								<tr>
									<td><?php echo esc_html( $retrieved_data->certificate_name ); ?></td>
									<td><?php echo esc_html( $retrieved_data->certificate_title ); ?></td>
									<td>
										<div class="btn-group">
											<ul class="action mjschool-action-ul mjschool_staff_actions_padding">
												<li>
													<div class="dropdown mjschool-show-hide-li">
														<button class="mjschool-staff-action-btn dropdown-toggle" type="button" id="dropdownMenu<?php echo esc_attr( $i ); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool_action.png' ); ?>">
														</button>
														<ul class="dropdown-menu mjschool-staff-action-option" aria-labelledby="dropdownMenu<?php echo esc_attr( $i ); ?>">
															<?php
															if ( $latter_access_edit === 1 ) {
																?>
																<li>
																	<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_certificate&action=new&letter_type=' . urlencode( $retrieved_data->certificate_name )) ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-edit.png' ); ?>"><?php esc_html_e( 'Assign', 'mjschool' ); ?>
																	</a>
																</li>
																<?php
															}
															?>
														</ul>
													</div>
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
				</div>
				<?php
			}
		}
		if ( $active_tab === 'assign_list' ) {
			$user_access_edit   = 1;
			$user_access_delete = 1;
			$user_access_add    = 1;
			?>
			<?php
			$i       = 1;
			$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
			$message_string = '';
			switch ( $message ) {
				case '1':
					$message_string = esc_html__( 'Certificate Deleted Successfully.', 'mjschool' );
					break;
				case 'cret_crt':
					$message_string = esc_html__( 'Certificate Created Successfully.', 'mjschool' );
					break;
				case 'cret_crt_edt':
					$message_string = esc_html__( 'Certificate Updated Successfully.', 'mjschool' );
					break;
			}
			if ( $message ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class updated new_msg_mx mjschool-below-h2">
					<p> <?php echo esc_html( $message_string ); ?> </p>
					<button type="button" class="close_btn_new" onclick="closeMessage()">x</button>
				</div>
				<?php
			}
			if ( $user_access_add === '1' ) {
				?>
				<div class="mjschool-dashboard-btn mjschool-addgroup-btn">
					<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_certificate&action=new' )); ?>">
						<button type="button" class="mjschool-form-submit-btn">
							<?php esc_html_e( 'Assign Certificate', 'mjschool' ); ?>
						</button>
					</a>
				</div>
			<?php } ?>
			<div class="header">
				<h4 class="mjschool-first-header">
					<?php esc_html_e( 'Assigned Certificate List', 'mjschool' ); ?>
				</h4>
			</div>
			<?php
			$certificate_list = $obj_document->mjschool_get_issued_certificate_list();
			if ( $certificate_list ) {
				?>
				<div class="mjschool-calendar-event-new">
					<form name="certificate" action="" method="post" class="mjschool-form-horizontal" id="certificate" enctype="multipart/form-data">
						<?php wp_nonce_field( 'mjschool_delete_certificate_nonce' ); ?>
						<table class="table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Certificate Type', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Issue Date', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1;
								foreach ( $certificate_list as $retrieved_data ) {
									?>
									<tr>
										<td><?php echo esc_html( $retrieved_data->student_name ); ?></td>
										<td><?php echo esc_html( $retrieved_data->certificate_type ); ?></td>
										<td><?php echo esc_html( $retrieved_data->issue_date ); ?></td>
										<td>
											<div class="btn-group">
												<ul class="action mjschool-action-ul mjschool_staff_actions_padding">
													<li>
														<div class="dropdown mjschool-show-hide-li">
															<button class="mjschool-staff-action-btn dropdown-toggle" type="button" id="dropdownMenu<?php echo esc_attr( $i ); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool_action.png' ); ?>">
															</button>
															<ul class="dropdown-menu mjschool-staff-action-option" aria-labelledby="dropdownMenu<?php echo esc_attr( $i ); ?>">
																<?php
																if ( $user_access_edit === '1' ) {
																	?>
																	<li>
																		<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_certificate&action=edit&student_id=' . mjschool_encrypt_id( $retrieved_data->student_id ) . '&acc=' . mjschool_encrypt_id( $retrieved_data->ID ) )); ?>">
																			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-edit.png' ); ?>">
																			<?php esc_html_e( 'Edit', 'mjschool' ); ?>
																		</a>
																	</li>
																	<?php
																}
																?>
															</ul>
														</div>
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
								<input type="checkbox" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>" >
								<label for="checkbox" class="mjschool-margin-right-5px"> <?php esc_html_e( 'Select All', 'mjschool' ); ?> </label>
							</button>
							<?php
							if ( $user_access_delete === '1' ) {
								?>
								
								<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
								<?php
							}
							?>
						</div>
						<?php if ( $user_access_delete === '1' ) { ?>
							<div class="mjschool-print-button pull-left"></div>
						<?php } ?>
					</form>
				</div>
			</div>
				<?php
			} else {
				if ( $user_access_add === '1' ) {
					?>
				<div class="mjschool-no-data-list-div mjschool_margin_top_40px">
					<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_certificate&action=new' ) ); ?>">
						<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
					</a>
					<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
						<label class="mjschool-no-data-list-label">
							<?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?>
						</label>
					</div>
				</div>
					<?php
				} else {
					?>
				<div class="mjschool-calendar-event-new">
					<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
				</div>
					<?php
				}
			}
		}
		if ( $active_tab === 'assign_certificate' ) {
			$students = mjschool_get_student_group_by_class();
			$edit     = 0;
			if ( isset( $_REQUEST['action'] ) && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' || sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'view' ) ) {
				$edit       = 1;
				$student_id = isset( $_REQUEST['student_id'] )
					? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ) )
					: 0;

				$ids = isset( $_REQUEST['acc'] )
					? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['acc'] ) ) ) )
					: 0;
				$post       = get_post( $ids );
				mjschool_create_transfer_letter();
			}
			$results = mjschool_get_certificate_id_and_name();
			$selected_cert = '';
			if ( isset( $_POST['certificate_type'] ) ) {
				$selected_cert = sanitize_text_field( wp_unslash( $_POST['certificate_type'] ) );
			} elseif ( isset( $_GET['letter_type'] ) ) {
				$selected_cert = sanitize_text_field( wp_unslash( $_GET['letter_type'] ) );
			}
			?>
			<?php
			$selected_student  = isset( $_POST['student_id'] ) ? sanitize_text_field( wp_unslash( $_POST['student_id'] ) ) : '';
			$selected_teacher  = isset( $_POST['teacher_id'] ) ? sanitize_text_field( wp_unslash( $_POST['teacher_id'] ) ) : '';
			$selected_teacher1 = isset( $_POST['teacher_new_id'] ) ? sanitize_text_field( wp_unslash( $_POST['teacher_new_id'] ) ) : '';
			?>
			<form name="certificate" action="" method="post" class="mjschool-form-horizontal" id="certificate" enctype="multipart/form-data">
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="header">
							<h4 class="mjschool-first-header"> <?php esc_html_e( 'Certificate Information', 'mjschool' ); ?> </h4>
						</div>
						<div class="col-md-3 input mjschool-single-select">
							<span class="mjschool-multiselect-label">
								<label class="ml-1 mjschool-custom-top-label top" for="staff_name">
									<?php esc_html_e( 'Select Student', 'mjschool' ); ?><span class="required">*</span>
								</label>
							</span>
							<select class="form-control add-search-single-select-js validate[required] display-members max_mjschool-width-70px0" name="student_id">
								<option value=""> <?php esc_html_e( 'Select Student', 'mjschool' ); ?> </option>
								<?php
								if ( $edit ) {
									$student = $result->student_id;
								} elseif ( ! empty( $selected_student ) ) {
									$student = $selected_student;
								} elseif ( isset( $_REQUEST['student_id'] ) ) {
									$student = $student_id;
								} else {
									$student = '';
								}
								$studentdata = mjschool_get_all_student_list( 'student' );
								foreach ( $students as $label => $opt ) {
									?>
									<optgroup label="<?php echo esc_attr( esc_html__( 'Class :', 'mjschool' ) . ' ' . $label ); ?>">
										<?php foreach ( $opt as $id => $name ) : ?>
											<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $id, $student ); ?> > <?php echo esc_html( $name ); ?> </option>
										<?php endforeach; ?>
									</optgroup>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-3 input">
							<label class="mjschool-custom-top-label mjschool-lable-top top" for="certificate_type">
								<?php esc_html_e( 'Certificate Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span>
							</label>
							<select id="certificate_type" name="certificate_type" class="form-control max_mjschool-width-70px0 validate[required] mjschool_45px">
								<!-- Static option. -->
								<option value="transfer_static" <?php selected( $selected_cert, 'transfer' ); ?>> <?php esc_html_e( 'Transfer', 'mjschool' ); ?></option>
								<!-- Dynamic options from DB. -->
								<?php foreach ( $results as $cert ) : ?>
									<option value="<?php echo esc_attr( $cert->certificate_name ); ?>" data-id="<?php echo esc_attr( $cert->id ); ?>" <?php selected( $selected_cert, $cert->certificate_name ); ?>>
										<?php echo esc_html( $cert->certificate_name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-3 input mjschool-single-select">
							<label class="ml-1 mjschool-custom-top-label top" for="student_id"><?php esc_html_e( 'Class Teacher', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<select name="teacher_id" id="teacher_id" class="form-control mjschool-max-width-100px validate[required] mjschool_45px">
								<option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></option>
								<?php mjschool_get_teacher_list_selected( $selected_teacher ); ?>
							</select>
						</div>
						<div class="col-md-3 input mjschool-single-select">
							<label class="ml-1 mjschool-custom-top-label top" for="student_id"><?php esc_html_e( 'Checked By', 'mjschool' ); ?></label>
							<select name="teacher_new_id" id="teacher_new_id" class="form-control mjschool-max-width-100px validate[required] mjschool-input-height-47px" >
								<option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></option>
								<?php mjschool_get_teacher_list_selected( $selected_teacher1 ); ?>
							</select>
						</div>
						<div class="col-md-2">
							<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'GO', 'mjschool' ); } else { esc_attr_e( 'GO', 'mjschool' ); } ?>" name="save_latter" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form>
			
			<div class="latter_content">
				<?php
				if ( isset( $_POST['save_latter'] ) ) {
					// Get the selected letter type.
					$certificate_type = isset( $_REQUEST['certificate_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_type'] ) ) : '';
					if ( $certificate_type ) {
						mjschool_create_transfer_letter();
					} else {
						echo '<p>' . esc_html__( 'No letter type selected.', 'mjschool' ) . '</p>';
					}
				}
				?>
			</div>
			<?php
		}
		?>
	</div> <!-------- Panel body. --------->
</div><!------- Panel white. -------->