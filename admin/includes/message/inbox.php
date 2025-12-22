<?php
/**
 * Inbox Message List Template.
 *
 * This file renders the "Inbox" view under the Message Management section in the admin panel.
 * It displays a paginated list of all received messages for the logged-in user, along with
 * sender details, subject, description, attachments, and timestamps.
 *
 * Key Features:
 * - Displays received messages in a DataTable with sorting and search capabilities.
 * - Supports message deletion (single or multiple) with confirmation.
 * - Includes checkboxes for bulk selection with "Select All" functionality.
 * - Integrates dynamic unread message counters and reply counts.
 * - Fetches sender and receiver information using WordPress post and meta APIs.
 * - Securely handles deletion with WordPress sanitization and redirect logic.
 * - Uses the jQuery DataTables plugin with multi-language support.
 * - Displays message attachments and formatted date/time.
 * - Provides fallback UI when no messages are found.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/message
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for inbox tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_message_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<div class="mjschool-mailbox-content mjschool-custom-padding-0"><!--Mjschool-mailbox-content.  -->
	<?php
	$max = 10;
	if ( isset( $_GET['pg'] ) ) {
		$p = intval( wp_unslash( $_GET['pg'] ) );
	} else {
		$p = 1;
	}
	$limit   = ( $p - 1 ) * $max;
	$post_id = 0;
	if ( isset( $_REQUEST['delete_selected'] ) && ! empty( $_REQUEST['delete_selected'] ) ) {
		// Verify nonce for delete action.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'mjschool_delete_inbox' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
		}
		if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
			$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
			foreach ( $ids as $id ) {
				$result = mjschool_delete_inbox_message( intval( $id ) );
				if ( $result ) {
					$nonce = wp_create_nonce( 'mjschool_message_tab' );
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_message&tab=inbox&_wpnonce=' . $nonce . '&message=2' ) );
					exit;
				}
			}
		}
	}
	$message = mjschool_get_inbox_message( get_current_user_id(), $limit, $max );
	if ( ! empty( $message ) ) {
		?>
		<form name="wcwm_report" action="" method="post"><!-- Form-div. -->
			<?php wp_nonce_field( 'mjschool_delete_inbox', '_wpnonce' ); ?>
			<div class="table-responsive" id="sentbox_table"><!-- Table-responsive.  -->
				<table id="inbox_list" class="table"><!--Inbox-list table. -->
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
							<th class="mjschool-custom-padding-0 mjschool_padding_15px_0px" ><input type="checkbox" class="select_all" id="select_all"></th>
							<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Message From', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Message For', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Attachment', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Date & Time', 'mjschool' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ( ! empty( $message ) ) {
							$i = 0;
							foreach ( $message as $msg ) {
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
								$message_for = get_post_meta( $msg->post_id, 'message_for', true );
								$attchment   = get_post_meta( $msg->post_id, 'message_attachment', true );
								if ( $message_for === 'student' || $message_for === 'supportstaff' || $message_for === 'teacher' || $message_for === 'parent' || $message_for === 'administrator' ) {
									if ( $post_id === $msg->post_id ) {
										continue;
									} else {
										?>
										<tr>
											<td class="mjschool-checkbox-width-10px">
												<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $msg->message_id ); ?>">
											</td>
											<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
												<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-inbox.png' ); ?>" height="30px" width="30px" class="mjschool-massage-image">
												</p>
											</td>
											<td>
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=inbox&id=' . rawurlencode( mjschool_encrypt_id( $msg->message_id ) ) ) ); ?>" class="mjschool-text-decoration-none">
													<?php
													$auth   = get_post( $msg->post_id );
													$authid = $auth->post_author;
													$author = mjschool_get_display_name( $authid );
													echo esc_html( $author );
													?>
												</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Message From', 'mjschool' ); ?>"></i>
												<?php
												$unread_count = mjschool_count_unread_message_current_user( $msg->post_id );
												if ( $unread_count > 0 ) :
													?>
													<span class="badge badge-success ms-1 mjschool_background_color_purple" >
														<?php echo esc_html( $unread_count ); ?>
													</span>
												<?php endif; ?>
											</td>
											<td>
												<?php
												$check_message_single_or_multiple = mjschool_send_message_check_single_user_or_multiple( $msg->post_id );
												if ( $check_message_single_or_multiple === 1 ) {
													global $wpdb;
													$tbl_name = $wpdb->prefix . 'mjschool_message';
													$post_id  = $msg->post_id;
													// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
													$get_single_user = $wpdb->get_row( "SELECT * FROM $tbl_name where post_id = $post_id" );
													$mjschool_role            = mjschool_get_display_name( $get_single_user->receiver );
													echo esc_html( $mjschool_role );
												} else {
													$mjschool_role = get_post_meta( $msg->post_id, 'message_for', true );
													echo esc_html( $mjschool_role );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Message For', 'mjschool' ); ?>"></i>
											</td>
											<td >
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=inbox&id=' . rawurlencode( mjschool_encrypt_id( $msg->message_id ) ) ) ); ?>" class="mjschool-inbox-tab mjschool-text-decoration-none">
													<?php
													$obj_message = new Mjschool_Message();
													$msg_post_id = $obj_message->mjschool_count_reply_item( $msg->post_id );
													$subject_char = strlen( $msg->subject );
													if ( $subject_char <= 10 ) {
														echo esc_html( $msg->subject );
													} else {
														$char_limit   = 10;
														$subject_body = substr( strip_tags( $msg->subject ), 0, $char_limit ) . '...';
														echo esc_html( $subject_body );
													}
													if ( $msg_post_id >= 1 ) {
														?>
														<span class="mjschool-inbox-count-number badge badge-success pull-right ms-1"><?php echo esc_html( $msg_post_id ); ?></span>
														<?php
													}
													?>
												</a> 
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $msg->subject ) ) { echo esc_attr( $msg->subject ); } else { esc_attr_e( 'Subject', 'mjschool' ); } ?>"></i>
											</td>
											<td >
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=inbox&id=' . rawurlencode( mjschool_encrypt_id( $msg->message_id ) ) ) ); ?>" class="mjschool-text-decoration-none">
													<?php
													$body_char = strlen( $msg->message_body );
													if ( $body_char <= 30 ) {
														echo esc_html( $msg->message_body );
													} else {
														$char_limit = 30;
														$msg_body   = substr( strip_tags( $msg->message_body ), 0, $char_limit ) . '...';
														echo esc_html( $msg_body );
													}
													?>
												</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $msg->message_body ) ) { echo esc_attr( $msg->message_body ); } else { esc_attr_e( 'Description', 'mjschool' ); } ?>"></i>
											</td>
											<td>
												<?php
												if ( ! empty( $attchment ) ) {
													$attchment_array = explode( ',', $attchment );
													foreach ( $attchment_array as $attchment_data ) {
														?>
														<a target="blank" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . sanitize_file_name( $attchment_data ) ) ); ?>" class="btn btn-default"><i class="fas fa-download"></i> <?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a>
														<?php
													}
												} else {
													esc_html_e( 'No Attachment', 'mjschool' );
												}
												?>
											</td>
											<td>
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=inbox&id=' . rawurlencode( mjschool_encrypt_id( $msg->message_id ) ) ) ); ?>" class="mjschool-text-decoration-none">
													<?php echo esc_html( mjschool_convert_date_time( $msg->date ) ); ?>
												</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date & Time', 'mjschool' ); ?>"></i>
											</td>
										</tr>
										<?php
									}
								} else {
									?>
									<tr>
										<td class="mjschool-checkbox-width-10px">
											<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $msg->message_id ); ?>">
										</td>
										<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
											<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-inbox.png' ); ?>" height="30px" width="30px" class="mjschool-massage-image">
											</p>
										</td>
										<td>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=inbox&id=' . rawurlencode( mjschool_encrypt_id( $msg->message_id ) ) ) ); ?>" class="mjschool-text-decoration-none">
												<?php
												$auth   = get_post( $msg->post_id );
												$authid = $auth->post_author;
												$author = mjschool_get_display_name( $authid );
												echo esc_html( $author );
												?>
											</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Message From', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=inbox&id=' . rawurlencode( mjschool_encrypt_id( $msg->message_id ) ) ) ); ?>" class="mjschool-text-decoration-none">
												<?php
												$check_message_single_or_multiple = mjschool_send_message_check_single_user_or_multiple( $msg->post_id );
												if ( $check_message_single_or_multiple === 1 ) {
													global $wpdb;
													$tbl_name = $wpdb->prefix . 'mjschool_message';
													$post_id  = $msg->post_id;
													// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
													$get_single_user = $wpdb->get_row( "SELECT * FROM $tbl_name where post_id = $post_id" );
													$mjschool_role            = mjschool_get_display_name( $get_single_user->receiver );
													echo esc_html( $mjschool_role );
												} else {
													$mjschool_role = get_post_meta( $msg->post_id, 'message_for', true );
													echo esc_html( $mjschool_role );
												}
												?>
											</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Message For', 'mjschool' ); ?>"></i>
										</td>
										<td class="mjschool-width-100px">
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=inbox&id=' . rawurlencode( mjschool_encrypt_id( $msg->message_id ) ) ) ); ?>" class="mjschool-text-decoration-none">
												<?php
												$obj_message = new Mjschool_Message();
												$msg_post_id = $obj_message->mjschool_count_reply_item( $msg->post_id );
												$subject_char = strlen( $msg->subject );
												if ( $subject_char <= 10 ) {
													echo esc_html( $msg->subject );
												} else {
													$char_limit   = 10;
													$subject_body = substr( strip_tags( $msg->subject ), 0, $char_limit ) . '...';
													echo esc_html( $subject_body );
												}
												if ( $msg_post_id >= 1 ) {
													?>
													<span class="badge badge-success pull-right"><?php echo esc_html( $msg_post_id ); ?></span>
													<?php
												}
												?>
											</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $msg->subject ) ) { echo esc_attr( $msg->subject ); } else { esc_attr_e( 'Subject', 'mjschool' ); } ?>"></i>
										</td>
										<td>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=inbox&id=' . rawurlencode( mjschool_encrypt_id( $msg->message_id ) ) ) ); ?>" class="mjschool-text-decoration-none">
												<?php
												$body_char = strlen( $msg->message_body );
												if ( $body_char <= 30 ) {
													echo esc_html( $msg->message_body );
												} else {
													$char_limit = 30;
													$msg_body   = substr( strip_tags( $msg->message_body ), 0, $char_limit ) . '...';
													echo esc_html( $msg_body );
												}
												?>
											</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $msg->message_body ) ) { echo esc_attr( $msg->message_body ); } else { esc_attr_e( 'Description', 'mjschool' ); } ?>"></i>
										</td>
										<td>
											<?php
											if ( ! empty( $attchment ) ) {
												$attchment_array = explode( ',', $attchment );
												foreach ( $attchment_array as $attchment_data ) {
													?>
													<a target="blank" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . sanitize_file_name( $attchment_data ) ) ); ?>" class="btn btn-default"><i class="fas fa-download"></i> <?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a>
													<?php
												}
											} else {
												esc_html_e( 'No Attachment', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attachment', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=inbox&id=' . rawurlencode( mjschool_encrypt_id( $msg->message_id ) ) ) ); ?>" class="mjschool-text-decoration-none">
												<?php echo esc_html( mjschool_convert_date_time( $msg->date ) ); ?>
											</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date & Time', 'mjschool' ); ?>"></i>
										</td>
									</tr>
									<?php
								}
								$post_id = $msg->post_id;
								++$i;
							}
						}
						?>
					</tbody>
				</table><!-- Inbox-list table. -->
				<div class="mjschool-print-button pull-left">
					<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload" type="button">
						<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
						<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
					</button>
					<?php
					if ( $user_access_delete === '1' ) {
						?>
						<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
						<?php  
					}
					?>
				</div>
			</div><!-- Table-responsive.  -->
		</form><!-- Form-div. -->
		<?php
	} else {
		?>
		<div class="mjschool-calendar-event-new">
			<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
		</div>
		<?php
	}
	?>
</div>