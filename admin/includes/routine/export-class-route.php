<?php
/**
 * Export Class Data to CSV.
 *
 * This file provides the admin interface for exporting class data to a CSV file
 * within the MJSchool plugin. It allows administrators to select a class and
 * corresponding section and generate a downloadable CSV file containing related
 * information such as class name, section, and associated details.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/routine
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$edit       = 0;
$route_data = null;
$classval   = '';
$sectionval = '';
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
	$edit = 1;
	if ( isset( $_REQUEST['route_id'] ) ) {
		$route_data = mjschool_get_route_by_id( intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['route_id'] ) ) ) ) );
		if ( $route_data ) {
			$classval   = $route_data->class_id;
			$sectionval = isset( $route_data->section_name ) ? $route_data->section_name : '';
		}
	}
}
?>
<div class="mjschool-panel-white mjschool-margin-top-20px mjschool-padding-top-25px-res">
	<div class="mjschool-panel-body"> <!------- Panel Body. ------->
		<form name="export_class_csv" action="" method="post" class="mjschool-form-horizontal" id="export_class_csv" enctype="multipart/form-data">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label>
						<?php
						if ( ! $edit && isset( $_POST['class_id'] ) ) {
							$classval = intval( $_POST['class_id'] );
						}
						?>
						<select name="class_id" id="mjschool-class-list" class="form-control validate[required] mjschool-max-width-100px">
							<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
						<select name="class_section" class="form-control mjschool-max-width-100px mjschool-section-id-exam" id="class_section">
							<option value=""><?php esc_html_e( 'Select Class Section', 'mjschool' ); ?></option>
							<?php
							if ( $edit && $route_data ) {
								foreach ( mjschool_get_class_sections( $route_data->class_id ) as $sectiondata ) {
									?>
									<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				</div>
			</div>
			<?php wp_nonce_field( 'upload_class_route_admin_nonce' ); ?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" name="save_export_csv" class="btn mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div><!------- End Panel Body. ------->
</div>