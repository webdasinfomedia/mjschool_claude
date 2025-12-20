<?php

/**
 * Teacher Performance Report Template.
 *
 * Generates a performance graph for teachers based on the total number of
 * students who failed their subjects.  
 * Counts unique failing students per teacher, prepares dataset arrays, and
 * renders a Google Column Chart.
 *
 * Includes nonce validation, subject-to-teacher mapping, marks evaluation,
 * and passing criteria comparison.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for teacher performance report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_student_infomation_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$chart_array[] = array( esc_attr__( 'Teacher', 'mjschool' ), esc_attr__( 'No. of Students Failed', 'mjschool' ) );
	global $wpdb;
	$table_name_mark       = $wpdb->prefix . 'mjschool_marks';
	$teachers              = get_users( array( 'role' => 'teacher' ) );
	$teacher_student_fails = array(); // Stores unique failing students per teacher.
	$obj_subject          = new Mjschool_Subject();
	if ( ! empty( $teachers ) ) {
		foreach ( $teachers as $teacher ) {
			$subject_ids = $obj_subject->mjschool_get_subject_id_by_teacher( $teacher->ID );
			if ( ! empty( $subject_ids ) ) {
				$sub_str = implode( ',', $subject_ids );
				$results = $wpdb->get_results( "SELECT * FROM {$table_name_mark} WHERE subject_id IN ({$sub_str})", ARRAY_A );
				$failed_students = array();
				if ( ! empty( $results ) ) {
					foreach ( $results as $result ) {
						$marks_total = ( $result['contributions'] === 'yes' ) ? array_sum( json_decode( $result['class_marks'], true ) ?? array() ) : (int) $result['marks'];
						$exam_obj  = new Mjschool_exam();
						$exam_data = $exam_obj->mjschool_exam_data( $result['exam_id'] );
						if ( ( $exam_data ) && ( $marks_total < $exam_data->passing_mark ) ) {
							$failed_students[ $result['student_id'] ] = true; // ensure uniqueness
						}
					}
				}
				if ( ! empty( $failed_students ) ) {
					$teacher_name                           = mjschool_get_display_name( $teacher->ID );
					$teacher_student_fails[ $teacher_name ] = count( $failed_students );
				}
			}
		}
	}
	// Populate chart array.
	if ( ! empty( $teacher_student_fails ) ) {
		foreach ( $teacher_student_fails as $teacher_name => $count ) {
			$chart_array[] = array( $teacher_name, $count );
		}
	}
	$color   = get_option( 'mjschool_system_color_code' );
	$options = array(
		'title'          => esc_attr__( 'Teacher Performance Report', 'mjschool' ),
		'titleTextStyle' => array(
			'color'    => '#4e5e6a',
			'fontSize' => 16,
			'bold'     => false,
			'italic'   => false,
			'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
		),
		'legend' => array(
			'position'  => 'right',
			'textStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 13,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
		),
		'hAxis' => array(
			'title'          => esc_attr__( 'Teacher Name', 'mjschool' ),
			'titleTextStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 16,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'textStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 13,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'maxAlternation' => 2,
		),
		'vAxis'          => array(
			'title'          => esc_attr__( 'No. of Students', 'mjschool' ),
			'minValue'       => 0,
			'format'         => '#',
			'titleTextStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 16,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'textStyle'      => array(
				'color'    => '#4e5e6a',
				'fontSize' => 13,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
		),
		'colors' => array( $color ),
	);
	if ( ! empty( $teacher_student_fails ) ) {
		?>
		<div id="chart_div" class="w-100 h-100 mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res" data-chart='<?php echo wp_json_encode( $chart_array ); ?>' data-options='<?php echo wp_json_encode( $options ); ?>'></div>
		<?php
	} else {
		 ?>
		<div class="mjschool-calendar-event-new">
			<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
		</div>
		<?php 
	}
	?>
</div>