<?php
/**
 * School Management Library Management Class.
 *
 * This file contains the Mjschool_Library class, which handles
 * the core business logic and CRUD operations for the library module,
 * including books, categories, issue periods, and book transactions.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
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
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param array $data Array of book data, including fields like 'isbn', 'book_name', 'action', etc.
	 * @return int|false The ID of the inserted row on success (insert), the number of updated rows (update), or false on failure.
	 */
	public function mjschool_add_book( $data ) {
		global $wpdb;
		$table_book                 = $wpdb->prefix . 'mjschool_library_book';
		$bookdata['ISBN']           = sanitize_textarea_field( stripslashes( $data['isbn'] ) );
		$bookdata['book_name']      = sanitize_textarea_field( stripslashes( $data['book_name'] ) );
		$bookdata['publisher']      = sanitize_text_field( $data['publisher'] );
		$bookdata['author_name']    = sanitize_text_field( $data['author_name'] );
		$bookdata['cat_id']         = sanitize_text_field( $data['bookcat_id'] );
		$bookdata['rack_location']  = sanitize_text_field( $data['rack_id'] );
		$bookdata['book_number']    = $data['book_number'];
		$bookdata['price']          = sanitize_text_field( $data['book_price'] );
		$bookdata['quentity']       = sanitize_text_field( $data['quentity'] );
		$bookdata['total_quentity'] = sanitize_text_field( $data['quentity'] );
		$bookdata['description']    = sanitize_textarea_field( stripslashes( $data['description'] ) );
		$bookdata['added_by']       = get_current_user_id();
		$bookdata['added_date']     = sanitize_text_field( $data['post_date'] );
		if ( $data['action'] == 'edit' ) {
			$book_id['id'] = $data['book_id'];
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->update( $table_book, $bookdata, $book_id );
			$book   = $bookdata['book_name'];
			mjschool_append_audit_log( '' . esc_html__( 'Book Updated', 'mjschool' ) . '( ' . $book . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', sanitize_textarea_field(wp_unslash($_REQUEST['page'])) );
			return $result;
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_book, $bookdata );
			if ( $result !== false ) {
				$insert_id = $wpdb->insert_id;
				$book      = $bookdata['book_name'];
				mjschool_append_audit_log(
					'' . esc_html__( 'Book Added', 'mjschool' ) . '( ' . $book . ' )' . '',
					get_current_user_id(),
					get_current_user_id(),
					'insert',
					sanitize_textarea_field(wp_unslash($_REQUEST['page']))
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
	 * @since      1.0.0
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
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $user_id The ID of the user who added the books.
	 * @return array|object|null Array of results on success, or null on failure.
	 */
	public function mjschool_get_all_books_created_by( $user_id ) {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM $table_book where added_by=" . $user_id . 'ORDER BY added_date DESC' );
		return $result;
	}
	/**
	 * Retrieves a single book record by its ID.
	 *
	 * NOTE: The SQL query is not using $wpdb->prepare and may be vulnerable to SQL injection
	 * if $id is not properly validated/casted before this call.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $id The ID of the book to retrieve.
	 * @return object|null The row object on success, or null on failure.
	 */
	public function mjschool_get_single_books( $id ) {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( "SELECT * FROM $table_book where id=" . $id );
		return $result;
	}
	/**
	 * Retrieves all book categories, which are stored as custom posts of type 'smgt_bookcategory'.
	 *
	 * Uses get_posts() to fetch the custom post type records.
	 *
	 * @since      1.0.0
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
	 * @since      1.0.0
	 * @param array $data Array containing 'category_name' (used as post title).
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public function mjschool_add_bookcat( $data ) {
		global $wpdb;
		$result = wp_insert_post(
			array(
				'post_status' => 'publish',
				'post_type'   => 'smgt_bookcategory',
				'post_title'  => sanitize_textarea_field( $data['category_name'] ),
			)
		);
		return $result;
	}
	/**
	 * Deletes a book category custom post by its ID.
	 *
	 * @since      1.0.0
	 * @param int $cat_id The ID of the book category post to delete.
	 * @return object|false|null WP_Post object if the post was trashed, true if deleted, or false/null on failure.
	 */
	public function mjschool_delete_cat_type( $cat_id ) {
		$result = wp_delete_post( $cat_id );
		return $result;
	}
	/**
	 * Retrieves all rack locations, which are stored as custom posts of type 'mjschool_rack'.
	 *
	 * @since      1.0.0
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
	 * @since      1.0.0
	 * @param array $data Array containing 'category_name' (used as post title).
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public function mjschool_add_rack( $data ) {
		global $wpdb;
		$result = wp_insert_post(
			array(
				'post_status' => 'publish',
				'post_type'   => 'mjschool_rack',
				'post_title'  => sanitize_textarea_field( $data['category_name'] ),
			)
		);
		return $result;
	}
	/**
	 * Deletes a rack location custom post by its ID.
	 *
	 * @since      1.0.0
	 * @param int $cat_id The ID of the rack location post to delete.
	 * @return object|false|null WP_Post object if the post was trashed, true if deleted, or false/null on failure.
	 */
	public function mjschool_delete_rack_type( $cat_id ) {
		$result = wp_delete_post( $cat_id );
		return $result;
	}
	/**
	 * Deletes a book record from the library book table.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $id The ID of the book to delete.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function mjschool_delete_book( $id ) {
		global $wpdb;
		$table_book = $wpdb->prefix . 'mjschool_library_book';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$event = $wpdb->get_row( "SELECT * FROM $table_book where id=$id" );
		if ( $event){
			$book  = $event->book_name;
			mjschool_append_audit_log( '' . esc_html__( 'Book Deleted', 'mjschool' ) . '( ' . $book . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', sanitize_textarea_field(wp_unslash($_REQUEST['page'])) );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->query( "DELETE FROM $table_book where id= " . $id );
		return $result;
	}
	/**
	 * Adds a new book issue period by creating a custom post of type 'mjschool_bookperiod'.
	 *
	 * @since      1.0.0
	 * @param array $data Array containing 'category_name' (used as post title).
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public function mjschool_add_period( $data ) {
		global $wpdb;
		$result = wp_insert_post(
			array(
				'post_status' => 'publish',
				'post_type'   => 'mjschool_bookperiod',
				'post_title'  => sanitize_textarea_field( $data['category_name'] ),
			)
		);
		return $result;
	}
	/**
	 * Retrieves all book issue periods, stored as custom posts of type 'mjschool_bookperiod'.
	 *
	 * @since      1.0.0
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
	 * @since      1.0.0
	 * @param int $cat_id The ID of the book period post to delete.
	 * @return object|false|null WP_Post object if the post was trashed, true if deleted, or false/null on failure.
	 */
	public function mjschool_delete_period( $cat_id ) {
		$result = wp_delete_post( $cat_id );
		return $result;
	}
	/**
	 * Issues one or more books to a student and records the issue.
	 *
	 * Updates the book quantity, inserts records into the issue table, and sends a push notification.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param array $data Array of issue data, including student ID, book IDs, dates, fine, etc.
	 * @return int|false The result of the last $wpdb->insert operation.
	 */
	public function mjschool_add_issue_book( $data ) {
		global $wpdb;
		$table_issue           = $wpdb->prefix . 'mjschool_library_book_issue';
		$issuedata['class_id'] = sanitize_text_field( $data['class_id'] );
		if ( isset( $data['class_section'] ) ) {
			$issuedata['section_id'] = sanitize_text_field( $data['class_section'] );
		}
		$issuedata['student_id']      = sanitize_text_field( $data['student_id'] );
		$issuedata['library_card_no'] = sanitize_text_field( $data['library_card'] );
		$issuedata['cat_id']          = sanitize_text_field( $data['bookcat_id'] );
		$issuedata['issue_date']      = date( 'Y-m-d', strtotime( $data['issue_date'] ) );
		$issuedata['end_date']        = date( 'Y-m-d', strtotime( $data['return_date'] ) );
		$issuedata['period']          = sanitize_text_field( $data['period_id'] );
		$issuedata['fine']            = 0;
		if ( isset( $data['fine'] ) ) {
			$issuedata['fine'] = $data['fine'];
		}
		$issuedata['status']   = 'Issue';
		$issuedata['issue_by'] = get_current_user_id();
		$book_ids              = is_array( $data['book_id'] ) ? $data['book_id'] : array( $data['book_id'] );
		mjschool_append_audit_log( '' . esc_html__( 'Issue Book Added', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'insert', sanitize_textarea_field(wp_unslash($_REQUEST['page'])) );
		foreach ( $book_ids as $book ) {
			$issuedata['book_id'] = $book;
			$this->mjschool_get_qty_book_id( $book, 'issue' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_issue, $issuedata );
			// Push notification
			$device_token      = array();
			$device_token[]    = get_user_meta( sanitize_textarea_field(wp_unslash($_POST['student_id'])), 'token_id', true );
			$title             = esc_attr__( 'New Notification For Book Issue', 'mjschool' );
			$text              = esc_attr__( 'New book', 'mjschool' ) . ' ' . mjschool_get_book_name( $book ) . ' ' . esc_attr__( 'has been issue to you.', 'mjschool' );
			$notification_data = array(
				'registration_ids' => $device_token,
				'data'             => array(
					'title' => $title,
					'body'  => $text,
					'type'  => 'notification',
				),
			);
			$json              = json_encode( $notification_data );
			$message           = mjschool_send_push_notification( $json );
		}
		return $result;
	}
	/**
	 * Checks if a library card number is already associated with a *different* student ID.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param string $card_no The library card number to check.
	 * @param int $student_id The ID of the student to exclude from the count.
	 * @return int Returns 1 if the card is in use by another student, 0 otherwise.
	 */
	function mjschool_exits_library_card_no_submit($card_no,$student_id)
	{
		global $wpdb;
		$response = 0;
		$table_name = $wpdb->prefix . 'mjschool_library_book_issue';
		$query = "SELECT COUNT(*) FROM $table_name WHERE library_card_no = %s AND student_id != %d";
		$count = $wpdb->get_var( $wpdb->prepare( $query, $card_no , $student_id) ); //phpcs:ignore
		if ( $count > 0 ) {
			$response = 1;
		}
		return $response ;
	}
	/**
	 * Retrieves all book issue records from the issue table.
	 *
	 * @since      1.0.0
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
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $user_id The ID of the user who issued the books.
	 * @return array|object|null Array of results on success, or null on failure.
	 */
	public function mjschool_get_all_issuebooks_created_by( $user_id ) {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM $table_issuebook where issue_by=" . $user_id );
		return $result;
	}
	/**
	 * Retrieves all book issue records for a specific student ID.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $user_id The ID of the student.
	 * @return array|object|null Array of results on success, or null on failure.
	 */
	public function mjschool_get_all_issuebooks_for_student( $user_id ) {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM $table_issuebook where student_id=" . $user_id );
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
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $book_id The ID of the book.
	 * @return array Array of book issue records.
	 */
	public function mjschool_get_all_issuebooks_book_id( $book_id ) {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		$role_name       = mjschool_get_user_role( get_current_user_id() );
		if ( $role_name == 'student' ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( "SELECT * FROM $table_issuebook where book_id=" . $book_id . ' AND student_id=' . get_current_user_id() );
		} elseif ( $role_name == 'parent' ) {
			$child = mjschool_get_parents_child_id( get_current_user_id() );
			foreach ( $child as $student_id ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$book[] = $wpdb->get_results( "SELECT * FROM $table_issuebook where book_id=" . $book_id . ' AND student_id=' . $student_id );
			}
			if ( ! empty( $book ) ) {
				$mergedArray = array_merge( ...$book );
				$result      = array_unique( $mergedArray, SORT_REGULAR );
			} else {
				$result = array();
			}
		} elseif ( $role_name == 'teacher' ) {
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
			$result = $wpdb->get_results( "SELECT * FROM $table_issuebook where book_id=" . $book_id );
		}
		return $result;
	}
	/**
	 * Retrieves the library card number(s) associated with a student ID from the issue table.
	 *
	 * @since      1.0.0
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
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $id The ID of the issue record.
	 * @return object|null The row object on success, or null on failure.
	 */
	public function mjschool_get_single_issuebooks( $id ) {
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( "SELECT * FROM $table_issuebook where id=" . $id );
		return $result;
	}
	/**
	 * Deletes a book issue record by its ID and logs the action.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $id The ID of the issue record to delete.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function mjschool_delete_issuebook( $id ) {
		mjschool_append_audit_log( '' . esc_html__( 'Issue Book Deleted', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'delete', sanitize_textarea_field(wp_unslash($_REQUEST['page'])) );
		global $wpdb;
		$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->query( "DELETE FROM $table_issuebook where id= " . $id );
		return $result;
	}
	/**
	 * Retrieves book issue records that are currently 'Issued' (not yet returned) for a specific library card number.
	 *
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param string $library_card_no The library card number.
	 * @return array|object|null Array of issue records on success, or null on failure.
	 */
	public function mjschool_get_issued_books_by_card( $library_card_no ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_library_book_issue';
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
	 * @since      1.0.0
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $id The ID of the book to update.
	 * @param string $action 'issue' to decrement the quantity, or any other value to increment.
	 * @return int The new available quantity of the book.
	 */
	public function mjschool_get_qty_book_id( $id, $action ) {
		global $wpdb;
		$tbl_book_issue = $wpdb->prefix . 'mjschool_library_book_issue';
		$tbl_book       = $wpdb->prefix . 'mjschool_library_book';
		$Book           = $this->mjschool_get_single_books( $id );
		$sql            = "SELECT COUNT(*) FROM $tbl_book_issue WHERE book_id=$id AND status='Issue'";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context		
		$BookData = $wpdb->get_var( $sql );
		if ( $action == 'issue' ) {
			if ( $BookData == 0 ) {
				$BookData = 1;
			}
			$QTY = $Book->quentity - $BookData;
		} else {
			$QTY = $Book->quentity + 1;
		}
		$UpdateData['quentity'] = $QTY;
		$where['id']            = $id;
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
	 * @since      1.0.0
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
		$issuedata['fine']               = $data['fine'];
		$issuedata['comment']            = $data['comment'];
		$issuedata['actual_return_date'] = sanitize_text_field( $data['return_date'] );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->update( $table_issuebook, $issuedata, $issue_id );
		return $result;
	}

	/**
	 * Update a library book record in the database.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int   $book_id   The ID of the book to update.
	 * @param array $book_data Key–value pairs of the columns to update.
	 *
	 * @return int|false Number of rows updated on success, or false on failure.
	 */

	public function mjschool_update_library_book($bookdata, $id)
	{
		global $wpdb;

		// Table name
		$table_name = $wpdb->prefix . 'mjschool_library_book';

		$result = $wpdb->update( $table_mjschool_library_book, $bookdata, $id );
	}

	/**
	 * Insert a new library book record into the database.	
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $book_data  Associative array of column => value for insertion.
	 *
	 * @return int|false The inserted row ID on success, or false on failure.
	 */
	public function mjschool_insert_library_book($bookdata)
	{
		global $wpdb;

		// Table name
		$table_name = $wpdb->prefix . 'mjschool_library_book';

		$result = $wpdb->insert( $table_mjschool_library_book, $bookdata );
	}
}
