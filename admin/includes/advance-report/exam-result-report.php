<?php
/**
 * The admin interface for displaying the advanced exam result report.
 *
 * This file renders the Exam Result Advanced Report in the admin area.
 * It provides a dynamic, DataTables-based view that allows administrators
 * to analyze student performance by class, section, and exam.
 * The report highlights failed subjects (below 50%) and supports
 * advanced search, filtering, and export options (CSV and Print).
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/advance-report
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for advance examination report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_advance_student_infomation_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

$mjschool_obj_marks = new Mjschool_Marks_Manage();
$class_id           = isset( $_REQUEST['class_id'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_id'])) : '';
$section_id         = isset( $_REQUEST['class_section'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_section'])) : '';
$exam_id            = isset( $_REQUEST['exam_id'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) : '';
$school_type = get_option( 'mjschool_custom_class' );
?>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<form method="post" id="fee_payment_report">
		<div class="form-body mjschool-user-form">
			<div class="row">
				<!-- Class Selection. -->
				<?php if ( $school_type === 'university' ) {?>
					<div class="col-md-6 input">
				<?php }else{?>
					<div class="col-md-3 input">
				<?php }?>
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control class_id_exam validate[required]">
						<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
						<?php foreach ( mjschool_get_all_class() as $classdata ) : ?>
							<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_attr( $classdata['class_name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<!-- Section Selection. -->
				 <?php if ( $school_type === 'school' ) {?>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top"  for="class_section">
							<?php esc_html_e( 'Select Section', 'mjschool' ); ?>
						</label>
						<select name="class_section" class="mjschool-line-height-30px mjschool-section-id-exam form-control" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( ! empty( $class_id ) ) :
								foreach ( mjschool_get_class_sections( $class_id ) as $sectiondata ) :
									?>
									<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $section_id, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
									<?php
								endforeach;
							endif;
							?>
						</select>
					</div>
				<?php }?>
				<!-- Exam Selection. -->
				<div class="col-md-3 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-id"><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select id="mjschool-exam-id" name="exam_id" class="mjschool-line-height-30px form-control exam_list validate[required]">
						<option value=""><?php esc_html_e( 'Select Exam Name', 'mjschool' ); ?></option>
						<?php foreach ( mjschool_get_all_data( 'exam' ) as $exam ) : ?>
							<option value="<?php echo esc_attr( $exam->exam_id ); ?>" <?php selected( $exam->exam_id, $exam_id ); ?>><?php echo esc_attr( $exam->exam_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php wp_nonce_field( 'mjschool-exam-result-report-nonce' ); ?>
				<div class="col-md-3">
					<input type="submit" name="report_5" value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form>
</div>
<?php
if (isset( $_POST['report_5'])) 
{
	if (! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'mjschool-exam-result-report-nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$subject_list    = $mjschool_obj_marks->mjschool_student_subject( intval( $class_id ), $section_id );
	$subject_name_list = array_map(
		function ( $s ) {
			return $s->sub_name;
		},
		$subject_list
	);
     
    $exlude_id = mjschool_approve_student_list();
    $args = array(
        'meta_query' => array(array( 'key' => 'class_name', 'value' => intval($class_id ) ) ),
        'role' => 'student',
        'exclude' => $exlude_id
    );
    
	if ( ! empty( $section_id ) ) {
		$args['meta_query'][] = array(
			'key'   => 'class_section',
			'value' => $section_id,
		);
	}
	$student = get_users( $args );
	if ( ! empty( $student ) ) :
		?>
		<script type="text/javascript">
			(function(jQuery) {
				"use strict";
				var subjects = <?php echo json_encode( $subject_name_list ); ?>;
				function mjschool_build_subject_percentage_and_result_filter(subject, result = 'F', percentageCondition = '<', percentageValue = 50) {
					return {
						logic: 'AND',
						criteria: [
							{
								data: subject + ' %',
								condition: percentageCondition,
								value: [String(percentageValue)]
							},
							{
								data: subject + ' R',
								condition: '=',
								value: [String(result)]
							}
						]
					};
				}
				const failedSubjectFilters = subjects.map(subject => mjschool_build_subject_percentage_and_result_filter(subject) );
				jQuery(document).ready(function() {
					var table = jQuery( '#advance-report-custom' ).DataTable({
						responsive: false,
						"order": [[1, "Desc"]],
						"dom": 'Qlfrtip',
						searchBuilder: {
							preDefined: {
								logic: 'AND',
								criteria: [
									...failedSubjectFilters,
									{
										condition: '=',
										data: 'Result',
										value: ['Fail'],
										origData: 'Result'
									}
								]
							}
						},
						buttons: [
							{
								extend: 'csv',
								text: '<?php esc_html_e( 'csv', 'mjschool' ); ?>',
								title: '<?php esc_html_e( 'Exam Result Report', 'mjschool' ); ?>'
							},
							{
								extend: 'print',
								text: '<?php esc_html_e( 'Print', 'mjschool' ); ?>',
								title: '<?php esc_html_e( 'Exam Result Report', 'mjschool' ); ?>'
							}
						],
						"aoColumns": [
							{ "bSortable": true },
							{ "bSortable": true },
							<?php
							if ( ! empty( $subject_list ) ) {
								foreach ( $subject_list as $sub_id ) {
									?>
									{ "bSortable": true },
									{ "bSortable": true },
									{ "bSortable": true },
									<?php
								}
							}
							?>
							{ "bSortable": true },
							{ "bSortable": true }
						],
						language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
					});
					jQuery( '.dataTables_filter input' ).attr( "placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>");
					jQuery( '.btn-place' ).html(table.buttons().container( ) );
				});
			})(jQuery);
		</script>
		<div class="clearfix mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
			<div class="admission-report my-3">
				<?php foreach ( $subject_name_list as $subject ) { ?>
					<div class="badge-container d-inline-flex flex-wrap align-items-center mb-2">
						<span class="report-label">
						<?php
						// translators: %s is the subject name.
						echo esc_html( sprintf( esc_html__( '%s Subject Less Than', 'mjschool' ), $subject ) );
						?>
						</span>
						<span class="status-text"><?php esc_html_e( '50% Mark &', 'mjschool' ); ?></span>
						<span class="report-label"><?php esc_html_e( 'Result', 'mjschool' ); ?></span>
						<span class="status-text"><?php esc_html_e( 'Fail', 'mjschool' ); ?></span>
					</div>
				<?php } ?>
				<div class="badge-container d-inline-flex flex-wrap align-items-center mb-2">
					<span class="report-label"><?php esc_html_e( 'Result Is', 'mjschool' ); ?></span>
					<span class="status-text"><?php esc_html_e( 'Fail', 'mjschool' ); ?></span>
				</div>
			</div>
			<div class="table-responsive">
				<table id="advance-report-custom" class="display responsive nowrap" cellspacing="0" width="100%">
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
							<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
							<?php
							foreach ( $subject_list as $sub ) :
								$short_name = $sub->sub_name;
								?>
								<th><?php echo esc_html( $short_name ); ?></th>
								<th><?php echo esc_html( $short_name ); ?> %</th>
								<th><?php echo esc_html( $short_name ); ?><?php esc_html_e( 'R', 'mjschool' ); ?></th>
							<?php endforeach; ?>
							<th><?php esc_html_e( 'Total', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $student as $mjschool_user ) :
							$total       = 0;
							$pass_status = true;
							?>
							<tr>
								<td><?php echo esc_html( $mjschool_user->roll_id ); ?></td>
								<td><?php echo esc_html( mjschool_get_user_name_by_id( $mjschool_user->ID ) ); ?></td>
								<?php
								foreach ( $subject_list as $sub ) :
									$mark_detail = $mjschool_obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $sub->subid, $mjschool_user->ID );
									$marks       = 0;
									$max_marks   = $mjschool_obj_marks->mjschool_get_max_marks( $exam_id ); // Same max for all subjects.
									$pass_mark   = $mjschool_obj_marks->mjschool_get_pass_marks( $exam_id );
									if ( $mark_detail ) {
										$marks = ( $mark_detail->contributions === 'yes' ) ? array_sum( json_decode( $mark_detail->class_marks, true ) ) : (int) $mark_detail->marks;
									}
									$percentage   = ( $max_marks > 0 ) ? round( ( $marks / $max_marks ) * 100, 2 ) : 0;
									$subject_pass = ( $marks >= $pass_mark );
									$result_text  = $subject_pass ? esc_attr__( 'P', 'mjschool' ) : esc_attr__( 'F', 'mjschool' );
									$result_color = $subject_pass ? 'green' : 'red';
									if ( ! $subject_pass ) {
										$pass_status = false;
									}
									$total += $marks;
									?>
									<td><?php echo esc_html( $marks ); ?></td>
									<td><?php echo esc_html( $percentage ); ?>%</td>
									<td><span style="color:<?php echo esc_attr( $result_color ); ?>; font-weight:bold;"><?php echo esc_attr( $result_text ); ?></span> </td>
								<?php endforeach; ?>
								<td><?php echo esc_attr( $total ); ?></td>
								<?php
								$final_result_text  = $pass_status ? esc_attr__( 'Pass', 'mjschool' ) : esc_attr__( 'Fail', 'mjschool' );
								$final_result_color = $pass_status ? 'green' : 'red';
								?>
								<td><span style="color:<?php echo esc_attr( $final_result_color ); ?>; font-weight:bold;"><?php echo esc_html( $final_result_text ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	endif;
}
?>