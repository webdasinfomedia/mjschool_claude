<?php
/**
 * Sentbox Management Interface.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/message
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for sendbox tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_message_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<div class="mjschool-mailbox-content mjschool-custom-padding-0"><!-- Mjschool-mailbox-content. -->
	<?php
	$offset = isset( $_REQUEST['pg'] ) ? intval( wp_unslash( $_REQUEST['pg'] ) ) : 0;
	$max = 0;
	
	if ( isset( $_REQUEST['delete_selected'] ) && ! empty( $_REQUEST['delete_selected'] ) ) {
		// Verify nonce for delete action.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'mjschool_delete_sentbox' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
		}
		if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
			$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			mjschool_append_audit_log( esc_html__( 'Message Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $page_name );
			$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
			foreach ( $ids as $id ) {
				$result = wp_delete_post( intval( $id ) );
				if ( $result ) {
					$nonce = wp_create_nonce( 'mjschool_message_tab' );
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_message&tab=sentbox&_wpnonce=' . $nonce . '&message=2' ) );
					exit;
				}
			}
		}
	}	
	$mjschool_custom_field_obj = new Mjschool_Custome_Field();
	$module                    = 'message';
	$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
	$message_data              = mjschool_get_send_message( get_current_user_id(), $max, $offset );
	if ( ! empty( $message_data ) ) {
		?>
		<form name="wcwm_report" action="" method="post"><!-- Form-div. -->
			<?php wp_nonce_field( 'mjschool_delete_sentbox', '_wpnonce' ); ?>
			<div class="table-responsive" id="sentbox_table"><!-- Table-responsive. -->
				<table id="sent_list" class="table">
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
							<th class="mjschool-custom-padding-0 mjschool_padding_15px_0px"><input type="checkbox" class="select_all" id="select_all"></th>
							<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Message For', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Attachment', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Date & Time', 'mjschool' ); ?></th>
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
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 0;
						foreach ( $message_data as $msg_post ) {
							if ( $i === 10 ) {
								$i = 0;
							}
							$color_classes = array(
								'mjschool-class-color0', 'mjschool-class-color1', 'mjschool-class-color2',
								'mjschool-class-color3', 'mjschool-class-color4', 'mjschool-class-color5',
								'mjschool-class-color6', 'mjschool-class-color7', 'mjschool-class-color8',
								'mjschool-class-color9'
							);
							$color_class_css = $color_classes[ $i ];
							if ( $msg_post->post_author === get_current_user_id() ) {
								?>
								<tr>
									<td class="mjschool-checkbox-width-10px">
										<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $msg_post->ID ); ?>">
									</td>
									<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
										<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-sendbox.png' ); ?>" height="30px" width="30px" class="mjschool-massage-image">
										</p>
									</td>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=sendbox&id=' . rawurlencode( mjschool_encrypt_id( $msg_post->ID ) ) ) ); ?>" class="mjschool-text-decoration-none">
											<span>
												<?php
												$check_message_single_or_multiple = mjschool_send_message_check_single_user_or_multiple( $msg_post->ID );
												if ( $check_message_single_or_multiple === 1 ) {
													global $wpdb;
													$tbl_name = $wpdb->prefix . 'mjschool_message';
													// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
													$get_single_user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $tbl_name WHERE post_id = %d", $msg_post->ID ) );
													$mjschool_role   = mjschool_get_display_name( $get_single_user->receiver );
													echo esc_html( $mjschool_role );
												} else {
													$mjschool_role = get_post_meta( $msg_post->ID, 'message_for', true );
													echo esc_html( $mjschool_role );
												}
												?>
											</span>
										</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Message For', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=sendbox&id=' . rawurlencode( mjschool_encrypt_id( $msg_post->ID ) ) ) ); ?>" class="mjschool-text-decoration-none">
											<span>
												<?php
												$smgt_class_id = get_post_meta( $msg_post->ID, 'smgt_class_id', true );
												if ( $smgt_class_id === '' || $smgt_class_id === 'all' ) {
													esc_html_e( 'All', 'mjschool' );
												} elseif ( $smgt_class_id != '' ) {
													$class_id_array   = explode( ',', $smgt_class_id );
													$class_name_array = array();
													foreach ( $class_id_array as $data ) {
														$class_name_array[] = mjschool_get_class_name( $data );
													}
													echo esc_html( implode( ',', $class_name_array ) );
												} else {
													echo 'NA';
												}
												?>
											</span>
										</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=sendbox&id=' . rawurlencode( mjschool_encrypt_id( $msg_post->ID ) ) ) ); ?>" class="mjschool-text-decoration-none">
											<?php
											$obj_message = new Mjschool_Message();
											$msg_post_id = $obj_message->mjschool_count_reply_item( $msg_post->ID );
											$subject_char = strlen( $msg_post->post_title );
											if ( $subject_char <= 10 ) {
												echo esc_html( $msg_post->post_title );
											} else {
												$subject_body = substr( wp_strip_all_tags( $msg_post->post_title ), 0, 10 ) . '...';
												echo esc_html( $subject_body );
											}
											if ( $msg_post_id >= 1 ) {
												?>
												<span class="badge badge-success pull-right"><?php echo esc_html( $msg_post_id ); ?></span>
												<?php
											}
											?>
										</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php echo ! empty( $msg_post->post_title ) ? esc_attr( $msg_post->post_title ) : esc_attr__( 'Subject', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=sendbox&id=' . rawurlencode( mjschool_encrypt_id( $msg_post->ID ) ) ) ); ?>" class="mjschool-text-decoration-none">
											<?php
											$body_char = strlen( $msg_post->post_content );
											if ( $body_char <= 30 ) {
												echo esc_html( $msg_post->post_content );
											} else {
												$msg_body = substr( wp_strip_all_tags( $msg_post->post_content ), 0, 30 ) . '...';
												echo esc_html( $msg_body );
											}
											?>
										</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php echo ! empty( $msg_post->post_content ) ? esc_attr( $msg_post->post_content ) : esc_attr__( 'Description', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										$attchment = get_post_meta( $msg_post->ID, 'message_attachment', true );
										if ( ! empty( $attchment ) ) {
											$attchment_array = explode( ',', $attchment );
											foreach ( $attchment_array as $attchment_data ) {
												?>
												<a target="blank" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . sanitize_file_name( $attchment_data ) ) ); ?>" class="btn btn-default"><i class="fas fa-download"></i><?php esc_html_e( 'View Attachment', 'mjschool' ); ?></a></br>
												<?php
											}
										} else {
											esc_html_e( 'No Attachment', 'mjschool' );
										}
										?>
									</td>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=view_message&from=sendbox&id=' . rawurlencode( mjschool_encrypt_id( $msg_post->ID ) ) ) ); ?>" class="mjschool-text-decoration-none">
											<?php echo esc_html( mjschool_convert_date_time( $msg_post->post_date_gmt ) ); ?>
										</a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date & Time', 'mjschool' ); ?>"></i>
									</td>
									<?php
									// Custom Field Values.
									if ( ! empty( $user_custom_field ) ) {
										foreach ( $user_custom_field as $custom_field ) {
											if ( $custom_field->show_in_table === '1' ) {
												$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $msg_post->ID, $custom_field->id );
												if ( $custom_field->field_type === 'date' ) {
													?>
													<td><?php echo ! empty( $custom_field_value ) ? esc_html( mjschool_get_date_in_input_box( $custom_field_value ) ) : esc_html__( 'N/A', 'mjschool' ); ?></td>
													<?php
												} elseif ( $custom_field->field_type === 'file' ) {
													?>
													<td>
														<?php
														if ( ! empty( $custom_field_value ) ) {
															?>
															<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . sanitize_file_name( $custom_field_value ) ) ); ?>" download="CustomFieldfile">
																<button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
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
													<td><?php echo ! empty( $custom_field_value ) ? esc_html( $custom_field_value ) : esc_html__( 'N/A', 'mjschool' ); ?></td>
													<?php
												}
											}
										}
									}
									?>
								</tr>
								<?php
								++$i;
							}
						}
						?>
					</tbody>
				</table>
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
			</div><!--Table-responsive. -->
		</form><!-- Form-div. -->
		<?php
	} elseif ( $user_access_add === '1' ) {
		$nonce = wp_create_nonce( 'mjschool_message_tab' );
		?>
		<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=compose&_wpnonce=' . $nonce ) ); ?>">
				<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
			</a>
			<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
				<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
			</div>
		</div>
		<?php
	} else {
		?>
		<div class="mjschool-calendar-event-new">
			<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
		</div>
		<?php
	}
	?>
</div><!-- Mjschool-mailbox-content. -->