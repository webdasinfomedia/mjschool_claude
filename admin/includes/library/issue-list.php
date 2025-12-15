<?php
/**
 * Library Issue List Management.
 *
 * Displays and manages the list of users (students and teachers) who currently have 
 * issued books within the Mjschool plugin. The file dynamically retrieves users with 
 * active book issues, displays them in a responsive DataTable, and provides quick 
 * access to issue and return actions.
 *
 * Key Features:
 * - Retrieves students and teachers with active library issues.
 * - Excludes users based on approval or role restrictions.
 * - Displays detailed user information (image, email, mobile, and role).
 * - Integrates DataTables for responsive search, sorting, and pagination.
 * - Includes bulk selection and record deletion functionality.
 * - Supports role-based display logic for user images and details.
 * - Ensures secure output using WordPress escaping and nonce handling.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/library
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for issue-return list tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_library_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
$mjschool_obj_lib = new Mjschool_Library();
if ( $active_tab === 'issuelist' ) {
	$exclude_ids = mjschool_approve_student_list(); // ensure this returns an array.
	// Get students excluding certain IDs.
	$students = get_users(
		array(
			'role'    => 'student',
			'exclude' => is_array( $exclude_ids ) ? $exclude_ids : array(),
		)
	);
	// Get teachers.
	$teachers = get_users(
		array(
			'role' => 'teacher',
		)
	);
	// Merge both lists.
	$alluser_with_issues = array();
	foreach ( array_merge( $students, $teachers ) as $mjschool_user ) {
		// Get library card no.
		$library_card_data = $mjschool_obj_lib->mjschool_get_library_card_for_student( $mjschool_user->ID );
		if ( ! empty( $library_card_data ) ) {
			$card_no = $library_card_data[0]->library_card_no;
			// Check if book is issued using your plugin’s function or a custom query.
			$issued_books = $mjschool_obj_lib->mjschool_get_issued_books_by_card( $card_no ); // <-- you must define or use such a method.
			if ( ! empty( $issued_books ) ) {
				$alluser_with_issues[] = $mjschool_user;
			}
		}
	}
	$alluser = $alluser_with_issues;
	if ( ! empty( $alluser ) ) {
		?>
		<div class="mjschool-panel-body"><!--mjschool-panel-body. -->
			<div class="table-responsive">
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="mjschool-issue-list" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Name & Email', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Library Card No', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'User Type', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?></th>
								<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $alluser ) ) {
								foreach ( $alluser as $retrieved_data ) {
									$library_card_no = $mjschool_obj_lib->mjschool_get_library_card_for_student( $retrieved_data->ID );
									?>
									<tr>
										<td class="mjschool-user-image mjschool-width-50px-td">
											<a href="#">
												<?php
												$uid       = $retrieved_data->ID;
												$mjschool_role_name = mjschool_get_user_role( $uid );
												$umetadata = mjschool_get_user_image( $uid );
												
												if (empty($umetadata ) ) {
													if ($mjschool_role_name === "student") {
														echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
													} elseif ($mjschool_role_name === "teacher") {
														echo '<img src=' . esc_url( get_option( 'mjschool_teacher_thumb_new' ) ) . ' class="img-circle" />';
													}
												} else {
													echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
												}
												
												?>
											</a>
										</td>
										<td class="name">
											<a class="mjschool-color-black" href="admin.php?page=mjschool_library&tab=issue_return&user_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ); ?>"><?php echo esc_html( $retrieved_data->display_name ); ?></a>
											<br>
											<label class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></label>
										</td>
										<td>
											<?php
											if ( ! empty( $library_card_no ) ) {
												$library_card = $library_card_no[0]->library_card_no;
												if ( ! empty( $library_card ) ) {
													echo esc_html( $library_card );
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												}
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Library Card No', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php echo esc_html( mjschool_get_user_role( $retrieved_data->ID ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'User Type', 'mjschool' ); ?>"></i>
										</td>
										<td class="name">
											+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>
											<?php
											if ( ! empty( $retrieved_data->mobile_number ) ) {
												echo esc_html( $retrieved_data->mobile_number );
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile No.', 'mjschool' ); ?>"></i>
										</td>
										<td class="action">
											<div class="mjschool-user-dropdown">
												<ul  class="mjschool_ul_style">
													<li >
														<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
														</a>
														<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
															<li class="mjschool-float-left-width-100px">
																<a href="?page=mjschool_library&tab=issue_return&user_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-book"> </i><?php esc_html_e( 'Issue & Return', 'mjschool' ); ?> </a>
															</li>
														</ul>
													</li>
												</ul>
											</div>
										</td>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
					</table>
				</form>
			</div>
		</div> <!--mjschool-panel-body. -->
		<?php
	} else {
		?>
		<div class="mjschool-calendar-event-new">
			<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
		</div>
		<?php
	}
} ?>