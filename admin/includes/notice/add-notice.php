<?php
/**
 * Admin: Add/Edit Notice Page.
 *
 * This file handles the creation and editing of notices in the MJSchool plugin.
 * It provides a form interface for administrators to publish, update, and manage notices
 * that are visible to teachers, students, parents, and support staff.
 *
 * Key Features:
 * - Allows adding or editing notice details such as title, content, start/end dates, and target audience.
 * - Includes options to send notifications via email or SMS.
 * - Supports custom fields specific to the 'notice' module.
 * - Validates input fields and uses WordPress sanitization and escaping functions.
 * - Uses nonces to ensure secure form submissions.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/notice
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$logo         = get_option( 'mjschool_system_logo' );
$system_color = get_option( 'mjschool_system_color_code' );
$school_type  = get_option( 'mjschool_custom_class' );
?>
<?php
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
	$edit      = 1;
	$notice_id = isset( $_REQUEST['notice_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['notice_id'] ) ) ) ) : 0;
	$post      = get_post( $notice_id );
}
?>
<div class="mjschool-panel-body"> <!-- Mjschool-panel-body. -->
	<form name="class_form" action="" method="post" class="mjschool-form-horizontal" id="notice_form" enctype="multipart/form-data"><!-- Notice form. -->
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="notice_id" value="<?php if ( $edit ) { echo esc_attr( intval( $notice_id ) ); } ?>" />
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( ' Notice Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="mjschool-notice-title" class="form-control validate[required,custom[description_validation]] text-input" maxlength="100" type="text" value="<?php if ( $edit ) { echo esc_attr( $post->post_title ); } ?>" name="notice_title">
							<label for="mjschool-notice-title"><?php esc_html_e( 'Notice Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="notice_content" class="mjschool-textarea-height-60px form-control validate[custom[description_validation]]" maxlength="1000" id="notice_content"><?php if ( $edit ) { echo esc_textarea( $post->post_content ); } ?></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="notice_content"><?php esc_html_e( 'Notice Comment', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="notice_Start_date" class="form-control date_picker validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( get_post_meta( $post->ID, 'start_date', true ) ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="start_date" readonly>
							<label class="date_label" for="notice_content"><?php esc_html_e( 'Notice Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_notice_admin_nonce' ); ?>
				<div class="col-md-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="notice_end_date" class="form-control date_picker validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( get_post_meta( $post->ID, 'end_date', true ) ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="end_date" readonly>
							<label class="date_label" for="notice_content"><?php esc_html_e( 'Notice End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool_notice_for"><?php esc_html_e( 'Notice For', 'mjschool' ); ?></label>
					<?php
					$notice_for_value = '';
					if ( $edit ) {
						$notice_for_value = get_post_meta( $post->ID, 'notice_for', true );
					}
					?>
					<select name="notice_for" id="mjschool_notice_for" class="form-control notice_for_ajax mjschool-max-width-100px">
						<option value="all"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
						<option value="teacher" <?php selected( $notice_for_value, 'teacher' ); ?>><?php esc_html_e( 'Teacher', 'mjschool' ); ?></option>
						<option value="student" <?php selected( $notice_for_value, 'student' ); ?>><?php esc_html_e( 'Student', 'mjschool' ); ?></option>
						<option value="parent" <?php selected( $notice_for_value, 'parent' ); ?>><?php esc_html_e( 'Parent', 'mjschool' ); ?></option>
						<option value="supportstaff" <?php selected( $notice_for_value, 'supportstaff' ); ?>><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></option>
					</select>
				</div>
				<div class="col-md-6 input" id="mjschool-smgt-select-class">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?></label>
					<?php
					if ( $edit ) {
						$classval = get_post_meta( $post->ID, 'smgt_class_id', true );
					} elseif ( isset( $_POST['class_id'] ) ) {
						$classval = intval( wp_unslash( $_POST['class_id'] ) );
					} else {
						$classval = '';
					}
					?>
					<select name="class_id" id="mjschool-class-list" class="form-control mjschool-max-width-100px">
						<option value="all"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
						<?php
						foreach ( mjschool_get_all_class() as $classdata ) {
							?>
							<option value="<?php echo esc_attr( intval( $classdata['class_id'] ) ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<?php if ( $school_type !== 'university' ) { ?>
					<div class="col-md-6 input" id="smgt_select_section">
						<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
						<?php
						if ( $edit ) {
							$sectionval = get_post_meta( $post->ID, 'smgt_section_id', true );
						} elseif ( isset( $_POST['class_section'] ) ) {
							$sectionval = intval( wp_unslash( $_POST['class_section'] ) );
						} else {
							$sectionval = '';
						}
						?>
						<select name="class_section" class="form-control mjschool-max-width-100px" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( $edit ) {
								foreach ( mjschool_get_class_sections( $classval ) as $sectiondata ) {
									?>
									<option value="<?php echo esc_attr( intval( $sectiondata->id ) ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<?php
				}
				if ( ! $edit ) {
					?>
					<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-rtl-margin-top-15px mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-rtl-relative-position">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="chk_mjschool_sent_mail"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
										<input id="chk_mjschool_sent_mail" class="mjschool-check-box-input-margin" type="checkbox" <?php $smgt_mail_service_enable = 0; if ( $smgt_mail_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_mail_service_enable">
										<span><?php esc_html_e( 'Mail', 'mjschool' ); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-rtl-margin-top-15px mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-rtl-relative-position">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="chk_mjschool_sent"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
										<input id="chk_mjschool_sent" type="checkbox" <?php $mjschool_service_enable = 0; if ( $mjschool_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_sms_service_enable">
										<span> <?php esc_html_e( 'SMS', 'mjschool' ); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				?>
				<div class="col-md-6 mjschool-message-none" id="mjschool-message-sent">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<textarea id="mjschool_template" name="sms_template" class="mjschool-textarea-height-47px form-control validate[required]" maxlength="160"></textarea>
							<span class="mjschool-txt-title-label"></span>
							<label class="text-area address active" for="mjschool_template"><?php esc_html_e( 'SMS Text', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
			</div>
			<?php
			// --------- Get Module-Wise Custom Field Data. --------------//
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'notice';
			$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
			?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Notice', 'mjschool' ); } else { esc_attr_e( 'Add Notice', 'mjschool' ); } ?>" name="save_notice" class="btn btn-success mjschool-save-btn" />
					</div>
				</div>
			</div>
		</div>
	</form><!-- Notice form. -->
</div><!-- Mjschool-panel-body. -->