<?php
/**
 * Admin Migration Page — Class Promotion Management.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/migration
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );

if ( $mjschool_role === 'administrator' ) {
	$user_access_view = 1;
} else {
	$user_access      = mjschool_get_user_role_wise_filter_access_right_array( 'report' );
	$user_access_view = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
	}
}

// This is Class at admin side.
if ( isset( $_REQUEST['migration'] ) ) {
	// Verify nonce.
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_migration_admin_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	
	$current_class = isset( $_REQUEST['current_class'] ) ? intval( wp_unslash( $_REQUEST['current_class'] ) ) : 0;
	$next_class    = isset( $_REQUEST['next_class'] ) ? intval( wp_unslash( $_REQUEST['next_class'] ) ) : 0;
	
	if ( ! empty( $_REQUEST['exam_id'] ) ) {
		if ( $current_class !== $next_class ) {
			$exam_id       = intval( wp_unslash( $_REQUEST['exam_id'] ) );
			$passing_marks = isset( $_REQUEST['passing_marks'] ) ? intval( wp_unslash( $_REQUEST['passing_marks'] ) ) : 0;
			$student_fail  = mjschool_fail_student_list( $current_class, $next_class, $exam_id, $passing_marks );
			$update        = mjschool_migration( $current_class, $next_class, $exam_id, $student_fail, $passing_marks );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Migration&message=1' ) );
			exit;
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Migration&message=2' ) );
			exit;
		}
	} else {
		if ( $current_class !== $next_class ) {
			$update = mjschool_migration_without_exam( $current_class, $next_class );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Migration&message=1' ) );
			exit;
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Migration&message=2' ) );
			exit;
		}
	}
}
?>
<div class="mjschool-page-inner mjschool-min-h-1631-px"><!--------- Page Inner. ------->
	<div class="mjschool-marks-list mjschool-main-list-margin-5px">
		<?php
		$log_url = admin_url( 'admin.php?page=mjschool_report&tab=migration_report' );
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		$message_string = '';
		switch ( $message ) {
			case '1':
				$message_string = sprintf(
					/* translators: %s: URL to view migration log */
					esc_html__( 'Migration Completed Successfully. %s', 'mjschool' ),
					'<a href="' . esc_url( $log_url ) . '">' . esc_html__( 'Go to View Log', 'mjschool' ) . '</a>'
				);
				break;
			case '2':
				$message_string = esc_html__( 'Current Class and Next Class can not be same.', 'mjschool' );
				break;
		}
		if ( $message && ! empty( $message_string ) ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p>
					<?php
					// Safe output - HTML is constructed safely above with proper escaping.
					echo wp_kses(
						$message_string,
						array(
							'a' => array(
								'href' => array(),
							),
						)
					);
					?>
				</p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
		?>
		<div class="mjschool-panel-white"><!--------- Panel White. ------->
			<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res"><!--------- Panel body. ------->
				<?php $tablename = 'mjschool_marks'; ?>
				<div class="mjschool-panel-body"><!--------- Panel body. ------->
					<form method="post" id="migration_index_table">
						<?php wp_nonce_field( 'save_migration_admin_nonce' ); ?>
						<div class="header">
							<h3 class="mjschool-first-header"><?php esc_html_e( 'Migration Information', 'mjschool' ); ?></h3>
						</div>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-md-6 input">
									<label class="ml-1 mjschool-custom-top-label top" for="current_class"><?php esc_html_e( 'Select Current Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									<select name="current_class" id="current_class" class="form-control validate[required] text-input">
										<option value=""><?php esc_html_e( 'Select Current Class', 'mjschool' ); ?></option>
										<?php
										foreach ( mjschool_get_all_class() as $classdata ) {
											?>
											<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col-md-6 input mjschool-error-msg-left-margin">
									<label class="ml-1 mjschool-custom-top-label top" for="next_class"><?php esc_html_e( 'Select Next Class Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									<select name="next_class" id="next_class" class="form-control validate[required] text-input">
										<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
										<?php
										foreach ( mjschool_get_all_class() as $classdata ) {
											?>
											<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col-md-6 input">
									<label class="ml-1 mjschool-custom-top-label top" for="exam_id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?></label>
									<?php
									$tablename           = 'mjschool_exam';
									$retrieve_class_data = mjschool_get_all_data( $tablename );
									?>
									<select name="exam_id" class="form-control text-input" id="exam_id">
										<option value=""><?php esc_html_e( 'Select Exam', 'mjschool' ); ?></option>
										<?php
										foreach ( $retrieve_class_data as $retrieved_data ) {
											?>
											<option value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>"><?php echo esc_html( $retrieved_data->exam_name ); ?></option>
											<?php
										}
										?>
									</select>
								</div>
								<div id="mjschool-migration-passing-mark" class="mjschool-passing-mark-display-none col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="mjschool-passing-marks" type="number" name="passing_marks" value="" class="form-control validate[required,min[0],maxSize[5]]">
											<label for="mjschool-passing-marks"><?php esc_html_e( 'Passing Marks', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="form-group col-md-6 mjschool-button-possition-padding">
									<input type="submit" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" name="migration" class="btn btn-info mjschool-save-btn" />
								</div>
							</div>
						</div>
					</form>
				</div><!--------- Panel body. ------->
				<div class="clearfix"></div>
			</div><!--------- Panel body. ------->
		</div><!--------- Panel White. ------->
	</div>
</div><!--------- Page Inner. ------->