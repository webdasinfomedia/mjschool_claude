<?php
/**
 * Custom Field Management Class
 *
 * This class handles all database operations, CRUD functions, and UI rendering
 * related to creating, managing, and utilizing custom fields within the mjschool system.
 * It manages the main custom field definitions, their options (for dropdowns/checkboxes),
 * and the meta values associated with records in various modules (e.g., students, teachers).
 *
 * @package    Mjschool
 * @subpackage Mjschool/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Core class for Mjschool Custom Field management.
 *
 * Defines methods for custom field CRUD, option management, meta data handling,
 * and front-end form generation.
 *
 * @since 1.0.0
 */
class Mjschool_Custome_Field {
	/**
	 * Handles the creation or update of a custom field definition.
	 *
	 * This method inserts/updates the main custom field record and manages
	 * the associated options (dropdown, radio, checkbox metas).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param  array $custome_data Array of data submitted from the custom field form.
	 * @return int The ID of the inserted/updated custom field record.
	 * @since 1.0.0
	 */
	function mjschool_add_custom_field( $custome_data ) {
		global $wpdb;
		$wpnc_custom_fields                    = $wpdb->prefix . 'mjschool_custom_field';
		$wpnc_custom_field_dropdown_metas      = $wpdb->prefix . 'mjschool_custom_field_dropdown_metas';
		$custom_field_data['field_label']      = stripslashes( sanitize_text_field( $custome_data['field_label'] ) );
		$validation_array_filter               = array_filter( $custome_data['validation'] );
		$custom_field_data['field_validation'] = implode( '|', $validation_array_filter );
		if ( $custome_data['field_visibility'] === '' ) {
			$custom_field_data['field_visibility'] = 0;
		} else {
			$custom_field_data['field_visibility'] = sanitize_text_field( $custome_data['field_visibility'] );
		}
		if ( $custome_data['action'] === 'edit' ) {
			$custom_field_data['updated_by'] = get_current_user_id();
			$custom_field_data['updated_at'] = date( 'Y-m-d H:i:s' );
			if ( isset( $custome_data['show_in_table'] ) && $custome_data['show_in_table'] != '' ) {
				$custom_field_data['show_in_table'] = $custome_data['show_in_table'];
			}
			$whereid['id'] = $custome_data['custom_field_id'];
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$update_custom_field = $wpdb->update( $wpnc_custom_fields, $custom_field_data, $whereid );
			$page_param = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			mjschool_append_audit_log( '' . esc_html__( 'Custom Field Updated', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'edit', $page_param );
			// Dropdown Label Code.
			if ( isset( $custome_data['d_label'] ) ) {
				$d_label = $custome_data['d_label'];
			} else {
				$d_label = null;
			}
			if ( ! empty( $d_label ) ) {
				// Delete old value.
				$custom_field_id = intval( $custome_data['custom_field_id'] );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$query = $wpdb->prepare( "DELETE FROM $wpnc_custom_field_dropdown_metas WHERE custom_fields_id = %d", $custom_field_id );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$delete_custom_field_dropdown_data = $wpdb->query( $query );
				foreach ( $d_label as $key => $value ) {
					$label = sanitize_text_field( $d_label[ $key ] );
					$custom_field_dropdown_data['custom_fields_id'] = $custom_field_id;
					$custom_field_dropdown_data['option_label']     = $label;
					$custom_field_dropdown_data['created_by']       = get_current_user_id();
					$custom_field_dropdown_data['created_at']       = date( 'Y-m-d H:i:s' );
					$custom_field_dropdown_data['updated_by']       = get_current_user_id();
					$custom_field_dropdown_data['updated_at']       = date( 'Y-m-d H:i:s' );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$insert_custom_field_dropdown_data = $wpdb->insert( $wpnc_custom_field_dropdown_metas, $custom_field_dropdown_data );
				}
			}
			// Checkbox Label Code.
			if ( isset( $custome_data['c_label'] ) ) {
				$c_label = $custome_data['c_label'];
			} else {
				$c_label = null;
			}
			if ( ! empty( $c_label ) ) {
				// Delete old value.
				$custom_field_id = intval( $custome_data['custom_field_id'] );
				$query           = $wpdb->prepare( "DELETE FROM $wpnc_custom_field_dropdown_metas WHERE custom_fields_id = %d", $custom_field_id );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$delete_custom_field_checkbox_data = $wpdb->query( $query );
				foreach ( $c_label as $key => $value ) {
					$label = sanitize_text_field( $c_label[ $key ] );
					$custom_field_checkbox_data['custom_fields_id'] = $custom_field_id;
					$custom_field_checkbox_data['option_label']     = $label;
					$custom_field_checkbox_data['created_by']       = get_current_user_id();
					$custom_field_checkbox_data['created_at']       = date( 'Y-m-d H:i:s' );
					$custom_field_checkbox_data['updated_by']       = get_current_user_id();
					$custom_field_checkbox_data['updated_at']       = date( 'Y-m-d H:i:s' );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$insert_custom_field_checkbox_data = $wpdb->insert( $wpnc_custom_field_dropdown_metas, $custom_field_checkbox_data );
				}
			}
			// Radio Label Code.
			if ( isset( $custome_data['r_label'] ) ) {
				$r_label = $custome_data['r_label'];
			} else {
				$r_label = null;
			}
			if ( ! empty( $r_label ) ) {
				// Delete old value.
				$custom_field_id = intval( $custome_data['custom_field_id'] );
				$query           = $wpdb->prepare( "DELETE FROM $wpnc_custom_field_dropdown_metas WHERE custom_fields_id = %d", $custom_field_id );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$delete_custom_field_radio_data = $wpdb->query( $query );
				foreach ( $r_label as $key => $value ) {
					$label                                       = sanitize_text_field( $r_label[ $key ] );
					$custom_field_radio_data['custom_fields_id'] = $custom_field_id;
					$custom_field_radio_data['option_label']     = $label;
					$custom_field_radio_data['created_by']       = get_current_user_id();
					$custom_field_radio_data['created_at']       = date( 'Y-m-d H:i:s' );
					$custom_field_radio_data['updated_by']       = get_current_user_id();
					$custom_field_radio_data['updated_at']       = date( 'Y-m-d H:i:s' );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$insert_custom_field_radio_data = $wpdb->insert( $wpnc_custom_field_dropdown_metas, $custom_field_radio_data );
				}
			}
			return intval( $custome_data['custom_field_id'] );
		} else {
			$custom_field_data['form_name']  = sanitize_text_field( $custome_data['form_name'] );
			$custom_field_data['field_type'] = sanitize_text_field( $custome_data['field_type'] );
			$custom_field_data['created_by'] = get_current_user_id();
			$custom_field_data['created_at'] = date( 'Y-m-d H:i:s' );
			if ( isset( $custome_data['show_in_table'] ) && $custome_data['show_in_table'] != '' ) {
				$custom_field_data['show_in_table'] = sanitize_text_field( $custome_data['show_in_table'] );
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$insert_custom_field = $wpdb->insert( $wpnc_custom_fields, $custom_field_data );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$custom_field_id = $wpdb->insert_id;
			$page_param = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			mjschool_append_audit_log( '' . esc_html__( 'Custom Field Added', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'insert', $page_param );
			// Dropdown Label Code.
			if ( isset( $custome_data['d_label'] ) ) {
				$d_label = $custome_data['d_label'];
			} else {
				$d_label = null;
			}
			if ( ! empty( $d_label ) ) {
				foreach ( $d_label as $key => $value ) {
					$label = sanitize_text_field( $d_label[ $key ] );
					$custom_field_dropdown_data['custom_fields_id'] = $custom_field_id;
					$custom_field_dropdown_data['option_label']     = $label;
					$custom_field_dropdown_data['created_by']       = get_current_user_id();
					$custom_field_dropdown_data['created_at']       = date( 'Y-m-d H:i:s' );
					$custom_field_dropdown_data['updated_by']       = get_current_user_id();
					$custom_field_dropdown_data['updated_at']       = date( 'Y-m-d H:i:s' );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$insert_custom_field_dropdown_data = $wpdb->insert( $wpnc_custom_field_dropdown_metas, $custom_field_dropdown_data );
				}
			}
			// Checkbox Label Code.
			if ( isset( $custome_data['c_label'] ) ) {
				$c_label = $custome_data['c_label'];
			} else {
				$c_label = null;
			}
			if ( ! empty( $c_label ) ) {
				foreach ( $c_label as $key => $value ) {
					$label = sanitize_text_field( $c_label[ $key ] );
					$custom_field_checkbox_data['custom_fields_id'] = $custom_field_id;
					$custom_field_checkbox_data['option_label']     = $label;
					$custom_field_checkbox_data['created_by']       = get_current_user_id();
					$custom_field_checkbox_data['created_at']       = date( 'Y-m-d H:i:s' );
					$custom_field_checkbox_data['updated_by']       = get_current_user_id();
					$custom_field_checkbox_data['updated_at']       = date( 'Y-m-d H:i:s' );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$insert_custom_field_checkbox_data = $wpdb->insert( $wpnc_custom_field_dropdown_metas, $custom_field_checkbox_data );
				}
			}
			// Radio Label Code.
			if ( isset( $custome_data['r_label'] ) ) {
				$r_label = $custome_data['r_label'];
			} else {
				$r_label = null;
			}
			if ( ! empty( $r_label ) ) {
				foreach ( $r_label as $key => $value ) {
					$label                                       = sanitize_text_field( $r_label[ $key ] );
					$custom_field_radio_data['custom_fields_id'] = $custom_field_id;
					$custom_field_radio_data['option_label']     = $label;
					$custom_field_radio_data['created_by']       = get_current_user_id();
					$custom_field_radio_data['created_at']       = date( 'Y-m-d H:i:s' );
					$custom_field_radio_data['updated_by']       = get_current_user_id();
					$custom_field_radio_data['updated_at']       = date( 'Y-m-d H:i:s' );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$insert_custom_field_radio_data = $wpdb->insert( $wpnc_custom_field_dropdown_metas, $custom_field_radio_data );
				}
			}
			return $custom_field_id;
		}
	}
	/**
     * Retrieves all active (non-soft-deleted) custom field definitions.
     *
     * Fields with `field_visibility` set to 2 are excluded. Results are ordered by creation date.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of custom field objects, or an empty array if none are found.
     * @since 1.0.0
     */
	function mjschool_get_all_custom_field_data() {
		global $wpdb;
		$wpnc_custom_fields = $wpdb->prefix . 'mjschool_custom_field';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_custom_field_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpnc_custom_fields WHERE field_visibility != %d ORDER BY created_at DESC", 2 ) );
		return $get_custom_field_data;
	}
	/**
     * Retrieves custom field definitions created only by the current logged-in user.
     *
     * Filters results by the `created_by` column and excludes soft-deleted fields.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of custom field objects created by the current user.
     * @since 1.0.0
     */
	function mjschool_get_all_custom_field_data_own() {
		$created_by = get_current_user_id(); // Get the current user's ID.
		global $wpdb;
		$wpnc_custom_fields = $wpdb->prefix . 'mjschool_custom_field';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_custom_field_data = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $wpnc_custom_fields WHERE created_by = %d AND field_visibility != %d ORDER BY created_at DESC", $created_by, 2 )
		);
		return $get_custom_field_data;
	}
	/**
     * Retrieves a single custom field definition record by its ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $cf_id The ID of the custom field to retrieve.
     * @return object|null Database row object containing the field data, or null if not found.
     * @since 1.0.0
     */
	function mjschool_get_single_custom_field_data( $cf_id ) {
		global $wpdb;
		$wpnc_custom_fields = $wpdb->prefix . 'mjschool_custom_field';
		$custom_fields_id   = intval( $cf_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$single_custom_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpnc_custom_fields WHERE id = %d", $custom_fields_id ) );
		return $single_custom_field_data;
	}
	/**
     * Retrieves all options (dropdown/radio/checkbox metas) for a specific custom field ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $cf_id The ID of the parent custom field.
     * @return array Array of option objects associated with the custom field.
     * @since 1.0.0
     */
	function mjschool_get_single_custom_field_dropdown_meta_data( $cf_id ) {
		global $wpdb;
		$wpnc_custom_field_dropdown_metas = $wpdb->prefix . 'mjschool_custom_field_dropdown_metas';
		$custom_fields_id                 = intval( $cf_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$custom_field_dropdown_meta_data = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $wpnc_custom_field_dropdown_metas WHERE custom_fields_id = %d", $custom_fields_id )
		);
		return $custom_field_dropdown_meta_data;
	}
	/**
     * Performs a soft delete on a single custom field record.
     *
     * Updates the `field_visibility` column to 2 to mark it as deleted, preserving the record.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $id The ID of the custom field to soft delete.
     * @return int|bool The number of rows updated on success, or false on error.
     * @since 1.0.0
     */
	public function mjschool_delete_custome_field( $id ) {
		global $wpdb;
		$wpnc_custom_fields               = $wpdb->prefix . 'mjschool_custom_field';
		$custom_field['field_visibility'] = 2;
		$whereid['id']                    = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$delete_rules = $wpdb->update( $wpnc_custom_fields, $custom_field, $whereid );
		return $delete_rules;
	}
	/**
     * Performs a soft delete on a selected custom field record (used for single record deletion).
     *
     * Updates the `field_visibility` column to 2. Functionally identical to `mjschool_delete_custome_field`.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $record_id The ID of the custom field record to soft delete.
     * @return int|bool The number of rows updated on success, or false on error.
     * @since 1.0.0
     */
	public function mjschool_delete_selected_custome_field( $record_id ) {
		global $wpdb;
		$wpnc_custom_fields               = $wpdb->prefix . 'mjschool_custom_field';
		$custom_field['field_visibility'] = 2;
		$whereid['id']                    = intval( $record_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->update( $wpnc_custom_fields, $custom_field, $whereid );
		return $result;
	}
	/**
     * Retrieves active custom field definitions based on the assigned module (form name).
     *
     * Used to populate custom fields on specific forms (e.g., student registration).
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  string $module The name of the module/form (e.g., 'student', 'teacher').
     * @return array Array of custom field objects matching the module and visibility.
     * @since 1.0.0
     */
	function mjschool_get_custom_field_by_module( $module ) {
		global $wpdb;
		$wpnc_custom_fields = $wpdb->prefix . 'mjschool_custom_field';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_data = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $wpnc_custom_fields WHERE form_name = %s AND field_visibility = %d", $module, 1 )
		);
		return $get_data;
	}
	/**
     * Retrieves the stored meta value for a single custom field associated with a record.
     *
     * Uses the module name, record ID, and custom field ID to query the meta table.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  string $module The name of the module (e.g., 'student').
     * @param  int    $m_id The primary record ID of the module (e.g., student ID).
     * @param  int    $cf_id The ID of the custom field definition.
     * @return string The stored field value, or an empty string if no meta is found.
     * @since 1.0.0
     */
	function mjschool_get_single_custom_field_meta_value( $module, $m_id, $cf_id ) {
		global $wpdb;
		$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
		$custom_field_id         = intval( $cf_id );
		$module_record_id        = intval( $m_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_data = $wpdb->get_row(
			$wpdb->prepare( "SELECT field_value FROM $wpnc_custom_field_metas WHERE module = %s AND module_record_id = %d AND custom_fields_id = %d", $module, $module_record_id, $custom_field_id )
		);
		if ( ! empty( $get_data ) ) {
			return $get_data->field_value;
		} else {
			return '';
		}
	}
	/**
     * Retrieves all options (dropdown/radio/checkbox) associated with a custom field ID.
     *
     * This function is redundant as it is functionally identical to
     * `mjschool_get_single_custom_field_dropdown_meta_data`.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $cf_id The ID of the custom field definition.
     * @return array Array of option objects.
     * @since 1.0.0
     */
	function mjschool_get_dropdown_value( $cf_id ) {
		global $wpdb;
		$wpnc_custom_field_dropdown_metas = $wpdb->prefix . 'mjschool_custom_field_dropdown_metas';
		$custom_field_id                  = intval( $cf_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_data = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $wpnc_custom_field_dropdown_metas WHERE custom_fields_id = %d", $custom_field_id )
		);
		return $get_data;
	}
	/**
     * Checks if a custom field is of type 'checkbox'.
     *
     * Retrieves the custom field definition and checks the `field_type`.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $cf_id The ID of the custom field definition.
     * @return int 1 if the field type is 'checkbox', 0 otherwise.
     * @since 1.0.0
     */
	function mjschool_get_checked_checkbox( $cf_id ) {
		global $wpdb;
		$wpnc_custom_fields = $wpdb->prefix . 'mjschool_custom_field';
		$custom_field_id    = intval( $cf_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_data = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $wpnc_custom_fields WHERE id = %d", $custom_field_id )
		);
		if ( ! empty( $get_data ) ) {
			$f_type = $get_data->field_type;
			if ( $f_type === 'checkbox' ) {
				return 1;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
	/**
     * Inserts new custom field meta values for a record in a specific module.
     *
     * Iterates through the provided custom field values (`$custom`) and inserts them into the meta table.
     * Handles checkbox values by imploding the array into a comma-separated string.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  string $module The name of the module (e.g., 'student').
     * @param  array  $custom An array of custom field IDs and their corresponding values.
     * @param  int    $module_record_id The primary ID of the record in the module.
     * @return int|bool The result of the last `$wpdb->insert()` operation.
     * @since 1.0.0
     */
	function mjschool_add_custom_field_metas( $module, $custom, $module_record_id ) {
		global $wpdb;
		$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
		$insert_custom_meta_data = false;
		if ( ! empty( $custom ) && is_array( $custom ) ) {
			foreach ( $custom as $key => $value ) {
				$value                                = $custom[ $key ];
				$checkboxreturn                       = $this->mjschool_get_checked_checkbox( intval( $key ) );
				$custom_meta_data['module']           = sanitize_text_field( $module );
				$custom_meta_data['module_record_id'] = intval( $module_record_id );
				$custom_meta_data['custom_fields_id'] = intval( $key );
				if ( ! empty( $checkboxreturn ) ) {
					$sanitized_values = array_map( 'sanitize_text_field', (array) $value );
					$custom_meta_data['field_value'] = implode( ',', $sanitized_values );
				} else {
					$custom_meta_data['field_value'] = sanitize_text_field( $value );
				}
				$custom_meta_data['created_at'] = date( 'Y-m-d H:i:s' );
				$custom_meta_data['updated_at'] = date( 'Y-m-d H:i:s' );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$insert_custom_meta_data = $wpdb->insert( $wpnc_custom_field_metas, $custom_meta_data );
			}
		}
		return $insert_custom_meta_data;
	}
	/**
     * Checks if a custom field meta record already exists for a given record and field.
     *
     * Used internally by `mjschool_update_custom_field_metas` to determine whether to INSERT or UPDATE.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  string $module The name of the module (e.g., 'student').
     * @param  int    $m_id The primary record ID of the module (e.g., student ID).
     * @param  int    $cf_id The ID of the custom field definition.
     * @return object|null The meta data row object if found, or null otherwise.
     * @since 1.0.0
     */
	function mjschool_check_field_old_or_new( $module, $m_id, $cf_id ) {
		global $wpdb;
		$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
		$module_record_id        = intval( $m_id );
		$custom_field_id         = intval( $cf_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_data = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $wpnc_custom_field_metas WHERE module = %s AND module_record_id = %d AND custom_fields_id = %d", $module, $module_record_id, $custom_field_id )
		);
		return $get_data;
	}
	/**
     * Updates custom field meta values for an existing record.
     *
     * Iterates through field values, checks if a meta record exists, and performs an UPDATE or INSERT accordingly.
     * Handles checkbox values by converting the array to a comma-separated string.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  string $module The name of the module (e.g., 'student').
     * @param  array  $custom An array of custom field IDs and their corresponding values.
     * @param  int    $module_record_id The primary ID of the record in the module.
     * @return int|bool The result of the last database query (UPDATE or INSERT).
     * @since 1.0.0
     */
	function mjschool_update_custom_field_metas( $module, $custom, $module_record_id ) {
		global $wpdb;
		$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
		$update_custom_meta_data = false;
		if ( ! empty( $custom ) && is_array( $custom ) ) {
			foreach ( $custom as $key => $value ) {
				$value                  = $custom[ $key ];
				$key_int                = intval( $key );
				$checkboxreturn         = $this->mjschool_get_checked_checkbox( $key_int );
				$check_field_old_or_new = $this->mjschool_check_field_old_or_new( $module, $module_record_id, $key_int );
				if ( ! empty( $check_field_old_or_new ) ) {
					if ( ! empty( $checkboxreturn ) ) {
						$sanitized_values = array_map( 'sanitize_text_field', (array) $value );
						$field_value = implode( ',', $sanitized_values );
					} else {
						$field_value = sanitize_text_field( $value );
					}
					$updated_at = date( 'Y-m-d H:i:s' );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$update_custom_meta_data = $wpdb->query( $wpdb->prepare( "UPDATE $wpnc_custom_field_metas SET field_value = %s, updated_at = %s WHERE module = %s AND module_record_id = %d AND custom_fields_id = %d", $field_value, $updated_at, $module, intval( $module_record_id ), $key_int ) );
				} else {
					$custom_meta_data['module']           = sanitize_text_field( $module );
					$custom_meta_data['module_record_id'] = intval( $module_record_id );
					$custom_meta_data['custom_fields_id'] = $key_int;
					if ( ! empty( $checkboxreturn ) ) {
						$sanitized_values = array_map( 'sanitize_text_field', (array) $value );
						$custom_meta_data['field_value'] = implode( ',', $sanitized_values );
					} else {
						$custom_meta_data['field_value'] = sanitize_text_field( $value );
					}
					$custom_meta_data['created_at'] = date( 'Y-m-d H:i:s' );
					$custom_meta_data['updated_at'] = date( 'Y-m-d H:i:s' );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$update_custom_meta_data = $wpdb->insert( $wpnc_custom_field_metas, $custom_meta_data );
				}
			}
		}
		return $update_custom_meta_data;
	}
	/**
     * Retrieves all custom field meta values associated with a specific record ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $m_id The primary record ID of the module (e.g., student ID).
     * @return array Array of all custom field meta objects for the record.
     * @since 1.0.0
     */
	function mjschool_get_single_custom_fields_id_meta_value( $m_id ) {
		global $wpdb;
		$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
		$module_record_id        = intval( $m_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_data = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $wpnc_custom_field_metas WHERE module_record_id = %d", $module_record_id )
		);
		return $get_data;
	}
	/**
     * Retrieves all custom field meta values associated with a specific custom field definition ID.
     *
     * This returns all values for one custom field across all records in all modules.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $cf_id The ID of the custom field definition.
     * @return array Array of custom field meta objects.
     * @since 1.0.0
     */
	function mjschool_get_single_field_value_meta_value( $cf_id ) {
		global $wpdb;
		$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
		$custom_field_id         = intval( $cf_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_data = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $wpnc_custom_field_metas WHERE custom_fields_id = %d", $custom_field_id )
		);
		return $get_data;
	}
	/**
     * Retrieves the custom field meta value for a specific record ID and custom field ID.
     *
     * This is functionally similar to `mjschool_get_single_custom_field_meta_value` but omits the module filter
     * and specifically references 'student_id' in the description context.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $id The primary record ID (e.g., student ID).
     * @param  int $cf_id The ID of the custom field definition.
     * @return string The stored field value, or an empty string if no meta is found.
     * @since 1.0.0
     */
	function mjschool_get_single_field_value_meta_value_by_filed_and_student_id( $id, $cf_id ) {
		global $wpdb;
		$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
		$custom_field_id         = intval( $cf_id );
		$student_id              = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$get_data = $wpdb->get_row(
			$wpdb->prepare( "SELECT field_value FROM $wpnc_custom_field_metas WHERE module_record_id = %d AND custom_fields_id = %d", $student_id, $custom_field_id )
		);
		if ( ! empty( $get_data ) ) {
			return $get_data->field_value;
		} else {
			return '';
		}
	}
	/**
	 * Retrieve and render custom fields for a given module.
	 *
	 * This function dynamically loads and displays all custom fields associated with the specified module.
	 * It supports multiple field types such as text, textarea, date, dropdown, checkbox, radio, and file upload.
	 * The function also manages validation rules, file type/size restrictions, and edit modes.
	 *
	 * @param string $module The module name for which to retrieve custom fields (e.g., 'student', 'teacher', 'exam').
	 * @return void Outputs HTML directly.
	 *  @since 1.0.0
	 */ 
	function mjschool_get_custom_field_by_module_callback( $module ) {
		$compact_custom_field = $this->mjschool_get_custom_field_by_module( $module );
		$edit                 = 0;
		if ( isset( $_REQUEST['action'] ) && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) ) {
			$edit = 1;
		}
		if ( ! empty( $compact_custom_field ) ) {
			?>
			<div class="header">
				<h3 class="mjschool-first-header mjschool-margin-top-0px-image"><?php esc_html_e( 'Custom Fields', 'mjschool' ); ?></h3>
			</div>
			<script type="text/javascript">
				(function(jQuery) {
					"use strict";
					// File check function.
					function mjschool_custom_filed_file_check(obj) {
						var fileExtension = jQuery(obj).attr( 'file_types' );
						var fileExtensionArr = fileExtension.split( ',' );
						var file_size = jQuery(obj).attr( 'file_size' );
						var sizeInkb = obj.files[0].size / 1024;
						if (jQuery.inArray(jQuery(obj).val().split( '.' ).pop().toLowerCase(), fileExtensionArr) === -1) {
							alert( "Only " + fileExtension + " formats are allowed.");
							jQuery(obj).val( '' );
						}
					}
					// Make the function accessible globally if needed.
					window.mjschool_custom_filed_file_check = mjschool_custom_filed_file_check;
					jQuery(document).ready(function() {
						// Custom Date Picker.
						jQuery( '.custom_datepicker' ).datepicker({
							dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
							endDate: '+0d',
							autoclose: true,
							changeMonth: true,
							changeYear: true,
							orientation: "bottom"
						});
						jQuery( '.space_validation' ).on( 'keypress', function(e) {
							if (e.which === 32 ) return false;
						});
						// Custom field datepickers.
						jQuery( '.after_or_equal' ).datepicker({
							dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
							minDate: 0,
							changeMonth: true,
							changeYear: true,
							beforeShow: function(textbox, instance) {
								instance.dpDiv.css({
									marginTop: (-textbox.offsetHeight) + 'px'
								});
							}
						});
						jQuery( '.date_equals' ).datepicker({
							dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
							minDate: 0,
							maxDate: 0,
							changeMonth: true,
							changeYear: true,
							beforeShow: function(textbox, instance) {
								instance.dpDiv.css({
									marginTop: (-textbox.offsetHeight) + 'px'
								});
							}
						});
						jQuery( '.before_or_equal' ).datepicker({
							dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
							maxDate: 0,
							changeMonth: true,
							changeYear: true,
							beforeShow: function(textbox, instance) {
								instance.dpDiv.css({
									marginTop: (-textbox.offsetHeight) + 'px'
								});
							}
						});
					});
				})(jQuery);
			</script>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<?php
					foreach ( $compact_custom_field as $custom_field ) {
						$custom_field_value = '';
						if ( $edit && ( isset( $_REQUEST['student_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['teacher_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['teacher_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['supportstaff_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['parent_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['parent_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['class_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['subject_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['subject_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['exam_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['exam_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['hall_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hall_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['grade_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['grade_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['homework_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['homework_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['document_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['document_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['leave_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['leave_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['book_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['book_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['fees_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['fees_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['fees_pay_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['fees_pay_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['income_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['income_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['expense_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['expense_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['tax_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['tax_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['hostel_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['transport_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['transport_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['holiday_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['holiday_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['notice_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['notice_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['event_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['event_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['payment_history_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['payment_history_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						if ( $edit && ( isset( $_REQUEST['notification_id'] ) ) ) {
							$custom_field_id    = $custom_field->id;
							$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['notification_id'] ) ) ) );
							$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
						}
						// Custom Field Validation. //
						$exa              = explode( '|', $custom_field->field_validation );
						$min              = '';
						$max              = '';
						$required         = '';
						$red              = '';
						$limit_value_min  = '';
						$limit_value_max  = '';
						$numeric          = '';
						$alpha            = '';
						$space_validation = '';
						$alpha_space      = '';
						$alpha_num        = '';
						$email            = '';
						$url              = '';
						$minDate          = '';
						$maxDate          = '';
						$file_types       = '';
						$file_size        = '';
						$datepicker_class = '';
						foreach ( $exa as $key => $value ) {
							if ( strpos( $value, 'min' ) !== false ) {
								$min             = $value;
								$limit_value_min = substr( $min, 4 );
							} elseif ( strpos( $value, 'max' ) !== false ) {
								$max             = $value;
								$limit_value_max = substr( $max, 4 );
							} elseif ( strpos( $value, 'required' ) !== false ) {
								$required = 'required';
								$red      = '*';
							} elseif ( strpos( $value, 'numeric' ) !== false ) {
								$numeric = 'onlyNumberSp';
							} elseif ( $value === 'alpha' ) {
								$alpha            = 'onlyLetterSp';
								$space_validation = 'space_validation';
							} elseif ( $value === 'alpha_space' ) {
								$alpha_space = 'onlyLetterSp';
							} elseif ( strpos( $value, 'alpha_num' ) !== false ) {
								$alpha_num = 'onlyLetterNumber';
							} elseif ( strpos( $value, 'email' ) !== false ) {
								$email = 'email';
							} elseif ( strpos( $value, 'url' ) !== false ) {
								$url = 'url';
							} elseif ( strpos( $value, 'after_or_equal:today' ) !== false ) {
								$minDate          = 1;
								$datepicker_class = 'after_or_equal';
							} elseif ( strpos( $value, 'date_equals:today' ) !== false ) {
								$minDate          = $maxDate = 1;
								$datepicker_class = 'date_equals';
							} elseif ( strpos( $value, 'before_or_equal:today' ) !== false ) {
								$maxDate          = 1;
								$datepicker_class = 'before_or_equal';
							} elseif ( strpos( $value, 'file_types' ) !== false ) {
								$types      = $value;
								$file_types = substr( $types, 11 );
							} elseif ( strpos( $value, 'file_upload_size' ) !== false ) {
								$size      = $value;
								$file_size = substr( $size, 17 );
							}
						}
						$option = $this->mjschool_get_dropdown_value( $custom_field->id );
						$data   = 'custom.' . $custom_field->id;
						$datas  = 'custom.' . $custom_field->id;
						if ( $custom_field->field_type === 'text' ) {
							?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input class="form-control hideattar<?php echo esc_attr( $custom_field->form_name ); ?> validate[ <?php if ( ! empty( $required ) ) { echo esc_attr( $required ); ?>, <?php } ?> <?php if ( ! empty( $limit_value_min ) ) { ?> minSize[<?php echo esc_attr( $limit_value_min ); ?>], <?php } if ( ! empty( $limit_value_max ) ) { ?> maxSize[<?php echo esc_attr( $limit_value_max ); ?>], <?php } if ( $numeric != '' || $alpha != '' || $alpha_space != '' || $alpha_num != '' || $email != '' || $url != '' ) { ?> custom[<?php echo esc_attr( $numeric ); echo esc_attr( $alpha ); echo esc_attr( $alpha_space ); echo esc_attr( $alpha_num ); echo esc_attr( $email ); echo esc_attr( $url ); ?>]<?php } ?>] <?php echo esc_attr( $space_validation ); ?>" type="text" name="custom[<?php echo esc_attr( $custom_field->id ); ?>]" id="<?php echo esc_attr( $custom_field->id ); ?>" label="<?php echo esc_attr( $custom_field->field_label ); ?>" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $custom_field_value ); ?>" <?php } ?>>
										<label for="<?php echo esc_attr( $custom_field->id ); ?>"><?php echo esc_html( $custom_field->field_label ); ?><span class="required red"><?php echo esc_html( $red ); ?></span></label>
									</div>
								</div>
							</div>
							<?php
						} elseif ( $custom_field->field_type === 'textarea' ) {
							?>
							<div class="col-md-6 mjschool-note-text-notice">
								<div class="form-group input">
									<div class="col-md-12 mjschool-note-border">
										<div class="form-field">
											<textarea rows="3" class="mjschool-textarea-height-47px form-control hideattar<?php echo esc_attr( $custom_field->form_name ); ?> validate[<?php if ( ! empty( $required ) ) { echo esc_attr( $required ); ?> ,<?php } ?> <?php if ( ! empty( $limit_value_min ) ) { ?> minSize[<?php echo esc_attr( $limit_value_min ); ?>], <?php } if ( ! empty( $limit_value_max ) ) { ?> maxSize[<?php echo esc_attr( $limit_value_max ); ?>], <?php } if ( $numeric != '' || $alpha != '' || $alpha_space != '' || $alpha_num != '' || $email != '' || $url != '' ) { ?> custom[ <?php echo esc_attr( $numeric ); echo esc_attr( $alpha ); echo esc_attr( $alpha_space ); echo esc_attr( $alpha_num ); echo esc_attr( $email ); echo esc_attr( $url ); ?> ]<?php } ?>] <?php echo esc_attr( $space_validation ); ?>" name="custom[<?php echo esc_attr( $custom_field->id ); ?>]" id="<?php echo esc_attr( $custom_field->id ); ?>" label="<?php echo esc_attr( $custom_field->field_label ); ?>"><?php if ( $edit ) { echo esc_textarea( $custom_field_value ); } ?></textarea>
											<span class="mjschool-txt-title-label"></span>
											<label for="photo" class="text-area address"><?php echo esc_html( $custom_field->field_label ); ?><span class="required red"><?php echo esc_html( $red ); ?></span></label>
										</div>
									</div>
								</div>
							</div>
							<?php
						} elseif ( $custom_field->field_type === 'date' ) {
							?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input class="form-control date_picker custom_datepicker <?php echo esc_attr( $datepicker_class ); ?> hideattar<?php echo esc_attr( $custom_field->form_name ); ?> <?php if ( ! empty( $required ) ) { ?> validate[<?php echo esc_attr( $required ); ?>] <?php } ?>" type="text" name="custom[<?php echo esc_attr( $custom_field->id ); ?>]" <?php if ( $edit ) { ?> value="<?php if ( ! empty( $custom_field_value ) ) { echo esc_attr( mjschool_get_date_in_input_box( $custom_field_value ) ); } ?>" <?php } else { ?> value="<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); ?><?php } ?>" id="<?php echo esc_attr( $custom_field->id ); ?>" label="<?php echo esc_attr( $custom_field->field_label ); ?>">
										<label class="date_label"><?php echo esc_html( $custom_field->field_label ); ?><span class="required red"><?php echo esc_html( $red ); ?></span></label>
									</div>
								</div>
							</div>
							<?php
						} elseif ( $custom_field->field_type === 'dropdown' ) {
							?>
							<div class="col-md-6 col-sm-6 input">
								<label for="photo" class="ml-1 mjschool-custom-top-label top"><?php echo esc_html( $custom_field->field_label ); ?><span class="required red"><?php echo esc_html( $red ); ?></span></label>
								<select class="form-control mjschool-standard-category mjschool-line-height-30px  hideattar<?php echo esc_attr( $custom_field->form_name ); ?> <?php if ( ! empty( $required ) ) { ?> validate[<?php echo esc_attr( $required ); ?>] <?php } ?>" name="custom[<?php echo esc_attr( $custom_field->id ); ?>]" id="<?php echo esc_attr( $custom_field->id ); ?>" label="<?php echo esc_attr( $custom_field->field_label ); ?>">
									<option value=""><?php esc_html_e( 'Select', 'mjschool' ); ?></option>
									<?php
									if ( ! empty( $option ) ) {
										foreach ( $option as $options ) {
											?>
											<option value="<?php echo esc_attr( $options->option_label ); ?>" <?php if ( $edit ) { echo selected( $custom_field_value, $options->option_label ); } ?>>
												<?php echo esc_html( $options->option_label ); ?>
											</option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<?php
						} elseif ( $custom_field->field_type === 'checkbox' ) {
							?>
							<div class="col-md-6 mb-3 mjschool-main-custome-field">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div>
												<label class="mjschool-custom-top-label mjschool-margin-left-0"><?php echo esc_html( $custom_field->field_label ); ?><span class="required red"><?php echo esc_html( $red ); ?></span></label>
												<?php
												if ( ! empty( $option ) ) {
													foreach ( $option as $options ) {
														if ( $edit ) {
															$custom_field_value_array = explode( ',', $custom_field_value );
														}
														?>
														<label class="me-2">
															<input type="checkbox" value="<?php echo esc_attr( $options->option_label ); ?>" <?php if ( $edit ) { echo checked( in_array( $options->option_label, $custom_field_value_array ) ); } ?> class="hideattar<?php echo esc_attr( $custom_field->form_name ); ?> <?php if ( ! empty( $required ) ) { ?> validate[<?php echo esc_attr( $required ); ?>] <?php } ?>" name="custom[<?php echo esc_attr( $custom_field->id ); ?>][]">&nbsp;&nbsp;
															<span class="mjschool-span-left-custom mjschool_margin_bottom_negetive_5px"><?php echo esc_html( $options->option_label ); ?></span>
														</label>
														<?php
													}
												}
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
						} elseif ( $custom_field->field_type === 'radio' ) {
							?>
							<div class="col-md-6 mb-3 mjschool-rtl-margin-top-15px">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div class="input-group">
												<label class="mjschool-custom-top-label mjschool-margin-left-0"><?php echo esc_html( $custom_field->field_label ); ?><span class="required red"><?php echo esc_html( $red ); ?></span></label>
												<?php
												if ( ! empty( $option ) ) {
													foreach ( $option as $options ) {
														?>
														<div class="d-inline-block">
															<label class="radio-inline">
																<input type="radio" value="<?php echo esc_attr( $options->option_label ); ?>" <?php if ( $edit ) { echo checked( $options->option_label, $custom_field_value ); } ?> name="custom[<?php echo esc_attr( $custom_field->id ); ?>]" class="mjschool-custom-control-input hideattar<?php echo esc_attr( $custom_field->form_name ); ?> <?php if ( ! empty( $required ) ) { ?> validate[<?php echo esc_attr( $required ); ?>] <?php } ?>" id="<?php echo esc_attr( $options->option_label ); ?>">
																<?php echo esc_html( $options->option_label ); ?>
															</label>&nbsp;&nbsp;
														</div>
														<?php
													}
												}
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
						} elseif ( $custom_field->field_type === 'file' ) {
							?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
										<label for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php echo esc_html( $custom_field->field_label ); ?><span class="required red"><?php echo esc_html( $red ); ?></span></label>
										<div class="col-sm-12 mjschool-display-flex">
											<input type="hidden" name="hidden_custom_file[<?php echo esc_attr( $custom_field->id ); ?>]" value="<?php if ( $edit ) { echo esc_attr( $custom_field_value ); } ?>">
											<input type="file" onchange="mjschool_custom_filed_file_check(this);" Class="form-control file hideattar <?php echo esc_attr( $custom_field->form_name ); if ( $edit ) { if ( ! empty( $required ) ) { if ( $custom_field_value === '' ) { ?> validate[<?php echo esc_attr( $required ); ?>] <?php } } } elseif ( ! empty( $required ) ) { ?> validate[<?php echo esc_attr( $required ); ?>] <?php } ?>" name="custom_file[<?php echo esc_attr( $custom_field->id ); ?>]" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $custom_field_value ); ?>" <?php } ?> id="<?php echo esc_attr( $custom_field->id ); ?>" file_types="<?php echo esc_attr( $file_types ); ?>" file_size="<?php echo esc_attr( $file_size ); ?>">
										</div>
										<?php
										if ( ! empty( $custom_field_value ) ) {
											?>
											<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
												<a target="blank" class="mjschool-status-read btn btn-default" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>">
													<i class="fa fa-download"></i>&nbsp;&nbsp;<?php esc_html_e( 'Download', 'mjschool' ); ?>
												</a>
											</div>
											<?php
										}
										?>
									</div>
								</div>
							</div>
							<?php
						}
					}
					?>
				</div>
			</div>
			<?php
		}
	}
	/**
	 * Helper function to sanitize custom field array data.
	 *
	 * @param array $custom_data The custom field data array.
	 * @return array Sanitized array.
	 * @since 1.0.0
	 */
	private function mjschool_sanitize_custom_array( $custom_data ) {
		if ( ! is_array( $custom_data ) ) {
			return array();
		}
		$sanitized = array();
		foreach ( $custom_data as $key => $value ) {
			$key_int = intval( $key );
			if ( is_array( $value ) ) {
				$sanitized[ $key_int ] = array_map( 'sanitize_text_field', $value );
			} else {
				$sanitized[ $key_int ] = sanitize_text_field( $value );
			}
		}
		return $sanitized;
	}
	/**
     * Inserts custom field data, including handling file uploads, for a specific module record.
     *
     * This function processes two types of custom field data:
     * 1. File uploads (`$_FILES['custom_file']`): Files are uploaded via the helper function
     * `mjschool_load_documets_new()`, and the resulting file path/name is saved as meta data.
     * 2. Standard fields (`$_POST['custom']`): Non-file field data is passed to the
     * `mjschool_add_custom_field_metas()` method for insertion.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  string $module The name of the module (e.g., 'student', 'teacher') being updated.
     * @param  int    $module_id The primary record ID of the module (e.g., student ID).
     * @return void The function performs database insertion and file uploads but does not return a value.
     * @since 1.0.0
     */
	function mjschool_insert_custom_field_data_module_wise( $module, $module_id ) {
		$custom_field_file_array = array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File data is handled by WordPress upload functions
		if ( ! empty( $_FILES['custom_file']['name'] ) ) {
			$count_array = count( $_FILES['custom_file']['name'] );
			for ( $a = 0; $a < $count_array; $a++ ) {
				foreach ( $_FILES['custom_file'] as $image_key => $image_val ) {
					foreach ( $image_val as $image_key1 => $image_val2 ) {
						if ( $_FILES['custom_file']['name'][ $image_key1 ] != '' ) {
							$custom_file_array[ intval( $image_key1 ) ] = array(
								'name'     => sanitize_file_name( $_FILES['custom_file']['name'][ $image_key1 ] ),
								'type'     => sanitize_mime_type( $_FILES['custom_file']['type'][ $image_key1 ] ),
								'tmp_name' => $_FILES['custom_file']['tmp_name'][ $image_key1 ],
								'error'    => intval( $_FILES['custom_file']['error'][ $image_key1 ] ),
								'size'     => intval( $_FILES['custom_file']['size'][ $image_key1 ] ),
							);
						}
					}
				}
			}
			if ( ! empty( $custom_file_array ) ) {
				foreach ( $custom_file_array as $key => $value ) {
					global $wpdb;
					$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
					$get_file_name           = $custom_file_array[ $key ]['name'];
					$custom_field_file_value = mjschool_load_documets_new( $value, $value, $get_file_name );
					// Add File in Custom Field Meta.//
					$custom_meta_data['module']           = sanitize_text_field( $module );
					$custom_meta_data['module_record_id'] = intval( $module_id );
					$custom_meta_data['custom_fields_id'] = intval( $key );
					$custom_meta_data['field_value']      = sanitize_text_field( $custom_field_file_value );
					$custom_meta_data['created_at']       = date( 'Y-m-d H:i:s' );
					$custom_meta_data['updated_at']       = date( 'Y-m-d H:i:s' );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$insert_custom_meta_data = $wpdb->insert( $wpnc_custom_field_metas, $custom_meta_data );
				}
			}
		}
		// Sanitize custom POST data.
		$custom_post_data = isset( $_POST['custom'] ) ? $this->mjschool_sanitize_custom_array( wp_unslash( $_POST['custom'] ) ) : array();
		$add_custom_field = $this->mjschool_add_custom_field_metas( $module, $custom_post_data, $module_id );
	}
	/**
     * Updates custom field data, including handling file uploads, for a specific module record.
     *
     * It first processes file uploads (INSERTs new files or UPDATEs existing ones)
     * and then delegates standard field updates to another method.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param string $module The name of the module (e.g., 'student', 'teacher') being updated.
     * @param int    $module_id The primary record ID of the module (e.g., student ID).
     * @return void
     * @since 1.0.0
     */
	function mjschool_update_custom_field_data_module_wise( $module, $module_id ) {
		$custom_field_file_array = array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File data is handled by WordPress upload functions
		if ( ! empty( $_FILES['custom_file']['name'] ) ) {
			$count_array = count( $_FILES['custom_file']['name'] );
			for ( $a = 0; $a < $count_array; $a++ ) {
				foreach ( $_FILES['custom_file'] as $image_key => $image_val ) {
					foreach ( $image_val as $image_key1 => $image_val2 ) {
						if ( $_FILES['custom_file']['name'][ $image_key1 ] != '' ) {
							$custom_file_array[ intval( $image_key1 ) ] = array(
								'name'     => sanitize_file_name( $_FILES['custom_file']['name'][ $image_key1 ] ),
								'type'     => sanitize_mime_type( $_FILES['custom_file']['type'][ $image_key1 ] ),
								'tmp_name' => $_FILES['custom_file']['tmp_name'][ $image_key1 ],
								'error'    => intval( $_FILES['custom_file']['error'][ $image_key1 ] ),
								'size'     => intval( $_FILES['custom_file']['size'][ $image_key1 ] ),
							);
						}
					}
				}
			}
			// Sanitize hidden_custom_file array.
			$file_value = isset( $_REQUEST['hidden_custom_file'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['hidden_custom_file'] ) ) : array();
			foreach ( $file_value as $filed_key => $filed_val ) {
				if ( $filed_val != '' ) {
					if ( ! empty( $custom_file_array ) ) {
						foreach ( $custom_file_array as $key => $value ) {
							global $wpdb;
							$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
							$get_file_name           = $custom_file_array[ $key ]['name'];
							$custom_field_file_value = mjschool_load_documets_new( $value, $value, $get_file_name );
							// Add File in Custom Field Meta.//
							$updated_at = date( 'Y-m-d H:i:s' );
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$update_custom_meta_data = $wpdb->query( $wpdb->prepare( "UPDATE $wpnc_custom_field_metas SET field_value = %s, updated_at = %s WHERE module = %s AND module_record_id = %d AND custom_fields_id = %d", sanitize_text_field( $custom_field_file_value ), $updated_at, sanitize_text_field( $module ), intval( $module_id ), intval( $key ) ) );
						}
					}
				} elseif ( ! empty( $custom_file_array ) ) {
					foreach ( $custom_file_array as $key => $value ) {
						global $wpdb;
						$wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
						$get_file_name           = $custom_file_array[ $key ]['name'];
						$custom_field_file_value = mjschool_load_documets_new( $value, $value, $get_file_name );
						// Add File in Custom Field Meta.//
						$custom_meta_data['module']           = sanitize_text_field( $module );
						$custom_meta_data['module_record_id'] = intval( $module_id );
						$custom_meta_data['custom_fields_id'] = intval( $key );
						$custom_meta_data['field_value']      = sanitize_text_field( $custom_field_file_value );
						$custom_meta_data['created_at']       = date( 'Y-m-d H:i:s' );
						$custom_meta_data['updated_at']       = date( 'Y-m-d H:i:s' );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$insert_custom_meta_data = $wpdb->insert( $wpnc_custom_field_metas, $custom_meta_data );
					}
				}
			}
		}
		// Sanitize custom POST data.
		$custom_post_data = isset( $_POST['custom'] ) ? $this->mjschool_sanitize_custom_array( wp_unslash( $_POST['custom'] ) ) : array();
		$update_custom_field = $this->mjschool_update_custom_field_metas( $module, $custom_post_data, $module_id );
	}
	/**
     * Renders a section on a detail page to display inserted custom field data for a module.
     *
     * It dynamically determines the module record ID from various $_REQUEST parameters.
     * It handles formatting for 'date' fields and provides a download link for 'file' fields.
     *
     * @param string $module The name of the module (e.g., 'student', 'teacher').
     * @return void Outputs HTML directly.
     * @since 1.0.0
     */
	function mjschool_show_inserted_customfield_data_in_datail_page( $module ) {
		$user_custom_field = $this->mjschool_get_custom_field_by_module( $module );
		if ( ! empty( $user_custom_field ) ) {
			?>
			<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs mjschool-rtl-custom-padding-0px">
				<div class="mjschool-guardian-div">
					<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Other Information', 'mjschool' ); ?> </label>
					<div class="row">
						<?php
						foreach ( $user_custom_field as $custom_field ) {
							$custom_field_value = '';
							if ( isset( $_REQUEST['id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							if ( isset( $_REQUEST['student_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							if ( isset( $_REQUEST['teacher_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['teacher_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							if ( isset( $_REQUEST['supportstaff_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							if ( isset( $_REQUEST['parent_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['parent_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							if ( isset( $_REQUEST['class_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							if ( isset( $_REQUEST['subject_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['subject_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							if ( isset( $_REQUEST['book_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['book_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							if ( isset( $_REQUEST['hostel_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							?>
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
								<p class="mjschool-view-page-header-labels"> <?php echo esc_html( $custom_field->field_label ); ?></p>
								<?php
								if ( $custom_field->field_type === 'date' ) {
									?>
									<p class="mjschool-view-page-header-labels">
										<?php
										if ( ! empty( $custom_field_value ) ) {
											echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
									</p>
									<?php
								} elseif ( $custom_field->field_type === 'file' ) {
									if ( ! empty( $custom_field_value ) ) {
										?>
										<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>" download="CustomFieldfile">
											<button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
										</a>
										<?php
									} else {
										esc_html_e( 'N/A', 'mjschool' );
									}
								} else {
									?>
									<p class="mjschool-label-value">
										<?php
										if ( ! empty( $custom_field_value ) ) {
											echo esc_html( $custom_field_value );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
									</p>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}
	}
	/**
     * Renders a section to display inserted custom field data on a receipt/payment detail page.
     *
     * The function determines the record ID by checking for 'payment_id' or 'receipt_id'
     * in the global $_REQUEST array, decrypting and casting the value as an integer.
     *
     * @param string $module The name of the module (e.g., 'fees_payment', 'receipt').
     * @return void Outputs HTML directly.
     * @since 1.0.0
     */
	function mjschool_show_inserted_customfield_receipt( $module ) {
		$user_custom_field = $this->mjschool_get_custom_field_by_module( $module );
		if ( ! empty( $user_custom_field ) ) {
			?>
			<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs mjschool-rtl-custom-padding-0px">
				<div class="mjschool-guardian-div">
					<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Other Information', 'mjschool' ); ?> </label>
					<div class="row">
						<?php
						foreach ( $user_custom_field as $custom_field ) {
							$custom_field_value = '';
							if ( isset( $_REQUEST['payment_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['payment_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							if ( isset( $_REQUEST['receipt_id'] ) ) {
								$custom_field_id    = $custom_field->id;
								$module_record_id   = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['receipt_id'] ) ) ) );
								$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
							}
							?>
							<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px">
								<?php
								if ( $custom_field->field_type === 'date' ) {
									?>
									<p class="mjschool-label-value">
										<strong><?php echo esc_html( $custom_field->field_label ); ?>:</strong>
										<?php echo ! empty( $custom_field_value ) ? esc_html( mjschool_get_date_in_input_box( $custom_field_value ) ) : esc_html__( 'N/A', 'mjschool' ); ?>
									</p>
									<?php
								} elseif ( $custom_field->field_type === 'file' ) {
									?>
									<p class="mjschool-label-value">
										<strong><?php echo esc_html( $custom_field->field_label ); ?>:</strong>
										<?php if ( ! empty( $custom_field_value ) ) { ?>
											<a target="_blank" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>" download="CustomFieldfile">
												<button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?> </button>
											</a>
											<?php
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
									</p>
									<?php
								} else {
									?>
									<p class="mjschool-label-value">
										<strong><?php echo esc_html( $custom_field->field_label ); ?>:</strong>
										<?php echo ! empty( $custom_field_value ) ? esc_html( $custom_field_value ) : esc_html__( 'N/A', 'mjschool' ); ?>
									</p>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}
	}
	/**
     * Renders custom field data for display inside a popup/modal window.
     *
     * This function retrieves the custom field values for a specific module record
     * and formats them with labels and values side-by-side.
     *
     * @param string $module The name of the module (e.g., 'student', 'fees_payment').
     * @param int    $module_id The numeric ID of the record whose data is to be displayed.
     * @return void Outputs HTML directly.
     * @since 1.0.0
     */
	function mjschool_show_inserted_custom_field_data_in_popup( $module, $module_id ) {
		$user_custom_field = $this->mjschool_get_custom_field_by_module( $module );
		if ( ! empty( $user_custom_field ) ) {
			foreach ( $user_custom_field as $custom_field ) {
				$custom_field_id    = $custom_field->id;
				$module_record_id   = intval( $module_id );
				$custom_field_value = $this->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
				?>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"><?php echo esc_html( $custom_field->field_label ); ?></label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( $custom_field->field_type === 'date' ) {
							if ( ! empty( $custom_field_value ) ) {
								echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
						} elseif ( $custom_field->field_type === 'file' ) {
							if ( ! empty( $custom_field_value ) ) {
								?>
								<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>" download="CustomFieldfile">
									<button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
								</a>
								<?php
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
						} elseif ( ! empty( $custom_field_value ) ) {
							echo esc_html( $custom_field_value );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<?php
			}
		}
	}
}
?>