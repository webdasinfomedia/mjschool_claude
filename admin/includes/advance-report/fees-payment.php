<?php
/**
 * The admin interface for displaying the Advanced Fees Payment Report.
 *
 * This file renders the Fees Payment Report within the Advanced Reports module.
 * It dynamically lists fee details such as term, student name, class, payment status,
 * total and due amounts, and late time calculation (month/quarter/year wise).
 * The report supports DataTables with advanced search, filters, export options (CSV, Print),
 * and dynamic late payment detection for administrators and authorized roles.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/advance-report
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for advance finance report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_advance_finance_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
$active_tab      = isset( $_GET['tab2'] ) ? $_GET['tab2'] : 'fees_payment_datatable';
$mjschool_role            = mjschool_get_roles( get_current_user_id() );
$class_name      = mjschool_get_all_class_array();
// $class_name_list = array_map(
// 	function ( $s ) {
// 		return trim( $s->class_name ); // Trim each class name.
// 	},
// 	$class_name
// );
?>
<?php
if ( $active_tab === 'fees_payment_datatable' ) {
	?>
	<div class="clearfix"> </div>
	<?php
	$result_feereport = mjschool_get_payment_report_front_all_advance();
	if ( ! empty( $result_feereport ) ) {
		?>
		
		<div class="table-responsive">
			<div class="btn-place"></div>
			<table id="mjschool-fees-payment-advance-report" class="display" cellspacing="0" width="100%">
				<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
					<tr>
						<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Fees Term', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Payment Status', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Due Amount', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Start To End Year', 'mjschool' ); ?></th>
						<th class="late-time-col"><?php esc_html_e( 'Late Time', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 0;
					foreach ( $result_feereport as $retrieved_data ) {
						$color_class_css = 'mjschool_class_color' . ( $i % 10 );
						?>
						<tr>
							<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
								<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
                                    
                                    <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png"); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
                                    
								</p>
							</td>
							<?php
							$fees_type = array_map( 'mjschool_get_fees_term_name', explode( ',', $retrieved_data->fees_id ) );
							?>
							<td><?php echo esc_html( implode( ', ', $fees_type ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Term', 'mjschool' ); ?>"></i></td>
							<td><?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
							<td><?php echo esc_html( $retrieved_data->class_id ) === '0' ? esc_html__( 'All Class', 'mjschool' ) : esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_id, $retrieved_data->section_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i></td>
							<td> 
								<?php $payment_status = mjschool_get_payment_status( $retrieved_data->fees_pay_id ); if ( $payment_status === 'Not Paid' ) { echo "<span class='mjschool-red-color'>"; } elseif ( $payment_status === 'Partially Paid' ) { echo "<span class='mjschool-purpal-color'>"; } else { echo "<span class='mjschool-green-color'>"; } echo esc_html( $payment_status ); echo '</span>'; ?>
								<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Status', 'mjschool' ); ?>"></i>
							</td>
							<td><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->total_amount, 2 ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i></td>
							<td><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->total_amount - $retrieved_data->fees_paid_amount, 2 ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Due Amount', 'mjschool' ); ?>"></i></td>
							<td><?php echo esc_html( $retrieved_data->start_year ) . '-' . esc_html( $retrieved_data->end_year ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start To End Year', 'mjschool' ); ?>"></i></td>
							<td>
								<?php
								$end_year     = isset( $retrieved_data->end_year ) ? $retrieved_data->end_year : '';
								$paid_amount  = $retrieved_data->fees_paid_amount;
								$total_amount = $retrieved_data->total_amount;
								// Default CSS class for the payment status.
								$status_class = 'status-on-time';  // Default class for "On-Time Payment".
								$status_text  = esc_html__( 'On-Time Payment', 'mjschool' );  // Default text.
								if ( ! empty( $end_year ) && $paid_amount < $total_amount ) {
									$today        = date( 'Y-m-d' );
									$due_date     = strtotime( $end_year );
									$current_date = strtotime( $today );
									$diff_in_days = ( $current_date - $due_date ) / ( 60 * 60 * 24 );
									// Calculate the difference in months, quarters, and years.
									$diff_in_months   = floor( $diff_in_days / 30 );
									$diff_in_quarters = floor( $diff_in_days / 90 );  // Approximation: 90 days per quarter.
									$diff_in_years    = floor( $diff_in_days / 365 );    // Approximation: 365 days per year.
									if ( $diff_in_years > 0 ) {
										$status_class = 'status-late';  // Set class for late payment.
										$status_text  = "$diff_in_years " . esc_html__( 'year(s) late', 'mjschool' );
									} elseif ( $diff_in_quarters > 0 ) {
										$status_class = 'status-late';  // Set class for late payment.
										$status_text  = "$diff_in_quarters " . esc_html__( 'quarter(s) late', 'mjschool' );
									} elseif ( $diff_in_months > 0 ) {
										$status_class = 'status-late';  // Set class for late payment.
										$status_text  = "$diff_in_months " . esc_html__( 'month(s) late', 'mjschool' );
									} else {
										$status_class = 'status-due-soon';  // Set class for "Due Soon".
										$status_text  = esc_html__( 'Due Soon', 'mjschool' );
									}
								}
								// Output the payment status with dynamic CSS class and text.
								echo '<span class="' . esc_attr( $status_class ) . '">' . esc_html( $status_text ) . '</span>';
								?>
								<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Late Time', 'mjschool' ); ?>"></i>
							</td>
							<td class="action">
								<div class="mjschool-user-dropdown">
									<ul  class="mjschool_ul_style">
										<li >
											<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                                
                                                <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
                                                
											</a>
											<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn">
												<li class="mjschool-float-left-width-100px">
													<a href="<?php echo $mjschool_role === 'administrator' ? '?page=mjschool_fees_payment&tab=view_fesspayment' : '?dashboard=mjschool_user&page=feepayment&tab=view_fesspayment'; ?>&idtest=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->fees_pay_id ) ); ?>&view_type=view_payment" class="mjschool-float-left-width-100px">
														<i class="fas fa-eye"></i><?php esc_html_e( 'View', 'mjschool' ); ?>
													</a>
												</li>
											</ul>
										</li>
									</ul>
								</div>
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
	} else {
		?>
		<div class="mjschool-calendar-event-new">
            
            <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
            
		</div>
		<?php
	}
}
?>