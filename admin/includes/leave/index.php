<?php
/**
 * Leave Management Admin Page.
 *
 * This file handles all leave-related operations for the admin Interface,
 * including adding, editing, approving, rejecting, deleting, exporting,
 * and displaying student leave records.
 *
 * @package Mjschool
 * @subpackage MJSchool/admin/includes/leave
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$mjschool_obj_leave        = new Mjschool_Leave();
$active_tab                = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'leave_list';
$to                        = array();
$arr                       = array();
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'leave';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<div class="mjschool-page-inner"><!--------- Page Inner. ------->
	<div class="mjschool-main-list-margin-5px">
		<?php
		if ( isset( $_POST['save_leave'] ) ) {
			$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
			if ( wp_verify_nonce( $nonce, 'save_leave_nonce' ) ) {
				if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
					$leave_id = isset( $_REQUEST['leave_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_id'] ) ) : '';
					$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
					if ( wp_verify_nonce( $nonce_action, 'edit_action' ) ) {
						$result = $mjschool_obj_leave->mjschool_add_leave( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
						// UPDATE CUSTOM FIELD DATA.
						$mjschool_custom_field_obj = new Mjschool_Custome_Field();
						$module                    = 'leave';
						$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $leave_id );
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list&message=2' ) );
						die();
					} else {
						wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
					}
				} else {
					global $wpdb;
					$result                    = $mjschool_obj_leave->mjschool_add_leave( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
					$_POST['leave_id']         = $result;
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'leave';
					$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
					if ( $result ) {
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list&message=1' ) );
						die();
					}
				}
			}
		}
		if ( isset( $_POST['approve_comment'] ) ) {
			$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
			if ( wp_verify_nonce( $nonce, 'approve_leave_nonce' ) ) {
				$result = $mjschool_obj_leave->mjschool_approve_leave( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list&message=4' ) );
					die();
				} else {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list&message=6' ) );
					die();
				}
			}
		}
		if ( isset( $_POST['reject_leave'] ) ) {
			$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
			if ( wp_verify_nonce( $nonce, 'reject_leave_nonce' ) ) {
				$result = $mjschool_obj_leave->mjschool_reject_leave( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list&message=5' ) );
					die();
				} else {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list&message=6' ) );
					die();
				}
			}
		}
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
			$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
			if ( wp_verify_nonce( $nonce_action, 'delete_action' ) ) {
				$leave_id = isset( $_REQUEST['leave_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['leave_id'] ) ) ) ) : 0;
				$result   = $mjschool_obj_leave->mjschool_delete_leave( $leave_id );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list&message=3' ) );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		}
		if ( isset( $_REQUEST['delete_selected'] ) ) {
			if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
				$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
				foreach ( $ids as $id ) {
					$result = $mjschool_obj_leave->mjschool_delete_leave( $id );
				}
			}
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list&message=3' ) );
				die();
			}
		}
		if ( isset( $_POST['Leave_export_csv'] ) ) {
			if ( isset( $_POST['id'] ) && is_array( $_POST['id'] ) ) {
				$export_ids = array_map( 'intval', wp_unslash( $_POST['id'] ) );
				foreach ( $export_ids as $leave_id ) {
					$leave_list[] = $mjschool_obj_leave->mjschool_get_single_leave( $leave_id );
				}
				if ( ! empty( $leave_list ) ) {
					$header   = array();
					$header[] = 'Student Name';
					$header[] = 'Leave Type';
					$header[] = 'Leave Duration';
					$header[] = 'Start Date';
					$header[] = 'End Date';
					$header[] = 'Reason';
					$header[] = 'Status';
					$header[] = 'Created By';
					$filename = 'export/mjschool-export-leave.csv';
					$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( esc_html__( "can't open file", 'mjschool' ) );
					fputcsv( $fh, $header );
					foreach ( $leave_list as $retrive_data ) {
						$row   = array();
						$row[] = mjschool_student_display_name_with_roll( $retrive_data->student_id );
						$row[] = get_the_title( $retrive_data->leave_type );
						$row[] = mjschool_leave_duration_label( $retrive_data->leave_duration );
						$row[] = mjschool_get_date_in_input_box( $retrive_data->start_date );
						if ( $retrive_data->end_date ) {
							$row[] = mjschool_get_date_in_input_box( $retrive_data->end_date );
						} else {
							$row[] = '';
						}
						$row[] = $retrive_data->reason;
						$row[] = $retrive_data->status;
						$row[] = mjschool_get_display_name( $retrive_data->created_by );
						fputcsv( $fh, $row );
					}
					fclose( $fh );
					// download csv file.
					ob_clean();
					$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-leave.csv'; // file location.
					$mime = 'text/plain';
					header( 'Content-Type:application/force-download' );
					header( 'Pragma: public' );       // required.
					header( 'Expires: 0' );           // no cache.
					header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
					header( 'Last-Modified: ' . date( 'D, d M Y H:i:s', filemtime( $file ) ) . ' GMT' );
					header( 'Cache-Control: private', false );
					header( 'Content-Type: ' . $mime );
					header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
					header( 'Content-Transfer-Encoding: binary' );
					header( 'Connection: close' );
					readfile( $file );
					die();
				}
			}
		}
		if ( isset( $_REQUEST['message'] ) ) {
			$message = sanitize_text_field( wp_unslash( $_REQUEST['message'] ) );
			if ( $message === '1' ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php esc_html_e( 'Leave Added Successfully', 'mjschool' ); ?></p>
				</div>
				<?php
			} elseif ( $message === '2' ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php esc_html_e( 'Leave Updated Successfully', 'mjschool' ); ?></p>
				</div>
				<?php
			} elseif ( $message === '3' ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php esc_html_e( 'Leave Deleted Successfully', 'mjschool' ); ?></p>
				</div>
				<?php
			} elseif ( $message === '4' ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php esc_html_e( 'Leave Approved Successfully', 'mjschool' ); ?></p>
				</div>
				<?php
			} elseif ( $message === '5' ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php esc_html_e( 'Leave Rejected Successfully', 'mjschool' ); ?></p>
				</div>
				<?php
			} elseif ( $message === '6' ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php esc_html_e( 'Oops, Something went wrong.', 'mjschool' ); ?></p>
				</div>
				<?php
			}
		}
		?>
		<div class="mjschool-panel-white"><!--------- panel White. ------->
			<div class="mjschool-panel-body"> <!--------- panel body. ------->
				<?php
				if ( $active_tab === 'leave_list' ) {
					?>
					<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
						<form method="post">
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-3 input">
										<select class="form-control Student_leave_drop" id="Student_leave" name="Student_id">
											<option value="all_student"><?php esc_html_e( 'Select All Student', 'mjschool' ); ?></option>
											<?php
											$emp_id      = 0;
											$studentdata = mjschool_get_all_student_list();
											foreach ( $studentdata as $student ) {
												if ( isset( $_POST['Student_id'] ) ) {
													$emp_id = intval( wp_unslash( $_POST['Student_id'] ) );
												} else {
													$uid    = $student->ID;
													$emp_id = get_user_meta( $uid, 'student', true );
												}
												?>
												<option value="<?php echo esc_attr( intval( $student->ID ) ); ?>" <?php selected( $student->ID, $emp_id ); ?>><?php echo esc_html( mjschool_student_display_name_with_roll( $student->ID ) ); ?></option>
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
										<select class="form-control" id="lave_status" name="status">
											<?php
											$select_status = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : '';
											?>
											<option value="all_status" <?php selected( $select_status, 'all_status' ); ?>><?php esc_html_e( 'Select All Status', 'mjschool' ); ?></option>
											<option value="Not Approved" <?php selected( $select_status, 'Not Approved' ); ?>><?php esc_html_e( 'Not Approved', 'mjschool' ); ?></option>
											<option value="Approved" <?php selected( $select_status, 'Approved' ); ?>><?php esc_html_e( 'Approved', 'mjschool' ); ?></option>
											<option value="Rejected" <?php selected( $select_status, 'Rejected' ); ?>><?php esc_html_e( 'Rejected', 'mjschool' ); ?></option>
										</select>
									</div>
									<div class="col-md-3 mb-3 input">
										<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date', 'mjschool' ); ?></label>
										<select class="mjschool-line-height-30px form-control date_type validate[required]" id="date_type" name="date_type" autocomplete="off">
											<?php
											$date_type = isset( $_REQUEST['date_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['date_type'] ) ) : 'this_month';
											?>
											<option value="today" <?php selected( $date_type, 'today' ); ?>><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
											<option value="this_week" <?php selected( $date_type, 'this_week' ); ?>><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
											<option value="last_week" <?php selected( $date_type, 'last_week' ); ?>><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
											<option value="this_month" <?php selected( $date_type, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
											<option value="last_month" <?php selected( $date_type, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
											<option value="last_3_month" <?php selected( $date_type, 'last_3_month' ); ?>><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
											<option value="last_6_month" <?php selected( $date_type, 'last_6_month' ); ?>><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
											<option value="last_12_month" <?php selected( $date_type, 'last_12_month' ); ?>><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
											<option value="this_year" <?php selected( $date_type, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
											<option value="last_year" <?php selected( $date_type, 'last_year' ); ?>><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
											<option value="period" <?php selected( $date_type, 'period' ); ?>><?php esc_html_e( 'Period', 'mjschool' ); ?></option>
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
															<input type="text" id="report_sdate" class="form-control" name="start_date" value="<?php echo isset( $_POST['start_date'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
															<label for="report_sdate" class="active"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
														</div>
													</div>
												</div>
												<div class="col-md-6 mb-2">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input type="text" id="report_edate" class="form-control" name="end_date" value="<?php echo isset( $_POST['end_date'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
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
										<input type="submit" name="view_student" Value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
									</div>
								</div>
							</div>
						</form>
						<?php
						if ( isset( $_REQUEST['view_student'] ) ) {
							$date_type  = isset( $_POST['date_type'] ) ? sanitize_text_field( wp_unslash( $_POST['date_type'] ) ) : '';
							$start_date = '';
							$end_date   = '';
							if ( isset( $_POST['start_date'] ) ) {
								$start_date = sanitize_text_field( wp_unslash( $_POST['start_date'] ) );
							}
							if ( isset( $_POST['end_date'] ) ) {
								$end_date = sanitize_text_field( wp_unslash( $_POST['end_date'] ) );
							}
							$Student_id = isset( $_POST['Student_id'] ) ? sanitize_text_field( wp_unslash( $_POST['Student_id'] ) ) : '';
							$status     = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
							$leave_data = mjschool_get_leave_data_filter_wise( $Student_id, $status, $date_type, $start_date, $end_date );
						} else {
							$Student_id = 'all_student';
							$status     = 'all_status';
							$date_type  = 'this_month';
							$start_date = '';
							$end_date   = '';
							$leave_data = mjschool_get_leave_data_filter_wise( $Student_id, $status, $date_type, $start_date, $end_date );
						}
						if ( ! empty( $leave_data ) ) {
							?>
							<div class="table-responsive"><!-- table-responsive. -->
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="leave_list" class="display mjschool-admin-transport-datatable" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input name="select_all" value="all" class="select_all mjschool-custom-padding-0" name="select_all" type="checkbox"></th>
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
												?>
												<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 0;
											foreach ( $leave_data as $retrieved_data ) {
												$leave_id = mjschool_encrypt_id( $retrieved_data->id );
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
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk selected_leave select-checkbox" name="id[]" value="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>"></td>
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
														<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
															
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-leave.png' ); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
															
														</p>
													</td>
													<td>
														<?php
														$sname = mjschool_student_display_name_with_roll( $retrieved_data->student_id );
														if ( $sname != '' ) {
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
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Duration', 'mjschool' ); ?>"></i>
													</td>
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
														$status = $retrieved_data->status;
														if ( $status === 'Approved' ) {
															echo "<span class='mjschool-green-color'> " . esc_html( $status ) . ' </span>';
														} else {
															echo "<span class='mjschool-red-color'> " . esc_html( $status ) . ' </span>';
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $retrieved_data->status_comment ) ) { echo esc_attr( $retrieved_data->status_comment ); } else { esc_attr_e( 'Status', 'mjschool' ); } ?>"></i>
													</td>
													<td>
														<?php
														$comment = $retrieved_data->reason;
														$reason  = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
														echo esc_html( $reason );
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_attr( $comment ); } else { esc_attr_e( 'Reason', 'mjschool' ); } ?>"></i>
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
																			<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile">
																				<button class="btn btn-default view_document" type="button">
																					<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
																				</button>
																			</a>
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
														<div class="gtsm-mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li>
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<?php
																		if ( ( $retrieved_data->status != 'Approved' ) ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="#" leave_id="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>" class="mjschool-float-left-width-100px leave-approve mjschool_height_17px">
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-leave-approved.png' ); ?>" >&nbsp;&nbsp;<?php esc_html_e( 'Approve', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		if ( ( $retrieved_data->status != 'Rejected' ) ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="#" leave_id="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>" class="leave-reject mjschool-float-left-width-100px">
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-leave-rejected.png' ); ?>" class="mjschool_height_17px">&nbsp;&nbsp;<?php esc_html_e( 'Reject', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		if ( $mjschool_role === 'administrator' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_leave&tab=add_leave&action=edit&leave_id=' . rawurlencode( $leave_id ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list&action=delete&leave_id=' . rawurlencode( $leave_id ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																					<i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		} else {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'leave', 'tab' => 'add_leave', 'action' => 'edit', 'leave_id' => rawurlencode( $leave_id ), '_wpnonce_action' => rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ), home_url() ) ); ?>" leave_id="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>" class="mjschool-float-left-width-100px leave-reject"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'leave', 'tab' => 'leave_list', 'action' => 'delete', 'leave_id' => rawurlencode( $leave_id ), '_wpnonce_action' => rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ), home_url() ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																					<i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?>
																				</a>
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
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="select_all" class="mjschool-sub-chk select_all mjchool_margin_top_0px" name="id[]" value="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>" >
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										
										<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
										<button data-toggle="tooltip" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" name="Leave_export_csv" class="leave_csv_selected mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-export-csv.png' ); ?>"></button>
										
									</div>
								</form>
							</div><!--------- Table Responsive. ------->
							<?php
						} elseif ( isset( $_REQUEST['view_student'] ) ) {
							
							?>
							<div class="mjschool-no-data-list-div">
								<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php
							
						} else {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_leave&tab=add_leave' ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<!-- Start Panel body. -->
					<?php
				}
				if ( $active_tab === 'add_leave' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/leave/add-leave.php';
				}
				?>
			</div><!--------- panel body. ------->
		</div><!--------- panel White. ------->
	</div>
</div>