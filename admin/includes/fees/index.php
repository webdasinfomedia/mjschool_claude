<?php
/**
 * Fees Management Page
 *
 * Handles the management of fee types within the MJSchool plugin.
 * Provides functionality to add, edit, delete, and list fee types,
 * along with integration of custom fields for each fee module.
 *
 * Displays tabs for viewing the list of fee types and adding new ones,
 * and processes related form submissions securely using nonces.
 *
 *
 * @since      1.0.0
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/fees
 */
defined( 'ABSPATH' ) || exit;
$mjschool_obj_fees         = new Mjschool_Fees();
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'fee_pay';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce_action']) ), 'delete_action' ) ) {
		if ( isset( $_REQUEST['fees_id'] ) ) {
			$result = $mjschool_obj_fees->mjschool_delete_feetype_data( mjschool_decrypt_id( wp_unslash($_REQUEST['fees_id']) ) );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees&tab=feeslist&message=3' ) );
				die();
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_POST['save_feetype'] ) ) {
	if ( $_REQUEST['action'] === 'edit' ) {
		if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce_action']) ), 'edit_action' ) ) {
			$fees_id                   = sanitize_text_field( wp_unslash($_REQUEST['fees_id']) );
			$result                    = $obj_lib->mjschool_add_book( wp_unslash($_POST) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                = 'fee_pay';
			$custom_field_update   = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $book_id );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees&tab=feeslist&message=2' ) );
				die();
			}
		} else {
			wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
		}
	} elseif ( ! $mjschool_obj_fees->mjschool_is_duplicat_fees( $_POST['fees_title_id'], $_POST['class_id'] ) ) {
		$result                    = $mjschool_obj_fees->mjschool_add_fees( wp_unslash($_POST) );
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                = 'fee_pay';
		$insert_custom_data    = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees&tab=feeslist&message=1' ) );
			die();
		}
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees&tab=feeslist&message=4' ) );
		die();
	}
}
if ( isset( $_REQUEST['message'] ) ) {
	$message = sanitize_text_field( wp_unslash($_REQUEST['message']) );
	if ( $message === 1 ) { ?>
		<div id="mjschool-message" class="mjschool-message_class updated mjschool-below-h2">
			<p><?php esc_html_e( 'Record Added successfully', 'mjschool' ); ?></p>
		</div>
		<?php
	} elseif ( $message === 2 ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class updated mjschool-below-h2">
			<p><?php esc_html_e( 'Record updated successfully', 'mjschool' ); ?></p>
		</div>
		<?php
	} elseif ( $message === 3 ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class updated mjschool-below-h2">
			<p><?php esc_html_e( 'Record deleted successfully', 'mjschool' ); ?></p>
		</div>
		<?php
	} elseif ( $message === 4 ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class updated mjschool-below-h2">
			<p><?php esc_html_e( 'Fee type All Ready Exist', 'mjschool' ); ?></p>
		</div>
		<?php
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'feeslist';
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="invoice_data"></div>		 
		</div>
	</div>    
</div>
<!-- End POP-UP Code. -->
<div class="mjschool-page-inner">
	<div class="page-title">
		
		<h3><img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="img-circle head_logo" width="40" height="40" /><?php echo esc_html( get_option( 'mjschool_name' ) );?></h3>
		
	</div>
	<div  id="main-wrapper" class="payment_list mjschool_new_main_warpper"> 
		<div class="panel mjschool-panel-white">
			<div class="mjschool-panel-body">     
				<h2 class="nav-tab-wrapper">
					<a href="<?php echo esc_url( '?page=mjschool_fees&tab=feeslist' ); ?>" class="nav-tab <?php echo esc_attr( $active_tab  ) === 'feeslist' ? 'nav-tab-active' : ''; ?>">
						<?php echo '<span class="dashicons dashicons-menu"></span>'. esc_html__( 'Fees Type List', 'mjschool' ); //phpcs:ignore ?>
					</a>
					<?php
					if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'edit' && $_REQUEST['tab'] === 'addfeetype' ) {
						?>
						<a href="<?php echo esc_url( '?page=mjschool_fees&tab=addfeetype&action=edit&fees_id='. sanitize_text_field( wp_unslash( $_REQUEST['fees_id'] ) ) ); ?>" class="nav-tab <?php echo esc_attr( $active_tab  ) === 'addfeetype' ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e( 'Edit Fees Type', 'mjschool' ); ?>
						</a>  
						<?php
					} else {
						?>
						<a href="<?php echo esc_url( '?page=mjschool_fees&tab=addfeetype' ); ?>" class="nav-tab <?php echo esc_attr( $active_tab  ) === 'addfeetype' ? 'nav-tab-active' : ''; ?>">
							<?php echo '<span class="dashicons dashicons-plus-alt"></span>'. esc_html__( 'Add Fee Type', 'mjschool' ); //phpcs:ignore ?>
						</a>  
						<?php
					}
					?>
				</h2>
				<?php
				if ( $active_tab === 'feeslist' ) {
					$retrieve_class_data = $mjschool_obj_fees->mjschool_get_all_fees();
					?>
					<div class="mjschool-panel-body">
						<div class="table-responsive">
							<table id="mjschool-fees-table" class="display" cellspacing="0" width="100%">
								<thead>
									<tr>                
										<th><?php esc_html_e( 'Fee Type', 'mjschool' ); ?></th>                
										<th><?php esc_html_e( 'Class', 'mjschool' ); ?> </th>              
										<th><?php esc_html_e( 'Amount', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>				   
										<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>             
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th><?php esc_html_e( 'Fee Type', 'mjschool' ); ?></th>                
										<th><?php esc_html_e( 'Class', 'mjschool' ); ?> </th>              
										<th><?php esc_html_e( 'Amount', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>                
										<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>         
									</tr>
								</tfoot>
								<tbody>
									<?php
									foreach ( $retrieve_class_data as $retrieved_data ) {
										?>
										<tr>
											<td><?php echo esc_html( get_the_title( $retrieved_data->fees_title_id ) ); ?></td>
											<td><?php echo esc_html( mjschool_get_class_name( $retrieved_data->class_id ) ); ?></td>
											<td><?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . esc_html( $retrieved_data->fees_amount ); ?></td>
											<td><?php echo esc_html( $retrieved_data->description ); ?></td>
											<td>
												<a href="<?php echo esc_url('?page=mjschool_fees&tab=addfeetype&action=edit&fees_id='. mjschool_encrypt_id( $retrieved_data->fees_id ) .'&_wpnonce_action='. mjschool_get_nonce( 'edit_action' ) ); ?>" class="btn btn-info"><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
												<a href="<?php echo esc_url('?page=mjschool_fees&tab=feeslist&action=delete&fees_id='. mjschool_encrypt_id( $retrieved_data->fees_id ) .'&_wpnonce_action='. mjschool_get_nonce( 'delete_action' ) ); ?>" class="btn btn-danger" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
											</td>
										</tr>
									<?php } ?>     
								</tbody>        
							</table>
						</div>
					</div>
					<?php
				}
				if ( $active_tab === 'addfeetype' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/fees/add-feetype.php';
				}
				?>
			</div>
		</div>
	</div>
</div>