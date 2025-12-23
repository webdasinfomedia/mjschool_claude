<?php

/**
 * Renders the Audit Trail Report page in the MJSchool plugin.
 *
 * This template displays an interactive audit log table with filters for 
 * date range, action type (insert, edit, delete), and predefined date shortcuts 
 * (today, this week, last month, etc.). It validates nonce security, processes 
 * form input, fetches audit log records from the database using safe prepared 
 * queries, and initializes DataTables with CSV/Print export options.
 *
 * The page also supports:
 * - Custom date range selection with jQuery datepickers.
 * - Bulk deletion of audit log entries.
 * - Dynamic table sorting, search, and pagination.
 * - Multi-language support for DataTables labels.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for audit trail report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_audit_trail_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
if ( isset( $_POST['date_type'] ) ) {
	$date_type_value = sanitize_text_field(wp_unslash($_POST['date_type']));
} else {
	$date_type_value = 'this_month';
}
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res"> <!------  penal body  -------->
	<div class="mjschool-panel-body clearfix">
		<form method="post" id="student_attendance">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<?php $selected_date_type = isset( $_POST['date_type'] ) ? $_POST['date_type'] : ''; ?>
					<div class="col-md-6 mb-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php mjschool_date_filter_dropdown( $date_type_value ); ?>
					</div>
					<div class="col-md-6 mb-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-action"><?php esc_html_e( 'Action', 'mjschool' ); ?></label>
						<select id="mjschool-action" class="mjschool-line-height-30px form-control date_action_filter" name="date_action" autocomplete="off">
							<option value="all"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
							<option value="edit"><?php esc_html_e( 'Edit Action', 'mjschool' ); ?></option>
							<option value="insert"><?php esc_html_e( 'Insert Action', 'mjschool' ); ?></option>
							<option value="delete"><?php esc_html_e( 'Delete Action', 'mjschool' ); ?></option>
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
							<?php
						}
						?>
					</div>
					<div class="col-md-3 mb-2">
						<input type="submit" name="audit_report" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	if ( isset( $_REQUEST['audit_report'] ) ) {
		$date_type   = sanitize_text_field( $_POST['date_type'] );
		$date_action = sanitize_text_field( $_POST['date_action'] );
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
		$date_action = 'all';
		$start_date  = date( 'Y-m-d' );
		$end_date    = date( 'Y-m-d' );
	}
	if ( $date_action === 'all' || $date_action === '' ) {
		global $wpdb;
		$table_audit_log = $wpdb->prefix . 'mjschool_audit_log';
		$start_date = date('Y-m-d 00:00:00', strtotime($start_date));
		$end_date   = date('Y-m-d 23:59:59', strtotime($end_date));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_6 = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_audit_log WHERE created_at BETWEEN %s AND %s", $start_date, $end_date )
		);
	} else {
		global $wpdb;
		$table_audit_log = $wpdb->prefix . 'mjschool_audit_log';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_6 = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_audit_log WHERE action = %s AND created_at BETWEEN %s AND %s", $date_action, $start_date, $end_date )
		);
	}
	if ( ! empty( $report_6 ) ) {
		$obj_feespayment = new Mjschool_Feespayment();
		if ( isset( $_REQUEST['delete_selected_audit_log'] ) ) {
			if ( ! empty( $_REQUEST['id'] ) ) {
				foreach ( $_REQUEST['id'] as $id ) {
					$result = mjschool_delete_audit_log( $id );
				}
			}
			if ( $result ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php esc_html_e( 'Record Deleted Successfully.', 'mjschool' ); ?></p>
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
				</div>
				<?php
			}
		}
		?>
		<div class="mjschool-panel-body mjschool-padding-top-15px-res"> <!------  Panel body.  -------->
			<div class="btn-place"></div>
			<div class="table-responsive"> <!------  Table Responsive.  -------->
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="tble_audit_log_" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th class="mjschool-custom-padding-0"><input type="checkbox" class=" multiple_select select_all" name="select_all"></th>
								<th><?php esc_html_e( 'Message', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'IP Address', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $report_6 as $result ) {
								?>
								<tr>
									<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $result->id ); ?>"></td>
									<td class="patient">
										<?php
										if ( ! empty( $result->audit_action ) ) {
											echo esc_html( $result->audit_action );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										}
										?>
										<?php echo ' ' . 'By' . ' ' . esc_html( mjschool_get_user_name_by_id( $result->created_by ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Message', 'mjschool' ); ?>"></i>
									</td>
									<td class="income_amount">
										<?php
										if ( ! empty( $result->ip_address ) ) {
											echo esc_html( $result->ip_address );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'IP Address', 'mjschool' ); ?>"></i>
									</td>
									<td class="status">
										<?php
										if ( ! empty( $result->date_time ) ) {
											echo esc_html( mjschool_get_date_in_input_box( $result->date_time ) );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date & Time', 'mjschool' ); ?>"></i>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					<div class="mjschool-print-button pull-left">
						<button class="mjschool-btn-sms-color mjschool-button-reload">
							<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $result->id ); ?>" >
							<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
						</button>
                        
                        <button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_audit_log" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
                    </div>
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