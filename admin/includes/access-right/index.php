<?php
/**
 * Access Rights Management Page.
 *
 * Displays the Access Rights admin page with tabs
 * for different user roles (Student, Teacher, Parent, Support Staff, Management)
 * and loads tab content dynamically.
 *
 * @package MJSchool
 * @subpackage MJSchool/admin/includes/access-rights
 */

defined( 'ABSPATH' ) || exit;

// Available tabs and corresponding files.
$tabs = array(
    'Student'       => 'student.php',
    'Teacher'       => 'teacher.php',
    'Support staff' => 'support-staff.php',
    'Parent'        => 'parent.php',
    'Management'    => 'management.php',
);

// Verify nonce if present, or use default tab (improved for WordPress Coding Standards).
$active_tab = isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_access_rights_tab' ) && isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'Student';

// Validate that the active tab exists in the allowed tabs array.
if ( ! array_key_exists( $active_tab, $tabs ) ) {
    $active_tab = 'Student';
}

$nonce = wp_create_nonce( 'mjschool_access_rights_tab' );
?>
<!-- Popup Background. -->
<div class="mjschool-popup-bg">
    <div class="mjschool-overlay-content">
        <div class="mjschool-notice-content"></div>
    </div>
</div>

<div class="mjschool-page-inner mjschool-access-right">
    <div class="mjschool-main-list-margin-15px mjschool-notice-page mjschool-custom-font-size">
        <div class="row">
            <div class="col-md-12 mjschool-custom-padding-0">
                <!-- Navigation Tabs. -->
                <ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
                    <?php foreach ( $tabs as $key => $file ) : ?>
                        <?php
                        $active_class = ( $active_tab === $key ) ? 'active nav-tab-active' : '';
                        ?>
                        <li class="<?php echo esc_attr( $active_tab === $key ? 'active' : '' ); ?>">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_access_right&tab=' . urlencode( $key ) . '&_wpnonce=' . $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_class ); ?>" >
                                <?php echo esc_html( $key ); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="clearfix"></div>
                <!-- Load Tab Content.. -->
                <?php
                if ( isset( $tabs[ $active_tab ] ) ) {
                    $file_path = trailingslashit( MJSCHOOL_ADMIN_DIR . '/access-right' ) . $tabs[ $active_tab ];

                    if ( file_exists( $file_path ) ) {
                        require_once $file_path;
                    } else {
                        echo '<div class="notice notice-error"><p>' . esc_html__( 'Tab file not found.', 'mjschool' ) . '</p></div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>