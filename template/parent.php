<?php
/**
 * Parent Profile and Child Management View.
 *
 * This file displays a parent's profile information and includes forms/tables
 * for managing or viewing their associated children (students). It features
 * client-side script for dynamically adding or removing multiple child input fields,
 * and displays a table listing the enrolled children with their class and roll number.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$mjschool_role_name                 = mjschool_get_user_role( get_current_user_id() );
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'parent';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'parentlist';
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) == $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) == 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) == $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) == 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) == $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) == 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
// --------------------------  Save parent. ----------------------//
if ( isset( $_POST['save_parent'] ) ) {
	$role  = 'parent';
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_parent_admin_nonce' ) ) {
		$firstname  = sanitize_text_field( wp_unslash($_POST['first_name']) );
		$middlename = sanitize_text_field( wp_unslash($_POST['middle_name']) );
		$lastname   = sanitize_text_field( wp_unslash($_POST['last_name']) );
		$userdata   = array(
			'user_login'    => sanitize_email( wp_unslash($_POST['email']) ),
			'user_nicename' => null,
			'user_email'    => sanitize_email( wp_unslash($_POST['email']) ),
			'user_url'      => null,
			'display_name'  => $firstname . ' ' . $middlename . ' ' . $lastname,
		);
		if ( $_POST['password'] != '' ) {
			$userdata['user_pass'] = mjschool_password_validation( sanitize_text_field(wp_unslash($_POST['password'])) );
		}
		if ( isset( $_FILES['upload_user_avatar_image'] ) && ! empty( $_FILES['upload_user_avatar_image'] ) && $_FILES['upload_user_avatar_image']['size'] != 0 ) {
			if ( $_FILES['upload_user_avatar_image']['size'] > 0 ) {
				$member_image = mjschool_load_documets( $_FILES['upload_user_avatar_image'], 'upload_user_avatar_image', 'pimg' );
			}
			$photo = esc_url(content_url( '/uploads/school_assets/' . $member_image));
		} else {
			if ( isset( $_REQUEST['hidden_upload_user_avatar_image'] ) ) {
				$member_image = sanitize_text_field(wp_unslash($_REQUEST['hidden_upload_user_avatar_image']));
			}
			$photo = $member_image;
		}
		// DOCUMENT UPLOAD FILE CODE START.
		$document_content = array();
		if ( ! empty( $_FILES['document_file']['name'] ) ) {
			$count_array = count( $_FILES['document_file']['name'] );
			for ( $a = 0; $a < $count_array; $a++ ) {
				if ( ( $_FILES['document_file']['size'][ $a ] > 0 ) && ( ! empty( $_POST['document_title'][ $a ] ) ) ) {
					$document_title = sanitize_text_field(wp_unslash($_POST['document_title'][ $a ]));
					$document_file  = mjschool_upload_document_user_multiple( $_FILES['document_file'], $a, sanitize_text_field(wp_unslash($_POST['document_title'][ $a ])) );
				} elseif ( ! empty( $_POST['user_hidden_docs'][ $a ] ) && ! empty( $_POST['document_title'][ $a ] ) ) {
					$document_title = sanitize_text_field(wp_unslash($_POST['document_title'][ $a ]));
					$document_file  = sanitize_text_field(wp_unslash($_POST['user_hidden_docs'][ $a ]));
				}
				if ( ! empty( $document_file ) && ! empty( $document_title ) ) {
					$document_content[] = array(
						'document_title' => $document_title,
						'document_file'  => $document_file,
					);
				}
			}
		}
		if ( ! empty( $document_content ) ) {
			$final_document = json_encode( $document_content );
		} else {
			$final_document = '';
		}
		// DOCUMENT UPLOAD FILE CODE END.
		$usermetadata = array(
			'middle_name'      => sanitize_text_field( wp_unslash($_POST['middle_name']) ),
			'gender'           => sanitize_text_field( wp_unslash($_POST['gender']) ),
			'birth_date'       => sanitize_text_field(wp_unslash($_POST['birth_date'])),
			'address'          => sanitize_textarea_field( wp_unslash($_POST['address']) ),
			'city'             => sanitize_text_field( wp_unslash($_POST['city_name']) ),
			'state'            => sanitize_text_field( wp_unslash($_POST['state_name']) ),
			'zip_code'         => sanitize_text_field( wp_unslash($_POST['zip_code']) ),
			'phone'            => sanitize_text_field( wp_unslash($_POST['phone']) ),
			'mobile_number'    => sanitize_text_field( wp_unslash($_POST['mobile_number']) ),
			'user_document'    => $final_document,
			'relation'         => sanitize_text_field( wp_unslash($_POST['relation']) ),
			'mjschool_user_avatar' => $photo,
			'created_by'       => get_current_user_id(),
		);
		if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$userdata['ID'] = mjschool_decrypt_id( intval(wp_unslash($_REQUEST['parent_id'])) );
				$result         = mjschool_update_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $role );
				// UPDATE CUSTOM FIELD DATA.
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'parent';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=parent&tab=parentlist&message=1') );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} elseif ( ! email_exists( $_POST['email'] ) ) {
			$result = mjschool_add_new_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $role );
			// ADD CUSTOM FIELD DATA.
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'parent';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=parent&tab=parentlist&message=2' ));
				die();
			}
		} else {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=parent&tab=parentlist&message=3' ));
			die();
		}
	}
}
$addparent = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'addparent' ) {
	if ( isset( $_REQUEST['student_id'] ) ) {
		$student   = get_userdata( intval(wp_unslash($_REQUEST['student_id'])) );
		$addparent = 1;
	}
}
// ------------------------ DELETE PARENT. ------------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$parent_id = intval( mjschool_decrypt_id( intval( wp_unslash($_REQUEST['parent_id'])) ) );
		$childs    = get_user_meta( $parent_id, 'child', true );
		if ( ! empty( $childs ) ) {
			foreach ( $childs as $childvalue ) {
				$parents = get_user_meta( $childvalue, 'parent_id', true );
				if ( ! empty( $parents ) ) {
					if ( ( $key = array_search( $parent_id, $parents ) ) !== false ) {
						unset( $parents[ $key ] );
						update_user_meta( $childvalue, 'parent_id', $parents );
					}
				}
			}
		}
		$result = mjschool_delete_usedata( $parent_id );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=parent&tab=parentlist&message=4' ));
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ------------- MULTIPLE DELETE PARENTS. -------------//
if ( isset( $_POST['delete_selected'] ) ) {
	// Verify nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_parents' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( ! empty( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $id ) {
			$id = intval( $id );
			
			$childs = get_user_meta( $id, 'child', true );
			if ( ! empty( $childs ) ) {
				foreach ( $childs as $childvalue ) {
					$parents = get_user_meta( $childvalue, 'parent_id', true );
					if ( ! empty( $parents ) ) {
						if ( ( $key = array_search( $id, $parents ) ) !== false ) {
							unset( $parents[ $key ] );
							update_user_meta( $childvalue, 'parent_id', $parents );
						}
					}
				}
			}
			$result = mjschool_delete_usedata( $id );
		}
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=parent&tab=parentlist&message=4') );
		exit;
	}
}
$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
switch ( $message ) {
	case '1':
		$message_string = esc_html__( 'Parent Updated Successfully.', 'mjschool' );
		break;
	case '2':
		$message_string = esc_html__( 'Parent Added successfully.', 'mjschool' );
		break;
	case '3':
		$message_string = esc_html__( 'Username Or Emailid Already Exist.', 'mjschool' );
		break;
	case '4':
		$message_string = esc_html__( 'Parent Deleted Successfully.', 'mjschool' );
		break;
	case '5':
		$message_string = esc_html__( 'Parent CSV Uploaded Successfully .', 'mjschool' );
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
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!------------ Panel body. ------------->
	<div>
		<?php
		// ------------------- Parent list tab. -------------------//
		if ( $active_tab === 'parentlist' ) {
			$user_id = get_current_user_id();
			// ------- PARENT DATA FOR STUDENT. ---------//
			if ( $school_obj->role === 'student' ) {
				$own_data = $user_access['own_data'];
				if ( $own_data === '1' ) {
					$parentdata1 = $school_obj->parent_list;
					foreach ( $parentdata1 as $pid ) {
						$user = get_userdata( $pid );
						if ( $user ) {
							$parentdata[] = $user;
						}
					}
				} else {
					$parentdata = mjschool_get_users_data( 'parent' );
				}
			}
			// ------- PARENT DATA FOR TEACHER. ---------//
			elseif ( $school_obj->role === 'teacher' ) {
				$own_data = $user_access['own_data'];
				if ( $own_data === '1' ) {
					$parent = mjschool_parent_own_data_for_teacher();
					foreach ( $parent as $pid ) {
						$user = get_userdata( $pid );
						if ( $user ) {
							$parentdata[] = $user;
						}
					}
				} else {
					$parentdata = mjschool_get_users_data( 'parent' );
				}
			}
			// ------- PARENT DATA FOR PARENT. ---------//
			elseif ( $school_obj->role === 'parent' ) {
				$own_data = $user_access['own_data'];
				if ( $own_data === '1' ) {
					$parentdata[] = get_userdata( $user_id );
				} else {
					$parentdata = mjschool_get_users_data( 'parent' );
				}
			}
			// ------- PARENT DATA FOR SUPPORT STAFF. ---------//
			else {
				$own_data = $user_access['own_data'];
				if ( $own_data === '1' ) {
					
					$parentdata = get_users(
						array(
							'role' => 'parent',
							'meta_query' => array(
								array(
									'key' => 'created_by',
									'value' => $user_id,
									'compare' => '='
								)
							)
						)
					);
					
				} else {
					$parentdata = mjschool_get_users_data( 'parent' );
				}
			}
			if ( ! empty( $parentdata ) ) {
				?>
				<div class="mjschool-panel-body"><!------------ Panel body. ------------->
					<!--------------- Parent list form. --------------->
					<form name="wcwm_report" action="" method="post">
						<?php wp_nonce_field( 'bulk_delete_parents' ); ?>
						<div class="table-responsive"><!--------------- Table responsive. --------------->
							<table id="parent_list_front" class="display dataTable" cellspacing="0" width="100%">
								<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
									<tr>
										<?php
										if ( $mjschool_role_name === 'supportstaff' ) {
											?>
											<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
											<?php
										}
										?>
										<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Parent Name & Email', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Relation', 'mjschool' ); ?></th>
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
									if ( ! empty( $parentdata ) ) {
										foreach ( $parentdata as $retrieved_data ) {
											$uid = $retrieved_data->ID;
											?>
											<tr>
												<?php
												if ( $mjschool_role_name === 'supportstaff' ) {
													?>
													<td class="mjschool-checkbox-width-10px">
														<input type="checkbox" class="mjschool-sub-chk mjschool-selected-parent" name="id[]" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
													</td>
													<?php
												}
												?>
												<td class="mjschool-user-image mjschool-width-50px-td">
													<a  href="<?php echo esc_url('?dashboard=mjschool_user&page=parent&tab=view_parent&action=view_parent&parent_id='. mjschool_encrypt_id( $retrieved_data->ID )); ?>">
														<?php
														$uid       = $retrieved_data->ID;
														$umetadata = mjschool_get_user_image( $uid );
														
														if (empty($umetadata ) ) {
															echo '<img src=' . esc_url( get_option( 'mjschool_parent_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
														} else {
															echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
														}
														
														?>
													</a>
												</td>
												<td class="name">
													<a class="mjschool-color-black" href="<?php echo esc_url('?dashboard=mjschool_user&page=parent&tab=view_parent&action=view_parent&parent_id='. mjschool_encrypt_id( $retrieved_data->ID )); ?>"><?php echo esc_html( mjschool_get_parent_name_by_id( $retrieved_data->ID ) ); ?></a>
													<br>
													<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></span>
												</td>
												<td >
													+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( get_user_meta( $uid, 'mobile_number', true ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
												</td>
												<td >
													<?php echo esc_html( ucfirst( get_user_meta( $uid, 'gender', true ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
												</td>
												<td >
													<?php echo esc_html( ucfirst( get_user_meta( $uid, 'relation', true ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Relation', 'mjschool' ); ?>"></i>
												</td>
												<?php
												// Custom Field Values.
												if ( ! empty( $user_custom_field ) ) {
													foreach ( $user_custom_field as $custom_field ) {
														if ( $custom_field->show_in_table === '1' ) {
															$module             = 'parent';
															$custom_field_id    = $custom_field->id;
															$module_record_id   = $retrieved_data->ID;
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
																		<a href="<?php echo esc_url('?dashboard=mjschool_user&page=parent&tab=view_parent&action=view_parent&parent_id='. mjschool_encrypt_id( $retrieved_data->ID )); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																	</li>
																	<?php
																	if ( $user_access['edit'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																			<a href="<?php echo esc_url('?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce_action='. mjschool_get_nonce( 'edit_action' )); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																		</li>
																		<?php
																	}
																	if ( $user_access['delete'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=parent&tab=parentlist&action=delete&parent_id=' . mjschool_encrypt_id( $retrieved_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
									}
									?>
								</tbody>
							</table>
							<?php
							if ( $mjschool_role_name === 'supportstaff' ) {
								?>
								<div class="mjschool-print-button pull-left">
									<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
										<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all" value="<?php echo esc_attr( $retrieved_data->ID ); ?>" >
										<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
									</button>
									<?php
									if ( $user_access['delete'] === '1' ) {
										 ?>
										<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
										
										<?php
									}
									?>
								</div>
								<?php
							}
							?>
						</div><!--------------- Table responsive. --------------->
					</form><!--------------- Parent list form. --------------->
				</div><!------------ Panel body. ------------->
				<?php
			} elseif ( $user_access['add'] === '1' ) {
				 ?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=parent&tab=addparent') ); ?>">
						<img class="col-md-12 mjschool-no-img-width-100px parent_img" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
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
		// ------------------- Parent add form tab. -------------------//
		if ( $active_tab === 'addparent' ) {
			$students = mjschool_get_student_group_by_class();
			$role     = 'parent';
			$edit     = 0;
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
				$edit      = 1;
				$user_info = get_userdata( mjschool_decrypt_id( intval(wp_unslash($_REQUEST['parent_id'])) ) );
			}
			?>
			<?php
			$document_option    = get_option( 'mjschool_upload_document_type' );
			$document_type      = explode( ', ', $document_option );
			$document_type_json = $document_type;
			$document_size      = get_option( 'mjschool_upload_document_size' );
			?>
			<div class="mjschool-panel-body"><!---------- Panel body. ------------>
				<!---------------- Parent add form. ---------------->
				<form name="parent_form" action="" method="post" class="mt-3 mjschool-form-horizontal" id="parent_form" enctype="multipart/form-data">
					<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
					<input type="hidden"  name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>"><input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
					<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'PERSONAL Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"><!-- User form. -->
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="first_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->first_name ); } elseif ( isset( $_POST['first_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['first_name'])) );} ?>" autocomplete="first_name" name="first_name">
										<label  for="first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="middle_name" class="form-control validate[custom[onlyLetter_specialcharacter]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->middle_name ); } elseif ( isset( $_POST['middle_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['middle_name'])) );} ?>" name="middle_name">
										<label  for="middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="last_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->last_name ); } elseif ( isset( $_POST['last_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['last_name'])) );} ?>" name="last_name">
										<label  for="last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
								<div class="form-group">
									<div class="col-md-12 form-control">
										<div class="row mjschool-padding-radio">
											<div class="input-group">
												<span class="mjschool-custom-top-label mjschool-margin-left-0" for="gender"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
												<div class="d-inline-block">
													<?php
													$genderval = 'male';
													if ( $edit ) {
														$genderval = $user_info->gender;
													} elseif ( isset( $_POST['gender'] ) ) {
														$genderval = sanitize_text_field(wp_unslash($_POST['gender']));
													}
													?>
													<label class="radio-inline">
														<input type="radio" value="male" class="tog validate[required]" name="gender" <?php checked( 'male', $genderval ); ?> /><?php esc_html_e( 'Male', 'mjschool' ); ?>
													</label>
													&nbsp;&nbsp;
													<label class="radio-inline">
														<input type="radio" value="female" class="tog validate[required]" name="gender" <?php checked( 'female', $genderval ); ?> /><?php esc_html_e( 'Female', 'mjschool' ); ?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="birth_date" class="form-control date_picker validate[required]" type="text" name="birth_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $user_info->birth_date ) ); } elseif ( isset( $_POST['birth_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['birth_date'])) ) );} ?>" readonly>
										<label class="date_label" for="birth_date"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin">
								<label class="ml-1 mjschool-custom-top-label top" for="relation"><?php esc_html_e( 'Relation', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<?php
								if ( $edit ) {
									$relationval = $user_info->relation;
								} elseif ( isset( $_POST['relation'] ) ) {
									$relationval = sanitize_text_field(wp_unslash($_POST['relation']));
								} else {
									$relationval = '';
								}
								?>
								<select name="relation" class="mjschool-line-height-30px form-control validate[required]" id="relation">
									<option value=""><?php esc_html_e( 'Select Relation', 'mjschool' ); ?></option>
									<option value="Father" <?php selected( $relationval, 'Father' ); ?>><?php esc_html_e( 'Father', 'mjschool' ); ?></option>
									<option value="Mother" <?php selected( $relationval, 'Mother' ); ?>><?php esc_html_e( 'Mother', 'mjschool' ); ?></option>
								</select>
							</div>
						</div>
					</div><!-- User form. -->
					<hr>
					<div class="form-body mjschool-user-form"><!-- User form. -->
						<?php
						if ( $edit ) {
							$parent_data = get_user_meta( $user_info->ID, 'child', true );
							if ( ! empty( $parent_data ) ) {
								$i = 1;
								foreach ( $parent_data as $id1 ) {
									?>
									<!-- Edit time. -->
									<div id="mjschool-parents-child" class="form-group row mb-3 mjschool-parents-child">
										<div class="col-md-6 input">
											<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Child', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="chield_list[]" id="student_list" class="form-control validate[required] mjschool-max-width-100px mjschool_heights_47px" >
												<option value=""><?php esc_html_e( 'Select Child', 'mjschool' ); ?></option>
												<?php
												foreach ( $students as $label => $opt ) {
													?>
													<optgroup label="<?php echo 'Class : ' . esc_attr( $label ); ?>">
														<?php foreach ( $opt as $id => $name ) : ?>
															<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $id, $id1 ); ?>><?php echo esc_html( $name ); ?></option>
														<?php endforeach; ?>
													</optgroup>
												<?php } ?>
											</select>
										</div>
										<?php
										if ( $i === 1 ) {
											 ?>
											<div class="col-md-1 col-sm-1 col-xs-12 mjschool-width-20px-res">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_Child()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
											</div>
											<?php
										} else {
											?>
											<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
												<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-input-btn-height-width">
											</div>
											<?php
										}
										?>
									</div>
									<?php
									$i++;
								}
							} else { ?>
								<div id="mjschool-parents-child" class="row mb-3 mjschool-parents-child">
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Child', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select name="chield_list[]" id="student_list" class="mjschool-line-height-30px form-control validate[required]">
											<option value=""><?php esc_html_e( 'Select Child', 'mjschool' ); ?></option>
											<?php
											foreach ($students as $label => $opt) { ?>
												<optgroup label="<?php esc_html_e( 'Class', 'mjschool' ); ?><?php echo ": " . esc_attr($label); ?>">
													<?php foreach ($opt as $id => $name): ?>
														<option value="<?php echo esc_attr($id); ?>"><?php echo esc_html( $name); ?></option>
													<?php endforeach; ?>
												</optgroup>
											<?php }  ?>
										</select>
									</div>
									<div class="col-md-1 col-sm-1 col-xs-12 mjschool-width-20px-res">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_Child()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
									</div>
								</div>
								<?php
							}
						} else { 	?>
							<div id="mjschool-parents-child" class="row mb-3 mjschool-parents-child">
								<div class="col-md-6 input mjschool-width-80px-res">
									<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Child', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									<select name="chield_list[]" id="student_list" class="mjschool-line-height-30px form-control validate[required]">
										<option value=""><?php esc_html_e( 'Select Child', 'mjschool' ); ?></option>
										<?php
										foreach ($students as $label => $opt) { ?>
											<optgroup label="<?php esc_html_e( 'Class', 'mjschool' ); ?><?php echo ": " . esc_attr($label); ?>">
												<?php foreach ($opt as $id => $name): ?>
													<option value="<?php echo esc_attr($id); ?>"><?php echo esc_html( $name); ?></option>
												<?php endforeach; ?>
											</optgroup>
										<?php }  ?>
									</select>
								</div>
								<div class="col-md-1 col-sm-1 col-xs-12 mjschool-width-20px-res">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_Child()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
								</div>
							</div>
							<?php 
						}
						?>
					</div><!-- User form. -->
					<hr>
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Contact Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"><!-- User form. -->
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="address" class="form-control validate[required,custom[address_description_validation]]" maxlength="120" type="text" autocomplete="address" name="address" value="<?php if ( $edit ) { echo esc_attr( $user_info->address ); } elseif ( isset( $_POST['address'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['address'])) );} ?>">
										<label  for="address"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="city_name" class="form-control validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->city ); } elseif ( isset( $_POST['city_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['city_name'])) ); } ?>">
										<label  for="city_name"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="state_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->state ); } elseif ( isset( $_POST['state_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['state_name'])) ); } ?>">
										<label  for="state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="zip_code" class="form-control  validate[required,custom[zipcode]]" maxlength="15" type="text" name="zip_code" value="<?php if ( $edit ) { echo esc_attr( $user_info->zip_code ); } elseif ( isset( $_POST['zip_code'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['zip_code'])) ); } ?>">
										<label  for="zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<?php wp_nonce_field( 'save_parent_admin_nonce' ); ?>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="row">
									<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
										<div class="form-group input mjschool-margin-bottom-0">
											<div class="col-md-12 form-control mjschool-mobile-input">
												<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="form-control country_code phonecode" name="phonecode">
												<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
												<input id="mobile_number" class="form-control mjschool-btn-top validate[required],minSize[6],maxSize[15]] text-input" type="text" name="mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mobile_number'])) );} ?>">
												<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mobile_number"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-mobile-input">
										<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="form-control country_code phonecode" name="phonecode">
										<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
										<input id="phone" class="form-control validate[custom[phone_number],minSize[6],maxSize[15]] text-input" type="text" autocomplete="phone" name="phone" value="<?php if ( $edit ) { echo esc_attr( $user_info->phone ); } elseif ( isset( $_POST['phone'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['phone'])) );} ?>">
										<label class="mjschool-custom-control-label mjschool-custom-top-label" for="phone"><?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div><!-- User form. -->
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Login Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"> <!-- User form. -->
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="email" class="mjschool-student-email-id form-control validate[required,custom[email]] text-input" maxlength="100" type="text" autocomplete="email" name="email" value="<?php if ( $edit ) { echo esc_attr( $user_info->user_email ); } elseif ( isset( $_POST['email'] ) ) { echo esc_attr( sanitize_email(wp_unslash($_POST['email'])) );} ?>">
										<label  for="email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="password" class="form-control  <?php if ( ! $edit ) { echo 'validate[required,minSize[8],maxSize[12]]'; } else { echo 'validate[minSize[8],maxSize[12]]'; } ?>" type="password" name="password" autocomplete="current-password">
										<label  for="password"><?php esc_html_e( 'Password', 'mjschool' ); ?>
											<?php if ( ! $edit ) { ?>
												<span class="mjschool-require-field">*</span>
											<?php } ?>
										</label>
										<!-- Use class + data-target. -->
										<i class="fas fa-eye-slash togglePassword" data-target="#password"></i>
									</div>
								</div>
							</div>
						</div>
					</div><!-- User form. -->
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Profile Image', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"><!-- User form. -->
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-upload-profile-image-frontend mjschool-res-rtl-height-50px">
										<span for="mjschool_membershipimage" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Profile Image', 'mjschool' ); ?></span>
										<div class="col-sm-12">
											<input type="hidden" id="smgt_user_avatar_url" class="form-control" name="smgt_user_avatar" value="<?php if ( $edit ) { echo esc_html( $user_info->smgt_user_avatar ); } elseif ( isset( $_POST['mjschool_user_avatar'] ) ) { echo esc_url( sanitize_text_field(wp_unslash($_POST['mjschool_user_avatar'])) ); } ?>" readonly />
											<input type="hidden" name="hidden_upload_user_avatar_image" value="<?php if ( $edit ) { echo esc_html( $user_info->smgt_user_avatar ); } elseif ( isset( $_POST['hidden_upload_user_avatar_image'] ) ) { echo esc_url( sanitize_text_field(wp_unslash($_POST['hidden_upload_user_avatar_image'])) ); } ?>">
											<input id="upload_user_avatar" name="upload_user_avatar_image" type="file" class="form-control file mjchool_border_0px" onchange="mjschool_file_check(this);" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
										</div>
									</div>
									<div class="clearfix"></div>
									<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
										<div id="mjschool-upload-user-avatar-preview">
											
											<?php
											if ($edit) {
												if ($user_info->smgt_user_avatar === "") {
													?>
													<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>">
													<?php
												} else {
													?>
													<img class="mjschool-image-preview-css" src="<?php if ($edit) echo esc_url($user_info->smgt_user_avatar); ?>" />
													<?php
												}
											} else {
												?>
												<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>">
												<?php
											} ?>
											
										</div>
									</div>
								</div>
							</div>
						</div>
					</div> <!-- User form. -->
					<!-- DOCUMENT UPLOAD FIELD START. -->
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Documnt Details', 'mjschool' ); ?></h3>
					</div>
					<div class="mjschool-more-document">
						<?php
						if ( $edit ) {
							// CHECK USER DOCUMENT EXISTS OR NOT.
							if ( ! empty( $user_info->user_document ) ) {
								$document_array = json_decode( $user_info->user_document );
								foreach ( $document_array as $key => $value ) {
									?>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="document_title" class="form-control text-input" maxlength="50" type="text" value="<?php echo esc_attr( $value->document_title ); ?>" name="document_title[]">
														<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div class="col-md-5 col-10 col-sm-1">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
														<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
														<div class="col-sm-12 row">
															<input type="hidden" id="user_hidden_docs" class="mjschool-image-path-dots form-control" name="user_hidden_docs[]" value="<?php echo esc_attr( $value->document_file ); ?>" readonly />
															<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 mt-1">
																<input id="upload_user_avatar_button" name="document_file[]" type="file" class="p-1 form-control mjschool-file-validation file" />
															</div>
															<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 p-0">
																<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $value->document_file )); ?>" record_id="<?php echo esc_attr( $key ); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
															</div>
														</div>
													</div>
												</div>
											</div>
											<?php
											if ( $key === 0 ) {
												 ?>
												<div class="col-md-1 col-2 col-sm-1 col-xs-12">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
												</div>
												<?php
											}
											else
											{
												?>
												<div class="col-md-1 col-2 col-sm-3 col-xs-12">
													<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" class="mjschool-rtl-margin-top-15px mjschool-float-right mjschool-remove-certificate mjschool-input-btn-height-width">
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<?php
								}
							}
							else
							{
								?>
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
											<div class="form-group input">
												<div class="col-md-12 form-control">
													<input id="document_title" class="form-control text-input" maxlength="50" type="text" value="" name="document_title[]">
													<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
												</div>
											</div>
										</div>
										<div class="col-md-5 col-10 col-sm-1">
											<div class="form-group input">
												<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
													<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
													<div class="col-sm-12 mjschool-display-flex">
														<input id="upload_user_avatar_button" name="document_file[]" type="file" class="p-1 form-control mjschool-file-validation file" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-1 col-2 col-sm-1 col-xs-12">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
										</div>
									</div>
								</div>
								<?php
							}
						}
						else
						{
							?>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="document_title" class="form-control text-input" maxlength="50" type="text" value="" name="document_title[]">
												<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-5 col-10 col-sm-1">
										<div class="form-group input">
											<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
												<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
												<div class="col-sm-12 mjschool-display-flex">
													<input id="upload_user_avatar_button" name="document_file[]" type="file" class="p-1 form-control file mjschool-file-validation" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-1 col-2 col-sm-1 col-xs-12">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
									</div>
								</div>
								
							</div>
							<?php
						}
						?>
					</div>
					<?php
					// --------- Get module-wise custom field data. --------------//
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'parent';
					$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
					?>
					<div class="form-body mjschool-user-form"><!-- User form. -->
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Parent', 'mjschool' ); } else { esc_html_e( 'Add Parent', 'mjschool' ); } ?>" name="save_parent" class="btn btn-success mjschool-save-btn" />
							</div>
						</div>
					</div>
				</form><!---------------- Parent add form. ---------------->
			</div><!---------- Panel body. ------------>
			<?php
		}
		?>
		<?php
		// ---------------- View parent tab. ---------------//
		if ( $active_tab === 'view_parent' ) {
			$parent_id                 = intval( mjschool_decrypt_id( intval( wp_unslash($_REQUEST['parent_id'])) ) );
			$active_tab1               = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
			$parent_data               = get_userdata( $parent_id );
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$user_meta                 = get_user_meta( $parent_id, 'child', true );
			
			?>
			<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start panel body div.-->
				<div class="content-body"><!-- Start content body div.-->
					<!-- Detail Page Header Start. -->
					<section id="mjschool-user-information">
						<div class="mjschool-view-page-header-bg">
							<div class="row">
								
								<div class="col-xl-10 col-md-9 col-sm-10">
									<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
										<?php
										$umetadata = mjschool_get_user_image($parent_data->ID);
										?>
										<img class="mjschool-user-view-profile-image" src="<?php if ( ! empty( $umetadata ) ) { echo esc_url($umetadata);} else { echo esc_url( get_option( 'mjschool_parent_thumb_new' ) );} ?>">
										<div class="row mjschool-profile-user-name">
											<div class="mjschool-float-left mjschool-view-top1">
												<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
													<label class="mjschool-view-user-name-label"><?php echo esc_html( $parent_data->display_name); ?></label>
													<?php
													if ($user_access['edit'] === '1' ) {
														?>
														<div class="mjschool-view-user-edit-btn">
															<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( '?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . mjschool_encrypt_id( $parent_data->ID ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
															</a>
														</div>
														<?php
													}
													?>
												</div>
												<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
													<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<label><?php echo esc_html( $parent_data->mobile_number); ?></label>
													</div>
												</div>
											</div>
										</div>
										<div class="row mjschool-padding-top-15px-res mjschool-view-user-teacher-label">
											<div class="col-xl-12 col-md-12 col-sm-12">
												<div class="mjschool-view-top2">
													<div class="row mjschool-view-user-teacher-label">
														<div class="col-md-12 mjschool-address-student-div">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page"><?php echo esc_html( $parent_data->address); ?></label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2">
									<div class="mjschool-group-thumbs">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
									</div>
								</div>
								
							</div>
						</div>
					</section>
					<!-- Detail Page Header End. -->
					<!-- Detail Page Tabbing Start. -->
					<section id="body_area" class="body_areas">
						<div class="row">
							<div class="col-xl-12 col-md-12 col-sm-12">
								<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
									<li class="<?php if ( $active_tab1 === 'general' ) { ?> active<?php } ?>">
										<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=parent&tab=view_parent&action=view_parent&tab1=general&parent_id=' . intval( wp_unslash( $_REQUEST['parent_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>"> <?php esc_html_e( 'GENERAL', 'mjschool' ); ?></a>
									</li>
									<li class="<?php if ( $active_tab1 === 'Child' ) { ?> active<?php } ?>">
										<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=parent&tab=view_parent&action=view_parent&tab1=Child&parent_id=' . intval( wp_unslash( $_REQUEST['parent_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'Child' ? 'active' : ''; ?>"> <?php esc_html_e( 'Child List', 'mjschool' ); ?></a>
									 </li>
								</ul>
							</div>
						</div>
					</section>
					<!-- Detail Page Tabbing End. -->
					<!-- Detail Page Body Content Section.  -->
					<section id="mjschool-body-content-area">
						<div class="mjschool-panel-body"><!-- Start panel body div.-->
							<?php
							// General tab start.
							if ( $active_tab1 === 'general' ) {
								?>
								<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
										<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br/>
										<label class="mjschool-view-page-content-labels"> <?php echo esc_html( $parent_data->user_email ); ?> </label>
									</div>
									<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
										<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Mobile Number', 'mjschool' ); ?> </label><br/>
										<?php
										if ( $user_access['edit'] === '1' && empty( $parent_data->mobile_number ) ) {
											$edit_url = home_url( '?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<label class="mjschool-view-page-content-labels">
												+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $parent_data->mobile_number ); ?>
											</label>
										<?php } ?>
									</div>
									<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
										<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br/>
										<?php
										$birth_date      = $parent_data->birth_date;
										$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
										if ( $user_access_edit === '1' && $is_invalid_date ) {
											$edit_url = admin_url( '?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<label class="mjschool-view-page-content-labels"> 
												<?php
												if ( ! empty( $parent_data->birth_date ) ) {
													echo esc_html( mjschool_get_date_in_input_box( $parent_data->birth_date ) );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</label>
										<?php } ?>
									</div>
									<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
										<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br/>
										<?php
										if ( $user_access['edit'] === '1' && empty( $parent_data->gender ) ) {
											$edit_url = home_url( '?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<label class="mjschool-view-page-content-labels"> <?php echo esc_html( ucfirst( $parent_data->gender ) ); ?></label>	
											<?php 
										} ?>
									</div>
									<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
										<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Relation', 'mjschool' ); ?> </label><br/>
										<?php
										if ( $user_access['edit'] === '1' && empty( $parent_data->relation ) ) {
											$edit_url = home_url( '?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
											echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
										} else {
											?>
											<label class="mjschool-view-page-content-labels">
												<?php
												$relation = $parent_data->relation;
												if ( ! empty( $relation ) ) {
													echo esc_html( $parent_data->relation );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</label>
										<?php } ?>
									</div>
								</div>
								<!-- Student Information div start.  -->
								<div class="row mjschool-margin-top-20px">
									<div class="col-xl-12 col-md-12 col-sm-12">
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
											<div class="mjschool-guardian-div">
												<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Contact Information', 'mjschool' ); ?> </label>
												<div class="row">
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'City', 'mjschool' ); ?> </label> <br>
														<?php
														if ( $user_access['edit'] === '1' && empty( $parent_data->city ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $parent_data->city ) ) {
																	echo esc_html( $parent_data->city );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</label>
															<?php 
														} ?>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
														<?php
														if ( $user_access['edit'] === '1' && empty( $parent_data->state ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $parent_data->state ) ) {
																	echo esc_html( $parent_data->state );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' ); 
																}
																?>
															</label>
															<?php 
														} ?>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Zip Code', 'mjschool' ); ?> </label><br>
														<?php
														if ( $user_access['edit'] === '1' && empty( $parent_data->zip_code ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-view-page-content-labels"><?php echo esc_html( $parent_data->zip_code ); ?></label>
															<?php 
														} ?>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?> </label><br>
														<?php
														if ( $user_access['edit'] === '1' && empty( $parent_data->phone ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
														} else {
															?>
															<label class="mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $parent_data->phone ) ) {
																	?>
																	+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;
																	<?php
																	echo esc_html( $parent_data->phone );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' ); 
																}
																?>
															</label>
															<?php 
														} ?>
													</div>
												</div>
												<?php
												if ( ! empty( $parent_data->user_document ) ) {
													?>
													<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Document Information', 'mjschool' ); ?> </label>
													<div class="row">
														<?php
														$document_array = json_decode( $parent_data->user_document );
														foreach ( $document_array as $key => $value ) {
															?>
															<div class="col-xl-3 col-md-3 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
																<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php echo esc_html( $value->document_title ); ?> </label><br>
																<label class="mjschool-label-value">
																<?php
																if ( ! empty( $value->document_file ) ) {
																	?>
																	<a target="blank" class="mjschool-status-read btn btn-default mjschool-download-btn-syllebus" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $value->document_file )); ?>" record_id="<?php echo esc_attr( $key ); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a> 
																	<?php
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
																</label>
															</div>
															<?php
														}
														?>
													</div>
													<?php
												}
												?>
											</div>	
										</div>
										<?php
										$module = 'parent';
										$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
										?>
									</div>
								</div>
								<?php
							}
							// Attendance tab start.
							elseif ( $active_tab1 === 'Child' ) {
								?>
								<div>
									<div id="Section1" class="mjschool_new_sections"">
										<div class="row">
											<div class="col-lg-12">
												<div>
													<div class="card-content">
														<div class="table-responsive">
															<?php if ( ! empty( $user_meta ) ) { ?>
																<table id="mjschool-parents-child-list-detail-page-front" class="display table" cellspacing="0" width="100%">
																	<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
																		<tr>
																			<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
																			<th><?php esc_html_e( 'Child Name & Email', 'mjschool' ); ?></th>
																			<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?> </th>
																			<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
																			<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
																		</tr>
																	</thead>
																	<tbody>
																		<?php
																		foreach ( $user_meta as $childsdata ) {
																			$child = get_userdata( $childsdata );
																			if ( ! empty( $child ) ) {
																				?>
																				<tr>
																					<td class="mjschool-width-50px-td">
																						
																						<?php
																						if ($childsdata) {
																							$umetadata = mjschool_get_user_image($childsdata);
																						}
																						if (empty($umetadata ) ) {
																							echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
																						} else
																							echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
																						?>
																					</td>
																					<td class="name">
																						<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . $child->ID ); ?>"><?php echo esc_attr($child->first_name) . " " . esc_attr($child->middle_name) . " " . esc_attr($child->last_name); ?></a>
																						<br>
																						<span class="mjschool-list-page-email"><?php echo esc_html( $child->user_email); ?></span>
																					</td>
																					<td>+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $child->mobile_number); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i></td>
																					<td>
																						<?php
																						$class_id = get_user_meta($child->ID, 'class_name', true);
																						$section_name = get_user_meta($child->ID, 'class_section', true);
																						$classname = mjschool_get_class_section_name_wise( $class_id, $section_name);
																						echo esc_html( $classname) ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
																					</td>
																					<td> <?php echo esc_html( get_user_meta($child->ID, 'roll_id', true ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Roll No.', 'mjschool' ); ?>"></i></td>
																				</tr>
																				<?php
																			}
																		}
																		?>
																	</tbody>
																</table>
															<?php } else {
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
									</div>
								</div>
								<?php
							}
							?>
						</div><!-- End panel body div.-->
					</section>
					<!-- Detail page body content section end. -->
				</div><!-- End content body div.-->
			</div><!-- End panel body div.-->
			<?php
		}
		?>
	</div>
</div><!------------ Panel body. ------------->