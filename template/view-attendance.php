<?php
/**
 * View Attendance Page
 *
 * This file is used by students, parents, and teachers to view attendance
 * records, either for a student's overall attendance or subject-specific
 * attendance over a selected date range.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$obj_mark   = new Mjschool_Marks_Manage();
$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : 'stud_attendance';
$mjschool_role       = 'student';
if ( isset( $_REQUEST['student_id'] ) ) {
	$student_id = sanitize_text_field(wp_unslash($_REQUEST['student_id']));
}
?>
<div class="mjschool-panel-body mjschool-panel-white">
	<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap" role="tablist">
		<li class="<?php if ( $active_tab === 'stud_attendance' ) { ?> active<?php } ?>">
			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=view-attendance&tab=stud_attendance&student_id=' . $student_id ); ?>" class="nav-tab2"> <i class="fas fa-align-justify"></i> <?php esc_html_e( 'Attendance', 'mjschool' ); ?></a>
		</li>
		<li class="<?php if ( $active_tab === 'sub_attendance' ) { ?> active<?php } ?>">
			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=view-attendance&tab=sub_attendance&student_id=' . $student_id ); ?>" class="nav-tab2 <?php echo esc_attr( $active_tab  ) === 'sub_attendance' ? 'active' : ''; ?>"> <i class="fas fa-align-justify"></i> <?php esc_html_e( 'Subject Wise Attendance', 'mjschool' ); ?></a>
		</li>     
	</ul>
	<div class="tab-content">
		<?php
		if ( $active_tab === 'stud_attendance' ) {
			$student_data = get_userdata( sanitize_text_field(wp_unslash($_REQUEST['student_id'] )));
			?>
			<div class="mjschool-panel-body">
				<form name="wcwm_report" action="" method="post">
					<input type="hidden" name="attendance" value=1> 
					<input type="hidden" name="user_id" value=<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) ); ?>>  
					<div class="row">
						<div class="col-md-3 col-sm-4 col-xs-12">	
							<?php
							$umetadata = mjschool_get_user_image( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) );
							
							if(empty($umetadata ) )
							{
								echo '<img class="img-circle img-responsive member-profile w-150-px h-150-px" src='.esc_url( get_option( 'mjschool_student_thumb_new' ) ).'/>';
							}
							else
								echo '<img class="img-circle img-responsive member-profile w-150-px h-150-px" src='.esc_url($umetadata).' />';
							
							?>
						</div>
						<div class="col-md-9 col-sm-8 col-xs-12">
							<div class="row">
								<h2><?php echo esc_html( $student_data->display_name ); ?></h2>
							</div>
							<div class="row">
								<div class="col-md-4 col-sm-3 col-xs-12">
									<i class="fas fa-envelope"></i>&nbsp;
									<span class="email-span"><?php echo esc_html( $student_data->user_email ); ?></span>
								</div>
								<div class="col-md-3 col-sm-3 col-xs-12">
									<i class="fas fa-phone"></i>&nbsp;
									<span><?php echo esc_html( $student_data->phone ); ?></span>
								</div>
								<div class="col-md-5 col-sm-3 col-xs-12 no-padding">
									<i class="fas fa-list-alt"></i>&nbsp;
									<span><?php echo esc_html( $student_data->roll_id ); ?></span>
								</div>
							</div>					
						</div>
					</div>
					<div class="form-group col-md-3">
						<label for="exam_id"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
						<input type="text"  class="form-control sdate" name="sdate" value="<?php if ( isset( $_REQUEST['sdate'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['sdate'])) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>                               
					</div>
					<div class="form-group col-md-3">
						<label for="exam_id"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
						<input type="text" class="form-control edate" name="edate" value="<?php if ( isset( $_REQUEST['edate'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['edate'])) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>                               
					</div>
					<div class="form-group col-md-3 button-possition">
						<label for="subject_id">&nbsp;</label>
						<input type="submit" name="view_attendance" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info"/>
					</div>	
				</form>
				<div class="clearfix"></div>
				<?php
				if ( isset( $_REQUEST['view_attendance'] ) ) {
					$start_date = sanitize_text_field(wp_unslash($_REQUEST['sdate']));
					$end_date   = sanitize_text_field(wp_unslash($_REQUEST['edate']));
					$user_id    = sanitize_text_field(wp_unslash($_REQUEST['user_id']));
					$period       = new DatePeriod(
						new DateTime( $start_date ),
						new DateInterval( 'P1D' ),
						new DateTime( $end_date )
					);
					$attendance   = mjschool_view_student_attendance( $start_date, $end_date, $user_id );
					$curremt_date = $start_date;
					?>
					<div class="mjschool-panel-body">
						<div class="table-responsive">
							<table id="attendance_list" class="display" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
									</tr>
								</thead> 
								<tfoot>
									<tr>
										<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
									</tr>
								</tfoot> 
								<tbody>
									<?php
									while ( $end_date >= $curremt_date ) {
											echo '<tr>';
											echo '<td>';
										echo esc_html( mjschool_get_display_name( $user_id ) );
											echo '</td>';
											echo '<td>';
										echo esc_html( mjschool_get_class_name_by_id( get_user_meta( $user_id, 'class_name', true ) ) );
											echo '</td>';
											echo '<td>';
										echo esc_html( mjschool_get_date_in_input_box( $curremt_date ) );
											echo '</td>';
											$attendance_status = mjschool_get_attendence( $user_id, $curremt_date );
											echo '<td>';
											echo esc_html( date( 'D', strtotime( $curremt_date ) ) );
											echo '</td>';
										if ( ! empty( $attendance_status ) ) {
											echo '<td>';
											echo esc_html( mjschool_get_attendence( $user_id, $curremt_date ) );
											echo '</td>';
										} else {
											echo '<td>';
											echo esc_attr__( 'Absent', 'mjschool' );
											echo '</td>';
										}
										echo '<td>';
										echo esc_html( mjschool_get_attendence_comment( $user_id, $curremt_date ) );
										echo '</td>';
										echo '</tr>';
										$curremt_date = strtotime( '+1 day', strtotime( $curremt_date ) );
										$curremt_date = date( 'Y-m-d', $curremt_date );
									}
									?>
								</tbody>        
							</table>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php
		}
		if ( $active_tab === 'sub_attendance' ) {
			$student_data = get_userdata( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) );
			?>
			<div class="mjschool-panel-body">
				<form name="wcwm_report" action="" id="subject_attendance" method="post">
					<input type="hidden" name="attendance" value=1> 
					<input type="hidden" name="user_id" value=<?php echo esc_attr( $student_id ); ?>> 
					<div class="row">
						<div class="col-md-3 col-sm-4 col-xs-12">	
							<?php
							$umetadata = mjschool_get_user_image( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) );
							
							if(empty($umetadata ) )
							{
								echo '<img class="img-circle img-responsive member-profile w-150-px h-150-px" src='.esc_url( get_option( 'mjschool_student_thumb_new' ) ).'/>';
							}
							else
								echo '<img class="img-circle img-responsive member-profile w-150-px h-150-px" src='.esc_url($umetadata).' />';
							
							?>
						</div>						
						<div class="col-md-9 col-sm-8 col-xs-12">
							<div class="row">
								<h2><?php echo esc_html( $student_data->display_name ); ?></h2>
							</div>
							<div class="row">
								<div class="col-md-4 col-sm-3 col-xs-12">
									<i class="fas fa-envelope"></i>&nbsp;
									<span class="email-span"><?php echo esc_html( $student_data->user_email ); ?></span>
								</div>
								<div class="col-md-3 col-sm-3 col-xs-12">
									<i class="fas fa-phone"></i>&nbsp;
									<span><?php echo esc_html( $student_data->phone ); ?></span>
								</div>
								<div class="col-md-5 col-sm-3 col-xs-12 no-padding">
									<i class="fas fa-list-alt"></i>&nbsp;
									<span><?php echo esc_html( $student_data->roll_id ); ?></span>
								</div>
							</div>					
						</div>
					</div>
					<div class="form-group col-md-3">
						<label for="exam_id"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>									
						<input type="text"  class="form-control sdate" name="sdate" value="<?php if ( isset( $_REQUEST['sdate'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_REQUEST['sdate'])) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>                           
					</div>
					<div class="form-group col-md-3">
						<label for="exam_id"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
						<input type="text"   class="form-control edate" name="edate" value="<?php if ( isset( $_REQUEST['edate'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_REQUEST['edate'])) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>                               
					</div>					
					<div class="form-group col-md-3">
						<label for="class_id"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>			
						<?php $class_id = get_user_meta( $student_id, 'class_name', true ); ?>
						<select name="sub_id"  class="form-control validate[required]">
							<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
							<?php
							$sub_id = 0;
							if ( isset( $_POST['sub_id'] ) ) {
								$sub_id = sanitize_text_field(wp_unslash($_POST['sub_id']));
							}
							$allsubjects = mjschool_get_subject_by_class_id( $class_id );
							foreach ( $allsubjects as $subjectdata ) {
								?>
								<option value="<?php echo esc_attr( $subjectdata->subid ); ?>" <?php selected( $subjectdata->subid, $sub_id ); ?>><?php echo esc_html( $subjectdata->sub_name ); ?></option>
							<?php } ?>
						</select>						
					</div>
					<div class="form-group col-md-3 button-possition">
						<label for="subject_id">&nbsp;</label>
						<input type="submit" name="view_attendance" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info"/>
					</div>	
				</form>
				<div class="clearfix"></div>
				<?php
				if ( isset( $_REQUEST['view_attendance'] ) ) {
					$start_date = sanitize_text_field(wp_unslash($_REQUEST['sdate']));
					$end_date   = sanitize_text_field(wp_unslash($_REQUEST['edate']));
					$user_id    = sanitize_text_field(wp_unslash($_REQUEST['user_id']));
					$sub_id     = sanitize_text_field(wp_unslash($_REQUEST['sub_id']));
					$attendance = mjschool_view_student_attendance( $start_date, $end_date, $user_id );
					$curremt_date = $start_date;
					?>
					<div class="table-responsive">
						<table class="table col-md-12">
							<tr>
							<th width="200px"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
							</tr>
							<?php
							while ( $end_date >= $curremt_date ) {
								echo '<tr>';
								echo '<td>';
								echo esc_html( mjschool_get_date_in_input_box( $curremt_date ) );
								echo '</td>';
								$sub_attendance_status = mjschool_get_sub_attendence( $user_id, $curremt_date, $sub_id );
								echo '<td>';
								echo esc_html( date( 'D', strtotime( $curremt_date ) ) );
								echo '</td>';
								if ( ! empty( $sub_attendance_status ) ) {
									echo '<td>';
									echo esc_html( mjschool_get_sub_attendence( $user_id, $curremt_date, $sub_id ) );
									echo '</td>';
								} else {
									echo '<td>';
									echo esc_attr__( 'Absent', 'mjschool' );
									echo '</td>';
								}
								echo '<td>';
								echo esc_html( mjschool_get_sub_attendence_comment( $user_id, $curremt_date, $sub_id ) );
								echo '</td>';
								echo '</tr>';
								$curremt_date = strtotime( '+1 day', strtotime( $curremt_date ) );
								$curremt_date = date( 'Y-m-d', $curremt_date );
							}
							?>
						</table>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</div>