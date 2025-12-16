<?php
/**
 * Manage Subject Form (Admin Page).
 *
 * This file is responsible for handling subject management functionality within the MJSchool plugin.
 * It provides a complete interface for administrators and authorized users to add, edit, and manage
 * subject details including author, edition, credit, syllabus, assigned class, section, teachers,
 * and students. The file also integrates dynamic AJAX-based field loading for enhanced interactivity.
 *
 * Key Features:
 * - Supports both add and edit operations for subjects with pre-filled form data on edit.
 * - Dynamically loads class sections, teachers, and students via AJAX based on user selection.
 * - Allows syllabus file uploads with validation and secure file handling.
 * - Implements Bootstrap Multiselect for assigning multiple teachers and students.
 * - Includes “Add More” functionality for adding multiple subject entries dynamically.
 * - Provides conditional form fields based on school type (School or University mode).
 * - Displays admin notifications and validates user inputs before form submission.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/subject
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
	$edit    = 1;
	$subject = mjschool_get_subject( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['subject_id'])) ) ) );
}
$school_type=get_option( 'mjschool_custom_class' );
?>
<div class="mjschool-panel-body">
	<form name="mjschool-student-form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="subject_form">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="subject_id" value="<?php if ( $edit ) { echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['subject_id'])) );} ?>">
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Subject Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="subject_name" class="form-control validate[required] mjschool-margin-top-10px_res" type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $subject->sub_name ); } ?>" name="subject_name">
							<label for="subject_name"><?php esc_html_e( 'Subject Name', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-padding-top-15px-res">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="subject_edition" class="form-control validate[custom[address_description_validation]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $subject->edition ); }?>" name="subject_edition">
							<label for="subject_edition"><?php esc_html_e( 'Edition', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="subject_author" class="form-control validate[custom[city_state_country_validation]]" maxlength="100" type="text" value="<?php if ( $edit ) { echo esc_attr( $subject->author_name ); }?>" name="subject_author">
							<label for="subject_author"><?php esc_html_e( 'Author Name', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<?php if ( $school_type === "university"){ ?>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="subject_credit" class="form-control validate[required] mjschool-margin-top-10-res" type="number" maxlength="50" value="<?php if ($edit) { echo esc_attr($subject->subject_credit); } ?>" name="subject_credit">
								<label for="subject_credit"><?php esc_html_e( 'Subject Credit', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 input mjschool-error-msg-left-margin">
						<label class="ml-1 mjschool-custom-top-label top" for="class_list_subject"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label><?php
						if ( $edit ) {
							$classval = $subject->class_id;
						} else {
							$classval = '';
						}
						$name_attr = $edit ? 'subject_class' : 'subject_class[]';
						?>
						<select name="<?php echo esc_attr($name_attr); ?>" class="mjschool-line-height-30px form-control validate[required] class_by_teacher_subject" id="class_list_subject">
							<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
							<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-6 mjschool-rtl-margin-top-15px mb-3 mjschool-teacher-list-multiselect">
						<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
							<?php
							if ( $edit){
								$teacherdata_array = mjschool_get_users_by_class_id($subject->class_id);
							}
							else
							{
								$teacherdata_array = mjschool_get_users_data( 'student' );
							}
							$selected_students = array();
							if ( isset( $subject->selected_students ) && !empty( $subject->selected_students ) ) {
								$selected_students = explode( ',', $subject->selected_students );
							}
							?>
							<select name="student_id[0][]" multiple="multiple" id="subject_student_subject" class="form-control validate[required] teacher_list">
								<?php foreach ( $teacherdata_array as $teacherdata ) { ?>
									<option value="<?php echo esc_attr( $teacherdata->ID ); ?>" <?php selected( in_array( $teacherdata->ID, $selected_students ), true ); ?>>
										<?php echo esc_html( mjschool_student_display_name_with_roll($teacherdata->ID ) ); ?>
									</option>
								<?php } ?>
							</select>
							<span class="mjschool-multiselect-label">
								<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Students', 'mjschool' ); ?><span class="required">*</span></label>
							</span>
						</div>
					</div>
					<?php
				}
				if ( $edit ) {
					$syllabus = $subject->syllabus;
					?>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Syllabus', 'mjschool' ); ?></span>
								<div class="col-sm-12">
									<input type="file" name="subject_syllabus" class='form-control file mjschool-file-validation' id="subject_syllabus" />
									<input type="hidden" name="sylybushidden" value="<?php if ( $edit ) { echo esc_attr( $subject->syllabus ); } else { echo ''; }?>">
								</div>
								<?php
								if ( ! empty( $syllabus ) ) {
									?>
									<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
										<a target="blank" class="mjschool-status-read btn btn-default" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . sanitize_file_name( $syllabus ) ); ?>" record_id="<?php echo esc_attr( $subject->subject ); ?>"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?> </a>
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
								<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Syllabus', 'mjschool' ); ?></span>
								<div class="col-sm-12">
									<input type="file" class="col-md-12 form-control file mjschool-file-validation" name="subject_syllabus" id="subject_syllabus" />
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'More Information', 'mjschool' ); ?></h3>
		</div>
		<?php if ( $edit ) { ?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="subject_code" class="form-control validate[required,custom[popup_category_validation],maxSize[8],min[0]] text-input" type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $subject->subject_code ); }?>" name="subject_code">
								<label for="subject_code"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<?php
					if ( $school_type === 'school' )
					{
						?>
						<div class="col-md-6 input mjschool-error-msg-left-margin">
							<label class="ml-1 mjschool-custom-top-label top" for="class_list_subject"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label><?php
							if ( $edit ) {
								$classval = $subject->class_id;
							} else {
								$classval = '';
							}
							$name_attr = $edit ? 'subject_class' : 'subject_class[]';
							?>
							<select name="<?php echo esc_attr($name_attr); ?>" class="mjschool-line-height-30px form-control validate[required] class_by_teacher_subject" id="class_list_subject">
								<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
								<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
									<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-section-subject"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
							<?php
							if ( $edit ) {
								$sectionval = $subject->section_id;
							} elseif ( isset( $_POST['class_section'] ) ) {
								$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
							} else {
								$sectionval = '';
							}
							?>
							<select name="class_section" class="mjschool-line-height-30px form-control" id="mjschool-class-section-subject">
								<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
								<?php
								if ( $edit ) {
									foreach ( mjschool_get_class_sections( $subject->class_id ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<?php
					}
					?>
					<div class="col-md-6 mjschool-rtl-margin-top-15px mb-3 mjschool-teacher-list-multiselect">
						<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
							<?php
							$teachval = array();
							if ( $edit ) {
								$teachval          = mjschool_teacher_by_subject( $subject );
								$teacherdata_array = mjschool_get_teacher_by_class_id( $subject->class_id );
							} else {
								$teacherdata_array = mjschool_get_users_data( 'teacher' );
							}
							?>
							<select name="subject_teacher[]" multiple="multiple" id="subject_teacher_subject" class="form-control validate[required] teacher_list">
								<?php foreach ( $teacherdata_array as $teacherdata ) { ?>
									<option value="<?php echo esc_attr( $teacherdata->ID ); ?>" <?php echo $teacher_obj->mjschool_in_array_r( $teacherdata->ID, $teachval ) ? 'selected' : ''; ?>><?php echo esc_html( $teacherdata->display_name ); ?></option>
								<?php } ?>
							</select>
							<span class="mjschool-multiselect-label">
								<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?><span class="required">*</span></label>
							</span>
						</div>
					</div>
				</div>
			</div>
		<?php } else { ?>
			<div class="more_info">
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="subject_code" class="form-control validate[required,custom[popup_category_validation],maxSize[8],min[0]] text-input" type="text" maxlength="50" value="" name="subject_code[]">
									<label for="subject_code"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<?php
						if ( $school_type === 'school' )
						{
							?>
							<div class="col-md-6 input mjschool-error-msg-left-margin">
								<label class="ml-1 mjschool-custom-top-label top" for="class_list_subject"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label>
								<select name="subject_class[]" class="form-control validate[required] mjschool-width-100px class_by_teacher_subject" id="class_list_subject">
									<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
									<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
										<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php } ?>
								</select>
							</div>
							<?php
						}
						if ( $school_type != 'university' )
						{ ?>
							<div class="col-md-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-section-subject"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
								<select name="class_section[]" class="form-control mjschool-width-100px" id="mjschool-class-section-subject">
									<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
								</select>
							</div>
						<?php }?>
						<?php if ( $school_type != 'university' ) {?>
							<div class="col-md-5 col-10 mjschool-rtl-margin-top-15px mjschool-teacher-list-multiselect mjschool-margin-bottom-15px">
						<?php }else{ ?>
							<div class="col-md-6 col-12 mjschool-rtl-margin-top-15px mjschool-teacher-list-multiselect mjschool-margin-bottom-15px">
						<?php } ?>
							<div class="col-sm-12 mjschool-multiselect-validation-teacher mjschool-multiple-select mjschool-rtl-padding-left-right-0px mjschool-res-rtl-width-100px">
								<?php $teacherdata_array = mjschool_get_users_data( 'teacher' ); ?>
								<select name="subject_teacher[0][]" multiple="multiple" id="subject_teacher_subject" class="form-control validate[required]">
									<?php foreach ( $teacherdata_array as $teacherdata ) { ?>
										<option value="<?php echo esc_attr( $teacherdata->ID ); ?>"><?php echo esc_html( $teacherdata->display_name ); ?></option>
									<?php } ?>
								</select>
								<span class="mjschool-multiselect-label">
									<label class="ml-1 mjschool-custom-top-label top" for="subject_teacher_subject"><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?><span class="required">*</span></label>
								</span>
							</div>
						</div>
						<input type="hidden" class="click_value" name="" value="1">
						
						<?php if ( $school_type === 'school' ) { ?>
							<div class="col-md-1 col-2 col-sm-1 col-xs-12">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_entry()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
							</div>
						<?php } ?>
						
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6 mjschool-rtl-margin-top-15px mb-3">
					<div class="form-group">
						<div class="col-md-12 form-control mjschool-input-height-50px mjschool-checkbox-input-height-47px">
							<div class="row mjschool-padding-radio">
								<div class="input-group mjschool-input-checkbox">
									<span class="mjschool-custom-top-label"><?php esc_html_e( 'Send Email to Teacher', 'mjschool' ); ?></span>
									<div class="checkbox mjschool-checkbox-label-padding-8px">
										<label>
											<input id="chk_subject_mail" type="checkbox" <?php $smgt_mail_service_enable = 0; if ( $smgt_mail_service_enable ) { echo 'checked'; } ?> value="1" name="smgt_mail_service_enable">&nbsp;&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		// --------- Get module-wise custom field data. --------------//
		$custom_field_obj = new Mjschool_Custome_Field();
		$module           = 'subject';
		$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
		?>
		<?php wp_nonce_field( 'save_subject_admin_nonce' ); ?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">
					<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Subject', 'mjschool' ); } else { esc_html_e( 'Add Subject', 'mjschool' );}?>" name="subject" class="btn btn-success mjschool-save-btn mjschool-teacher-for-alert" />
				</div>
			</div>
		</div>
	</form>
</div>