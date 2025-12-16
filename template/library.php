<?php
/**
 * Library Management View/Controller.
 *
 * This file is the main entry point for the Library section, handling the list of
 * books, book issues, and book returns. It displays different views based on user role
 * and URL parameters, allowing for CRUD operations on library records.
 *
 * Key features include:
 * - **Access Control:** Enforces permissions based on the current user's role ($user_access).
 * - **View Modes:** Supports displaying lists of Books, Book Issues, or other library-related data.
 * - **Book Issue/Return Logic:** Provides functionality, including AJAX calls, to issue and accept returns for books.
 * - **DataTables:** Initializes a jQuery DataTables instance for managing book and issue lists.
 * - **User Role Specific Actions:** Conditionally displays actions like "Accept Returns" only for authorized users (e.g., 'supportstaff', 'teacher').
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
// --------------- Access-wise role. -----------//
$mjschool_role_name           = mjschool_get_user_role( get_current_user_id() );
$mjschool_obj = new MJSchool_Management( get_current_user_id() );
$user_access         = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( isset($user_access['view']) && $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( isset($user_access['edit']) && $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( isset($user_access['delete']) && $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( isset($user_access['add']) && $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'library';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="view_popup"></div>
			<div class="invoice_data"></div>
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<!-- End POP-UP Code. -->
<?php
$mjschool_obj_lib = new Mjschool_Library();
// --------------Delete code.-------------------------------
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = $mjschool_obj_lib->mjschool_delete_book( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['book_id'])) ) );
		if ( $result ) {
			$nonce = wp_create_nonce( 'mjschool_library_tab' );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=booklist&_wpnonce='.esc_attr( $nonce ).'&message=1' ));
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_POST['delete_selected_book'] ) ) {
	
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_books' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( ! empty( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $id ) {
			$result = $mjschool_obj_lib->mjschool_delete_book( intval( $id ) );
		}
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=booklist&message=1' ));
		exit;
	}
}
// ------------------Edit-Add code. ------------------------------
if ( isset( $_POST['save_book'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_book_frontend_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_library_tab' );
		if ( $_REQUEST['action'] === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$book_id                   = intval(wp_unslash($_REQUEST['book_id']));
				$result                    = $mjschool_obj_lib->mjschool_add_book( wp_unslash($_POST) );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'library';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $book_id );
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=booklist&_wpnonce='.esc_attr( $nonce ).'&message=4' ));
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result                    = $mjschool_obj_lib->mjschool_add_book( wp_unslash($_POST) );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'library';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=booklist&_wpnonce='.esc_attr( $nonce ).'&message=3' ));
				die();
			}
		}
	}
}
// --------------------------- Save issue book. ----------------------//
if ( isset( $_POST['save_issue_book'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'issue_book_frontend_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_library_tab' );
		if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$result = $mjschool_obj_lib->mjschool_add_issue_book( wp_unslash($_POST) );
				if ( $result ) {
					/* Book issue mail notification. */
					if ( isset( $_POST['mjschool_issue_book_mail_service_enable'] ) ) {
						foreach ( $_POST['book_id'] as $book_id ) {
							$smgt_issue_book_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_issue_book_mail_service_enable']));
							if ( $smgt_issue_book_mail_service_enable ) {
								$search['{{student_name}}'] = mjschool_get_teacher( sanitize_text_field(wp_unslash($_POST['student_id'])) );
								$search['{{book_name}}']    = mjschool_get_book_name( $book_id );
								$search['{{issue_date}}']   = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['issue_date'])) );
								$search['{{return_date}}']  = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['return_date'])) );
								$search['{{school_name}}']  = get_option( 'mjschool_name' );
								$message                    = mjschool_string_replacement( $search, get_option( 'mjschool_issue_book_mailcontent' ) );
								$mail_id                    = mjschool_get_email_id_by_user_id( sanitize_text_field(wp_unslash($_POST['student_id'])) );
								$headers  = '';
								$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
								$headers .= "MIME-Version: 1.0\r\n";
								$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
								if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
									wp_mail( $mail_id, get_option( 'mjschool_issue_book_title' ), $message, $headers );
								}
							}
						}
					}
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=issuelist&_wpnonce='.esc_attr( $nonce ).'&message=5' ));
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			if( isset( $_POST['library_card'] ) ){
				$exits = $mjschool_obj_lib->mjschool_exits_library_card_no_submit(sanitize_text_field(wp_unslash($_POST['library_card'])));
				if ( $exits > 0){
					wp_safe_redirect( admin_url() . 'admin.php?page=mjschool_library&tab=view_book&book_id=' . sanitize_text_field(wp_unslash($_GET['book_id'])) . '&issue_message=exits_no' );
				}	
				else{
					$result = $mjschool_obj_lib->mjschool_add_issue_book( wp_unslash($_POST) );
				}
			}
			$result = $mjschool_obj_lib->mjschool_add_issue_book( wp_unslash($_POST) );
			if ( isset($result) ) {
				/* Book Issue Mail Notification. */
				if ( isset( $_POST['mjschool_issue_book_mail_service_enable'] ) ) {
					foreach ( $_POST['book_id'] as $book_id ) {
						$smgt_issue_book_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_issue_book_mail_service_enable']));
						if ( $smgt_issue_book_mail_service_enable ) {
							$search['{{student_name}}'] = mjschool_get_teacher( sanitize_text_field(wp_unslash($_POST['student_id'])) );
							$search['{{book_name}}']    = mjschool_get_book_name( $book_id );
							$search['{{issue_date}}']   = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['issue_date'])) );
							$search['{{return_date}}']  = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['return_date'])) );
							$search['{{school_name}}']  = get_option( 'mjschool_name' );
							$message                    = mjschool_string_replacement( $search, get_option( 'mjschool_issue_book_mailcontent' ) );
							$mail_id                    = mjschool_get_email_id_by_user_id( sanitize_text_field(wp_unslash($_POST['student_id'])) );
							$headers  = '';
							$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
							$headers .= "MIME-Version: 1.0\r\n";
							$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
							if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
								wp_mail( $mail_id, get_option( 'mjschool_issue_book_title' ), $message, $headers );
							}
						}
					}
				}
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=issuelist&_wpnonce='.esc_attr( $nonce ).'&message=6' ));
				die();
			}
		}
	}
}
// ------------------ Submit book. ------------------------//
if ( isset( $_POST['submit_book'] ) ) {
	$result = $mjschool_obj_lib->mjschool_submit_return_book( wp_unslash($_POST) );
	if ( $result ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span> </button>
			<?php esc_html_e( 'Book Submitted Successfully', 'mjschool' ); ?>
		</div>
		<?php
	}
}
/*Save Book Import Data. */
// Upload book list csv.
if ( isset( $_REQUEST['upload_csv_file'] ) ) {
	if ( isset( $_FILES['csv_file'] ) ) {
		$errors     = array();
		$nonce = wp_create_nonce( 'mjschool_library_tab' );
		$file_name  = $_FILES['csv_file']['name'];
		$file_size  = $_FILES['csv_file']['size'];
		$file_tmp   = $_FILES['csv_file']['tmp_name'];
		$file_type  = $_FILES['csv_file']['type'];
		$value      = explode( '.', $_FILES['csv_file']['name'] );
		$file_ext   = strtolower( array_pop( $value ) );
		$extensions = array( 'csv' );
		$upload_dir = wp_upload_dir();
		if ( in_array( $file_ext, $extensions ) === false ) {
			$errors[] = 'this file not allowed, please choose a CSV file.';
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=booklist&_wpnonce='.esc_attr( $nonce ).'&message=8') );
			die();
		}
		if ( $file_size > 2097152 ) {
			$errors[] = 'File size limit 2 MB';
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=booklist&_wpnonce='.esc_attr( $nonce ).'&message=9') );
			die();
		}
		if ( empty( $errors ) === true ) {
			$rows   = array_map( 'str_getcsv', file( $file_tmp ) );
			$header = array_map( 'strtolower', array_shift( $rows ) );
			$csv    = array();
			foreach ( $rows as $row ) {
				$csv = array_combine( $header, $row );
				if ( isset( $csv['isbn'] ) ) {
					$bookdata['isbn'] = $csv['isbn'];
				}
				if ( isset( $csv['book_name'] ) ) {
					$bookdata['book_name'] = $csv['book_name'];
				}
				if ( isset( $csv['author_name'] ) ) {
					$bookdata['author_name'] = $csv['author_name'];
				}
				if ( isset( $csv['rack_location'] ) ) {
					$bookdata['rack_location'] = $csv['rack_location'];
				}
				if ( isset( $csv['cat_id'] ) ) {
					$bookdata['cat_id'] = $csv['cat_id'];
				}
				if ( isset( $csv['price'] ) ) {
					$bookdata['price'] = $csv['price'];
				}
				if ( isset( $csv['quentity'] ) ) {
					$bookdata['quentity'] = $csv['quentity'];
				}
				if ( isset( $csv['description'] ) ) {
					$bookdata['description'] = $csv['description'];
				}
				$bookdata['added_by']   = get_current_user_id();
				$bookdata['added_date'] = date( 'Y-m-d' );
				
				$all_book  = $mjschool_obj_lib->mjschool_get_all_books();
				$book_name = array();
				$book_isbn = array();
				foreach ( $all_book as $book_data ) {
					$book_name[] = $book_data->book_name;
					$book_isbn[] = $book_data->ISBN;
				}
				if ( in_array( $bookdata['book_name'], $book_name ) && in_array( $bookdata['isbn'], $book_isbn ) ) {
					$import_book_name = $bookdata['book_name'];
					$import_isbn      = $bookdata['isbn'];
					$existing_book_data = mjschool_get_existing_library_book($import_book_name, $import_isbn);
					$id['id']           = $existing_book_data->id;
					$result = $mjschool_obj_lib->mjschool_update_library_book($bookdata, $id);
					$success = 1;
				} else {
					$result = $mjschool_obj_lib->mjschool_insert_library_book($bookdata);
					$success = 1;
				}
			}
		} else {
			foreach ( $errors as &$error ) {
				echo esc_html( $error );
			}
		}
		if ( isset( $success ) ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=booklist&_wpnonce='.esc_attr( $nonce ).'&message=7') );
			die();
		}
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'booklist';
?>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Book Deleted successfully', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Issue Book Deleted successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Book Added Successfully.', 'mjschool' );
			break;
		case '4':
			$message_string = esc_html__( 'Book Updated Successfully.', 'mjschool' );
			break;
		case '5':
			$message_string = esc_html__( 'Issue Book Updated Successfully.', 'mjschool' );
			break;
		case '6':
			$message_string = esc_html__( 'Issue Book Added Successfully.', 'mjschool' );
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
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
			
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	?>
	<?php
	// TABBING START.
	if ( $active_tab === 'issue_return' || $active_tab === 'view_book' ) {
		echo '';
	} else {
		?>
		<?php $nonce = wp_create_nonce( 'mjschool_library_tab' ); ?>
		<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist"><!--Start nav-tabs -->
			<li class="<?php if ( $active_tab === 'booklist' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=booklist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab  <?php echo esc_attr( $active_tab  ) === 'booklist' ? 'active' : ''; ?>"> <?php esc_html_e( 'Book List', 'mjschool' ); ?></a> </a>
			</li>
			<?php
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' && sanitize_text_field(wp_unslash($_REQUEST['tab'])) === 'addbook' ) {
				?>
				<li class="<?php if ( $active_tab === 'addbook' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . intval( wp_unslash( $_REQUEST['book_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab  <?php echo esc_attr( $active_tab  ) === 'addbook' ? 'active' : ''; ?>"> <?php esc_html_e( 'Edit Book', 'mjschool' ); ?></a> </a>
				</li>
				<?php
			} elseif ( $active_tab === 'addbook' ) {
				if ( isset($user_access['add']) && $user_access['add'] === '1' ) {
					?>
					<li class="<?php if ( $active_tab === 'addbook' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=addbook' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addbook' ? 'active' : ''; ?>"> <?php esc_html_e( 'Add Book', 'mjschool' ); ?></a> </a>
					</li>
					<?php
				}
			}
			?>
			<li class="<?php if ( $active_tab === 'issuelist' ) { ?> active<?php } ?>">
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=issuelist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'issuelist' ? 'active' : ''; ?>"> <?php esc_html_e( 'Issue & Return', 'mjschool' ); ?></a> </a>
			</li>
		</ul>
		<?php
	}
	// BOOK LIST TAB START.
	if ( $active_tab === 'booklist' ) {
		// Check nonce for book list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_library_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		?>
		<div class="mjschool-panel-body">
			<form id="mjschool-common-form" name="mjschool-common-form" method="post">
				<?php wp_nonce_field( 'bulk_delete_books' ); ?>
				<?php
				$user_id = get_current_user_id();
				// ------- BOOK DATA FOR STUDENT. ---------//
				if ( $mjschool_obj->role === 'supportstaff' ) {
					$own_data = isset($user_access['own_data']) ? $user_access['own_data'] : '0';
					if ( $own_data === '1' ) {
						$retrieve_books = $mjschool_obj_lib->mjschool_get_all_books_created_by( $user_id );
					} else {
						$retrieve_books = $mjschool_obj_lib->mjschool_get_all_books();
					}
				} else {
					$retrieve_books = $mjschool_obj_lib->mjschool_get_all_books();
				}
				if ( ! empty( $retrieve_books ) ) {
					?>
					<div class="table-responsive">
						<table id="mjschool-liabrary-book-list" class="display dataTable mjschool-booklist-datatable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $mjschool_role_name === 'supportstaff' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
										<?php
									}
									?>
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
										?>
										<tr>
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<td class="mjschool-checkbox-width-10px">
													<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->id ); ?>">
												</td>
												<?php
											}
											 ?>
											<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-library.png"); ?>" class="img-circle" /></td>
											
											<td><a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=view_book&book_id=' . mjschool_encrypt_id( $retrieved_data->id ) ); ?>"><?php echo esc_html( stripslashes( $retrieved_data->book_name ) ); ?></a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Book Title', 'mjschool' ); ?>"></td>
											<td><?php echo esc_html( $retrieved_data->ISBN ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'ISBN', 'mjschool' ); ?>"></i></td>
											<td>
												<?php echo esc_html( get_the_title( $retrieved_data->cat_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Book Category', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . number_format( $retrieved_data->price, 2, '.', '' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Book Price', 'mjschool' ); ?>"></i>
											</td>
											<td><?php echo esc_html( stripslashes( $retrieved_data->author_name ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Author Name', 'mjschool' ); ?>"></td>
											<td>
												<?php
												if ( ! empty( $retrieved_data->publisher ) ) {
													echo esc_html( stripslashes( $retrieved_data->publisher ) );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Publisher', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php
												if ( $retrieved_data->rack_location !== '0' ) {
													echo esc_html( get_the_title( $retrieved_data->rack_location ) );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Rack Location', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo esc_html( $retrieved_data->quentity ) . ' ' . esc_html__( 'Out of', 'mjschool' ) . ' ' . esc_html( $retrieved_data->total_quentity ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Remaining Quantity', 'mjschool' ); ?>"></i>
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
																	esc_html_e( 'Not Provided', 'mjschool' );
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
																	<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
																	<?php
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
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
																	esc_html_e( 'Not Provided', 'mjschool' );
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
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
															</a>
															<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																<li class="mjschool-float-left-width-100px">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=view_book&book_id=' . mjschool_encrypt_id( $retrieved_data->id ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																</li>
																<?php
																if ($mjschool_obj->role === 'supportstaff' ) {
																	if (isset($user_access['edit']) && $user_access['edit'] ===  '1') { ?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . mjschool_encrypt_id( $retrieved_data->id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?> </a>
																		</li>
																		<?php
																	}
																	if ($user_access['delete'] === '1') { ?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=booklist&action=delete&book_id=' . mjschool_encrypt_id( $retrieved_data->id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																}
																?>
															</ul>
														</li>
													</ul>
												</div>
											</td>
										</tr>
										<?php
										$i++;
									}
								}
								?>
							</tbody>
						</table>
						<?php
						if ($mjschool_role_name === "supportstaff") {
							?>
							<div class="mjschool-print-button pull-left">
								<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
									<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
									<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
								</button>
								<?php
								if ($mjschool_obj->role === 'supportstaff' ) {
									if ( isset($user_access['delete']) && $user_access['delete'] === '1' ) { ?>
										<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_book" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
										<?php
									}
								} ?>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				} else {
					if ( isset($user_access['add']) && $user_access['add'] === '1' ) {
						?>
						<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
							<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=library&tab=addbook' )); ?>">
								<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
							</a>
							<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
								<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="mjschool-calendar-event-new">
							<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
						</div>
						<?php  
					}
				}
				?>
			</form>
		</div>
		<?php
	}
	// ADD BOOK TAB START.
	if ( $active_tab === 'addbook' ) {
		$bookid = 0;
		if ( isset( $_REQUEST['book_id'] ) ) {
			// $bookid=$_REQUEST['book_id'];
			$bookid = intval( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['book_id'])) ) );
		}
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$edit   = 1;
			$result = $mjschool_obj_lib->mjschool_get_single_books( $bookid );
		}
		?>
		<div class="mjschool-panel-body"><!--Mjschool-panel-body. -->
			<form name="book_form" action="" method="post" class="mt-3 mjschool-form-horizontal" id="book_form" enctype="multipart/form-data">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="book_id" value="<?php echo esc_attr( $bookid ); ?>">
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'BooK Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="book_name" class="form-control validate[required,custom[address_description_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_html( stripslashes( $result->book_name ) );} ?>" name="book_name">
									<label  for="book_name"><?php esc_html_e( 'Book Title', 'mjschool' ); ?><span class="mjschool-require-field"><span class="mjschool-require-field">*</span></span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="book_number" class="form-control validate[required] text-input" maxlength="10" type="number" value="<?php if ( $edit ) { echo esc_html( stripslashes( $result->book_number ) );} ?>" name="book_number">
									<label  for="book_number"><?php esc_html_e( 'Book Number', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="isbn" class="form-control validate[required,custom[address_description_validation]]" type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $result->ISBN );} ?>" name="isbn">
									<label  for="isbn"><?php esc_html_e( 'ISBN Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input mjschool-rtl-margin-0px">
								<div class="col-md-12 form-control">
									<input id="publisher" class="form-control validate[required,custom[city_state_country_validation]] text-input" type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_html( stripslashes( $result->publisher ) );} ?>" name="publisher">
									<label  for="publisher"><?php esc_html_e( 'Publisher', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="author_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_html( stripslashes( $result->author_name ) );} ?>" name="author_name">
									<label  for="author_name"><?php esc_html_e( 'Author Name', 'mjschool' ); ?><span class="mjschool-require-field"><span class="mjschool-require-field">*</span></span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
							<label class="ml-1 mjschool-custom-top-label top" for="category_data"><?php esc_html_e( 'Select Category', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<select name="bookcat_id" id="category_data" class="mjschool-line-height-30px form-control smgt_bookcategory validate[required] mjschool-width-100px">
								<option value=""><?php esc_html_e( 'Select Category', 'mjschool' ); ?></option>
								<?php
								$activity_category = mjschool_get_all_category( 'smgt_bookcategory' );
								if ( ! empty( $activity_category ) ) {
									if ( $edit ) {
										$fees_val = $result->cat_id;
									} else {
										$fees_val = '';
									}
									foreach ( $activity_category as $retrive_data ) {
										?>
										<option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $fees_val ); ?>><?php echo esc_attr( $retrive_data->post_title ); ?> </option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3">
							<button id="mjschool-addremove-cat" class="mjschool-rtl-margin-top-15px mjschool-add-btn sibling_add_remove" model="smgt_bookcategory"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="book_price" class="form-control validate[required,min[0],maxSize[8]]" type="number" step="0.01" value="<?php if ( $edit ) { echo esc_attr( $result->price );} ?>" name="book_price">
									<label  for="book_price"><?php echo esc_html__( 'Price', 'mjschool' ) . '( ' . esc_html( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) ) . ' )'; ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
							<label class="ml-1 mjschool-custom-top-label top" for="rack_category_data"><?php esc_html_e( 'Rack Location', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<select name="rack_id" id="rack_category_data" class="mjschool-line-height-30px form-control smgt_rack validate[required] mjschool-max-width-100px">
								<option value=""><?php esc_html_e( 'Select Rack Location', 'mjschool' ); ?></option>
								<?php
								$activity_category = mjschool_get_all_category( 'smgt_rack' );
								if ( ! empty( $activity_category ) ) {
									if ( $edit ) {
										$rank_val = $result->rack_location;
									} else {
										$rank_val = '';
									}
									foreach ( $activity_category as $retrive_data ) {
										?>
										<option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $rank_val ); ?>><?php echo esc_attr( $retrive_data->post_title ); ?> </option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3">
							<button id="mjschool-addremove-cat" class="mjschool-rtl-margin-top-15px mjschool-add-btn sibling_add_remove" model="smgt_rack"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>
						</div>
						<?php wp_nonce_field( 'save_book_frontend_nonce' ); ?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="quentity" class="form-control validate[required,min[0],maxSize[5]]" type="number" value="<?php if ( $edit ) { echo esc_attr( $result->quentity );} ?>" name="quentity">
									<label  for="quentity"><?php esc_html_e( 'Total Quantity', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="post_date" class="datepicker form-control validate[required] text-input" type="text" name="post_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $result->added_date ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) );} ?>" readonly>
									<label  for="post_date"><?php esc_html_e( 'Post Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea id="description" name="description" class="mjschool-textarea-height-47px validate[custom[description_validation]] form-control"><?php if ( $edit ) { echo esc_attr( $result->description ); } ?></textarea>
										<span class="mjschool-txt-title-label"></span>
										<label class="text-area address active" for="description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'library';
				$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6">
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Book', 'mjschool' ); } else { esc_html_e( 'Add Book', 'mjschool' ); } ?>" name="save_book" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form>
		</div><!--Mjschool-panel-body. -->
		<?php
	}
	// ISSUE LIST TAB START.
	if ( $active_tab === 'issuelist' ) {
		// Check nonce for book list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_library_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		if ( isset($user_access['own_data']) && $user_access['own_data'] === '1' ) {
			$alluser = array();
			if ( $mjschool_role_name === 'student' ) {
				$alluser[] = get_userdata( get_current_user_id() );
			} elseif ( $mjschool_role_name === 'parent' ) {
				$childs  = $mjschool_obj->child_list;
				$alluser = array();
				if ( ! empty( $childs ) ) {
					foreach ( $childs as $key => $value ) {
						$alluser[] = get_userdata( $value );
					}
				}
			} elseif ( $mjschool_role_name === 'teacher' ) {
				$alluser[] = get_userdata( get_current_user_id() );
			} else {
				$exclude_ids = mjschool_approve_student_list(); // Ensure this returns an array.
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
				$alluser = array_merge( $students, $teachers );
			}
		} else {
			$exclude_ids = mjschool_approve_student_list(); // Ensure this returns an array.
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
			$alluser = array_merge( $students, $teachers );
		}
		if ( ! empty( $alluser ) ) {
			?>
			<div class="mjschool-panel-body"><!--Mjschool-panel-body. -->
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
												<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=issue_return&user_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>">
													<?php  
													?>
													<?php
													$uid = $retrieved_data->ID;
													$mjschool_role_name = mjschool_get_user_role($uid);
													$umetadata = mjschool_get_user_image($uid);
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
												<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=issue_return&user_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>" idtest=<?php echo esc_attr( $retrieved_data->ID ); ?>><?php echo esc_attr( $retrieved_data->display_name ); ?></a>
												<br>
												<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></span>
											</td>
											<td>
												<?php
												if ( ! empty( $library_card_no ) ) {
													$library_card = $library_card_no[0]->library_card_no;
													if ( ! empty( $library_card ) ) {
														echo esc_html( $library_card );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
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
													esc_html_e( 'Not Provided', 'mjschool' );
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
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=issue_return&user_id=' . mjschool_encrypt_id( $retrieved_data->ID ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-book"> </i><?php esc_html_e( 'Issue & Return', 'mjschool' ); ?> </a>
																</li>
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
					</form>
				</div>
			</div> <!--Mjschool-panel-body. -->
			<?php
		} elseif ( $user_access_add === '1' ) {
			 ?>
			<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
				<a href="<?php echo esc_url( admin_url() . 'admin.php?page=mjschool_library&tab=issuebook' ); ?>">
					<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
				</a>
				<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
					<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="mjschool-calendar-event-new">
				<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
			</div>
			<?php  
		}
	}
	// Issue return tab start.
	if ( $active_tab === 'issue_return' ) {
		$active_tab1      = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
		$user_id          = intval( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['user_id'])) ) );
		$user_data        = get_userdata( $user_id );
		$mjschool_role_name        = mjschool_get_user_role( $user_id );
		$mjschool_obj_lib = new Mjschool_Library();
		$library_card_no  = $mjschool_obj_lib->mjschool_get_library_card_for_student( $user_id );
		?>
		<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start panel body div.-->
			<div class="content-body">
				<section id="mjschool-user-information">
					<div class="mjschool-view-page-header-bg">
						<div class="row">
							<?php ?>
							<div class="col-xl-10 col-md-9 col-sm-10">
								<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
									<?php
									$userimage = mjschool_get_user_image($user_data->ID);
									?>
									<img class="mjschool-user-view-profile-image" src="<?php if ( ! empty( $userimage ) ) { echo esc_url($userimage); } else { if ($mjschool_role_name === "student") { echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); } elseif ($mjschool_role_name === "teacher") { echo esc_html( get_option( 'mjschool_teacher_thumb_new' ) ); } } ?>">
									<div class="row mjschool-profile-user-name">
										<div class="mjschool-float-left mjschool-view-top1">
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
												<label class="mjschool-view-user-name-label"><?php echo esc_html( $user_data->display_name); ?></label>
											</div>
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
												<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<label class="mjschool-color-white-rs"><?php echo esc_html( $user_data->mobile_number); ?></label>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-xl-12 col-md-12 col-sm-12">
											<div class="mjschool-view-top2">
												<div class="row mjschool-view-user-doctor-label">
													<div class="col-md-12 mjschool-address-student-div">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page"><?php echo esc_html( $user_data->address); ?></label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2 mjschool-add-btn_possition_teacher_res">
								<div class="mjschool-group-thumbs">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
								</div>
							</div>
							<?php  
							?>
						</div>
					</div>
				</section>
				<section id="body_area" class="teacher_view_tab body_areas">
					<div class="row">
						<div class="col-xl-12 col-md-12 col-sm-12 mjschool-rs-width">
							<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
								<li class="<?php if ( $active_tab1 === 'general' ) { ?> active<?php } ?>">
									<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=issue_return&user_id=' . intval( wp_unslash( $_REQUEST['user_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>"> <i class="fas fa-book"> </i> <?php esc_html_e( 'issue & Return Details', 'mjschool' ); ?></a>
								</li>
							</ul>
						</div>
					</div>
				</section>
				<section id="mjschool-body-content-area">
					<div class="mjschool-panel-body"><!-- Start panel body div.-->
						<?php
						if ( $active_tab1 === 'general' ) {
							if ( isset( $_POST['save_issue_book'] ) ) {
								$result = $mjschool_obj_lib->mjschool_add_issue_book( wp_unslash($_POST) );
								if ( $result ) {
									/* Book Issue Mail Notification. */
									if ( isset( $_POST['mjschool_issue_book_mail_service_enable'] ) ) {
										foreach ( $_POST['book_id'] as $book_id ) {
											$smgt_issue_book_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_issue_book_mail_service_enable']));
											if ( $smgt_issue_book_mail_service_enable ) {
												$search['{{student_name}}'] = mjschool_get_teacher( sanitize_text_field(wp_unslash($_POST['student_id'])) );
												$search['{{book_name}}']    = mjschool_get_book_name( $book_id );
												$search['{{issue_date}}']   = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['issue_date'])) );
												$search['{{return_date}}']  = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['return_date'])) );
												$search['{{school_name}}']  = get_option( 'mjschool_name' );
												$message                    = mjschool_string_replacement( $search, get_option( 'mjschool_issue_book_mailcontent' ) );
												$mail_id                    = mjschool_get_email_id_by_user_id( sanitize_text_field(wp_unslash($_POST['student_id'])) );
												$headers  = '';
												$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
												$headers .= "MIME-Version: 1.0\r\n";
												$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
												if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
													wp_mail( $mail_id, get_option( 'mjschool_issue_book_title' ), $message, $headers );
												}
											}
										}
									}
									wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=issue_return&user_id=' . intval(wp_unslash($_REQUEST['user_id'])) . '&issue_message=issue_success' ));
									die();
								}
							}
							if ( isset( $_POST['return_book'] ) ) {
								$result = $mjschool_obj_lib->mjschool_submit_return_book( wp_unslash($_POST) );
								if ( $result ) {
									wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=issue_return&user_id=' . intval(wp_unslash($_REQUEST['user_id'])) . '&issue_message=return_success' ));
									die();
								}
							}
							?>
							<?php
							if ( isset( $_REQUEST['issue_message'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['issue_message'])) === 'issue_success' ) ) {
								?>
								<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
									
									<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
									
									<p><?php esc_html_e( 'Book Issued Successfully.', 'mjschool' ); ?></p>
								</div>
								<?php
							}
							if ( isset( $_REQUEST['issue_message'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['issue_message'])) === 'return_success' ) ) {
								?>
								<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
									
									<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
									
									<p><?php esc_html_e( 'Book Returned Successfully.', 'mjschool' ); ?></p>
								</div>
								<?php
							}
							?>
							<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
								<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br />
									<label class="mjschool-view-page-content-labels"> <?php echo esc_html( $user_data->user_email ); ?> </label>
								</div>
								<div class="col-xl-3 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Library Card No', 'mjschool' ); ?> </label><br />
									<label class="mjschool-view-page-content-labels">
										<?php
										if ( ! empty( $library_card_no ) ) {
											$library_card = $library_card_no[0]->library_card_no;
											if ( ! empty( $library_card ) ) {
												echo esc_html( $library_card );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
										} else {
											esc_html_e( 'Not Provided', 'mjschool' );
										}
										?>
									</label>
								</div>
								<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br />
									<label class="mjschool-view-page-content-labels"> <?php echo esc_html( ucfirst( $user_data->gender ) ); ?></label>
								</div>
								<div class="col-xl-3 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br />
									<label class="mjschool-view-page-content-labels"> 
										<?php
										if ( ! empty( $user_data->birth_date ) ) {
											echo esc_html( mjschool_get_date_in_input_box( $user_data->birth_date ) );
										} else {
											esc_html_e( 'Not Provided', 'mjschool' );
										}
										?>
									</label>
								</div>
							</div>
							<!-- Issue book form.  -->
							<div class="row mjschool-margin-top-20px">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
										<?php
										if ( isset($user_access['add']) && $user_access['add'] === '1' ) {
											?>
											<div class="mjschool-guardian-div">
												<form name="book_form" action="" method="post" class="mjschool-form-horizontal" id="book_form">
													<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
													<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
													<input type="hidden" name="issue_id" value="<?php echo esc_attr( $issuebook_id ); ?>">
													<input type="hidden" name="student_id" value="<?php echo esc_attr( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['user_id'])) ) ); ?>">
													<div class="header">
														<h3 class="mjschool-first-header"><?php esc_html_e( 'Issue Book Information', 'mjschool' ); ?></h3>
													</div>
													<div class="form-body mjschool-user-form">
														<div class="row">
															<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
																<div class="form-group input">
																	<div class="col-md-12 form-control">
																		<?php
																		$library_card_name = '';
																		if ( ! empty( $library_card_no ) ) {
																			$library_card = $library_card_no[0]->library_card_no;
																			if ( ! empty( $library_card ) ) {
																				$library_card_name = $library_card;
																			}
																		}
																		?>
																		<input id="library_card" class="form-control validate[required,custom[address_description_validation]]" type="text" maxlength="50" value="<?php echo esc_attr( $library_card_name ); ?>" name="library_card" <?php if ( ! empty( $library_card_name ) ) { echo 'readonly'; } ?> >
																		<label  for="library_card"><?php esc_html_e( 'Library Card No.', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																	</div>
																</div>
															</div>
															<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-error-msg-left-margin mjschool-rtl-margin-0px">
																<label class="ml-1 mjschool-custom-top-label top" for="category_data validate[required]"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																<select name="bookcat_id" id="bookcat_list" class="form-control mjschool-max-width-100px mjschool_heights_47px" >
																	<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
																	<?php
																	$book_cat = '';
																	if ( $edit ) {
																		$book_cat = $result->cat_id;
																	}
																	$category_data = $mjschool_obj_lib->mjschool_get_bookcat();
																	if ( ! empty( $category_data ) ) {
																		foreach ( $category_data as $retrieved_data ) {
																			echo '<option value="' . esc_attr( $retrieved_data->ID ) . '" ' . selected( $book_cat, $retrieved_data->ID ) . '>' . esc_html( $retrieved_data->post_title ) . '</option>';
																		}
																	}
																	?>
																</select>
															</div>
															<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mb-3 mjschool-error-msg-top-margin">
																<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
																	<?php
																	$book_id    = 0;
																	$books_data = $mjschool_obj_lib->mjschool_get_all_books();
																	?>
																	<select name="book_id[]" id="book_list1" multiple="multiple" class="form-control validate[required]"></select>
																	<span class="mjschool-multiselect-label">
																		<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Book', 'mjschool' ); ?><span class="required">*</span></label>
																	</span>
																</div>
															</div>
															<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
																<label class="ml-1 mjschool-custom-top-label top" for="period"><?php esc_html_e( 'Period ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																<select name="period_id" id="category_data" class="mjschool-line-height-30px form-control issue_period validate[required] mjschool-max-width-100px period_type">
																	<option value=""><?php esc_html_e( 'Select Period', 'mjschool' ); ?></option>
																	<?php
																	if ( $edit ) {
																		$period_id = $result->period;
																	} else {
																		$period_id = get_option( 'mjschool_return_period' );
																	}
																	$category_data = $mjschool_obj_lib->mjschool_get_period_list();
																	if ( ! empty( $category_data ) ) {
																		foreach ( $category_data as $retrieved_data ) {
																			echo '<option value="' . esc_attr( $retrieved_data->ID ) . '" ' . selected( $period_id, $retrieved_data->ID ) . '>' . esc_html( $retrieved_data->post_title ) . ' ' . esc_html__( 'Days', 'mjschool' ) . '</option>';
																		}
																	}
																	?>
																</select>
															</div>
															<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3">
																<button id="mjschool-addremove-cat" class="mjschool-rtl-margin-top-15px mjschool-add-btn sibling_add_remove" model="period_type"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>
															</div>
															<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
																<div class="form-group input">
																	<div class="col-md-12 form-control">
																		<input id="issue_date" class="datepicker form-control validate[required] text-input" type="text" name="issue_date" value="<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); ?>" readonly>
																		<label  for="issue_date"><?php esc_html_e( 'Issue Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																	</div>
																</div>
															</div>
															<?php wp_nonce_field( 'save_issuebook_admin_nonce' ); ?>
															<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-error-msg-left-margin">
																<div class="form-group input">
																	<div class="col-md-12 form-control">
																		<input id="return_date" class="form-control validate[required] date_picker text-input" type="text" name="return_date" value="" readonly>
																		<label class="active date_label" for="return_date"><?php esc_html_e( 'Return Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																	</div>
																</div>
															</div>
															<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
																<div class="form-group">
																	<div class="col-md-12 form-control mjschool-rtl-relative-position">
																		<div class="row mjschool-padding-radio">
																			<div>
																				<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="enable"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
																				<input id="chk_mjschool_sent1" type="checkbox" class="mjschool-check-box-input-margin" <?php $smgt_issue_book_mail_service_enable = 0; if ( $smgt_issue_book_mail_service_enable ) { echo 'checked'; } ?> value="1" name="smgt_issue_book_mail_service_enable"> <?php esc_html_e( 'Send Mail', 'mjschool' ); ?>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="form-body mjschool-user-form">
														<div class="row">
															<div class="col-sm-6">
																<input type="submit" value="<?php esc_html_e( 'Issue Book', 'mjschool' ); ?>" name="save_issue_book" class="mjschool-save-btn btn btn-success book_for_alert mjschool-rtl-margin-0px" />
															</div>
														</div>
													</div>
												</form>
											</div>
											<?php
										}
										?>
										<!-- Issue book list.  -->
										<div class="mjschool-panel-body mt-3">
											<div class="header">
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Issue Book List', 'mjschool' ); ?></h3>
											</div>
											<?php
											$issue_data = $mjschool_obj_lib->mjschool_get_all_issuebooks_for_student( $user_id );
											if ( ! empty( $issue_data ) ) {
												?>
												<div class="table-responsive">
													<form id="mjschool-common-form" name="mjschool-common-form" method="post">
														<table id="user_issue_list_second" class="display" cellspacing="0" width="100%">
															<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
																<tr>
																	<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Book Title', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Issue Date', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Due Return Date ', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Accept Return Date ', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Period', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Fine', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
																	<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
																</tr>
															</thead>
															<tbody>
																<?php
																$i = 0;
																foreach ( $issue_data as $retrieved_data ) {
																	$book_data = $mjschool_obj_lib->mjschool_get_single_books( $retrieved_data->book_id );
																	?>
																	<tr>
																		<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-library.png"); ?>" class="img-circle" /></td>
																		<td><?php echo esc_html( stripslashes( mjschool_get_book_name( $retrieved_data->book_id ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Book Title', 'mjschool' ); ?>"></i></td>
																		<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->issue_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Issue Date', 'mjschool' ); ?>"></i> </td>
																		<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Due Return Date', 'mjschool' ); ?>"></i></td>
																		<td>
																			<?php
																			if ( ! empty( $retrieved_data->actual_return_date ) ) {
																				echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->actual_return_date ) );
																			} else {
																				esc_html_e( 'Not Provided', 'mjschool' );
																			}
																			?>
																			<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Accept Return Date', 'mjschool' ); ?>"></i>
																		</td>
																		<td><?php echo esc_html( get_the_title( $retrieved_data->period ) ); ?> <?php esc_html_e( 'Day', 'mjschool' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Period', 'mjschool' ); ?>"></i></td>
																		<td >
																			<?php
																			if ( $retrieved_data->fine === '' || $retrieved_data->fine === 0 ) {
																				esc_html_e( 'Not Provided', 'mjschool' );
																			} else {
																				echo esc_html( mjschool_get_currency_symbol() ) . esc_html( $retrieved_data->fine );
																			}
																			?>
																			<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Fine', 'mjschool' ); ?>"></i>
																		</td>
																		<td >
																			<?php
																			if ( empty($retrieved_data->comment) ) {
																				esc_html_e( 'Not Provided', 'mjschool' );
																			} else {
																				echo esc_html( $retrieved_data->comment );
																			}
																			?>
																			<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php echo empty($retrieved_data->comment) ? 'Comment' : esc_attr($retrieved_data->comment); ?>"> </i>
																		</td>
																		<td class="action">
																			<div class="mjschool-user-dropdown">
																				<?php
																				$current_role = mjschool_get_user_role( get_current_user_id() );
																				?>
																				<ul  class="mjschool_ul_style">
																					<li >
																						<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																						</a>
																						<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																							<?php
																							if ($retrieved_data->status === "Issue" &&  ($current_role === "supportstaff" ||  $current_role === "teacher" ) ) {
																								?>
																								<li class="mjschool-float-left-width-100px">
																									<a idtest=<?php echo esc_attr($retrieved_data->id); ?> id="accept_returns_book_popup" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-accept-book-return.png"); ?>" class="mjschool_heights_13px">&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Accept Returns', 'mjschool' ); ?> </a>
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
																}
																?>
															</tbody>
														</table>
													</form>
												</div>
												<?php
											} else {
												?>
												<div class="mjschool-calendar-event-new">
													<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
												</div>
												<?php
											}
											?>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</section>
			</div>
		</div>
		<?php
	}
	if ( $active_tab === 'view_book' ) {
		$mjschool_obj_lib = new Mjschool_Library();
		if ( isset( $_GET['book_id'] ) && is_array( $_GET['book_id'] ) ) {
			$book_id = reset( sanitize_text_field(wp_unslash($_GET['book_id'])) ); // Get the first book_id from the array.
		} else {
			$book_id = sanitize_text_field(wp_unslash($_GET['book_id'])) ?? ''; // Use single value if not an array.
		}
		// Now safely pass it to your function.
		$bookid                    = mjschool_decrypt_id( $book_id );
		$book_data                 = $mjschool_obj_lib->mjschool_get_single_books( $bookid );
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		?>
		<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start panel body div.-->
			<div class="content-body">
				<section id="mjschool-user-information">
					<div class="mjschool-view-page-header-bg">
						<div class="row">
							<div class="col-xl-10 col-md-9 col-sm-10">
								<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
									<img class="mjschool-user-view-profile-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-library.png"); ?>">
									<div class="row mjschool-profile-user-name">
										<div class="mjschool-float-left mjschool-view-top1">
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
												<label class="mjschool-view-user-name-label"><?php echo esc_html( ucfirst($book_data->book_name ) ); ?></label>
												<?php
												if (isset($user_access['edit']) && $user_access['edit'] === '1' ) {
													?>
													<div class="mjschool-view-user-edit-btn">
														<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . mjschool_encrypt_id( $book_data->id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
														</a>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2 mjschool-add-btn_possition_teacher_res">
								<div class="mjschool-group-thumbs">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
									
								</div>
							</div>
						</div>
					</div>
				</section>
				<section id="mjschool-body-content-area" class="mt-5">
					<div class="mjschool-panel-body"><!-- Start panel body div.-->
						<?php
						if ( isset( $_REQUEST['issue_message'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['issue_message'])) === 'issue_success' ) ) {
							?>
							<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
								
								<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
								
								<p><?php esc_html_e( 'Book Issued Successfully.', 'mjschool' ); ?></p>
							</div>
							<?php
						}
						if ( isset( $_REQUEST['issue_message'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['issue_message'])) === 'return_success' ) ) {
							?>
							<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
								
								<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
									
								</button>
								<p><?php esc_html_e( 'Book Returned Successfully.', 'mjschool' ); ?></p>
							</div>
							<?php
						}
						?>
						<div class="row">
							<div class="col-xl-12 col-md-12 col-sm-12">
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px-rs">
									<div class="mjschool-guardian-div">
										<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Book Information', 'mjschool' ); ?> </label>
										<div class="row">
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'ISBN', 'mjschool' ); ?> </label> <br>
												<?php
												if ( isset($user_access['edit']) && $user_access['edit'] === '1' && empty( $book_data->ISBN ) ) {
													$edit_url = home_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $book_data->ISBN ) ) {
															echo esc_html( $book_data->ISBN );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Book Number', 'mjschool' ); ?> </label><br>
												<?php
												if ( isset($user_access['edit']) && $user_access['edit'] === '1' && empty( $book_data->book_number ) ) {
													$edit_url = home_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $book_data->book_number ) ) {
															echo esc_html( $book_data->book_number );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Book Category', 'mjschool' ); ?> </label><br>
												<?php
												if ( isset($user_access['edit']) && $user_access['edit'] === '1' && empty( $book_data->cat_id ) ) {
													$edit_url = home_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $book_data->cat_id ) ) {
															echo esc_html( get_the_title( $book_data->cat_id ) );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Author Name', 'mjschool' ); ?> </label><br>
												<?php
												if ( isset($user_access['edit']) && $user_access['edit'] === '1' && empty( $book_data->author_name ) ) {
													$edit_url = home_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $book_data->author_name ) ) {
															echo esc_html( $book_data->author_name );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Publisher', 'mjschool' ); ?> </label><br>
												<?php
												if ( isset($user_access['edit']) && $user_access['edit'] === '1' && empty( $book_data->publisher ) ) {
													$edit_url = home_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $book_data->publisher ) ) {
															echo esc_html( $book_data->publisher );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Rack Location', 'mjschool' ); ?> </label><br>
												<?php
												if ( isset($user_access['edit']) && $user_access['edit'] === '1' && empty( $book_data->rack_location ) ) {
													$edit_url = home_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $book_data->rack_location ) ) {
															echo esc_html( get_the_title( $book_data->rack_location ) );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Book Price', 'mjschool' ); ?> </label><br>
												<?php
												if ( isset($user_access['edit']) && $user_access['edit'] === '1' && empty( $book_data->price ) ) {
													$edit_url = home_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $book_data->price, 2, '.', '' ) ) ); ?></label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Remaining Quantity', 'mjschool' ); ?> </label><br>
												<?php
												if ( isset($user_access['edit']) && $user_access['edit'] === '1' && empty( $book_data->total_quentity ) ) {
													$edit_url = home_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( $book_data->quentity ) . ' ' . esc_html__( 'Out Of', 'mjschool' ) . ' ' . esc_html( $book_data->total_quentity ); ?></label>
												<?php } ?>
											</div>
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Description', 'mjschool' ); ?> </label><br>
												<?php
												if ( isset($user_access['edit']) && $user_access['edit'] === '1' && empty( $book_data->description ) ) {
													$edit_url = home_url( '?dashboard=mjschool_user&page=library&tab=addbook&action=edit&book_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['book_id'])) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label >
														<?php
														if ( ! empty( $book_data->description ) ) {
															echo esc_html( $book_data->description );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
										</div>
									</div>
									<?php
									$module = 'library';
									$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
									?>
								</div>
								<?php
								if ( isset( $_POST['save_issue_book'] ) ) {
									$result = $mjschool_obj_lib->mjschool_add_issue_book( wp_unslash($_POST) );
									if ( $result ) {
										/* Book issue mail notification. */
										if ( isset( $_POST['mjschool_issue_book_mail_service_enable'] ) ) {
											foreach ( $_POST['book_id'] as $book_id ) {
												$smgt_issue_book_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_issue_book_mail_service_enable']));
												if ( $smgt_issue_book_mail_service_enable ) {
													$search['{{student_name}}'] = mjschool_get_teacher( sanitize_text_field(wp_unslash($_POST['student_id'])) );
													$search['{{book_name}}']    = mjschool_get_book_name( $book_id );
													$search['{{issue_date}}']   = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['issue_date'])) );
													$search['{{return_date}}']  = mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['return_date'])) );
													$search['{{school_name}}']  = get_option( 'mjschool_name' );
													$message                    = mjschool_string_replacement( $search, get_option( 'mjschool_issue_book_mailcontent' ) );
													$mail_id                    = mjschool_get_email_id_by_user_id( sanitize_text_field(wp_unslash($_POST['student_id'])) );
													$headers  = '';
													$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
													$headers .= "MIME-Version: 1.0\r\n";
													$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
													if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
														wp_mail( $mail_id, get_option( 'mjschool_issue_book_title' ), $message, $headers );
													}
												}
											}
										}
										wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=view_book&book_id=' . mjschool_encrypt_id( $bookid ) . '&issue_message=issue_success' ));
										die();
									}
								}
								if ( isset( $_POST['return_book'] ) ) {
									$result = $mjschool_obj_lib->mjschool_submit_return_book( wp_unslash($_POST) );
									wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=library&tab=view_book&book_id=' . mjschool_encrypt_id( $bookid ) . '&issue_message=return_success' ));
									die();
								}
								?>
								<div class="col-xl-12 col-md-12 col-sm-12 mt-3 mjschool-margin-top-15px-rs">
									<?php
									if ( isset($user_access['add']) && $user_access['add'] === '1' ) {
										?>
										<div class="mjschool-guardian-div">
											<label class="mjschool-view-page-label-heading mb-4"> <?php esc_html_e( 'Issue Book Information', 'mjschool' ); ?> </label>
											<form name="issue_book_form" action="" method="post" class="mjschool-form-horizontal" id="issue_book_form">
												<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
												<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
												<input type="hidden" name="issue_id" value="">
												<input type="hidden" name="book_id" value="<?php echo esc_attr( $bookid ); ?>">
												<input type="hidden" name="bookcat_id" value="<?php echo esc_attr( $book_data->cat_id ); ?>">
												<div class="form-body mjschool-user-form">
													<div class="row">
														<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
															<label class="ml-1 mjschool-custom-top-label top" for="student_id"><?php esc_html_e( 'Select User', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															<select name="student_id" id="student_id" class="form-control change_library_card validate[required] mjschool-max-width-100px">
																<option value=""><?php esc_html_e( 'Select User', 'mjschool' ); ?></option>
																<?php echo esc_html( mjschool_get_student_and_teacher_for_library() ); ?>
															</select>
														</div>
														<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
															<div class="form-group input">
																<div class="col-md-12 form-control">
																	<input id="issue_library_card" class="form-control validate[required,custom[address_description_validation]]" type="text" maxlength="50" value="" name="library_card">
																	<label  for="library_card"><?php esc_html_e( 'Library Card No.', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
														<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
															<label class="ml-1 mjschool-custom-top-label top" for="period"><?php esc_html_e( 'Period', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															<select name="period_id" id="category_data" class="form-control issue_period validate[required] mjschool-max-width-100px period_type mjschool-line-height-30px">
																<option value=""><?php esc_html_e( 'Select Period', 'mjschool' ); ?></option>
																<?php
																if ( $edit ) {
																	$period_id = $result->period;
																} else {
																	$period_id = get_option( 'mjschool_return_period' );
																}
																$category_data = $mjschool_obj_lib->mjschool_get_period_list();
																if ( ! empty( $category_data ) ) {
																	foreach ( $category_data as $retrieved_data ) {
																		echo '<option value="' . esc_attr( $retrieved_data->ID ) . '" ' . selected( $period_id, $retrieved_data->ID ) . '>' . esc_html( $retrieved_data->post_title ) . ' ' . esc_attr__( 'Days', 'mjschool' ) . '</option>';
																	}
																}
																?>
															</select>
														</div>
														<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3">
															<button id="mjschool-addremove-cat" class="mjschool-rtl-margin-top-15px mjschool-add-btn sibling_add_remove" model="period_type"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>
														</div>
														<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
															<div class="form-group input">
																<div class="col-md-12 form-control">
																	<input id="issue_date" class="datepicker form-control validate[required] text-input" type="text" name="issue_date" value="<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); ?>" readonly>
																	<label  for="issue_date"><?php esc_html_e( 'Issue Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
														<?php wp_nonce_field( 'save_issuebook_admin_nonce' ); ?>
														<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
															<div class="form-group input">
																<div class="col-md-12 form-control">
																	<input id="return_date" class="form-control validate[required] date_picker text-input" type="text" name="return_date" value="" readonly>
																	<label class="active date_label" for="return_date"><?php esc_html_e( 'Return Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
														<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
															<div class="form-group">
																<div class="col-md-12 form-control mjschool-rtl-relative-position">
																	<div class="row mjschool-padding-radio">
																		<div>
																			<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="enable"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
																			<input id="chk_mjschool_sent1" type="checkbox" class="mjschool-check-box-input-margin" <?php $smgt_issue_book_mail_service_enable = 0; if ( $smgt_issue_book_mail_service_enable ) { echo 'checked'; } ?> value="1" name="smgt_issue_book_mail_service_enable"> <?php esc_html_e( 'Send Mail', 'mjschool' ); ?>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="form-body mjschool-user-form">
													<div class="row">
														<div class="col-sm-6">
															<input type="submit" value="<?php esc_html_e( 'Issue Book', 'mjschool' ); ?>" name="save_issue_book" class="mjschool-save-btn btn btn-success book_for_alert mjschool-rtl-margin-0px" />
														</div>
													</div>
												</div>
											</form>
										</div>
										<?php
									}
									?>
									<div class="mjschool-panel-body mt-3">
										<div class="header">
											<h3 class="mjschool-first-header"><?php esc_html_e( 'Issue Book List', 'mjschool' ); ?></h3>
										</div>
										<?php
										$issue_data = $mjschool_obj_lib->mjschool_get_all_issuebooks_book_id( $bookid );
										if ( ! empty( $issue_data ) ) {
											?>
											<div class="table-responsive">
												<form id="mjschool-common-form" name="mjschool-common-form" method="post">
													<table id="user_issue_list" class="display" cellspacing="0" width="100%">
														<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
															<tr>
																<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'User Name', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Issue Date', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Due Return Date ', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Accept Return Date ', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Period', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Fine', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
																<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
															</tr>
														</thead>
														<tbody>
															<?php
															$i            = 0;
															$current_role = mjschool_get_user_role( get_current_user_id() );
															foreach ( $issue_data as $retrieved_data ) {
																?>
																<tr>
																	<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-library.png"); ?>" class="img-circle" /></td>
																	<td><?php echo esc_html( mjschool_get_display_name( $retrieved_data->student_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'User Name', 'mjschool' ); ?>"></i></td>
																	<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->issue_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Issue Date', 'mjschool' ); ?>"></i> </td>
																	<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Due Return Date', 'mjschool' ); ?>"></i></td>
																	<td>
																		<?php
																		if ( ! empty( $retrieved_data->actual_return_date ) ) {
																			echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->actual_return_date ) );
																		} else {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		}
																		?>
																		<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Accept Return Date', 'mjschool' ); ?>"></i>
																	</td>
																	<td><?php echo esc_html( get_the_title( $retrieved_data->period ) ); ?> <?php esc_html_e( 'Day', 'mjschool' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Period', 'mjschool' ); ?>"></i></td>
																	<td >
																		<?php
																		if ( $retrieved_data->status === 'Issue' ) {
																			esc_html_e( 'Issued', 'mjschool' );
																		} elseif ( $retrieved_data->status === 'Submitted' ) {
																			esc_html_e( 'Returned', 'mjschool' );
																		}
																		?>
																		<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
																	</td>
																	<td >
																		<?php
																		if ( $retrieved_data->fine === '' || $retrieved_data->fine === 0 ) {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		} else {
																			echo esc_html( mjschool_get_currency_symbol() ) . esc_html( $retrieved_data->fine );
																		}
																		?>
																		<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Fine', 'mjschool' ); ?>"></i>
																	</td>
																	<td >
																		<?php
																		if ( $retrieved_data->comment === '' ) {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		} else {
																			echo esc_html( $retrieved_data->comment );
																		}
																		?>
																		<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( $retrieved_data->comment === '' ) { echo 'Comment'; } else { echo esc_html( $retrieved_data->comment ); } ?>"></i>
																	</td>
																	<td class="action">
																		<div class="mjschool-user-dropdown">
																			<ul  class="mjschool_ul_style">
																				<li >
																					<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																						<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																					</a>
																					<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																						<?php
																						if ($retrieved_data->status === "Issue" && ($current_role === "supportstaff" ||  $current_role === "teacher" ) ) {
																							?>
																							<li class="mjschool-float-left-width-100px">
																								<a idtest=<?php echo esc_attr($retrieved_data->id); ?> id="accept_returns_book_popup" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-accept-book-return.png"); ?>" class="mjschool_heights_13px">&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Accept Returns', 'mjschool' ); ?> </a>
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
															}
															?>
														</tbody>
													</table>
												</form>
											</div>
											<?php
										} else {
											?>
											<div class="mjschool-calendar-event-new">
												<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
											</div>
											<?php
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
			</div>
		</div>
		<?php
	}
	?>
</div>