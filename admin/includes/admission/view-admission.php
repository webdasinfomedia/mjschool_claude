<?php
/**
 * View Admission Details
 *
 * Displays detailed student admission information including personal details,
 * family information, and custom fields.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/admission
 * @since      1.0.0
 * @since      2.0.1 Security audit - Already compliant with WordPress.org standards
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Security check: Verify nonce
if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'view_action' ) ) {
	$student_id = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) );
	
	// Get student data
	$student_data = get_userdata( $student_id );
	
	if ( ! $student_data ) {
		wp_die( esc_html__( 'Student not found.', 'mjschool' ) );
	}
	
	// Get custom field object
	$mjschool_custom_field_obj = new Mjschool_Custome_Field();
	$module                    = 'admission';
	$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
	?>
	
	<div class="mjschool-panel-body">
		<div class="mjschool-view-admission-details">
			<!-- Student Photo -->
			<div class="mjschool-student-photo-section">
				<div class="mjschool-photo-container">
					<?php
					$student_photo = mjschool_get_user_image( $student_id );
					if ( ! empty( $student_photo ) ) {
						?>
						<img src="<?php echo esc_url( $student_photo ); ?>" alt="<?php echo esc_attr( $student_data->display_name ); ?>" class="mjschool-student-photo">
						<?php
					} else {
						?>
						<img src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>" alt="<?php esc_attr_e( 'Default student photo', 'mjschool' ); ?>" class="mjschool-student-photo">
						<?php
					}
					?>
				</div>
			</div>
			
			<!-- Admission Information -->
			<div class="mjschool-section">
				<h3 class="mjschool-section-title"><?php esc_html_e( 'Admission Information', 'mjschool' ); ?></h3>
				<div class="mjschool-info-grid">
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Admission Number:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( $student_data->admission_no ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Admission Date:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( mjschool_get_date_in_input_box( $student_data->admission_date ) ); ?></span>
					</div>
					<?php if ( get_option( 'mjschool_admission_fees' ) === 'yes' ) { ?>
						<div class="mjschool-info-item">
							<label><?php esc_html_e( 'Admission Fees:', 'mjschool' ); ?></label>
							<span><?php echo esc_html( mjschool_get_currency_symbol() . ' ' . get_user_meta( $student_id, 'admission_fees_amount', true ) ); ?></span>
						</div>
					<?php } ?>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Status:', 'mjschool' ); ?></label>
						<span class="mjschool-status-badge <?php echo esc_attr( strtolower( $student_data->status ) ); ?>">
							<?php echo esc_html( $student_data->status ); ?>
						</span>
					</div>
				</div>
			</div>
			
			<!-- Student Information -->
			<div class="mjschool-section">
				<h3 class="mjschool-section-title"><?php esc_html_e( 'Student Information', 'mjschool' ); ?></h3>
				<div class="mjschool-info-grid">
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Full Name:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( $student_data->display_name ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'First Name:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( $student_data->first_name ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Middle Name:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( ! empty( $student_data->middle_name ) ? $student_data->middle_name : 'N/A' ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Last Name:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( $student_data->last_name ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Date of Birth:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( ! empty( $student_data->birth_date ) ? mjschool_get_date_in_input_box( $student_data->birth_date ) : 'N/A' ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Gender:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( ucfirst( $student_data->gender ) ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Email:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( $student_data->user_email ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Mobile Number:', 'mjschool' ); ?></label>
						<span>+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) . ' ' . $student_data->mobile_number ); ?></span>
					</div>
					<?php if ( ! empty( $student_data->alternet_mobile_number ) ) { ?>
						<div class="mjschool-info-item">
							<label><?php esc_html_e( 'Alternate Mobile:', 'mjschool' ); ?></label>
							<span>+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) . ' ' . $student_data->alternet_mobile_number ); ?></span>
						</div>
					<?php } ?>
					<?php if ( ! empty( $student_data->preschool_name ) ) { ?>
						<div class="mjschool-info-item">
							<label><?php esc_html_e( 'Previous School:', 'mjschool' ); ?></label>
							<span><?php echo esc_html( $student_data->preschool_name ); ?></span>
						</div>
					<?php } ?>
				</div>
			</div>
			
			<!-- Address Information -->
			<div class="mjschool-section">
				<h3 class="mjschool-section-title"><?php esc_html_e( 'Address Information', 'mjschool' ); ?></h3>
				<div class="mjschool-info-grid">
					<div class="mjschool-info-item mjschool-full-width">
						<label><?php esc_html_e( 'Address:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( $student_data->address ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'City:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( $student_data->city ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'State:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( ! empty( $student_data->state ) ? $student_data->state : 'N/A' ); ?></span>
					</div>
					<div class="mjschool-info-item">
						<label><?php esc_html_e( 'Zip Code:', 'mjschool' ); ?></label>
						<span><?php echo esc_html( $student_data->zip_code ); ?></span>
					</div>
				</div>
			</div>
			
			<!-- Siblings Information -->
			<?php
			if ( ! empty( $student_data->sibling_information ) ) {
				$sibling_data = json_decode( $student_data->sibling_information );
				if ( ! empty( $sibling_data ) ) {
					?>
					<div class="mjschool-section">
						<h3 class="mjschool-section-title"><?php esc_html_e( 'Siblings Information', 'mjschool' ); ?></h3>
						<div class="mjschool-siblings-list">
							<?php
							foreach ( $sibling_data as $sibling ) {
								if ( ! empty( $sibling->siblingsstudent ) ) {
									$sibling_name = mjschool_student_display_name_with_roll( $sibling->siblingsstudent );
									if ( $sibling_name !== 'N/A' ) {
										?>
										<div class="mjschool-sibling-item">
											<label><?php esc_html_e( 'Sibling:', 'mjschool' ); ?></label>
											<span><?php echo esc_html( $sibling_name ); ?></span>
											<span class="mjschool-sibling-class">
												(<?php echo esc_html( mjschool_get_class_section_name_wise( $sibling->siblingsclass, $sibling->siblingssection ) ); ?>)
											</span>
										</div>
										<?php
									}
								}
							}
							?>
						</div>
					</div>
					<?php
				}
			}
			?>
			
			<!-- Father Information -->
			<?php if ( $student_data->parent_status !== 'Mother' ) { ?>
				<div class="mjschool-section">
					<h3 class="mjschool-section-title"><?php esc_html_e( 'Father Information', 'mjschool' ); ?></h3>
					<div class="mjschool-info-grid">
						<?php if ( ! empty( $student_data->father_first_name ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Full Name:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( trim( $student_data->father_first_name . ' ' . $student_data->father_middle_name . ' ' . $student_data->father_last_name ) ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->father_email ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Email:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( $student_data->father_email ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->father_mobile ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Mobile:', 'mjschool' ); ?></label>
								<span>+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) . ' ' . $student_data->father_mobile ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->father_birth_date ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Date of Birth:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( mjschool_get_date_in_input_box( $student_data->father_birth_date ) ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->father_occuption ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Occupation:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( $student_data->father_occuption ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->father_education ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Education:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( $student_data->father_education ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->fathe_income ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Annual Income:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( mjschool_get_currency_symbol() . ' ' . $student_data->fathe_income ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->father_address ) ) { ?>
							<div class="mjschool-info-item mjschool-full-width">
								<label><?php esc_html_e( 'Address:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( $student_data->father_address ); ?></span>
							</div>
						<?php } ?>
						<?php
						// Father document
						if ( ! empty( $student_data->father_doc ) ) {
							$father_doc      = str_replace( '"[', '[', $student_data->father_doc );
							$father_doc1     = str_replace( ']"', ']', $father_doc );
							$father_doc_info = json_decode( $father_doc1 );
							
							if ( ! empty( $father_doc_info[0]->value ) ) {
								$safe_filename = sanitize_file_name( $father_doc_info[0]->value );
								?>
								<div class="mjschool-info-item">
									<label><?php echo esc_html( $father_doc_info[0]->title ); ?>:</label>
									<a href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $safe_filename ); ?>" download class="mjschool-download-btn">
										<i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
									</a>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
			<?php } ?>
			
			<!-- Mother Information -->
			<?php if ( $student_data->parent_status !== 'Father' ) { ?>
				<div class="mjschool-section">
					<h3 class="mjschool-section-title"><?php esc_html_e( 'Mother Information', 'mjschool' ); ?></h3>
					<div class="mjschool-info-grid">
						<?php if ( ! empty( $student_data->mother_first_name ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Full Name:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( trim( $student_data->mother_first_name . ' ' . $student_data->mother_middle_name . ' ' . $student_data->mother_last_name ) ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->mother_email ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Email:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( $student_data->mother_email ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->mother_mobile ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Mobile:', 'mjschool' ); ?></label>
								<span>+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) . ' ' . $student_data->mother_mobile ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->mother_birth_date ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Date of Birth:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( mjschool_get_date_in_input_box( $student_data->mother_birth_date ) ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->mother_occuption ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Occupation:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( $student_data->mother_occuption ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->mother_education ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Education:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( $student_data->mother_education ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->mother_income ) ) { ?>
							<div class="mjschool-info-item">
								<label><?php esc_html_e( 'Annual Income:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( mjschool_get_currency_symbol() . ' ' . $student_data->mother_income ); ?></span>
							</div>
						<?php } ?>
						<?php if ( ! empty( $student_data->mother_address ) ) { ?>
							<div class="mjschool-info-item mjschool-full-width">
								<label><?php esc_html_e( 'Address:', 'mjschool' ); ?></label>
								<span><?php echo esc_html( $student_data->mother_address ); ?></span>
							</div>
						<?php } ?>
						<?php
						// Mother document
						if ( ! empty( $student_data->mother_doc ) ) {
							$mother_doc      = str_replace( '"[', '[', $student_data->mother_doc );
							$mother_doc1     = str_replace( ']"', ']', $mother_doc );
							$mother_doc_info = json_decode( $mother_doc1 );
							
							if ( ! empty( $mother_doc_info[0]->value ) ) {
								$safe_filename = sanitize_file_name( $mother_doc_info[0]->value );
								?>
								<div class="mjschool-info-item">
									<label><?php echo esc_html( $mother_doc_info[0]->title ); ?>:</label>
									<a href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $safe_filename ); ?>" download class="mjschool-download-btn">
										<i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
									</a>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
			<?php } ?>
			
			<!-- Custom Fields -->
			<?php
			if ( ! empty( $user_custom_field ) ) {
				$has_custom_data = false;
				
				// Check if there's any custom field data
				foreach ( $user_custom_field as $custom_field ) {
					$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( 
						$module, 
						$student_id, 
						$custom_field->id 
					);
					if ( ! empty( $custom_field_value ) ) {
						$has_custom_data = true;
						break;
					}
				}
				
				if ( $has_custom_data ) {
					?>
					<div class="mjschool-section">
						<h3 class="mjschool-section-title"><?php esc_html_e( 'Additional Information', 'mjschool' ); ?></h3>
						<div class="mjschool-info-grid">
							<?php
							foreach ( $user_custom_field as $custom_field ) {
								$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( 
									$module, 
									$student_id, 
									$custom_field->id 
								);
								
								if ( ! empty( $custom_field_value ) ) {
									?>
									<div class="mjschool-info-item">
										<label><?php echo esc_html( $custom_field->field_label ); ?>:</label>
										<?php
										if ( $custom_field->field_type === 'date' ) {
											?>
											<span><?php echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) ); ?></span>
											<?php
										} elseif ( $custom_field->field_type === 'file' ) {
											$safe_custom_file = sanitize_file_name( $custom_field_value );
											?>
											<a href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $safe_custom_file ); ?>" download class="mjschool-download-btn">
												<i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
											</a>
											<?php
										} else {
											?>
											<span><?php echo esc_html( $custom_field_value ); ?></span>
											<?php
										}
										?>
									</div>
									<?php
								}
							}
							?>
						</div>
					</div>
					<?php
				}
			}
			?>
			
			<!-- Action Buttons -->
			<div class="mjschool-action-buttons">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_admission&tab=admission_list' ) ); ?>" class="mjschool-btn mjschool-btn-secondary">
					<i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Back to List', 'mjschool' ); ?>
				</a>
				
				<?php if ( mjschool_get_user_role_wise_filter_access_right_array( 'admission' )['edit'] === '1' || mjschool_get_user_role( get_current_user_id() ) === 'administrator' ) { ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . mjschool_encrypt_id( $student_id ) . '&_wpnonce=' . mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-btn mjschool-btn-primary">
						<i class="fa fa-edit"></i> <?php esc_html_e( 'Edit Admission', 'mjschool' ); ?>
					</a>
				<?php } ?>
			</div>
		</div>
	</div>
	
	<?php
} else {
	wp_die( esc_html__( 'Security check failed. Invalid access.', 'mjschool' ) );
}
?>