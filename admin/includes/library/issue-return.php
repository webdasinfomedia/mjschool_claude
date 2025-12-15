<?php
/**
 * Library Book Issue and Return Management.
 *
 * Handles the issuing and returning of library books for students and teachers 
 * within the MJSchool plugin. This file displays user information, manages book 
 * issue and return operations, and shows an issue history list with details such 
 * as issue date, return date, fine, and comments.
 *
 * Key Features:
 * - Displays detailed user profile with contact and address information.
 * - Issues new books and records return transactions.
 * - Sends email notifications when books are issued (if enabled).
 * - Shows a detailed list of all issued books with DataTables integration.
 * - Calculates and displays fines, due dates, and issue status.
 * - Supports AJAX-based dynamic return date calculation.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/library
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;


$active_tab1 = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
$user_id          = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['user_id'])) ) );
$user_data        = get_userdata( $user_id );
$mjschool_role_name        = mjschool_get_user_role( $user_id );
$mjschool_obj_lib = new Mjschool_Library();
$library_card_no  = $mjschool_obj_lib->mjschool_get_library_card_for_student( $user_id );
?>
<div class="mjschool-panel-body mjschool-view-page-main"><!-- START PANEL BODY DIV.-->
	<div class="content-body">
		<section id="mjschool-user-information">
			<div class="mjschool-view-page-header-bg">
				<div class="row">
					<div class="col-xl-10 col-md-9 col-sm-10">
						<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
							<?php
							$userimage = mjschool_get_user_image( $user_data->ID );
							?>
							<img class="mjschool-user-view-profile-image" src="<?php if ( ! empty( $userimage ) ) { echo esc_url($userimage); } else { if ($mjschool_role_name === "student") { echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); } elseif ($mjschool_role_name === "teacher") { echo esc_url( get_option( 'mjschool_teacher_thumb_new' ) ); } } ?>">
							<div class="row mjschool-profile-user-name">
								<div class="mjschool-float-left mjschool-view-top1">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
										<label class="mjschool-view-user-name-label"><?php echo esc_html( $user_data->display_name ); ?></label>
									</div>
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
										<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<label class="mjschool-color-white-rs"><?php echo esc_html( $user_data->mobile_number); ?></label>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div class="mjschool-view-top2">
										<div class="row mjschool-view-user-doctor-label">
											<div class="col-md-12 mjschool-address-student-div">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page"><?php echo esc_html( $user_data->address); ?></label>
											</div>
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
		</section>
		<section id="body_area" class="teacher_view_tab body_areas">
			<div class="row">
				<div class="col-xl-12 col-md-12 col-sm-12 mjschool-rs-width">
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab1 === 'general' ) { ?>active<?php } ?>">
							<a href="admin.php?page=mjschool_library&tab=issue_return&user_id=<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['user_id'])) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>">
								<i class="fas fa-book"> </i> <?php esc_html_e( 'issue & Return Details', 'mjschool' ); ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</section>
		<section id="mjschool-body-content-area">
			<div class="mjschool-panel-body"><!-- START PANEL BODY DIV.-->
				<?php
				if ( $active_tab1 === 'general' ) {
					if ( isset( $_POST['save_issue_book'] ) ) {
						$result = $mjschool_obj_lib->mjschool_add_issue_book( wp_unslash($_POST) );
						if ( $result ) {
							// Book Issue Mail Notification.
							if ( isset( $_POST['mjschool_issue_book_mail_service_enable'] ) ) {
								foreach ( $_POST['book_id'] as $book_id ) {
									$smgt_issue_book_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_issue_book_mail_service_enable']));
									if ( $smgt_issue_book_mail_service_enable ) {
										$search['{{student_name}}'] = mjschool_get_teacher( sanitize_text_field(wp_unslash($_POST['student_id'])) );
										$search['{{book_name}}']    = mjschool_get_book_name( sanitize_text_field(wp_unslash($book_id)) );
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
							wp_redirect( admin_url() . 'admin.php?page=mjschool_library&tab=issue_return&user_id=' . sanitize_text_field(wp_unslash($_REQUEST['user_id'])) . '&issue_message=issue_success' );
							die();
						}
					}
					if ( isset( $_POST['return_book'] ) ) {
						$result = $mjschool_obj_lib->mjschool_submit_return_book( wp_unslash($_POST) );
						if ( $result ) {
							wp_redirect( admin_url() . 'admin.php?page=mjschool_library&tab=issue_return&user_id=' . sanitize_text_field(wp_unslash($_REQUEST['user_id'])) . '&issue_message=return_success' );
							die();
						}
					}
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
					?>
					<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
						<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
							<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br/>
							<label class="mjschool-view-page-content-labels"> <?php echo esc_html( $user_data->user_email ); ?> </label>
						</div>
						<div class="col-xl-3 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
							<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Library Card No', 'mjschool' ); ?> </label><br/>
							<label class="mjschool-view-page-content-labels">
								<?php
								if ( ! empty( $library_card_no ) ) {
									$library_card = $library_card_no[0]->library_card_no;
									if ( ! empty( $library_card ) ) {
										echo esc_html( $library_card );
									} else {
										esc_html_e( 'N/A', 'mjschool' );
									}
								} else {
									esc_html_e( 'N/A', 'mjschool' );
								}
								?>
							</label>
						</div>
						<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
							<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br/>
							<label class="mjschool-view-page-content-labels"> <?php echo esc_html( ucfirst( $user_data->gender ) ); ?></label>
						</div>
						<div class="col-xl-3 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
							<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br/>
							<label class="mjschool-view-page-content-labels"> 
								<?php
								if ( ! empty( $user_data->birth_date ) ) {
									echo esc_html( mjschool_get_date_in_input_box( $user_data->birth_date ) );
								} else {
									esc_html_e( 'N/A', 'mjschool' );
								}
								?>
							</label>
						</div>
					</div>
					<div class="row mjschool-margin-top-20px">
						<div class="col-xl-12 col-md-12 col-sm-12">
							<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
								<div class="mjschool-guardian-div">
									<form name="book_form" action="" method="post" class="mjschool-form-horizontal" id="book_form">
										<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
										<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
										<input type="hidden" name="issue_id" value="<?php echo esc_attr( $issuebook_id ); ?>">
										<input type="hidden" name="student_id" value="<?php echo esc_attr( $user_id ); ?>">
										<div class="header">
											<h3 class="mjschool-first-header"><?php esc_html_e( 'Issue Book Information', 'mjschool' ); ?></h3>
										</div>
										<div class="form-body mjschool-user-form">
											<div class="row">
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<?php
															$library_card_name = '';
															if ( ! empty( $library_card_no ) ) {
																$library_card = $library_card_no[0]->library_card_no;
																if ( ! empty( $library_card ) ) {
																	$library_card_name = $library_card;
																}
															}
															?>
															<input id="library_card" class="form-control validate[required,custom[address_description_validation]]" type="text" maxlength="50" value="<?php echo esc_attr( $library_card_name ); ?>" name="library_card" <?php if ( ! empty( $library_card_name ) ) { echo 'readonly'; } ?>>
															<label  for="library_card"><?php esc_html_e( 'Library Card No.', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
												<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-error-msg-left-margin mjschool-rtl-margin-0px">
													<label class="ml-1 mjschool-custom-top-label top" for="category_data"><?php esc_html_e( 'Select Category', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													<select name="bookcat_id" id="bookcat_list" class="form-control validate[required] mjschool-max-width-100px">
														<option value=""><?php esc_html_e( 'Select Category', 'mjschool' ); ?></option>
														<?php
														$book_cat = '';
														if ( $edit ) {
															$book_cat = $result->cat_id;
														}
														$category_data = $mjschool_obj_lib->mjschool_get_bookcat();
														if ( ! empty( $category_data ) ) {
															foreach ( $category_data as $retrieved_data ) {
																echo '<option value="' . esc_attr( $retrieved_data->ID ) . '" ' . selected( $book_cat, $retrieved_data->ID ) . '>' . esc_html( $retrieved_data->post_title ) . '</option>';
															}
														}
														?>
													</select>
												</div>
												<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-error-msg-top-margin">
													<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
														<?php
														$book_id    = 0;
														$books_data = $mjschool_obj_lib->mjschool_get_all_books();
														?>
														<select name="book_id[]" id="book_list1" multiple="multiple" class="form-control validate[required]"></select>
														<span class="mjschool-multiselect-label">
															<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Book', 'mjschool' ); ?><span class="required">*</span></label>
														</span>
													</div>
												</div>
												<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
													<label class="ml-1 mjschool-custom-top-label top" for="period"><?php esc_html_e( 'Period', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
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
												<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-error-msg-left-margin">
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
																	<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="enable"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
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
									$issue_data = $mjschool_obj_lib->mjschool_get_all_issuebooks_for_student( $user_id );
									if ( ! empty( $issue_data ) ) {
										?>
										<div class="table-responsive">
											<form id="mjschool-common-form" name="mjschool-common-form" method="post">
												<table id="user_issue_list" class="display" cellspacing="0" width="100%">
													<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
														<tr>
															<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Book Title', 'mjschool' ); ?></th>
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
															$book_data = $mjschool_obj_lib->mjschool_get_single_books( $retrieved_data->book_id );
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
																
																<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-library.png"); ?>" class="img-circle" /></td>
																
																<td><?php echo esc_html( stripslashes( mjschool_get_book_name( $retrieved_data->book_id ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Book Title', 'mjschool' ); ?>"></i></td>
																<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->issue_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Issue Date', 'mjschool' ); ?>"></i> </td>
																<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Due Return Date', 'mjschool' ); ?>"></i></td>
																<td>
																	<?php
																	if ( ! empty( $retrieved_data->actual_return_date ) ) {
																		echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->actual_return_date ) );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
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
																		esc_html_e( 'N/A', 'mjschool' );
																	} else {
																		echo esc_html( mjschool_get_currency_symbol() ) . esc_html( $retrieved_data->fine );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Fine', 'mjschool' ); ?>"></i>
																</td>
																<td >
																	<?php
																	if ( $retrieved_data->comment === '' ) {
																		esc_html_e( 'N/A', 'mjschool' );
																	} else {
																		echo esc_html( $retrieved_data->comment );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top"  title="<?php if ( $retrieved_data->comment === '' ) { echo 'Comment'; } else { echo esc_html( $retrieved_data->comment ); } ?>"></i>
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
																					if ($retrieved_data->status === "Issue") {
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
					<?php
				}
				?>
			</div>
		</section>
	</div>
</div>