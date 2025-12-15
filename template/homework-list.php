<?php
/**
 * Homework List View (Admin/Staff/Teacher Perspective).
 *
 * This file contains the template code responsible for displaying the list of all
 * homework assignments to users with the appropriate permissions (typically
 * administrators, support staff, or teachers) within the 'mjschool' plugin.
 * It is a sub-template/view loaded by the main homework controller file.
 *
 * Key features include:
 * - **Data Retrieval:** Fetches all homework records using `Mjschool_Homework::mjschool_get_all_homework_list()`.
 * - **DataTables:** Initializes a jQuery DataTables instance (`#homework_list_1`) for filtering, sorting, and pagination of the list.
 * - **User Role Check:** Conditionally displays columns and action links (like "View Submission" and "Download Document") based on the current user's role (`$mjschool_role_name`).
 * - **Action Links:** Provides links to view submissions and download attached homework documents.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$obj            = new Mjschool_Homework();
$retrieve_class_data = $obj->mjschool_get_all_homework_list();
$mjschool_role_name      = mjschool_get_user_role( get_current_user_id() );
?>
<div class="mjschool-panel-body">
	<div class="table-responsive">
		<form id="mjschool-common-form" name="mjschool-common-form" method="post">
			<table id="homework_list_1" class="display" cellspacing="0" width="100%">
				<tbody>
				<?php
				foreach ( $retrieve_class_data as $retrieved_data ) {
					?>
					<tr>
						<?php
						if ( $mjschool_role_name === 'supportstaff' ) {
							?>
							<td><input type="checkbox" class="select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->homework_id ); ?>"></td>
							<?php
						}
						?>
						<td><?php echo esc_html( $retrieved_data->title ); ?></td>
						<td><?php echo esc_html( mjschool_get_class_name( $retrieved_data->class_name ) ); ?></td>
						<td><?php echo esc_html( mjschool_get_subject_by_id( $retrieved_data->subject ) ); ?></td>
						<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?></td>
						<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?></td>
						<td>
							<?php
							$doc_data = json_decode( $retrieved_data->homework_document );
							?>
							<a href="?page=mjschool_student_homewrok&tab=addhomework&action=edit&homework_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" class="btn btn-info"> <?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
							<a href="?page=mjschool_student_homewrok&tab=homeworklist&action=delete&homework_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="btn btn-danger" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
							<?php
							if ( isset($user_access['add']) && $user_access['add'] === '1' ) {
								?>
								<a href="?page=mjschool_student_homewrok&tab=view_stud_detail&action=viewsubmission&homework_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->homework_id ) ); ?>" class="btn btn-default"> <?php echo '<span class="fa fa-eye"></span> ' . esc_html__( 'View Submission', 'mjschool' ); ?></a>
								<?php
							}
							?>
							<?php
							if ( ! empty( $doc_data[0]->value ) ) {
								?>
								<a download href="<?php print esc_url( content_url() . '/uploads/school_assets/' . $doc_data[0]->value ); ?>"  class="mjschool-status-read btn btn-default" record_id="<?php echo esc_attr( $retrieved_data->homework_id ); ?>"><i class="fas fa-download"></i><?php esc_html_e( 'Download Document', 'mjschool' ); ?></a>
								<a target="blank" href="<?php print esc_url( content_url() . '/uploads/school_assets/' . $doc_data[0]->value ); ?>" class="mjschool-status-read btn btn-default" record_id="<?php echo esc_attr( $retrieved_data->homework_id ); ?>"><i class="fas fa-eye"></i><?php esc_html_e( 'View Document', 'mjschool' ); ?></a>
								<?php
							}
							?>
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
			<?php
			if ( $mjschool_role_name === 'supportstaff' ) {
				?>
				<div class="mjschool-print-button pull-left">
					<input id="delete_selected" type="submit" value="<?php esc_html_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="btn btn-danger delete_selected"/>
				</div>
				<?php
			}
			?>
		</form>
	</div>
</div>