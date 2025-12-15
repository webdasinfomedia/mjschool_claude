<?php
/**
 * Virtual Classroom Management - Admin Dashboard.
 *
 * Handles the creation, listing, editing, and deletion of Zoom virtual classroom meetings 
 * within the MJSchool plugin. This file integrates WordPress admin UI, AJAX-based DataTables, 
 * and jQuery validation to manage meeting data efficiently. It also includes Zoom API integration 
 * for synchronizing meeting records.
 *
 * Key Features:
 * - Displays all Zoom meetings with DataTables (sortable, searchable, and responsive).
 * - Supports single and bulk deletion of meetings with confirmation prompts.
 * - Enables administrators to edit or update existing meeting information securely.
 * - Integrates jQuery validation, date pickers, and localized strings for multilingual support.
 * - Includes nonce verification for secure form submission and CSRF protection.
 * - Provides user role and access control checks for restricted operations.
 * - Displays success/error notifications for CRUD operations (Add, Update, Delete, etc.).
 * - Automatically formats start and end times, weekdays, and agenda text.
 * - Uses proper WordPress escaping and sanitization functions throughout.
 * - Dynamically loads edit and view pages (Edit Meeting, View Participant List).
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/virtual-classroom
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<?php
require_once MJSCHOOL_PLUGIN_DIR . '/lib/vendor/autoload.php';
$obj_virtual_classroom = new Mjschool_Virtual_Classroom();
$active_tab            = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'meeting_list';
$mjschool_page_name    = sanitize_text_field(wp_unslash($_REQUEST['page']));
$user_access           = mjschool_get_management_access_right_array( $mjschool_page_name );
// Edit meeting in zoom.
if ( isset( $_POST['edit_meeting'] ) ) {
	$nonce = $_POST['_wpnonce'];
	if ( wp_verify_nonce( $nonce, 'edit_meeting_admin_nonce' ) ) {
		$result = $obj_virtual_classroom->mjschool_create_meeting_in_zoom( wp_unslash($_POST) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=meeting_list&message=2' ) );
			exit;
		}
	}
}
// Delete student in zoom.
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	$result = $obj_virtual_classroom->mjschool_delete_meeting_in_zoom( sanitize_text_field(wp_unslash($_REQUEST['meeting_id'])) );
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=meeting_list&message=3' ) );
		exit;
	}
}
/* Delete selected subject. */
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $meeting_id ) {
			$result = $obj_virtual_classroom->mjschool_delete_meeting_in_zoom( $meeting_id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=meeting_list&message=3' ) );
		exit;
	}
}
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="view_meeting_detail_popup"></div>
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<!-- End POP-UP code. -->
<div class="mjschool-page-inner">
	<div class="mjschool-class-list mjschool-main-list-margin-5px">
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
			case '4':
				$message_string = esc_html__( 'Your Access Token Is Updated.', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'Something Wrong.', 'mjschool' );
				break;
			case '6':
				$message_string = esc_html__( 'First Start Your Virtual Class.', 'mjschool' );
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
		<div class="mjschool-panel-white">
			<div class="mjschool-panel-body">
				<?php
				if ( $active_tab === 'meeting_list' ) {
					if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
						 ?>
						<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url("https://www.youtube.com/embed/wJ7D1I8zOao?si=PbzhjGNMS-cVdTFr"); ?>" title="<?php esc_attr_e( 'Zoom Meeting Setup', 'mjschool' ); ?>">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-youtube-icon.png"); ?>" alt="<?php esc_html_e( 'YouTube', 'mjschool' ); ?>">
						</a>
						<?php  
					}
					$meeting_list_data = $obj_virtual_classroom->mjschool_get_all_meeting_data_in_zoom();
					if ( ! empty( $meeting_list_data ) ) {
						?>
						<div class="mjschool-panel-body">
							<form id="mjschool-common-form" name="mjschool-common-form" method="post">
								<div class="table-responsive">
									<table id="index_meeting_list" class="display datatable" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-custom-padding-0"><input type="checkbox" class="mjschool-sub-chk select_all" id="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Created By', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Start To End Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Start To End Time', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Agenda', 'mjschool' ); ?></th>
												<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 0;
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
												$route_data = mjschool_get_route_by_id( $retrieved_data->route_id );
												$stime      = explode( ':', $route_data->start_time );
												$start_hour = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
												$start_min  = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
												$start_time = $start_hour . ':' . $start_min;
												$etime      = explode( ':', $route_data->end_time );
												$end_hour   = str_pad( $etime[0], 2, '0', STR_PAD_LEFT );
												$end_min    = str_pad( $etime[1], 2, '0', STR_PAD_LEFT );
												$end_time = $end_hour . ':' . $end_min;
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
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->meeting_id ); ?>"></td>
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
														<a href="" class="show-popup" meeting_id="<?php echo esc_attr( $retrieved_data->meeting_id ); ?>"> 
															<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">	
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-virtual-class.png")?>" class="mjschool-massage-image">
															</p>
														</a>
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
														if ( ! empty( $retrieved_data->teacher_id ) ) {
															echo esc_html( mjschool_get_teacher( $retrieved_data->teacher_id ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' ); 
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i>
													</td>
													<td><?php echo esc_html( $day ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_display_name( $retrieved_data->created_by ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Created By', 'mjschool' ); ?>"></i></td>
													<td>
														<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?> <?php esc_html_e( 'To', 'mjschool' ); ?> <?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start To End Date', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_time_remove_colon_before_am_pm( $start_time ) ); ?> <?php esc_html_e( 'To', 'mjschool' ); ?> <?php echo esc_html( mjschool_time_remove_colon_before_am_pm( $end_time ) ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start To End Time', 'mjschool' ); ?>"></i>
													</td>
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
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-more.png")?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<li class="mjschool-float-left-width-100px">
																			<a href="" class="mjschool-float-left-width-100px show-popup" meeting_id="<?php echo esc_attr( $retrieved_data->meeting_id ); ?>"><i class="fas fa-eye"></i> <?php esc_html_e( 'View', 'mjschool' ); ?></a> 
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( $retrieved_data->meeting_start_link ); ?>" class="mjschool-float-left-width-100px" target="_blank"><i class="fas fa-video-camera" aria-hidden="true"></i> <?php esc_html_e( 'Start Virtual Class', 'mjschool' ); ?> </a>
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?page=mjschool_virtual_classroom&tab=view_past_participle_list&action=view&meeting_uuid=' . esc_attr( $retrieved_data->uuid ) ); ?>"class="mjschool-float-left-width-100px"><i class="fas fa-eye" aria-hidden="true"></i> <?php esc_html_e( 'View Participant List', 'mjschool' ); ?> </a>
																		</li>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																			<a href="<?php echo esc_url( '?page=mjschool_virtual_classroom&tab=edit_meeting&action=edit&meeting_id=' . esc_attr( $retrieved_data->meeting_id ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"></i> <?php esc_html_e( 'Edit', 'mjschool' ); ?> </a>
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?page=mjschool_virtual_classroom&tab=meeting_list&action=delete&meeting_id=' . esc_attr( $retrieved_data->meeting_id ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
											?>
										</tbody>
									</table>
								</div>
								<div class="mjschool-print-button pull-left">
									<button class="mjschool-btn-sms-color mjschool-button-reload">
										<input type="checkbox" name="id[]" class="select_all mjschool-sub-chk mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>" >
										<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
									</button>
									<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
								</div>
							</form>
						</div>
						<?php
					} elseif ( $mjschool_role === 'administrator' || $user_access['add'] === '1' ) {
						?>
						<div class="mjschool-no-data-list-div pt-2">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_route&tab=addroute' ) ); ?>">
								<img class="col-md-12 mjschool-no-img-width-100px rtl_float_remove" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
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
				if ( $active_tab === 'edit_meeting' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/virtual-classroom/edit-meeting.php';
				} elseif ( $active_tab === 'view_past_participle_list' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/virtual-classroom/view-past-participle-list.php';
				}
				?>
			</div>
		</div>
	</div>
</div>