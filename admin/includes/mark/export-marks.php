<?php
/**
 * Exam Result Export Interface.
 *
 * Handles the backend logic and interface for exporting exam results within the MJSchool plugin. 
 * This file allows administrators and authorized users to select class, section, and exam details 
 * to export student marks in a structured format.
 *
 * Key Features:
 * - Validates user input and ensures secure access with WordPress nonces.
 * - Supports class and section selection based on the school type (school/university).
 * - Dynamically loads exams associated with selected classes via AJAX.
 * - Restricts export access based on user roles and permissions.
 * - Integrates client-side validation using jQuery ValidationEngine.
 * - Ensures fully translatable text strings with WordPress internationalization functions.
 *
 * @package      MJSchool
 * @subpackage MJSchool/admin/includes/mark
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );

// Check nonce for export marks tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_exam_result_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res"> <!--------- Panel body. ------->
	<form method="post" id="export_mark_table">
		<div class="form-body mjschool-user-form"><!--------- Form body. ------->
			<div class="row">
				<?php
				if ( 'university' === $school_type ) {
					?>
					<div class="col-md-6 input">
					<?php
				} else { ?>
					<div class="col-md-3 input">
					<?php
					}
					?>
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required] class_id_exam text-input">
						<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
						<?php
						foreach ( mjschool_get_all_class() as $classdata ) {
							?>
							<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<?php if ( $school_type === 'school' ) {?>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
						<?php
						$class_section = '';
						if ( isset( $_REQUEST['class_section'] ) ) {
							$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
						}
						?>
						<select name="class_section" class="mjschool-line-height-30px form-control mjschool-section-id-exam" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( isset( $_REQUEST['class_section'] ) ) {
								$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
								// Sanitize class_id for use in function argument.
								$class_id_sanitized = intval( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) );
								foreach ( mjschool_get_class_sections( $class_id_sanitized ) as $sectiondata ) {
									?>
									<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				<?php } ?>
				<div class="col-md-3 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select id="mjschool-exam-id" name="exam_id" class="mjschool-line-height-30px form-control validate[required] text-input exam_list">
						<option value=""><?php esc_html_e( 'Select Exam', 'mjschool' ); ?></option>
					</select>
				</div>
				<?php
				$mjschool_obj = new MJSchool_Management( get_current_user_id() );
				if ( $mjschool_obj->role === 'teacher' || $mjschool_obj->role === 'supportstaff' ) {
					if ( $user_access['add'] === '1' ) {
						$access = 1;
					} else {
						$access = 0;
					}
				} else {
					$access = 1;
				}
				if ( $access === 1 ) {
					?>
					<div class="col-md-3">
						<input type="submit" value="<?php esc_html_e( 'Export Marks', 'mjschool' ); ?>" name="export_marks" class="btn btn-info mjschool-save-btn" />
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</form>
</div> <!--------- Panel body. ------->