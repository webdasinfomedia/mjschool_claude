<?php
/**
 * Homework Management View/Controller (Front-End/Student Perspective).
 *
 * This file handles the view and form processing for the Homework module,
 * primarily focusing on student actions like viewing assigned homework,
 * submitting completed homework, and viewing their submission details.
 * It also includes the necessary logic for staff/admin views to list and manage homework.
 *
 * Key features include:
 * - **Form Processing:** Handles the submission (insert/update) of student homework files and comments.
 * - **Database Interaction:** Uses the global `$wpdb` object for direct database updates (`$wpdb->update`) for submission status.
 * - **Validation:** Initializes jQuery Validation Engine for client-side form validation (`#homework_form_tempalte`, `#view_submition_form_front`).
 * - **DataTables:** Initializes a jQuery DataTables instance (`#mjschool-homework-list-front`) for displaying the list of homework.
 * - **Custom Fields:** Integrates custom fields managed by `Mjschool_Custome_Field` for the 'homework' module.
 * - **Access Control:** Checks user role (`$mjschool_role_name`) to conditionally display columns and functionality.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type			   = get_option( 'mjschool_custom_class' );
$mjschool_role_name         = mjschool_get_user_role( get_current_user_id() );
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'homework';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST ['page'] ) ) {
	if ( isset($user_access['view']) && $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST ['page'] ) && sanitize_text_field(wp_unslash($_REQUEST ['page'])) === $user_access['page_link'] && ( $sanitize_text_field(wp_unslash(_REQUEST['action'])) === 'edit' ) ) {
			if ( isset($user_access['edit']) && $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST ['page'] ) && sanitize_text_field(wp_unslash($_REQUEST ['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( isset($user_access['delete']) && $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST ['page'] ) && sanitize_text_field(wp_unslash($_REQUEST ['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( isset($user_access['add']) && $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
require_once MJSCHOOL_INCLUDES_DIR . '/class-mjschool-management.php';
$homewrk    = new Mjschool_Homework();
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'homeworklist';
if ( isset( $_GET['success'] ) && sanitize_text_field(wp_unslash($_GET['success'])) === 1 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
		<?php esc_html_e( 'Homework Uploaded successfully.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['filesuccess'] ) && sanitize_text_field(wp_unslash($_GET['filesuccess'])) === 1 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
		<?php esc_html_e( 'File Extension Invalid !', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['addsuccess'] ) && sanitize_text_field(wp_unslash($_GET['addsuccess'])) === 1 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
		<?php esc_html_e( 'Homework Added Successfully.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['deletesuccess'] ) && sanitize_text_field(wp_unslash($_GET['deletesuccess'])) === 1 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
		<?php esc_html_e( 'Homework Deleted Successfully', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['updatesuccess'] ) && sanitize_text_field(wp_unslash($_GET['updatesuccess'])) === 1 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
		<?php esc_html_e( 'Homework Updated Successfully', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['updatesuccess'] ) && sanitize_text_field(wp_unslash($_GET['updatesuccess'])) === 2 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
	<?php esc_html_e( 'Homework Reviewed Successfully.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['deleteselectedsuccess'] ) && sanitize_text_field(wp_unslash($_GET['deleteselectedsuccess'])) === 1 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
		<?php esc_html_e( 'Homework Deleted Successfully', 'mjschool' ); ?>
	</div>
	<?php
}
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="view_popup"></div>     
		</div>
	</div>    
</div>
<!-- End POP-UP Code. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!------------  Panel body. -------------->
	<!---------------- Tabbing start. ---------------->
	<?php
	$page_action = '';
	if ( isset($_REQUEST['action']) && ! empty( $_REQUEST['action'] ) ) {
		$page_action = sanitize_text_field(wp_unslash($_REQUEST['action']));
	}
	if ( $active_tab !== 'view_homework' ) {
		?>
		<?php $nonce = wp_create_nonce( 'mjschool_homework_tab' ); ?>
		<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
			<li class="<?php if ( $active_tab === 'homeworklist' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=homeworklist&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'homeworklist' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Upcoming Homework', 'mjschool' ); ?></a>
			</li>
			<li class="<?php if ( $active_tab === 'closed_homework' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=closed_homework&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'closed_homework' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Closed Homework', 'mjschool' ); ?></a>
			</li>
			<?php
			if ( $active_tab === 'addhomework' ) {
				if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
					?>
					<li class="<?php if ( $active_tab === 'addhomework' || sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=addhomework&action=edit&homework_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['homework_id'] ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addhomework' ? 'nav-tab-active' : ''; ?>"> <?php esc_html_e( 'Edit Homework', 'mjschool' ); ?></a>  
					</li> 
					<?php
				} else {
					?>
					<?php
					if ( isset($user_access['add']) && $user_access['add'] === '1' ) {
						?>
						<li class="<?php if ( $active_tab === 'addhomework' ) { ?> active<?php } ?>">
							<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=addhomework' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addhomework' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Add Homework', 'mjschool' ); ?></a>  
						</li> 
						<?php
					}
				}
			}
			?>
		</ul>
		<!---------------- Tabbing end. ---------------->
		<?php
	}
	if ( $active_tab === 'addhomework' ) {
		require_once MJSCHOOL_PLUGIN_DIR . '/template/add-student-homework.php';
	}
	if ( $active_tab === 'view_homework' ) {
		$active_tab1  = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
		$objj         = new Mjschool_Homework();
		$homeworkdata = $objj->mjschool_get_edit_record( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ) ) );
		?>
		<div class="mjschool-panel-body mjschool-view-page-main"><!--  Start panel body div.-->
			<div class="content-body"><!--  Start content body div.-->
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
													<?php
													if ( isset($user_access['edit']) && $user_access['edit'] === '1' ) {
														?>
														<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=addhomework&action=edit&homework_id=' . mjschool_encrypt_id( $homeworkdata->homework_id ) ); ?>">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-edit.png' ); ?>">
														</a>
														<?php
													}
													?>
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
								<?php
								if ( $mjschool_role_name === 'student' || $mjschool_role_name === 'parent' ) {
									if ( $active_tab1 === 'upload_homework' ) {
										?>
										<li class="<?php if ( $active_tab1 === 'upload_homework' ) { ?> active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=upload_homework&action=view&id=' . sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) . '&student_id=' . sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'upload_homework' ? 'active' : ''; ?>"> <?php esc_html_e( 'Homework Details', 'mjschool' ); ?></a>
										</li>
										<?php
									}
								}
								if ( $mjschool_role_name === 'teacher' || $mjschool_role_name === 'supportstaff' ) {
									?>
									<li class="<?php if ( $active_tab1 === 'general' ) { ?> active<?php } ?>">
										<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=general&id=' . sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>"> <?php esc_html_e( 'Homework Details', 'mjschool' ); ?></a>
									</li>
									<li class="<?php if ( $active_tab1 === 'submission' ) { ?> active<?php } ?>">
										<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=submission&id=' . sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'submission' ? 'active' : ''; ?>"> <?php esc_html_e( 'Submissions', 'mjschool' ); ?></a>
									</li>
									<?php
									if ( $active_tab1 === 'review_homework' ) {
										?>
										<li class="<?php if ( $active_tab1 === 'review_homework' ) { ?> active<?php } ?>">
											<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=review_homework&id=' . sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) . '&stu_homework_id=' . sanitize_text_field( wp_unslash( $_REQUEST['stu_homework_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'review_homework' ? 'active' : ''; ?>"> <?php esc_html_e( 'Evaluate Homework', 'mjschool' ); ?></a>
										</li>
										<?php
									}
								}
								?>
							</ul>
						</div>
					</div>
				</section>
				<section id="mjschool-body-content-area">
					<div class="mjschool-panel-body"><!--  Start panel body div.-->
						<?php
						if ( $active_tab1 === 'general' ) {
							?>
							<div class="row">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px-rs">
										<div class="mjschool-guardian-div">
											<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Homework Information', 'mjschool' ); ?> </label>
											<div class="row">
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Subject', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_subject_by_id( $homeworkdata->subject ) ); ?></label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Class', 'mjschool' ); ?> </label><br>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_class_section_name_wise( $homeworkdata->class_name, $homeworkdata->section_id ) ); ?></label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Homework Date', 'mjschool' ); ?> </label><br>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $homeworkdata->created_date ) ) {
															echo esc_html( mjschool_get_date_in_input_box( $homeworkdata->created_date ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' ); 
														}
														?>
													</label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Submission Date', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_date_in_input_box( $homeworkdata->submition_date ) ); ?></label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Marks', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $homeworkdata->marks ) ) {
															echo esc_html( $homeworkdata->marks );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Documents Title', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
															$doc_data = json_decode( $homeworkdata->homework_document );
														if ( ! empty( $doc_data[0]->title ) ) {
															echo esc_attr( $doc_data[0]->title );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Download File', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														$doc_data = json_decode( $homeworkdata->homework_document );
														if ( ! empty( $doc_data[0]->value ) ) {
															?>
															<a download href="<?php echo esc_url( content_url( '/uploads/school_assets/' . basename( $doc_data[0]->value ) ) ); ?>"  class="btn mjschool-custom-padding-0 popup_download_btn" record_id="<?php echo esc_attr( $homeworkdata->homework_id ); ?>"><i class="fa fa-download" id="mjschool-download-icon"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
															<?php
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Homework Content', 'mjschool' ); ?> </label><br>
													<label >
														<?php
														if ( ! empty( $homeworkdata->content ) ) {
															echo esc_html( $homeworkdata->content );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						if ( $active_tab1 === 'upload_homework' ) {
							$objj      = new Mjschool_Homework();
							$classdata = $objj->mjschool_parent_update_detail( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_GET['id'])) ), mjschool_decrypt_id( sanitize_text_field(wp_unslash($_GET['student_id'])) ) );
							$data      = $classdata[0];
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
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Class', 'mjschool' ); ?> </label><br>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_class_section_name_wise( $homeworkdata->class_name, $homeworkdata->section_id ) ); ?></label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Subject', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_subject_by_id( $homeworkdata->subject ) ); ?></label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Documents Title', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														$doc_data = json_decode( $homeworkdata->homework_document );
														if ( ! empty( $doc_data[0]->title ) ) {
															echo esc_attr( $doc_data[0]->title );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Download File', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														$doc_data = json_decode( $homeworkdata->homework_document );
														if ( ! empty( $doc_data[0]->value ) ) {
															?>
															<a download href="<?php print esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value )); ?>"  class="btn mjschool-custom-padding-0 popup_download_btn" record_id="<?php echo esc_attr( $homeworkdata->homework_id ); ?>"><i class="fa fa-download" id="mjschool-download-icon"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
															<?php
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Homework Date', 'mjschool' ); ?> </label><br>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $homeworkdata->created_date ) ) {
															echo esc_attr( mjschool_get_date_in_input_box( $homeworkdata->created_date ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' ); 
														}
														?>
													</label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Submission Date', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels"><?php echo esc_attr( mjschool_get_date_in_input_box( $homeworkdata->submition_date ) ); ?></label>
												</div>
												<?php
												if ( $data->status !== '0' ) {
													?>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Submitted Date', 'mjschool' ); ?> </label><br>
														<label class="mjschool-view-page-content-labels"><?php echo esc_attr( mjschool_get_date_in_input_box( $data->uploaded_date ) ); ?></label>
													</div>
													<?php
												}
												if ( $data->status === '2' ) {
													?>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Evaluate Date', 'mjschool' ); ?> </label><br>
														<label class="mjschool-view-page-content-labels"><?php echo esc_attr( mjschool_get_date_in_input_box( $data->evaluate_date ) ); ?></label>
													</div>
													<?php
												}
												?>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Total Marks', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $homeworkdata->marks ) ) {
															echo esc_html( $homeworkdata->marks );
														} else {
															esc_html_e( 'N/A', 'mjschool' );}
														?>
													</label>
												</div>
												<?php
												if ( $data->status === '2' ) {
													?>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?> </label><br>
														<label class="mjschool-view-page-content-labels"><?php echo esc_html( $data->obtain_marks ); ?></label>
													</div>
													<?php
												}
												?>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Status', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( $data->status === '1' ) {
															if ( date( 'Y-m-d', strtotime( $data->uploaded_date ) ) <= $data->submition_date ) {
																?>
																<span class="mjschool-homework-submitted"><?php esc_html_e( 'Submitted', 'mjschool' ); ?></span>
																<?php
															} else {
																?>
																<span class="mjschool-homework-submitted"><?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?></span>
																<?php
															}
														} elseif ( $data->status === '2' ) {
															?>
															<span class="mjschool-homework-evaluated"><?php esc_html_e( 'Evaluated', 'mjschool' ); ?></span>
															<?php
														} else {
															?>
															<span class="mjschool-homework-pending"><?php esc_html_e( 'Pending', 'mjschool' ); ?></span>
															<?php
														}
														?>
													</label>
												</div>
												<?php
												if ( $data->status === '2' ) {
													?>
													<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Teacher Comment', 'mjschool' ); ?> </label><br>
														<label >
															<?php
															if ( ! empty( $data->teacher_comment ) ) {
																echo esc_html( $data->teacher_comment );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</label>
													</div>
													<?php
												}
												?>
												<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Homework Content', 'mjschool' ); ?> </label><br>
													<label >
														<?php
														if ( ! empty( $homeworkdata->content ) ) {
															echo esc_html( $homeworkdata->content );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
											</div>
											<form name="class_form" action="" method="post" class="mjschool-form-horizontal mt-4" id="homework_form_tempalte" enctype="multipart/form-data">
												<?php $action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
												<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
												<input type="hidden" id="stu_homework_id" name="stu_homework_id" value="<?php echo esc_attr( $data->stu_homework_id ); ?>">
												<input type="hidden" id="homework_id" name="homework_id" value="<?php echo esc_attr( $data->homework_id ); ?>">
												<input type="hidden" id="status" name="status" value="<?php echo esc_attr( $data->status ); ?>">    
												<input type="hidden" id="student_id" name="student_id" value="<?php echo esc_attr( $data->student_id ); ?>">       		
												<div class="header">	
													<h3 class="mjschool-first-header"><?php esc_html_e( 'Submit Homework Information', 'mjschool' ); ?></h3>
												</div>
												<div class="form-body mjschool-user-form"> <!------  Form Body. -------->
													<div class="row">
														<?php
														if ( $data->status === 0 ) {
															?>
															<div class="col-md-6">	
																<div class="form-group input">
																	<div class="col-md-12 form-control">	
																		<div class="col-sm-12">	
																			<input id="file" type='file' class="form-control file-validation input-file" value="<?php if ( $view ) { echo esc_attr( $data->submition_date );} ?>" name="file">
																			<label for="userinput1" class="mjschool-upload-homework-label"><?php esc_html_e( 'Uploaded File', 'mjschool' ); ?><span class="required">*</span></label>
																		</div>
																	</div>
																</div>
															</div>
															<?php
														} else {
															?>
															<div class="col-md-6">	
																<div class="form-group input">
																	<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
																		<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Uploaded File', 'mjschool' ); ?><span class="required">*</span></label>	
																		<div class="col-sm-12">
																			<?php
																			if ( $data->status !== 2 ) {
																				?>
																				<input id="file" type='file' class="form-control input-file"  value="" name="file">
																				<?php
																			}
																			?>
																			<input type="hidden" name="Uploaded_file" value="<?php echo esc_url( $data->file ); ?>">
																		</div>
																		<?php
																		if ( ! empty( $data->file ) ) {
																			?>
																			<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
																				<a download href="<?php print esc_url( content_url( '/uploads/homework_file/' . $data->file )); ?>" class=" btn " record_id="<?php echo esc_attr( $data->stu_homework_id ); ?>" download><i class="fa fa-download"></i>&nbsp;&nbsp;<?php esc_html_e( 'Download', 'mjschool' ); ?></a>
																			</div>
																			<?php
																		}
																		?>
																	</div>
																</div>
															</div> 
															<?php
														}
														?>
														<div class="col-md-6 mjschool-note-text-notice">
															<div class="form-group input">
																<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
																	<div class="form-field">
																		<textarea name="student_comment" class="mjschool-textarea-height-60px form-control" maxlength="1000" id="notice_content"> <?php if ( ! empty( $data->student_comment ) ) { echo esc_textarea( $data->student_comment ); } ?> </textarea>
																		<span class="mjschool-txt-title-label"></span>
																		<label class="text-area address active" for="student_comment"><?php esc_html_e( 'Student Comment', 'mjschool' ); ?></label>
																	</div>
																</div>
															</div>
														</div>	
														<?php
														if ( ! empty( $data->review_file ) ) {
															?>
															<div class="col-md-6">
																<div class="form-group input">
																	<div class="col-md-12 form-control">
																		<a download href="<?php print esc_url( content_url( '/uploads/homework_file/' . $data->review_file )); ?>" class=" btn " record_id="<?php echo esc_attr( $data->stu_homework_id ); ?>" download><i class="fa fa-download"></i>&nbsp;&nbsp;<?php esc_html_e( 'Download', 'mjschool' ); ?></a>
																		<label for="userinput1" class="mjschool-upload-homework-label"><?php esc_html_e( 'Evaluate file', 'mjschool' ); ?></label>
																	</div>
																</div>
															</div>
															<?php
														}
														if ( $data->status !== 2 ) {
															?>
															<div class="form-body mjschool-user-form"> <!------  Form Body. -------->
																<div class="row">
																	<div class="col-sm-6">        	
																		<input type="submit" value="<?php esc_attr_e( 'Submit Homework', 'mjschool' ); ?>" name="student_upload_homework" class="btn btn-success save_homework mjschool-save-btn" />
																	</div> 
																</div>
															</div>
															<?php
														}
														?>
													</div>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						if ( $active_tab1 === 'submission' ) {
							if ( isset( $_REQUEST['review_success'] ) && sanitize_text_field(wp_unslash($_REQUEST['review_success'])) === 'review_success' ) {
								?>
								<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
									<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
									<?php esc_html_e( 'Homework Evaluated Successfully.', 'mjschool' ); ?>
								</div>
								<?php
							}
							$retrieve_class_data = $objj->mjschool_view_submission( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'])) ) ) );
							if ( ! empty( $retrieve_class_data ) ) {
								?>
								<div class="table-responsive"><!-- Table-responsive. --> 	
									<form id="mjschool-common-form" name="mjschool-common-form" method="post">
										<table id="frontend_submission_list" class="display" cellspacing="0" width="100%">
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
																<a  href="?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->student_id ) ); ?>"><?php echo esc_attr( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) ); ?></a> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
															</td>
															<td><?php echo esc_html( mjschool_get_class_name( $retrieved_data->class_name ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i></td>
															<td><?php echo esc_attr( mjschool_get_subject_by_id( $retrieved_data->subject ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i></td>
															<td><?php echo esc_attr( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?>  <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submission Date', 'mjschool' ); ?>"></i></td>
															<?php
															if ( $retrieved_data->uploaded_date === 0000 - 00 - 00 ) {
																?>
																<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo 'N/A '; ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td> 
																<?php
															} else {
																?>
																<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->uploaded_date ) ); ?>  <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td>
																<?php
															}
															?>
															<td>
																<?php
																if ( ! empty( $retrieved_data->evaluate_date ) ) {
																	echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->evaluate_date ) );
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
																<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Evaluate Date', 'mjschool' ); ?>"></i>
															</td>
															<td>
																<?php
																if ( ! empty( $retrieved_data->marks ) ) {
																	echo esc_html( $retrieved_data->marks );
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
																<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Total Marks', 'mjschool' ); ?>"></i>
															</td>
															<td>
																<?php
																if ( ! empty( $retrieved_data->obtain_marks ) ) {
																	echo esc_html( $retrieved_data->obtain_marks );
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
																<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks Obtained', 'mjschool' ); ?>"></i>
															</td>
															<?php
															if ( $retrieved_data->status === 1 ) {
																if ( date( 'Y-m-d', strtotime( $retrieved_data->uploaded_date ) ) <= $retrieved_data->submition_date ) {
																	?>
																	<td><span class="mjschool-homework-submitted"><?php esc_html_e( 'Submitted', 'mjschool' ); ?></span> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
																	<?php
																} else {
																	?>
																	<td><span class="mjschool-homework-submitted"><?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?></span> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
																	<?php
																}
															} elseif ( $retrieved_data->status === 2 ) {
																?>
																<td><span class="mjschool-homework-evaluated"><?php esc_html_e( 'Evaluated', 'mjschool' ); ?></span> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
																<?php
															} else {
																?>
																<td><span class="mjschool-homework-pending"><?php esc_html_e( 'Pending', 'mjschool' ); ?></span> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
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
																				if ( $retrieved_data->status !== 0 ) {
																					?>
																					<li class="mjschool-float-left-width-100px">
																						<a href="?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=review_homework&id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>&stud_homework_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->stu_homework_id ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-check"></i><?php esc_html_e( 'Evaluate Homework', 'mjschool' ); ?></a>
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
														++$i;
													}
												}
												?>
											</tbody>
										</table>
									</form>
								</div><!-- Table-responsive. --> 
								<?php
							} else {
								?>
								<div class="mjschool-calendar-event-new"> 
									<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
								</div>
								<?php
							}
						}
						if ( $active_tab1 === 'review_homework' ) {
							$homework_obj = new Mjschool_Homework();
							$data          = $homework_obj->mjschool_get_student_submitted_homework( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['stud_homework_id'])) ) ) );
							$homework_data = $homework_obj->mjschool_get_edit_record( $data->homework_id );
							if ( isset( $_POST['student_review_homework'] ) ) {
								// Initialize variables.
								$file_name = '';
								// File upload handling.
								if ( ! empty( $_FILES['review_file']['name'] ) ) {
									$randm     = mt_rand( 5, 15 ); // Generate random number.
									$file_name = 'H' . $randm . '_' . sanitize_file_name( $_FILES['review_file']['name'] );
									$file_tmp  = $_FILES['review_file']['tmp_name'];
									$upload          = wp_upload_dir();
									$upload_dir_path = $upload['basedir'];
									$upload_dir      = $upload_dir_path . '/homework_file';
									// Ensure the upload directory exists.
									if ( ! file_exists( $upload_dir ) ) {
										if ( ! mkdir( $upload_dir, 0700, true ) && ! is_dir( $upload_dir ) ) {
											wp_die( 'Failed to create upload directory.' );
										}
									}
									// Move uploaded file.
									if ( ! move_uploaded_file( $file_tmp, $upload_dir . '/' . $file_name ) ) {
										wp_die( 'Failed to upload file.' );
									}
								}
								// Retrieve and sanitize POST data.
								$stud_homework_id = isset( $_POST['stu_homework_id'] ) ? intval( wp_unslash($_POST['stu_homework_id']) ) : 0;
								$obtain_marks     = isset( $_POST['obtain_marks'] ) ? sanitize_text_field( wp_unslash($_POST['obtain_marks']) ) : '';
								$teacher_comment  = isset( $_POST['teacher_comment'] ) ? sanitize_textarea_field( wp_unslash($_POST['teacher_comment']) ) : '';
								$evaluate_date    = date( 'Y-m-d' );
								$status           = 2; // Assuming 2 is the evaluated status.
								$result = $objj->mjschool_update_student_homework($stud_homework_id, $file_name, $obtain_marks, $teacher_comment, $evaluate_date, $status);
								// Redirect with appropriate message.
								if ( $result !== false ) {
									wp_safe_redirect(rect( home_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=submission&id=' . mjschool_encrypt_id( $data->homework_id ) . '&review_success=review_success' ) );
									die();
								} else {
									wp_die( 'Failed to update homework review.' );
								}
							}
							if ( isset( $_REQUEST['review_success'] ) && sanitize_text_field(wp_unslash($_REQUEST['review_success'])) === 'review_success' ) {
								?>
								<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
									<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
									<?php esc_html_e( 'Homework Evaluated Successfully.', 'mjschool' ); ?>
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
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Student Name', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels"><?php echo esc_html( ucfirst( mjschool_get_display_name( $data->student_id ) ) ); ?></label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Class', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_class_section_name_wise( $homework_data->class_name, $homework_data->section_id ) ); ?></label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Subject', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_subject_by_id( $homework_data->subject ) ); ?></label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Uploaded Document', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $data->file ) ) {
															?>
															<a download href="<?php print esc_url( content_url( '/uploads/homework_file/' . $data->file )); ?>" class="btn mjschool-custom-padding-0 popup_download_btn" record_id="<?php echo esc_attr( $data->stu_homework_id ); ?>" download><i class="fa fa-download"></i>&nbsp;&nbsp;<?php esc_html_e( 'Download', 'mjschool' ); ?></a>
															<?php
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Submission Date', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels"><?php echo esc_attr( mjschool_get_date_in_input_box( $homework_data->submition_date ) ); ?></label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Submitted Date', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels"><?php echo esc_attr( mjschool_get_date_in_input_box( $data->uploaded_date ) ); ?></label>
												</div>
												<?php
												if ( $data->status === '2' ) {
													?>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Evaluate Date', 'mjschool' ); ?> </label> <br>
														<label class="mjschool-view-page-content-labels"><?php echo esc_attr( mjschool_get_date_in_input_box( $data->evaluate_date ) ); ?></label>
													</div>
													<?php
												}
												?>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Total Marks', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $homework_data->marks ) ) {
															echo esc_html( $homework_data->marks );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Status', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( $data->status === '1' ) {
															if ( date( 'Y-m-d', strtotime( $data->uploaded_date ) ) <= $homework_data->submition_date ) {
																?>
																<span class="mjschool-homework-submitted"><?php esc_html_e( 'Submitted', 'mjschool' ); ?></span>
																<?php
															} else {
																?>
																<span class="mjschool-homework-submitted"><?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?></span>
																<?php
															}
														} elseif ( $data->status === '2' ) {
															?>
															<span class="mjschool-homework-evaluated"><?php esc_html_e( 'Evaluated', 'mjschool' ); ?></span>
															<?php
														} else {
															?>
															<span class="mjschool-homework-pending"><?php esc_html_e( 'Pending', 'mjschool' ); ?></span>
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
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</label>
												</div>
											</div>
											<form name="review_form" action="" method="post" class="mjschool-form-horizontal mt-4" id="homework_form_tempalte" enctype="multipart/form-data">
												<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
												<input type="hidden" id="stu_homework_id" name="stu_homework_id" value="<?php echo esc_attr( $data->stu_homework_id ); ?>">
												<input type="hidden" id="homework_id" name="homework_id" value="<?php echo esc_attr( $data->homework_id ); ?>">
												<input type="hidden" id="status" name="status" value="<?php echo esc_attr( $data->status ); ?>">    
												<input type="hidden" id="student_id" name="student_id" value="<?php echo esc_attr( $data->student_id ); ?>">   
												<div class="header">	
													<h3 class="mjschool-first-header"><?php esc_html_e( 'Evaluate Homework', 'mjschool' ); ?></h3>
												</div>
												<div class="form-body mjschool-user-form"> <!------  Form Body. -------->
													<div class="row">
														<div class="col-md-6">	
															<div class="form-group input">
																<div class="col-md-12 form-control">	
																	<div class="col-sm-12">	
																		<?php
																		if ( ! empty( $data->status !== '2' ) ) {
																			?>
																			<input id="review_file" type='file' class="form-control file-validation input-file"  value="" name="review_file">
																			<?php
																		} elseif ( empty( $data->review_file ) ) {
																			// Check if review_file is empty.
																			?>
																			<input id="review_file" type='file' class="form-control file-validation input-file" name="review_file">
																			<?php
																		}
																		?>
																		<label for="userinput1" class="mjschool-upload-homework-label"><?php esc_html_e( 'Evaluated File', 'mjschool' ); ?></label>
																	</div>
																	<?php
																	if ( ! empty( $data->review_file ) ) {
																		?>
																		<a download href="<?php print esc_url( content_url( '/uploads/homework_file/' . $data->review_file )); ?>" class="btn" record_id="<?php echo esc_attr( $data->stu_homework_id ); ?>" download><i class="fa fa-download"></i>&nbsp;&nbsp;<?php esc_html_e( 'Download', 'mjschool' ); ?></a>
																		<?php
																	}
																	?>
																</div>
															</div>
														</div>
														<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
															<div class="form-group input">
																<div class="col-md-12 form-control">
																	<input id="marks" value="<?php if ( ! empty( $data->obtain_marks ) ) { echo esc_attr( $data->obtain_marks );} ?>" class="form-control validate[max[<?php echo esc_attr( $homework_data->marks ); ?>]] text-input" type="number" name="obtain_marks">
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
																			?>
																		</textarea>
																		<span class="mjschool-txt-title-label"></span>
																		<label class="text-area address active" for="teacher_comment"><?php esc_html_e( 'Teacher Comment', 'mjschool' ); ?></label>
																	</div>
																</div>
															</div>
														</div>	
													</div>
												</div>
												<div class="form-body mjschool-user-form"><!------  Form Body. -------->
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
			</div><!-- End content body div.-->
		</div><!-- End Panel body div.-->
		<?php
	}
	if ( $active_tab === 'view_stud_detail' ) {
		$homework = new Mjschool_Homework();
		if ( $school_obj->role === 'teacher' ) {
			$res = $homework->mjschool_get_teacher_homeworklist();
		} elseif ( isset($user_access['own_data']) && $user_access['own_data'] === '1' ) {
			$res = $homework->mjschool_get_all_own_homeworklist();
		} else {
			$res = $homework->mjschool_get_all_homework_list();
		}
		if ( $page_action === 'edit' ) {
			$edit = 1;
		} else {
			$edit = 0;
		}
		?>
		<div class="mjschool-panel-body marging_top_50px_rs"><!-- Mjschool-panel-body.--> 	
			<div class="mjschool-homework-list"> <!-- Mjschool-homework-list.--> 
				<form name="view_submition_form_front" action="" method="post" class="mjschool-margin-top-20px mjschool-padding-top-25px-res mjschool-form-horizontal" id="class_form_second">
					<div class="form-body mjschool-user-form mb-2"> <!-- Mjschool-user-form div.-->   
						<div class="row"><!--Row div.--> 
							<div class="col-sm-9 col-md-9 col-lg-9 col-xl-9 input mjschool-form-select">
								<label class="mjschool-custom-top-label mjschool-lable-top top" for="homewrk"><?php esc_html_e( 'Select Homework', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<?php
								if ( isset( $_REQUEST['homewrk'] ) ) {
									$classval = sanitize_text_field(wp_unslash($_REQUEST['homewrk']));
								} else {
									$classval = '';
								}
								?>
								<select name="homewrk" class="mjschool-line-height-30px form-control validate[required]" id="homewrk">
									<option value=""><?php esc_html_e( 'Select Homework', 'mjschool' ); ?></option>
									<?php
									$classval = '';
									if ( isset( $_REQUEST['homewrk'] ) ) {
										$classval = sanitize_text_field(wp_unslash($_REQUEST['homewrk']));
									}
									foreach ( $res as $classdata ) {
										?>
										<option value="<?php echo esc_attr( $classdata->homework_id ); ?>" <?php selected( $classdata->homework_id, $classval ); ?>><?php echo esc_html( $classdata->title ); ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<div class="col-md-3 col-sm-3 col-xs-3 mjschool-res-rtl-width-100px">
								<input type="submit" value="<?php esc_attr_e( 'View', 'mjschool' ); ?>" name="view"  class="mjschool-save-btn mjschool-custom-class"/>
							</div>
						</div><!--Row div.--> 
					</div> <!-- Mjschool-user-form div.--> 
					<?php
					$obj = new Mjschool_Homework();
					if ( isset( $_POST['homewrk'] ) ) {
						$data           = sanitize_text_field(wp_unslash($_POST['homewrk']));
						$retrieve_class_data = $obj->mjschool_view_submission( $data );
						require_once MJSCHOOL_PLUGIN_DIR . '/template/viewsubmission.php';
					} elseif ( isset( $_REQUEST['homewrk'] ) ) {
						$data           = sanitize_text_field(wp_unslash($_REQUEST['homewrk']));
						$retrieve_class_data = $obj->mjschool_view_submission( $data );
						require_once MJSCHOOL_PLUGIN_DIR . '/template/viewsubmission.php';
						// require_once MJSCHOOL_PLUGIN_DIR. '/admin/includes/student-homework/viewsubmission.php';
					}
					?>
				</form>
			</div><!-- Mjschool-homework-list.--> 
		</div><!-- Mjschool-panel-body.-->
		<?php
	}
	?>
	<div>
		<?php
		if ( $active_tab === 'homeworklist' ) {
			// Check nonce for homework list tab.
			if ( isset( $_GET['tab'] ) ) {
				if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_homework_tab' ) ) {
					wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
				}
			}
			?>
			<div class="tab-pane active" id="examlist">         
				<?php
				// ------- Homework data for student. ---------//
				if ( $school_obj->role === 'student' ) {
					$result = $homewrk->mjschool_student_view_upcoming_homework();
				}
				// ------- Homework data for parent. ---------//
				elseif ( $school_obj->role === 'parent' ) {
					$result = $homewrk->mjschool_parent_upcoming_homework();
				}
				// ------- Homework data for teacher. ---------//
				elseif ( $school_obj->role === 'teacher' ) {
					$own_data = isset($user_access['own_data']) ? $user_access['own_data'] : 0;
					if ( $own_data === '1' ) {
						$result = $homewrk->mjschool_get_all_own_upcoming_homework_list_for_teacher();
					} else {
						$result = $homewrk->mjschool_get_all_upcoming_homework();
					}
				}
				// ------- Homework data for supportstaff. ---------//
				else {
					$own_data = isset($user_access['own_data']) ? $user_access['own_data'] : 0;
					if ( $own_data === '1' ) {
						$result = $homewrk->mjschool_get_all_own_upcoming_homeworklist();
					} else {
						$result = $homewrk->mjschool_get_all_upcoming_homework();
					}
				}
				if ( ! empty( $result ) ) {
					?>
					<div class="mjschool-panel-body"><!----------- Panel body. -------------->
						<div class="table-responsive"><!----------- Table responsive. --------------->
							<!---------------- Homework list page form. ------------->
							<form id="mjschool-common-form" name="mjschool-common-form" method="post">
								<?php wp_nonce_field( 'bulk_delete_books' ); ?>
								<!----------- Homework list table. ------------->
								<table id="mjschool-homework-list-front" class="display dataTable" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" id="select_all1"></th>
												<?php
											}
											?>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></th>
											<?php
											if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
												?>
												<th><?php esc_html_e( 'Submitted Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Evaluate Date', 'mjschool' ); ?></th>
												<?php
											}
											?>
											<th><?php esc_html_e( 'Marks', 'mjschool' ); ?></th>
											<?php
											if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
												?>
												<th><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
												<?php
											}
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
											<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										foreach ( $result as $retrieved_data ) {
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
												<?php
												if ( $mjschool_role_name === 'supportstaff' ) {
													?>
													<td class="mjschool-checkbox-width-10px">
														<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->homework_id ); ?>">
													</td>
													<?php
												}
												?>
												<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">	
													<a  href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>">
														<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">	
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png' ); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
														</p>
													</a>
												</td>
												<td>
													<?php
													if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
														?>
														<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=upload_homework&action=view&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) . '&student_id=' . mjschool_encrypt_id( $retrieved_data->student_id ) ); ?>">
														<?php
													} else {
														?>
														<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>">
														<?php
													}
													?>
														<?php echo esc_html( $retrieved_data->title ); ?>
													</a> 
													<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Title', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_name, $retrieved_data->section_id ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( mjschool_get_subject_by_id( $retrieved_data->subject ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Homework Date', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submission Date', 'mjschool' ); ?>"></i>
												</td>
												<?php
												if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
													if ( $retrieved_data->uploaded_date === 0000 - 00 - 00 ) {
														?>
														<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo 'N/A '; ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td> 
														<?php
													} else {
														?>
														<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->uploaded_date ) ); ?>  <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td>
														<?php
													}
													?>
													<td>
														<?php
														if ( ! empty( $retrieved_data->evaluate_date ) ) {
															echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->evaluate_date ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Evaluate Date', 'mjschool' ); ?>"></i>
													</td>
													<?php
												}
												?>
												<td>
													<?php
													if ( ! empty( $retrieved_data->marks ) ) {
														echo esc_html( $retrieved_data->marks );
													} else {
														esc_html_e( 'N/A', 'mjschool' );
													}
													?>
													<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
												</td>
												<?php
												if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
													?>
													<td>
														<?php
														if ( ! empty( $retrieved_data->obtain_marks ) ) {
															echo esc_html( $retrieved_data->obtain_marks );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
													</td>
													<?php
													if ( $retrieved_data->status === 1 ) {
														if ( date( 'Y-m-d', strtotime( $retrieved_data->uploaded_date ) ) <= $retrieved_data->submition_date ) {
															?>
															<td>
																<span class="mjschool-homework-submitted">
																	<?php esc_html_e( 'Submitted', 'mjschool' ); ?>
																</span>
																<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
															</td>
															<?php
														} else {
															?>
															<td>
																<span class="mjschool-purpal-color">
																	<?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?> 
																</span>
																<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
															</td>
															<?php
														}
													} elseif ( $retrieved_data->status === 2 ) {
														?>
														<td><span class="mjschool-homework-evaluated"><?php esc_html_e( 'Evaluated', 'mjschool' ); ?></span> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
														<?php
													} else {
														?>
														<td>
															<span class="mjschool-homework-pending">
																<?php esc_html_e( 'Pending', 'mjschool' ); ?> 
															</span>
															<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
														</td>
														<?php
													}
												}
												// Custom Field Values.
												if ( ! empty( $user_custom_field ) ) {
													foreach ( $user_custom_field as $custom_field ) {
														if ( $custom_field->show_in_table === '1' ) {
															$module             = 'homework';
															$custom_field_id    = $custom_field->id;
															$module_record_id   = $retrieved_data->homework_id;
															$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
															if ( $custom_field->field_type === 'date' ) {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															} elseif ( $custom_field->field_type === 'file' ) {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		?>
																		<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
																		<?php
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															} else {
																?>
																<td> 
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		echo esc_html( $custom_field_value );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );}
																	?>
																</td>
																<?php
															}
														}
													}
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
																	$doc_data = json_decode( $retrieved_data->homework_document );
																	if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>" class="mjschool-float-left-width-100px" type="Homework_view"><i class="fa fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																	if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=upload_homework&action=view&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) . '&student_id=' . mjschool_encrypt_id( $retrieved_data->student_id ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-eye" aria-hidden="true"></i><?php esc_html_e( 'Upload Homework', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																	if ( isset($user_access['edit']) && $user_access['edit'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=addhomework&action=edit&homework_id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																	if ( isset($user_access['delete']) && $user_access['delete'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=homeworklist&action=delete&homework_id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fa fa-trash"> </i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
											++$i;
										}
										?>
									</tbody>
								</table>
								<!----------- Homework list table. ------------->
								<?php
								if ( $mjschool_role_name === 'supportstaff' ) {
									?>
									<div class="mjschool-print-button pull-left">
										<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>" >
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="homework_delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
									</div>
									<?php
								}
								?>
							<form><!---------------- Homework list page form. ------------->
						</div><!----------- Table responsive. --------------->
					</div><!----------- Panel body. -------------->
					<?php
				} elseif ( isset($user_access['add']) && $user_access['add'] === '1' ) {
					?>
					<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px"> 
						<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=homework&tab=addhomework') ); ?>">
							<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
						</a>
						<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
							<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
						</div> 
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
			<?php
		}
		if ( $active_tab === 'closed_homework' ) {
			// Check nonce for closed homework list tab.
			if ( isset( $_GET['tab'] ) ) {
				if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_homework_tab' ) ) {
					wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
				}
			}
			?>
			<div class="tab-pane active" id="examlist">         
				<?php
				// ------- Homework data for student. ---------//
				if ( $school_obj->role === 'student' ) {
					$result = $homewrk->mjschool_student_view_closed_homework();
				}
				// ------- Homework data for parent. ---------//
				elseif ( $school_obj->role === 'parent' ) {
					$result = $homewrk->mjschool_parent_closed_homework();
				}
				// ------- Homework data for teacher. ---------//
				elseif ( $school_obj->role === 'teacher' ) {
					$own_data = isset($user_access['own_data']) ? $user_access['own_data'] : 0;
					if ( $own_data === '1' ) {
						$result = $homewrk->mjschool_get_all_own_closed_homework_list_for_teacher();
					} else {
						$result = $homewrk->mjschool_get_all_closed_homework();
					}
				}
				// ------- Homework data for supportstaff. ---------//
				else {
					$own_data = isset($user_access['own_data']) ? $user_access['own_data'] : 0;
					if ( $own_data === '1' ) {
						$result = $homewrk->mjschool_get_all_own_closed_homeworklist();
					} else {
						$result = $homewrk->mjschool_get_all_closed_homework();
					}
				}
				if ( ! empty( $result ) ) {
					?>
					<div class="mjschool-panel-body"><!----------- Panel body. -------------->
						<div class="table-responsive"><!----------- Table responsive. --------------->
							<!---------------- Homework list page form. ------------->
							<form id="mjschool-common-form" name="mjschool-common-form" method="post">
								<?php wp_nonce_field( 'bulk_delete_books' ); ?>
								<!----------- Homework list table. ------------->
								<table id="mjschool-homework-list-front" class="display dataTable" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
												<?php
											}
											?>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></th>
											<?php
											if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
												?>
												<th><?php esc_html_e( 'Submitted Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Evaluate Date', 'mjschool' ); ?></th>
												<?php
											}
											?>
											<th><?php esc_html_e( 'Marks', 'mjschool' ); ?></th>
											<?php
											if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
												?>
												<th><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
												<?php
											}
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
											<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										foreach ( $result as $retrieved_data ) {
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
												<?php
												if ( $mjschool_role_name === 'supportstaff' ) {
													?>
													<td class="mjschool-checkbox-width-10px">
														<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->homework_id ); ?>">
													</td>
													<?php
												}
												?>
												<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">	
													<a  href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>">
														<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">	
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png' ); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
														</p>
													</a>
												</td>
												<td>
													<?php
													if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
														?>
														<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=upload_homework&action=view&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) . '&student_id=' . mjschool_encrypt_id( $retrieved_data->student_id ) ); ?>">
														<?php
													} else {
														?>
														<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>">
														<?php
													}
													?>
														<?php echo esc_html( $retrieved_data->title ); ?>
													</a> 
													<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Title', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_name, $retrieved_data->section_id ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( mjschool_get_subject_by_id( $retrieved_data->subject ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Homework Date', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submission Date', 'mjschool' ); ?>"></i>
												</td>
												<?php
												if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
													if ( $retrieved_data->uploaded_date === 0000 - 00 - 00 ) {
														?>
														<td><?php esc_html_e( 'N/A', 'mjschool' ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td> 
														<?php
													} else {
														?>
														<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->uploaded_date ) ); ?>  <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td>
														<?php
													}
													?>
													<td>
														<?php
														if ( ! empty( $retrieved_data->evaluate_date ) ) {
															echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->evaluate_date ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Evaluate Date', 'mjschool' ); ?>"></i>
													</td>
													<?php
												}
												?>
												<td>
													<?php
													if ( ! empty( $retrieved_data->marks ) ) {
														echo esc_html( $retrieved_data->marks );
													} else {
														esc_html_e( 'N/A', 'mjschool' );}
													?>
													<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
												</td>
												<?php
												if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
													?>
													<td>
														<?php
														if ( ! empty( $retrieved_data->obtain_marks ) ) {
															echo esc_html( $retrieved_data->obtain_marks );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
													</td>
													<?php
													if ( $retrieved_data->status === 1 ) {
														if ( date( 'Y-m-d', strtotime( $retrieved_data->uploaded_date ) ) <= $retrieved_data->submition_date ) {
															?>
															<td>
																<span class="mjschool-homework-submitted"> <?php esc_html_e( 'Submitted', 'mjschool' ); ?> </span>
																<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
															</td>
															<?php
														} else {
															?>
															<td>
																<span class="mjschool-purpal-color"> <?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?> </span>
																<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
															</td>
															<?php
														}
													} elseif ( $retrieved_data->status === 2 ) {
														?>
														<td>
															<span class="mjschool-homework-evaluated"><?php esc_html_e( 'Evaluated', 'mjschool' ); ?></span> 
															<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
														</td>
														<?php
													} else {
														?>
														<td>
															<span class="mjschool-homework-pending"> <?php esc_html_e( 'Pending', 'mjschool' ); ?> </span>
															<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
														</td>
														<?php
													}
												}
												// Custom Field Values
												if ( ! empty( $user_custom_field ) ) {
													foreach ( $user_custom_field as $custom_field ) {
														if ( $custom_field->show_in_table === '1' ) {
															$module             = 'homework';
															$custom_field_id    = $custom_field->id;
															$module_record_id   = $retrieved_data->homework_id;
															$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
															if ( $custom_field->field_type === 'date' ) {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															} elseif ( $custom_field->field_type === 'file' ) {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		?>
																		<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
																		<?php
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															} else {
																?>
																<td> 
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		echo esc_html( $custom_field_value );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															}
														}
													}
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
																	$doc_data = json_decode( $retrieved_data->homework_document );
																	if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>" class="mjschool-float-left-width-100px" type="Homework_view"><i class="fa fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																	if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_homework&tab1=upload_homework&action=view&id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) . '&student_id=' . mjschool_encrypt_id( $retrieved_data->student_id ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-eye" aria-hidden="true"></i><?php esc_html_e( 'Upload Homework', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																	if ( isset($user_access['edit']) && $user_access['edit'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=addhomework&action=edit&homework_id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																	if ( isset($user_access['delete']) && $user_access['delete'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=homeworklist&action=delete&homework_id=' . mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fa fa-trash"> </i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
											++$i;
										}
										?>
									</tbody>
								</table>
								<!----------- Homework list table. ------------->
								<?php
								if ( $mjschool_role_name === 'supportstaff' ) {
									?>
									<div class="mjschool-print-button pull-left">
										<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
											<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="homework_delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
									</div>
									<?php
								}
								?>
							<form><!---------------- Homework list page form. ------------->
						</div><!----------- Table responsive. --------------->
					</div><!----------- Panel body. -------------->
					<?php
				} elseif ( isset($user_access['add']) && $user_access['add'] === '1' ) {
					?>
					<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px"> 
						<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=homework&tab=addhomework') ); ?>">
							<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
						</a>
						<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
							<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
						</div> 
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
			<?php
		}
		?>
	</div>
</div> <!------------ Panel body. -------------->
<?php
if ( isset( $_POST['save_homework_front'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_homework_front_nonce' ) ) {
		$insert = new Mjschool_Homework();
		$nonce = wp_create_nonce( 'mjschool_homework_tab' );
		if ( sanitize_text_field(wp_unslash($_POST['action'])) === 'edit' ) {
			if ( isset( $_FILES['homework_document'] ) && ! empty( $_FILES['homework_document'] ) && $_FILES['homework_document']['size'] !== 0 ) {
				if ( $_FILES['homework_document']['size'] > 0 ) {
					$upload_docs1 = mjschool_load_documets_new( $_FILES['homework_document'], $_FILES['homework_document'], sanitize_text_field(wp_unslash($_POST['document_name'])) );
				}
			} elseif ( isset( $_REQUEST['old_hidden_homework_document'] ) ) {
				$upload_docs1 = sanitize_text_field(wp_unslash($_REQUEST['old_hidden_homework_document']));
			}
			$document_data = array();
			if ( ! empty( $upload_docs1 ) ) {
				$document_data[] = array(
					'title' => sanitize_text_field(wp_unslash($_POST['document_name'])),
					'value' => $upload_docs1,
				);
			} else {
				$document_data[] = '';
			}
			$homework_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['homework_id'])) ) );
			$update_data = $insert->mjschool_add_homework( wp_unslash($_POST), $document_data );
			// Update custom field data.
			$custom_field_obj    = new Mjschool_Custome_Field();
			$module              = 'homework';
			$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $homework_id );
			if ( $update_data ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=homework&tab=homeworklist&_wpnonce='.esc_attr( $nonce ).'&updatesuccess=1') );
				die();
			}
		} else {
			$args = array( 'meta_query' => array( array( 'key' => 'class_name', 'value' => sanitize_text_field(wp_unslash($_POST['class_name'])), 'compare' => '=' ) ), 'count_total' => true );  //phpcs:ignore
			$users = new WP_User_Query( $args );
			if ( $users->get_total() === 0 ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=homework&tab=homeworklist&_wpnonce='.esc_attr( $nonce ).'&message=4') );
				die();
			} else {
				if ( isset( $_FILES['homework_document'] ) && ! empty( $_FILES['homework_document'] ) && $_FILES['homework_document']['size'] !== 0 ) {
					if ( $_FILES['homework_document']['size'] > 0 ) {
						$upload_docs1 = mjschool_load_documets_new( $_FILES['homework_document'], $_FILES['homework_document'], sanitize_text_field(wp_unslash($_POST['document_name'])) );
					}
				} else {
					$upload_docs1 = '';
				}
				$document_data = array();
				if ( ! empty( $upload_docs1 ) ) {
					$document_data[] = array(
						'title' => sanitize_text_field(wp_unslash($_POST['document_name'])),
						'value' => $upload_docs1,
					);
				} else {
					$document_data[] = '';
				}
				$insert_data        = $insert->mjschool_add_homework( wp_unslash($_POST), $document_data );
				$custom_field_obj   = new Mjschool_Custome_Field();
				$module             = 'homework';
				$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $insert_data );
				if ( $insert_data ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=homework&tab=homeworklist&_wpnonce='.esc_attr( $nonce ).'&addsuccess=1') );
					die();
				}
			}
		}
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	$delete = new Mjschool_Homework();
	$dele   = $delete->mjschool_get_delete_record( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['homework_id'])) ) ) );
	if ( $dele ) {
		$nonce = wp_create_nonce( 'mjschool_homework_tab' );
		header( 'Location: ?dashboard=mjschool_user&page=homework&tab=homeworklist&_wpnonce='.esc_attr( $nonce ).'&deletesuccess=1' );
	}
}
if ( isset( $_REQUEST['homework_delete_selected'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_books' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	$tablename = 'mjschool_homework';
	$ojc       = new Mjschool_Homework();
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$delete = $ojc->mjschool_get_delete_records( $tablename, sanitize_text_field(wp_unslash($id)) );
			if ( $delete ) {
				$nonce = wp_create_nonce( 'mjschool_homework_tab' );
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=homework&_wpnonce='.esc_attr( $nonce ).'&deleteselectedsuccess=1') );
				die();
			}
		}
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'download' ) {
	$assign_id    = sanitize_text_field(wp_unslash($_REQUEST['stud_homework_id']));
	$homework_obj = new Mjschool_Homework();
	$filedata     = $homework_obj->mjschool_check_uploaded( $assign_id );
	if ( $filedata !== false ) {
		$file = $filedata;
	}
	$upload          = wp_upload_dir();
	$upload_dir_path = $upload['basedir'];
	$file            = $upload_dir_path . '/homework_file/' . $file;
	if ( file_exists( $file ) ) {
		header( 'Content-Description: File Transfer' );
		header( 'Content-type: application/pdf', true, 200 );
		header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file ) );
		ob_clean();
		flush();
		readfile( $file );
		die();
	}
}
if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
	if ( isset( $_POST['student_upload_homework'] ) ) {
		// Initialize variables.
		$file_name     = '';
		$uploaded_date = current_time( 'mysql' ); // Get the current time in MySQL format.
		// Handle file upload.
		if ( ! empty( $_FILES['file']['name'] ) ) {
			// Generate unique file name.
			$randm               = mt_rand( 5, 15 );
			$sanitized_file_name = sanitize_file_name( $_FILES['file']['name'] );
			$file_name           = "H{$randm}_{$sanitized_file_name}";
			$file_tmp            = $_FILES['file']['tmp_name'];
			// Set upload directory.
			$upload          = wp_upload_dir();
			$upload_dir_path = $upload['basedir'];
			$upload_dir      = $upload_dir_path . '/homework_file';
			// Create directory if it doesn't exist.
			if ( ! file_exists( $upload_dir ) ) {
				if ( ! mkdir( $upload_dir, 0700, true ) ) {
					wp_die( esc_html( 'Failed to create directory for homework files.', 'mjschool' ) );
				}
			}
			// Move uploaded file.
			if ( ! move_uploaded_file( $file_tmp, $upload_dir . '/' . $file_name ) ) {
				wp_die( esc_html( 'Failed to upload file.', 'mjschool' ) );
			}
		} else {
			// Use existing file if provided.
			$file_name = sanitize_text_field( wp_unslash($_POST['Uploaded_file']) ?? '' );
		}
		// Proceed only if a file name exists.
		if ( ! empty( $file_name ) ) {
			// Sanitize input data
			$stud_homework_id = sanitize_text_field( wp_unslash($_POST['stu_homework_id']) );
			$stud_id          = sanitize_text_field( wp_unslash($_POST['student_id']) );
			$homework_id      = sanitize_text_field( wp_unslash($_POST['homework_id']) );
			$student_comment  = sanitize_textarea_field( wp_unslash($_POST['student_comment']) );
			$status           = 1; // Default status.
			$result = $objj->mjschool_update_student_homework($stud_homework_id, $file_name, $obtain_marks, $teacher_comment, $evaluate_date, $status);
			// Redirect or show error based on the result.
			if ( $result !== false ) {
				header( 'Location: ?dashboard=mjschool_user&page=homework&tab=homeworklist&success=1' );
			} else {
				wp_die( esc_html( 'Failed to update homework record.', 'mjschool' ) );
			}
		} else {
			echo esc_html__( 'File Not Uploaded.', 'mjschool' );
		}
	}
}
?>