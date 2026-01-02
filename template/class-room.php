<?php
/**
 * Class Room and Building Management Page.
 *
 * This file serves as the main view/controller for managing **Class Rooms and School Buildings/Blocks**
 * within the Mjschool dashboard. It handles the complete CRUD (Create, Read, Update, Delete) lifecycle
 * for these physical assets.
 *
 * It is primarily responsible for:
 *
 * 1. **Access Control**: Performing necessary browser/JavaScript checks and implementing **role-based
 * access control** to restrict 'view', 'add', 'edit', and 'delete' operations based on the user's role.
 * 2. **Form Handling**: Displaying the 'Add/Edit Class Room' form (on the `addclassroom` tab)
 * and managing form data submission for creating or modifying room records.
 * 3. **List Display**: Presenting a tabular list of existing Class Rooms and Buildings (on the
 * `classroom_list` tab), showing details like room type, capacity, and associated building name.
 * 4. **CRUD Operations**: Processing URL actions (`action=edit`, `action=delete`, `action=insert`)
 * to perform the corresponding database operations for class rooms.
 * 5. **Data Validation**: Enforcing input validation (e.g., maximum length, minimum number check for capacity).
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
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

?>
<?php
// This is Class at admin side.
if ( isset( $_POST['save_classroom'] ) ) {

	// Verify nonce safely
	if ( ! isset( $_POST['_wpnonce'] ) || 
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_class_room_admin_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed', 'mjschool' ) );
	}

	// Sanitize inputs - Fixed: Removed nested sanitization for array values
	$room_name     = isset( $_POST['room_name'] ) ? sanitize_text_field( wp_unslash( $_POST['room_name'] ) ) : '';
	$class_name    = isset( $_POST['class_name'] ) && is_array( $_POST['class_name'] ) ? array_map( 'intval', wp_unslash( $_POST['class_name'] ) ) : array();
	$room_type     = isset( $_POST['room_type'] ) ? sanitize_text_field( wp_unslash( $_POST['room_type'] ) ) : '';
	$room_capacity = isset( $_POST['room_capacity'] ) ? intval( $_POST['room_capacity'] ) : 0;

	// Fixed: Removed nested sanitization and proper array handling
	$subject_ids   = isset( $_POST['mjschool-subject-list'] ) && is_array( $_POST['mjschool-subject-list'] ) 
	                 ? wp_json_encode( array_map( 'intval', wp_unslash( $_POST['mjschool-subject-list'] ) ) )
	                 : wp_json_encode( array() );

	$created_date  = current_time( 'mysql' ); 

	$classroomdata = array(
		'room_name'     => $room_name,
		'class_id'      => wp_json_encode( $class_name ),
		'room_type'     => $room_type,
		'room_capacity' => $room_capacity,
		'created_by'    => get_current_user_id(),
		'created_date'  => $created_date,
		'sub_id'        => $subject_ids,
	);

	$tablename = 'mjschool_class_room';

	$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

	if ( $action === 'edit' ) {

		$room_id = isset( $_REQUEST['class_room_id'] ) 
		           ? intval( wp_unslash( $_REQUEST['class_room_id'] ) ) 
		           : 0;

		if ( $room_id > 0 ) {
			$result = mjschool_update_record( $tablename, $classroomdata, array( 'room_id' => $room_id ) );
		}

		if ( isset( $result ) && $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=class_room&tab=class_room_list&message=2' ) );
			exit;
		}

	} else {

		$result = mjschool_insert_record( $tablename, $classroomdata );

		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=class_room&tab=class_room_list&message=1' ) );
			exit;
		}
	}
}
$tablename = 'mjschool_class_room';
/* Delete selected Subject. */
if ( isset( $_REQUEST['delete_selected'] ) ) {
	// Verify nonce for delete action
	if ( ! isset( $_REQUEST['_wpnonce'] ) || 
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'save_class_room_admin_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_class_room( $tablename, intval( $id ) );
		}
	}
	if ( isset( $result ) && $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=class_room&tab=class_room_list&message=3' ) );
		exit;
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
	// Verify nonce for delete action
	if ( ! isset( $_REQUEST['_wpnonce'] ) || 
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'save_class_room_admin_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed', 'mjschool' ) );
	}
	$result = mjschool_delete_class_room( $tablename, intval( $_REQUEST['class_room_id'] ) );
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=class_room&tab=class_room_list&message=3' ) );
		exit;
	}
}
$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'class_room_list';
?>
<!-- End POP-UP code. -->
<div class="mjschool-list-padding-5px"> <!--------- List page padding. ---------->
	<div class="mjschool-class-list"> <!--------- List page main wrapper. ---------->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Class Room Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Class Room Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Class Room Deleted Successfully.', 'mjschool' );
				break;
			default:
				$message_string = '';
				break;
		}
		if ( $message && ! empty( $message_string ) ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible margin_left_right_0" role="alert">
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
					<i class="fa fa-times"></i>
				</button>
				<strong><?php esc_html_e( 'Success! ', 'mjschool' ); ?></strong> <?php echo esc_html( $message_string ); ?>
			</div>
			<?php
		}
		?>
		<div class="row mjschool-panel-white"> <!--------- List page inner wrapper. ---------->
			<div class="mjschool-panel-body">
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
					<li class="<?php if ( $active_tab === 'class_room_list' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'class_room', 'tab' => 'class_room_list' ), home_url() ) ); ?>" class="mjschool-padding-left-0 mjschool-tab <?php echo esc_attr( $active_tab ) === 'class_room_list' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Class Room List', 'mjschool' ); ?>
						</a>
					</li>
					<?php
					if ( $active_tab === 'add_class_room' ) {
						if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
							?>
							<li class="<?php if ( $active_tab === 'add_class_room' || ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) ) { ?> active<?php } ?>">
								<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'add_class_room' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Edit Class Room', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						} else {
							?>
							<li class="<?php if ( $active_tab === 'add_class_room' ) { ?> active<?php } ?>">
								<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'add_class_room' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Add Class Room', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
					}
					?>
				</ul>
				<?php
				// -------------- Class Room list tabbing. -----------------//
				if ( $active_tab === 'class_room_list' ) {
					$class_room_data = mjschool_get_class_room_list();
					if ( ! empty( $class_room_data ) ) {
						?>
						<div class="row">
							<?php if ( $user_access['add'] === '1' ) { ?>
								<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 mjschool-button-section">
									<button class="mjschool-btn-sms-color mjschool-button-reload">
										<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'class_room', 'tab' => 'add_class_room' ), home_url() ) ); ?>" title="<?php esc_attr_e( 'Add Class Room', 'mjschool' ); ?>" class="mjschool-btn-sms-color-white">
											<?php esc_html_e( 'Add Class Room', 'mjschool' ); ?>
										</a>
									</button>
								</div>
							<?php } ?>
						</div>
						<div class="mjschool-list-table-page"><!------------ List page. ---------------->
							<div class="table-responsive">
								<form action="" method="post" name="table_form" id="table_form" onsubmit="return confirm_data();">
									<?php wp_nonce_field( 'save_class_room_admin_nonce' ); ?>
									<table id="class-room-details-page" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th><?php esc_html_e( 'No.', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Room Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Room Type', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Room Capacity', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 1;
											foreach ( $class_room_data as $retrieved_data ) {
												$color_class_css = mjschool_table_list_background_color( $i );
												?>
												<tr class="<?php echo esc_attr( $color_class_css ); ?>">
													<td>
														<input type="checkbox" name="id[]" class="mjschool-sub-chk" value="<?php echo esc_attr( $retrieved_data->room_id ); ?>">
														<?php echo esc_html( $i ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'No.', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( $retrieved_data->room_name ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Room Name', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( $retrieved_data->room_type ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Room Type', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( $retrieved_data->room_capacity ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Room Capacity', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														$all_class = json_decode( $retrieved_data->class_id, true );
														if ( ! empty( $all_class ) && is_array( $all_class ) ) {
															foreach ( $all_class as $clsid ) {
																$mjschool_class = new Mjschool_Class();
																echo esc_html( $mjschool_class->mjschool_get_class_name( $clsid ) ) . ', ';
															}
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														$all_subjects = json_decode( $retrieved_data->sub_id, true );
														if ( ! empty( $all_subjects ) && is_array( $all_subjects ) ) {
															foreach ( $all_subjects as $subid ) {
																$obj_subject = new Mjschool_Subject();
																$subject_info = $obj_subject->mjschool_get_subject_data_info( $subid );
																if ( $subject_info ) {
																	echo esc_html( $subject_info->sub_name ) . ', ';
																}
															}
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<div class="btn-group pull-right">
															<button type="button" class="btn mjschool-dropdown-setting-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																<i class="fas fa-ellipsis-v"></i>
															</button>
															<ul class="dropdown-menu mjschool-dropdown-menu dropdown-menu-right">
																<li>
																	<ul class="mjschool-hover-dropdown-menu">
																		<?php if ( $user_access['edit'] === '1' ) { ?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'class_room', 'tab' => 'add_class_room', 'action' => 'edit', 'class_room_id' => $retrieved_data->room_id ), admin_url() ) ); ?>" class="mjschool-float-left-width-100px mjschool_blue_color"> <i class="fa fa-pencil"></i> <?php esc_html_e( 'Edit', 'mjschool' ); ?> </a>
																			</li>
																		<?php } ?>
																		<?php if ( $user_access['delete'] === '1' ) { ?>
																			<li class="mjschool-float-left-width-100px">
																<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'class_room', 'tab' => 'class_room_list', 'action' => 'delete', 'class_room_id' => $retrieved_data->room_id ), admin_url() ), 'save_class_room_admin_nonce' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_attr_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fa fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
																			</li>
																		<?php } ?>
																	</ul>
																</li>
															</ul>
														</div>
													</td>
												</tr>
											<?php
											++$i;
											} ?>
										</tbody>
									</table>
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload" type="button">
											<input type="checkbox" name="" class="mjschool-sub-chk select_all mjschool_width_0px" value="">
											<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected" type="button"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" alt="<?php esc_attr_e( 'Delete', 'mjschool' ); ?>"></button>
									</div>
								</form>
							</div>
						</div>
						<?php
					} else {
						if ( $user_access['add'] === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'class_room', 'tab' => 'add_class_room' ), home_url() ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
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
				
				// -------------- Add classroom tabbing. -----------------//
				if ( $active_tab === 'add_class_room' ) { ?>
					<?php
					$edit = 0;
					if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
						$edit = 1;
						$classroomdata = mjschool_get_class_room_by_id( intval( $_REQUEST['class_room_id'] ) );
					}
					?>
					<div class="mjschool-panel-body"><!-------- Panel body. -------->
						<form name="mjschool-class-room-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-class-room-form"><!------- form Start --------->
						<?php $action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
							<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'Class Room Information', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 rtl_mjschool-margin-top-15px">
										<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
											<?php
											$classes = array();
											if ( $edit && isset( $classroomdata->class_id ) ) {
												$classes = json_decode( $classroomdata->class_id, true );
												$classes = is_array( $classes ) ? $classes : array();
											} elseif ( isset( $_POST['class_name'] ) && is_array( $_POST['class_name'] ) ) {
												$classes = array_map( 'intval', wp_unslash( $_POST['class_name'] ) );
											}
											?>
											<select name="class_name[]" multiple="multiple" class="validate[required] form-control" id="subject_teacher_subject_front">
												<?php
												$mjschool_class = new Mjschool_Class();
												foreach ( $mjschool_class->mjschool_get_all_class() as $classdata ) {
													$selected = in_array( $classdata['class_id'], $classes ) ? 'selected' : '';
													?>
													<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php echo esc_attr( $selected ); ?>>
														<?php echo esc_html( $classdata['class_name'] ); ?>
													</option>
													<?php 
												} ?>
											</select>
											<span class="mjschool-multiselect-label">
												<label class="ml-1 mjschool-custom-top-label top" for="staff_name">
													<?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="required">*</span>
												</label>
											</span>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="room_name" class="form-control validate[required,custom[popup_category_validation,required]" maxlength="50" type="text" value="<?php if ( $edit && isset( $classroomdata->room_name ) ) { echo esc_attr( $classroomdata->room_name ); } ?>" name="room_name">
												<label for="userinput1" class=""><?php esc_html_e( 'Room Name', 'mjschool' ); ?><span class="required">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-6 rtl_mjschool-margin-top-15px mb-3 mjschool-teacher-list-multiselect">
										<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
											<?php
											$obj_subject = new Mjschool_Subject();
											$selected_subjects = array();
											if ( $edit && isset( $classroomdata->sub_id ) && ! empty( $classroomdata->sub_id ) ) {
												$selected_subjects = json_decode( $classroomdata->sub_id, true );
												$selected_subjects = is_array( $selected_subjects ) ? $selected_subjects : array();
											}
											$all_subjects = $obj_subject->mjschool_get_all_subject();
											?>
											<select name="mjschool-subject-list[]" multiple="multiple" id="mjschool-subject-list-front" class="form-control validate[required] teacher_list">
												<?php
												if ( ! empty( $all_subjects ) ) {
													foreach ( $all_subjects as $subject ) { ?>
														<option value="<?php echo esc_attr( $subject->subid ); ?>" <?php echo in_array( $subject->subid, $selected_subjects ) ? 'selected' : ''; ?>>
															<?php echo esc_html( $subject->sub_name . ' - ' . $subject->subject_code ); ?>
														</option>
													<?php }
												}
												?>
											</select>
											<span class="mjschool-multiselect-label">
												<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="required">*</span></label>
											</span>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="room_type" class="form-control validate[required,custom[popup_category_validation,required]" maxlength="50" type="text" value="<?php if ( $edit && isset( $classroomdata->room_type ) ) { echo esc_attr( $classroomdata->room_type ); } ?>" name="room_type">
												<label for="userinput1" class=""><?php esc_html_e( 'Room Type', 'mjschool' ); ?><span class="required">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="room_capacity" oninput="this.value = Math.abs(this.value)" class="form-control validate[min[0],maxSize[4]]" type="number" value="<?php if ( $edit && isset( $classroomdata->room_capacity ) ) { echo esc_attr( $classroomdata->room_capacity ); } ?>" name="room_capacity">
												<label for="userinput1" class=""><?php esc_html_e( 'Room Capacity', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									
								</div>
							</div>
							<?php wp_nonce_field( 'save_class_room_admin_nonce' ); ?>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
									<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Class Room', 'mjschool' ); } else { esc_attr_e( 'Add Class Room', 'mjschool' ); } ?>" name="save_classroom" class="mjschool-save-btn" />
									</div>
								</div>
							</div>
						</form> <!------- Form end. --------->
					</div><!-------- Panel body. -------->
					<?php	
				}
				?>
			</div>
		</div>
	</div>
</div>