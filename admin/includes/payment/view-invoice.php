<?php
/**
 * MjSchool Invoice View Template
 *
 * This file is responsible for displaying and formatting invoices, income, and expense data
 * in the MjSchool plugin. It dynamically adjusts layout and design based on selected format
 * and supports RTL languages.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/payment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$mjschool_obj_invoice = new Mjschool_Invoice();
$invoice_type = isset( $_REQUEST['invoice_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['invoice_type'] ) ) : '';
$idtest = isset( $_REQUEST['idtest'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['idtest'] ) ) : '';
if ( $invoice_type === 'invoice' ) {
    $invoice_data = mjschool_get_payment_by_id( intval( mjschool_decrypt_id( $idtest ) ) );
}
if ( $invoice_type === 'income' ) {
    $income_data = $mjschool_obj_invoice->mjschool_get_income_data( intval( mjschool_decrypt_id( $idtest ) ) );
}
if ( $invoice_type === 'expense' ) {
    $expense_data = $mjschool_obj_invoice->mjschool_get_income_data( intval( mjschool_decrypt_id( $idtest ) ) );
}
$format = get_option( 'mjschool_invoice_option' );
?>
<?php if ( is_rtl() ) {
	wp_enqueue_style( 'mjschool-invoice-rtl-style', plugins_url( '/assets/css/mjschool-invoice-rtl.css', __FILE__ ) );
 } ?>
<div class="penal-body"><!----- Panel Body. --------->
	<div id="mjschool-payment-invoice"><!----- Payment Invoice. --------->
		<div class="modal-body mjschool-border-invoice-page mjschool-margin-top-15px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool-height-600px"><!---- Model body.  ----->
			<?php if ($format === 0) { ?>
				<img class="mjschool-rtl-image-set-invoice mjschool-invoice-image mjschool-image-width-98px mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url(plugins_url( '/mjschool/assets/images/listpage-icon/mjschool-invoice.png' ) ); ?>" width="100%">
			<?php } ?>
			<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
				<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
					<div class="row mjschool-margin-top-20px">
						<?php if ($format === 1) { ?>
							<div class="mjschool-width-print mjschool-rtl-heads mjschool_fees_style">
								<div class="mjschool_float_left_width_100">
									<div class="mjschool_float_left_width_25">
										<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
											<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
										</div>
									</div>
									<div class="mjschool_float_left_padding_width_75">
										<p class="mjschool_fees_widht_100_fonts_24px"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
										<p class="mjschool_fees_center_fonts_17px"><?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
										<div class="mjschool_fees_center_margin_0px">
											<p class="mjschool_fees_width_fit_content_inline"><?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?></p>
											<p class="mjschool_fees_width_fit_content_inline">&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?></p>
										</div>
									</div>
								</div>
							</div>
						<?php } else { ?>
							<h3 class="mjschool-school-name-for-invoice-view"><?php echo esc_html( get_option( 'mjschool_name' ) ) ?></h3>
							<div class="col-md-1 col-sm-2 col-xs-3">
								<div class="width_1 mjschool-rtl-width-80px">
									<img class="system_logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
								</div>
							</div>
							
							<div class="col-md-11 col-sm-10 col-xs-9 mjschool-invoice-address mjschool-invoice-address-css">
								<div class="row">
									<div class="col-md-12 col-sm-12 col-xs-12 mjschool-invoice-padding-bottom-15px mjschool-padding-right-0">
										<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Address', 'mjschool' ); ?></label><br>
										<label class="mjschool-label-value mjschool-word-break-all"> <?php echo nl2br( esc_html( chunk_split( get_option( 'mjschool_address' ), 100 ) ) ); ?></label>
									</div>
									<div class="row col-md-12 mjschool-invoice-padding-bottom-15px">
										<div class="col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-email-width-auto">
											<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Email', 'mjschool' ); ?> </label><br>
											<label class="mjschool-label-value mjschool-word-break-all"><?php echo esc_html( get_option( 'mjschool_email' ) ), '<BR>'; ?></label>
										</div>
										<div class="col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-padding-left-30px">
											<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Phone', 'mjschool' ); ?> </label><br>
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
							<div class="mjschool-width-print mjschool_fees_padding_border_2px" >
								<div class="mjschool_float_left_width_100">
									<?php
									$student_id = null;
									if ( ! empty( $expense_data ) ) {
										$party_name = $expense_data->supplier_name;
										$ex_name    = $party_name ? wp_kses_post( chunk_split( ucwords( $party_name ), 30, '<br>' ) ) : 'N/A';
									} else {
										$student_id = ! empty( $income_data ) ? $income_data->supplier_name : $invoice_data->student_id;
										$patient    = get_userdata( $student_id );
										$in_name    = $patient ? wp_kses_post( chunk_split( ucwords( $patient->display_name ), 30, '<br>' ) ) : 'N/A';
									}
									?>
									<div  class="mjschool_padding_10px">
										<div class="mjschool_float_left_width_65">
											<b><?php esc_html_e( 'Bill To', 'mjschool' ); ?>:</b> 
											<?php echo esc_html( get_user_meta( $student_id, 'first_name', true ) ) . ' ' . esc_html( get_user_meta( $student_id, 'student_id', true ) ); ?>&nbsp;
											<?php
											if ( ! empty( $expense_data ) ) {
												echo wp_kses_post( $ex_name );
											} else {
												echo esc_html( mjschool_student_display_name_with_roll( $student_id ) );
											}
											?>
										</div>
										<div class="mjschool_float_right_width_35">
											<b><?php esc_html_e( 'Status', 'mjschool' ); ?>:</b>
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
											<div>
												<b><?php esc_html_e( 'Address', 'mjschool' ); ?>:</b>
												<?php echo esc_html( $address ); ?>
											</div>
											<div><?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?></div>
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
											<b><?php esc_html_e( 'Issue Date', 'mjschool' ); ?>:</b>
											<?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
										</div>
									</div>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="row">
								<div class="mjschool-width-50px mjschool-float-left-width-100px">
									<div class="col-md-8 col-sm-8 col-xs-5 mjschool-custom-padding-0 mjschool-float-left mjschool-display-grid mjschool-display-inherit-res mjschool-margin-bottom-20px mjschool-rs-main-billed-to">
										<div class="mjschool-billed-to mjschool-display-flex mjschool-invoice-address-heading mjschool-rs-width-billed-to">
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
											<h3 class="mjschool-billed-to-lable mjschool-invoice-model-heading mjschool-bill-to-width-12px mjschool-rs-bill-to-width-40px"><?php esc_html_e( 'Bill To', 'mjschool' ); ?> : </h3>
											<?php
											if ( ! empty( $expense_data ) ) {
												$party_name = $expense_data->supplier_name;
												if ( $party_name ) {
													$processed_name = chunk_split( ucwords( $party_name ), 30, '<br>' );
													$escaped_name   = wp_kses( $processed_name, array( 'br' => array() ) );
													echo '<h3 class="display_name mjschool-invoice-width-100px">' . wp_kses_post( $escaped_name ) . '</h3>';
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
													$raw_name       = $patient->display_name;
													$capitalized    = ucwords( $raw_name );
													$chunked_name   = chunk_split( $capitalized, 30, '<br>' );
													$escaped_output = wp_kses( $chunked_name, array( 'br' => array() ) );
													echo '<h3 class="display_name mjschool-invoice-width-100px">' . wp_kses_post( $escaped_output ) . '</h3>';
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												}
											}
											?>
										</div>
										<div class="mjschool-width-60px mjschool-address-information-invoice">
											<?php
											if ( ! empty( $expense_data ) ) {
												echo "";
											} else {
												if ( ! empty( $income_data ) ) {
													$student_id = $income_data->supplier_name;
												}
												if ( ! empty( $invoice_data ) ) {
													$student_id = $invoice_data->student_id;
												}
												$patient           = get_userdata( $student_id );
												$address           = get_user_meta( $student_id, 'address', true );
												$formatted_address = chunk_split( esc_html( $address ), 30, "\n" );
												echo wp_kses_post( nl2br( $formatted_address ) );
												echo esc_html( get_user_meta( $student_id, 'city', true ) ) . ',' . '<BR>';
												echo esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . ',<BR>';
											}
											?>
										</div>
									</div>
									<div class="col-md-3 col-sm-4 col-xs-7 mjschool-float-left">
										<div class="mjschool-width-50px">
											<div class="mjschool-width-20px" align="center">
												<?php
												if ( ! empty( $invoice_data ) ) {
													echo "";
												}
												?>
												<h5 class="mjschool-align-left"> <label class="mjschool-popup-label-heading text-transfer-upercase"><?php echo esc_html__( 'Date :', 'mjschool' ); ?> </label>&nbsp; <label class="mjschool-invoice-model-value"><?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?></label></h5>
												<h5 class="mjschool-align-left"><label class="mjschool-popup-label-heading text-transfer-upercase"><?php echo esc_html__( 'Status :', 'mjschool' ); ?> </label> &nbsp;<label class="mjschool-invoice-model-value">
													<?php
													if ( $payment_status === 'Paid' ) {
														echo '<span class="mjschool-green-color">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
													}
													if ( $payment_status === 'Part Paid' ) {
														echo '<span class="mjschool-purpal-color">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
													}
													if ( $payment_status === 'Unpaid' ) {
														echo '<span class="mjschool-red-color">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
													}
													?>
												</h5>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					<table class="mjschool-width-100px mjschool-margin-top-10px-res mt-4">
						<tbody>
							<tr>
								<td>
									<?php
									if ( ! empty( $invoice_data ) ) {
										?>
										<h3 class="display_name"><?php esc_html_e( 'Invoice Entries', 'mjschool' ); ?></h3>
										<?php
									} elseif ( ! empty( $income_data ) ) {
										?>
										<h3 class="display_name"><?php esc_html_e( 'Income Entries', 'mjschool' ); ?></h3>
										<?php
									} elseif ( ! empty( $expense_data ) ) {
										?>
										<h3 class="display_name"><?php esc_html_e( 'Expense Entries', 'mjschool' ); ?></h3>
										<?php
									}
									?>
								<td>
							</tr>
						</tbody>
					</table>
					<?php if ( $format === 1 ) { ?>
						<div class="table-responsive mjschool-rtl-padding-left-40px">
							<table class="table table-bordered mjschool-model-invoice-table">
								<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_border_color_2px" >
									<tr>
										<th class="text-center mjschool_tables_width_15px"><?php esc_html_e( 'Number', 'mjschool' ); ?></th>
										<th class="text-center mjschool_tables_width_20"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
										<th class="text-center mjschool_black_solid_border_2px" ><?php esc_html_e( 'Entry', 'mjschool' ); ?></th>
										<th class="text-center mjschool_black_solid_border_2px" ><?php esc_html_e( 'Issued By', 'mjschool' ); ?></th>
										<th class="text-center mjschool_tables_width_15px"><?php echo esc_html__( 'Price', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$id           = 1;
									$total_amount = 0;
									// Merge logic for income/expense entries.
									if ( ! empty( $income_data ) || ! empty( $expense_data ) ) {
										if ( ! empty( $expense_data ) ) {
											$income_data = $expense_data; // Treat expense as income for this context.
										}
										$all_entries = $mjschool_obj_invoice->mjschool_get_onestudent_income_data( mjschool_decrypt_id( $idtest ) );
										foreach ( $all_entries as $entry_row ) {
											$entry_list = json_decode( $entry_row->entry );
											foreach ( $entry_list as $entry ) {
												$amount        = floatval( $entry->amount );
												$total_amount += $amount;
												?>
												<tr>
													<td class="text-center mjschool_border_black_2px"><?php echo esc_html( $id++ ); ?></td>
													<td class="text-center mjschool_border_black_2px"><?php echo esc_html( mjschool_get_date_in_input_box( $entry_row->income_create_date ) ); ?></td>
													<td class="text-center mjschool_border_black_2px"><?php echo esc_html( $entry->entry ); ?></td>
													<td class="text-center mjschool_border_black_2px"><?php echo esc_html( mjschool_get_display_name( $entry_row->create_by ) ); ?></td>
													<td class="text-center mjschool_border_black_2px"><?php echo esc_html( number_format( $amount, 2, '.', '' ) ); ?></td>
												</tr>
												<?php
											}
										}
									}
									// Show single invoice entry if available.
									if ( ! empty( $invoice_data ) ) {
										$total_amount = $invoice_data->amount;
										?>
										<tr>
											<td class="text-center mjschool_border_black_2px"><?php echo esc_html( $id ); ?></td>
											<td class="text-center mjschool_border_black_2px"><?php echo esc_html( mjschool_get_date_in_input_box( $invoice_data->date ) ); ?></td>
											<td class="text-center mjschool_border_black_2px"><?php echo esc_html( $invoice_data->payment_title ); ?></td>
											<td class="text-center mjschool_border_black_2px"><?php echo esc_html( mjschool_get_display_name( $invoice_data->payment_reciever_id ) ); ?></td>
											<td class="text-center mjschool_border_black_2px"><?php echo esc_html( number_format( $invoice_data->amount, 2, '.', '' ) ); ?></td>
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
						<div class="table-responsive mjschool-table-max-height-180px mjschool-rtl-padding-left-40px">
							<table class="table mjschool-model-invoice-table">
								<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading">
									<tr>
										<th class="mjschool-entry-table-heading mjschool-align-center">#</th>
										<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
										<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Entry', 'mjschool' ); ?></th>
										<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Price', 'mjschool' ); ?></th>
										<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Issue By', 'mjschool' ); ?></th>
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
										$patient_all_income = $mjschool_obj_invoice->mjschool_get_onestudent_income_data( mjschool_decrypt_id( $idtest ) );
										foreach ( $patient_all_income as $result_income ) {
											$income_entries = json_decode( $result_income->entry );
											foreach ( $income_entries as $each_entry ) {
												$total_amount += $each_entry->amount;
												?>
												<tr>
													<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $id ); ?></td>
													<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_get_date_in_input_box( $result_income->income_create_date ) ); ?></td>
													<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $each_entry->entry ); ?> </td>
													<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $each_entry->amount, 2, '.', '' ) ) ); ?></td>
													<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_get_display_name( $result_income->create_by ), 'mjschool' ); ?></td>
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
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_get_date_in_input_box( $invoice_data->date ) ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( $invoice_data->payment_title ); ?> </td>
											<td class="mjschool-align-center mjschool-invoice-table-data"> <?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $invoice_data->amount, 2, '.', '' ) ) ); ?></td>
											<td class="mjschool-align-center mjschool-invoice-table-data"><?php echo esc_html( mjschool_get_display_name( $invoice_data->payment_reciever_id ) ); ?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
					<?php } ?>
					<?php
					if ( ! empty( $invoice_data ) ) {
						$grand_total = $total_amount;
						$sub_total   = $invoice_data->fees_amount;
						$tax_amount  = $invoice_data->tax_amount;
						if ( ! empty( $invoice_data->tax ) ) {
							$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $invoice_data->tax ) );
						} else {
							$tax_name = '';
						}
					}
					if ( ! empty( $income_data ) ) {
						if ( ! empty( $income_data->tax ) ) {
							$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $income_data->tax ) );
						} else {
							$tax_name = '';
						}
						$sub_total = 0;
						if ( ! empty( $income_data->entry ) ) {
							$all_income_entry = json_decode( $income_data->entry );
							foreach ( $all_income_entry as $one_entry ) {
								$sub_total += $one_entry->amount;
							}
						}
						$tax_amount  = $income_data->tax_amount;
						$grand_total = $sub_total + $tax_amount;
					}
					?>
					<?php
					if ( $format === 1 ) {
						?>
						<div class="table-responsive mjschool-rtl-padding-left-40px mjschool-rtl-float-left-width-100px">
							<table class="table table-bordered mjschool_fees_collapse_width_100" >
								<tbody>
									<?php if ( isset( $tax_amount ) && ! empty( $tax_amount ) ) : ?>
										<tr>
											<th style="width: 85%;text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;font-weight: 600;background-color: #b8daff;padding: 10px;border: 2px solid black;" scope="row">
												<?php echo esc_html__( 'Sub Total', 'mjschool' ) . ' :'; ?>
											</th>
											<td style="width: 15%;text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;padding: 10px;font-weight: 600;border: 2px solid black;">
												<?php echo esc_html( number_format( $sub_total, 2, '.', '' ) ); ?>
											</td>
										</tr>
										<tr>
											<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;font-weight: 600;background-color: #b8daff;padding: 10px;border: 2px solid black;" scope="row">
												<?php echo esc_html__( 'Tax Amount', 'mjschool' ) . ' ( ' . esc_html( $tax_name ) . ' ) :'; ?>
											</th>
											<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;padding: 10px;font-weight: 600;border: 2px solid black;">
												<?php echo '+' . esc_html( number_format( $tax_amount, 2, '.', '' ) ); ?>
											</td>
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
									<?php
									if ( isset( $tax_amount ) && ! empty( $tax_amount ) ) {
										?>
										<tr>
											<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right"><?php echo esc_html__( 'Sub Total', 'mjschool' ) . '  :'; ?></td>
											<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $sub_total, 2, '.', '' ) ) ); ?></td>
										</tr>
										<tr>
											<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right"><?php echo esc_html__( 'Tax Amount', 'mjschool' ) . '( ' . esc_attr( $tax_name ) . ' )' . '  :'; ?></td>
											<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value"><?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $tax_amount, 2, '.', '' ) ) ); ?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
					<?php } ?>
					<div id="mjschool-res-rtl-width-100px" class="row mjschool-margin-top-10px-res mjschool-res-rtl-width-100px col-md-4 col-sm-4 col-xs-4 mjschool-view-invoice-lable-css mjschool-inovice-width-100px-rs mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total mjschool_float_margin_right_0px">
						<div class="mjschool-width-50-res mjschool-align-right col-md-5 col-sm-5 col-xs-5 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
							<h3 class="padding mjschool-color-white margin mjschool-invoice-total-label mjschool_float_right"><?php esc_html_e( 'Grand Total', 'mjschool' ); ?> </h3>
						</div>
						<div class="mjschool-width-50-res mjschool-align-right col-md-7 col-sm-7 col-xs-7 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
							<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value mjschool_float_right" >
								<?php
								$formatted_amount = number_format( $grand_total, 2, '.', '' );
								$currency_symbol  = mjschool_get_currency_symbol(); // Use this if function to get the symbol.
								echo esc_html( "({$currency_symbol}){$formatted_amount}" );
								?>
							</h3>
						</div>
					</div>
					<div class="rtl_sings mjschool_fees_border_2px_margin_20px">
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
					<div class="col-md-12 grand_total_main_div total_mjschool-padding-15px mjschool-rtl-float-none">
						<div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-8 col-sm-8 col-xs-8 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
							<div class="col-md-2 mjschool-print-btn-rs mjschool-width-50-res mjschool-width-10px">
								<a href="?page=mjschool_payment&print=print&invoice_id=<?php echo esc_attr($idtest); ?>&invoice_type=<?php echo esc_attr($invoice_type); ?>" target="_blank" class="btn mjschool-color-white btn mjschool-save-btn mjschool-invoice-btn-div"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-print.png"); ?>"> </a>
							</div>
							<?php
							if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
								if ( isset( $_POST['download_app_pdf'] ) ) {
									$file_path = content_url() . '/uploads/invoice_pdf/income/' . mjschool_decrypt_id( $idtest ) . '.pdf';
									if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
										unlink( $file_path ); // Delete the file.
									}
									$generate_pdf = mjschool_fees_income_pdf_for_mobile_app( $idtest, $invoice_type );
									wp_safe_redirect( $file_path );
									die();
								}
								?>
								<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
									<form name="app_pdf1" action="" method="post">
										<div class="form-body mjschool-user-form mjschool-margin-top-40px">
											<div class="row mjschool-invoice-print-pdf-btn">
												<div class="col-md-1 mjschool-print-btn-rs">
													<button data-toggle="tooltip" name="download_app_pdf" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-pdf.png"); ?>"></button>
												</div>
											</div>
										</div>
									</form>
								</div>
								<?php
							} else {
								?>
								<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
									<a href="?page=mjschool_payment&print=pdf&invoice_id=<?php echo esc_attr($idtest); ?>&invoice_type=<?php echo esc_attr($invoice_type); ?>" target="_blank" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-pdf.png"); ?>"></a>
								</div>
								<?php
							}
							?>
						</div>
					</div>
					<div class="mjschool-margin-top-20px"></div>
				</div>
			</div>
		</div><!---- Model body.  ----->
	</div><!----- Payment Invoice. --------->
</div><!----- penal Body. --------->