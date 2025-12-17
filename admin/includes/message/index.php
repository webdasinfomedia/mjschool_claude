<?php
/**
 * Message Management Module.
 *
 * Handles all backend operations and admin UI for the Message Management module
 * in the MJSchool plugin. This file manages the complete lifecycle of messages
 * — including composing, sending, viewing, and listing messages across tabs
 * like Inbox, Sentbox, and All Messages.
 *
 * Key Features:
 * - Implements CRUD access control based on user roles and permissions.
 * - Provides message composition with attachments, class, and user selection.
 * - Supports sending messages to multiple users, roles, or class groups.
 * - Integrates push notifications, email, and SMS service for message delivery.
 * - Handles file uploads securely with type and size validation.
 * - Uses AJAX-driven dynamic multiselect fields for selecting users and classes.
 * - Validates form data with jQuery Validation Engine.
 * - Displays messages in tabbed layout (Inbox, Sent, View All, Compose, etc.).
 * - Utilizes WordPress APIs for post, meta, and mail operations.
 * - Includes localized strings and secure nonce handling for safe submission.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/message
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'message' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			if ( 'message' === $user_access['page_link'] && ( $action === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'message' === $user_access['page_link'] && ( $action === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'message' === $user_access['page_link'] && ( $action === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
if ( isset( $_POST['save_message'] ) ) {
	// Verify nonce for message form submission.
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'mjschool_save_message' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	$created_date                     = current_time( 'Y-m-d H:i:s' );
	$subject                          = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message_body                     = isset( $_POST['message_body'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) ) : '';
	$tablename                        = 'mjschool_message';
	$mjschool_service_enable          = isset( $_REQUEST['mjschool_service_enable'] ) ? intval( wp_unslash( $_REQUEST['mjschool_service_enable'] ) ) : 0;
	if ( ! empty( $_POST['mjschool_message_mail_service_enable'] ) && intval( wp_unslash( $_POST['mjschool_message_mail_service_enable'] ) ) === 1 ) {
		$mjschool_role             = isset( $_POST['receiver'] ) ? sanitize_text_field( wp_unslash( $_POST['receiver'] ) ) : '';
		$MailBody                  = get_option( 'mjschool_message_received_mailcontent' );
		$SchoolName                = get_option( 'mjschool_name' );
		$SubArr['{{school_name}}'] = $SchoolName;
		$SubArr['{{from_mail}}']   = mjschool_get_display_name( get_current_user_id() );
		$MailSub                   = mjschool_string_replacement( $SubArr, get_option( 'mjschool_message_received_mailsubject' ) );
	}
	$mjschool_role     = isset( $_REQUEST['receiver'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['receiver'] ) ) : '';
	$class_id          = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : '';
	$class_section     = isset( $_REQUEST['class_section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ) : '';
	$selected_users    = isset( $_REQUEST['selected_users'] ) && is_array( $_REQUEST['selected_users'] ) ? array_map( 'intval', wp_unslash( $_REQUEST['selected_users'] ) ) : array();
	$selected_users    = array_unique( $selected_users );
	$upload_docs_array = array();
	if ( ! empty( $_FILES['message_attachment']['name'] ) ) {
		$count_array = count( $_FILES['message_attachment']['name'] );
		for ( $a = 0; $a < $count_array; $a++ ) {
			foreach ( $_FILES['message_attachment'] as $image_key => $image_val ) {
				$document_array[ $a ] = array(
					'name'     => sanitize_file_name( $_FILES['message_attachment']['name'][ $a ] ),
					'type'     => sanitize_mime_type( $_FILES['message_attachment']['type'][ $a ] ),
					'tmp_name' => sanitize_text_field( $_FILES['message_attachment']['tmp_name'][ $a ] ),
					'error'    => intval( $_FILES['message_attachment']['error'][ $a ] ),
					'size'     => intval( $_FILES['message_attachment']['size'][ $a ] ),
				);
			}
		}
		foreach ( $document_array as $key => $value ) {
			$get_file_name = $document_array[ $key ]['name'];
			if ( ! empty( $value['name'] ) ) {
				$upload_docs_array[] = mjschool_load_documets_new( $value, $value, $subject );
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
		$result                    = add_post_meta( $post_id, 'message_for', $mjschool_role );
		$result                    = add_post_meta( $post_id, 'smgt_class_id', $class_id );
		$result                    = add_post_meta( $post_id, 'message_attachment', $attachment );
		$m                         = 0;
		$reci_number               = array();
		$device_token              = array();
		foreach ( $selected_users as $user_id ) {
			$user_info      = get_userdata( $user_id );
			$reci_number[]  = '+' . mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) . get_user_meta( $user_id, 'mobile_number', true );
			$device_token[] = get_user_meta( $user_id, 'token_id', true );
		}
		// Start Send Push Notification.
		$title             = esc_html__( 'You have received new message', 'mjschool' ) . ' ' . $subject;
		$text              = $message_body;
		$notification_data = array(
			'registration_ids' => $device_token,
			'data'             => array(
				'title' => $title,
				'body'  => $text,
				'type'  => 'Message',
			),
		);
		$json    = wp_json_encode( $notification_data );
		$message = mjschool_send_push_notification( $json );
		// End Send Push Notification.
		foreach ( $selected_users as $user_id ) {
			$message_content          = isset( $_POST['mjschool_template'] ) ? sanitize_text_field( wp_unslash( $_POST['mjschool_template'] ) ) : '';
			$current_mjschool_service = get_option( 'mjschool_service' );
			// Send SMS Notification.
			if ( $mjschool_service_enable ) {
				$type = 'Message';
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
			$to        = $user_info->user_email;
			$headers  = '';
			$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
			if ( ! empty( $_POST['mjschool_message_mail_service_enable'] ) && intval( wp_unslash( $_POST['mjschool_message_mail_service_enable'] ) ) === 1 ) {
				$MesArr['{{receiver_name}}']   = mjschool_get_display_name( $user_id );
				$MesArr['{{message_content}}'] = $message_body;
				$MesArr['{{school_name}}']     = $SchoolName;
				$message                       = mjschool_string_replacement( $MesArr, $MailBody );
				if ( ! empty( $upload_docs_array_filter ) ) {
					$mailattachment = array();
					foreach ( $upload_docs_array_filter as $attachment_data ) {
						$mailattachment[] = WP_CONTENT_DIR . '/uploads/school_assets/' . $attachment_data;
					}
					
					wp_mail( $to, $MailSub, $message, $headers, $mailattachment );
				} else {
					wp_mail( $to, $MailSub, $message, $headers );
				}
			}
			++$m;
		}
	} else {
		$user_list                = array();
		$class_list               = $class_id;
		$query_data['role']       = $mjschool_role;
		$exlude_id                = mjschool_approve_student_list();
		$multi_class_id           = isset( $_POST['multi_class_id'] ) && is_array( $_POST['multi_class_id'] ) ? array_map( 'intval', wp_unslash( $_POST['multi_class_id'] ) ) : array();
		$class_selection_type     = isset( $_POST['class_selection_type'] ) ? sanitize_text_field( wp_unslash( $_POST['class_selection_type'] ) ) : '';
		if ( $mjschool_role === 'student' ) {
			if ( $class_selection_type === 'single' ) { 
				$query_data['exclude'] = $exlude_id;
				if ( $class_section ) {
					$query_data['meta_key'] = 'class_section';
					$query_data['meta_value'] = $class_section;
					$query_data['meta_query'] = array(
						array( 'key' => 'class_name', 'value' => intval( $class_list ), 'compare' => '=' )
					);
				} elseif ( $class_list != '' ) {
					$query_data['meta_key'] = 'class_name';
					$query_data['meta_value'] = intval( $class_list );
				}
			} else {
				$query_data['exclude'] = $exlude_id;
				$query_data['meta_query'] = array(
					array( 'key' => 'class_name', 'value' => $multi_class_id, 'compare' => 'IN' )
				);
				
			}
			$results = get_users( $query_data );
		}
		if ( $mjschool_role === 'teacher' ) {
			if ( $class_selection_type === 'single' ) {
				if ( $class_list != '' ) {
					global $wpdb;
					$table_mjschool_teacher_class = $wpdb->prefix . 'mjschool_teacher_class';
					$query                        = $wpdb->prepare( "SELECT * FROM $table_mjschool_teacher_class WHERE class_id = %d", $class_list );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$teacher_list = $wpdb->get_results( $query );
					if ( $teacher_list ) {
						foreach ( $teacher_list as $teacher ) {
							$user_list[] = $teacher->teacher_id;
						}
					}
				} else {
					$results = get_users( $query_data );
				}
			} else {
				global $wpdb;
				$table_mjschool_teacher_class = $wpdb->prefix . 'mjschool_teacher_class';
				// Prepare the placeholders for the IN clause.
				$placeholders = implode( ',', array_fill( 0, count( $multi_class_id ), '%d' ) );
				// Prepare the query with placeholders.
				$query = $wpdb->prepare( "SELECT * FROM $table_mjschool_teacher_class WHERE class_id IN ($placeholders)", ...$multi_class_id );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$teacher_list = $wpdb->get_results( $query );
				if ( $teacher_list ) {
					foreach ( $teacher_list as $teacher ) {
						$user_list[] = $teacher->teacher_id;
					}
				}
			}
		}
		if ( $mjschool_role === 'supportstaff' ) {
			$results = get_users( $query_data );
		}
		if ( $mjschool_role === 'parent' ) {
			if ( $class_selection_type === 'single' ) {
				if ( $class_list === '' ) {
					$results = get_users( $query_data );
				} else {
					$query_data['role'] = 'student';
					
					$query_data['exclude'] = $exlude_id;
					if ( $class_section ) {
						$query_data['meta_key'] = 'class_section';
						$query_data['meta_value'] = $class_section;
						$query_data['meta_query'] = array(
							array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' )
						);
					} elseif ( $class_list != '' ) {
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
					$parent_data = get_user_meta( $users->ID, 'parent_id', true );
					if ( ! empty( $parent_data ) ) {
						foreach ( $parent_data as $p_data ) {
							$user_list[] = $p_data;
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
			$post_id  = wp_insert_post(
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
			$result                    = add_post_meta( $post_id, 'message_for', $mjschool_role );
			if ( $class_selection_type === 'single' ) {
				$result = add_post_meta( $post_id, 'smgt_class_id', $class_id );
			} else {
				$result = add_post_meta( $post_id, 'smgt_class_id', implode( ',', $multi_class_id ) );
			}
			$result      = add_post_meta( $post_id, 'message_attachment', $attachment );
			$reci_number = array();
			foreach ( $user_data_list as $user_id ) {
				$user_info     = get_userdata( $user_id );
				$reci_number[] = '+' . mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) . get_user_meta( $user_id, 'mobile_number', true );
			}
			$device_token = array();
			foreach ( $user_data_list as $user_id ) {
				$message_content = isset( $_POST['mjschool_template'] ) ? sanitize_text_field( wp_unslash( $_POST['mjschool_template'] ) ) : '';
				$type            = 'Message';
				// Send SMS Notification.
				if ( $mjschool_service_enable ) {
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
				$headers  = '';
				$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
				if ( ! empty( $_POST['mjschool_message_mail_service_enable'] ) && intval( wp_unslash( $_POST['mjschool_message_mail_service_enable'] ) ) === 1 ) {
					$to                            = $user_info->user_email;
					$MesArr['{{receiver_name}}']   = mjschool_get_display_name( $user_id );
					$MesArr['{{message_content}}'] = $message_body;
					$MesArr['{{school_name}}']     = $SchoolName;
					$message                       = mjschool_string_replacement( $MesArr, $MailBody );
					if ( ! empty( $upload_docs_array_filter ) ) {
						$mailattachment = array();
						foreach ( $upload_docs_array_filter as $attachment_data ) {
							$mailattachment[] = WP_CONTENT_DIR . '/uploads/school_assets/' . $attachment_data;
						}
						wp_mail( $to, $MailSub, $message, $headers, $mailattachment );
					} else {
						wp_mail( $to, $MailSub, $message, $headers );
					}
				}
			}
			// Start Send Push Notification.
			$title             = esc_html__( 'You have received new message', 'mjschool' ) . ' ' . $subject;
			$text              = $message_body;
			$notification_data = array(
				'registration_ids' => $device_token,
				'data'             => array(
					'title' => $title,
					'body'  => $text,
					'type'  => 'Message',
				),
			);
			$json    = wp_json_encode( $notification_data );
			$message = mjschool_send_push_notification( $json );
			// End Send Push Notification.
		}
	}
}
if ( isset( $result ) ) {
	$nonce = wp_create_nonce( 'mjschool_message_tab' );
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_message&tab=sentbox&_wpnonce=' . $nonce . '&message=1' ) );
	exit;
}
?>
<?php $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'inbox'; ?>
<div class="mjschool-page-inner"><!--Mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!--Mjschool-main-list-margin-15px.-->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Message Sent Successfully!', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Message Deleted Successfully', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Message', 'mjschool' );
				break;
		}
		?>
		<div class="row"><!--Row.-->
			<?php
			if ( $message ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php echo esc_html( $message_string ); ?></p>
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
				</div>
				<?php
			}
			?>
			<div class="col-md-12 mjschool-custom-padding-0"><!-- Start Col-md-12 Mjschool-custom-padding-0.-->
				<?php
				$nonce       = wp_create_nonce( 'mjschool_message_tab' );
				$current_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : '';
				?>
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per list-unstyled mjschool-mailbox-nav">
					<li <?php if ( ! isset( $_REQUEST['tab'] ) || ( $current_tab === 'inbox' ) ) { ?> class="active"<?php } ?>>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=inbox&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-inbox-tab"><i class="fas fa-inbox"></i> <?php esc_html_e( 'Inbox', 'mjschool' ); ?><span class="mjschool-inbox-count-number badge badge-success  pull-right ms-1"><?php echo esc_html( mjschool_count_unread_message( get_current_user_id() ) ); ?></span></a>
					</li>
					<li <?php if ( $current_tab === 'sentbox' ) { ?> class="active"<?php } ?>>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=sentbox&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab"><i class="fass fa-sign-out-alt"></i><?php esc_html_e( 'Sent', 'mjschool' ); ?></a>
					</li>
					<li <?php if ( $current_tab === 'compose' ) { ?> class="active"<?php } ?>>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=compose&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab"><?php esc_html_e( 'Compose', 'mjschool' ); ?></a>
					</li>	   
				</ul>
			</div><!-- End Col-md-12 Mjschool-custom-padding-0.-->
			<?php
			if ( $current_tab === 'sentbox' ) {
				require_once MJSCHOOL_ADMIN_DIR . '/message/sendbox.php';
			}
			if ( ! isset( $_REQUEST['tab'] ) || ( $current_tab === 'inbox' ) ) {
				require_once MJSCHOOL_ADMIN_DIR . '/message/inbox.php';
			}
			if ( $current_tab === 'compose' ) {
				require_once MJSCHOOL_ADMIN_DIR . '/message/compose-email.php';
			}
			if ( $current_tab === 'view_message' ) {
				require_once MJSCHOOL_ADMIN_DIR . '/message/view-message.php';
			}
			?>
		</div><!--Row.-->
	</div><!--Mjschool-main-list-margin-15px.-->
</div><!-- Page-inner. -->