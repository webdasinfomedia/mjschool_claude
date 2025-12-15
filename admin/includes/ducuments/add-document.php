<?php
/**
 * Document Upload and Management Form.
 *
 * This file handles the creation and editing of documents within the MJ School system.
 * It includes form validation, file upload handling, and user selection (students, teachers, parents, etc.).
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/documents
 */
defined( 'ABSPATH' ) || exit;
?>
<?php
$document_id = 0;
if ( isset( $_REQUEST['document_id'] ) ) {
	$document_id = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['document_id'] ) ) ) );
}
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
	$edit   = 1;
	$result = $mjschool_obj_document->mjschool_get_single_document( $document_id );
}
?>
<div class="mjschool-panel-body mjschool-custom-padding-0"><!--PANEL BODY.-->
	<!--DOCUMENT FORM.-->
	<form name="document_form" action="" method="post" class="mjschool-form-horizontal" id="document_form" enctype="multipart/form-data">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input id="action" type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="document_id" value="<?php echo esc_attr( $document_id ); ?>" />
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Document Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"> <!-- mjschool-user-form start.-->
			<div class="row"><!--Row Div start.-->
				<?php
				if ( $edit ) {
					$document_for = $result->document_for;
					if ( $document_for !== 'student' ) {
						$display_class = 'display:none;';
					} else {
						$display_class = 'display:block;';
					}
				} elseif ( isset( $_POST['document_for'] ) ) {
					$document_for = sanitize_text_field( wp_unslash($_POST['document_for']));
				} else {
					$document_for = '';
				}
				?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="document_for"><?php esc_html_e( 'Document For', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select name="document_for" class="form-control validate[required] text-input mjschool-min-width-100px document_for" id="document_for">
						<option value="student" <?php selected( 'student', $document_for ); ?>><?php esc_html_e( 'Students', 'mjschool' ); ?></option>
						<option value="teacher" <?php selected( 'teacher', $document_for ); ?>><?php esc_html_e( 'Teachers', 'mjschool' ); ?></option>
						<option value="parent" <?php selected( 'parent', $document_for ); ?>><?php esc_html_e( 'Parents', 'mjschool' ); ?></option>
						<option value="supportstaff" <?php selected( 'supportstaff', $document_for ); ?>><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></option>
					</select>
				</div>
				<div class="col-md-6 input class_document_div" style="<?php echo esc_attr( $display_class ); ?>">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<?php
					if ( $edit ) {
						$classval = $result->class_id;
					} elseif ( isset( $_POST['class_id'] ) ) {
						$classval = intval( wp_unslash( $_REQUEST['class_id'] ) );
					} else {
						$classval = '';
					}
					?>
					<select id="mjschool-class" name="class_id" class="form-control validate[required] mjschool-max-width-100px mjschool-class-list-document">
						<option value="all class" <?php selected( 'all class', $classval ); ?>><?php esc_html_e( 'All Class', 'mjschool' ); ?></option>
						<?php
						foreach ( mjschool_get_all_class() as $classdata ) {
							?>
							<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<?php if ( $school_type === 'school' ) {?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin mjschool-class-section-document-div" style="<?php echo esc_attr( $display_class ); ?>">
						<label class=" ml-1 mjschool-custom-top-label top" for="mjschool-class-name"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
						<?php
						if ( $edit ) {
							$sectionval = $result->section_id;
						} elseif ( isset( $_POST['class_section'] ) ) {
							$sectionval = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
						} else {
							$sectionval = '';
						}
						?>
						<select name="class_section" id="mjschool-class-name" class="form-control mjschool-max-width-100px mjschool-class-section-document">
							<option value="all section" <?php selected( 'all section', $sectionval ); ?>><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( $edit ) {
								foreach ( mjschool_get_class_sections( $result->class_id ) as $sectiondata ) {
									?>
									<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				<?php } ?>
				<div class="col-md-6 input select_Student_div">
					<label for="mjschool-selected-users" class="ml-1 mjschool-custom-top-label top document_label"><?php esc_html_e( 'Select User', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<span class="document_user_display_block">
						<?php
						if ( $edit ) {
							$student_val = $result->student_id;
						} elseif ( isset( $_POST['selected_users'] ) ) {
							$student_val = sanitize_text_field( wp_unslash($_POST['selected_users']));
						} else {
							$student_val = 'all student';
						}
						?>
						<select id="mjschool-selected-users" name="selected_users" class="form-control validate[required] mjschool-max-width-100px student_list">
							<?php
							if ( $student_val === 'all student' ) {
								?>
								<option value="all student"><?php esc_html_e( 'All Student', 'mjschool' ); ?></option>
								<?php
							} elseif ( $student_val === 'all teacher' ) {
								?>
								<option value="all teacher"><?php esc_html_e( 'All Teacher', 'mjschool' ); ?></option>
								<?php
							} elseif ( $student_val === 'all supportstaff' ) {
								?>
								<option value="all supportstaff"><?php esc_html_e( 'All Supoprt Staff', 'mjschool' ); ?></option>
								<?php
							} elseif ( $student_val === 'all parent' ) {
								?>
								<option value="all parent"><?php esc_html_e( 'All Parent', 'mjschool' ); ?></option>
								<?php
							} else {
								echo '<option value="' . esc_attr( $result->student_id ) . '" ' . selected( $result->student_id, $result->student_id ) . '>' . esc_html( mjschool_user_display_name( $result->student_id ) ) . '</option>';
							}
							?>
						</select>
					</span>
				</div>
			</div>
		</div>
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Upload Document', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"> <!-- mjschool-user-form start.-->
			<div class="row"><!--Row Div start.-->
				<?php
				if ( $edit ) {
					$doc_data = json_decode( $result->document_content );
					?>
					<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="doc_title" maxlength="50" name="doc_title" class="form-control validate[required,custom[description_validation]] text-input" type="text" value="<?php if ( ! empty( $doc_data[0]->title ) ) { echo esc_attr( $doc_data[0]->title ); } elseif ( isset( $_POST['doc_title'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash($_POST['doc_title'] )) ); } ?>">
								<label  for="doc_title"><?php esc_html_e( 'Document Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-res-rtl-height-75px">
								<span class="ustom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl" for="photo"><?php esc_html_e( 'Upload Document', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
								<div class="col-sm-12">
									<input type="file" name="document_content" class="form-control file mjschool-file-validation" />
									<input type="hidden" name="old_hidden_document" value="<?php if ( ! empty( $doc_data[0]->value ) ) { echo esc_attr( $doc_data[0]->value ); } elseif ( isset( $_POST['document_content'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash($_POST['document_content'])) ); } ?>">
								</div>
								<?php
								if ( ! empty( $doc_data[0]->value ) ) {
									?>
									<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
										<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value ) ); ?>" record_id="<?php echo esc_attr( $result->document_id ); ?>">
											<i class="fa fa-download"></i>&nbsp;&nbsp;<?php esc_html_e( 'Download', 'mjschool' ); ?>
										</a>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					</div>
					<?php
				} else {
					?>
					<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="doc_title" maxlength="50" class="form-control validate[required,custom[description_validation]] text-input" type="text" value="" name="doc_title">
								<label  for="doc_title"><?php esc_html_e( 'Document Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
								<span class="ustom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl" for="photo"><?php esc_html_e( 'Upload Document', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
								<div class="col-sm-12 mjschool-display-flex">
									<input id="upload_file" name="upload_file" type="file" <?php if ( $edit ) { ?> class="margin_left_15_res form-control file mjschool-file-validation"  <?php } else { ?> class="validate[required] margin_left_15_res form-control file mjschool-file-validation margin_top_5_res" <?php } ?> />
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				?>
				<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea id="mjschool-description" name="description" maxlength="150" class="mjschool-textarea-height-47px form-control validate[custom[description_validation]] text-input resize"><?php if ( $edit ) { echo esc_textarea( $result->description );} ?></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="mjschool-description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		// --------- Get Module Wise Custom Field Data. --------------//
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = 'document';
		$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
		?>
		<!---------- Save btn. -------------->
		<div class="form-body mjschool-user-form "> <!-- mjschool-user-form start.-->
			<div class="row"><!--Row Div start.-->
				<div class="col-md-6 col-sm-6 col-xs-12">
					<?php wp_nonce_field( 'save_document_nonce' ); ?>
					<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Document', 'mjschool' ); } else { esc_html_e( 'Add Document', 'mjschool' ); } ?>" name="save_document" class="btn mjschool-save-btn" />
				</div>
			</div><!--Row Div End.-->
		</div><!-- mjschool-user-form End.-->
	</form><!--END DOCUMENT FORM.-->
</div><!--END PANEL BODY.-->