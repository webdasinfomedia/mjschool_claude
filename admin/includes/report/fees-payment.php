<?php

/**
 * Fees Payment Report – Graph & DataTable View.
 *
 * This file handles the frontend reporting interface for the fees payment module
 * within the MjSchool plugin. It provides two main views:
 *
 * 1. **Graph View**  
 *    - Displays monthly fee collection data using Google Charts.  
 *    - Fetches fee payment totals grouped by month and year.  
 *    - Uses Google Material Bar Charts for visual representation.
 *
 * 2. **DataTable View**  
 *    - Generates a detailed, filterable, and exportable fee payment report.  
 *    - Allows filtering by class, section, fee type, payment status, date range,
 *      and student active/deactive status.
 *    - Uses jQuery DataTables for searching, sorting, exporting (CSV/Print).
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
$active_tab = isset( $_GET['tab2'] ) ? $_GET['tab2'] : 'fees_payment_graph';
$mjschool_role       = mjschool_get_roles( get_current_user_id() );
?>
<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
	<li class="<?php if ( $active_tab === 'fees_payment_graph' ) { ?> active<?php } ?>">
		<a href="<?php if ( $mjschool_role === 'administrator' ) { echo '?page=mjschool_report'; } else { echo '?dashboard=mjschool_user&page=report'; } ?>&tab=finance_report&tab1=fees_payment&tab2=fees_payment_graph&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'fees_payment_graph' ? 'active' : ''; ?>"> <?php esc_html_e( 'Graph', 'mjschool' ); ?></a>
	</li>
	<li class="<?php if ( $active_tab === 'fees_payment_datatable' ) { ?> active<?php } ?>">
		<a href="<?php if ( $mjschool_role === 'administrator' ) { echo '?page=mjschool_report'; } else { echo '?dashboard=mjschool_user&page=report'; } ?>&tab=finance_report&tab1=fees_payment&tab2=fees_payment_datatable&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'fees_payment_datatable' ? 'active' : ''; ?>"> <?php esc_html_e( 'DataTable', 'mjschool' ); ?></a>
	</li>
</ul>
<?php
if ( $active_tab === 'fees_payment_graph' ) {
	// Check nonce for fees graph report tab.
	if ( isset( $_GET['tab'] ) ) {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_finance_report_tab' ) ) {
			wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
		}
	}

	?>
	<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		$month = array(
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
		$year = isset( $_POST['year'] ) ? $_POST['year'] : date( 'Y' );
		$chart_array = array();
		// $chart_array[] = array(esc_html__( 'Month','mjschool' ),esc_html__( 'Fees Payment','mjschool' ) );
		array_push( $chart_array, array( esc_html__( 'Month', 'mjschool' ), esc_html__( 'Payment', 'mjschool' ) ) );
		$sumArray = array();
		foreach ( $month as $key => $value ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'mjschool_fees_payment';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM $table_name WHERE MONTH(paid_by_date) = %d AND YEAR(paid_by_date) = %d GROUP BY MONTH(paid_by_date) ORDER BY paid_by_date ASC", $key, $year )
			);
			$currency             = mjschool_get_currency_symbol();
			$income_yearly_amount = 0;
			$currency_1           = html_entity_decode( $currency );
			if ( ! empty( $result ) ) {
				$amount = 0;
				foreach ( $result as $retrieved_data ) {
					$amount += $retrieved_data->total_amount;
				}
				$income_yearly_amount += $amount;
			}
			if ( $income_yearly_amount === 0 ) {
				$income_amount = null;
			} else {
				$income_amount = $currency_1 . '' . $income_yearly_amount;
			}
			$feepayment_array[] = $income_amount;
			array_push( $chart_array, array( $value, $income_amount ) );
		}
		$new_array  = $chart_array;
		$feepayment = array_filter( $feepayment_array );
		if ( ! empty( $feepayment ) ) {
			?>
			<div id="mjschool-barchart-material-fees" class="mjschool-barchart-material-fees" data-chart='<?php echo wp_json_encode( $new_array ); ?>' data-color='<?php echo esc_attr( get_option( "mjschool_system_color_code" ) ); ?>' style="width:100%;height: 430px; padding:20px;"></div>
			<?php
		} else {
			 ?>
            <div class="mjschool-calendar-event-new">
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
            </div>
            <?php  
		}
		?>
	</div>
	<?php
}
if ( $active_tab === 'fees_payment_datatable' ) {
	
	// Check nonce for fees datatable report tab.
	if ( isset( $_GET['tab'] ) ) {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_finance_report_tab' ) ) {
			wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
		}
	}
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<!--- Panel body. --->
		<!--------------- FEES PAYMENT FORM. -------------------->
		<form method="post" id="fee_payment_report">
			<div class="form-body mjschool-user-form">
				<!-------------- FORM BODY. ------------------>
				<div class="row">
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control mjschool-load-fee-type-single validate[required]">
							<?php $select_class = isset( $_REQUEST['class_id'] ) ? $_REQUEST['class_id'] : ''; ?>
							<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
							<option value="all_class" <?php echo selected( $select_class, 'all_class' ); ?>><?php esc_html_e( 'All Class', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php echo selected( $select_class, $classdata['class_id'] ); ?>> <?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<?php if ( $school_type === 'school' ) {?>
						<div class="col-md-3 input">
							<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
							<?php
							$class_section = '';
							if ( isset( $_REQUEST['class_section'] ) ) {
								$class_section = $_REQUEST['class_section'];
							}
							?>
							<select name="class_section" class="mjschool-line-height-30px form-control" id="class_section">
								<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
								<?php
								if ( isset( $_REQUEST['class_section'] ) ) {
									echo esc_html( $class_section = $_REQUEST['class_section'] );
									foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>> <?php echo esc_attr( $sectiondata->section_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					<?php }?>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="fees_data"><?php esc_html_e( 'FeesType', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select id="fees_data" class="mjschool-line-height-30px form-control validate[required]" name="fees_id">
							<option value=""><?php esc_html_e( 'Select Fee Type', 'mjschool' ); ?></option>
							<?php
							global $wpdb;
							$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
                            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$result = $wpdb->get_results(
								$wpdb->prepare( "SELECT * FROM $table_mjschool_fees WHERE class_id = %s", $_REQUEST['class_id'] )
							);
							if ( ! empty( $result ) ) {
								foreach ( $result as $retrive_data ) {
									echo '<option value="' . esc_attr( $retrive_data->fees_id ) . '" ' . selected( $_REQUEST['fees_id'], $retrive_data->fees_id ) . '>' . esc_html( get_the_title( $retrive_data->fees_title_id ) ) . '</option>';
								}
							}
							?>
						</select>
					</div>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="fee_status"><?php esc_html_e( 'Payment Status', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select id="fee_status" class="mjschool-line-height-30px form-control validate[required]" name="fee_status">
							<?php $select_payment = isset( $_REQUEST['fee_status'] ) ? $_REQUEST['fee_status'] : ''; ?>
							<option value=""><?php esc_html_e( 'Select Payment Status', 'mjschool' ); ?></option>
							<option value="0" <?php echo selected( $select_payment, 0 ); ?>> <?php esc_html_e( 'Not Paid', 'mjschool' ); ?></option>
							<option value="1" <?php echo selected( $select_payment, 1 ); ?>> <?php esc_html_e( 'Partially Paid', 'mjschool' ); ?></option>
							<option value="2" <?php echo selected( $select_payment, 2 ); ?>> <?php esc_html_e( 'Fully paid', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-md-3">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input type="text" id="sdate" class="form-control" name="sdate" value="<?php if ( isset( $_REQUEST['sdate'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( $_REQUEST['sdate'] ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( 'first day of this month' ) ) ) ); } ?>"readonly>
								<label for="sdate"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input type="text" id="edate" class="form-control" name="edate" value="<?php if ( isset( $_REQUEST['edate'] ) ) {echo esc_attr( mjschool_get_date_in_input_box( $_REQUEST['edate'] ) );} else {echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) );}?>"readonly>
								<label for="edate"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-3 mb-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-status"><?php esc_html_e( 'Student Status', 'mjschool' ); ?></label>
						<?php
						$status = ''; // Default to empty.
						if ( isset( $_REQUEST['status'] ) ) {
							$status = sanitize_text_field( $_REQUEST['status'] );
						}
						?>
						<select id="mjschool-status" name="status" class="mjschool-line-height-30px form-control">
							<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'mjschool' ); ?></option>
							<option value="deactive" <?php selected( $status, 'deactive' ); ?>><?php esc_html_e( 'Deactive', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-md-3">
						<input type="submit" name="report_4" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
			<!-------------- FORM BODY. ------------------>
		</form>
		<!--------------- FEES PAYMENT FORM. -------------------->
	</div>
	<!--- Panel body. --->
	<div class="clearfix"> </div>
	<?php
	if ( isset( $_POST['report_4'] ) ) {
		if ( $_POST['class_id'] != ' ' && $_POST['fees_id'] != ' ' && $_POST['sdate'] != ' ' && $_POST['edate'] != ' ' ) {
			$class_id   = $_POST['class_id'];
			$section_id = 0;
			if ( isset( $_POST['class_section'] ) ) {
				$section_id = $_POST['class_section'];
			}
			$fee_term       = $_POST['fees_id'];
			$payment_status = sanitize_text_field( $_POST['fee_status'] );
			$sdate          = sanitize_text_field( $_POST['sdate'] );
			$edate          = sanitize_text_field( $_POST['edate'] );
			$student_status = sanitize_text_field( $_POST['status'] );
			$feereport      = mjschool_get_payment_report_front( $class_id, $fee_term, $payment_status, $sdate, $edate, $section_id );
            
            if ($student_status === "active") {
                $students = get_users(array(
                    'role'       => 'student',
                    'meta_query' => array(
                        array(
                            'key'     => 'hash',
                            'compare' => 'NOT EXISTS'
                        )
                    )
                ) );
                $student_ids = wp_list_pluck($students, 'ID' );
            } else {
                $students = get_users(array(
                    'role'       => 'student',
                    'meta_query' => array(
                        array(
                            'key'     => 'hash',
                            'compare' => 'EXISTS'
                        )
                    )
                ) );
                $student_ids = wp_list_pluck($students, 'ID' );
            }
            
			$result_feereport = array();
			foreach ( $feereport as $feereport_data ) {
				if ( in_array( $feereport_data->student_id, $student_ids ) ) {
					$result_feereport[] = $feereport_data;
				}
			}
		}
		if ( ! empty( $result_feereport ) ) {
			?>
			<div class="table-responsive">
				<!-------------- Table responsive. ---------------->
				<div class="btn-place"></div>
				<table class="display fees_payment_report" cellspacing="0" width="100%">
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
							<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ( ! empty( $result_feereport ) ) {
							$i = 0;
							foreach ( $result_feereport as $retrieved_data ) {
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
									<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
										
										<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>"> <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png")?>" class="mjschool-massage-image mjschool-margin-top-3px"> </p>
									</td>
									<?php
									$fees_id=explode( ',',$retrieved_data->fees_id);
									$fees_type=array();
									foreach($fees_id as $id)
									{ 
										$fees_type[] = mjschool_get_fees_term_name($id);
									}
									?>
									<td>
										<?php echo esc_html( implode( " , " ,$fees_type ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Term','mjschool' );?>"></i>
									</td>
									<td>
										<?php if( ! empty( $retrieved_data ) ){echo esc_html( mjschool_student_display_name_with_roll($retrieved_data->student_id ) );}else{echo '';}?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name','mjschool' );?>"></i>
									</td>
									<td><?php if ( $retrieved_data->class_id === "0"){ esc_html_e( 'All Class','mjschool' );}else{ echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_id,$retrieved_data->section_id ) );} ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name','mjschool' );?>"></i></td>
									<td>
										<?php 
										$payment_status=mjschool_get_payment_status($retrieved_data->fees_pay_id);
										if ( $payment_status === 'Not Paid' )
										{
											echo "<span class='mjschool-red-color'>";
										}
										elseif ( $payment_status === 'Partially Paid' )
										{
											echo "<span class='mjschool-purpal-color'>";
										}
										else
										{
											echo "<span class='mjschool-green-color'>";
										}
										echo esc_html__( "$payment_status","mjschool" );
										echo "</span>";	
										?> 
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Status','mjschool' );?>"></i>
									</td>
									<td>
										<?php echo esc_html( mjschool_currency_symbol_position_language_wise(number_format($retrieved_data->total_amount,2,'.','' ) ) );?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount','mjschool' );?>"></i>
									</td>
									<?php $Due_amt = $retrieved_data->total_amount-$retrieved_data->fees_paid_amount; ?>
									<td>
										<?php echo esc_html( mjschool_currency_symbol_position_language_wise(number_format($Due_amt,2,'.','' ) ) );?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Due Amount','mjschool' );?>"></i>
									</td>
									<td>
										<?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->start_year ) ).' to '.esc_html( mjschool_get_date_in_input_box($retrieved_data->end_year ) );?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start To End Year','mjschool' );?>"></i>
									</td>
									<td class="action">
										<div class="mjschool-user-dropdown">
											<ul  class="mjschool_ul_style">
												<li >
													<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-more.png")?>">
													</a>
													<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
														<li class="mjschool-float-left-width-100px">
															<a href="<?php if ( $mjschool_role === 'administrator' ){ echo esc_url("?page=mjschool_fees_payment&tab=view_fesspayment"); }else{ echo esc_url("?dashboard=mjschool_user&page=feepayment&tab=view_fesspayment"); }?>&idtest=<?php echo esc_attr($retrieved_data->fees_pay_id); ?>&view_type=view_payment" class="mjschool-float-left-width-100px"><i class="fa fa-eye"></i><?php esc_html_e( 'View','mjschool' );?></a>
														</li>
													</ul>
												</li>
											</ul>
										</div>
									</td>
								</tr>
								<?php 
								$i++;
							}
						}
						?>
					</tbody>
				</table>
			</div>
            <!-------------- Table responsive. ---------------->
        	<?php
        } else {
        	?>
            <div class="mjschool-calendar-event-new">
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
            </div>
            <?php  
		}
	}
}