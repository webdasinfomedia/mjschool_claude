<?php
/**
 * School Management Invoice Class.
 *
 * This file contains the Mjschool_Invoice class, which handles
 * invoice number generation, invoice data retrieval, and income/expense management.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Handles all business logic and data manipulation for the Mjschool payment module.
 *
 * This class manages invoice number generation, invoice data retrieval, and income/expense management.
 *
 * @since 1.0.0
 */
class Mjschool_Invoice
{
    /**
     * Generates a new invoice number based on the current date and the last invoice ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return string The generated invoice number (e.g., YYMMDDXXX).
     * @since  1.0.0
     */
    public function mjschool_generate_invoce_number()
    {
        global $wpdb;
        $table_invoice = $wpdb->prefix . 'mjschool_invoice';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_invoice ORDER BY invoice_id DESC"));
        $year   = date('y');
        $month  = date('m');
        $date   = date('d');
        $concat = $year . $month . $date;
        if (! empty($result) ) {
            $res = $result->invoice_id + 1;
            return $concat . $res;
        } else {
            $res = 1;
            return $concat . $res;
        }
    }
    /**
     * Retrieves invoice data by invoice ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $invoice_id The ID of the invoice to retrieve.
     * @return object|null The invoice data object or null if not found.
     * @since  1.0.0
     */
    public function mjschool_get_invoice_data( $invoice_id )
    {
        global $wpdb;
        $table_invoice = $wpdb->prefix . 'mjschool_invoice';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_invoice where invoice_id=%d", $invoice_id));
        return $result;
    }
    /**
     * Retrieves all invoice data.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of invoice data objects.
     * @since  1.0.0
     */
    public function mjschool_get_all_invoice_data()
    {
        global $wpdb;
        $table_invoice = $wpdb->prefix . 'mjschool_invoice';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_invoice"));
        return $result;
    }
    /**
     * Deletes an invoice record by ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $invoice_id The ID of the invoice to delete.
     * @return int|false The number of rows deleted, or false on error.
     * @since  1.0.0
     */
    public function mjschool_delete_invoice( $invoice_id )
    {
        global $wpdb;
        $table_invoice = $wpdb->prefix . 'mjschool_invoice';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query($wpdb->prepare("DELETE FROM $table_invoice where invoice_id=%d", $invoice_id));
        return $result;
    }
    /**
     * Formats income entries and amounts into a JSON string.
     *
     * @param  array $data Contains 'income_entry' (array of entry names) and 'income_amount' (array of amounts).
     * @return string JSON encoded array of entry and amount pairs.
     * @since  1.0.0
     */
    public function mjschool_get_entry_records( $data )
    {
        $all_income_entry  = $data['income_entry'];
        $all_income_amount = $data['income_amount'];
        $entry_data        = array();
        $i                 = 0;
        foreach ( $all_income_entry as $one_entry ) {
            $entry_data[] = array(
            'entry'  => mjschool_strip_tags_and_stripslashes($one_entry),
            'amount' => $all_income_amount[ $i ],
            );
            ++$i;
        }
        return json_encode($entry_data);
    }
    /**
     * Calculates the total amount from a set of income entries.
     *
     * @param  array $data Contains 'income_entry' (array of entry names) and 'income_amount' (array of amounts).
     * @return float The total calculated income amount.
     * @since  1.0.0
     */
    public function mjschool_get_entry_total_amount( $data )
    {
        $all_income_entry  = $data['income_entry'];
        $all_income_amount = $data['income_amount'];
        $entry_amount      = 0;
        $i                 = 0;
        foreach ( $all_income_entry as $one_entry ) {
            $entry_amount += $all_income_amount[ $i ];
            ++$i;
        }
        return $entry_amount;
    }
    /**
     * Adds or updates an income record.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array $data The income data array from a form submission.
     * @return int|false The ID of the inserted record or the result of the update query.
     * @since  1.0.0
     */
    public function mjschool_add_income( $data )
    {
        $entry_value  = $this->mjschool_get_entry_records($data);
        $total_amount = $this->mjschool_get_entry_total_amount($data);
        global $wpdb;
        $table_income               = $wpdb->prefix . 'mjschool_income_expense';
        $incomedata['invoice_type'] = $data['invoice_type'];
        if (isset($data['class_id']) ) {
            $incomedata['class_id'] = sanitize_text_field($data['class_id']);
        }
        if (isset($data['class_section']) ) {
            $incomedata['section_id'] = sanitize_text_field($data['class_section']);
        }
        $incomedata['supplier_name']      = sanitize_text_field($data['supplier_name']);
        $incomedata['income_create_date'] = date('Y-m-d', strtotime($data['invoice_date']));
        $incomedata['payment_status']     = sanitize_text_field($data['payment_status']);
        $incomedata['entry']              = $entry_value;
        $incomedata['create_by']          = get_current_user_id();
        if (isset($data['tax']) ) {
            $tax        = implode(',', (array) $_POST['tax']);
            $tax_amount = mjschool_get_tax_amount($total_amount, $data['tax']);
        } else {
            $tax        = null;
            $tax_amount = 0;
        }
        $incomedata['tax']        = $tax;
        $incomedata['tax_amount'] = $tax_amount;
        if ($data['action'] == 'edit' ) {
            $income_dataid['income_id'] = intval($data['income_id']);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result  = $wpdb->update($table_income, $incomedata, $income_dataid);
            $student = mjschool_get_user_name_by_id(sanitize_text_field($incomedata['supplier_name']));
            mjschool_append_audit_log('' . esc_html__('Income Updated', 'mjschool') . '( ' . $student . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            return $result;
        } elseif ($data['action'] == 'edit_payment' ) {
            // Delete payment record and add in income record.
            $tablename      = 'mjschool_payment';
            $delete_payment = mjschool_delete_payment($tablename, $data['payment_id']);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result  = $wpdb->insert($table_income, $incomedata);
            $student = mjschool_get_user_name_by_id(sanitize_text_field($incomedata['supplier_name']));
            mjschool_append_audit_log('' . esc_html__('Income Added', 'mjschool') . '( ' . $student . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            return $result;
        } else {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result   = $wpdb->insert($table_income, $incomedata);
            $leave_id = $wpdb->insert_id;
            $student  = mjschool_get_user_name_by_id(sanitize_text_field($incomedata['supplier_name']));
            mjschool_append_audit_log('' . esc_html__('Income Added', 'mjschool') . '( ' . $student . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            return $leave_id;
        }
    }
    /**
     * Retrieves all income records.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of income data objects.
     * @since  1.0.0
     */
    public function mjschool_get_all_income_data()
    {
        global $wpdb;
        $invoice_type = 'income';
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income where invoice_type=%s", $invoice_type));
        return $result;
    }
    /**
     * Retrieves a single income record by ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $income_id The ID of the income record.
     * @return object|null The income data object or null if not found.
     * @since  1.0.0
     */
    public function mjschool_get_income_data( $income_id )
    {
        global $wpdb;
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_income where income_id=%d", $income_id));
        return $result;
    }
    /**
     * Retrieves income records created for a specific user ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $user_id The ID of the student/supplier.
     * @return array Array of income data objects.
     * @since  1.0.0
     */
    public function mjschool_get_income_own_data( $user_id )
    {
        global $wpdb;
        $invoice_type = 'income';
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income where invoice_type=%s and supplier_name=%d", $invoice_type, $user_id));
        return $result;
    }
    /**
     * Retrieves income records for all associated children of the current user (parent).
     *
     * Assumes MJSchool_Management class is available to get child list.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of income data objects for the children.
     * @since  1.0.0
     */
    public function mjschool_get_income_own_data_for_parent()
    {
        global $wpdb;
        // Instantiate MJSchool_Management class with the current user ID.
        $school_obj   = new MJSchool_Management(get_current_user_id());
        $invoice_type = 'income';
        $child_list   = $school_obj->child_list;
        // If no child is associated, return an empty array.
        if (empty($child_list) ) {
            return array();
        }
        // Sanitize and prepare child IDs for the query.
        $child_ids    = implode(',', array_map('intval', $child_list));
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
        // Use a secure and correctly formatted query with the IN clause.
        $query = "SELECT * FROM $table_income WHERE invoice_type = %s AND supplier_name IN ($child_ids)";
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare($query, $invoice_type));
        return $result;
    }
    /**
     * Retrieves income records created by a specific user ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $user_id The ID of the user who created the record.
     * @return array Array of income data objects.
     * @since  1.0.0
     */
    public function mjschool_get_income_data_created_by( $user_id )
    {
        global $wpdb;
        $invoice_type = 'income';
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income where invoice_type=%s and create_by=%d", $invoice_type, $user_id));
        return $result;
    }
    /**
     * Deletes an income record by ID and logs the action.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $income_id The ID of the income record to delete.
     * @return int|false The number of rows deleted, or false on error.
     * @since  1.0.0
     */
    public function mjschool_delete_income( $income_id )
    {
        global $wpdb;
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $event   = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_income where income_id= %d", $income_id));
        $student = mjschool_get_user_name_by_id($event->supplier_name);
        mjschool_append_audit_log('' . esc_html__('Income Deleted', 'mjschool') . '( ' . $student . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', sanitize_text_field(wp_unslash($_REQUEST['page'])));
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query($wpdb->prepare("DELETE FROM $table_income where income_id= %d", $income_id));
        return $result;
    }
    /**
     * Retrieves income records for a specific patient/supplier ID, ordered by date.
     *
     * Handles both numeric (ID) and non-numeric (string) supplier names.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int|string $patient_id The ID or name of the patient/supplier.
     * @return array Array of income data objects.
     * @since  1.0.0
     */
    public function mjschool_get_onepatient_income_data( $patient_id )
    {
        global $wpdb;
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income where supplier_name= '" . $patient_id . "' order by income_create_date desc"));
        if (is_numeric($patient_id) ) {
            // Treat as an ID (integer).
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income WHERE supplier_name = %d ORDER BY income_create_date DESC", $patient_id));
        } else {
            // Treat as a string.
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income WHERE supplier_name = %s ORDER BY income_create_date DESC", sanitize_text_field($patient_id)));
        }
        return $result;
    }
    /**
     * Retrieves a single income record by its primary ID.
     *
     * The naming suggests 'student' but it fetches a single record by income_id.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $income_id The ID of the income record.
     * @return array Array of income data objects (should be a single record).
     * @since  1.0.0
     */
    public function mjschool_get_onestudent_income_data( $income_id )
    {
        global $wpdb;
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income where income_id= %d", $income_id));
        return $result;
    }
    /**
     * Adds or updates an expense record.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array $data The expense data array from a form submission.
     * @return int|false The ID of the inserted record or the result of the update query.
     * @since  1.0.0
     */
    public function mjschool_add_expense( $data )
    {
        $entry_value = $this->mjschool_get_entry_records($data);
        global $wpdb;
        $table_income                     = $wpdb->prefix . 'mjschool_income_expense';
        $incomedata['invoice_type']       = sanitize_text_field($data['invoice_type']);
        $incomedata['supplier_name']      = sanitize_text_field($data['supplier_name']);
        $incomedata['income_create_date'] = date('Y-m-d', strtotime($data['invoice_date']));
        $incomedata['payment_status']     = sanitize_text_field($data['payment_status']);
        $incomedata['entry']              = $entry_value;
        $incomedata['create_by']          = get_current_user_id();
        if ($data['action'] == 'edit' ) {
            $expense_dataid['income_id'] = sanitize_text_field($data['expense_id']);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result  = $wpdb->update($table_income, $incomedata, $expense_dataid);
            $suplier = $incomedata['supplier_name'];
            mjschool_append_audit_log('' . esc_html__('Expense Updated', 'mjschool') . '( ' . $suplier . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            return $result;
        } else {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result   = $wpdb->insert($table_income, $incomedata);
            $leave_id = $wpdb->insert_id;
            $suplier  = $incomedata['supplier_name'];
            mjschool_append_audit_log('' . esc_html__('Expense Added', 'mjschool') . '( ' . $suplier . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            return $leave_id;
        }
    }
    /**
     * Deletes an expense record by ID and logs the action.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $expense_id The ID of the expense record to delete.
     * @return int|false The number of rows deleted, or false on error.
     * @since  1.0.0
     */
    public function mjschool_delete_expense( $expense_id )
    {
        global $wpdb;
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $event   = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_income where income_id= %d,$expense_id"));
        $student = $event->supplier_name;
        mjschool_append_audit_log('' . esc_html__('Expense Deleted', 'mjschool') . '( ' . $student . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', sanitize_text_field(wp_unslash($_REQUEST['page'])));
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query($wpdb->prepare("DELETE FROM $table_income where income_id=%d", $expense_id));
        return $result;
    }
    /**
     * Retrieves all expense records.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of expense data objects.
     * @since  1.0.0
     */
    public function mjschool_get_all_expense_data()
    {
        global $wpdb;
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income where invoice_type=%s", 'expense'));
        return $result;
    }
    /**
     * Retrieves all expense records created by a specific user ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $user_id The ID of the user who created the record.
     * @return array Array of expense data objects.
     * @since  1.0.0
     */
    public function mjschool_get_all_expense_data_created_by( $user_id )
    {
        global $wpdb;
        $table_income = $wpdb->prefix . 'mjschool_income_expense';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income where invoice_type= %s and create_by=%d", 'expense', $user_id));
        return $result;
    }
    /**
     * Retrieves all payment records created by a specific user ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $user_id The ID of the user who created the payment.
     * @return array Array of payment data objects.
     * @since  1.0.0
     */
    public function mjschool_get_invoice_created_by( $user_id )
    {
        global $wpdb;
        $table_income = $wpdb->prefix . 'mjschool_payment';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_income where created_by=%d", $user_id));
        return $result;
    }
}
