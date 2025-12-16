<?php
/**
 * Admin Exam Time Table Management Form.
 *
 * This file handles the administrative interface for creating, viewing, and updating 
 * exam timetables within the Mjschool plugin. It allows administrators to assign exam 
 * dates and times to specific subjects for each class and section, while maintaining 
 * proper validation and data integrity.
 *
 * Key Features:
 * - Select an exam and dynamically load related subjects by class and section.
 * - Set individual subject exam dates and start/end times.
 * - Supports both school and university structures.
 * - Enforces date range limits based on exam start and end dates.
 * - Includes client-side validation and timepicker/datepicker integration.
 * - Automatically handles section-specific and subject-based filtering.
 * - Displays responsive, accessible, and translation-ready HTML tables.
 * - Ensures secure form submissions using WordPress sanitization and validation methods.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/exam_time_table
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

$school_type = get_option( 'mjschool_custom_class' );
if ($active_tab === 'exam_time_table' ) {

    // Check nonce for exam time table tab.
    if ( isset( $_GET['tab'] ) ) {
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_exam_module_tab' ) ) {
           wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
        }
    }

	?>
    <div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res">
        <!----- Panel body. ------>
        <!----------- Exam Time table Form. ---------->
        <form name="exam_form" action="" method="post" class="mb-3 mjschool-form-horizontal" enctype="multipart/form-data" id="exam_form">
            <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_exam_time_table_nonce' ) ); ?>">
            <div class="form-body mjschool-user-form mjschool-padding-top-25px-res">
                <div class="row">
                    <div class="col-md-8 input mjschool-exam-time-table-error-msg">
                        <label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="required">*</span></label>
                        <?php
                        $tablename           = 'mjschool_exam';
                        $retrieve_class_data = mjschool_get_all_data($tablename);
                        $exam_id             = '';
                        if ( isset( $_REQUEST['exam_id'] ) ) {
                            $exam_id = sanitize_text_field( wp_unslash($_REQUEST['exam_id']) );
                        }
                        ?>
                        <select id="mjschool-exam-id" name="exam_id" class="form-control validate[required] mjschool-width-100px">
                            <option value=""><?php esc_html_e( 'Select Exam Name', 'mjschool' ); ?></option>
                            <?php
                            foreach ($retrieve_class_data as $retrieved_data) {
                                $cid      = $retrieved_data->class_id;
                                $clasname = mjschool_get_class_name($cid);
                                if ($retrieved_data->section_id != 0) {
                                    $section_name = mjschool_get_section_name($retrieved_data->section_id);
                                } else {
                                    $section_name = esc_html__( 'No Section', 'mjschool' );
                                }
                            	?>
                                <option value="<?php echo esc_attr($retrieved_data->exam_id); ?>" <?php selected($retrieved_data->exam_id, $exam_id); ?>>
                                    <?php echo esc_html( $retrieved_data->exam_name) . '( ' . esc_html( mjschool_get_class_section_name_wise( $cid, $retrieved_data->section_id ) ) . ' )'; ?>
                                </option>
                            	<?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-3 col-xs-12">
                        <input type="submit" id="save_exam_time_table" value="<?php esc_attr_e( 'Manage Exam Time', 'mjschool' ); ?>" name="save_exam_time_table" class="btn btn-success mjschool-save-btn" />
                    </div>
                </div>
            </div>
        </form>
        <!----------- Exam Time table Form. ---------->
        <?php
        // save exam time table.
        if ( isset( $_POST['save_exam_time_table'] ) ) {
            if (! isset($_POST['security']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_exam_time_table_nonce')) {
                wp_die(esc_html__('Security check failed.', 'mjschool'));
            }

            $exam_data      = mjschool_get_exam_by_id(intval( wp_unslash($_POST['exam_id']) ) );
            $mjschool_obj = new MJSchool_Management();
            if ($exam_data->section_id != 0) {
                $subject_data = $mjschool_obj->mjschool_subject_list_with_calss_and_section($exam_data->class_id, $exam_data->section_id);
            } else // --------- section empty. -----------//
            {  
                $subject_data = $mjschool_obj->mjschool_subject_list($exam_data->class_id);
                if ( $school_type === 'university' )
                {
                    // Step 2: Decode the exam subject_data JSON field.
                    $exam_subjects = json_decode($exam_data->subject_data);
                    //Get subject_ids from exam data.
                    $exam_subject_ids = array_column($exam_subjects, 'subject_id' );
                    // Filter only matching subjects.
                    $subject_data = array_filter($subject_data, function($subject) use ($exam_subject_ids) {
                        return in_array((int)$subject->subid, $exam_subject_ids);
                    });
                }
            }
            $start_date = $exam_data->exam_start_date;
            $end_date   = $exam_data->exam_end_date;
        	?>
            <input type="hidden" id="start" value="<?php echo esc_attr( mjschool_get_date_in_input_box($start_date ) ); ?>">
            <input type="hidden" id="end" value="<?php echo esc_attr( mjschool_get_date_in_input_box($end_date ) ); ?>">
            <div class="form-group">
                <!-------- Form Body. -------->
                <div class="col-md-12 mjschool-rtl-custom-padding-0px">
                    <div class="mjschool-exam-table-res">
                        <table class="table mjschool_examhall_border_1px_center" >
                            <thead>
                                <tr>
                                    <th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium" ><?php esc_html_e( 'Exam', 'mjschool' ); ?></th>
                                    <th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
                                    <th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Section', 'mjschool' ); ?></th>
                                    <th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Term', 'mjschool' ); ?></th>
                                    <th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table" ><?php esc_html_e( 'Start Date', 'mjschool' ); ?></th>
                                    <th class="mjschool-exam-hall-receipt-table-heading mjchool_receipt_table_head" ><?php esc_html_e( 'End Date', 'mjschool' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( $exam_data->exam_name); ?></td>
                                    <td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_class_name($exam_data->class_id ) ); ?></td>
                                    <td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php if ($exam_data->section_id != 0) { echo esc_html( mjschool_get_section_name($exam_data->section_id ) ); } else { esc_html_e( 'No Section', 'mjschool' ); } ?></td>
                                    <td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( get_the_title($exam_data->exam_term ) ); ?></td>
                                    <td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_date_in_input_box($start_date ) ); ?></td>
                                    <td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><?php echo esc_html( mjschool_get_date_in_input_box($end_date ) ); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-------- Form Body. -------->
            <?php
            if ( isset( $subject_data ) ) {
                $mjschool_obj_exam = new Mjschool_exam();
                foreach ($subject_data as $retrieved_data) {
                    $exam_time_table_data = $mjschool_obj_exam->mjschool_check_exam_time_table($exam_data->class_id, $exam_data->exam_id, $retrieved_data->subid);
                }
				if ( ! empty( $subject_data ) ) {
					?>
					<div class="col-md-12 mjschool-rtl-custom-padding-0px">
						<div class="mjschool-exam-table-res mjschool_margin_20px">
							<form id="exam_form2" name="exam_form2" method="post">
								<!-------- Exam Form. -------->
								<input type='hidden' name='subject_data' id="subject_data" value='<?php echo esc_attr( wp_json_encode($subject_data) ); ?>'>
								<input type="hidden" name="class_id" value="<?php echo esc_attr($exam_data->class_id); ?>">
								<input type="hidden" name="section_id" value="<?php echo esc_attr($exam_data->section_id); ?>">
								<input type="hidden" name="exam_id" value="<?php echo esc_attr($exam_data->exam_id); ?>">
								<div class="mjschool-exam-time-table-main-div">
									<table class="exam_timelist_admin mjschool-width-100px mjschool_examhall_border_1px_center">
										<thead>
											<tr>
												<th class="exam_hall_receipt_add_table_heading mjschool_examhall_heading_medium"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
												<th class="exam_hall_receipt_add_table_heading mjschool_library_table" ><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></th>
												<th class="exam_hall_receipt_add_table_heading mjschool_library_table" ><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></th>
												<th class="exam_hall_receipt_add_table_heading mjschool-min-width-115 mjschool_library_table" ><?php esc_html_e( 'Exam Start Time', 'mjschool' ); ?></th>
												<th class="exam_hall_receipt_add_table_heading mjschool-min-width-115 mjchool_receipt_table_head" ><?php esc_html_e( 'Exam End Time', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 1;
											foreach ($subject_data as $retrieved_data) {
												// ------- View Exam Time Table Data. ------------//
												$exam_time_table_data = $mjschool_obj_exam->mjschool_check_exam_time_table($exam_data->class_id, $exam_data->exam_id, $retrieved_data->subid);
												?>
												<tr class="mjschool-main-date-css mjschool_border_1px_white">
													<input type="hidden" name="subject_id" value="<?php echo esc_attr($retrieved_data->subid); ?>">
													<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><input type="hidden" name="subject_code_<?php echo esc_attr($retrieved_data->subid); ?>" value="<?php echo esc_attr($retrieved_data->subject_code); ?>"><?php echo esc_html( $retrieved_data->subject_code); ?></td>
													<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px" ><input type="hidden" name="subject_name_<?php echo esc_attr($retrieved_data->subid); ?>" value="<?php echo esc_attr($retrieved_data->sub_name); ?>"><?php echo esc_html( $retrieved_data->sub_name); ?></td>
													<td class="mjschool-exam-hall-receipt-table-value mjschool-exam-time-tbl-validation mjschool_border_right_1px" ><input id="exam_date_<?php echo esc_attr($retrieved_data->subid); ?>" class="datepicker form-control datepicker_icon validate[required] text-input exam_date mjschool-min-width-160 mjschool-date-border-css" placeholder="<?php esc_attr_e( 'Select Date', 'mjschool' ); ?>" type="text" name="exam_date_<?php echo esc_attr($retrieved_data->subid); ?>" value="<?php if ( ! empty( $exam_time_table_data->exam_date ) ) { echo esc_attr( mjschool_get_date_in_input_box($exam_time_table_data->exam_date ) ); } ?>" readonly></td>
													<?php
													if ( ! empty( $exam_time_table_data->start_time ) ) {
														// ------------ Start time convert. --------------//
														$stime       = explode( ':', $exam_time_table_data->start_time);
														$start_hour  = $stime[0];
														$start_min   = $stime[1];
														$shours      = str_pad($start_hour, 2, '0', STR_PAD_LEFT);
														$smin        = str_pad($start_min, 2, '0', STR_PAD_LEFT);
														$start_am_pm = $stime[2];
														$start_time  = $shours . ':' . $smin . ':' . $start_am_pm;
													}
													if ( ! empty( $exam_time_table_data->end_time ) ) {
														// -------------------- End time convert. -----------------//
														$etime     = explode( ':', $exam_time_table_data->end_time);
														$end_hour  = $etime[0];
														$end_min   = $etime[1];
														$ehours    = str_pad($end_hour, 2, '0', STR_PAD_LEFT);
														$emin      = str_pad($end_min, 2, '0', STR_PAD_LEFT);
														$end_am_pm = $etime[2];
														$end_time  = $ehours . ':' . $emin . ':' . $end_am_pm;
													}
													?>
													<td class="mjschool-exam-hall-receipt-table-value mjschool-exam-time-tbl-validation  mjschool_border_right_1px" >
														<input type="text" name="start_time_<?php echo esc_attr($retrieved_data->subid); ?>" class="start_time timepicker form-control validate[required] text-input mjschool-date-border-css start_time_<?php echo esc_attr($retrieved_data->subid); ?>" placeholder="<?php esc_attr_e( 'Start Time', 'mjschool' ); ?>" value="<?php if ( ! empty( $exam_time_table_data->start_time ) ) { echo esc_attr($start_time); } ?>" />
													</td>
													<td class="mjschool-exam-hall-receipt-table-value mjschool-exam-time-tbl-validation  mjschool_border_right_1px" >
														<input type="text" name="end_time_<?php echo esc_attr($retrieved_data->subid); ?>" class="end_time timepicker form-control validate[required] text-input mjschool-date-border-css end_time_<?php echo esc_attr($retrieved_data->subid); ?> " placeholder="<?php esc_attr_e( 'End Time', 'mjschool' ); ?>" value="<?php if ( ! empty( $exam_time_table_data->end_time ) ) { echo esc_attr($end_time); } ?>" />
													</td>
												</tr>
												<?php
												++$i;
											}
											?>
										</tbody>
									</table>
								</div>
								<?php
								if ( ! empty( $subject_data ) ) {
								    ?>
									<div class="col-md-3 mjschool-margin-top-20px mjschool-padding-top-25px-res mjschool-rtl-custom-padding-0px mjschool-rtl-margin-0px">
										<input type="submit" id="save_exam_time" value="<?php esc_attr_e( 'Save Time Table', 'mjschool' ); ?>" name="save_exam_table" class="btn width-auto btn-success mjschool-save-btn" />
									</div>
								    <?php
								}
								?>
							</form>
							<!-------- Exam Form. -------->
						</div>
					</div>
					<?php
                } else {
                	?>
                    <div id="mjschool-message" class="mjschool-message_class mjschool-rtl-message-display-inline-block alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible mjschool_margin_20px">
                        <p><?php esc_html_e( 'No Any Subject', 'mjschool' ); ?></p>
                        <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
                    </div>
            		<?php
                }
            }
        }
        ?>
    </div>
    <!----- Panel body.	------>
	<?php
}
?>