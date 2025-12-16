<?php
/**
 * Admin Exam Management Form.
 *
 * This file provides the complete administrative interface for creating and editing exams
 * within the Mjschool plugin. It includes features such as form validation, AJAX-driven 
 * dynamic content loading, and file upload handling for exam syllabus. The form supports 
 * both school and university types, offering flexible contribution marking and 
 * subject-specific score configurations.
 *
 * Key Features:
 * - Add or edit exam details such as name, class, section, term, and date range.
 * - Role-based validation and nonce-based security for safe form submissions.
 * - Upload exam syllabus files with file type and size validation.
 * - Dynamically load university subjects via AJAX.
 * - Manage contribution marks for combined class and exam scores.
 * - Validate total and passing marks to ensure logical score limits.
 * - Option to send notifications via email or SMS to students and parents.
 * - Supports custom fields for the "exam" module.
 * - Responsive, RTL-compatible layout with translation-ready labels.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/exam
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'edit' ) {
	$edit      = 1;
	$exam_data = mjschool_get_exam_by_id( intval( mjschool_decrypt_id( wp_unslash($_REQUEST['exam_id']) ) ) );
}
?>
<!--Group POP-up code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="view_popup"></div>
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<div class="mjschool-panel-body mjschool-margin-top-20px"><!-------- Panel Body. --------->
	<form name="exam_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="exam_form"><!-------- Exam Form --------->
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash($_REQUEST['action']) ) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<div class="header">
			<h3 class="mjschool-first-header">
				<?php esc_html_e( 'Exam Information', 'mjschool' ); ?>
			</h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="exam_name" class="form-control validate[required,custom[popup_category_validation]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $exam_data->exam_name ); } ?>" name="exam_name">
							<label for="exam_name">
								<?php esc_html_e( 'Exam Name', 'mjschool' ); ?><span class="required">*</span>
							</label>
						</div>
					</div>
				</div>
				<div class="col-md-6 input mjschool-error-msg-left-margin">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list">
						<?php esc_html_e( 'Class Name', 'mjschool' ); ?><span class="required">*</span>
					</label>
					<select name="class_id" class="form-control validate[required] mjschool-width-100px" id="mjschool-class-list">
						<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
						<?php
						$classval = '';
						if ( $edit ) {
							$classval = $exam_data->class_id;
							foreach ( mjschool_get_all_class() as $class ) {
								?>
								<option value="<?php echo esc_attr( $class['class_id'] ); ?>" <?php selected( $class['class_id'], $classval ); ?>><?php echo esc_html( mjschool_get_class_name( $class['class_id'] ) ); ?></option>
								<?php
							}
						} else {
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $classval ); ?>>
									<?php echo esc_html( $classdata['class_name'] ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<?php if ( $school_type != 'university' ) { ?>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Section Name', 'mjschool' ); ?></label>
						<?php
						if ( $edit ) {
							$sectionval = $exam_data->section_id;
						} elseif ( isset( $_POST['class_section'] ) ) {
							$sectionval = sanitize_text_field( wp_unslash($_POST['class_section']) );
						} else {
							$sectionval = '';
						}
						?>
						<select name="class_section" class="form-control mjschool-width-100px" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( $edit ) {
								foreach ( mjschool_get_class_sections( $exam_data->class_id ) as $sectiondata ) {
									?>
									<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>>
										<?php echo esc_html( $sectiondata->section_name ); ?>
									</option>
									<?php
								}
							}
							?>
						</select>
					</div>
				<?php }
				if ( $school_type === 'university' )
				{	?>
					<div id="university_subjects_container"></div>
					<?php
				}
				?>
				<div class="col-md-4 input mjschool-width-70px">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-select-exam">
						<?php esc_html_e( 'Exam Term', 'mjschool' ); ?><span class="required">*</span>
					</label>
					<?php
					if ( $edit ) {
						$sectionval1 = $exam_data->exam_term;
					} elseif ( isset( $_POST['exam_term'] ) ) {
						$sectionval1 = sanitize_text_field( wp_unslash($_POST['exam_term']) );
					} else {
						$sectionval1 = '';
					}
					?>
					<select id="mjschool-select-exam" class="form-control validate[required] term_category mjschool-width-100px" name="exam_term">
						<option value=""><?php esc_html_e( 'Select Term', 'mjschool' ); ?></option>
						<?php
						$activity_category = mjschool_get_all_category( 'term_category' );
						if ( ! empty( $activity_category ) ) {
							foreach ( $activity_category as $retrive_data ) {
								?>
								<option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $sectionval1 ); ?>>
									<?php echo esc_html( $retrive_data->post_title ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<div class="col-md-2 col-sm-1 mjschool-res-width-30px">
					<input type="button" id="mjschool-addremove-cat" value="<?php esc_attr_e( 'ADD', 'mjschool' ); ?>" model="term_category" class="btn btn-success mjschool-save-btn" />
				</div>
				<?php 
				if ( $school_type === 'school' ) { ?>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mjschool_passing_mark" class="form-control text-input mjschool-onlyletter-number-space-validation validate[required,max[100]]" type="number" value="<?php if ( $edit ) { echo esc_attr( $exam_data->passing_mark ); } ?>" name="passing_mark">
								<label for="mjschool_passing_mark"><?php esc_html_e( 'Passing Marks', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input mjschool-error-msg-left-margin">
							<div class="col-md-12 form-control">
								<input id="mjschool_total_mark" class="form-control validate[required,max[100]] total_mark mjschool-onlyletter-number-space-validation text-input" name="total_mark" type="number" value="<?php if ( $edit ) { echo esc_attr( $exam_data->total_mark ); } ?>" >
								<label for="mjschool_total_mark"><?php esc_html_e( 'Total Marks', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="exam_start_date" class="form-control date_picker validate[required] text-input" type="text" name="exam_start_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" readonly>
							<label for="exam_start_date" class="date_label"><?php esc_html_e( 'Exam Start Date', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="exam_end_date" class="form-control date_picker validate[required] text-input" type="text" name="exam_end_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" readonly>
							<label for="exam_end_date" class="date_label"><?php esc_html_e( 'Exam End Date', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_exam_admin_nonce' ); ?>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="exam_comment" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150" id="exam_comment"><?php if ( $edit ) { echo esc_textarea( $exam_data->exam_comment ); } ?> </textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="exam_comment"><?php esc_html_e( 'Exam Comment', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
				</div>
				<?php
				if ( $edit ) {
					$doc_data = json_decode( $exam_data->exam_syllabus );
					?>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl mjschool-margin-left-30px">
									<?php esc_html_e( 'Exam Syllabus', 'mjschool' ); ?>
								</span>
								<div class="col-sm-12">
									<input type="file" name="exam_syllabus" class="form-control file mjschool-file-validation" />
									<input type="hidden" name="old_hidden_exam_syllabus" value="<?php if ( ! empty( $doc_data[0]->value ) ) { echo esc_attr( $doc_data[0]->value ); } elseif ( isset( $_POST['exam_syllabus'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash($_POST['exam_syllabus']) ) ); } ?>">
								</div>
								<?php
								if ( ! empty( $doc_data[0]->value ) ) {
									?>
									<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
										<a target="blank" class="mjschool-status-read btn btn-default" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value ) ); ?>" record_id="<?php echo esc_attr( $exam_data->exam_id ); ?>">
											<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
										</a>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					</div>
					<?php
				} else {
					?>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl mjschool-margin-left-30px">
									<?php esc_html_e( 'Exam Syllabus', 'mjschool' ); ?>
								</span>
								<div class="col-sm-12">
									<input type="file" name="exam_syllabus" class="form-control file col-md-12 col-sm-12 col-xs-12 mjschool-file-validation input-file-1">
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				if ( $school_type === 'school' )
				{
					?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-margin-15px-rtl mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio mjschool-rtl-relative-position">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-right-position" for="contributions_section_option">
											<?php esc_html_e( 'Contributions for Class Score and Exam Score', 'mjschool' ); ?>
										</label>
										<input id="contributions_section_option" type="checkbox" class="contributions_section mjschool-check-box-input-margin" name="contributions_section_option" <?php if ( $edit ) { if ( $exam_data->contributions === 'yes' ) { echo 'checked'; } } ?> value="yes"/>&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php 
				}?>
			</div>
		</div>
		<?php
		if ( $edit ) {
			if ( ! empty( $exam_data->contributions_data ) ) {
				?>
				<div id="cuntribution_div" class="<?php if ( $exam_data->contributions === 'yes' ) { ?>mjschool-cuntribution-div-block <?php } else { ?>mjschool-cuntribution-div-none<?php } ?>">
					<?php
					$contributions_data = json_decode( $exam_data->contributions_data );
					foreach ( $contributions_data as $key => $value ) {
						?>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="contributions_label" class="form-control" maxlength="50" type="text" value="<?php echo esc_attr( $value->label ); ?>" name="contributions_label[]">
											<label for="contributions_label"><?php esc_html_e( 'Contributions Label', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-5 col-10">
									<div class="form-group input mjschool-error-msg-left-margin">
										<div class="col-md-12 form-control">
											<input id="contributions_mark" class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="<?php echo esc_attr( $value->mark ); ?>" name="contributions_mark[]">
											<label for="contributions_mark"><?php esc_html_e( 'Contributions Marks', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<?php
								if ( $key === 0 ) {
									 ?>
									<div class="col-md-1 col-2 col-sm-3 col-xs-12">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_contributions()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
									</div>
									<?php 
								} else {
									?>
									<div class="col-md-1 col-2 col-sm-3 col-xs-12">
										<input type="image" onclick="mjschool_delete_parent_elementConstribution(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-float-right mjschool-input-btn-height-width">
									</div>
									<?php
								}
								?>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			} else {
				?>
				<div id="cuntribution_div" class="<?php if ( $exam_data->contributions === 'yes' ) { ?>mjschool-cuntribution-div-block <?php } else { ?>mjschool-cuntribution-div-none<?php } ?>">
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input class="form-control" maxlength="50" type="text" value="" id="contributions_label" name="contributions_label[]">
										<label for="contributions_label">
											<?php esc_html_e( 'Contributions Label', 'mjschool' ); ?>
										</label>
									</div>
								</div>
							</div>
							<div class="col-md-5 col-10">
								<div class="form-group input mjschool-error-msg-left-margin">
									<div class="col-md-12 form-control">
										<input id="contributions_mark" class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="" name="contributions_mark[]">
										<label for="contributions_mark">
											<?php esc_html_e( 'Contributions Marks', 'mjschool' ); ?>
										</label>
									</div>
								</div>
							</div>
							<div class="col-md-1 col-2 col-sm-3 col-xs-12">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_contributions()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
							</div>
						</div>
					</div>
				</div>
				<?php
			}
		} else {
			?>
			<div id="cuntribution_div" class="mjschool-cuntribution-div-none">
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="contributions_label" class="form-control" maxlength="50" type="text" value="" name="contributions_label[]">
									<label for="contributions_label">
										<?php esc_html_e( 'Contributions Label', 'mjschool' ); ?>
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-5 col-10">
							<div class="form-group input mjschool-error-msg-left-margin">
								<div class="col-md-12 form-control">
									<input id="contributions_mark" class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="" name="contributions_mark[]">
									<label for="contributions_mark">
										<?php esc_html_e( 'Contributions Marks', 'mjschool' ); ?>
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-1 col-2 col-sm-3 col-xs-12">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_contributions()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		if ( ! $edit ) {
			?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-margin-15px-rtl mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio mjschool-rtl-relative-position">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-right-position" for="mjschool_enable_exam_mail">
											<?php esc_html_e( 'Send Mail To Parents & Students', 'mjschool' ); ?>
										</label>
										<input type="checkbox" id="mjschool_enable_exam_mail" class="mjschool-check-box-input-margin" name="mjschool_enable_exam_mail" value="1" <?php echo checked( get_option( 'mjschool_enable_exam_mail' ), 'yes' ); ?>/>&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-margin-15px-rtl mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio mjschool-rtl-relative-position">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-right-position" for="mjschool_enable_exam_mjschool_student">
											<?php esc_html_e( 'Send SMS To Students', 'mjschool' ); ?>
										</label>
										<input type="checkbox" id="mjschool_enable_exam_mjschool_student" class="mjschool-check-box-input-margin" name="smgt_enable_exam_mjschool_student" value="1" />&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-margin-15px-rtl mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio mjschool-rtl-relative-position">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-right-position" for="mjschool_enable_exam_mjschool_parent">
											<?php esc_html_e( 'Send SMS To Parents', 'mjschool' ); ?>
										</label>
										<input type="checkbox" id="mjschool_enable_exam_mjschool_parent" class="mjschool-check-box-input-margin" name="smgt_enable_exam_mjschool_parent" value="1" />&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		// --------- Get Module Wise Custom Field Data. --------------//
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = 'exam';
		$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">
					<input type="submit" id="save_exam" value="<?php if ( $edit ) { esc_attr_e( 'Save Exam', 'mjschool' ); } else { esc_attr_e( 'Add Exam', 'mjschool' ); } ?>" name="save_exam" class="btn btn-success check_contribution_marks mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form><!-------- End Form. --------->
</div> <!-------- Panel Body. --------->