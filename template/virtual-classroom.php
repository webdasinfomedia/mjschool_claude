<?php
/**
 * Virtual Classroom Management Page.
 *
 * This file handles the listing, creation, and management of virtual
 * class meetings, including integration with external virtual class providers.
 * It also displays the list of participants for a specific meeting.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<?php
$obj_virtual_classroom = new Mjschool_Virtual_Classroom();
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'meeting_list';
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
// EDIT MEETING IN ZOOM.
if ( isset( $_POST['edit_meeting'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'edit_meeting_nonce' ) ) {
		$result = $obj_virtual_classroom->mjschool_create_meeting_in_zoom( wp_unslash($_POST) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=virtual-classroom&tab=meeting_list&message=2') );
			die();
		}
	}
}
// DELETE STUDENT IN ZOOM.
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	$result = $obj_virtual_classroom->mjschool_delete_meeting_in_zoom( sanitize_text_field(wp_unslash($_REQUEST['meeting_id'])) );
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=virtual-classroom&tab=meeting_list&message=3') );
		die();
	}
}
?>
<!-- Nav tabs. -->
<?php
$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
switch ( $message ) {
	case '1':
		$message_string = esc_html__( 'Virtual Class Added Successfully.', 'mjschool' );
		break;
	case '2':
		$message_string = esc_html__( 'Virtual Class Updated Successfully.', 'mjschool' );
		break;
	case '3':
		$message_string = esc_html__( 'Virtual Class Deleted Successfully.', 'mjschool' );
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
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="view_meeting_detail_popup">
			</div>
		</div>
	</div>
</div>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<!-- Tab panes. -->
	<?php
	if ( $active_tab === 'meeting_list' ) {
		$user_id = get_current_user_id();
		if ( $school_obj->role === 'student' ) {
			$class_id   = get_user_meta( get_current_user_id(), 'class_name', true );
			$section_id = get_user_meta( get_current_user_id(), 'class_section', true );
			if ( $section_id ) {
				$meeting_list_data = $obj_virtual_classroom->mjschool_get_meeting_by_class_id_and_section_id_data_in_zoom( $class_id, $section_id );
			} else {
				$meeting_list_data = $obj_virtual_classroom->mjschool_get_meeting_by_class_id_data_in_zoom( $class_id );
			}
		} elseif ( $school_obj->role === 'teacher' ) {
			$retrieve_class_data = mjschool_get_all_class();
			foreach ( $retrieve_class_data as $data ) {
				$meeting_list_data = $obj_virtual_classroom->mjschool_get_meeting_by_class_id_data_in_zoom( $data['class_id'] );
			}
		} elseif ( $school_obj->role === 'parent' ) {
			$chil_array = $school_obj->child_list;
			if ( ! empty( $chil_array ) ) {
				foreach ( $chil_array as $child_id ) {
					$class_id   = get_user_meta( $child_id, 'class_name', true );
					$section_id = get_user_meta( $child_id, 'class_section', true );
					if ( $section_id ) {
						$meeting_list_data = $obj_virtual_classroom->mjschool_get_meeting_by_class_id_and_section_id_data_in_zoom( $class_id, $section_id );
					} else {
						$meeting_list_data = $obj_virtual_classroom->mjschool_get_meeting_by_class_id_data_in_zoom( $class_id );
					}
				}
			}
		}
		// ------- MEETING DATA FOR SUPPORT STAFF. ---------//
		else {
			$meeting_list_data = $obj_virtual_classroom->mjschool_get_all_meeting_data_in_zoom();
		}
		if ( ! empty( $meeting_list_data ) ) {
			?>
			<div class="mjschool-panel-body">
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<div class="table-responsive">
						<table id="meeting_list" class="display datatable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Section Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Start Date & Time', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'End Date & Time', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Agenda', 'mjschool' ); ?></th>
									<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ( $school_obj->role === 'parent' ) {
									$chil_array = $school_obj->child_list;
									if ( ! empty( $chil_array ) ) {
										foreach ( $chil_array as $child_id ) {
											$class_id   = get_user_meta( $child_id, 'class_name', true );
											$section_id = get_user_meta( $child_id, 'class_section', true );
											if ( $section_id ) {
												$meeting_list_data = $obj_virtual_classroom->mjschool_get_meeting_by_class_id_and_section_id_data_in_zoom( $class_id, $section_id );
											} else {
												$meeting_list_data = $obj_virtual_classroom->mjschool_get_meeting_by_class_id_data_in_zoom( $class_id );
											}
											$i = 0;
											foreach ( $meeting_list_data as $retrieved_data ) {
												if ( $retrieved_data->weekday_id === 1 ) {
													$day = esc_html__( 'Monday', 'mjschool' );
												} elseif ( $retrieved_data->weekday_id === 2 ) {
													$day = esc_html__( 'Tuesday', 'mjschool' );
												} elseif ( $retrieved_data->weekday_id === 3 ) {
													$day = esc_html__( 'Wednesday', 'mjschool' );
												} elseif ( $retrieved_data->weekday_id === 4 ) {
													$day = esc_html__( 'Thursday', 'mjschool' );
												} elseif ( $retrieved_data->weekday_id === 5 ) {
													$day = esc_html__( 'Friday', 'mjschool' );
												} elseif ( $retrieved_data->weekday_id === 6 ) {
													$day = esc_html__( 'Saturday', 'mjschool' );
												} elseif ( $retrieved_data->weekday_id === 7 ) {
													$day = esc_html__( 'Sunday', 'mjschool' );
												}
												$route_data  = mjschool_get_route_by_id( $retrieved_data->route_id );
												$stime       = explode( ':', $route_data->start_time );
												$start_hour  = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
												$start_min   = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
												$start_am_pm = $stime[2];
												$start_time  = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
												$etime       = explode( ':', $route_data->end_time );
												$end_hour    = str_pad( $etime[0], 2, '0', STR_PAD_LEFT );
												$end_min     = str_pad( $etime[1], 2, '0', STR_PAD_LEFT );
												$end_am_pm   = $etime[2];
												$end_time    = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
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
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
														<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
															
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-virtual-class.png"); ?>" class="mjschool-massage-image">
															
														</p>
													</td>
													<td>
														<?php
														$subid = $retrieved_data->subject_id;
														echo esc_html( mjschool_get_single_subject_name( $subid ) );
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Name', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														$cid = $retrieved_data->class_id;
														echo esc_html( $clasname = mjschool_get_class_name( $cid ) );
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( $retrieved_data->section_id != 0 ) {
															echo esc_html( mjschool_get_section_name( $retrieved_data->section_id ) );
														} else {
															esc_html_e( 'No Section', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Section Name', 'mjschool' ); ?>"></i>
													</td>
													<td><?php echo esc_html( mjschool_get_teacher( $retrieved_data->teacher_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( $day ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?> <?php esc_html_e( 'And', 'mjschool' ); ?> <?php echo esc_html( $start_time ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Date & Time', 'mjschool' ); ?>"></i> </td>
													<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <?php esc_html_e( 'And', 'mjschool' ); ?> <?php echo esc_html( $end_time ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'End Date & Time', 'mjschool' ); ?>"></i> </td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->agenda ) ) {
															$strlength = strlen( $retrieved_data->agenda );
															if ( $strlength > 50 ) {
																echo esc_html( substr( $retrieved_data->agenda, 0, 30 ) ) . '...';
															} else {
																echo esc_html( $retrieved_data->agenda );
															}
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Agenda', 'mjschool' ); ?>"></i>
													</td>
													<td class="action">
														<div class="mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li >
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																		
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( $retrieved_data->meeting_join_link ); ?>" class="mjschool-float-left-width-100px" target="_blank"><i class="fas fa-video-camera" aria-hidden="true"></i> <?php esc_html_e( 'Join Virtual Class', 'mjschool' ); ?> </a>
																		</li>
																	</ul>
																</li>
															</ul>
														</div>
													</td>
												</tr>
												<?php
												++$i;
											}
										}
									}
								} elseif ( $school_obj->role === 'teacher' ) {
									$retrieve_class_data = mjschool_get_all_class();
									foreach ( $retrieve_class_data as $data ) {
										$meeting_list_data = $obj_virtual_classroom->mjschool_get_meeting_by_class_id_data_in_zoom( $data['class_id'] );
										$i                 = 0;
										foreach ( $meeting_list_data as $retrieved_data ) {
											if ( $retrieved_data->weekday_id === 1 ) {
												$day = esc_attr__( 'Monday', 'mjschool' );
											} elseif ( $retrieved_data->weekday_id === 2 ) {
												$day = esc_attr__( 'Tuesday', 'mjschool' );
											} elseif ( $retrieved_data->weekday_id === 3 ) {
												$day = esc_attr__( 'Wednesday', 'mjschool' );
											} elseif ( $retrieved_data->weekday_id === 4 ) {
												$day = esc_attr__( 'Thursday', 'mjschool' );
											} elseif ( $retrieved_data->weekday_id === 5 ) {
												$day = esc_attr__( 'Friday', 'mjschool' );
											} elseif ( $retrieved_data->weekday_id === 6 ) {
												$day = esc_attr__( 'Saturday', 'mjschool' );
											} elseif ( $retrieved_data->weekday_id === 7 ) {
												$day = esc_attr__( 'Sunday', 'mjschool' );
											}
											$route_data  = mjschool_get_route_by_id( $retrieved_data->route_id );
											$stime       = explode( ':', $route_data->start_time );
											$start_hour  = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
											$start_min   = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
											$start_am_pm = $stime[2];
											$start_time  = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
											$etime       = explode( ':', $route_data->end_time );
											$end_hour    = str_pad( $etime[0], 2, '0', STR_PAD_LEFT );
											$end_min     = str_pad( $etime[1], 2, '0', STR_PAD_LEFT );
											$end_am_pm   = $etime[2];
											$end_time    = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
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
												<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
													<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
														
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-virtual-class.png"); ?>" class="mjschool-massage-image">
														
													</p>
												</td>
												<td>
													<?php
													$subid = $retrieved_data->subject_id;
													echo esc_html( mjschool_get_single_subject_name( $subid ) );
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Name', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php
													$cid = $retrieved_data->class_id;
													echo esc_attr( $clasname = mjschool_get_class_name( $cid ) );
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php
													if ( $retrieved_data->section_id != 0 ) {
														echo esc_html( mjschool_get_section_name( $retrieved_data->section_id ) );
													} else {
														esc_html_e( 'No Section', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Section Name', 'mjschool' ); ?>"></i>
												</td>
												<td><?php echo esc_html( mjschool_get_teacher( $retrieved_data->teacher_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i></td>
												<td><?php echo esc_html( $day ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i></td>
												<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?> <?php esc_html_e( 'And', 'mjschool' ); ?> <?php echo esc_html( $start_time ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Date & Time', 'mjschool' ); ?>"></i> </td>
												<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <?php esc_html_e( 'And', 'mjschool' ); ?> <?php echo esc_html( $end_time ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'End Date & Time', 'mjschool' ); ?>"></i> </td>
												<td>
													<?php
													if ( ! empty( $retrieved_data->agenda ) ) {
														$strlength = strlen( $retrieved_data->agenda );
														if ( $strlength > 50 ) {
															echo esc_html( substr( $retrieved_data->agenda, 0, 30 ) ) . '...';
														} else {
															echo esc_html( $retrieved_data->agenda );
														}
													} else {
														esc_html_e( 'N/A', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Agenda', 'mjschool' ); ?>"></i>
												</td>
												<td class="action">
													<div class="mjschool-user-dropdown">
														<ul  class="mjschool_ul_style">
															<li >
																<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																	
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	
																</a>
																<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																	<?php
																	if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="#" class="mjschool-float-left-width-100px show-popup" meeting_id="<?php echo esc_attr( $retrieved_data->meeting_id ); ?>"><i class="fas fa-eye"></i> <?php esc_html_e( 'View', 'mjschool' ); ?></a>
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( $retrieved_data->meeting_start_link ); ?>" class="mjschool-float-left-width-100px" target="_blank"><i class="fas fa-video-camera" aria-hidden="true"></i> <?php esc_html_e( 'Start Virtual Class', 'mjschool' ); ?> </a>
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=virtual-classroom&tab=view_past_participle_list&action=view&meeting_uid=' . $retrieved_data->uid ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye" aria-hidden="true"></i> <?php esc_html_e( 'View Participant List', 'mjschool' ); ?> </a>
																		</li>
																		<?php
																	} elseif ( $school_obj->role === 'student' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( $retrieved_data->meeting_join_link ); ?>" class="mjschool-float-left-width-100px" target="_blank"><i class="fas fa-video-camera" aria-hidden="true"></i> <?php esc_html_e( 'Join Meeting', 'mjschool' ); ?> </a>
																		</li>
																		<?php
																	}
																	if ( $user_access['edit'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=virtual-classroom&tab=edit_meeting&action=edit&meeting_id=' . $retrieved_data->meeting_id ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"></i> <?php esc_html_e( 'Edit', 'mjschool' ); ?> </a>
																		</li>
																		<?php
																	}
																	if ( $user_access['delete'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=virtual-classroom&tab=meeting_list&action=delete&meeting_id=' . $retrieved_data->meeting_id ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
									}
								} else {
									$i = 0;
									foreach ( $meeting_list_data as $retrieved_data ) {
										if ( $retrieved_data->weekday_id === 1 ) {
											$day = esc_html__( 'Monday', 'mjschool' );
										} elseif ( $retrieved_data->weekday_id === 2 ) {
											$day = esc_html__( 'Tuesday', 'mjschool' );
										} elseif ( $retrieved_data->weekday_id === 3 ) {
											$day = esc_html__( 'Wednesday', 'mjschool' );
										} elseif ( $retrieved_data->weekday_id === 4 ) {
											$day = esc_html__( 'Thursday', 'mjschool' );
										} elseif ( $retrieved_data->weekday_id === 5 ) {
											$day = esc_html__( 'Friday', 'mjschool' );
										} elseif ( $retrieved_data->weekday_id === 6 ) {
											$day = esc_html__( 'Saturday', 'mjschool' );
										} elseif ( $retrieved_data->weekday_id === 7 ) {
											$day = esc_html__( 'Sunday', 'mjschool' );
										}
										$route_data  = mjschool_get_route_by_id( $retrieved_data->route_id );
										$stime       = explode( ':', $route_data->start_time );
										$start_hour  = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
										$start_min   = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
										$start_am_pm = $stime[2];
										$start_time  = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
										$etime       = explode( ':', $route_data->end_time );
										$end_hour    = str_pad( $etime[0], 2, '0', STR_PAD_LEFT );
										$end_min     = str_pad( $etime[1], 2, '0', STR_PAD_LEFT );
										$end_am_pm   = $etime[2];
										$end_time    = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
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
											<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
												<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-virtual-class.png"); ?>" class="mjschool-massage-image">
												</p>
											</td>
											<td>
												<?php
												$subid = $retrieved_data->subject_id;
												echo esc_attr( mjschool_get_single_subject_name( $subid ) );
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Name', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php
												$cid = $retrieved_data->class_id;
												echo esc_html( $clasname = mjschool_get_class_name( $cid ) );
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php
												if ( $retrieved_data->section_id != 0 ) {
													echo esc_html( mjschool_get_section_name( $retrieved_data->section_id ) );
												} else {
													esc_html_e( 'No Section', 'mjschool' );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Section Name', 'mjschool' ); ?>"></i>
											</td>
											<td><?php echo esc_html( mjschool_get_teacher( $retrieved_data->teacher_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i></td>
											<td><?php echo esc_html( $day ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i></td>
											<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?> <?php esc_html_e( 'And', 'mjschool' ); ?> <?php echo esc_html( $start_time ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Date & Time', 'mjschool' ); ?>"></i> </td>
											<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <?php esc_html_e( 'And', 'mjschool' ); ?> <?php echo esc_html( $end_time ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'End Date & Time', 'mjschool' ); ?>"></i> </td>
											<td>
												<?php
												if ( ! empty( $retrieved_data->agenda ) ) {
													$strlength = strlen( $retrieved_data->agenda );
													if ( $strlength > 50 ) {
														echo esc_html( substr( $retrieved_data->agenda, 0, 30 ) ) . '...';
													} else {
														echo esc_html( $retrieved_data->agenda );
													}
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Agenda', 'mjschool' ); ?>"></i>
											</td>
											<td class="action">
												<div class="mjschool-user-dropdown">
													<ul  class="mjschool_ul_style">
														<li >
															<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
															</a>
															<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																<?php
																if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="" class="mjschool-float-left-width-100px show-popup" meeting_id="<?php echo esc_attr( $retrieved_data->meeting_id ); ?>"><i class="fas fa-eye"></i> <?php esc_html_e( 'View', 'mjschool' ); ?></a>
																	</li>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( $retrieved_data->meeting_start_link ); ?>" class="mjschool-float-left-width-100px" target="_blank"><i class="fas fa-video-camera" aria-hidden="true"></i> <?php esc_html_e( 'Start Virtual Class', 'mjschool' ); ?> </a>
																	</li>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=virtual-classroom&tab=view_past_participle_list&action=view&meeting_uid=' . $retrieved_data->uid ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye" aria-hidden="true"></i> <?php esc_html_e( 'View Participant List', 'mjschool' ); ?> </a>
																	</li>
																	<?php
																} elseif ( $school_obj->role === 'student' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( $retrieved_data->meeting_join_link ); ?>" class="mjschool-float-left-width-100px" target="_blank"><i class="fas fa-video-camera" aria-hidden="true"></i> <?php esc_html_e( 'Join Meeting', 'mjschool' ); ?> </a>
																	</li>
																	<?php
																}
																if ( $user_access['edit'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=virtual-classroom&tab=edit_meeting&action=edit&meeting_id=' . $retrieved_data->meeting_id ); ?>"  class="mjschool-float-left-width-100px"><i class="fas fa-edit"></i> <?php esc_html_e( 'Edit', 'mjschool' ); ?> </a>
																	</li>
																	<?php
																}
																if ( $user_access['delete'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=virtual-classroom&tab=meeting_list&action=delete&meeting_id=' . $retrieved_data->meeting_id ); ?>"  class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
								}
								?>
							</tbody>
						</table>
					</div>
				</form>
			</div>
			<?php
		} else {
			 
			if ($user_access['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
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
	} elseif ( $active_tab === 'edit_meeting' ) {
		$meeting_data    = $obj_virtual_classroom->mjschool_get_single_meeting_data_in_zoom( sanitize_text_field(wp_unslash($_REQUEST['meeting_id'])) );
		$route_data      = mjschool_get_route_by_id( $meeting_data->route_id );
		$start_time_data = explode( ':', $route_data->start_time );
		$end_time_data   = explode( ':', $route_data->end_time );
		if ( $start_time_data[1] === 0 || $end_time_data[1] === 0 ) {
			$start_time_minit = '00';
			$end_time_minit   = '00';
		} else {
			$start_time_minit = $start_time_data[1];
			$end_time_minit   = $end_time_data[1];
		}
		$start_time = date( 'H:i A', strtotime( "$start_time_data[0]:$start_time_minit $start_time_data[2]" ) );
		$end_time   = date( 'H:i A', strtotime( "$end_time_data[0]:$end_time_minit $end_time_data[2]" ) );
		?>
		<div class="mjschool-panel-body">
			<form name="route_form" action="" method="post" class="mjschool-form-horizontal" id="meeting_form">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="meeting_id" value="<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['meeting_id'])) ); ?>">
				<input type="hidden" name="route_id" value="<?php echo esc_attr( $meeting_data->route_id ); ?>">
				<input type="hidden" name="class_id" value="<?php echo esc_attr( $route_data->class_id ); ?>">
				<input type="hidden" name="subject_id" value="<?php echo esc_attr( $route_data->subject_id ); ?>">
				<input type="hidden" name="class_section_id" value="<?php echo esc_attr( $route_data->section_name ); ?>">
				<input type="hidden" name="duration" value="<?php echo esc_attr( $meeting_data->duration ); ?>">
				<input type="hidden" name="weekday" value="<?php echo esc_attr( $route_data->weekday ); ?>">
				<input type="hidden" name="start_time" value="<?php echo esc_attr( $start_time ); ?>">
				<input type="hidden" name="end_time" value="<?php echo esc_attr( $end_time ); ?>">
				<input type="hidden" name="teacher_id" value="<?php echo esc_attr( $route_data->teacher_id ); ?>">
				<input type="hidden" name="zoom_meeting_id" value="<?php echo esc_attr( $meeting_data->zoom_meeting_id ); ?>">
				<input type="hidden" name="uuid" value="<?php echo esc_attr( $meeting_data->uuid ); ?>">
				<input type="hidden" name="meeting_join_link" value="<?php echo esc_attr( $meeting_data->meeting_join_link ); ?>">
				<input type="hidden" name="meeting_start_link" value="<?php echo esc_attr( $meeting_data->meeting_start_link ); ?>">
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Virtual Classroom Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="class_name" class="form-control" maxlength="50" type="text" value="<?php echo esc_attr( mjschool_get_class_name( $route_data->class_id ) ); ?>" name="class_name" disabled>
									<label for="userinput1"><?php esc_html_e( 'Class Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="class_section" class="form-control" maxlength="50" type="text" value="<?php echo esc_attr( mjschool_get_section_name( $route_data->section_id ) ); ?>" name="class_section" disabled>
									<label for="userinput1"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="subject" class="form-control" type="text" value="<?php echo esc_attr( mjschool_get_single_subject_name( $route_data->subject_id ) ); ?>" name="class_section" disabled>
									<label for="userinput1"><?php esc_html_e( 'Subject', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="start_time" class="form-control" type="text" value="<?php echo esc_attr( $start_time ); ?>" name="start_time" disabled>
									<label for="userinput1"><?php esc_html_e( 'Start Time', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="end_time" class="form-control" type="text" value="<?php echo esc_attr( $end_time ); ?>" name="end_time" disabled>
									<label for="userinput1"><?php esc_html_e( 'End Time', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="start_date" class="form-control validate[required] text-input" type="text" placeholder="<?php esc_html_e( 'Enter Start Date', 'mjschool' ); ?>" name="start_date" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( $meeting_data->start_date ) ) ); ?>" readonly>
									<label for="userinput1"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="end_date" class="form-control validate[required] text-input" type="text" placeholder="<?php esc_html_e( 'Enter Exam Date', 'mjschool' ); ?>" name="end_date" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( $meeting_data->end_date ) ) ); ?>" readonly>
									<label for="userinput1"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea name="agenda" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="250"><?php echo esc_textarea( $meeting_data->agenda ); ?></textarea>
										<span class="mjschool-txt-title-label"></span>
										<label class="text-area address"><?php esc_html_e( 'Topic', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="password" class="form-control validate[minSize[8],maxSize[12]]" type="password" value="<?php echo esc_attr( $meeting_data->password ); ?>" name="password">
									<label for="userinput1"><?php esc_html_e( 'Password', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'edit_meeting_nonce' ); ?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6 mjschool-margin-top-10px_button">
							<input type="submit" value="<?php esc_attr_e( 'Save Meeting', 'mjschool' ); ?>" name="edit_meeting" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
				<div class="offset-sm-2 col-sm-8">
				</div>
			</form>
		</div>
		<?php
	} elseif ( $active_tab === 'view_past_participle_list' ) {
		$past_participle_list = $obj_virtual_classroom->mjschool_view_past_participle_list_in_zoom( sanitize_text_field(wp_unslash($_REQUEST['meeting_uuid'])) );
		if ( ! empty( $past_participle_list ) ) {
			?>
			<div class="mjschool-panel-body">
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<div class="table-responsive">
						<table id="past_participle_list" class="display datatable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $mjschool_role_name === 'supportstaff' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" id="select_all"></th>
										<?php
									}
									?>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?> </th>
									<th><?php esc_html_e( 'Payment Title', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Amount', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
									<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 0;
								foreach ( $past_participle_list->participants as $retrieved_data ) {
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
										<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
											<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-virtual-class.png"); ?>" class="mjschool-massage-image">
											</p>
										</td>
										<td><?php echo esc_html( $retrieved_data->name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Name', 'mjschool' ); ?>"></i></td>
										<td><?php echo esc_html( $retrieved_data->user_email ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Email', 'mjschool' ); ?>"></i></td>
									</tr>
									<?php
									++$i;
								}
								?>
							</tbody>
						</table>
					</div>
				</form>
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