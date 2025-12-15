<?php
/**
 * Admin Exam Hall Receipt Interface.
 *
 * Provides the administrative interface for generating and viewing exam hall 
 * allocation receipts within the MJSchool plugin. This form allows administrators 
 * to select an exam and dynamically fetch related hall assignment data.
 *
 * Key Features:
 * - Displays all available exams with class and section details.
 * - Allows selection of an exam to generate corresponding hall receipts.
 * - Uses AJAX for dynamic data loading based on exam selection.
 * - Includes client-side validation and secure form handling.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/hall
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// Check nonce for exam hall list tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_exam_hall_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res"><!-------- Panel Body. -------->
	<form name="exam_form" action="" class="hall_recipt" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="receipt_form">
		<div class="form-body"><!-------- Form Body. -------->
			<div class="row">
				<div class="col-md-9 input">
					<label class="ml-1 mjschool-custom-top-label top" for="exam_id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="required">*</span></label>
					<?php
					$tablename      = 'mjschool_exam';
					$retrieve_class_data = mjschool_get_all_data( $tablename );
					$exam_id        = '';
					if ( isset( $_REQUEST['exam_id'] ) ) {
						$exam_id = intval( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) );
					}
					?>
					<select name="exam_id" class="form-control validate[required] exam_hall_receipt" id="exam_id">
						<option value=""><?php esc_html_e( 'Select Exam Name', 'mjschool' ); ?></option>
						<?php
						foreach ( $retrieve_class_data as $retrieved_data ) {
							$cid      = $retrieved_data->class_id;
							$clasname = mjschool_get_class_name( $cid );
							if ( $retrieved_data->section_id != 0 ) {
								$section_name = mjschool_get_section_name( $retrieved_data->section_id );
							} else {
								$section_name = esc_attr__( 'No Section', 'mjschool' );
							}
							?>
							<option value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" <?php selected( $retrieved_data->exam_id, $exam_id ); ?>><?php echo esc_html( $retrieved_data->exam_name ) . '( ' . esc_html( mjschool_get_class_section_name_wise( $cid, $retrieved_data->section_id ) ) . ' )'; ?></option>
							<?php
						}
						?>
					</select>                  
				</div>
				<div class="form-group col-md-3">
					<input type="button" value="<?php esc_html_e( 'Search Exam', 'mjschool' ); ?>" name="search_exam" id="search_exam" class="btn btn-info search_exam mjschool-save-btn"/>
				</div>
			</div>
		</div><!-------- Form Body. -------->
	</form>
	<div class="col-md-12 col-sm-12 col-xs-12 mjschool-rtl-custom-padding-0px">
		<div class="mjschool-exam-hall-receipt-div"></div>
	</div>
</div> <!-------- Panel Body. -------->