<?php
/**
 * Class Schedule and Routine Management File.
 *
 * This file handles the display, creation, and management of class routines (schedules).
 * It includes front-end validation, datepickers for time range selection, user
 * access control, and logic for creating and managing virtual classroom meetings
 * (e.g., Zoom) associated with the class routine periods. It also handles routine deletion.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( "mjschool_custom_class");
$cust_class_room = get_option( "mjschool_class_room");
?>

<?php
// Schedule.
$mjschool_obj_route    = new Mjschool_Class_Routine();
$obj_virtual_classroom = new Mjschool_Virtual_Classroom();
$active_tab            = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'schedulelist';
if ( isset( $_POST['create_meeting'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'create_meeting_admin_nonce' ) ) {
		$result = $obj_virtual_classroom->mjschool_create_meeting_in_zoom( wp_unslash($_POST) );
		if ( $result ) {
			$nonce = wp_create_nonce( 'mjschool_class_routine_tab' );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=virtual-classroom&tab=meeting_list&_wpnonce='.esc_attr( $nonce ).'&message=1') );
			die();
		}
	}
}
mjschool_browser_javascript_check();
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
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) == 'delete' ) {
	$tablename = 'mjschool_time_table';
	$result  = mjschool_delete_route( $tablename, mjschool_decrypt_id( wp_unslash($_REQUEST['route_id']) ) );
	if ( $result ) {
		$nonce = wp_create_nonce( 'mjschool_class_routine_tab' );
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=schedule&tab=schedulelist&_wpnonce='.esc_attr( $nonce ).'&message=5') );
		die();
	}
}
// -------------- DELETE TEACHER CLASS. ----------------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete_teacher' ) {
	$tablename = 'mjschool_time_table';
	$result  = mjschool_delete_route( $tablename, mjschool_decrypt_id( wp_unslash($_REQUEST['route_id']) ) );
	if ( $result ) {
		$nonce = wp_create_nonce( 'mjschool_class_routine_tab' );
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=schedule&tab=teacher_timetable&_wpnonce='.esc_attr( $nonce ).'&message=5') );
		die();
	}
}
if ( isset( $_GET['message'] ) && sanitize_text_field(wp_unslash($_GET['message'])) === 1 ) {
	 ?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
		<?php esc_html_e( 'Routine Added Successfully.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['message']) && sanitize_text_field(wp_unslash($_GET['message'])) === 2 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
		</button>
		<?php esc_html_e( 'Routine Alredy Added For This Time Period.Please Try Again.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['message']) && sanitize_text_field(wp_unslash($_GET['message'])) === 3) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
		<?php esc_html_e( 'Teacher Is Not Available.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['message']) && sanitize_text_field(wp_unslash($_GET['message'])) === 4) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
		<?php esc_html_e( 'Routine Updated Successfully.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['message']) && sanitize_text_field(wp_unslash($_GET['message'])) === 5) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
		<?php esc_html_e( 'Routine Deleted Successfully.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['message']) && sanitize_text_field(wp_unslash($_GET['message'])) === 6) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
		<?php esc_html_e( 'End Time should be greater than Start Time.', 'mjschool' ); ?>
		
	</div>
	<?php
}
?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="create_meeting_popup"></div>
	</div>
</div>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!----------- Panel body. ------------->
	<!---------------------- Tabbing. ---------------------->
	<?php
	if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
		?>
		<?php $nonce = wp_create_nonce( 'mjschool_class_routine_tab' ); ?>
		<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
			<li class="<?php if ( $active_tab === 'schedulelist' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=schedule&tab=schedulelist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'schedulelist' ? 'active' : ''; ?>"> <?php esc_html_e( 'Routine list', 'mjschool' ); ?></a>
			</li>
			<li class="<?php if ( $active_tab === 'teacher_timetable' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=schedule&tab=teacher_timetable&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'teacher_timetable' ? 'active' : ''; ?>"> <?php esc_html_e( 'Teacher TimeTable', 'mjschool' ); ?></a>
			</li>
			<?php
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) == 'edit' && $active_tab == 'addroute' ) {
				?>
				<li class="<?php if ( $active_tab === 'addroute' ) { ?> active<?php } ?>">
					<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addroute' ? 'nav-tab-active' : ''; ?>"> <?php esc_html_e( 'Edit Class Time Table', 'mjschool' ); ?></a>
				</li>
				<?php
			} elseif ( $mjschool_page_name === 'schedule' && $active_tab === 'addroute' ) {
				?>
				<li class="<?php if ( $active_tab === 'addroute' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?page=mjschool_library&tab=addbook' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addroute' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Add Class Time Table', 'mjschool' ); ?></a>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
	?>
	<div class="tab-content mjschool-class-schedule-tab-content"><!------------ Tab content. ------------>
		<div class="mjschool-panel-body"><!----------- Panel body. ------------->
			<div class="panel-group accordion accordion-flush mjschool-padding-top-15px-res" id="mjschool-accordion">
				<?php
				$i = 0;
				if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
					// ------------- SCHEDULE-LIST TAB. ---------------//
					if ( $active_tab === 'schedulelist' ) {

						// Check nonce for class schedule list tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_class_routine_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}

						$retrieve_class_data = mjschool_get_all_class();
						$i              = 0;
						if ( ! empty( $retrieve_class_data ) ) {
							foreach ( $retrieve_class_data as $class ) {
								if ( ! empty( $class ) ) {
									?>
									<div class="mt-1 accordion-item mjschool-class-border-div">
										<h4 class="accordion-header" id="heading<?php echo esc_attr( $i ); ?>">
											<a data-bs-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo esc_attr( $i ); ?>">
												<button class="accordion-button class_route_list collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo esc_attr( $i ); ?>" aria-expanded="true" aria-controls="collapse<?php echo esc_attr( $i ); ?>"> <?php esc_html_e( 'Class', 'mjschool' ); ?> : <?php echo esc_html( $class['class_name'] ); ?>
											</a>
										</h4>
										<div id="collapse<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse" aria-labelledby="headingOne" data-bs-parent="#mjschool-accordion">
											<div class="mjschool-panel-body">
												<table class="table table-bordered " cellspacing="0" cellpadding="0" border="0">
													<?php
													foreach ( mjschool_day_list() as $daykey => $dayname ) {
														?>
														<tr>
															<th><?php echo esc_html( $dayname ); ?></th>
															<td>
																<?php
																// ------- NEW LINE ADDED FOR ERROR. ---------//
																$sectionid = 0;
																// -----------------------------------------//
																$period = $mjschool_obj_route->mjschool_get_period( $class['class_id'], $sectionid, $daykey );
																if ( ! empty( $period ) ) {
																	// Sorting function based on start time and then end time.
																	usort(
																		$period,
																		function ( $a, $b ) {
																			$startA = DateTime::createFromFormat( 'h:i A', trim( $a->start_time ) );
																			$startB = DateTime::createFromFormat( 'h:i A', trim( $b->start_time ) );
																			if ( $startA === $startB ) {
																				$endA = DateTime::createFromFormat( 'h:i A', trim( $a->end_time ) );
																				$endB = DateTime::createFromFormat( 'h:i A', trim( $b->end_time ) );
																				return $endA <=> $endB;
																			}
																			return $startA <=> $startB;
																		}
																	);
																	foreach ( $period as $period_data ) {
																		echo '<div class="btn-group m-b-sm">';
																		if ( $period_data->multiple_teacher === 'yes' ) {
																			echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown"><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_attr( mjschool_get_single_subject_name( $period_data->subject_id ) ) . '( ' . esc_attr( mjschool_get_display_name( $period_data->teacher_id ) ) . ' )';
																		} else {
																			echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown"><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_attr( mjschool_get_single_subject_name( $period_data->subject_id ) );
																		}
																		$start_time_data = explode( ':', $period_data->start_time );
																		$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
																		$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
																		$end_time_data   = explode( ':', $period_data->end_time );
																		$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
																		$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
																		if ( $school_type === 'university' ){
																			if ($cust_class_room === 1) {	
																				$class_room = mjschool_get_class_room_name($period_data->room_id);
																				if( ! empty( $class_room ) )
																				{
																					echo '<span class="time"> ( ' . esc_html( $class_room->room_name) . ' ) </span>';
																				}
																			}
																		}
																		if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
																			$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
																			if ( empty( $meeting_data ) ) {
																				$create_meeting = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none show-popup" href="#" id="' . esc_attr( $period_data->route_id ) . '">' . esc_html__( 'Create Virtual Class', 'mjschool' ) . '</a></li>';
																			} else {
																				$create_meeting = '';
																			}
																			if ( ! empty( $meeting_data ) ) {
																				$update_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url(
																					'?dashboard=mjschool_user&page=virtual-classroom&tab=edit_meeting&action=edit&meeting_id=' . $meeting_data->meeting_id ) . '">' . esc_html__( 'Edit Virtual Class', 'mjschool' ) . '</a></li>';
																				$delete_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url(
																					'?dashboard=mjschool_user&page=virtual-classroom&tab=meeting_list&action=delete&meeting_id=' . $meeting_data->meeting_id ) . '" onclick="return confirm(\'' . esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ) . '\' );">' . esc_html__( 'Delete Virtual Class', 'mjschool' ) . '</a></li>';
																				$meeting_statrt_link = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url(
																					  $meeting_data->meeting_start_link ). '" target="_blank">' . esc_html__( 'Start Virtual Class', 'mjschool' ) . '</a></li>';
																			} else {
																				$update_meeting      = '';
																				$delete_meeting      = '';
																				$meeting_statrt_link = '';
																			}
																		}
																		echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ) </span>';
																		echo '</span><span class="caret"></span></button>';
																		if ( $user_access['edit'] === '1' ) {
																			$edit_route = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url(
																					'?dashboard=mjschool_user&page=schedule&tab=addroute&action=edit&route_id=' . esc_attr( mjschool_encrypt_id( $period_data->route_id ) ) ) . '">' . esc_html__( 'Edit Route', 'mjschool' ) . '</a></li>';
																		} else {
																			$edit_route = '';
																		}
																		if ( $user_access['delete'] === '1' ) {
																			$delete_route = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" onclick="return confirm(\'Do you want to to delet route?\' );" href="' . esc_url(
																					'?dashboard=mjschool_user&page=schedule&tab=schedulelist&action=delete&route_id=' . esc_attr( mjschool_encrypt_id( $period_data->route_id ) ) ) . '">' . esc_html__( 'Delete', 'mjschool' ) . '</a></li>';
																		} else {
																			$delete_route = '';
																		}
																		echo '</span></span> </button>';
																		if ( ( $edit_route === '' ) && ( $delete_route === '' ) && ( $create_meeting === '' ) && ( $update_meeting === '' ) && ( $delete_meeting === '' ) && ( $meeting_statrt_link === '' ) ) {
																			echo '';
																		} else {
																			echo '<ul role="menu" class="dropdown-menu schedule_menu"> ' . wp_kses_post( $edit_route ) . '' . wp_kses_post( $delete_route ) . '' . wp_kses_post( $create_meeting ) . '' . wp_kses_post( $update_meeting ) . '' . wp_kses_post( $delete_meeting ) . '' . wp_kses_post( $meeting_statrt_link ) . ' </ul>';
																		}
																		echo '</div>';
																	}
																}
																?>
															</td>
														</tr>
														<?php
													}
													?>
												</table>
											</div>
										</div>
									</div>
									<?php
								}
								$sectionname        = '';
								$sectionid          = '';
								$class_sectionsdata = mjschool_get_class_sections( $class['class_id'] );
								if ( ! empty( $class_sectionsdata ) ) {
									foreach ( $class_sectionsdata as $section ) {
										++$i;
										?>
										<div class="accordion-item mt-1 mjschool-class-border-div">
											<h4 class="accordion-header" id="heading<?php echo esc_attr( $i ); ?>">
												<a data-bs-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo esc_attr( $i ); ?>">
													<button class="accordion-button class_route_list collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo esc_attr( $i ); ?>" aria-expanded="true" aria-controls="collapse<?php echo esc_attr( $i ); ?>"> <?php esc_html_e( 'Class', 'mjschool' ); ?> : <?php echo esc_html( mjschool_get_class_section_name_wise( $section->class_id, $section->id ) ); ?> &nbsp;&nbsp;&nbsp;&nbsp;
												</a>
											</h4>
											<div id="collapse<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse" show" aria-labelledby="heading<?php echo esc_attr( $i ); ?>" data-bs-parent="#mjschool-accordion">
												<div class="mjschool-panel-body">
													<table class="table table-bordered mjschool-table-left" cellspacing="0" cellpadding="0" border="0">
														<?php
														foreach ( mjschool_day_list() as $daykey => $dayname ) {
															?>
															<tr>
																<th><?php echo esc_html( $dayname ); ?></th>
																<td>
																	<?php
																	$period = $mjschool_obj_route->mjschool_get_period( $class['class_id'], $section->id, $daykey );
																	if ( ! empty( $period ) ) {
																		// Sorting function based on start time and then end time
																		usort(
																			$period,
																			function ( $a, $b ) {
																				$startA = DateTime::createFromFormat( 'h:i A', trim( $a->start_time ) );
																				$startB = DateTime::createFromFormat( 'h:i A', trim( $b->start_time ) );
																				if ( $startA === $startB ) {
																					$endA = DateTime::createFromFormat( 'h:i A', trim( $a->end_time ) );
																					$endB = DateTime::createFromFormat( 'h:i A', trim( $b->end_time ) );
																					return $endA <=> $endB;
																				}
																				return $startA <=> $startB;
																			}
																		);
																		foreach ( $period as $period_data ) {
																			echo '<div class="btn-group m-b-sm">';
																			if ( $period_data->multiple_teacher === 'yes' ) {
																				echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown"><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_attr( mjschool_get_single_subject_name( $period_data->subject_id ) ) . '( ' . esc_attr( mjschool_get_display_name( $period_data->teacher_id ) ) . ' )';
																			} else {
																				echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown"><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_attr( mjschool_get_single_subject_name( $period_data->subject_id ) );
																			}
																			$start_time_data = explode( ':', $period_data->start_time );
																			$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
																			$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
																			$start_am_pm     = $start_time_data[2];
																			$end_time_data   = explode( ':', $period_data->end_time );
																			$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
																			$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
																			$end_am_pm       = $end_time_data[2];
																			$create_meeting  = '';
																			$update_meeting  = '';
																			$delete_meeting  = '';
																			if ( $school_type === 'university' ){
																				if ($cust_class_room === 1) {	
																					$class_room = mjschool_get_class_room_name($period_data->room_id);
																					if( ! empty( $class_room ) )
																					{
																						echo '<span class="time"> ( ' . esc_html( $class_room->room_name) . ' ) </span>';
																					}
																				}
																			}
																			echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' ' . esc_html( $start_am_pm ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ' . esc_html( $end_am_pm ) . ' ) </span>';
																			echo "</span><span class='caret'></span></button>";
																			$virtual_classroom_page_name    = 'virtual_classroom';
																			$virtual_classroom_access_right = mjschool_get_user_role_wise_filter_access_right_array( $virtual_classroom_page_name );
																			if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
																				if ( $virtual_classroom_access_right['view'] === '1' ) {
																					$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
																					if ( empty( $meeting_data ) ) {
																						if ( $virtual_classroom_access_right['add'] === '1' ) {
																							$create_meeting = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none show-popup" href="#" id="' . $period_data->route_id . '">' . esc_html__( 'Create Virtual Class', 'mjschool' ) . '</a></li>';
																						}
																					} else {
																						$create_meeting = '';
																					}
																					if ( ! empty( $meeting_data ) ) {
																						if ( $virtual_classroom_access_right['edit'] === '1' ) {
																							$update_meeting = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url('?dashboard=mjschool_user&page=virtual-classroom&tab=edit_meeting&action=edit&meeting_id=' . $meeting_data->meeting_id ). '">' . esc_html__( 'Edit Virtual Class', 'mjschool' ) . '</a></li>';
																						}
																						if ( $virtual_classroom_access_right['delete'] === '1' ) {
																							$delete_meeting = '<li class="mjschool-float-left width-100px">
	<a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url('?dashboard=mjschool_user&page=virtual-classroom&tab=meeting_list&action=delete&meeting_id=' . $meeting_data->meeting_id ) . '" onclick="return confirm(\'' . esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ) . '\');"> ' . esc_html__( 'Delete Virtual Class', 'mjschool' ) . '</a>
</li>';

																						}
																						$meeting_statrt_link = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url($meeting_data->meeting_start_link ). '" target="_blank">' . esc_html__( 'Start Virtual Class', 'mjschool' ) . '</a></li>';
																					} else {
																						$update_meeting      = '';
																						$delete_meeting      = '';
																						$meeting_statrt_link = '';
																					}
																				}
																			}
																			if ( $user_access['edit'] === '1' ) {
																				$edit_route = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url('?dashboard=mjschool_user&page=schedule&tab=addroute&action=edit&route_id=' . mjschool_encrypt_id( $period_data->route_id ) ) . '">' . esc_html__( 'Edit Route', 'mjschool' ) . '</a></li>';
																			} else {
																				$edit_route = '';
																			}
																			if ( $user_access['delete'] === '1' ) {
																				$delete_route = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" onclick="return confirm(\'Do you want to to delet route?\' );" href="' . esc_url('?dashboard=mjschool_user&page=schedule&tab=schedulelist&action=delete&route_id=' . mjschool_encrypt_id( $period_data->route_id ) ) . '">' . esc_html__( 'Delete', 'mjschool' ) . '</a></li>';
																			} else {
																				$delete_route = '';
																			}
																			echo '</span></span> </button>';
																			if ( ( $edit_route === '' ) && ( $delete_rout === '' ) && ( $create_meeting === '' ) && ( $update_meeting === '' ) && ( $delete_meeting === '' ) && ( $meeting_statrt_link === '' ) ) {
																				echo '';
																			} else {
																				echo '<ul role="menu" class="dropdown-menu schedule_menu"> ' . wp_kses_post( $edit_route ) . '' . wp_kses_post( $delete_route ) . '' . wp_kses_post( $create_meeting ) . '' . wp_kses_post( $update_meeting ) . '' . wp_kses_post( $delete_meeting ) . '' . wp_kses_post( $meeting_statrt_link ) . ' </ul>';
																			}
																			echo '</div>';
																		}
																	}
																	?>
																</td>
															</tr>
															<?php
														}
														?>
													</table>
												</div>
											</div>
										</div>
										<?php
									}
								}
								++$i;
							}
						} elseif ( $mjschool_role === 'administrator' || $user_access['add'] === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								
								<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=schedule&tab=addroute') ); ?>">
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
					// ---------------- ADD ROUTE TAB. ---------------//
					if ( $active_tab === 'addroute' ) {
						// ----------- SAVE ROUTE CODE. -------------//
						if ( isset( $_POST['save_route'] ) ) {
							$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
							
							if ( wp_verify_nonce( $nonce, 'save_root_admin_nonce' ) ) {
								$nonce = wp_create_nonce( 'mjschool_class_routine_tab' );
								$teacher_id                   = sanitize_text_field(wp_unslash($_POST['subject_teacher']));
								$start_time                   = mjschool_time_convert( sanitize_text_field(wp_unslash($_POST['start_time'])) );
								$end_time                     = mjschool_time_convert( sanitize_text_field(wp_unslash($_POST['end_time'])) );
								$start_time_1                 = sanitize_text_field(wp_unslash($_POST['start_time']));
								$end_time_1                   = sanitize_text_field(wp_unslash($_POST['end_time']));
								$start_time_convert           = date( 'h:i', strtotime( sanitize_text_field(wp_unslash($_POST['start_time'])) ) );
								$end_time_convert             = date( 'h:i', strtotime( sanitize_text_field(wp_unslash($_POST['end_time'])) ) );
								$start_time_data              = explode( ':', $start_time_1 );
								$start_hour                   = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
								$start_min                    = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
								$start_time_new               = $start_hour . ':' . $start_min;
								$start_time_in_24_hour_format = date( 'H:i', strtotime( $start_time_new ) );
								$end_time_data                = explode( ':', $end_time_1 );
								$end_hour                     = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
								$end_min                      = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
								$end_time_new                 = $end_hour . ':' . $end_min;
								$end_time_in_24_hour_format   = date( 'H:i', strtotime( $end_time_new ) );
								if ( ( $end_time_in_24_hour_format === '00:00' && $start_time_in_24_hour_format > '00:00' ) || ( $end_time_in_24_hour_format === '12:00' && $start_time_in_24_hour_format > '12:00' ) || ( $end_time_in_24_hour_format > $start_time_in_24_hour_format ) ) 
								{
									if (isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
										$route_data                     = array();
										$route_data['subject_id']       = sanitize_text_field(wp_unslash($_POST['subject_id']));
										$route_data['class_id']         = sanitize_text_field(wp_unslash($_POST['class_id']));
										$route_data['section_name']     = sanitize_text_field(wp_unslash($_POST['class_section']));
										$route_data['teacher_id']       = $teacher_id;
										$route_data['start_time']       = $start_time_new;
										$route_data['end_time']         = $end_time_new;
										$route_data['weekday']          = sanitize_text_field(wp_unslash($_POST['weekday']));
										$route_data['multiple_teacher'] = 'yes';
										$route_data['room_id'] = sanitize_text_field(wp_unslash($_POST['room_id']));
									} else {
										$route_data = array();
										foreach ( $teacher_id as $teacher ) {
											foreach ( $_POST['weekday'] as $week_days ) {
												$route_data[] = array(
													'subject_id' => sanitize_text_field(wp_unslash($_POST['subject_id'])),
													'class_id' => sanitize_text_field(wp_unslash($_POST['class_id'])),
													'section_name' => isset($_POST['class_section']) ? sanitize_text_field(wp_unslash($_POST['class_section'])) : '',
													'teacher_id' => $teacher,
													'start_time' => $start_time_new,
													'end_time' => $end_time_new,
													'weekday' => $week_days,
													'multiple_teacher' => 'yes',
													'room_id' => isset($_POST['room_id']) ? sanitize_text_field(wp_unslash($_POST['room_id'])) : '',
												);
											}
										}
									}
									if ( $_REQUEST['action'] === 'edit' ) {
										$route_id = array( 'route_id' => mjschool_decrypt_id( wp_unslash($_REQUEST['route_id']) ) );
										$mjschool_obj_route->mjschool_update_route( $route_data, $route_id );
										wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=schedule&tab=schedulelist&_wpnonce='.esc_attr( $nonce ).'&message=4') );
										die();
									} else {
										foreach ( $route_data as $route ) {
											$retuen_val = $mjschool_obj_route->mjschool_is_route_exist( $route );
										}
										if ( $retuen_val === 'success' ) {
											$route_id_array = $mjschool_obj_route->mjschool_save_route_with_virtual_class( $route_data );
											if ( $route_id_array ) {
												foreach ( $route_id_array as $route_id ) {
													if ( sanitize_text_field(wp_unslash($_POST['create_virtual_classroom'])) === 1 ) {
														$start_date = sanitize_text_field(wp_unslash($_POST['start_date']));
														$end_date   = sanitize_text_field(wp_unslash($_POST['end_date']));
														$agenda     = sanitize_text_field(wp_unslash($_POST['agenda']));
														$obj_mark   = new Mjschool_Class_Routine();
														$route_data = mjschool_get_route_by_id( $route_id );
														$start_time = mjschool_time_convert( $route_data->start_time );
														$end_time   = mjschool_time_convert( $route_data->end_time );
														if ( empty( $_POST['password'] ) ) {
															$password = wp_generate_password( 10, true, true );
														} else {
															$password = sanitize_text_field(wp_unslash($_POST['password']));
														}
														$metting_data = array(
															'teacher_id' => $route_data->teacher_id,
															'password' => $password,
															'start_date' => $start_date,
															'start_time' => $start_time,
															'end_date' => $end_date,
															'end_time' => $end_time,
															'weekday' => $route_data->weekday,
															'agenda' => $agenda,
															'route_id' => $route_id,
															'class_id' => $route_data->class_id,
															'class_section_id' => $route_data->section_name,
															'subject_id' => $route_data->subject_id,
															'action' => 'insert',
														);
														$result       = $obj_virtual_classroom->mjschool_create_meeting_in_zoom( $metting_data );
													}
												}
												wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=schedule&tab=schedulelist&_wpnonce='.esc_attr( $nonce ).'&message=1') );
												die();
											}
										} elseif ( $retuen_val === 'duplicate' ) {
											wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=schedule&tab=schedulelist&_wpnonce='.esc_attr( $nonce ).'&message=2') );
											die();
										} elseif ( $retuen_val === 'teacher_duplicate' ) {
											wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=schedule&tab=schedulelist&_wpnonce='.esc_attr( $nonce ).'&message=3') );
											die();
										}
									}
								} else {
									wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=schedule&tab=schedulelist&_wpnonce='.esc_attr( $nonce ).'&message=6') );
									die();
								}
							}
						}
						?>
						<div class="mjschool-panel-white"><!--------------- Panel white. ------------------>
							<?php
							$edit = 0;
							if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
								$edit       = 1;
								$route_data = mjschool_get_route_by_id( mjschool_decrypt_id( wp_unslash($_REQUEST['route_id']) ) );
							}
							?>
							<div class="mjschool-panel-body"><!--------------- Panel body. -------------------->
								<!-------------- Route form start. --------------------->
								<form name="route_form" action="" method="post" class="mjschool-form-horizontal" id="rout_form">
									<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
									<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-md-6 input">
												<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label>
												<?php
												if ( $edit ) {
													$classval = $route_data->class_id;
												} elseif ( isset( $_POST['class_id'] ) ) {
													$classval = sanitize_text_field(wp_unslash($_POST['class_id']));
												} else {
													$classval = '';
												}
												?>
												<select name="class_id" id="mjschool-class-list" class="form-control validate[required] mjschool-line-height-30px mjschool-max-width-100px">
													<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
													<?php
													foreach ( mjschool_get_all_class() as $classdata ) {
														?>
														<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
													<?php } ?>
												</select>
											</div>
											<?php wp_nonce_field( 'save_root_admin_nonce' ); ?>
											<?php if ( $school_type === 'school' ) {?>
												<div class="col-md-6 input">
													<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
													<select name="class_section" class="form-control mjschool-max-width-100px mjschool-line-height-30px mjschool-section-id-exam" id="class_section">
														<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
														<?php
														if ( $edit ) {
															foreach ( mjschool_get_class_sections( $route_data->class_id ) as $sectiondata ) {
																?>
																<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
																<?php
															}
														}
														?>
													</select>
												</div>
											<?php } ?>
											<div class="col-md-6 input">
												<label class="ml-1 mjschool-custom-top-label top" for="mjschool-subject-list"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="required">*</span></label>
												<?php
												if ( $edit ) {
													$subject_id = $route_data->subject_id;
												} elseif ( isset( $_POST['subject_id'] ) ) {
													$subject_id = sanitize_text_field(wp_unslash($_POST['subject_id']));
												} else {
													$subject_id = '';
												}
												?>
												<select name="subject_id" id="mjschool-subject-list" class="form-control mjschool-change-subject validate[required] mjschool-line-height-30px mjschool-max-width-100px">
													<?php
													if ( $edit ) {
														$subject = mjschool_get_subject_by_class_id( $route_data->class_id );
														if ( ! empty( $subject ) ) {
															foreach ( $subject as $ubject_data ) {
																?>
																<option value="<?php echo esc_attr( $ubject_data->subid ); ?>" <?php selected( $subject_id, $ubject_data->subid ); ?>><?php echo esc_html( $ubject_data->sub_name ); ?></option>
																<?php
															}
														}
													} else {
														?>
														<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
														<?php
													}
													?>
												</select>
											</div>
											<?php
											if ( $school_type === 'university' )
											{
												if ( $cust_class_room === 1)
												{	?>
													<div class="col-md-6 input">
														<label class="ml-1 mjschool-custom-top-label top" for="classroom_id"><?php esc_html_e( 'Class Room', 'mjschool' ); ?><span class="required">*</span></label>
														<?php if ( $edit){ $room_id=$route_data->room_id; }elseif( isset( $_POST['room_id'] ) ){$room_id=sanitize_text_field(wp_unslash($_POST['room_id']));}else{$room_id='';}?>
														<select name="room_id" id="classroom_id" class="form-control validate[required] mjschool-max-width-100px">
															<option value=""><?php esc_html_e( 'Select class Room', 'mjschool' ); ?></option>
															<?php
															if( $edit )
															{
																$classroom = mjschool_get_assign_class_room_for_single_class($route_data->class_id);
																if( ! empty( $classroom ) )
																{
																	foreach ($classroom as $room_data)
																	{
																	?>
																		<option value="<?php echo esc_attr($room_data->room_id) ;?>" <?php selected($room_id, $room_data->room_id);  ?>><?php echo esc_html( $room_data->room_name);?></option>
																	<?php 
																	}
																}
															}
															?>
														</select>
													</div>
													<?php
												}
											}
											if ( $edit ) {
												$teachval = mjschool_teacher_by_subject_id( $subject_id );
												?>
												<div class="col-md-6 input">
													<label class="ml-1 mjschool-custom-top-label top" for="subject_teacher"><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?><span class="required">*</span></label>
													<select id="subject_teacher" name="subject_teacher" class="form-control validate[required] teacher_list mjschool-input-height-47px">
														<option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></option>
														<?php
														foreach ( $teachval as $teacher ) {
															?>
															<option value="<?php echo esc_attr( $teacher ); ?>" <?php selected( $route_data->teacher_id, $teacher ); ?>><?php echo esc_html( mjschool_get_display_name( $teacher ) ); ?></option>
															<?php
														}
														?>
													</select>
												</div>
												<?php
											} else {
												?>
												<div class="col-md-6 mjschool-rtl-margin-top-15px mjschool-teacher-list-multiselect">
													<div class="col-sm-12 mjschool-multiselect-validation-teacher mjschool-multiple-select mjschool-rtl-padding-left-right-0px mjschool-res-rtl-width-100px">
														<select name="subject_teacher[]" multiple="multiple" id="subject_teacher" class="form-control validate[required] teacher_list">
														</select>
													</div>
												</div>
												<?php
											}
											if ( $edit ) {
												$day_key = $route_data->weekday;
											} elseif ( isset( $_POST['weekday'] ) ) {
												$day_key = sanitize_text_field(wp_unslash($_POST['weekday']));
											} else {
												$day_key = '';
											}
											if ( $edit ) {
												?>
												<div class="col-md-6 input">
													<label class="ml-1 mjschool-custom-top-label top" for="weekday"><?php esc_html_e( 'Day', 'mjschool' ); ?></label>
													<select name="weekday" class="form-control validate[required] mjschool-line-height-30px mjschool-max-width-100px" id="weekday">
														<?php
														foreach ( mjschool_day_list() as $daykey => $dayname ) {
															echo '<option  value="' . esc_attr( $daykey ) . '" ' . selected( $day_key, $daykey ) . '>' . esc_html( $dayname ) . '</option>';
														}
														?>
													</select>
												</div>
												<?php
											} else {
												?>
												<div class="col-md-6 input mjschool-multiple-select">
													<select name="weekday[]" class="form-control validate[required] mjschool-line-height-30px mjschool-max-width-100px mjschool-multiple-select-day" id="weekday" multiple="multiple">
														<?php
														foreach ( mjschool_day_list() as $daykey => $dayname ) {
															echo '<option  value="' . esc_attr( $daykey ) . '" ' . selected( $day_key, $daykey ) . '>' . esc_html( $dayname ) . '</option>';
														}
														?>
													</select>
												</div>
												<?php
											}
											?>
											<div class="col-md-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input type="text" id="start_time" name="start_time" class="form-control timepicker validate[required] start_time" value="<?php if ( ! empty( $route_data->start_time ) ) { echo esc_html( $route_data->start_time );} ?>" />
														<label for="start_time"><?php esc_html_e( 'Start Time', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input type="text" id="end_time" name="end_time" class="form-control timepicker validate[required] end_time" value="<?php if ( ! empty( $route_data->end_time ) ) { echo esc_html( $route_data->end_time );} ?>" />
														<label for="end_time"><?php esc_html_e( 'End Time', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
									if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
										if ( ! $edit ) {
											$virtual_classroom_access_right = mjschool_get_user_role_wise_filter_access_right_array( 'virtual_classroom' );
											if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' && $virtual_classroom_access_right['add'] === '1' ) {
												?>
												<!-- Create Virtual Classroom. -->
												<div class="form-body mjschool-user-form">
													<div class="row">
														<div class="col-md-6 mjschool-rtl-margin-top-15px mb-3">
															<div class="form-group">
																<div class="col-md-12 form-control mjschool-input-height-50px">
																	<div class="row mjschool-padding-radio">
																		<div class="input-group mjschool-input-checkbox">
																			<label class="mjschool-custom-top-label"><?php esc_html_e( 'Create Virtual Class', 'mjschool' ); ?></label>
																			<div class="checkbox mjschool-checkbox-label-padding-8px">
																				<label>
																					<input type="checkbox" id="isCheck" class="mjschool-margin-right-checkbox-css create_virtual_classroom" name="create_virtual_classroom" value="1" />
																				</label>
																			</div>
																			<label>&nbsp;&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?></label>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="form-body mjschool-user-form mjschool-create-virtual-classroom-div mjschool-create-virtual-classroom-div-none">
													<div class="row">
														<div class="col-md-6">
															<div class="form-group input">
																<div class="col-md-12 form-control">
																	<input id="start_date" class="form-control validate[required] text-input start_date" type="text" placeholder="<?php esc_html_e( 'Enter Start Date', 'mjschool' ); ?>" name="start_date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
																	<label for="userinput1"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group input">
																<div class="col-md-12 form-control">
																	<input id="end_date" class="form-control validate[required] text-input end_date" type="text" placeholder="<?php esc_html_e( 'Enter End Date', 'mjschool' ); ?>" name="end_date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
																	<label for="userinput1"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group input">
																<div class="col-md-12 form-control">
																	<input id="end_date" class="form-control validate[custom[address_description_validation]]" type="text" name="password" value="">
																	<label for="userinput1"><?php esc_html_e( 'Topic', 'mjschool' ); ?></label>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group input">
																<div class="col-md-12 form-control">
																	<input id="end_date" class="form-control validate[required,minSize[8],maxSize[12]] text-input" type="password" name="agenda" value="">
																	<label for="userinput1"><?php esc_html_e( 'Password', 'mjschool' ); ?><span class="required">*</span></label>
																</div>
															</div>
														</div>
													</div>
												</div>
												<?php
											}
										}
									}
									?>
									<div class="form-body">
										<div class="row">
											<div class="col-sm-6">
												<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Route', 'mjschool' ); } else { esc_html_e( 'Add Route', 'mjschool' ); } ?>" name="save_route" class="btn btn-success mjschool-save-btn" />
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
						<?php
					}
					// ------------- Schedule-list tab. ---------------//
					if ( $active_tab === 'teacher_timetable' ) {

						// Check nonce for class schedule list tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_class_routine_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}
						?>
						<div class="mjschool-panel-white mjschool-margin-top-20px"><!-------- Panel white. ------->
							<div class="mjschool-panel-body"><!-------- Panel body. ------->
								<div id="accordion" class="mjschool_fix_accordion panel-group accordion accordion-flush mjschool-padding-top-15px-res" aria-multiselectable="true" role="tablist">
									<?php
									$page        = 'schedule';
									$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
									$own_data    = $user_access['own_data'];
									if ( $own_data === '1' ) {
										$user_id       = get_current_user_id();
										$teacherdata[] = get_userdata( $user_id );
									} else {
										$teacherdata = mjschool_get_users_data( 'teacher' );
									}
									if ( ! empty( $teacherdata ) ) {
										$i = 0;
										foreach ( $teacherdata as $retrieved_data ) {
											$teacher_obj = new Mjschool_Teacher();
											$classes     = '';
											$classes     = $teacher_obj->mjschool_get_class_by_teacher( $retrieved_data->ID );
											$classname   = '';
											foreach ( $classes as $class ) {
												$classname .= mjschool_get_class_name( $class['class_id'] ) . ',';
											}
											$classname_rtrim = rtrim( $classname, ', ' );
											$classname_ltrim = ltrim( $classname_rtrim, ', ' );
											?>
											<div class="mt-1 accordion-item mjschool-class-border-div">
												<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
													<button class="accordion-button class_route_list collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
														<span class="Title_font_weight"><?php esc_html_e( 'Teacher', 'mjschool' ); ?></span> : 
														<?php
														if ( ! empty( $classname_ltrim ) ) {
															echo esc_html( $retrieved_data->display_name ) . '( ' . esc_html( $classname_ltrim ) . ' )';
														} else {
															echo esc_html( $retrieved_data->display_name );
														}
														?>
													</button>
												</h4>
												<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
													<div class="mjschool-panel-body">
														<table class="table table-bordered">
															<?php
															++$i;
															foreach ( mjschool_day_list() as $daykey => $dayname ) {
																?>
																<tr>
																	<th><?php echo esc_html( $dayname ); ?></th>
																	<td>
																		<?php
																		$period_1 = $mjschool_obj_route->mjschool_get_period_by_teacher( $retrieved_data->ID, $daykey );
																		$period_2 = $mjschool_obj_route->mjschool_get_period_by_particular_teacher( $retrieved_data->ID, $daykey );
																		$period   = array();
																		if ( ! empty( $period_1 ) ) {
																			$period = $period_1;
																		}
																		if ( ! empty( $period_2 ) ) {
																			$period = array_merge( $period, $period_2 );
																		}
																		if ( ! empty( $period ) ) {
																			// Sort by start time.
																			usort(
																				$period,
																				function ( $a, $b ) {
																					$startA = DateTime::createFromFormat( 'h:i A', trim( $a->start_time ) );
																					$startB = DateTime::createFromFormat( 'h:i A', trim( $b->start_time ) );
																					if ( $startA === $startB ) {
																						$endA = DateTime::createFromFormat( 'h:i A', trim( $a->end_time ) );
																						$endB = DateTime::createFromFormat( 'h:i A', trim( $b->end_time ) );
																						return $endA <=> $endB;
																					}
																					return $startA <=> $startB;
																				}
																			);
																			foreach ( $period as $period_data ) {
																				// Optional: Skip mismatched days.
																				if ( (int) $period_data->weekday !== (int) $daykey ) {
																					continue;
																				}
																				echo '<div class="btn-group m-b-sm">';
																				echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown">';
																				echo '<span class="mjschool-period-box" id="' . esc_attr( $period_data->route_id ) . '">';
																				echo esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) );
																				$start_time_data = explode( ':', $period_data->start_time );
																				$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
																				$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
																				$end_time_data   = explode( ':', $period_data->end_time );
																				$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
																				$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
																				if ( $school_type === 'university' ){
																					if ($cust_class_room === 1) {	
																						$class_room = mjschool_get_class_room_name($period_data->room_id);
																						if( ! empty( $class_room ) )
																						{
																							echo '<span class="time"> ( ' . esc_html( $class_room->room_name) . ' ) </span>';
																						}
																					}
																				}
																				echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ) </span>';
																				$create_meeting      = '';
																				$update_meeting      = '';
																				$delete_meeting      = '';
																				$meeting_statrt_link = '';
																				if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
																					$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
																					if ( empty( $meeting_data ) ) {
																						$create_meeting = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none show-popup" href="#" id="' . esc_attr( $period_data->route_id ) . '">' . esc_html__( 'Create Virtual Class', 'mjschool' ) . '</a></li>';
																					} else {
																						$create_meeting = '';
																					}
																					if ( ! empty( $meeting_data ) ) {
																						$update_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url('?dashboard=mjschool_user&page=virtual-classroom&tab=edit_meeting&action=edit&meeting_id=' . $meeting_data->meeting_id ). '">' . esc_html__( 'Edit Virtual Class', 'mjschool' ) . '</a></li>';
																						$delete_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url('?dashboard=mjschool_user&page=virtual-classroom&tab=meeting_list&action=delete&meeting_id=' . $meeting_data->meeting_id ). '" onclick="return confirm(\'' . esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ) . '\' );">' . esc_html__( 'Delete Virtual Class', 'mjschool' ) . '</a></li>';
																						$meeting_statrt_link = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url(  $meeting_data->meeting_start_link ). '" target="_blank">' . esc_html__( 'Virtual Class Start', 'mjschool' ) . '</a></li>';
																					} else {
																						$update_meeting      = '';
																						$delete_meeting      = '';
																						$meeting_statrt_link = '';
																					}
																				}
																				echo '<span>' . esc_html( mjschool_get_class_name( $period_data->class_id ) ) . '</span>';
																				echo '</span></span><span class="caret"></span></button>';
																				if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
																					?>
																					<ul role="menu" class="pt-2 dropdown-menu">
																						<?php
																						if ( $user_access['edit'] === '1' ) {
																							?>
																							<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="<?php echo esc_url('?dashboard=mjschool_user&page=schedule&tab=addroute&action=edit&route_id='. mjschool_encrypt_id( $period_data->route_id )); ?>"><?php echo esc_html__( 'Edit', 'mjschool' ); ?></a></li>
																							<?php
																						}
																						if ( $user_access['delete'] === '1' ) {
																							?>
																							<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="<?php echo esc_url( '?dashboard=mjschool_user&page=schedule&tab=teacher_timetable&action=delete_teacher&route_id=' . mjschool_encrypt_id( $period_data->route_id ) ); ?>" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><?php echo esc_html__( 'Delete', 'mjschool' ); ?></a></li>
																							<?php echo wp_kses_post( $create_meeting ) . '' . wp_kses_post( $update_meeting ) . '' . wp_kses_post( $delete_meeting ) . '' . wp_kses_post( $meeting_statrt_link ); ?>
																							<?php
																						}
																						?>
																					</ul>
																					<?php
																				}
																				echo '</div>';
																			}
																		}
																		?>
																	</td>
																</tr>
																<?php
															}
															?>
														</table>
													</div>
												</div>
											</div>
											<?php
										}
									} else {
										esc_html_e( 'Teacher data not avilable', 'mjschool' );
									}
									?>
								</div>
							</div><!-------- Panel body. ------->
						</div><!-------- Panel white. ------->
						<?php
					}
				} elseif ( $school_obj->role === 'student' ) {
					$class       = $school_obj->class_info;
					$sectionname = '';
					$section     = 0;
					$section     = get_user_meta( get_current_user_id(), 'class_section', true );
					if ( $section != '' ) {
						$sectionname = mjschool_get_section_name( $section );
					} else {
						$section = 0;
					}
					?>
					<div class="accordion-item mt-1 mjschool-class-border-div">
						<h4 class="accordion-header" id="heading<?php echo esc_attr( $i ); ?>">
							<a class="class_section_a_tag" data-bs-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo esc_attr( $i ); ?>">
								<button class="accordion-button class_route_list collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo esc_attr( $i ); ?>" aria-expanded="true" aria-controls="collapse<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Class', 'mjschool' ); ?> : <?php echo esc_html( mjschool_get_class_section_name_wise( $class->class_id, $section ) ); ?> &nbsp;&nbsp;
								</button>
							</a>
						</h4>
						<div id="collapse<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse" aria-labelledby="heading<?php echo esc_attr( $i ); ?>" data-bs-parent="#mjschool-accordion">
							<div class="mjschool-panel-body">
								<div class="table-responsive"> <!-- Added wrapper div. -->
									<table class="table table-bordered" cellspacing="0" cellpadding="0" border="0">
										<?php foreach ( mjschool_day_list() as $daykey => $dayname ) { ?>
											<tr>
												<th><?php echo esc_html( $dayname ); ?></th>
												<td>
													<?php
													$period = $mjschool_obj_route->mjschool_get_period( $class->class_id, $section, $daykey );
													if ( ! empty( $period ) ) {
														foreach ( $period as $period_data ) {
															$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
															if ( ! empty( $meeting_data ) ) {
																$data_toggle = 'data-bs-toggle="dropdown"';
															} else {
																$data_toggle = '';
															}
															echo '<div class="btn-group m-b-sm">';
															$subject_name = mjschool_get_single_subject_name( $period_data->subject_id );
															$teacher_name = '';
															if ( $period_data->multiple_teacher === 'yes' ) {
																$teacher_name = mjschool_get_display_name( $period_data->teacher_id );
																$display      = $subject_name . ' ( ' . $teacher_name . ' )';
															} else {
																$display = $subject_name;
															}
															echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" aria-expanded="false" ' . esc_attr( $data_toggle ) . '><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_html( $display );
															$start_time_data = explode( ':', $period_data->start_time );
															$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
															$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
															$start_am_pm     = $start_time_data[2];
															$end_time_data   = explode( ':', $period_data->end_time );
															$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
															$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
															$end_am_pm       = $end_time_data[2];
															echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' ' . esc_html( $start_am_pm ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ' . esc_html( $end_am_pm ) . ' ) </span>';
															$virtual_classroom_page_name    = 'virtual_classroom';
															$virtual_classroom_access_right = mjschool_get_user_role_wise_filter_access_right_array( $virtual_classroom_page_name );
															if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
																if ( $virtual_classroom_access_right['view'] === '1' ) {
																	if ( ! empty( $meeting_data ) ) {
																		$meeting_join_link = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( $meeting_data->meeting_join_link ) . '" target="_blank">' . esc_html__( 'Join Virtual Class', 'mjschool' ) . '</a></li>';
																	} else {
																		$meeting_join_link = '';
																	}
																}
															}
															echo "<span class='caret'></span></button>";
															echo '<ul role="menu" class="dropdown-menu schedule_menu">' . esc_html( $meeting_join_link ) . '</ul>';
															echo '</div>';
														}
													}
													?>
												</td>
											</tr>
										<?php } ?>
									</table>
								</div> <!-- End of wrapper div. -->
							</div>
						</div>
					</div>
					<?php
				} elseif ( $school_obj->role === 'parent' ) {
					$chil_array = $school_obj->child_list;
					$i          = 0;
					if ( ! empty( $chil_array ) ) {
						foreach ( $chil_array as $child_id ) {
							++$i;
							$sectionname = '';
							$section     = 0;
							$class       = $school_obj->mjschool_get_user_class_id( $child_id );
							$section     = get_user_meta( $child_id, 'class_section', true );
							if ( $section != '' ) {
								$sectionname = mjschool_get_section_name( $section );
							} else {
								$section = 0;
							}
							?>
							<div class="accordion-item mt-1 mjschool-class-border-div">
								<h4 class="accordion-header" id="heading<?php echo esc_attr( $i ); ?>">
									<a class="class_section_a_tag" data-bs-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button class_route_list collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo esc_attr( $i ); ?>" aria-expanded="true" aria-controls="collapse<?php echo esc_attr( $i ); ?>"> <?php esc_html_e( 'Class', 'mjschool' ); ?> : <?php echo esc_html( mjschool_get_class_section_name_wise( $class->class_id, $section ) ); ?> &nbsp;&nbsp;
									</a>
								</h4>
								<div id="collapse<?php echo esc_attr( $i ); ?>" class="panel-collapse collapse <?php if ( $i === 1 ) { echo 'in';} ?>">
									<div class="mjschool-panel-body">
										<table class="table table-bordered" cellspacing="0" cellpadding="0" border="0">
											<?php
											foreach ( mjschool_day_list() as $daykey => $dayname ) {
												?>
												<tr>
													<th><?php echo esc_html( $dayname ); ?></th>
													<td>
														<?php
														$period = $mjschool_obj_route->mjschool_get_period( $class->class_id, $section, $daykey );
														if ( ! empty( $period ) ) {
															foreach ( $period as $period_data ) {
																$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
																if ( ! empty( $meeting_data ) ) {
																	$data_toggle = 'data-bs-toggle="dropdown"';
																} else {
																	$data_toggle = '';
																}
																echo '<div class="btn-group m-b-sm">';
																echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" aria-expanded="false" ' . esc_attr( $data_toggle ) . '><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) );
																$start_time_data = explode( ':', $period_data->start_time );
																$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
																$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
																$start_am_pm     = $start_time_data[2];
																$end_time_data   = explode( ':', $period_data->end_time );
																$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
																$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
																$end_am_pm       = $end_time_data[2];
																echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' ' . esc_html( $start_am_pm ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ' . esc_html( $end_am_pm ) . ' ) </span>';
																$virtual_classroom_page_name    = 'virtual_classroom';
																$virtual_classroom_access_right = mjschool_get_user_role_wise_filter_access_right_array( $virtual_classroom_page_name );
																if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
																	if ( $virtual_classroom_access_right['view'] === '1' ) {
																		if ( ! empty( $meeting_data ) ) {
																			$meeting_join_link = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url($meeting_data->meeting_join_link ). '" target="_blank">' . esc_html__( 'Join Virtual Class', 'mjschool' ) . '</a></li>';
																		} else {
																			$meeting_join_link = '';
																		}
																	}
																}
																if ( $school_type === 'university' ){
																	if ($cust_class_room === 1) {	
																		$class_room = mjschool_get_class_room_name($period_data->room_id);
																		if( ! empty( $class_room ) )
																		{
																			echo '<span class="time"> ( ' . esc_html( $class_room->room_name) . ' ) </span>';
																		}
																	}
																}
																echo "<span class='caret'></span></button>";
																echo '<ul role="menu" class="dropdown-menu schedule_menu"> ' . esc_html( $meeting_join_link ) . ' </ul>';
																echo '</div>';
															}
														}
														?>
													</td>
												</tr>
											<?php } ?>
										</table>
									</div>
								</div>
							</div>
							<?php
						}
					} else {
						esc_html_e( 'Child data not avilable', 'mjschool' );
					}
				}
				?>
			</div>
		</div> <!----------- Panel body. ------------->
	</div><!------------ Tab content. ------------>
</div><!----------- Panel body. ------------->