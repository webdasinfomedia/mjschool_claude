<?php
/**
 * Expense List Management Page.
 *
 * This file handles the "Expense List" tab of the MJSchool Payment module.
 * It displays all recorded expense entries in a searchable, sortable, and interactive
 * DataTable interface, allowing administrators and authorized users to view, edit, 
 * and delete expense records securely.
 *
 * Key Features:
 * - Displays a dynamic list of expense records with supplier details, total amount, status, and creator.
 * - Supports role-based access control (Add, Edit, Delete, View) for expense data.
 * - Integrates WordPress nonces for secure tab access and action validation.
 * - Fetches and displays custom fields dynamically based on the expense module.
 * - Allows bulk selection and deletion of multiple expense records.
 * - Includes status color-coding for “Paid,” “Part Paid,” and “Unpaid” entries.
 * - Provides DataTable functionality (search, sort, pagination) with multi-language support.
 * - Supports AJAX-based checkbox selection and confirmation prompts for deletions.
 * - Displays responsive “No Data” placeholders with quick links for first-time setup.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/payment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for expense list tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_payment_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'payment' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'expense';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
$obj_invoice = new Mjschool_Invoice();
if ( $active_tab === 'expenselist' ) {
	$invoice_id = 0;
	if ( ! empty( $obj_invoice->mjschool_get_all_expense_data() ) ) {
		?>
		<div class="mjschool-panel-body"><!--------- Panel body. --------->
			<div class="table-responsive"><!--------- Table-responsive. --------->
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="tblexpence" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" id="select_all"></th>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Supplier Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Created By', 'mjschool' ); ?></th>
								<?php
								if ( ! empty( $user_custom_field ) ) {
									foreach ( $user_custom_field as $custom_field ) {
										if ( $custom_field->show_in_table === '1' ) {
											?>
											<th><?php echo esc_html( $custom_field->field_label ); ?></th>
											<?php
										}
									}
								}
								?>
								<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							foreach ( $obj_invoice->mjschool_get_all_expense_data() as $retrieved_data ) {
								$all_entry = json_decode( $retrieved_data->entry );
								$total_amount = 0;
								foreach ( $all_entry as $entry ) {
									$total_amount += $entry->amount;
								}
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
								?>
								<tr>
									<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->income_id ); ?>"></td>
									<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
										<a href="?page=mjschool_payment&tab=view_invoice&idtest=<?php echo esc_attr( $retrieved_data->income_id ); ?>&invoice_type=expense">
											<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png"); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
											</p>
										</a>
									</td>
									<td class="patient_name"><?php echo esc_html( $retrieved_data->supplier_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Supplier Name', 'mjschool' ); ?>"></i></td>
									<td class="income_amount"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $total_amount, 2, '.', '' ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i></td>
									<td>
										<?php
										if ( $retrieved_data->payment_status === 'Paid' ) {
											echo "<span class='mjschool-green-color'> " . esc_html__( 'Fully Paid', 'mjschool' ) . ' </span>';
										} elseif ( $retrieved_data->payment_status === 'Part Paid' ) {
											echo "<span class='mjschool-purpal-color'> " . esc_html__( 'Partially Paid', 'mjschool' ) . ' </span>';
										} else {
											echo "<span class='mjschool-red-color'> " . esc_html__( 'Not Paid', 'mjschool' ) . ' </span>';
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
									</td>
									<td class="status"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->income_create_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i></td>
									<td class="status"><?php echo esc_html( mjschool_get_display_name( $retrieved_data->create_by ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Created By', 'mjschool' ); ?>"></i></td>
									<?php // Custom Field Values.
									if ( ! empty( $user_custom_field ) ) {
										foreach ( $user_custom_field as $custom_field ) {
											if ( $custom_field->show_in_table === '1' ) {
												$module          = 'expense';
												$custom_field_id = $custom_field->id;
												$module_record_id   = $retrieved_data->income_id;
												$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
												if ( $custom_field->field_type === 'date' ) {
													?>
													<td>
														<?php
														if ( ! empty( $custom_field_value ) ) {
															echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</td>
													<?php
												} elseif ( $custom_field->field_type === 'file' ) {
													?>
													<td>
														<?php
														if ( ! empty( $custom_field_value ) ) {
															?>
															<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile">
																<button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i><?php esc_html_e( 'Download', 'mjschool' ); ?></button>
															</a>
															<?php
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</td>
													<?php
												} else {
													?>
													<td> 
														<?php
														if ( ! empty( $custom_field_value ) ) {
															echo esc_html( $custom_field_value );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
													</td>
													<?php
												}
											}
										}
									}
									?>
									<td class="action">
										<div class="mjschool-user-dropdown">
											<ul  class="mjschool_ul_style">
												<li >
													<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
													</a>
													<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
														<li class="mjschool-float-left-width-100px">
															<a href="?page=mjschool_payment&tab=view_invoice&idtest=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->income_id ) ); ?>&invoice_type=expense" class="mjschool-float-left-width-100px"><i class="fas fa-eye"></i> <?php esc_html_e( 'View Invoice', 'mjschool' ); ?></a>
														</li>
														<?php
														if ( $user_access_edit === '1' ) {
															?>
															<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																<a href="?page=mjschool_payment&tab=addexpense&action=edit&expense_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->income_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
															</li>
															<?php
														}
														if ( $user_access_delete === '1' ) {
															?>
															<li class="mjschool-float-left-width-100px">
																<a href="?page=mjschool_payment&tab=expenselist&action=delete&expense_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->income_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																	<i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?>
																</a>
															</li>
															<?php
														}
														?>
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
					<div class="mjschool-print-button pull-left">
						<button class="mjschool-btn-sms-color mjschool-button-reload">
							<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
							<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
						</button>
						<?php
						if ( $user_access_delete === '1' ) {
							 ?>
							<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_expense" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
							<?php  
						}
						?>
					</div>
				</form>
			</div><!--------- Table-responsive. --------->
		</div><!--------- Panel body. --------->
		<?php
	} elseif ( $user_access_add === '1' ) {
		?>
		<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_payment&tab=addexpense' ) ); ?>">
				<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
			</a>
			<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
				<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
			</div>
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
?>