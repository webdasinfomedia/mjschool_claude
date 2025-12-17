<?php
/**
 * Attendance Management by Qr Page
 *
 * This file handles the QR-based attendance functionality, including class, section, and subject
 * selection and the student attendance scanning process.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/attendance
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for student attendence with qr tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_student_attendance_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<?php
if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
	?>
	<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url( 'https://www.youtube.com/embed/Ed5SkDCKiu4?si=4rsfAczrulo_l8if' ); ?>" title="<?php esc_attr_e( 'Student Attendance With QR Code', 'mjschool' ); ?>">
		
		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-youtube-icon.png"); ?>" alt="<?php esc_html_e( 'YouTube', 'mjschool' ); ?>">
		
	</a>
	<?php
}
?>
<div class="mjschool-panel-body mjschool-attendence-panel-body">
	<form method="post">
		<div class="form-body mjschool-user-form"> <!-- mjschool-user-form start.-->
			<div class="row"><!--Row Div start.-->
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="curr_date" class="form-control date_picker qr_date" type="text"value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['curr_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="curr_date" readonly>
							<label class="date_label" for="curr_date"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<?php
					if ( isset( $_REQUEST['class_id'] ) ) {
						$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
					}
					?>
					<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required] mjschool_qr_class_id">
						<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
						<?php
						foreach ( mjschool_get_all_class() as $classdata ) {
							?>
							<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
					<?php
					$class_section = '';
					if ( isset( $_REQUEST['class_section'] ) ) {
						$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
					}
					?>
					<select name="class_section" class="mjschool-line-height-30px form-control mjschool-qr-class-section mjschool-class-section-subject" id="class_section">
						<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
						<?php
						if ( isset( $_REQUEST['class_section'] ) ) {
							$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
							$class_id_request = isset( $_REQUEST['class_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) : '';
							foreach ( mjschool_get_class_sections( sanitize_text_field( wp_unslash( $class_id_request ) ) ) as $sectiondata ) {
								?>
								<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-subject-list"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field"></span></label>
					<select name="sub_id" id="mjschool-subject-list" class="mjschool-line-height-30px form-control validate[required] mjschool-qr-class-subject">
						<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
						<?php
						$sub_id = 0;
						if ( isset( $_POST['sub_id'] ) ) {
							$sub_id = sanitize_text_field(wp_unslash($_POST['sub_id']));
							$allsubjects = mjschool_get_subject_by_class_id( sanitize_text_field(wp_unslash($_POST['class_id'])) );
							foreach ( $allsubjects as $subjectdata ) {
								?>
								<option value="<?php echo esc_attr( $subjectdata->subid ); ?>" <?php selected( $subjectdata->subid, $sub_id ); ?>> <?php echo esc_html( $subjectdata->sub_name ); ?></option>
								<?php
							}
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<div class="mjschool-panel-heading">
			<h4 class="mjschool-panel-title"><?php esc_html_e( 'Scan QR Code To Take Attendance', 'mjschool' ); ?>
		</div>
		<div class="col-md-12">
		<div class="qrscanner" id="scanner" data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-success-text="<?php esc_html_e( 'Success!', 'mjschool' ); ?>" data-success-msg="<?php esc_html_e( 'Attendance successfully', 'mjschool' ); ?>" data-error-class="<?php esc_html_e( 'Please select correct class!', 'mjschool' ); ?>" data-error-student="<?php esc_html_e( 'Student Not Found!', 'mjschool' ); ?>" data-error-common="<?php esc_html_e( 'Something went wrong, you should choose again!', 'mjschool' ); ?>" data-warning-date="<?php esc_html_e( 'Please select date!', 'mjschool' ); ?>" data-warning-class="<?php esc_html_e( 'Selected class not match to student class!', 'mjschool' ); ?>" data-warning-class-empty="<?php esc_html_e( 'Please select class!', 'mjschool' ); ?>" data-error-camera="<?php esc_html_e( 'Camera device not found!', 'mjschool' ); ?>" data-error-invalid="<?php esc_html_e( 'QR code does not match, you should choose again!', 'mjschool' ); ?>" ></div>
			<hr>
		</div>
	</form>
</div>