<?php

/**
 * Homework Add/Edit Form Template.
 *
 * This template displays the form for adding new homework or editing an existing one.
 * It handles the initial data loading for edit mode, displays fields for homework
 * information (Title, Class, Subject, Submission Date, Marks), document upload,
 * content, and notification options.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
    $edit      = 1;
	$obj      = new Mjschool_Homework();
	$classdata = $obj->mjschool_get_edit_record( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['homework_id'] ) ) ) );
}
$school_type = get_option( 'mjschool_custom_class' );
?>
<div class="mjschool-panel-body"><!------------ panel body. --------------->
	<!------------------ Homework add form. ---------------->
	<form name="class_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="class_form">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>

		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Homework Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="title" class="form-control validate[required,custom[address_description_validation]]" maxlength="100" type="text" value="<?php if ( $edit ) { echo esc_attr( $classdata->title ); } ?>" name="title">
							<label  for="title"><?php esc_html_e( 'Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<?php
					if ( $edit ) {
						$classval = $classdata->class_name;
					} elseif ( isset( $_POST['class_name'] ) ) {
						$classval = sanitize_text_field( wp_unslash( $_POST['class_name'] ) );
					} else {
						$classval = '';
					}
					?>
					<select name="class_name" class="mjschool-line-height-30px form-control validate[required] mjschool-max-width-100px" id="mjschool-class-list">
						<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
						<?php
						foreach ( mjschool_get_all_class() as $classdata1 ) {
							?>
							<option value="<?php echo esc_attr( $classdata1['class_id'] ); ?>" <?php selected( $classval, $classdata1['class_id'] ); ?>><?php echo esc_html( $classdata1['class_name'] ); ?></option>
						<?php } ?>
					</select>
				</div>
				<?php if ( $school_type === 'school' ) { ?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin">
						<label class="ml-1 mjschool-custom-top-label top " for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
						<?php
						if ( $edit ) {
							$sectionval = $classdata->section_id;
						} elseif ( isset( $_POST['class_section'] ) ) {
							$sectionval = sanitize_text_field( wp_unslash( $_POST['class_section'] ) );
						} else {
							$sectionval = '';
						}
						?>
						<select name="class_section" class="mjschool-line-height-30px form-control mjschool-max-width-100px mjschool-class-section-subject" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( $edit ) {
								foreach ( mjschool_get_class_sections( $classdata->class_name ) as $sectiondata ) {
									?>
									<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				<?php } ?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-subject-list"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<?php
					$subject = ( $edit ) ? mjschool_get_subject_by_class_id( $classval ) : array();
					?>
					<select name="subject_id" id="mjschool-subject-list" class="mjschool-line-height-30px form-control validate[required] text-input mjschool-max-width-100px">
						<?php
						if ( $edit ) {
							foreach ( $subject as $record ) {
								$select = ( $record->subid === $classdata->subject ) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr( $record->subid ); ?>" <?php selected( $record->subid, $classdata->subject ); ?>><?php echo esc_html( $record->sub_name ); ?></option>
								<?php
							}
						} else {
							?>
							<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Homework Document', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<?php
				if ( $edit ) {
					$doc_data = json_decode( $classdata->homework_document );
					?>
					<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input type="text" name="document_name" id="title_value" value="<?php if ( ! empty( $doc_data[0]->title ) ) { echo esc_attr( $doc_data[0]->title ); } elseif ( isset( $_POST['document_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['document_name'] ) ) ); } ?>" class="form-control validate[custom[onlyLetter_specialcharacter],maxSize[50]] margin_cause" />
								<label  for="title_value"><?php esc_html_e( 'Documents Title', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-5">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl mjschool-margin-left-30px"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
								<div class="col-sm-12">
									<input type="file" name="homework_document" class="form-control file mjschool-file-validation input-file" />
									<input type="hidden" name="old_hidden_homework_document" value="<?php if ( ! empty( $doc_data[0]->value ) ) { echo esc_attr( $doc_data[0]->value ); } elseif ( isset( $_POST['homework_document'] ) ) { echo esc_attr( sanitize_file_name( wp_unslash( $_POST['homework_document'] ) ) ); } ?>">
								</div>
							</div>
						</div>
					</div>
					<?php
					if ( ! empty( $doc_data[0]->value ) ) {
						?>
						<div class="col-lg-1 col-md-1 col-sm-1 col-xs-12 mjschool-download-icon">
							<a target="blank" class="mjschool-status-read btn btn-default" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . sanitize_file_name( $doc_data[0]->value ) ); ?>" record_id="<?php echo esc_attr( $classdata->homework_id ); ?>">
							<i class="fas fa-download" id="mjschool-download-icon"></i></a>
						</div>
						<?php
					}
				} else {
					?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input type="text" name="document_name" id="title_value" class="form-control validate[custom[description_validation],maxSize[50]] margin_cause" />
								<label  for="title_value"><?php esc_html_e( 'Documents Title', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
								<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl mjschool-margin-left-30px"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
								<div class="col-sm-12">
									<input type="file" name="homework_document" class="form-control file col-md-12 mjschool-file-validation input-file">
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="sdate" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $classdata->submition_date ) ); } ?>" class="datepicker date_picker form-control validate[required] text-input" type="text" name="sdate" readonly>
							<label class="date_label" for="sdate"><?php esc_html_e( 'Submission Date', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="marks" value="<?php if ( $edit ) { echo esc_attr( $classdata->marks ); } ?>" class="form-control text-input" type="number" name="homework_marks">
							<label class="date_label" for="marks"><?php esc_html_e( 'Homework Marks', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Homework Content', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 input">
					<span class="ml-1 mjschool-custom-top-label top" for="class_capacity"><?php esc_html_e( 'Content', 'mjschool' ); ?> </span>
					<div class="form-control">
						<?php
						$setting = array(
							'media_buttons' => false,
						);
						if ( ! empty( $classdata ) ) {
							$content = $classdata->content;
						} else {
							$content = '';
						}
						wp_editor( $edit ? wp_kses_post( stripslashes( $content ) ) : '', 'content', $setting );
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
		if ( ! $edit ) {
			?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-margin-15px-rtl mb-3">
								<div class="row mjschool-padding-radio">
									<div class="input-group mjschool-input-checkbox">
										<label class="mjschool-custom-top-label" for="mjschool_enable_homework_mail"><?php esc_html_e( 'Send Mail To Parents & Students', 'mjschool' ); ?></label>
										<input  id="mjschool_enable_homework_mail" type="checkbox" class="mjschool-check-box-input-margin" name="mjschool_enable_homework_mail" value="1" <?php checked( get_option( 'mjschool_enable_homework_mail' ), 'yes' ); ?> />&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px mb-3">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label" for="mjschool_enable_homework_mjschool_student"><?php esc_html_e( 'Enable Send SMS To Student', 'mjschool' ); ?></label>
										<input id="mjschool_enable_homework_mjschool_student" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_homework_mjschool_student" value="1" /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px mb-3">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label" for="mjschool_enable_homework_mjschool_parent"><?php esc_html_e( 'Enable Send SMS To Parent', 'mjschool' ); ?></label>
										<input id="mjschool_enable_homework_mjschool_parent" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_homework_mjschool_parent" value="1" /><?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		wp_nonce_field( 'save_homework_front_nonce' ); 
		// --------- Get module wise custom field data. --------------//
		$custom_field_obj = new Mjschool_Custome_Field();
		$module           = 'homework';
		$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6 col-sm-6 col-xs-12">
					<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Homework', 'mjschool' ); } else { esc_attr_e( 'Add Homework', 'mjschool' ); } ?>" name="save_homework_front" class="btn btn-success mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form>
</div>