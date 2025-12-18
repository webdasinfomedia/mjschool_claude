<?php
/**
 * View Homework Details and Submissions Page.
 *
 * This file manages the display of homework details, student submissions,
 * and homework evaluation functionality within the MJSchool plugin. It
 * allows administrators and teachers to view, review, and evaluate homework
 * assignments submitted by students.
 *
 * Key Features:
 * - Displays detailed homework information including subject, class, dates, marks, and attachments.
 * - Provides tab-based navigation for Homework Details, Submissions, and Evaluation.
 * - Allows teachers to evaluate student submissions with marks, comments, and uploaded review files.
 * - Integrates DataTables for dynamic listing and search functionality of student submissions.
 * - Implements WordPress nonces for secure edit and view actions.
 * - Validates and sanitizes user inputs and uploaded files before processing.
 * - Uses custom helper functions from MJSchool for encryption, formatting, and retrieval of related data.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/student-homework
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$active_tab1      = isset( $_REQUEST['tab1'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab1'] ) ) : 'general';
$objj             = new Mjschool_Homework();
$homework_id      = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) );
$custom_field_obj = new Mjschool_Custome_Field();
$homeworkdata     = $objj->mjschool_get_edit_record( $homework_id );
?>
<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start panel body div. -->
	<div class="content-body"><!-- Start content body div. -->
		<section id="mjschool-user-information">
			<div class="mjschool-view-page-header-bg">
				<div class="row">
					<div class="col-xl-10 col-md-9 col-sm-10">
						<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
							<img class="mjschool-user-view-profile-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-homework-detail.png' ); ?>">
							<div class="row mjschool-profile-user-name">
								<div class="mjschool-float-left mjschool-view-top1">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
										<span class="mjschool-view-user-name-label"><?php echo esc_html( $homeworkdata->title ); ?></span>
										<div class="mjschool-view-user-edit-btn">
											<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=' . rawurlencode( mjschool_encrypt_id( $homeworkdata->homework_id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-edit.png' ); ?>">
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2 mjschool-add-btn_possition_teacher_res">
						<div class="mjschool-group-thumbs">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-group.png' ); ?>">
						</div>
					</div>
				</div>
			</div>
		</section>
		<section id="body_area" class="teacher_view_tab body_areas">
			<div class="row">
				<div class="col-xl-12 col-md-12 col-sm-12 mjschool-rs-width">
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="
						<?php
						if ( $active_tab1 === 'general' ) {
							?>
							active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=view_homework&tab1=general&id=' . rawurlencode( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1 ) === 'general' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Homework Details', 'mjschool' ); ?>
							</a>
						</li>
						<li class="
						<?php
						if ( $active_tab1 === 'submission' ) {
							?>
							active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=view_homework&tab1=submission&id=' . rawurlencode( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1 ) === 'submission' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Submissions', 'mjschool' ); ?>
							</a>
						</li>
						<?php
						if ( $active_tab1 === 'review_homework' ) {
							?>
							<li class="
							<?php
							if ( $active_tab1 === 'review_homework' ) {
								?>
								active<?php } ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=view_homework&tab1=review_homework&id=' . rawurlencode( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) . '&stud_homework_id=' . rawurlencode( sanitize_text_field( wp_unslash( $_REQUEST['stud_homework_id'] ) ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1 ) === 'review_homework' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Evaluate Homework', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
			</div>
		</section>
		<section id="mjschool-body-content-area">
			<div class="mjschool-panel-body"><!-- Start panel body div. -->
				<?php
				if ( $active_tab1 === 'general' ) {
					?>
					<div class="row">
						<div class="col-xl-12 col-md-12 col-sm-12">
							<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px-rs">
								<div class="mjschool-guardian-div">
									<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Homework Information', 'mjschool' ); ?></label>
									<div class="row">
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Subject', 'mjschool' ); ?></label><br>
											<?php
											if ( $user_access_edit === '1' && empty( $homeworkdata->subject ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=' . rawurlencode( mjschool_encrypt_id( $homeworkdata->homework_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_subject_by_id( $homeworkdata->subject ) ); ?></label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Class', 'mjschool' ); ?></label><br>
											<?php
											if ( $user_access_edit === '1' && empty( $homeworkdata->class_name ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=' . rawurlencode( mjschool_encrypt_id( $homeworkdata->homework_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_class_section_name_wise( $homeworkdata->class_name, $homeworkdata->section_id ) ); ?></label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></label><br>
											<?php
											$birth_date      = $homeworkdata->created_date;
											$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
											if ( $user_access_edit === '1' && $is_invalid_date ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=' . rawurlencode( mjschool_encrypt_id( $homeworkdata->homework_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
													<?php
													if ( ! empty( $homeworkdata->created_date ) ) {
														echo esc_html( mjschool_get_date_in_input_box( $homeworkdata->created_date ) );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></label><br>
											<?php
											$birth_date      = $homeworkdata->submition_date;
											$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
											if ( $user_access_edit === '1' && $is_invalid_date ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=' . rawurlencode( mjschool_encrypt_id( $homeworkdata->homework_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_date_in_input_box( $homeworkdata->submition_date ) ); ?></label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Marks', 'mjschool' ); ?></label><br>
											<?php
											if ( $user_access_edit === '1' && empty( $homeworkdata->marks ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=' . rawurlencode( mjschool_encrypt_id( $homeworkdata->homework_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels">
													<?php
													if ( ! empty( $homeworkdata->marks ) ) {
														echo esc_html( $homeworkdata->marks );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Documents Title', 'mjschool' ); ?> </label><br>
											<?php
											$doc_data = json_decode( $homeworkdata->homework_document );
											if ( $user_access_edit === '1' && empty( $doc_data[0]->title ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=' . rawurlencode( mjschool_encrypt_id( $homeworkdata->homework_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels">
													<?php
													$doc_data = json_decode( $homeworkdata->homework_document );
													if ( ! empty( $doc_data[0]->title ) ) {
														echo esc_html( $doc_data[0]->title );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Download File', 'mjschool' ); ?></label><br>
											<?php
											$doc_data = json_decode( $homeworkdata->homework_document );
											if ( $user_access_edit === '1' && empty( $doc_data[0]->value ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=' . rawurlencode( mjschool_encrypt_id( $homeworkdata->homework_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels">
													<?php
													if ( ! empty( $doc_data[0]->value ) ) {
														?>
														<a download href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value ) ); ?>"  class="btn mjschool-custom-padding-0 popup_download_btn" record_id="<?php echo esc_attr( $homeworkdata->homework_id ); ?>"><i class="fas fa-download" id="mjschool-download-icon"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
														<?php
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px mt-3">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Homework Content', 'mjschool' ); ?></label><br>
											<?php
											if ( $user_access_edit === '1' && empty( $homeworkdata->content ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=' . rawurlencode( mjschool_encrypt_id( $homeworkdata->homework_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label >
													<?php
													if ( ! empty( $homeworkdata->content ) ) {
														echo esc_html( $homeworkdata->content );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
							<?php
							$module = 'homework';
							$custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
							?>
						</div>
					</div>
					<?php
				}
				if ( $active_tab1 === 'submission' ) {
					if ( isset( $_REQUEST['review_success'] ) && sanitize_text_field( wp_unslash( $_REQUEST['review_success'] ) ) === 'review_success' ) {
						?>
						<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
							<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span>
							</button>
							<?php esc_html_e( 'Homework Evaluated Successfully.', 'mjschool' ); ?>
						</div>
						<?php
					}
					$retrieve_class_data = $objj->mjschool_view_submission( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) );
					if ( ! empty( $retrieve_class_data ) ) {
						?>
						<div class="table-responsive"><!-- Table responsive div. --> 	
							<form id="mjschool-common-form" name="mjschool-common-form" method="post">
								<table id="submission_list" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Submitted Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Evaluate Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Total Marks', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
											<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										foreach ( $retrieve_class_data as $retrieved_data ) {
											$student = get_userdata( $retrieved_data->student_id );
											if ( ! empty( $student ) ) {
												?>
												<tr>
													<td class="mjschool-padding-left-0 mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">	
														<?php
														$umetadata = mjschool_get_user_image( $retrieved_data->student_id );
														if ( empty( $umetadata ) ) {
															echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
														} else {
															echo '<img src=' . esc_url( $umetadata ) . ' class="img-circle" />';
														}
														?>
													</td>
													<td>
														<a  href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&tab=view_student&action=view_student&student_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->student_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>"><?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) ); ?></a> 
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
													</td>
													<td><?php echo esc_html( mjschool_get_class_name( $retrieved_data->class_name ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_subject_by_id( $retrieved_data->subject ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?>  <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submission Date', 'mjschool' ); ?>"></i></td>
													<?php
													if ( $retrieved_data->uploaded_date === 0000 - 00 - 00 ) {
														?>
														<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Not Provided', 'mjschool' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td> 
														<?php
													} else {
														?>
														<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->uploaded_date ) ); ?>  <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td>
														<?php
													}
													?>
													<td>
														<?php
														if ( ! empty( $retrieved_data->evaluate_date ) ) {
															echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->evaluate_date ) );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Evaluate Date', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->marks ) ) {
															echo esc_html( $retrieved_data->marks );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Total Marks', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->obtain_marks ) ) {
															echo esc_html( $retrieved_data->obtain_marks );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks Obtained', 'mjschool' ); ?>"></i>
													</td>
													<?php
													if ( $retrieved_data->status === 1 ) {
														if ( date( 'Y-m-d', strtotime( $retrieved_data->uploaded_date ) ) <= $retrieved_data->submition_date ) {
															?>
															<td><span class="mjschool-homework-submitted"><?php esc_html_e( 'Submitted', 'mjschool' ); ?></span> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
															<?php
														} else {
															?>
															<td><span class="mjschool-homework-submitted"><?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?></span> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
															<?php
														}
													} elseif ( $retrieved_data->status === 2 ) {
														?>
														<td><span class="mjschool-homework-evaluated"><?php esc_html_e( 'Evaluated', 'mjschool' ); ?></span> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
														<?php
													} else {
														?>
														<td><span class="mjschool-homework-pending"><?php esc_html_e( 'Pending', 'mjschool' ); ?></span> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
														<?php
													}
													?>
													<td class="action">
														<div class="mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li >
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<?php
																		if ( $retrieved_data->status != 0 ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=view_homework&tab1=review_homework&id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->homework_id ) ) . '&stud_homework_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->stu_homework_id ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-check"></i><?php esc_html_e( 'Evaluate Homework', 'mjschool' ); ?></a>
																			</li>
																			<?php
																		}
																		if ( $retrieved_data->status === 1 ) {
																			echo '';
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
							</form>
						</div><!-- Table responsive div. --> 
						<?php
					} else {
						?>
						<div class="mjschool-calendar-event-new"> 
							<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
						</div>
						<?php
					}
				}
				if ( $active_tab1 === 'review_homework' ) {
					$homework_obj  = new Mjschool_Homework();
					$data          = $homework_obj->mjschool_get_student_submitted_homework( intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['stud_homework_id'] ) ) ) ) );
					$homework_data = $homework_obj->mjschool_get_edit_record( $data->homework_id );
					if ( isset( $_POST['student_review_homework'] ) ) {
						// Initialize variables.
						$file_name = '';
						// File upload handling.
						if ( ! empty( $_FILES['review_file']['name'] ) ) {
							$randm           = mt_rand( 5, 15 ); // Generate random number.
							$file_name       = 'H' . $randm . '_' . sanitize_file_name( wp_unslash( $_FILES['review_file']['name'] ) );
							$file_tmp        = sanitize_text_field( wp_unslash( $_FILES['review_file']['tmp_name'] ) );
							$upload          = wp_upload_dir();
							$upload_dir_path = $upload['basedir'];
							$upload_dir      = $upload_dir_path . '/homework_file';
							// Ensure the upload directory exists.
							if ( ! file_exists( $upload_dir ) ) {
								if ( ! mkdir( $upload_dir, 0700, true ) && ! is_dir( $upload_dir ) ) {
									wp_die( esc_html__( 'Failed to create upload directory.', 'mjschool' ) );
								}
							}
							// Move uploaded file.
							if ( ! move_uploaded_file( $file_tmp, $upload_dir . '/' . $file_name ) ) {
								wp_die( esc_html__( 'Failed to upload file.', 'mjschool' ) );
							}
						}
						// Retrieve and sanitize POST data.
						$stud_homework_id = isset( $_POST['stu_homework_id'] ) ? intval( wp_unslash( $_POST['stu_homework_id'] ) ) : 0;
						$obtain_marks     = isset( $_POST['obtain_marks'] ) ? sanitize_text_field( wp_unslash( $_POST['obtain_marks'] ) ) : '';
						$teacher_comment  = isset( $_POST['teacher_comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['teacher_comment'] ) ) : '';
						$evaluate_date    = date( 'Y-m-d' );
						$status           = 2; // Assuming 2 is the evaluated status.
						$result           = $objj->mjschool_update_student_homework( $stud_homework_id, $file_name, $obtain_marks, $teacher_comment, $evaluate_date, $status );
						// Redirect with an appropriate message.
						if ( $result !== false ) {
							wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=view_homework&tab1=submission&id=' . mjschool_encrypt_id( $data->homework_id ) . '&review_success=review_success' ) ) );
							exit;
						} else {
							wp_die( esc_html__( 'Failed to update homework review.', 'mjschool' ) );
						}
					}
					if ( isset( $_REQUEST['review_success'] ) && sanitize_text_field( wp_unslash( $_REQUEST['review_success'] ) ) === 'review_success' ) {
						?>
						<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
							<p><?php esc_html_e( 'Homework Evaluated Successfully.', 'mjschool' ); ?></p>
							<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
						</div>
						<?php
					}
					?>
					<div class="row">
						<div class="col-xl-12 col-md-12 col-sm-12">
							<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px-rs">
								<div class="mjschool-guardian-div">
									<div class="header">	
										<h3 class="mjschool-first-header"><?php esc_html_e( 'Homework Information', 'mjschool' ); ?></h3>
									</div>
									<div class="row">
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Student Name', 'mjschool' ); ?></label><br>
											<label class="mjschool-view-page-content-labels"><?php echo esc_html( ucfirst( mjschool_get_display_name( $data->student_id ) ) ); ?></label>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Class', 'mjschool' ); ?></label><br>
											<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_class_section_name_wise( $homework_data->class_name, $homework_data->section_id ) ); ?></label>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Subject', 'mjschool' ); ?></label><br>
											<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_subject_by_id( $homework_data->subject ) ); ?></label>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Uploaded Document', 'mjschool' ); ?></label><br>
											<label class="mjschool-view-page-content-labels">
												<?php
												if ( ! empty( $data->file ) ) {
													?>
													<a download href="<?php echo esc_url( content_url( '/uploads/homework_file/' . $data->file ) ); ?>" class="btn mjschool-custom-padding-0 popup_download_btn" record_id="<?php echo esc_attr( $data->stu_homework_id ); ?>" download><i class="fas fa-download"></i>&nbsp;&nbsp;<?php esc_html_e( 'Download', 'mjschool' ); ?></a>
													<?php
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</label>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></label> <br>
											<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_date_in_input_box( $homework_data->submition_date ) ); ?></label>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Submitted Date', 'mjschool' ); ?> </label> <br>
											<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_date_in_input_box( $data->uploaded_date ) ); ?></label>
										</div>
										<?php
										if ( $data->status === '2' ) {
											?>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Evaluate Date', 'mjschool' ); ?></label><br>
												<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_date_in_input_box( $data->evaluate_date ) ); ?></label>
											</div>
											<?php
										}
										?>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Total Marks', 'mjschool' ); ?></label><br>
											<label class="mjschool-view-page-content-labels">
												<?php
												if ( ! empty( $homework_data->marks ) ) {
													echo esc_html( $homework_data->marks );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</label>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Status', 'mjschool' ); ?></label><br>
											<label class="mjschool-view-page-content-labels">
												<?php
												if ( $data->status === '1' ) {
													if ( date( 'Y-m-d', strtotime( $data->uploaded_date ) ) <= $homework_data->submition_date ) {
														?>
														<label class="mjschool-homework-submitted"><?php esc_html_e( 'Submitted', 'mjschool' ); ?></label>
														<?php
													} else {
														?>
														<label class="mjschool-homework-submitted"><?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?></label>
														<?php
													}
												} elseif ( $data->status === '2' ) {
													?>
													<label class="mjschool-homework-evaluated"><?php esc_html_e( 'Evaluated', 'mjschool' ); ?></label>
													<?php
												} else {
													?>
													<label class="mjschool-homework-pending"><?php esc_html_e( 'Pending', 'mjschool' ); ?></label>
													<?php
												}
												?>
											</label>
										</div>
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Student Comment', 'mjschool' ); ?> </label><br>
											<label >
												<?php
												if ( ! empty( $data->student_comment ) ) {
													echo esc_html( $data->student_comment );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</label>
										</div>
									</div>
									<form name="review_form" action="" method="post" class="mjschool-form-horizontal mt-4" id="homework_form_tempalte" enctype="multipart/form-data">
										<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
										<input type="hidden" id="stu_homework_id" name="stu_homework_id" value="<?php echo esc_attr( $data->stu_homework_id ); ?>">
										<input type="hidden" id="homework_id" name="homework_id" value="<?php echo esc_attr( $data->homework_id ); ?>">
										<input type="hidden" id="status" name="status" value="<?php echo esc_attr( $data->status ); ?>">    
										<input type="hidden" id="student_id" name="student_id" value="<?php echo esc_attr( $data->student_id ); ?>">   
										<div class="header">	
											<h3 class="mjschool-first-header"><?php esc_html_e( 'Evaluate Homework', 'mjschool' ); ?></h3>
										</div>
										<div class="form-body mjschool-user-form"> <!------  Form body. -------->
											<div class="row">
												<div class="col-md-6">	
													<div class="form-group input">
														<div class="col-md-12 form-control">	
															<div class="col-sm-12">
																<?php
																if ( ! empty( $data->status != '2' ) ) {
																	?>
																	<input id="review_file" type='file' class="form-control mjschool-file-validation input-file"  value="" name="review_file">
																	<?php
																}
																?>
																<label for="userinput1" class="mjschool-upload-homework-label"><?php esc_html_e( 'Evaluated File', 'mjschool' ); ?></label>
															</div>
															<?php
															if ( ! empty( $data->review_file ) ) {
																?>
																<a download href="<?php echo esc_url( content_url( '/uploads/homework_file/' . $data->review_file ) ); ?>" class="btn" record_id="<?php echo esc_attr( $data->stu_homework_id ); ?>" download><i class="fas fa-download"></i>&nbsp;&nbsp;<?php esc_html_e( 'Download', 'mjschool' ); ?></a>
																<?php
															}
															?>
														</div>
													</div>
												</div>
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="marks" value="<?php if ( ! empty( $data->obtain_marks ) ) { echo esc_attr( $data->obtain_marks );} ?>" class="form-control validate[max[<?php echo esc_attr( $homework_data->marks ); ?>],maxSize[10]] text-input" type="number" name="obtain_marks">
															<label class="date_label" for="class_capacity"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></label>
														</div>
													</div>
												</div>
												<div class="col-md-6 mjschool-note-text-notice">
													<div class="form-group input">
														<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
															<div class="form-field">
																<textarea name="teacher_comment" class="mjschool-textarea-height-60px form-control validate[custom[description_validation]]" maxlength="1000" id="teacher_comment"><?php
																if ( ! empty( $data->teacher_comment ) ) {
																	echo esc_textarea( $data->teacher_comment );}
																?></textarea>
																<span class="mjschool-txt-title-label"></span>
																<label class="text-area address active" for="teacher_comment"><?php esc_html_e( 'Teacher Comment', 'mjschool' ); ?></label>
															</div>
														</div>
													</div>
												</div>	
											</div>
										</div>
										<div class="form-body mjschool-user-form"><!------ Form body. -------->
											<div class="row">
												<div class="col-sm-6">        	
													<input type="submit" value="<?php esc_attr_e( 'Evaluate Homework', 'mjschool' ); ?>" name="student_review_homework" class="btn btn-success save_homework mjschool-save-btn" />
												</div> 
											</div>
										</div>
									</form>	
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</section>	
	</div><!-- End content body div. -->
</div><!-- End panel body div. -->