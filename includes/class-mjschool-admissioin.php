<?php
defined('ABSPATH') || exit;
set_time_limit(300);
/**
 * Handles the student admission process in the MJ School Management plugin.
 *
 * This class manages adding new student admissions, assigning roles,
 * creating parent accounts, and sending notification emails.
 *
 * @package    MjSchool
 * @subpackage MjSchool/includes
 * @since      1.0.0
 */
class Mjschool_admission
{
    /**
     * Add or update a student admission record.
     *
     * This method handles the creation or update of a student's admission.
     * It validates permissions, creates a new WordPress user, saves custom
     * user metadata, and optionally generates admission fee invoices and
     * sends notification emails.
     *
     * @since 1.0.0
     *
     * @param array  $data                 The student admission form data (including personal, contact, and class info).
     * @param array  $father_document_data Uploaded father documents (if any).
     * @param array  $mother_document_data Uploaded mother documents (if any).
     * @param string $role                 Role to assign to the created user (e.g., 'student', 'parent', etc.).
     *
     * @return int|WP_Error The created or updated user ID on success, or WP_Error on failure.
     *
     * @throws WP_Error If nonce verification or permission checks fail.
     */
    public function mjschool_add_admission( $data, $father_document_data, $mother_document_data, $role )
    {
        // Ensure the user is logged in.
        if (! is_user_logged_in() ) {
            wp_die(esc_html('Security check failed! You are not logged in.', 'mjschool'), 'Error', array( 'response' => 403 ));
        }
        if (! isset($data['security']) || ! wp_verify_nonce($data['security'], 'mjschool_nonce') ) {
            wp_die(esc_html('Security check failed! Invalid security token.', 'mjschool'), 'Error', array( 'response' => 403 ));
        }
        // Get current user ID and role.
        $current_user_id = get_current_user_id();
        $current_role    = mjschool_get_user_role($current_user_id);
        $role            = sanitize_text_field($role);
        $allowed_roles   = array( 'administrator', 'management', 'supportstaff', 'teacher' );
        if (! in_array($current_role, $allowed_roles) ) {
            wp_die(esc_html('Permission denied! You do not have the required access.', 'mjschool'), 'Error', array( 'response' => 403 ));
        }
        // Map assignable roles per user role.
        $assignable_roles = array(
        'administrator' => array( 'administrator', 'management', 'supportstaff', 'teacher', 'student', 'student_temp', 'parent' ),
        'management'    => array( 'supportstaff', 'teacher', 'student', 'student_temp', 'parent' ),
        'supportstaff'  => array( 'student', 'student_temp', 'parent' ),
        'teacher'       => array( 'student', 'student_temp', 'parent' ),
        );
        $permitted_roles  = isset($assignable_roles[ $current_role ]) ? $assignable_roles[ $current_role ] : array();
        // Final check: block assigning roles outside current user's permission.
        if (! in_array($role, $permitted_roles) ) {
            wp_die(esc_html('You are not allowed to assign this role.', 'mjschool'), 'Error', array( 'response' => 403 ));
        }
        $firstname  = sanitize_text_field($data['first_name']);
        $middlename = sanitize_text_field($data['middle_name']);
        $lastname   = sanitize_text_field($data['last_name']);
        $userdata   = array(
        'user_login'    => sanitize_email($data['email']),
        'user_nicename' => null,
        'user_email'    => sanitize_email($data['email']),
        'user_url'      => null,
        'display_name'  => $firstname . ' ' . $middlename . ' ' . $lastname,
        );
        if ($data['password'] != '' ) {
            $userdata['user_pass'] = mjschool_password_validation($data['password']);
        } else {
            $userdata['user_pass'] = wp_generate_password();
        }
        if (isset($data['mjschool_user_avatar']) && $data['mjschool_user_avatar'] != '' ) {
            $photo = $data['mjschool_user_avatar'];
        } else {
            $photo = '';
        }
        // Add Sibling details.
        $sibling_value = array();
        if (! empty($data['siblingsclass']) ) {
            foreach ( $data['siblingsclass'] as $key => $value ) {
                $sibling_value[] = array(
                'siblingsclass'   => $value,
                'siblingssection' => $data['siblingssection'][ $key ],
                'siblingsstudent' => $data['siblingsstudent'][ $key ],
                );
            }
        }
        $admission_fees_amount = '';
        if (get_option('mjschool_admission_fees') == 'yes' ) {
            $admission_fees_amount = $data['admission_fees_amount'];
            $admission_fees_id     = $data['admission_fees_id'];
        }
        $parent_status = null;
        if(! empty($data['father_email']) || ! empty($data['mother_email']) ) {
            $parent_status = sanitize_text_field($data['pstatus']);
        }
        // Add user meta.
        // Initialize the base metadata.
        $usermetadata = array(
        'admission_no'           => sanitize_textarea_field($data['admission_no']),
        'admission_date'         => mjschool_get_format_for_db($data['admission_date']),
        'admission_fees'         => $admission_fees_amount,
        'role'                   => sanitize_textarea_field($data['role']),
        'status'                 => sanitize_textarea_field($data['status']),
        'roll_id'                => '',
        'middle_name'            => sanitize_text_field($data['middle_name']),
        'gender'                 => sanitize_text_field($data['gender']),
        'birth_date'             => sanitize_text_field($data['birth_date']),
        'address'                => sanitize_textarea_field($data['address']),
        'city'                   => sanitize_text_field($data['city_name']),
        'state'                  => sanitize_text_field($data['state_name']),
        'zip_code'               => sanitize_text_field($data['zip_code']),
        'preschool_name'         => sanitize_text_field($data['preschool_name']),
        'phone_code'             => sanitize_textarea_field($data['phone_code']),
        'mobile_number'          => sanitize_text_field(wp_unslash($_POST['mobile_number'])),
        'alternet_mobile_number' => sanitize_text_field($data['alternet_mobile_number']),
        'sibling_information'    => json_encode($sibling_value),
        'parent_status'          => $parent_status,
        'mjschool_user_avatar'   => $photo,
        'created_by'             => get_current_user_id(),
        );
        // Initialize dynamic metadata arrays.
        $father_metadata = array();
        $mother_metadata = array();
        // Add father metadata if father_email is not empty.
        if (! empty($data['father_email']) ) {
            $father_metadata = array(
            'fathersalutation'   => sanitize_text_field($data['fathersalutation']),
            'father_first_name'  => sanitize_text_field($data['father_first_name']),
            'father_middle_name' => sanitize_text_field($data['father_middle_name']),
            'father_last_name'   => sanitize_text_field($data['father_last_name']),
            'fathe_gender'       => sanitize_textarea_field($data['fathe_gender']),
            'father_birth_date'  => sanitize_text_field($data['father_birth_date']),
            'father_address'     => sanitize_textarea_field($data['father_address']),
            'father_city_name'   => sanitize_text_field($data['father_city_name']),
            'father_state_name'  => sanitize_text_field($data['father_state_name']),
            'father_zip_code'    => sanitize_text_field($data['father_zip_code']),
            'father_email'       => sanitize_email($data['father_email']),
            'father_mobile'      => sanitize_text_field($data['father_mobile']),
            'father_school'      => sanitize_text_field($data['father_school']),
            'father_medium'      => sanitize_text_field($data['father_medium']),
            'father_education'   => sanitize_text_field($data['father_education']),
            'fathe_income'       => sanitize_textarea_field($data['fathe_income']),
            'father_occuption'   => sanitize_text_field($data['father_occuption']),
            'father_doc'         => json_encode($father_document_data),
            );
        }
        // Add mother metadata if mother_email is not empty.
        if (! empty($data['mother_email']) ) {
            $mother_metadata = array(
            'mothersalutation'   => sanitize_text_field($data['mothersalutation']),
            'mother_first_name'  => sanitize_text_field($data['mother_first_name']),
            'mother_middle_name' => sanitize_text_field($data['mother_middle_name']),
            'mother_last_name'   => sanitize_text_field($data['mother_last_name']),
            'mother_gender'      => sanitize_text_field($data['mother_gender']),
            'mother_birth_date'  => sanitize_text_field($data['mother_birth_date']),
            'mother_address'     => sanitize_textarea_field($data['mother_address']),
            'mother_city_name'   => sanitize_text_field($data['mother_city_name']),
            'mother_state_name'  => sanitize_text_field($data['mother_state_name']),
            'mother_zip_code'    => sanitize_text_field($data['mother_zip_code']),
            'mother_email'       => sanitize_email($data['mother_email']),
            'mother_mobile'      => sanitize_text_field($data['mother_mobile']),
            'mother_school'      => sanitize_text_field($data['mother_school']),
            'mother_medium'      => sanitize_text_field($data['mother_medium']),
            'mother_education'   => sanitize_text_field($data['mother_education']),
            'mother_income'      => sanitize_text_field($data['mother_income']),
            'mother_occuption'   => sanitize_text_field($data['mother_occuption']),
            'mother_doc'         => json_encode($mother_document_data),
            );
        }
        // Merge metadata arrays.
        $usermetadata = array_merge($usermetadata, $father_metadata, $mother_metadata);
        if ($data['action'] == 'edit' ) {
            mjschool_append_audit_log('' . esc_html('Addmission Updated', 'mjschool') . '', $data['user_id'], get_current_user_id(), 'edit', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            $userdata['ID'] = intval($data['user_id']);
            $user_id        = wp_update_user($userdata);
            foreach ( $usermetadata as $key => $val ) {
                $returnans = update_user_meta($user_id, $key, $val, '');
            }
        } else {
            mjschool_append_audit_log('' . esc_html('Addmission Added', 'mjschool') . '', $data['user_id'], get_current_user_id(), 'insert', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            $user_id = wp_insert_user($userdata);
            $user    = new WP_User($user_id);
            $user->set_role($role);
            foreach ( $usermetadata as $key => $val ) {
                $returnans = add_user_meta($user_id, $key, $val, true);
            }
            if (get_option('mjschool_admission_fees') == 'yes' ) {
                $generated = mjschool_generate_admission_fees_invoice($admission_fees_amount, $user_id, $admission_fees_id, 0, 0, 'Admission Fees');
            }
        }
        if ($user_id ) {
            // ---------- Admission request mail. ---------//
            $string                     = array();
            $string['{{student_name}}'] = mjschool_get_display_name($user_id);
            $string['{{user_name}}']    = $firstname . ' ' . $lastname;
            $string['{{email}}']        = $userdata['user_email'];
            $string['{{school_name}}']  = get_option('mjschool_name');
            $MsgContent                 = get_option('mjschool_admission_mailtemplate_content');
            $MsgSubject                 = get_option('mjschool_admissiion_title');
            $message                    = mjschool_string_replacement($string, $MsgContent);
            $MsgSubject                 = mjschool_string_replacement($string, $MsgSubject);
            $email                      = $userdata['user_email'];
            mjschool_send_mail($email, $MsgSubject, $message);
        }
        $returnval = update_user_meta($user_id, 'first_name', $firstname);
        $returnval = update_user_meta($user_id, 'last_name', $lastname);
        $hash      = md5(rand(0, 1000));
        $returnval = update_user_meta($user_id, 'hash', $hash);
        return $user_id;
    }
    /**
     * Creates or links parent user accounts for a given student.
     *
     * This function automatically creates WordPress user accounts for
     * the student's father and/or mother based on the admission data.
     * It also links the parent and child accounts and sends notification emails.
     *
     * @since 1.0.0
     *
     * @param int    $student_id   The ID of the student user.
     * @param string $role_parents The role to assign to created parent users (usually 'parent').
     *
     * @return bool True on success, false otherwise.
     */
    public function mjschool_add_parent( $student_id, $role_parents )
    {
        $student_data = get_user_meta($student_id);
        if ($student_data['parent_status'][0] == 'Both' ) {
            if (( ! empty($student_data['father_first_name'][0]) ) || ( ! empty($student_data['mother_first_name'][0]) ) ) {
                // ------------------ Father data insert. ------------------//
                $fatherdata = array(
                 'user_login'    => sanitize_email($student_data['father_email'][0]),
                 'user_nicename' => null,
                 'user_email'    => sanitize_email($student_data['father_email'][0]),
                 'user_url'      => null,
                 'user_pass'     => wp_generate_password(),
                 'display_name'  => sanitize_text_field($student_data['father_first_name'][0]) . ' ' . sanitize_text_field($student_data['father_middle_name'][0]) . ' ' . sanitize_text_field($student_data['father_last_name'][0]),
                );
                // Add user meta.
                $fathermetadata = array(
                 'middle_name'   => sanitize_text_field($student_data['father_middle_name'][0]),
                 'gender'        => sanitize_text_field($student_data['fathe_gender'][0]),
                 'birth_date'    => sanitize_text_field($student_data['father_birth_date'][0]),
                 'address'       => sanitize_text_field($student_data['father_address'][0]),
                 'city'          => sanitize_text_field($student_data['father_city_name'][0]),
                 'state'         => sanitize_text_field($student_data['father_state_name'][0]),
                 'zip_code'      => sanitize_text_field($student_data['father_zip_code'][0]),
                 'phone'         => sanitize_text_field($student_data['father_mobile'][0]),
                 'mobile_number' => sanitize_text_field($student_data['father_mobile'][0]),
                 'relation'      => 'Father',
                );
                $father_id      = wp_insert_user($fatherdata);
                $user           = new WP_User($father_id);
                $user->set_role($role_parents);
                foreach ( $fathermetadata as $key => $val ) {
                    $returnans = add_user_meta($father_id, $key, $val, true);
                }
                // ---------- Mail for add parents. ----------//
                if ($father_id ) {
                    $string                    = array();
                    $string['{{user_name}}']   = $student_data['father_first_name'][0] . ' ' . $student_data['father_middle_name'][0] . ' ' . $student_data['father_last_name'][0];
                    $string['{{school_name}}'] = get_option('mjschool_name');
                    $string['{{role}}']        = $role_parents;
                    $string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
                    $string['{{username}}']    = $fatherdata['user_login'];
                    $string['{{Password}}']    = $fatherdata['user_pass'];
                    $MsgContent                = get_option('mjschool_add_user_mail_content');
                    $MsgSubject                = get_option('mjschool_add_user_mail_subject');
                    $message                   = mjschool_string_replacement($string, $MsgContent);
                    $MsgSubject                = mjschool_string_replacement($string, $MsgSubject);
                    $email                     = $fatherdata['user_email'];
                    mjschool_send_mail($email, $MsgSubject, $message);
                }
                $returnval = update_user_meta($father_id, 'first_name', $student_data['father_first_name'][0]);
                $returnval = update_user_meta($father_id, 'last_name', $student_data['father_last_name'][0]);
                // ------------ Mother data insert. ------------------//
                $motherdata = array(
                 'user_login'    => sanitize_email($student_data['mother_email'][0]),
                 'user_nicename' => null,
                 'user_email'    => sanitize_email($student_data['mother_email'][0]),
                 'user_url'      => null,
                 'user_pass'     => wp_generate_password(),
                 'display_name'  => sanitize_text_field($student_data['mother_first_name'][0]) . ' ' . sanitize_text_field($student_data['mother_middle_name'][0]) . ' ' . sanitize_text_field($student_data['mother_last_name'][0]),
                );
                // Add user meta.
                $mothermetadata = array(
                 'middle_name'   => sanitize_text_field($student_data['mother_middle_name'][0]),
                 'gender'        => sanitize_text_field($student_data['mother_gender'][0]),
                 'birth_date'    => sanitize_text_field($student_data['mother_birth_date'][0]),
                 'address'       => sanitize_text_field($student_data['mother_address'][0]),
                 'city'          => sanitize_text_field($student_data['mother_city_name'][0]),
                 'state'         => sanitize_text_field($student_data['mother_state_name'][0]),
                 'zip_code'      => sanitize_text_field($student_data['mother_zip_code'][0]),
                 'phone'         => sanitize_text_field($student_data['mother_mobile'][0]),
                 'mobile_number' => sanitize_text_field($student_data['mother_mobile'][0]),
                 'relation'      => 'Mother',
                );
                $mother_id      = wp_insert_user($motherdata);
                $user1          = new WP_User($mother_id);
                $user1->set_role($role_parents);
                foreach ( $mothermetadata as $key => $val ) {
                    $returnans = add_user_meta($mother_id, $key, $val, true);
                }
                // ---------- Mail for add parents. ----------//
                if ($mother_id ) {
                    $string                    = array();
                    $string['{{user_name}}']   = $student_data['mother_first_name'][0] . ' ' . $student_data['mother_middle_name'][0] . ' ' . $student_data['mother_last_name'][0];
                    $string['{{school_name}}'] = get_option('mjschool_name');
                    $string['{{role}}']        = $role_parents;
                    $string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
                    $string['{{username}}']    = $motherdata['user_login'];
                    $string['{{Password}}']    = $motherdata['user_pass'];
                    $MsgContent                = get_option('mjschool_add_user_mail_content');
                    $MsgSubject                = get_option('mjschool_add_user_mail_subject');
                    $message                   = mjschool_string_replacement($string, $MsgContent);
                    $MsgSubject                = mjschool_string_replacement($string, $MsgSubject);
                    $email                     = $motherdata['user_email'];
                    mjschool_send_mail($email, $MsgSubject, $message);
                }
                $returnval = update_user_meta($mother_id, 'first_name', $student_data['mother_first_name'][0]);
                $returnval = update_user_meta($mother_id, 'last_name', $student_data['mother_last_name'][0]);
                $parant_id = array( $father_id, $mother_id );
                $returnval = add_user_meta($student_id, 'parent_id', $parant_id);
                $child_id  = array( $student_id );
                $returnval = add_user_meta($father_id, 'child', $child_id);
                $returnval = add_user_meta($mother_id, 'child', $child_id);
                return $returnval;
            }
        } elseif ($student_data['parent_status'][0] == 'Father' ) {
            if (( ! empty($student_data['father_email'][0]) ) and ( ! empty($student_data['father_first_name'][0]) ) ) {
                if (email_exists($student_data['father_email'][0]) ) {
                    $user      = get_user_by('email', $student_data['father_email'][0]);
                    $user_id   = $user->ID;
                    $parant_id = array( $user_id );
                    $returnval = add_user_meta($student_id, 'parent_id', $parant_id);
                    $child_id  = array( $student_id );
                    $returnval = update_user_meta($user_id, 'child', $child_id);
                } else {
                    // ------------ Father data insert. ------------------//
                    $userdata = array(
                    'user_login'    => sanitize_email($student_data['father_email'][0]),
                    'user_nicename' => null,
                    'user_email'    => sanitize_email($student_data['father_email'][0]),
                    'user_url'      => null,
                    'user_pass'     => wp_generate_password(),
                    'display_name'  => sanitize_text_field($student_data['father_first_name'][0]) . ' ' . sanitize_text_field($student_data['father_middle_name'][0]) . ' ' . sanitize_text_field($student_data['father_last_name'][0]),
                    );
                    // Add user meta.
                    $usermetadata = array(
                    'middle_name'   => sanitize_text_field($student_data['father_middle_name'][0]),
                    'gender'        => sanitize_text_field($student_data['fathe_gender'][0]),
                    'birth_date'    => sanitize_text_field($student_data['father_birth_date'][0]),
                    'address'       => sanitize_text_field($student_data['father_address'][0]),
                    'city'          => sanitize_text_field($student_data['father_city_name'][0]),
                    'state'         => sanitize_text_field($student_data['father_state_name'][0]),
                    'zip_code'      => sanitize_text_field($student_data['father_zip_code'][0]),
                    'phone'         => sanitize_text_field($student_data['father_mobile'][0]),
                    'mobile_number' => sanitize_text_field($student_data['father_mobile'][0]),
                    'relation'      => 'Father',
                    );
                    $user_id      = wp_insert_user($userdata);
                    $user         = new WP_User($user_id);
                    $user->set_role($role_parents);
                    foreach ( $usermetadata as $key => $val ) {
                        $returnans = add_user_meta($user_id, $key, $val, true);
                    }
                    // ---------- Mail for add parents. ----------//
                    if ($user_id ) {
                        $string                    = array();
                        $string['{{user_name}}']   = $student_data['father_first_name'][0] . ' ' . $student_data['father_middle_name'][0] . ' ' . $student_data['father_last_name'][0];
                        $string['{{school_name}}'] = get_option('mjschool_name');
                        $string['{{role}}']        = $role_parents;
                        $string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
                        $string['{{username}}']    = $userdata['user_login'];
                        $string['{{Password}}']    = $userdata['user_pass'];
                        $MsgContent                = get_option('mjschool_add_user_mail_content');
                        $MsgSubject                = get_option('mjschool_add_user_mail_subject');
                        $message                   = mjschool_string_replacement($string, $MsgContent);
                        $MsgSubject                = mjschool_string_replacement($string, $MsgSubject);
                        $email                     = $userdata['user_email'];
                        mjschool_send_mail($email, $MsgSubject, $message);
                    }
                    $returnval = update_user_meta($user_id, 'first_name', $student_data['father_first_name'][0]);
                    $returnval = update_user_meta($user_id, 'last_name', $student_data['father_last_name'][0]);
                    $parant_id = array( $user_id );
                    $returnval = add_user_meta($student_id, 'parent_id', $parant_id);
                    $child_id  = array( $student_id );
                    $returnval = add_user_meta($user_id, 'child', $child_id);
                }
                return $returnval;
            }
        } elseif ($student_data['parent_status'][0] == 'Mother' ) {
            if (( ! empty($student_data['mother_email'][0]) ) and ( ! empty($student_data['mother_first_name'][0]) ) ) {
                if (email_exists($student_data['mother_email'][0]) ) {
                    $user      = get_user_by('email', $student_data['mother_email'][0]);
                    $user_id   = $user->ID;
                    $parant_id = array( $user_id );
                    $returnval = add_user_meta($student_id, 'parent_id', $parant_id);
                    $child_id  = array( $student_id );
                    $returnval = update_user_meta($user_id, 'child', $child_id);
                } else {
                    // ------------ Mother data insert. ------------------//
                    $userdata = array(
                    'user_login'    => sanitize_email($student_data['mother_email'][0]),
                    'user_nicename' => null,
                    'user_email'    => sanitize_email($student_data['mother_email'][0]),
                    'user_url'      => null,
                    'user_pass'     => wp_generate_password(),
                    'display_name'  => sanitize_text_field($student_data['mother_first_name'][0]) . ' ' . sanitize_text_field($student_data['mother_middle_name'][0]) . ' ' . sanitize_text_field($student_data['mother_last_name'][0]),
                    );
                    // Add user meta.
                    $usermetadata = array(
                    'middle_name'   => sanitize_text_field($student_data['mother_middle_name'][0]),
                    'gender'        => sanitize_text_field($student_data['mother_gender'][0]),
                    'birth_date'    => $student_data['mother_birth_date'][0],
                    'address'       => sanitize_text_field($student_data['mother_address'][0]),
                    'city'          => sanitize_text_field($student_data['mother_city_name'][0]),
                    'state'         => sanitize_text_field($student_data['mother_state_name'][0]),
                    'zip_code'      => sanitize_text_field($student_data['mother_zip_code'][0]),
                    'phone'         => sanitize_text_field($student_data['mother_mobile'][0]),
                    'mobile_number' => sanitize_text_field($student_data['mother_mobile'][0]),
                    'relation'      => 'Mother',
                    );
                    $user_id      = wp_insert_user($userdata);
                    $user         = new WP_User($user_id);
                    $user->set_role($role_parents);
                    foreach ( $usermetadata as $key => $val ) {
                        $returnans = add_user_meta($user_id, $key, $val, true);
                    }
                    // ---------- Mail for add parents. ----------//
                    if ($user_id ) {
                        $string                    = array();
                        $string['{{user_name}}']   = $student_data['mother_first_name'][0] . ' ' . $student_data['mother_middle_name'][0] . ' ' . $student_data['mother_last_name'][0];
                        $string['{{school_name}}'] = get_option('mjschool_name');
                        $string['{{role}}']        = $role_parents;
                        $string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
                        $string['{{username}}']    = $userdata['user_login'];
                        $string['{{Password}}']    = $userdata['user_pass'];
                        $MsgContent                = get_option('mjschool_add_user_mail_content');
                        $MsgSubject                = get_option('mjschool_add_user_mail_subject');
                        $message                   = mjschool_string_replacement($string, $MsgContent);
                        $MsgSubject                = mjschool_string_replacement($string, $MsgSubject);
                        $email                     = $userdata['user_email'];
                        mjschool_send_mail($email, $MsgSubject, $message);
                    }
                    $returnval = update_user_meta($user_id, 'first_name', $student_data['mother_first_name'][0]);
                    $returnval = update_user_meta($user_id, 'last_name', $student_data['mother_last_name'][0]);
                    $parant_id = array( $user_id );
                    $returnval = add_user_meta($student_id, 'parent_id', $parant_id);
                    $child_id  = array( $student_id );
                    $returnval = add_user_meta($user_id, 'child', $child_id);
                }
                return $returnval;
            }
        }
    }
}
