<?php
/**
 * View Book Details and Issue Management Page.
 *
 * This file displays detailed information about a specific book and
 * allows administrators to issue or return books to users. It includes
 * options for book editing, email notifications, and viewing issued book lists.
 *
 * @package    Mjschool
 * @subpackage MJSchool/admin/includes/library
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$mjschool_obj_lib = new Mjschool_Library();
if ( isset( $_GET['book_id'] ) && is_array( $_GET['book_id'] ) ) {
	$book_id = reset( $_GET['book_id'] ); // Get the first book_id from the array.
} else {
	$book_id = sanitize_text_field(wp_unslash($_GET['book_id'])) ?? ''; // Use single value if not an array.
}
// Now safely pass it to the function.
$decoded_id                = mjschool_decrypt_id( $book_id );
$book_data                 = $mjschool_obj_lib->mjschool_get_single_books( $decoded_id );
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
?>
<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start Panel Body Div.-->
	<div class="content-body">
		<section id="mjschool-user-information">
			<div class="mjschool-view-page-header-bg">
				<div class="row">
					<div class="col-xl-10 col-md-9 col-sm-10">
						<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
							<img class="mjschool-user-view-profile-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-library.png"); ?>">
							<div class="row mjschool-profile-user-name">
								<div class="mjschool-float-left mjschool-view-top1">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
										<span class="mjschool-view-user-name-label"><?php echo esc_html( ucfirst($book_data->book_name ) ); ?></span>
										<div class="mjschool-view-user-edit-btn">
											<a class="mjschool-color-white mjschool-margin-left-2px" href="?page=mjschool_library&tab=addbook&action=edit&book_id=<?php echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['book_id']))); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2 mjschool-add-btn_possition_teacher_res">
						<div class="mjschool-group-thumbs">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
						</div>
					</div>
				</div>
			</div>
			<?php  
			?>
		</section>
		<section id="mjschool-body-content-area" class="mt-5">
			<div class="mjschool-panel-body"><!-- Start Panel Body Div. -->
				<?php
				if ( isset( $_REQUEST['issue_message'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['issue_message'])) === 'issue_success' ) ) {
					?>
					<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
						<p><?php echo esc_attr__( 'Book Issued Successfully.', 'mjschool' ); ?></p>
						<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
					</div>
					<?php
				}
				if ( isset( $_REQUEST['issue_message'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['issue_message'])) === 'return_success' ) ) {
					?>
					<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
						<p><?php echo esc_attr__( 'Book Returned Successfully.', 'mjschool' ); ?></p>
						<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
					</div>
					<?php
				}
				if ( isset( $_REQUEST['issue_message'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['issue_message'])) === 'exits_no' ) ) {
					?>
					<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
						<p><?php echo esc_attr__( 'Library Card No is Exits.', 'mjschool' ); ?></p>
						<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
					</div>
					<?php
				}
				?>
				<div class="row">
					<div class="col-xl-12 col-md-12 col-sm-12">
						<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px-rs">
							<div class="mjschool-guardian-div">
								<span class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Book Information', 'mjschool' ); ?> </span>
								<div class="row">
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
										<span class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'ISBN', 'mjschool' ); ?> </span> <br>
										<?php
										if ( $user_access_edit === '1' && empty( $book_data->ISBN ) ) {
											$edit_url = admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<span class="mjschool-view-page-content-labels">
												<?php
												if ( ! empty( $book_data->ISBN ) ) {
													echo esc_html( $book_data->ISBN );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</span>
										<?php } ?>
									</div>
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
										<span class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Book Number', 'mjschool' ); ?> </span><br>
										<?php
										if ( $user_access_edit === '1' && empty( $book_data->book_number ) ) {
											$edit_url = admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<span class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
												<?php
												if ( ! empty( $book_data->book_number ) ) {
													echo esc_html( $book_data->book_number );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
												</span>
										<?php } ?>
									</div>
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
										<span class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Book Category', 'mjschool' ); ?> </span><br>
										<?php
										if ( $user_access_edit === '1' && empty( $book_data->cat_id ) ) {
											$edit_url = admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<span class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
												<?php
												if ( ! empty( $book_data->cat_id ) ) {
													echo esc_html( get_the_title( $book_data->cat_id ) );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</span>
										<?php } ?>
									</div>
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
										<span class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Author Name', 'mjschool' ); ?> </span><br>
										<?php
										if ( $user_access_edit === '1' && empty( $book_data->author_name ) ) {
											$edit_url = admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<span class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
												<?php
												if ( ! empty( $book_data->author_name ) ) {
													echo esc_html( $book_data->author_name );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</span>
										<?php } ?>
									</div>
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
										<span class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Publisher', 'mjschool' ); ?> </span><br>
										<?php
										if ( $user_access_edit === '1' && empty( $book_data->publisher ) ) {
											$edit_url = admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<span class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
												<?php
												if ( ! empty( $book_data->publisher ) ) {
													echo esc_html( $book_data->publisher );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</span>
										<?php } ?>
									</div>
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
										<span class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Rack Location', 'mjschool' ); ?> </span><br>
										<?php
										if ( $user_access_edit === '1' && empty( $book_data->rack_location ) ) {
											$edit_url = admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<span class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
												<?php
												if ( ! empty( $book_data->rack_location ) ) {
													echo esc_html( get_the_title( $book_data->rack_location ) );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</span>
										<?php } ?>
									</div>
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
										<span class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Book Price', 'mjschool' ); ?> </span><br>
										<?php
										if ( $user_access_edit === '1' && empty( $book_data->price ) ) {
											$edit_url = admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<span class="mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $book_data->price, 2, '.', '' ) ) ); ?></span>
										<?php } ?>
									</div>
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
										<span class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Remaining Quantity', 'mjschool' ); ?> </span><br>
										<?php
										if ( $user_access_edit === '1' && empty( $book_data->total_quentity ) ) {
											$edit_url = admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<span class="mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( $book_data->quentity ) . ' ' . esc_html__( 'Out Of', 'mjschool' ) . ' ' . esc_html( $book_data->total_quentity ); ?></span>
										<?php } ?>
									</div>
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px">
										<span class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Description', 'mjschool' ); ?> </span><br>
										<?php
										if ( $user_access_edit === '1' && empty( $book_data->description ) ) {
											$edit_url = admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<span>
												<?php
												if ( ! empty( $book_data->description ) ) {
													echo esc_html( $book_data->description );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</span>
										<?php } ?>
									</div>
								</div>
							</div>
							<?php
							$module = 'library';
							$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
							?>
						</div>
						<?php
						if ( isset( $_POST['save_issue_book'] ) ) {
							if( isset( $_POST['library_card'] ) ){
								$exits = $mjschool_obj_lib->mjschool_exits_library_card_no_submit(sanitize_text_field(wp_unslash($_POST['library_card'])),sanitize_text_field(wp_unslash($_POST['student_id'])));
								if ( $exits > 0){
									wp_redirect( admin_url() . 'admin.php?page=mjschool_library&tab=view_book&book_id=' . sanitize_text_field(wp_unslash($_GET['book_id'])) . '&issue_message=exits_no' );
								}	
								else{
									$result = $mjschool_obj_lib->mjschool_add_issue_book( wp_unslash($_POST) );
								}
							}
							if ( isset($result) ) {
								// Book Issue Mail Notification.
								if ( isset( $_POST['mjschool_issue_book_mail_service_enable'] ) ) {
									foreach ( $_POST['book_id'] as $b_id ) {
										$smgt_issue_book_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_issue_book_mail_service_enable']));
										if ( $smgt_issue_book_mail_service_enable ) {
											$search['{{student_name}}'] = mjschool_get_teacher( sanitize_text_field(wp_unslash($_POST['student_id'])) );
											$search['{{book_name}}']    = mjschool_get_book_name( sanitize_text_field(wp_unslash($b_id)) );
											$search['{{issue_date}}']   = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['issue_date'])) );
											$search['{{return_date}}']  = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['return_date'])) );
											$search['{{school_name}}']  = get_option( 'mjschool_name' );
											$message                    = mjschool_string_replacement( $search, get_option( 'mjschool_issue_book_mailcontent' ) );
											$mail_id                    = mjschool_get_email_id_by_user_id( sanitize_text_field(wp_unslash($_POST['student_id'])) );
											$headers    = '';
											$headers   .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
											$headers   .= "MIME-Version: 1.0\r\n";
											$headers   .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
											if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
												wp_mail( $mail_id, get_option( 'mjschool_issue_book_title' ), $message, $headers);
											}
										}
									}
								}
								wp_redirect( admin_url() . 'admin.php?page=mjschool_library&tab=view_book&book_id=' . sanitize_text_field(wp_unslash($_GET['book_id'])) . '&issue_message=issue_success' );
								die();
							}
						}
						if ( isset( $_POST['return_book'] ) ) {
							$result = $mjschool_obj_lib->mjschool_submit_return_book( wp_unslash($_POST) );
							wp_redirect( admin_url() . 'admin.php?page=mjschool_library&tab=view_book&book_id=' . sanitize_text_field(wp_unslash($_GET['book_id'])) . '&issue_message=return_success' );
							die();
						}
						?>
						<div class="col-xl-12 col-md-12 col-sm-12 mt-3 mjschool-margin-top-15px-rs">
							<div class="mjschool-guardian-div">
								<span class="mjschool-view-page-label-heading mb-4"> <?php esc_html_e( 'Issue Book Information', 'mjschool' ); ?> </span>
								<form name="issue_book_form" action="" method="post" class="mjschool-form-horizontal" id="issue_book_form">
									<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
									<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
									<input type="hidden" name="issue_id" value="">
									<input type="hidden" name="book_id" value="<?php echo esc_attr( $decoded_id ); ?>">
									<input type="hidden" name="bookcat_id" value="<?php echo esc_attr( $book_data->cat_id ); ?>">
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input">
												<label class="ml-1 mjschool-custom-top-label top" for="student_id"><?php esc_html_e( 'Select User', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												<select name="student_id" id="student_id" class="form-control change_library_card validate[required] mjschool-max-width-100px">
													<option value=""><?php esc_html_e( 'Select User', 'mjschool' ); ?></option>
													<?php echo esc_html( mjschool_get_student_and_teacher_for_library() ); ?>
												</select>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="issue_library_card" class="form-control validate[required,custom[address_description_validation]] library_card" type="text" maxlength="50" value="" name="library_card">
														<label  for="issue_library_card"><?php esc_html_e( 'Library Card No.', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
												<label class="ml-1 mjschool-custom-top-label top" for="category_data"><?php esc_html_e( 'Period', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												<select name="period_id" id="category_data" class="form-control issue_period validate[required] mjschool-max-width-100px period_type">
													<option value=""><?php esc_html_e( 'Select Period', 'mjschool' ); ?></option>
													<?php
													if ( $edit ) {
														$period_id = $result->period;
													} else {
														$period_id = get_option( 'mjschool_return_period' );
													}
													$category_data = $mjschool_obj_lib->mjschool_get_period_list();
													if ( ! empty( $category_data ) ) {
														foreach ( $category_data as $retrieved_data ) {
															echo '<option value="' . esc_attr( $retrieved_data->ID ) . '" ' . selected( $period_id, $retrieved_data->ID ) . '>' . esc_html( $retrieved_data->post_title ) . ' ' . esc_attr__( 'Days', 'mjschool' ) . '</option>';
														}
													}
													?>
												</select>
											</div>
											<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3">
												<button id="mjschool-addremove-cat" class="mjschool-rtl-margin-top-15px mjschool-add-btn sibling_add_remove" model="period_type"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>
											</div>
											<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="issue_date" class="datepicker form-control validate[required] text-input" type="text" name="issue_date" value="<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); ?>" readonly>
														<label  for="issue_date"><?php esc_html_e( 'Issue Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
											</div>
											<?php wp_nonce_field( 'save_issuebook_admin_nonce' ); ?>
											<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="return_date" class="form-control validate[required] date_picker text-input" type="text" name="return_date" value="" readonly>
														<label class="active date_label" for="return_date"><?php esc_html_e( 'Return Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
												<div class="form-group">
													<div class="col-md-12 form-control mjschool-rtl-relative-position">
														<div class="row mjschool-padding-radio">
															<div>
																<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="chk_mjschool_sent1"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
																<input id="chk_mjschool_sent1" type="checkbox" class="mjschool-check-box-input-margin" <?php $smgt_issue_book_mail_service_enable = 0; if ( $smgt_issue_book_mail_service_enable ) { echo 'checked'; } ?> value="1" name="smgt_issue_book_mail_service_enable"> <?php esc_html_e( 'Send Mail', 'mjschool' ); ?>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-sm-6">
												<input type="submit" value="<?php esc_html_e( 'Issue Book', 'mjschool' ); ?>" name="save_issue_book" class="mjschool-save-btn btn btn-success book_for_alert mjschool-rtl-margin-0px" />
											</div>
										</div>
									</div>
								</form>
							</div>
							<div class="mjschool-panel-body mt-3">
								<div class="header">
									<h3 class="mjschool-first-header"><?php esc_html_e( 'Issue Book List', 'mjschool' ); ?></h3>
								</div>
								<?php
								$issue_data = $mjschool_obj_lib->mjschool_get_all_issuebooks_book_id( $decoded_id );
								if ( ! empty( $issue_data ) ) {
									?>
									<div class="table-responsive">
										<form id="mjschool-common-form" name="mjschool-common-form" method="post">
											<table id="user_issue_list" class="display" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'User Name', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Issue Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Due Return Date ', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Accept Return Date ', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Period', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Fine', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
														<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													$i = 0;
													foreach ( $issue_data as $retrieved_data ) {
														?>
														<tr>
															<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-library.png"); ?>" class="img-circle" /></td>
															<td><?php echo esc_html( mjschool_get_display_name( $retrieved_data->student_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'User Name', 'mjschool' ); ?>"></i></td>
															<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->issue_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Issue Date', 'mjschool' ); ?>"></i> </td>
															<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Due Return Date', 'mjschool' ); ?>"></i></td>
															<td>
																<?php
																if ( ! empty( $retrieved_data->actual_return_date ) ) {
																	echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->actual_return_date ) );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Accept Return Date', 'mjschool' ); ?>"></i>
															</td>
															<td><?php echo esc_html( get_the_title( $retrieved_data->period ) ); ?> <?php esc_html_e( 'Day', 'mjschool' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Period', 'mjschool' ); ?>"></i></td>
															<td >
																<?php
																if ( $retrieved_data->status === 'Issue' ) {
																	esc_html_e( 'Issued', 'mjschool' );
																} elseif ( $retrieved_data->status === 'Submitted' ) {
																	esc_html_e( 'Returned', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
															</td>
															<td >
																<?php
																if ( $retrieved_data->fine === '' || $retrieved_data->fine === 0 ) {
																	esc_html_e( 'Not Provided', 'mjschool' );
																} else {
																	echo esc_html( mjschool_get_currency_symbol() ) . esc_html( $retrieved_data->fine );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Fine', 'mjschool' ); ?>"></i>
															</td>
															<td >
																<?php
																if ( $retrieved_data->comment === '' ) {
																	esc_html_e( 'Not Provided', 'mjschool' );
																} else {
																	echo esc_attr( $retrieved_data->comment );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( $retrieved_data->comment === '' ) { echo 'Comment'; } else { echo esc_html( $retrieved_data->comment ); } ?>"></i>
															</td>
															<td class="action">
																<div class="mjschool-user-dropdown">
																	<ul  class="mjschool_ul_style">
																		<li >
																			<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																				<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																			</a>
																			<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																				<?php
																				if ( $retrieved_data->status === 'Issue' ) {
																					?>
																					<li class="mjschool-float-left-width-100px">
																						<a idtest=<?php echo esc_attr($retrieved_data->id); ?> id="accept_returns_book_popup" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-accept-book-return.png"); ?>" class="mjschool_height_15px">&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Accept Returns', 'mjschool' ); ?> </a>
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
													}
													?>
												</tbody>
											</table>
										</form>
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
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>