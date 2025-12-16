<?php
/**
 * User Account/Profile Management Template.
 *
 * This file handles the display and processing of the current user's account information,
 * including profile details, password change functionality, and profile picture updates.
 * It's designed to be included within a WordPress administrative or frontend dashboard page.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 * 
 */

defined( 'ABSPATH' ) || exit;
?>
<?php
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
$school_obj = new MJSchool_Management( get_current_user_id() );
$mjschool_user       = wp_get_current_user();
$mjschool_user_info  = get_userdata( $mjschool_user->ID );
$mjschool_user_data  = get_userdata( $mjschool_user->ID );
require_once ABSPATH . 'wp-includes/class-phpass.php';
$wp_hasher = new PasswordHash( 8, true );
if ( isset( $_POST['save_change'] ) ) {

    $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

    if ( wp_verify_nonce( $nonce, 'password_save_change_nonce' ) ) {

        $current_pass = isset( $_POST['current_pass'] ) 
            ? sanitize_text_field(wp_unslash( $_POST['current_pass'] )) 
            : '';

        $new_pass = isset( $_POST['new_pass'] ) 
            ? sanitize_text_field(wp_unslash( $_POST['new_pass']) ) 
            : '';

        $confirm_pass = isset( $_POST['conform_pass'] ) 
            ? sanitize_text_field(wp_unslash( $_POST['conform_pass']) ) 
            : '';

        $referrer = isset( $_SERVER['HTTP_REFERER'] ) 
            ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) 
            : '';

        if ( ! empty( $current_pass ) && ! empty( $new_pass ) && ! empty( $confirm_pass ) ) {

            $success = 0;

            if ( $wp_hasher->CheckPassword( $current_pass, $mjschool_user_data->user_pass ) ) {

                if ( $new_pass === $confirm_pass ) {

                    wp_set_password( $new_pass, $mjschool_user->ID );
                    $success = 1;

                } else {
                    wp_safe_redirect( $referrer . '&sucess=2' );
                    exit;
                }

            } else {
                wp_safe_redirect( $referrer . '&sucess=3' );
                exit;
            }

            if ( $success === 1 ) {

                wp_cache_delete( $mjschool_user->ID, 'users' );
                wp_cache_delete( $mjschool_user_data->user_login, 'mjschool_user_logins' );

                wp_logout();

                $login_result = wp_signon(
                    array(
                        'user_login'    => $mjschool_user_data->user_login,
                        'user_password' => $new_pass,
                    ),
                    false
                );

                if ( ! is_wp_error( $login_result ) ) {
                    wp_safe_redirect( $referrer . '&sucess=1' );
                    exit;
                }

                ob_start();

            } else {
                wp_set_auth_cookie( $mjschool_user->ID, true );
            }
        }
    }
}
if ( isset( $_POST['save_change_new'] ) ) {

    $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

    if ( wp_verify_nonce( $nonce, 'password_save_change_nonce_new' ) ) {

        $current_pass = isset( $_POST['current_pass'] ) ? sanitize_text_field(wp_unslash( $_POST['current_pass'] )) : '';
        $new_pass     = isset( $_POST['new_pass'] )     ? sanitize_text_field(wp_unslash( $_POST['new_pass'] ))     : '';
        $confirm_pass = isset( $_POST['conform_pass'] ) ? sanitize_text_field(wp_unslash( $_POST['conform_pass']) ) : '';

        $referrer = isset( $_SERVER['HTTP_REFERER'] )
            ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) )
            : '';

        if ( ! empty( $current_pass ) && ! empty( $new_pass ) && ! empty( $confirm_pass ) ) {

            $success = 0;

            if ( wp_check_password( $current_pass, $mjschool_user_data->user_pass, $mjschool_user_data->ID ) ) {

                if ( $new_pass === $confirm_pass ) {

                    wp_set_password( $new_pass, $mjschool_user->ID );
                    $success = 1;

                } else {
                    wp_safe_redirect( $referrer . '&sucess=2' );
                    exit;
                }

            } else {
                wp_safe_redirect( $referrer . '&sucess=3' );
                exit;
            }

            if ( $success === 1 ) {

                wp_cache_delete( $mjschool_user->ID, 'users' );
                wp_cache_delete( $mjschool_user_data->user_login, 'mjschool_user_logins' );

                wp_logout();

                $login_result = wp_signon(
                    array(
                        'user_login'    => $mjschool_user_data->user_login,
                        'user_password' => $new_pass,
                    ),
                    false
                );

                if ( ! is_wp_error( $login_result ) ) {
                    wp_safe_redirect( $referrer . '&sucess=1' );
                    exit;
                }

                ob_start();

            } else {

                wp_set_auth_cookie( $mjschool_user->ID, true );

            }
        }
    }
}

if ( isset( $_REQUEST['sucess'] ) ) {
	$message = isset( $_REQUEST['sucess'] )
    ? sanitize_text_field( wp_unslash( $_REQUEST['sucess'] ) )
    : '';
	if ( $message === "1" ) {
		 ?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
			<p><?php esc_html_e( "Password Changed Successfully.", 'mjschool' ); ?></p>
		</div>
		<?php
	}
	if ($message === "2" ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
			<p><?php esc_html_e( "Confirm password does not match.", 'mjschool' ); ?></p>
		</div>
		<?php
	}
	if ($message === "3") {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
			<p><?php esc_html_e( "Enter correct current password.", 'mjschool' ); ?></p>
		</div>
		<?php
	}
	if ($message === "4") {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
			<p><?php esc_html_e( "Record Updated Successfully.", 'mjschool' ); ?></p>
		</div>
		<?php
	}
	if ($message === "5") {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
			<p><?php esc_html_e( "Enter New password.", 'mjschool' ); ?></p>
		</div>
		<?php
	}
	if ($message === "6") {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
			<p><?php esc_html_e( "Profile Updated Successfully.", 'mjschool' ); ?></p>
		</div>
		<?php
	}
}
if ($school_obj->role === "student") {
	?>
	<style>
		img.qr_code_account {
			height: 150px;
			width: 150px;
			margin-left: 15px;
		}
		img.account_profile_qr {
			height: 150px;
			width: 150px;
		}
		.mjschool-user-profile-header-left .row.user_student_account {
			width: 65% !important;
		}
		section.student_qr_margin {
			margin-top: 8% !important;
		}
	</style>
	<?php
	if (is_rtl( ) ) {
		?>
		<style>
			img.qr_code_account {
				height: 150px;
				width: 150px;
				margin-right: 15px;
				margin-left: 0px;
			}
			.mjschool-user-profile-header-left .row.user_student_account {
				width: 60% !important;
			}
		</style>
		<?php
	}
}
?>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!------------ Panel body. ------------>
	<div class="mjschool-view-page-main">
		<!-- POP-UP code. -->
		<div class="mjschool-popup-bg">
			<div class="mjschool-overlay-content">
				<div class="modal-content">
					<div class="profile_picture"></div>
				</div>
			</div>
		</div>
		<!-- End POP-UP code. -->
		<!-- Detail page header start. -->
		<section id="mjschool-user-information">
			<div class="mjschool-view-page-header-bg">
				<div class="row">
					<div class="col-xl-10 col-md-9 col-sm-10">
						<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
							<?php
							$userimage = mjschool_get_user_image($mjschool_user->ID);
							$class_id = get_user_meta(get_current_user_id(), 'class_name', true);
							$section_name = get_user_meta(get_current_user_id(), 'class_section', true);
							?>
							<img id="profile_change" class="mjschool-cursor-pointer mjschool-user-view-profile-image account_profile_qr" src="<?php if ( ! empty( $userimage ) ) { echo esc_url($userimage); } else { if ($school_obj->role === 'student' ) { echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); } elseif ($school_obj->role === 'supportstaff' ) { echo esc_url( get_option( 'mjschool_supportstaff_thumb_new' ) ); } elseif ($school_obj->role === 'teacher' ) { echo esc_url( get_option( 'mjschool_teacher_thumb_new' ) ); } else { echo esc_url( get_option( 'mjschool_parent_thumb_new' ) ); } } ?>">
							<?php if ($school_obj->role === "student") { ?>
								<img class="mjschool-id-card-barcode mjschool-user-view-profile-image qr_code_account" id='' src=''>
							<?php } ?>
							<div class="row mjschool-profile-user-name user_student_account">
								<div class="mjschool-float-left mjschool-view-top1">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
										<label class="mjschool-view-user-name-label"><?php echo esc_html( $mjschool_user->display_name); ?></label>
									</div>
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
										<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<label class="mjschool-color-white-rs"><?php echo esc_html( $mjschool_user->mobile_number); ?></label>
										</div>
									</div>
								</div>
							</div>
							<div id="rs_fd_account_address_width " class="row fd_account_module user_student_account">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div id="res_mt_8_per" class="mjschool-view-top2">
										<div class="row mjschool-view-user-doctor-label">
											<div class="col-md-12 mjschool-address-student-div">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page"><?php echo esc_html( $mjschool_user->address); ?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2 mjschool-add-btn-possition-res">
						<div class="mjschool-group-thumbs">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
						</div>
						
					</div>
				</div>
			</div>
		</section>
		<!-- Detail page header end. -->
		<section id="body_area" class="student_qr_margin body_areas">
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Account Information', 'mjschool' ); ?></h3>
			</div>
			<form class="mjschool-form-horizontal" action="#" id="user_account_info" method="post">
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<div class="row"><!--Row Div start.-->
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="email" class="mjschool_email_id_validation form-control validate[required,custom[email]] text-input" maxlength="50" type="text" name="email" value="<?php echo esc_attr( $mjschool_user_info->user_email ); ?>" disabled>
									<label  for="desc"><?php esc_html_e( 'Email', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input type="password" class="form-control" id="inputPassword" name="current_pass">
									<label  for="desc"><?php esc_html_e( 'Current Password', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input type="password" class="validate[required] form-control" minlength="8" maxlength="12" id="inputPassword" name="new_pass">
									<label  for="desc"><?php esc_html_e( 'New Password', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input type="password" class="validate[required] form-control" minlength="8" maxlength="12" id="inputPassword" name="conform_pass">
									<label  for="desc"><?php esc_html_e( 'Confirm Password', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'password_save_change_nonce_new' ); ?>
				<?php
				if ( $user_access['edit'] === 1 ) {
					?>
					<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
						<div class="row"><!--Row Div start.-->
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
								<button type="submit" class="btn mjschool-save-btn" name="save_change_new"><?php esc_html_e( 'Save', 'mjschool' ); ?></button>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</form>
			<?php $mjschool_user_info = get_userdata( get_current_user_id() ); ?>
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Other Information', 'mjschool' ); ?></h3>
			</div>
			<?php
			$edit = 1;
			?>
			<form class="mjschool-form-horizontal" id="user_other_info" action="#" method="post">
				<input type="hidden" value="<?php print esc_attr( $first_name ); ?>" name="first_name">
				<input type="hidden" value="<?php print esc_attr( $last_name ); ?>" name="last_name">
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<div class="row"><!--Row Div start.-->
						<div class="col-md-6 col-lg-6 col-sm-12 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="first_name" class="form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $mjschool_user_info->first_name ); } ?>" name="first_name">
									<label  for="date"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<?php wp_nonce_field( 'profile_save_change_nonce' ); ?>
						<div class="col-md-6 col-lg-6 col-sm-12 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="middle_name" class="form-control validate[custom[onlyLetter_specialcharacter] " type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $mjschool_user_info->middle_name );} ?>" name="middle_name">
									<label  for="date"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-6 col-sm-12 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="last_name" class="form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value=" <?php if ( $edit ) { echo esc_attr( $mjschool_user_info->last_name );} ?>" name="last_name">
									<label  for="date"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4">
									<div class="form-group input mjschool-margin-bottom-0">
										<div class="col-md-12 form-control">
											<input type="text" readonly value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) );?>"  class="form-control" name="phonecode">
											<label for="phonecode" class="pl-2 popup_countery_code_css"><?php esc_html_e( 'Country Code','mjschool' );?><span class="required red">*</span></label>
										</div>											
									</div>
								</div>
								<div class="col-md-8">
									<div class="form-group input mjschool-margin-bottom-0">
										<div class="col-md-12 form-control">
											<input id="mobile_number" class="form-control mjschool-margin-top-10px_res text-input validate[required,custom[phone_number],minSize[6],maxSize[15]]" type="text"  name="mobile_number" value="<?php if ( $edit){ echo esc_html( $mjschool_user_info->mobile_number);}elseif( isset( $_POST['mobile_number'] ) ) echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['mobile_number'] ) ) );?>">
											<label  for="mobile"><?php esc_html_e( 'Mobile Number','mjschool' );?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="address" class="form-control validate[custom[address_description_validation]]" type="text" maxlength="120" name="address" value="<?php if ( $edit){ echo esc_attr($mjschool_user_info->address);} ?>">
									<label  for="middle_name"><?php esc_html_e( 'Address','mjschool' );?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="city_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text"  name="city_name" value="<?php if ( $edit){ echo esc_attr($mjschool_user_info->city);} ?>">
									<label  for="middle_name"><?php esc_html_e( 'City','mjschool' );?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input class="form-control validate[custom[city_state_country_validation]]" type="text" maxlength="50" name="state_name" value="<?php if ( $edit){ echo esc_attr($mjschool_user_info->state);} ?>">
									<label  for="middle_name"><?php esc_html_e( 'State','mjschool' );?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input class="form-control validate[custom[onlyLetterNumber]]" maxlength="15" type="text"  name="zipcode" value="<?php if ( $edit){ echo esc_attr($mjschool_user_info->zip_code);} ?>">
									<label  for="middle_name"><?php esc_html_e( 'Zip Code','mjschool' );?></label>
								</div>
							</div>
						</div>
						<?php
						if ( $school_obj->role === 'student' ) {
						} elseif ( $school_obj->role === 'supportstaff' || $school_obj->role === 'teacher' ) {
							?>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="working_hour"><?php esc_html_e( 'Working Hour', 'mjschool' ); ?></label>
								<?php
								if ( $edit ) {
									$workrval = $mjschool_user_info->working_hour;
								} elseif ( isset( $_POST['working_hour'] ) ) {
									$workrval = sanitize_text_field( wp_unslash( $_REQUEST['working_hour'] ) );
								} else {
									$workrval = '';
								}
								?>
								<select name="working_hour" class="mjschool-line-height-30px form-control mjschool-max-width-100px" id="working_hour">
									<option value=""><?php esc_html_e( 'Select Job Time', 'mjschool' ); ?></option>
									<option value="full_time" <?php selected( $workrval, 'full_time' ); ?>><?php esc_html_e( 'Full Time', 'mjschool' ); ?></option>
									<option value="half_day" <?php selected( $workrval, 'half_day' ); ?>><?php esc_html_e( 'Part time', 'mjschool' ); ?></option>
								</select>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="email" class="form-control validate[custom[address_description_validation]]" maxlength="50" type="text" name="possition" value="<?php if ( $edit ) { echo esc_attr( $mjschool_user_info->possition ); } elseif ( isset( $_POST['possition'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['possition'] ) ) ); } ?>">
										<label  for="possition"><?php esc_html_e( 'Position', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<?php if ( $user_access['edit'] === '1' ) { ?>
						<div class="row"><!--Row Div start.-->
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
								<button type="submit" class="btn mjschool-save-btn" name="profile_save_change"><?php esc_html_e( 'Save', 'mjschool' ); ?></button>
							</div>
						</div>
					<?php } ?>
				</div>
			</form>
		</section>
	</div>
</div>
<?php
if ( ( $school_obj->role ) === 'teacher' ) {
	$teacher_id = $mjschool_user->ID;
}
if ( isset( $_POST['profile_save_change'] ) ) {

    $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

    if ( wp_verify_nonce( $nonce, 'profile_save_change_nonce' ) ) {

        $usermetadata = array(
            'address'       => sanitize_textarea_field( wp_unslash( $_POST['address'] ) ),
            'city'          => sanitize_text_field( wp_unslash( $_POST['city_name'] ) ),
            'state'         => sanitize_text_field( wp_unslash( $_POST['state_name'] ) ),
            'mobile_number' => sanitize_text_field( wp_unslash( $_POST['mobile_number'] ) ),
            'middle_name'   => sanitize_text_field( wp_unslash( $_POST['middle_name'] ) ),
            'first_name'    => sanitize_text_field( wp_unslash( $_POST['first_name'] ) ),
            'last_name'     => sanitize_text_field( wp_unslash( $_POST['last_name'] ) ),
            'zip_code'      => sanitize_text_field( wp_unslash( $_POST['zipcode'] ) ),
        );

        $firstname = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
        $lastname  = sanitize_text_field( wp_unslash( $_POST['last_name'] ) );

        $userdata = array(
            'display_name' => $firstname . ' ' . $lastname,
            'ID'           => $mjschool_user->ID,
        );

        $result = mjschool_update_user_profile( $userdata, $usermetadata );

        wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=account&sucess=4' ) );
        exit;
    }
}
if ( isset( $_POST['profile_save_change_new'] ) ) {
	 $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'profile_save_change_nonce_new' ) ) {
		$usermetadata   = array(
			'address' => sanitize_textarea_field( wp_unslash( $_POST['address'] ) ),
			'city'    => sanitize_text_field( wp_unslash( $_POST['city_name'] ) ),
			'state'   => sanitize_text_field( wp_unslash( $_POST['state_name'] ) ),
			'phone'   => sanitize_text_field( wp_unslash( $_POST['phone'] ) ),
		);
		$userdata       = array( 'user_email' => sanitize_email( wp_unslash( $_POST['email'] ) ) );
		$userdata['ID'] = $mjschool_user->ID;
		$result         = mjschool_update_user_profile( $userdata, $usermetadata );
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=account&sucess=4' ) );
		die();
	}
}
// Save profile picture.
if ( isset( $_POST['save_profile_pic'] ) ) {
	$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_POST['HTTP_REFERER'] ) ) : '';
	if ( $_FILES['profile']['size'] > 0 ) {
		$mjschool_user_image      = mjschool_load_documets( $_FILES['profile'], 'profile', 'pimg' );
		$photo_image_url = esc_url(content_url( '/uploads/school_assets/' . $mjschool_user_image));
	}
	$returnans = update_user_meta( $mjschool_user->ID, 'mjschool_user_avatar', $photo_image_url );
	if ( $returnans ) {
		wp_safe_redirect( $referrer . '&sucess=6' );
		die();
	}
}
?>