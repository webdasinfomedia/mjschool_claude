<?php
/**
 * School Management Document Class.
 *
 * This file contains the Mjschool_Document class which manages
 * CRUD operations for documents (files, letters, certificates)
 * related to students and teachers.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Mjschool_Document Class.
 *
 * Handles all database operations and business logic related to
 * document management, including adding, updating, retrieving,
 * and deleting documents and certificates.
 *
 * @since 1.0.0
 */
class Mjschool_Document
{

    /**
     * Handles adding or updating a document record.
     *
     * Checks if the action is 'edit' to update an existing record, otherwise inserts
     * a new document record into the database. Handles data sanitation and logging.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param array $data          Array of primary document details from the form submission.
     * @param array $document_data Array containing the actual document files/content to be JSON encoded.
     *
     * @return int|false The inserted document ID on insert, the number of affected rows on update, or false on error.
     */
    public function mjschool_add_document( $data, $document_data )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_document';
        if ($data['document_for'] == 'student' ) {
            $documentdata['class_id']   = sanitize_text_field($data['class_id']);
            $documentdata['section_id'] = sanitize_text_field($data['class_section']);
        } else {
            $documentdata['class_id']   = '';
            $documentdata['section_id'] = '';
        }
        $documentdata['student_id']       = sanitize_text_field($data['selected_users']);
        $documentdata['document_for']     = sanitize_text_field($data['document_for']);
        $documentdata['document_content'] = json_encode($document_data);
        $documentdata['description']      = sanitize_textarea_field($data['description']);
        $documentdata['createdby']        = get_current_user_id();
        $documentdata['created_date']     = date('Y-m-d');
        if ($data['action'] == 'edit' ) {
            mjschool_append_audit_log('' . esc_html__('Update Document Detail', 'mjschool') . '', null, get_current_user_id(), 'edit', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            $whereid['document_id'] = intval($data['document_id']);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->update($table_name, $documentdata, $whereid);
            return $result;
        } else {
            mjschool_append_audit_log('' . esc_html__('Add New Document Detail', 'mjschool') . '', null, get_current_user_id(), 'insert', sanitize_text_field(wp_unslash($_REQUEST['page'])));
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->insert($table_name, $documentdata);
            return $wpdb->insert_id;
        }
    }
    /**
     * Retrieve all document records, ordered by creation date.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @return array|object|null An array of document objects, or null if no results are found.
     */
    public function mjschool_get_all_documents()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_document';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY created_date DESC"));
        return $result;
    }
    /**
     * Retrieve documents created by or assigned to a specific user.
     *
     * Fetches documents where the user is either the creator (`createdby`) or the
     * assigned recipient (`student_id`).
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param int $user_id ID of the user whose documents are being retrieved.
     *
     * @return array|object|null An array of document objects, ordered by creation date, or null if no results are found.
     */
    public function mjschool_get_own_documents( $user_id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_document';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name where student_id=%d OR createdby=%d ORDER BY created_date DESC", $user_id, $user_id));
        return $result;
    }
    /**
     * Retrieve a single document record by its ID.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param int $id The unique ID of the document (`document_id`).
     *
     * @return object|null The document record object if found, or null otherwise.
     */
    public function mjschool_get_single_document( $id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_document';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name where document_id=%d", $id));
        return $result;
    }
    /**
     * Delete a document record by its ID.
     *
     * Also appends an entry to the audit log for the deletion.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param int $id The unique ID of the document to delete.
     *
     * @return int|false The number of rows deleted, or false on error.
     */
    public function mjschool_delete_document( $id )
    {
        mjschool_append_audit_log('' . esc_html__('Delete Document Detail', 'mjschool') . '', null, get_current_user_id(), 'delete', sanitize_text_field(wp_unslash($_REQUEST['page'])));
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_document';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query($wpdb->prepare("DELETE FROM $table_name where document_id=%d", $id));
        return $result;
    }
    /**
     * Handles adding or updating an experience/certificate letter record.
     *
     * Strips slashes and updates content URLs before inserting or updating the record.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param array $data Array of letter details, including content and update flag.
     *
     * @return int|false The inserted certificate ID on insert, the number of affected rows on update, or false on error.
     */
    function mjschool_create_experience_letter( $data )
    {
        global $wpdb;
        $table_exprience_letter             = $wpdb->prefix . 'mjschool_certificate';
        $str_rplc                           = str_replace('../wp-content', content_url(), stripslashes($data['lett_content']));
        $letter_data['student_id']          = $data['student_id'];
        $letter_data['certificate_type']    = $data['certificate_type'];
        $letter_data['certificate_content'] = $str_rplc;
        $letter_data['created_by']          = get_current_user_id();
        $letter_data['created_at']          = date('Y-m-d H:i:s');
        if ($data['edit'] ) {
            $where['id'] = $data['id'];
        	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->update($table_exprience_letter, $letter_data, $where);
        } else {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->insert($table_exprience_letter, $letter_data);
        }
        return $result;
    }
    /**
     * Retrieve a student's experience/certificate letter.
     *
     * Fetches a single certificate record associated with the given student/employee ID.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param int $emp_id ID of the student or employee user.
     *
     * @return object|null The certificate record object if found, or null otherwise.
     */
    function mjschool_view_experience_letter_student( $emp_id )
    {
        global $wpdb;
        $table_exprience_letter = $wpdb->prefix . 'mjschool_certificate';
        $sql                    = $wpdb->prepare("SELECT * FROM $table_exprience_letter where student_id=%d", intval($emp_id));
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $result = $wpdb->get_row($sql);
    }
    /**
     * Retrieve a generic experience/certificate letter record.
     *
     * This function appears to return only the first record from the certificate table.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @return object|null The first certificate record object if found, or null otherwise.
     */
    function mjschool_view_experience_letter()
    {
        global $wpdb;
        $table_exprience_letter = $wpdb->prefix . 'mjschool_certificate';
        $sql                    = $wpdb->prepare("SELECT * FROM $table_exprience_letter");
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $result = $wpdb->get_row($sql);
    }
}
