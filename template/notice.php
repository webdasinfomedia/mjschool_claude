<?php
/**
 * Notice Board/Communication Management View/Controller.
 *
 * This file handles the view and form processing for the school's Notice Board or
 * mass communication system, allowing authorized users (e.g., staff) to create,
 * view, and manage notices for various recipients.
 *
 * Key features include:
 * - **DataTables:** Initializes a jQuery DataTables instance for displaying the list of existing notices.
 * - **Targeting:** Allows notices to be targeted to specific classes, individual users, or all users.
 * - **Communication Channels:** Includes options for sending the notice content via both the internal system and SMS.
 * - **Form Processing:** Handles the creation (insert/update) of new notices.
 * - **Custom Fields:** Integrates custom fields managed by `Mjschool_Custome_Field` for the 'notice' module.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 * 
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( "mjschool_custom_class");
$role_name                 = mjschool_get_user_role( get_current_user_id() );
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'notice';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'noticelist';
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
// -------------------- SAVE NOTICE. ---------------------------//
if ( isset( $_POST['save_notice'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_notice_admin_nonce' ) ) {
		$start_date = date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_REQUEST['start_date'])) ) );
		$end_date   = date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_REQUEST['end_date'])) ) );
		$exlude_id  = mjschool_approve_student_list();
		if ( $start_date > $end_date ) {
			 ?>
			 <div class="mjschool-date-error-trigger" data-error="1"></div>
			<?php  
		} else {
			if ( isset( $_POST['class_id'] ) ) {
				$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
			}
			if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
				$notice_id = intval( mjschool_decrypt_id( intval( wp_unslash($_REQUEST['notice_id'])) ) );
				if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
					$args    = array(
						'ID'           => $notice_id,
						'post_title'   => sanitize_textarea_field( $_REQUEST['notice_title'] ),
						'post_content' => sanitize_textarea_field( $_REQUEST['notice_content'] ),
					);
					$result1 = wp_update_post( $args );
					// UPDATE CUSTOM FIELD DATA.
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'notice';
					$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $notice_id );
					$result2                   = update_post_meta( $notice_id, 'notice_for', sanitize_text_field(wp_unslash($_REQUEST['notice_for'])) );
					$result3                   = update_post_meta( $notice_id, 'start_date', sanitize_text_field(wp_unslash($_REQUEST['start_date'])) );
					$result4                   = update_post_meta( $notice_id, 'end_date', sanitize_text_field(wp_unslash($_REQUEST['end_date'])) );
					if ( isset( $_POST['class_id'] ) ) {
						$result5 = update_post_meta( $notice_id, 'smgt_class_id', intval( wp_unslash($_REQUEST['class_id'])) );
					}
					if ( isset( $_POST['class_section'] ) ) {
						$result6 = update_post_meta( $notice_id, 'smgt_section_id', sanitize_text_field(wp_unslash($_REQUEST['class_section'])) );
					}
					$role                             = sanitize_text_field(wp_unslash($_POST['notice_for']));
					$mjschool_service_enable = 0;
					$current_mjschool_service_active  = get_option( 'mjschool_service' );
					if ( isset( $_POST['mjschool_service_enable'] ) ) {
						$mjschool_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_service_enable']));
					}
					if ( $mjschool_service_enable ) {
						$current_mjschool_service = get_option( 'mjschool_service' );
						if ( ! empty( $current_mjschool_service ) ) {
							$userdata = mjschool_get_user_notice( $role, intval( wp_unslash($_REQUEST['class_id'])), sanitize_text_field(wp_unslash($_REQUEST['class_section'])) );
							if ( ! empty( $userdata ) ) {
								$mail_id = array();
								$i       = 0;
								foreach ( $userdata as $mjschool_user ) {
									if ( $role === 'parent' && $class_id !== 'all' ) {
										$mail_id[] = $mjschool_user['ID'];
									} else {
										$mail_id[] = $mjschool_user->ID;
									}
									++$i;
								}
								$parent_number = array();
								foreach ( $mail_id as $mjschool_user ) {
									// SEND SMS NOTIFICATION.
									$message_content = sanitize_text_field(wp_unslash($_POST['mjschool_template']));
									$type            = 'Message';
									mjschool_send_mjschool_notification( $mjschool_user, $type, $message_content );
								}
							}
						}
					}
					if ( $result1 || $result2 || $result3 || $result4 || isset( $result5 ) ) {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=notice&tab=noticelist&message=2') );
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else {
				$current_mjschool_service  = get_option( 'mjschool_service' );
				$post_id                   = wp_insert_post(
					array(
						'post_status'  => 'publish',
						'post_type'    => 'notice',
						'post_title'   => sanitize_textarea_field( wp_unslash($_REQUEST['notice_title']) ),
						'post_content' => sanitize_textarea_field( wp_unslash($_REQUEST['notice_content']) ),
					)
				);
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'notice';
				$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $post_id );
				if ( ! empty( $_POST['notice_for'] ) ) {
					// Send Push Notification. //
					$notice_for_value = sanitize_text_field(wp_unslash($_POST['notice_for']));
					if ( $notice_for_value === 'supportstaff' || $notice_for_value === 'parent' ) {
						$user_list_array = get_users(
							array(
								'role__in' => array( $notice_for_value ),
								'fields'   => array( 'ID' ),
							)
						);
					} elseif ( $notice_for_value === 'all' ) {
						$user_list_array = get_users(
							array(
								'role__in' => array( 'supportstaff', 'parent', 'teacher', 'student' ),
								'fields'   => array( 'ID' ),
							)
						);
					} elseif ( $notice_for_value === 'teacher' ) {
						$class_list = $_POST['class_id'];
						if ( $_POST['class_id'] === 'all' ) {
							$user_list_array = get_users(
								array(
									'role__in' => array( $notice_for_value ),
									'fields'   => array( 'ID' ),
								)
							);
						} else {
							$teacher_list = mjschool_get_teacher_by_class_id ($class_list );
							if ( $teacher_list ) {
								foreach ( $teacher_list as $teacher ) {
									$user_list_array[] = $teacher->teacher_id;
								}
							}
						}
					} elseif ( $notice_for_value === 'student' ) {
						
						$user_list = array();
						$exlude_id = mjschool_approve_student_list();
						$query_data['exclude'] = $exlude_id;
						$class_list = intval( wp_unslash($_POST['class_id']));
						$query_data['role'] = $notice_for_value;
						$query_data['fields'] = array( 'ID' );
						$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
						if ($class_section) {
							$query_data['meta_key'] = 'class_section';
							$query_data['meta_value'] = $class_section;
							$query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' ) );
							$user_list_array = get_users($query_data);
						} elseif ($class_list === 'all' ) {
							$user_list_array = get_users(array(
								'role__in'     => array($notice_for_value),
								'fields' => array( 'ID' ),
							 ) );
						} elseif (empty($class_section) and !empty($class_list ) ) {
							$query_data['meta_key'] = 'class_name';
							$query_data['meta_value'] = $class_list;
							$user_list_array = get_users($query_data);
						}
						
					}
					if ( ! empty( $user_list_array ) ) {
						$device_token = array();
						foreach ( $user_list_array as $retrive_data ) {
							$device_token[] = get_user_meta( $retrive_data->id, 'token_id', true );
						}
						$title             = esc_html__( 'You have a New Notice', 'mjschool' ) . ' ' . sanitize_textarea_field( stripslashes( $_POST['notice_title'] ) );
						$text              = sanitize_textarea_field( stripslashes( $_POST['notice_content'] ) );
						$notification_data = array(
							'registration_ids' => $device_token,
							'notification'     => array(
								'title' => $title,
								'body'  => $text,
								'type'  => 'notice',
							),
						);
						$json              = json_encode( $notification_data );
						mjschool_send_push_notification( $json );
						// End Send Push Notification. //
					}
					delete_post_meta( $post_id, 'notice_for' );
					$result = add_post_meta( $post_id, 'notice_for', sanitize_text_field(wp_unslash($_POST['notice_for'])) );
					$result = add_post_meta( $post_id, 'start_date', sanitize_text_field(wp_unslash($_POST['start_date'])) );
					$result = add_post_meta( $post_id, 'end_date', sanitize_text_field(wp_unslash($_POST['end_date'])) );
					if ( isset( $_POST['class_id'] ) ) {
						$result = add_post_meta( $post_id, 'smgt_class_id', intval( wp_unslash($_POST['class_id'])) );
					}
					if ( isset( $_POST['class_section'] ) ) {
						$result6 = update_post_meta( intval( wp_unslash($_REQUEST['notice_id'])), 'smgt_section_id', sanitize_text_field(wp_unslash($_REQUEST['class_section'])) );
					}
					$role                             = sanitize_text_field(wp_unslash($_POST['notice_for']));
					$mjschool_service_enable = 0;
					$smgt_mail_service_enable         = 0;
					$current_mjschool_service_active  = get_option( 'mjschool_service' );
					$userdata                         = mjschool_get_user_notice( $role, intval( wp_unslash( $_POST['class_id'] ) ) );
					if ( ! empty( $userdata ) ) {
						if ( isset( $_POST['mjschool_mail_service_enable'] ) ) {
							$smgt_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_mail_service_enable']));
						}
						if ( $smgt_mail_service_enable ) {
							$mail_id   = array();
							$i         = 0;
							$startdate = strtotime( sanitize_text_field(wp_unslash($_POST['start_date'])) );
							$enddate   = strtotime( sanitize_text_field(wp_unslash($_POST['end_date'])) );
							if ( $startdate === $enddate ) {
								$date = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_date'])) );
							} else {
								$date = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_date'])) ) . ' To ' . mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['end_date'])) );
							}
							$search['{{notice_title}}']   = sanitize_text_field(wp_unslash($_REQUEST['notice_title']));
							$search['{{notice_date}}']    = $date;
							$search['{{notice_comment}}'] = sanitize_text_field(wp_unslash($_REQUEST['notice_content']));
							$search['{{school_name}}']    = get_option( 'mjschool_name' );
							$message                      = mjschool_string_replacement( $search, get_option( 'mjschool_notice_mailcontent' ) );
							$headers  = '';
							$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
							$headers .= "MIME-Version: 1.0\r\n";
							$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
							foreach ( $userdata as $mjschool_user ) {
								$userinfo = get_user_by( 'id', $mjschool_user->id );
								if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
									wp_mail( $userinfo->user_email, get_option( 'mjschool_notice_mailsubject' ), $message, $headers );
								}
								if ( $role === 'parent' && $class_id !== 'all' ) {
									$mail_id[] = $mjschool_user['ID'];
								} else {
									$mail_id[] = $mjschool_user->ID;
								}
								++$i;
							}
						}
						if ( isset( $_POST['mjschool_service_enable'] ) ) {
							$mjschool_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_service_enable']));
						}
						if ( $mjschool_service_enable ) {
							if ( ! empty( $current_mjschool_service ) ) {
								$parent_number = array();
								foreach ( $mail_id as $mjschool_user ) {
									$message_content = sanitize_text_field(wp_unslash($_POST['mjschool_template']));
									$type            = 'Message';
									mjschool_send_mjschool_notification( $user_id, $type, $message_content );
								}
							}
						}
					}
					if ( $result ) {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=notice&tab=noticelist&message=1') );
						die();
					}
				}
			}
		}
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = wp_delete_post( $id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( home_url('?dashboard=mjschool_user&page=notice&tab=noticelist&message=3') );
		die();
	}
}
// ----------------------------- SAVE NOTICE. -----------------------------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = wp_delete_post( mjschool_decrypt_id( intval( wp_unslash( $_REQUEST['notice_id'] ) ) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=notice&tab=noticelist&message=3') );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
?>
<!-- View Popup Code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="mjschool-notice-content"></div>
	</div>
</div>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Notice Added Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Notice Updated Successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Notice Deleted Successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
				
			</button>
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	if ( $active_tab === 'noticelist' ) {
		$user_id = get_current_user_id();
		// ------- NOTICE DATA FOR STUDENT. ---------//
		if ( $school_obj->role === 'student' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$class_name    = get_user_meta( get_current_user_id(), 'class_name', true );
				$class_section = get_user_meta( get_current_user_id(), 'class_section', true );
				$notice_list   = mjschool_student_notice_dashboard( $class_name, $class_section );
			} else {
				$args['post_type']      = 'notice';
				$args['posts_per_page'] = -1;
				$args['post_status']    = 'public';
				$q                      = new WP_Query();
				$notice_list            = $q->query( $args );
			}
		}
		// ------- NOTICE DATA FOR TEACHER. ---------//
		elseif ( $school_obj->role === 'teacher' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$class_name  = get_user_meta( get_current_user_id(), 'class_name', true );
				$notice_list = mjschool_teacher_notice_dashbord();
			} else {
				$args['post_type']      = 'notice';
				$args['posts_per_page'] = -1;
				$args['post_status']    = 'public';
				$q                      = new WP_Query();
				$notice_list            = $q->query( $args );
			}
		}
		// ------- NOTICE DATA FOR PARENT. ---------//
		elseif ( $school_obj->role === 'parent' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$notice_list = mjschool_parent_notice_dashbord();
			} else {
				$args['post_type']      = 'notice';
				$args['posts_per_page'] = -1;
				$args['post_status']    = 'public';
				$q                      = new WP_Query();
				$notice_list            = $q->query( $args );
			}
		}
		// ------- NOTICE DATA FOR SUPPORT STAFF. ---------//
		else {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$notice_list = mjschool_supportstaff_notice_dashbord();
			} else {
				$args['post_type']      = 'notice';
				$args['posts_per_page'] = -1;
				$args['post_status']    = 'public';
				$q                      = new WP_Query();
				$notice_list            = $q->query( $args );
			}
		}
		?>
		<div class="mjschool-panel-body">
			<?php
			if ( ! empty( $notice_list ) ) {
				?>
				<div class="table-responsive">
					<form id="mjschool-common-form" name="mjschool-common-form" method="post"><!-- Mjschool-panel-body. -->
						<table id="frontend_notice_list" class="display dataTable mjschool-notice-datatable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $role_name === 'supportstaff' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
										<?php
									}
									?>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Notice Title', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Notice Comment', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Notice Start Date', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Notice End Date', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Notice For', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
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
								if ( ! empty( $notice_list ) ) {
									$i = 0;
									foreach ( $notice_list as $retrieved_data ) {
										$color_class_css = mjschool_table_list_background_color( $i );
										?>
										<tr>
											<?php
											if ( $role_name === 'supportstaff' ) {
												?>
												<td class="mjschool-checkbox-width-10px">
													<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
												</td>
												<?php
											}
											?>
											<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
												<a class="mjschool-color-black view-notice" id="<?php echo esc_attr( $retrieved_data->ID ); ?>" href="#">
													<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
														
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-notice.png"); ?>" height="30px" width="30px" class="mjschool-massage-image">
														
													</p>
												</a>
											</td>
											<td><a class="mjschool-color-black view-notice" id="<?php echo esc_attr( $retrieved_data->ID ); ?>" href="#"><?php echo esc_attr( $retrieved_data->post_title ); ?></a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Notice Title', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php
												if ( ! empty( $retrieved_data->post_content ) ) {
													$strlength = strlen( $retrieved_data->post_content );
													if ( $strlength > 30 ) {
														echo esc_html( substr( $retrieved_data->post_content, 0, 30 ) ) . '...';
													} else {
														echo esc_html( $retrieved_data->post_content );
													}
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->post_content ) ) { echo esc_html( $retrieved_data->post_content ); } else { esc_html_e( 'Notice Comment', 'mjschool' ); } ?>"></i>
											</td>
											<td><?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $retrieved_data->ID, 'start_date', true ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Notice Start Date', 'mjschool' ); ?>"></i></td>
											<td><?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $retrieved_data->ID, 'end_date', true ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Notice End Date', 'mjschool' ); ?>"></i></td>
											<td><?php echo esc_html( ucfirst( get_post_meta( $retrieved_data->ID, 'notice_for', true ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Notice For', 'mjschool' ); ?>"></i></td>
											<td>
												<?php
												if ( get_post_meta( $retrieved_data->ID, 'notice_for', true ) === 'all' ) {
													esc_html_e( 'N/A', 'mjschool' );
												} elseif ( get_post_meta( $retrieved_data->ID, 'smgt_class_id', true ) !== '' && get_post_meta( $retrieved_data->ID, 'smgt_class_id', true ) === 'all' ) {
													esc_html_e( 'All', 'mjschool' );
												} elseif ( get_post_meta( $retrieved_data->ID, 'smgt_class_id', true ) !== '' ) {
													echo esc_html( mjschool_get_class_name( get_post_meta( $retrieved_data->ID, 'smgt_class_id', true ) ) );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
											</td>
											<?php
											// Custom Field Values.
											if ( ! empty( $user_custom_field ) ) {
												foreach ( $user_custom_field as $custom_field ) {
													if ( $custom_field->show_in_table === '1' ) {
														$module             = 'notice';
														$custom_field_id    = $custom_field->id;
														$module_record_id   = $retrieved_data->ID;
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
																	<td>
																		<?php
																		if ( ! empty( $custom_field_value ) ) {
																			?>
																			<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
																			<?php
																		} else {
																			esc_html_e( 'N/A', 'mjschool' );
																		}
																		?>
																	</td>
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
												<div class="mjschool-user-dropdown">
													<ul  class="mjschool_ul_style">
														<li >
															<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																
															</a>
															<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																<li class="mjschool-float-left-width-100px">
																	<a href="#" class="mjschool-float-left-width-100px view-notice" id="<?php echo esc_attr( $retrieved_data->ID ); ?>"><i class="fas fa-eye"> </i><?php esc_html_e( 'View Notice Detail', 'mjschool' ); ?></a>
																</li>
																<?php
																if ( $user_access['edit'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=notice&tab=addnotice&action=edit' . '&notice_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) )); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																	</li>
																	<?php
																}
																if ( $user_access['delete'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px"> <a href="<?php echo esc_url('?dashboard=mjschool_user&page=notice&tab=noticelist&action=delete' . '&notice_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'delete_action' ) )); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
						<?php
						if ( $role_name === 'supportstaff' ) {
							?>
							<div class="mjschool-print-button pull-left">
								<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload" type="button">
									<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
									<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
								</button>
								<?php  
								if ($user_access['delete'] === '1' ) { ?>
									<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									<?php  
								}
								?>
							</div>
							<?php
						}
						?>
					</form>
				</div>
				<?php
			} elseif ( $user_access['add'] === '1' ) {
				 ?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=notice&tab=addnotice') ); ?>">
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
		<?php
	}
	if ( $active_tab === 'addnotice' ) {
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$edit = 1;
			$post = get_post( mjschool_decrypt_id( intval( wp_unslash($_REQUEST['notice_id'])) ) );
		}
		?>
		<div class="mjschool-panel-body">
			<form name="class_form" action="" method="post" class="mt-3 mjschool-form-horizontal" id="notice_form" enctype="multipart/form-data">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="notice_id" value="<?php if ( $edit ) { echo esc_attr( intval( wp_unslash($_REQUEST['notice_id'] ) ) ); } ?>" />
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Notice Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mjschool-notice-title" class="form-control validate[required,custom[description_validation]] text-input" maxlength="100" type="text" value="<?php if ( $edit ) { echo esc_attr( $post->post_title ); } ?>" name="notice_title">
									<label for="mjschool-notice-title"><?php esc_html_e( 'Notice Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea name="notice_content" class="mjschool-textarea-height-60px form-control validate[custom[description_validation]]" maxlength="1000" id="notice_content"> <?php if ( $edit ) { echo esc_attr( $post->post_content ); } ?> </textarea>
										<span class="mjschool-txt-title-label"></span>
										<label class="text-area address active" for="notice_content"><?php esc_html_e( 'Notice Comment', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="notice_Start_date" class="datepicker form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( get_post_meta( $post->ID, 'start_date', true ) ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="start_date" readonly>
									<label  for="notice_content"><?php esc_html_e( 'Notice Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<?php wp_nonce_field( 'save_notice_admin_nonce' ); ?>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="notice_end_date" class="datepicker form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( get_post_meta( $post->ID, 'end_date', true ) ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="end_date" readonly>
									<label  for="notice_content"><?php esc_html_e( 'Notice End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-notice-for"><?php esc_html_e( 'Notice For', 'mjschool' ); ?></label>
							<select name="notice_for" id="mjschool-notice-for" class="mjschool-line-height-30px form-control notice_for_ajax">
								<option value="all"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
								<option value="teacher" <?php if ( $edit ) { echo selected( get_post_meta( $post->ID, 'notice_for', true ), 'teacher' );} ?> ><?php esc_html_e( 'Teacher', 'mjschool' ); ?></option>
								<option value="student" <?php if ( $edit ) { echo selected( get_post_meta( $post->ID, 'notice_for', true ), 'student' );} ?> ><?php esc_html_e( 'Student', 'mjschool' ); ?></option>
								<option value="parent" <?php if ( $edit ) { echo selected( get_post_meta( $post->ID, 'notice_for', true ), 'parent' );} ?> ><?php esc_html_e( 'Parent', 'mjschool' ); ?></option>
								<option value="supportstaff" <?php if ( $edit ) { echo selected( get_post_meta( $post->ID, 'notice_for', true ), 'supportstaff' ); } ?> ><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></option>
							</select>
						</div>
						<div class="col-md-6 input" id="mjschool-smgt-select-class">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?></label>
							<?php
							if ( $edit ) {
								$classval = get_post_meta( $post->ID, 'smgt_class_id', true );
							} elseif ( isset( $_POST['class_id'] ) ) {
								$classval = intval( wp_unslash($_POST['class_id']));
							} else {
								$classval = '';
							}
							?>
							<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control">
								<option value="all"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
								<?php
								foreach ( mjschool_get_all_class() as $classdata ) {
									?>
									<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php echo selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php
								}
								?>
							</select>
						</div>
						<?php if ( $school_type === 'school' ){ ?>
							<div class="col-md-6 input" id="smgt_select_section">
								<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
								<?php
								if ( $edit ) {
									$sectionval = get_post_meta( $post->ID, 'smgt_section_id', true );
								} elseif ( isset( $_POST['class_section'] ) ) {
									$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
								} else {
									$sectionval = '';
								}
								?>
								<select name="class_section" class="mjschool-line-height-30px form-control" id="class_section">
									<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
									<?php
									if ( $edit ) {
										foreach ( mjschool_get_class_sections( $classval ) as $sectiondata ) {
											?>
											<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
						<?php }?>
						<?php
						if ( ! $edit ) {
							?>
							<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-rtl-margin-top-15px">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div class="input-group mjschool-input-checkbox">
												<label class="mjschool-custom-top-label" for="chk_mjschool_sent_mail"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
												<input id="chk_mjschool_sent_mail" class="mjschool-check-box-input-margin" type="checkbox" <?php $smgt_mail_service_enable = 0; if ( $smgt_mail_service_enable ) { echo 'checked';} ?> value="1" name="smgt_mail_service_enable">
												&nbsp;<span><?php esc_html_e( 'Mail', 'mjschool' ); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-rtl-margin-top-15px">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div class="input-group mjschool-input-checkbox">
												<label class="mjschool-custom-top-label" for="chk_mjschool_sent"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
												<input id="chk_mjschool_sent" type="checkbox" <?php $mjschool_service_enable = 0; if ( $mjschool_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_service_enable">
												&nbsp;&nbsp;<span><?php esc_html_e( 'SMS', 'mjschool' ); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'notice';
				$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6">
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Notice', 'mjschool' ); } else { esc_html_e( 'Add Notice', 'mjschool' ); } ?>" name="save_notice" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
	?>
</div>