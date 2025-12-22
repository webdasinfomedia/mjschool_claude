<?php

/**
 * Finance Report: Income & Expense Graph and DataTable View.
 *
 * This file handles the display logic for the finance report section, including:
 * - Monthly income/expense graph using Chart.js.
 * - Income/expense filtering by predefined date ranges or custom period.
 * - DataTable output showing total income, total expense, and net profit.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
$active_tab = isset( $_GET['tab2'] ) ? $_GET['tab2'] : 'income_expense_graph';
$mjschool_role       = mjschool_get_roles( get_current_user_id() );
?>
<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
	<li class="<?php if ( $active_tab === 'income_expense_graph' ) { ?> active<?php } ?>">
		<a href="<?php if ( $mjschool_role === 'administrator' ) { echo '?page=mjschool_report'; } else { echo '?dashboard=mjschool_user&page=report'; } ?>&tab=finance_report&tab1=income_expense_payment&tab2=income_expense_graph&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'income_expense_graph' ? 'active' : ''; ?>"> <?php esc_html_e( 'Graph', 'mjschool' ); ?></a>
	</li>
	<li class="<?php if ( $active_tab === 'income_expense_datatable' ) { ?> active<?php } ?>">
		<a href="<?php if ( $mjschool_role === 'administrator' ) { echo '?page=mjschool_report'; } else { echo '?dashboard=mjschool_user&page=report'; } ?>&tab=finance_report&tab1=income_expense_payment&tab2=income_expense_datatable&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'income_expense_datatable' ? 'active' : ''; ?>"> <?php esc_html_e( 'DataTable', 'mjschool' ); ?></a>
	</li>
</ul>
<?php
if ( $active_tab === 'income_expense_graph' ) {

	// Check nonce for income expence graph report tab.
	if ( isset( $_GET['tab'] ) ) {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_finance_report_tab' ) ) {
			wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
		}
	}

	$current_year = date( 'Y' );
	$month        = array(
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
	$labels           = array();
	$income_array     = array();
	$expense_array    = array();
	$net_profit_array = array();
	$currency_symbol  = mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) );
	foreach ( $month as $key => $value ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_income_expense';
		// phpcs:ignore
		$result_income = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE YEAR(income_create_date) = %d AND MONTH(income_create_date) = %d AND invoice_type = %s", $current_year, $key, 'income' )
		);
		// phpcs:ignore
		$result_expense = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE YEAR(income_create_date) = %d AND MONTH(income_create_date) = %d AND invoice_type = %s", $current_year, $key, 'expense' )
		);
		$income_amount = 0;
		foreach ( $result_income as $entry ) {
			$all_entry = json_decode( $entry->entry );
			foreach ( $all_entry as $item ) {
				$income_amount += $item->amount;
			}
		}
		$expense_amount = 0;
		foreach ( $result_expense as $entry ) {
			$all_entry = json_decode( $entry->entry );
			foreach ( $all_entry as $item ) {
				$expense_amount += $item->amount;
			}
		}
		$labels[]           = $value;
		$income_array[]     = $income_amount;
		$expense_array[]    = $expense_amount;
		$net_profit_array[] = $income_amount - $expense_amount;
	}
	$income_filtered  = array_filter( $income_array );
	$expense_filtered = array_filter( $expense_array );
	if ( ! empty( $income_filtered ) || ! empty( $expense_filtered ) ) {
		?>
		<div style="height: 400px;">
			<canvas id="barChartIncomeExpense" data-labels='<?php echo wp_json_encode($labels); ?>' data-income='<?php echo wp_json_encode($income_array); ?>' data-expense='<?php echo wp_json_encode($expense_array); ?>' data-profit='<?php echo wp_json_encode($net_profit_array); ?>' data-currency='<?php echo esc_html( html_entity_decode( $currency_symbol ) ); ?>'></canvas>
		</div>
		<?php
	} else {
		?>
		<div class="mjschool-calendar-event-new"> 
			<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
		</div>
		<?php
	}
}
if ( $active_tab === 'income_expense_datatable' ) {

	// Check nonce for income expence datatable report tab.
	if ( isset( $_GET['tab'] ) ) {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_finance_report_tab' ) ) {
			wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
		}
	}

	?>
	<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<div class="mjschool-panel-body clearfix">
			<form method="post" id="student_income_expence_payment">
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
							<input type="submit" name="income_expense_report" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
		if ( isset( $_REQUEST['income_expense_report'] ) ) {
			$date_type = sanitize_text_field( $_POST['date_type'] );
			if ( $date_type === 'period' ) {
				$start_date = sanitize_text_field( $_REQUEST['start_date'] );
				$end_date   = sanitize_text_field( $_REQUEST['end_date'] );
				$start_date = date('Y-m-d 00:00:00', strtotime($start_date));
				$end_date   = date('Y-m-d 23:59:59', strtotime($end_date));
			} else {
				$result     = mjschool_all_date_type_value( $date_type );
				$response   = json_decode( $result );
				$start_date = $response[0];
				$end_date   = $response[1];
			}
			$income_data  = mjschool_get_total_income( $start_date, $end_date );
			$expense_data = mjschool_get_total_expense( $start_date, $end_date );
			// ----------- Expense Record Sum. ------------//
			$expense_yearly_amount = 0;
			foreach ( $expense_data as $expense_entry ) {
				$all_entry = json_decode( $expense_entry->entry );
				$amount    = 0;
				foreach ( $all_entry as $entry ) {
					$amount += $entry->amount;
				}
				$expense_yearly_amount += $amount;
			}
			if ( $expense_yearly_amount === 0 ) {
				$expense_amount = null;
			} else {
				$expense_amount = "$expense_yearly_amount";
			}
			// ----------- Expense Record Sum. ------------//
			// ----------- Income Record Sum. -------------//
			$income_yearly_amount = 0;
			foreach ( $income_data as $income_entry ) {
				$all_entry = json_decode( $income_entry->entry );
				$amount    = 0;
				foreach ( $all_entry as $entry ) {
					$amount += $entry->amount;
				}
				$income_yearly_amount += $amount;
			}
			if ( $income_yearly_amount === 0 ) {
				$income_amount = null;
			} else {
				$income_amount = "$income_yearly_amount";
			}
			// ----------- Income Record Sum. -------------//
		} else {
			$start_date   = date( 'Y-m-d' );
			$end_date     = date( 'Y-m-d' );
			$income_data  = mjschool_get_total_income( $start_date, $end_date );
			$expense_data = mjschool_get_total_expense( $start_date, $end_date );
			// ----------- Expense Record Sum. ------------//
			$expense_yearly_amount = 0;
			foreach ( $expense_data as $expense_entry ) {
				$all_entry = json_decode( $expense_entry->entry );
				$amount    = 0;
				foreach ( $all_entry as $entry ) {
					$amount += $entry->amount;
				}
				$expense_yearly_amount += $amount;
			}
			if ( $expense_yearly_amount === 0 ) {
				$expense_amount = null;
			} else {
				$expense_amount = "$expense_yearly_amount";
			}
			// ----------- Expense Record Sum. ------------//
			// ----------- Income Record Sum. -------------//
			$income_yearly_amount = 0;
			foreach ( $income_data as $income_entry ) {
				$all_entry = json_decode( $income_entry->entry );
				$amount    = 0;
				foreach ( $all_entry as $entry ) {
					$amount += $entry->amount;
				}
				$income_yearly_amount += $amount;
			}
			if ( $income_yearly_amount === 0 ) {
				$income_amount = null;
			} else {
				$income_amount = "$income_yearly_amount";
			}
			// ----------- Income Record Sum. -------------//
		}
		if ( ! empty( $expense_amount ) || ! empty( $income_amount ) ) {
			?>
			<div class="mjschool-panel-body mjschool-padding-top-15px-res"> <!------  Panel body.  -------->
				<div class="btn-place"></div>
				<div class="table-responsive"> <!------  Table Responsive.  -------->
					<form id="mjschool-common-form1" name="mjschool-common-form1" method="post">
						<table id="table_income_expense" class="display" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Total Income', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Total Expense', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Net Profite', 'mjschool' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php $net_profit = $income_amount - $expense_amount; ?>
								<tr>
                                    <td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
                                        <p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px mjschool-class-color0">
                                            <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png"); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
                                        </p>
                                    </td>
                                    <td class="patient"><?php if( ! empty( $income_amount ) ){ echo esc_html( mjschool_currency_symbol_position_language_wise(number_format($income_amount,2,'.','' ) ) ); }else{ echo esc_html( mjschool_currency_symbol_position_language_wise(number_format(0,2,'.','' ) ) ); } ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Income','mjschool' );?>"></i></td>
                                    <td class="patient_name"><?php if( ! empty( $expense_amount ) ){ echo esc_html( mjschool_currency_symbol_position_language_wise(number_format($expense_amount,2,'.','' ) ) ); }else{ echo esc_html( mjschool_currency_symbol_position_language_wise(number_format(0,2,'.','' ) ) ); } ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Expense','mjschool' );?>"></i></td>
                                    <td class="income_amount" style="<?php if ( $net_profit < 0){ echo "color: red !important"; } ?>"><?php if( ! empty( $net_profit ) ){ echo esc_html( mjschool_currency_symbol_position_language_wise(number_format($net_profit,2,'.','' ) ) ); }else{ echo esc_html( mjschool_currency_symbol_position_language_wise(number_format(0,2,'.','' ) ) ); } ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Net Profit/Loss','mjschool' );?>"></i></td>
                                </tr>
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
	</div>
	<?php
}