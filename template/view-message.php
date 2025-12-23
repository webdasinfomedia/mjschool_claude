<?php
/**
 * View Single Message and Reply Page
 *
 * This file displays the details of a single message from the user's inbox
 * or sent box, including attachments. It also provides a form to reply
 * to the message thread.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
// Subject.
if ( isset( $_REQUEST['message'] ) ) {
	$message = sanitize_text_field(wp_unslash($_REQUEST['message']));
	
	if ( $message === 1)
	{
		?>
		<div class="mjschool-panel-body">
			<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
				<p>
					<?php esc_html_e( 'Message Send Successfully','mjschool' ); ?>
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-close.png")?>"></span> </button>
				</p>
			</div>
		</div>
		<?php 
	}
	elseif ( $message === 2 )
	{?>
		<div class="mjschool-panel-body">
			<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible" role="alert">
				<p>
					<?php esc_html_e( 'Message deleted successfully','mjschool' ); ?>
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-close.png")?>"></span></button>
				</p>
			</div>
		</div>
		<?php 
	}
	
}
if ( sanitize_text_field(wp_unslash($_REQUEST['from'])) === 'sendbox' ) {
	$mesage_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ) );
	$message   = get_post( $mesage_id );
	mjschool_change_read_status_reply( $mesage_id );
	$author = $message->post_author;
	if ( isset( $_REQUEST['delete'] ) ) {
		wp_delete_post( $mesage_id );
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=message&tab=sendbox' ) );
		die();
	}
	$box = 'sendbox';
}
if ( sanitize_text_field(wp_unslash($_REQUEST['from'])) === 'inbox' ) {
	$mesage_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ) );
	$message   = mjschool_get_message_by_id( $mesage_id );
	$message1  = get_post( $message->post_id );
	$author    = $message1->post_author;
	mjschool_change_read_status( $mesage_id );
	mjschool_change_read_status_reply( $message->post_id );
	$box = 'inbox';
}
if ( isset( $_REQUEST['delete'] ) ) {
	$mesage_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ) );
	mjschool_delete_message( 'mjschool_message', $mesage_id );
	wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=message&tab=inbox' ) );
	die();
}
if ( isset( $_POST['replay_message'] ) ) {
	$message_id   = sanitize_text_field(wp_unslash($_REQUEST['id']));
	$message_from = sanitize_text_field(wp_unslash($_REQUEST['from']));
	$result       = mjschool_send_replay_message( wp_unslash($_POST) );
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=message&tab=view_message&from=' . $message_from . "&id=$message_id&message=1" ) );
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete-reply' ) {
	$message_id   = sanitize_text_field(wp_unslash($_REQUEST['id']));
	$message_from = sanitize_text_field(wp_unslash($_REQUEST['from']));
	$obj_message  = new Mjschool_Message();
	$result       = $obj_message->mjschool_delete_reply( sanitize_text_field(wp_unslash($_REQUEST['reply_id'])) );
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=message&tab=view_message&action=delete-reply&from=' . $message_from . '&id=' . $message_id . '&message=2') );
		die();
	}
}
?>
<div class="mjschool-mailbox-content">
	<div class="mjschool-message-header">
		<h3>
			<span><?php esc_html_e( 'Subject', 'mjschool' ); ?> :</span> 
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
	</div>
	<div class="mjschool-message-sender">                                
		<p>
			<?php
			if ( $box === 'sendbox' ) {
				$message_for = get_post_meta( sanitize_text_field(wp_unslash($_REQUEST['id'])), 'message_for', true );
				echo '' . esc_html__( 'From', 'mjschool' ) . ' : ' . esc_html( mjschool_get_display_name( $message->post_author ) ) . '<span>&lt;' . esc_html( mjschool_get_email_id_by_user_id( $message->post_author ) ) . '&gt;</span><br>';
				$check_message_single_or_multiple = mjschool_send_message_check_single_user_or_multiple( sanitize_text_field(wp_unslash($_REQUEST['id'])) );
				if ( $check_message_single_or_multiple === 1 ) {
					$post_id  = mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) );
					$get_single_user = mjschool_get_message_by_post_id($post_id );
					echo '' . esc_html__( 'To', 'mjschool' ) . ' : ' . esc_html( mjschool_get_display_name( $get_single_user->receiver ) ) . '<span>&lt;' . esc_html( mjschool_get_email_id_by_user_id( $get_single_user->receiver ) ) . '&gt;</span><br>';
				} else {
					echo '' . esc_html__( 'To', 'mjschool' ) . ' : ' . esc_html( get_post_meta( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ), 'message_for', true ) );
				}
			} else {
				echo '' . esc_html__( 'From', 'mjschool' ) . ' : ' . esc_html( mjschool_get_display_name( $message->sender ) ) . '<span>&lt;' . esc_html( mjschool_get_email_id_by_user_id( $message->sender ) ) . '&gt;</span><br>';
				$check_message_single_or_multiple = mjschool_send_message_check_single_user_or_multiple( $message->post_id );
				if ( $check_message_single_or_multiple === 1 ) {
					$post_id  = $message->post_id;
					$get_single_user = mjschool_get_message_by_post_id($post_id );
					echo '' . esc_html__( 'To', 'mjschool' ) . ' : ' . esc_html( mjschool_get_display_name( $get_single_user->receiver ) ) . '<span>&lt;' . esc_html( mjschool_get_email_id_by_user_id( $get_single_user->receiver ) ) . '&gt;</span><br>';
				} else {
					echo '' . esc_html__( 'To', 'mjschool' ) . ' : ' . esc_html( get_post_meta( $message->post_id, 'message_for', true ) );
				}
			}
			?>
		</p>
	</div>
	<div class="mjschool-message-content">
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
						<a target="blank" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $attchment_data )); ?>" class="btn btn-default"><i class="fas fa-download"></i><?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a>
						<?php
					}
				}
				$receiver_id = ( get_post_meta( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ), 'message_mjschool_user_id', true ) );
			} else {
				echo esc_html( $message->message_body );
				echo '</br>';
				echo '</br>';
				$attchment = get_post_meta( $message->post_id, 'message_attachment', true );
				if ( ! empty( $attchment ) ) {
					$attchment_array = explode( ',', $attchment );
					foreach ( $attchment_array as $attchment_data ) {
						?>
						<a target="blank" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $attchment_data )); ?>" class="btn btn-default"><i class="fas fa-download"></i><?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a>
						<?php
					}
				}
				$receiver_id = $message->sender;
			}
			?>
		</p>
		<div class="message-options pull-right mjschool_float_right" >
			<?php
			if ( $user_access['delete'] === '1' ) {
				?>
				<a class="btn mjschool-save-btn mjschool-msg-delete-btn" href="<?php echo esc_url( '?dashboard=mjschool_user&page=message&tab=view_message&id=' . sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) . '&delete=1' ); ?>" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash m-r-xs"></i><?php esc_html_e( 'Delete', 'mjschool' ); ?></a> 
				<?php
			}
			?>
		</div>
	</div>
	<?php
	$obj_message = new Mjschool_Message();
	if ( isset( $_REQUEST['from'] ) && sanitize_text_field(wp_unslash($_REQUEST['from'])) === 'inbox' ) {
		$allreply_data = $obj_message->mjschool_get_all_replies_frontend( $message->post_id );
	} else {
		$allreply_data = $obj_message->mjschool_get_all_replies_frontend( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ) );
	}
	if ( ! empty( $allreply_data ) ) {
		foreach ( $allreply_data as $reply ) {
			if ( $reply->sender_id === get_current_user_id() || $reply->receiver_id === get_current_user_id() ) {
				?>
				<div class="mjschool-message-content">
					<p>
						<?php echo esc_html( $reply->message_comment );
						$reply_attchment = $reply->message_attachment;
						if ( ! empty( $reply_attchment ) ) {
							echo '</br>';
							echo '</br>';
							$reply_attchment_array = explode( ',', $reply_attchment );
							foreach ( $reply_attchment_array as $attchment_data1 ) {
								?>
								<a target="blank" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $attchment_data1 )); ?>" class="btn btn-default"><i class="fas fa-download"></i><?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a>
								<?php
							}
						}
						?>
						<br>
						<h5 class="mjschool-reply-h5">
							<?php
							esc_html_e( 'Reply By : ', 'mjschool' );
							echo esc_html( mjschool_get_display_name( $reply->sender_id ) );
							esc_html_e( ' || ', 'mjschool' );
							esc_html_e( 'Reply To : ', 'mjschool' );
							echo esc_html( mjschool_get_display_name( $reply->receiver_id ) );
							esc_html_e( ' || ', 'mjschool' );
							?>
							<span class="timeago"  title="<?php echo esc_attr( mjschool_convert_date_time( $reply->created_date ) ); ?>"></span>
							<?php
							if ( $reply->sender_id === get_current_user_id() ) {
								if ( $user_access['delete'] === '1' ) {
									?>
									<span class="comment-delete">
									<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=message&tab=view_message&action=delete-reply&from=' . sanitize_text_field( wp_unslash( $_REQUEST['from'] ) ) . '&id=' . sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) . '&reply_id=' . $reply->id ); ?>" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><?php esc_html_e( 'Delete', 'mjschool' ); ?></a></span> 
									<?php
								}
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
	<form name="message-replay" method="post" id="message-replay" enctype="multipart/form-data">
		<input type="hidden" name="message_id" value="<?php if ( sanitize_text_field(wp_unslash($_REQUEST['from'])) === 'sendbox' ) { echo esc_html( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ) ); } else { echo esc_html( $message->post_id ); } ?>">
		<input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
		<div class="mjschool-message-content">
			<?php
			$current_user_id = get_current_user_id();
			if ( (string) $current_user_id === $author ) {
				if ( sanitize_text_field(wp_unslash($_REQUEST['from'])) === 'sendbox' ) {
					$msg_id         = mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) );
					$msg_id_integer = (int) $msg_id;
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$reply_to_users = mjschool_get_message_by_post_id($msg_id_integer );
				} else {
					$msg_id         = $message->post_id;
					$msg_id_integer = (int) $msg_id;
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$reply_to_users = mjschool_get_message_by_post_id($msg_id_integer );
				}
			} else {
				$reply_to_users   = array();
				$reply_to_users[] = (object) array( 'receiver' => $author );
			}
			?>
			<div class="message-options pull-right mjschool_float_right" >
				<button type="button" name="replay_message_btn" class="btn mjschool-save-btn replay_message_btn" id="replay_message_btn"><i class="fas fa-reply m-r-xs"></i><?php esc_html_e( 'Reply', 'mjschool' ); ?></button>
			</div>
			<div class="mjschool-message-content mjschool-float-left-width-100px replay_message_div">
				<div class="form-body mjschool-user-form mt-3"><!-- Mjschool-user-form.-->   
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-single-class-div mjschool-support-staff-user-div input">			
							<div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px-for-btn">
								<span class="user_display_block">
								<select name="receiver_id[]" class="form-control" id="selected_users" multiple="true">
									<?php
									foreach ( $reply_to_users as $reply_to_user ) {
										$user_data = get_userdata( $reply_to_user->receiver );
										if ( ! empty( $user_data ) ) {
											if ( $reply_to_user->receiver != get_current_user_id() ) {
												?>
												<option  value="<?php echo esc_attr( $reply_to_user->receiver ); ?>"><?php echo esc_html( mjschool_get_display_name( $reply_to_user->receiver ) ); ?></option>
												<?php
											}
										}
									}
									?>
								</select>
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
						<div class="col-md-6 mjschool-attachment-div">
							<div class="row">
								<div class="col-md-10">	
									<div class="form-group input">
										<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
											<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" for="photo"><?php esc_html_e( 'Attachment ', 'mjschool' ); ?></label>
											<div class="col-sm-12">	
												<input  class="mjschool-btn-top input-file" name="message_attachment[]" type="file" />
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-2 col-sm-2 col-xs-12">	
									
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_new_attachment2()" class="mjschool-rtl-margin-top-15px mjschool-float-right mjschool-more-attachment" id="add_more_sibling">
									
								</div>
							</div>
						</div>
					</div><!-- Mjschool-user-form.-->	
					<div class="form-body mjschool-user-form mt-3"><!-- mjschool-user-form.-->   
						<div class="row message-options">
							<div class="col-sm-6">    
								<?php
								if ( $user_access['add'] === '1' ) {
									?>
									<button type="submit" name="replay_message" class="btn btn-success mjschool-save-btn" id="check_reply_user"><?php esc_html_e( 'Send Message', 'mjschool' ); ?></button>
									<?php
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>