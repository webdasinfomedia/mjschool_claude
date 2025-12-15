<?php
/**
 * Admin Recurring Fees Payment Management Interface.
 *
 * This file manages the backend functionality for listing, editing, deleting, and sending reminders 
 * for recurring fees payments within the MJSchool plugin. It provides role-based access control, 
 * secure form handling, and dynamic user interface components for managing recurring fees effectively.
 *
 * Key Features:
 * - Displays recurring fees payment lists with sorting, filtering, and responsive tables via DataTables.
 * - Provides checkboxes for bulk actions with "Select All" and individual row sync.
 * - Allows administrators and authorized users to edit or delete recurring fees payments.
 * - Implements WordPress nonces and sanitization for secure data operations.
 * - Provides client-side validation, confirmation dialogs, and reminder alerts using jQuery.
 * - Supports multi-language compatibility and responsive design.
 * - Integrates tooltips and information icons for better user guidance.
 * - Handles dynamic class color assignment for rows for better UI visualization.
 * - Handles conditional display of UI elements based on user access rights.
 * - Supports no-data scenarios with actionable buttons for adding new records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/feespayment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
$retrieve_class_data = $mjschool_obj_feespayment->mjschool_get_all_recurring_fees();

// Check nonce for recurring feespayment list tab.
if ( isset( $_GET['tab'] ) ) {
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_feespayment_tab' ) ) {
       wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
    }
}
if ( ! empty( $retrieve_class_data ) ) {
	?>
    <div class="mjschool-panel-body">
        <div class="table-responsive">
            <form id="mjschool-common-form" name="mjschool-common-form" method="post">
                <table id="recurring_fees_paymnt_list" class="display" cellspacing="0" width="100%">
                    <thead class="<?php echo esc_attr( mjschool_datatable_header( ) ); ?>">
                        <tr>
                            <th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
                            <th><?php esc_html_e( 'Fees Title', 'mjschool' ); ?></th>
                            <th><?php esc_html_e( 'Recurring Type', 'mjschool' ); ?></th>
                            <th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
                            <th><?php esc_html_e( 'Class Name', 'mjschool' ); ?> </th>
                            <th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
                            <th><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></th>
                            <th><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></th>
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
                                <td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr($retrieved_data->recurring_id); ?>"></td>
                                <td>
                                    <?php
                                    $fees_id   = explode( ',', $retrieved_data->fees_id);
                                    $fees_type = array();
                                    foreach ($fees_id as $id) {
                                        $fees_type[] = mjschool_get_fees_term_name($id);
                                    }
                                    echo esc_html( implode( ' , ', $fees_type ) );
                                    ?>
                                    <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Title', 'mjschool' ); ?>"></i>
                                </td>
                                <td>
                                    <?php
                                    if ($retrieved_data->recurring_type === 'monthly' ) {
                                        esc_html_e( 'Monthly', 'mjschool' );
                                    } elseif ($retrieved_data->recurring_type === 'weekly' ) {
                                        esc_html_e( 'Weekly', 'mjschool' );
                                    } elseif ($retrieved_data->recurring_type === 'quarterly' ) {
                                        esc_html_e( 'Quarterly', 'mjschool' );
                                    } elseif ($retrieved_data->recurring_type === 'half_yearly' ) {
                                        esc_html_e( 'Half- Yearly', 'mjschool' );
                                    } else {
                                        esc_html_e( 'One Time', 'mjschool' );
                                    }
                                    ?>
                                    <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Recurring Type', 'mjschool' ); ?>"></i>
                                </td>
                                <?php
                                $student_id_array = explode( ',', $retrieved_data->student_id);
                                $student_data     = array();
                                foreach ($student_id_array as $student_id) {
                                    $student_data[] = mjschool_student_display_name_with_roll($student_id);
                                }
                                ?>
                                <td><?php echo implode( ',<br>', $student_data); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_attr( implode( ', ', $student_data ) ); ?>"></i></td>
                                <td>
                                    <?php
                                    if ($retrieved_data->class_id === '0' ) {
                                        esc_html_e( 'All Class', 'mjschool' );
                                    } else {
                                        echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_id, $retrieved_data->section_id ) );
                                    }
                                    ?>
                                    <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
                                </td>
                                <td><?php echo esc_html( $retrieved_data->status); ?></td>
                                <td><?php echo esc_html( mjschool_currency_symbol_position_language_wise(number_format($retrieved_data->total_amount, 2, '.', '' ) ) ); ?>
                                    <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i>
                                </td>
                                <td><?php echo esc_html( $retrieved_data->start_year) . ' ' . esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( $retrieved_data->end_year); ?>
                                    <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Date To End Date', 'mjschool' ); ?>"></i>
                                </td>
                                <td class="action">
                                    <div class="mjschool-user-dropdown">
                                        <ul  class="mjschool_ul_style">
                                            <li >
                                                <a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
                                                </a>
                                                <ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
                                                    <?php
                                                    if ($user_access_edit === '1' ) {
                                                    	?>
                                                        <li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
                                                            <a href="<?php echo esc_url('?page=mjschool_fees_payment&tab=addrecurringpayment&action=edit&recurring_fees_id='.rawurlencode( mjschool_encrypt_id($retrieved_data->recurring_id ) ).'&_wpnonce_action='.rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit">
                                                                </i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
															</a>
                                                        </li>
                                                    	<?php
                                                    }
                                                    if ($user_access_delete === '1' ) {
                                                    	?>
                                                        <li class="mjschool-float-left-width-100px">
                                                            <a href="<?php echo esc_url('?page=mjschool_fees_payment&tab=feespaymentlist&action=delete&recurring_fees_id='.rawurlencode( mjschool_encrypt_id($retrieved_data->recurring_id ) ).'&_wpnonce_action='.rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
                                                                <i class="fas fa-trash"></i>
                                                                <?php esc_html_e( 'Delete', 'mjschool' ); ?> 
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
                    if ($user_access_delete === '1' ) {
                    	 ?>
                        <button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_recurring_feelist" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
                        <?php  
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>
	<?php
} elseif ($user_access_add === '1' ) {
	?>
    <div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=addpaymentfee' ) ); ?>">
            
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
        
        <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
        
    </div>
	<?php
}
?>