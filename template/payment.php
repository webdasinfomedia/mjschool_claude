<?php
/**
 * Payment and Invoice Management Page.
 *
 * This file manages the display and functionality related to financial transactions
 * and invoices. It includes access control checks, logic to determine the active tab
 * (e.g., 'incomelist'), and options for generating and downloading invoices in PDF format.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
$user_id   = get_current_user_id();
$school_type = get_option( "mjschool_custom_class");
$mjschool_role_name = mjschool_get_user_role( get_current_user_id() );
mjschool_browser_javascript_check();
$tablename            = 'mjschool_payment';
$mjschool_obj_invoice = new Mjschool_Invoice();
$active_tab           = isset( $_REQUEST['tab'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : 'incomelist';
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
// --------------- Save payment. ---------------------//
if ( isset( $_POST['save_payment'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_payment_frontend_nonce' ) ) {
		$section_id = 0;
		if ( isset( $_POST['class_section'] ) ) {
			$section_id = sanitize_text_field(wp_unslash($_POST['class_section']));
		}
		$created_date = date( 'Y-m-d H:i:s' );
		if ( isset( $_POST['tax'] ) ) {
			$tax        = implode( ',', (array) sanitize_text_field(wp_unslash($_POST['tax'])) );
			$tax_amount = mjschool_get_tax_amount( sanitize_text_field(wp_unslash($_POST['amount'])), sanitize_text_field(wp_unslash($_POST['tax'])) );
		} else {
			$tax        = null;
			$tax_amount = 0;
		}
		$nonce = wp_create_nonce( 'mjschool_payment_tab' );
		$total_amount = sanitize_text_field(wp_unslash($_POST['amount'])) + $tax_amount;
		$payment_data = array(
			'student_id'          => sanitize_text_field( wp_unslash($_POST['student_id']) ),
			'class_id'            => sanitize_text_field( wp_unslash($_POST['class_id']) ),
			'section_id'          => $section_id,
			'payment_title'       => sanitize_textarea_field( wp_unslash($_POST['payment_title']) ),
			'description'         => sanitize_textarea_field( wp_unslash($_POST['description']) ),
			'payment_status'      => sanitize_textarea_field( wp_unslash($_POST['payment_status']) ),
			'date'                => $created_date,
			'payment_reciever_id' => get_current_user_id(),
			'created_by'          => get_current_user_id(),
			'tax'                 => $tax,
			'tax_amount'          => $tax_amount,
			'fees_amount'         => sanitize_text_field(wp_unslash($_POST['amount'])),
			'amount'              => $total_amount,
		);
		if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$transport_id = array( 'payment_id' => mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['payment_id'])) ) );
				$result       = mjschool_update_record( $tablename, $payment_data, $transport_id );
				if ( $result ) {
					
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=paymentlist&_wpnonce='.esc_attr( $nonce ).'&message=2') );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result = mjschool_insert_record( $tablename, $payment_data );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=paymentlist&_wpnonce='.esc_attr( $nonce ).'&message=1') );
				die();
			}
		}
	}
}
// --------Save income.-------------//
if ( isset( $_POST['save_income'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_income_frontend_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_payment_tab' );
		if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$income_id                 = intval(wp_unslash($_REQUEST['income_id']));
				$result                    = $mjschool_obj_invoice->mjschool_add_income( wp_unslash($_POST) );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'income';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $income_id );
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=incomelist&_wpnonce='.esc_attr( $nonce ).'&message=4') );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result                    = $mjschool_obj_invoice->mjschool_add_income( wp_unslash($_POST) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'income';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=incomelist&_wpnonce='.esc_attr( $nonce ).'&message=3') );
				die();
			}
		}
	}
}
// --------Save expense.-------------//
if ( isset( $_POST['save_expense'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_expense_front_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_payment_tab' );
		if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$expense_id                = intval(wp_unslash($_REQUEST['expense_id']));
				$result                    = $mjschool_obj_invoice->mjschool_add_expense( wp_unslash($_POST) );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'expense';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $expense_id );
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=expenselist&_wpnonce='.esc_attr( $nonce ).'&message=6') );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result                    = $mjschool_obj_invoice->mjschool_add_expense( wp_unslash($_POST) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'expense';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=expenselist&_wpnonce='.esc_attr( $nonce ).'&message=5') );
				die();
			}
		}
	}
}
// ----------------- DELETE RECORD. ------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$nonce = wp_create_nonce( 'mjschool_payment_tab' );
		if ( isset( $_REQUEST['payment_id'] ) ) {
			$result = mjschool_delete_payment( $tablename, ( intval(wp_unslash($_REQUEST['payment_id'])) ) );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=paymentlist&_wpnonce='.esc_attr( $nonce ).'&message=8') );
				die();
			}
		}
		if ( isset( $_REQUEST['income_id'] ) ) {
			$result = $mjschool_obj_invoice->mjschool_delete_income( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['income_id'])) ) );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=incomelist&_wpnonce='.esc_attr( $nonce ).'&message=9') );
				die();
			}
		}
		if ( isset( $_REQUEST['expense_id'] ) ) {
			$result = $mjschool_obj_invoice->mjschool_delete_expense( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['expense_id'])) ) );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=expenselist&_wpnonce='.esc_attr( $nonce ).'&message=7') );
				die();
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ---------------- DELETE MULTIPLE PAYMENT RECORD. -------------//
if ( isset( $_POST['delete_selected_payment'] ) ) {
	// Verify nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_payments' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( ! empty( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $id ) {
			$result = mjschool_delete_payment( $tablename, intval( $id ) );
		}
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=paymentlist&message=8') );
		exit;
	}
}
// ----------------- DELETE INCOME MULTIPLE RECORD. ------------//
if ( isset( $_POST['delete_selected_income'] ) ) {
	// Verify nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_income' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( isset( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $id ) {
			$result = $mjschool_obj_invoice->mjschool_delete_income( intval( $id ) );
		}
	}
	if ( isset( $_POST['payment_id'] ) ) {
		foreach ( $_POST['payment_id'] as $id ) {
			$result = mjschool_delete_payment( $tablename, intval( $id ) );
		}
	}
	wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=incomelist&message=9') );
	exit;
}
// ----------------- DELETE EXPENSE MULTIPLE RECORD. ------------//
if ( isset( $_POST['delete_selected_expense'] ) ) {
	// Verify nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_expenses' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( ! empty( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $id ) {
			$result = $mjschool_obj_invoice->mjschool_delete_expense( intval( $id ) );
		}
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=payment&tab=expenselist&message=7') );
		exit;
	}
}
?>
<!-- Popup code -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="invoice_data"></div>
		</div>
	</div>
</div>
<!-- End POP-UP Code. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!---------- Panel body. --------------->
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Payment Inserted Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Payment Updated Successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Income Added successfully.', 'mjschool' );
			break;
		case '4':
			$message_string = esc_html__( 'Income updated successfully.', 'mjschool' );
			break;
		case '5':
			$message_string = esc_html__( 'Expense Added successfully.', 'mjschool' );
			break;
		case '6':
			$message_string = esc_html__( 'Expense updated successfully.', 'mjschool' );
			break;
		case '7':
			$message_string = esc_html__( 'Expense Deleted Successfully.', 'mjschool' );
			break;
		case '8':
			$message_string = esc_html__( 'Payment Deleted Successfully.', 'mjschool' );
			break;
		case '9':
			$message_string = esc_html__( 'Income Deleted Successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php?>
		<?php
	}
	if ( $active_tab != 'view_invoice' ) {
		$page_action = '';
		if ( ! empty( $_REQUEST['action'] ) ) {
			$page_action = sanitize_text_field(wp_unslash($_REQUEST['action']));
		}
		?>
		<?php $nonce = wp_create_nonce( 'mjschool_payment_tab' );?>
		<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
			<?php
			$user_role = mjschool_get_roles( $user_id );
			?>
			<li class="<?php if ( $active_tab === 'incomelist' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=incomelist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'incomelist' ? 'active' : ''; ?>"> 
					<?php
					if ( ( $user_role != 'student' ) and ( $user_role != 'parent' ) ) {
						esc_html_e( 'Income List', 'mjschool' );
					} else {
						esc_html_e( 'Payment', 'mjschool' );
					}
					?>
				</a>
			</li>
			<?php
			if ( $active_tab === 'addincome' && $page_action === 'edit' ) {
				?>
				<li class="<?php if ( $active_tab == 'addincome' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=addincome&action=edit&income_id=' . intval( wp_unslash( $_REQUEST['income_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addincome' ? 'active' : ''; ?>"> <?php esc_html_e( 'Edit Income', 'mjschool' ); ?></a>
				</li>
				<?php
			} elseif ( $active_tab === 'addincome' ) {
				if ( $user_access['add'] === '1' ) {
					?>
					<li class="<?php if ( $active_tab === 'addincome' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=addincome' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addincome' ? 'active' : ''; ?>"> <?php esc_html_e( 'Add Income', 'mjschool' ); ?></a>
					</li>
					<?php
				}
			}
			if ( ( $user_role != 'student' ) and ( $user_role != 'parent' ) ) {
				?>
				<li class="<?php if ( $active_tab === 'expenselist' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=expenselist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'expenselist' ? 'active' : ''; ?>"> <?php esc_html_e( 'Expense List', 'mjschool' ); ?></a>
				</li>
				<?php
				if ( $active_tab === 'addexpense' && $page_action === 'edit' ) {
					?>
					<li class="<?php if ( $active_tab === 'addexpense' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=addexpense&action=edit&expense_id=' . intval( wp_unslash( $_REQUEST['expense_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addexpense' ? 'active' : ''; ?>"> <?php esc_html_e( 'Edit Expense', 'mjschool' ); ?></a>
					</li>
					<?php
				} elseif ( $active_tab === 'addexpense' ) {
					if ( $user_access['add'] === '1' ) {
						?>
						<li class="<?php if ( $active_tab === 'addexpense' ) { ?> active<?php } ?>">
							<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=addexpense' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addexpense' ? 'active' : ''; ?>"> <?php esc_html_e( 'Add Expense', 'mjschool' ); ?></a>
						</li>
						<?php
					}
				}
			}
			?>
		</ul>
		<?php
	}
	?>
	<div>
		<?php
		// --------------------- Income list. ------------------------//
		if ( $active_tab === 'incomelist' ) {
			// Check nonce for income list tab.
			if ( isset( $_GET['tab'] ) ) {
				if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_payment_tab' ) ) {
					wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
				}
			}
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'income';
			$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
			$user_id                   = get_current_user_id();
			if ( $school_obj->role === 'student' ) {
				$all_income_data = $mjschool_obj_invoice->mjschool_get_income_own_data( $user_id );
				$retrieve_class_data  = $school_obj->payment_list;
				$merged_data     = array_merge( $all_income_data, $retrieve_class_data );
			}
			// ------- Payment DATA FOR PARENT. ---------//
			elseif ( $school_obj->role === 'parent' ) {
				$all_income_data = $mjschool_obj_invoice->mjschool_get_income_own_data_for_parent();
				$retrieve_class_data  = $school_obj->payment_list;
				$merged_data     = array_merge( $all_income_data, $retrieve_class_data );
			}
			// ------- Payment DATA FOR SUPPORT STAFF. ---------//
			elseif ( $school_obj->role === 'supportstaff' ) {
				$own_data = $user_access['own_data'];
				if ( $own_data === '1' ) {
					$all_income_data = $mjschool_obj_invoice->mjschool_get_income_data_created_by( $user_id );
					$retrieve_class_data  = $mjschool_obj_invoice->mjschool_get_invoice_created_by( $user_id );
					$merged_data     = array_merge( $all_income_data, $retrieve_class_data );
				} else {
					$all_income_data = $mjschool_obj_invoice->mjschool_get_all_income_data();
					$retrieve_class_data  = $mjschool_obj_invoice->mjschool_get_payment_list();
					$merged_data     = array_merge( $all_income_data, $retrieve_class_data );
				}
			} else {
				$all_income_data = $mjschool_obj_invoice->mjschool_get_all_income_data();
				$retrieve_class_data  = $mjschool_obj_invoice->mjschool_get_payment_list();
				$merged_data     = array_merge( $all_income_data, $retrieve_class_data );
			}
			if ( ! empty( $merged_data ) ) {
				?>
				<div class="mjschool-panel-body"><!------------- Panel body. --------------->
					<div class="table-responsive"><!------------ Table responsive. ----------------->
						<!--------------- Income list form. ------------------>
						<form id="mjschool-common-form" name="mjschool-common-form" method="post">
							<?php wp_nonce_field( 'bulk_delete_income' ); ?>
							<table id="tblincome" class="display" cellspacing="0" width="100%">
								<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
									<tr>
										<?php
										if ( $mjschool_role_name === 'supportstaff' ) {
											?>
											<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" id="select_all"></th>
											<?php
										}
										?>
										<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
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
									foreach ( $merged_data as $retrieved_data ) {
										if ( isset( $retrieved_data->invoice_type ) && ( $retrieved_data->invoice_type === 'income' ) ) {
											$all_entry    = json_decode( $retrieved_data->entry );
											$total_amount = 0;
											foreach ( $all_entry as $entry ) {
												$total_amount += $entry->amount;
											}
											$final_total = $total_amount + $retrieved_data->tax_amount;
										} else {
											$final_total = $retrieved_data->amount;
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
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<td class="mjschool-checkbox-width-10px">
													<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="<?php echo ( isset( $retrieved_data->invoice_type ) && $retrieved_data->invoice_type === 'income' ) ? 'id[]' : 'payment_id[]'; ?>" value="<?php echo ( isset( $retrieved_data->invoice_type ) && $retrieved_data->invoice_type === 'income' ) ? esc_attr( $retrieved_data->income_id ) : esc_attr( $retrieved_data->payment_id ); ?>">
												</td>
												<?php
											}
											?>
											<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
												<?php
												if ( isset( $retrieved_data->invoice_type ) && ( $retrieved_data->invoice_type === 'income' ) ) {
													?>
													<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=view_invoice&idtest=' . mjschool_encrypt_id( $retrieved_data->income_id ) . '&invoice_type=income' ); ?>">
													<?php
												} else {
													?>
													<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=view_invoice&idtest=' . mjschool_encrypt_id( $retrieved_data->payment_id ) . '&invoice_type=invoice' ); ?>">
													<?php
												}
												?>
												
												<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png"); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
												</p>
												
												</a>
											</td>
											<td class="patient_name">
												<?php
												if ( isset( $retrieved_data->invoice_type ) && ( $retrieved_data->invoice_type === 'income' ) ) {
													echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->supplier_name ) );
												} else {
													echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
											</td>
											<td class="income_amount"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $final_total, 2, '.', '' ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i></td>
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
											<td class="status">
												<?php
												if ( isset( $retrieved_data->invoice_type ) && ( $retrieved_data->invoice_type === 'income' ) ) {
													echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->income_create_date ) );
												} else {
													echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->date ) );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i>
											</td>
											<td class="status">
												<?php
												if ( isset( $retrieved_data->invoice_type ) && ( $retrieved_data->invoice_type === 'income' ) ) {
													echo esc_html( mjschool_get_display_name( $retrieved_data->create_by ) );
												} else {
													echo esc_html( mjschool_get_display_name( $retrieved_data->payment_reciever_id ) );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Created By', 'mjschool' ); ?>"></i>
											</td>
											<?php
											// Custom Field Values.
											if ( ! empty( $user_custom_field ) ) {
												foreach ( $user_custom_field as $custom_field ) {
													if ( $custom_field->show_in_table === '1' ) {
														$module             = 'income';
														$custom_field_id    = $custom_field->id;
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
															if ( ! empty( $custom_field_value ) ) {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		?>
																		<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
																		<?php
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
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
																<?php
																if ( isset( $retrieved_data->invoice_type ) && ( $retrieved_data->invoice_type === 'income' ) ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=view_invoice&idtest=' . mjschool_encrypt_id( $retrieved_data->income_id ) . '&invoice_type=income' ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"></i> <?php esc_html_e( 'View Invoice', 'mjschool' ); ?></a>
																	</li>
																	<?php
																} else {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=view_invoice&idtest=' . mjschool_encrypt_id( $retrieved_data->payment_id ) . '&invoice_type=invoice' ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"></i> <?php esc_html_e( 'View Invoice', 'mjschool' ); ?></a>
																	</li>
																	<?php
																}
																if ( $user_access['edit'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=addincome&action=' . ( ( isset( $retrieved_data->invoice_type ) && $retrieved_data->invoice_type === 'income' ) ? 'edit' : 'edit_payment' ) . '&income_id=' . ( ( isset( $retrieved_data->invoice_type ) && $retrieved_data->invoice_type === 'income' ) ? mjschool_encrypt_id( $retrieved_data->income_id ) : mjschool_encrypt_id( $retrieved_data->payment_id ) ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																	</li>
																	<?php
																}
																if ( $user_access['delete'] === '1' ) {
																	if ( isset( $retrieved_data->invoice_type ) && ( $retrieved_data->invoice_type === 'income' ) ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=incomelist&action=delete&income_id=' . mjschool_encrypt_id( $retrieved_data->income_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm('<?php echo esc_js( esc_html__( 'Are you sure you want to delete this record?', 'mjschool' ) ); ?>');"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
																		</li>
																		<?php
																	} else {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=incomelist&action=delete&payment_id=' . mjschool_encrypt_id( $retrieved_data->payment_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
																		</li>
																		<?php
																	}
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
							<?php
							if ( $mjschool_role_name === 'supportstaff' ) {
								 ?>
								<div class="mjschool-print-button pull-left">
									<button class="mjschool-btn-sms-color mjschool-button-reload">
										<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
										<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
									</button>
									<?php
									if ($user_access['delete'] === '1' ) {
										?>
										<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_income" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
										<?php
									}
									?>
								</div>
								<?php
							}
							?>
						</form><!--------------- Income list form. ------------------>
					</div><!------------ Table responsive. ----------------->
				</div><!------------- Panel body. --------------->
				<?php
			} else {
				if ($user_access['add'] === '1' ) {
					?>
					<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
						<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=payment&tab=addincome') ); ?>">
							<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
						</a>
						<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
							<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
						</div>
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
		}
		if ( $active_tab === 'addincome' ) {
			$income_id = 0;
			if ( isset( $_REQUEST['income_id'] ) ) {
				$income_id = intval( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['income_id'])) ) );
			}
			// $income_id = $_REQUEST['income_id'];
			$edit = 0;
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
				$edit   = 1;
				$result = $mjschool_obj_invoice->mjschool_get_income_data( $income_id );
			}
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit_payment' ) {
				$edit   = 1;
				$result = mjschool_get_payment_by_id( $income_id );
			}
			?>
			<div class="mjschool-panel-body">
				<form name="income_form" action="" method="post" class="mt-3 mjschool-form-horizontal" id="income_form" enctype="multipart/form-data">
					<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
					<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
					<input type="hidden" name="income_id" value="<?php echo esc_attr( $income_id ); ?>">
					<input type="hidden" name="invoice_type" value="income">
					<div class="header">
						<h3 class="mjschool-first-header mjschool-margin-top-0px-image"><?php esc_html_e( 'Income Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"><!--------- Form Body. --------->
						<div class="row"><!--------- Row Div. --------->
							<div class="col-md-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<?php
								if ( $edit ) {
									$classval = $result->class_id;
								} else {
									$classval = '';
								}
								?>
								<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required] mjschool-max-width-100px">
									<?php
									if ( $addparent ) {
										$classdata = mjschool_get_class_by_id( $student->class_name );
										?>
										<option value="<?php echo esc_attr( $student->class_name ); ?>"><?php echo esc_html( $classdata->class_name ); ?></option>
										<?php
									}
									?>
									<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
									<?php
									foreach ( mjschool_get_all_class() as $classdata ) {
										?>
										<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php } ?>
								</select>
							</div>
							<?php if ( $school_type === 'school' ){ ?>
								<div class="col-md-6 input">
									<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
									<?php
									if ( $edit ) {
										$sectionval = $result->section_id;
									} elseif ( isset( $_POST['class_section'] ) ) {
										$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
									} else {
										$sectionval = '';
									}
									?>
									<select name="class_section" class="mjschool-line-height-30px form-control mjschool-max-width-100px" id="class_section">
										<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
										<?php
										if ( $edit ) {
											foreach ( mjschool_get_class_sections( $result->class_id ) as $sectiondata ) {
												?>
												<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
												<?php
											}
										}
										?>
									</select>
								</div>
							<?php }?>
							<div class="col-md-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<?php
								if ( $edit ) {
									$classval = $result->class_id;
								} else {
									$classval = '';
								}
								?>
								<select name="supplier_name" id="student_list" class="mjschool-line-height-30px form-control validate[required] mjschool-max-width-100px">
									<?php
									if ( isset( $result->supplier_name ) ) {
										$student = get_userdata( $result->supplier_name );
										?>
										<option value="<?php echo esc_attr( $result->supplier_name ); ?>"><?php echo esc_html( $student->first_name ) . ' ' . esc_html( $student->last_name ); ?></option>
										<?php
									} elseif ( isset( $result->student_id ) ) {
										$student = get_userdata( $result->student_id );
										?>
										<option value="<?php echo esc_attr( $result->student_id ); ?>"><?php echo esc_html( mjschool_student_display_name_with_roll( $result->student_id ) ); ?></option>
										<?php
									} else {
										?>
										<option value=""><?php esc_html_e( 'Select student', 'mjschool' ); ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<div class="col-md-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Status', 'mjschool' ); ?></label>
								<select name="payment_status" id="payment_status" class="mjschool-line-height-30px form-control validate[required] mjschool-max-width-100px">
									<option value="Paid" <?php if ( $edit ) { selected( 'Paid', $result->payment_status );} ?> ><?php esc_html_e( 'Paid', 'mjschool' ); ?></option>
									<option value="Part Paid" <?php if ( $edit ) { selected( 'Part Paid', $result->payment_status );} ?> ><?php esc_html_e( 'Part Paid', 'mjschool' ); ?></option>
									<option value="Unpaid" <?php if ( $edit ) { selected( 'Unpaid', $result->payment_status );} ?> ><?php esc_html_e( 'Unpaid', 'mjschool' ); ?></option>
								</select>
							</div>
							<?php wp_nonce_field( 'save_income_frontend_nonce' ); ?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="invoice_date" class="form-control " type="text" value="<?php if ( $edit ) { if ( isset( $result->income_create_date ) ) { echo esc_attr( mjschool_get_date_in_input_box( $result->income_create_date ) ); } elseif ( isset( $result->date ) ) { echo esc_attr( mjschool_get_date_in_input_box( $result->date ) ); } } elseif ( isset( $_POST['invoice_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['invoice_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?> " name="invoice_date" readonly>
										<label for="userinput1"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<div class="mjschool-rtl-margin-top-15px col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-bottom-0px mb-3 mjschool-multiselect-validation-member mjschool-multiple-select">
								<select class="form-control tax_charge" id="tax_id" name="tax[]" multiple="multiple">
									<?php
									if ( $edit ) {
										if ( $result->tax !== null ) {
											$tax_id = explode( ',', $result->tax );
										} else {
											$tax_id[] = '';
										}
									} else {
										$tax_id[] = '';
									}
									$obj_tax   = new Mjschool_Tax_Manage();
									$smgt_taxs = $obj_tax->mjschool_get_all_tax();
									if ( ! empty( $smgt_taxs ) ) {
										foreach ( $smgt_taxs as $data ) {
											$selected = '';
											if ( in_array( $data->tax_id, $tax_id ) ) {
												$selected = 'selected';
											}
											?>
											<option value="<?php echo esc_attr( $data->tax_id ); ?>" <?php echo esc_html( $selected ); ?>> <?php echo esc_html( $data->tax_title ); ?> - <?php echo esc_html( $data->tax_value ); ?></option>
											<?php
										}
									}
									?>
								</select>
								<span class="mjschool-multiselect-label">
									<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Tax', 'mjschool' ); ?></label>
								</span>
							</div>
						</div><!--------- Row Div. --------->
					</div><!--------- Form Body. --------->
					<hr>
					<div class="header">
						<h3 class="mjschool-first-header mjschool-margin-top-0px-image"><?php esc_html_e( 'Income Entry', 'mjschool' ); ?></h3>
					</div>
					<div id="income_entry_main">
						<?php
						if ( $edit ) {
							if ( isset( $result->entry ) ) {
								$all_entry = json_decode( $result->entry );
							} else {
								$payment_title          = $result->payment_title;
								$payment_amount         = $result->fees_amount;
								$payment_object         = new stdClass();
								$payment_object->entry  = $payment_title;
								$payment_object->amount = $payment_amount;
								$all_entry              = array();
								$all_entry[]            = $payment_object;
							}
						} elseif ( isset( $_POST['income_entry'] ) ) {
							$all_data  = $mjschool_obj_invoice->mjschool_get_entry_records( wp_unslash($_POST) );
							$all_entry = json_decode( $all_data );
						}
						if ( ! empty( $all_entry ) ) {
							$i = 0;
							foreach ( $all_entry as $entry ) {
								?>
								<div id="income_entry">
									<div class="form-body mjschool-user-form mjschool-income-feild">
										<div class="row">
											<div class="col-md-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="income_amount" class="form-control validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="<?php echo esc_attr( $entry->amount ); ?>" name="income_amount[]">
														<label for="userinput1"><?php esc_html_e( 'Income Amount', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-3 col-9">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="income_entry" class="form-control mjschool-btn-top validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="<?php echo esc_attr( $entry->entry ); ?>" name="income_entry[]">
														<label for="userinput1"><?php esc_html_e( 'Income Entry Label', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<?php  
											if ($i === 0) {
												?>
												<div class="col-md-2 col-3 mjschool-symptoms-dropdown-div">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_entry()" name="add_new_entry" class="mjschool-rtl-margin-top-15px mjschool-daye-name-onclick" id="add_new_entry">
												</div>
												<?php
											} else {
												?>
												<div class="col-md-2 col-3 mjschool-symptoms-dropdown-div">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" onclick="mjschool_delete_parent_element(this)" class="mjschool-rtl-margin-top-15px">
												</div>
												<?php
											}
											?>
										</div>
									</div>
								</div>
								<?php
								$i++;
							}
						} else { ?>
							<div id="income_entry">
								<div class="form-body mjschool-user-form mjschool-income-feild">
									<div class="row">
										<div class="col-md-3">
											<div class="form-group input">
												<div class="col-md-12 form-control">
													<input id="income_amount" class="form-control mjschool-btn-top validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="" name="income_amount[]">
													<label for="userinput1"><?php esc_html_e( 'Income Amount', 'mjschool' ); ?><span class="required">*</span></label>
												</div>
											</div>
										</div>
										<div class="col-md-3 col-9">
											<div class="form-group input">
												<div class="col-md-12 form-control">
													<input id="income_entry" class="form-control mjschool-btn-top validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="" name="income_entry[]">
													<label for="userinput1"><?php esc_html_e( 'Income Entry Label', 'mjschool' ); ?><span class="required">*</span></label>
												</div>
											</div>
										</div>
										<div class="col-md-2 col-3 mjschool-symptoms-dropdown-div">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_entry()" name="add_new_entry" class="mjschool-rtl-margin-top-15px mjschool-daye-name-onclick" id="add_new_entry">
										</div>
									</div>
								</div>
								<?php  
								?>
							</div>
							<?php
						}
						?>
					</div>
					<?php
					// --------- Get module-wise custom field data. --------------//
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'income';
					$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
					?>
					<hr>
					<div class="form-body mjschool-user-form mjschool-income-feild">
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Income', 'mjschool' ); } else { esc_html_e( 'Add Income', 'mjschool' ); } ?>" name="save_income" class="btn btn-success mjschool-save-btn" />
							</div>
						</div>
					</div>
				</form>
			</div>
			<?php
		}
		// --------------------- Expense list. ------------------------//
		if ( $active_tab === 'expenselist' ) {
			// Check nonce for expense list tab.
			if ( isset( $_GET['tab'] ) ) {
				if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_payment_tab' ) ) {
					wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
				}
			}
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'expense';
			$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
			$user_id                   = get_current_user_id();
			if ( $school_obj->role === 'supportstaff' ) {
				$own_data = $user_access['own_data'];
				if ( $own_data === '1' ) {
					$all_expense_data = $mjschool_obj_invoice->mjschool_get_all_expense_data_created_by( $user_id );
				} else {
					$all_expense_data = $mjschool_obj_invoice->mjschool_get_all_expense_data();
				}
			} else {
				$all_expense_data = $mjschool_obj_invoice->mjschool_get_all_expense_data();
			}
			if ( ! empty( $all_expense_data ) ) {
				$invoice_id = 0;
				?>
				<div class="mjschool-panel-body"><!-------------- Panel body. --------------->
					<div class="table-responsive"><!-------------- Table responsive. -------------->
						<!-------------- Expense list form. ------------------>
						<form id="mjschool-common-form" name="mjschool-common-form" method="post">
							<?php wp_nonce_field( 'bulk_delete_expenses' ); ?>
							<table id="tblexpence-frontend" class="display mjschool-expense-datatable" cellspacing="0" width="100%">
								<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
									<tr>
										<?php
										if ( $mjschool_role_name === 'supportstaff' ) {
											?>
											<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" id="select_all"></th>
											<?php
										}
										?>
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
									foreach ( $all_expense_data as $retrieved_data ) {
										$all_entry    = json_decode( $retrieved_data->entry );
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
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->income_id ); ?>"></td>
												<?php
											}
											?>
											<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
												<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=view_invoice&idtest=' . mjschool_encrypt_id( $retrieved_data->income_id ) . '&invoice_type=expense' ); ?>">
													<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png"); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
													</p>
													
												</a>
											</td>
											<td class="patient_name"><?php echo esc_attr( $retrieved_data->supplier_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Supplier Name', 'mjschool' ); ?>"></i></td>
											<td class="income_amount"><?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . number_format( $total_amount, 2, '.', '' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i></td>
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
											<?php
											// Custom Field Values.
											if ( ! empty( $user_custom_field ) ) {
												foreach ( $user_custom_field as $custom_field ) {
													if ( $custom_field->show_in_table === '1' ) {
														$module             = 'expense';
														$custom_field_id    = $custom_field->id;
														$module_record_id   = $retrieved_data->income_id;
														$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
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
																	<td>
																		<?php
																		if ( ! empty( $custom_field_value ) ) {
																			?>
																			<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
																			<?php
																		} else {
																			esc_html_e( 'N/A', 'mjschool' );
																		}
																		?>
																	</td>
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
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=view_invoice&idtest=' . mjschool_encrypt_id( $retrieved_data->income_id ) . '&invoice_type=expense' ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"></i> <?php esc_html_e( 'View Invoice', 'mjschool' ); ?></a>
																</li>
																<?php
																if ( $user_access['edit'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=addexpense&action=edit&expense_id=' . mjschool_encrypt_id( $retrieved_data->income_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																	</li>
																	<?php
																}
																if ( $user_access['delete'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=payment&tab=expenselist&action=delete&expense_id=' . mjschool_encrypt_id( $retrieved_data->income_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
							<?php
							if ( $mjschool_role_name === 'supportstaff' ) {
								 ?>
								<div class="mjschool-print-button pull-left">
									<button class="mjschool-btn-sms-color mjschool-button-reload">
										<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
										<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
									</button>
									<?php
									if ($user_access['delete'] === '1' ) {
										?>
										<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_expense" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
										<?php
									}
									?>
								</div>
								<?php
							}
							?>
						</form><!-------------- Expense list form. ------------------>
					</div><!-------------- Table responsive. -------------->
				</div><!-------------- Panel body. --------------->
				<?php
			} else {
				if ($user_access['add'] === '1' ) {
					?>
					<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
						<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=payment&tab=addexpense') ); ?>">
							<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
						</a>
						<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
							<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
						</div>
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
		}
		if ( $active_tab === 'addexpense' ) {
			$expense_id = 0;
			if ( isset( $_REQUEST['expense_id'] ) ) {
				
				$expense_id = intval( mjschool_decrypt_id( intval( wp_unslash($_REQUEST['expense_id'])) ) );
			}
			$edit = 0;
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
				$edit   = 1;
				$result = $mjschool_obj_invoice->mjschool_get_income_data( $expense_id );
			}
			?>
			<div class="mjschool-panel-body">
				<form name="expense_form" action="" method="post" class="mt-3 mjschool-form-horizontal" id="expense_form" enctype="multipart/form-data">
					<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
					<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
					<input type="hidden" name="expense_id" value="<?php echo esc_attr( $expense_id ); ?>">
					<input type="hidden" name="invoice_type" value="expense">
					<div class="header">
						<h3 class="mjschool-first-header mjschool-margin-top-0px-image"><?php esc_html_e( 'Expense Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"><!--------- Form Body. --------->
						<div class="row"><!--------- Row Div. --------->
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="supplier_name" class="form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->supplier_name ); } elseif ( isset( $_POST['supplier_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['supplier_name'])) ); } ?>" name="supplier_name">
										<label for="userinput1"><?php esc_html_e( 'Supplier Name', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-md-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Status', 'mjschool' ); ?></label>
								<select name="payment_status" id="payment_status" class="mjschool-line-height-30px form-control validate[required] mjschool-max-width-100px">
									<option value="Paid" <?php if ( $edit ) { selected( 'Paid', $result->payment_status );} ?> ><?php esc_html_e( 'Paid', 'mjschool' ); ?></option>
									<option value="Part Paid" <?php if ( $edit ) { selected( 'Part Paid', $result->payment_status );} ?> ><?php esc_html_e( 'Part Paid', 'mjschool' ); ?></option>
									<option value="Unpaid" <?php if ( $edit ) { selected( 'Unpaid', $result->payment_status );} ?> ><?php esc_html_e( 'Unpaid', 'mjschool' ); ?></option>
								</select>
							</div>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="invoice_date" class="form-control validate[required]" type="text" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $result->income_create_date ) ); } elseif ( isset( $_POST['invoice_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['invoice_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="invoice_date" readonly>
										<label for="userinput1"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
						</div><!--------- Row Div. --------->
					</div><!--------- Form Body. --------->
					<hr>
					<div id="expense_entry_main">
						<?php
						if ( $edit ) {
							$all_entry = json_decode( $result->entry );
						} elseif ( isset( $_POST['income_entry'] ) ) {
							$all_data  = $mjschool_obj_invoice->mjschool_get_entry_records( wp_unslash($_POST) );
							$all_entry = json_decode( $all_data );
						}
						if ( ! empty( $all_entry ) ) {
							$i = 0;
							foreach ( $all_entry as $entry ) {
								?>
								<div id="expense_entry">
									<div class="form-body mjschool-user-form mjschool-income-feild">
										<div class="row">
											<div class="col-md-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="income_amount" class="form-control mjschool-btn-top amt validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="<?php echo esc_attr( $entry->amount ); ?>" name="income_amount[]">
														<label for="userinput1"><?php esc_html_e( 'Expense Amount', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="income_entry" class="form-control entry mjschool-btn-top validate[required,custom[description_validation]] text-input" maxlength="50" type="text" value="<?php echo esc_attr( $entry->entry ); ?>" name="income_entry[]">
														<label for="userinput1"><?php esc_html_e( 'Expense Entry Label', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<?php
											if ( $i === 0 ) {
												 ?>
												<div class="col-md-2 mjschool-symptoms-dropdown-div">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_entry()" name="add_new_entry" class="mjschool-rtl-margin-top-15px mjschool-daye-name-onclick" id="add_new_entry">
												</div>
												<?php
											} else {
												?>
												<div class="col-md-2 mjschool-symptoms-dropdown-div">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" onclick="mjschool_delete_parent_element(this)" class="mjschool-rtl-margin-top-15px">
												</div>
												<?php
											}
											?>
										</div>
									</div>
								</div>
								<?php
								$i++;
							}
						} else {
							?>
							<div id="expense_entry">
								<div class="form-body mjschool-user-form mjschool-income-feild">
									<div class="row">
										<div class="col-md-3">
											<div class="form-group input">
												<div class="col-md-12 form-control">
													<input id="income_amount" class="form-control mjschool-btn-top validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="" name="income_amount[]">
													<label for="userinput1"><?php esc_html_e( 'Expense Amount', 'mjschool' ); ?><span class="required">*</span></label>
												</div>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group input">
												<div class="col-md-12 form-control">
													<input id="income_entry" class="form-control mjschool-btn-top validate[required,custom[description_validation]] text-input" maxlength="50" type="text" value="" name="income_entry[]">
													<label for="userinput1"><?php esc_html_e( 'Expense Entry Label', 'mjschool' ); ?><span class="required">*</span></label>
												</div>
											</div>
										</div>
										<div class="col-md-2 mjschool-symptoms-dropdown-div">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_entry()" name="add_new_entry" class="mjschool-rtl-margin-top-15px mjschool-daye-name-onclick" id="add_new_entry">
										</div>
									</div>
									
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<?php
					// --------- Get module-wise custom field data. --------------//
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'expense';
					$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
					?>
					<?php wp_nonce_field( 'save_expense_front_nonce' ); ?>
					<hr>
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Expense', 'mjschool' ); } else { esc_html_e( 'Create Expense Entry', 'mjschool' ); } ?>" name="save_expense" class="btn btn-success mjschool-save-btn" />
							</div>
						</div>
					</div>
				</form>
			</div>
			<?php
		}
		if ( $active_tab === 'view_invoice' ) {
			$mjschool_obj_invoice = new Mjschool_Invoice();
			if ( sanitize_text_field(wp_unslash($_REQUEST['invoice_type'])) === 'invoice' ) {
				$invoice_data = mjschool_get_payment_by_id( intval( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['idtest'])) ) ) );
			}
			if ( sanitize_text_field(wp_unslash($_REQUEST['invoice_type'])) === 'income' ) {
				$income_data = $mjschool_obj_invoice->mjschool_get_income_data( intval( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['idtest'])) ) ) );
			}
			if ( sanitize_text_field(wp_unslash($_REQUEST['invoice_type'])) === 'expense' ) {
				$expense_data = $mjschool_obj_invoice->mjschool_get_income_data( intval( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['idtest'])) ) ) );
			}
			$format = get_option( 'mjschool_invoice_option' );
			?>
			<?php if ( is_rtl() ) { 
				   wp_enqueue_style( 'mjschool-invoice-rtl-style', plugins_url( '/assets/css/mjschool-invoice-rtl.css', __FILE__ ) );
				} ?>
			<div class="penal-body"><!----- Panel body. --------->
				<div id="mjschool-payment-invoice"><!----- Payment Invoice. --------->
					<div class="modal-body mjschool-border-invoice-page mjschool-margin-top-15px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool_height_1350px"><!---- model body  ----->
						<?php if ( $format === 0 ) { ?>
							<img class="mjschool-rtl-image-set-invoice mjschool-invoice-image mjschool-image-width-98px mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url( plugins_url( '/mjschool/assets/images/listpage-icon/mjschool-invoice.png' ) ); ?>" width="100%">
						<?php } ?>
						<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
							<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
								<div class="row mjschool-margin-top-20px">
									<?php if ( $format === 1 ) { ?>
										<div class="mjschool-width-print mjschool-rtl-heads mjschool_fees_style">
											<div class="mjschool_float_left_width_100">
												<div class="mjschool_float_left_width_25">
													<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
														<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>" class="mjschool_main_logo_class" />
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
										<h3 class="mjschool-school-name-for-invoice-view"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></h3>
										<div class="col-md-1 col-sm-2 col-xs-3">
											<div class="width_1 mjschool-rtl-width-80px">
												<img class="system_logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
											</div>
										</div>
										<div class="col-md-11 col-sm-10 col-xs-9 mjschool-invoice-address mjschool-invoice-address-css">
											<div class="row">
												<div class="col-md-12 col-sm-12 col-xs-12 mjschool-invoice-padding-bottom-15px mjschool-padding-right-0">
													<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Address', 'mjschool' ); ?> </label><br>
													<label class="mjschool-label-value mjschool-word-break-all"> 
														<?php
														echo nl2br( esc_html( chunk_split( get_option( 'mjschool_address' ), 100 ) ) );
														?>
													</label>
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
										<div class="mjschool-width-print mjschool_payment_style">
											<div class="mjschool_float_left_width_100">
												<?php
												if ( ! empty( $expense_data ) ) {
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
													<div class="mjschool_float_left_width_65"><b><?php esc_html_e( 'Bill To', 'mjschool' ); ?>:</b> 
														<?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ) . ' ' . esc_html( get_user_meta( $uid, 'student_id', true ) ); ?>&nbsp;
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
													<div class="mjschool_float_right_width_35"><b><?php esc_html_e( 'Status', 'mjschool' ); ?>:</b>
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
														<div><b><?php esc_html_e( 'Address', 'mjschool' ); ?>:</b>
															<?php echo esc_html( $address ); ?></div>
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
													<div class="mjschool_float_left_width_100"><b><?php esc_html_e( 'Issue Date', 'mjschool' ); ?>:</b>
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
													$all_entries = $mjschool_obj_invoice->mjschool_get_onestudent_income_data( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['idtest'])) ) );
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
													<th class="mjschool-entry-table-heading mjschool-align-center"> <?php esc_html_e( 'Date', 'mjschool' ); ?></th>
													<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Entry', 'mjschool' ); ?> </th>
													<th class="mjschool-entry-table-heading mjschool-align-center"><?php esc_html_e( 'Price', 'mjschool' ); ?></th>
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
													$patient_all_income = $mjschool_obj_invoice->mjschool_get_onestudent_income_data( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['idtest'])) ) );
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
								<?php }
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
										<table class="table table-bordered mjschool_fees_collapse_width_100">
											<tbody>
												<?php if ( isset( $tax_amount ) && ! empty( $tax_amount ) ) : ?>
													<tr>
														<th style="width: 85%; text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 2px solid black;" scope="row">
															<?php echo esc_html__( 'Sub Total', 'mjschool' ) . ' :'; ?>
														</th>
														<td style="width: 15%; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 2px solid black;">
															<?php echo esc_html( number_format( $sub_total, 2, '.', '' ) ); ?>
														</td>
													</tr>
													<tr>
														<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 2px solid black;" scope="row">
															<?php echo esc_html__( 'Tax Amount', 'mjschool' ) . ' ( ' . esc_html( $tax_name ) . ' ) :'; ?>
														</th>
														<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 2px solid black;">
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
											$currency_symbol  = mjschool_get_currency_symbol(); // Use this if your project has a function to get the symbol.
											echo esc_html( "({$currency_symbol}){$formatted_amount}" );
											?>
										</h3>
									</div>
								</div>
								<div class="rtl_sings mjschool_fees_border_2px_margin_20px">
									<!-- Teacher Signature (Middle). -->
									<div class="mjschool_fees_center_width_33">
										<div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" /> </div>
										<div class="mjschool_fees_width_150px"></div>
										<div class="mjschool_margin_top_5px"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
									</div>
								</div>
								<div class="col-md-12 grand_total_main_div total_mjschool-padding-15px mjschool-rtl-float-none">
									<div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-8 col-sm-8 col-xs-8 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
										<div class="col-md-2 mjschool-print-btn-rs mjschool-width-50-res mjschool_widht_10">
											<a href="<?php echo esc_url( '?page=mjschool_payment&print=print&invoice_id=' . intval( wp_unslash( $_REQUEST['idtest'] ) ) . '&invoice_type=' . sanitize_text_field( wp_unslash( $_REQUEST['invoice_type'] ) ) ); ?>" target="_blank" class="btn mjschool-color-white btn mjschool-save-btn mjschool-invoice-btn-div"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-print.png' ); ?>"> </a>
										</div>
										<?php
										if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
											if ( isset( $_POST['download_app_pdf'] ) ) {
												$file_path = esc_url(content_url( '/uploads/invoice_pdf/income/' . mjschool_decrypt_id( intval(wp_unslash($_REQUEST['idtest'])) ) . '.pdf'));
												if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
													unlink( $file_path ); // Delete the file.
												}
												$generate_pdf = mjschool_fees_income_pdf_for_mobile_app( intval(wp_unslash($_REQUEST['idtest'])), sanitize_text_field(wp_unslash($_REQUEST['invoice_type'])) );
												wp_safe_redirect( $file_path );
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
												<a href="<?php echo esc_url( '?page=mjschool_payment&print=pdf&invoice_id=' . intval( wp_unslash( $_REQUEST['idtest'] ) ) . '&invoice_type=' . sanitize_text_field( wp_unslash( $_REQUEST['invoice_type'] ) ) ); ?>" target="_blank" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>"></a>
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
			</div><!----- Panel body. --------->
			<?php
		}
		?>
	</div>
</div><!---------- Panel body. --------------->