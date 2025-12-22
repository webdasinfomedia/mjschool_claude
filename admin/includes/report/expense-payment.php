<?php

/**
 * Finance Report – Expense Graph & DataTable View.
 *
 * Handles rendering of the Finance → Expense Report section inside the
 * MjSchool plugin. This file:
 *
 * - Renders navigation tabs for "Graph" and "DataTable" expense reports.
 * - Generates monthly expense chart using Google Charts.
 * - Processes date filters (Today, This Week, Last Month, Period, etc.).
 * - Fetches expense data from the database for chart and table output.
 * - Initializes DataTables with export options (CSV, Print).
 * - Ensures security with nonce validation.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
$active_tab = isset( $_GET['tab2'] ) ? $_GET['tab2'] : 'expense_graph_payment';
$mjschool_role       = mjschool_get_roles( get_current_user_id() );
?>
<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
	<li class="<?php if ( $active_tab === 'expense_graph_payment' ) { ?> active<?php } ?>">
		<a href="<?php if ( $mjschool_role === 'administrator' ) { echo '?page=mjschool_report'; } else { echo '?dashboard=mjschool_user&page=report'; } ?>&tab=finance_report&tab1=expense_payment&tab2=expense_graph_payment&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'expense_graph_payment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Graph', 'mjschool' ); ?></a>
	</li>
	<li class="<?php if ( $active_tab === 'mjschool-expense-datatable' ) { ?> active<?php } ?>">
		<a href="<?php if ( $mjschool_role === 'administrator' ) { echo '?page=mjschool_report'; } else { echo '?dashboard=mjschool_user&page=report'; } ?>&tab=finance_report&tab1=expense_payment&tab2=mjschool-expense-datatable&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'mjschool-expense-datatable' ? 'active' : ''; ?>"> <?php esc_html_e( 'DataTable', 'mjschool' ); ?></a>
	</li>
</ul>
<?php
if ( $active_tab === 'expense_graph_payment' ) {
	// Check nonce for expense graph report tab.
	if ( isset( $_GET['tab'] ) ) {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_finance_report_tab' ) ) {
			wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
		}
	}
	?>
	<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		$month       = array(
			'1'  => esc_html__( 'January', 'mjschool' ),
			'2'  => esc_html__( 'February', 'mjschool' ),
			'3'  => esc_html__( 'March', 'mjschool' ),
			'4'  => esc_html__( 'April', 'mjschool' ),
			'5'  => esc_html__( 'May', 'mjschool' ),
			'6'  => esc_html__( 'June', 'mjschool' ),
			'7'  => esc_html__( 'July', 'mjschool' ),
			'8'  => esc_html__( 'August', 'mjschool' ),
			'9'  => esc_html__( 'September', 'mjschool' ),
			'10' => esc_html__( 'October', 'mjschool' ),
			'11' => esc_html__( 'November', 'mjschool' ),
			'12' => esc_html__( 'December', 'mjschool' ),
		);
		$year        = isset( $_POST['year'] ) ? $_POST['year'] : date( 'Y' );
		$chart_array = array();
		array_push( $chart_array, array( esc_html__( 'Month', 'mjschool' ), esc_html__( 'Expense', 'mjschool' ) ) );
		$sumArray = array();
		foreach ( $month as $key => $value ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'mjschool_income_expense';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result               = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM $table_name WHERE YEAR(income_create_date) = %d AND MONTH(income_create_date) = %d AND invoice_type = %s", $year, $key, 'expense' )
			);
			$income_yearly_amount = 0;
			$currency             = mjschool_get_currency_symbol();
			$currency_1           = html_entity_decode( $currency );
			foreach ( $result as $income_entry ) {
				$all_entry = json_decode( $income_entry->entry );
				$amount    = 0;
				foreach ( $all_entry as $entry ) {
					$amount += $entry->amount;
				}
				$income_yearly_amount += $amount;
			}
			$income_amount = (float) $income_yearly_amount;
			$income_array[] = $income_amount;
			// array_push( $chart_array, array( $value, $income_amount ) );
			$chart_array[] = array($value, $income_amount);
		}
		$new_array = $chart_array;
		$expence = array_filter($income_array);
		$chart_data = wp_json_encode($chart_array);
		if ( ! empty( $expence ) ) {
			?>
			<div id="mjschool-barchart-material-expence" class="mjschool-barchart-material-expence" data-chart='<?php echo esc_attr($chart_data); ?>' style="width:100%; height:430px; padding:20px;"></div>
			<?php
		} else {
			?>
            <div class="mjschool-calendar-event-new">
				
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
                
			</div>
			<?php
		}
		?>
	</div>
	<?php
}
if ( $active_tab === 'mjschool-expense-datatable' ) {
	// Check nonce for expense datatable report tab.
	if ( isset( $_GET['tab'] ) ) {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_finance_report_tab' ) ) {
			wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
		}
	}
	?>
	<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res"> <!-----  penal body  -------->
		<div class="mjschool-panel-body clearfix">
			<form method="post" id="student_expence_payment">
				<div class="form-body mjschool-user-form">
					<div class="row">
						<?php $selected_date_type = isset( $_POST['date_type'] ) ? $_POST['date_type'] : ''; ?>
						<div class="col-md-3 mb-3 input">
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
							<input type="submit" name="expense_payment" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
		if ( isset( $_REQUEST['expense_payment'] ) ) {
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
			$start_date = date( 'Y-m-d' );
			$end_date   = date( 'Y-m-d' );
		}
		global $wpdb;
		$table_income = $wpdb->prefix . 'mjschool_income_expense';
		$start_date = date('Y-m-d 00:00:00', strtotime($start_date));
		$end_date   = date('Y-m-d 23:59:59', strtotime($end_date));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_6 = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_income WHERE invoice_type = %s AND income_create_date BETWEEN %s AND %s", 'expense', $start_date, $end_date )
		);
		if ( ! empty( $report_6 ) ) {
			?>
			<div class="mjschool-panel-body mjschool-padding-top-15px-res"><!----- Table panel body.  -------->
				<div class="btn-place"></div>
				<div class="table-responsive"> <!----- Table responsive.  -------->
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<table id="tblexpence" class="display" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<th><?php esc_html_e( 'Supplier Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Amount', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Create Date', 'mjschool' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								global $wpdb;
								$table_income = $wpdb->prefix . 'mjschool_income_expense';
                                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
								$report_6 = $wpdb->get_results(
									$wpdb->prepare( "SELECT * FROM $table_income WHERE invoice_type = %s AND income_create_date BETWEEN %s AND %s", 'expense', $start_date, $end_date )
								);
								if ( ! empty( $report_6 ) ) {
									$i = 0;
									foreach ( $report_6 as $result ) {
										if ( $i === 10 ) {
											$i = 0;
										}
										if ( $i === 0 ) {
											$color_class_css = 'mjschool-class-color0';
										} elseif ( $i === 1 ) {
											$color_class_css = 'mjschool-class-color1';
										} elseif ( $i === 2 ) {
											$color_class_css = 'mjschool-class-color2';
										} elseif ( $i === 3 ) {
											$color_class_css = 'mjschool-class-color3';
										} elseif ( $i === 4 ) {
											$color_class_css = 'mjschool-class-color4';
										} elseif ( $i === 5 ) {
											$color_class_css = 'mjschool-class-color5';
										} elseif ( $i === 6 ) {
											$color_class_css = 'mjschool-class-color6';
										} elseif ( $i === 7 ) {
											$color_class_css = 'mjschool-class-color7';
										} elseif ( $i === 8 ) {
											$color_class_css = 'mjschool-class-color8';
										} elseif ( $i === 9 ) {
											$color_class_css = 'mjschool-class-color9';
										}
										$all_entry    = json_decode( $result->entry );
										$total_amount = 0;
										foreach ( $all_entry as $entry ) {
											$total_amount += $entry->amount;
											?>
											<tr>
												<td class="patient_name"><?php echo esc_html( $result->supplier_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Supplier Name', 'mjschool' ); ?>"></i></td>
												<td class="income_amount"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $total_amount, 2, '.', '' ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Amount', 'mjschool' ); ?>"></i></td>
												<td class="status"><?php echo esc_html( mjschool_get_date_in_input_box( $result->income_create_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Create Date', 'mjschool' ); ?>"></i></td>
											</tr>
											<?php
										}
										++$i;
									}
								}
								?>
							</tbody>
						</table>
					</form>
				</div> <!----- Table responsive.  -------->
			</div> <!----- Table panel body.  -------->
			<?php
		} else {
			?>
			<div class="mjschool-calendar-event-new">
                
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
                
			</div>
			<?php
		}
		?>
	</div> <!-----  Panel body.  -------->
	<?php
}
?>