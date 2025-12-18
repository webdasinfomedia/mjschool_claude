<?php
/**
 * School Management Library Management Class.
 *
 * This file contains the Mjschool_Library class, which handles
 * the core business logic and CRUD operations for the library module,
 * including books, categories, issue periods, and book transactions.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Handles all business logic and data manipulation for the Mjschool Library module.
 *
 * This class manages CRUD operations for books, book categories, rack locations,
 * issue periods, and the book issuance/return processes.
 *
 * @since 1.0.0
 */
class Mjschool_Library {
	/**
	 * Adds a new book or updates an existing one in the library database.
	 *
	 * Uses $wpdb->insert or $wpdb->update for database interaction.
	 * Sanitizes all incoming data before database storage.
	 * Logs the action to the audit log.
	 *
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param array $data Array of book data, including fields like 'isbn', 'book_name', 'action', etc.
	 * @return int|false The ID of the inserted row on success (insert), the number of updated rows (update), or false on failure.
	 */
	public function mjschool_add_book( $data ) {
		global $wpdb;
		$table_book                 = $wpdb->prefix . 'mjschool_library_book';
		$bookdata['ISBN']           = isset( $data['isbn'] ) ? sanitize_text_field( wp_unslash( $data['isbn'] ) ) : '';
		$bookdata['book_name']      = isset( $data['book_name'] ) ? sanitize_text_field( wp_unslash( $data['book_name'] ) ) : '';
		$bookdata['publisher']      = isset( $data['publisher'] ) ? sanitize_text_field( wp_unslash( $data['publisher'] ) ) : '';
		$bookdata['author_name']    = isset( $data['author_name'] ) ? sanitize_text_field( wp_unslash( $data['author_name'] ) ) : '';
		$bookdata['cat_id']         = isset( $data['bookcat_id'] ) ? intval( $data['bookcat_id'] ) : 0;
		$bookdata['rack_location']  = isset( $data['rack_id'] ) ? intval( $data['rack_id'] ) : 0;
		$bookdata['book_number']    = isset( $data['book_number'] ) ? sanitize_text_field( wp_unslash( $data['book_number'] ) ) : '';
		$bookdata['price']          = isset( $data['book_price'] ) ? floatval( $data['book_price'] ) : 0;
		$bookdata['quentity']       = isset( $data['quentity'] ) ? intval( $data['quentity'] ) : 0;
		$bookdata['total_quentity'] = isset( $data['quentity'] ) ? intval( $data['quentity'] ) : 0;
		$bookdata['description']    = isset( $data['description'] ) ? sanitize_textarea_field( wp_unslash( $data['description'] ) ) : '';
		$bookdata['added_by']       = get_current_user_id();
		$bookdata['added_date']     = isset( $data['post_date'] ) ? sanitize_text_field( wp_unslash( $data['post_date'] ) ) : gmdate( 'Y-m-d' );
		$action                     = isset( $data['action'] ) ? sanitize_text_field( wp_unslash( $data['action'] ) ) : '';
		$page_name                  = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( $action === 'edit' ) {
			$book_id['id'] = isset( $data['book_id'] ) ? intval( $data['book_id'] ) : 0;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->update( $table_book, $bookdata, $book_id );
			$book   = $bookdata['book_name'];
			mjschool_append_audit_log( '' . esc_html__( 'Book Updated', 'mjschool' ) . '( ' . esc_html( $book ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', $page_name );
			return $result;
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_book, $bookdata );
			if ( $result !== false ) {
				$insert_id = $wpdb->insert_id;
				$book      = $bookdata['book_name'];
				mjschool_append_audit_log(
					'' . esc_html__( 'Book Added', 'mjschool' ) . '( ' . esc_html( $book ) . ' )' . '',
					get_current_user_id(),
					get_current_user_id(),
					'insert',
					$page_name
				);
				return $insert_id;
			} else {
				return false;
			}
		}
	}
	/**
	 * Retrieves all book records from the library book table.
	 *
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @return array|object|null Array of results on success, or null on failure.
	 */
	public function mjschool_get_all_books() {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM $table_book ORDER BY added_date DESC" );
		return $result;
	}
	/**
	 * Retrieves all book records added by a specific user ID.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $user_id The ID of the user who added the books.
	 * @return array|object|null Array of results on success, or null on failure.
	 */
	public function mjschool_get_all_books_created_by( $user_id ) {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		$user_id    = intval( $user_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_book WHERE added_by = %d ORDER BY added_date DESC", $user_id ) );
		return $result;
	}
	/**
	 * Retrieves a single book record by its ID.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $id The ID of the book to retrieve.
	 * @return object|null The row object on success, or null on failure.
	 */
	public function mjschool_get_single_books( $id ) {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		$book_id    = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_book WHERE id = %d", $book_id ) );
		return $result;
	}
	/**
	 * Retrieves all book categories, which are stored as custom posts of type 'smgt_bookcategory'.
	 *
	 * Uses get_posts() to fetch the custom post type records.
	 *
	 * @since      1.0.0
	 * @return array Array of WP_Post objects representing the book categories.
	 */
	public function mjschool_get_bookcat() {
		$args   = array(
			'post_type'      => 'smgt_bookcategory',
			'posts_per_page' => -1,
			'orderby'        => 'post_title',
			'order'          => 'Asc',
		);
		$result = get_posts( $args );
		return $result;
	}
	/**
	 * Adds a new book category by creating a custom post of type 'smgt_bookcategory'.
	 *
	 * @since      1.0.0
	 * @param array $data Array containing 'category_name' (used as post title).
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public function mjschool_add_bookcat( $data ) {
		global $wpdb;
		$result = wp_insert_post(
			array(
				'post_status' => 'publish',
				'post_type'   => 'smgt_bookcategory',
				'post_title'  => isset( $data['category_name'] ) ? sanitize_text_field( wp_unslash( $data['category_name'] ) ) : '',
			)
		);
		return $result;
	}
	/**
	 * Deletes a book category custom post by its ID.
	 *
	 * @since      1.0.0
	 * @param int $cat_id The ID of the book category post to delete.
	 * @return object|false|null WP_Post object if the post was trashed, true if deleted, or false/null on failure.
	 */
	public function mjschool_delete_cat_type( $cat_id ) {
		$result = wp_delete_post( intval( $cat_id ) );
		return $result;
	}
	/**
	 * Retrieves all rack locations, which are stored as custom posts of type 'mjschool_rack'.
	 *
	 * @since      1.0.0
	 * @return array Array of WP_Post objects representing the rack locations.
	 */
	public function mjschool_get_rack_list() {
		$args   = array(
			'post_type'      => 'mjschool_rack',
			'posts_per_page' => -1,
			'orderby'        => 'post_title',
			'order'          => 'Asc',
		);
		$result = get_posts( $args );
		return $result;
	}
	/**
	 * Adds a new rack location by creating a custom post of type 'mjschool_rack'.
	 *
	 * @since      1.0.0
	 * @param array $data Array containing 'category_name' (used as post title).
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public function mjschool_add_rack( $data ) {
		global $wpdb;
		$result = wp_insert_post(
			array(
				'post_status' => 'publish',
				'post_type'   => 'mjschool_rack',
				'post_title'  => isset( $data['category_name'] ) ? sanitize_text_field( wp_unslash( $data['category_name'] ) ) : '',
			)
		);
		return $result;
	}
	/**
	 * Deletes a rack location custom post by its ID.
	 *
	 * @since      1.0.0
	 * @param int $cat_id The ID of the rack location post to delete.
	 * @return object|false|null WP_Post object if the post was trashed, true if deleted, or false/null on failure.
	 */
	public function mjschool_delete_rack_type( $cat_id ) {
		$result = wp_delete_post( intval( $cat_id ) );
		return $result;
	}
	/**
	 * Deletes a book record from the library book table.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $id The ID of the book to delete.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function mjschool_delete_book( $id ) {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		$book_id    = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_book WHERE id = %d", $book_id ) );
		if ( $event ) {
			$book      = $event->book_name;
			$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			mjschool_append_audit_log( '' . esc_html__( 'Book Deleted', 'mjschool' ) . '( ' . esc_html( $book ) . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_book WHERE id = %d", $book_id ) );
		return $result;
	}
	/**
	 * Adds a new book issue period by creating a custom post of type 'mjschool_bookperiod'.
	 *
	 * @since      1.0.0
	 * @param array $data Array containing 'category_name' (used as post title).
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public function mjschool_add_period( $data ) {
		global $wpdb;
		$result = wp_insert_post(
			array(
				'post_status' => 'publish',
				'post_type'   => 'mjschool_bookperiod',
				'post_title'  => isset( $data['category_name'] ) ? sanitize_text_field( wp_unslash( $data['category_name'] ) ) : '',
			)
		);
		return $result;
	}
	/**
	 * Retrieves all book issue periods, stored as custom posts of type 'mjschool_bookperiod'.
	 *
	 * @since      1.0.0
	 * @return array Array of WP_Post objects representing the book periods.
	 */
	public function mjschool_get_period_list() {
		$args   = array(
			'post_type'      => 'mjschool_bookperiod',
			'posts_per_page' => -1,
			'orderby'        => 'post_title',
			'order'          => 'Asc',
		);
		$result = get_posts( $args );
		return $result;
	}
	/**
	 * Deletes a book issue period custom post by its ID.
	 *
	 * @since      1.0.0
	 * @param int $cat_id The ID of the book period post to delete.
	 * @return object|false|null WP_Post object if the post was trashed, true if deleted, or false/null on failure.
	 */
	public function mjschool_delete_period( $cat_id ) {
		$result = wp_delete_post( intval( $cat_id ) );
		return $result;
	}
	/**
	 * Issues one or more books to a student and records the issue.
	 *
	 * Updates the book quantity, inserts records into the issue table, and sends a push notification.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param array $data Array of issue data, including student ID, book IDs, dates, fine, etc.
	 * @return int|false The result of the last $wpdb->insert operation.
	 */
	public function mjschool_add_issue_book( $data ) {
		global $wpdb;
		$table_issue           = $wpdb->prefix . 'mjschool_library_book_issue';
		$issuedata['class_id'] = isset( $data['class_id'] ) ? intval( $data['class_id'] ) : 0;
		if ( isset( $data['class_section'] ) ) {
			$issuedata['section_id'] = intval( $data['class_section'] );
		}
		$issuedata['student_id']      = isset( $data['student_id'] ) ? intval( $data['student_id'] ) : 0;
		$issuedata['library_card_no'] = isset( $data['library_card'] ) ? sanitize_text_field( wp_unslash( $data['library_card'] ) ) : '';
		$issuedata['cat_id']          = isset( $data['bookcat_id'] ) ? intval( $data['bookcat_id'] ) : 0;
		$issuedata['issue_date']      = isset( $data['issue_date'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $data['issue_date'] ) ) ) ) : gmdate( 'Y-m-d' );
		$issuedata['end_date']        = isset( $data['return_date'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $data['return_date'] ) ) ) ) : gmdate( 'Y-m-d' );
		$issuedata['period']          = isset( $data['period_id'] ) ? intval( $data['period_id'] ) : 0;
		$issuedata['fine']            = 0;
		if ( isset( $data['fine'] ) ) {
			$issuedata['fine'] = floatval( $data['fine'] );
		}
		$issuedata['status']   = 'Issue';
		$issuedata['issue_by'] = get_current_user_id();
		$book_ids              = isset( $data['book_id'] ) && is_array( $data['book_id'] ) ? array_map( 'intval', $data['book_id'] ) : array( intval( $data['book_id'] ) );
		$page_name             = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Issue Book Added', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'insert', $page_name );
		foreach ( $book_ids as $book ) {
			$issuedata['book_id'] = intval( $book );
			$this->mjschool_get_qty_book_id( intval( $book ), 'issue' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_issue, $issuedata );
			// Push notification
			$device_token      = array();
			$device_token[]    = get_user_meta( $issuedata['student_id'], 'token_id', true );
			$title             = esc_html__( 'New Notification For Book Issue', 'mjschool' );
			$text              = esc_html__( 'New book', 'mjschool' ) . ' ' . esc_html( $this->mjschool_get_book_name( intval( $book ) ) ) . ' ' . esc_html__( 'has been issue to you.', 'mjschool' );
			$notification_data = array(
				'registration_ids' => $device_token,
				'data'             => array(
					'title' => $title,
					'body'  => $text,
					'type'  => 'notification',
				),
			);
			$json              = wp_json_encode( $notification_data );
			$message           = mjschool_send_push_notification( $json );
		}
		return $result;
	}
	/**
	 * Checks if a library card number is already associated with a *different* student ID.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param string $card_no The library card number to check.
	 * @param int    $student_id The ID of the student to exclude from the count.
	 * @return int Returns 1 if the card is in use by another student, 0 otherwise.
	 */
	public function mjschool_exits_library_card_no_submit( $card_no, $student_id ) {
		global $wpdb;
		$response   = 0;
		$table_name = $wpdb->prefix . 'mjschool_library_book_issue';
		$card_no    = sanitize_text_field( $card_no );
		$student_id = intval( $student_id );
		$query      = "SELECT COUNT(*) FROM $table_name WHERE library_card_no = %s AND student_id != %d";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$count = $wpdb->get_var( $wpdb->prepare( $query, $card_no, $student_id ) );
		if ( $count > 0 ) {
			$response = 1;
		}
		return $response;
	}
	/**
	 * Retrieves all book issue records from the issue table.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @return array|object|null Array of results on success, or null on failure.
	 */
	public function mjschool_get_all_issuebooks() {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM $table_issuebook ORDER BY issue_date DESC" );
		return $result;
	}
	/**
	 * Retrieves all book issue records created (issued) by a specific user ID.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $user_id The ID of the user who issued the books.
	 * @return array|object|null Array of results on success, or null on failure.
	 */
	public function mjschool_get_all_issuebooks_created_by( $user_id ) {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		$user_id         = intval( $user_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_issuebook WHERE issue_by = %d", $user_id ) );
		return $result;
	}
	/**
	 * Retrieves all book issue records for a specific student ID.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $user_id The ID of the student.
	 * @return array|object|null Array of results on success, or null on failure.
	 */
	public function mjschool_get_all_issuebooks_for_student( $user_id ) {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		$user_id         = intval( $user_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_issuebook WHERE student_id = %d", $user_id ) );
		return $result;
	}
	/**
	 * Retrieves book issue records for a specific book, filtered by the current user's role.
	 *
	 * Filters by:
	 * - 'student': Only issues to the current user.
	 * - 'parent': Issues to all children of the current user.
	 * - 'teacher': Issues to current user (as student) or issued by current user (as staff).
	 * - 'other': All issues for the book.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $book_id The ID of the book.
	 * @return array Array of book issue records.
	 */
	public function mjschool_get_all_issuebooks_book_id( $book_id ) {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		$role_name       = mjschool_get_user_role( get_current_user_id() );
		$book_id         = intval( $book_id );
		if ( $role_name === 'student' ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_issuebook WHERE book_id = %d AND student_id = %d", $book_id, get_current_user_id() ) );
		} elseif ( $role_name === 'parent' ) {
			$child = mjschool_get_parents_child_id( get_current_user_id() );
			$book  = array();
			foreach ( $child as $student_id ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$book[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_issuebook WHERE book_id = %d AND student_id = %d", $book_id, intval( $student_id ) ) );
			}
			if ( ! empty( $book ) ) {
				$mergedArray = array_merge( ...$book );
				$result      = array_unique( $mergedArray, SORT_REGULAR );
			} else {
				$result = array();
			}
		} elseif ( $role_name === 'teacher' ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $table_issuebook WHERE book_id = %d AND (student_id = %d OR issue_by = %d)",
					$book_id,
					get_current_user_id(),
					get_current_user_id()
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_issuebook WHERE book_id = %d", $book_id ) );
		}
		return $result;
	}
	/**
	 * Retrieves the library card number(s) associated with a student ID from the issue table.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $user_id The student ID.
	 * @return array|object|null Array of results (objects with 'library_card_no' property) on success, or null on failure.
	 */
	public function mjschool_get_library_card_for_student( $user_id ) {
		global $wpdb;
		$user_id         = intval( $user_id );
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		$query           = $wpdb->prepare( "SELECT library_card_no FROM $table_issuebook WHERE library_card_no IS NOT NULL AND student_id = %d", $user_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results( $query );
		return $results;
	}
	/**
	 * Retrieves a single book issue record by its ID.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $id The ID of the issue record.
	 * @return object|null The row object on success, or null on failure.
	 */
	public function mjschool_get_single_issuebooks( $id ) {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		$issue_id        = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_issuebook WHERE id = %d", $issue_id ) );
		return $result;
	}
	/**
	 * Deletes a book issue record by its ID and logs the action.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $id The ID of the issue record to delete.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function mjschool_delete_issuebook( $id ) {
		$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Issue Book Deleted', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		$issue_id        = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_issuebook WHERE id = %d", $issue_id ) );
		return $result;
	}
	/**
	 * Retrieves book issue records that are currently 'Issued' (not yet returned) for a specific library card number.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param string $library_card_no The library card number.
	 * @return array|object|null Array of issue records on success, or null on failure.
	 */
	public function mjschool_get_issued_books_by_card( $library_card_no ) {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'mjschool_library_book_issue';
		$library_card_no = sanitize_text_field( $library_card_no );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE library_card_no = %s AND (actual_return_date = '' OR actual_return_date IS NULL OR status = %s)",
				$library_card_no,
				'Issued'
			)
		);
		return $results;
	}
	/**
	 * Updates the available quantity ('quentity') of a book based on the number of currently 'Issue' records.
	 *
	 * It first counts the currently issued books for the given book ID, then calculates the new available quantity.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int    $id The ID of the book to update.
	 * @param string $action 'issue' to decrement the quantity, or any other value to increment.
	 * @return int The new available quantity of the book.
	 */
	public function mjschool_get_qty_book_id( $id, $action ) {
		global $wpdb;
		$tbl_book_issue = $wpdb->prefix . 'mjschool_library_book_issue';
		$tbl_book       = $wpdb->prefix . 'mjschool_library_book';
		$book_id        = intval( $id );
		$Book           = $this->mjschool_get_single_books( $book_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$BookData = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $tbl_book_issue WHERE book_id = %d AND status = %s", $book_id, 'Issue' ) );
		if ( $action === 'issue' ) {
			if ( $BookData === 0 ) {
				$BookData = 1;
			}
			$QTY = $Book->quentity - $BookData;
		} else {
			$QTY = $Book->quentity + 1;
		}
		$UpdateData['quentity'] = $QTY;
		$where['id']            = $book_id;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->update( $tbl_book, $UpdateData, $where );
		return $QTY;
	}
	/**
	 * Processes the return of an issued book.
	 *
	 * Updates the issue record status to 'Submitted', records fine, comment, and actual return date.
	 * Also calls mjschool_get_qty_book_id() to increment the book's available quantity.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param array $data Array of return data, including 'issue_book_id', 'fine', 'comment', and 'return_date'.
	 * @return int|false The number of updated rows on success, or false on error.
	 */
	public function mjschool_submit_return_book( $data ) {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		$book_id         = intval( $data['issue_book_id'] );
		$issue           = $this->mjschool_get_single_issuebooks( $book_id );
		$this->mjschool_get_qty_book_id( $issue->book_id, '' );
		$issue_id['id']                  = $book_id;
		$issuedata['status']             = 'Submitted';
		$issuedata['fine']               = isset( $data['fine'] ) ? floatval( $data['fine'] ) : 0;
		$issuedata['comment']            = isset( $data['comment'] ) ? sanitize_textarea_field( wp_unslash( $data['comment'] ) ) : '';
		$issuedata['actual_return_date'] = isset( $data['return_date'] ) ? sanitize_text_field( wp_unslash( $data['return_date'] ) ) : '';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->update( $table_issuebook, $issuedata, $issue_id );
		return $result;
	}

	/**
	 * Update a library book record in the database.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $bookdata Key–value pairs of the columns to update.
	 * @param array $id       The ID condition for the update.
	 *
	 * @return int|false Number of rows updated on success, or false on failure.
	 */
	public function mjschool_update_library_book( $bookdata, $id ) {
		global $wpdb;

		// Table name
		$table_name = $wpdb->prefix . 'mjschool_library_book';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->update( $table_name, $bookdata, $id );
		return $result;
	}

	/**
	 * Insert a new library book record into the database.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $bookdata  Associative array of column => value for insertion.
	 *
	 * @return int|false The inserted row ID on success, or false on failure.
	 */
	public function mjschool_insert_library_book( $bookdata ) {
		global $wpdb;

		// Table name
		$table_name = $wpdb->prefix . 'mjschool_library_book';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->insert( $table_name, $bookdata );
		if ( $result ) {
			return $wpdb->insert_id;
		}
		return false;
	}

	/**
	 * Get book name by book ID.
	 *
	 * @since 1.0.0
	 * @param int $id Book ID.
	 * @return string Book name or 'N/A'.
	 */
	public function mjschool_get_book_name( $id ) {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		$book_id    = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_book WHERE id = %d", $book_id ) );
		if ( ! empty( $result ) ) {
			return $result->book_name;
		} else {
			return 'N/A';
		}
	}

	/**
	 * Get ISBN number of a book.
	 *
	 * @since 1.0.0
	 * @param int $id Book ID.
	 * @return string ISBN value.
	 */
	public function mjschool_get_ISBN( $id ) {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		$book_id    = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_book WHERE id = %d", $book_id ) );
		return $result->ISBN;
	}

	/**
	 * Get book number from library table.
	 *
	 * @since 1.0.0
	 * @param int $id Book ID.
	 * @return string Book number.
	 */
	public function mjschool_get_book_number( $id ) {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		$book_id    = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_book WHERE id = %d", $book_id ) );
		return $result->book_number;
	}

	/**
	 * Get payment report for frontend filters.
	 *
	 * @since 1.0.0
	 * @param int|string $class_id Class ID or 'all_class'.
	 * @param int        $fee_term Fee term ID.
	 * @param string     $payment_status Status (paid/unpaid/partial).
	 * @param string     $sdate Start date.
	 * @param string     $edate End date.
	 * @param int        $section_id Section ID.
	 * @return array List of filtered payment records.
	 */
	public function mjschool_get_payment_report_front( $class_id, $fee_term, $payment_status, $sdate, $edate, $section_id ) {
		global $wpdb;
		// Sanitize inputs.
		$start_date            = gmdate( 'Y-m-d', strtotime( sanitize_text_field( $sdate ) ) );
		$end_date              = gmdate( 'Y-m-d', strtotime( sanitize_text_field( $edate ) ) );
		$class_id              = ( $class_id === 'all_class' ) ? 0 : intval( $class_id );
		$fee_term              = intval( $fee_term );
		$payment_status        = sanitize_text_field( $payment_status );
		$mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
		$sql                   = "SELECT * FROM $mjschool_fees_payment WHERE paid_by_date BETWEEN %s AND %s";
		$params                = array( $start_date, $end_date );
		// Optional filters.
		if ( $class_id > 0 ) {
			$sql     .= ' AND class_id = %d';
			$params[] = $class_id;
		}
		if ( $fee_term > 0 ) {
			$sql     .= ' AND FIND_IN_SET(%d, fees_id)';
			$params[] = $fee_term;
		}
		if ( ! empty( $payment_status ) ) {
			$sql     .= ' AND payment_status = %s';
			$params[] = $payment_status;
		}
		// Prepare and execute.
		$prepared_sql = $wpdb->prepare( $sql, ...$params );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $prepared_sql );
		return $result;
	}
}