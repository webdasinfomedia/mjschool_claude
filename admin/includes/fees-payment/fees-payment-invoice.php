<?php
/**
 * View Invoice for MJSchool Plugin.
 *
 * Handles rendering the invoice details for a student payment within the MJSchool plugin.
 * Supports multiple invoice formats, RTL and LTR layouts, and displays student details,
 * payment status, fees breakdown, tax, discount, subtotal, and due amount.
 * Escapes output for security and formats amounts according to the currency settings.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/feespayment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

global $wpdb;
$fees_pay_id                = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['idtest'] ) ) );
$fees_detail_result         = mjschool_get_single_fees_payment_record( $fees_pay_id );
$fees_history_detail_result = mjschool_get_payment_history_by_fees_pay_id( $fees_pay_id );
$mjschool_obj_feespayment   = new Mjschool_Feespayment();
$format                     = get_option( 'mjschool_invoice_option' );

// FIXED: Use meaningful table name variable
$fees_payment_table = $wpdb->prefix . 'mjschool_fees_payment';

$invoice_number             = mjschool_generate_invoice_number( $fees_pay_id );
?>
<?php if ( is_rtl() ) { 
	wp_enqueue_style( 'mjschool-invoice-rtl-style', plugins_url( '/assets/css/mjschool-invoice-rtl.css', __FILE__ ) );
}
if ( sanitize_text_field( wp_unslash($_REQUEST['view_type'])) === 'view_payment' ) {
	?>
	<div class="penal-body"><!----- Panel Body. --------->
		<div id="Fees_invoice"><!----- Fees Invoice. --------->
			<div class="modal-body mjschool-border-invoice-page mjschool-margin-top-25px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool-custom-padding-0_res mjschool_height_1600px" >
				<?php if ( $format === 0 ) { 
					 ?>
					<img class="mjschool-rtl-image-set-invoice mjschool-invoice-image mjschool-float-left mjschool-image-width-98px mjschool-invoice-image-model" src="<?php echo esc_url( plugins_url( '/mjschool/assets/images/listpage-icon/mjschool-invoice.png' ) ); ?>" width="100%">
					<?php  
				} ?>
				<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
					<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
						<div class="row mjschool-margin-top-20px">
							<?php if ( $format === 1 ) { ?>
								<div id="rtl_heads_logo" class="mjschool-width-print mjschool-rtl-heads rtl_heads_logo mjschool_fees_style" >
									<div class="mjschool_float_left_width_100">
										<div class="mjschool_float_left_width_25">
											<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
												<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
											</div>
										</div>
										<div class="mjschool_float_left_padding_width_75">
											<p class="mjschool_fees_widht_100_fonts_24px">
												<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
											</p>
											<p class="mjschool_fees_center_fonts_17px">
												<?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
											</p>
											<div class="mjschool_fees_center_margin_0px">
												<p class="mjschool_fees_width_fit_content_inline">
													<?php esc_html_e( 'E-mail', 'mjschool' ); ?> :
													<?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
												</p>
												<p class="mjschool_fees_width_fit_content_inline">
													&nbsp;&nbsp;
													<?php esc_html_e( 'Phone', 'mjschool' ); ?> :
													<?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
												</p>
											</div>
										</div>
									</div>
								</div>
								<?php
							} else {
								?>
								<h3 class="mjschool-school-name-for-invoice-view">
									<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
								</h3>
								<div class="col-md-1 col-sm-2 col-xs-3">
									<div class="width_1 mjschool-rtl-width-80px">
										<img class="system_logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
									</div>
								</div>
								<div class="col-md-11 col-sm-10 col-xs-9 mjschool-invoice-address mjschool-invoice-address-css">
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12 mjschool-invoice-padding-bottom-15px mjschool-padding-right-0">
											<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Address', 'mjschool' ); ?></label><br>
											<label class="mjschool-label-value mjschool-word-break-all">
												<?php
												$address         = get_option( 'mjschool_address' );
												$escaped_address = esc_html( $address );
												echo nl2br( chunk_split( $escaped_address, 100, "\n" ) );
												?>
											</label>
										</div>
										<div class="row col-md-12 mjschool-invoice-padding-bottom-15px">
											<div class="col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-email-width-auto">
												<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Email', 'mjschool' ); ?></label><br>
												<label class="mjschool-label-value mjschool-word-break-all"><?php echo esc_html( get_option( 'mjschool_email' ) ), '<BR>'; ?></label>
											</div>
											<div class="col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-padding-left-30px">
												<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Phone', 'mjschool' ); ?></label><br>
												<label class="mjschool-label-value"><?php echo esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>'; ?></label>
											</div>
										</div>
										<div align="right" class="mjschool-width-24px"></div>
									</div>
								</div>
							<?php } ?>
						</div>
						<div class="col-md-12 col-sm-12 col-xl-12 mjschool-mozila-display-css mjschool-margin-top-20px">
							<?php if ( $format === 1 ) { ?>
								<div class="mjschool-width-print mjschool_fees_padding_border_2px">
									<div class="mjschool_float_left_width_100">
										<?php
										$student_id = $fees_detail_result->student_id;
										$patient    = get_userdata( $student_id );
										if ( $patient ) {
											$display_name         = isset( $patient->display_name ) ? $patient->display_name : '';
											$escaped_display_name = esc_html( ucwords( $display_name ) );
											$split_display_name   = chunk_split( $escaped_display_name, 30, '<br>' );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
										<div  class="mjschool_padding_10px">
											<div class="mjschool_float_left_width_65">
												<b><?php esc_html_e( 'Bill To', 'mjschool' ); ?>:</b> <?php echo esc_html( mjschool_student_display_name_with_roll( $student_id ) ); ?>
											</div>
											<div class="mjschool_float_right_width_35">
												<b><?php esc_html_e( 'Invoice Number', 'mjschool' ); ?>:</b> <?php echo esc_html( $invoice_number ); ?>
											</div>
										</div>
									</div>
									<div class="mjschool_float_left_width_65">
										<?php
										$student_id = $fees_detail_result->student_id;
										$patient    = get_userdata( $student_id );
										if ( $patient ) {
											$address = esc_html( get_user_meta( $student_id, 'address', true ) );
											$city    = esc_html( get_user_meta( $student_id, 'city', true ) );
											$zip     = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
											?>
											<div class="mjschool_padding_10px">
												<div>
													<b><?php esc_html_e( 'Address', 'mjschool' ); ?>:</b> <?php echo esc_html( $address ); ?>
												</div>
												<div>
													<?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?>
												</div>
											</div>
										<?php } ?>
									</div>
									<div class="mjschool_float_right_width_35">
										<?php
										$issue_date = 'DD-MM-YYYY';
										$issue_date = $fees_detail_result->paid_by_date;
										if ( ! empty( $income_data ) ) {
											$issue_date = $income_data->income_create_date;
										} elseif ( ! empty( $invoice_data ) ) {
											$issue_date = $invoice_data->date;
										} elseif ( ! empty( $expense_data ) ) {
											$issue_date = $expense_data->income_create_date;
										}
										?>
										<div class="mjschool_fees_padding_10px">
											<div class="mjschool_float_left_width_100">
												<b><?php esc_html_e( 'Issue Date', 'mjschool' ); ?>:</b> <?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
											</div>
										</div>
									</div>
									<div class="mjschool_float_right_width_35">
										<div class="mjschool_fees_padding_10px">
											<b><?php esc_html_e( 'Status', 'mjschool' ); ?>:</b>
											<?php
											$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
											if ( $payment_status === 'Fully Paid' ) {
												echo '<span class="mjschool-green-color">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
											}
											if ( $payment_status === 'Partially Paid' ) {
												echo '<span class="mjschool-purpal-color">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
											}
											if ( $payment_status === 'Not Paid' ) {
												echo '<span class="mjschool-red-color">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
											}
											?>
										</div>
									</div>
								</div>
								<?php
							} else {
								?>
								<div class="row">
									<div class="mjschool-width-50px mjschool-float-left-width-100px">
										<div class="col-md-8 col-sm-8 col-xs-5 mjschool-custom-padding-0 mjschool-float-left mjschool-display-grid mjschool-display-inherit-res mjschool-margin-bottom-20px">
											<div class="mjschool-billed-to mjschool-display-flex mjschool-display-inherit-res mjschool-invoice-address-heading">
												<h3 class="mjschool-billed-to-lable mjschool-invoice-model-heading mjschool-bill-to-width-12px">
													<?php esc_html_e( 'Bill To', 'mjschool' ); ?> :
												</h3>
												<?php
												$student_id = $fees_detail_result->student_id;
												$patient    = get_userdata( $student_id );
												if ( $patient ) {
													$display_name         = isset( $patient->display_name ) ? $patient->display_name : '';
													$escaped_display_name = esc_html( ucwords( $display_name ) );
													$split_display_name   = chunk_split( $escaped_display_name, 30, '<br>' );
													echo "<h3 class='display_name mjschool-invoice-width-100px'>" . esc_html( mjschool_student_display_name_with_roll( $student_id ) ) . '</h3>';
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												}
												?>
											</div>
											<div class="mjschool-width-60px mjschool-address-information-invoice">
												<?php
												$student_id = $fees_detail_result->student_id;
												$patient    = get_userdata( $student_id );
												if ( $patient ) {
													$address         = get_user_meta( $student_id, 'address', true );
													$escaped_address = esc_html( $address );
													$split_address   = chunk_split( $escaped_address, 30, '<br>' );
													echo wp_kses_post( $split_address );
													echo esc_html( get_user_meta( $student_id, 'city', true ) ) . ',' . '<BR>';
													echo esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . ',<BR>';
												}
												?>
											</div>
										</div>
										<div class="col-md-3 col-sm-4 col-xs-7 mjschool-float-left">
											<div class="mjschool-width-50px">
												<div class="mjschool-width-20px" align="center">
													<h5 class="mjschool-align-left"> 
														<label class="mjschool-popup-label-heading text-transfer-upercase"><?php echo esc_html__( 'Invoice Number :', 'mjschool' ); ?></label>&nbsp; <label class="mjschool-invoice-model-value"><?php echo esc_html( $invoice_number ); ?></label>
													</h5>
													<?php
													$issue_date     = 'DD-MM-YYYY';
													$issue_date     = $fees_detail_result->paid_by_date;
													$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
													?>
													<h5 class="mjschool-align-left">
														<label class="mjschool-popup-label-heading text-transfer-upercase"><?php echo esc_html__( 'Date :', 'mjschool' ); ?></label>&nbsp; <label class="mjschool-invoice-model-value"><?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?></label>
													</h5>
													<h5 class="mjschool-align-left">
														<label class="mjschool-popup-label-heading text-transfer-upercase">
															<?php echo esc_html__( 'Status :', 'mjschool' ); ?>
														</label> &nbsp;
														<label class="mjschool-invoice-model-value">
															<?php
															if ( $payment_status === 'Fully Paid' ) {
																echo '<span class="mjschool-green-color">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
															}
															if ( $payment_status === 'Partially Paid' ) {
																echo '<span class="mjschool-purpal-color">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
															}
															if ( $payment_status === 'Not Paid' ) {
																echo '<span class="mjschool-red-color">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
															}
															?>
														</label>
													</h5>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php } ?>
						</div>
						<table class="mjschool-width-100px mjschool-margin-top-10px-res mt-3">
							<tbody>
								<tr>
									<td>
										<h3 class="display_name"><?php esc_html_e( 'Invoice Entries', 'mjschool' ); ?></h3>
									<td>
								</tr>
							</tbody>
						</table>
						<div class="table-responsive mjschool-padding-bottom-15px mjschool-rtl-padding-left-40px">
							<?php if ( $format === 1 ) { ?>
								<div class="table-responsive">
									<table class="table table-bordered mjschool-model-invoice-table mjschool_border_black_2px">
										<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_border_color_2px">
											<tr>
												<th class="mjschool-entry-table-heading mjschool-align-left mjschool_tables_width_15px">
													Number
												</th>
												<th class="mjschool-entry-table-heading mjschool-align-left mjschool_tables_width_20">
													<?php esc_html_e( 'Date', 'mjschool' ); ?>
												</th>
												<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_2px">
													<?php esc_html_e( 'Fees Type', 'mjschool' ); ?>
												</th>
												<th class="mjschool-entry-table-heading mjschool-align-left mjschool_tables_width_15px">
													<?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
											// FIXED: Sanitize fees_id and use proper array validation
											$fees_id_string = $fees_detail_result->fees_id;
											$fees_id = array_map( 'intval', explode( ',', $fees_id_string ) );
											
											$x       = 1;
											$amounts = 0;
											foreach ( $fees_id as $id ) {
												// FIXED: Validate that $id is a positive integer
												if ( $id > 0 ) {
													?>
													<tr>
														<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
															<?php echo esc_html( $x ); ?>
														</td>
														<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
															<?php echo esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ); ?>
														</td>
														<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
															<?php echo esc_html( mjschool_get_fees_term_name( $id ) ); ?>
														</td>
														<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
															<?php
															$amount   = $mjschool_obj_feespayment->mjschool_feetype_amount_data( $id );
															$amounts += $amount;
															echo esc_html( number_format( $amount, 2, '.', '' ) );
															?>
														</td>
													</tr>
													<?php
													++$x;
												}
											}
											$sub_total = $amounts;
											if ( ! empty( $fees_detail_result->tax ) ) {
												$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $fees_detail_result->tax ) );
											} else {
												$tax_name = '';
											}
											if ( $fees_detail_result->discount ) {
												$discount_name = mjschool_get_discount_name( $fees_detail_result->discount, $fees_detail_result->discount_type );
											} else {
												$discount_name = '';
											}
											?>
										</tbody>
									</table>
								</div>
								<?php
							} else {
								?>
								<table class="table mjschool-model-invoice-table">
									<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading">
										<tr>
											<th class="mjschool-entry-table-heading mjschool-align-left">#</th>
											<th class="mjschool-entry-table-heading mjschool-align-left"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
											<th class="mjschool-entry-table-heading mjschool-align-left"><?php esc_html_e( 'Fees Type', 'mjschool' ); ?></th>
											<th class="mjschool-entry-table-heading mjschool-align-left"><?php esc_html_e( 'Total', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										// FIXED: Same sanitization as above
										$fees_id_string = $fees_detail_result->fees_id;
										$fees_id = array_map( 'intval', explode( ',', $fees_id_string ) );
										
										$x       = 1;
										$amounts = 0;
										foreach ( $fees_id as $id ) {
											if ( $id > 0 ) {
												?>
												<tr>
													<td class="mjschool-align-left mjschool-invoice-table-data">
														<?php echo esc_html( $x ); ?>
													</td>
													<td class="mjschool-align-left mjschool-invoice-table-data">
														<?php echo esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ); ?>
													</td>
													<td class="mjschool-align-left mjschool-invoice-table-data">
														<?php echo esc_html( mjschool_get_fees_term_name( $id ) ); ?>
													</td>
													<td class="mjschool-align-left mjschool-invoice-table-data">
														<?php
														$amount   = $mjschool_obj_feespayment->mjschool_feetype_amount_data( $id );
														$amounts += $amount;
														echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $amount, 2, '.', '' ) ) );
														?>
													</td>
												</tr>
												<?php
												++$x;
											}
										}
										$sub_total = $amounts;
										if ( ! empty( $fees_detail_result->tax ) ) {
											$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $fees_detail_result->tax ) );
										} else {
											$tax_name = '';
										}
										if ( $fees_detail_result->discount ) {
											$discount_name = mjschool_get_discount_name( $fees_detail_result->discount, $fees_detail_result->discount_type );
										} else {
											$discount_name = '';
										}
										?>
									</tbody>
								</table>
							<?php } ?>
						</div>
						<?php
						if ( $format === 1 ) {
							?>
							<div class="table-responsive mjschool-rtl-padding-left-40px mjschool-rtl-float-left-width-100px">
								<table class="table table-bordered mjschool_fees_collapse_width_100">
									<tbody>
										<tr>
											<th style="width: 85%;text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;font-weight: 600;background-color: #b8daff;padding: 10px;border: 2px solid black;" scope="row">
												<?php echo esc_html__( 'Sub Total', 'mjschool' ) . ' :'; ?>
											</th>
											<td style="width: 15%;text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;padding: 10px;font-weight: 600;border: 2px solid black;">
												<?php echo esc_html( number_format( $sub_total, 2, '.', '' ) ); ?>
											</td>
										</tr>
										<?php if ( isset( $fees_detail_result->discount_amount ) && ( $fees_detail_result->discount_amount ) != 0 ) { ?>
											<tr>
												<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;font-weight: 600;background-color: #b8daff;padding: 10px;border: 2px solid black;" scope="row">
													<?php echo esc_html__( 'Discount Amount', 'mjschool' ) . ' ( ' . esc_html( $discount_name ) . ' ) :'; ?>
												</th>
												<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;padding: 10px;font-weight: 600;border: 2px solid black;">
													<?php echo '-' . esc_html( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ); ?>
												</td>
											</tr>
										<?php } ?>
										<?php if ( isset( $fees_detail_result->tax_amount ) && ( $fees_detail_result->tax_amount ) != 0 ) { ?>
											<tr>
												<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;font-weight: 600;background-color: #b8daff;padding: 10px;border: 2px solid black;" scope="row">
													<?php echo esc_html__( 'Tax Amount', 'mjschool' ) . ' ( ' . esc_html( $tax_name ) . ' ) :'; ?>
												</th>
												<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;padding: 10px;font-weight: 600;border: 2px solid black;">
													<?php echo '+' . esc_html( number_format( $fees_detail_result->tax_amount, 2, '.', '' ) ); ?>
												</td>
											</tr>
										<?php } ?>
										<tr>
											<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;font-weight: 600;background-color: #b8daff;padding: 10px;border: 2px solid black;" scope="row">
												<?php echo esc_html__( 'Payment Made :', 'mjschool' ); ?>
											</th>
											<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;padding: 10px;font-weight: 600;border: 2px solid black;">
												<?php echo esc_html( number_format( $fees_detail_result->fees_paid_amount, 2, '.', '' ) ); ?>
											</td>
										</tr>
										<tr>
											<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;font-weight: 600;background-color: #b8daff;padding: 10px;border: 2px solid black;" scope="row">
												<?php echo esc_html__( 'Due Amount :', 'mjschool' ); ?>
											</th>
											<?php $Due_amount = $fees_detail_result->total_amount - $fees_detail_result->fees_paid_amount; ?>
											<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;padding: 10px;font-weight: 600;border: 2px solid black;">
												<?php echo esc_html( number_format( $Due_amount, 2, '.', '' ) ); ?>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<?php
						} else {
							?>
							<div class="table-responsive  mjschool-rtl-float-left-width-100px">
								<table width="100%" border="0">
									<tbody>
										<tr >
											<td align="right" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading">
												<?php esc_html_e( 'Sub Total :', 'mjschool' ); ?>
											</td>
											<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
												<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $sub_total, 2, '.', '' ) ) ); ?>
											</td>
										</tr>
										<?php if ( isset( $fees_detail_result->discount_amount ) && ( $fees_detail_result->discount_amount ) != 0 ) { ?>
											<tr>
												<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right">
													<?php echo esc_html__( 'Discount Amount', 'mjschool' ) . '( ' . esc_html( $discount_name ) . ' )' . '  :'; ?>
												</td>
												<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
													<?php echo '-' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ) ); ?>
												</td>
											</tr>
										<?php } ?>
										<?php
										if ( isset( $fees_detail_result->tax_amount ) && ( $fees_detail_result->tax_amount ) != 0 ) {
											?>
											<tr>
												<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right">
													<?php echo esc_html__( 'Tax Amount', 'mjschool' ) . '( ' . esc_html( $tax_name ) . ' )' . '  :'; ?>
												</td>
												<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
													<?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->tax_amount, 2, '.', '' ) ) ); ?>
												</td>
											</tr>
											<?php
										}
										?>
										<tr>
											<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right">
												<?php esc_html_e( 'Payment Made :', 'mjschool' ); ?>
											</td>
											<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
												<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->fees_paid_amount, 2, '.', '' ) ) ); ?>
											</td>
										</tr>
										<tr>
											<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right">
												<?php esc_html_e( 'Due Amount :', 'mjschool' ); ?>
											</td>
											<?php $Due_amount = $fees_detail_result->total_amount - $fees_detail_result->fees_paid_amount; ?>
											<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
												<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Due_amount, 2, '.', '' ) ) ); ?>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						<?php } ?>
						<?php
						$subtotal    = $fees_detail_result->total_amount;
						$paid_amount = $fees_detail_result->fees_paid_amount;
						$grand_total = $subtotal - $paid_amount;
						?>
						<div id="mjschool-res-rtl-width-100px" class="mjschool-res-rtl-width-100px mjschool-rtl-float-left row mjschool-margin-top-10px-res col-md-4 col-sm-4 col-xs-4 mjschool-view-invoice-lable-css mjschool-inovice-width-100px-rs mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total mjschool_float_margin_right_0px" >
							<div class="mjschool-width-50-res mjschool-align-right col-md-5 col-sm-5 col-xs-5 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
								<h3  class="padding mjschool-color-white margin mjschool-invoice-total-label mjschool_float_right">
									<?php esc_html_e( 'Grand Total', 'mjschool' ); ?>
								</h3>
							</div>
							<div class="mjschool-width-50-res mjschool-align-right col-md-7 col-sm-7 col-xs-7 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
								<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value">
									<?php
									$formatted_amount = number_format( $subtotal, 2, '.', '' );
									$currency         = mjschool_get_currency_symbol();
									echo esc_html( "($currency)$formatted_amount" );
									?>
								</h3>
							</div>
						</div>
						<?php
						if ( ! empty( $fees_history_detail_result ) ) {
							?>
							<table class="mjschool-width-100px mjschool-margin-top-10px-res">
								<tbody>
									<tr>
										<td>
											<h3 class="display_name mjschool-res-pay-his-mt-10px"><?php esc_html_e( 'Payment History', 'mjschool' ); ?></h3>
										<td>
									</tr>
								</tbody>
							</table>
							<div class="table-responsive mjschool-rtl-padding-left-40px">
								<table class="table table-bordered mjschool-model-invoice-table">
									<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_border_color_2px">
										<tr>
											<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_2px" ><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
											<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_2px" ><?php esc_html_e( 'Method', 'mjschool' ); ?></th>
											<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_2px" ><?php echo esc_html__( 'Amount', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ( $fees_history_detail_result as $retrive_date ) {
											$payment_id = mjschool_encrypt_id( $retrive_date->payment_history_id );
											?>
											<tr>
												<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
													<?php echo esc_html( mjschool_get_date_in_input_box( $retrive_date->paid_by_date ) ); ?>
												</td>
												<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
													<?php
													$data = $retrive_date->payment_method;
													echo esc_html( $data );
													?>
												</td>
												<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
													<?php echo esc_html( number_format( $retrive_date->amount, 2, '.', '' ) ); ?>
													<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=view_fesspayment&idtest=' . rawurlencode(sanitize_text_field( wp_unslash($_REQUEST['idtest']))) . '&payment_id=' . rawurlencode( $payment_id ) . '&view_type=view_receipt&_wpnonce_action=1e4d916199' ) ); ?>" class="btn btn-primary btn-sm mjschool_margin_left_10px" >
														View Receipt
													</a>
												</td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<?php
							$total_payment = 0;
							foreach ( $fees_history_detail_result as $retrive_date ) {
								$total_payment += floatval( $retrive_date->amount );
							}
							$subtotal    = $subtotal = $total_payment;
							$grand_total = $subtotal - $paid_amount;
							?>
							<div id="mjschool-res-rtl-width-100px" class="mjschool-res-rtl-width-100px mjschool-rtl-float-left row mjschool-margin-top-10px-res col-md-5 col-sm-5 col-xs-5 mjschool-view-invoice-lable-css mjschool-inovice-width-100px-rs mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total mjschool_float_margin_right_0px" >
								<div class="mjschool-width-50-res mjschool-align-right col-md-5 col-sm-5 col-xs-5 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
									<h3  class="padding mjschool-color-white margin mjschool-invoice-total-label mjschool_float_right">
										<?php esc_html_e( 'Total Payment', 'mjschool' ); ?>
									</h3>
								</div>
								<div class="mjschool-width-50-res mjschool-align-right col-md-7 col-sm-7 col-xs-7 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
									<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value">
										<?php
										$formatted_amount = number_format( $subtotal, 2, '.', '' );
										$currency         = mjschool_get_currency_symbol();
										echo esc_html( "($currency)$formatted_amount" );
										?>
									</h3>
								</div>
							</div>
							<?php
						}
						?>
						<div class="rtl-signs mjschool_fees_border_2px_margin_20px" >
							<!-- Teacher Signature (Middle). -->
							<div class="mjschool_fees_center_width_33">
								<div>
									<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" />
								</div>
								<div class="mjschool_fees_width_150px"></div>
								<div class="mjschool_margin_top_5px">
									<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
								</div>
							</div>
						</div>
						<?php
						if ( ! empty( $fees_history_detail_result ) ) {
							?>
							<div class="row">
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px mb-3">
									<div class="form-group">
										<div class="col-md-12 form-control mjschool-rtl-relative-position">
											<div class="row mjschool-padding-radio">
												<div>
													<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="smgt_enable_holiday_mail"><?php esc_html_e( 'Print With Payment History', 'mjschool' ); ?></label>
													<input type="checkbox" class="mjschool-check-box-input-margin" name="certificate_header" id="certificate_header" value="1" />&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
						<div class="col-md-12 grand_total_main_div total_mjschool-padding-15px mjschool-rtl-float-none">
							<div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-6 col-sm-6 col-xs-6 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
								<div class="col-md-2 mjschool-print-btn-rs mjschool-width-50-res">
									<a href="<?php echo esc_url('?page=mjschool_fees_payment&print=print&payment_id='. rawurlencode(sanitize_text_field( wp_unslash($_REQUEST['idtest']))) .'&fee_paymenthistory=fee_paymenthistory' ); ?>" id="exprience_latter" target="_blank" class="btn btn mjschool-save-btn mjschool-invoice-btn-div">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-print.png"); ?>">
									</a>
								</div>
								<?php
								if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field( wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
									if ( isset( $_POST['download_app_pdf'] ) ) {
										$file_path = content_url() . '/uploads/invoice_pdf/fees_payment/' . mjschool_decrypt_id( wp_unslash($_REQUEST['idtest']) ) . '.pdf';
										if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
											unlink( $file_path ); // Delete the file.
										}
										$generate_pdf = mjschool_fees_payment_pdf_for_mobile_app( wp_unslash($_REQUEST['idtest']) );
										wp_redirect( $file_path );
										die();
									}
									?>
									<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
										<form name="app_pdf1" action="" method="post">
											<div class="form-body mjschool-user-form mjschool-margin-top-40px">
												<div class="row mjschool-invoice-print-pdf-btn">
													<div class="col-md-1 mjschool-print-btn-rs">
														<button data-toggle="tooltip" name="download_app_pdf" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>"></button>
													</div>
												</div>
											</div>
										</form>
									</div>
									<?php
								} else {
									?>
									<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
										<a href="<?php echo esc_url('?page=mjschool_fees_payment&print=pdf&payment_id='. rawurlencode( wp_unslash($_REQUEST['idtest']) ).'&fee_paymenthistory=fee_paymenthistory' ); ?>" id="download_pdf" target="_blank" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>"></a>
									</div>
									<?php
								}
								?>
							</div>
						</div>
						<div class="mjschool-margin-top-20px"></div>
					</div>
				</div>
			</div>
		</div><!----- Fees Invoice. --------->
	</div><!----- Panel Body. --------->
	<?php
} else {
	$fee_pay_id   = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['payment_id']) ) );
	$fees_history = mjschool_get_single_payment_history( $fee_pay_id );
	?>
	<div class="penal-body"><!----- Panel Body. --------->
		<div id="Fees_invoice"><!----- Fees Invoice. --------->
			<div class="modal-body mjschool-border-invoice-page mjschool-margin-top-25px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool-custom-padding-0_res mjschool_height_1350px">
				<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
					<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
						<div class="row mjschool-margin-top-20px">
							<div id="rtl_heads_logo" class="mjschool-width-print mjschool-rtl-heads rtl_heads_logo mjschool_fees_style">
								<div class="mjschool_float_left_width_100">
									<div class="mjschool_float_left_width_25">
										<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
											<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>" class="mjschool_main_logo_class" class="mjschool-system-logo1" />
										</div>
									</div>
									<div class="mjschool_float_left_padding_width_75">
										<p class="mjschool_fees_widht_100_fonts_24px">
											<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
										</p>
										<p class="mjschool_fees_center_fonts_17px">
											<?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
										</p>
										<div class="mjschool_fees_center_margin_0px">
											<p class="mjschool_fees_width_fit_content_inline">
												<?php esc_html_e( 'E-mail', 'mjschool' ); ?> :
												<?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
											</p>
											<p class="mjschool_fees_width_fit_content_inline">
												&nbsp;&nbsp;
												<?php esc_html_e( 'Phone', 'mjschool' ); ?> :
												<?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 col-sm-12 col-xl-12 mjschool-mozila-display-css mjschool-margin-top-20px">
							<div class="mjschool-width-print mjschool_fees_padding_border_2px" >
								<div class="mjschool_float_left_width_100">
									<?php
									$student_id = $fees_detail_result->student_id;
									$patient    = get_userdata( $student_id );
									if ( $patient ) {
										$display_name         = isset( $patient->display_name ) ? $patient->display_name : '';
										$escaped_display_name = esc_html( ucwords( $display_name ) );
										$split_display_name   = chunk_split( $escaped_display_name, 30, '<br>' );
									} else {
										esc_html_e( 'N/A', 'mjschool' );
									}
									?>
									<div  class="mjschool_padding_10px">
										<div class="mjschool_float_left_width_65">
											<b><?php esc_html_e( 'Bill To', 'mjschool' ); ?>:</b>
											<?php echo esc_html( mjschool_student_display_name_with_roll( $student_id ) ); ?>
										</div>
										<div class="mjschool_float_right_width_35">
											<b><?php esc_html_e( 'Receipt Number', 'mjschool' ); ?>:</b>
											<?php echo esc_html( mjschool_generate_receipt_number( $fee_pay_id ) ); ?>
										</div>
									</div>
								</div>
								<div class="mjschool_float_left_width_65">
									<?php
									$student_id = $fees_detail_result->student_id;
									$patient    = get_userdata( $student_id );
									if ( $patient ) {
										$address = esc_html( get_user_meta( $student_id, 'address', true ) );
										$city    = esc_html( get_user_meta( $student_id, 'city', true ) );
										$zip     = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
										?>
										<div class="mjschool_padding_10px">
											<div>
												<b><?php esc_html_e( 'Address', 'mjschool' ); ?>:</b>
												<?php echo esc_html( $address ); ?>
											</div>
											<div>
												<?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?>
											</div>
										</div>
									<?php } ?>
								</div>
								<div class="mjschool_float_right_width_35">
									<?php
									$issue_date = 'DD-MM-YYYY';
									$issue_date = isset( $fees_history[0] ) ? $fees_history[0]->paid_by_date : '';
									?>
									<div class="mjschool_fees_padding_10px">
										<div class="mjschool_float_left_width_100">
											<b><?php esc_html_e( 'Issue Date', 'mjschool' ); ?>:</b>
											<?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
										</div>
									</div>
								</div>
								<div class="mjschool_float_right_width_35">
									<div class="mjschool_fees_padding_10px">
										<div class="mjschool_float_left_width_100">
											<b><?php esc_html_e( 'Payment Method', 'mjschool' ); ?>:</b>
											<?php echo esc_html( $fees_history[0]->payment_method ); ?>
										</div>
									</div>
								</div>
								<div class="mjschool_float_right_width_35">
									<div class="mjschool_fees_padding_10px">
										<div class="mjschool_float_left_width_100">
											<b><?php esc_html_e( 'Invoice Refrence', 'mjschool' ); ?>:</b>
											<?php echo esc_html( $invoice_number ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
						if ( ! empty( $fees_history ) ) {
							?>
							<table class="mjschool-width-100px mjschool-margin-top-10px-res mt-2">
								<tbody>
									<tr>
										<td>
											<h3 class="display_name mjschool-res-pay-his-mt-10px mjschool_fees_center_font_24px" >
												<?php esc_html_e( 'Payment Receipt', 'mjschool' ); ?>
											</h3>
										</td>
									</tr>
								</tbody>
							</table>
							<div class="mjschool_fees_padding_10px" class="mb-3">
								<div class="mjschool_float_left_width_100">
									<b><?php esc_html_e( 'Transaction Id', 'mjschool' ); ?>:</b>
									<?php echo esc_html( $fees_history[0]->trasaction_id ); ?>
								</div>
							</div>
							<?php
							$mjschool_custom_field_obj = new Mjschool_Custome_Field();
							$module                    = 'fee_transaction';
							$mjschool_custom_field_obj->mjschool_show_inserted_customfield_receipt( $module );
							?>
							<div class="table-responsive mjschool-rtl-padding-left-40px">
								<table class="table table-bordered mjschool-model-invoice-table mjschool_fees_collapse_width_100">
									<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_fees_color_border_2px" >
										<tr>
											<th class="mjschool-entry-table-heading mjschool-align-left mjschool_width_heading_70">
												<?php esc_html_e( 'Description', 'mjschool' ); ?>
											</th>
											<th class="mjschool-entry-table-heading mjschool-align-left mjschool_fees_center_width_30_border_black" >
												<?php echo esc_html__( 'Amount', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
											</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ( $fees_history as $retrive_date ) {
											?>
											<tr class="mjschool_height_230px">
												<td class="mjschool_fees_vertical_align_width_70">
													<?php
													$data = $retrive_date->payment_note;
													echo esc_html( $data );
													?>
												</td>
												<td class="mjschool_fees_vertical_align_width_30">
													<?php echo esc_html( number_format( $retrive_date->amount, 2, '.', '' ) ); ?>
												</td>
											</tr>
											<?php
										}
										?>
										<tr>
											<th class="mjschool_fees_border_2px_width_70">
												<?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
											</th>
											<th class="mjschool_fees_border_2px_width_30">
												<?php echo esc_html( number_format( $retrive_date->amount, 2, '.', '' ) ); ?>
											</th>
										</tr>
									</tbody>
								</table>
								<p class="mt-2 mjschool_width_700_font_16px" >
									<?php echo esc_html( ucfirst( mjschool_convert_number_to_words( $retrive_date->amount ) ) . ' Only' ); ?>
								</p>
							</div>
							<div class="rtl-signs mjschool_fees_padding_width_overflow_hidden">
								<!-- Teacher Signature (Middle). -->
								<div class="mjschool_fees_center_width_33">
									<div>
										<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" />
									</div>
									<div class="mjschool_fees_width_150px"></div>
									<div class="mjschool_margin_top_5px">
										<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						<div class="col-md-12 grand_total_main_div total_mjschool-padding-15px mjschool-rtl-float-none">
							<div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-6 col-sm-6 col-xs-6 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
								<div class="col-md-2 mjschool-print-btn-rs mjschool-width-50-res">
									<a href="<?php echo esc_url('?page=mjschool_fees_receipt&print=print&payment_id='. rawurlencode(wp_unslash($_REQUEST['idtest']) ).'&receipt_id='. esc_attr(  wp_unslash($_REQUEST['payment_id']) ) .'&fee_paymenthistory=fee_paymenthistory'); ?>" target="_blank" class="btn btn mjschool-save-btn mjschool-invoice-btn-div">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-print.png' ); ?>">
									</a>
								</div>
								<?php
								// check this.
								if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field( wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
									if ( isset( $_POST['download_app_pdf'] ) ) {
										$file_path = content_url() . '/uploads/invoice_pdf/fees_payment/' . mjschool_decrypt_id(  wp_unslash($_REQUEST['idtest']) ) . '.pdf';
										if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
											unlink( $file_path ); // Delete the file.
										}
										$generate_pdf = mjschool_fees_receipt_pdf_for_mobile_app(  wp_unslash($_REQUEST['idtest']),  wp_unslash($_REQUEST['payment_id']) );
										wp_redirect( $file_path );
										die();
									}
									?>
									<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
										<form name="app_pdf2" action="" method="post">
											<div class="form-body mjschool-user-form mjschool-margin-top-40px">
												<div class="row mjschool-invoice-print-pdf-btn">
													<div class="col-md-1 mjschool-print-btn-rs">
														<button data-toggle="tooltip" name="download_app_pdf" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>"></button>
													</div>
												</div>
											</div>
										</form>
									</div>
									<?php
								} else {
									?>
									<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
										<a href="<?php echo esc_url('?page=mjschool_fees_payment&print=pdf&payment_id='. rawurlencode( wp_unslash($_REQUEST['idtest'])).'&fee_paymenthistory=fee_paymenthistory'); ?>" target="_blank" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-pdf.png"); ?>">
										</a>
									</div>
									<?php
								}
								?>
							</div>
						</div>
						<div class="mjschool-margin-top-20px"></div>
					</div>
				</div>
			</div>
		</div><!----- Fees Invoice. --------->
	</div><!----- Panel Body. --------->
<?php } ?>