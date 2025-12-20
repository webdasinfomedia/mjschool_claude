<?php
/**
 * Message Management Interface - Compose Email.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/message
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for send message tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_message_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

$school_type = get_option( 'mjschool_custom_class' );
?>
<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
	<h2>
       <?php
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			echo esc_html__( 'Edit Message', 'mjschool' );
			$edit = 1;
		}
		?>
	</h2>
	<form name="class_form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-message-form" enctype="multipart/form-data"><!-- form div -->
		<?php 
	wp_nonce_field( 'mjschool_save_message', '_wpnonce' );
		$mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; 
		?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<div class="form-body mjschool-user-form"><!--User form. -->
			<div class="row"><!--Row. -->
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="send_to"><?php esc_html_e( 'Message To', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select name="receiver" class="form-control validate[required] text-input mjschool-min-width-100px" id="send_to">
						<option value="student"><?php esc_html_e( 'Students', 'mjschool' ); ?></option>
						<option value="teacher"><?php esc_html_e( 'Teachers', 'mjschool' ); ?></option>
						<option value="parent"><?php esc_html_e( 'Parents', 'mjschool' ); ?></option>
						<option value="supportstaff"><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></option>
					</select>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input class_selection">
					<label class="ml-1 mjschool-custom-top-label top" for="class_selection_type"><?php esc_html_e( 'Class Selection Type', 'mjschool' ); ?></label>
					<select id="class_selection_type" name="class_selection_type" class="form-control text-input class_selection_type mjschool-min-width-100px">
						<option value="single"><?php esc_html_e( 'Single', 'mjschool' ); ?></option>
						<option value="multiple"><?php esc_html_e( 'Multiple', 'mjschool' ); ?></option>
					</select>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-multiple-class-div input mjchool_display_none">
					<div class="col-sm-12 mjschool-msg-multiple mjschool-multiple-select mjschool-multiselect-validation1 mjschool-rtl-custom-padding-0px">
						<select name="multi_class_id[]" class="form-control" id="selected_class" multiple="multiple">
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php
							}
							?>
						</select>
						<span class="mjschool-multiselect-label">
							<label class="ml-1 mjschool-custom-top-label top" for="selected_class"><?php esc_html_e( 'Select Users', 'mjschool' ); ?><span class="required">*</span></label>
						</span>
					</div>
				</div>
				<div id="mjschool-smgt-select-class" class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-single-class-div class_list_id">
					<label class="ml-1 mjschool-custom-top-label top" for="class_list_id"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select name="class_id" id="class_list_id" class="form-control mjschool-min-width-100px validate[required]">
						<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
						<?php
						foreach ( mjschool_get_all_class() as $classdata ) {
							?>
							<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
						<?php } ?>
					</select>
				</div>
				<?php if ( $school_type === 'school' ) { ?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input class_section_id">
						<label class="ml-1 mjschool-custom-top-label top" for="class_section_id"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
						<?php
						$sectionval = isset( $_POST['class_section'] ) ? intval( wp_unslash( $_POST['class_section'] ) ) : '';
						?>
						<select name="class_section" class="form-control mjschool-min-width-100px" id="class_section_id">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( $edit && isset( $user_info->class_name ) ) {
								foreach ( mjschool_get_class_sections( $user_info->class_name ) as $sectiondata ) {
									?>
									<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				<?php } ?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-single-class-div mjschool-support-staff-user-div input">
					<div id="messahe_test"></div>
					<div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
						<span class="user_display_block">
							<select name="selected_users[]" id="selected_users" class="form-control mjschool-min-width-250px validate[required]" multiple="multiple">
								<?php
								$student_list = mjschool_get_all_student_list();
								foreach ( $student_list as $retrive_data ) {
									echo '<option value="' . esc_attr( $retrive_data->ID ) . '">' . esc_html( $retrive_data->display_name ) . '</option>';
								}
								?>
							</select>
						</span>
						<span class="mjschool-multiselect-label">
							<label class="ml-1 mjschool-custom-top-label top" for="selected_users"><?php esc_html_e( 'Select Users', 'mjschool' ); ?><span class="required">*</span></label>
						</span>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="subject" class="form-control validate[required,custom[description_validation]] text-input" maxlength="100" type="text" name="subject">
							<label for="subject"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="message_body" id="message_body" maxlength="1000" class="mjschool-textarea-height-60px form-control validate[required,custom[description_validation]] text-input"></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="message_body"><?php esc_html_e( 'Message Comment', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-attachment-div">
					<div class="row">
						<div class="col-md-10">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl" for="photo"><?php esc_html_e( 'Attachment', 'mjschool' ); ?></span>
									<div class="col-sm-12">
										<input class="col-md-12 form-control file mjschool-file-validation" name="message_attachment[]" type="file" />
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-2 col-sm-2 col-xs-12">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_new_attachment()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
						</div>
					</div>
				</div>
				<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px mb-3">
					<div class="form-group">
						<div class="col-md-12 form-control mjschool-rtl-relative-position">
							<div class="row mjschool-padding-radio">
								<div>
									<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="mjschool_message_mail_service_enable"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
									<input id="mjschool_message_mail_service_enable" type="checkbox" value="1" name="mjschool_message_mail_service_enable">
									<span> <?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px mb-3">
					<div class="form-group">
						<div class="col-md-12 form-control mjschool-rtl-relative-position">
							<div class="row mjschool-padding-radio">
								<div>
									<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="chk_mjschool_sent"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
									<input id="chk_mjschool_sent" type="checkbox" value="1" name="mjschool_service_enable">
									<span> <?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-message-none" id="mjschool-message-sent">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<textarea id="mjschool_template" name="mjschool_template" class="mjschool-textarea-height-47px form-control validate[required]" maxlength="160"></textarea>
							<span class="mjschool-txt-title-label"></span>
							<label class="text-area address active" for="mjschool_template"><?php esc_html_e( 'SMS Text', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
			</div><!--Row. -->
		</div><!--User form. -->
		<?php
		// --------- Get Module Wise Custom Field Data --------------//
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = 'message';
		$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
		?>
		<div class="form-body mjschool-user-form"><!--User form. -->
			<div class="row"><!--Row. -->
				<div class="col-sm-6">
					<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Message', 'mjschool' ); } else { esc_attr_e( 'Send Message', 'mjschool' ); } ?>" name="save_message" class="btn btn-success mjschool-save-message-selected-user mjschool-save-btn mjschool-rtl-margin-0px" />
				</div>
			</div><!--Row. -->
		</div><!--User form. -->
	</form><!-- Form div -->
</div><!-- Mjschool-panel-body. -->