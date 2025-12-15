<?php 

/**
 * Student Failed Report – Form, Data Processing & Chart Rendering.
 *
 * Displays a filter form for class, section, and exam selection, then generates
 * a subject-wise failed-student report. Supports both school and university
 * modes, retrieves exam and marks data, processes failure counts, and renders
 * a Google Column Chart based on the results.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for student failed report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_student_infomation_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

$school_type = get_option( 'mjschool_custom_class' );
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped 
?>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<form method="post" id="failed_report">
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<?php
					$class_id = '';
					if ( isset( $_REQUEST['class_id'] ) ) {
						$class_id = $_REQUEST['class_id'];
					}
					?>
					<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required] class_id_exam">
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
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
						<?php
						$class_section = '';
						if ( isset( $_REQUEST['class_section'] ) ) {
							$class_section = $_REQUEST['class_section'];
						}
						?>
						<select name="class_section" class="mjschool-line-height-30px mjschool-section-id-exam form-control" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( isset( $_REQUEST['class_section'] ) ) {
								echo esc_html( $class_section = $_REQUEST['class_section'] );
								foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
									?>
									<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				<?php } ?>
				<div class="col-md-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-id">
						<?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span>
					</label>
					<?php
					global $wpdb;
					$exam_id  = '';
					if ( isset( $_REQUEST['exam_id'] ) ) {
						$exam_id = sanitize_text_field( $_REQUEST['exam_id'] );
					}
					$class_id = '';
					if ( isset( $_REQUEST['class_id'] ) ) {
						$class_id = sanitize_text_field( $_REQUEST['class_id'] );
					}
					// Fetch only exams of that class.
					$exams = array();
					if ( ! empty( $class_id ) ) {
						// phpcs:disable
						$exams = $wpdb->get_results(
							$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mjschool_exam WHERE class_id = %d", $class_id )
						);
						// phpcs:enable
					}
					?>
					<select id="mjschool-exam-id" name="exam_id" class="mjschool-line-height-30px form-control exam_list validate[required]">
						<option value=""><?php esc_html_e( 'Select Exam Name', 'mjschool' ); ?></option>
						<?php
						if ( ! empty( $exams ) ) {
							foreach ( $exams as $exam ) {
								?>
								<option value="<?php echo esc_attr( $exam->exam_id ); ?>" <?php selected( $exam->exam_id, $exam_id ); ?>>
									<?php echo esc_html( $exam->exam_name ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6">
					<input type="submit" name="report_1" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form>
</div>
<!-- Panel body div. -->
<div class="clearfix"> </div>
<div class="clearfix"> </div>
<?php
global $wpdb;
$exam_obj    = new Mjschool_exam();
$chart_array = array( array( 'Class', 'No. of Student Fail' ) );
$array_val   = array();
if ( isset( $_REQUEST['report_1'] ) ) {
	$exam_id    = isset( $_REQUEST['exam_id'] ) ? sanitize_text_field( $_REQUEST['exam_id'] ) : '';
	$class_id   = isset( $_REQUEST['class_id'] ) ? sanitize_text_field( $_REQUEST['class_id'] ) : '';
	$section_id = isset( $_REQUEST['class_section'] ) ? sanitize_text_field( $_REQUEST['class_section'] ) : '';
	$exam_data  = $exam_obj->mjschool_exam_data( $exam_id );
	$pass_marks = isset( $exam_data->passing_mark ) ? (int) $exam_data->passing_mark : 0;
	// Fetch report data.

	if ( $school_type === 'university' )
	{
		$exam_data = $exam_obj->mjschool_exam_data($exam_id);
		$exam_subject_data = json_decode($exam_data->subject_data);
		foreach($exam_subject_data as $final_data)
		{
			$pass_marks = $final_data->passing_marks;
			$report_1            = mjschool_get_failed_student_report_data( $exam_id, $class_id, $section_id, $pass_marks );
			$subject_fail_counts = array(); // Array to store failure count per subject.
			if ( ! empty( $report_1 ) ) {
				foreach ( $report_1 as $result ) {
					$obtain_marks = $result->marks;
					if ( $obtain_marks < (int) $final_data->passing_marks ) {
						$subject = mjschool_get_single_subject_name( $final_data->subject_id );
						// Count occurrences of each subject.
						if ( array_key_exists($subject, $subject_fail_counts ) ) {	
							$subject_fail_counts[ $subject ] = $subject_fail_counts[ $subject ] + 1;
						} else {
							$subject_fail_counts[ $subject ] = 0;
						}
					}
				}
			}
		}
	}
	else
	{
		$report_1            = mjschool_get_failed_student_report_data( $exam_id, $class_id, $section_id, $pass_marks );
		$subject_fail_counts = array(); // Array to store failure count per subject.
		if ( ! empty( $report_1 ) ) {
			foreach ( $report_1 as $result ) {
				$marks_total = ( $result->contributions === 'yes' ) ? array_sum( json_decode( $result->class_marks, true ) ?? array() ) : (int) $result->marks;
				if ( $marks_total < (int) $exam_data->passing_mark ) {
					$subject = mjschool_get_single_subject_name( $result->subject_id );
					// Count occurrences of each subject.
					if ( ! isset( $subject_fail_counts[ $subject ] ) ) {
						$subject_fail_counts[ $subject ] = 1;
					} else {
						++$subject_fail_counts[ $subject ];
					}
				}
			}
		}
	}
	// Populate arrays for the chart.
	if ( ! empty( $subject_fail_counts ) ) {
		foreach ( $subject_fail_counts as $subject => $count ) {
			$array_val[]   = array( $subject, $count );
			$chart_array[] = array( $subject, $count );
		}
	}
	if ( ! empty( $array_val ) ) {
		$options = array(
			'title'          => esc_attr__( 'Exam Failed Report', 'mjschool' ),
			'titleTextStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 16,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif',
			),
			'legend'         => array(
				'position'  => 'right',
				'textStyle' => array(
					'color'    => '#4e5e6a',
					'fontSize' => 13,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif',
				),
			),
			'hAxis'          => array(
				'title'          => esc_attr__( 'Subject', 'mjschool' ),
				'titleTextStyle' => array(
					'color'    => '#4e5e6a',
					'fontSize' => 16,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif',
				),
				'textStyle'      => array(
					'color'    => '#4e5e6a',
					'fontSize' => 13,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif',
				),
				'maxAlternation' => 2,
			),
			'vAxis'          => array(
				'title'          => esc_attr__( 'No. of Student', 'mjschool' ),
				'minValue'       => 0,
				'maxValue'       => 5,
				'format'         => '#',
				'titleTextStyle' => array(
					'color'    => '#4e5e6a',
					'fontSize' => 16,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif',
				),
				'textStyle'      => array(
					'color'    => '#4e5e6a',
					'fontSize' => 13,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif',
				),
			),
			'colors'         => array( '#5840bb' ),
		);
		// Load Google Chart.
		$chart = $GoogleCharts->load( 'column', 'chart_div' )->get( $chart_array, $options );
	}
}
if ( ! empty( $array_val ) ) {
	?>
	<div id="chart_div" class="w-100 h-100 mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res" data-chart='<?php echo wp_json_encode( $chart_array ); ?>' data-options='<?php echo wp_json_encode( $options ); ?>'></div>

	<?php
} else {
	 ?>
	<div class="mjschool-calendar-event-new">
		<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
	</div>
	<?php 
}
?>