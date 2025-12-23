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
//-------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role(get_current_user_id( ) );
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

?>
<?php
// This is Class at admin side.
if ( isset( $_POST['save_classroom'] ) ) {

	// Verify nonce safely
	if ( ! isset( $_POST['_wpnonce'] ) || 
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_class_room_admin_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed', 'mjschool' ) );
	}

	// Sanitize inputs
	$room_name     = isset($_POST['room_name']) ? sanitize_text_field( wp_unslash($_POST['room_name']) ) : '';
	$class_name    = isset($_POST['class_name']) ? array_map('intval', (array) $_POST['class_name']) : [];
	$room_type     = isset($_POST['room_type']) ? sanitize_text_field( wp_unslash($_POST['room_type']) ) : '';
	$room_capacity = isset($_POST['room_capacity']) ? intval($_POST['room_capacity']) : 0;

	$subject_ids   = isset($_POST['mjschool-subject-list']) 
	                 ? wp_json_encode( array_map('intval', (array) sanitize_text_field( wp_unslash( $_POST['mjschool-subject-list']) ) ) )
	                 : wp_json_encode([]);

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

	$tablename = "mjschool_class_room";

	$action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';

	if ( $action === 'edit' ) {

		$room_id = isset($_REQUEST['class_room_id']) 
		           ? intval(wp_unslash($_REQUEST['class_room_id'])) 
		           : 0;

		if ( $room_id > 0 ) {
			$result = mjschool_update_record( $tablename, $classroomdata, array( 'room_id' => $room_id ) );
		}

		if ( isset($result) && $result ) {
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
$tablename = "mjschool_class_room";
/* Delete selected Subject. */
if ( isset(  $_REQUEST['delete_selected'] ) ) {
	// Verify nonce for delete action
	if ( ! isset( $_REQUEST['_wpnonce'] ) || 
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'save_class_room_admin_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) ) {
			foreach ($_REQUEST['id'] as $id) {
				$result = mjschool_delete_class_room($tablename, sanitize_text_field( wp_unslash( $id ) ) );
		}
	}
	if ($result) {
		wp_safe_redirect(home_url( '?dashboard=mjschool_user&page=class_room&tab=class_room_list&message=3' ) );
		exit;
	}
}
if ( isset( $_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action']) ) === 'delete' ) {
	// Verify nonce for delete action
	if ( ! isset( $_REQUEST['_wpnonce'] ) || 
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'save_class_room_admin_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed', 'mjschool' ) );
	}
	$result = mjschool_delete_class_room($tablename, sanitize_text_field( wp_unslash( $_REQUEST['class_room_id'] ) ) );
	if ($result) {
		wp_safe_redirect(home_url( '?dashboard=mjschool_user&page=class_room&tab=class_room_list&message=3' ) );
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
		switch ($message) 
		{
			case '1':
				esc_html__( 'Class Room Added Successfully.', 'mjschool' );
				break;
			case '2':
				esc_html__( 'Class Room Updated Successfully.', 'mjschool' );
				break;
			case '3':
				esc_html__( 'Class Room Deleted Successfully.', 'mjschool' );
				break;
		}
		if ($message) 
		{
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-below-h2 notice is-dismissible mjschool-message-disabled alert-dismissible">
				<p><?php echo esc_html( $message_string); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text">Dismiss this notice.</span></button>
			</div>
			<?php
		}
		?>
		<div class="mjschool-panel-white">
			<div class="mjschool-panel-body">
				<?php
				//--------------- Class list tabing. ----------------//
				if ($active_tab === 'class_room_list' ) 
				{
					
					$own_data = $user_access['own_data'];
					if ($mjschool_role === 'teacher' ) 
					{
						if ( $own_data === '1' ) 
						{
							$class_id 	= 	get_user_meta(get_current_user_id(), 'class_name', true);
							$retrieve_class	= mjschool_get_all_class_data_by_class_room_array($class_id);
						} 
						else 
						{
							$retrieve_class = mjschool_get_all_data($tablename);
						}
					}
					elseif ($mjschool_role === 'student' ) 
					{
						if ( $own_data === '1' ) 
						{
							$class_id 	= 	get_user_meta(get_current_user_id(), 'class_name', true);
							$retrieve_class	= mjschool_get_all_class_data_by_class_room_array($class_id);
						} 
						else 
						{
							$retrieve_class = mjschool_get_all_data($tablename);
						}
					}
					elseif ($mjschool_role === 'supportstaff' ) 
					{
						
						if ( $own_data === '1' ) 
						{
					        $retrieve_class = mjschool_get_user_own_data($tablename);
						}
						else 
						{
							$retrieve_class = mjschool_get_all_data($tablename);
						}
					}
					if ( ! empty( $retrieve_class ) ) 
					{
						?>
						<div class="mjschool-panel-body">
							<div class="table-responsive">
								<form id="frm-example" name="frm-example" method="post">
									<?php wp_nonce_field( 'save_class_room_admin_nonce' ); ?>
									<table id="mjschool-classroom-list-front" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header( ) ) ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Room Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Room Type', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Assign Class', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Room Capacity', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Created Date', 'mjschool' ); ?></th>
												<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											foreach ($retrieve_class as $retrieved_data) 
											{
												$room_id = $retrieved_data->room_id;
												?>
												<tr>
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr($room_id); ?>"></td>
													<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-Class.png"); ?>" class="img-circle" /></td>
													<td>
														<?php 
														$room_name = $retrieved_data->room_name;
														if( ! empty( $room_name ) ) 
														{
															echo esc_html( $room_name);
														} 
														else 
														{
															esc_html_e( 'N/A', 'mjschool' );
														} 
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Room Name', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php 
														$room_type = $retrieved_data->room_type;
														if( ! empty( $room_type ) ) 
														{
															echo esc_html( $room_type);
														} 
														else 
														{
															esc_html_e( 'N/A', 'mjschool' );
														} 
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Room Type', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														$class_id = json_decode($retrieved_data->class_id);
														$classname = "";
														foreach ($class_id as $class) 
														{
															$classname .= mjschool_get_class_name_category_wise($class) . ", ";
														}
														$classname_rtrim = rtrim($classname, ", ");
														$classname_ltrim = ltrim($classname_rtrim, ", ");
														if ( ! empty( $classname_ltrim ) ) 
														{
															echo esc_html( $classname_ltrim);
														} 
														else 
														{
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Assign Class', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														$class_id = json_decode($retrieved_data->sub_id);		
														$classname = "";
														foreach ($class_id as $class) 
														{
															$classname .= mjschool_get_subject_by_id($class) . ", ";
														}
														$classname_rtrim = rtrim($classname, ", ");
														$classname_ltrim = ltrim($classname_rtrim, ", ");
														if ( ! empty( $classname_ltrim ) ) 
														{
															echo esc_html( $classname_ltrim);
														} 
														else 
														{
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subjects', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php 
														if( ! empty( $retrieved_data->room_capacity ) ) 
														{
															echo esc_html( $retrieved_data->room_capacity);
														} 
														else 
														{
															esc_html_e( 'N/A', 'mjschool' );
														} 
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Room Capacity', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php 
														if( ! empty( $retrieved_data->created_date ) ) 
														{
															echo esc_html( mjschool_get_date_in_input_box($retrieved_data->created_date ) );
														} 
														else 
														{
															esc_html_e( 'N/A', 'mjschool' );
														} 
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Created Date', 'mjschool' ); ?>"></i>
													</td>
													<td class="action">
														<div class="mjschool-user-dropdown">
															<ul class="" class="mjschool_ul_style">
																<li class="">
																	<a class="" href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<?php if ($user_access['edit'] === '1' ) { ?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'class_room', 'tab' => 'add_class_room', 'action' => 'edit', 'class_room_id' => $retrieved_data->room_id ), admin_url() ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																		<?php } if ($user_access['delete'] === '1' ) { ?>
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
											<?php } ?>
										</tbody>
									</table>
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" name="" class="mjschool-sub-chk select_all mjschool_width_0px" value="">
											<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									</div>
								</form>
							</div>
						</div>
						<?php
					} 
					else 
					{
						if ($user_access['add'] === '1' ) 
						{
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'class_room', 'tab' => 'add_class_room' ), home_url() ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<?php
						} 
						else 
						{
							?>
							<div class="mjschool-calendar-event-new">
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php
						}
					}
				}
				
				// -------------- Add classroom tabbing. -----------------//
				if ($active_tab === 'add_class_room' ) { ?>
					<?php
					$edit = 0;
					if ( isset( $_REQUEST['action']) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) 
					{
						$edit = 1;
						$classroomdata = mjschool_get_class_room_by_id( sanitize_text_field( wp_unslash( $_REQUEST['class_room_id'] ) ) );
					}
					?>
					<div class="mjschool-panel-body"><!-------- Panel body. -------->
						<form name="mjschool-class-room-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-class-room-form"><!------- form Start --------->
						<?php $action = isset($_REQUEST['action']) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
							<input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'Class Room Information', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 rtl_mjschool-margin-top-15px">
										<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
											<?php
											$classes = array();
											if ($edit) 
											{
												$classes = json_decode($classroomdata->class_id, true); // Ensure associative array.
											} 
											elseif ( isset( $_POST['class_name'] ) ) {
												$classes = array_map('intval', (array) $_POST['class_name'] );
												$classes = array(); // Initialize as empty array.
											}
											?>
											<select name="class_name[]" multiple="multiple" class="validate[required] form-control" id="subject_teacher_subject_front">
												<?php
												foreach (mjschool_get_all_class() as $classdata) {
													$selected = in_array($classdata['class_id'], $classes) ? 'selected' : '';
													?>
													<option value="<?php echo esc_attr($classdata['class_id']); ?>" <?php echo esc_attr($selected); ?>>
														<?php echo esc_html( $classdata['class_name']); ?>
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
												<input id="room_name" class="form-control validate[required,custom[popup_category_validation,required]" maxlength="50" type="text" value="<?php if ($edit) { echo esc_attr($classroomdata->room_name);} ?>" name="room_name">
												<label for="userinput1" class=""><?php esc_html_e( 'Room Name', 'mjschool' ); ?><span class="required">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-6 rtl_mjschool-margin-top-15px mb-3 mjschool-teacher-list-multiselect">
										<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
											<?php
											$obj_subject = new Mjschool_Subject();
											$selected_subjects = array();
											if ($edit && !empty($classroomdata->sub_id ) ) {
												$selected_subjects = json_decode($classroomdata->sub_id, true);
											}
											$all_subjects = $obj_subject->mjschool_get_all_subject(); // You need to have this function or replace with your subject fetch logic.
											?>
											<select name="mjschool-subject-list[]" multiple="multiple" id="mjschool-subject-list-front" class="form-control validate[required] teacher_list">
												<?php foreach ($all_subjects as $subject) { ?>
													<option value="<?php echo esc_attr($subject->subid); ?>" <?php echo in_array($subject->subid, $selected_subjects) ? 'selected' : ''; ?>>
														<?php echo esc_html( $subject->sub_name . " - " . $subject->subject_code); ?>
													</option>
												<?php } ?>
											</select>
											<span class ="mjschool-multiselect-label">
												<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Subject','mjschool' );?><span class="required">*</span></label>
											</span>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="room_type" class="form-control validate[required,custom[popup_category_validation,required]" maxlength="50" type="text" value="<?php if ($edit) { echo esc_attr($classroomdata->room_type);} ?>" name="room_type">
												<label for="userinput1" class=""><?php esc_html_e( 'Room Type', 'mjschool' ); ?><span class="required">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="room_capacity" oninput="this.value = Math.abs(this.value)" class="form-control validate[min[0],maxSize[4]]" type="number" value="<?php if ($edit) { echo esc_attr($classroomdata->room_capacity); } ?>" name="room_capacity">
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
									<input type="submit" value="<?php if ($edit) { esc_html_e( 'Save Class Room', 'mjschool' );} else { esc_html_e( 'Add Class Room', 'mjschool' );} ?>" name="save_classroom" class="mjschool-save-btn" />
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