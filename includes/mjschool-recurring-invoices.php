<?php
/**
 * MJSchool Recurring Invoices & Reminders.
 *
 * This script is designed to be executed via a scheduled job (cron) outside the normal
 * WordPress request cycle. It bootstraps the WordPress environment and triggers the
 * core functions for automated billing tasks: generating new recurring invoices and
 * sending payment reminder notifications.
 *
 * @package Mjschool
 * @subpackage Mjschool
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// --------- UPDATE PLUGIN PATH. -------//
$root = dirname( dirname( dirname( __DIR__ ) ) );
if ( file_exists( $root . '/wp-load.php' ) ) {
	require_once $root . '/wp-load.php';
} else {
	require_once $root . '/wp-config.php';
}
mjschool_generate_recurring_invoice();
mjschool_send_payment_reminder();