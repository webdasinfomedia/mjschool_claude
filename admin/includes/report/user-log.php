<?php

/**
 * User Log Report Page.
 *
 * Displays login activity for all user roles with date-range filtering,
 * datatable listing, CSV export, and print options. Supports multiple
 * time-period presets and custom date ranges. Includes nonce validation
 * for preventing unauthorized access.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for user log report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_user_log_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res"> <!------  Panel body.  -------->
	<div class="mjschool-panel-body clearfix">
		<form method="post" id="student_attendance">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<?php $selected_date_type = isset( $_POST['date_type'] ) ? $_POST['date_type'] : ''; ?>
					<div class="col-md-6 mb-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select class="mjschool-line-height-30px form-control date_type validate[required]" id="date_type" name="date_type" autocomplete="off">
							<option value=""><?php esc_html_e( 'Select', 'mjschool' ); ?></option>
							<option value="today" <?php selected( $selected_date_type, 'today' ); ?>><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
							<option value="this_week" <?php selected( $selected_date_type, 'this_week' ); ?>><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
							<option value="last_week" <?php selected( $selected_date_type, 'last_week' ); ?>><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
							<option value="this_month" <?php selected( $selected_date_type, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
							<option value="last_month" <?php selected( $selected_date_type, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
							<option value="last_3_month" <?php selected( $selected_date_type, 'last_3_month' ); ?>><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
							<option value="last_6_month" <?php selected( $selected_date_type, 'last_6_month' ); ?>><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
							<option value="last_12_month" <?php selected( $selected_date_type, 'last_12_month' ); ?>><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
							<option value="this_year" <?php selected( $selected_date_type, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
							<option value="last_year" <?php selected( $selected_date_type, 'last_year' ); ?>><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
							<option value="period" <?php selected( $selected_date_type, 'period' ); ?>><?php esc_html_e( 'Period', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-md-6 mb-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-role-type"><?php esc_html_e( 'Action', 'mjschool' ); ?></label>
						<select id="mjschool-role-type" class="mjschool-line-height-30px form-control" name="role_type" autocomplete="off">
							<option value="all"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
							<option value="student"><?php esc_html_e( 'Students', 'mjschool' ); ?></option>
							<option value="teacher"><?php esc_html_e( 'Teachers', 'mjschool' ); ?></option>
							<option value="parent"><?php esc_html_e( 'Parents', 'mjschool' ); ?></option>
							<option value="supportstaff"><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></option>
						</select>
					</div>
					<div id="date_type_div" class="col-md-6 <?php echo ( $selected_date_type === 'period' ) ? '' : 'date_type_div_none'; ?>">
						<?php
						if ( $selected_date_type === 'period' ) {
							?>
							<div class="row">
								<div class="col-md-6 mb-2">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text" id="report_sdate" class="form-control" name="start_date" value="<?php echo isset( $_POST['start_date'] ) ? esc_attr( $_POST['start_date'] ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
											<label for="report_sdate" class="active"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-6 mb-2">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text" id="report_edate" class="form-control" name="end_date" value="<?php echo isset( $_POST['end_date'] ) ? esc_attr( $_POST['end_date'] ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
											<label for="report_edate" class="active"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
							<script type="text/javascript">
								(function(jQuery) {
									"use strict";
									jQuery(document).ready(function() {
										jQuery( "#report_sdate").datepicker({
											dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
											changeYear: true,
											changeMonth: true,
											maxDate: 0,
											onSelect: function(selected) {
												var dt = new Date(selected);
												dt.setDate(dt.getDate( ) );
												jQuery( "#report_edate").datepicker( "option", "minDate", dt);
											}
										});
										jQuery( "#report_edate").datepicker({
											dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
											changeYear: true,
											changeMonth: true,
											maxDate: 0,
											onSelect: function(selected) {
												var dt = new Date(selected);
												dt.setDate(dt.getDate( ) );
												jQuery( "#report_sdate").datepicker( "option", "maxDate", dt);
											}
										});
									});
								})(jQuery);
							</script>
							<?php
						}
						?>
					</div>
					<div class="col-md-3 mb-2">
						<input type="submit" name="user_log_report" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	if ( isset( $_REQUEST['user_log_report'] ) ) {
		$date_type = sanitize_text_field( $_POST['date_type'] );
		$mjschool_role_type = sanitize_text_field( $_POST['role_type'] );
		if ( $date_type === 'period' ) {
			$start_date = sanitize_text_field( $_REQUEST['start_date'] );
			$end_date   = sanitize_text_field( $_REQUEST['end_date'] );
		} else {
			$result     = mjschool_all_date_type_value( $date_type );
			$response   = json_decode( $result );
			$start_date = $response[0];
			$end_date   = $response[1];
		}
	} else {
		$mjschool_role_type  = 'all';
		$start_date = date( 'Y-m-d' );
		$end_date   = date( 'Y-m-d' );
	}
	if ( $mjschool_role_type === 'all' || $mjschool_role_type === '' ) {
		global $wpdb;
		$table_user_log = $wpdb->prefix . 'mjschool_user_log';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_6 = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_user_log WHERE created_at BETWEEN %s AND %s", $start_date, $end_date )
		);
	} else {
		global $wpdb;
		$table_user_log = $wpdb->prefix . 'mjschool_user_log';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_6 = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_user_log WHERE role = %s AND created_at BETWEEN %s AND %s", $mjschool_role_type, $start_date, $end_date )
		);
	}
	if ( ! empty( $report_6 ) ) {
		?>
		<script type="text/javascript">
			(function(jQuery){
				"use strict";
				jQuery(document).ready(function(){
					var table = jQuery( '#tble_login_log' ).DataTable({
						"order": [[2, "Desc"]],
						"dom": 'lifrtp',
						buttons: [
							{
								extend: 'csv',
								text: '<?php esc_html_e( "csv", "mjschool" ); ?>',
								title: '<?php esc_html_e( "User Log Report", "mjschool" ); ?>'
							},
							{
								extend: 'print',
								text: '<?php esc_html_e( "Print", "mjschool" ); ?>',
								title: '<?php esc_html_e( "User Log Report", "mjschool" ); ?>'
							}
						],
						"aoColumns": [
							{"bSortable": false},
							{"bSortable": true},
							{"bSortable": true},
							{"bSortable": true},
							{"bSortable": true}
						],
						language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
					});
					// jQuery( '.btn-place' ).html(table.buttons().container( ) );
					jQuery('.dataTables_filter input')
						.attr("placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>")
						.attr("id", "datatable_search")
						.attr("name", "datatable_search");
			})(jQuery);
		</script>
		<div class="mjschool-panel-body mjschool-padding-top-15px-res"> <!------  Panel body. -------->
			<div class="btn-place"></div>
			<div class="table-responsive"> <!------  Table Responsive.  -------->
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="tble_login_log" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'User Login', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'User Role', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'IP Address', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Login Date', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $report_6 as $result ) {
								$user_object = get_user_by( 'login', $result->user_login );
								$class       = get_user_meta( $user_object->ID, 'class_name', true );
								$section     = get_user_meta( $user_object->ID, 'class_section', true );
								?>
								<tr>
									<td class="patient">
										<?php
										if ( ! empty( $result->user_login ) ) {
											echo esc_html( $result->user_login );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										} ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'User Login', 'mjschool' ); ?>"></i>
									</td>
									<td class="patient_name mjschool-text-transform-capitalize">
										<?php
										if ( ! empty( $result->role ) ) {
											echo esc_html( $result->role );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										} ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'User Role', 'mjschool' ); ?>"></i>
									</td>
									<td class="status">
										<?php
										if ( $result->role === 'student' ) {
											echo esc_html( mjschool_get_class_section_name_wise( $class, $section ) ); 
										} ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
									</td>
									<td class="income_amount"><?php echo esc_html( getHostByName( getHostName() ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'IP Address', 'mjschool' ); ?>"></i></td>
									<td class="status">
										<?php
										if ( ! empty( $result->date_time ) ) {
											echo esc_html( mjschool_get_date_in_input_box( $result->date_time ) );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										} ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Login Time', 'mjschool' ); ?>"></i>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				</form>
			</div> <!------  Table responsive.  -------->
		</div> <!------  Panel body.  -------->
		<?php
	} else {
		 
        ?>
        <div class="mjschool-calendar-event-new">
            <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
        </div>
        <?php  
	}
	?>
</div> <!------  Panel body.  -------->