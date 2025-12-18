<?php
/**
 * Admin Fee Transaction Management Interface.
 *
 * This file manages the backend interface for displaying and managing fee payment transactions 
 * within the MJSchool plugin. It provides detailed records of payment history, including payment amount, 
 * method, transaction ID, and custom field data, allowing administrators to efficiently track and manage 
 * financial transactions for students.
 *
 * Key Features:
 * - Displays fee payment transactions associated with a specific student or invoice.
 * - Integrates DataTables for responsive layout, search, sorting, and pagination.
 * - Supports bulk selection and record deletion with “Select All” checkbox functionality.
 * - Dynamically displays custom fields based on module configuration.
 * - Implements WordPress nonces and ID encryption for secure data handling.
 * - Includes color-coded visual indicators for better record distinction.
 * - Provides “View Receipt” functionality for reviewing payment details.
 * - Supports tooltips and localized strings for multi-language compatibility.
 * - Ensures secure handling of file downloads for uploaded payment documents.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/feespayment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
global $wpdb;
$fees_pay_id                = intval(mjschool_decrypt_id( wp_unslash($_REQUEST['idtest']) ) );
$fees_detail_result         = mjschool_get_single_fees_payment_record($fees_pay_id);
$fees_history_detail_result = mjschool_get_payment_history_by_fees_pay_id($fees_pay_id);
$mjschool_obj_feespayment   = new Mjschool_Feespayment();
$format                     = get_option( 'mjschool_invoice_option' );
$table                      = $wpdb->prefix . 'mjschool_fees_payment';
$invoice_number             = mjschool_generate_invoice_number($fees_pay_id);
$mjschool_custom_field_obj  = new Mjschool_Custome_Field();
$module                     = 'fee_transaction';
$user_custom_field          = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module($module);
?>
<div class="penal-body">
    <!----- Panel Body. --------->
    <?php
    $retrieve_class_data = $mjschool_obj_feespayment->mjschool_get_all_fees_payments($fees_pay_id);
    if ( ! empty( $retrieve_class_data ) ) {
    	?>
        <div class="mjschool-panel-body">
            <div class="table-responsive">
                <form id="mjschool-common-form" name="mjschool-common-form" method="post">
                    <table id="feetype_list" class="display mjschool-admin-feestype-datatable" cellspacing="0" width="100%">
                        <thead class="<?php echo esc_attr( mjschool_datatable_header( ) ); ?>">
                            <tr>
                                <th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
                                <th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
                                <th><?php esc_html_e( 'Amount', 'mjschool' ); ?></th>
                                <th><?php esc_html_e( 'Payment Method', 'mjschool' ); ?></th>
                                <th><?php esc_html_e( 'Payment Date', 'mjschool' ); ?></th>
                                <th><?php esc_html_e( 'Transaction id', 'mjschool' ); ?></th>
                                <th><?php esc_html_e( 'Note', 'mjschool' ); ?></th>
                                <?php
                                if ( ! empty( $user_custom_field ) ) {
                                    foreach ($user_custom_field as $custom_field) {
                                        if ($custom_field->show_in_table === '1' ) {
                                			?>
                                            <th><?php echo esc_html( $custom_field->field_label); ?></th>
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
                            foreach ($retrieve_class_data as $retrieved_data) {
                                if ($i === 10) {
                                    $i = 0;
                                }
                                if ($i === 0) {
                                    $color_class_css = 'mjschool-class-color0';
                                } elseif ($i === 1) {
                                    $color_class_css = 'mjschool-class-color1';
                                } elseif ($i === 2 ) {
                                    $color_class_css = 'mjschool-class-color2';
                                } elseif ($i === 3) {
                                    $color_class_css = 'mjschool-class-color3';
                                } elseif ($i === 4) {
                                    $color_class_css = 'mjschool-class-color4';
                                } elseif ($i === 5) {
                                    $color_class_css = 'mjschool-class-color5';
                                } elseif ($i === 6) {
                                    $color_class_css = 'mjschool-class-color6';
                                } elseif ($i === 7) {
                                    $color_class_css = 'mjschool-class-color7';
                                } elseif ($i === 8) {
                                    $color_class_css = 'mjschool-class-color8';
                                } elseif ($i === 9) {
                                    $color_class_css = 'mjschool-class-color9';
                                }
                            	?>
                                <tr>
                                    <td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr($retrieved_data->fees_id); ?>"></td>
                                    <td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
                                        <p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
                                            <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png' ); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
                                        </p>
                                    </td>
                                    <td>
                                        <?php echo esc_html( $retrieved_data->amount); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Amount', 'mjschool' ); ?>"></i>
                                    </td>
                                    <td>
                                        <?php echo esc_html( $retrieved_data->payment_method); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Method', 'mjschool' ); ?>"></i>
                                    </td>
                                    <td>
                                        <?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->paid_by_date ) ); ?>
                                        <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Date', 'mjschool' ); ?>"></i>
                                    </td>
                                    <td>
                                        <?php echo !empty($retrieved_data->trasaction_id) ? esc_html( $retrieved_data->trasaction_id) : 'N/A'; ?>
                                        <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Transaction Id', 'mjschool' ); ?>"></i>
                                    </td>
                                    <?php
                                    $comment     = $retrieved_data->payment_note;
                                    $comment     = ltrim($comment, ' ' );
                                    $description = strlen($comment) > 30 ? substr( $comment, 0, 30) . '...' : $comment;
                                    ?>
                                    <td>
                                        <?php
                                        if ( ! empty( $comment ) ) {
                                            echo esc_html( $description);
                                        } else {
                                            esc_html_e( 'N/A', 'mjschool' );
                                        }
                                        ?>
                                        <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_attr( $comment); } else { esc_attr_e( 'Description', 'mjschool' ); } ?>"></i>
                                    </td>
                                    <?php
                                    // Custom Field Values.
                                    if ( ! empty( $user_custom_field ) ) {
                                        foreach ($user_custom_field as $custom_field) {
                                            if ($custom_field->show_in_table === '1' ) {
                                                $module             = 'fee_transaction';
                                                $custom_field_id    = $custom_field->id;
                                                $module_record_id   = $retrieved_data->payment_history_id;
                                                $custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value($module, $module_record_id, $custom_field_id);
                                                if ($custom_field->field_type === 'date' ) {
                                    				?>
                                                    <td>
                                                        <?php
                                                        if ( ! empty( $custom_field_value ) ) {
                                                            echo esc_html( mjschool_get_date_in_input_box($custom_field_value ) );
                                                        } else {
                                                            esc_html_e( 'N/A', 'mjschool' );
                                                        }
                                                        ?>
                                                    </td>
                                                	<?php
                                                } elseif ($custom_field->field_type == 'file' ) {
                                                	?>
                                                    <td>
                                                        <?php
                                                        if ( ! empty( $custom_field_value ) ) {
                                                            ?>
                                                            <a target="" href="<?php echo esc_url(content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?> </button></a>
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
                                                            echo esc_html( $custom_field_value);
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
                                                        <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
                                                    </a>
                                                    <ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
                                                        <li class="mjschool-float-left-width-100px">
                                                            <a href="<?php echo esc_url('?page=mjschool_fees_payment&tab=view_fesspayment&idtest='.rawurlencode( mjschool_encrypt_id($retrieved_data->fees_pay_id ) ).'&payment_id='. rawurlencode( mjschool_encrypt_id($retrieved_data->payment_history_id ) ).'&view_type=view_receipt&_wpnonce_action='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ); ?>" class="mjschool-float-left-width-100px">
                                                                <i class="fas fa-eye"></i>
                                                                <?php esc_html_e( 'View Receipt', 'mjschool' ); ?>
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
                </form>
            </div>
        </div>
    	<?php
    }
    ?>
</div>
<!----- Panel Body. --------->