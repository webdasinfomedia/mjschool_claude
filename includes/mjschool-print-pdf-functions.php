<?php
/**
 * The functions of  the Print and PDFs.
 *
 * This file loads required functions, and handles the
 * print and pdf features and helper functions.
 *
 * @since      1.0.0
 * @package    MJSCHOOL
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the student fees invoice in HTML format.
 *
 * Retrieves fee payment details, invoice number, student data, and renders
 * the complete printable invoice layout based on the selected invoice format.
 * This function also handles RTL styles, tax/discount calculations, and
 * payment status display.
 *
 * @since 1.0.0
 *
 * @param int $fees_pay_id Encrypted fees payment ID.
 *
 * @return void
 */
function mjschool_student_fees_invoice_print( $fees_pay_id ) {
	wp_print_styles();
	$format                     = get_option( 'mjschool_invoice_option' );
	$fees_pay_id                = intval( mjschool_decrypt_id( $fees_pay_id ) );
	$fees_detail_result         = mjschool_get_single_fees_payment_record( $fees_pay_id );
	$fees_history_detail_result = mjschool_get_payment_history_by_feespayid( $fees_pay_id );
	$invoice_number             = mjschool_generate_invoice_number( $fees_pay_id );
	$obj_feespayment            = new mjschool_feespayment();
	if ( is_rtl() ) {
		?>
		<style>
			.rtl_billto {
				margin-right: -18px;
			}
			.new-rtl-padding-fix {
				padding-left: 12px !important;
			}
			.rtl_sings {
				width: 98% !important;
				margin-left: 2% !important;
			}
		</style>
		<?php
	} 
	?>
	<style>
		body,
		body * {
			font-family: 'Poppins' !important;
		}
		table thead {
			-webkit-print-color-adjust: exact;
		}
		.mjschool-invoice-table-grand-total {
			-webkit-print-color-adjust: exact;
			background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;
		}
		@media print {
			* {
				color-adjust: exact !important;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}
			.invoice_description {
				width: 75%;
			}
			.mjschool_invoce_notice {
				width: 100%;
				float: left;
			}
		}
	</style>
	<div id="Fees_invoice">
		<div class="modal-body mjschool-margin-top-15px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool-custom-padding-0_res height_1000px">
			<?php if ( $format === 0 ) { 
				
				if (is_rtl( ) ) {
					?>
					<img class="mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url(plugins_url( '/mjschool/assets/images/listpage_icon/invoice_rtl.png' ) ); ?>" width="100%">
					<?php
				} else {
					?>
					<img class="mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url(plugins_url( '/mjschool/assets/images/listpage_icon/invoice.png' ) ); ?>" width="100%">
					<?php
				}
			}
			?>
			<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
				<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
					<div class="row mjschool_margin_right_0px" >
						<?php if ($format === 1) { ?>
							<div class="mjschool-width-print mjschool_border_print_width_98" >
								<div class="mjschool_float_left_width_100">
									<div class="mjschool_float_left_width_25">
										<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
											<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
										</div>
									</div>
									<div class="mjschool_float_left_width_75">
										<p class="mjschool_fees_widht_100_fonts_24px"> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </p>
										<p class="mjschool_print_invoice_line_height_30px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?> </p>
										<div class="mjschool_fees_center_margin_0px">
											<p class="mjschool_receipt_print_margin_0px"> <?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?> </p>
											<p class="mjschool_receipt_print_margin_0px"> &nbsp;&nbsp; <?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?> </p>
										</div>
									</div>
								</div>
							</div>
						<?php } else { ?>
							<h3 > <?php echo esc_html( get_option( 'mjschool_name' ) ) ?> </h3>
							<div class="col-md-1 col-sm-2 col-xs-3 mjschool_widht_10" >
								<div class="width_1">
									<img class="system_logo" <?php if (is_rtl( ) ) { ?> style="float:unset;" <?php } ?> src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
								</div>
								
							</div>
							<div class="mjschool_widht_90 col-md-11 col-sm-10 col-xs-9 mjschool-invoice-address mjschool-invoice-address-css">
								<div class="row">
									<div class="col-md-12 col-sm-12 col-xs-12 mjschool-invoice-padding-bottom-15px mjschool-padding-right-0 mjschool-width-25px-res">
										<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Address', 'mjschool' ); ?> </label><br>
										<label  class="mjschool_padding_top_10px mjschool-label-value mjschool-word-break-all">
											<?php
											$school_address   = get_option( 'mjschool_address' );
											$escaped_address  = esc_html( $school_address );
											$split_address    = chunk_split( $escaped_address, 100, '<br>' );
											$formatted_output = str_replace( '<br>', '<BR>', $split_address );
											echo wp_kses_post( $formatted_output );
											?>
										</label>
									</div>
									<div class="row col-md-12 mjschool-invoice-padding-bottom-15px">
										<div class="mjschool_width_50 col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-email-width-auto">
											<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Email', 'mjschool' ); ?> </label><br>
											<label class="mjschool_padding_top_10px mjschool-label-value mjschool-word-break-all"> <?php echo esc_html( get_option( 'mjschool_email' ) ), '<BR>'; ?> </label>
										</div>
										<div class="mjschool_width_50 col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-padding-left-30px">
											<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Phone', 'mjschool' ); ?> </label><br>
											<label class="mjschool_padding_top_10px mjschool-label-value"> <?php echo esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>'; ?> </label>
										</div>
									</div>
									<div align="right" class="mjschool-width-24px"></div>
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="col-md-12 col-sm-12 col-xl-12 mjschool-mozila-display-css mjschool-margin-top-10px" >
						<?php if ( $format === 1 ) { ?>
							<div class="mjschool-width-print rtl_billto mjschool_print_padding_bottom_top_border_2px" >
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
											<b> <?php esc_html_e( 'Bill To', 'mjschool' ); ?>: </b>&nbsp; <?php echo esc_html( mjschool_student_display_name_with_roll( $student_id ) ); ?>
										</div>
										<div class="mjschool_float_right_width_35">
											<b> <?php esc_html_e( 'Invoice Number', 'mjschool' ); ?>: </b> <?php echo esc_html( $invoice_number ); ?>
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
												<b> <?php esc_html_e( 'Address', 'mjschool' ); ?>: </b> <?php echo esc_html( $address ); ?>
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
									<div  class="mjschool_padding_0_10px">
										<div class="mjschool_float_left_width_100">
											<b><?php esc_html_e( 'Issue Date', 'mjschool' ); ?>:</b>
											<?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
										</div>
									</div>
								</div>
								<div class="mjschool_float_right_width_35">
									<div class="mjschool_padding_top_10px">
										<b> <?php esc_html_e( 'Status', 'mjschool' ); ?>: </b>
										<?php
										$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
										if ( $payment_status === 'Fully Paid' ) {
											echo '<span class="mjschool-green-color">' . esc_attr__( 'Fully Paid', 'mjschool' ) . '</span>';
										}
										if ( $payment_status === 'Partially Paid' ) {
											echo '<span class="mjschool-purpal-color">' . esc_attr__( 'Partially Paid', 'mjschool' ) . '</span>';
										}
										if ( $payment_status === 'Not Paid' ) {
											echo '<span class="mjschool-red-color">' . esc_attr__( 'Not Paid', 'mjschool' ) . '</span>';
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
										<div class="mjschool-billed-to mjschool-display-flex mjschool-display-inherit-res mjschool-invoice-address-heading mjschool_float_left_width_100" >
											<h3 class="mjschool-billed-to-lable mjschool-invoice-model-heading mjschool-bill-to-width-12px mjschool_width_250px" > <?php esc_html_e( 'Bill To', 'mjschool' ); ?> : </h3>
											<?php
											$student_id = $fees_detail_result->student_id;
											$patient    = get_userdata( $student_id );
											if ( $patient ) {
												$display_name = esc_html( ucwords( $patient->display_name ) );
												$split_name   = str_replace( '<br>', '<BR>', chunk_split( $display_name, 100, '<br>' ) );
												echo '<h3 class="display_name mjschool-invoice-width-100px mjschool_float_right_width_100">' . esc_html( mjschool_student_display_name_with_roll( $student_id ) ) . '</h3>';
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
									<div class="col-md-3 col-sm-4 col-xs-7 mjschool-float-left mjschool-right">
										<div class="mjschool-width-50px">
											<div class="mjschool-width-20px" align="center">
												<h5 class="mjschool-align-left"> 
													<label class="mjschool-popup-label-heading text-transfer-upercase"> <?php echo esc_html__( 'Invoice Number :', 'mjschool' ); ?> </label>&nbsp; 
													<label class="mjschool-invoice-model-value"> <?php echo esc_html( $invoice_number ); ?> </label>
												</h5>
												<?php
												$issue_date     = 'DD-MM-YYYY';
												$issue_date     = $fees_detail_result->paid_by_date;
												$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
												?>
												<h5 class="mjschool-align-left"> 
													<label class="mjschool-popup-label-heading text-transfer-upercase"> <?php echo esc_html__( 'Date :', 'mjschool' ); ?> </label>&nbsp; 
													<label class="mjschool-invoice-model-value"> <?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?> </label>
												</h5>
												<h5 class="mjschool-align-left">
													<label class="mjschool-popup-label-heading text-transfer-upercase"> <?php echo esc_html__( 'Status :', 'mjschool' ); ?> </label> &nbsp;
													<label class="mjschool-invoice-model-value">
														<?php
														if ( $payment_status === 'Fully Paid' ) {
															echo '<span class="mjschool-green-color">' . esc_attr__( 'Fully Paid', 'mjschool' ) . '</span>';
														}
														if ( $payment_status === 'Partially Paid' ) {
															echo '<span class="mjschool-purpal-color">' . esc_attr__( 'Partially Paid', 'mjschool' ) . '</span>';
														}
														if ( $payment_status === 'Not Paid' ) {
															echo '<span class="mjschool-red-color">' . esc_attr__( 'Not Paid', 'mjschool' ) . '</span>';
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
					<table class="mjschool-width-100px mjschool-margin-top-10px-res">
						<tbody>
							<tr>
								<td>
									<h3 class="display_name mjschool_margin_bottom_1px" > <?php esc_html_e( 'Invoice Entries', 'mjschool' ); ?> </h3>
								<td>
							</tr>
						</tbody>
					</table>
					<?php if ( $format === 1 ) { ?>
						<div class="table-responsive mjschool-rtl-padding-left-40px new-rtl-padding-fix">
							<table class="table table-bordered mjschool-model-invoice-table mjschool-margin-bottom-0px">
								<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_print_bgcolor_border_1px">
									<tr>
										<th class="mjschool-entry-table-heading mjschool-align-left mjschool_print_bgcolor_border"> Number</th>
										<th class="mjschool-entry-table-heading mjschool-align-left mjschool_print_bgcolor_border_20"> <?php esc_html_e( 'Date', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-left mjschool_print_bgcolor_border" > <?php esc_html_e( 'Fees Type', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-left mjschool_print_bgcolor_border_15" > <?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?> </th>
									</tr>
								</thead>
								<tbody>
									<?php
									$fees_id = explode( ',', $fees_detail_result->fees_id );
									$x       = 1;
									$amounts = 0;
									foreach ( $fees_id as $id ) {
										?>
										<tr>
											<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_font_14px" > <?php echo esc_html( $x ); ?> </td>
											<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_font_14px" > <?php echo esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ); ?> </td>
											<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_font_14px" > <?php echo esc_html( mjschool_get_fees_term_name( $id ) ); ?> </td>
											<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_font_14px" >
												<?php
												$amount   = $obj_feespayment->mjschool_feetype_amount_data( $id );
												$amounts += $amount;
												echo esc_html( number_format( $amount, 2, '.', '' ) );
												?>
											</td>
										</tr>
										<?php
										++$x;
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
						<div class="table-responsive mjschool-padding-bottom-15px">
							<table class="table mjschool-model-invoice-table">
								<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading">
									<tr>
										<th class="mjschool-entry-table-heading <?php if ( ! is_rtl() ) { ?> mjschool-align-left <?php } ?>"> #</th>
										<th class="mjschool-entry-table-heading <?php if ( ! is_rtl() ) { ?> mjschool-align-left <?php } ?>"> <?php esc_html_e( 'Date', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading <?php if ( ! is_rtl() ) { ?> mjschool-align-left <?php } ?>"> <?php esc_html_e( 'Fees Type', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading <?php if ( ! is_rtl() ) { ?> mjschool-align-left <?php } ?>"> <?php esc_html_e( 'Total', 'mjschool' ); ?> </th>
									</tr>
								</thead>
								<tbody>
									<?php
									$fees_id = explode( ',', $fees_detail_result->fees_id );
									$x       = 1;
									$amounts = 0;
									foreach ( $fees_id as $id ) {
										?>
										<tr>
											<td class="<?php if ( ! is_rtl() ) { ?> mjschool-align-left <?php } ?> mjschool-invoice-table-data"> <?php echo esc_html( $x ); ?> </td>
											<td class="<?php if ( ! is_rtl() ) { ?> mjschool-align-left <?php } ?> mjschool-invoice-table-data"> <?php echo esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ); ?> </td>
											<td class="<?php if ( ! is_rtl() ) { ?> mjschool-align-left <?php } ?> mjschool-invoice-table-data"> <?php echo esc_html( mjschool_get_fees_term_name( $id ) ); ?> </td>
											<td class="<?php if ( ! is_rtl() ) { ?> mjschool-align-left <?php } ?> mjschool-invoice-table-data">
												<?php
												$amount   = $obj_feespayment->mjschool_feetype_amount_data( $id );
												$amounts += $amount;
												echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $amount, 2, '.', '' ) ) );
												?>
											</td>
										</tr>
										<?php
										++$x;
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
					<?php }
					if ( is_rtl() ) {
						$align = 'left';
					} else {
						$align = 'right';
					}
					if ( $format === 1 ) {
						?>
						<div class="table-responsive mjschool-rtl-padding-left-40px mjschool-rtl-float-left-width-100px new-rtl-padding-fix">
							<table class="table table-bordered mjschool_collapse_width_100" >
								<tbody>
									<tr>
										<th style="width: 85%; text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px !important;" scope="row"> <?php echo esc_html__( 'Sub Total', 'mjschool' ) . ' :'; ?> </th>
										<td style="width: 15%; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px !important;"> <?php echo esc_html( number_format( $sub_total, 2, '.', '' ) ); ?> </td>
									</tr>
									<?php if ( isset( $fees_detail_result->discount_amount ) && ( $fees_detail_result->discount_amount ) != 0 ) { ?>
										<tr>
											<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px !important;" scope="row"> <?php echo esc_html__( 'Discount Amount', 'mjschool' ) . ' ( ' . esc_html( $discount_name ) . ' ) :'; ?> </th>
											<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px !important;"> <?php echo '-' . esc_html( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ); ?> </td>
										</tr>
									<?php } ?>
									<?php if ( isset( $fees_detail_result->tax_amount ) && ( $fees_detail_result->tax_amount ) != 0 ) { ?>
										<tr>
											<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px !important;" scope="row"> <?php echo esc_html__( 'Tax Amount', 'mjschool' ) . ' ( ' . esc_html( $tax_name ) . ' ) :'; ?> </th>
											<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px !important;"> <?php echo '+' . esc_html( number_format( $fees_detail_result->tax_amount, 2, '.', '' ) ); ?> </td>
										</tr>
									<?php } ?>
									<tr>
										<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px !important;" scope="row"> <?php echo esc_html__( 'Payment Made :', 'mjschool' ); ?> </th>
										<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; color: #d9534f; font-size: 14px !important;"> <?php echo esc_html( number_format( $fees_detail_result->fees_paid_amount, 2, '.', '' ) ); ?> </td>
									</tr>
									<tr>
										<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px !important;" scope="row"> <?php echo esc_html__( 'Due Amount :', 'mjschool' ); ?> </th>
										<?php $Due_amount = $fees_detail_result->total_amount - $fees_detail_result->fees_paid_amount; ?>
										<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; color: #d9534f; font-size: 14px !important;"> <?php echo esc_html( number_format( $Due_amount, 2, '.', '' ) ); ?> </td>
									</tr>
								</tbody>
							</table>
						</div>
						<?php
					} else {
						?>
						<div class="table-responsive">
							<table width="100%" border="0">
								<tbody>
									<tr >
										<td align="<?php echo esc_html( $align ); ?>" class="mjschool-padding-bottom-15px mjschool-total-heading"> <?php esc_html_e( 'Sub Total :', 'mjschool' ); ?> </td>
										<td align="<?php echo esc_html( $align ); ?>" class="mjschool-padding-bottom-15px mjschool-total-value"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $sub_total, 2, '.', '' ) ) ); ?> </td>
									</tr>
									<?php
									if ( isset( $fees_detail_result->discount_amount ) && ( $fees_detail_result->discount_amount != 0 ) ) {
										?>
										<tr>
											<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="<?php echo esc_html( $align ); ?>"> <?php echo esc_attr__( 'Discount Amount', 'mjschool' ) . '( ' . esc_html( $discount_name ) . ' )' . '  :'; ?> </td>
											<td align="<?php echo esc_html( $align ); ?>" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value"> <?php echo '-' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ) ); ?> </td>
										</tr>
										<?php
									}
									?>
									<?php
									if ( isset( $fees_detail_result->tax_amount ) && ( $fees_detail_result->tax_amount != 0 ) ) {
										?>
										<tr>
											<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="<?php echo esc_html( $align ); ?>"> <?php echo esc_attr__( 'Tax Amount', 'mjschool' ) . '( ' . esc_html( $tax_name ) . ' )' . '  :'; ?> </td>
											<td align="<?php echo esc_html( $align ); ?>" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value"> <?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->tax_amount, 2, '.', '' ) ) ); ?> </td>
										</tr>
										<?php
									}
									?>
									<tr>
										<td width="85%" class="mjschool-padding-bottom-15px mjschool-total-heading" align="<?php echo esc_html( $align ); ?>"> <?php esc_html_e( 'Payment Made :', 'mjschool' ); ?> </td>
										<td align="<?php echo esc_html( $align ); ?>" class="mjschool-padding-bottom-15px mjschool-total-value"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->fees_paid_amount, 2, '.', '' ) ) ); ?> </td>
									</tr>
									<tr>
										<td width="85%" class="mjschool-padding-bottom-15px mjschool-total-heading" align="<?php echo esc_html( $align ); ?>"> <?php esc_html_e( 'Due Amount :', 'mjschool' ); ?> </td>
										<?php
										$Due_amount = $fees_detail_result->total_amount - $fees_detail_result->fees_paid_amount;
										?>
										<td align="<?php echo esc_html( $align ); ?>" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Due_amount, 2, '.', '' ) ) ); ?> </td>
									</tr>
								</tbody>
							</table>
						</div>
					<?php }
					$subtotal    = $fees_detail_result->total_amount;
					$paid_amount = $fees_detail_result->fees_paid_amount;
					$grand_total = $subtotal - $paid_amount;
					?>
					<div class="row mjschool-margin-top-10px-res col-md-6 col-sm-6 col-xs-6 mjschool-view-invoice-lable-css mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total" style="width:50%;float:<?php echo esc_attr( $align ); ?> !important;display:inline-block;margin-right:0px;">
						<div class="mjschool_width_50 mjschool-width-50-res mjschool-align-right col-md-8 col-sm-8 col-xs-8 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
							<h3  class="padding mjschool-color-white margin mjschool-invoice-total-label mjschool_float_right"> <?php esc_html_e( 'Grand Total', 'mjschool' ); ?> </h3>
						</div>
						<div class="mjschool_width_50 mjschool-width-50-res mjschool-align-right col-md-4 col-sm-4 col-xs-4 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
							<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value mjschool_float_right" >
								<?php
								$formatted_amount = number_format( $subtotal, 2, '.', '' );
								$currency         = mjschool_get_currency_symbol();
								echo esc_html( "($currency)$formatted_amount" );
								?>
							</h3>
						</div>
					</div>
					<?php
					if ( ! empty( $fees_history_detail_result ) && isset( $_REQUEST['certificate_header']) && sanitize_text_field(wp_unslash($_REQUEST['certificate_header'])) === 1 ) {
						?>
						<table class="mjschool-width-100px mjschool-margin-top-10px-res">
							<tbody>
								<tr>
									<td>
										<h3 class="display_name mjschool-res-pay-his-mt-10px"> <?php esc_html_e( 'Payment History', 'mjschool' ); ?> </h3>
									<td>
								</tr>
							</tbody>
						</table>
						<div class="table-responsive mjschool-rtl-padding-left-40px">
							<table class="table table-bordered mjschool-model-invoice-table">
								<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_white_black_color" >
									<tr>
										<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_1px" > <?php esc_html_e( 'Date', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_1px" > <?php esc_html_e( 'Method', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_1px" > <?php echo esc_html__( 'Amount', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?> </th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ( $fees_history_detail_result as $retrive_date ) {
										?>
										<tr>
											<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_1px mjschool_border_black_1px"> <?php echo esc_html( mjschool_get_date_in_input_box( $retrive_date->paid_by_date ) ); ?> </td>
											<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_1px">
												<?php
												$data = $retrive_date->payment_method;
												echo esc_html( $data );
												?>
											</td>
											<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( number_format( $retrive_date->amount, 2, '.', '' ) ); ?> </td>
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
						<div id="mjschool-res-rtl-width-100px" class="mjschool-res-rtl-width-100px mjschool-rtl-float-left row mjschool-margin-top-10px-res col-md-5 col-sm-5 col-xs-5 mjschool-view-invoice-lable-css mjschool-inovice-width-100px-rs mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total mjschool_right_52" >
							<div class="mjschool-width-50-res mjschool-align-right col-md-5 col-sm-5 col-xs-5 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
								<h3  class="padding mjschool-color-white margin mjschool-invoice-total-label mjschool_float_right"> <?php esc_html_e( 'Total Payment', 'mjschool' ); ?> </h3>
							</div>
							<div class="mjschool-width-50-res mjschool-align-right col-md-7 col-sm-7 col-xs-7 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
								<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value mjschool-text-align-end" >
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
					<div class="rtl_sings mjschool_print_avoid_left_border_2px" >
						<!-- Teacher Signature (Middle) -->
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
				</div>
			</div>
		</div>
	</div>
	<?php
}







/**
 * Handles printing operations for fee-related invoices and receipts.
 *
 * Based on the request parameters, this function loads necessary print styles,
 * triggers the print dialog, and outputs the correct template:
 * - Fee invoice print
 * - Fee receipt print
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_print_fees_invoice() {
	$sanitize_print = isset($_REQUEST['print']) ? sanitize_text_field(wp_unslash($_REQUEST['print'])) : '';
	$sanitize_page = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
	if ( $sanitize_print === 'print' && $sanitize_page === 'mjschool_fees_payment' ) {
		if ( is_rtl() ) 
		{
			wp_enqueue_style( 'bootstrap-rtl', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.rtl.min.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-custome-rtl', plugins_url( '/assets/css/mjschool-custome-rtl.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-newdesign-rtl', plugins_url( '/assets/css/mjschool-new-design-rtl.css', __FILE__ ) );
		}
		wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-new-design', plugins_url( '/assets/css/mjschool-smgt-new-design.css', __FILE__ ) );
		wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
		wp_enqueue_style( 'buttons-dataTables', plugins_url( '/assets/css/third-party-css/buttons.dataTables.min.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-poppins-fontfamily', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
		?>
		<script type="text/javascript">
			(function() {
				"use strict";
				function mjschool_print_with_delay() {
					setTimeout(function() {
						window.print();
					}, 500); // 500ms delay to ensure content is rendered.
				}
				window.addEventListener( 'load', mjschool_print_with_delay);
			})();
		</script>
		<?php
		mjschool_student_fees_invoice_print( intval(wp_unslash($_REQUEST['payment_id'])) );
		die();
	}
	if ( $sanitize_print === 'print' && $sanitize_page === 'mjschool_fees_receipt' ) {
		if ( is_rtl() ) 
		{
			wp_enqueue_style( 'bootstrap-rtl', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.rtl.min.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-custome-rtl', plugins_url( '/assets/css/mjschool-custome-rtl.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-newdesign-rtl', plugins_url( '/assets/css/mjschool-new-design-rtl.css', __FILE__ ) );
		}
		wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-new-design', plugins_url( '/assets/css/mjschool-smgt-new-design.css', __FILE__ ) );
		wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
		wp_enqueue_style( 'buttons-dataTables', plugins_url( '/assets/css/third-party-css/buttons.dataTables.min.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-poppins-fontfamily', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
		?>
		<script type="text/javascript">
			(function() {
				"use strict";
				function mjschool_print_with_delay() {
					setTimeout(function() {
						window.print();
					}, 500); // Delay ensures content is fully rendered.
				}
				window.addEventListener( 'load', mjschool_print_with_delay);
			})();
		</script>
		<?php
		mjschool_student_fees_receipt_print( intval(wp_unslash($_REQUEST['payment_id'])) );
		die();
	}
}
add_action( 'init', 'mjschool_print_fees_invoice' );

/**
 * Handles the exam receipt print action and triggers browser print.
 *
 * When the `student_exam_receipt` request parameter is detected, it triggers
 * the browser's print dialog and renders the exam receipt for the specified student.
 *
 * @since 1.0.0
 *
 * @return void Outputs print script and renders the receipt content.
 */
function mjschool_print_exam_receipt() {
	if ( isset( $_REQUEST['student_exam_receipt'] ) && sanitize_text_field(wp_unslash($_REQUEST['student_exam_receipt'])) === 'student_exam_receipt' ) {
		?>
		<script type="text/javascript">
			(function() {
				"use strict";
				window.addEventListener( 'load', function() {
					window.print();
				});
			})();
		</script>
		<?php
		mjschool_student_exam_receipt_print( sanitize_text_field(wp_unslash($_REQUEST['student_id'])), intval(wp_unslash($_REQUEST['exam_id'])) );
		die();
	}
}
add_action( 'init', 'mjschool_print_exam_receipt' );

/**
 * Generates and displays the examination hall ticket receipt for a student.
 *
 * This function decrypts the provided student and exam IDs,
 * retrieves all required student, exam, hall, and timetable details,
 * and renders the complete hall ticket in printable format
 * (supports RTL and LTR layouts).
 *
 * @since 1.0.0
 *
 * @param string $student_id Encrypted student ID.
 * @param string $exam_id    Encrypted exam ID.
 *
 * @return void
 */
function mjschool_student_exam_receipt_print( $student_id, $exam_id ) {
	$student_id      = intval( mjschool_decrypt_id( $student_id ) );
	$exam_id         = intval( mjschool_decrypt_id( $exam_id ) );
	$student_data    = get_userdata( $student_id );
	$umetadata       = mjschool_get_user_image( $student_id );
	$exam_data       = mjschool_get_exam_by_id( $exam_id );
	$exam_hall_data  = mjschool_get_exam_hall_name( $student_id, $exam_id );
	$exam_hall_name  = mjschool_get_hall_name( $exam_hall_data->hall_id );
	$obj_exam        = new mjschool_exam();
	$exam_time_table = $obj_exam->mjschool_get_exam_time_table_by_exam( $exam_id );
	?>
	<style>
		@media print {
			* {
				color-adjust: exact !important;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}
			.mjschool-width-print {
				font-family: Poppins;
			}
			table,
			.header,
			span.sign {
				font-family: Poppins;
				font-size: 12px;
				color: #444;
			}
			.borderpx {
				border: 2px solid #97C4E7;
			}
			.count td,
			.count th {
				padding-left: 10px;
				height: 40px;
			}
			.resultdate {
				float: left;
				width: 200px;
				padding-top: 100px;
				text-align: center;
			}
			.mjschool-th-margin {
				padding-left: 0px !important;
			}
			.signature {
				float: right;
				width: 200px;
				padding-top: 55px;
				text-align: center;
			}
			.exam_receipt_print {
				width: 90%;
				margin: 0 auto;
			}
			.header_logo {
				float: left;
				width: 100%;
				text-align: center;
			}
			.font_22 {
				font-size: 22px;
			}
			.mjschool-Examination-header {
				float: left;
				width: 100%;
				font-size: 18px;
				text-align: center;
				padding-bottom: 20px;
			}
			.mjschool-Examination-header-color {
				color: #970606;
			}
			.mjschool-float-width {
				float: left;
				width: 100%;
			}
			.mjschool-padding-top-20 {
				padding-top: 20px;
			}
			.mjschool-img-td {
				text-align: center;
				border-right: 2px solid #97C4E7;
			}
			.mjschool-border-bottom {
				border-bottom: 1px solid #97C4E7;
			}
			.mjschool-border-bottom-0 {
				border-bottom: 0px;
			}
			.mjschool-border-bottom-rigth {
				border-bottom: 1px solid #97C4E7;
				border-right: 1px solid #97C4E7;
			}
			.mjschool-border-rigth {
				border-right: 1px solid #97C4E7;
			}
			.mjschool-main-td {
				text-align: center;
				border-bottom: 1px solid #97C4E7;
			}
			.hr_color {
				color: #97C4E7;
			}
			.header_color {
				color: #204759;
			}
			.max_height_100 {
				max-height: 100px;
			}
		}
		table,
		.header,
		span.sign {
			font-family: Poppins;
			font-size: 12px;
			color: #444;
		}
		.borderpx {
			border: 2px solid #97C4E7;
		}
		.count td,
		.count th {
			padding-left: 10px;
			height: 40px;
		}
		.resultdate {
			float: left;
			width: 200px;
			padding-top: 100px;
			text-align: center;
		}
		.mjschool-th-margin {
			padding-left: 0px !important;
		}
		.signature {
			float: right;
			width: 200px;
			padding-top: 55px;
			text-align: center;
		}
		.exam_receipt_print {
			width: 100%;
			margin: 0 auto;
		}
		.mjschool-width-print {
			width: 94% !important;
		}
		.header_logo {
			float: left;
			width: 100%;
			text-align: center;
		}
		.font_22 {
			font-size: 22px;
		}
		.mjschool-Examination-header {
			float: left;
			width: 100%;
			font-size: 18px;
			text-align: center;
			padding-bottom: 20px;
		}
		.mjschool-Examination-header-color {
			color: #970606;
		}
		.mjschool-float-width {
			float: left;
			width: 100%;
		}
		.mjschool-padding-top-20 {
			padding-top: 20px;
		}
		.mjschool-img-td {
			text-align: center;
			border-right: 2px solid #97C4E7;
		}
		.mjschool-border-bottom {
			border-bottom: 2px solid #97C4E7;
		}
		.mjschool-border-bottom-0 {
			border-bottom: 0px;
		}
		.mjschool-border-bottom-rigth {
			border-bottom: 2px solid #97C4E7;
			border-right: 2px solid #97C4E7;
		}
		.mjschool-border-rigth {
			border-right: 2px solid #97C4E7;
		}
		.mjschool-main-td {
			text-align: center;
			border-bottom: 2px solid #97C4E7;
		}
		.hr_color {
			color: #97C4E7;
		}
		.header_color {
			color: #204759;
		}
		.max_height_100 {
			max-height: 100px;
		}
	</style>
	<?php
	if ( is_rtl() ) {
		?>
		<div class="modal-body mjschool_direction_rtl">
			<div id="exam_receipt_print" class="exam_receipt_print">
				<div class="mjschool_margin_bottom_8px">
					<div class="mjschool-width-print mjschool_border_2px_width_96" >
						<div class="mjschool_float_left_width_100">
							<div class="mjschool_float_left_width_25">
								
								<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
									<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
								</div>
							</div>
							<div class="mjschool_float_left_width_75">
								<p class="mjschool_fees_widht_100_fonts_24px">
									<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
								</p>
								<p class="mjschool_fees_center_fonts_17px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?> </p>
								<div class="mjschool_fees_center_margin_0px">
									<p class="mjschool_fees_width_fit_content_inline">
										<?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
									</p>
									<p class="mjschool_fees_width_fit_content_inline">
										&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="header mjschool-Examination-header mjschool-margin-top-10px" >
					<span><strong class="mjschool-Examination-header-color"><?php esc_html_e( 'Examination Hall Ticket', 'mjschool' ); ?></strong></span>
				</div>
				<div class="mjschool-float-width">
					<table width="100%" class="count borderpx" cellspacing="0" cellpadding="0">
						<thead>
						</thead>
						<tbody>
							<tr>
								<td rowspan="4" class="mjschool-img-td">
									<?php
									if (empty($umetadata['meta_value'] ) ) { ?>
										<img src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>" width="100px" height="100px">
										<?php
									} else {
										?>
										<img src="<?php echo esc_url($umetadata['meta_value']); ?>" width="100px" height="100px">
										<?php
									}
									?>
								</td>
								
								<td colspan="2" class="mjschool-border-bottom"> <strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->display_name ); ?></a> </td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-rigth" align="left"> <strong><?php esc_html_e( 'Roll Number', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->roll_id ); ?> </td>
								<td class="mjschool-border-bottom" align="left"> <strong><?php esc_html_e( 'Exam Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $exam_data->exam_name ); ?> </td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-rigth" align="left"> <strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></strong><?php echo esc_html( mjschool_get_class_name( $student_data->class_name ) ); ?> </td>
								<td class="mjschool-border-bottom" align="left">
									<strong><?php esc_html_e( 'Section Name', 'mjschool' ); ?> : </strong>
									<?php
									$section_name = $student_data->class_section;
									if ( $section_name != '' ) {
										echo esc_html( mjschool_get_section_name( $section_name ) );
									} else {
										esc_html_e( 'No Section', 'mjschool' );
									}
									?>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-rigth" align="left"> <strong><?php esc_html_e( 'Start Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) ); ?> </td>
								<td class="mjschool-border-bottom-0" align="left"> <strong><?php esc_html_e( 'End Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) ); ?> </td>
							</tr>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="mjschool-padding-top-20 mjschool-float-width">
					<table width="100%" class="count borderpx" cellspacing="0" cellpadding="0">
						<thead>
						</thead>
						<tbody>
							<tr>
								<td class="mjschool-border-bottom">
									<strong><?php esc_html_e( 'Examination Centre', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( $exam_hall_name ); ?>,
									<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-0">
									<strong><?php esc_html_e( 'Examination Centre Address', 'mjschool' ); ?> : </strong><?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
								</td>
							</tr>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="mjschool-padding-top-20 mjschool-float-width">
					<table width="100%" class="count borderpx" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th colspan="5" class="mjschool-border-bottom"> <?php esc_html_e( 'Time Table For Exam Hall', 'mjschool' ); ?> </th>
							</tr>
							<tr>
								<th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php esc_html_e( 'Exam Time', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php esc_html_e( 'Examiner Sign.', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $exam_time_table ) ) {
								foreach ( $exam_time_table as $retrieved_data ) {
									?>
									<tr>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php echo esc_html( mjschool_get_single_subject_code( $retrieved_data->subject_id ) ); ?></td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject_id ) ); ?></td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_date ) ); ?></td>
										<?php
										$start_time_data = explode( ':', $retrieved_data->start_time );
										$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
										$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
										$start_am_pm     = $start_time_data[2];
										$start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
										$end_time_data   = explode( ':', $retrieved_data->end_time );
										$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
										$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
										$end_am_pm       = $end_time_data[2];
										$end_time        = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
										?>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin">
											<?php echo esc_html( $start_time ); ?>
											<?php esc_html_e( 'To', 'mjschool' ); ?>
											<?php echo esc_html( $end_time ); ?>
										</td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"></td>
									</tr>
									<?php
								}
							} else {
								?>
								<tr>
									<td class="mjschool-main-td" colspan="5"> <?php esc_html_e( 'No Data Available', 'mjschool' ); ?> </td>
								</tr>
								<?php
							}
							?>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="resultdate">
					<hr color="#97C4E7">
					<span><?php esc_html_e( 'Student Signature', 'mjschool' ); ?></span>
				</div>
				<div class="signature">
					
					<span> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px; margin-right:15px;" /> </span>
					<hr color="#97C4E7">
					<span><?php esc_html_e( 'Authorized Signature', 'mjschool' ); ?></span>
				</div>
			</div>
		</div>
		<!---RTL ENDS-->
		<?php
	} else {
		?>
		<div class="modal-body">
			<div id="exam_receipt_print" class="exam_receipt_print">
				<div class="mjschool_margin_bottom_8px">
					<div class="mjschool-width-print mjschool_border_2px_width_96" >
						<div class="mjschool_float_left_width_100">
							<div class="mjschool_float_left_width_25">
								<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
									<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
								</div>
							</div>
							<div class="mjschool_float_left_width_75">
								<p class="mjschool_fees_widht_100_fonts_24px"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
								<p class="mjschool_fees_center_fonts_17px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
								<div class="mjschool_fees_center_margin_0px">
									<p class="mjschool_fees_width_fit_content_inline">
										<?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
									</p>
									<p class="mjschool_fees_width_fit_content_inline">
										&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="header mjschool-Examination-header mjschool-margin-top-10px">
					<span><strong class="mjschool-Examination-header-color"><?php esc_html_e( 'Examination Hall Ticket', 'mjschool' ); ?></strong></span>
				</div>
				<div class="mjschool-float-width">
					<table width="100%" class="count borderpx" cellspacing="0" cellpadding="0">
						<thead>
						</thead>
						<tbody>
							<tr>
								<td rowspan="4" class="mjschool-img-td">
									<?php
									if (empty($umetadata ) ) { ?>
										<img src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>" width="100px" height="100px">
										<?php
									} else {
										?>
										<img src="<?php echo esc_url($umetadata); ?>" width="100px" height="100px">
										<?php
									}
									?>
								</td>
								
								<td colspan="2" class="mjschool-border-bottom"> <strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->display_name ); ?></a> </td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-rigth" align="left"> <strong><?php esc_html_e( 'Roll Nunmber', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->roll_id ); ?> </td>
								<td class="mjschool-border-bottom" align="left"> <strong><?php esc_html_e( 'Exam Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $exam_data->exam_name ); ?> </td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-rigth" align="left">
									<strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_class_name( $student_data->class_name ) ); ?>
								</td>
								<td class="mjschool-border-bottom" align="left">
									<strong><?php esc_html_e( 'Section Name', 'mjschool' ); ?> : </strong>
									<?php
									$section_name = $student_data->class_section;
									if ( $section_name != '' ) {
										echo esc_html( mjschool_get_section_name( $section_name ) );
									} else {
										esc_html_e( 'No Section', 'mjschool' );
									}
									?>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-rigth" align="left">
									<strong><?php esc_html_e( 'Start Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) ); ?>
								</td>
								<td class="mjschool-border-bottom-0" align="left">
									<strong><?php esc_html_e( 'End Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) ); ?>
								</td>
							</tr>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="mjschool-padding-top-20 mjschool-float-width">
					<table width="100%" class="count borderpx" cellspacing="0" cellpadding="0">
						<thead>
						</thead>
						<tbody>
							<tr>
								<td class="mjschool-border-bottom">
									<strong><?php esc_html_e( 'Examination Centre', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( $exam_hall_name ); ?>,
									<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-0">
									<strong><?php esc_html_e( 'Examination Centre Address', 'mjschool' ); ?> : </strong><?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
								</td>
							</tr>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="mjschool-padding-top-20 mjschool-float-width">
					<table width="100%" class="count borderpx" style="border-bottom:none;" cellspacing="0"
						cellpadding="0">
						<thead>
							<tr>
								<th colspan="5" class="mjschool-border-bottom"> <?php esc_html_e( 'Time Table For Exam Hall', 'mjschool' ); ?> </th>
							</tr>
							<tr>
								<th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php esc_html_e( 'Exam Time', 'mjschool' ); ?></th>
								<th class="mjschool-main-td  mjschool-th-margin"><?php esc_html_e( 'Examiner Sign.', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $exam_time_table ) ) {
								foreach ( $exam_time_table as $retrieved_data ) {
									?>
									<tr>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php echo esc_html( mjschool_get_single_subject_code( $retrieved_data->subject_id ) ); ?></td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject_id ) ); ?></td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_date ) ); ?></td>
										<?php
										$start_time_data = explode( ':', $retrieved_data->start_time );
										$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
										$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
										$start_am_pm     = $start_time_data[2];
										$start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
										$end_time_data   = explode( ':', $retrieved_data->end_time );
										$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
										$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
										$end_am_pm       = $end_time_data[2];
										$end_time        = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
										?>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin">
											<?php echo esc_html( $start_time ); ?>
											<?php esc_html_e( 'To', 'mjschool' ); ?>
											<?php echo esc_html( $end_time ); ?>
										</td>
										<td class="mjschool-main-td  mjschool-th-margin"></td>
									</tr>
									<?php
								}
							} else {
								?>
								<tr>
									<td class="mjschool-main-td" colspan="5"> <?php esc_html_e( 'No Data Available', 'mjschool' ); ?> </td>
								</tr>
								<?php
							}
							?>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="resultdate">
					<hr color="" style="border-top: 2px solid #97C4E7 !important; border:none;">
					<span><?php esc_html_e( 'Student Signature', 'mjschool' ); ?></span>
				</div>
				<div class="signature">
					
					<span> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px; margin-right:15px;" /> </span>
					
					<hr color="" style="border-top: 2px solid #97C4E7 !important; border:none;">
					<span><?php esc_html_e( 'Authorized Signature', 'mjschool' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}
}

/**
 * Generates and outputs the student examination hall ticket in printable HTML format.
 *
 * @since 1.0.0
 *
 * @param int $student_id Student ID for whom the hall ticket is generated.
 * @param int $exam_id    Exam ID associated with the hall ticket.
 *
 * @return void Outputs HTML content directly.
 */
function mjschool_student_exam_receipt_pdf( $student_id, $exam_id ) {
	$student_data    = get_userdata( $student_id );
	$umetadata       = mjschool_get_user_image( $student_id );
	$exam_data       = mjschool_get_exam_by_id( $exam_id );
	$exam_hall_data  = mjschool_get_exam_hall_name( $student_id, $exam_id );
	$exam_hall_name  = mjschool_get_hall_name( $exam_hall_data->hall_id );
	$obj_exam        = new mjschool_exam();
	$exam_time_table = $obj_exam->mjschool_get_exam_time_table_by_exam( $exam_id );
	?>
	<style>
		table,
		.header,
		span.sign {
			font-family: Poppins;
			font-size: 12px;
			color: #444;
		}
		.borderpx {
			border: 2px solid #97C4E7;
		}
		.count td,
		.count th {
			height: 40px;
		}
		.td_pdf {
			padding-left: 10px;
		}
		.mjschool-th-margin {
			padding-left: 0px;
		}
		.resultdate {
			float: left;
			width: 200px;
			padding-top: 100px;
			text-align: center;
		}
		.signature {
			float: right;
			width: 200px;
			padding-top: 55px;
			text-align: center;
		}
		.exam_receipt_print {
			width: 100%;
			margin: 0 auto;
		}
		.header_logo {
			float: left;
			width: 100%;
			text-align: center;
		}
		.font_22 {
			font-size: 22px;
		}
		.mjschool-Examination-header {
			float: left;
			width: 100%;
			font-size: 18px;
			text-align: center;
			padding-bottom: 20px;
		}
		.mjschool-Examination-header-color {
			color: #970606;
		}
		.mjschool-float-width {
			float: left;
			width: 100%;
		}
		.mjschool-padding-top-20 {
			padding-top: 20px;
		}
		.mjschool-img-td {
			text-align: center;
			border-right: 2px solid #97C4E7;
		}
		.mjschool-border-bottom {
			border-bottom: 1px solid #97C4E7;
		}
		.mjschool-border-bottom-0 {
			border-bottom: 0px;
		}
		.mjschool-border-bottom-rigth {
			border-bottom: 1px solid #97C4E7;
			border-right: 1px solid #97C4E7;
		}
		.mjschool-border-rigth {
			border-right: 1px solid #97C4E7;
		}
		.mjschool-main-td {
			text-align: center;
			border-bottom: 1px solid #97C4E7;
		}
		.hr_color {
			color: #97C4E7;
		}
		.header_color {
			color: #204759;
		}
		.max_height_100 {
			max-height: 100px;
		}
		.mjschool-tr-back-color {
			background-color: #337AB7;
		}
		.mjschool-color-white {
			color: white;
		}
	</style>
	<?php
	if ( is_rtl() ) {
		?>
		<div class="modal-body mjschool_direction_rtl" >
			<div id="exam_receipt_print" class="exam_receipt_print">
				<div class="container" style="margin-bottom:12px;">
					<div style="border: 2px solid;">
						<div style="padding:20px;">
							<div class="mjschool_float_left_width_100">
								<div class="mjschool_float_left_width_25">
									<div class="mjschool-custom-logo-class" style="float:left;border-radius:50px;">
										<div style="width: 150px;background-image: url( '<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>' );height: 150px;border-radius: 50%;background-repeat:no-repeat;background-size:cover;"></div>
									</div>
								</div>
								<div style="float:left; width:74%;font-size:24px;padding-top:25px;">
									<p class="mjschool_fees_widht_100_fonts_24px"> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
									<p class="mjschool_fees_center_fonts_17px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
									<div class="mjschool_fees_center_margin_0px">
										<p class="mjschool_fees_width_fit_content_inline">
											<?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="header mjschool-Examination-header" style="margin-top: 10px;">
					<span><strong class="mjschool-Examination-header-color"><?php esc_html_e( 'Examination Hall Ticket', 'mjschool' ); ?></strong></span>
				</div>
				<div class="mjschool-float-width">
					<table width="100%" class="count borderpx" cellspacing="0" cellpadding="0">
						<thead>
						</thead>
						<tbody>
							<tr>
								
								<td rowspan="4" class="mjschool-img-td">
									<?php
									if (empty($umetadata ) ) { ?>
										<img src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>" width="100px" height="100px">
										<?php
									} else {
										?>
										<img src="<?php echo esc_url($umetadata); ?>" width="100px" height="100px">
										<?php
									}
									?>
								</td>
								
								<td colspan="2" class="mjschool-border-bottom td_pdf"> <strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->display_name ); ?></a> </td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-rigth td_pdf" align="left"> <strong><?php esc_html_e( 'Roll Nunmber', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->roll_id ); ?> </td>
								<td class="mjschool-border-bottom td_pdf" align="left"> <strong><?php esc_html_e( 'Exam Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $exam_data->exam_name ); ?> </td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-rigth td_pdf" align="left">
									<strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_class_name( $student_data->class_name ) ); ?>
								</td>
								<td class="mjschool-border-bottom td_pdf" align="left">
									<strong><?php esc_html_e( 'Section Name', 'mjschool' ); ?> : </strong>
									<?php
									$section_name = $student_data->class_section;
									if ( $section_name != '' ) {
										echo esc_html( mjschool_get_section_name( $section_name ) );
									} else {
										esc_html_e( 'No Section', 'mjschool' );
									}
									?>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-rigth td_pdf" align="left">
									<strong><?php esc_html_e( 'Start Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) ); ?>
								</td>
								<td class="mjschool-border-bottom-0 td_pdf" align="left">
									<strong><?php esc_html_e( 'End Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) ); ?>
								</td>
							</tr>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="mjschool-padding-top-20 mjschool-float-width">
					<table width="100%" class="count borderpx" cellspacing="0" cellpadding="0">
						<thead>
						</thead>
						<tbody>
							<tr>
								<td class="mjschool-border-bottom td_pdf">
									<strong><?php esc_html_e( 'Examination Centre', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( $exam_hall_name ); ?>,
									<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-0 td_pdf">
									<strong><?php esc_html_e( 'Examination Centre Address', 'mjschool' ); ?> : </strong><?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
								</td>
							</tr>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="mjschool-padding-top-20 mjschool-float-width">
					<table width="100%" cellspacing="0" cellpadding="0" class="count borderpx">
						<thead>
							<tr>
								<th colspan="5" class="mjschool-border-bottom"> <?php esc_html_e( 'Time Table For Exam Hall', 'mjschool' ); ?></th>
							</tr>
							<tr class="mjschool-tr-back-color">
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php esc_html_e( 'Exam Time', 'mjschool' ); ?></th>
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php esc_html_e( 'Examiner Sign.', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $exam_time_table ) ) {
								foreach ( $exam_time_table as $retrieved_data ) {
									?>
									<tr>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php echo esc_html( mjschool_get_single_subject_code( $retrieved_data->subject_id ) ); ?></td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject_id ) ); ?></td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_date ) ); ?></td>
										<?php
										$start_time_data = explode( ':', $retrieved_data->start_time );
										$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
										$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
										$start_am_pm     = $start_time_data[2];
										$start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
										$end_time_data   = explode( ':', $retrieved_data->end_time );
										$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
										$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
										$end_am_pm       = $end_time_data[2];
										$end_time        = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
										?>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px">
											<?php echo esc_html( $start_time ); ?>
											<?php esc_html_e( 'To', 'mjschool' ); ?>
											<?php echo esc_html( $end_time ); ?>
										</td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"></td>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="resultdate">
					<hr color="#97C4E7">
					<span><?php esc_html_e( 'Student Signature', 'mjschool' ); ?></span>
				</div>
				<div class="signature">
					<span>
						
						<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px; margin-right:15px;" />
					</span>
					<hr color="#97C4E7">
					<span><?php esc_html_e( 'Authorized Signature', 'mjschool' ); ?></span>
				</div>
			</div>
		</div>
		<!-- RTL END --->
		<?php
	} else {
		?>
		<div class="modal-body">
			<div id="exam_receipt_print" class="exam_receipt_print">
				<div style="margin-bottom:8px;">
					<div class="mjschool-width-print" style="border: 2px solid;float:left;width:96%;margin: 6px 0px 0px 0px;padding:20px;">
						<div class="mjschool_float_left_width_100">
							<div class="mjschool_float_left_width_25">
								<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
									<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" style="height: 150px;border-radius:50%;background-repeat:no-repeat;background-size:cover;" />
								</div>
								
							</div>
							<div class="mjschool_float_left_padding_width_75">
								<p class="mjschool_fees_widht_100_fonts_24px"> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </p>
								<p class="mjschool_fees_center_fonts_17px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?> </p>
								<div class="mjschool_fees_center_margin_0px">
									<p class="mjschool_fees_width_fit_content_inline">
										<?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
									</p>
									<p class="mjschool_fees_width_fit_content_inline">
										&nbsp;&nbsp;
										<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="header mjschool-Examination-header" style="margin-top: 10px;">
					<span><strong class="mjschool-Examination-header-color"> <?php esc_html_e( 'Examination Hall Ticket', 'mjschool' ); ?> </strong></span>
				</div>
				<div class="mjschool-float-width">
					<table width="100%" class="count borderpx" cellspacing="0" cellpadding="0">
						<thead>
						</thead>
						<tbody>
							<tr>
								<td rowspan="4" class="mjschool-img-td">
									<?php 
									if (empty($umetadata ) ) { ?>
										<img src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>" width="100px" height="100px">
										<?php
									} else {
										?>
										<img src="<?php echo esc_url($umetadata); ?>" width="100px" height="100px">
										<?php
									}
									?>
									
								</td>
								<td colspan="2" class="mjschool-border-bottom td_pdf">
									<strong> <?php esc_html_e( 'Student Name', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( $student_data->display_name ); ?></a>
								</td>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-rigth td_pdf" align="left">
									<strong> <?php esc_html_e( 'Roll Number', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( $student_data->roll_id ); ?>
								</td>
								<td class="mjschool-border-bottom td_pdf" align="left">
									<strong> <?php esc_html_e( 'Exam Name', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( $exam_data->exam_name ); ?>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-rigth td_pdf" align="left">
									<strong> <?php esc_html_e( 'Class Name', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( mjschool_get_class_name( $student_data->class_name ) ); ?>
								</td>
								<td class="mjschool-border-bottom td_pdf" align="left">
									<strong> <?php esc_html_e( 'Section Name', 'mjschool' ); ?> : </strong>
									<?php
									$section_name = $student_data->class_section;
									if ( $section_name != '' ) {
										echo esc_html( mjschool_get_section_name( $section_name ) );
									} else {
										esc_html_e( 'No Section', 'mjschool' );
									}
									?>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-rigth td_pdf" align="left">
									<strong> <?php esc_html_e( 'Start Date', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) ); ?>
								</td>
								<td class="mjschool-border-bottom-0 td_pdf" align="left">
									<strong> <?php esc_html_e( 'End Date', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) ); ?>
								</td>
							</tr>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="mjschool-padding-top-20 mjschool-float-width">
					<table width="100%" class="count borderpx" cellspacing="0" cellpadding="0">
						<thead>
						</thead>
						<tbody>
							<tr>
								<td class="mjschool-border-bottom td_pdf">
									<strong> <?php esc_html_e( 'Examination Centre', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( $exam_hall_name ); ?>,
									<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
								</td>
							</tr>
							<tr>
								<td class="mjschool-border-bottom-0 td_pdf">
									<strong> <?php esc_html_e( 'Examination Centre Address', 'mjschool' ); ?> : </strong>
									<?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
								</td>
							</tr>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="mjschool-padding-top-20 mjschool-float-width">
					<table width="100%" cellspacing="0" cellpadding="0" class="count borderpx">
						<thead>
							<tr>
								<th colspan="5" class="mjschool-border-bottom"> <?php esc_html_e( 'Time Table For Exam Hall', 'mjschool' ); ?> </th>
							</tr>
							<tr class="mjschool-tr-back-color">
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"> <?php esc_html_e( 'Subject Code', 'mjschool' ); ?> </th>
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"> <?php esc_html_e( 'Subject', 'mjschool' ); ?> </th>
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"> <?php esc_html_e( 'Exam Date', 'mjschool' ); ?> </th>
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"> <?php esc_html_e( 'Exam Time', 'mjschool' ); ?> </th>
								<th class="mjschool-main-td mjschool-color-white mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"> <?php esc_html_e( 'Examiner Sign.', 'mjschool' ); ?> </th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $exam_time_table ) ) {
								foreach ( $exam_time_table as $retrieved_data ) {
									?>
									<tr>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php echo esc_html( mjschool_get_single_subject_code( $retrieved_data->subject_id ) ); ?></td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject_id ) ); ?></td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_date ) ); ?></td>
										<?php
										$start_time_data = explode( ':', $retrieved_data->start_time );
										$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
										$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
										$start_am_pm     = $start_time_data[2];
										$start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
										$end_time_data   = explode( ':', $retrieved_data->end_time );
										$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
										$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
										$end_am_pm       = $end_time_data[2];
										$end_time        = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
										?>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px">
											<?php echo esc_html( $start_time ); ?>
											<?php esc_html_e( 'To', 'mjschool' ); ?>
											<?php echo esc_html( $end_time ); ?>
										</td>
										<td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin mjschool_padding_10px"></td>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>
				<div class="resultdate">
					<hr color="#97C4E7">
					<span> <?php esc_html_e( 'Student Signature', 'mjschool' ); ?> </span>
				</div>
				<div class="signature">
					
					<span> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px; margin-right:15px;" /> </span>
					
					<hr color="#97C4E7">
					<span> <?php esc_html_e( 'Authorized Signature', 'mjschool' ); ?> </span>
				</div>
			</div>
		</div>
		<?php
	}
}

/**
 * Generates and outputs a PDF of an individual student's exam result.
 *
 * Uses MPDF to create a formatted result sheet including marks, grades,
 * GPA calculation, and student details.
 *
 * @since 1.0.0
 *
 * @param int $sudent_id Student ID.
 *
 * @return void Outputs the PDF directly to the browser.
 */
function mjschool_download_result_pdf( $sudent_id ) {
	ob_start();
	$obj_mark      = new mjschool_Marks_Manage();
	$exam_obj      = new mjschool_exam();
	$uid           = $sudent_id;
	$user          = get_userdata( $uid );
	$user_meta     = get_user_meta( $uid );
	$class_id      = $user_meta['class_name'][0];
	$section_id    = $user_meta['class_section'][0];
	$subject       = $obj_mark->mjschool_student_subject_list( $class_id, $section_id );
	$total_subject = count( $subject );
	$exam_id       = intval(wp_unslash($_REQUEST['exam_id']));
	$total         = 0;
	$grade_point   = 0;
	$umetadata     = mjschool_get_user_image( $uid );
	?>
	<center>
		
		<div class="mjschool_float_left_width_100"> <img src="<?php echo esc_html( get_option( 'mjschool_logo' ) ) ?>" style="max-height:50px;" /> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </div>
		<div style="width:100%;float:left;border-bottom:1px solid red;"></div>
		<div style="width:100%;float:left;border-bottom:1px solid yellow;padding-top:5px;"></div>
		<br>
		<div style="float:left;width:100%;padding:10px 0;">
			<div style="width:70%;float:left;text-align:left;">
				<p>
					<?php esc_html_e( 'Surname', 'mjschool' ); ?> : <?php get_user_meta($uid, 'last_name', true); ?>
				</p>
				<p>
					<?php esc_html_e( 'First Name', 'mjschool' ); ?> : <?php echo esc_html( get_user_meta($uid, 'first_name', true ) ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Class', 'mjschool' ); ?> :
					<?php $class_id = get_user_meta($uid, 'class_name', true);
					$classname = mjschool_get_class_name($class_id);
					echo esc_html( $classname)
					?>
				</p>
				<p>
					<?php esc_html_e( 'Exam Name', 'mjschool' ); ?> : <?php echo esc_html( mjschool_get_exam_name_id($exam_id ) ); ?>
				</p>
			</div>
			<div style="float:right;width:30%;"> <img src="<?php echo esc_url($umetadata['meta_value']); ?>" /> </div>
			
		</div>
		<br>
		<table style="float:left;width:100%;border:1px solid #000;" cellpadding="0" cellspacing="0">
			<thead>
				<?php
				$exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
				$contributions = $exam_data->contributions;
				if ( $contributions === 'yes' ) {
					$contributions_data       = $exam_data->contributions_data;
					$contributions_data_array = json_decode( $contributions_data, true );
				}
				?>
				<tr style="border-bottom: 1px solid #000;">
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"> <?php esc_html_e( 'S/No', 'mjschool' ); ?></th>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"> <?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
					<?php
					if ( $contributions === 'yes' ) {
						foreach ( $contributions_data_array as $con_id => $con_value ) {
							?>
							<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"> <?php echo esc_html( $con_value['label'] ) . '<br>' . '(out of ' . esc_html( $con_value['mark'] ) . ' )'; ?>
							</th>
							<?php
						}
					} else {
						?>
						<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"> <?php esc_html_e( 'Obtain Mark', 'mjschool' ); ?></th>
						<?php
					}
					?>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$i = 1;
				foreach ( $subject as $sub ) {
					?>
					<tr style="border-bottom: 1px solid #000;">
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $i ); ?> </td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $sub->sub_name ); ?></td>
						<?php
						$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
						if ( $contributions === 'yes' ) {
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								?>
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
									<?php
									if ( is_array( $obtain_marks ) ) {
										echo esc_html( $obtain_marks[ $con_id ] );
									} else {
										echo esc_html( $obtain_marks );
									}
									?>
								</td>
								<?php
							}
						} else {
							?>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $obtain_marks ); ?> </td>
							<?php
						}
						?>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
							<?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?>
						</td>
					</tr>
					<?php
					++$i;
					if ( $contributions === 'yes' ) {
						$tmarks = 0; // Initialize the variable.
						foreach ( $contributions_data_array as $con_id => $con_value ) {
							if ( is_array( $obtain_marks ) ) {
								$tmarks += (int) $obtain_marks[ $con_id ];
							} else {
								$tmarks += (int) $obtain_marks;
							}
						}
						$total_marks = $tmarks;
					} else {
						$total_marks += $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
					}
					$total       += $total_marks;
					$grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
				}
				?>
			</tbody>
		</table>
		<p class="result_total">
			<?php
			esc_html_e( 'Total Marks', 'mjschool' );
			echo ' : ' . esc_html( $total );
			?>
		</p>
		<p class="result_point">
			<?php
			esc_html_e( 'GPA(grade point average)', 'mjschool' );
			$GPA = $grade_point / $total_subject;
			echo ' : ' . esc_html( round( $GPA, 2 ) );
			?>
		</p>
		<hr />
	</center>
	<?php
	$out_put = ob_get_contents();
	ob_clean();
	header( 'Content-type: application/pdf' );
	header( 'Content-Disposition: inline; filename="result"' );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Accept-Ranges: bytes' );
	require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
	$mpdf = new Mpdf\Mpdf();
	$mpdf->WriteHTML( $out_put );
	$mpdf->Output();
	unset( $out_put );
	unset( $mpdf );
	die();
}
/**
 * Generates and outputs a PDF of a group's exam result for a student.
 *
 * Similar to the individual result PDF, this includes subject marks,
 * grade calculation, and dynamic contribution-based marks where applicable.
 *
 * @since 1.0.0
 *
 * @param int $sudent_id Student ID.
 *
 * @return void Outputs the PDF directly to the browser.
 */
function mjschool_download_group_result_pdf( $sudent_id ) {
	ob_start();
	$obj_mark      = new mjschool_Marks_Manage();
	$exam_obj      = new mjschool_exam();
	$uid           = $sudent_id;
	$user          = get_userdata( $uid );
	$user_meta     = get_user_meta( $uid );
	$class_id      = $user_meta['class_name'][0];
	$section_id    = $user_meta['class_section'][0];
	$subject       = $obj_mark->mjschool_student_subject_list( $class_id, $section_id );
	$total_subject = count( $subject );
	$exam_id       = intval(wp_unslash($_REQUEST['exam_id']));
	$total         = 0;
	$grade_point   = 0;
	$umetadata     = mjschool_get_user_image( $uid );
	?>
	<center>
		
		<div class="mjschool_float_left_width_100"> <img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" style="max-height:50px;" /> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </div>
		
		<div style="width:100%;float:left;border-bottom:1px solid red;"></div>
		<div style="width:100%;float:left;border-bottom:1px solid yellow;padding-top:5px;"></div>
		<br>
		<div style="float:left;width:100%;padding:10px 0;">
			<div style="width:70%;float:left;text-align:left;">
				<p> <?php esc_html_e( 'Surname', 'mjschool' ); ?> : <?php get_user_meta( $uid, 'last_name', true ); ?> </p>
				<p> <?php esc_html_e( 'First Name', 'mjschool' ); ?> : <?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ); ?> </p>
				<p>
					<?php esc_html_e( 'Class', 'mjschool' ); ?> :
					<?php
					$class_id  = get_user_meta( $uid, 'class_name', true );
					$classname = mjschool_get_class_name( $class_id );
					echo esc_html( $classname )
					?>
				</p>
				<p>
					<?php esc_html_e( 'Exam Name', 'mjschool' ); ?> : <?php echo esc_html( mjschool_get_exam_name_id( $exam_id ) ); ?>
				</p>
			</div>
			
			<div style="float:right;width:30%;"> <img src="<?php echo esc_url($umetadata['meta_value']); ?>" /> </div>
			
		</div>
		<br>
		<table style="float:left;width:100%;border:1px solid #000;" cellpadding="0" cellspacing="0">
			<thead>
				<?php
				$exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
				$contributions = $exam_data->contributions;
				if ( $contributions === 'yes' ) {
					$contributions_data       = $exam_data->contributions_data;
					$contributions_data_array = json_decode( $contributions_data, true );
				}
				?>
				<tr style="border-bottom: 1px solid #000;">
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"> <?php esc_html_e( 'S/No', 'mjschool' ); ?></th>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"> <?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
					<?php
					if ( $contributions === 'yes' ) {
						foreach ( $contributions_data_array as $con_id => $con_value ) {
							?>
							<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"> <?php echo esc_html( $con_value['label'] ) . '<br>' . '(out of ' . esc_html( $con_value['mark'] ) . ' )'; ?> </th>
							<?php
						}
					} else {
						?>
						<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"> <?php esc_html_e( 'Obtain Mark', 'mjschool' ); ?></th>
						<?php
					}
					?>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"> <?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$i = 1;
				foreach ( $subject as $sub ) {
					?>
					<tr style="border-bottom: 1px solid #000;">
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $i ); ?> </td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $sub->sub_name ); ?></td>
						<?php
						$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
						if ( $contributions === 'yes' ) {
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								?>
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
									<?php
									if ( is_array( $obtain_marks ) ) {
										echo esc_html( $obtain_marks[ $con_id ] );
									} else {
										echo esc_html( $obtain_marks );
									}
									?>
								</td>
								<?php
							}
						} else {
							?>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
								<?php echo esc_html( $obtain_marks ); ?> </td>
							<?php
						}
						?>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
							<?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?>
						</td>
					</tr>
					<?php
					++$i;
					if ( $contributions === 'yes' ) {
						$tmarks = 0; // Initialize the variable.
						foreach ( $contributions_data_array as $con_id => $con_value ) {
							if ( is_array( $obtain_marks ) ) {
								$tmarks += (int) $obtain_marks[ $con_id ];
							} else {
								$tmarks += (int) $obtain_marks;
							}
						}
						$total_marks = $tmarks;
					} else {
						$total_marks += $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
					}
					$total       += $total_marks;
					$grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
				}
				?>
			</tbody>
		</table>
		<p class="result_total">
			<?php
			esc_html_e( 'Total Marks', 'mjschool' );
			echo ' : ' . esc_html( $total );
			?>
		</p>
		<p class="result_point">
			<?php
			esc_html_e( 'GPA(grade point average)', 'mjschool' );
			$GPA = $grade_point / $total_subject;
			echo ' : ' . esc_html( round( $GPA, 2 ) );
			?>
		</p>
		<hr />
	</center>
	<?php
	$out_put = ob_get_contents();
	ob_clean();
	header( 'Content-type: application/pdf' );
	header( 'Content-Disposition: inline; filename="result"' );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Accept-Ranges: bytes' );
	require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
	$mpdf = new Mpdf\Mpdf();
	$mpdf->WriteHTML( $out_put );
	$mpdf->Output();
	unset( $out_put );
	unset( $mpdf );
	die();
}
/**
 * Generates and prints the student's exam result report.
 *
 * This function fetches exam details, subject-wise marks, grades,
 * contributions, GPA, percentage, teacher comments, and signatures.
 * It then renders the complete marksheet layout for printing.
 *
 * @since 1.0.0
 *
 * @param int    $sudent_id       Student User ID.
 * @param int    $class_id        Class ID.
 * @param int    $section_id      Section ID.
 * @param string $teacher_comment Teacher's comment for the student.
 * @param int    $teacher_id      Teacher User ID for signature.
 *
 * @return void Outputs HTML directly for printing. No return value.
 */
function mjschool_download_result_print( $sudent_id, $class_id, $section_id, $teacher_comment, $teacher_id ) {
	wp_print_styles();
	$school_type	 = get_option( 'mjschool_custom_class' );
	$sudent_id       = intval( $sudent_id );    // error_reporting(0);
	$exam_id         = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) );   // error_reporting(0);
	$obj_mark        = new mjschool_Marks_Manage();
	$uid             = $sudent_id;
	$exam_obj        = new mjschool_exam();
	$user            = get_userdata( $uid );
	$user_meta       = get_user_meta( $uid );
	$exam_data       = $exam_obj->mjschool_exam_data( $exam_id );
	$class_id        = $exam_data->class_id;
	$exam_section_id = $exam_data->section_id;
	$metadata        = get_user_meta( $teacher_id );
	$signature_path  = isset( $metadata['signature'][0] ) ? $metadata['signature'][0] : '';
	$signature_url   = $signature_path ? content_url( $signature_path ) : '';
	if ( $exam_section_id == 0 ) {
		$subject = mjschool_get_subject_by_class_id($class_id);
	} else {
		$subject = mjschool_get_subjects_by_class_and_section($class_id, $exam_section_id);
	}
	$total_subject = count( $subject );
	// $exam_id = $_REQUEST['exam_id'];
	$total       = 0;
	$grade_point = 0;
	$umetadata   = mjschool_get_user_image( $uid );
	ob_start();
	 
	?>
	
	<?php
	if ( is_rtl() ) {
		?>
		<div style="margin-bottom:8px;">
			<div class="mjschool-width-print" style="border: 2px solid;float:left;width:96%;margin: 6px 0px 0px 0px;padding:20px;padding-top: 4px;padding-bottom: 5px;">
				<div style="float:left;width:100%;">
					<div style="float:left;width:25%;">
						
						<div class="mjschool-custom-logo-class" style="float:letf;border-radius:50px;">
							<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" style="height: 130px;border-radius:50%;background-repeat:no-repeat;background-size:cover;margin-top: 3px;" />
						</div>
					</div>
					
					<div style="float:left; width:75%;padding-top:10px;">
						<p style="margin:0px;width:100%;font-weight:bold;color:#1B1B8D;font-size:24px;text-align:center;"><?php echo esc_html(  get_option( 'mjschool_name' ) ); ?></p>
						<p style="margin:0px;font-size:17px;text-align:center;"><?php echo esc_html(  get_option( 'mjschool_address' ) ); ?></p>
						<div style="margin:0px;width:100%;text-align:center;">
							<p style="margin: 0px;width: fit-content;font-size: 17px;display: inline-block;"><?php esc_html_e( 'E-mail', 'mjschool' ); ?> :<?php echo esc_html(  get_option( 'mjschool_email' ) ); ?></p>
							<p style="margin: 0px;width: fit-content;font-size: 17px;display: inline-block;">&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> :<?php echo esc_html(  get_option( 'mjschool_contact_number' ) ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="mjschool-width-print" style="border: 2px solid;margin-bottom:8px;float:left;width:97%;padding:20px;margin-top:10px;">
			<div style="float:left;width:100%;">
				<div  style="padding:10px;">
					<div style="float:left;width:50%;">
						<b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>:<?php echo esc_html(  get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html(  get_user_meta( $uid, 'last_name', true ) ); ?>
					</div>
					<div style="float:left;width:50%;">
						<b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>:<?php echo esc_html(  mjschool_get_exam_name_id( $exam_id ) ); ?>
					</div>
				</div>
			</div>
			<div style="float:left;width:50%;">
				<div  style="padding:10px;">
					<div style="float:left;width:50%;">
						<b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>: <?php echo esc_html(  get_user_meta( $uid, 'roll_id', true ) ); ?>
					</div>
				</div>
			</div>
			<div style="float:right;width:50%;">
				<div  style="padding-top:10px;">
					<b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
					<?php
					$classname = mjschool_get_class_name( $class_id );
					$section_name = ! empty( $section_id ) ? mjschool_get_section_name( $section_id ) : esc_html__( 'No Section', 'mjschool' );
					echo esc_html(  $classname ) . ' - ' . esc_html(  $section_name );
					?>
				</div>
			</div>
		</div>
		<table style="float:right;width:100%;border: 2px solid;margin-bottom:8px; direction: rtl;" cellpadding="10" cellspacing="0">
			<thead>
				<?php
				$exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
				$exam_marks    = $exam_data->total_mark;
				$contributions = $exam_data->contributions;
				if ( $contributions === 'yes' ) {
					$contributions_data       = $exam_data->contributions_data;
					$contributions_data_array = json_decode( $contributions_data, true );
				}
				?>
				<tr class="table_color" style="border-bottom: 2px solid;background-color:#b8daff;">
					<th style="border-bottom: 2px solid;text-align:right;border-right: 2px solid;"> <?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
					<?php
					if ( $contributions === 'yes' ) {
						foreach ( $contributions_data_array as $con_id => $con_value ) {
							?>
							<th style="border-bottom: 2px solid;text-align:right;border-right: 2px solid;"> <?php echo esc_html(  $con_value['label'] ) . ' ( ' . esc_html(  $con_value['mark'] ) . ' )'; ?></th>
							<?php
						}
						?>
						<th style="border-bottom: 2px solid;text-align:right;border-right: 2px solid;"> <?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html(  $exam_marks ) . ' )'; ?></th>
						<?php
					} else {
						?>
						<th style="border-bottom: 2px solid;text-align:right;border-right: 2px solid;"> <?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html(  $exam_marks ) . ' )'; ?></th>
						<?php
					}
					?>
					<th style="border-bottom: 2px solid;text-align:right;border-right: 2px solid;"> <?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$i               = 1;
				$total_pass_mark = 0;
				$total_max_mark  = 0;
				foreach ( $subject as $sub ) {
					$total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
					$marks_get        = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
					?>
					<tr style="border-bottom: 2px solid;">
						<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $sub->sub_name ); ?> </td>
						<td style="border-bottom: 2px solid;border-right: 2px solid;"> <?php echo esc_html(  $obj_mark->mjschool_get_max_marks( $exam_id ) ); ?> </td>
						<td style="border-bottom: 2px solid;border-right: 2px solid;"> <?php echo esc_html(  $obj_mark->mjschool_get_pass_marks( $exam_id ) ); ?></td>
						<?php
						$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
						if ( $contributions === 'yes' ) {
							$subject_total = 0;
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
								$subject_total += $mark_value;
								?>
								<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $mark_value ); ?> </td> <?php
							}
							?>
							<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $subject_total ); ?> </td>
							<?php
						} else {
							?>
							<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $obtain_marks ); ?> </td>
							<?php
						}
						?>
						<td style="border-bottom: 2px solid;border-right: 2px solid;"> <?php echo esc_html(  $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?> </td>
					</tr>
					<?php
					++$i;
					// Calculate total marks.
					if ( $contributions === 'yes' ) {
						foreach ( $contributions_data_array as $con_id => $con_value ) {
							$total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
						}
					} else {
						$total_marks += $obtain_marks;
					}
					$grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
				}
				$total         += $total_marks;
				$total_max_mark = $exam_marks * $total_subject;
				$GPA            = $grade_point / $total_subject;
				$percentage     = $total / $total_max_mark * 100;
				?>
			</tbody>
		</table>
		<table style="float:right;width:100%;border: 2px solid;margin-bottom:8px; direction: rtl;" cellpadding="10" cellspacing="0">
			<thead>
				<tr class="table_color" style="border-bottom: 2px solid;background-color:#b8daff;">
					<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"><?php esc_html_e( 'Marks Obtainable', 'mjschool' ); ?></th>
					<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
					<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"><?php esc_html_e( 'Percentage(%)', 'mjschool' ); ?></th>
					<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"><?php esc_html_e( 'GPA', 'mjschool' ); ?></th>
					<th style="border-bottom: 2px solid;text-align:center;"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr style="border-bottom: 2px solid;">
					<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $total_max_mark ); ?></td>
					<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $total ); ?></td>
					<td style="border-bottom: 2px solid;border-right: 2px solid;">
						<?php
						if ( ! empty( $percentage ) ) {
							echo number_format( $percentage, 2, '.', '' );
						} else {
							echo '-';
						}
						?>
					</td>
					<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  round( $GPA, 2 ) ); ?></td>
					<td style="border-bottom: 2px solid;">
						<?php
						$result = array();
						$rest1  = array();
						foreach ( $subject as $sub ) {
							$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
							if ( $contributions === 'yes' ) {
								$subject_total = 0;
								foreach ( $contributions_data_array as $con_id => $con_value ) {
									$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
									$subject_total += $mark_value;
								}
								$marks_total = $subject_total;
							} else {
								$marks_total = $obtain_marks;
							}
							if ( $marks_total >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
								$result[] = 'pass';
							} else {
								$result1[] = 'fail';
							}
						}
						if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
							esc_html_e( 'Fail', 'mjschool' );
						} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
							esc_html_e( 'Pass', 'mjschool' );
						} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
							esc_html_e( 'Fail', 'mjschool' );
						} else {
							echo '-';
						}
						?>
					</td>
				</tr>
			</tbody>
		</table>
		<div  style="border: 2px solid; width:96%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;">
			<!-- Teacher's Comment (Left Side) -->
			<div style="float: left; width: 33.33%;">
				<div style="margin-left: 20px;">
					<strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong> <p><?php echo esc_html(  $teacher_comment ); ?></p>
				</div>
			</div>
			<!-- Teacher Signature (Middle) -->
			<div style="float: left; width: 33.33%; text-align: center; margin-top: 0px;">
				<?php
				if ( ! empty( $signature_url ) ) {
					?>
					<div>
						<img src="<?php echo esc_url( $signature_url ); ?>" style="width:100px;height:45px;" />
					</div>
					<?php
				} else {
					?>
					<div>
						<div style="width:100px;height:45px;"></div>
					</div>
				<?php } ?>
				<div style="border-top: 1px solid #000; width: 150px; margin: 5px auto;"></div>
				<div style="margin-top: 5px;"> <?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?> </div>
				<div style="border-top: 1px solid #000; width: 150px; margin: 5px auto;"></div>
				<div style="margin-top: 5px;"> <?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?> </div>
			</div>
			<!-- Principal Signature (Right Side) -->
			<div style="float: left; width: 30%; text-align: right; padding-right: 20px;">
				
				<div><img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px;" /></div>
				<div style="border-top: 1px solid #000; width: 150px; margin: 5px 0 5px auto;"></div>
				<div style="margin-right:10px; margin-bottom:10px;"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
			</div>
		</div>
		<?php
	} else {
		?>
		<div style="margin-bottom:8px;">
			<div class="mjschool-width-print" style="border: 2px solid;float:left;width:96%;margin: 6px 0px 0px 0px;padding:20px;padding-top: 4px;padding-bottom: 5px;">
				<div style="float:left;width:100%;">
					<div style="float:left;width:25%;">
						<div class="mjschool-custom-logo-class" style="float:letf;border-radius:50px;">
							<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" style="height: 130px;border-radius:50%;background-repeat:no-repeat;background-size:cover;margin-top: 3px;" />
						</div>
					</div>
					
					<div style="float:left; width:75%;padding-top:10px;">
						<p style="margin:0px;width:100%;font-weight:bold;color:#1B1B8D;font-size:24px;text-align:center;"> <?php echo esc_html(  get_option( 'mjschool_name' ) ); ?></p>
						<p style="margin:0px;font-size:17px;text-align:center;"> <?php echo esc_html(  get_option( 'mjschool_address' ) ); ?></p>
						<div style="margin:0px;width:100%;text-align:center;">
							<p style="margin: 0px;width: fit-content;font-size: 17px;display: inline-block;"> <?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html(  get_option( 'mjschool_email' ) ); ?></p>
							<p style="margin: 0px;width: fit-content;font-size: 17px;display: inline-block;"> &nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html(  get_option( 'mjschool_contact_number' ) ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="mjschool-width-print" style="border: 2px solid;margin-bottom:8px;float:left;width:97%;padding:20px;margin-top:10px;">
			<div style="float:left;width:100%;">
				<div  style="padding:10px;">
					<div style="float:left;width:50%;">
						<b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>: <?php echo esc_html(  get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html(  get_user_meta( $uid, 'last_name', true ) ); ?>
					</div>
					<div style="float:left;width:50%;">
						<b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>: <?php echo esc_html(  mjschool_get_exam_name_id( $exam_id ) ); ?>
					</div>
				</div>
			</div>
			<div style="float:left;width:50%;">
				<div  style="padding:10px;">
					<div style="float:left;width:50%;">
						<b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>: <?php echo esc_html(  get_user_meta( $uid, 'roll_id', true ) ); ?>
					</div>
				</div>
			</div>
			<div style="float:right;width:50%;">
				<?php if ( $school_type === 'school' ){ ?>
					<div  style="padding-top:10px;">
						<b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
						<?php
						$classname    = mjschool_get_class_name( $class_id );
						$section_name = ! empty( $section_id ) ? mjschool_get_section_name( $section_id ) : esc_html__( 'No Section', 'mjschool' );
						echo esc_html(  $classname ) . ' - ' . esc_html(  $section_name );
						?>
					</div>
				<?php }elseif ( $school_type === 'university' ) {?>
					<div  style="padding-top:10px;">
						<b><?php esc_html_e( 'Class Name', 'mjschool' ); ?></b>:
						<?php
						$classname    = mjschool_get_class_name( $class_id );
						echo esc_html(  $classname );
						?>
					</div>
				<?php }?>
			</div>
		</div>
		<table style="float:left;width:100%;border: 2px solid;border-bottom: 2px; margin-bottom:8px;border-right: 1px;" cellpadding="10" cellspacing="0">
			<thead>
				<?php
				$exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
				$exam_marks    = $exam_data->total_mark;
				$contributions = $exam_data->contributions;
				if ( $contributions === 'yes' ) {
					$contributions_data       = $exam_data->contributions_data;
					$contributions_data_array = json_decode( $contributions_data, true );
				}
				?>
				<tr class="table_color" style="border-bottom: 2px solid;background-color:#b8daff;">
					<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"> <?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
					<?php
					if ( $contributions === 'yes' ) {
						foreach ( $contributions_data_array as $con_id => $con_value ) {
							?>
							<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"> <?php echo esc_html(  $con_value['label'] ) . ' ( ' . esc_html(  $con_value['mark'] ) . ' )'; ?></th>
							<?php
						}
						?>
						<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"> <?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html(  $exam_marks ) . ' )'; ?></th>
						<?php
					} else {
						if ( $school_type === 'school' ){
							?>
							<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"><?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html(  $exam_marks ) . ' )'; ?></th>
							<?php
						}elseif ( $school_type === 'university' ){
							?>
							<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"><?php esc_html_e( 'Total', 'mjschool' ); ?></th>
							<?php
						}
					}
					?>
					<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"> <?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( $school_type === 'school' ){
					$i               = 1;
					$total_pass_mark = 0;
					$total_max_mark  = 0;
					$total_marks     = 0;
					foreach ( $subject as $sub ) {
						$total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
						$marks_get        = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
						?>
						<tr style="border-bottom: 2px solid;">
							<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $sub->sub_name ); ?></td>
							<?php
							$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
							if ( $contributions === 'yes' ) {
								$subject_total = 0;
								foreach ( $contributions_data_array as $con_id => $con_value ) {
									$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
									$subject_total += $mark_value;
									?>
									<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $mark_value ); ?> </td>
									<?php
								}
								?>
								<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $subject_total ); ?></td>
								<?php
							} else {
								?>
								<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $obtain_marks ); ?></td>
								<?php
							}
							?>
							<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
						</tr>
						<?php
						++$i;
						// Calculate total marks.
						if ( $contributions === 'yes' ) {
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								$total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
							}
						} else {
							$total_marks += $obtain_marks;
						}
						$grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
					}
					$total         += $total_marks;
					$total_max_mark = $exam_marks * $total_subject;
					$GPA            = $grade_point / $total_subject;
					if( ! empty( $total) && !empty($total_max_mark ) )
					{
						$percentage     = $total / $total_max_mark * 100;
					}
				}elseif ( $school_type == "university"){
					$i               = 1;
					$total_pass_mark = 0;
					$total_max_mark  = 0;
					$total_marks     = 0;
					$exam_subject_data = json_decode($exam_data->subject_data,true);
					$exam_subject_lookup = [];
					foreach ($exam_subject_data as $exam_sub) {
						$exam_subject_lookup[$exam_sub['subject_id']] = $exam_sub;
					}
					foreach ( $subject as $sub ) {
						$total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
						$marks_get        = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
						$max_marks = isset($exam_subject_lookup[$sub->subid]) ? $exam_subject_lookup[$sub->subid]['max_marks'] : 'N/A';

						//filter students for the current subject.
						$assigned_student_ids = array_map( 'intval', explode( ',', $sub->selected_students ) );
						$current_student_id   = (int) $user->ID;
						
						if (!in_array($current_student_id, $assigned_student_ids, true ) ) {
							continue; // Skip students not assigned to this subject.
						}
						if (!isset($exam_subject_lookup[$sub->subid] ) ) {
							continue;
						}
						?>
						<tr style="border-bottom: 2px solid;">
							<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $sub->sub_name ); ?></td>
							<?php
							$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
							if ( $contributions === 'yes' ) {
								$subject_total = 0;
								foreach ( $contributions_data_array as $con_id => $con_value ) {
									$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
									$subject_total += $mark_value;
									?>
									<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $mark_value ); ?> </td>
									<?php
								}
								?>
								<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $subject_total ); ?></td>
								<?php
							} else {
								?>
								<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html( $obtain_marks) ." / ". esc_html( $max_marks); ?></td>
								<?php
								$total_max_mark +=$max_marks;
							}
							?>
							<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
						</tr>
						<?php
						++$i;
						// Calculate total marks.
						if ( $contributions === 'yes' ) {
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								$total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
							}
						} else {
							$total_marks += $obtain_marks;
						}
						$grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
					}
					$total         += $total_marks;
					$GPA            = $grade_point / $total_subject;
					if( ! empty( $total) && !empty($total_max_mark ) )
					{
						$percentage     = $total / $total_max_mark * 100;
					}
				}
				?>
			</tbody>
		</table>
		<table style="float:left;width:100%;border: 2px solid; border-bottom: 2px;margin-bottom:8px;" cellpadding="10" cellspacing="0">
			<thead>
				<tr class="table_color" style="border-bottom: 2px solid;background-color:#b8daff;">
					<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"><?php esc_html_e( 'Marks Obtainable', 'mjschool' ); ?></th>
					<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
					<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"><?php esc_html_e( 'Percentage(%)', 'mjschool' ); ?></th>
					<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"><?php esc_html_e( 'GPA', 'mjschool' ); ?></th>
					<th style="border-bottom: 2px solid;text-align:center;"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr style="border-bottom: 2px solid;">
					<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $total_max_mark ); ?> </td>
					<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $total ); ?></td>
					<td style="border-bottom: 2px solid;border-right: 2px solid;">
						<?php
						if ( ! empty( $percentage ) ) {
							echo number_format( $percentage, 2, '.', '' );
						} else {
							echo '-';
						}
						?>
					</td>
					<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  round( $GPA, 2 ) ); ?> </td>
					<td style="border-bottom: 2px solid;">
						<?php
						if ( $school_type != 'university' )
						{
							$result = array();
							$rest1  = array();
							foreach ( $subject as $sub ) {
								$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
								if ( $contributions === 'yes' ) {
									$subject_total = 0;
									foreach ( $contributions_data_array as $con_id => $con_value ) {
										$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
										$subject_total += $mark_value;
									}
									$marks_total = $subject_total;
								} else {
									$marks_total = $obtain_marks;
								}
								if ( $marks_total >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
									$result[] = 'pass';
								} else {
									$result1[] = 'fail';
								}
							}
							if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
								esc_html_e( 'Fail', 'mjschool' );
							} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
								esc_html_e( 'Pass', 'mjschool' );
							} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
								esc_html_e( 'Fail', 'mjschool' );
							} else {
								echo '-';
							}
						}elseif ( $school_type === 'university' ){
							$result = array();
							$rest1  = array();
							foreach ( $subject as $sub ) {
								$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid ) ?? 0;
								if ( $obtain_marks >= $exam_subject_lookup[$sub->subid]['passing_marks'] ) {
									$result[] = 'pass';
								} else {
									$result1[] = 'fail';
								}
							}
							if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
								esc_html_e( 'Fail', 'mjschool' );
							} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
								esc_html_e( 'Pass', 'mjschool' );
							} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
								esc_html_e( 'Fail', 'mjschool' );
							} else {
								echo '-';
							}
						}
						?>
					</td>
				</tr>
			</tbody>
		</table>
		<div  style="border: 2px solid; width:96%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;">
			<!-- Teacher's Comment (Left Side) -->
			<div style="float: left; width: 33.33%;">
				<div style="margin-left: 20px;">
					<strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong> <p><?php echo esc_html(  $teacher_comment ); ?></p>
				</div>
			</div>
			<!-- Teacher Signature (Middle) -->
			<div style="float: left; width: 33.33%; text-align: center; margin-top: 0px;">
				<?php
				if ( ! empty( $signature_url ) ) {
					?>
					<div>
						<img src="<?php echo esc_url( $signature_url ); ?>" style="width:100px;height:45px;" />
					</div>
					<?php
				} else {
					?>
					<div>
						<div style="width:100px;height:45px;"></div>
					</div>
				<?php } ?>
				<div style="border-top: 1px solid #000; width: 150px; margin: 5px auto;"></div>
				<div style="margin-top: 5px;"> <?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?> </div>
			</div>
			<!-- Principal Signature (Right Side) -->
			<div style="float: left; width: 30%; text-align: right; padding-right: 20px;">
				
				<div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px;" /> </div>
				
				<div style="border-top: 1px solid #000; width: 150px; margin: 5px 0 5px auto;"></div>
				<div style="margin-right:10px; margin-bottom:10px;"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
			</div>
		</div>
		<?php
	}
	$out_put = ob_get_contents();
}
/**
 * Generates and outputs the printable Group Result sheet for a student.
 *
 * This function prepares all necessary student, exam, subject, marks,
 * grading, and signature data, then renders a full printable HTML layout
 * for group result reporting using inline styles suitable for printing.
 *
 * @since 1.0.0
 *
 * @param string $sudent_id        Encrypted student ID.
 * @param int    $class_id         Class ID.
 * @param int    $section_id       Section ID.
 * @param string $teacher_comment  Teacher's final remark for the student.
 * @param int    $teacher_id       Teacher user ID for fetching signature.
 *
 * @return void Outputs HTML directly for printing.
 */
function mjschool_download_group_result_print( $sudent_id, $class_id, $section_id, $teacher_comment, $teacher_id ) {
	
	wp_print_styles();
	$sudent_id         = intval( mjschool_decrypt_id( $sudent_id ) );   // error_reporting(0);
	$merge_id          = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['merge_id'])) ) );
	$obj_mark          = new mjschool_Marks_Manage();
	$uid               = $sudent_id;
	$exam_obj          = new mjschool_exam();
	$merge_data        = $exam_obj->mjschool_get_single_merge_exam_setting( $merge_id );
	$merge_name        = $merge_data->merge_name;
	$merge_config_data = json_decode( $merge_data->merge_config );
	$totalObjects      = ! empty( $merge_config_data ) ? count( $merge_config_data ) : 0;
	$user              = get_userdata( $uid );
	$user_meta      = get_user_meta( $uid );
	$subject        = $obj_mark->mjschool_student_subject_list( $class_id, $section_id );
	$total_subject  = count( $subject );
	$umetadata      = mjschool_get_user_image( $uid );
	$metadata       = get_user_meta( $teacher_id );
	$signature_path = isset( $metadata['signature'][0] ) ? $metadata['signature'][0] : '';
	$signature_url  = $signature_path ? content_url( $signature_path ) : '';
	ob_start();
	
	?>
	<style>
		body,
		body * {
			font-family: 'Poppins' !important;
		}
		@media print {
			* {
				color-adjust: exact !important;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}
			.mjschool-width-print {
				width: 93.3% !important;
			}
			.color_f5c6cc {
				background-color: #f5c6cc !important;
				-webkit-print-color-adjust: exact;
			}
			.table_color {
				background-color: #b8daff !important;
				-webkit-print-color-adjust: exact;
			}
			.footer_color {
				background-color: #eacf80 !important;
				-webkit-print-color-adjust: exact;
			}
			.tfoot_border {
				border-bottom: 1px solid #000 !important;
			}
			.mt_10 {
				margin-top: 10px !important;
			}
			@media print {
				.result-table-container {
					width: 100% !important;
					max-width: 100% !important;
					zoom: 0.75;
					/* Better than transform, no position shift */
				}
				table {
					table-layout: auto !important;
					width: 99.3% !important;
					font-size: 15px !important;
				}
				th,
				td {
					padding: 4px 6px !important;
					word-wrap: break-word;
				}
				body {
					margin: 0;
					padding: 0;
					-webkit-print-color-adjust: exact;
					print-color-adjust: exact;
				}
			}
		}
	</style>
	<div style="margin-bottom:8px;">
		<div class="mjschool-width-print" style="border: 2px solid;float:left;width:96%;margin: 6px 0px 0px 0px;padding:20px;padding-top: 4px;padding-bottom: 5px;">
			<div style="float:left;width:100%;">
				<div style="float:left;width:25%;">
					
					<div class="mjschool-custom-logo-class" style="float:letf;border-radius:50px;">
						<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" style="height: 130px;border-radius:50%;background-repeat:no-repeat;background-size:cover;margin-top: 3px;" />
					</div>
					
				</div>
				<div style="float:left; width:75%;padding-top:10px;">
					<p style="margin:0px;width:100%;font-weight:bold;color:#1B1B8D;font-size:24px;text-align:center;"> <?php echo esc_html(  get_option( 'mjschool_name' ) ); ?></p>
					<p style="margin:0px;font-size:17px;text-align:center;"> <?php echo esc_html(  get_option( 'mjschool_address' ) ); ?></p>
					<div style="margin:0px;width:100%;text-align:center;">
						<p style="margin: 0px;width: fit-content;font-size: 17px;display: inline-block;"> <?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html(  get_option( 'mjschool_email' ) ); ?></p>
						<p style="margin: 0px;width: fit-content;font-size: 17px;display: inline-block;"> &nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html(  get_option( 'mjschool_contact_number' ) ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="mjschool-width-print" style="border: 2px solid; margin-bottom:8px; float:left; width:96%; padding:20px; margin-top:10px;">
		<div style="float:left; width:100%;">
			<div  style="padding:10px;">
				<div style="float:left; width:50%;">
					<b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>: <?php echo esc_html(  get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html(  get_user_meta( $uid, 'last_name', true ) ); ?>
				</div>
				<div style="float:left; width:50%;">
					<b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>: <?php echo esc_html(  $merge_name ); ?>
				</div>
				<!-- Second Row: Roll No + Class & Section -->
				<div style="float:left; width:50%; margin-top:12px;">
					<b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>: <?php echo esc_html(  get_user_meta( $uid, 'roll_id', true ) ); ?>
				</div>
				<div style="float:left; width:50%; margin-top:12px;">
					<b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
					<?php
					$classname = mjschool_get_class_name( $class_id );
					if ( ! empty( $section_id ) ) {
						$section_name = mjschool_get_section_name( $section_id );
						echo esc_html(  $classname ) . ' - ' . esc_html(  $section_name );
					} else {
						echo esc_html(  $classname );
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="result-table-container">
		<table style="float:left;width:99%;border: 2px solid;border-bottom: 2px; margin-bottom:12px;" cellpadding="10" cellspacing="0">
			<thead>
				<tr class="table_color" style="border-bottom: 2px solid;background-color:#b8daff;">
					<th rowspan="2" style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"> <?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
					<?php
					if ( ! empty( $merge_config_data ) ) {
						foreach ( $merge_config_data as $item ) {
							$exam_id   = $item->exam_id;
							$exam_name = mjschool_get_exam_name_id( $exam_id );
							if ( mjschool_check_contribution( $exam_id ) === 'yes' ) {
								$exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
								$contributions_data_array = json_decode( $exam_data->contributions_data, true );
								echo '<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;" colspan="' . ( count( $contributions_data_array ) ) . '">' . esc_html(  $exam_name ) . '</th>';
							} else {
								echo '<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"  >' . esc_html(  $exam_name ) . '</th>';
							}
						}
					}
					?>
					<th colspan="2" style="border-bottom: 2px solid;text-align:left;"> <?php echo esc_html(  mjschool_print_weightage_data_pdf( $merge_data->merge_config ) ); ?></th>
				</tr>
				<tr class="table_color" style="border-bottom: 2px solid;background-color:#b8daff;">
					<?php
					if ( ! empty( $merge_config_data ) ) {
						foreach ( $merge_config_data as $item ) {
							$exam_id = $item->exam_id;
							if ( mjschool_check_contribution( $exam_id ) === 'yes' ) {
								$exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
								$contributions_data_array = json_decode( $exam_data->contributions_data, true );
								foreach ( $contributions_data_array as $con_id => $con_value ) {
									echo '<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;">' . esc_attr( $con_value['label'] ) . ' ( ' . esc_html(  $con_value['mark'] ) . ' )</th>';
								}
							} else {
								$exam_data = $exam_obj->mjschool_exam_data( $exam_id );
								?>
								<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"> <?php esc_html_e( 'Grand Total(100)', 'mjschool' ); ?></th>
								<?php
							}
						}
					}
					?>
					<th style="border-bottom: 2px solid;text-align:left;border-right: 2px solid;"> <?php esc_html_e( 'Grand Total(100)', 'mjschool' ); ?></th>
					<th style="border-bottom: 2px solid;text-align:left;"><?php esc_html_e( 'Grade', 'mjschool' ); ?> </th>
				</tr>
			</thead>
			<tbody>
				<?php
				$total_obtained     = 0;
				$total_max_possible = 0;
				$any_subject_failed = false;
				foreach ( $subject as $sub ) {
					echo '<tr style="border-bottom: 2px solid;">';
					echo '<td style="border-bottom: 2px solid;border-right: 2px solid;">' . esc_html(  $sub->sub_name ) . '</td>';
					$subject_total_weighted = 0;
					foreach ( $merge_config_data as $item ) {
						$exam_id        = $item->exam_id;
						$exam_weightage = $item->weightage;
						$marks          = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
						if ( mjschool_check_contribution( $exam_id ) === 'yes' ) {
							$exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
							$contributions_data_array = json_decode( $exam_data->contributions_data, true );
							$subject_total            = 0;
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								$mark_value     = isset( $marks[ $con_id ] ) ? floatval( $marks[ $con_id ] ) : 0;
								$subject_total += $mark_value;
								echo '<td style="border-bottom: 2px solid;border-right: 2px solid;">' . esc_html(  $mark_value ) . '</td>';
							}
							$weighted_marks = ( $subject_total * $exam_weightage ) / 100;
							if ( $subject_total < $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
								$any_subject_failed = true;
							}
						} else {
							$marks = floatval( $marks );
							echo '<td style="border-bottom: 2px solid;border-right: 2px solid;">' . esc_html(  $marks ) . '</td>';
							$weighted_marks = ( $marks * $exam_weightage ) / 100;
							if ( $marks < $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
								$any_subject_failed = true;
							}
						}
						$subject_total_weighted += $weighted_marks;
						$grade                   = $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid );
						$comment                 = $obj_mark->mjschool_get_grade_comment( $exam_id, $class_id, $sub->subid, $uid );
					}
					$subject_grade = $obj_mark->mjschool_get_grade_base_on_grand_total( $subject_total_weighted );
					echo '<td style="border-bottom: 2px solid;border-right: 2px solid;">' . esc_html(  round( $subject_total_weighted, 2 ) ) . '</td>';
					echo '<td style="border-bottom: 2px solid;">' . esc_html(  $subject_grade ) . '</td>';
					echo '</tr>';
					$total_obtained     += $subject_total_weighted;
					$total_max_possible += 100;
				}
				$percentage   = ( $total_obtained / $total_max_possible ) * 100;
				$final_grade  = $obj_mark->mjschool_get_grade_base_on_grand_total( $percentage );
				$final_result = ( $any_subject_failed || $percentage < 33 ) ? esc_html__( 'Fail', 'mjschool' ) : esc_html__( 'Pass', 'mjschool' );
				?>
			</tbody>
		</table>
	</div>
	<table style="float:left;width:98%;border: 2px solid; border-bottom: 2px;margin-bottom:12px;" cellpadding="10" cellspacing="0">
		<thead>
			<tr class="table_color" style="border-bottom: 2px solid;background-color:#b8daff;">
				<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"> <?php esc_html_e( 'Overall Mark', 'mjschool' ); ?></th>
				<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"> <?php esc_html_e( 'Percentage', 'mjschool' ); ?></th>
				<th style="border-bottom: 2px solid;text-align:center;border-right: 2px solid;"> <?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
				<th style="border-bottom: 2px solid;text-align:center;"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr style="border-bottom: 2px solid;">
				<td style="border-bottom: 2px solid;border-right: 2px solid;"> <?php echo esc_html(  round( $total_obtained, 2 ) ) . ' / ' . esc_html(  $total_max_possible ); ?></td>
				<td style="border-bottom: 2px solid;border-right: 2px solid;"> <?php echo number_format( $percentage, 2 ) . '%'; ?></td>
				<td style="border-bottom: 2px solid;border-right: 2px solid;"><?php echo esc_html(  $final_grade ); ?></td>
				<td style="border-bottom: 2px solid;"><?php echo esc_html(  $final_result ); ?></td>
			</tr>
		</tbody>
	</table>
	<div  style="border: 2px solid; width:96%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;">
		<!-- Teacher's Comment (Left Side) -->
		<div style="float: left; width: 33.33%;">
			<div style="margin-left: 20px;">
				<strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong> <p><?php echo esc_html(  $teacher_comment ); ?></p>
			</div>
		</div>
		<!-- Teacher Signature (Middle) -->
		<div style="float: left; width: 33.33%; text-align: center; margin-top: 0px;">
			<?php
			if ( ! empty( $signature_url ) ) {
				?>
				<div>
					<img src="<?php echo esc_url( $signature_url ); ?>" style="width:100px;height:45px;" />
				</div>
				<?php
			} else {
				?>
				<div>
					<div style="width:100px;height:45px;"></div>
				</div>
			<?php } ?>
			<div style="border-top: 1px solid #000; width: 150px; margin: 5px auto;"></div>
			<div style="margin-top: 5px;"> <?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?> </div>
		</div>
		<!-- Principal Signature (Right Side) -->
		<div style="float: left; width: 30%; text-align: right; padding-right: 20px;">
			
			<div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px;" /> </div>
			
			<div style="border-top: 1px solid #000; width: 150px; margin: 5px 0 5px auto;"></div>
			<div style="margin-right:10px; margin-bottom:10px;"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
		</div>
	</div>
	<?php
	$out_put = ob_get_contents();
}
/**
 * Handles admin-side print requests and triggers result print rendering.
 *
 * Detects print actions for both single result printing and group result
 * printing from the admin panel. Enqueues required fonts, prints the page,
 * and calls appropriate rendering functions before terminating execution.
 *
 * @since 1.0.0
 *
 * @return void Prints the result layout and terminates script execution.
 */
function mjschool_print_init_admin_side() {
	$sanitize_print = isset($_REQUEST['print']) ? sanitize_text_field(wp_unslash($_REQUEST['print'])) : '';
	$sanitize_page = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
	if ( $sanitize_print === 'print' && $sanitize_page === 'mjschool_student' ) {
		wp_enqueue_style( 'mjschool-poppins-fontfamily', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
		?>
		<script type="text/javascript">
			(function() {
				"use strict";
				window.addEventListener( 'load', function() {
					window.print();
				});
			})();
		</script>
		<?php
		$sudent_id       = mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student'])) );
		$class_id        = isset( $_REQUEST['class_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['class_id'])) ) ) : 0;
		$section_id      = isset( $_REQUEST['section_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['section_id'])) ) ) : 0;
		$teacher_comment = isset( $_REQUEST['comment'] ) ? sanitize_text_field( wp_unslash($_REQUEST['comment']) ) : '';
		$teacher_id      = isset( $_REQUEST['teacher_id'] ) ? intval( wp_unslash($_REQUEST['teacher_id']) ) : 0;
		mjschool_download_result_print( $sudent_id, $class_id, $section_id, $teacher_comment, $teacher_id );
		die();
	}
	if ( isset( $_REQUEST['print'] ) && sanitize_text_field(wp_unslash($_REQUEST['print'])) === 'group_result_print' && $_REQUEST['page'] === 'mjschool_student' ) {
		wp_enqueue_style( 'mjschool-poppins-fontfamily', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
		?>
		<script type="text/javascript">
			(function() {
				"use strict";
				window.addEventListener( 'load', function() {
					// Delay to ensure content (e.g., iframes) renders properly.
					setTimeout(function() {
						window.print();
					}, 200);
				});
			})();
		</script>
		<?php
		$sudent_id       = sanitize_text_field(wp_unslash($_REQUEST['student']));
		$class_id        = isset( $_REQUEST['class_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['class_id'])) ) ) : 0;
		$section_id      = isset( $_REQUEST['section_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['section_id'])) ) ) : 0;
		$teacher_comment = isset( $_REQUEST['comment'] ) ? sanitize_text_field( wp_unslash($_REQUEST['comment']) ) : '';
		$teacher_id      = isset( $_REQUEST['teacher_id'] ) ? intval( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) ) : 0;
		mjschool_download_group_result_print( $sudent_id, $class_id, $section_id, $teacher_comment, $teacher_id );
		die();
	}
}
/**
 * Handles student-side print requests for individual result reports.
 *
 * When the print action is triggered from the student dashboard, this
 * function prepares fonts, sets a delay to ensure rendering, and prints
 * the student's result sheet using the result rendering function.
 *
 * @since 1.0.0
 *
 * @return void Outputs printable student result HTML and stops execution.
 */
function mjschool_print_init_student_side() {
	if ( isset( $_REQUEST['print'] ) && sanitize_text_field(wp_unslash($_REQUEST['print'])) === 'print' && $_REQUEST['page'] === 'student' ) {
		wp_enqueue_style( 'mjschool-poppins-fontfamily', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
		?>
		<script type="text/javascript">
			(function() {
				"use strict";
				function mjschool_print_with_delay() {
					setTimeout(function () {
						window.print();
					}, 500); // Delay to allow content to render.
				}
				window.addEventListener( 'load', mjschool_print_with_delay);
			})();
		</script>
		<?php
		$sudent_id = sanitize_text_field(wp_unslash($_REQUEST['student']));
		mjschool_download_result_print( $sudent_id );
		die();
	}
}
add_action( 'init', 'mjschool_print_init_student_side' );
add_action( 'init', 'mjschool_print_init_admin_side' );

/**
 * Generate and display the student payment history invoice in PDF format.
 *
 * This function retrieves payment details, invoice information, student data,
 * billing address, fees breakdown, tax, discounts, payment status, and renders
 * them into an invoice layout based on the selected template format.
 *
 * @param string $id Encrypted payment history ID.
 *
 * @since 1.0.0
 */
function mjschool_student_payment_history_pdf( $id ) {
	// lol.
	$format                     = get_option( 'mjschool_invoice_option' );
	$fees_pay_id                = mjschool_decrypt_id( $id );
	$invoice_number             = mjschool_generate_invoice_number( $fees_pay_id );
	$fees_detail_result         = mjschool_get_single_fees_payment_record( $fees_pay_id );
	$fees_history_detail_result = mjschool_get_payment_history_by_feespayid( $fees_pay_id );
	?>
	
	<?php
	if ( $format != 1 ) {
		if ( is_rtl() ) {
			?>
			<h3 class=""><?php echo esc_html( get_option( 'mjschool_school_name' ) ); ?></h3>
			<table style="float: right;position: absolute;vertical-align: top;background-repeat: no-repeat;">
				<tbody>
					<tr>
						<?php // @codingStandardsIgnoreStart ?>
						<td>
							<img class=" invoiceimage float_left invoice_image_model"
								src="<?php echo esc_url(plugins_url('/mjschool/assets/images/listpage_icon/invoice_rtl.png')); ?>"
								width="100%">
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		} else {
			?>
			<h3 class=""><?php echo esc_html(get_option('mjschool_school_name')) ?></h3>
			<table style="float: left;position: absolute;vertical-align: top;background-repeat: no-repeat;">
				<tbody>
					<tr>
						<td>
							<img class="invoiceimage float_left invoice_image_model"
								src="<?php echo esc_url(plugins_url('/mjschool/assets/images/listpage_icon/invoice.png')); ?>"
								width="100%">
						</td>
					</tr>
				</tbody>
			</table>

			<?php
		}
	}
	?>
	<?php if ($format === 1) { ?>
		<div class="width_print"
			style="border: 2px solid;float:left;width:96%;margin: 0px 0px 0px 0px;padding:20px;padding-top: 4px;padding-bottom: 5px;margin-bottom: 0px !important">
			<div style="float:left;width:100%; ">
				<div style="float:left;width:25%;">
					<div class="asasa" style="float:letf;border-radius:50px;">
						<img src="<?php echo esc_url(get_option('mjschool_school_logo')) ?>"
							style="height: 130px;border-radius:50%;background-repeat:no-repeat;background-size:cover;margin-top: 3px;" />
					</div>
				</div>
				<div style="float:left; width:75%;padding-top:10px;">
					<p style="margin:0px;width:100%;font-weight:bold;color:#1B1B8D;font-size:24px;text-align:center;">
						<?php echo esc_html(get_option('mjschool_school_name')); ?></p>
					<p style="margin:0px;font-size:17px;text-align:center;">
						<?php echo esc_html(get_option('mjschool_school_address')); ?></p>
					<div style="margin:0px;width:100%;text-align:center;">
						<p style="margin: 0px;width: fit-content;font-size: 17px;display: inline-block;">
							<?php esc_html_e('E-mail', 'mjschool'); ?> :
							<?php echo esc_html(get_option('mjschool_email')); ?>&nbsp;&nbsp;<?php esc_html_e('Phone', 'mjschool'); ?>
							: <?php echo esc_html(get_option('mjschool_contact_number')); ?></p>
					</div>
				</div>
			</div>
		</div>
	<?php } else { ?>

		<table style="float: left;width: 100%;position: absolute!important;margin-top:-160px;">
			<tbody>
				<tr>
					<td width="80%">
						<table>
							<tbody>
								<tr>
									<td width="10%">
										<img class="system_logo"
											src="<?php echo esc_url(get_option('mjschool_school_logo')); ?>">
									</td>
									<?php // @codingStandardsIgnoreEnd ?>
									<td width="90%" style="padding-left: 20px;">
										<h4 class="popup_label_heading"><?php esc_html_e( 'Address', 'mjschool' ); ?>
										</h4>
										<label for="" class="label_value word_break_all"
											style="font-size: 16px !important;color: #333333 !important;font-weight: 400;">
											<?php
											$school_address  = get_option( 'mjschool_school_address' );
											$escaped_address = esc_html( $school_address );
											$split_address   = str_replace( '<br>', '<BR>', chunk_split( $escaped_address, 100, '<br>' ) );
											echo wp_kses_post( $split_address );
											?>
											</label><br>
										<h4 class="popup_label_heading"><?php esc_html_e( 'Email', 'mjschool' ); ?>
										</h4>
										<label for=""
											style="font-size: 16px !important;color: #333333 !important;font-weight: 400;"
											class="label_value word_break_all"><?php echo esc_html( get_option( 'mjschool_email' ) ), '<BR>'; ?></label><br>
										<h4 class="popup_label_heading"><?php esc_html_e( 'Phone', 'mjschool' ); ?>
										</h4>
										<label for=""
											style="font-size: 16px !important;color: #333333 !important;font-weight: 400;"
											class="label_value"><?php echo esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>'; ?></label>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	<?php } ?>
	<br>
	<?php
	if ( $format === 1 ) {
		
		?>
		<div class="width_print"
			style="border: 2px solid;margin-bottom:8px;float:left;width:96%;padding:20px;padding-top: 5px;padding-bottom: 5px;margin-bottom: 0px !important;margin-top: 0px !important">
			<div style="float:left;width:100%;">
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
				<div class="123" style="padding:10px;">
					<div style="float:left;width:65%;"><b><?php esc_html_e( 'Bill To', 'mjschool' ); ?>:</b>
						<?php echo esc_html( mjschool_student_display_name_with_roll( $student_id ) ); ?></div>

					<div style="float:left;width:35%;"><b><?php esc_html_e( 'Invoice Number', 'mjschool' ); ?>:</b>
						<?php echo esc_html( $invoice_number ); ?>
					</div>
				</div>
			</div>
			<div style="float:left; width:64%;">
				<?php
				$student_id = $fees_detail_result->student_id;
				$patient    = get_userdata( $student_id );

				if ( $patient ) {
					$address = esc_html( get_user_meta( $student_id, 'address', true ) );
					$city    = esc_html( get_user_meta( $student_id, 'city', true ) );
					$zip     = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
					?>
					<div style="padding:10px;">
						<div><b><?php esc_html_e( 'Address', 'mjschool' ); ?>:</b>
							<?php echo esc_html( $address ); ?></div>
						<div><?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?></div>
					</div>

				<?php } ?>
			</div>
			<div style="float:right;width: 35.3%;">
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
				<div style="padding:10px 0;">
					<div style="float:left;width:100%;"><b><?php esc_html_e( 'Issue Date', 'mjschool' ); ?>:</b>
						<?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
					</div>
				</div>
			</div>
			<div style="float:right;width: 35.3%;">
				<div style="padding:10px 0;">
					<b><?php esc_html_e( 'Status', 'mjschool' ); ?>:</b>

					<?php
					$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
					if ( $payment_status === 'Fully Paid' ) {
						echo '<span class="green_color">' . esc_attr__( 'Fully Paid', 'mjschool' ) . '</span>';
					}
					if ( $payment_status === 'Partially Paid' ) {
						echo '<span class="perpal_color">' . esc_attr__( 'Partially Paid', 'mjschool' ) . '</span>';
					}
					if ( $payment_status === 'Not Paid' ) {
						echo '<span class="red_color">' . esc_attr__( 'Not Paid', 'mjschool' ) . '</span>';
					}
					?>
				</div>
			</div>
		</div>
		<?php
	} else {
		?>
		<table>
			<tbody>
				<tr>
					<td width="40%">
						<h3 class="billed_to_lable invoice_model_heading bill_to_width_12">
							<?php esc_html_e( 'Bill To', 'mjschool' ); ?> : </h3>
						<?php
						$student_id = $fees_detail_result->student_id;
						$patient    = get_userdata( $student_id );
						if ( $patient ) {
							$display_name = esc_html( ucwords( $patient->display_name ) );
							$split_name   = str_replace( '<br>', '<BR>', chunk_split( $display_name, 30, '<br>' ) );
							echo "<h3 class='display_name invoice_width_100'>" . wp_kses_post( $split_name ) . '</h3>';
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
						<div>
							<?php
							$student_id = $fees_detail_result->student_id;
							$patient    = get_userdata( $student_id );
							if ( $patient ) {
								$address         = get_user_meta( $student_id, 'address', true );
								$escaped_address = esc_html( $address );
								$split_address   = str_replace( '<br>', '<BR>', chunk_split( $escaped_address, 30, '<br>' ) );
								echo wp_kses_post( $split_address );
								echo esc_html( get_user_meta( $student_id, 'city', true ) ) . ',' . '<BR>';
								echo esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . ',<BR>';
							}
							?>
						</div>
					</td>
					<td width="15%">
						<?php
						$issue_date     = 'DD-MM-YYYY';
						$issue_date     = $fees_detail_result->paid_by_date;
						$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
						?>
						<label
							style="color: #818386 !important;font-size: 14px !important;text-transform: uppercase;font-weight: 500;line-height: 0px;"><?php echo esc_html__( 'Invoice Number', 'mjschool' ); ?>
						</label>: <label class="invoice_model_value"
							style="font-weight: 600;color: #333333;font-size: 16px !important;"><?php echo esc_html( $invoice_number ); ?></label>
						<br>
						<label
							style="color: #818386 !important;font-size: 14px !important;text-transform: uppercase;font-weight: 500;line-height: 0px;"><?php echo esc_html__( 'Date', 'mjschool' ); ?>
						</label>: <label class="invoice_model_value"
							style="font-weight: 600;color: #333333;font-size: 16px !important;"><?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?></label><br>
						<label
							style="color: #818386 !important;font-size: 14px !important;text-transform: uppercase;font-weight: 500;line-height: 0px;"><?php echo esc_html__( 'Status', 'mjschool' ); ?>
						</label>: <label class="invoice_model_value"
							style="font-weight: 600;color: #333333;font-size: 16px !important;">
							<?php
							if ( $payment_status === 'Fully Paid' ) {
								echo '<span style="color:green;">' . esc_attr__( 'Fully Paid', 'mjschool' ) . '</span>';
							}
							if ( $payment_status === 'Partially Paid' ) {
								echo '<span style="color:#3895d3;">' . esc_attr__( 'Partially Paid', 'mjschool' ) . '</span>';
							}
							if ( $payment_status === 'Not Paid' ) {
								echo '<span style="color:red;">' . esc_attr__( 'Not Paid', 'mjschool' ) . '</span>';
							}
							?>
							</label>
					</td>
				</tr>
			</tbody>
		</table>
	<?php } ?>
	<h4 style="font-size: 14px;font-weight: 600;color: #333333;"><?php esc_html_e( 'Invoice Entry', 'mjschool' ); ?></h4>
	<div class="table-responsive mjschool-table-max-height-180px mjschool-rtl-padding-left-40px">
		<?php if ( $format === 1 ) { ?>
			<table class="table table-bordered mjschool-model-invoice-table" width="100%"
				style="border-collapse: collapse; border: 1px solid black;">
				<thead style="background-color: #F2F2F2;">
					<tr>
						<th style="text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black;background-color: #b8daff !important;width: 15%;font-size: 14px;">Number</th>
						<th style="text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black;background-color: #b8daff !important;width: 20%;font-size: 14px;"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
						<th style="text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black;background-color: #b8daff !important;font-size: 14px;"><?php esc_html_e( 'Fees Type', 'mjschool' ); ?></th>
						<th style="text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black;background-color: #b8daff !important;width: 15%;font-size: 14px;"><?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$fees_id = explode( ',', $fees_detail_result->fees_id );
					$x       = 1;
					$amounts = 0;
					foreach ( $fees_id as $id ) {
						$obj_feespayment = new mjschool_feespayment();
						$amount          = $obj_feespayment->mjschool_feetype_amount_data( $id );
						$amounts        += $amount;
						?>
						<tr>
							<td style="text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black;font-size: 14px;"><?php echo esc_html( $x ); ?></td>
							<td style="text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black;font-size: 14px;"><?php echo esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ); ?></td>
							<td style="text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black;font-size: 14px;"><?php echo esc_html( mjschool_get_fees_term_name( $id ) ); ?></td>
							<td style="text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black;font-size: 14px;"><?php echo esc_html( number_format( $amount, 2, '.', '' ) ); ?></td>
						</tr>
						<?php
						++$x;
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
			<?php
		} else {
			?>
			<table class="table table-bordered" width="100%">
				<thead style="background-color: #F2F2F2 !important;">
					<tr style="background-color: #F2F2F2 !important;">
						<th class="mjschool-align-left mjschool_border_padding_15px">#</th>
						<th class="mjschool-align-left mjschool_border_padding_15px"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
						<th class ="mjschool-align-left mjschool_border_padding_15px"><?php esc_html_e( 'Fees Type', 'mjschool' ); ?></th>
						<th class="mjschool-align-left" style="color: #818386 !important;font-weight: 600;border-bottom-color: #E1E3E5 !important;padding: 15px;"><?php esc_html_e( 'Total', 'mjschool' ); ?> </th>
					</tr>
				</thead>
				<?php
				$fees_id = explode( ',', $fees_detail_result->fees_id );
				$x       = 1;
				$amounts = 0;
				foreach ( $fees_id as $id ) {
					?>
					<tbody>
						<tr style=" border-bottom: 1px solid #E1E3E5 !important;">
							<td class="align-center mjschool_tables_bottoms"> <?php echo esc_html( $x ); ?></td>
							<td class="align-center mjschool_tables_bottoms"> <?php echo esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ); ?></td>
							<td class="align-center mjschool_tables_bottoms"> <?php echo esc_html( mjschool_get_fees_term_name( $id ) ); ?></td>
							<td class="align-center mjschool_tables_bottoms">
								<?php
								$obj_feespayment = new mjschool_feespayment();
								$amount          = $obj_feespayment->mjschool_feetype_amount_data( $id );
								$amounts        += $amount;
								echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $amount, 2, '.', '' ) ) );
								?>
							</td>
						</tr>
					</tbody>
					<?php
					++$x;
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
			</table>
		<?php } ?>
		<?php
		if ( $format === 1 ) {
			?>
			<div class="table-responsive mjschool-rtl-padding-left-40px mjschool-rtl-float-left-width-100px" style="margin-top: 10px;">
				<table class="table table-bordered" style="width: 100%; border-collapse: collapse;margin-bottom: 0px !important;">
					<tbody>
						<tr>
							<th style="width: 85%; text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;" scope="row"> <?php echo esc_html__( 'Sub Total', 'mjschool' ) . ' :'; ?> </th>
							<td style="width: 15%; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;"> <?php echo esc_html( number_format( $sub_total, 2, '.', '' ) ); ?> </td>
						</tr>
						<?php if ( isset( $fees_detail_result->discount_amount ) && ( $fees_detail_result->discount_amount ) != 0 ) { ?>
							<tr>
								<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;" scope="row"> <?php echo esc_html__( 'Discount Amount', 'mjschool' ) . ' ( ' . esc_html( $discount_name ) . ' ) :'; ?> </th>
								<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;"> <?php echo '-' . esc_html( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ); ?> </td>
							</tr>
						<?php } ?>
						<?php if ( isset( $fees_detail_result->tax_amount ) && ( $fees_detail_result->tax_amount ) != 0 ) { ?>
							<tr>
								<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;" scope="row"> <?php echo esc_html__( 'Tax Amount', 'mjschool' ) . ' ( ' . esc_html( $tax_name ) . ' ) :'; ?> </th>
								<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;"> <?php echo '+' . esc_html( number_format( $fees_detail_result->tax_amount, 2, '.', '' ) ); ?> </td>
							</tr>
						<?php } ?>
						<tr>
							<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;" scope="row"> <?php echo esc_html__( 'Payment Made :', 'mjschool' ); ?> </th>
							<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;"> <?php echo esc_html( number_format( $fees_detail_result->fees_paid_amount, 2, '.', '' ) ); ?> </td>
						</tr>
						<tr>
							<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;" scope="row"> <?php echo esc_html__( 'Due Amount :', 'mjschool' ); ?> </th>
							<?php $Due_amount = $fees_detail_result->total_amount - $fees_detail_result->fees_paid_amount; ?>
							<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;"> <?php echo esc_html( number_format( $Due_amount, 2, '.', '' ) ); ?> </td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
		} else {
			?>
			<table width="100%" border="0">
				<tbody>
					<tr>
						<td width="80%" <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?> style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;"> <?php esc_html_e( 'Sub Total :', 'mjschool' ); ?></td>
						<td <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?> style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $sub_total, 2, '.', '' ) ) ); ?> </td>
					</tr>
					<?php if ( isset( $fees_detail_result->discount_amount ) && ( $fees_detail_result->discount_amount ) != 0 ) { ?>
						<tr>
							<td width="80%" style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;" <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?>> <?php echo esc_attr__( 'Discount Amount', 'mjschool' ) . '( ' . esc_html( $discount_name ) . ' )' . '  :'; ?> </td>
							<td <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?> style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;">
								<?php echo '-' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ) ); ?>
							</td>
						</tr>
					<?php } ?>
					<?php
					if ( isset( $fees_detail_result->tax_amount ) && ( $fees_detail_result->tax_amount ) != 0 ) {
						?>
						<tr>
							<td width="80%" style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;" <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?>> <?php echo esc_attr__( 'Tax Amount', 'mjschool' ) . '( ' . esc_html( $tax_name ) . ' )' . '  :'; ?> </td>
							<td <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?> style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;">
								<?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->tax_amount, 2, '.', '' ) ) ); ?>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td width="80%" <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?> style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;"> <?php esc_html_e( 'Payment Made :', 'mjschool' ); ?></td>
						<td <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?> style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->fees_paid_amount, 2, '.', '' ) ) ); ?> </td>
					</tr>
					<tr>
						<td width="80%" <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?> style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;"> <?php esc_html_e( 'Due Amount :', 'mjschool' ); ?></td>
						<?php $Due_amount = $fees_detail_result->total_amount - $fees_detail_result->fees_paid_amount; ?>
						<td <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?> style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;">
							<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Due_amount, 2, '.', '' ) ) ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php } ?>
	</div>
	<?php
	$subtotal    = $fees_detail_result->total_amount;
	$paid_amount = $fees_detail_result->fees_paid_amount;
	$grand_total = $subtotal - $paid_amount;
	?>
	<table style="width:100%">
		<tbody>
			<tr>
				<td width="62%" align="left"></td>
				<td align="right" style="float:right; background-color:  <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;color: #fff;">
					<table style="background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;color: #fff;">
						<tbody>
							<tr>
								<?php
								$subtotal    = $fees_detail_result->total_amount;
								$paid_amount = $fees_detail_result->fees_paid_amount;
								$grand_total = $subtotal - $paid_amount;
								?>
								<td style="background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;color: #fff;padding:10px">
									<h3> <?php esc_html_e( 'Grand Total', 'mjschool' ); ?> </h3>
								</td>
								<td style="background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;color: #fff;padding:10px;">
									<h3>
										<?php
										$formatted_amount = number_format( $subtotal, 2, '.', '' );
										$currency         = mjschool_get_currency_symbol();
										echo esc_html( "($currency)$formatted_amount" );
										?>
									</h3>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	if ( ! empty( $fees_history_detail_result ) && sanitize_text_field(wp_unslash($_REQUEST['certificate_header'])) == 1 ) {
		?>
		<table class="mjschool-width-100px mjschool-margin-top-10px-res">
			<tbody>
				<tr>
					<td> <p class="display_name mjschool-res-pay-his-mt-10px"><?php esc_html_e( 'Payment History', 'mjschool' ); ?> </p> </td>
				</tr>
			</tbody>
		</table>
		<div class="table-responsive mjschool-rtl-padding-left-40px">
			<table width="100%" style="border-collapse: collapse; border: 1px solid black; margin-top: 10px;">
				<thead style="background-color: #b8daff;">
					<tr>
						<th style="text-align: center; font-weight: 600; color: black; padding: 8px; border: 1px solid black; font-size: 14px;background-color: #b8daff;"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
						<th style="text-align: center; font-weight: 600; color: black; padding: 8px; border: 1px solid black; font-size: 14px;background-color: #b8daff;"><?php esc_html_e( 'Method', 'mjschool' ); ?></th>
						<th style="text-align: center; font-weight: 600; color: black; padding: 8px; border: 1px solid black; font-size: 14px;background-color: #b8daff;"><?php echo esc_html__( 'Amount', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $fees_history_detail_result as $retrive_date ) { ?>
						<tr>
							<td style="text-align: center; padding: 8px; border: 1px solid black; font-size: 13px;"> <?php echo esc_html( mjschool_get_date_in_input_box( $retrive_date->paid_by_date ) ); ?></td>
							<td style="text-align: center; padding: 8px; border: 1px solid black; font-size: 13px;"> <?php echo esc_html( $retrive_date->payment_method ); ?></td>
							<td style="text-align: center; padding: 8px; border: 1px solid black; font-size: 13px;"> <?php echo esc_html( number_format( $retrive_date->amount, 2, '.', '' ) ); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
		$total_payment = 0;
		foreach ( $fees_history_detail_result as $retrive_date ) {
			$total_payment += floatval( $retrive_date->amount );
		}
		$subtotal         = $total_payment;
		$currency         = mjschool_get_currency_symbol();
		$formatted_amount = number_format( $subtotal, 2, '.', '' );
		?>
		<table width="100%" style="margin-top:20px; border-collapse: collapse;">
			<tr>
				<td width="50%"></td>
				<td width="50%" align="right">
					<table width="100%" style="border-collapse: collapse; background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>; color: #fff;"> 
						<tr>
							<td style="padding: 10px; font-size: 16px; font-weight: bold;"> <?php esc_html_e( 'Total Payment', 'mjschool' ); ?> </td>
							<td style="padding: 10px; font-size: 16px; font-weight: bold;" align="right"> (<?php echo esc_html( $currency ); ?>)<?php echo esc_html( $formatted_amount ); ?> </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<?php
	}
	?>
	<div class=""
		style="border: 2px solid; width:100%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;margin-top: 4px;">
		<!-- Teacher Signature (Middle) -->
		<div style="float: right; width: 33.33%; text-align: center;">
			<div>
				<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px;" />
			</div>
			<div style="border-top: 1px solid #000; width: 150px; margin: 5px auto;"></div>
			<div style="margin-top: 5px;">
				<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Prints the invoice layout for students, income, or expense entries.
 *
 * This function prepares and renders the full printable invoice HTML,
 * including school details, user/party information, payment status,
 * issue date, address, and formatted invoice sections based on
 * invoice type (invoice, income, or expense). The layout style is
 * determined by the invoice format setting (classic or bordered).
 *
 * It also auto-loads print styles, applies RTL adjustments,
 * and outputs inline styles required for proper print rendering.
 *
 * @since 1.0.0
 *
 * @param int $invoice_id The ID of the invoice, income, or expense record.
 *
 * @return void Outputs HTML directly to the browser for print display.
 */
function mjschool_student_invoice_print( $invoice_id ) {
	wp_print_styles();
	$format      = get_option( 'mjschool_invoice_option' );
	$obj_invoice = new mjschool_invoice();
	$sanitize_invoice_type = isset($_REQUEST['invoice_type']) ? sanitize_text_field(wp_unslash($_REQUEST['invoice_type'])) : '';
	if ( $sanitize_invoice_type === 'invoice' ) {
		$invoice_data = mjschool_get_payment_by_id( $invoice_id );
	}
	if ( $sanitize_invoice_type === 'income' ) {
		$income_data = $obj_invoice->mjschool_get_income_data( $invoice_id );
	}
	if ( $sanitize_invoice_type === 'expense' ) {
		$expense_data = $obj_invoice->mjschool_get_income_data( $invoice_id );
	}
	if (is_rtl() && $format === 1) {
		?>
		<style>
			.rtl_billto {
				margin-right: -18px !important;
			}
		</style>
		<?php
	}
	?>
	<style>
		body,
		body * {
			font-family: 'Poppins' !important;
		}
	</style>
	<style>
		table thead {
			-webkit-print-color-adjust: exact;
		}
		.mjschool-invoice-table-grand-total {
			-webkit-print-color-adjust: exact;
			background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?> ;
		}
		@media print {
			* {
				color-adjust: exact !important;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}
		}
	</style>
	<html>
	<?php
	if ( is_rtl() ) {
		?>
		<div class="modal-body mjschool-margin-top-15px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool-custom-padding-0_res">
			<!---- model body  ----->
			<?php if ( $format === 0 ) { ?>
				
				<img class="mjschool-rtl-image-set-invoice mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url(plugins_url( '/mjschool/assets/images/listpage_icon/invoice.png' ) ); ?>" width="100%">
			<?php } ?>
			<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
				<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
					<?php if ($format === 1) { ?>
						<div class="mjschool-width-print" class="mjschool_print_invoice_width_95">
							<div class="mjschool_float_left_width_100">
								<div class="mjschool_float_left_width_25">
									<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
										<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
									</div>
								</div>
								<div class="mjschool_float_left_width_75">
									<p class="mjschool_print_invoice_blue"> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </p>
									<p class="mjschool_print_invoice_line_height_30px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?> </p>
									<div class="mjschool_fees_center_margin_0px">
										<p class="mjschool_print_invoice_fit"> <?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?> </p>
										<p class="mjschool_print_invoice_fit">
											&nbsp;&nbsp; <?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
					<?php } else { ?>
						<h3 > <?php echo esc_attr(get_option( 'mjschool_name' ) ) ?> </h3>
						<div class="row mjschool-margin-top-20px mjschool_margin_right_15px">
							<div class="col-md-1 col-sm-2 col-xs-3 mjschool_width_8" >
								<div class="width_1 mjschool-rtl-width-80px"> <img class="system_logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>"> </div>
							</div>
							
							<div class="mjschool_width_91 col-md-11 col-sm-10 col-xs-9 mjschool-invoice-address mjschool-invoice-address-css">
								<div class="row">
									<div class="col-md-12 col-sm-12 col-xs-12 mjschool-invoice-padding-bottom-15px mjschool-padding-right-0 mjschool-width-25px-res">
										<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Address', 'mjschool' ); ?> </label><br>
										<label class="mjschool_padding_top_10px mjschool-label-value mjschool-word-break-all">
											<?php
											$school_address  = get_option( 'mjschool_address' );
											$escaped_address = esc_html( $school_address );
											$split_address   = str_replace( '<br>', '<BR>', chunk_split( $escaped_address, 100, '<br>' ) );
											echo wp_kses_post( $split_address );
											?>
										</label>
									</div>
									<div class="row col-md-12 mjschool-invoice-padding-bottom-15px">
										<div class="mjschool_width_50 col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-email-width-auto">
											<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Email', 'mjschool' ); ?> </label><br>
											<label class="mjschool_padding_top_10px mjschool-label-value mjschool-word-break-all"> <?php echo esc_html( get_option( 'mjschool_email' ) ), '<BR>'; ?> </label>
										</div>
										<div class="mjschool_width_50 col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-padding-left-30px">
											<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Phone', 'mjschool' ); ?> </label><br>
											<label class="mjschool_padding_top_10px mjschool-label-value"> <?php echo esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>'; ?> </label>
										</div>
									</div>
									<div align="right" class="mjschool-width-24px"></div>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="col-md-12 col-sm-12 col-xl-12 mjschool-mozila-display-css mjschool-margin-top-20px">
						<?php
						if ( $format === 1 ) {
							?>
							<div class="mjschool-width-print mjschool_print_paddings_width_99">
								<div class="mjschool_float_left_width_100">
									<?php
									if ( ! empty( $expense_data ) ) {
										$party_name = $expense_data->supplier_name;
										$party_name = $expense_data->supplier_name;
										$ex_name = $party_name
										? wp_kses_post( chunk_split( ucwords( $party_name ), 30, '<br>' ) )
										: 'N/A';
									} else {
										$student_id = ! empty( $income_data ) ? $income_data->supplier_name : $invoice_data->student_id;
										$patient    = get_userdata( $student_id );
										$in_name    = $patient ? wp_kses_post( chunk_split( ucwords( $patient->display_name ), 30, '<br>' ) ) : 'N/A';
									}
									?>
									<div  class="mjschool_padding_10px">
										<div class="mjschool_float_left_width_65">
											<b> <?php esc_html_e( 'Bill To', 'mjschool' ); ?>: </b> <?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ) . ' ' . esc_html( get_user_meta( $uid, 'student_id', true ) ); ?>&nbsp;
											<?php
											if ( ! empty( $expense_data ) ) {
												// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
												echo wp_kses_post( $ex_name );
												// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
											} else {
												echo esc_html( mjschool_student_display_name_with_roll( $student_id ) );
											}
											?>
										</div>
										<div class="mjschool_float_right_width_35">
											<b> <?php esc_html_e( 'Status', 'mjschool' ); ?>: </b>
											<?php
											$payment_status = '';
											if ( ! empty( $income_data ) ) {
												$payment_status = $income_data->payment_status;
											}
											if ( ! empty( $invoice_data ) ) {
												$payment_status = $invoice_data->payment_status;
											}
											if ( ! empty( $expense_data ) ) {
												$payment_status = $expense_data->payment_status;
											}
											switch ( $payment_status ) {
												case 'Paid':
													echo '<span class="mjschool-green-color">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
													break;
												case 'Part Paid':
													echo '<span class="mjschool-purpal-color">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
													break;
												case 'Unpaid':
													echo '<span class="mjschool-red-color">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
													break;
												default:
													esc_html_e( 'N/A', 'mjschool' );
											}
											?>
										</div>
									</div>
								</div>
								<div class="mjschool_float_left_width_65">
									<?php
									if ( empty( $expense_data ) ) {
										$student_id = ! empty( $income_data ) ? $income_data->supplier_name : $invoice_data->student_id;
										$address    = esc_html( get_user_meta( $student_id, 'address', true ) );
										$city       = esc_html( get_user_meta( $student_id, 'city', true ) );
										$zip        = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
										?>
										<div class="mjschool_padding_10px">
											<div><b> <?php esc_html_e( 'Address', 'mjschool' ); ?>: </b> <?php echo esc_html( $address ); ?> </div>
											<div> <?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?> </div>
										</div>
									<?php } ?>
								</div>
								<div class="mjschool_float_right_width_35">
									<?php
									$issue_date = 'DD-MM-YYYY';
									if ( ! empty( $income_data ) ) {
										$issue_date = $income_data->income_create_date;
									}
									if ( ! empty( $invoice_data ) ) {
										$issue_date = $invoice_data->date;
									}
									if ( ! empty( $expense_data ) ) {
										$issue_date = $expense_data->income_create_date;
									}
									?>
									<div class="mjschool_fees_padding_10px">
										<div class="mjschool_float_left_width_100">
											<b> <?php esc_html_e( 'Issue Date', 'mjschool' ); ?>: </b> <?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
										</div>
									</div>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="row">
								<div class="mjschool-float-left-width-100px">
									<div class="col-md-12 col-sm-12 col-xs-12 mjschool-custom-padding-0 mjschool-float-left mjschool-display-grid mjschool-display-inherit-res mjschool-margin-bottom-20px">
										<div class="mjschool-billed-to mjschool-display-flex mjschool-display-inherit-res mjschool-invoice-address-heading mjschool_float_left_width_100" >
											<?php
											$issue_date = 'DD-MM-YYYY';
											if ( ! empty( $income_data ) ) {
												$issue_date     = $income_data->income_create_date;
												$payment_status = $income_data->payment_status;
											}
											if ( ! empty( $invoice_data ) ) {
												$issue_date     = $invoice_data->date;
												$payment_status = $invoice_data->payment_status;
											}
											if ( ! empty( $expense_data ) ) {
												$issue_date     = $expense_data->income_create_date;
												$payment_status = $expense_data->payment_status;
											}
											?>
											<div class="mjschool_flex_width_100">
												<div class="mjschool_width_50">
													<h3 class="mjschool-billed-to-lable mjschool-invoice-model-heading mjschool-bill-to-width-12px mjschool_width_100" > <?php esc_html_e( 'Bill To', 'mjschool' ); ?> : </h3>
												</div>
												<div class="mjschool_float_left_width_100">
													<?php
													if ( ! empty( $expense_data ) ) {
														$party_name   = $expense_data->supplier_name;
														$escaped_name = esc_html( ucwords( $party_name ) );
														$split_name   = chunk_split( $escaped_name, 100 );
														echo '<h3 class="display_name mjschool-invoice-width-100px mjschool_float_right_width_100" >' . wp_kses_post( $split_name ) . '</h3>';
													} else {
														if ( ! empty( $income_data ) ) {
															$student_id = $income_data->supplier_name;
														}
														if ( ! empty( $invoice_data ) ) {
															$student_id = $invoice_data->student_id;
														}
														$patient      = get_userdata( $student_id );
														$display_name = esc_html( ucwords( $patient->display_name ) );
														$split_name   = str_replace( '<br>', '<BR>', chunk_split( $display_name, 100, '<br>' ) );
														echo '<h3 class="display_name mjschool-invoice-width-100px mjschool_float_right_width_100 mjschool_float_right_width_100">' . wp_kses_post( $split_name ) . '</h3>';
													}
													?>
												</div>
											</div>
										</div>
										<div class="mjschool-address-information-invoice mjschool_float_right_width_50" width="100%" >
											<?php
											if ( ! empty( $expense_data ) ) {
												$party_name   = $expense_data->supplier_name;
												$escaped_name = esc_html( ucwords( $party_name ) );
												$split_name   = str_replace( '<br>', '<BR>', chunk_split( $escaped_name, 30, '<br>' ) );
												echo '<h3 class="display_name mjschool-invoice-width-100px">' . wp_kses_post( $split_name ) . '</h3>';
											} else {
												if ( ! empty( $income_data ) ) {
													$student_id = $income_data->supplier_name;
												}
												if ( ! empty( $invoice_data ) ) {
													$student_id = $invoice_data->student_id;
												}
												$patient = get_userdata( $student_id );
												$address         = get_user_meta( $student_id, 'address', true );
												$escaped_address = esc_html( $address );
												$split_address   = str_replace( '<br>', '<BR>', chunk_split( $escaped_address, 30, '<br>' ) );
												echo wp_kses_post( $split_address );
												echo esc_html( get_user_meta( $student_id, 'city', true ) ) . ',' . '<BR>';
												echo esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . ',<BR>';
											}
											?>
										</div>
									</div>
									<div class="col-md-3 col-sm-3 col-xs-3" class="mjschool_float_left_width_100">
										<div class="mjschool-width-50px">
											<div class="mjschool-width-20px" align="right">
												<h5 class="mjschool-align-left"> 
													<label class="mjschool-popup-label-heading text-transfer-upercase"> <?php echo esc_html__( 'Date :', 'mjschool' ); ?> </label>&nbsp; 
													<label class="mjschool-invoice-model-value"> <?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?> </label>
												</h5>
												<h5 class="mjschool-align-left">
													<label class="mjschool-popup-label-heading text-transfer-upercase"> <?php echo esc_html__( 'Status :', 'mjschool' ); ?> </label> &nbsp;
													<label class="mjschool-invoice-model-value">
														<?php
														if ( $payment_status === 'Paid' ) {
															echo '<span class="mjschool-green-color">' . esc_attr__( 'Fully Paid', 'mjschool' ) . '</span>';
														}
														if ( $payment_status === 'Part Paid' ) {
															echo '<span class="mjschool-purpal-color">' . esc_attr__( 'Partially Paid', 'mjschool' ) . '</span>';
														}
														if ( $payment_status === 'Unpaid' ) {
															echo '<span class="mjschool-red-color">' . esc_attr__( 'Not Paid', 'mjschool' ) . '</span>';
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
					<table class="mjschool-width-100px mjschool-margin-top-10px-res">
						<tbody>
							<tr>
								<td>
									<?php
									if ( ! empty( $invoice_data ) ) {
										?>
										<h3 class="display_name"> <?php esc_html_e( 'Invoice Entries', 'mjschool' ); ?> </h3>
										<?php
									} elseif ( ! empty( $income_data ) ) {
										?>
										<h3 class="display_name"> <?php esc_html_e( 'Income Entries', 'mjschool' ); ?> </h3>
										<?php
									} elseif ( ! empty( $expense_data ) ) {
										?>
										<h3 class="display_name"> <?php esc_html_e( 'Expense Entries', 'mjschool' ); ?> </h3>
										<?php
									}
									?>
								<td>
							</tr>
						</tbody>
					</table>
					<?php if ( $format === 1 ) { ?>
						<div class="table-responsive mjschool-rtl-padding-left-40px">
							<table class="table table-bordered mjschool-model-invoice-table mjschool_ul_style">
								<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_white_black_color" >
									<tr>
										<th class="text-center mjschool_blacks_1px_border_15" > <?php esc_html_e( 'Number', 'mjschool' ); ?></th>
										<th class="text-center mjschool_blacks_1px_border_20"> <?php esc_html_e( 'Date', 'mjschool' ); ?></th>
										<th class="text-center mjschool_blacks_1px_border"> <?php esc_html_e( 'Entry', 'mjschool' ); ?></th>
										<th class="text-center mjschool_blacks_1px_border"> <?php esc_html_e( 'Issued By', 'mjschool' ); ?></th>
										<th class="text-center mjschool_blacks_1px_border_15" > <?php echo esc_html__( 'Price', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$id           = 1;
									$total_amount = 0;
									if ( ! empty( $income_data ) || ! empty( $expense_data ) ) {
										if ( ! empty( $expense_data ) ) {
											$income_data = $expense_data;
										}
										$patient_all_income = $obj_invoice->mjschool_get_onepatient_income_data( $income_data->supplier_name );
										foreach ( $patient_all_income as $result_income ) {
											$income_entries = json_decode( $result_income->entry );
											foreach ( $income_entries as $each_entry ) {
												$total_amount += $each_entry->amount;
												?>
												<tr>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( $id ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( $result_income->income_create_date ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( $each_entry->entry ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( mjschool_get_display_name( $result_income->create_by ) ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( number_format( $each_entry->amount, 2, '.', '' ) ); ?> </td>
												</tr>
												<?php
												$id += 1;
											}
										}
									}
									if ( ! empty( $invoice_data ) ) {
										$total_amount = $invoice_data->amount
										?>
										<tr>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( $id ); ?> </td>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( date( 'Y-m-d', strtotime( $invoice_data->date ) ) ); ?> </td>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( $invoice_data->payment_title ); ?> </td>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( mjschool_get_display_name( $invoice_data->payment_reciever_id ) ); ?> </td>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"> <?php echo esc_html( number_format( $invoice_data->amount, 2, '.', '' ) ); ?> </td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
						<?php
					} else {
						?>
						<div class="table-responsive">
							<table class="table mjschool-model-invoice-table">
								<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading">
									<tr>
										<th class="mjschool-entry-table-heading mjschool-align-center">#</th>
										<th class="mjschool-entry-table-heading mjschool-align-center"> <?php esc_html_e( 'Date', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-center"> <?php esc_html_e( 'Entry', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-center"> <?php esc_html_e( 'Price', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-center"> <?php esc_html_e( 'Issue By', 'mjschool' ); ?> </th>
									</tr>
								</thead>
								<tbody>
									<?php
									$id           = 1;
									$total_amount = 0;
									if ( ! empty( $income_data ) || ! empty( $expense_data ) ) {
										if ( ! empty( $expense_data ) ) {
											$income_data = $expense_data;
										}
										$patient_all_income = $obj_invoice->mjschool_get_onepatient_income_data( $income_data->supplier_name );
										foreach ( $patient_all_income as $result_income ) {
											$income_entries = json_decode( $result_income->entry );
											foreach ( $income_entries as $each_entry ) {
												$total_amount += $each_entry->amount;
												?>
												<tr>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( $id ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( $result_income->income_create_date ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( $each_entry->entry ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $each_entry->amount, 2, '.', '' ) ) ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( mjschool_get_display_name( $result_income->create_by ) ); ?> </td>
												</tr>
												<?php
												$id += 1;
											}
										}
									}
									if ( ! empty( $invoice_data ) ) {
										$total_amount = $invoice_data->amount
										?>
										<tr>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $id ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( date( 'Y-m-d', strtotime( $invoice_data->date ) ) ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $invoice_data->payment_title ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $invoice_data->amount, 2, '.', '' ) ) ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_get_display_name( $invoice_data->payment_reciever_id ) ); ?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
						<?php
					}
					if ( ! empty( $invoice_data ) ) {
						$grand_total = $total_amount;
						$sub_total   = $invoice_data->fees_amount;
						if ( ! empty( $invoice_data->tax ) ) {
							$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $invoice_data->tax ) );
						} else {
							$tax_name = '';
						}
						if ( $invoice_data->discount ) {
							$discount_name = mjschool_get_discount_name( $invoice_data->discount, $invoice_data->discount_type );
						} else {
							$discount_name = '';
						}
					}
					if ( ! empty( $income_data ) ) {
						$grand_total = $total_amount;
					}
					?>
					<div class="table-responsive mjschool-rtl-padding-left-40px mjschool-rtl-float-left-width-100px">
						<table width="100%" border="0">
							<tbody>
								<tr>
									<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right"> <?php echo esc_attr__( 'Sub Total', 'mjschool' ) . '  :'; ?> </td>
									<td align="left" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $sub_total, 2, '.', '' ) ) ); ?> </td>
								</tr>
								<?php if ( isset( $invoice_data->tax_amount ) && ! empty( $invoice_data->tax_amount ) ) { ?>
									<tr>
										<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right"> <?php echo esc_attr__( 'Tax Amount', 'mjschool' ) . '( ' . esc_html( $tax_name ) . ' )' . '  :'; ?> </td>
										<td align="left" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value"> <?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $invoice_data->tax_amount, 2, '.', '' ) ) ); ?> </td>
									</tr>
								<?php } ?>
								<?php if ( isset( $invoice_data->discount_amount ) && ! empty( $invoice_data->discount_amount ) ) { ?>
									<tr>
										<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right"> <?php echo esc_attr__( 'Discount Amount', 'mjschool' ) . '( ' . esc_html( $discount_name ) . ' )' . '  :'; ?> </td>
										<td align="left" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value"> <?php echo '-' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $invoice_data->discount_amount, 2, '.', '' ) ) ); ?> </td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<div class="row mjschool-margin-top-10px-res col-md-6 col-sm-6 col-xs-6 mjschool-view-invoice-lable-css mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total mjschool_width_50" >
						<div class="mjschool_width_50 mjschool-width-50-res mjschool-align-right col-md-8 col-sm-8 col-xs-8 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
							<h3  class="padding mjschool-color-white margin mjschool-invoice-total-label mjschool_float_right"> <?php esc_html_e( 'Grand Total', 'mjschool' ); ?> </h3>
						</div>
						<div class="mjschool_width_50 mjschool-width-50-res mjschool-align-right col-md-4 col-sm-4 col-xs-4 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
							<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value mjschool_float_right" > <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $grand_total, 2, '.', '' ) ) ); ?> </h3>
						</div>
					</div>
					<div  class="mjschool_margin_overflow_hidden_widht_96">
						<!-- Teacher Signature (Middle) -->
						<div class="mjschool_fees_center_width_33">
							<div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" /> </div>
							<div class="mjschool_fees_width_150px"></div>
							<div class="mjschool_margin_top_5px"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
						</div>
					</div>
					<div class="mjschool-margin-top-20px"></div>
				</div>
			</div>
		</div><!---- model body  ----->
		<?php
	} else {
		?>
		<div class="modal-body mjschool-margin-top-15px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool-custom-padding-0_res">
			<!---- model body  ----->
			<?php if ( $format === 0 ) { 
				 ?>
				<img class="mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url(plugins_url( '/mjschool/assets/images/listpage_icon/invoice.png' ) ); ?>" width="100%">
			<?php } ?>
			<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
				<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
					<?php if ($format === 1) { ?>
						<div class="mjschool-width-print mjschool_left_padding_margin_width_100" >
							<div class="mjschool_float_left_width_100">
								<div class="mjschool_float_left_width_25">
									<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
										<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
									</div>
								</div>
								<div class="mjschool_float_left_width_75">
									<p class="mjschool_print_invoice_blue"> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </p>
									<p class="mjschool_print_invoice_line_height_30px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?> </p>
									<div class="mjschool_fees_center_margin_0px">
										<p class="mjschool_fees_width_fit_content_inline"> <?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?> </p>
										<p class="mjschool_print_invoice_fit"> &nbsp;&nbsp; <?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?> </p>
									</div>
								</div>
							</div>
						</div>
					<?php } else { ?>
						<h3 > <?php echo esc_html( get_option( 'mjschool_name' ) ) ?> </h3>
						<div class="row mjschool-margin-top-20px">
							<div class="col-md-1 col-sm-2 col-xs-3 mjschool_width_8">
								<div class="width_1"> <img class="system_logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>"> </div>
							</div>
							
							<div class="mjschool_width_91 col-md-11 col-sm-10 col-xs-9 mjschool-invoice-address mjschool-invoice-address-css">
								<div class="row">
									<div class="col-md-12 col-sm-12 col-xs-12 mjschool-invoice-padding-bottom-15px mjschool-padding-right-0 mjschool-width-25px-res">
										<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Address', 'mjschool' ); ?> </label><br>
										<label class="mjschool_padding_top_10px mjschool-label-value mjschool-word-break-all">
											<?php
											$school_address  = get_option( 'mjschool_address' );
											$escaped_address = esc_html( $school_address );
											$split_address   = str_replace( '<br>', '<BR>', chunk_split( $escaped_address, 100, '<br>' ) );
											echo wp_kses_post( $split_address );
											?>
										</label>
									</div>
									<div class="row col-md-12 mjschool-invoice-padding-bottom-15px">
										<div class="mjschool_width_50 col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-email-width-auto">
											<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Email', 'mjschool' ); ?> </label><br>
											<label class="mjschool_padding_top_10px mjschool-label-value mjschool-word-break-all"> <?php echo esc_html( get_option( 'mjschool_email' ) ), '<BR>'; ?> </label>
										</div>
										<div class="mjschool_width_50 col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-padding-left-30px">
											<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Phone', 'mjschool' ); ?> </label><br>
											<label class="mjschool_padding_top_10px mjschool-label-value"> <?php echo esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>'; ?> </label>
										</div>
									</div>
									<div align="right" class="mjschool-width-24px"></div>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="col-md-12 col-sm-12 col-xl-12 mjschool-mozila-display-css mjschool-margin-top-20px">
						<?php
						if ( $format === 1 ) {
							?>
							<div class="mjschool-width-print mjschool_print_paddings" >
								<div class="mjschool_float_left_width_100">
									<?php
									$student_id = null;
									if ( ! empty( $expense_data ) ) {
										$ex_name = $party_name
										? wp_kses_post( chunk_split( ucwords( $party_name ), 30, '<br>' ) )
										: 'N/A';
									} else {
										$student_id = ! empty( $income_data ) ? $income_data->supplier_name : $invoice_data->student_id;
										$patient    = get_userdata( $student_id );
										$in_name    = $patient ? wp_kses_post( chunk_split( ucwords( $patient->display_name ), 30, '<br>' ) ) : 'N/A';
									}
									?>
									<div  class="mjschool_padding_10px">
										<div class="mjschool_float_left_width_65">
											<b> <?php esc_html_e( 'Bill To', 'mjschool' ); ?>: </b> <?php echo esc_html( get_user_meta( $student_id, 'first_name', true ) ) . ' ' . esc_html( get_user_meta( $student_id, 'student_id', true ) ); ?>&nbsp;
											<?php
											if ( ! empty( $expense_data ) ) {
												// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
												echo wp_kses_post( $ex_name );
												// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
											} else {
												echo esc_html( mjschool_student_display_name_with_roll( $student_id ) );
											}
											?>
										</div>
										<div class="mjschool_float_right_width_35">
											<b> <?php esc_html_e( 'Status', 'mjschool' ); ?>: </b>
											<?php
											$payment_status = '';
											if ( ! empty( $income_data ) ) {
												$payment_status = $income_data->payment_status;
											}
											if ( ! empty( $invoice_data ) ) {
												$payment_status = $invoice_data->payment_status;
											}
											if ( ! empty( $expense_data ) ) {
												$payment_status = $expense_data->payment_status;
											}
											switch ( $payment_status ) {
												case 'Paid':
													echo '<span class="mjschool-green-color">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
													break;
												case 'Part Paid':
													echo '<span class="mjschool-purpal-color">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
													break;
												case 'Unpaid':
													echo '<span class="mjschool-red-color">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
													break;
												default:
													esc_html_e( 'N/A', 'mjschool' );
											}
											?>
										</div>
									</div>
								</div>
								<div class="mjschool_float_left_width_65">
									<?php
									if ( empty( $expense_data ) ) {
										$student_id = ! empty( $income_data ) ? $income_data->supplier_name : $invoice_data->student_id;
										$address    = esc_html( get_user_meta( $student_id, 'address', true ) );
										$city       = esc_html( get_user_meta( $student_id, 'city', true ) );
										$zip        = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
										?>
										<div class="mjschool_padding_10px">
											<div> <b> <?php esc_html_e( 'Address', 'mjschool' ); ?>: </b> <?php echo esc_html( $address ); ?> </div>
											<div> <?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?> </div>
										</div>
									<?php } ?>
								</div>
								<div class="mjschool_float_right_width_35">
									<?php
									$issue_date = 'DD-MM-YYYY';
									if ( ! empty( $income_data ) ) {
										$issue_date = $income_data->income_create_date;
									}
									if ( ! empty( $invoice_data ) ) {
										$issue_date = $invoice_data->date;
									}
									if ( ! empty( $expense_data ) ) {
										$issue_date = $expense_data->income_create_date;
									}
									?>
									<div class="mjschool_fees_padding_10px">
										<div class="mjschool_float_left_width_100">
											<b> <?php esc_html_e( 'Issue Date', 'mjschool' ); ?>: </b> <?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
										</div>
									</div>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="row">
								<div class="mjschool-float-left-width-100px">
									<div class="col-md-12 col-sm-12 col-xs-12 mjschool-custom-padding-0 mjschool-float-left mjschool-display-grid mjschool-display-inherit-res mjschool-margin-bottom-20px">
										<div class="mjschool-billed-to mjschool-display-flex mjschool-display-inherit-res mjschool-invoice-address-heading mjschool_float_left_width_100" >
											<?php
											$issue_date = 'DD-MM-YYYY';
											if ( ! empty( $income_data ) ) {
												$issue_date     = $income_data->income_create_date;
												$payment_status = $income_data->payment_status;
											}
											if ( ! empty( $invoice_data ) ) {
												$issue_date     = $invoice_data->date;
												$payment_status = $invoice_data->payment_status;
											}
											if ( ! empty( $expense_data ) ) {
												$issue_date     = $expense_data->income_create_date;
												$payment_status = $expense_data->payment_status;
											}
											?>
											<div class="mjschool_flex_width_100">
												<div class="mjschool_width_50">
													<h3 class="mjschool-billed-to-lable mjschool-invoice-model-heading mjschool-bill-to-width-12px mjschool_width_150px"> <?php esc_html_e( 'Bill To', 'mjschool' ); ?> : </h3>
												</div>
												<div class="mjschool_float_left_width_100">
													<?php
													if ( ! empty( $expense_data ) ) {
														$party_name = $expense_data->supplier_name;
														if ( $party_name ) {
															$escaped_name = esc_html( ucwords( $party_name ) );
															$split_name   = str_replace( '<br>', '<BR>', chunk_split( $escaped_name, 30, '<br>' ) );
															echo '<h3 class="display_name mjschool-invoice-width-100px">' . wp_kses_post( $split_name ) . '</h3>';
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
													} else {
														if ( ! empty( $income_data ) ) {
															$student_id = $income_data->supplier_name;
														}
														if ( ! empty( $invoice_data ) ) {
															$student_id = $invoice_data->student_id;
														}
														$patient = get_userdata( $student_id );
														if ( $patient ) {
															$display_name = esc_html( ucwords( $patient->display_name ) );
															$split_name   = str_replace( '<br>', '<BR>', chunk_split( $display_name, 100, '<br>' ) );
															echo '<h3 class="display_name mjschool-invoice-width-100px mjschool_float_right_width_100" >' . wp_kses_post( $split_name ) . '</h3>';
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
													}
													?>
												</div>
											</div>
										</div>
										<div class="mjschool-address-information-invoice" width="100%" class="mjschool_float_left_width_100">
											<?php
											if ( ! empty( $expense_data ) ) {
												$party_name = $expense_data->supplier_name;
											} else {
												if ( ! empty( $income_data ) ) {
													$student_id = $income_data->supplier_name;
												}
												if ( ! empty( $invoice_data ) ) {
													$student_id = $invoice_data->student_id;
												}
												$patient         = get_userdata( $student_id );
												$address         = get_user_meta( $student_id, 'address', true );
												$escaped_address = esc_html( $address );
												$split_address   = str_replace( '<br>', '<BR>', chunk_split( $escaped_address, 30, '<br>' ) );
												echo wp_kses_post( $split_address );
												echo esc_html( get_user_meta( $student_id, 'city', true ) ) . ',' . '<BR>';
												echo esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . ',<BR>';
											}
											?>
										</div>
									</div>
									<div class="col-md-3 col-sm-3 col-xs-3 mjschool-float-left mjschool-right" >
										<div class="mjschool-width-50px">
											<div class="mjschool-width-20px" align="right">
												<h5 class="mjschool-align-left"> 
													<label class="mjschool-popup-label-heading text-transfer-upercase"> <?php echo esc_html__( 'Date :', 'mjschool' ); ?> </label>&nbsp; 
													<label class="mjschool-invoice-model-value"> <?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?> </label>
												</h5>
												<h5 class="mjschool-align-left">
													<label class="mjschool-popup-label-heading text-transfer-upercase"> <?php echo esc_html__( 'Status :', 'mjschool' ); ?> </label> &nbsp;
													<label class="mjschool-invoice-model-value">
														<?php
														if ( $payment_status === 'Paid' ) {
															echo '<span class="mjschool-green-color mjschool_green_colors" >' . esc_attr__( 'Fully Paid', 'mjschool' ) . '</span>';
														}
														if ( $payment_status === 'Part Paid' ) {
															echo '<span class="mjschool-purpal-color" >' . esc_attr__( 'Partially Paid', 'mjschool' ) . '</span>';
														}
														if ( $payment_status === 'Unpaid' ) {
															echo '<span class="mjschool-red-color" >' . esc_attr__( 'Not Paid', 'mjschool' ) . '</span>';
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
					<table class="mjschool-width-100px mjschool-margin-top-10px-res">
						<tbody>
							<tr>
								<td>
									<?php
									if ( ! empty( $invoice_data ) ) {
										?>
										<h3 class="display_name"> <?php esc_html_e( 'Invoice Entries', 'mjschool' ); ?> </h3>
										<?php
									} elseif ( ! empty( $income_data ) ) {
										?>
										<h3 class="display_name"> <?php esc_html_e( 'Income Entries', 'mjschool' ); ?> </h3>
										<?php
									} elseif ( ! empty( $expense_data ) ) {
										?>
										<h3 class="display_name"> <?php esc_html_e( 'Expense Entries', 'mjschool' ); ?> </h3>
										<?php
									}
									?>
								<td>
							</tr>
						</tbody>
					</table>
					<?php if ( $format === 1 ) { ?>
						<div class="table-responsive mjschool-rtl-padding-left-40px">
							<table class="table table-bordered mjschool-model-invoice-table mjschool-margin-bottom-0px" >
								<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_white_black_color" >
									<tr>
										<th class="text-center mjschool_blacks_1px_border_15" ><?php esc_html_e( 'Number', 'mjschool' ); ?></th>
										<th class="tex-center mjschool_blacks_1px_border_20" ><?php esc_html_e( 'Date', 'mjschool' ); ?> </th>
										<th class="text-center mjschool_blacks_1px_border"><?php esc_html_e( 'Entry', 'mjschool' ); ?></th>
										<th class="text-center mjschool_blacks_1px_border" ><?php esc_html_e( 'Issued By', 'mjschool' ); ?></th>
										<th class="text-center mjschool_blacks_1px_border_15" ><?php echo esc_html__( 'Price', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$id           = 1;
									$total_amount = 0;
									if ( ! empty( $income_data ) || ! empty( $expense_data ) ) {
										if ( ! empty( $expense_data ) ) {
											$income_data = $expense_data;
										}
										$patient_all_income = $obj_invoice->mjschool_get_onepatient_income_data( $income_data->supplier_name );
										foreach ( $patient_all_income as $result_income ) {
											$income_entries = json_decode( $result_income->entry );
											foreach ( $income_entries as $each_entry ) {
												$total_amount += $each_entry->amount;
												?>
												<tr>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( $id ); ?></td>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( $result_income->income_create_date ); ?></td>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( $each_entry->entry ); ?></td>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( mjschool_get_display_name( $result_income->create_by ) ); ?></td>
													<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( number_format( $each_entry->amount, 2, '.', '' ) ); ?></td>
												</tr>
												<?php
												$id += 1;
											}
										}
									}
									if ( ! empty( $invoice_data ) ) {
										$total_amount = $invoice_data->amount
										?>
										<tr>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( $id ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( date( 'Y-m-d', strtotime( $invoice_data->date ) ) ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( $invoice_data->payment_title ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( mjschool_get_display_name( $invoice_data->payment_reciever_id ) ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data mjschool_border_black_1px"><?php echo esc_html( number_format( $invoice_data->amount, 2, '.', '' ) ); ?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
						<?php
					} else {
						?>
						<div class="table-responsive">
							<table class="table mjschool-model-invoice-table">
								<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading">
									<tr>
										<th class="mjschool-entry-table-heading mjschool-align-center">#</th>
										<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Date', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Entry', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Price', 'mjschool' ); ?> </th>
										<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Issue By', 'mjschool' ); ?> </th>
									</tr>
								</thead>
								<tbody>
									<?php
									$id           = 1;
									$total_amount = 0;
									if ( ! empty( $income_data ) || ! empty( $expense_data ) ) {
										if ( ! empty( $expense_data ) ) {
											$income_data = $expense_data;
										}
										$patient_all_income = $obj_invoice->mjschool_get_onepatient_income_data( $income_data->supplier_name );
										foreach ( $patient_all_income as $result_income ) {
											$income_entries = json_decode( $result_income->entry );
											foreach ( $income_entries as $each_entry ) {
												$total_amount += $each_entry->amount;
												?>
												<tr>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( $id ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( $result_income->income_create_date ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( $each_entry->entry ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $each_entry->amount, 2, '.', '' ) ) ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( mjschool_get_display_name( $result_income->create_by ) ); ?> </td>
												</tr>
												<?php
												$id += 1;
											}
										}
									}
									if ( ! empty( $invoice_data ) ) {
										$total_amount = $invoice_data->amount
										?>
										<tr>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $id ); ?> </td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( date( 'Y-m-d', strtotime( $invoice_data->date ) ) ); ?> </td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $invoice_data->payment_title ); ?> </td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $invoice_data->amount, 2, '.', '' ) ) ); ?> </td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_get_display_name( $invoice_data->payment_reciever_id ) ); ?> </td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
					<?php }
					if ( ! empty( $invoice_data ) ) {
						$grand_total     = $total_amount;
						$sub_total       = $invoice_data->fees_amount;
						$tax_amount      = $invoice_data->tax_amount;
						$discount_amount = $invoice_data->discount_amount;
						if ( ! empty( $invoice_data->tax ) ) {
							$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $invoice_data->tax ) );
						} else {
							$tax_name = '';
						}
						if ( $invoice_data->discount ) {
							$discount_name = mjschool_get_discount_name( $invoice_data->discount, $invoice_data->discount_type );
						} else {
							$discount_name = '';
						}
					}
					if ( ! empty( $income_data ) ) {
						if ( ! empty( $income_data->tax ) ) {
							$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $income_data->tax ) );
						} else {
							$tax_name = '';
						}
						if ( isset($income_data->discount) ) {
							$discount_name = mjschool_get_discount_name( $income_data->discount, $income_data->discount_type );
						} else {
							$discount_name = '';
						}
						$sub_total = 0;
						if ( ! empty( $income_data->entry ) ) {
							$all_income_entry = json_decode( $income_data->entry );
							foreach ( $all_income_entry as $one_entry ) {
								$sub_total += $one_entry->amount;
							}
						}
						if( isset( $income_data->discount_amount ) )
						{
							$discount_amount = $income_data->discount_amount;
						}
						$tax_amount      = $income_data->tax_amount;
						$grand_total     = $sub_total + $tax_amount;
					}
					if ( $format === 1 ) { ?>
						<div class="table-responsive mjschool-rtl-padding-left-40px mjschool-rtl-float-left-width-100px">
							<table class="table table-bordered mjschool_collapse_width_100">
								<tbody>
									<tr>
										<th style="width: 85%; text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black;" scope="row"> <?php echo esc_html__( 'Sub Total', 'mjschool' ) . ' :'; ?> </th>
										<td style="width: 15%; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black;"> <?php echo esc_html( number_format( $sub_total, 2, '.', '' ) ); ?> </td>
									</tr>
									<?php if ( isset( $discount_amount ) && ( $discount_amount ) != 0 ) { ?>
										<tr>
											<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black;" scope="row"> <?php echo esc_html__( 'Discount Amount', 'mjschool' ) . ' ( ' . esc_html( $discount_name ) . ' ) :'; ?> </th>
											<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black;"> <?php echo '-' . esc_html( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ); ?> </td>
										</tr>
									<?php } ?>
									<?php if ( isset( $tax_amount ) && ! empty( $tax_amount ) ) : ?>
										<tr>
											<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black;" scope="row"> <?php echo esc_html__( 'Tax Amount', 'mjschool' ) . ' ( ' . esc_html( $tax_name ) . ' ) :'; ?> </th>
											<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black;"> <?php echo '+' . esc_html( number_format( $tax_amount, 2, '.', '' ) ); ?> </td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
						<?php
					} else {
						?>
						<div class="table-responsive mjschool-rtl-padding-left-40px mjschool-rtl-float-left-width-100px">
							<table width="100%" border="0">
								<tbody>
									<tr>
										<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right"> <?php echo esc_attr__( 'Sub Total', 'mjschool' ) . '  :'; ?> </td>
										<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value"> <?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $sub_total, 2, '.', '' ) ) ); ?> </td>
									</tr>
									<?php if ( isset( $discount_amount ) && ! empty( $discount_amount ) ) { ?>
										<tr>
											<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right"> <?php echo esc_attr__( 'Discount Amount', 'mjschool' ) . '( ' . esc_html( $discount_name ) . ' )' . '  :'; ?> </td>
											<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value"> <?php echo '-' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $discount_amount, 2, '.', '' ) ) ); ?> </td>
										</tr>
									<?php } ?>
									<?php if ( isset( $tax_amount ) && ! empty( $tax_amount ) ) { ?>
										<tr>
											<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right"> <?php echo esc_attr__( 'Tax Amount', 'mjschool' ) . '( ' . esc_html( $tax_name ) . ' )' . '  :'; ?> </td>
											<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value"> <?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $tax_amount, 2, '.', '' ) ) ); ?> </td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					<?php } ?>
					<div class="row mjschool-margin-top-10px-res col-md-6 col-sm-6 col-xs-6 mjschool-view-invoice-lable-css mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total mjschool_width_50">
						<div class="mjschool_width_50 mjschool-width-50-res mjschool-align-right col-md-8 col-sm-8 col-xs-8 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
							<h3  class="padding mjschool-color-white margin mjschool-invoice-total-label mjschool_float_right"> <?php esc_html_e( 'Grand Total', 'mjschool' ); ?> </h3>
						</div>
						<div class="mjschool_width_50 mjschool-width-50-res mjschool-align-right col-md-4 col-sm-4 col-xs-4 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
							<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value mjschool_float_right" >
								<?php
								$formatted_amount = number_format( $grand_total, 2, '.', '' );
								$currency_symbol  = mjschool_get_currency_symbol(); // Use this if your project has a function to get the symbol.
								echo esc_html( "({$currency_symbol}){$formatted_amount}" );
								?>
							</h3>
						</div>
					</div>
					<div class="mjschool_fees_border_2px_margin_20px">
						<!-- Teacher Signature (Middle). -->
						<div class="mjschool_fees_center_width_33">
							<div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" /> </div>
							<div class="mjschool_fees_width_150px"></div>
							<div class="mjschool_margin_top_5px"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
						</div>
					</div>
					<div class="mjschool-margin-top-20px"></div>
				</div>
			</div>
		</div><!---- Model body. ----->
		<?php
	}
}

/**
 * Generates an exam receipt PDF for a student and emails it as an attachment.
 *
 * @since 1.0.0
 *
 * @param string|array $emails      Recipient email address(es).
 * @param string       $subject     Email subject.
 * @param string       $message     Email body content.
 * @param int          $student_id  Student ID.
 * @param int          $exam_id     Exam ID.
 *
 * @return void
 */
function mjschool_send_mail_receipt_pdf( $emails, $subject, $message, $student_id, $exam_id ) {
	$document_dir  = WP_CONTENT_DIR;
	$document_dir .= '/uploads/exam_receipt/';
	$document_path = $document_dir;
	if ( ! file_exists( $document_path ) ) {
		mkdir( $document_path, 0777, true );
	}
	$student_data    = get_userdata( $student_id );
	$umetadata       = mjschool_get_user_image( $student_id );
	$exam_data       = mjschool_get_exam_by_id( $exam_id );
	$exam_hall_data  = mjschool_get_exam_hall_name( $student_id, $exam_id );
	$exam_hall_name  = mjschool_get_hall_name( $exam_hall_data->hall_id );
	$obj_exam        = new mjschool_exam();
	$exam_time_table = $obj_exam->mjschool_get_exam_time_table_by_exam( $exam_id );
	 
	$header_html = '<div style="margin-bottom:8px;">
		<div class="mjschool-width-print" style="border: 2px solid; float:left; width:96%; margin: 6px 0px 0px 0px; padding:20px; padding-top: 4px; padding-bottom: 5px;">
			<div style="float:left; width:100%;">
				<div style="float:left; width:25%;">
					<div style="float:left; border-radius:50px;">
						<img src="' . esc_url( get_option( 'mjschool_logo' ) ) . '" style="height: 130px; border-radius:50%; background-repeat:no-repeat; background-size:cover; margin-top: 3px;" />
					</div>
				</div>
				<div style="float:left; width:75%; padding-top:10px;">
					<p style="margin:0px; width:100%; font-weight:bold; color:#1B1B8D; font-size:24px; text-align:center;">' . esc_html( get_option( 'mjschool_name' ) ) . '</p>
					<p style="margin:0px; font-size:17px; text-align:center;">' . esc_html( get_option( 'mjschool_address' ) ) . '</p>
					<div style="margin:0px; width:100%; text-align:center;">
						<p style="margin: 0px; width: fit-content; font-size: 17px; display: inline-block;">
							E-mail: ' . esc_html( get_option( 'mjschool_email' ) ) . '
						</p>
						<p style="margin: 0px; width: fit-content; font-size: 17px; display: inline-block;">
							&nbsp;&nbsp;Phone: ' . esc_html( get_option( 'mjschool_contact_number' ) ) . '
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>';
	 
	$signature = get_option( 'mjschool_principal_signature' );
	require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
	$mpdf       = new Mpdf\Mpdf();
	$stylesheet = file_get_contents( MJSCHOOL_PLUGIN_DIR . '/assets/css/receipt_pdf_mail.css' ); // Get css content.
	$mpdf->WriteHTML( '<html>' );
	$mpdf->WriteHTML( '<head>' );
	$mpdf->WriteHTML( '<style></style>' );
	$mpdf->WriteHTML( $stylesheet, 1 ); // Writing style to pdf.
	$mpdf->WriteHTML( '</head>' );
	$mpdf->WriteHTML( '<body>' );
	// $mpdf->SetTitle( 'Invoice' );
	$mpdf->WriteHTML( '<div class="modal-body">' );
	$mpdf->WriteHTML( '<div id="exam_receipt_print" class="exam_receipt_print">' );
	$mpdf->WriteHTML( $header_html );
	$mpdf->WriteHTML( '<div class="header mjschool-Examination-header"><span><strong class="mjschool-Examination-header-color">' . esc_attr__( 'Examination Hall Ticket', 'mjschool' ) . '</strong></span></div>' );
	$mpdf->WriteHTML( '<div class="mjschool-float-width">' );
	$mpdf->WriteHTML( '<table width="100%" class="count borderpx" cellspacing ="0" cellpadding="0">' );
	$mpdf->WriteHTML( '<thead>' );
	$mpdf->WriteHTML( '</thead>' );
	$mpdf->WriteHTML( '<tbody>' );
	$mpdf->WriteHTML( '<tr>' );
	$mpdf->WriteHTML( '<td rowspan="4" class="mjschool-img-td">' );
	 
	if (empty($umetadata ) ) {
		$mpdf->WriteHTML( '<img src="' . get_option( 'mjschool_student_thumb_new' ) . '" width="100px" height="100px">' );
	} else {
		$mpdf->WriteHTML( '<img src="' . $umetadata . '" width="100px" height="100px">' );
	}
	 
	$mpdf->WriteHTML( '</td>' );
	$mpdf->WriteHTML( '<td colspan="2" class="mjschool-border-bottom">' );
	$mpdf->WriteHTML( '<strong>' . esc_attr__( 'Student Name', 'mjschool' ) . ' : </strong>' . $student_data->display_name . '</td>' );
	$mpdf->WriteHTML( '</td>' );
	$mpdf->WriteHTML( '</tr>' );
	$mpdf->WriteHTML( '<tr>' );
	$mpdf->WriteHTML( '<td class="mjschool-border-bottom-rigth" align="left">' );
	$mpdf->WriteHTML( '<strong>' . esc_attr__( 'Roll Number', 'mjschool' ) . ' : </strong>' . $student_data->roll_id . ' </td>' );
	$mpdf->WriteHTML( '<td class="mjschool-border-bottom" align="left">' );
	$mpdf->WriteHTML( '<strong>' . esc_attr__( 'Exam Name', 'mjschool' ) . ' : </strong>' . $exam_data->exam_name . '</td>' );
	$mpdf->WriteHTML( '</tr>' );
	$mpdf->WriteHTML( '<tr>' );
	$mpdf->WriteHTML( '<td class="mjschool-border-bottom-rigth" align="left">' );
	$mpdf->WriteHTML( '<strong>' . esc_attr__( 'Class Name', 'mjschool' ) . ': </strong>' . mjschool_get_class_name( $student_data->class_name ) . '</td>' );
	$mpdf->WriteHTML( '<td class="mjschool-border-bottom" align="left">' );
	$mpdf->WriteHTML( '<strong>' . esc_attr__( 'Section Name', 'mjschool' ) . ' : </strong>' );
	$section_name = $student_data->class_section;
	if ( $section_name != '' ) {
		$mpdf->WriteHTML( '' . mjschool_get_section_name( $section_name ) . '' );
	} else {
		$mpdf->WriteHTML( '' . esc_attr__( 'No Section', 'mjschool' ) );
	}
	$mpdf->WriteHTML( '</td>' );
	$mpdf->WriteHTML( '</tr>' );
	$mpdf->WriteHTML( '<tr>' );
	$mpdf->WriteHTML( '<td class="mjschool-border-rigth" align="left">' );
	$mpdf->WriteHTML( '<strong>' . esc_attr__( 'Start Date', 'mjschool' ) . ' : </strong>' . mjschool_get_date_in_input_box( $exam_data->exam_start_date ) . '</td>' );
	$mpdf->WriteHTML( '<td class="mjschool-border-bottom-0" align="left">' );
	$mpdf->WriteHTML( '<strong>' . esc_attr__( 'End Date', 'mjschool' ) . ' : </strong>' . mjschool_get_date_in_input_box( $exam_data->exam_end_date ) . '</td>' );
	$mpdf->WriteHTML( '</tr>' );
	$mpdf->WriteHTML( '</tbody>' );
	$mpdf->WriteHTML( '<tfoot>' );
	$mpdf->WriteHTML( '</tfoot>' );
	$mpdf->WriteHTML( '</table>' );
	$mpdf->WriteHTML( '</div>' );
	$mpdf->WriteHTML( '<div class="mjschool-padding-top-20 mjschool-float-width">' );
	$mpdf->WriteHTML( '<table width="100%" class="count borderpx" cellspacing ="0" cellpadding="0">' );
	$mpdf->WriteHTML( '<thead>' );
	$mpdf->WriteHTML( '</thead>' );
	$mpdf->WriteHTML( '<tbody>' );
	$mpdf->WriteHTML( '<tr>' );
	$mpdf->WriteHTML( '<td class="mjschool-border-bottom">' );
	$mpdf->WriteHTML( '<strong>' . esc_attr__( 'Examination Centre', 'mjschool' ) . ' : </strong>' . $exam_hall_name . ',' . get_option( 'mjschool_name' ) . '</td>' );
	$mpdf->WriteHTML( '</tr>' );
	$mpdf->WriteHTML( '<tr>' );
	$mpdf->WriteHTML( '<td class="mjschool-border-bottom-0">' );
	$mpdf->WriteHTML( '<strong>' . esc_attr__( 'Examination Centre Address', 'mjschool' ) . ' : </strong>' . get_option( 'mjschool_address' ) . '</td>' );
	$mpdf->WriteHTML( '</tr>' );
	$mpdf->WriteHTML( '</tbody>' );
	$mpdf->WriteHTML( '<tfoot>' );
	$mpdf->WriteHTML( '</tfoot>' );
	$mpdf->WriteHTML( '</table>' );
	$mpdf->WriteHTML( '</div>' );
	$mpdf->WriteHTML( '<div class="mjschool-padding-top-20 mjschool-float-width">' );
	$mpdf->WriteHTML( '<table width="100%" cellspacing ="0" cellpadding="0" class="count borderpx">' );
	$mpdf->WriteHTML( '<thead>' );
	$mpdf->WriteHTML( '<tr>' );
	$mpdf->WriteHTML( '<th colspan="5" class="mjschool-border-bottom">' . esc_attr__( 'Time Table For Exam Hall', 'mjschool' ) . '</th>' );
	$mpdf->WriteHTML( '</tr>' );
	$mpdf->WriteHTML( '<tr class="mjschool-tr-back-color">' );
	$mpdf->WriteHTML( '<th class="mjschool-main-td mjschool-border-rigth mjschool-color-white mjschool_padding_10px">' . esc_attr__( 'Subject Code', 'mjschool' ) . '</th>' );
	$mpdf->WriteHTML( '<th class="mjschool-main-td mjschool-border-rigth mjschool-color-white mjschool_padding_10px">' . esc_attr__( 'Subject', 'mjschool' ) . '</th>' );
	$mpdf->WriteHTML( '<th class="mjschool-main-td mjschool-border-rigth mjschool-color-white mjschool_padding_10px">' . esc_attr__( 'Exam Date', 'mjschool' ) . '</th>' );
	$mpdf->WriteHTML( '<th class="mjschool-main-td mjschool-border-rigth mjschool-color-white mjschool_padding_10px">' . esc_attr__( 'Exam Time', 'mjschool' ) . '</th>' );
	$mpdf->WriteHTML( '<th class="mjschool-main-td mjschool-border-rigth mjschool-color-white mjschool_padding_10px">' . esc_attr__( 'Examiner Sign.', 'mjschool' ) . '</th>' );
	$mpdf->WriteHTML( '</tr>' );
	$mpdf->WriteHTML( '</thead>' );
	$mpdf->WriteHTML( '<tbody>' );
	if ( ! empty( $exam_time_table ) ) {
		foreach ( $exam_time_table as $retrieved_data ) {
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<td class="mjschool-main-td mjschool-border-rigth mjschool_padding_10px">' . mjschool_get_single_subject_code( $retrieved_data->subject_id ) . '</td>' );
			$mpdf->WriteHTML( '<td class="mjschool-main-td mjschool-border-rigth mjschool_padding_10px">' . mjschool_get_single_subject_name( $retrieved_data->subject_id ) . '</td>' );
			$mpdf->WriteHTML( '<td class="mjschool-main-td mjschool-border-rigth mjschool_padding_10px">' . mjschool_get_date_in_input_box( $retrieved_data->exam_date ) . '</td>' );
			$start_time_data = explode( ':', $retrieved_data->start_time );
			$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
			$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
			$start_am_pm     = $start_time_data[2];
			$start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
			$end_time_data   = explode( ':', $retrieved_data->end_time );
			$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
			$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
			$end_am_pm       = $end_time_data[2];
			$end_time        = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
			$mpdf->WriteHTML( '<td class="mjschool-main-td mjschool-border-rigth mjschool_padding_10px">' . $start_time . '' . esc_attr__( 'To', 'mjschool' ) . '' . $end_time . '</td>' );
			$mpdf->WriteHTML( '<td class="mjschool-main-td mjschool-border-rigth mjschool_padding_10px"></td>' );
			$mpdf->WriteHTML( '</tr>' );
		}
	}
	$mpdf->WriteHTML( '</tbody>' );
	$mpdf->WriteHTML( '<tfoot>' );
	$mpdf->WriteHTML( '</tfoot>' );
	$mpdf->WriteHTML( '</table>' );
	$mpdf->WriteHTML( '</div>' );
	$mpdf->WriteHTML( '<div class="resultdate"><hr color="#97C4E7"><span>' . esc_attr__( 'Student Signature', 'mjschool' ) . '</span></div>' );
	 
	$mpdf->WriteHTML( '<div class="signature"><span>
		<img src="' . $signature . '" style="width:100px; margin-right:15px;" />
	</span><hr color="#97C4E7"><span>' . esc_attr__( 'Authorized Signature', 'mjschool' ) . '</span></div>' );
	 
	$mpdf->WriteHTML( '</div>' );
	$mpdf->WriteHTML( '</div>' );
	$mpdf->WriteHTML( '</body>' );
	$mpdf->WriteHTML( '</html>' );
	$mpdf->Output( $document_path . 'exam receipt' . $student_id . '.pdf', 'F' );
	$mail_attachment = array( $document_path . 'exam receipt' . $student_id . '.pdf' );
	$school          = get_option( 'mjschool_name' );
	$headers         = '';
	$headers        .= 'From: ' . $school . ' <noreplay@gmail.com>' . "\r\n";
	$headers        .= "MIME-Version: 1.0\r\n";
	$headers        .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	// MAIL CONTEMNT WITH TEMPLATE DESIGN.
	$email_template = mjschool_get_mail_content_with_template_design( $message );
	if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
		wp_mail( $emails, $subject, $email_template, $headers, $mail_attachment );
	}
	unlink( $document_path . 'exam receipt' . $student_id . '.pdf' );
}

add_action( 'init', 'mjschool_print_certificate_for_student' );
/**
 * Handles printing of student certificates with optional header.
 *
 * @since 1.0.0
 *
 * @return void Outputs printable HTML and stops execution.
 */
function mjschool_print_certificate_for_student() {
	if ( isset( $_REQUEST['print_certificate_id'] ) && ! empty( $_REQUEST['print_certificate_id'] ) ) {
		$certificate_id    = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['print_certificate_id'])) ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = mjschool_get_certificate_by_id($certificate_id);
		if ( $result && ! empty( $result->certificate_content ) ) {
			$header_html = '';
			// Only add header if 'certificate_header' is set to 1.
			if ( isset( $_REQUEST['certificate_header'] ) && intval(wp_unslash($_REQUEST['certificate_header'])) === 1 ) {
				ob_start();
				?>
				<div class="mjschool_margin_bottom_8px">
					<div class="mjschool-width-print mjschool_border_2px_width_96">
						<div class="mjschool_float_left_width_100">
							<div class="mjschool_float_left_width_25">
								
								<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
									<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
								</div>
								
							</div>
							<div class="mjschool_float_left_width_75">
								<p class="mjschool_fees_widht_100_fonts_24px"> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </p>
								<p class="mjschool_fees_center_fonts_17px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?> </p>
								<div class="mjschool_fees_center_margin_0px">
									<p class="mjschool_fees_width_fit_content_inline">
										<?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
									</p>
									<p class="mjschool_fees_width_fit_content_inline">
										&nbsp;&nbsp;
										<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				$header_html = ob_get_clean();
			}
			// Combine header and content.
			$full_html = $header_html . stripslashes( $result->certificate_content );
			// Output the full printable HTML page.
			echo '<html><head>';
			echo '<title>Print Certificate</title>';
			echo '<style>
				body {
					font-family: "Poppins", sans-serif;
				}
				* {
					font-family: "Poppins", sans-serif;
				}
				 .certificate_heading {
				 margin-bottom: -5px !important;
		         }
				 .mjschool-width-print {
					width: 94% !important;
				}
			</style>';
			echo '<script type="text/javascript">
			(function() {
				"use strict";
				window.addEventListener( "load", function() {
					setTimeout(function() {
						window.print();
					}, 500);
				});
			})();
			</script>';
			echo '</head><body>';
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $full_html;
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</body></html>';
			die();
		}
	}
}
// phpcs:disable
/**
 * Handles printing of student and standard ID cards.
 *
 * @since 1.0.0
 *
 * @return void Outputs printable ID card and stops execution.
 */
function mjschool_print_id_card_for_student() {
	if ( isset( $_REQUEST['print_id_card'] ) ) {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-new-design', plugins_url( '/assets/css/mjschool-smgt-new-design.css', __FILE__ ) );
		wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-poppins-fontfamily', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
		if ( ! empty( $_REQUEST['id'] ) ) {
			?>
			<script type="text/javascript">
				(function() {
					"use strict";
					function mjschool_print_with_delay() {
						setTimeout(function () {
							window.print();
						}, 500);
					}
					window.addEventListener( 'load', mjschool_print_with_delay);
				})();
			</script>
			<?php
			mjschool_print_id_card( intval(wp_unslash($_REQUEST['id'])) );
			die();
		}
	} elseif ( isset( $_REQUEST['print_standard_id_card'] ) ) {
		wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-new-design', plugins_url( '/assets/css/mjschool-smgt-new-design.css', __FILE__ ) );
		wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-poppins-fontfamily', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
		if ( ! empty( $_REQUEST['id'] ) ) {
			?>
			<script type="text/javascript">
				(function() {
					"use strict";
					function mjschool_print_with_delay() {
						setTimeout(function () {
							window.print();
						}, 500);
					}
					window.addEventListener( 'load', mjschool_print_with_delay);
				})();
			</script>
			<?php
			mjschool_print_standard_id_card( intval(wp_unslash($_REQUEST['id'])) );
			die();
		}
	}
}
// phpcs:enable
add_action( 'init', 'mjschool_print_id_card_for_student' );
/**
 * Generates and prints the student ID card layout (3 cards per row).
 *
 * This function loads required scripts/styles, prepares QR codes,
 * and renders ID cards with student details, profile photo, QR code,
 * and school branding. The output is buffered for printing.
 *
 * @since 1.0.0
 *
 * @param array $id List of student user IDs to print ID cards for.
 *
 * @return void
 */
function mjschool_print_id_card( $id ) {
	wp_print_scripts();
	wp_print_styles();
	ob_start();
	?>
	<style type="text/css">
		@media print {
			* {
				color-adjust: exact !important;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}
			.row {
				display: -ms-flexbox;
				display: flex;
				-ms-flex-wrap: wrap;
				flex-wrap: wrap;
				margin-right: -15px;
				margin-left: -15px;
			}
			.pb-4,
			.py-4 {
				padding-bottom: 1.5rem !important;
			}
			.col-md-4 {
				-ms-flex: 0 0 33.333333%;
				flex: 0 0 33.333333%;
				max-width: 33.333333%;
				padding-right: 10px;
				padding-left: 10px;
			}
			.col-md-3 {
				-ms-flex: 0 0 25%;
				margin-right: 4mm !important;
				flex: 0 0 25%;
				max-width: 25%;
			}
			.col-md-9 {
				-ms-flex: 0 0 75%;
				flex: 0 0 75%;
				max-width: 75%;
				margin-left: 10px !important
			}
			.p-0 {
				padding: 0 !important;
			}
			.col-md-6 {
				-ms-flex: 0 0 50%;
				flex: 0 0 50%;
				max-width: 45%;
			}
			.mjschool-id-page-card {
				border: 1px solid black;
			}
			.mjschool-card-heading {
				background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;
				padding: 15px 15px 15px 80px;
			}
			.mjschool-id-card-label {
				color: #ffffff;
				margin-bottom: 0px;
			}
			.mjschool-id-card-body {
				padding: 5px;
			}
			img.mjschool-id-card-user-image {
				height: 65px;
				border-radius: 25%;
				width: 65px;
			}
			h5.user_info {
				font-size: 13px !important;
				font-weight: 400;
				color: #333333;
			}
			.student_info {
				font-size: 13px !important;
				font-weight: 400;
				color: #818386 !important;
				margin-bottom: 15px !important;
			}
			img.mjschool-icard-logo {
				height: 58px;
				border-radius: 50%;
				margin-top: 5px;
				margin-left: 13px;
				margin-right: 10px;
				float: left;
			}
			.mjschool-id-margin {
				margin-right: 0px;
			}
			.mjschool-id-card-barcode {
				height: 100px;
			}
			.mjschool-card-title-position {
				height: 70px !important;
			}
			.mjschool-id-card-info {
				margin-left: 5px !important;
				width: 75% !important;
				margin-top: 12px;
			}
			.mjschool-print-id-card {
				height: 40px;
				background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?> !important;
				width: 40px;
			}
			.mjschool-print-id-button {
				display: inline;
			}
			img.mjschool-id-card-image {
				margin-top: 4px;
			}
			p.mjschool-icard-dotes {
				display: inline;
				float: left;
				margin-top: -3px;
				margin-bottom: auto;
			}
			.mjschool-card-code {
				margin-bottom: 0px;
			}
		}
		.row {
			display: -ms-flexbox;
			display: flex;
			-ms-flex-wrap: wrap;
			flex-wrap: wrap;
			margin-right: -15px;
			margin-left: -15px;
		}
		.pb-4,
		.py-4 {
			padding-bottom: 1.5rem !important;
		}
		.col-md-4 {
			-ms-flex: 0 0 33.333333%;
			flex: 0 0 33.333333%;
			max-width: 33.333333%;
			padding-right: 10px;
			padding-left: 10px;
		}
		.col-md-3 {
			-ms-flex: 0 0 25%;
			margin-right: 4mm !important;
			flex: 0 0 25%;
			max-width: 25%;
		}
		.col-md-9 {
			-ms-flex: 0 0 75%;
			flex: 0 0 75%;
			max-width: 70%;
			margin-left: 10px !important
		}
		.p-0 {
			padding: 0 !important;
		}
		.col-md-6 {
			-ms-flex: 0 0 50%;
			flex: 0 0 50%;
			max-width: 45%;
		}
		.mjschool-id-page-card {
			border: 1px solid black;
		}
		.mjschool-card-heading {
			background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;
			padding: 15px 15px 15px 80px;
		}
		.mjschool-id-card-label {
			color: #ffffff;
			margin-bottom: 0px;
		}
		.mjschool-id-card-body {
			padding: 5px;
		}
		img.mjschool-id-card-user-image {
			height: 65px;
			border-radius: 25%;
			width: 65px;
		}
		h5.user_info {
			font-size: 13px !important;
			font-weight: 400;
			color: #333333;
		}
		.student_info {
			font-size: 13px !important;
			font-weight: 400;
			color: #818386 !important;
			margin-bottom: 15px !important;
		}
		img.mjschool-icard-logo {
			height: 58px;
			border-radius: 50%;
			margin-top: 5px;
			margin-left: 13px;
			margin-right: 10px;
			float: left;
		}
		.mjschool-id-margin {
			margin-right: 0px;
		}
		.mjschool-id-card-barcode {
			height: 95px;
		}
		.mjschool-id-card-info {
			margin-left: 5px !important;
			width: 70% !important;
			margin-top: 12px;
		}
		.mjschool-print-id-card {
			height: 40px;
			background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?> !important;
			width: 40px;
		}
		.mjschool-card-title-position {
			height: 70px !important;
		}
		.mjschool-print-id-button {
			display: inline;
		}
		img.mjschool-id-card-image {
			margin-top: 4px;
		}
		p.mjschool-icard-dotes {
			display: inline;
			float: left;
			margin-top: -3px;
		}
		.mjschool-card-code {
			margin-bottom: 0px;
		}
	</style>
	<div class="mjschool-icard-setup container-fluid">
		<?php
		$counter = 0;
		$printed = false;
		foreach ( $id as $row ) {
			$student_data = get_userdata( $row );
			$userimage    = mjschool_get_user_image( $row );
			$usersdata    = get_user_meta( $row, 'mjschool_user_avatar', true );
			$class_id     = get_user_meta( $row, 'class_name', true );
			$section_name = get_user_meta( $row, 'class_section', true );
			?>
			<script type="text/javascript">
				(function(jQuery) {
					"use strict";
					jQuery(document).ready(function () {
						var qr_code_urlnew = JSON.stringify({
							"user_id": '<?php echo esc_js( $row ); ?>',
							"class_id": '<?php echo esc_js( $class_id ); ?>',
							"section_id": '<?php echo esc_js( $section_name ); ?>',
							"qr_type": "schoolqr"
						});
						var url = 'https://api.qrserver.com/v1/create-qr-code/?data=' + qr_code_urlnew + '&amp;size=50x50';
						jQuery( '.id_card_barcode_<?php echo esc_js( $row ); ?>' ).attr( 'src', url);
					});
				})(jQuery);
			</script>
			<?php
			if ( $counter % 3 === 0 && $printed ) {
				echo '</div>';
			}
			if ( $counter % 3 === 0 ) {
				$printed = true;
				echo '<div class="row pb-4">';
			}
			?>
			<div class="col-md-4">
				<div class="mjschool-id-page-card">
					
					<img class="mjschool-icard-logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
					<div class="mjschool-card-heading mjschool-card-title-position">
						<label class="mjschool-id-card-label p-0"> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </label>
					</div>
					<div class="mjschool-id-card-body">
						<div class="row">
							<div class="col-md-3 mjschool-id-margin">
								<p class="mjschool-id-card-image"> <img class="mjschool-id-card-user-image" src="<?php if ( ! empty( $userimage ) ) { echo esc_url($userimage); } else { echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); } ?>"> </p>
								<p class="mjschool-id-card-image mjschool-card-code"> <img class="id_card_barcode_<?php echo esc_attr($row); ?> mjschool-id-card-barcode" id='qrcode' src=''> </p>
							</div>
							
							<div class="col-md-9 mjschool-id-card-info row">
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<h5 class="mjschool-student-info"> <?php esc_html_e( 'Student Name', 'mjschool' ); ?> </h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<p class="mjschool-icard-dotes">:&nbsp;</p>
									<h5 class="mjschool-user-info"> <?php echo esc_html( $student_data->display_name ); ?> </h5>
								</div>
								<div class="p-0 col-md-6">
									<h5 class="mjschool-student-info"><?php esc_html_e( 'Class', 'mjschool' ); ?></h5>
								</div>
								<div class="p-0 col-md-6 col-6">
									<p class="mjschool-icard-dotes">:&nbsp;</p>
									<h5 class="mjschool-user-info">
										<?php
										$class_name = mjschool_get_class_section_name_wise( $student_data->class_name, $student_data->class_section );
										if ( $class_name === ' ' ) {
											esc_html_e( 'N/A', 'mjschool' );
										} else {
											echo esc_html( $class_name );
										}
										?>
									</h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<h5 class="mjschool-student-info"> <?php esc_html_e( 'Roll No.', 'mjschool' ); ?> </h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<p class="mjschool-icard-dotes">:&nbsp;</p>
									<h5 class="mjschool-user-info">
										<?php
										if ( ! empty( $student_data->roll_id ) ) {
											echo esc_html( $student_data->roll_id );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
									</h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<h5 class="mjschool-student-info"> <?php esc_html_e( 'Contact No', 'mjschool' ); ?>. </h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<p class="mjschool-icard-dotes">:&nbsp;</p>
									<h5 class="mjschool-user-info"> <?php echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $student_data->mobile_number ); ?> </h5>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- print your data end -->
			<?php
			$counter += 1;
		}
		?>
	</div>
	<?php
	$out_put = ob_get_contents();
}
/**
 * Generates and prints the standard student ID card layout (2 cards per row).
 *
 * Similar to mjschool_print_id_card(), but uses different print
 * dimensions and card structure. Renders student details,
 * QR code, school name, and profile image with print-ready styling.
 *
 * @since 1.0.0
 *
 * @param array $id List of student user IDs to generate standard ID cards for.
 *
 * @return void
 */
function mjschool_print_standard_id_card( $id ) {
	wp_enqueue_script( 'jquery' );
	wp_print_scripts();
	wp_print_styles();
	ob_start();
	?>
	<style type="text/css">
		@media print {
			* {
				color-adjust: exact !important;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}
			.row {
				display: -ms-flexbox;
				display: flex;
				-ms-flex-wrap: wrap;
				flex-wrap: wrap;
				margin-right: -15px;
				margin-left: -15px;
			}
			.pb-4,
			.py-4 {
				padding-bottom: 1.5rem !important;
			}
			.col-md-4 {
				-ms-flex: 0 0 43.333333%;
				flex: 0 0 43.333333%;
				max-width: 43.333333%;
				padding-right: 10px;
				padding-left: 10px;
			}
			.col-md-3 {
				-ms-flex: 0 0 25%;
				margin-right: 4mm !important;
				flex: 0 0 25%;
				max-width: 25%;
			}
			.col-md-9 {
				-ms-flex: 0 0 75%;
				flex: 0 0 75%;
				max-width: 75%;
				margin-left: 10px !important
			}
			.p-0 {
				padding: 0 !important;
			}
			.col-md-6 {
				-ms-flex: 0 0 50%;
				flex: 0 0 50%;
				max-width: 45%;
			}
			.mjschool-id-page-card {
				border: 1px solid black;
			}
			.mjschool-card-heading {
				background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?> ;
				padding: 15px 15px 15px 80px;
			}
			.mjschool-id-card-label {
				color: #ffffff;
				margin-bottom: 0px;
				font-size: 18px !important;
			}
			.mjschool-id-card-body {
				padding: 5px;
			}
			img.mjschool-id-card-user-image {
				height: 65px;
				border-radius: 25%;
				width: 65px;
			}
			h5.user_info {
				font-size: 13px !important;
				font-weight: 400;
				color: #333333;
			}
			.student_info {
				font-size: 13px !important;
				font-weight: 400;
				color: #818386 !important;
				margin-bottom: 15px !important;
			}
			img.mjschool-icard-logo {
				height: 58px;
				border-radius: 50%;
				margin-top: 5px;
				margin-left: 13px;
				margin-right: 10px;
				float: left;
			}
			.mjschool-id-margin {
				margin-right: 0px;
			}
			.mjschool-id-card-barcode {
				height: 100px;
			}
			.mjschool-card-title-position {
				height: 70px !important;
			}
			.mjschool-id-card-info {
				margin-left: 5px !important;
				width: 75% !important;
				margin-top: 12px;
			}
			.mjschool-print-id-card {
				height: 40px;
				background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?> !important;
				width: 40px;
			}
			.mjschool-print-id-button {
				display: inline;
			}
			img.mjschool-id-card-image {
				margin-top: 4px;
			}
			p.mjschool-icard-dotes {
				display: inline;
				float: left;
				margin-top: -3px;
				margin-bottom: auto;
			}
			.mjschool-card-code {
				margin-bottom: 0px;
			}
		}
		.row {
			display: -ms-flexbox;
			display: flex;
			-ms-flex-wrap: wrap;
			flex-wrap: wrap;
			margin-right: -15px;
			margin-left: -15px;
		}
		.pb-4,
		.py-4 {
			padding-bottom: 1.5rem !important;
		}
		.col-md-4 {
			-ms-flex: 0 0 43.333333%;
			flex: 0 0 43.333333%;
			max-width: 43.333333%;
			padding-right: 10px;
			padding-left: 10px;
		}
		.col-md-3 {
			-ms-flex: 0 0 25%;
			margin-right: 4mm !important;
			flex: 0 0 25%;
			max-width: 25%;
		}
		.col-md-9 {
			-ms-flex: 0 0 75%;
			flex: 0 0 75%;
			max-width: 70%;
			margin-left: 10px !important
		}
		.p-0 {
			padding: 0 !important;
		}
		.col-md-6 {
			-ms-flex: 0 0 50%;
			flex: 0 0 50%;
			max-width: 45%;
		}
		.mjschool-id-page-card {
			border: 1px solid black;
		}
		.mjschool-card-heading {
			background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?> ;
			padding: 15px 15px 15px 80px;
		}
		.mjschool-id-card-label {
			color: #ffffff;
			margin-bottom: 0px;
			font-size: 18px !important;
		}
		.mjschool-id-card-body {
			padding: 5px;
		}
		img.mjschool-id-card-user-image {
			height: 65px;
			border-radius: 25%;
			width: 65px;
		}
		h5.user_info {
			font-size: 13px !important;
			font-weight: 400;
			color: #333333;
		}
		.student_info {
			font-size: 13px !important;
			font-weight: 400;
			color: #818386 !important;
			margin-bottom: 15px !important;
		}
		img.mjschool-icard-logo {
			height: 58px;
			border-radius: 50%;
			margin-top: 5px;
			margin-left: 13px;
			margin-right: 10px;
			float: left;
		}
		.mjschool-id-margin {
			margin-right: 0px;
		}
		.mjschool-id-card-barcode {
			height: 95px;
		}
		.mjschool-id-card-info {
			margin-left: 5px !important;
			width: 70% !important;
			margin-top: 12px;
		}
		.mjschool-print-id-card {
			height: 40px;
			background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?> !important;
			width: 40px;
		}
		.mjschool-card-title-position {
			height: 70px !important;
		}
		.mjschool-print-id-button {
			display: inline;
		}
		img.mjschool-id-card-image {
			margin-top: 4px;
		}
		p.mjschool-icard-dotes {
			display: inline;
			float: left;
			margin-top: -3px;
		}
		.mjschool-card-code {
			margin-bottom: 0px;
		}
	</style>
	<div class="mjschool-icard-setup container-fluid">
		<?php
		$counter = 0;
		$printed = false;
		foreach ( $id as $row ) {
			$student_data = get_userdata( $row );
			$userimage    = mjschool_get_user_image( $row );
			$usersdata    = get_user_meta( $row, 'mjschool_user_avatar', true );
			$class_id     = get_user_meta( $row, 'class_name', true );
			$section_name = get_user_meta( $row, 'class_section', true );
			?>
			<script type="text/javascript">
				(function(jQuery) {
					"use strict";
					jQuery(document).ready(function () {
						var qr_code_urlnew = JSON.stringify({
							"user_id": '<?php echo esc_js( $row ); ?>',
							"class_id": '<?php echo esc_js( $class_id ); ?>',
							"section_id": '<?php echo esc_js( $section_name ); ?>',
							"qr_type": "schoolqr"
						});
						var url = 'https://api.qrserver.com/v1/create-qr-code/?data=' + qr_code_urlnew + '&amp;size=50x50';
						jQuery( '.id_card_barcode_<?php echo esc_js( $row ); ?>' ).attr( 'src', url);
					});
				})(jQuery);
			</script>
			<?php
			if ( $counter % 2 === 0 && $printed ) {
				echo '</div>';
			}
			if ( $counter % 2 === 0 ) {
				$printed = true;
				echo '<div class="row pb-4">';
			}
			?>
			<div class="col-md-4">
				
				<div class="mjschool-id-page-card">
					<img class="mjschool-icard-logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
					<div class="mjschool-card-heading mjschool-card-title-position">
						<label class="mjschool-id-card-label p-0"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </label>
					</div>
					<div class="mjschool-id-card-body">
						<div class="row">
							<div class="col-md-3 mjschool-id-margin">
								<p class="mjschool-id-card-image"> <img class="mjschool-id-card-user-image" src="<?php if ( ! empty( $userimage ) ) { echo esc_url($userimage); } else { echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); } ?>"> </p>
								<p class="mjschool-id-card-image mjschool-card-code"> <img class="id_card_barcode_<?php echo esc_attr($row); ?> mjschool-id-card-barcode" id='qrcode' src=''> </p>
								
							</div>
							<div class="col-md-9 mjschool-id-card-info row">
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<h5 class="mjschool-student-info"> <?php esc_html_e( 'Student Name', 'mjschool' ); ?></h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<p class="mjschool-icard-dotes">:&nbsp;</p>
									<h5 class="mjschool-user-info"> <?php echo esc_html( $student_data->display_name ); ?> </h5>
								</div>
								<div class="p-0 col-md-6">
									<h5 class="mjschool-student-info"><?php esc_html_e( 'Class', 'mjschool' ); ?> </h5>
								</div>
								<div class="p-0 col-md-6 col-6">
									<p class="mjschool-icard-dotes">:&nbsp;</p>
									<h5 class="mjschool-user-info">
										<?php
										$class_name = mjschool_get_class_section_name_wise( $student_data->class_name, $student_data->class_section );
										if ( $class_name === ' ' ) {
											esc_html_e( 'N/A', 'mjschool' );
										} else {
											echo esc_html( $class_name );
										}
										?>
									</h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<h5 class="mjschool-student-info"> <?php esc_html_e( 'Roll No.', 'mjschool' ); ?></h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<p class="mjschool-icard-dotes">:&nbsp;</p>
									<h5 class="mjschool-user-info">
										<?php
										if ( ! empty( $student_data->roll_id ) ) {
											echo esc_html( $student_data->roll_id );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
									</h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<h5 class="mjschool-student-info"><?php esc_html_e( 'Contact No', 'mjschool' ); ?>.</h5>
								</div>
								<div class="p-0 col-md-6 mjschool-card-user-name">
									<p class="mjschool-icard-dotes">:&nbsp;</p>
									<h5 class="mjschool-user-info"><?php echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $student_data->mobile_number ); ?></h5>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- print your data end -->
			<?php
			$counter += 1;
		}
		?>
	</div>
	<?php
	$out_put = ob_get_contents();
}