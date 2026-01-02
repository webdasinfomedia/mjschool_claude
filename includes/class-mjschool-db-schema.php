<?php
/**
 * Database Schema Manager - Complete Implementation.
 * 
 * Handles all database table creation and updates for MJ School plugin.
 * This is a complete refactoring of the original mjschool_install_tables() function.
 * with ALL original functionality preserved.
 *
 * @package MJSchool
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MJSchool_DB_Schema {

	/**
	 * WordPress database object
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Table prefix
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->prefix = $wpdb->prefix . 'mjschool_';
	}

	/**
	 * Install all database tables
	 *
	 * @return void
	 */
	public function install_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Create tables by module
		$this->create_attendance_tables();
		$this->create_exam_tables();
		$this->create_grade_tables();
		$this->create_event_tables();
		$this->create_hall_tables();
		$this->create_certificate_tables();
		$this->create_holiday_tables();
		$this->create_marks_tables();
		$this->create_class_tables();
		$this->create_room_tables();
		$this->create_subject_tables();
		$this->create_fee_tables();
		$this->create_payment_tables();
		$this->create_message_tables();
		$this->create_time_table_tables();
		$this->create_transport_tables();
		$this->create_income_expense_tables();
		$this->create_audit_log_tables();
		$this->create_library_tables();
		$this->create_homework_tables();
		$this->create_hostel_tables();
		$this->create_custom_field_tables();
		$this->create_zoom_tables();
		$this->create_notification_tables();
		$this->create_leave_tables();

		// Run schema updates
		$this->run_schema_updates();

		// Initialize default data
		$this->initialize_default_data();
	}

	/**
	 * Create a table using dbDelta
	 *
	 * @param string $table_name Table name without prefix
	 * @param string $sql SQL CREATE TABLE statement
	 * @return bool Success status
	 */
	private function create_table( $table_name, $sql ) {
		$result = dbDelta( $sql );
		
		if ( empty( $result ) ) {
			error_log( "MJSchool: Failed to create table {$table_name}" );
			return false;
		}
		
		return true;
	}

	/**
	 * Add column to table if it doesn't exist
	 *
	 * @param string $table_name Full table name
	 * @param string $column_name Column name
	 * @param string $column_definition Column definition (e.g., 'INT(11) NOT NULL')
	 * @return bool Success status
	 */
	private function add_column_if_not_exists( $table_name, $column_name, $column_definition ) {
		// Check if column exists
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$columns = $this->wpdb->get_col( "DESC {$table_name}", 0 );
		
		if ( in_array( $column_name, $columns, true ) ) {
			return true; // Column already exists
		}

		// Add column
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $this->wpdb->query( 
			"ALTER TABLE {$table_name} ADD {$column_name} {$column_definition}" 
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( false === $result ) {
			error_log( "MJSchool: Failed to add column {$column_name} to table {$table_name}" );
			return false;
		}

		return true;
	}

	/**
	 * Modify column definition
	 *
	 * @param string $table_name Full table name
	 * @param string $column_name Column name
	 * @param string $column_definition New column definition
	 * @return bool Success status
	 */
	private function modify_column( $table_name, $column_name, $column_definition ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $this->wpdb->query(
			"ALTER TABLE {$table_name} MODIFY {$column_name} {$column_definition}"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( false === $result ) {
			error_log( "MJSchool: Failed to modify column {$column_name} in table {$table_name}" );
			return false;
		}

		return true;
	}

	/**
	 * Create attendance related tables
	 *
	 * @return void
	 */
	private function create_attendance_tables() {
		// Main attendance table
		$table_attendence = $this->prefix . 'attendence';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_attendence} (
			`attendence_id` INT(50) NOT NULL AUTO_INCREMENT,
			`user_id` INT(50) NOT NULL,
			`class_id` INT(50) NOT NULL,
			`attend_by` INT(11) NOT NULL,
			`attendence_date` DATE NOT NULL,
			`status` VARCHAR(50) NOT NULL,
			`role_name` VARCHAR(20) NOT NULL,
			`comment` TEXT NOT NULL,
			PRIMARY KEY (`attendence_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add attendence_type column
		$this->add_column_if_not_exists( $table_attendence, 'attendence_type', 'TEXT' );

		// Sub attendance table
		$mjschool_sub_attendance = $this->prefix . 'sub_attendance';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$mjschool_sub_attendance} (
			`attendance_id` INT(11) NOT NULL AUTO_INCREMENT,
			`user_id` INT(11) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`section_id` INT(11) NOT NULL,
			`sub_id` INT(11) NOT NULL,
			`attend_by` INT(11) NOT NULL,
			`attendance_date` DATE NOT NULL,
			`status` VARCHAR(50) NOT NULL,
			`role_name` VARCHAR(50) NOT NULL,
			`categories` VARCHAR(10) NULL,
			`comment` TEXT NOT NULL,
			PRIMARY KEY (`attendance_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns if missing
		$this->add_column_if_not_exists( $mjschool_sub_attendance, 'categories', "VARCHAR(10) DEFAULT 'subject'" );
		$this->add_column_if_not_exists( $mjschool_sub_attendance, 'section_id', 'INT(11) NULL' );
		$this->add_column_if_not_exists( $mjschool_sub_attendance, 'attendence_type', 'VARCHAR(10) NULL' );
		$this->add_column_if_not_exists( $mjschool_sub_attendance, 'comment', 'TEXT' );

		// Modify columns
		$this->modify_column( $mjschool_sub_attendance, 'sub_id', 'INT(11) NULL' );
		$this->modify_column( $mjschool_sub_attendance, 'section_id', 'INT(11) NULL' );

		// Add comment to main attendance
		$this->add_column_if_not_exists( $table_attendence, 'comment', 'TEXT' );
	}

	/**
	 * Create exam related tables
	 *
	 * @return void
	 */
	private function create_exam_tables() {
		// Main exam table
		$table_exam = $this->prefix . 'exam';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_exam} (
			`exam_id` INT(11) NOT NULL AUTO_INCREMENT,
			`exam_name` VARCHAR(200) NOT NULL,
			`exam_start_date` DATE NOT NULL,
			`exam_end_date` DATE NOT NULL,
			`exam_comment` TEXT NOT NULL,
			`created_date` DATETIME NOT NULL,
			`modified_date` DATETIME NOT NULL,
			`exam_creater_id` INT(11) NOT NULL,
			`contributions` VARCHAR(10) NULL,
			`contributions_data` TEXT NULL,
			PRIMARY KEY (`exam_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns if missing
		$this->add_column_if_not_exists( $table_exam, 'subject_data', 'TEXT' );
		$this->add_column_if_not_exists( $table_exam, 'contributions', 'VARCHAR(10) NULL' );
		$this->add_column_if_not_exists( $table_exam, 'contributions_data', 'TEXT NULL' );
		$this->add_column_if_not_exists( $table_exam, 'class_id', 'INT(11) NOT NULL AFTER exam_name' );
		$this->add_column_if_not_exists( $table_exam, 'section_id', 'INT(11) NOT NULL AFTER class_id' );
		$this->add_column_if_not_exists( $table_exam, 'exam_term', 'INT(11) NOT NULL AFTER section_id' );
		$this->add_column_if_not_exists( $table_exam, 'passing_mark', 'TINYINT(3) NOT NULL AFTER exam_term' );
		$this->add_column_if_not_exists( $table_exam, 'total_mark', 'TINYINT(3) NOT NULL AFTER passing_mark' );
		$this->add_column_if_not_exists( $table_exam, 'exam_start_date', 'DATE NOT NULL' );
		$this->add_column_if_not_exists( $table_exam, 'exam_end_date', 'DATE NOT NULL' );
		$this->add_column_if_not_exists( $table_exam, 'exam_syllabus', 'VARCHAR(255) DEFAULT NULL AFTER exam_end_date' );

		// Modify contributions column
		$this->modify_column( $table_exam, 'contributions', 'VARCHAR(10) NULL' );

		// Exam time table
		$table_mjschool_exam_time_table = $this->prefix . 'exam_time_table';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_exam_time_table} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`class_id` INT(11) NOT NULL,
			`exam_id` INT(11) NOT NULL,
			`subject_id` INT(11) NOT NULL,
			`exam_date` DATE NOT NULL,
			`start_time` TEXT NOT NULL,
			`end_time` TEXT NOT NULL,
			`created_date` DATE NOT NULL,
			`created_by` INT(11) NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Exam hall receipt
		$table_mjschool_exam_hall_receipt = $this->prefix . 'exam_hall_receipt';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_exam_hall_receipt} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`exam_id` INT(11) NOT NULL,
			`user_id` INT(11) NOT NULL,
			`hall_id` INT(11) NOT NULL,
			`exam_hall_receipt_status` INT(11) NOT NULL,
			`created_date` DATE NOT NULL,
			`created_by` INT(11) NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Exam merge settings
		$exam_merge_settings = $this->prefix . 'exam_merge_settings';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$exam_merge_settings} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`class_id` INT(11) NOT NULL,
			`section_id` INT(11) NULL,
			`merge_name` VARCHAR(100) NOT NULL,
			`merge_config` TEXT NOT NULL,
			`created_by` INT(11) NOT NULL,
			`status` VARCHAR(10) NOT NULL,
			`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );
	}

	/**
	 * Create grade and marks tables
	 *
	 * @return void
	 */
	private function create_grade_tables() {
		// Grade table
		$table_grade = $this->prefix . 'grade';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_grade} (
			`grade_id` INT(11) NOT NULL AUTO_INCREMENT,
			`grade_name` VARCHAR(20) NOT NULL,
			`grade_point` FLOAT NOT NULL,
			`mark_from` TINYINT(3) NOT NULL,
			`mark_upto` TINYINT(3) NOT NULL,
			`grade_comment` TEXT NOT NULL,
			`created_date` DATETIME NOT NULL,
			`creater_id` INT(11) NOT NULL,
			PRIMARY KEY (`grade_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Modify columns to support float
		$this->modify_column( $table_grade, 'mark_from', 'FLOAT NOT NULL' );
		$this->modify_column( $table_grade, 'mark_upto', 'FLOAT NOT NULL' );
	}

	/**
	 * Create marks table
	 *
	 * @return void
	 */
	private function create_marks_tables() {
		// Marks table
		$table_marks = $this->prefix . 'marks';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_marks} (
			`mark_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
			`exam_id` INT(11) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`subject_id` INT(11) NOT NULL,
			`marks` TINYINT(3) NOT NULL,
			`class_marks` TEXT NOT NULL,
			`contributions` VARCHAR(25) NOT NULL,
			`attendance` TINYINT(4) NOT NULL,
			`grade_id` INT(11) NOT NULL,
			`student_id` INT(11) NOT NULL,
			`marks_comment` TEXT NOT NULL,
			`created_date` DATETIME NOT NULL,
			`modified_date` DATETIME NOT NULL,
			`created_by` INT(11) NOT NULL,
			PRIMARY KEY (`mark_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns if missing
		$this->add_column_if_not_exists( $table_marks, 'class_marks', 'TEXT NULL' );
		$this->add_column_if_not_exists( $table_marks, 'contributions', 'VARCHAR(25) NULL' );
		$this->add_column_if_not_exists( $table_marks, 'section_id', 'INT(11) NOT NULL' );

		// Modify columns
		$this->modify_column( $table_marks, 'marks', 'FLOAT' );
		$this->modify_column( $table_marks, 'grade_id', 'INT(11) NULL' );
	}

	/**
	 * Create event table
	 *
	 * @return void
	 */
	private function create_event_tables() {
		$table_event = $this->prefix . 'event';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_event} (
			`event_id` INT(11) NOT NULL AUTO_INCREMENT,
			`event_title` VARCHAR(100) NOT NULL,
			`description` TEXT NOT NULL,
			`start_date` DATE NOT NULL,
			`start_time` VARCHAR(100) NOT NULL,
			`end_date` DATE NOT NULL,
			`end_time` VARCHAR(100) NOT NULL,
			`event_doc` VARCHAR(255) NOT NULL,
			`created_by` INT(11) NOT NULL,
			`created_date` DATE NOT NULL,
			PRIMARY KEY (`event_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );
	}

	/**
	 * Create hall table
	 *
	 * @return void
	 */
	private function create_hall_tables() {
		$table_hall = $this->prefix . 'hall';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_hall} (
			`hall_id` INT(11) NOT NULL AUTO_INCREMENT,
			`hall_name` VARCHAR(200) NOT NULL,
			`number_of_hall` INT(11) NOT NULL,
			`hall_capacity` INT(11) NOT NULL,
			`description` TEXT NOT NULL,
			`date` DATETIME NOT NULL,
			PRIMARY KEY (`hall_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add created_by column
		$this->add_column_if_not_exists( $table_hall, 'created_by', 'INT(11) NOT NULL' );
	}

	/**
	 * Create certificate tables
	 *
	 * @return void
	 */
	private function create_certificate_tables() {
		// Main certificate table
		$table_exprience_letter = $this->prefix . 'certificate';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_exprience_letter} (
			`id` INT(20) NOT NULL AUTO_INCREMENT,
			`student_id` INT(20) NOT NULL,
			`certificate_type` VARCHAR(150) NOT NULL,
			`certificate_content` LONGTEXT,
			`created_by` INT(20) NOT NULL,
			`created_at` TIMESTAMP,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add certificate_id column
		$this->add_column_if_not_exists( $table_exprience_letter, 'certificate_id', 'INT(20) NOT NULL' );

		// Dynamic certificate table
		$daynamic_certificate = $this->prefix . 'daynamic_certificate';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$daynamic_certificate} (
			id INT(11) NOT NULL AUTO_INCREMENT,
			certificate_name VARCHAR(100) NOT NULL,
			certificate_content LONGTEXT NOT NULL,
			created_date DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY certificate_name (certificate_name)
		) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

		dbDelta( $sql );
	}

	/**
	 * Create holiday table
	 *
	 * @return void
	 */
	private function create_holiday_tables() {
		$table_holiday = $this->prefix . 'holiday';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_holiday} (
			`holiday_id` INT(11) NOT NULL AUTO_INCREMENT,
			`holiday_title` VARCHAR(200) NOT NULL,
			`description` TEXT NOT NULL,
			`date` DATE NOT NULL,
			`end_date` DATE NOT NULL,
			`created_by` INT(11) NOT NULL,
			PRIMARY KEY (`holiday_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $table_holiday, 'created_date', 'DATETIME NULL' );
		$this->add_column_if_not_exists( $table_holiday, 'status', 'INT(11) NOT NULL' );
	}

	/**
	 * Create class related tables
	 *
	 * @return void
	 */
	private function create_class_tables() {
		// Main class table
		$table_mjschool_class = $this->prefix . 'class';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_class} (
			`class_id` INT(11) NOT NULL AUTO_INCREMENT,
			`class_name` VARCHAR(100) NOT NULL,
			`class_num_name` VARCHAR(5) NOT NULL,
			`class_section` VARCHAR(50) NOT NULL,
			`class_capacity` TINYINT(4) NOT NULL,
			`creater_id` INT(11) NOT NULL,
			`created_date` DATETIME NOT NULL,
			`modified_date` DATETIME NOT NULL,
			PRIMARY KEY (`class_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $table_mjschool_class, 'class_description', 'TEXT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_class, 'academic_year', 'VARCHAR(20) NULL' );

		// Modify class_capacity
		$this->modify_column( $table_mjschool_class, 'class_capacity', 'INT' );

		// Class section table
		$mjschool_class_section = $this->prefix . 'class_section';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$mjschool_class_section} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`class_id` INT(11) NOT NULL,
			`section_name` VARCHAR(255) NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Teacher class table
		$mjschool_teacher_class = $this->prefix . 'teacher_class';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$mjschool_teacher_class} (
			`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
			`teacher_id` BIGINT(20) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`created_by` BIGINT(20) NOT NULL,
			`created_date` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );
	}

	/**
	 * Create room tables
	 *
	 * @return void
	 */
	private function create_room_tables() {
		// Class room table
		$table_mjschool_class_room = $this->prefix . 'class_room';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_class_room} (
			`room_id` INT(11) NOT NULL AUTO_INCREMENT,
			`room_name` VARCHAR(255) NOT NULL,
			`class_id` TEXT NOT NULL,
			`room_type` VARCHAR(255) NOT NULL,
			`room_capacity` INT(11) NULL,
			`created_date` DATETIME NOT NULL,
			`created_by` INT(11) NOT NULL,
			PRIMARY KEY (`room_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add sub_id column
		$this->add_column_if_not_exists( $table_mjschool_class_room, 'sub_id', 'TEXT NULL' );

		// Custom class table
		$table_custom_class = $this->prefix . 'custom_class';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_custom_class} (
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`sub_id` VARCHAR(255) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`student_id` VARCHAR(255) NOT NULL,
			`created_by` INT(11) NOT NULL,
			`created_date` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add sub_id column
		$this->add_column_if_not_exists( $table_custom_class, 'sub_id', 'TEXT NULL' );
	}

	/**
	 * Create subject tables
	 *
	 * @return void
	 */
	private function create_subject_tables() {
		// Subject table
		$table_subject = $this->prefix . 'subject';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_subject} (
			`subid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`sub_name` VARCHAR(255) NOT NULL,
			`teacher_id` INT(11) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`author_name` VARCHAR(255) NOT NULL,
			`edition` VARCHAR(255) NOT NULL,
			`syllabus` VARCHAR(255) DEFAULT NULL,
			`created_by` INT(11) NOT NULL,
			PRIMARY KEY (`subid`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $table_subject, 'section_id', 'INT(11) NOT NULL' );
		$this->add_column_if_not_exists( $table_subject, 'selected_students', 'TEXT NULL' );
		$this->add_column_if_not_exists( $table_subject, 'subject_credit', 'VARCHAR(255) NULL' );
		$this->add_column_if_not_exists( $table_subject, 'subject_code', 'VARCHAR(255) DEFAULT NULL' );

		// Teacher subject table
		$smgt_teacher_sub = $this->prefix . 'teacher_subject';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$smgt_teacher_sub} (
			`teacher_subject_id` INT(11) NOT NULL AUTO_INCREMENT,
			`teacher_id` BIGINT(20) NOT NULL,
			`subject_id` BIGINT(20) NOT NULL,
			`created_date` DATETIME NOT NULL,
			`created_by` BIGINT(20) NOT NULL,
			PRIMARY KEY (`teacher_subject_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );
	}

	/**
	 * Create fee tables
	 *
	 * @return void
	 */
	private function create_fee_tables() {
		// Fees table
		$table_mjschool_fees = $this->prefix . 'fees';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_fees} (
			`fees_id` INT(11) NOT NULL AUTO_INCREMENT,
			`fees_title_id` BIGINT(20) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`fees_amount` FLOAT NOT NULL,
			`description` TEXT NOT NULL,
			`created_date` DATETIME NOT NULL,
			`created_by` INT(11) NOT NULL,
			PRIMARY KEY (`fees_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Modify and add columns
		$this->modify_column( $table_mjschool_fees, 'class_id', 'VARCHAR(20) NOT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_fees, 'section_id', 'INT(11) NOT NULL' );

		// Taxes table
		$table_mjschool_taxes = $this->prefix . 'taxes';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_taxes} (
			`tax_id` INT(11) NOT NULL AUTO_INCREMENT,
			`tax_title` VARCHAR(255) NOT NULL,
			`tax_value` DOUBLE NOT NULL,
			`created_date` DATE NOT NULL,
			PRIMARY KEY (`tax_id`)
		) DEFAULT CHARSET=utf8";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$this->wpdb->query( $sql );
	}

	/**
	 * Create payment tables
	 *
	 * @return void
	 */
	private function create_payment_tables() {
		// Fees payment table
		$table_mjschool_fees_payment = $this->prefix . 'fees_payment';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_fees_payment} (
			`fees_pay_id` INT(11) NOT NULL AUTO_INCREMENT,
			`class_id` INT(11) NOT NULL,
			`student_id` BIGINT(20) NOT NULL,
			`fees_id` VARCHAR(255) NOT NULL,
			`total_amount` FLOAT NOT NULL,
			`fees_paid_amount` FLOAT NOT NULL,
			`payment_status` TINYINT(4) NOT NULL,
			`description` TEXT NOT NULL,
			`start_year` VARCHAR(20) NOT NULL,
			`end_year` VARCHAR(20) NOT NULL,
			`paid_by_date` DATE NOT NULL,
			`created_date` DATETIME NOT NULL,
			`created_by` BIGINT(20) NOT NULL,
			PRIMARY KEY (`fees_pay_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $table_mjschool_fees_payment, 'section_id', 'INT(11) NOT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_fees_payment, 'tax', 'VARCHAR(100) NULL' );
		$this->add_column_if_not_exists( $table_mjschool_fees_payment, 'tax_amount', 'DOUBLE DEFAULT 0' );
		$this->add_column_if_not_exists( $table_mjschool_fees_payment, 'discount', 'VARCHAR(20) DEFAULT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_fees_payment, 'discount_type', 'VARCHAR(10) DEFAULT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_fees_payment, 'fees_amount', 'FLOAT' );
		$this->add_column_if_not_exists( $table_mjschool_fees_payment, 'discount_amount', 'DOUBLE DEFAULT 0' );
		$this->add_column_if_not_exists( $table_mjschool_fees_payment, 'invoice_status', 'VARCHAR(20) DEFAULT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_fees_payment, 'invoice_id', 'INT(11) DEFAULT NULL' );

		// Modify fees_id column
		$this->modify_column( $table_mjschool_fees_payment, 'fees_id', 'VARCHAR(255) NOT NULL' );

		// Recurring payments table
		$mjschool_fees_payment_recurring = $this->prefix . 'fees_payment_recurring';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$mjschool_fees_payment_recurring} (
			`recurring_id` INT(11) NOT NULL AUTO_INCREMENT,
			`class_id` INT(11) NOT NULL,
			`section_id` INT(11) NOT NULL,
			`student_id` TEXT NOT NULL,
			`fees_id` TEXT NOT NULL,
			`total_amount` FLOAT NOT NULL,
			`description` TEXT NULL,
			`start_year` DATE NOT NULL,
			`end_year` DATE NOT NULL,
			`recurring_type` VARCHAR(20) NOT NULL,
			`recurring_enddate` DATE NOT NULL,
			`status` VARCHAR(20) NOT NULL,
			`created_date` DATETIME NOT NULL,
			`created_by` BIGINT(20) NOT NULL,
			PRIMARY KEY (`recurring_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $mjschool_fees_payment_recurring, 'tax', 'VARCHAR(100) NULL' );
		$this->add_column_if_not_exists( $mjschool_fees_payment_recurring, 'tax_amount', 'DOUBLE DEFAULT 0' );
		$this->add_column_if_not_exists( $mjschool_fees_payment_recurring, 'fees_amount', 'FLOAT' );

		// Payment history table
		$table_mjschool_fee_payment_history = $this->prefix . 'fee_payment_history';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_fee_payment_history} (
			`payment_history_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
			`fees_pay_id` INT(11) NOT NULL,
			`amount` FLOAT NOT NULL,
			`payment_method` VARCHAR(50) NOT NULL,
			`paid_by_date` DATE NOT NULL,
			`created_by` BIGINT(20) NOT NULL,
			`trasaction_id` VARCHAR(50) NOT NULL,
			`payment_note` TEXT NULL,
			PRIMARY KEY (`payment_history_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add payment_note column
		$this->add_column_if_not_exists( $table_mjschool_fee_payment_history, 'payment_note', 'TEXT NULL' );

		// General payment table
		$table_mjschool_payment = $this->prefix . 'payment';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_payment} (
			`payment_id` INT(11) NOT NULL AUTO_INCREMENT,
			`student_id` INT(11) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`payment_title` VARCHAR(100) NOT NULL,
			`tax` VARCHAR(100) NULL,
			`tax_amount` DOUBLE DEFAULT 0,
			`fees_amount` FLOAT NOT NULL,
			`description` TEXT NOT NULL,
			`amount` INT(11) NOT NULL,
			`payment_status` VARCHAR(10) NOT NULL,
			`date` DATETIME NOT NULL,
			`payment_reciever_id` INT(11) NOT NULL,
			PRIMARY KEY (`payment_id`)
		) DEFAULT CHARSET=utf8 AUTO_INCREMENT=7";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $table_mjschool_payment, 'section_id', 'INT(11) NOT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_payment, 'created_by', 'INT(11) NOT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_payment, 'tax', 'VARCHAR(100) NULL' );
		$this->add_column_if_not_exists( $table_mjschool_payment, 'tax_amount', 'DOUBLE DEFAULT 0' );
		$this->add_column_if_not_exists( $table_mjschool_payment, 'fees_amount', 'FLOAT' );
	}

	/**
	 * Create message tables
	 *
	 * @return void
	 */
	private function create_message_tables() {
		// Message table
		$table_message = $this->prefix . 'message';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_message} (
			`message_id` INT(11) NOT NULL AUTO_INCREMENT,
			`sender` INT(11) NOT NULL,
			`receiver` INT(11) NOT NULL,
			`date` DATETIME NOT NULL,
			`subject` VARCHAR(150) NOT NULL,
			`message_body` TEXT NOT NULL,
			`status` INT(11) NOT NULL,
			`post_id` INT(11) NOT NULL,
			PRIMARY KEY (`message_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add post_id column
		$this->add_column_if_not_exists( $table_message, 'post_id', 'INT(11)' );

		// Message replies table
		$mjschool_message_replies = $this->prefix . 'message_replies';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$mjschool_message_replies} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`message_id` INT(11) NOT NULL,
			`sender_id` INT(11) NOT NULL,
			`receiver_id` INT(11) NOT NULL,
			`message_comment` TEXT NOT NULL,
			`message_attachment` TEXT,
			`status` INT(11),
			`created_date` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $mjschool_message_replies, 'message_attachment', 'TEXT' );
		$this->add_column_if_not_exists( $mjschool_message_replies, 'status', 'INT(11)' );
	}

	/**
	 * Create time table table
	 *
	 * @return void
	 */
	private function create_time_table_tables() {
		$table_mjschool_time_table = $this->prefix . 'time_table';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_time_table} (
			`route_id` INT(11) NOT NULL AUTO_INCREMENT,
			`subject_id` INT(11) NOT NULL,
			`teacher_id` INT(11) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`start_time` VARCHAR(10) NOT NULL,
			`end_time` VARCHAR(10) NOT NULL,
			`weekday` TINYINT(4) NOT NULL,
			PRIMARY KEY (`route_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Modify and add columns
		$this->modify_column( $table_mjschool_time_table, 'teacher_id', 'TEXT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_time_table, 'multiple_teacher', 'TEXT' );
		$this->add_column_if_not_exists( $table_mjschool_time_table, 'room_id', 'INT(11) NULL' );
		$this->add_column_if_not_exists( $table_mjschool_time_table, 'section_name', 'INT(11) NOT NULL' );
	}

	/**
	 * Create transport tables
	 *
	 * @return void
	 */
	private function create_transport_tables() {
		// Transport table
		$table_transport = $this->prefix . 'transport';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_transport} (
			`transport_id` INT(11) NOT NULL AUTO_INCREMENT,
			`route_name` VARCHAR(200) NOT NULL,
			`number_of_vehicle` INT(11) NOT NULL,
			`vehicle_reg_num` VARCHAR(50) NOT NULL,
			`smgt_user_avatar` VARCHAR(5000) NOT NULL,
			`driver_name` VARCHAR(100) NOT NULL,
			`driver_phone_num` VARCHAR(15) NOT NULL,
			`driver_address` TEXT NOT NULL,
			`route_description` TEXT NOT NULL,
			`route_fare` INT(11) NOT NULL,
			`status` TINYINT(4) NOT NULL DEFAULT '1',
			PRIMARY KEY (`transport_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Modify and add columns
		$this->modify_column( $table_transport, 'number_of_vehicle', 'INT(11) NOT NULL' );
		$this->add_column_if_not_exists( $table_transport, 'created_by', 'TEXT' );

		// Assign transport table
		$table_assign_transport = $this->prefix . 'assign_transport';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_assign_transport} (
			`assign_transport_id` INT(11) NOT NULL AUTO_INCREMENT,
			`transport_id` INT(11) NOT NULL,
			`route_name` VARCHAR(200) NOT NULL,
			`route_user` TEXT NOT NULL,
			`route_fare` INT(11) NOT NULL,
			`created_by` INT(11) NOT NULL,
			PRIMARY KEY (`assign_transport_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );
	}

	/**
	 * Create income/expense table
	 *
	 * @return void
	 */
	private function create_income_expense_tables() {
		$table_mjschool_income_expense = $this->prefix . 'income_expense';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_income_expense} (
			`income_id` INT(11) NOT NULL AUTO_INCREMENT,
			`invoice_type` VARCHAR(50) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`supplier_name` VARCHAR(100) NOT NULL,
			`entry` TEXT NOT NULL,
			`payment_status` VARCHAR(50) NOT NULL,
			`create_by` INT(11) NOT NULL,
			`income_create_date` DATE NOT NULL,
			PRIMARY KEY (`income_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $table_mjschool_income_expense, 'section_id', 'INT(11) NOT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_income_expense, 'tax', 'VARCHAR(100) NULL' );
		$this->add_column_if_not_exists( $table_mjschool_income_expense, 'tax_amount', 'DOUBLE DEFAULT 0' );
	}

	/**
	 * Create audit log tables
	 *
	 * @return void
	 */
	private function create_audit_log_tables() {
		// Audit log table
		$table_mjschool_audit_log = $this->prefix . 'audit_log';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_audit_log} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`audit_action` TEXT NOT NULL,
			`user_id` INT(11) NULL,
			`action` TEXT NOT NULL,
			`ip_address` TEXT NOT NULL,
			`created_by` INT(11) NOT NULL,
			`created_at` DATE NOT NULL,
			`date_time` DATETIME NOT NULL,
			`deleted_status` BOOLEAN NOT NULL,
			`updated_by` INT(11) NULL,
			`updated_date` DATETIME NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add module column
		$this->add_column_if_not_exists( $table_mjschool_audit_log, 'module', 'TEXT' );

		// User log table
		$table_mjschool_user_log = $this->prefix . 'user_log';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_user_log} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`user_login` TEXT NOT NULL,
			`role` TEXT NOT NULL,
			`ip_address` TEXT NOT NULL,
			`created_at` DATE NOT NULL,
			`date_time` DATETIME NOT NULL,
			`deleted_status` BOOLEAN NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Migration log table
		$table_mjschool_migration_log = $this->prefix . 'migration_log';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_migration_log} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`ip_address` TEXT NOT NULL,
			`created_by` INT(11) NOT NULL,
			`current_class` INT(11) NOT NULL,
			`next_class` INT(11) NOT NULL,
			`exam_name` INT(11) NULL,
			`pass_mark` INT(11) NULL,
			`created_at` DATE NOT NULL,
			`date_time` DATETIME NOT NULL,
			`deleted_status` BOOLEAN NOT NULL,
			`pass_students` TEXT NULL,
			`fail_students` TEXT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Cron reminder log table
		$table_mjschool_cron_reminder_log = $this->prefix . 'cron_reminder_log';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_cron_reminder_log} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`student_id` TEXT NOT NULL,
			`fees_pay_id` INT(11) NOT NULL,
			`date_time` DATE NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// CSV log table
		$table_csv_log = $this->prefix . 'csv_log';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_csv_log} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`error_log` VARCHAR(255) NOT NULL,
			`created_by` INT(11) NOT NULL,
			`created_at` DATETIME NOT NULL,
			`module` VARCHAR(40) NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$this->wpdb->query( $sql );

		// Add status column
		$this->add_column_if_not_exists( $table_csv_log, 'status', 'VARCHAR(50) DEFAULT NULL' );

		// Check status table
		$mjschool_check_status = $this->prefix . 'check_status';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$mjschool_check_status} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`type` VARCHAR(50) NULL,
			`user_id` INT(11) NOT NULL,
			`type_id` INT(11) NOT NULL,
			`status` INT(11) NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );
	}

	/**
	 * Create library tables
	 *
	 * @return void
	 */
	private function create_library_tables() {
		// Library book table
		$table_mjschool_library_book = $this->prefix . 'library_book';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_library_book} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`ISBN` VARCHAR(50) NOT NULL,
			`book_name` VARCHAR(200) CHARACTER SET utf8 NOT NULL,
			`author_name` VARCHAR(100) CHARACTER SET utf8 NOT NULL,
			`cat_id` INT(11) NOT NULL,
			`rack_location` INT(11) NOT NULL,
			`price` VARCHAR(10) NOT NULL,
			`quentity` INT(11) NOT NULL,
			`description` TEXT CHARACTER SET utf8 NOT NULL,
			`added_by` INT(11) NOT NULL,
			`added_date` VARCHAR(20) NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $table_mjschool_library_book, 'book_number', 'INT(11) NOT NULL DEFAULT 0 AFTER book_name' );
		$this->add_column_if_not_exists( $table_mjschool_library_book, 'publisher', 'VARCHAR(100) DEFAULT NULL AFTER author_name' );
		$this->add_column_if_not_exists( $table_mjschool_library_book, 'total_quentity', 'INT(11) NOT NULL DEFAULT 0 AFTER quentity' );

		// Library book issue table
		$table_mjschool_library_book_issue = $this->prefix . 'library_book_issue';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_library_book_issue} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`class_id` INT(11) NOT NULL,
			`student_id` INT(11) NOT NULL,
			`cat_id` INT(11) NOT NULL,
			`book_id` INT(11) NOT NULL,
			`issue_date` VARCHAR(20) NOT NULL,
			`end_date` VARCHAR(20) NOT NULL,
			`actual_return_date` VARCHAR(20) NOT NULL,
			`period` INT(11) NOT NULL,
			`fine` VARCHAR(20) NOT NULL,
			`status` VARCHAR(50) NOT NULL,
			`comment` TEXT NULL,
			`issue_by` INT(11) NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $table_mjschool_library_book_issue, 'section_id', 'INT(11) NOT NULL' );
		$this->add_column_if_not_exists( $table_mjschool_library_book_issue, 'library_card_no', 'VARCHAR(50) DEFAULT NULL AFTER student_id' );
		$this->add_column_if_not_exists( $table_mjschool_library_book_issue, 'comment', 'TEXT DEFAULT NULL AFTER student_id' );
	}

	/**
	 * Create homework tables
	 *
	 * @return void
	 */
	private function create_homework_tables() {
		// Homework table
		$mjschool_homework = $this->prefix . 'homework';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$mjschool_homework} (
			`homework_id` INT(11) NOT NULL AUTO_INCREMENT,
			`title` VARCHAR(250) NOT NULL,
			`class_name` INT(11) NOT NULL,
			`section_id` INT(11) NOT NULL,
			`subject` INT(11) NOT NULL,
			`content` TEXT NOT NULL,
			`submition_date` DATE NOT NULL,
			`createdby` INT(11) NOT NULL,
			`created_date` DATETIME NOT NULL,
			PRIMARY KEY (`homework_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $mjschool_homework, 'homework_document', 'VARCHAR(255) DEFAULT NULL AFTER content' );
		$this->add_column_if_not_exists( $mjschool_homework, 'marks', 'TINYINT(3) DEFAULT NULL AFTER content' );

		// Student homework table
		$mjschool_student_homework = $this->prefix . 'student_homework';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$mjschool_student_homework} (
			`stu_homework_id` INT(50) NOT NULL AUTO_INCREMENT,
			`homework_id` INT(11) NOT NULL,
			`student_id` INT(11) NOT NULL,
			`status` TINYINT(4) NOT NULL,
			`uploaded_date` DATETIME DEFAULT NULL,
			`file` TEXT NOT NULL,
			`created_by` INT(11) NOT NULL,
			`created_date` DATETIME NOT NULL,
			PRIMARY KEY (`stu_homework_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $mjschool_student_homework, 'review_file', 'TEXT DEFAULT NULL' );
		$this->add_column_if_not_exists( $mjschool_student_homework, 'obtain_marks', 'TINYINT(3) DEFAULT NULL AFTER review_file' );
		$this->add_column_if_not_exists( $mjschool_student_homework, 'evaluate_date', 'DATETIME DEFAULT NULL AFTER obtain_marks' );
		$this->add_column_if_not_exists( $mjschool_student_homework, 'student_comment', 'TEXT DEFAULT NULL AFTER evaluate_date' );
		$this->add_column_if_not_exists( $mjschool_student_homework, 'teacher_comment', 'TEXT DEFAULT NULL AFTER student_comment' );

		// Document table
		$smgt_document = $this->prefix . 'document';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$smgt_document} (
			`document_id` INT(11) NOT NULL AUTO_INCREMENT,
			`document_for` VARCHAR(50) NOT NULL,
			`class_id` VARCHAR(255) NOT NULL,
			`section_id` VARCHAR(255) NOT NULL,
			`student_id` VARCHAR(255) NOT NULL,
			`document_content` VARCHAR(255) NOT NULL,
			`description` TEXT NOT NULL,
			`createdby` INT(11) NOT NULL,
			`created_date` DATETIME NOT NULL,
			PRIMARY KEY (`document_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add document_for column
		$this->add_column_if_not_exists( $smgt_document, 'document_for', 'VARCHAR(50) DEFAULT NULL' );
	}

	/**
	 * Create hostel tables
	 *
	 * @return void
	 */
	private function create_hostel_tables() {
		// Hostel table
		$smgt_mjschool_hostel = $this->prefix . 'hostel';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$smgt_mjschool_hostel} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`hostel_name` VARCHAR(255) NOT NULL,
			`hostel_type` VARCHAR(255) NOT NULL,
			`Description` TEXT NOT NULL,
			`created_by` BIGINT(20) NOT NULL,
			`created_date` DATETIME NOT NULL,
			`updated_by` BIGINT(20) NOT NULL,
			`updated_date` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add columns
		$this->add_column_if_not_exists( $smgt_mjschool_hostel, 'hostel_address', 'VARCHAR(255) AFTER hostel_name' );
		$this->add_column_if_not_exists( $smgt_mjschool_hostel, 'hostel_intake', 'INT(11) NOT NULL DEFAULT 0 AFTER hostel_type' );

		// Room table
		$smgt_mjschool_room = $this->prefix . 'room';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$smgt_mjschool_room} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`room_unique_id` VARCHAR(20) NOT NULL,
			`hostel_id` INT(11) NOT NULL,
			`room_status` INT(11) NOT NULL,
			`room_category` INT(11) NOT NULL,
			`beds_capacity` INT(11) NOT NULL,
			`room_description` TEXT NOT NULL,
			`created_by` BIGINT(20) NOT NULL,
			`created_date` DATETIME NOT NULL,
			`updated_by` BIGINT(20) NOT NULL,
			`updated_date` DATETIME NOT NULL,
			`facilities` TEXT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add facilities column
		$this->add_column_if_not_exists( $smgt_mjschool_room, 'facilities', 'TEXT DEFAULT NULL' );

		// Beds table
		$smgt_mjschool_beds = $this->prefix . 'beds';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$smgt_mjschool_beds} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`bed_unique_id` VARCHAR(20) NOT NULL,
			`room_id` INT(11) NOT NULL,
			`bed_status` INT(11) NOT NULL,
			`bed_description` TEXT NOT NULL,
			`created_by` BIGINT(20) NOT NULL,
			`created_date` DATETIME NOT NULL,
			`updated_by` BIGINT(20) NOT NULL,
			`updated_date` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add bed_charge column
		$this->add_column_if_not_exists( $smgt_mjschool_beds, 'bed_charge', 'INT(11) NOT NULL DEFAULT 0 AFTER bed_description' );

		// Assign beds table
		$smgt_mjschool_assign_beds = $this->prefix . 'assign_beds';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$smgt_mjschool_assign_beds} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`hostel_id` INT(11) NOT NULL,
			`room_id` INT(11) NOT NULL,
			`bed_id` INT(11) NOT NULL,
			`bed_unique_id` VARCHAR(20) NOT NULL,
			`student_id` INT(11) NOT NULL,
			`assign_date` DATETIME NOT NULL,
			`created_by` BIGINT(20) NOT NULL,
			`created_date` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );
	}

	/**
	 * Create custom field tables
	 *
	 * @return void
	 */
	private function create_custom_field_tables() {
		// Custom field table
		$table_custom_field = $this->prefix . 'custom_field';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_custom_field} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`form_name` VARCHAR(255),
			`field_type` VARCHAR(100) NOT NULL,
			`field_label` VARCHAR(100) NOT NULL,
			`field_visibility` INT(10),
			`field_validation` VARCHAR(100),
			`created_by` INT(11),
			`created_at` DATETIME NOT NULL,
			`updated_by` INT(11),
			`updated_at` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Add show_in_table column
		$this->add_column_if_not_exists( $table_custom_field, 'show_in_table', 'VARCHAR(255) DEFAULT NULL' );

		// Custom field dropdown metas
		$table_custom_field_dropdown_metas = $this->prefix . 'custom_field_dropdown_metas';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_custom_field_dropdown_metas} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`custom_fields_id` INT(11) NOT NULL,
			`option_label` VARCHAR(255) NOT NULL,
			`created_by` INT(11),
			`created_at` DATETIME NOT NULL,
			`updated_by` INT(11),
			`updated_at` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Custom field metas
		$table_custom_field_metas = $this->prefix . 'custom_field_metas';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_custom_field_metas} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`module` VARCHAR(100) NOT NULL,
			`module_record_id` INT(11) NOT NULL,
			`custom_fields_id` INT(11) NOT NULL,
			`field_value` TEXT,
			`created_at` DATETIME NOT NULL,
			`updated_at` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );
	}

	/**
	 * Create Zoom meeting tables
	 *
	 * @return void
	 */
	private function create_zoom_tables() {
		// Zoom meeting table
		$smgt_zoom_meeting = $this->prefix . 'zoom_meeting';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$smgt_zoom_meeting} (
			`meeting_id` INT(11) NOT NULL AUTO_INCREMENT,
			`title` VARCHAR(255) NOT NULL,
			`route_id` INT(11) NOT NULL,
			`zoom_meeting_id` VARCHAR(50) NOT NULL,
			`uuid` VARCHAR(100) NOT NULL,
			`class_id` INT(11) NOT NULL,
			`section_id` INT(11) NULL,
			`subject_id` INT(11) NOT NULL,
			`teacher_id` INT(11) NOT NULL,
			`weekday_id` INT(11) NOT NULL,
			`password` VARCHAR(50) NULL,
			`agenda` VARCHAR(2000) NULL,
			`start_date` DATE NOT NULL,
			`end_date` DATE NOT NULL,
			`meeting_join_link` VARCHAR(1000) NOT NULL,
			`meeting_start_link` VARCHAR(1000) NOT NULL,
			`created_by` INT(11),
			`created_date` DATETIME NOT NULL,
			`updated_by` INT(11),
			`updated_date` DATETIME NULL,
			PRIMARY KEY (`meeting_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		// Zoom reminder log
		$table_mjschool_reminder_zoom_meeting_mail_log = $this->prefix . 'reminder_zoom_meeting_mail_log';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_mjschool_reminder_zoom_meeting_mail_log} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`user_id` INT(11) NOT NULL,
			`meeting_id` INT(11) NOT NULL,
			`class_id` VARCHAR(20) NOT NULL,
			`alert_date` DATE NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$this->wpdb->query( $sql );
	}

	/**
	 * Create notification table
	 *
	 * @return void
	 */
	private function create_notification_tables() {
		$mjschool_notification = $this->prefix . 'notification';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$mjschool_notification} (
			`notification_id` INT(11) NOT NULL AUTO_INCREMENT,
			`student_id` INT(11) NOT NULL,
			`title` VARCHAR(500) DEFAULT NULL,
			`message` VARCHAR(5000) DEFAULT NULL,
			`device_token` VARCHAR(255) DEFAULT NULL,
			`device_type` TINYINT(4) NOT NULL,
			`bicon` INT(11) DEFAULT NULL,
			`created_date` DATE DEFAULT NULL,
			`created_by` INT(11) DEFAULT NULL,
			PRIMARY KEY (`notification_id`)
		) DEFAULT CHARSET=utf8";

		dbDelta( $sql );
	}

	/**
	 * Create leave table
	 *
	 * @return void
	 */
	private function create_leave_tables() {
		$smgt_leave = $this->prefix . 'leave';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$smgt_leave} (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`student_id` INT(11) NOT NULL,
			`leave_type` INT(11) NOT NULL,
			`leave_duration` VARCHAR(50) NOT NULL,
			`start_date` VARCHAR(50) NOT NULL,
			`end_date` VARCHAR(50) NOT NULL,
			`reason` TEXT NOT NULL,
			`status` VARCHAR(50) NOT NULL,
			`status_comment` TEXT NOT NULL,
			`created_by` INT(11) NOT NULL,
			`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

		dbDelta( $sql );

		// Add status_comment column
		$this->add_column_if_not_exists( $smgt_leave, 'status_comment', 'TEXT NULL' );
	}

	/**
	 * Run schema updates for existing tables
	 *
	 * @return void
	 */
	private function run_schema_updates() {
		// Apply column modifications for existing tables
		$this->apply_column_modifications();

		// Run section transfer
		$this->transfer_section_id();

		// Migrate teacher class data
		$this->migrate_teacher_class_data();
	}

	/**
	 * Apply column modifications to existing tables
	 * 
	 * This method uses the modify_column() helper to update
	 * column definitions for existing tables
	 *
	 * @return void
	 */
	private function apply_column_modifications() {
		// Grade table modifications
		$table_grade = $this->prefix . 'grade';
		$this->modify_column( $table_grade, 'mark_from', 'FLOAT NOT NULL' );
		$this->modify_column( $table_grade, 'mark_upto', 'FLOAT NOT NULL' );

		// Exam table modifications
		$table_exam = $this->prefix . 'exam';
		$this->modify_column( $table_exam, 'contributions', 'VARCHAR(10) NULL' );

		// Fees table modifications
		$table_fees = $this->prefix . 'fees';
		$this->modify_column( $table_fees, 'class_id', 'VARCHAR(20) NOT NULL' );

		// Fees payment table modifications
		$table_fees_payment = $this->prefix . 'fees_payment';
		$this->modify_column( $table_fees_payment, 'fees_id', 'VARCHAR(255) NOT NULL' );

		// Time table modifications
		$table_time_table = $this->prefix . 'time_table';
		$this->modify_column( $table_time_table, 'teacher_id', 'TEXT NULL' );

		// Sub attendance modifications
		$table_sub_attendance = $this->prefix . 'sub_attendance';
		$this->modify_column( $table_sub_attendance, 'sub_id', 'INT(11) NULL' );
		$this->modify_column( $table_sub_attendance, 'section_id', 'INT(11) NULL' );

		// Marks table modifications
		$table_marks = $this->prefix . 'marks';
		$this->modify_column( $table_marks, 'marks', 'FLOAT' );
		$this->modify_column( $table_marks, 'grade_id', 'INT(11) NULL' );

		// Class table modifications
		$table_class = $this->prefix . 'class';
		$this->modify_column( $table_class, 'class_capacity', 'INT' );

		// Transport table modifications
		$table_transport = $this->prefix . 'transport';
		$this->modify_column( $table_transport, 'number_of_vehicle', 'INT(11) NOT NULL' );
	}

	/**
	 * Transfer section ID functionality
	 * This was called mjschool_transfer_section_id() in the original code
	 *
	 * @return void
	 */
	private function transfer_section_id() {
		// Call the external function if it exists
		if ( function_exists( 'mjschool_transfer_section_id' ) ) {
			mjschool_transfer_section_id();
		}
	}

	/**
	 * Migrate teacher class data from user meta to teacher_class table
	 *
	 * @return void
	 */
	private function migrate_teacher_class_data() {
		$mjschool_teacher_class = $this->prefix . 'teacher_class';
		
		// Check if table is empty
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$teacher_class = $this->wpdb->get_results( "SELECT * FROM {$mjschool_teacher_class}" );
		
		if ( ! empty( $teacher_class ) ) {
			return; // Already migrated
		}

		$teacherlist = get_users( array( 'role' => 'teacher' ) );
		
		if ( empty( $teacherlist ) ) {
			return;
		}

		foreach ( $teacherlist as $retrieve_data ) {
			$created_by   = get_current_user_id();
			$created_date = current_time( 'mysql' );
			$class_id     = get_user_meta( $retrieve_data->ID, 'class_name', true );
			
			if ( empty( $class_id ) ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$success = $this->wpdb->insert(
				$mjschool_teacher_class,
				array(
					'teacher_id'   => absint( $retrieve_data->ID ),
					'class_id'     => absint( $class_id ),
					'created_by'   => absint( $created_by ),
					'created_date' => $created_date,
				),
				array( '%d', '%d', '%d', '%s' )
			);

			if ( false === $success ) {
				error_log( "MJSchool: Failed to insert teacher class data for teacher ID: {$retrieve_data->ID}" );
			}
		}
	}

	/**
	 * Initialize default data
	 *
	 * @return void
	 */
	private function initialize_default_data() {
		// Add default admission fees type
		if ( function_exists( 'mjschool_add_default_admission_fees_type' ) ) {
			mjschool_add_default_admission_fees_type();
		}
		
		// Add default registration fees type
		if ( function_exists( 'mjschool_add_default_registration_fees_type' ) ) {
			mjschool_add_default_registration_fees_type();
		}
		
		// Add default library periods
		if ( function_exists( 'mjschool_add_default_library_periods' ) ) {
			mjschool_add_default_library_periods();
		}
	}
}