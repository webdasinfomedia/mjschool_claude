<?php
/**
 * School Management Hostel Management Class.
 *
 * This file contains the Mjschool_Hostel class, which handles
 * CRUD operations for hostels, rooms, and beds using custom database tables.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Mjschool_Hostel Class.
 *
 * Manages database functions for hostels, rooms, beds, and bed assignment.
 *
 * @since 1.0.0
 */
class Mjschool_Hostel {
	/**
	 * Inserts a new hostel record or updates an existing one.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  array $data Array of hostel data, including 'hostel_name', 'hostel_intake', etc.
	 * @return int|false The inserted ID on insert, or the number of affected rows on update, or false on error.
	 */
	public function mjschool_insert_hostel( $data ) {
		global $wpdb;
		$table_mjschool_hostel          = $wpdb->prefix . 'mjschool_hostel';
		$hostel_data['hostel_name']     = isset( $data['hostel_name'] ) ? sanitize_text_field( wp_unslash( $data['hostel_name'] ) ) : '';
		$hostel_data['hostel_type']     = isset( $data['hostel_type'] ) ? sanitize_text_field( wp_unslash( $data['hostel_type'] ) ) : '';
		$hostel_data['hostel_address']  = isset( $data['hostel_address'] ) ? sanitize_text_field( wp_unslash( $data['hostel_address'] ) ) : '';
		$hostel_data['hostel_intake']   = isset( $data['hostel_intake'] ) ? intval( $data['hostel_intake'] ) : 0;
		$hostel_data['Description']     = isset( $data['Description'] ) ? sanitize_textarea_field( wp_unslash( $data['Description'] ) ) : '';
		$page_name                      = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$action                         = isset( $data['action'] ) ? sanitize_text_field( wp_unslash( $data['action'] ) ) : '';
		if ( $action === 'edit' ) {
			$hostel_data['updated_by']   = get_current_user_id();
			$hostel_data['updated_date'] = date( 'Y-m-d' );
			$hostel_id['id']             = isset( $data['hostel_id'] ) ? intval( $data['hostel_id'] ) : 0;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->update( $table_mjschool_hostel, $hostel_data, $hostel_id );
			$hostel = $hostel_data['hostel_name'];
			mjschool_append_audit_log( '' . esc_html__( 'Hostel Updated', 'mjschool' ) . '( ' . esc_html( $hostel ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', $page_name );
			return $result;
		} else {
			$hostel_data['created_by']   = get_current_user_id();
			$hostel_data['created_date'] = date( 'Y-m-d' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_mjschool_hostel, $hostel_data );
			$ids    = $wpdb->insert_id;
			$hostel = $hostel_data['hostel_name'];
			mjschool_append_audit_log( '' . esc_html__( 'Hostel Added', 'mjschool' ) . '( ' . esc_html( $hostel ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', $page_name );
			return $ids;
		}
	}
	/**
	 * Retrieves a single hostel record by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the hostel.
	 * @return object|null The hostel record object, or null otherwise.
	 */
	public function mjschool_get_hostel_by_id( $id ) {
		global $wpdb;
		$table_mjschool_hostel = $wpdb->prefix . 'mjschool_hostel';
		$hostel_id             = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_hostel WHERE id = %d", $hostel_id ) );
		return $result;
	}
	/**
	 * Deletes a hostel record by its ID and logs the action.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the hostel to delete.
	 * @return int|false The number of rows deleted (1 on success), or false on error.
	 */
	public function mjschool_delete_hostel( $id ) {
		global $wpdb;
		$table_mjschool_hostel = $wpdb->prefix . 'mjschool_hostel';
		$hostel_id             = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$hostel      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_hostel where id=%d", $hostel_id ) );
		$hostel_name = isset( $hostel->hostel_name ) ? $hostel->hostel_name : '';
		$page_name   = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Hostel Deleted', 'mjschool' ) . '( ' . esc_html( $hostel_name ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		return $wpdb->query( $wpdb->prepare( "DELETE FROM $table_mjschool_hostel WHERE id= %d", $hostel_id ) );
	}
	/**
	 * Retrieves all hostel records.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return array|object|null An array of hostel records, or null if none are found.
	 */
	public function mjschool_get_all_hostel() {
		global $wpdb;
		$table_mjschool_hostel = $wpdb->prefix . 'mjschool_hostel';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM $table_mjschool_hostel" );
		return $result;
	}
	/**
	 * Inserts a new room record or updates an existing one.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  array $data Array of room data.
	 * @return int|false The inserted ID on insert, or the number of affected rows on update, or false on error.
	 */
	public function mjschool_insert_room( $data ) {
		if ( ! empty( $data['mjschool_hostel_room_facilities'] ) && is_array( $data['mjschool_hostel_room_facilities'] ) ) {
			$facilities_sanitized = array_map( 'sanitize_text_field', wp_unslash( $data['mjschool_hostel_room_facilities'] ) );
			$facilities           = wp_json_encode( $facilities_sanitized );
		} else {
			$facilities = '';
		}
		global $wpdb;
		$table_mjschool_room           = $wpdb->prefix . 'mjschool_room';
		$room_data['room_unique_id']   = isset( $data['room_unique_id'] ) ? sanitize_text_field( wp_unslash( $data['room_unique_id'] ) ) : '';
		$room_data['hostel_id']        = isset( $data['hostel_id'] ) ? intval( $data['hostel_id'] ) : 0;
		$room_data['room_status']      = '0';
		$room_data['room_category']    = isset( $data['room_category'] ) ? intval( $data['room_category'] ) : 0;
		$room_data['beds_capacity']    = isset( $data['beds_capacity'] ) ? intval( $data['beds_capacity'] ) : 0;
		$room_data['room_description'] = isset( $data['room_description'] ) ? sanitize_textarea_field( wp_unslash( $data['room_description'] ) ) : '';
		$room_data['facilities']       = $facilities;
		$page_name                     = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$action                        = isset( $data['action'] ) ? sanitize_text_field( wp_unslash( $data['action'] ) ) : '';
		if ( $action === 'edit_room' ) {
			$room_data['updated_by']   = get_current_user_id();
			$room_data['updated_date'] = date( 'Y-m-d' );
			$room_id['id']             = isset( $data['room_id'] ) ? intval( $data['room_id'] ) : 0;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->update( $table_mjschool_room, $room_data, $room_id );
			$room   = $room_data['room_unique_id'];
			mjschool_append_audit_log( '' . esc_html__( 'Room Updated', 'mjschool' ) . '( ' . esc_html( $room ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', $page_name );
			return $result;
		} else {
			$room_data['created_by']   = get_current_user_id();
			$room_data['created_date'] = date( 'Y-m-d' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_mjschool_room, $room_data );
			$room   = $room_data['room_unique_id'];
			mjschool_append_audit_log( '' . esc_html__( 'Room Added', 'mjschool' ) . '( ' . esc_html( $room ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', $page_name );
			return $result;
		}
	}
	/**
	 * Deletes a room record by its ID and logs the action.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the room to delete.
	 * @return int|false The number of rows deleted (1 on success), or false on error.
	 */
	public function mjschool_delete_room( $id ) {
		global $wpdb;
		$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
		$room_id             = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$room_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_room where id=%d", $room_id ) );
		$room      = isset( $room_data->room_unique_id ) ? $room_data->room_unique_id : '';
		$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Room Deleted', 'mjschool' ) . '( ' . esc_html( $room ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		return $wpdb->query( $wpdb->prepare( "DELETE FROM $table_mjschool_room WHERE id= %d", $room_id ) );
	}
	/**
	 * Retrieves a single room record by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the room.
	 * @return object|null The room record object, or null otherwise.
	 */
	public function mjschool_get_room_by_id( $id ) {
		global $wpdb;
		$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
		$room_id             = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_room where id=%d", $room_id ) );
		return $result;
	}
	/**
	 * Retrieves all room records.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return array|object|null An array of room records, or null if none are found.
	 */
	public function mjschool_get_all_room() {
		global $wpdb;
		$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM $table_mjschool_room" );
		return $result;
	}
	/**
	 * Inserts a new bed record or updates an existing one.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  array $data Array of bed data.
	 * @return int|false The inserted ID on insert, or the number of affected rows on update, or false on error.
	 */
	public function mjschool_insert_bed( $data ) {
		global $wpdb;
		$table_mjschool_beds         = $wpdb->prefix . 'mjschool_beds';
		$bed_data['bed_unique_id']   = isset( $data['bed_unique_id'] ) ? sanitize_text_field( wp_unslash( $data['bed_unique_id'] ) ) : '';
		$bed_data['room_id']         = isset( $data['room_id'] ) ? intval( $data['room_id'] ) : 0;
		$bed_data['bed_charge']      = isset( $data['bed_charge'] ) ? floatval( $data['bed_charge'] ) : 0;
		$bed_data['bed_description'] = isset( $data['bed_description'] ) ? sanitize_textarea_field( wp_unslash( $data['bed_description'] ) ) : '';
		$page_name                   = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$action                      = isset( $data['action'] ) ? sanitize_text_field( wp_unslash( $data['action'] ) ) : '';
		if ( $action === 'edit_bed' ) {
			$bed_data['updated_by']   = get_current_user_id();
			$bed_data['updated_date'] = date( 'Y-m-d' );
			$bed_id['id']             = isset( $data['bed_id'] ) ? intval( $data['bed_id'] ) : 0;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->update( $table_mjschool_beds, $bed_data, $bed_id );
			$bed    = $bed_data['bed_unique_id'];
			mjschool_append_audit_log( '' . esc_html__( 'Bed Updated', 'mjschool' ) . '( ' . esc_html( $bed ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', $page_name );
			return $result;
		} else {
			$bed_data['bed_status']   = '0';
			$bed_data['created_by']   = get_current_user_id();
			$bed_data['created_date'] = date( 'Y-m-d' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_mjschool_beds, $bed_data );
			$bed    = $bed_data['bed_unique_id'];
			mjschool_append_audit_log( '' . esc_html__( 'Bed Added', 'mjschool' ) . '( ' . esc_html( $bed ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', $page_name );
			return $result;
		}
	}
	/**
	 * Retrieves a single bed record by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the bed.
	 * @return object|null The bed record object, or null otherwise.
	 */
	public function mjschool_get_bed_by_id( $id ) {
		global $wpdb;
		$table_mjschool_beds = $wpdb->prefix . 'mjschool_beds';
		$bed_id              = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_beds where id=%d", $bed_id ) );
		return $result;
	}
	/**
	 * Retrieves all bed records for a specific room ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the room.
	 * @return array|object|null An array of bed records, or null if none are found.
	 */
	public function mjschool_get_all_bed_by_room_id( $id ) {
		global $wpdb;
		$table_mjschool_beds = $wpdb->prefix . 'mjschool_beds';
		$room_id             = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_beds where room_id=%d", $room_id ) );
		return $result;
	}
	/**
	 * Deletes a bed record by its ID and logs the action.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the bed to delete.
	 * @return int|false The number of rows deleted (1 on success), or false on error.
	 */
	public function mjschool_delete_bed( $id ) {
		global $wpdb;
		$table_mjschool_beds = $wpdb->prefix . 'mjschool_beds';
		$bed_id              = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$event     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_beds where id=%d", $bed_id ) );
		$bed       = isset( $event->bed_unique_id ) ? $event->bed_unique_id : '';
		$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Bed Deleted', 'mjschool' ) . '( ' . esc_html( $bed ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		return $wpdb->query( $wpdb->prepare( "DELETE FROM $table_mjschool_beds WHERE id= %d", $bed_id ) );
	}
	/**
	 * Retrieves an assigned bed record by its bed ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the bed.
	 * @return object|null The assigned bed record object, or null otherwise.
	 */
	public function mjschool_get_assign_bed_by_id( $id ) {
		global $wpdb;
		$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
		$bed_id                     = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_assign_beds where bed_id=%d", $bed_id ) );
		return $result;
	}
	/**
	 * Retrieves the student ID assigned to a specific bed.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the bed.
	 * @return object|null The student ID column object, or null otherwise.
	 */
	public function mjschool_get_assign_bed_student_by_id( $id ) {
		global $wpdb;
		$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
		$bed_id                     = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT student_id FROM $table_mjschool_assign_beds where bed_id=%d", $bed_id ) );
		return $result;
	}
	/**
	 * Retrieves the hostel ID associated with a room ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $room_id The unique ID of the room.
	 * @return int|null The hostel ID, or null if the room is not found.
	 */
	public function mjschool_get_hostel_id_by_room_id( $room_id ) {
		global $wpdb;
		$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
		$room_id             = intval( $room_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_room where id=%d", $room_id ) );
		if ( $result ) {
			return intval( $result->hostel_id );
		}
		return null;
	}
	/**
	 * Assigns/updates a room and bed assignment for students.
	 *
	 * This function iterates through a list of student/bed/room assignments,
	 * updates the assignment table, updates the bed status in the beds table,
	 * and sends out notifications.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  array $data Array of assignment data, typically containing nested arrays for students.
	 * @return int|false The result of the last database operation, or false.
	 */
	public function mjschool_assign_room( $data ) {
		global $wpdb;
		$table_mjschool_beds        = $wpdb->prefix . 'mjschool_beds';
		$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
		$hostel_id_raw              = isset( $data['hostel_id'] ) ? sanitize_text_field( wp_unslash( $data['hostel_id'] ) ) : '';
		$hostel_id                  = intval( mjschool_decrypt_id( $hostel_id_raw ) );
		$page_name                  = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$result                     = false;
		$result_update              = false;
		if ( ! empty( $data['room_id_new'] ) && is_array( $data['room_id_new'] ) ) {
			foreach ( $data['room_id_new'] as $key => $value ) {
				$student_unique = isset( $data['student_id'][ $key ] ) ? intval( $data['student_id'][ $key ] ) : 0;
				if ( ! empty( $student_unique ) ) {
					$bed_id          = isset( $data['bed_id'][ $key ] ) ? intval( $data['bed_id'][ $key ] ) : 0;
					$bed_data        = $this->mjschool_get_bed_by_id( $bed_id );
					$assign_bed_data = $this->mjschool_get_assign_bed_by_id( $bed_id );
					$bed_unique_id   = isset( $data['bed_unique_id'][ $key ] ) ? sanitize_text_field( wp_unslash( $data['bed_unique_id'][ $key ] ) ) : '';
					$assign_date_raw = isset( $data['assign_date'][ $key ] ) ? sanitize_text_field( wp_unslash( $data['assign_date'][ $key ] ) ) : date( 'Y-m-d' );
					if ( ! empty( $assign_bed_data ) ) {
						$assign_bed_id['id']          = $assign_bed_data->id;
						$assign_data['hostel_id']     = $hostel_id;
						$assign_data['room_id']       = intval( $value );
						$assign_data['bed_id']        = $bed_id;
						$assign_data['bed_unique_id'] = $bed_unique_id;
						$assign_data['student_id']    = $student_unique;
						$assign_data['assign_date']   = date( 'Y-m-d', strtotime( $assign_date_raw ) );
						$assign_data['created_date']  = date( 'Y-m-d' );
						$assign_data['created_by']    = get_current_user_id();
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$result = $wpdb->update( $table_mjschool_assign_beds, $assign_data, $assign_bed_id );
						mjschool_append_audit_log( '' . esc_html__( 'Assign Room Updated', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'edit', $page_name );
						if ( $result ) {
							$bed_data_update['bed_status'] = 1;
							$assign_bed_id_update['id']    = $assign_bed_id;
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$result_update = $wpdb->update( $table_mjschool_beds, $bed_data_update, $assign_bed_id_update );
						}
					} else {
						$assign_data['hostel_id']     = $hostel_id;
						$assign_data['room_id']       = intval( $value );
						$assign_data['bed_id']        = $bed_id;
						$assign_data['bed_unique_id'] = $bed_unique_id;
						$assign_data['student_id']    = $student_unique;
						$assign_data['assign_date']   = date( 'Y-m-d', strtotime( $assign_date_raw ) );
						$assign_data['created_date']  = date( 'Y-m-d' );
						$assign_data['created_by']    = get_current_user_id();
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$result = $wpdb->insert( $table_mjschool_assign_beds, $assign_data );
						mjschool_append_audit_log( '' . esc_html__( 'Assign Bed Added', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'insert', $page_name );
						if ( $result ) {
							// ---------- Hostel bed assigned mail. ---------//
							$bed_data                   = $this->mjschool_get_bed_by_id( $bed_id );
							$currency_symbol            = mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) );
							$userdata                   = get_userdata( $student_unique );
							$string                     = array();
							$string['{{student_name}}'] = esc_html( mjschool_get_display_name( $student_unique ) );
							$string['{{hostel_name}}']  = esc_html( mjschool_hostel_name_by_id( $hostel_id_raw ) );
							$string['{{room_id}}']      = esc_html( mjschool_get_room_unique_id_by_room_id( intval( $value ) ) );
							$string['{{bed_id}}']       = esc_html( $bed_unique_id );
							$string['{{bed_charge}}']   = esc_html( html_entity_decode( $currency_symbol ) . '' . floatval( $bed_data->bed_charge ) );
							$string['{{school_name}}']  = esc_html( get_option( 'mjschool_name' ) );
							$MsgContent                 = get_option( 'mjschool_bed_content' );
							$MsgSubject                 = get_option( 'mjschool_bed_subject' );
							$message                    = mjschool_string_replacement( $string, $MsgContent );
							$MsgSubject                 = mjschool_string_replacement( $string, $MsgSubject );
							$email                      = $userdata->user_email;
							mjschool_send_mail( $email, $MsgSubject, $message );
							/* Start send push notification. */
							$device_token[]    = get_user_meta( $student_unique, 'token_id', true );
							$title             = esc_html__( 'New Notification For Assign Bed.', 'mjschool' );
							$text              = esc_html__( 'You have been assigned new Bed', 'mjschool' ) . ' ' . esc_html( $bed_unique_id );
							$notification_data = array(
								'registration_ids' => $device_token,
								'data'             => array(
									'title' => $title,
									'body'  => $text,
									'type'  => 'Message',
								),
							);
							$json              = wp_json_encode( $notification_data );
							$message           = mjschool_send_push_notification( $json );
							/* End send push notification. */
						}
						$assign_bed_id_update['id']    = $bed_id;
						$bed_data_update['bed_status'] = 1;
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$result_update = $wpdb->update( $table_mjschool_beds, $bed_data_update, $assign_bed_id_update );
					}
				}
			}
		}
		return $result;
	}
	/**
	 * Deletes a bed assignment record and updates the bed status.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $rid The room ID.
	 * @param  int $bid The bed ID.
	 * @param  int $id  The student ID.
	 * @return int|false The number of affected rows from the bed status update, or false on error.
	 */
	public function mjschool_delete_assigned_bed( $rid, $bid, $id ) {
		$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Assign Bed Deleted', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		global $wpdb;
		$room_id                    = intval( $rid );
		$bed_id                     = intval( $bid );
		$student_id                 = intval( $id );
		$table_mjschool_beds        = $wpdb->prefix . 'mjschool_beds';
		$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->query(
			$wpdb->prepare( "DELETE FROM $table_mjschool_assign_beds WHERE room_id=%d AND bed_id=%d AND student_id =%d", $room_id, $bed_id, $student_id )
		);
		$result_update = false;
		if ( $result ) {
			$assign_bed_id_update['id']    = $bed_id;
			$bed_data_update['bed_status'] = 0;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result_update = $wpdb->update( $table_mjschool_beds, $bed_data_update, $assign_bed_id_update );
		}
		return $result_update;
	}
	/**
	 * Calculates the remaining bed capacity for a specific room.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $rid The room ID.
	 * @return int The remaining bed capacity.
	 */
	public function mjschool_remaining_bed_capacity( $rid ) {
		global $wpdb;
		$room_id                    = intval( $rid );
		$table_mjschool_room        = $wpdb->prefix . 'mjschool_room';
		$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
		// Get bed capacity from room table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$beds_capacity = $wpdb->get_var( $wpdb->prepare( "SELECT beds_capacity FROM $table_mjschool_room WHERE id = %d", $room_id ) );
		// Get assigned beds data count using room ID.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$assign_beds_row    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_assign_beds WHERE room_id = %d", $room_id ) );
		$room_capacity      = intval( $beds_capacity );
		$assign_beds        = count( $assign_beds_row );
		$remaining_capacity = $room_capacity - $assign_beds;
		return $remaining_capacity;
	}
	/**
	 * Retrieves all assigned beds for a specific room ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $rid The room ID.
	 * @return array|object|null An array of assigned bed records, or null if none are found.
	 */
	public function mjschool_get_assign_bed_by_room_id( $rid ) {
		global $wpdb;
		$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
		$room_id                    = intval( $rid );
		// Use get_results with the prepared query.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_assign_beds WHERE room_id = %d", $room_id ) );
		return $result;
	}
	/**
	 * Retrieves all room records for a specific hostel ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The hostel ID.
	 * @return array|object|null An array of room records, or null if none are found.
	 */
	public function mjschool_get_room_by_hostel_id( $id ) {
		global $wpdb;
		$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
		$hostel_id           = intval( $id );
		// Use get_results with the prepared query.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_mjschool_room WHERE hostel_id = %d", $hostel_id )
		);
		return $result;
	}
	/**
	 * Retrieves all bed records associated with rooms in a specific hostel ID.
	 *
	 * @since 1.0.0
	 *
	 * @param  int $id The hostel ID.
	 * @return array An array of bed records.
	 */
	public function mjschool_get_bed_by_hostel_id( $id ) {
		global $wpdb;
		$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
		$hostel_id           = intval( $id );
		$room_data           = $this->mjschool_get_room_by_hostel_id( $hostel_id );
		$bed_data            = array();
		if ( ! empty( $room_data ) ) {
			foreach ( $room_data as $value ) {
				$bed_data = array_merge( $bed_data, $this->mjschool_get_all_bed_by_room_id( intval( $value->id ) ) );
			}
		}
		return $bed_data;
	}
}