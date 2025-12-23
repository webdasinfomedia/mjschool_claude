<?php 
/**
 * Admin Class Room Management Controller.
 *
 * This file handles all admin-side operations related to class room management in the Mjschool plugin.
 * It includes functionalities for adding, editing, deleting, and listing class rooms with full
 * role-based access control, nonce validation, and DataTables integration.
 *
 * Key Features:
 * - Role-based permission checks (add, edit, delete, view).
 * - Form submission handling with WordPress nonce verification.
 * - JavaScript validation and DataTables for interactive listing.
 * - Bulk delete functionality for selected records.
 * - Includes modular tab-based navigation (list / add / edit).
 * - Dynamic inclusion of `add-room.php` for form handling.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/class_room
 * @since      1.0.0
 */
//-------- Check browser javascript.. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role(get_current_user_id( ) );
if ($mjschool_role === 'administrator' ) 
{
	$user_access_add = '1';
	$user_access_edit = '1';
	$user_access_delete = '1';
	$user_access_view = '1';
} 
else 
{
	$user_access = mjschool_get_user_role_wise_filter_access_right_array( 'class_room' );
	$user_access_add = $user_access['add'];
	$user_access_edit = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ($user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die;
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			if ( 'class_room' === $user_access['page_link'] && (sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ($user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die;
				}
			}
			if ( 'class_room' === $user_access['page_link'] && (sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ($user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die;
				}
			}
			if ( 'class_room' === $user_access['page_link'] && (sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ($user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die;
				}
			}
		}
	}
}
?>

<?php
// This is Class at admin side.
if ( isset( $_POST['save_classroom'] ) ) {
	$nonce = sanitize_text_field( wp_unslash($_POST['_wpnonce']));
	if (wp_verify_nonce($nonce, 'save_class_room_admin_nonce' ) ) 
	{
		$subject_ids = isset($_POST['mjschool-subject-list']) ? json_encode(array_map('sanitize_text_field', wp_unslash($_POST['mjschool-subject-list']))) : json_encode([]);
		$created_date = date( "Y-m-d H:i:s");
		$classroomdata = array(
			'room_name' => sanitize_text_field(wp_unslash($_POST['room_name'] ) ),
			'class_id' => json_encode(array_map('intval', $_POST['class_name'])),
			'room_type' => sanitize_text_field(wp_unslash($_POST['room_type'] ) ),
			'room_capacity' => sanitize_text_field( wp_unslash($_POST['room_capacity'])),
			'created_by' => get_current_user_id(),
			'created_date' => $created_date,
			'sub_id' => $subject_ids
		);
		$tablename = "mjschool_class_room";
		if ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) 
		{
			$room_id = array( 'room_id' => intval(wp_unslash($_REQUEST['class_room_id'] ) ) );
			$result = mjschool_update_record($tablename, $classroomdata, $room_id);
			if ($result) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class_room&tab=class_room_list&message=2' ) );
				exit;
			}
		} 
		else 
		{
			$result = mjschool_insert_record($tablename, $classroomdata);
			if ($result) 
			{
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class_room&tab=class_room_list&message=1' ) );
				exit;
			}
		}
	}
}
$tablename = "mjschool_class_room";
/*Delete selected Subject.*/
if ( isset( $_REQUEST['delete_selected'] ) ) {
	// Verify nonce for bulk delete
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-class_rooms' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ($_REQUEST['id'] as $id) {
			$result = mjschool_delete_class_room($tablename, intval($id));
		}
	}
	if ($result) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class_room&tab=class_room_list&message=3' ) );
		exit;
	}
}
if ( isset( $_REQUEST['action']) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) {
	// Verify nonce for delete action
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'delete_action' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	$result = mjschool_delete_class_room($tablename, intval(wp_unslash($_REQUEST['class_room_id'])));
	if ($result) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class_room&tab=class_room_list&message=3' ) );
		exit;
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'class_room_list';
?>
<!-- End POP-UP Code. -->
<div class="mjschool-list-padding-5px"> <!--------- list page padding. ---------->
	<div class="mjschool-class-list"> <!--------- list page main wrapper. ---------->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ($message) 
		{
			case '1':
				$message_string = esc_html__( 'Class Room Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Class Room Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Class Room Deleted Successfully.', 'mjschool' );
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
				//--------------- CLASS LIST TABING. ----------------//
				if ($active_tab === 'class_room_list' ) 
				{
					$retrieve_class = mjschool_get_all_data($tablename);
					if ( ! empty( $retrieve_class ) ) 
					{
						?>
						<div class="mjschool-panel-body">
							<div class="table-responsive">
								<form id="frm-example" name="frm-example" method="post">
									<table id="mjschool-classroom-list" class="display" cellspacing="0" width="100%">
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
                                                    <td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-class.png' ); ?>" class="img-circle" /></td>
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
															<ul class="mjschool_ul_style">
																<li>
																	<a href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<!-- <img src="<?php //echo SMS_PLUGIN_URL . "/assets/images/listpage_icon/More.png" ?>"> -->
                                                                        <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<?php 
																		if ($user_access_edit === '1' ) { ?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class_room&tab=add_class_room&action=edit&class_room_id=' . $retrieved_data->room_id . '&_wpnonce=' . mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																		<?php } if ($user_access_delete === '1' ) { ?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class_room&tab=class_room_list&action=delete&class_room_id=' . $retrieved_data->room_id . '&_wpnonce=' . mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_attr_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																					<i class="fa fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> 
                                                                                </a>
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
						if ($user_access_add === '1' ) 
						{
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class_room&tab=add_class_room' ) ); ?>">
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
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php
						}
					}
				}
				// -------------- ADD CLASS ROOM TABING. -----------------//
				if ($active_tab === 'add_class_room' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/class-room/add-room.php';
				}
				?>
			</div>
		</div>
	</div>
</div