<?php
/**
 * Admin: Add Notification Page.
 *
 * This file renders the admin interface for creating and sending notifications
 * to students, teachers, parents, or staff members in the MJSchool plugin.
 * Administrators can target notifications based on class, section, or specific users,
 * and customize messages with optional custom fields.
 *
 * Key Features:
 * - Provides a dynamic form to create and send notifications to selected users.
 * - Supports filtering by class and section (if school type is "school").
 * - Includes a message title and body with validation and sanitization.
 * - Dynamically loads user lists based on selected class and section.
 * - Integrates WordPress nonces for secure form submission.
 * - Supports module-based custom fields for extended flexibility.
 * - Ensures full localization support using WordPress i18n functions.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/notification
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
?>
<div class="mjschool-panel-body overflow-hidden"><!-- Mjschool-panel-body. -->
	<form name="class_form" action="" method="post" class="mjschool-form-horizontal" id="notification_form" enctype="multipart/form-data">
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Notification Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-notification-class-list-id"><?php esc_html_e( 'Select Class', 'mjschool' ); ?></label>
					<select name="class_id" id="mjschool-notification-class-list-id" class="form-control mjschool-max-width-100px">
						<option value="All"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
						<?php
						foreach ( mjschool_get_all_class() as $classdata ) {
							?>
							<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<?php if ( $school_type === 'school' ) {?>
					<div class="col-md-6 input mjschool-notification-class-section-id">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-notification-class-section-id"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
						<select name="class_section" class="form-control mjschool-max-width-100px" id="mjschool-notification-class-section-id">
							<option value="All"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
						</select>
					</div>
				<?php }?>
				<div class="col-md-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-notification-selected-users"><?php esc_html_e( 'Select Users', 'mjschool' ); ?></label>
					<span class="mjschool-notification-user-display-block">
						<select name="selected_users" id="mjschool-notification-selected-users" class="form-control mjschool-max-width-100px">
							<option value="All"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
						</select>
					</span>
				</div>
				<?php wp_nonce_field( 'save_notice_admin_nonce' ); ?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="title" class="form-control validate[required,custom[description_validation]] text-input" type="text" maxlength="100" name="title">
							<label  for="title"><?php esc_html_e( 'Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="message_body" id="message_body" maxlength="1000" class="mjschool-textarea-height-60px form-control validate[required,custom[description_validation]] text-input"></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="message_body"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		// --------- Get Module-Wise Custom Field Data. --------------//
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = 'notification';
		$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">
					<input type="submit" value="<?php esc_html_e( 'Add Notification', 'mjschool' ); ?>" name="save_notification" class="btn btn-success mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form>
</div><!-- Mjschool-panel-body. -->