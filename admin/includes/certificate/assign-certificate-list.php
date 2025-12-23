<?php
/**
 * Admin Certificate Assignment List.
 *
 * This file displays and manages the list of assigned certificates in the admin panel.  
 * It provides functionality for:
 * - Viewing assigned certificates with student and certificate details
 * - Editing or deleting individual certificates
 * - Bulk deletion of selected certificates
 * - Integration with DataTables for responsive sorting, filtering, and pagination
 * - "Select All" functionality for managing multiple records at once
 *
 * Integrated Features:
 * - AJAX-based DataTables for better performance
 * - Role-based access (Add, Edit, Delete controls)
 * - Dynamic UI with responsive actions and confirmation prompts
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/certificate
 * @since      1.0.0
 * @since      2.0.1 Security hardening - Added nonce field for bulk delete
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for exam hall list tab.
if ( isset( $_GET['tab'] ) ) {
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_certificate_tab' ) ) {
       wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
    }
}
$user_access_add    = 1;
$user_access_edit   = 1;
$user_access_delete = 1;
if ($active_tab === 'assign_list' ) {
    $tablename           = 'mjschool_certificate';
    $retrieve_class_data = mjschool_get_all_data($tablename);
    if ( ! empty( $retrieve_class_data ) ) {
		?>
        <div class="mjschool-panel-body">
            <div class="table-responsive">
                <form id="mjschool-common-form" name="mjschool-common-form" method="post">
                    <?php 
                    // SECURITY FIX: Add nonce field for bulk delete
                    wp_nonce_field( 'mjschool_delete_certificate_nonce' ); 
                    ?>
                    <table id="grade_list" class="display" cellspacing="0" width="100%">
                        <thead class="<?php echo esc_attr( mjschool_datatable_header( ) ); ?>">
                            <tr>
                                <th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
                                <th><?php esc_html_e( 'images', 'mjschool' ); ?></th>
                                <th><?php esc_html_e( 'Certificate Name', 'mjschool' ); ?></th>
                                <th><?php esc_html_e( 'Certificate for', 'mjschool' ); ?></th>
                                <th><?php esc_html_e( 'Certificate Date', 'mjschool' ); ?></th>
                                <th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($retrieve_class_data as $retrieved_data) {
                                $color_class_css = mjschool_table_list_background_color( $i );
								?>
								<tr>
									<td class="mjschool-checkbox-width-10px">
										<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr($retrieved_data->id); ?>">
									</td>
									<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
										<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-grade.png"); ?>" class="mjschool-massage-image">
										</p>
									</td>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_certificate&action=view&certificate_type=' . $retrieved_data->certificate_type . '&action1=view&acc=' . mjschool_encrypt_id($retrieved_data->id ) . '&student_id=' . mjschool_encrypt_id($retrieved_data->student_id ) ) ); ?>" class="mjschool-color-black" id="<?php echo esc_attr($data_member_id); ?>">
											<?php echo esc_html( $retrieved_data->certificate_type); ?>
										</a>
									</td>
									<td class="title">
										<label ></label><?php echo esc_html( mjschool_get_display_name($retrieved_data->student_id ) ); ?>
									</td>
									<td>
										<?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->created_at ) ); ?>
									</td>
									<td class="action">
										<div class="mjschool-user-dropdown">
											<ul  class="mjschool_ul_style">
												<li >
													<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
													</a>
													<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
														<li class="mjschool-float-left-width-100px">
															<a href="<?php echo esc_url( 'admin.php?page=mjschool_certificate&tab=assign_certificate&action=view&certificate_type=' . $retrieved_data->certificate_type . '&action1=view&acc=' . mjschool_encrypt_id($retrieved_data->id ) . '&student_id=' . mjschool_encrypt_id($retrieved_data->student_id ) ); ?>" class="mjschool-float-left-width-100px" id="<?php echo esc_attr($retrieved_data->id); ?>">
																<i class="fas fa-eye"></i> <?php esc_html_e( 'View', 'mjschool' ); ?>
															</a>
														</li>
														<?php
														if ($user_access_edit === '1' ) {
															?>
															<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_certificate&action=edit&acc=' . rawurlencode( mjschool_encrypt_id($retrieved_data->id ) ) . '&certificate_type=' . $retrieved_data->certificate_type . '&student_id=' . rawurlencode( mjschool_encrypt_id($retrieved_data->student_id ) ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px">
																	<i class="fas fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
																</a>
															</li>
															<?php
														}
														if ($user_access_delete === '1' ) {
															?>
															<li class="mjschool-float-left-width-100px">
																<a href="#" class="mjschool-float-left-width-100px delete_letter mjschool_orange_color" tab="latters" acc="<?php echo esc_attr($retrieved_data->id); ?>">
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
                            <input type="checkbox" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_none" value="<?php echo esc_attr($retrieved_data->ID); ?>">
                            <label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
                        </button>
                        <?php
                        if ($user_access_delete === '1' ) {
                        	?>
                            <button data-toggle="tooltip" id="delete_selected" title="<?php echo esc_attr( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected">
                                <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>">
                            </button>
                        	<?php
                        }
                        ?>
                    </div>
                    <?php if ($user_access_delete == '1' ) { ?>
                        <div class="mjschool-print-button pull-left">
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    <?php
    } else {
        
        if ($user_access_add === '1' ) {
		    ?>
            <div class="mjschool-no-data-list-div mjschool_margin_top_40px">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_certificate&action=new' ) ); ?>">
                    <img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
                </a>
                <div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
                    <label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?>
                    </label>
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
}
?>