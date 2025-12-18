<?php
/**
 * Library Management Admin Page.
 *
 * This file manages the main functionality of the Library module in the Mjschool plugin.
 * It handles:
 * - Access rights validation.
 * - Adding, editing, and deleting books.
 * - Uploading and exporting books via CSV.
 * - Displaying books in a DataTable with custom fields.
 * - Initializing related JS scripts and UI components.
 *
 * @package    Mjschool
 * @subpackage Admin/Library
 */
defined( 'ABSPATH' ) || exit;
// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'library' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			if ( 'library' === $user_access['page_link'] && ( $action === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'library' === $user_access['page_link'] && ( $action === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'library' === $user_access['page_link'] && ( $action === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'library';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<!-- Start POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="view_popup"></div>
			<div class="invoice_data"></div>
			<div class="mjschool-category-list">
			</div>
		</div>
	</div>
</div>
<!-- End POP-UP Code. -->
<?php
$mjschool_obj_lib = new Mjschool_Library();
if ( $action === 'delete' ) {
	$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
	if ( wp_verify_nonce( $nonce_action, 'delete_action' ) ) {
		$nonce = wp_create_nonce( 'mjschool_library_tab' );
		$book_id = isset( $_REQUEST['book_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['book_id'] ) ) ) ) : 0;
		$result = $mjschool_obj_lib->mjschool_delete_book( $book_id );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce=' . rawurlencode( $nonce ) . '&message=10' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['delete_selected_book'] ) ) {
	$nonce = wp_create_nonce( 'mjschool_library_tab' );
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $ids as $id ) {
			$result = $mjschool_obj_lib->mjschool_delete_book( $id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce=' . rawurlencode( $nonce ) . '&message=10' ) );
		die();
	}
}
// Upload booklist CSV.
if ( isset( $_REQUEST['upload_csv_file'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'upload_csv_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_library_tab' );
		if ( isset( $_FILES['csv_file'] ) ) {
			$errors             = array();
			$file_name          = sanitize_file_name( $_FILES['csv_file']['name'] );
			$file_size          = intval( $_FILES['csv_file']['size'] );
			$file_tmp           = sanitize_text_field( $_FILES['csv_file']['tmp_name'] );
			$file_type          = sanitize_mime_type( $_FILES['csv_file']['type'] );
			$value              = explode( '.', $file_name );
			$file_ext           = strtolower( array_pop( $value ) );
			$allowed_extensions = array( 'csv' );
			if ( ! in_array( $file_ext, $allowed_extensions, true ) ) {
				$module      = 'library';
				$status      = 'file type error';
				$log_message = 'Book import fail due to invalid file type';
				mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
				$errors[] = 'This file is not allowed, please upload a CSV file.';
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce=' . rawurlencode( $nonce ) . '&message=8' ) );
				die();
			}
			if ( $file_size > 2097152 ) {
				$errors[] = 'File size exceeds 2 MB.';
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce=' . rawurlencode( $nonce ) . '&message=9' ) );
				die();
			}
			if ( empty( $errors ) ) {
				$rows   = array_map( 'str_getcsv', file( $file_tmp ) );
				$header = array_map( 'strtolower', array_shift( $rows ) );
				if ( ! empty( $rows ) ) {
					global $wpdb;
					$table_mjschool_library_book = $wpdb->prefix . 'mjschool_library_book';
					foreach ( $rows as $row ) {
						$csv       = array_combine( $header, array_map( 'trim', $row ) );
						$bookdata  = array(
							'isbn'           => sanitize_text_field( $csv['isbn'] ?? '' ),
							'book_name'      => sanitize_text_field( $csv['book_name'] ?? '' ),
							'publisher'      => sanitize_text_field( $csv['publisher'] ?? '' ),
							'author_name'    => sanitize_text_field( $csv['author_name'] ?? '' ),
							'book_number'    => sanitize_text_field( $csv['book_number'] ?? '' ),
							'price'          => floatval( $csv['price'] ?? 0 ),
							'quentity'       => intval( $csv['quantity'] ?? 0 ),
							'total_quentity' => intval( $csv['quantity'] ?? 0 ),
							'description'    => sanitize_textarea_field( $csv['description'] ?? '' ),
							'added_by'       => get_current_user_id(),
							'added_date'     => date( 'Y-m-d' ),
						);
						$book_name = $bookdata['book_name'] ?? '';
						// Rack Location.
						$rack_location_name = sanitize_text_field( $csv['rack_location'] ?? '' );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$rack_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type='smgt_rack' AND post_title=%s", $rack_location_name ) );
						if ( ! $rack_id && ! empty( $rack_location_name ) ) {
							$rack_id = wp_insert_post(
								array(
									'post_status' => 'publish',
									'post_type'   => 'smgt_rack',
									'post_title'  => $rack_location_name,
								)
							);
						}
						$bookdata['rack_location'] = $rack_id;
						// Book Category.
						$cat_name = sanitize_text_field( $csv['cat_id'] ?? '' );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type='smgt_bookcategory' AND post_title=%s", $cat_name ) );
						if ( ! $cat_id && ! empty( $cat_name ) ) {
							$cat_id = wp_insert_post(
								array(
									'post_status' => 'publish',
									'post_type'   => 'smgt_bookcategory',
									'post_title'  => $cat_name,
								)
							);
						}
						$bookdata['cat_id'] = $cat_id;
						// Check for duplicate books.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$existing_book = $wpdb->get_row(
							$wpdb->prepare(
								"SELECT id FROM $table_mjschool_library_book WHERE book_name=%s AND ISBN=%s", $bookdata['book_name'], $bookdata['isbn']
							)
						);
						if ( $existing_book ) {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$wpdb->update( $table_mjschool_library_book, $bookdata, array( 'id' => $existing_book->id ) );
							$module      = 'library';
							$status      = 'Success';
							$emails      = isset( $email ) ? $email : ''; // or collect all emails.
							$log_message = "CSV Data Updated Successful for Book: {$book_name}";
							mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
						} else {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$wpdb->insert( $table_mjschool_library_book, $bookdata );
							$module      = 'library';
							$status      = 'Success';
							$emails      = isset( $email ) ? $email : ''; // or collect all emails.
							$log_message = "CSV Inserted Successful for Book: {$book_name}";
							mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
						}
					}
				}
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce=' . rawurlencode( $nonce ) . '&message=7' ) );
				die();
			}
		} else {
			echo esc_html( implode( '<br>', $errors ) );
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_POST['book_csv_selected'] ) ) {
	if ( isset( $_POST['id'] ) && is_array( $_POST['id'] ) ) {
		$book_list = array();
		$export_ids = array_map( 'intval', wp_unslash( $_POST['id'] ) );
		foreach ( $export_ids as $b_id ) {
			$book_list[] = mjschool_get_book( $b_id );
		}
		if ( ! empty( $book_list ) ) {
			$header    = array(
				'isbn',
				'cat_id',
				'book_name',
				'book_number',
				'publisher',
				'author_name',
				'rack_location',
				'price',
				'quantity',
				'description',
			);
			$filename  = 'export/mjschool-export-book.csv';
			$file_path = MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename;
			// Open file for writing.
			$fh = fopen( $file_path, 'w' );
			if ( ! $fh ) {
				wp_die( esc_html__( 'Unable to open file for writing.', 'mjschool' ) );
			}
			// Write header row.
			fputcsv( $fh, $header );
			// Write book data.
			foreach ( $book_list as $retrive_data ) {
				$row = array(
					$retrive_data->ISBN,
					get_the_title( $retrive_data->cat_id ),
					$retrive_data->book_name,
					$retrive_data->book_number,
					$retrive_data->publisher,
					$retrive_data->author_name,
					get_the_title( $retrive_data->rack_location ),
					$retrive_data->price,
					$retrive_data->quentity,
					$retrive_data->description,
				);
				fputcsv( $fh, $row );
			}
			// Close the file.
			fclose( $fh );
			// Start file download.
			ob_clean();
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment; filename="mjschool-export-book.csv"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			readfile( $file_path );
			die();
		} else {
			echo "<div style='background: red; border: 1px solid; color: white; font-size: 17px; padding: 10px; width: 98%;'>" . esc_html__( 'Records not found.', 'mjschool' ) . "</div>";
		}
	}
}
if ( isset( $_POST['save_book'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_book_admin_nonce' ) ) {
		$redirect_nonce = wp_create_nonce( 'mjschool_library_tab' );
		if ( $action === 'edit' ) {
			$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
			if ( wp_verify_nonce( $nonce_action, 'edit_action' ) ) {
				$book_id                   = isset( $_REQUEST['book_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['book_id'] ) ) : '';
				$result                    = $mjschool_obj_lib->mjschool_add_book( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'library';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $book_id );
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce=' . rawurlencode( $redirect_nonce ) . '&message=1' ) );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result                    = $mjschool_obj_lib->mjschool_add_book( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'library';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce=' . rawurlencode( $redirect_nonce ) . '&message=2' ) );
				die();
			}
		}
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'booklist';
?>
<div class="mjschool-page-inner"><!-- mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Book Updated Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Book Added Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Issue Book Updated Successfully.', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'Book Submitted Successfully.', 'mjschool' );
				break;
			case '6':
				$message_string = esc_html__( 'Issue Book Deleted Successfully.', 'mjschool' );
				break;
			case '7':
				$message_string = esc_html__( 'Book Uploaded Successfully.', 'mjschool' );
				break;
			case '8':
				$message_string = esc_html__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
				break;
			case '9':
				$message_string = esc_html__( 'File size limit 2 MB.', 'mjschool' );
				break;
			case '10':
				$message_string = esc_html__( 'Book Deleted Successfully.', 'mjschool' );
				break;
		}
		?>
		<div class="row"><!-- Row. -->
			<?php
			if ( $message ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php echo esc_html( $message_string ); ?></p>
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
				</div>
				<?php
			}
			?>
			<div class="col-md-12 mjschool-custom-padding-0"><!-- col-md-12. -->
				<?php
				if ( $active_tab === 'issue_return' || $active_tab === 'view_book' ) {
					echo '';
				} else {
					?>
					<?php $nonce = wp_create_nonce( 'mjschool_library_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist"><!--Start nav-tabs. -->
						<li class="<?php if ( $active_tab === 'booklist' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'booklist' ? 'nav-tab-active' : ''; ?>">
								<?php esc_html_e( 'Book List', 'mjschool' ); ?>
							</a>
						</li>
						<?php if ( $action === 'edit' && isset( $_REQUEST['book_id'] ) ) { 
							$book_id_param = isset( $_REQUEST['book_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['book_id'] ) ) : '';
							?>
							<li class="<?php if ( $active_tab === 'addbook' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&issuebook_id=' . rawurlencode( $book_id_param ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addbook' ? 'nav-tab-active' : ''; ?>">
									<?php esc_html_e( 'Edit Book', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						} else {
							?>
							<?php
							if ( $user_access_add === '1' ) {
								?>
								<?php if ( $mjschool_page_name === 'mjschool_library' && $active_tab === 'addbook' ) { ?>
									<li class="<?php if ( $active_tab === 'addbook' ) { ?>active<?php } ?>">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=addbook' ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addbook' ? 'nav-tab-active' : ''; ?>">
											<?php esc_html_e( 'Add Book', 'mjschool' ); ?>
										</a>
									</li>
									<?php
								}
							}
						}
						?>
						<li class="<?php if ( $active_tab === 'issuelist' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=issuelist&_wpnonce=' . rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'issuelist' ? 'nav-tab-active' : ''; ?>">
								<?php esc_html_e( 'Issue & Return', 'mjschool' ); ?>
							</a>
						</li>
					</ul><!--End nav-tabs. -->
					<?php
				}
				if ( $active_tab === 'booklist' ) {

					// Check nonce for book list tab.
					if ( isset( $_GET['tab'] ) ) {
						$tab_nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
						if ( ! wp_verify_nonce( $tab_nonce, 'mjschool_library_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}
					if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
						?>
						<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url( 'https://www.youtube.com/embed/CZQzPhCPIr4?si=Hg16bHUL2gzi9xLA' ); ?>" title="<?php esc_attr_e( 'Library Module Setup', 'mjschool' ); ?>">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-youtube-icon.png' ); ?>" alt="<?php esc_attr_e( 'YouTube', 'mjschool' ); ?>">
						</a>
						<?php
					}
					$retrieve_books = $mjschool_obj_lib->mjschool_get_all_books();
					if ( ! empty( $retrieve_books ) ) {
						?>
						<div class="mjschool-panel-body"><!--mjschool-panel-body. -->
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="book_list" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Book Title', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'ISBN', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Book Category', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Book Price', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Author Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Publisher', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Rack Location', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Remaining Quantity', 'mjschool' ); ?></th>
												<?php
												if ( ! empty( $user_custom_field ) ) {
													foreach ( $user_custom_field as $custom_field ) {
														if ( $custom_field->show_in_table === '1' ) {
															?>
															<th><?php echo esc_html( $custom_field->field_label ); ?></th>
															<?php
														}
													}
												}
												?>
												<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											if ( ! empty( $retrieve_books ) ) {
												$i = 0;
												foreach ( $retrieve_books as $retrieved_data ) {
													$book_id = mjschool_encrypt_id( $retrieved_data->id );
													?>
													<tr>
														<td class="mjschool-checkbox-width-10px">
															<input type="checkbox" class="mjschool-sub-chk selected_book select-checkbox" name="id[]" value="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>">
														</td>
														<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-library.png' ); ?>" class="img-circle" /></td>
														
														<td>
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=view_book&book_id=' . rawurlencode( $book_id ) ) ); ?>">
																<?php echo esc_html( stripslashes( $retrieved_data->book_name ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Book Title', 'mjschool' ); ?>"></i>
															</a>
														</td>
														<td>
															<?php echo esc_html( $retrieved_data->ISBN ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'ISBN', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( get_the_title( $retrieved_data->cat_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Book Category', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->price, 2, '.', '' ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Book Price', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( stripslashes( $retrieved_data->author_name ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Author Name', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( ! empty( $retrieved_data->publisher ) ) {
																echo esc_html( stripslashes( $retrieved_data->publisher ) );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Publisher', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( $retrieved_data->rack_location !== '0' ) {
																echo esc_html( get_the_title( $retrieved_data->rack_location ) );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Rack Location', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															echo esc_html( $retrieved_data->quentity ) . ' ' . esc_html__( 'Out of', 'mjschool' ) . ' ' . esc_html( $retrieved_data->total_quentity );
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Remaining Quantity', 'mjschool' ); ?>"></i>
														</td>
														<?php
														// Custom Field Values.
														if ( ! empty( $user_custom_field ) ) {
															foreach ( $user_custom_field as $custom_field ) {
																if ( $custom_field->show_in_table === '1' ) {
																	$module             = 'library';
																	$custom_field_id    = $custom_field->id;
																	$module_record_id   = $retrieved_data->id;
																	$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
																	if ( $custom_field->field_type === 'date' ) {
																		?>
																		<td>
																			<?php
																			if ( ! empty( $custom_field_value ) ) {
																				echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
																			} else {
																				esc_html_e( 'N/A', 'mjschool' );
																			}
																			?>
																		</td>
																		<?php
																	} elseif ( $custom_field->field_type === 'file' ) {
																		?>
																		<td>
																			<?php
																			if ( ! empty( $custom_field_value ) ) {
																				?>
																				<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile">
																					<button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
																				</a>
																				<?php
																			} else {
																				esc_html_e( 'N/A', 'mjschool' );
																			}
																			?>
																		</td>
																		<?php
																	} else {
																		?>
																		<td> 
																			<?php
																			if ( ! empty( $custom_field_value ) ) {
																				echo esc_html( $custom_field_value );
																			} else {
																				esc_html_e( 'N/A', 'mjschool' );
																			}
																			?>
																		</td>
																		<?php
																	}
																}
															}
														}
														?>
														<td class="action">
															<div class="mjschool-user-dropdown">
																<ul  class="mjschool_ul_style">
																	<li >
																		<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																			
																			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																			
																		</a>
																		<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=view_book&book_id=' . rawurlencode( $book_id ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																			</li>
																			<?php
																			if ( $user_access_edit === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=addbook&action=edit&book_id=' . rawurlencode( $book_id ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?> </a>
																				</li>
																				<?php
																			}
																			?>
																			<?php
																			if ( $user_access_delete === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=booklist&action=delete&book_id=' . rawurlencode( $book_id ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
																				</li>
																				<?php
																			}
																			?>
																		</ul>
																	</li>
																</ul>
															</div>
														</td>
													</tr>
													<?php
													++$i;
												}
											}
											?>
										</tbody>
									</table>
									<div class="mjschool-print-button pull-left">
										<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>" >
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										
										<?php
										if ( $user_access_delete === '1' ) { ?>
											<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_book" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
											<?php
										} ?>
										<button data-toggle="tooltip" title="<?php esc_attr_e( 'Import CSV', 'mjschool' ); ?>" type="button" name="import_csv" class="importdata mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-export-csv.png' ); ?>"></button>
										<button data-toggle="tooltip" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" name="book_csv_selected" class="book_csv_selected_alert mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-import-csv.png' ); ?>"></button>
										<button data-toggle="tooltip" title="<?php esc_attr_e( 'CSV logs', 'mjschool' ); ?>" name="csv_log" type="button" class="mjschool-download-csv-log mjschool-export-import-csv-btn mjschool-custom-padding-0" id="library"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-import-csv.png' ); ?>"></button>
										
									</div>
								</form>
							</div>
						</div><!--mjschool-panel-body. -->
						<?php
					} elseif ( $user_access_add === '1' ) {
						?>
						<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px mjschool-no-data-margin row">
							<div class="offset-md-2 col-md-4">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=addbook' ) ); ?>">
									
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
									
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<div class="col-md-4">
								<a data-toggle="tooltip" name="import_csv" type="button" class="importdata">
									
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-Import-list.png' ); ?>">
									
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to import CSV.', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="mjschool-calendar-event-new">
							
							<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							
						</div>
						<?php
					}
				}
				if ( $active_tab === 'addbook' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/library/add-new-book.php';
				}
				if ( $active_tab === 'issuelist' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/library/issue-list.php';
				}
				if ( $active_tab === 'issue_return' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/library/issue-return.php';
				}
				if ( $active_tab === 'view_book' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/library/view-book.php';
				}
				?>
			</div><!-- col-md-12. -->
		</div><!-- Row. -->
	</div><!-- mjschool-main-list-margin-15px. -->
</div><!-- mjschool-page-inner. -->