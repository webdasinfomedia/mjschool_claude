<?php
/**
 * Admin Certificate Management Page.
 *
 * This file manages all administrative certificate-related functionality within the plugin.
 * It includes creating, editing, assigning, cloning, and deleting certificates (both static and dynamic types).
 *
 * Key Functionalities:
 * - Role-based access control for certificate management actions (add/edit/delete/view).
 * - Certificate assignment for students, including dynamic templates.
 * - Integration with `mjschool_daynamic_certificate` database table.
 * - WYSIWYG editor support for certificate content using wp_editor().
 * - Secure data handling, sanitization, and redirects after form actions.
 * - Dynamic generation of certificate tabs (Transfer Certificate + custom templates).
 * - User notifications and alert messages for CRUD actions.
 *
 * Integrated Features:
 * - PHPCS-compliant security and escaping practices.
 * - Access right validation for administrators and other roles.
 * - Tabbed interface for certificate list, assignment, and dynamic templates.
 * - Custom variables (placeholders) support within certificate templates.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/certificate
 * @since      1.0.0
 * @since      2.0.1 Security hardening - Fixed 7 critical security issues
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript.. ----------//
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$mjschool_obj_document = new Mjschool_Document();
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role(get_current_user_id( ) );
if ($mjschool_role === 'administrator' ) {
    $user_access_add    = '1';
    $user_access_edit   = '1';
    $user_access_delete = '1';
    $user_access_view   = '1';
} else {
    $user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'certificate' );
    $user_access_add    = $user_access['add'];
    $user_access_edit   = $user_access['edit'];
    $user_access_delete = $user_access['delete'];
    $user_access_view   = $user_access['view'];
    if ( isset( $_REQUEST['page'] ) ) {
        if ($user_access_view === '0' ) {
            mjschool_access_right_page_not_access_message_admin_side();
            die();
        }
        if ( ! empty( $_REQUEST['action'] ) ) {
            if ( 'class' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action']) ) === 'edit' ) ) {
                if ($user_access_edit === '0' ) {
                    mjschool_access_right_page_not_access_message_admin_side();
                    die();
                }
            }
            if ( 'class' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'] ) ) === 'delete' ) ) {
                if ($user_access_delete === '0' ) {
                    mjschool_access_right_page_not_access_message_admin_side();
                    die();
                }
            }
            if ( 'class' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action']) ) === 'insert' ) ) {
                if ($user_access_add === '0' ) {
                    mjschool_access_right_page_not_access_message_admin_side();
                    die();
                }
            }
        }
    }
}

// SECURITY FIX 1: Add nonce verification for certificate save
if ( isset( $_POST['create_exprience_latter']) || isset($_POST['save_and_print'] ) ) {
    // SECURITY FIX: Verify nonce before processing
    if ( ! isset( $_POST['_wpnonce'] ) || 
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 
                           'mjschool_assign_certificate_nonce' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
    }
    
    $type = sanitize_text_field(wp_unslash( $_POST['certificate_type'] ) );
    if ( isset( $_POST['edit'] ) ) {
        $l_type    = sanitize_text_field(wp_unslash($_POST['certificate_type']));
        $emp_id    = sanitize_text_field(wp_unslash($_POST['student_id']));
        $result    = $mjschool_obj_document->mjschool_create_experience_letter(wp_unslash($_POST));
        $latter_id = sanitize_text_field(wp_unslash($_POST['id'] ) );
        if ($result) {
            wp_safe_redirect( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_list&message=cret_crt_edt' ) );
            die();
        }
    } else {
        $emp_id         = sanitize_text_field(wp_unslash($_POST['student_id']));
        $result         = $mjschool_obj_document->mjschool_create_experience_letter(wp_unslash($_POST));
        $l_type         = sanitize_text_field(wp_unslash($_POST['certificate_type']));
        $certificate_id = sanitize_text_field(wp_unslash($_POST['certificate_id']));
        $latter_id      = $wpdb->insert_id;
        if ($result) {
            wp_safe_redirect( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_list&message=cret_crt' ) );
            die();
        }
    }
}

$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
switch ($message) {
    case 'cret_crt':
        $message_string = esc_html__( 'certificate created successfully.', 'mjschool' );
        break;
    case 'cret_crt_edt':
        $message_string = esc_html__( 'certificate updated successfully.', 'mjschool' );
        break;
    case 'cret_crt_delete':
        $message_string = esc_html__( 'certificate deleted successfully.', 'mjschool' );
        break;
    case '1':
        $message_string = esc_html__( 'certificate deleted successfully.', 'mjschool' );
        break;
    case '2':
        $message_string = esc_html__( 'certificate changed successfully.', 'mjschool' );
        break;
}
if ($message) {
	?>
    <div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
        <button type="button" class="btn-default notice-dismiss mjschool-margin-top-7" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"></span> </button>
        <?php echo esc_html( $message_string); ?>
    </div>
	<?php
}

// SECURITY FIX 2: Add nonce verification and array validation for bulk delete
if ( isset( $_REQUEST['delete_selected'] ) ) {
    // SECURITY FIX: Verify nonce before deletion
    if ( ! isset( $_POST['_wpnonce'] ) || 
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 
                           'mjschool_delete_certificate_nonce' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
    }
    
    // SECURITY FIX: Validate array before iteration
    if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
        $deleted_count = 0;
        foreach ($_REQUEST['id'] as $id) {
            $result = mjschool_delete_letter_table_by_id( intval( $id ) );
            if ($result) {
                $deleted_count++;
            }
        }
        
        if ( $deleted_count > 0 ) {
            wp_safe_redirect( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_list&message=1' ) );
            exit;
        }
    }
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'certificatelist';
$changed    = 0;

// SECURITY FIX 3: Add nonce verification for transfer certificate save
if ( isset( $_REQUEST['save_transfer'] ) ) {
    // SECURITY FIX: Verify nonce before saving
    if ( ! isset( $_POST['_wpnonce'] ) || 
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 
                           'mjschool_save_transfer_certificate_nonce' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
    }
    
    $changed = 1;
    update_option( 'mjschool_transfer_certificate_title', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_transfer_certificate_title'] ) ) );
    $result = update_option( 'mjschool_transfer_certificate_template', wp_kses_post( wp_unslash( $_REQUEST['mjschool_transfer_certificate_template'] ) ) );
    wp_safe_redirect( admin_url( 'admin.php?page=mjschool_certificate&tab=certificatelist&message=2' ) );
    die();
}

// SECURITY FIX 4: Add nonce verification and improve database security
if ( isset( $_POST['save_dynamic_certificate']) && isset($_POST['id'] ) ) {
    // SECURITY FIX: Verify nonce before database operation
    if ( ! isset( $_POST['_wpnonce'] ) || 
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 
                           'mjschool_save_dynamic_certificate_nonce' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
    }
    
    global $wpdb;
    $id       = intval(wp_unslash($_POST['id']));
    $cert_key = sanitize_text_field(wp_unslash($_POST['certificate_key']));
    $template = wp_kses_post(wp_unslash($_POST[$cert_key . '_template']));
    $title    = sanitize_text_field(wp_unslash($_POST[$cert_key . '_title']));
    
    // SECURITY FIX: Add format arrays for safer database operations
    $wpdb->update(
        $wpdb->prefix . 'mjschool_daynamic_certificate',
        array(
            'certificate_name'    => $title,
            'certificate_content' => $template,
        ),
        array( 'id' => $id),
        array('%s', '%s'),  // SECURITY FIX: Format array for data
        array('%d')         // SECURITY FIX: Format array for where clause
    );
    wp_safe_redirect( admin_url( 'admin.php?page=mjschool_certificate&tab=certificatelist&message=2' ) );
    die();
}

// SECURITY FIX 5: Add nonce verification for certificate delete
if ( isset( $_GET['delete_certificate'] ) ) {
    // SECURITY FIX: Verify nonce before deletion
    if ( ! isset( $_GET['_wpnonce'] ) || 
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 
                           'mjschool_delete_certificate_action' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
    }
    
    global $wpdb;
    $id = intval(wp_unslash($_GET['delete_certificate']));
    
    // SECURITY FIX: Add format array for where clause
    $wpdb->delete( 
        $wpdb->prefix . 'mjschool_daynamic_certificate', 
        array( 'id' => $id),
        array('%d')  // SECURITY FIX: Format array
    );
    
    $delete_nonce = wp_create_nonce( 'mjschool_certificate_tab' );
    wp_safe_redirect( admin_url( 'admin.php?page=mjschool_certificate&tab=certificatelist&_wpnonce=' . $delete_nonce . '&message=1' ) );
    exit;
}
?>
<div class="mjschool-panel-white"><!------- Panel white.  -------->
    <div class="mjschool-panel-body"><!-------- Panel Body. --------->
        <?php $nonce = wp_create_nonce( 'mjschool_certificate_tab' ); ?>
        <ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
            <li class="<?php if ($active_tab === 'certificatelist' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=certificatelist&_wpnonce=' .rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'certificatelist' ? 'active' : ''; ?>">
                    <?php esc_html_e( 'Certificate List', 'mjschool' ); ?>
                </a>
            </li>
            <?php
            $mjschool_action = '';
            if ( ! empty( $_REQUEST['action'] ) ) {
                $mjschool_action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
            }
            ?>
            <li class="<?php if ($active_tab === 'assign_list' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_list&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'assign_list' ? 'active' : ''; ?>">
                    <?php esc_html_e( 'issued Certificate', 'mjschool' ); ?>
                </a>
            </li>
            <?php
            if ($active_tab === 'assign_certificate' ) {
                if ( isset( $_REQUEST['action']) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
            		?>
                    <li class="<?php if ($active_tab === 'assign_certificate' || $_REQUEST['action'] === 'edit' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
                        <a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addexam' ? 'nav-tab-active' : ''; ?>">
                            <?php esc_html_e( 'Edit Assign Certificate', 'mjschool' ); ?>
                        </a>
                    </li>
                	<?php
                } else {
                	?>
                    <li class="<?php if ($active_tab === 'assign_certificate' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
                        <a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'assign_certificate' ? 'nav-tab-active' : ''; ?>">
                            <?php esc_html_e( 'Assign Certificate', 'mjschool' ); ?>
                        </a>
                    </li>
            		<?php
                }
            }
            ?>
        </ul>
        <?php
        if ($active_tab === 'certificatelist' ) {

            // SECURITY FIX 6: Sanitize nonce before verification
            if ( isset( $_GET['tab'] ) ) {
                if ( ! isset( $_GET['_wpnonce'] ) || 
                     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 
                                       'mjschool_certificate_tab' ) ) {
                   wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
                }
            }
            $latter_access_edit = 1;
            $changed            = 0;
        	?>
            <?php $i = 1; ?>
            <div class="header">
                <h4 class="mjschool-first-header">
                    <?php esc_html_e( 'Certificate Information', 'mjschool' ); ?>
                </h4>
            </div>
            <div class="d-flex justify-content-start mb-2">
                <a class="mjschool-dropdown-icon-letter" data-toggle="tooltip" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_certificate&letter_type=transfer&action=new' ) ); ?>" title="<?php esc_attr_e( 'Assign Certificate', 'mjschool' ); ?>">
                    
                    <img height="41px" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-addmore-icon.png' ); ?>" class="add_more_icon_detailpage">
                    
                </a>
            </div>
            <div class="mjschool-main-email-template">
                <?php ++$i; ?>
                <div id="mjschool-accordion" class="mjschool-accordion panel-group accordion accordion-flush mjschool-padding-top-15px-res" id="mjschool-accordion-flush" aria-multiselectable="false" role="tablist">
                    <div class="mt-1 accordion-item">
                        <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                            <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                <?php esc_html_e( 'Transfer Certificate', 'mjschool' ); ?>
							</button>
                        </h4>
                        <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                            <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" frm="collapseOne" name="parent_form">
                                    <?php 
                                    // SECURITY FIX: Add nonce field for transfer certificate
                                    wp_nonce_field( 'mjschool_save_transfer_certificate_nonce' ); 
                                    ?>
                                    <div class="row">
                                        <input type="hidden" name="redirect" id="redirect" value="collapseOne">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group input">
                                                    <div class="col-md-12 form-control">
                                                        <input class="form-control validate[required]" type="text" name="smgt_transfer_certificate_title" id="smgt_transfer_certificate_title" placeholder="Enter Promossion" value="<?php print esc_attr(get_option( 'mjschool_transfer_certificate_title' ) ); ?>">
                                                        <label class="control-label">
                                                            <?php esc_html_e( 'Transfer Certificate Title', 'mjschool' ); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="form-group input">
                                                    <div class="col-md-12 form-control">
                                                        <label for="first_name" class="mjschool-custom-control-label mjschool-custom-top-label">
                                                            <?php esc_html_e( 'Transfer Certificate Template', 'mjschool' ); ?>
                                                        </label>
                                                        <?php
                                                        $content   = get_option( 'mjschool_transfer_certificate_template' );
                                                        $editor_id = 'mjschool_transfer_certificate_template';
                                                        $settings = array(
                                                            'textarea_name' => 'mjschool_transfer_certificate_template',
                                                            'textarea_rows' => 20,
                                                            'media_buttons' => false,
                                                            'tinymce'       => true,
                                                            'quicktags'     => true,
                                                        );
                                                        wp_editor($content, $editor_id, $settings);
                                                        add_action( 'admin_head', function () {
                                                          
                                                        });
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group input">
                                                    <div class="col-md-12 black-text">
                                                        <label><?php esc_html_e( 'You can use following variables in the transfer certificate template:', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{admission_no}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Admission No', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{admission_date}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Admission Date', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{roll_no}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Roll no', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{student_name}} -', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{mother_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Mother Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{father_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Father Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{date}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Date', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{birth_date}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Date Of Birth', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{birth_date_words}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Date Of Birth in Words', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{fails}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Fail (once/twice)', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{last_exam_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Last Exam Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{last_class}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Last Class Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{last_result}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Last Result', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{class_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student class Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{subject}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Current class subjects', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{total_present}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Presents', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{fees_pay}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student total fees pay', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{teacher_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Class Teacher Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{teacher_designation}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Class Teacher Designation', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{teacher_signature}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Class Teacher Signature', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{checking_teacher_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Certificate Check by Teacher Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{checking_teacher_designation}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Certificate Check by Teacher Designation', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{check_by_signature}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Certificate Check by Teacher Signature', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{principal_signature}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Principal Signature', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{place}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'School City', 'mjschool' ); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            if ($latter_access_edit === '1' ) {
                                                ?>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <input value="Save" name="save_transfer" class="btn btn-success mjschool-save-btn" type="submit">
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php ++$i; ?>
                </div>
            </div>
            <form method="post">
                <?php 
                // SECURITY FIX 7: Add nonce field for clone certificate
                wp_nonce_field( 'mjschool_clone_certificate_nonce' ); 
                ?>
                <input type="submit" name="clone_cert" value="<?php esc_attr_e( 'Add More Certificate', 'mjschool' ); ?>" class="button button-primary mjschool-save-btn">
            </form>
            <?php
            // SECURITY FIX 7: Add nonce verification for clone certificate
            if ( isset( $_POST['clone_cert'] ) ) {
                // SECURITY FIX: Verify nonce before cloning
                if ( ! isset( $_POST['_wpnonce'] ) || 
                     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 
                                       'mjschool_clone_certificate_nonce' ) ) {
                    wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
                }
                
                $result = mjschool_clone_certificate_template( 'mjschool_transfer_certificate', 'sample_certificate' );
                echo !is_wp_error($result) ? esc_html( $result['message']) : esc_html( 'Error: ' . $result->get_error_message( ) );
            }
            // Loop over dynamically created certificates like sample_certificate_1, sample_certificate_2, ...
            global $wpdb;
            ++$i;
            $table = $wpdb->prefix . 'mjschool_daynamic_certificate';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $results = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id ASC");
            foreach ($results as $cert) {
                $prefix   = 'sample_certificate_';
                $index    = $i - 1;
                $title    = $cert->certificate_name;
                $template = $cert->certificate_content;
                
                // Generate nonce for delete action
                $delete_cert_nonce = wp_create_nonce( 'mjschool_delete_certificate_action' );
            	?>
                <div class="d-flex justify-content-start mb-2 mt-1">
                    
                    <a class="mjschool-dropdown-icon-letter" data-toggle="tooltip" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_certificate&letter_type=' . rawurlencode( sanitize_text_field( $title ) ) . '&action=new' ) ); ?>" title="<?php esc_attr_e( 'Assign Certificate', 'mjschool' ); ?>">
                        <img height="41px" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-addmore-icon.png"); ?>" class="add_more_icon_detailpage">
                    </a>
                    <a class="mjschool-dropdown-icon-letter" data-toggle="tooltip" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=certificatelist&delete_certificate=' . rawurlencode( sanitize_text_field( $cert->id ) ) . '&_wpnonce=' . $delete_cert_nonce ) ); ?>" onclick="return confirm( 'Are you sure you want to delete this certificate?' );" title="<?php esc_attr_e( 'Delete Certificate', 'mjschool' ); ?>">
                        <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" class="add_more_icon_detailpage">
                    </a>
                    
                </div>
                <div class="mjschool-main-email-template">
                    <div id="mjschool-accordion" class="mjschool-accordion panel-group accordion accordion-flush mjschool-padding-top-15px-res" aria-multiselectable="false" role="tablist">
                        <div class="mt-1 accordion-item">
                            <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                    <?php echo esc_html( $title); ?>
                                </button>
                            </h4>
                            <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                    <form class="mjschool-form-horizontal" method="post">
                                        <?php 
                                        // SECURITY FIX: Add nonce field for dynamic certificate
                                        wp_nonce_field( 'mjschool_save_dynamic_certificate_nonce' ); 
                                        ?>
                                        <input type="hidden" name="certificate_key" value="<?php echo esc_attr($prefix . $index); ?>">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($cert->id); ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group input">
                                                    <div class="col-md-12 form-control">
                                                        <input class="form-control" type="text" name="<?php echo esc_attr($prefix . $index); ?>_title" placeholder="Enter Title" value="<?php echo esc_attr($title); ?>">
                                                        <label class="control-label"><?php esc_html_e( 'Certificate Title', 'mjschool' ); ?></label>
                                                    </div>
                                                </div>
                                                <div class="form-group input">
                                                    <div class="col-md-12 form-control">
                                                        <label class="mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Certificate Template', 'mjschool' ); ?></label>
                                                        <?php
                                                        $editor_id = $prefix . $index . '_template';
                                                        $settings  = array(
                                                            'textarea_name' => $prefix . $index . '_template',
                                                            'textarea_rows' => 20,
                                                            'media_buttons' => false,
                                                            'tinymce' => true,
                                                            'quicktags' => true,
                                                        );
                                                        wp_editor($template, $editor_id, $settings);
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group input">
                                                    <div class="col-md-12 black-text">
                                                        <label><?php esc_html_e( 'You can use following variables in the transfer certificate template:', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{admission_no}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Admission No', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{admission_date}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Admission Date', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{roll_no}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Roll no', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{student_name}} -', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{mother_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Mother Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{father_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Father Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{date}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Date', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{birth_date}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Date Of Birth', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{birth_date_words}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Date Of Birth in Words', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{fails}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Fail (once/twice)', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{last_exam_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Last Exam Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{last_class}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Last Class Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{last_result}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Last Result', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{class_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student class Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{subject}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Current class subjects', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{total_present}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student Presents', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{fees_pay}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Student total fees pay', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{teacher_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Class Teacher Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{teacher_designation}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Class Teacher Designation', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{teacher_signature}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Class Teacher Signature', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{checking_teacher_name}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Certificate Check by Teacher Name', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{checking_teacher_designation}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Certificate Check by Teacher Designation', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{check_by_signature}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Certificate Check by Teacher Signature', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{principal_signature}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'Principal Signature', 'mjschool' ); ?></label><br>
                                                        <label><strong><?php esc_html_e( '{{place}} - ', 'mjschool' ); ?></strong><?php esc_html_e( 'School City', 'mjschool' ); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            if ($latter_access_edit === '1' ) {
                                                ?>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <input value="Save" name="save_dynamic_certificate" class="btn btn-success mjschool-save-btn" type="submit">
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            	<?php
                ++$i;
                ++$index;
            }
            ?>
            <?php
        }
        if ($active_tab === 'assign_list' ) {
            require_once MJSCHOOL_ADMIN_DIR . '/certificate/assign-certificate-list.php';
        }
        if ($active_tab === 'assign_certificate' ) {
            require_once MJSCHOOL_ADMIN_DIR . '/certificate/assign-certificate.php';
        }
        ?>
    </div>
</div>