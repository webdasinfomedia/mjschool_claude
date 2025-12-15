<?php
/**
 * Student List Page with DataTable Integration.
 *
 * This file displays and manages the student list within the MJSchool plugin's admin area.
 * It provides an interactive DataTable interface for administrators to view, select,
 * delete, and print student records, as well as to import/export data in CSV format.
 *
 * Key Features:
 * - Implements DataTables with AJAX-based server-side processing for optimized performance.
 * - Supports sorting, searching, and pagination through WordPress AJAX endpoints.
 * - Includes "Select All" checkbox functionality with dynamic state management.
 * - Provides bulk actions such as deleting multiple records or printing student ID cards.
 * - Offers CSV import/export and log download capabilities for student data management.
 * - Displays YouTube tutorial link (optional) when enabled in settings.
 * - Ensures secure requests using nonces and WordPress escaping functions.
 * - Uses responsive table layout for mobile and desktop compatibility.
 * - Supports multilingual text through WordPress internationalization functions.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/student
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-max-height-overflow mjschool-padding-0">
		<div class="modal-content">
			<div class="result"></div>
			<div class="view-parent"></div>
			<div class="student_list"></div>
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<?php
if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
	?>
		<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo 'https://www.youtube.com/embed/Nk-iedcC4Y0?si=OshxLuh2R77dCUWT'; ?>" title="<?php esc_attr_e( 'Student ID Card', 'mjschool' ); ?>">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-youtube-icon.png' ); ?>" alt="<?php esc_html_e( 'YouTube', 'mjschool' ); ?>">
		</a>
		<?php
}
?>
<div id="mjschool-show-student-list">
	<div class="loader">
		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-school-app-loader.gif' ); ?>">
	</div>
	  
	<div class="table-responsive"><!-------- Table responsive. ----------->
		<form id="mjschool-common-form" name="mjschool-common-form" method="post"><!-------- Student form start. ----------->
			<table id="students_list" class="display" cellspacing="0" width="100%">
				<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
					<tr>
						<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
						<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Student Name & Email', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Student ID', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
						<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			<!-------- Delete and select all button. ----------->
			<div class="mjschool-print-button pull-left">
				<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload mjchool_margin_bottom_5px mjchool_margin_bottom_5px">
					<input type="checkbox" class="mjschool-sub-chk check_for_id select_all mjchool_margin_top_0px " value="" id="select_all" >
					<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
				</button>
				<?php
				if ( $user_access_delete === '1' ) {
					?>
					<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
					<?php
				}
				?>
				<button data-toggle="tooltip" title="<?php esc_attr_e( 'Print ID Card', 'mjschool' ); ?>" name="print_id_card" class="mjschool-print-id-card mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-print.png' ); ?>"></button>
				<button data-toggle="tooltip" title="<?php esc_attr_e( 'Print Standard ID Card', 'mjschool' ); ?>" name="print_standard_id_card" class="mjschool-print-id-card mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-print.png' ); ?>"></button>
				<button data-toggle="tooltip" title="<?php esc_attr_e( 'Import CSV', 'mjschool' ); ?>" class="view_import_student_csv_popup mjschool-export-import-csv-btn mjschool-custom-padding-0" type="button"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-export-csv.png' ); ?>"></button>
				<button data-toggle="tooltip" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" type="button" class="view_csv_popup mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-import-csv.png' ); ?>"></button>
				<button data-toggle="tooltip" title="<?php esc_attr_e( 'CSV logs', 'mjschool' ); ?>" name="csv_log" type="button" class="mjschool-download-csv-log mjschool-export-import-csv-btn mjschool-custom-padding-0" id="student"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-import-csv.png' ); ?>"></button>
			</div>
			
			<!-------- Delete and select all button. ----------->
		</form><!-------- Student form end. ----------->
	</div><!-------- Table responsive. ----------->
</div>
