<?php
/**
 * Tax Rate Management Page
 *
 * This file allows the administrator or privileged user to create, view,
 * edit, and delete tax rates used in the system (e.g., fee payments).
 * It includes data handling and security nonce verification.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$role_name         = mjschool_get_user_role( get_current_user_id() );
$user_access       = mjschool_get_user_role_wise_access_right_array();
$obj_tax           = new Mjschool_Tax_Manage();
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'tax';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'tax';
// --------------- Access-wise role. -----------//
if ( isset( $_REQUEST ['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST ['page'] ) && sanitize_text_field(wp_unslash($_REQUEST ['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST ['page'] ) && sanitize_text_field(wp_unslash($_REQUEST ['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST ['page'] ) && sanitize_text_field(wp_unslash($_REQUEST ['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
// ------------------ Save tax. --------------------//
if ( isset( $_POST['save_tax'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_tax_admin_nonce' ) ) {
		if ( isset( $_POST['action'] ) && sanitize_text_field(wp_unslash($_POST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$tax_id                  = wp_unslash($_REQUEST['tax_id']);
				$result                  = $obj_tax->mjschool_insert_tax( wp_unslash($_POST) );
				$custom_field_obj        = new Mjschool_Custome_Field();
				$module              = 'tax';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $tax_id );
				if ( $result ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=tax&tab=tax&message=2' ));
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result                     = $obj_tax->mjschool_insert_tax( wp_unslash($_POST) );
			$custom_field_obj           = new Mjschool_Custome_Field();
			$module             = 'tax';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=tax&tab=tax&message=1' ));
				die();
			}
		}
	}
}
// ------------------ Delete tax. --------------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = $obj_tax->mjschool_delete_tax( mjschool_decrypt_id( wp_unslash($_REQUEST['tax_id']) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=tax&tab=tax&message=3' ));
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_GET['message'] ) && sanitize_text_field(wp_unslash($_GET['message'])) === 1 ) {
	 ?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-close.png")?>"></span></button>
		<?php esc_html_e( 'Tax Inserted Successfully.','mjschool' );?>
	</div>
	<?php
}
if( isset( $_GET['message']) && sanitize_text_field(wp_unslash($_GET['message'])) === 2 )
{
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-close.png")?>"></span></button>
		<?php esc_html_e( 'Tax Updated Successfully.','mjschool' );?>
	</div>
	<?php
}
if( isset( $_GET['message']) && sanitize_text_field(wp_unslash($_GET['message'])) === 3 )
{
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-close.png")?>"></span></button>
		<?php esc_html_e( 'Tax Deleted Successfully.','mjschool' );?>
	</div>
	<?php 
}
?>
<!-- Nav tabs. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!-------------- Panel body. ------------>
	<?php
	// ------------- ACTIVE TAB CLASS LIST. -------------//
	if ( $active_tab === 'tax' ) {
		$user_id  = get_current_user_id();
		$own_data = $user_access['own_data'];
		// ------- Exam data for teacher. ---------//
		if ( $school_obj->role === 'teacher' ) {
			$retrieve_tax = $obj_tax->mjschool_get_all_tax();
		}
		// ------- Exam data for support staff. ---------//
		else {
			$retrieve_tax = $obj_tax->mjschool_get_all_tax();
		}
		if ( ! empty( $retrieve_tax ) ) {
			?>
			<div class="mjschool-panel-body"><!--------------- Panel body. ------------->
				<div class="table-responsive"><!--------------- Table responsive. ----------->
					<!----------- CLASS LIST FORM START. ---------->
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<table id="frontend_tax_list" class="display dataTable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Tax Title', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Tax Value(%)', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Created Date', 'mjschool' ); ?></th>
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
									if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
										?>
										<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 0;
								foreach ( $retrieve_tax as $retrieved_data ) {
									if ( $i === 10 ) {
										$i = 0;
									}
									if ( $i === 0 ) {
										$color_class_css = 'mjschool-class-color0';
									} elseif ( $i === 1 ) {
										$color_class_css = 'mjschool-class-color1';
									} elseif ( $i === 2 ) {
										$color_class_css = 'mjschool-class-color2';
									} elseif ( $i === 3 ) {
										$color_class_css = 'mjschool-class-color3';
									} elseif ( $i === 4 ) {
										$color_class_css = 'mjschool-class-color4';
									} elseif ( $i === 5 ) {
										$color_class_css = 'mjschool-class-color5';
									} elseif ( $i === 6 ) {
										$color_class_css = 'mjschool-class-color6';
									} elseif ( $i === 7 ) {
										$color_class_css = 'mjschool-class-color7';
									} elseif ( $i === 8 ) {
										$color_class_css = 'mjschool-class-color8';
									} elseif ( $i === 9 ) {
										$color_class_css = 'mjschool-class-color9';
									}
									?>
									<tr>
										<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">	
											<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">	
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-tax.png")?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
											</p>
										</td>
										<td>
											<?php
											if ( $retrieved_data->tax_title ) {
												echo esc_html( $retrieved_data->tax_title );
											} else {
												esc_html_e( 'N/A', 'mjschool' ); 
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Tax Title', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php
											if ( $retrieved_data->tax_value ) {
												echo esc_html( $retrieved_data->tax_value );
											} else {
												esc_html_e( 'N/A', 'mjschool' ); 
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Tax Value(%)', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php
											if ( $retrieved_data->created_date ) {
												echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) );
											} else {
												esc_html_e( 'N/A', 'mjschool' ); 
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Created Date', 'mjschool' ); ?>"></i>
										</td>
										<?php
										// Custom Field Values.
										if ( ! empty( $user_custom_field ) ) {
											foreach ( $user_custom_field as $custom_field ) {
												if ( $custom_field->show_in_table === '1' ) {
													$module             = 'tax';
													$custom_field_id    = $custom_field->id;
													$module_record_id   = $retrieved_data->tax_id;
													$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
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
																<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
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
										<?php if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) { ?>
											<td class="action">  
												<div class="mjschool-user-dropdown">
													<ul  class="mjschool_ul_style">
														<li >
															<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-more.png")?>">
															</a>
															<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																<?php
																if ( $user_access['edit'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=tax&tab=add_tax&action=edit&tax_id=' . mjschool_encrypt_id( $retrieved_data->tax_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																	</li>
																	<?php
																}
																if ( $user_access['delete'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=tax&tab=tax&action=delete&tax_id=' . mjschool_encrypt_id( $retrieved_data->tax_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"> <i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
																	</li>
																	<?php
																}
																?>
															</ul>
														</li>
													</ul>
												</div>	
											</td>
											<?php
											++$i;
										}
										?>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</form>
				</div><!------------- Table responsive. ------------------>
			</div><!------------- Panel body. ----------------->
			<?php
		} elseif ( $user_access['add'] === '1' ) {
			 ?>
			<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px"> 
				<a href="<?php echo esc_url(home_url().'?dashboard=mjschool_user&page=tax&tab=add_tax' );?>">
					<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
				</a>
				<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
					<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.','mjschool' ); ?> </label>
				</div> 
			</div>		
			<?php
		}
		else
		{
			?>
			<div class="mjschool-calendar-event-new"> 
				<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
			</div>	
			<?php
		}
	}
	// ------------- Active tab add tax form. ----------------------//
	if ( $active_tab === 'add_tax' ) {
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			$edit    = 1;
			$taxdata = $obj_tax->mjschool_get_single_tax( mjschool_decrypt_id( wp_unslash($_REQUEST['tax_id']) ) );
		}
		?>
		<div class="mjschool-panel-body"><!-------- Panel body. -------->
			<form name="mjschool-tax-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-tax-form" enctype="multipart/form-data"><!------- Form Start. --------->
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<input type="hidden" name="tax_id" value="<?php if ( $edit ) { echo esc_attr( mjschool_decrypt_id( wp_unslash($_REQUEST['tax_id']) ) );} ?>"  />
				<div class="header"><h3 class="mjschool-first-header"><?php esc_html_e( 'Tax Information', 'mjschool' ); ?></h3></div>
				<div class="form-body mjschool-user-form">
					<div class="row">	
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="tax_title" class="form-control validate[required,custom[popup_category_validation]]" maxlength="30" type="text" value="<?php if ( $edit ) { echo esc_attr( $taxdata->tax_title );} ?>" name="tax_title">
									<label for="tax_title"><?php esc_html_e( 'Tax Name', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="tax" class="form-control validate[required,custom[number]] text-input" onkeypress="if(this.value.length==6) return false;" step="0.01" type="number" value="<?php if ( $edit ) { echo esc_attr( $taxdata->tax_value ); } elseif ( isset( $_POST['tax_value'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['tax_value'])) );} ?>" name="tax_value" min="0" max="100">
									<label  for="tax"><?php esc_html_e( 'Tax Value', 'mjschool' ); ?>(%)<span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<?php wp_nonce_field( 'save_tax_admin_nonce' ); ?>	
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$custom_field_obj = new Mjschool_Custome_Field();
				$module           = 'tax';
				$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">	
						<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">        	
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Tax', 'mjschool' ); } else { esc_html_e( 'Add Tax', 'mjschool' );} ?>" name="save_tax" class="mjschool-save-btn" />
						</div> 
					</div>        
				</div>               
			</form> <!------- Form end. --------->
		</div><!-------- Panel body. -------->
		<?php
	}
	?>
</div> <!-------------- Panel body. ------------>