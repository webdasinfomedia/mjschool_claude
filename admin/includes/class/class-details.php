<?php
/**
 * Admin Class Details View.
 *
 * Displays detailed information for a specific class within the Mjschool plugin.
 * This file is part of the admin interface that allows users to view class data,
 * including general details, associated sections, and enrolled students.
 *
 * Key Features:
 * - Secure nonce verification to validate view actions.
 * - Dynamic tab navigation for class details (General, Section, Student).
 * - Displays class metadata such as name, numeric value, capacity, and sections.
 * - Conditional rendering based on user access rights and school type.
 * - Integrates WordPress escaping, sanitization, and i18n functions.
 * - Supports admin edit links when user permissions allow.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/class
 * @since      1.0.0
 * @since      2.0.1 Code quality improvements - Fixed array access with isset() checks
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'view_action' ) ) {
	$class_id                  = intval( mjschool_decrypt_id( $_REQUEST['class_id'] ) );
	$classdata                 = mjschool_get_class_by_id( $class_id );
	$mjschool_custom_field_obj = new Mjschool_Custome_Field();
	$active_tab1               = isset( $_REQUEST['tab1'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab1'] ) ) : 'general';
	?>
	<div class="mjschool-panel-body mjschool-view-page-main"><!-- START PANEL BODY DIV.-->
		<div class="content-body"><!-- START CONTENT BODY DIV.-->
			<section id="mjschool-user-information"><!-- Detail Page Header Start. -->
				
				<div class="mjschool-view-page-header-bg">
					<div class="row">
						<div class="col-xl-10 col-md-9 col-sm-10">
							<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
								<img class="mjschool-user-view-profile-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-class.png"); ?>">
								<div class="row mjschool-profile-user-name">
									<div class="mjschool-float-left mjschool-view-top1">
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
											<span class="mjschool-view-user-name-label"><?php echo esc_html( $classdata->class_name); ?></span>
											<?php
											if ($user_access_edit === '1' ) 
											{
												?>
												<div class="mjschool-view-user-edit-btn">
												<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=addclass&action=edit&class_id=' . mjschool_encrypt_id($classdata->class_id ) . '&_wpnonce=' . mjschool_get_nonce( 'edit_action' ) ) ); ?>">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
													</a>
												</div>
												<?php
											}
											?>
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
						<?php
						$class_id = isset($_GET['class_id']) ? sanitize_text_field(wp_unslash($_GET['class_id'])) : 0;
						$nonce    = esc_attr( mjschool_get_nonce('view_action') );
						?>
						<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
							<li class="<?php echo ($active_tab1 === 'general') ? 'active' : ''; ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=general&class_id=' . $class_id . '&_wpnonce=' . $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo ($active_tab1 === 'general') ? 'active' : ''; ?>">
									<?php esc_html_e('GENERAL', 'mjschool'); ?>
								</a>
							</li>
							<?php if ( $school_type === 'school' ) {?>
								<li class="<?php echo ($active_tab1 === 'section_list') ? 'active' : ''; ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=section_list&class_id=' . $class_id . '&_wpnonce=' . $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo ($active_tab1 === 'section_list') ? 'active' : ''; ?>">
										<?php esc_html_e('Section', 'mjschool'); ?>
									</a>
								</li>
							<?php } ?>
							<li class="<?php echo ($active_tab1 === 'student_list') ? 'active' : ''; ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=student_list&class_id=' . $class_id . '&_wpnonce=' . $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo ($active_tab1 === 'student_list') ? 'active' : ''; ?>">
									<?php esc_html_e('Student', 'mjschool'); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</section>
			<section id="mjschool-body-content-area">
				<div class="mjschool-panel-body"><!-- START PANEL BODY DIV.-->
					<?php
					
					$section_success = isset( $_REQUEST['section_success'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['section_success'] ) ) : '0';
					switch ( $section_success ) {
						case 'insert_success':
							$message_string = esc_html__( 'Section Added Successfully.', 'mjschool' );
							break;
						case 'edit_success':
							$message_string = esc_html__( 'Section Updated Successfully.', 'mjschool' );
							break;
						case 'delete_success':
							$message_string = esc_html__( 'Section Deleted Successfully.', 'mjschool' );
							break;
						case 'exist':
							$message_string = esc_html__( 'This Section is already exist in this Class.', 'mjschool' );
							break;
					}
					if ( $section_success ) {
						?>
						<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
							<p><?php echo esc_html( $message_string ); ?></p>
							<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
						</div>
						<?php
					}
					// --------------- GENERAL TAB START. ----------------//
					if ( $active_tab1 === 'general' ) {
						?>
						<div class="row">
							<div class="col-xl-12 col-md-12 col-sm-12">
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
									<div class="mjschool-guardian-div">
										<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Class Information', 'mjschool' ); ?> </label>
										<div class="row">
											<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
													<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Class Name', 'mjschool' ); ?> </label><br />
													<?php
													if ( $user_access_edit === '1' && empty( $classdata->class_name ) ) {
														$edit_url = admin_url( 'admin.php?page=mjschool_class&tab=addclass&action=edit&class_id=' . esc_attr( mjschool_encrypt_id( $classdata->class_id ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} 
													else {
														?>
														<label class="mjschool-view-page-content-labels"> <?php echo esc_html( ucfirst( $classdata->class_name ) ); ?> </label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
													<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Class Numeric Value', 'mjschool' ); ?> </label><br />
													<?php
													if ( $user_access_edit === '1' && empty( $classdata->class_num_name ) ) {
														$edit_url = admin_url( 'admin.php?page=mjschool_class&tab=addclass&action=edit&class_id=' . esc_attr( mjschool_encrypt_id( $classdata->class_id ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels"><?php echo esc_html( $classdata->class_num_name ); ?></label>
													<?php } ?>
												</div>
												<?php
												$class_id = $classdata->class_id;
												
												$mjschool_user = count(get_users(array(
													'meta_key' => 'class_name',
													'meta_value' => $class_id
												 ) ) );
												
												?>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
													<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Student Capacity', 'mjschool' ); ?> </label><br />
													<?php
													if ( $user_access_edit === '1' && empty( $classdata->class_capacity ) ) {
														$edit_url = admin_url( 'admin.php?page=mjschool_class&tab=addclass&action=edit&class_id=' . esc_attr( mjschool_encrypt_id( $classdata->class_id ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels">
															<?php
															echo esc_html( $mjschool_user ) . ' ';
															esc_attr_e( 'Out Of', 'mjschool' );
															echo ' ' . esc_html( $classdata->class_capacity );
															?>
														</label>
													<?php } ?>
												</div>
												<?php if ( $school_type === 'school' ) {?>
													<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
														<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Class Section', 'mjschool' ); ?> </label><br />
														<label class="mjschool-view-page-content-labels">
															<?php
															$section_id   = mjschool_get_section_by_class_id( $class_id );
															$section_name = '';
															foreach ( $section_id as $section ) {
																$section_name .= $section->section_name . ', ';
															}
															$section_name_rtrim = rtrim( $section_name, ', ' );
															$section_name_ltrim = ltrim( $section_name_rtrim, ', ' );
															if ( ! empty( $section_name_ltrim ) ) {
																echo esc_html( $section_name_ltrim );
															} else {
																esc_html_e( 'No Section', 'mjschool' );
															}
															?>
														</label>
													</div>
													<?php 
												}
												if ( $school_type === 'university' ) { ?>
													<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
														<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Academaic Year', 'mjschool' ); ?> </label><br />
														<label class="mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $classdata->academic_year ) ) {
																echo esc_html( $classdata->academic_year);
															} else {
																esc_html_e( 'Not Selected', 'mjschool' );;
															}
															?>
														</label>
													</div>
													<?php
												}
												?>
												<div class="col-xl-8 col-md-8 col-sm-12 mjschool-margin-bottom-10-res">
													<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Class Teachers', 'mjschool' ); ?> </label><br />
													<label class="mjschool-view-page-content-labels">
														<?php
														$teachers     = mjschool_get_teacher_by_class_id( $class_id );
														$teacher_name = '';
														foreach ( $teachers as $teacher_data ) {
															$teacher_name .= ucfirst( $teacher_data->display_name ) . ', ';
														}
														$teacher_name_rtrim = rtrim( $teacher_name, ', ' );
														$teacher_name_ltrim = ltrim( $teacher_name_rtrim, ', ' );
														if ( ! empty( $teacher_name_ltrim ) ) {
															echo esc_html( $teacher_name_ltrim );
														} else {
															esc_attr_e( 'No Teachers', 'mjschool' );
														}
														?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
								$module = 'class';
								$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
								?>
							</div>
						</div>
						<?php
					}
					// --------------- STUDENT LIST TAB START. ------------//
					if ( $active_tab1 === 'section_list' ) {
						?>
						<div class="row">
							<div class="col-xl-12 col-md-12 col-sm-12">
								<?php
									// INSERT SECTION DATA.
								if ( isset( $_POST['save_class_section'] ) ) {
									$class_id = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash($_REQUEST['class_id'] ))) );
									// Verify nonce for security.
									if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['_wpnonce'])), 'save_class_section_nonce' ) ) {
										wp_die( esc_html__( 'Invalid request. Please try again.', 'mjschool' ) );
									}
									$section          = sanitize_text_field( wp_unslash($_POST['section_name'] ) );
									$section_id       = isset( $_POST['section_id'] ) ? intval( $_POST['section_id'] ) : null;
									$mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : null;
									global $wpdb;
									$class_section_table = $wpdb->prefix . 'mjschool_class_section';
									// Check if the section already exists for the given class.
									$prepared_statement = $wpdb->prepare(
										"SELECT * FROM $class_section_table WHERE class_id = %d AND section_name = %s", $class_id, $section
									);
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
									$existing_section = $wpdb->get_row( $prepared_statement );
									$sectiondata      = array(
										'class_id'     => $class_id,
										'section_name' => $section,
									);
									if ( $mjschool_action === 'edit_section' ) {
										// Editing an existing section.
										if ( empty( $existing_section ) || $existing_section->id === $section_id ) {
											// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
											$result = $wpdb->update( $class_section_table, $sectiondata, array( 'id' => $section_id ) );
											// SECURITY FIX: Safe array access
											$class_id_param = isset($_REQUEST['class_id']) ? sanitize_text_field( wp_unslash($_REQUEST['class_id'])) : '';
											wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=section_list&class_id=' . $class_id_param . '&section_success=edit_success&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ) );
											exit;
										} else {
											// SECURITY FIX: Safe array access
											$class_id_param = isset($_REQUEST['class_id']) ? sanitize_text_field( wp_unslash($_REQUEST['class_id'])) : '';
											$section_id_param = isset($_POST['section_id']) ? sanitize_text_field( wp_unslash($_POST['section_id'])) : '';
											wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=section_list&action=edit_section&class_id=' . $class_id_param . '&section_id=' . $section_id_param . '&section_success=exist&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ) );
											exit;
										}
									} else {
										// Adding a new section.
										if ( empty( $existing_section ) ) {
											// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
											$result = $wpdb->insert( $class_section_table, $sectiondata );
											if ( $result ) {
												// SECURITY FIX: Safe array access
												$class_id_param = isset($_REQUEST['class_id']) ? sanitize_text_field( wp_unslash($_REQUEST['class_id'])) : '';
												wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=section_list&class_id=' . $class_id_param . '&section_success=insert_success&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ) );
											exit;
											}
										} else {
											// SECURITY FIX: Safe array access
											$class_id_param = isset($_REQUEST['class_id']) ? sanitize_text_field( wp_unslash($_REQUEST['class_id'])) : '';
											wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=section_list&class_id=' . $class_id_param . '&section_success=exist&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ) );
											exit;
										}
									}
								}
								// CLASS SECTION DELETE CODE.
								if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete_section' ) {
									$section_id = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash($_REQUEST['section_id'] ) ) ) );
									$result     = mjschool_delete_class_section( $section_id );
									if ( $result ) {
										// SECURITY FIX: Safe array access
										$class_id_param = isset($_REQUEST['class_id']) ? sanitize_text_field( wp_unslash($_REQUEST['class_id'])) : '';
										wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=section_list&class_id=' . $class_id_param . '&section_success=delete_success&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ) );
										exit;
									}
								}
								?>
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
									<div class="mjschool-guardian-div">
										<?php
										$edit = 0;
										if ( isset( $_REQUEST['action'] ) && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit_section' ) ) {
											$edit    = 1;
											$id      = intval( sanitize_text_field( wp_unslash($_REQUEST['section_id'])) );
											$section = mjschool_single_section( $id );
										}
										?>
										<form name="class_Section_form" action="" method="post" class="mjschool-form-horizontal" id="class_Section_form"><!------- form Start --------->
											<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash($_REQUEST['action'])) : 'insert'; ?>
											<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
											<input type="hidden" name="section_id" value="<?php if ( $edit ) { echo esc_attr( $section->id );} ?>"/>
											<div class="header">	
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Add Class Section', 'mjschool' ); ?></h3>
											</div>
											<div class="form-body mjschool-user-form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control">
																<input id="section_name" class="form-control validate[required,custom[popup_category_validation,required]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $section->section_name );} ?>" name="section_name">
																<label for="section_name"><?php esc_html_e( 'Section Name', 'mjschool' ); ?><span class="required">*</span></label>
															</div>
														</div>
													</div>
													<?php wp_nonce_field( 'save_class_section_nonce' ); ?>
													<div class="col-sm-3 col-md-3 col-lg-3 col-xs-12">
														<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Section', 'mjschool' ); } else { esc_html_e( 'Add Section', 'mjschool' );} ?>" name="save_class_section" class="mjschool-save-btn" />
													</div>
												</div>
											</div>
										</form>
									</div>
								</div>
								<div class="header mt-4">	
									<h3 class="mjschool-first-header"><?php esc_html_e( 'Class Section List', 'mjschool' ); ?></h3>
								</div>
								<?php
								global $wpdb;
								$class_section_table = $wpdb->prefix . 'mjschool_class_section';
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
								$retrieve_class_data = $wpdb->get_results(
									$wpdb->prepare( "SELECT * FROM $class_section_table WHERE class_id = %d", mjschool_decrypt_id($class_id) )
								);
								if ( ! empty( $retrieve_class_data ) ) {
									?>
									<div class="mjschool-panel-body">
										<div class="table-responsive">
											<form id="mjschool-common-form" name="mjschool-common-form" method="post">
												<table id="section_list" class="display" cellspacing="0" width="100%">
													<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
														<tr>
															<th><?php esc_html_e( 'Section Name', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
															<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
														</tr>
													</thead>
													<tbody>
														<?php
														foreach ( $retrieve_class_data as $retrieved_data ) {
															?>
															<tr>
																<td><?php echo esc_html( $retrieved_data->section_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Section Name', 'mjschool' ); ?>"></i></td>
																<td>
																	<?php
																	if ( ! empty( $retrieved_data->class_id ) ) {
																		echo esc_html( mjschool_get_class_name_by_id( $retrieved_data->class_id ) );
																	} else {
																		esc_html_e( 'Not Provided', 'mjschool' ); }
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
																</td>
																<td class="action">
																	<div class="mjschool-user-dropdown">
																		<ul  class="mjschool_ul_style">
																			<li >
																				<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																					
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-more.png")?>">
																					
																				</a>
																				<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																					<?php
																					if ( $user_access_edit === '1' ) {
																						// SECURITY FIX: Safe array access
																						$class_id_param = isset($_REQUEST['class_id']) ? sanitize_text_field( wp_unslash($_REQUEST['class_id'])) : '';
																						?>
																						<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=section_list&action=edit_section&class_id=' . $class_id_param . '&section_id=' . $retrieved_data->id . '&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a> 
																						</li>
																						<?php
																					}
																					if ( $user_access_delete === '1' ) {
																						// SECURITY FIX: Safe array access
																						$class_id_param = isset($_REQUEST['class_id']) ? sanitize_text_field( wp_unslash($_REQUEST['class_id'])) : '';
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=section_list&action=delete_section&class_id=' . $class_id_param . '&section_id=' . mjschool_encrypt_id( $retrieved_data->id ) . '&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color"  onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a> 
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
												<div class="mjschool-print-button pull-left">
													<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
														<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_none" value="">
														<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
													</button>
													<?php
													if ( $user_access_delete === '1' ) {
														 ?>
														<button id="mjschool-delete-selected-room" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected','mjschool' );?>" name="mjschool-delete-selected-room" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
														<?php 
													}
													?>
												</div>
											</form>
										</div>
									</div>
									<?php
								} else {
									?>
									<div class="mjschool-calendar-event-new"> 
										<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
									</div>		
									<?php
								}
								?>
							</div>
						</div>
						<?php
					}
					// --------------- STUDENT LIST TAB END. ------------//
					if ( $active_tab1 === 'student_list' ) {
						if ( $school_type === "school")
						{
							?>
							<form method="post">
								<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_list_nonce' ) ); ?>">
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-md-4 input">
											<label class="ml-1 mjschool-custom-top-label top" for="filter_section_id"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
											<select id="filter_section_id" name="filter_section_id" class="mjschool-line-height-30px form-control class_id_exam validate[required]">
												<option value="all_section"><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
												<?php
												$section    = mjschool_get_class_sections( $class_id );
												$section_id = '';
												if ( isset( $_REQUEST['filter_section_id'] ) ) {
													$section_id = sanitize_text_field( wp_unslash($_REQUEST['filter_section_id']));
												}
												foreach ( $section as $section_data ) {
													?>
													<option value="<?php echo esc_attr( $section_data->id ); ?>" <?php selected( $section_data->id, $section_id ); ?>><?php echo esc_html( $section_data->section_name ); ?></option>
													<?php
												}
												?>
											</select>         
										</div>
										<div class="col-md-3">
											<input type="submit" name="view_student_list" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
										</div>
									</div>
								</div>
							</form>
							<?php
						}
						
						if( isset( $_POST['view_student_list'] ) )
						{
							if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_student_list_nonce')) {
								wp_die(esc_html__('Security check failed.', 'mjschool'));
							}
							$class_id = intval(mjschool_decrypt_id(wp_unslash($_REQUEST['class_id'] ) ) );
							if ( sanitize_text_field( wp_unslash($_POST['filter_section_id'])) === "all_section")
							{
								$student_list = mjschool_get_student_name_with_class($class_id);
							}
							else
							{
								$filter_section = sanitize_text_field( wp_unslash($_POST['filter_section_id']));
								$student_list = mjschool_get_student_name_with_class_and_section($class_id, $filter_section);
							}
						}
						else
						{
							$student_list = mjschool_get_student_name_with_class(mjschool_decrypt_id($class_id));
						}
						
						if ( ! empty( $student_list ) ) {
							?>
							<div class="mjschool-panel-body">
								<div class="table-responsive">
									<form id="mjschool-common-form" name="mjschool-common-form" method="post">
										<table id="class_wise_student_list" class="display" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Student Name & Email', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
													<?php if ( $school_type === 'school' ) {?>
														<th><?php esc_html_e( 'Section', 'mjschool' ); ?></th>
													<?php } ?>
													<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php
												foreach ( $student_list as $retrieved_data ) {
													?>
													<tr>
														<td class="mjschool-user-image mjschool-width-50px-td">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ) ); ?>">
																<?php
																$uid       = $retrieved_data->ID;
																$umetadata = mjschool_get_user_image( $uid );
																
																if (empty($umetadata ) ) {
																	echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
																} else {
																	echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
																}
																
																?>
															</a>
														</td>
														<td class="name">
															<a class="mjschool-color-black" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&tab=view_student&action=view_student&student_id='.rawurlencode( mjschool_encrypt_id( $retrieved_data->ID ) ) .'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>">
																<?php echo esc_html( $retrieved_data->display_name ); ?>
															</a>
															<br>
															<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></span>
														</td>
														<td class="roll_no">
															<?php
															if ( get_user_meta( $retrieved_data->ID, 'roll_id', true ) ) {
																echo esc_html( get_user_meta( $retrieved_data->ID, 'roll_id', true ) );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Roll No.', 'mjschool' ); ?>"></i>
														</td>
														<td class="name">
															<?php $class_id  = get_user_meta( $retrieved_data->ID, 'class_name', true );
															$classname = mjschool_get_class_name( $class_id );
															if ( $classname === ' ' ) {
																esc_html_e( 'Not Provided', 'mjschool' );
															} else {
																echo esc_html( $classname );
															} ?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
														</td>
														<?php if ( $school_type === 'school' ) { ?>
															<td class="name">
																<?php $section_name = get_user_meta( $retrieved_data->ID, 'class_section', true );
																if ( $section_name !== '' ) {
																	echo esc_attr( mjschool_get_section_name( $section_name ) );
																} else {
																	esc_attr_e( 'No Section', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Section', 'mjschool' ); ?>"></i>
															</td>
														<?php } ?>
														<td class="action">
															<div class="mjschool-user-dropdown">
																<ul class="mjschool_ul_style">
																	<li>
																		<a href="#" data-bs-toggle="dropdown" aria-expanded="false">
																			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																		</a>
																		<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?> </a>
																			</li>
																		</ul>
																	</li>
																</ul>
															</div>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</form>
								</div>
							</div>
							<?php
						} else {
							 ?>
							<div class="mjschool-calendar-event-new">
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php 
						}
					}
					?>
				</div>
			</section>
		</div>
	</div>
	<?php
} else {
	wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
}