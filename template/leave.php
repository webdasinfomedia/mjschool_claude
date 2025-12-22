<?php
/**
 * Leave Management View/Controller.
 *
 * This file manages the view and form processing for Leave applications (for students,
 * teachers, or other staff members, depending on the role). It handles the submission
 * of new leave requests and the listing/management of existing requests.
 *
 * Key features include:
 * - **Access Control:** Enforces permissions based on the current user's role ($user_access).
 * - **View Switching:** Uses the 'tab' GET parameter to switch between 'leave_list' and 'add_leave' views.
 * - **Form Processing:** Handles the submission (insert/update) of leave applications.
 * - **SMS Integration:** Includes fields and logic for enabling SMS notifications to parents or users regarding leave status.
 * - **Custom Fields:** Integrates custom fields managed by `Mjschool_Custome_Field` for the 'leave' module.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$mjschool_obj_leave = new Mjschool_Leave();
$mjschool_role               = mjschool_get_user_role( get_current_user_id() );
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'leave_list';
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_key( wp_unslash( $_REQUEST['page'] ) ) === $user_access['page_link'] && ( sanitize_key( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) ) {
			if ( isset($user_access['edit']) && $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_key( wp_unslash( $_REQUEST['page'] ) ) === $user_access['page_link'] && ( sanitize_key( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) ) {
			if ( isset($user_access['delete']) && $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_key( wp_unslash( $_REQUEST['page'] ) ) === $user_access['page_link'] && ( sanitize_key( wp_unslash( $_REQUEST['action'] ) ) === 'insert' ) ) {
			if ( isset($user_access['add']) && $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'leave';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
if ( isset( $_POST['save_leave'] ) ) {

	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );

	if ( wp_verify_nonce( $nonce, 'save_leave_nonce' ) ) {

		if ( isset( $_REQUEST['action'] ) && sanitize_key( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {

			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'edit_action' ) ) {

				$leave_id = sanitize_text_field( wp_unslash( $_REQUEST['leave_id'] ) );

				$result                    = $mjschool_obj_leave->mjschool_add_leave( wp_unslash($_POST) );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'leave';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $leave_id );
				if ( $result ) {
					wp_safe_redirect( home_url('?dashboard=mjschool_user&page=leave&tab=leave_list&message=2' ));
					exit;
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result                    = $mjschool_obj_leave->mjschool_add_leave( wp_unslash($_POST) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'leave';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( home_url('?dashboard=mjschool_user&page=leave&tab=leave_list&message=1') );
				exit;
			}
		}
	}
}
if ( isset( $_POST['approve_comment'] ) && sanitize_text_field( wp_unslash( $_POST['approve_comment'] ) ) === 'Submit' ) {

	$result = $mjschool_obj_leave->mjschool_approve_leave( wp_unslash($_POST) );
	if ( $result ) {
		wp_safe_redirect( home_url('?dashboard=mjschool_user&page=leave&tab=leave_list&message=4') );
		exit;
	}
}

if ( isset( $_POST['reject_leave'] ) && sanitize_text_field( wp_unslash( $_POST['reject_leave'] ) ) === 'Submit' ) {

	$result = $mjschool_obj_leave->mjschool_reject_leave( wp_unslash($_POST) );
	if ( $result ) {
		wp_safe_redirect( home_url('?dashboard=mjschool_user&page=leave&tab=leave_list&message=5') );
		exit;
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_key( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {

	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'delete_action' ) ) {

		$result = $mjschool_obj_leave->mjschool_delete_leave( intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['leave_id'] ) ) ) ) );

		if ( $result ) {
			wp_safe_redirect( home_url('?dashboard=mjschool_user&page=leave&tab=leave_list&message=3') );
			exit;
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	// Verify nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_leave' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = $mjschool_obj_leave->mjschool_delete_leave( intval( $id ) );
		}
		wp_safe_redirect( home_url('?dashboard=mjschool_user&page=leave&tab=leave_list&message=3') );
		exit;
	}
}
if ( isset( $_REQUEST['message'] ) ) {
	$message = intval( wp_unslash( $_REQUEST['message'] ) );
	if ( $message === 1 ) { ?>
        
        <div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
            <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
            <p><?php esc_html_e( 'Leave inserted successfully', 'mjschool' ); ?></p>
        </div>
    	<?php
    } elseif ($message === 2 ) { ?>
        <div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
            <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
            <p><?php esc_html_e( "Leave updated successfully.", 'mjschool' ); ?></p>
        </div><?php
	} elseif ($message === 3) { ?>
        <div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
            <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
            <p><?php esc_html_e( 'Leave deleted successfully', 'mjschool' ); ?></p>
        </div><?php
	} elseif ($message === 4) { ?>
        <div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
            <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
            <p><?php esc_html_e( 'Leave Approved successfully', 'mjschool' ); ?></p>
        </div><?php
	} elseif ($message === 5) { ?>
        <div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
            <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
            <p><?php esc_html_e( 'Leave Rejected Successfully', 'mjschool' ); ?></p>
        </div><?php
	} elseif ($message === 6) { ?>
        <div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
            <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
            <p><?php esc_html_e( 'Oops, Something went wrong.', 'mjschool' ); ?></p>
            
		</div>
		<?php
	}
}
?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<div class="mjschool-page-inner"><!--------- Page inner. ------->
	<div class="mjschool-main-list-margin-5px">
		<div class="mjschool-panel-white"><!--------- Panel white. ------->
			<div class="mjschool-panel-body"> <!--------- Panel body. ------->
				<?php
				if ( $active_tab === 'leave_list' ) {
					$user_id = get_current_user_id();
					// ------- Leave data for student. ---------//
					if ( $school_obj->role === 'student' ) {
						$own_data = $user_access['own_data'];
						if ( $own_data === '1' ) {
							$leave_data = $mjschool_obj_leave->mjschool_get_single_user_leaves( $user_id );
						} else {
							$leave_data = mjschool_get_all_data( 'mjschool_leave' );
						}
					}
					// ------- Leave data for teacher. ---------//
					elseif ( $school_obj->role === 'teacher' ) {
						$own_data = $user_access['own_data'];
						if ( $own_data === '1' ) {
							$leave_data = mjschool_get_all_leave_created_by( $user_id );
						} else {
							$leave_data = mjschool_get_all_data( 'mjschool_leave' );
						}
					}
					// ------- Leave data for parent. ---------//
					elseif ( $school_obj->role === 'parent' ) {
						$child_id         = get_user_meta( $user_id, 'child', true );
						$leave_data_array = array();
						if ( ! empty( $child_id ) ) {
							foreach ( $child_id as $student_id ) {
								$leave_data_array[] = mjschool_get_all_leave_parent_by_child_list( $student_id );
							}
						}
						$mergedArray = array_merge( ...$leave_data_array );
						$leave_data  = array_unique( $mergedArray, SORT_REGULAR );
					} else {
						// ------- Leave data for supportstaff. ---------//
						$own_data = $user_access['own_data'];
						if ( $own_data === '1' ) {
							$leave_data = mjschool_get_all_leave_created_by( $user_id );
						} else {
							$leave_data = mjschool_get_all_data( 'mjschool_leave' );
						}
					}
					?>
					<div class="mjschool-panel-body">
						<?php
						if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
							?>
							<form method="post">
								<div class="form-body mjschool-user-form mt-3">
									<div class="row">
										<div class="col-md-3 input">
											<select class="form-control Student_leave_drop mjschool_heights_47px" id="Student_leave" name="Student_id" >
												<option value="all_student"><?php esc_html_e( 'Select All Student', 'mjschool' ); ?></option>
												<?php
												$emp_id = 0;
												if ( $school_obj->role === 'teacher' ) {
													$user_id     = get_current_user_id();
													$class_id    = get_user_meta( $user_id, 'class_name', true );
													$studentdata = $school_obj->mjschool_get_teacher_student_list( $class_id );
												} else {
													$studentdata = mjschool_get_all_student_list( 'student' );
												}
												foreach ( $studentdata as $student ) {
													if ( isset( $_POST['Student_id'] ) ) {
														$emp_id = sanitize_text_field(wp_unslash($_POST['Student_id']));
													} else {
														$uid    = $student->ID;
														$emp_id = get_user_meta( $uid, 'student', true );
													}
													?>
													<option value="<?php print esc_attr( $student->ID ); ?>" <?php selected( $student->ID, $emp_id ); ?>><?php echo esc_html( mjschool_student_display_name_with_roll( $student->ID ) ); ?></option>
													<?php
												}
												?>
											</select>
											<span class="mjschool-multiselect-label">
												<label class="ml-1 mjschool-custom-top-label top" for="Student_leave"><?php esc_html_e( 'Select Student', 'mjschool' ); ?></label>
											</span>
										</div>
										<div class="col-md-3 input">
											<label for="lave_status" class="ml-1 mjschool-custom-top-label top"><?php esc_html_e( 'Select Status', 'mjschool' ); ?></label>
											<select class="form-control mjschool_heights_47px" id="lave_status" name="status" >
												<?php
												$select_status = isset( $_REQUEST['status'] ) ? sanitize_text_field(wp_unslash($_REQUEST['status'])) : '';
												?>
												<option value="all_status" <?php echo selected( $select_status, 'all_status' ); ?>><?php esc_html_e( 'Select All Status', 'mjschool' ); ?></option>
												<option value="Not Approved" <?php echo selected( $select_status, 'Not Approved' ); ?>><?php esc_html_e( 'Not Approved', 'mjschool' ); ?></option>
												<option value="Approved" <?php echo selected( $select_status, 'Approved' ); ?>><?php esc_html_e( 'Approved', 'mjschool' ); ?></option>
												<option value="Rejected" <?php echo selected( $select_status, 'Rejected' ); ?>><?php esc_html_e( 'Rejected', 'mjschool' ); ?></option>
											</select>
										</div>
										<div class="col-md-3 mb-3 input">
											<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select class="mjschool-line-height-30px form-control date_type validate[required]" id="date_type" name="date_type" autocomplete="off">
												<?php
												$date_type = isset( $_REQUEST['date_type'] ) ? sanitize_text_field(wp_unslash($_REQUEST['date_type'])) : 'this_month';
												?>
												<option value="today" <?php echo selected( $date_type, 'today' ); ?>><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
												<option value="this_week" <?php echo selected( $date_type, 'this_week' ); ?>><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
												<option value="last_week" <?php echo selected( $date_type, 'last_week' ); ?>><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
												<option value="this_month" <?php echo selected( $date_type, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
												<option value="last_month" <?php echo selected( $date_type, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
												<option value="last_3_month" <?php echo selected( $date_type, 'last_3_month' ); ?>><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
												<option value="last_6_month" <?php echo selected( $date_type, 'last_6_month' ); ?>><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
												<option value="last_12_month" <?php echo selected( $date_type, 'last_12_month' ); ?>><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
												<option value="this_year" <?php echo selected( $date_type, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
												<option value="last_year" <?php echo selected( $date_type, 'last_year' ); ?>><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
												<option value="period" <?php echo selected( $date_type, 'period' ); ?>><?php esc_html_e( 'Period', 'mjschool' ); ?></option>
											</select>
										</div>
										<div id="date_type_div" class="col-md-6 <?php echo ( $date_type === 'period' ) ? '' : 'date_type_div_none'; ?>">
											<?php
											if ( $date_type === 'period' ) {
												?>
												<div class="row">
													<div class="col-md-6 mb-2">
														<div class="form-group input">
															<div class="col-md-12 form-control">
																<input type="text" id="report_sdate" class="form-control" name="start_date" value="<?php echo isset( $_POST['start_date'] ) ? esc_attr( sanitize_text_field(wp_unslash($_POST['start_date'])) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
																<label for="report_sdate" class="active"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
															</div>
														</div>
													</div>
													<div class="col-md-6 mb-2">
														<div class="form-group input">
															<div class="col-md-12 form-control">
																<input type="text" id="report_edate" class="form-control" name="end_date" value="<?php echo isset( $_POST['end_date'] ) ? esc_attr( sanitize_text_field(wp_unslash($_POST['end_date'])) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
																<label for="report_edate" class="active"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
															</div>
														</div>
													</div>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col-md-2">
											<input type="submit" name="view_student" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
										</div>
									</div>
								</div>
							</form>
							<?php
							if ( isset( $_REQUEST['view_student'] ) ) {
								$date_type = sanitize_text_field(wp_unslash($_POST['date_type']));
								if ( isset( $_POST['start_date'] ) ) {
									$start_date = sanitize_text_field(wp_unslash($_POST['start_date']));
								}
								if ( isset( $_POST['end_date'] ) ) {
									$end_date = sanitize_text_field(wp_unslash($_POST['end_date']));
								}
								$Student_id = sanitize_text_field(wp_unslash($_POST['Student_id']));
								$status     = sanitize_text_field(wp_unslash($_POST['status']));
								$leave_data = mjschool_get_leave_data_filter_wise( $Student_id, $status, $date_type, $start_date, $end_date );
							} else {
								$Student_id = 'all_student';
								$status     = 'all_status';
								$date_type  = 'this_month';
								$leave_data = mjschool_get_leave_data_filter_wise( $Student_id, $status, $date_type, $start_date, $end_date );
							}
						}
						if ( ! empty( $leave_data ) ) {
							?>
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="frontend_leave_list" class="display mjschool-admin-transport-datatable" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Leave Type', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Leave Duration', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Start Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'End Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Reason', 'mjschool' ); ?></th>
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
												if ( ( isset( $user_access['edit'] ) && $user_access['edit'] === '1' ) || ( isset( $user_access['delete'] ) && $user_access['delete'] === '1' ) ) {
													?>
													<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
													<?php
												}
												?>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 0;
											foreach ( $leave_data as $retrieved_data ) {
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
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
                                                        
                                                        <p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
                                                            <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-leave.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
                                                        </p>
                                                        
													</td>
													<td>
														<?php
														$sname = mjschool_student_display_name_with_roll( $retrieved_data->student_id );
														if ( $sname ) {
															echo esc_html( $sname );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
													</td>
													<td class="name">
														<?php
														$class_id   = get_user_meta( $retrieved_data->student_id, 'class_name', true );
														$section_id = get_user_meta( $retrieved_data->student_id, 'class_section', true );
														$classname  = mjschool_get_class_section_name_wise( $class_id, $section_id );
														if ( ! empty( $classname ) ) {
															echo esc_html( $classname );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class & Section', 'mjschool' ); ?>"></i>
													</td>
													<td><?php echo esc_html( get_the_title( $retrieved_data->leave_type ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Type', 'mjschool' ); ?>"></i></td>
													<td>
													<?php
													$duration = mjschool_leave_duration_label( $retrieved_data->leave_duration );
														echo esc_html( $duration );
													?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Duration', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Start Date', 'mjschool' ); ?>"></i></td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->end_date ) ) {
															echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave End Date', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( $retrieved_data->status === 'Approved' ) {
															echo "<span class='mjschool-green-color'>" . esc_html( $retrieved_data->status ) . '</span>';
														} else {
															echo "<span class='mjschool-red-color'>" . esc_html( $retrieved_data->status ) . '</span>';
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														$comment = $retrieved_data->reason;
														$reason  = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
														echo esc_html( $reason );
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_html( $comment ); } else { esc_html_e( 'Reason', 'mjschool' ); } ?>"></i>
													</td>
													<?php
													// Custom Field Values.
													if ( ! empty( $user_custom_field ) ) {
														foreach ( $user_custom_field as $custom_field ) {
															if ( $custom_field->show_in_table === '1' ) {
																$module             = 'leave';
																$custom_field_id    = $custom_field->id;
																$module_record_id   = $retrieved_data->id;
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
                                                                            <a target="" href="<?php echo esc_url(content_url( '/uploads/school_assets/' . $custom_field_value)); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
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
													<?php
													if ( ( isset( $user_access['edit'] ) && $user_access['edit'] === '1' ) || ( isset( $user_access['delete'] ) && $user_access['delete'] === '1' ) ) {
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
                                                                            if (($retrieved_data->status != 'Approved' ) ) {
                                                                            	?>
                                                                                <li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
                                                                                    <a href="#" leave_id="<?php echo esc_attr($retrieved_data->id) ?>" class="mjschool-float-left-width-100px leave-approve mjschool_height_17px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-leave-approved.png"); ?>" >&nbsp;&nbsp;<?php esc_html_e( 'Approve', 'mjschool' ); ?></a>
                                                                                </li>
                                                                            	<?php
                                                                            }
                                                                            if (($retrieved_data->status != 'Rejected' ) ) {
                                                                           		?>
                                                                                <li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
                                                                                    <a href="#" leave_id="<?php echo esc_attr($retrieved_data->id) ?>" class="leave-reject mjschool-float-left-width-100px "><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-leave-rejected.png"); ?>" class="mjschool_height_17px">&nbsp;&nbsp;<?php esc_html_e( 'Reject', 'mjschool' ); ?></a>
                                                                                    
																				</li>
																				<?php
																			}
																			if ( $mjschool_role === 'administrator' ) {
																				?>
																				<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																					<a href="?page=mjschool_leave&tab=add_leave&action=edit&leave_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																				</li>
																				<li class="mjschool-float-left-width-100px">
																					<a href="?page=mjschool_leave&tab=leave_list&action=delete&leave_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
																				</li>
																				<?php
																			} else {
																				if ( $user_access['edit'] === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																						<a href="?dashboard=mjschool_user&page=leave&tab=add_leave&action=edit&leave_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" leave_id="'.$retrieved_data->id.'" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																					</li>
																					<?php
																				}
																				if ( $user_access['delete'] === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px">
																						<a href="?dashboard=mjschool_user&page=leave&tab=leave_list&action=delete&leave_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
																					</li>
																					<?php
																				}
																			}
																			?>
																		</ul>
																	</li>
																</ul>
															</div>
														</td>
														<?php
													}
													?>
												</tr>
												<?php
												++$i;
											}
											?>
										</tbody>
									</table>
								</form>
							</div>
							<?php
						} elseif ( $user_access['add'] === '1' ) {
							 ?>
							<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
								<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=leave&tab=add_leave' )); ?>">
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
						?>
					</div>
					<!-- Start Panel body. -->
					<?php
				}
				if ( $active_tab === 'add_leave' ) {
					?>
					<?php
					$leave_id = 0;
					if ( isset( $_REQUEST['leave_id'] ) ) {
						$leave_id = intval( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['leave_id'])) ) );
					}
					$edit = 0;
					if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
						$edit   = 1;
						$result = $mjschool_obj_leave->mjschool_get_single_leave( $leave_id );
					}
					$students = mjschool_get_student_group_by_class();
					?>
					<!-- Start panel body. -->
					<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!--------- Panel body. ------->
						<!-- Start Leave form. -->
						<form name="leave_form" action="" method="post" class="mjschool-form-horizontal" id="leave_form" enctype="multipart/form-data">
							<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
							<input id="action" type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
							<input type="hidden" name="leave_id" value="<?php echo esc_attr( $leave_id ); ?>" />
							<input type="hidden" name="status" value="<?php echo 'Not Approved'; ?>" />
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'Leave Information', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<?php
									// ------- Leave data for student. ---------//
									if ( $mjschool_role === 'student' ) {
										?>
										<input value="<?php print esc_attr( get_current_user_id() ); ?>" name="student_id" type="hidden" />
										<?php
									} elseif ( $mjschool_role === 'parent' ) {
										?>
										<div class="col-md-6 input mjschool-single-select">
											<select id="mjschool-student-list" class="form-control  display-members mjschool-line-height-30px max_mjschool-width-70px0 validate[required]" name="student_id">
												<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
												<?php
												if ( ! empty( $school_obj->child_list ) ) {
													foreach ( $school_obj->child_list as $retrive_data_id ) {
														$retrive_data = get_userdata( $retrive_data_id );
														echo '<option value="' . esc_attr( $retrive_data->ID ) . '" ' . selected( $student, $retrive_data->ID ) . '>' . esc_html( mjschool_student_display_name_with_roll( $retrive_data->ID ) ) . '</option>';
													}
												}
												?>
											</select>
											<span class="mjschool-multiselect-label">
												<label class="ml-1 mjschool-custom-top-label top" for="mjschool-student-list"><?php esc_html_e( 'Select Student', 'mjschool' ); ?><span class="required">*</span></label>
											</span>
										</div>
										<?php
									} else {
										?>
										<div class="col-md-6 input mjschool-single-select">
											<select id="mjschool-select-student" class="form-control add-search-single-select-js display-members mjschool-line-height-30px max_mjschool-width-70px0" name="student_id">
												<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
												<?php
												if ( $edit ) {
													$student = $result->student_id;
												} elseif ( isset( $_REQUEST['student_id'] ) ) {
													$student = intval(wp_unslash($_REQUEST['student_id']));
												} else {
													$student = '';
												}
												$studentdata = mjschool_get_all_student_list( 'student' );
												foreach ( $students as $label => $opt ) {
													?>
													<optgroup label="<?php echo esc_html__( 'Class :', 'mjschool' ) . ' ' . esc_html( $label ); ?>">
														<?php foreach ( $opt as $id => $name ) : ?>
															<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $id, $student ); ?>><?php echo esc_html( $name ); ?></option>
														<?php endforeach; ?>
													</optgroup>
												<?php } ?>
											</select>
											<span class="mjschool-multiselect-label">
												<label class="ml-1 mjschool-custom-top-label top" for="mjschool-select-student"><?php esc_html_e( 'Select Student', 'mjschool' ); ?><span class="required">*</span></label>
											</span>
										</div>
										<?php
									}
									?>
									<div class="col-md-5 input">
										<label class="ml-1 mjschool-custom-top-label top" for="leave_type"><?php esc_html_e( 'Leave Type', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
										<select class="form-control mjschool-line-height-30px validate[required] leave_type mjschool-width-100px" name="leave_type" id="leave_type">
											<option value=""><?php esc_html_e( 'Select Leave Type', 'mjschool' ); ?></option>
											<?php
											if ( $edit ) {
												$category = $result->leave_type;
											} elseif ( isset( $_REQUEST['leave_type'] ) ) {
												$category = sanitize_text_field(wp_unslash($_REQUEST['leave_type']));
											} else {
												$category = '';
											}
											$activity_category = mjschool_get_all_category( 'leave_type' );
											if ( ! empty( $activity_category ) ) {
												foreach ( $activity_category as $retrive_data ) {
													echo '<option value="' . esc_attr( $retrive_data->ID ) . '" ' . selected( $category, $retrive_data->ID ) . '>' . esc_html( $retrive_data->post_title ) . '</option>';
												}
											}
											?>
										</select>
									</div>
									<div class="col-sm-12 col-md-1 col-lg-1 col-xl-1 mb-3">
										<button id="mjschool-addremove-cat" class="mjschool-save-btn sibling_add_remove" model="leave_type"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>
									</div>
									<div class="col-md-6 mjschool-res-margin-bottom-20px mjschool-rtl-margin-top-15px">
										<div class="form-group">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div class="input-group">
														<label class="mjschool-custom-top-label mjschool-margin-left-0" for="reason"><?php esc_html_e( 'Leave Duration', 'mjschool' ); ?><span class="required">*</span></label>
														<div class="d-inline-block">
															<?php
															$durationval = '';
															if ( $edit ) {
																$durationval = $result->leave_duration;
															} elseif ( isset( $_POST['duration'] ) ) {
																$durationval = sanitize_text_field(wp_unslash($_POST['duration']));
															}
															?>
															<label class="radio-inline">
																<input id="half_day" type="radio" value="half_day" class="tog duration" name="leave_duration" idset="<?php if ( $edit ) { echo esc_attr( $result->id );} ?>" <?php checked( 'half_day', $durationval ); ?> /><?php esc_html_e( 'Half Day', 'mjschool' ); ?>
															</label>
															<label class="radio-inline">
																<?php
																if ( $edit ) {
																	?>
																	<input id="full_day" type="radio" value="full_day" class="tog duration" idset="<?php if ( $edit ) { echo esc_attr( $result->id );} ?>" name="leave_duration" <?php checked( 'full_day', $durationval ); ?> /><?php esc_html_e( 'Full Day', 'mjschool' ); ?>
																	<?php
																} else {
																	?>
																	<input id="full_day" type="radio" value="full_day" class="tog duration" idset="<?php if ( $edit ) { echo esc_attr( $result->id );} ?>"  name="leave_duration" <?php checked( 'full_day', $durationval ); ?> checked /><?php esc_html_e( 'Full Day', 'mjschool' ); ?>
																	<?php
																}
																?>
															</label>
															<label class="radio-inline margin_left_top">
																<input id="more_then_day" type="radio" idset="<?php if ( $edit ) { echo esc_attr( $result->id );} ?>" value="more_then_day" class="tog duration" name="leave_duration" <?php checked( 'more_then_day', $durationval ); ?> /><?php esc_html_e( 'More Than One Day', 'mjschool' ); ?>
															</label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div id="leave_date" class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<?php
										if ( $edit ) {
											$durationval = $result->leave_duration;
											if ( $durationval === 'more_then_day' ) {
												?>
												<div class="row">
													<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
														<div class="form-group input">
															<div class="col-md-12 form-control">
																<input id="leave_start_date" class="form-control validate[required] leave_start_date start_date datepicker1" autocomplete="off" type="text" name="start_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->start_date ) ) ) ); } elseif ( isset( $_POST['start_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
																<label class="active" for="leave_start_date"><?php esc_html_e( 'Leave Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
														<div class="form-group input">
															<div class="col-md-12 form-control">
																<input id="leave_end_date" class="form-control validate[required] leave_end_date start_date datepicker2" type="text" name="end_date" autocomplete="off" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->end_date ) ) ) ); } elseif ( isset( $_POST['end_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['end_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
																<label class="active" for="end"><?php esc_html_e( 'Leave End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<?php
											} else {
												?>
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="leave_start_date" class="form-control validate[required] leave_start_date start_date datepicker1" autocomplete="off" type="text" name="start_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->start_date ) ) ) ); } elseif ( isset( $_POST['start_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
														<label class="active" for="leave_start_date"><?php esc_html_e( 'Leave Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
												<?php
											}
										} else {
											?>
											<div class="form-group input">
												<div class="col-md-12 form-control">
													<input id="leave_start_date" class="form-control validate[required] leave_start_date start_date datepicker1" autocomplete="off" type="text" name="start_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->start_date ) ) ) ); } elseif ( isset( $_POST['start_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); }?>">
													<label class="active" for="leave_start_date"><?php esc_html_e( 'Leave Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												</div>
											</div>
											<?php
										}
										?>
									</div>
									<div class="col-md-6 mjschool-note-text-notice">
										<div class="form-group input">
											<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
												<div class="form-field">
													<textarea id="reason" maxlength="150" class="mjschool-textarea-height-47px form-control validate[required,custom[address_description_validation]]" name="reason"> <?php if ( $edit ) { echo esc_textarea( $result->reason ); } elseif ( isset( $_POST['reason'] ) ) { echo esc_textarea(sanitize_text_field(wp_unslash( $_POST['reason'] )));} ?> </textarea>
													<span class="mjschool-txt-title-label"></span>
													<label class="text-area address active" for="reason"><?php esc_html_e( 'Reason', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												</div>
											</div>
										</div>
									</div>
									<?php wp_nonce_field( 'save_leave_nonce' ); ?>
								</div>
							</div>
							<div class="form-body mjschool-user-form">
								<?php
								if ( ! $edit ) {
									if ( $school_obj->role != 'student' && $school_obj->role != 'parent' ) {
										?>
										<div class="row">
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px mb-3">
												<div class="form-group">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label class="mjschool-custom-top-label" for="mjschool_enable_leave_mail"><?php esc_html_e( 'Send Mail To Parents & Students', 'mjschool' ); ?></label>
																<input id="mjschool_enable_leave_mail" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_leave_mail" value="1" /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px mb-3">
												<div class="form-group">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label class="mjschool-custom-top-label" for="mjschool_enable_leave_mjschool_student"><?php esc_html_e( 'Enable Send SMS To Student', 'mjschool' ); ?></label>
																<input id="mjschool_enable_leave_mjschool_student" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_leave_mjschool_student" value="1" /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px mb-3">
												<div class="form-group">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label class="mjschool-custom-top-label" for="mjschool_enable_leave_mjschool_parent"><?php esc_html_e( 'Enable Send SMS To Parent', 'mjschool' ); ?></label>
																<input id="mjschool_enable_leave_mjschool_parent" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_leave_mjschool_parent" value="1" /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<?php
									}
								}
								?>
							</div>
							<?php
							// --------- Get module-wise custom field data. --------------//
							$mjschool_custom_field_obj = new Mjschool_Custome_Field();
							$module                    = 'leave';
							$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
							?>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6">
										<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Leave', 'mjschool' ); } else { esc_html_e( 'Add Leave', 'mjschool' );} ?>" name="save_leave" class="btn btn-success mjschool-save-btn <?php if ( $mjschool_role != 'student' ) { echo 'save_leave_validate';} ?>" />
									</div>
								</div>
							</div>
						</form>
						<!-- End Leave form. -->
					</div>
					<!-- End panel body -->
					<?php
				}
				?>
			</div><!--------- Panel body. ------->
		</div><!--------- Panel white. ------->
	</div>
</div>