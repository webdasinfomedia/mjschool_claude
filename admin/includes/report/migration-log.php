<?php

/**
 * Migration Report Page.
 *
 * This file is responsible for displaying and processing the Migration Report,
 * including date-filter selection, CSV export, DataTables listing,
 * and bulk deletion of migration log entries.
 *
 * It validates security nonces, handles custom date ranges,
 * fetches migration records from the database, and renders them
 * in a sortable, searchable, and exportable DataTable.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for migration report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_migration_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

if ( isset( $_POST['date_type'] ) ) {
	$selected_value = $_POST['date_type'];
} else {
	$selected_value = 'this_month';
}
function mjschool_is_selected( $value, $selected_value ) {
	return $value === $selected_value ? 'selected' : '';
}
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res"> <!------  penal body  -------->
	<div class="mjschool-panel-body clearfix">
		<form method="post" id="student_attendance">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6 mb-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select class="mjschool-line-height-30px form-control date_type validate[required]" id="date_type" name="date_type" autocomplete="off">
							<option value=""><?php esc_html_e( 'Select', 'mjschool' ); ?></option>
							<option value="today" <?php echo esc_attr( mjschool_is_selected( 'today', $selected_value ) ); ?>><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
							<option value="this_week" <?php echo esc_attr( mjschool_is_selected( 'this_week', $selected_value ) ); ?>><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
							<option value="last_week" <?php echo esc_attr( mjschool_is_selected( 'last_week', $selected_value ) ); ?>><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
							<option value="this_month" <?php echo esc_attr( mjschool_is_selected( 'this_month', $selected_value ) ); ?>><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
							<option value="last_month" <?php echo esc_attr( mjschool_is_selected( 'last_month', $selected_value ) ); ?>><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
							<option value="last_3_month" <?php echo esc_attr( mjschool_is_selected( 'last_3_month', $selected_value ) ); ?>><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
							<option value="last_6_month" <?php echo esc_attr( mjschool_is_selected( 'last_6_month', $selected_value ) ); ?>><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
							<option value="last_12_month" <?php echo esc_attr( mjschool_is_selected( 'last_12_month', $selected_value ) ); ?>><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
							<option value="this_year" <?php echo esc_attr( mjschool_is_selected( 'this_year', $selected_value ) ); ?>><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
							<option value="last_year" <?php echo esc_attr( mjschool_is_selected( 'last_year', $selected_value ) ); ?>><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
							<option value="period" <?php echo esc_attr( mjschool_is_selected( 'period', $selected_value ) ); ?>><?php esc_html_e( 'Period', 'mjschool' ); ?></option>
						</select>
					</div>
					<div id="date_type_div" class="col-md-6 <?php echo ( $selected_value === 'period' ) ? '' : 'date_type_div_none'; ?>">
						<?php
						if ( $selected_value === 'period' ) {
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
											dateFormat: "<?php echo esc_js(get_option( 'mjschool_datepicker_format' ) ); ?>",
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
											dateFormat: "<?php echo esc_js(get_option( 'mjschool_datepicker_format' ) ); ?>",
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
						<input type="submit" name="migration_report" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	if ( isset( $_REQUEST['migration_report'] ) ) {
		$date_type = sanitize_text_field( $_POST['date_type'] );
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
		$start_date = date( 'Y-m-d' ); // Today's date.
		$end_date   = date( 'Y-m-t' );   // Last day of the current month.
	}
	global $wpdb;
	$table_mjschool_migration_log = $wpdb->prefix . 'mjschool_migration_log';
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$report_6 = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM $table_mjschool_migration_log WHERE created_at BETWEEN %s AND %s", $start_date, $end_date )
	);
	if ( ! empty( $report_6 ) ) {
		?>
		<script type="text/javascript">
			(function (jQuery) {
				"use strict";
				jQuery(document).ready(function () {
					var table = jQuery( '#tble_audit_log_' ).DataTable({
						initComplete: function () {
							jQuery( ".mjschool-print-button" ).css({ "margin-top": "-55px" });
						},
						order: [[2, "desc"]],
						dom: 'lifrtp',
						buttons: [
							{
								extend: 'csv',
								text: '<?php esc_html_e( 'csv', 'mjschool' ); ?>',
								title: '<?php esc_html_e( 'Migration Report', 'mjschool' ); ?>'
							},
							{
								extend: 'print',
								text: '<?php esc_html_e( 'Print', 'mjschool' ); ?>',
								title: '<?php esc_html_e( 'Migration Report', 'mjschool' ); ?>'
							}
						],
						aoColumns: [
							{ "bSortable": false },
							{ "bSortable": true },
							{ "bSortable": true },
							{ "bSortable": true },
							{ "bSortable": true },
							{ "bSortable": true },
							{ "bSortable": true },
							{ "bSortable": false },
							{ "bSortable": false }
						],
						language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
					});
					jQuery( '.btn-place' ).html(table.buttons().container( ) );
					jQuery( '.dataTables_filter input' ).attr( "placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>" );
					/* -------------------------
					* Checkbox bulk select.
					* ------------------------- */
					jQuery( '#checkbox-select-all' ).on( 'click', function () {
						var rows = table.rows({ search: 'applied' }).nodes();
						jQuery( 'input[type="checkbox"]', rows).prop( 'checked', this.checked);
						jQuery( ".select_all").prop( 'checked', this.checked);
					});
					jQuery(document).on( 'change', '.mjschool-sub-chk', function () {
						var allChecked = jQuery( '.mjschool-sub-chk' ).length === jQuery( '.mjschool-sub-chk:checked' ).length;
						jQuery( ".select_all, #checkbox-select-all").prop( 'checked', allChecked);
					});
					jQuery(document).on( 'click', '.select_all', function () {
						jQuery( ".mjschool-sub-chk, #checkbox-select-all").prop( 'checked', this.checked);
					});
					/* -------------------------
					* Bulk delete confirmation.
					* ------------------------- */
					jQuery(document).on( 'click', '#delete_selected', function () {
						if (jQuery( '.select-checkbox:checked' ).length === 0) {
							alert(language_translate2.one_record_select_alert);
							return false;
						}
						return confirm(language_translate2.delete_record_alert);
					});
				});
			})(jQuery);
		</script>
		<?php
		$obj_feespayment = new Mjschool_Feespayment();
		if ( isset( $_REQUEST['delete_selected_audit_log'] ) ) {
			if ( ! empty( $_REQUEST['id'] ) ) {
				foreach ( $_REQUEST['id'] as $id ) {
					$result = mjschool_delete_migration_log( $id );
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
								<th class="mjschool-custom-padding-0"><input type="checkbox" class=" multiple_select select_all" id="select_all"></th>
								<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'IP Address', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Current Class', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Next Class', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Exam', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Passing Marks', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Pass Student', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Failed student', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $report_6 as $result ) {
								?>
								<tr>
									<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $result->id ); ?>"></td>
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
									<td class="income_amount">
										<?php
										if ( ! empty( $result->ip_address ) ) {
											echo esc_html( $result->ip_address );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_html( $result->ip_address ); ?>"></i>
									</td>
									<td class="income_amount">
										<?php
										if ( ! empty( $result->current_class ) ) {
											echo esc_html( mjschool_get_class_name( $result->current_class ) );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_html( mjschool_get_class_name( $result->current_class ) ); ?>"></i>
									</td>
									<td class="income_amount">
										<?php
										if ( ! empty( $result->next_class ) ) {
											echo esc_html( mjschool_get_class_name( $result->next_class ) );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_html( mjschool_get_class_name( $result->next_class ) ); ?>"></i>
									</td>
									<td class="income_amount">
										<?php
										if ( ! empty( $result->exam_name ) && $result->exam_name !== 0 ) {
											echo esc_html( mjschool_get_exam_name_id( $result->exam_name ) );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_html( mjschool_get_exam_name_id( $result->exam_name ) ); ?>"></i>
									</td>
									<td class="income_amount">
										<?php
										if ( ! empty( $result->pass_mark ) ) {
											echo esc_html( $result->pass_mark );
										} else {
											esc_html_e( 'N/A', 'mjschool' ); 
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_attr( $result->pass_mark ); ?>"></i>
									</td>
									<td class="income_amount">
										<?php
										if ( ! empty( $result->pass_students ) ) {
											echo wp_kses_post( mjschool_get_pass_failed_string( $result->pass_students ) );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_attr( wp_strip_all_tags( mjschool_get_pass_failed_string( $result->pass_students ) ) ); ?>"></i>
									</td>
									<td class="income_amount">
										<?php
										if ( ! empty( $result->fail_students ) ) {
											echo wp_kses_post( mjschool_get_pass_failed_string( $result->fail_students ) );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_attr( wp_strip_all_tags( mjschool_get_pass_failed_string( $result->fail_students ) ) ); ?>"></i>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					<div class="mjschool-print-button pull-left">
						<button class="mjschool-btn-sms-color mjschool-button-reload">
							<input type="checkbox" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $result->id ); ?>" >
							<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
						</button>
                        
                        <button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_audit_log" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
                    </div>
                </form>
            </div> <!------  Table responsive  -------->
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