<?php
/**
 * Subject Management File.
 *
 * This file is responsible for displaying the list of available subjects and managing the
 * addition and editing of subjects. Functionality includes assigning subjects to teachers,
 * entering edition/author details, and uploading a syllabus file.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
if ( $school_obj->role === 'student' ) {
	$subjects = $school_obj->subject;
} else {
	$subjects = mjschool_get_all_data( 'subject' );
}
?>
<ul class="nav nav-tabs mjschool-flex-nowrap" role="tablist">
	<li class="active">
		<a href="#examlist" role="tab" data-toggle="tab"> <icon class="fa fa-home"></icon> <?php esc_html_e( 'Subject', 'mjschool' ); ?> </a>
	</li>
	<?php if ( $school_obj->role === 'teacher' ) { ?>
		<li>
			<a href="#add_subject" role="tab" data-toggle="tab"> <i class="fas fa-user"></i><?php esc_html_e( 'Add Subject', 'mjschool' ); ?> </a>
		</li>
	<?php } ?>
</ul>
<!-- Tab panes. -->
<div class="tab-content">
	<div class="tab-pane fade active in" id="examlist">
		<h2><?php echo esc_html( esc_attr__( 'Subject list', 'mjschool' ) ); ?></h2>       
		<table id="mjschool-subject-list" class="table table-bordered display dataTable" cellspacing="0" width="100%">
			<thead>
				<tr>                
					<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
					<th><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></th>
					<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>                               
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
					<th><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></th>
					<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				if ( $school_obj->role != 'parent' ) {
					foreach ( $subjects as $retrieved_data ) {
						?>
						<tr>
							<td><?php echo esc_html( mjschool_get_class_name( $retrieved_data->class_id ) ); ?></td>
							<td><?php echo esc_html( $retrieved_data->sub_name ); ?></td>
							<td><?php echo esc_html( mjschool_get_user_name_by_id( $retrieved_data->teacher_id ) ); ?></td>
						</tr>
						<?php
					}
				} else {
					$chid_array = $school_obj->child_list;
					foreach ( $chid_array as $child_id ) {
						$class_info = $school_obj->mjschool_get_user_class_id( $child_id );
						$subjects   = $school_obj->mjschool_subject_list( $class_info->class_id );
						foreach ( $subjects as $retrieved_data ) {
							?>
							<tr>
								<td><?php echo esc_html( mjschool_get_class_name( $retrieved_data->class_id ) ); ?></td>
								<td><?php echo esc_html( $retrieved_data->sub_name ); ?></td>
								<td><?php echo esc_html( mjschool_get_user_name_by_id( $retrieved_data->teacher_id ) ); ?></td>
							</tr>
							<?php
						}
					}
				}
				?>
			</tbody>
		</table>
	</div>
	<div class="tab-pane fade" id="add_subject">
		<?php
		if ( isset( $_POST['subject'] ) ) {
			if ( isset( $_POST['subject_syllabus'] ) ) {
				$sullabus = 'syllabus.pdf';
			} else {
				$sullabus = sanitize_text_field(wp_unslash($_POST['old_syllabus']));
			}
			$subjects  = array(
				'sub_name'    => sanitize_textarea_field( wp_unslash($_POST['subject_name']) ),
				'class_id'    => sanitize_text_field( wp_unslash($_POST['subject_class']) ),
				'teacher_id'  => sanitize_text_field(wp_unslash($_POST['subject_teacher'])),
				'edition'     => sanitize_textarea_field( wp_unslash($_POST['subject_edition']) ),
				'author_name' => sanitize_text_field( wp_unslash($_POST['subject_author']) ),
				'syllabus'    => $sullabus,
			);
			$tablename = 'subject';
			if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
				$subid = array( 'subid' => sanitize_text_field(wp_unslash($_REQUEST['subject_id'])) );
				mjschool_update_record( $tablename, $subjects, $subid );
			} else {
				mjschool_insert_record( $tablename, $subjects );
			}
		}
		?>
		<h2>
			<?php
			$edit = 0;
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
				$edit = 1;
				echo esc_html( 'Edit Subject', 'mjschool' );
				$subject = mjschool_get_subject( sanitize_text_field(wp_unslash($_REQUEST['subject_id'])) );
			} else {
				echo esc_html( 'Add New Subject', 'mjschool' );
			}
			?>
		</h2>
		<form name="mjschool-student-form" action="" method="post">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<table class="form-table">
				<tr class="mjschool-user-login-wrap">
					<th><label><?php esc_html_e( 'Subject Name', 'mjschool' ); ?> </label></th>
					<td>
						<input type="text" name="subject_name" class="regular-text ,custom[address_description_validation]" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $subject->sub_name );} ?>" />
					</td>
				</tr>
				<tr class="mjschool-user-login-wrap">
					<th><label><?php esc_html_e( 'Class', 'mjschool' ); ?> </label></th>
					<td>
						<?php
						if ( $edit ) {
							$classval = $subject->class_id;
						} else {
							$classval = '';
						}
						?>
						<select name="subject_class">
							<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr class="mjschool-user-login-wrap">
					<th><label><?php esc_html_e( 'Teacher', 'mjschool' ); ?> </label></th>
					<td>
						<?php
						if ( $edit ) {
							$teachval = $subject->teacher_id;
						} else {
							$teachval = '';
						}
						?>
						<select name="subject_teacher">
							<option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?> </option>
							<?php
							foreach ( mjschool_get_users_data( 'teacher' ) as $teacherdata ) {
								?>
								<option value="<?php echo esc_attr( $teacherdata->ID ); ?>" <?php selected( $teachval, $teacherdata->ID ); ?>><?php echo esc_html( $teacherdata->display_name ); ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr class="mjschool-user-login-wrap">
					<th> <label><?php esc_html_e( 'Edition', 'mjschool' ); ?> </label> </th>
					<td>
						<input type="text" name="subject_edition" class="regular-text validate[custom[address_description_validation]]" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $subject->edition ); } ?>" />
					</td>
				</tr>
				<tr class="mjschool-user-login-wrap">
					<th> <label><?php esc_html_e( 'Author Name', 'mjschool' ); ?> </label> </th>
					<td>
						<input type="text" name="subject_author" class="regular-text validate[custom[onlyLetter_specialcharacter]]" maxlength="100" value="<?php if ( $edit ) { echo esc_attr( $subject->author_name ); } ?>" />
					</td>
				</tr>
				<tr class="mjschool-user-login-wrap">
					<th> <label><?php esc_html_e( 'Syllabus', 'mjschool' ); ?> </label> </th>
					<td>
						<input type="file" name="subject_syllabus" />
						<input type="hidden" value="<?php if ( $edit ) { $syllabusval = $subject->syllabus; } else { $syllabusval = ''; }
						?>" name="old_syllabus" />
					</td>
				</tr>
				<tr>
					<th></th>
					<td><input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Subject', 'mjschool' ); } else { esc_html_e( 'Add Subject', 'mjschool' ); } ?>" name="subject" /></td>
				</tr>
			</table>
		</form>
	</div>
</div>