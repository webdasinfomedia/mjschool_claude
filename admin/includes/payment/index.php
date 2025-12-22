<?php
/**
 * Payment Module Main Controller.
 *
 * This file serves as the main controller for the Payment module in the MJSchool plugin.
 * It manages all core operations related to income and expense management including
 * creation, updating, deletion, and listing functionalities. It also controls the 
 * tab-based navigation for "Income List", "Add/Edit Income", "Expense List", and 
 * "Add/Edit Expense" pages within the WordPress admin dashboard.
 *
 * Key Features:
 * - Manages CRUD operations (Create, Read, Update, Delete) for both income and expense records.
 * - Implements WordPress nonce verification for secure form submissions and tab actions.
 * - Enforces role-based access rights (Add, Edit, Delete, View) for users other than administrators.
 * - Integrates custom field management for income and expense modules.
 * - Provides dynamic tab navigation between income and expense management sections.
 * - Displays contextual success messages (e.g., Added, Updated, Deleted) using WordPress alerts.
 * - Supports bulk deletion of income and expense records with confirmation.
 * - Handles AJAX-based invoice viewing in a modal popup interface.
 * - Uses WordPress sanitization and escaping functions for security and compliance.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/payment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
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
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			if ( 'payment' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'payment' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'payment' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
?>
<?php
$tablename            = 'mjschool_payment';
$mjschool_obj_invoice = new Mjschool_Invoice();
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$nonce = wp_create_nonce( 'mjschool_payment_tab' );
		if ( isset( $_REQUEST['payment_id'] ) ) {
			$result = mjschool_delete_payment( $tablename, mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['payment_id'])) ) );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=payment&_wpnonce='.rawurlencode( $nonce ).'&message=payment_del' ) );
				die();
			}
		}
		if ( isset( $_REQUEST['income_id'] ) ) {
			$result = $mjschool_obj_invoice->mjschool_delete_income( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['income_id'])) ) );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=incomelist&_wpnonce='.rawurlencode( $nonce ).'&message=income_del' ) );
				die();
			}
		}
		if ( isset( $_REQUEST['expense_id'] ) ) {
			$result = $mjschool_obj_invoice->mjschool_delete_expense( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['expense_id'])) ) );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=expenselist&_wpnonce='.rawurlencode( $nonce ).'&message=expense_del' ) );
				die();
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// Delete Income.
if ( isset( $_REQUEST['delete_selected_income'] ) ) {
	$nonce = wp_create_nonce( 'mjschool_payment_tab' );
	if ( isset( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = $mjschool_obj_invoice->mjschool_delete_income( $id );
		}
	}
	if ( isset( $_REQUEST['payment_id'] ) ) {
		foreach ( $_REQUEST['payment_id'] as $id ) {
			$result = mjschool_delete_payment( $tablename, $id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=incomelist&_wpnonce='.rawurlencode( $nonce ).'&message=income_del' ) );
		die();
	}
}
// Delete Expense.
if ( isset( $_REQUEST['delete_selected_expense'] ) ) {
	$nonce = wp_create_nonce( 'mjschool_payment_tab' );
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = $mjschool_obj_invoice->mjschool_delete_expense( $id );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=expenselist&_wpnonce='.rawurlencode( $nonce ).'&message=3' ) );
			die();
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=expenselist&_wpnonce='.rawurlencode( $nonce ).'&message=3' ) );
		die();
	}
}
// Save Income.
if ( isset( $_POST['save_income'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_income_fees_admin_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_payment_tab' );
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			$income_id                 = sanitize_text_field(wp_unslash($_REQUEST['income_id']));
			$result                    = $mjschool_obj_invoice->mjschool_add_income( wp_unslash($_POST) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'income';
			$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $income_id );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=incomelist&_wpnonce='.rawurlencode( $nonce ).'&message=income_edit' ) );
			die();
		} else {
			$result                    = $mjschool_obj_invoice->mjschool_add_income( wp_unslash($_POST) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'income';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=incomelist&_wpnonce='.rawurlencode( $nonce ).'&message=income_add' ) );
				die();
			}
		}
	}
}
// Save Expense.
if ( isset( $_POST['save_expense'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_expense_fees_admin_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_payment_tab' );
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
		    $expense_id = isset( $_REQUEST['expense_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['expense_id'] ) ) : '';
			$result                    = $mjschool_obj_invoice->mjschool_add_expense( wp_unslash($_POST) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'expense';
			$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $expense_id );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=expenselist&_wpnonce='.rawurlencode( $nonce ).'&message=expense_edit' ) );
			die();
		} else {
			$result                    = $mjschool_obj_invoice->mjschool_add_expense( wp_unslash($_POST) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'expense';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_payment&tab=expenselist&_wpnonce='.rawurlencode( $nonce ).'&message=expense_add' ) );
				die();
			}
		}
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'incomelist';
?>
<!-- Start POP-UP Code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-popup-payment">
		<div class="modal-content">
			<div class="invoice_data"></div>
		</div>
	</div>
</div>
<!-- End POP-UP Code. -->
<div class="mjschool-page-inner"><!------ Page Inner. ----->
	<div class=" payment_list mjschool-main-list-margin-5px mjschool-tab-margin-top-40px">
		<?php
		if ( isset( $_REQUEST['message'] ) ) {
			$message        = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : 0;
			$message_string = '';
			switch ( $message ) {
				case '1':
					$message_string = esc_html__( 'Payment Added Successfully.', 'mjschool' );
					break;
				case '2':
					$message_string = esc_html__( 'Payment Updated Successfully.', 'mjschool' );
					break;
				case '3':
					$message_string = esc_html__( 'Expense Deleted Successfully.', 'mjschool' );
					break;
				case 'payment_del':
					$message_string = esc_html__( 'Payment Deleted Successfully.', 'mjschool' );
					break;
				case 'income_del':
					$message_string = esc_html__( 'Income Deleted Successfully.', 'mjschool' );
					break;
				case 'expense_del':
					$message_string = esc_html__( 'Expense Deleted Successfully.', 'mjschool' );
					break;
				case 'income_add':
					$message_string = esc_html__( 'Income Added Successfully.', 'mjschool' );
					break;
				case 'income_edit':
					$message_string = esc_html__( 'Income Updated Successfully.', 'mjschool' );
					break;
				case 'expense_add':
					$message_string = esc_html__( 'Expense Added Successfully.', 'mjschool' );
					break;
				case 'expense_edit':
					$message_string = esc_html__( 'Expense Updated Successfully.', 'mjschool' );
					break;
			}
			if ( $message ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php echo esc_html( $message_string ); ?></p>
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
				</div>
				<?php
			}
		}
		?>
		<div class="mjschool-panel-white"><!--------- Panel White. ---------->
			<div class="mjschool-panel-body"> <!--------- Panel Body. ---------->
				<?php
				if ( $active_tab != 'view_invoice' ) {
					$mjschool_action = '';
					if ( ! empty( $_REQUEST['action'] ) ) {
						$mjschool_action = sanitize_text_field(wp_unslash($_REQUEST['action']));
					}
					?>
					<?php $nonce = wp_create_nonce( 'mjschool_payment_tab' );?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'incomelist' ) { ?>active<?php } ?>">
							<a href="?page=mjschool_payment&tab=incomelist&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'incomelist' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Income List', 'mjschool' ); ?>
							</a>
						</li>
						<?php
						if ( $active_tab === 'addincome' && $mjschool_action === 'edit' ) {
							?>
							<li class="<?php if ( $active_tab === 'addincome' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_payment&tab=addincome" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addincome' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Edit Income', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						} elseif ( $active_tab === 'addincome' ) {
							?>
							<li class="<?php if ( $active_tab === 'addincome' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_payment&tab=addincome" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addincome' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Add Income', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
						?>
						<li class="<?php if ( $active_tab === 'expenselist' ) { ?>active<?php } ?>">
							<a href="?page=mjschool_payment&tab=expenselist&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'expenselist' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Expense List', 'mjschool' ); ?>
							</a>
						</li>
						<?php
						if ( $active_tab === 'addexpense' && $mjschool_action === 'edit' ) {
							?>
							<li class="<?php if ( $active_tab === 'addexpense' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_payment&tab=addexpense" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addexpense' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Edit Expense', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						} elseif ( $active_tab === 'addexpense' ) {
							?>
							<li class="<?php if ( $active_tab === 'addexpense' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_payment&tab=addexpense" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addexpense' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Add Expense', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
					<?php
				}
				if ( $active_tab === 'incomelist' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/payment/income-list.php';
				}
				if ( $active_tab === 'addincome' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/payment/add-income.php';
				}
				if ( $active_tab === 'expenselist' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/payment/expense-list.php';
				}
				if ( $active_tab === 'addexpense' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/payment/add-expense.php';
				}
				if ( $active_tab === 'view_invoice' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/payment/view-invoice.php';
				}
				?>
			</div><!--------- Panel Body. ---------->
		</div><!--------- Panel White. ---------->
	</div>
</div><!------ Page Inner. ----->