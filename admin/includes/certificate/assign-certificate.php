<?php
/**
 * Admin Certificate Assignment Form
 *
 * This file provides the admin interface for assigning certificates to students.  
 * It includes the following key functionalities:
 * - Selecting a student and related class details  
 * - Choosing certificate type (static or dynamic from database)  
 * - Assigning class teacher and "checked by" teacher  
 * - Generating certificates dynamically (e.g., Transfer Certificates)  
 * - Integration with Select2 and jQuery ValidationEngine for enhanced UX  
 * - Secure handling of form data and database interactions
 *
 * Integrated Features:
 * - Dynamic certificate loading from `mjschool_daynamic_certificate` table
 * - Data sanitization and validation for all inputs
 * - Select2-based searchable dropdowns
 * - Responsive form layout using Mjschool CSS utilities
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/certificate
 * @since      1.0.0
 * @since      2.0.1 Security hardening - Added nonce field for CSRF protection
 */
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
if ($active_tab === 'assign_certificate' ) {
    // This block is currently empty.
}
$students = mjschool_get_student_group_by_class();
$edit     = 0;
if ( isset( $_REQUEST['action']) && (sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' || sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'view' ) ) {
    $edit       = 1;
    $student_id = intval(mjschool_decrypt_id(sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ) );
    $ids        = intval(mjschool_decrypt_id(sanitize_text_field( wp_unslash( $_REQUEST['acc'] ) ) ) );
    $post       = get_post($ids);
    mjschool_create_transfer_letter();
}

$results = mjschool_get_certificate_id_and_name();
// Get previously selected value (if any).
$selected_cert = '';
if ( isset( $_POST['certificate_type'] ) ) {
    $selected_cert = sanitize_text_field(wp_unslash($_POST['certificate_type']));
} elseif ( isset( $_GET['letter_type'] ) ) {
    $selected_cert = sanitize_text_field(wp_unslash($_GET['letter_type']));
}
$selected_student  = isset($_POST['student_id']) ? sanitize_text_field(wp_unslash($_POST['student_id'])) : '';
$selected_teacher  = isset($_POST['teacher_id']) ? sanitize_text_field(wp_unslash($_POST['teacher_id'])) : '';
$selected_teacher2 = isset($_POST['teacher_new_id']) ? sanitize_text_field(wp_unslash($_POST['teacher_new_id'])) : '';
?>
<form name="certificate" action="" method="post" class="mjschool-form-horizontal" id="certificate" enctype="multipart/form-data">
    <?php 
    // SECURITY FIX: Add nonce field for CSRF protection
    wp_nonce_field( 'mjschool_assign_certificate_nonce' ); 
    ?>
    <input type="hidden" name="certificate_id" id="certificate_id" value="">
    <div class="form-body mjschool-user-form">
        <div class="row">
            <div class="header">
                <h4 class="mjschool-first-header"><?php esc_html_e( 'Certificate Information', 'mjschool' ); ?></h4>
            </div>
            <div class="col-md-3 input mjschool-single-select">
                <select class="form-control add-search-single-select-js validate[required] display-members max_mjschool-width-70px0" name="student_id">
                    <option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
                    <?php
                    if ($edit) {
                        $student = $result->student_id;
                    } elseif ( ! empty( $selected_student ) ) {
                        $student = $selected_student;
                    } elseif ( isset( $_REQUEST['student_id'] ) ) {
                        $student = $student_id;
                    } else {
                        $student = '';
                    }
                    $studentdata = mjschool_get_all_student_list( 'student' );
                    foreach ($students as $label => $opt) {
                    	?>
                        <optgroup label="<?php echo esc_html__( 'Class :', 'mjschool' ) . ' ' . esc_attr($label); ?>">
                            <?php foreach ($opt as $id => $name) : ?>
                                <option value="<?php echo esc_attr($id); ?>" <?php selected($id, $student); ?>>
                                    <?php echo esc_html( $name); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php } ?>
                </select>
                <span class="mjschool-multiselect-label">
                    <label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Student', 'mjschool' ); ?><span class="required">*</span></label>
                </span>
            </div>
            <div class="col-md-3 input">
                <label class="mjschool-custom-top-label mjschool-lable-top top" for="certificate_type"><?php esc_html_e( 'Certificate Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                <select id="certificate_type" name="certificate_type" class="form-control max_mjschool-width-70px0 validate[required] mjschool_45px" >
                    <!-- Static option. -->
                    <option value="transfer_static" <?php selected($selected_cert, 'transfer' ); ?>>
                        <?php esc_html_e( 'Transfer', 'mjschool' ); ?>
                    </option>
                    <!-- Dynamic options from DB. -->
                    <?php foreach ($results as $cert) : ?>
                        <option value="<?php echo esc_attr($cert->certificate_name); ?>" data-id="<?php echo esc_attr($cert->id); ?>" <?php selected($selected_cert, $cert->certificate_name); ?>>
                            <?php echo esc_html( $cert->certificate_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 input mjschool-single-select">
                <label class="ml-1 mjschool-custom-top-label top" for="student_id"><?php esc_html_e( 'Class Teacher', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                <select name="teacher_id" id="teacher_id" class="form-control mjschool-max-width-100px validate[required]">
                    <option value=""><?php esc_html_e( 'Select class Teacher', 'mjschool' ); ?></option>
                    <?php mjschool_get_teacher_list_selected($selected_teacher); ?>
                </select>
            </div>
            <div class="col-md-3 input mjschool-single-select">
                <label class="ml-1 mjschool-custom-top-label top" for="student_id"><?php esc_html_e( 'Checked By', 'mjschool' ); ?></label>
                <select name="teacher_new_id" id="teacher_new_id" class="form-control mjschool-max-width-100px validate[required]">
                    <option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></option>
                    <?php mjschool_get_teacher_list_selected($selected_teacher2 ); ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="submit" value="<?php if ($edit) { esc_attr_e( 'GO', 'mjschool' ); } else { esc_attr_e( 'GO', 'mjschool' ); } ?>" name="save_latter" class="btn btn-success mjschool-save-btn" />
            </div>
        </div>
    </div>
</form>
<div class="latter_content">
    <?php
    if ( isset( $_POST['save_latter'] ) ) {
        // Get the selected letter type.
        $certificate_type = isset($_REQUEST['certificate_type']) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_type'] ) ) : '';       
        if ($certificate_type) {
            mjschool_create_transfer_letter();
        } else {
            echo 'No letter type selected.';
        }
    }
    ?>
</div>