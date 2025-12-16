<?php

/**
 * Compose New Message (Email/SMS) Page.
 *
 * This file serves as the view/controller for the 'Compose Message' functionality
 * within the Mjschool message module. It allows administrators and permitted users
 * to send messages (both email and SMS) to different user roles (e.g., Students,
 * Teachers, Parents, Support Staff).
 *
 * It is primarily responsible for:
 *
 * 1. **Access Control**: Checking user role and permissions.
 * 2. **Form Handling**: Displaying the form for composing a message, which includes:
 * - Selecting a receiver role.
 * - Entering the email subject and body (using a WYSIWYG editor like TinyMCE).
 * - Entering the SMS text (if SMS service is enabled).
 * 3. **Submission Logic**: Processing the form submission.
 * - Sanitizing input data (subject, message body, roles).
 * - Checking if both email and/or SMS services are enabled via plugin options.
 * - Retrieving a list of users based on the selected role(s).
 * - Iterating through the recipient list to send the message using appropriate functions.
 * - Handling mail templates and shortcodes (e.g., `{{school_name}}`).
 * - Saving the message to the database table (`mjschool_message`).
 * 4. **UI/Scripts**: Implementing client-side validation and dynamic field display
 * based on the selected service (Email/SMS).
 * 5. **Custom Fields**: Integrating and displaying custom fields relevant to the 'message' module.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="demo"></div>
<?php
$school_type = get_option( "mjschool_custom_class");
$role = mjschool_get_user_role( get_current_user_id() );
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( isset( $_POST['save_message'] ) ) {
	$created_date                     = date( 'Y-m-d H:i:s' );
	$subject                          = sanitize_text_field( wp_unslash( $_POST['subject'] ) );
	$message_body                     = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
	$created_date                     = date( 'Y-m-d H:i:s' );
	$tablename                        = 'mjschool_message';
	$mjschool_service_enable = isset( $_REQUEST['mjschool_service_enable'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_service_enable'] ) ) : 0;
	$role                             = sanitize_text_field( wp_unslash( $_POST['receiver'] ) );
	if ( isset( $_POST['mjschool_message_mail_service_enable'] ) === '1' ) {
		$MailBody                  = get_option( 'mjschool_message_received_mailcontent' );
		$SchoolName                = get_option( 'mjschool_name' );
		$SubArr['{{school_name}}'] = $SchoolName;
		$SubArr['{{from_mail}}']   = mjschool_get_display_name( get_current_user_id() );
		$MailSub                   = mjschool_string_replacement( $SubArr, get_option( 'mjschool_message_received_mailsubject' ) );
	}
	if ( isset( $_REQUEST['class_id'] ) ) {
		$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
	}
	$role              = sanitize_text_field( wp_unslash( $_REQUEST['receiver'] ) );
	$class_id          = isset( $_REQUEST['class_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) : '';
	$class_section     = isset( $_REQUEST['class_section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ) : '';
	$selected_users    = isset( $_REQUEST['selected_users'] ) ? array_map( 'intval', (array) $_REQUEST['selected_users'] ) : array();
	$upload_docs_array = array();
	if ( ! empty( $_FILES['message_attachment']['name'] ) ) {
		$count_array = count( $_FILES['message_attachment']['name'] );
		for ( $a = 0; $a < $count_array; $a++ ) {
			foreach ( $_FILES['message_attachment'] as $image_key => $image_val ) {
				$document_array[ $a ] = array(
					'name'     => $_FILES['message_attachment']['name'][ $a ],
					'type'     => $_FILES['message_attachment']['type'][ $a ],
					'tmp_name' => $_FILES['message_attachment']['tmp_name'][ $a ],
					'error'    => $_FILES['message_attachment']['error'][ $a ],
					'size'     => $_FILES['message_attachment']['size'][ $a ],
				);
			}
		}
		foreach ( $document_array as $key => $value ) {
			$get_file_name = $document_array[ $key ]['name'];
			if ( ! empty( $value['name'] ) ) {
				$subject = isset($_POST['subject']) ? sanitize_text_field( wp_unslash($_POST['subject']) ) : '';
				$document = isset($value) ? $value : array();
				$upload_docs_array[] = mjschool_load_documets_new( $document, $document, $subject );
			}
		}
	}
	$upload_docs_array_filter = array_filter( $upload_docs_array );
	if ( ! empty( $upload_docs_array_filter ) ) {
		$attachment = implode( ',', $upload_docs_array_filter );
	} else {
		$attachment = '';
	}
	if ( ! empty( $selected_users ) ) {
		$post_id                   = wp_insert_post(
			array(
				'post_status'  => 'publish',
				'post_type'    => 'message',
				'post_title'   => $subject,
				'post_content' => $message_body,
			)
		);
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = 'message';
		$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $post_id );
		$reci_number               = array();
		$device_token              = array();
		foreach ( $selected_users as $user_id ) {
			$user_info = get_userdata( $user_id );
			if ( $user_id != get_current_user_id() ) {
				$reci_number[]  = '+' . mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) . get_user_meta( $user_id, 'mobile_number', true );
				$device_token[] = get_user_meta( $user_id, 'token_id', true );
			}
		}
		/* Start send push notification. */
			$title             = esc_html__( 'You have received new message', 'mjschool' ) . ' ' . sanitize_text_field( wp_unslash( $_POST['subject'] ) );
			$text              = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
		$notification_data = array(
			'registration_ids' => $device_token,
			'notification'     => array(
				'title' => $title,
				'body'  => $text,
				'type'  => 'Message',
			),
		);
		$json    = json_encode( $notification_data );
		$message = mjschool_send_push_notification( $json );
		/* End send push notification. */
		$class_id = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
		$result = add_post_meta( $post_id, 'message_for', $role );
		$result = add_post_meta( $post_id, 'smgt_class_id', $class_id );
		$result = add_post_meta( $post_id, 'message_attachment', $attachment );
		foreach ( $selected_users as $user_id ) {
			$user_info = get_userdata( $user_id );
			// Send sms notification.
			if ( $mjschool_service_enable ) {
				$message_content = sanitize_text_field( wp_unslash( $_POST['mjschool_template'] ) );
				$type            = 'Message';
				mjschool_send_mjschool_notification( $user_id, $type, $message_content );
			}
			$message_data = array(
				'sender'       => get_current_user_id(),
				'receiver'     => $user_id,
				'subject'      => $subject,
				'message_body' => $message_body,
				'date'         => $created_date,
				'post_id'      => $post_id,
				'status'       => 0,
			);
			mjschool_insert_record( $tablename, $message_data );
			$user_info = get_userdata( $user_id );
			if ( isset( $_POST['mjschool_message_mail_service_enable'] ) === '1' ) {
				$to                            = $user_info->user_email;
				$MesArr['{{receiver_name}}']   = mjschool_get_display_name( $user_id );
				$MesArr['{{message_content}}'] = $message_body;
				$MesArr['{{school_name}}']     = $SchoolName;
				$message                       = mjschool_string_replacement( $MesArr, $MailBody );
				$headers  = '';
				$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
				if ( ! empty( $upload_docs_array_filter ) ) {
					$mailattachment = array();
					foreach ( $upload_docs_array_filter as $attachment_data ) {
						$mailattachment[] = WP_CONTENT_DIR . '/uploads/school_assets/' . $attachment_data;
					}
					if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
						wp_mail( $to, $MailSub, $message, $headers, $mailattachment );
					}
				} elseif ( get_option( 'mjschool_mail_notification' ) === 1 ) {
					wp_mail( $to, $MailSub, $message, $headers );
				}
			}
		}
	} else {
		$user_list          = array();
		$class_list         = $class_id;
		$query_data['role'] = $role;
		$exlude_id          = mjschool_approve_student_list();
		$multi_class_id     = sanitize_text_field( wp_unslash( $_POST['multi_class_id'] ) );
		if ( isset( $_POST['class_selection_type'] ) ) {
			$class_selection_type = sanitize_text_field( wp_unslash( $_REQUEST['class_selection_type'] ) );
		} else {
			$class_selection_type = 'single';
		}
		if ( $role === 'student' ) {
			if ($class_selection_type === 'single' ) {
				$query_data['exclude'] = $exlude_id;
				if ($class_section) {
					$query_data['meta_key'] = 'class_section';
					$query_data['meta_value'] = $class_section;
					$query_data['meta_query'] = array(
						array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' )
					);
				} elseif ($class_list != '' ) {
					$query_data['meta_key'] = 'class_name';
					$query_data['meta_value'] = $class_list;
				}
			} else {
				$query_data['exclude'] = $exlude_id;
				$query_data['meta_query'] = array(
					array( 'key' => 'class_name', 'value' => $multi_class_id, 'compare' => 'IN' )
				);
			}
			
			$results = get_users( $query_data );
		}
		if ( $role === 'teacher' ) {
			if ( $class_selection_type === 'single' ) {
				if ( $class_list != '' ) {
					$teacher_list = mjschool_get_teacher_by_class_id($class_list);
					if ( $teacher_list ) {
						foreach ( $teacher_list as $teacher ) {
							$user_list[] = $teacher->teacher_id;
						}
					}
				} else {
					$results = get_users( $query_data );
				}
			} else {
				$teacher_list = mjschool_get_teacher_class_assignments_by_class_ids($multi_class_id);
				if ( $teacher_list ) {
					foreach ( $teacher_list as $teacher ) {
						$user_list[] = $teacher->teacher_id;
					}
				}
			}
		}
		if ( $role === 'supportstaff' ) {
			$results = get_users( $query_data );
		}
		if ( $role === 'parent' ) {
			if ( $class_selection_type === 'single' ) {
				if ( $class_list === '' ) {
					$results = get_users( $query_data );
				} else {
					
					$query_data['role'] = 'student';
					$query_data['exclude'] = $exlude_id;
					if ($class_section) {
						$query_data['meta_key'] = 'class_section';
						$query_data['meta_value'] = $class_section;
						$query_data['meta_query'] = array(
							array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' )
						);
					} elseif ($class_list != '' ) {
						$query_data['meta_key'] = 'class_name';
						$query_data['meta_value'] = $class_list;
					}
					
					$userdata = get_users( $query_data );
					foreach ( $userdata as $users ) {
						$parent = get_user_meta( $users->ID, 'parent_id', true );
						if ( ! empty( $parent ) ) {
							foreach ( $parent as $p ) {
								$user_list[] = $p;
							}
						}
					}
				}
			} else {
				$query_data['role'] = 'student';
				$query_data['exclude'] = $exlude_id;
				$query_data['meta_query'] = array(
					array( 'key' => 'class_name', 'value' => $multi_class_id, 'compare' => 'IN' )
				);
				$userdata = get_users( $query_data );
				foreach ( $userdata as $users ) {
					$parent = get_user_meta( $users->ID, 'parent_id', true );
					if ( ! empty( $parent ) ) {
						foreach ( $parent as $p ) {
							$user_list[] = $p;
						}
					}
				}
			}
		}
		if ( isset( $results ) ) {
			foreach ( $results as $user_datavalue ) {
				$user_list[] = $user_datavalue->ID;
			}
		}
		$user_data_list = array_unique( $user_list );
		if ( ! empty( $user_data_list ) ) {
			$post_id                   = wp_insert_post(
				array(
					'post_status'  => 'publish',
					'post_type'    => 'message',
					'post_title'   => $subject,
					'post_content' => $message_body,
				)
			);
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'message';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $post_id );
			$result                    = add_post_meta( $post_id, 'message_for', $role );
			if ( $class_selection_type === 'single' ) {
				$class_id = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
				$result = add_post_meta( $post_id, 'smgt_class_id', $class_id );
			} else {
				$result = add_post_meta( $post_id, 'smgt_class_id', implode( ',', $multi_class_id ) );
			}
			$result       = add_post_meta( $post_id, 'message_attachment', $attachment );
			$device_token = array();
			foreach ( $user_data_list as $user_id ) {
				if ( $user_id != get_current_user_id() ) {
					$device_token[]           = get_user_meta( $user_id, 'token_id', true );
					$user_info                = get_userdata( $user_id );
					$reciever_number          = '+' . mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) . get_user_meta( $user_id, 'mobile_number', true );
					$message_content          = sanitize_text_field( wp_unslash( $_POST['mjschool_template'] ) );
					$current_mjschool_service = get_option( 'mjschool_service' );
					if ( $mjschool_service_enable ) {
						if ( $current_mjschool_service === 'clickatell' ) {
							$clickatell = get_option( 'mjschool_clickatell_mjschool_service' );
							$to         = $reciever_number;
							$message    = str_replace( ' ', '%20', $message_content );
							$username   = $clickatell['username']; // clickatell username.
							$password   = $clickatell['password']; // clickatell password.
							$api_key    = $clickatell['api_key']; // clickatell apikey.
							$sender_id  = $clickatell['sender_id']; // clickatell sender_id.
							$baseurl    = 'http://api.clickatell.com';
							$ret        = file( $url );
							$sess       = explode( ':', $ret[0] );
							if ( $sess[0] === 'OK' ) {
								$sess_id = trim( $sess[1] ); // remove any whitespace.
								$url     = "$baseurl/http/sendmsg?session_id=$sess_id&to=$to&text=$message&from=$sender_id";
								$ret     = file( $url );
								$send    = explode( ':', $ret[0] );
							}
						}
						if ( $current_mjschool_service === 'msg91' ) {
							// MSG91.
							$mobile_number = get_user_meta( $user_id, 'mobile_number', true );
							$country_code  = '+' . mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) );
							$message       = $message_content; // Message Text.
							mjschool_msg91_send_mail_callback( $mobile_number, $message, $country_code );
						}
					}
					$message_data = array(
						'sender'       => get_current_user_id(),
						'receiver'     => $user_id,
						'subject'      => $subject,
						'message_body' => $message_body,
						'date'         => $created_date,
						'post_id'      => $post_id,
						'status'       => 0,
					);
					mjschool_insert_record( $tablename, $message_data );
					$user_info = get_userdata( $user_id );
					if ( isset( $_POST['mjschool_message_mail_service_enable'] ) === '1' ) {
						$to                            = $user_info->user_email;
						$MesArr['{{receiver_name}}']   = mjschool_get_display_name( $user_id );
						$MesArr['{{message_content}}'] = $message_body;
						$MesArr['{{school_name}}']     = $SchoolName;
						$message                       = mjschool_string_replacement( $MesArr, $MailBody );
						$headers  = '';
						$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
						$headers .= "MIME-Version: 1.0\r\n";
						$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
						if ( ! empty( $upload_docs_array_filter ) ) {
							$mailattachment = array();
							foreach ( $upload_docs_array_filter as $attachment_data ) {
								$mailattachment[] = WP_CONTENT_DIR . '/uploads/school_assets/' . $attachment_data;
							}
							
							if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
								wp_mail( $to, $MailSub, $message, $headers, $mailattachment );
							}
						} elseif ( get_option( 'mjschool_mail_notification' ) === 1 ) {
							wp_mail( $to, $MailSub, $message, $headers );
						}
					}
				}
			}
			/* Start Send Push Notification. */
			$title             = esc_html__( 'You have received new message', 'mjschool' ) . ' ' . sanitize_text_field( stripslashes( $_POST['subject'] ) );
			$text              = sanitize_textarea_field( stripslashes( $_POST['message_body'] ) );
			$notification_data = array(
				'registration_ids' => $device_token,
				'notification'     => array(
					'title' => $title,
					'body'  => $text,
					'type'  => 'Message',
				),
			);
			$json              = json_encode( $notification_data );
			$message           = mjschool_send_push_notification( $json );
			/* Start Send Push Notification. */
		}
	}
	if ( isset( $result ) ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=message&tab=compose&message=1' ) );
		die();
	}
}
if ( isset( $_REQUEST['message'] ) ) {
	$message = sanitize_text_field( wp_unslash( $_REQUEST['message'] ) );
	if ( $message === 1 ) { ?>
		
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible mjschool-margin-top-15px" role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
			<?php esc_html_e( 'Message Sent Successfully', 'mjschool' );	?>
		</div>
		<?php
	} elseif ($message === 2 ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible mjschool-margin-top-15px" role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
			
			<?php esc_html_e( 'Message deleted successfully', 'mjschool' ); ?>
		</div>
		<?php
	}
}
?>
<div class="overflow-hidden mjschool-mailbox-content">
	<h2>
		<?php
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action']) ) === 'edit' ) {
			echo esc_html__( 'Edit Message', 'mjschool' );
			$edit      = 1;
			$exam_data = mjschool_get_exam_by_id( sanitize_text_field( wp_unslash( $_REQUEST['exam_id'] ) ) );
		}
		?>
	</h2>
	<form name="class_form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-message-form" enctype="multipart/form-data">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<div class="form-body mjschool-user-form"><!--User form. -->
			<div class="row"><!--Row. -->
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="to"><?php esc_html_e( 'Message To', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select name="receiver" class="mjschool-line-height-30px form-control validate[required] text-input" id="send_to">
						<?php
						if ( $school_obj->role === 'parent' ) {
							if ( get_option( 'mjschool_parent_send_message' ) === 1 ) {
								?>
								<option value="student"><?php esc_html_e( 'Students', 'mjschool' ); ?></option>
								<?php
							}
						} elseif ( $school_obj->role === 'student' ) {
							if ( get_option( 'mjschool_student_send_message' ) === 1 ) {
								?>
								<option value="student"><?php esc_html_e( 'Student', 'mjschool' ); ?></option>
								<?php
							}
						} else {
							?>
							<option value="student"><?php esc_html_e( 'Students', 'mjschool' ); ?></option>
							<?php
						}
						?>
						<option value="teacher"><?php esc_html_e( 'Teachers', 'mjschool' ); ?></option>
						<?php if ( $school_obj->role != 'student' && $school_obj->role != 'parent' ) { /* Student should not send SMS to parents. */ ?>
							<option value="parent"><?php esc_html_e( 'Parents', 'mjschool' ); ?></option>
							<?php
						}
						?>
						<option value="supportstaff"><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></option>
						<?php
						if ( $school_obj->role != 'student' ) {
							?>
							<option value="administrator"><?php esc_html_e( 'Admin', 'mjschool' ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<?php
				if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
					?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 class_selection input">
						<label class="ml-1 mjschool-custom-top-label top" for="to"><?php esc_html_e( 'Class Selection Type', 'mjschool' ); ?></label>
						<select name="class_selection_type" class="mjschool-line-height-30px form-control validate[required] text-input class_selection_type">
							<option value="single"><?php esc_html_e( 'Single', 'mjschool' ); ?></option>
							<option value="multiple"><?php esc_html_e( 'Multiple', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-multiple-class-div mjchool_display_none">
						<div class="col-sm-12 mjschool-msg-multiple mjschool-multiple-select mjschool-multiselect-validation1">
							<select name="multi_class_id[]" class="mjschool-line-height-30px form-control validate[required]" id="selected_class" multiple="true">
								<?php
								foreach ( mjschool_get_all_class() as $classdata ) {
									?>
									<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php
								}
								?>
							</select>
							<span class="mjschool-multiselect-label">
								<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="required">*</span></label>
							</span>
						</div>
					</div>
					<?php
				}
				?>
				<div id="mjschool-smgt-select-class" class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input class_list_id mjschool-single-class-div">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool_template"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<?php
					$result = array();
					$role   = mjschool_get_user_role( get_current_user_id() );
					if ( $role === 'parent' ) {
						$class_id   = array();
						$parentdata = get_user_meta( get_current_user_id(), 'child', true );
						foreach ( $parentdata as $student_key => $student_id ) {
							$class_id[] = get_user_meta( $student_id, 'class_name', true );
						}
						$class_id_arr = array_unique( $class_id );
					}
					if ( $role === 'student' ) {
						$student_class_id   = get_user_meta( get_current_user_id(), 'class_name', true );
						$student_class_name = mjschool_get_class_by_id( $student_class_id );
					}
					if ( $role === 'teacher' ) {
						$classdatas = array_filter( mjschool_get_all_teacher_data( get_current_user_id() ) );
						foreach ( $classdatas as $class_key => $class_val ) {
							$result[] = mjschool_get_class_by_id( $class_val->class_id );
						}
					}
					?>
					<select name="class_id" id="class_list_id" class="mjschool-line-height-30px form-control validate[required]">
						<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
						<?php
						if ( $role === 'teacher' ) {
							foreach ( $result as $key => $value ) {
								?>
								<option value="<?php print esc_attr( $value->class_id ); ?>"><?php print esc_html( $value->class_name ); ?></option>
							<?php }
						} elseif ( $role === 'student' ) {
							print '<option value="' . esc_attr( $student_class_id ) . '"> ' . esc_html( $student_class_name->class_name ) . ' </option>';
						} elseif ( $role === 'parent' ) {
							foreach ( $class_id_arr as $key => $class_id_val ) {
								print '<option value="' . esc_attr( $class_id_val ) . '">' . esc_html( mjschool_get_class_name_by_id( $class_id_val ) ) . '</option>';
							}
						} else {
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<?php
				if ( $school_obj->role === 'parent' ) {
					$class_selection_id_css = 'display:none';
				} else {
					$class_selection_id_css = 'display:auto';
				}
				if ( $school_obj->role != 'parent' && $school_obj->role != 'student' ) {
					if ( $school_type === 'school' ){
						?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input class_section_id" style="<?php echo esc_attr( $class_selection_id_css ); ?>">
							<label class="ml-1 mjschool-custom-top-label top" for="class_name"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
							<?php
							if ( isset( $_POST['class_section'] ) ) {
								$sectionval = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
							} else {
								$sectionval = '';
							}
							?>
							<select name="class_section" class="mjschool-line-height-30px form-control" id="class_section_id">
								<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
								<?php
								if ( $edit ) {
									foreach ( mjschool_get_class_sections( $user_info->class_name ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<?php
					}
				}
				?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-single-class-div mjschool-support-staff-user-div input">
					<div id="messahe_test"></div>
					<div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
						<span class="user_display_block">
							<select name="selected_users[]" id="selected_users" class="form-control" multiple="multiple">
								<?php
								if ( $role === 'teacher' ) {
									$student_list = mjschool_get_teacher_class_student( get_current_user_id() );
								} elseif ( $role === 'student' ) {
									if ( get_option( 'mjschool_student_send_message' ) === 1 ) {
										$std_list = mjschool_get_student_by_class_id( $student_class_id );
										foreach ( $std_list as $std_list_ley => $std_list_val ) {
											if ( $std_list_val->ID != get_current_user_id() ) {
												echo '<option value="' . esc_attr( $std_list_val->ID ) . '">' . esc_html( $std_list_val->display_name ) . '</option>';
											}
										}
									} else {
										$query_data['role'] = 'teacher';
										$student_list       = get_users( $query_data );
									}
								} elseif ( $role === 'parent' ) {
									if ( get_option( 'mjschool_parent_send_message' ) === 1 ) {
																	
										foreach ($class_id_arr as $key => $class_id_val) {
											$query_data['role'] = 'student';
											$exlude_id = mjschool_approve_student_list();
											$query_data['meta_key'] = 'class_name';
											$query_data['meta_value'] = $class_id_val;
											$results = get_users($query_data);
											foreach ($results as $userdata) {
												echo '<option value="' . esc_attr($userdata->ID) . '">' . esc_html( $userdata->display_name) . '</option>';
											}
										}
									} else {
										$query_data['role'] = 'teacher';
										$student_list       = get_users( $query_data );
									}
								} else {
									$student_list = mjschool_get_all_student_list();
								}
								if ( ! empty( $student_list ) ) {
									foreach ( $student_list  as $retrive_data ) {
										if ( $retrive_data->ID != get_current_user_id() ) {
											echo '<option value="' . esc_attr( $retrive_data->ID ) . '">' . esc_html( $retrive_data->display_name ) . '</option>';
										}
									}
								}
								?>
							</select>
						</span>
						<span class="mjschool-multiselect-label">
							<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Users', 'mjschool' ); ?><span class="required">*</span></label>
						</span>
					</div>
				</div>
				<div id="class_student_list" class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="subject" class="form-control validate[required,custom[description_validation]] text-input" maxlength="100" type="text" name="subject">
							<label  for="subject"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="message_body" id="message_body" maxlength="500" class="mjschool-textarea-height-60px form-control validate[required,custom[description_validation]] text-input"></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="subject"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-attachment-div">
					<div class="row">
						<div class="col-md-10">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" for="photo"><?php esc_html_e( 'Attachment ', 'mjschool' ); ?></label>
									<div class="col-sm-12">
										<input class="file_line_height_26px col-md-12 form-control file input-file" name="message_attachment[]" type="file" />
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-2 col-sm-2 col-xs-12">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_new_attachment()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
						</div>
					</div>
				</div>
				<?php
				if ( $role === 'teacher' || $role === 'supportstaff' ) {
					?>
					<div class="col-sm-3 col-md-3 mb-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label" for="enable"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
										<input type="checkbox" value="1" name="mjschool_message_mail_service_enable">
										<label> <?php esc_html_e( 'Enable', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-2 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label" for="enable"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
										<input id="chk_mjschool_sent" type="checkbox" value="1" name="mjschool_service_enable">
										<label> <?php esc_html_e( 'Enable', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-message-none" id="mjschool-message-sent">
						<div class="form-group input">
							<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
								<textarea name="mjschool_template" class="mjschool-textarea-height-47px form-control validate[required]" maxlength="160"></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="mjschool_template"><?php esc_html_e( 'SMS Text', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div><!--Row. -->
		</div><!--User form. -->
		<?php
		// --------- Get module-wise custom field data. --------------//
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = 'message';
		$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
		?>
		<div class="form-body mjschool-user-form mt-3"><!--User form. -->
			<div class="row"><!--Row. -->
				<div class="col-sm-6">
					<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Message', 'mjschool' ); } else { esc_html_e( 'Send Message', 'mjschool' ); } ?>" name="save_message" class="btn btn-success mjschool-save-btn mjschool-save-message-selected-user" />
				</div>
			</div>
		</div>
	</form>
</div>