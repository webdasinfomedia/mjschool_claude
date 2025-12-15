<?php
/**
 * View and Reply to a Message Template.
 *
 * This file handles viewing, replying to, and deleting individual messages within
 * the MJSchool messaging system. It supports both inbox and sent messages,
 * allowing users to view message details, attachments, and replies, as well as
 * send responses with optional file uploads.
 *
 * Key Features:
 * - Displays full message details for both sent and received messages.
 * - Supports deletion of messages and individual replies with confirmation prompts.
 * - Handles secure message decryption and read-status updates.
 * - Allows message replies with multiple recipients and attachments.
 * - Uses AJAX-powered UI for reply handling, attachment addition, and multiselect user input.
 * - Employs WordPress sanitization, escaping, and redirection functions for security.
 * - Implements PHPCS-compliant database queries using `$wpdb->prepare()`.
 * - Supports localization for multilingual message interfaces.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/message
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// Subject.
$message_id_decrypt = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ) );
if ( isset( $_REQUEST['from'] ) && sanitize_text_field( wp_unslash( $_REQUEST['from'] ) ) === 'sendbox' ) {
	$message = get_post( $message_id_decrypt );
	mjschool_change_read_status_reply( $message_id_decrypt );
	$author = $message->post_author;
	$box    = 'sendbox';
	if ( isset( $_REQUEST['delete'] ) ) {
		wp_delete_post( $message_id_decrypt );
		wp_safe_redirect( admin_url() . 'admin.php?page=mjschool_message&tab=sentbox&message=2' );
		die();
	}
}
if ( isset( $_REQUEST['from'] ) && sanitize_text_field( wp_unslash( $_REQUEST['from'] ) ) === 'inbox' ) {
	$message  = mjschool_get_message_by_id( $message_id_decrypt );
	$message1 = get_post( $message->post_id );
	$author   = $message1->post_author;
	mjschool_change_read_status( intval( $message_id_decrypt ) );
	mjschool_change_read_status_reply( $message->post_id );
	$box = 'inbox';
	if ( isset( $_REQUEST['delete'] ) ) {
		mjschool_delete_message( 'mjschool_message', intval(wp_unslash($_REQUEST['id'])) );
		wp_safe_redirect( admin_url() . 'admin.php?page=mjschool_message&tab=inbox' );
		die();
	}
}
if ( isset( $_POST['replay_message'] ) ) {
	$message_id   = sanitize_text_field(wp_unslash($_REQUEST['id']));
	$message_from = sanitize_text_field(wp_unslash($_REQUEST['from']));
	$result = mjschool_send_replay_message( wp_unslash($_POST) );
	if ( $result ) {
		wp_safe_redirect( admin_url() . 'admin.php?page=mjschool_message&tab=view_message&from=' . $message_from . "&id=$message_id&message=1" );
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete-reply' ) {
	$message_id   = sanitize_text_field(wp_unslash($_REQUEST['id']));
	$message_from = sanitize_text_field(wp_unslash($_REQUEST['from']));
	$result       = mjschool_delete_reply( sanitize_text_field(wp_unslash($_REQUEST['reply_id'])) );
	if ( $result ) {
		wp_redirect( admin_url() . 'admin.php?page=mjschool_message&tab=view_message&action=delete-reply&from=' . $message_from . '&id=' . $message_id . '&message=2' );
		die();
	}
}
?>
<div class="mjschool-mailbox-content">	<!-- Mjschool-mailbox-content. -->
	<div class="mjschool-message-header"><!-- Mjschool-message-header. -->
		<h3><span><?php esc_html_e( 'Subject', 'mjschool' ); ?> :</span>  
			<?php
			if ( $box === 'sendbox' ) {
				echo esc_html( $message->post_title );
			} else {
				echo esc_html( $message->subject ); 
			}
			?>
		</h3>
		<p class="mjschool-message-date">
			<?php
			if ( $box === 'sendbox' ) {
				$date_view = $message->post_date;
				echo esc_html( $date_view );
			} else {
				$date_view = $message->date;
				echo esc_html( mjschool_convert_date_time( $date_view ) );
			}
			?>
		</p>
	</div><!-- Mjschool-message-header. -->
	<div class="mjschool-message-sender"> <!-- Mjschool-message-sender. -->                         
		<p>
			<?php
			if ( $box === 'sendbox' ) {
				$message_for = get_post_meta( $message_id_decrypt, 'message_for', true );
				$author      = $message->post_author;
				$author_name = mjschool_get_display_name( $message->post_author );
				echo '' . esc_html__( 'From', 'mjschool' ) . ' : ' . esc_html( $author_name ) . '<span>&lt;' . esc_html( mjschool_get_email_id_by_user_id( $message->post_author ) ) . '&gt;</span><br>';
				$check_message_single_or_multiple = mjschool_send_message_check_single_user_or_multiple( $message_id_decrypt );
				if ( $check_message_single_or_multiple === 1 ) {
					global $wpdb;
					$tbl_name = $wpdb->prefix . 'mjschool_message';
					$post_id  = $message_id_decrypt;
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$get_single_user = $wpdb->get_row( "SELECT * FROM $tbl_name where post_id = $post_id" );
					$mjschool_role            = mjschool_get_display_name( $get_single_user->receiver );
					echo '' . esc_html__( 'To', 'mjschool' ) . ' : ' . esc_html( $mjschool_role ) . '<span>&lt;' . esc_html( mjschool_get_email_id_by_user_id( $get_single_user->receiver ) ) . '&gt;</span><br>';
				} else {
					$mjschool_role = get_post_meta( $message_id_decrypt, 'message_for', true );
					echo '' . esc_html__( 'To', 'mjschool' ) . ' : ' . esc_html( $mjschool_role );
				}
			} else {
				$author      = $message->sender;
				$author_name = mjschool_get_display_name( $message->sender );
				echo '' . esc_html__( 'From', 'mjschool' ) . ' : ' . esc_html( $author_name ) . '<span>&lt;' . esc_html( mjschool_get_email_id_by_user_id( $message->sender ) ) . '&gt;</span><br>';
				$check_message_single_or_multiple = mjschool_send_message_check_single_user_or_multiple( $message->post_id );
				if ( $check_message_single_or_multiple === 1 ) {
					global $wpdb;
					$tbl_name = $wpdb->prefix . 'mjschool_message';
					$post_id  = $message->post_id;
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$get_single_user = $wpdb->get_row( "SELECT * FROM $tbl_name where post_id = $post_id" );
					$mjschool_role            = mjschool_get_display_name( $get_single_user->receiver );
					echo '' . esc_html__( 'To', 'mjschool' ) . ' : ' . esc_html( $mjschool_role ) . '<span>&lt;' . esc_html( mjschool_get_email_id_by_user_id( $get_single_user->receiver ) ) . '&gt;</span><br>';
				} else {
					$mjschool_role = get_post_meta( $message->post_id, 'message_for', true );
					echo '' . esc_html__( 'To', 'mjschool' ) . ' : ' . esc_html( $mjschool_role );
				}
			}
			?>
		</p>
	</div><!-- Mjschool-message-sender. -->     
	<div class="mjschool-message-content"><!-- Mjschool-message-content. -->     			
		<p>
			<?php
			$receiver_id = 0;
			if ( $box === 'sendbox' ) {
				echo esc_html( $message->post_content );
				echo '</br>';
				echo '</br>';
				$attchment = get_post_meta( $message->ID, 'message_attachment', true );
				if ( ! empty( $attchment ) ) {
					$attchment_array = explode( ',', $attchment );
					foreach ( $attchment_array as $attchment_data ) {
						?>
						<a target="blank" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $attchment_data ); ?>" class="btn btn-default"><i class="fa fa-download"></i><?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a>
						<?php
					}
				}
				$receiver_id = ( get_post_meta( $message_id_decrypt, 'message_mjschool_user_id', true ) );
			} else {
				echo esc_html( $message->message_body );
				echo '</br>';
				echo '</br>';
				$attchment = get_post_meta( $message->post_id, 'message_attachment', true );
				if ( ! empty( $attchment ) ) {
					$attchment_array = explode( ',', $attchment );
					foreach ( $attchment_array as $attchment_data ) {
						?>
						<a target="blank" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $attchment_data ); ?>" class="btn btn-default"><i class="fa fa-download"></i><?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a>
						<?php
					}
				}
				$receiver_id = $message->sender;
			}
			?>
		</p>
		<div class="message-options pull-right mjschool_float_right" >
			<a class="btn mjschool-save-btn mjschool-msg-delete-btn" href="?page=mjschool_message&tab=view_message&id=<?php echo esc_attr( $message_id_decrypt ); ?>&from=<?php echo esc_attr( $box ); ?>&delete=1" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fa fa-trash m-r-xs"></i><?php esc_html_e( 'Delete', 'mjschool' ); ?></a> 
		</div>
	</div><!-- Mjschool-message-content. -->   
	<?php
	if ( isset( $_REQUEST['from'] ) && sanitize_text_field(wp_unslash($_REQUEST['from'])) === 'inbox' ) {
		$allreply_data = mjschool_get_all_replies( $message->post_id );
	} else {
		$allreply_data = mjschool_get_all_replies( $message_id_decrypt );
	}
	if ( ! empty( $allreply_data ) ) {
		foreach ( $allreply_data as $reply ) {
			$receiver_name = mjschool_get_receiver_name_array( $reply->message_id, $reply->sender_id, $reply->created_date, $reply->message_comment );
			if ( $reply->sender_id === get_current_user_id() || $reply->receiver_id === get_current_user_id() ) {
				?>
				<div class="mjschool-message-content">
					<p><?php echo esc_html( $reply->message_comment ); ?>
						<?php
						$reply_attchment = $reply->message_attachment;
						if ( ! empty( $reply_attchment ) ) {
							echo '</br>';
							echo '</br>';
							$reply_attchment_array = explode( ',', $reply_attchment );
							foreach ( $reply_attchment_array as $attchment_data1 ) {
								?>
								<a target="blank" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $attchment_data1 ); ?>" class="btn btn-default"><i class="fa fa-download"></i><?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a>
								<?php
							}
						}
						?>
						<br>
						<h5>
							<?php
							esc_html_e( 'Reply By : ', 'mjschool' );
							echo esc_html( mjschool_get_display_name( $reply->sender_id ) );
							esc_html_e( ' || ', 'mjschool' );
							esc_html_e( 'Reply To : ', 'mjschool' );
							echo esc_html( $receiver_name );
							esc_html_e( ' || ', 'mjschool' );
							?>
							<span class="timeago"  title="<?php echo esc_html( mjschool_convert_date_time( $reply->created_date ) ); ?>"></span>
							<?php
							if ( $reply->sender_id === get_current_user_id() ) {
								?>
								<span class="comment-delete"><a href="admin.php?page=mjschool_message&tab=view_message&action=delete-reply&from=<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['from'])) ); ?>&id=<?php echo esc_attr( $message_id_decrypt ); ?>&reply_id=<?php echo esc_attr( $reply->id ); ?>"><?php esc_html_e( 'Delete', 'mjschool' ); ?></a></span> 
								<?php
							}
							?>
						</h5> 
					</p>
				</div>
				<?php
			}
		}
	}
	?>
	<form name="message-replay" method="post" id="message-replay" enctype="multipart/form-data"><!-- Form. -->   
		<input type="hidden" name="message_id" value="<?php if ( sanitize_text_field(wp_unslash($_REQUEST['from'])) === 'sendbox' ) { echo esc_attr( $message_id_decrypt ); } else { echo esc_attr( $message->post_id ); } ?>">
		<input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
		<?php
		global $wpdb;
		$tbl_name        = $wpdb->prefix . 'mjschool_message';
		$current_user_id = get_current_user_id();
		if ( (string) $current_user_id === $author ) {
			if ( isset( $_REQUEST['from'] ) && sanitize_text_field( wp_unslash( $_REQUEST['from'] ) ) === 'sendbox' ) {
				$msg_id         = $message_id_decrypt;
				$msg_id_integer = (int) $msg_id;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$reply_to_users = $wpdb->get_results(
					$wpdb->prepare( "SELECT * FROM $tbl_name WHERE post_id = %d", $msg_id_integer )
				);
			} else {
				$msg_id         = $message->post_id;
				$msg_id_integer = (int) $msg_id;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$reply_to_users = $wpdb->get_results(
					$wpdb->prepare( "SELECT * FROM $tbl_name WHERE post_id = %d", $msg_id_integer )
				);
			}
		} else {
			$reply_to_users   = array();
			$reply_to_users[] = (object) array( 'receiver' => $author );
		}
		?>
		<div class="message-options pull-right mjschool_float_right" >
			<button type="button" name="replay_message_btn" class="btn mjschool-save-btn replay_message_btn" id="replay_message_btn"><i class="fa fa-reply m-r-xs"></i><?php esc_html_e( 'Reply', 'mjschool' ); ?></button>
		</div>
		<div class="form-body mjschool-user-form mt-3"><!-- Mjschool-user-form. -->   
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-single-class-div mjschool-support-staff-user-div input">
					<div id="messahe_test"></div>
					<div class="col-sm-12 mjschool-multiple-select">
						<span class="user_display_block">
							<select name="receiver_id[]" class="form-control" id="selected_users" multiple="true">
								<?php
								foreach ( $reply_to_users as $reply_to_user ) {
									$user_data = get_userdata( $reply_to_user->receiver );
									if ( ! empty( $user_data ) ) {
										if ( $reply_to_user->receiver != get_current_user_id() ) {
											?>
											<option value="<?php echo esc_attr( $reply_to_user->receiver ); ?>"><?php echo esc_html( mjschool_get_display_name( $reply_to_user->receiver ) ); ?></option>
											<?php
										}
									}
								}
								?>
							</select>
						</span>
					</div>
				</div>
				<div class="col-md-6 mjschool-note-text-notice mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="replay_message_body" id="replay_message_body" class="mjschool-textarea-height-47px form-control validate[required] form-control text-input"></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="photo"><?php esc_html_e( 'Message Comment', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
				</div>	
				<div  class="col-md-6 mjschool-attachment-div">
					<div class="row">
						<div class="col-md-10">	
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" for="photo"><?php esc_html_e( 'Attachment', 'mjschool' ); ?></label>
									<div class="col-sm-12">	
										<input class="input-file mjschool-file-validation file" name="message_attachment[]" type="file" />
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-2 col-sm-2 col-xs-12">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_new_attachment_view()" class="mjschool-rtl-margin-top-15px mjschool-float-right" id="add_more_sibling">	
						</div>
					</div>
				</div>
			</div>
		</div><!-- Mjschool-user-form. -->   
		<div class="form-body mjschool-user-form mt-3"><!-- Mjschool-user-form. -->   
			<div class="row">
				<div class="col-sm-6">          
					<button type="submit" name="replay_message" class="btn btn-success mjschool-save-btn" id="check_reply_user"><?php esc_html_e( 'Send Message', 'mjschool' ); ?></button>	
				</div>    
			</div>
		</div>  <!-- Mjschool-user-form. -->      
	</form><!-- Form div. -->   
</div><!-- Mjschool-mailbox-content. -->