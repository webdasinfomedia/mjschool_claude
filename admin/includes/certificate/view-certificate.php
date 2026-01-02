<?php
/**
 * View Certificate Page.
 *
 * This file handles the display and editing of certificates for students.
 * It includes the following key functionalities:
 * - Retrieving certificate data from the database.
 * - Displaying certificate templates with student information.
 * - Handling certificate editing, viewing, and printing.
 * - Replacing dynamic variables in certificate templates with actual student data.
 * - Access control for different user roles (administrator, management, teacher).
 * - Integration with attendance, marks, and exam data.
 * - PDF generation and download support.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/certificate
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Check if user is logged in.
if ( ! is_user_logged_in() ) {
    wp_die( esc_html__( 'You must be logged in.', 'mjschool' ) );
}

global $wpdb;
$obj_attend = new Mjschool_Attendence_Manage();
$obj_marks  = new Mjschool_Marks_Manage();
$obj_exam   = new Mjschool_exam();

// Get sanitized request parameters.
$certificate_id = isset($_REQUEST['certificate_id']) ? intval(wp_unslash($_REQUEST['certificate_id'])) : 0;
$curr_role      = mjschool_get_user_role( get_current_user_id() );
$action_view    = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'N/A';

if ( isset( $_REQUEST['_wpnonce'] ) ) {
    if ( ! wp_verify_nonce( sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'view_action' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
    }
}

if ( ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' || sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'view' ) && isset( $_REQUEST['acc'] ) ) {
    $id         = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['acc'])) ) );
    $student_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) ) );
} elseif ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'new' ) {
    $student_id     = intval( wp_unslash($_REQUEST['student_id'] ));
    $teacher_id     = intval( wp_unslash($_REQUEST['teacher_id'] ));
    $teacher_new_id = intval( wp_unslash($_REQUEST['teacher_new_id'] ));
}

if ( $curr_role === 'administrator' || $curr_role === 'management' ) {
    $action_url = admin_url( 'admin.php?page=mjschool_certificate&tab=assign_list' );
} else {
    $action_url = home_url( '?dashboard=mjschool_user&page=certificate&tab=assign_list' );
}

$letter_type = isset($_REQUEST['certificate_type']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_type'])) : '';
$c_id        = isset( $_REQUEST['acc'] ) ? intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['acc'])) ) ) : 0;
$data        = get_userdata( $student_id );
$teacher_id     = isset($_REQUEST['teacher_id']) ? absint(wp_unslash($_REQUEST['teacher_id'])) : 0;
$teacher_new_id = isset($_REQUEST['teacher_new_id']) ? absint(wp_unslash($_REQUEST['teacher_new_id'])) : 0;
$data2     = get_userdata($teacher_id);
$data3     = get_userdata($teacher_new_id);
$metadata  = get_user_meta($student_id);
$metadata1 = get_user_meta($teacher_id);
$metadata2 = get_user_meta($teacher_new_id);
$mjschool_user = new Mjschool_User();
$parentdata = $mjschool_user->mjschool_get_users_data( 'parent' );
$parent_ids = array();

if ( isset( $metadata['parent_id'][0] ) ) {
    $parent_ids_raw = $metadata['parent_id'][0];
    $parent_ids     = is_serialized( $parent_ids_raw ) ? unserialize( $parent_ids_raw ) : array();
    if ( ! is_array( $parent_ids ) ) {
        $parent_ids = array();
    }
}

$mother = '';
$father = '';

if ( isset( $metadata['parent_id'][0] ) ) {
    $parent_ids = unserialize( $metadata['parent_id'][0] );
    $parentdata = $mjschool_user->mjschool_get_users_data( 'parent' );
    foreach ( $parentdata as $parent ) {
        if ( in_array( $parent->ID, $parent_ids, true ) ) {
            $relation = get_user_meta( $parent->ID, 'relation', true );
            $name     = $parent->data->display_name;
            if ( strtolower( $relation ) === 'mother' ) {
                $mother = $name;
            } elseif ( strtolower( $relation ) === 'father' ) {
                $father = $name;
            }
        }
    }
}

$designation_id     = isset( $metadata1['designation'][0] ) ? $metadata1['designation'][0] : '';
$designation_new_id = isset( $metadata2['designation'][0] ) ? $metadata2['designation'][0] : '';
$designation_name   = '';
$designation_check_name = '';

if ( ! empty( $designation_id ) ) {
    $designation_post = get_post( intval( $designation_id ) );
    if ( $designation_post && $designation_post->post_type === 'designation' ) {
        $designation_name = $designation_post->post_title;
    }
}

if ( ! empty( $designation_new_id ) ) {
    $designation_post = get_post( intval( $designation_new_id ) );
    if ( $designation_post && $designation_post->post_type === 'designation' ) {
        $designation_check_name = $designation_post->post_title;
    }
}

$signature_path     = isset( $metadata1['signature'][0] ) ? sanitize_file_name( $metadata1['signature'][0] ) : '';
$signature_url      = $signature_path ? content_url( $signature_path ) : '';
$signature_path_new = isset( $metadata2['signature'][0] ) ? sanitize_file_name( $metadata2['signature'][0] ) : '';
$signature_url_new  = $signature_path_new ? content_url( $signature_path_new ) : '';

if ( $curr_role === 'administrator' ) {
    $user_access_add    = '1';
    $user_access_edit   = '1';
    $user_access_delete = '1';
    $user_access_view   = '1';
} else {
    $user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'teacher' );
    $user_access_add    = $user_access['add'];
    $user_access_edit   = $user_access['edit'];
    $user_access_delete = $user_access['delete'];
    $user_access_view   = $user_access['view'];
}

if ( $action_view === 'new' ) {
    $has_error = false;
    // Prepare role.
    $is_admin_or_management = ( $curr_role === 'administrator' || $curr_role === 'management' );
    
    // Class Teacher Signature.
    if ( empty( $signature_path ) ) {
        echo '<div class="alert alert-danger d-flex justify-content-between align-items-center mt-2">';
        echo '<span>' . esc_html__( 'Class Teacher signature is not added. Please upload the signature.', 'mjschool' ) . '</span>';
        if ( $user_access_edit === '1' && $teacher_id > 0 ) {
            $teacher_id_encrypted = mjschool_encrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) );
            $nonce                = mjschool_get_nonce( 'edit_action' );
            $edit_url             = $is_admin_or_management
                ? admin_url( "admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id={$teacher_id_encrypted}&_wpnonce={$nonce}" )
                : home_url( "?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id={$teacher_id_encrypted}&_wpnonce_action={$nonce}" );
            echo '<a href="' . esc_url( $edit_url ) . '" class="btn btn-warning btn-sm mjchool_margin_left_auto" target="_blank" >' . esc_html__( 'Edit Teacher', 'mjschool' ) . '</a>';
        }
        echo '</div>';
        $has_error = true;
    }
    
    // Checked By Teacher Signature.
    if ( empty( $signature_path_new ) ) {
        echo '<div class="alert alert-danger d-flex justify-content-between align-items-center mt-2">';
        echo '<span>' . esc_html__( 'Checked By (Teacher) signature is not added. Please upload the signature.', 'mjschool' ) . '</span>';
        if ( $user_access_edit === '1' && $teacher_new_id > 0 ) {
            $teacher_new_id_encrypted = mjschool_encrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_new_id'])) );
            $nonce                    = mjschool_get_nonce( 'edit_action' );
            $edit_url                 = $is_admin_or_management
                ? admin_url( "admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id={$teacher_new_id_encrypted}&_wpnonce={$nonce}" )
                : home_url( "?dashboard=mjschool_user&page=teacher&tab=addteacher&action=edit&teacher_id={$teacher_new_id_encrypted}&_wpnonce_action={$nonce}" );
            echo '<a href="' . esc_url( $edit_url ) . '" class="btn btn-warning btn-sm mjchool_margin_left_auto" target="_blank" >' . esc_html__( 'Edit Teacher', 'mjschool' ) . '</a>';
        }
        echo '</div>';
        $has_error = true;
    }
    
    if ( $has_error ) {
        return; // Stop further execution.
    }
}

// Build replacement array for certificate template variables.
$arr                                     = array();
$arr['{{mother_name}}']                  = $mother;
$arr['{{father_name}}']                  = $father;
$arr['{{teacher_designation}}']          = $designation_name;
$arr['{{checking_teacher_designation}}'] = $designation_check_name;
$arr['{{check_by_signature}}']           = $signature_url_new;
$arr['{{teacher_signature}}']            = $signature_url;
$arr['{{place}}']                        = get_option( 'mjschool_city' );
$arr['{{date}}']                         = wp_date( 'Y-m-d' );
$roll_no                                 = isset( $metadata['roll_id'][0] ) ? $metadata['roll_id'][0] : '';
$raw_birth_date                          = isset( $metadata['birth_date'][0] ) ? $metadata['birth_date'][0] : '';
$admission_date                          = isset( $metadata['admission_date'][0] ) ? $metadata['admission_date'][0] : '';
$formatted_birth_date                    = ! empty( $raw_birth_date ) ? wp_date( 'd-m-Y', strtotime( str_replace( '/', '-', $raw_birth_date ) ) ) : '';
$birth_date_in_words                     = ! empty( $raw_birth_date ) ? mjschool_date_in_words( $raw_birth_date ) : '';
$class_name                              = get_user_meta( $student_id, 'class_name', true );
$section_id                              = get_user_meta( $student_id, 'class_section', true );
$classname                               = mjschool_get_class_section_name_wise( $class_name, $section_id );
// Get fails.
$marks          = $obj_marks->mjschool_subject_makrs_by_student_id( $student_id );
$fail_count     = 0;
$last_exam_name = '';
if ( ! empty( $marks ) ) {
    foreach ( $marks as $mark ) {
        $exam_id      = (int) $mark->exam_id;
        $student_mark = (float) $mark->marks;
        // Get passing mark for this exam.
        $passing_mark = $obj_marks->mjschool_get_pass_marks( $exam_id );
        // If student mark is less than passing mark, count as fail.
        if ( $student_mark < (float) $passing_mark ) {
            ++$fail_count;
        }
    }
}
$last_exam_id = '';
$last_result_status = '';
if ( ! empty( $marks ) ) {
    // Sort marks by created_date descending.
    usort(
        $marks,
        function ( $a, $b ) {
            return strtotime( $b->created_date ) - strtotime( $a->created_date );
        }
    );
    foreach ( $marks as $index => $mark ) {
        $exam_id      = (int) $mark->exam_id;
        $student_mark = (float) $mark->marks;
        $passing_mark = (float) $obj_marks->mjschool_get_pass_marks( $exam_id );
        $last_exam_id = $marks[0]->exam_id;    
        if ( $student_mark < $passing_mark ) {
            ++$fail_count;
        }  
        // Only check the first record (latest).
        if ( $index === 0 ) {
            $last_result_status = ( $student_mark < $passing_mark ) ? 'Fail' : 'Pass';
        }
    }
}
$last_exam_names           = $obj_exam->mjschool_exam_name_by_id( $last_exam_id );
$last_exam_name            = isset( $last_exam_names->exam_name ) ? $last_exam_names->exam_name : '';
$arr['{{last_exam_name}}'] = $last_exam_name;
if ( $fail_count === 1 ) {
    $arr['{{fails}}'] = 'once';
} elseif ( $fail_count > 1 ) {
    $arr['{{fails}}'] = 'twice';
} else {
    $arr['{{fails}}'] = '';
}
// Get attendance data.
$presents      = '';
$presents      = $obj_attend->mjschool_get_students( $student_id );
$total_present = 0;
if ( ! empty( $presents ) ) {
    foreach ( $presents as $att ) {
        if ( isset( $att->status ) && strtolower( $att->status ) === 'present' ) {
            ++$total_present;
        }
    }
}

// Get subjects.
$class_id = get_user_meta( $student_id, 'class_name', true );
if ( ! empty( $class_id ) ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $subjects = $wpdb->get_col( $wpdb->prepare( "SELECT sub_name FROM {$wpdb->prefix}mjschool_subject WHERE class_id = %d", intval( $class_id ) ) );
    // Join subject names into a comma-separated string.
    $subject_list = implode( ', ', array_map( 'sanitize_text_field', $subjects ) );
    // Assign to template variable.
    $arr['{{subject}}'] = $subject_list;
} else {
    $arr['{{subject}}'] = '';
}

// Get last class from migration log.
$table_name = $wpdb->prefix . 'mjschool_migration_log';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$logs       = $wpdb->get_results( "SELECT * FROM {$table_name}" );
$last_class = '';

foreach ( $logs as $log ) {
    $pass_students = json_decode( $log->pass_students, true );
    if ( ! empty( $pass_students ) && is_array( $pass_students ) ) {
        foreach ( $pass_students as $student ) {
            if ( isset( $student['student_id'] ) && intval( $student['student_id'] ) === $student_id ) {
                $last_class = $log->current_class; // Found!
                break 2; // exit both loops
            }
        }
    }
}
$last_class_name = mjschool_get_class_section_name_wise( $last_class, $section_id );
// Get total fees paid.
$table_name      = $wpdb->prefix . 'mjschool_fees_payment';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$total_fees_pay = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(fees_paid_amount) FROM {$table_name} WHERE student_id = %d", intval( $student_id ) ) );
$total_fees_pay = $total_fees_pay ? intval( $total_fees_pay ) : 0;
if ( $total_fees_pay > 0 ) {
    $fees = mjschool_get_currency_symbol() . $total_fees_pay;
} else {
    $fees = '';
}
$arr['{{fees_pay}}']              = $fees;
$arr['{{last_class}}']            = $last_class_name;
$arr['{{total_present}}']         = $total_present;
$arr['{{last_result}}']           = $last_result_status;
$arr['{{class_name}}']            = $classname;
$arr['{{birth_date}}']            = $formatted_birth_date;
$arr['{{birth_date_words}}']      = $birth_date_in_words;
$admission_no                     = isset( $metadata['admission_no'][0] ) ? $metadata['admission_no'][0] : '';
$arr['{{admission_no}}']          = $admission_no;
$arr['{{roll_no}}']               = $roll_no;
$arr['{{admission_date}}']        = $admission_date;
$arr['{{principal_signature}}']   = get_option( 'mjschool_principal_signature' );
$arr['{{student_name}}']          = $data ? $data->display_name : '';
$arr['{{teacher_name}}']          = ( $data2 && isset( $data2->display_name ) ) ? $data2->display_name : '';
$arr['{{checking_teacher_name}}'] = ( $data3 && isset( $data3->display_name ) ) ? $data3->display_name : '';

// Get certificate template content.
if ( $letter_type === 'transfer_static' ) {
    // Static certificate.
    $content   = get_option( 'mjschool_transfer_certificate_template' );
    $presentto = get_option( 'mjschool_transfer_certificate_to' );
} elseif ( $action_view === 'edit' || $action_view === 'view' ) {
    // Dynamic certificate from DB.
    $table = $wpdb->prefix . 'mjschool_certificate';
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $cert = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", intval( $c_id ) ) );
    if ( $cert ) {
        $content   = $cert->certificate_content;
        $presentto = '';
    } else {
        wp_send_json_error( array( 'message' => esc_html__( 'Certificate not found.', 'mjschool' ) ) );
        exit;
    }
} else {
    $table = $wpdb->prefix . 'mjschool_daynamic_certificate';
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $cert = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE certificate_name = %s", sanitize_text_field( $letter_type ) ) );
    if ( $cert ) {
        $content   = $cert->certificate_content;
        $presentto = '';
    } else {
        wp_send_json_error( array( 'message' => esc_html__( 'Certificate not found.', 'mjschool' ) ) );
        exit;
    }
}

$replace_content = wpautop( mjschool_string_replacemnet( $arr, $content ) );
$replace_to      = wpautop( mjschool_string_replacemnet( $arr, $presentto ) );
$result          = null;

global $wpdb;
$table_exprience_letter = $wpdb->prefix . 'mjschool_certificate';

if ( $action_view === 'edit' || $action_view === 'view' ) {
    $sql = $wpdb->prepare( "SELECT * FROM {$table_exprience_letter} WHERE id = %d", intval( $id ) );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $result = $wpdb->get_row( $sql );
}
?>
<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
    <h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( get_option( 'mjschool_transfer_certificate_title' ) ); ?></h4>
</div>
<div class="div">
    <div id="invoice-pdf" class="pdf-content-main mjschool-float-left-width-100px">
        <div id="printcontent" class="mjschool_exprience">
            <form action="<?php echo esc_url( $action_url ); ?>" id="exp_letter" name="exp_letter" method="post">
                <?php 
                wp_nonce_field( 'mjschool_view_certificate_nonce' ); 
                ?>
                <input type="hidden" name="student_id" value="<?php echo esc_attr( ( $result && ! empty( $result->student_id ) ) ? intval( $result->student_id ) : intval( $student_id ) ); ?>">
                <input type="hidden" name="certificate_type" value="<?php echo esc_attr( sanitize_text_field( $letter_type ) ); ?>">
                <input type="hidden" name="certificate_id" value="<?php echo esc_attr( intval( $certificate_id ) ); ?>">
                <?php
                if ( $action_view === 'edit' ) {
                    ?>
                    <input type="hidden" name="edit" value="edit">
                    <input type="hidden" name="id" value="<?php echo esc_attr( $result ? intval( $result->id ) : 0 ); ?>">
                    <?php
                }
                ?>
                <div class="col-md-12">
                    <div class="div">
                        <h4><?php echo wp_kses_post( $replace_to ); ?></h4>
                    </div>
                    <?php
                    if ( $action_view === 'view' ) {
                        ?>
                        <p><?php echo wp_kses_post( $result ? $result->certificate_content : '' ); ?></p>
                        <?php
                    } else {
                        ?>
                        <div>
                            <div>
                                <textarea class="form-control textarea experiance_area" id="lett_content" name="lett_content" rows="8" data-readonly="<?php echo esc_attr( $action_view === 'view' ? 'true' : 'false' ); ?>" <?php if ( $action_view === 'view' ) { echo 'readonly'; } ?>>
                                    <?php
                                    if ( $action_view === 'edit' ) {
                                        echo wp_kses_post( $result ? $result->certificate_content : '' );
                                    } else {
                                        echo wp_kses_post( $replace_content );
                                    }
                                    ?>
                                </textarea>
                            </div>
                        </div>
                        <?php } ?>
                </div>
                <div class="col-md-offset-5 col-md-7 mt-2">
                    <h1>
                        <?php
                        if ( $action_view !== 'view' ) {
                            ?>
                            <input type="submit" name="create_exprience_latter" class="btn btn-primary btn-primary-prints" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>">
                            <?php
                        }
                        ?>
                    </h1>
                </div>
                <?php if ( $action_view === 'view' ) { ?>
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px mb-3 mjschool-rtl-margin-bottom-0px">
                        <div class="form-group">
                            <div class="col-md-12 form-control">
                                <div class="row mjschool-padding-radio mjschool-rtl-relative-position">
                                    <div>
                                        <label class="mjschool-custom-top-label mjschool-label-right-position" for="certificate_header"><?php esc_html_e( 'Print Certificate With Header', 'mjschool' ); ?></label>
                                        <input type="checkbox" class="mjschool-check-box-input-margin" id="certificate_header" name="certificate_header" value="1" />
                                        <?php esc_html_e( 'Enable', 'mjschool' ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </form>
        </div>
    </div>
    <?php if ( $action_view === 'view' ) { ?>
        <div class="col-md-offset-5 col-md-7 mt-2">
            <a id="exprience_latter" href="<?php echo esc_url( '?page=mjschool_certificate&print=print&print_certificate_id=' . rawurlencode( sanitize_text_field(wp_unslash($_REQUEST['acc'])) ) ); ?>" class="btn btn-primary btn-primary-prints" target="_blank">
                <?php esc_html_e( 'Print', 'mjschool' ); ?>
            </a>
            <a href="javascript:void(0)" id="download_certificate_pdf" class="btn btn-primary btn-primary-prints">
                <?php esc_html_e( 'Download PDF', 'mjschool' ); ?>
            </a>
        </div>
    <?php } ?>
</div>
<div id="mjschool-transfer-letter-trigger" data-trigger="1"></div>