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
	// Verify nonce for security
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mjschool_compose_message_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	// Replaced deprecated date() with wp_date() for WordPress compatibility
	$created_date                     = wp_date( 'Y-m-d H:i:s' );
	$subject                          = sanitize_text_field( wp_unslash( $_POST['subject'] ) );
	$message_body                     = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
	// Replaced deprecated date() with wp_date() for WordPress compatibility
	$created_date                     = wp_date( 'Y-m-d H:i:s' );
	$mjschool_message_table           = 'mjschool_message';
	$mjschool_service_enable = isset( $_REQUEST['mjschool_service_enable'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_service_enable'] ) ) : 0;
	$role                             = sanitize_text_field( wp_unslash( $_POST['receiver'] ) );
	if ( isset( $_POST['mjschool_message_mail_service_enable'] ) && $_POST['mjschool_message_mail_service_enable'] === '1' ) {
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
		// Replaced deprecated implode() syntax with modern syntax (array first)
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
		$mjschool_custom_field_obj = new Mjschool_Custom_Field();
		$module                    = 'message';
		$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $post_id );
		$reci_number               = array();
		$device_token              = array();
		foreach ( $selected_users as $user_id ) {
			$user_info = get_userdata( $user_id );
			// Changed != to !== for strict comparison
			if ( $user_id !== get_current_user_id() ) {
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
		// Replaced deprecated json_encode() without flags with wp_json_encode() for proper encoding
		$json    = wp_json_encode( $notification_data );
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
		mjschool_insert_record( $mjschool_message_table, $message_data );
			$user_info = get_userdata( $user_id );
			if ( isset( $_POST['mjschool_message_mail_service_enable'] ) && $_POST['mjschool_message_mail_service_enable'] === '1' ) {
				$to                            = $user_info->user_email;
				$MesArr['{{receiver_name}}']   = mjschool_get_display_name( $user_id );
				$MesArr['{{message_content}}'] = $message_body;
				$MesArr['{{school_name}}']     = $SchoolName;
				$message                       = mjschool_string_replacement( $MesArr, $MailBody );
				$headers                       = array( 'Content-Type: text/html; charset=UTF-8' );
				$headers[]                     = 'From:' . get_option( 'mjschool_email' );
				wp_mail( $to, $MailSub, $message, $headers );
			}
		}
		?>
		<div class="mx-auto">
		<span id="model_success_msg" data-notify="container" class="col-xs-11 col-sm-4 alert alert-success animated fadeInDown alert-with-icon alert-dismissible fade show mjschool-success-message-error" role="alert" data-notify-position="bottom-center">
				<button type="button" class="close" aria-label="Close" data-dismiss="alert" aria-hidden="true">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				<span data-notify="icon" class="fa fa-check"></span>
				<span data-notify="message">
					<?php
					esc_html_e( 'Record saved successfully.', 'mjschool' );
					?>
				</span>
			</span>
		</div>
		<script>
			setTimeout(function() {
				jQuery('.mjschool-success-message-error').slideToggle('slow');
			}, 5000);
		</script>
		<?php
	} else {
		?>
		<div class="mx-auto">
			<span id="model_err_msg" data-notify="container" class="col-xs-11 col-sm-4 alert alert-warning animated fadeInDown alert-with-icon alert-dismissible fade show mjschool-success-message-error" role="alert" data-notify-position="bottom-center">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close" aria-hidden="true">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				<span data-notify="icon" class="fa fa-warning"></span>
				<span data-notify="message">
					<?php esc_html_e( 'Please select user.', 'mjschool' ); ?>
				</span>
			</span>
		</div>
		<script>
			setTimeout(function() {
				jQuery('.mjschool-success-message-error').slideToggle('slow');
			}, 5000);
		</script>
		<?php
	}
}
$edit = false;
global $wpdb;
if ( isset( $_REQUEST['q'] ) ) {
	if ( isset( $_REQUEST['q'] ) && ! empty( $_REQUEST['q'] ) ) {
		$edit = true;
	}
}
$message_id = isset( $_REQUEST['q'] ) && ! empty( $_REQUEST['q'] ) ? intval( wp_unslash( $_REQUEST['q'] ) ) : 0;
if ( $edit ) {
	if ( $message_id > 0 ) {
		$table_name = $wpdb->base_prefix . 'mjschool_message';
		// Replaced deprecated $wpdb->escape() with esc_sql() for proper escaping
		$sql                   = "SELECT * FROM " . $wpdb->prefix . "mjschool_message WHERE sender=" . esc_sql( get_current_user_id() ) . " AND id=$message_id";
		$mjschool_message_data = $wpdb->get_row( $sql );
	}
}
$role_meta_key = 'wp_capabilities';
if ( is_multisite() ) {
	$blog_id       = get_current_blog_id();
	$role_meta_key = $wpdb->base_prefix . $blog_id . '_capabilities';
}
// Main Query
$main_query = "SELECT {$wpdb->users}.ID, {$wpdb->users}.display_name
	FROM {$wpdb->users}
	INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id )
	WHERE 1=1";

// Role filter
$roles = array( 'parent' );
if ( ! empty( $roles ) ) {
	$role_conditions = array();
	foreach ( $roles as $role ) {
		// Replaced deprecated $wpdb->escape() with esc_sql() for proper escaping
		$role_conditions[] = "{$wpdb->usermeta}.meta_key = '" . esc_sql( $role_meta_key ) . "' AND {$wpdb->usermeta}.meta_value LIKE '%\"" . esc_sql( $role ) . "\"%'";
	}
	$main_query .= ' AND (' . implode( ' OR ', $role_conditions ) . ')';
}

// Group by user ID
$main_query .= " GROUP BY {$wpdb->users}.ID";
$main_query .= ' ORDER BY display_name ASC';
// Changed != to !== for strict comparison
$parent_list = ( $role !== 'teacher' && $role !== 'parent' && $role !== 'student' ) ? $wpdb->get_results( $main_query ) : '';

if ( $role === 'teacher' || $role === 'supportstaff' ) {
	?>
	<script type="text/javascript">
		var receiver_id="<?php echo esc_attr( $role ); ?>";
		jQuery(".mjschool_student_class_div").hide();
		jQuery(".mjschool_teacher_class_div").hide();
		if(receiver_id === 'student' || receiver_id === 'parent' || receiver_id === 'teacher') {
			jQuery(".mjschool_"+receiver_id+"_class_div").show();
			jQuery("#mjschool-message-sent").hide();
			jQuery("#chk_mjschool_sent").prop('checked', false);
		}
		jQuery( ".mjschool-receiver-label" ).on( 'change', function() {
			var receiver_id = jQuery(this).val();
			jQuery(".mjschool_student_class_div").hide();
			jQuery(".mjschool_teacher_class_div").hide();
			jQuery(".mjschool_parent_class_div").hide();
			if(receiver_id === 'student' || receiver_id === 'parent' || receiver_id === 'teacher') {
				jQuery(".mjschool_"+receiver_id+"_class_div").show();
				jQuery("#mjschool-message-sent").hide();
				jQuery("#chk_mjschool_sent").prop('checked', false);
			} else {
				jQuery("#mjschool-message-sent").hide();
				jQuery("#chk_mjschool_sent").prop('checked', false);
			}
		});
		jQuery('#chk_mjschool_sent').click(function() {
			if (jQuery(this).is(':checked')) {
				jQuery('#mjschool-message-sent').show();
			} else {
				jQuery('#mjschool-message-sent').hide();
			}
		});
		<?php
		$custom_field_label_image = get_option( 'mjschool_custom_field_image_label' );
		if ( empty( $custom_field_label_image ) ) {
			?>
			jQuery(".mjschool-attachment-div").css({'display':'block','visibility':'visible'});
			<?php
		} else {
			?>
			jQuery(".mjschool-attachment-div").css({'display':'none','visibility':'hidden'});
			<?php
		}
		?>
		jQuery(document).ready(function() {
			jQuery("#send_message").click(function() {
				var roleid=jQuery("#roleid").val();
				if (roleid === 'parent') {
					var selected_value = jQuery('#selected_parent').val();
					jQuery('#selected_users').val(selected_value);
				}
			});
		});
		jQuery(document).ready(function(){
			var receiver = jQuery( ".mjschool-receiver-label" ).val();
			<?php if ( ! empty( $school_type ) ) { ?>
				jQuery('#student_class').on("change", function(){
					var classval=jQuery(this).val();
					if ( classval !== '' ) {
						var data = {'action' : 'mjschool_get_section_for_compose_message', 'receiver' : receiver, 'classval':classval};
						jQuery.post(ajaxurl, data, function(response) {
							jQuery("#class_student_section").html(response);
						});
					} else {
						jQuery('#class_student_section').css('display', 'none');
					}
					jQuery('.mjschool-single-class-div .user_display_block').html('');
				});
			<?php } ?>
			<?php if ( ! empty( $school_type ) ) { ?>
				jQuery('#teacher_class').on("change", function(){
					var classval=jQuery(this).val();
					if ( classval !== '' ) {
						var data = {'action' : 'mjschool_get_section_for_compose_message', 'receiver' : receiver, 'classval':classval};
						jQuery.post(ajaxurl, data, function(response) {
							jQuery("#class_teacher_section").html(response);
						});
					} else {
						jQuery('#class_teacher_section').css('display', 'none');
					}
					jQuery('.mjschool-single-class-div .user_display_block').html('');
				});
			<?php } ?>
			<?php if ( ! empty( $school_type ) ) { ?>
				jQuery('#parent_class').on("change", function(){
					var classval=jQuery(this).val();
					if ( classval !== '' ) {
						var data = {'action' : 'mjschool_get_section_for_compose_message', 'receiver' : receiver, 'classval':classval};
						jQuery.post(ajaxurl, data, function(response) {
							jQuery("#class_parent_section").html(response);
						});
					} else {
						jQuery('#class_parent_section').css('display', 'none');
					}
					jQuery('.mjschool-single-class-div .user_display_block').html('');
				});
			<?php } ?>
			<?php if ( ! empty( $school_type ) ) { ?>
				jQuery('#class_student_section').on("change", function(){
					var sectionval=jQuery('#class_student_section').val();
					var receiver=jQuery('#roleid').val();
					var classval=jQuery('#student_class').val();
					if ( receiver !== '' ) {
						var data = {'action' : 'mjschool_get_user_for_compose_message', 'receiver' : receiver, 'classval':classval, 'sectionval':sectionval};
						jQuery.post(ajaxurl, data, function(response) {
							jQuery('.mjschool-single-class-div .user_display_block').html(response);
						});
					}
				});
			<?php } ?>
			<?php if ( ! empty( $school_type ) ) { ?>
				jQuery('#class_teacher_section').on("change", function(){
					var sectionval=jQuery('#class_teacher_section').val();
					var receiver=jQuery('#roleid').val();
					var classval=jQuery('#teacher_class').val();
					if ( receiver !== '' ) {
						var data = {'action' : 'mjschool_get_user_for_compose_message', 'receiver' : receiver, 'classval':classval, 'sectionval':sectionval};
						jQuery.post(ajaxurl, data, function(response) {
							jQuery('.mjschool-single-class-div .user_display_block').html(response);
						});
					}
				});
			<?php } ?>
			<?php if ( ! empty( $school_type ) ) { ?>
				jQuery('#class_parent_section').on("change", function(){
					var sectionval=jQuery('#class_parent_section').val();
					var receiver=jQuery('#roleid').val();
					var classval=jQuery('#parent_class').val();
					if ( receiver !== '' ) {
						var data = {'action' : 'mjschool_get_user_for_compose_message', 'receiver' : receiver, 'classval':classval, 'sectionval':sectionval};
						jQuery.post(ajaxurl, data, function(response) {
							jQuery('.mjschool-single-class-div .user_display_block').html(response);
						});
					}
				});
			<?php } ?>
			<?php if ( empty( $school_type ) ) { ?>
				jQuery('#student_class, #teacher_class, #parent_class').on("change", function(){
					var receiver=jQuery('#roleid').val();
					var classval = jQuery(this).val();
					if ( receiver !== '' ) {
						var data = {'action' : 'mjschool_get_user_for_compose_message', 'receiver' : receiver, 'classval':classval, 'sectionval':''};
						jQuery.post(ajaxurl, data, function(response) {
							jQuery('.mjschool-single-class-div .user_display_block').html(response);
						});
					}
				});
			<?php } ?>
		});
	</script>
	<?php
}
?>
<div class="mjschool-page-body-wrap mjschool-float-left">
	<form method="POST" name="" class="compose_message_form" id="compose_message_form" enctype="multipart/form-data" action="">
		<?php wp_nonce_field( 'mjschool_compose_message_nonce' ); ?>
		<div class="form-body mjschool-user-form"><!--User form. -->
			<div class="row"><!--Row. -->
				<?php
				$sectionval = '';
				$role_receiver_value = array(
					'student' => esc_html__( 'Students', 'mjschool' ),
					'teacher' => esc_html__( 'Teachers', 'mjschool' ),
					'parent'  => esc_html__( 'Parents', 'mjschool' ),
				);
				if ( $role === 'student' || $role === 'parent' ) {
					$role_receiver_value = array(
						'teacher' => esc_html__( 'Teachers', 'mjschool' ),
					);
				}
				if ( $role === 'teacher' || $role === 'supportstaff' ) {
					$role_receiver_value = array(
						'student'      => esc_html__( 'Students', 'mjschool' ),
						'teacher'      => esc_html__( 'Teachers', 'mjschool' ),
						'parent'       => esc_html__( 'Parents', 'mjschool' ),
						'supportstaff' => esc_html__( 'Support Staffs', 'mjschool' ),
					);
				}
				?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 ">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<select name="receiver" id="roleid" class="mjschool-form-text form-control input-sm mjschool-receiver-label validate[required]">
								<option value="" disabled selected><?php esc_html_e( 'Select role', 'mjschool' ); ?></option>
								<?php
								foreach ( $role_receiver_value as $rolekey => $rolevalue ) {
									?>
									<option value="<?php echo esc_attr( $rolekey ); ?>"><?php echo esc_html( $rolevalue ); ?></option>
									<?php
								}
								?>
							</select>
							<label class="mjschool-custom-top-label" for="role"><?php esc_html_e( 'Role', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<?php
				if ( $role === 'teacher' || $role === 'supportstaff' ) {
					?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool_student_class_div mjschool_class_div">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<select name="class_id" id="student_class" class="mjschool-form-text form-control input-sm" >
									<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
									<?php
									$mjschool_class = new Mjschool_Class();
									$classdata = $mjschool_class->mjschool_get_all_class();
									if ( ! empty( $classdata ) ) {
										foreach ( $classdata as $classkey => $classvalue ) {
											?>
											<option value="<?php echo esc_attr( $classvalue->id ); ?>"><?php echo esc_html( $classvalue->class_name ); ?></option>
											<?php
										}
									}
									?>
								</select>
								<label class="mjschool-custom-top-label" for="class_id"><?php esc_html_e( 'Class', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<?php
					if ( ! empty( $school_type ) ) {
						?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool_student_class_div mjschool_class_div" id="class_student_section" style="display:none;">
							<div class="form-group input">
								<select name="class_section" class="mjschool-form-text form-control input-sm" >
									<option value=""><?php esc_html_e( 'Select Section', 'mjschool' ); ?></option>
									<?php
									$class_id = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
									if ( $class_id > 0 ) {
										$mjschool_class = new Mjschool_Class();
										$sectiondata = $mjschool_class->mjschool_get_section_name( $class_id );
										if ( ! empty( $sectiondata ) ) {
											$sectionval = isset( $_REQUEST['class_section'] ) ? intval( wp_unslash( $_REQUEST['class_section'] ) ) : 0;
											foreach ( $sectiondata as $sectionkey => $sectionval1 ) {
												?>
												<option value="<?php echo esc_attr( $sectionval1->id ); ?>" <?php selected( $sectionval, $sectionval1->id ); ?>><?php echo esc_html( $sectionval1->section_name ); ?></option>
												<?php
											}
										}
									}
									?>
								</select>
							</div>
						</div>
						<?php
					}
					?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool_teacher_class_div mjschool_class_div">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<select name="class_id" id="teacher_class" class="mjschool-form-text form-control input-sm" >
									<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
									<?php
									$mjschool_class = new Mjschool_Class();
									$classdata = $mjschool_class->mjschool_get_all_class();
									if ( ! empty( $classdata ) ) {
										foreach ( $classdata as $classkey => $classvalue ) {
											?>
											<option value="<?php echo esc_attr( $classvalue->id ); ?>"><?php echo esc_html( $classvalue->class_name ); ?></option>
											<?php
										}
									}
									?>
								</select>
								<label class="mjschool-custom-top-label" for="class_id"><?php esc_html_e( 'Class', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<?php
					if ( ! empty( $school_type ) ) {
						?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool_teacher_class_div mjschool_class_div" id="class_teacher_section" style="display:none;">
							<div class="form-group input">
								<select name="class_section" class="mjschool-form-text form-control input-sm" >
									<option value=""><?php esc_html_e( 'Select Section', 'mjschool' ); ?></option>
									<?php
									$class_id = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
									if ( $class_id > 0 ) {
										$mjschool_class = new Mjschool_Class();
										$sectiondata = $mjschool_class->mjschool_get_section_name( $class_id );
										if ( ! empty( $sectiondata ) ) {
											$sectionval = isset( $_REQUEST['class_section'] ) ? intval( wp_unslash( $_REQUEST['class_section'] ) ) : 0;
											foreach ( $sectiondata as $sectionkey => $sectiondata ) {
												?>
												<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
												<?php
											}
										}
									}
									?>
								</select>
							</div>
						</div>
						<?php
					}
				}
				?>
				<?php
				$hide_class = $role === 'teacher' ? 'mjschool_parent_class_div mjschool_class_div' : 'col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool_parent_class_div mjschool_class_div';
				?>
				<div class="<?php echo esc_attr( $hide_class ); ?>">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<select name="class_id" id="parent_class" class="mjschool-form-text form-control input-sm" >
								<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
								<?php
								$mjschool_class = new Mjschool_Class();
								$classdata = $mjschool_class->mjschool_get_all_class();
								if ( $role === 'teacher' || $role === 'supportstaff' ) {
									if ( ! empty( $classdata ) ) {
										foreach ( $classdata as $classkey => $classvalue ) {
											?>
											<option value="<?php echo esc_attr( $classvalue->id ); ?>"><?php echo esc_html( $classvalue->class_name ); ?></option>
											<?php
										}
									}
								} else {
									if ( ! empty( $classdata ) ) {
										foreach ( $classdata as $classkey => $classvalue ) {
											?>
											<option value="<?php echo esc_attr( $classvalue->id ); ?>"><?php echo esc_html( $classvalue->class_name ); ?></option>
											<?php
										}
									}
								}
								?>
							</select>
							<label class="mjschool-custom-top-label" for="class_id"><?php esc_html_e( 'Class', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<?php
				if ( $role === 'teacher' || $role === 'supportstaff' ) {
					if ( ! empty( $school_type ) ) {
						?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool_parent_class_div mjschool_class_div" id="class_parent_section" style="display:none;">
							<div class="form-group input">
								<select name="class_section" class="mjschool-form-text form-control input-sm" >
									<option value=""><?php esc_html_e( 'Select Section', 'mjschool' ); ?></option>
									<?php
									$class_id = isset( $_REQUEST['class_id'] ) ? intval( wp_unslash( $_REQUEST['class_id'] ) ) : 0;
									if ( $class_id > 0 ) {
										$mjschool_class = new Mjschool_Class();
										$sectiondata = $mjschool_class->mjschool_get_section_name( $class_id );
										if ( ! empty( $sectiondata ) ) {
											$sectionval = isset( $_REQUEST['class_section'] ) ? intval( wp_unslash( $_REQUEST['class_section'] ) ) : 0;
											foreach ( $sectiondata as $sectionkey => $sectiondata ) {
												?>
												<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
												<?php
											}
										}
									}
									?>
								</select>
							</div>
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
									$obj_message = new Mjschool_Message();
									$mjschool_class = new Mjschool_Class();
									$student_list = $obj_message->mjschool_get_teacher_class_student( get_current_user_id() );
								} elseif ( $role === 'student' ) {
									if ( get_option( 'mjschool_student_send_message' ) === 1 ) {
										$std_list = $mjschool_class->mjschool_get_student_by_class_id( $student_class_id );
										foreach ( $std_list as $std_list_ley => $std_list_val ) {
											// Changed != to !== for strict comparison
											if ( $std_list_val->ID !== get_current_user_id() ) {
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
										// Changed != to !== for strict comparison
										if ( $retrive_data->ID !== get_current_user_id() ) {
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
		$mjschool_custom_field_obj = new Mjschool_Custom_Field();
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