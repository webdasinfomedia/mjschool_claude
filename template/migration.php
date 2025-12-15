<?php
/**
 * Student Migration and Class Promotion View/Controller.
 *
 * This file manages the administrative process of migrating or promoting students
 * from one academic class to the next, typically based on examination results and
 * passing marks criteria.
 *
 * Key features include:
 * - **Access Control:** Enforces permissions based on the current user's role ($user_access).
 * - **Selection Forms:** Provides forms to select the **From Class**, **To Class**, and the **Exam** on which migration will be based.
 * - **Passing Marks:** Allows setting a mandatory passing marks threshold for migration eligibility.
 * - **Form Processing:** Handles the form submission to initiate the student migration/promotion process based on the selected criteria.
 * - **Validation:** Includes form validation for required fields like passing marks.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 * 
 * */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
?>
<?php
if ( isset( $_REQUEST['migration'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_migration_admin_nonce' ) ) {
		$current_class = sanitize_text_field( wp_unslash($_REQUEST['current_class']) );
		$next_class    = sanitize_text_field( wp_unslash($_REQUEST['next_class']) );
		if ( $current_class !== $next_class ) {
			$exam_id       = sanitize_text_field( wp_unslash($_REQUEST['exam_id']) );
			$passing_marks = sanitize_text_field( wp_unslash($_REQUEST['passing_marks']) );
			$student_fail  = mjschool_fail_student_list( $current_class, $next_class, $exam_id, $passing_marks );
			$update        = mjschool_migration( $current_class, $next_class, $exam_id, $student_fail, $passing_marks );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=migration&message=1' ) );
			die();
		} else {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=migration&message=2' ) );
			die();
		}
	}
}
?>
<div class="mjschool-page-inner mjschool-min-h-1631-px"><!--------- Page inner. ------->
	<div class="mjschool-marks-list mjschool-main-list-margin-5px">
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
		$log_url = home_url( '?dashboard=mjschool_user&page=report&tab=migration_report');
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Migration Completed Successfully.', 'mjschool' ) . ' <a href="' . esc_url($log_url) . '">Go to View Log</a>';
				break;
			case '2';
				$message_string = esc_html__( 'Current Class and Next Class can not be same.', 'mjschool' );
				break;
		}
		if ( $message ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible mjschool-margin-top-10px" role="alert">
				
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
				
				<p><?php echo esc_html( $message_string ); ?></p>
			</div>
			<?php
		}
		?>
		<div class="mjschool-panel-white"><!--------- Panel white. ------->
			<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res"> <!--------- Panel body. ------->
				<?php
				$tablename = 'mjschool_marks';
				?>
				<div class="mjschool-panel-body"> <!--------- Panel body. ------->
					<form method="post" id="select_data">
						<div class="header">
							<h3 class="mjschool-first-header"><?php esc_html_e( 'Migration Information', 'mjschool' ); ?></h3>
						</div>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-md-6 input">
									<label class="ml-1 mjschool-custom-top-label top" for="current_class"><?php esc_html_e( 'Select Current Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									<select name="current_class" id="current_class" class="mjschool-line-height-30px form-control validate[required] text-input">
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
									<select name="next_class" id="next_class" class="mjschool-line-height-30px form-control validate[required] text-input">
										<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
										<?php
										$tablename = 'mjschool_class';
										$classdata_new = mjschool_get_all_data($tablename);
										foreach ( $classdata_new as $classdata ) {
											?>
											<option value="<?php echo esc_attr( $classdata->class_id ); ?>"><?php echo esc_html( $classdata->class_name ); ?></option>
											<?php
										}
										?>
									</select>
								</div>
								<div class="col-md-6 input">
									<label class="ml-1 mjschool-custom-top-label top" for="exam_id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field"></span></label>
									<?php
									$tablename      = 'mjschool_exam';
									$retrieve_class_data = mjschool_get_all_data( $tablename );
									?>
									<select name="exam_id" id="exam_id" class="mjschool-line-height-30px form-control  text-input">
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
								<?php wp_nonce_field( 'save_migration_admin_nonce' ); ?>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin mjschool-passing-mark-display-none" id="mjschool-migration-passing-mark">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="mjschool-passing-marks" type="number" name="passing_marks" value="" class="form-control validate[required,min[0],maxSize[5]]">
											<label  for="mjschool-passing-marks"><?php esc_html_e( 'Passing Marks', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="form-group col-md-6 mjschool-button-possition-padding">
									<input type="submit" value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" name="migration" class="btn btn-info mjschool-save-btn" />
								</div>
							</div>
						</div>
					</form>
				</div><!--------- Panel body. ------->
				<div class="clearfix"> </div>
			</div><!--------- Panel body ------->
		</div><!--------- Panel white. ------->
	</div>
</div><!--------- Page inner. ------->