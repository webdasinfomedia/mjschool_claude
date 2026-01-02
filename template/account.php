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
$mjschool_obj_user   = new Mjschool_User();
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) ) {
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

// Replaced deprecated PasswordHash class with wp_check_password() - WordPress now handles password verification internally
if ( isset( $_POST['save_change'] ) ) {

    $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

    if ( wp_verify_nonce( $nonce, 'password_save_change_nonce' ) ) {

        $current_pass = isset( $_POST['current_pass'] ) 
            ? sanitize_text_field( wp_unslash( $_POST['current_pass'] ) ) 
            : '';

        $new_pass = isset( $_POST['new_pass'] ) 
            ? sanitize_text_field( wp_unslash( $_POST['new_pass'] ) ) 
            : '';

        $confirm_pass = isset( $_POST['conform_pass'] ) 
            ? sanitize_text_field( wp_unslash( $_POST['conform_pass'] ) ) 
            : '';

        $referrer = isset( $_SERVER['HTTP_REFERER'] ) 
            ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) 
            : '';

        if ( ! empty( $current_pass ) && ! empty( $new_pass ) && ! empty( $confirm_pass ) ) {

            $success = 0;

            // Replaced PasswordHash->CheckPassword() with wp_check_password()
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
if ( isset( $_POST['save_change_new'] ) ) {

    $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

    if ( wp_verify_nonce( $nonce, 'password_save_change_nonce_new' ) ) {

        $current_pass = isset( $_POST['current_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['current_pass'] ) ) : '';
        $new_pass     = isset( $_POST['new_pass'] )     ? sanitize_text_field( wp_unslash( $_POST['new_pass'] ) )     : '';
        $confirm_pass = isset( $_POST['conform_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['conform_pass'] ) ) : '';

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
	if ( $message === '1' ) {
		 ?>
		<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<h4><i class="icon fa fa-check"></i><?php esc_html_e( 'Success!', 'mjschool' ); ?></h4>
			<?php esc_html_e( 'Password Changed successfully.', 'mjschool' ); ?>
		</div>
		<?php
	} elseif ( $message === '2' ) {
		?>
		<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<h4><i class="icon fa fa-check"></i><?php esc_html_e( 'Error!', 'mjschool' ); ?></h4>
			<?php esc_html_e( 'New password does not match confirm password.', 'mjschool' ); ?>
		</div>
		<?php
	} elseif ( $message === '3' ) {
		?>
		<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<h4><i class="icon fa fa-check"></i><?php esc_html_e( 'Error!', 'mjschool' ); ?></h4>
			<?php esc_html_e( 'Current password is incorrect.', 'mjschool' ); ?>
		</div>
		<?php
	} elseif ( $message === '4' ) {
		?>
		<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<h4><i class="icon fa fa-check"></i><?php esc_html_e( 'Success!', 'mjschool' ); ?></h4>
			<?php esc_html_e( 'User information updated successfully.', 'mjschool' ); ?>
		</div>
		<?php
	} elseif ( $message === '5' ) {
		?>
		<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<h4><i class="icon fa fa-check"></i><?php esc_html_e( 'Error!', 'mjschool' ); ?></h4>
			<?php esc_html_e( 'Oops something wrong!', 'mjschool' ); ?>
		</div>
		<?php
	} elseif ( $message === '6' ) {
		?>
		<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<h4><i class="icon fa fa-check"></i><?php esc_html_e( 'Success!', 'mjschool' ); ?></h4>
			<?php esc_html_e( 'Profile Photo Upload Successfully.', 'mjschool' ); ?>
		</div>
		<?php
	}
}
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel mjschool-content-section">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 panel-heading panel-heading mjschool-button-section">
					<h6 class="panel-title custom-panel-title"><?php esc_html_e( 'User Information', 'mjschool' ); ?></h6>
				</div>
			</div>
			<div class="mjschool-user-info-section mjschool-user-photo-section"><!--Photo Section start.-->
				<div class="row">
					<div class="col-md-12">
						<div class="col-lg-6 col-xl-6 col-md-6 col-sm-12">
							<div class="mjschool-photo-main-div">
								<div class="mjschool-user-photo-section">
									<img alt="<?php esc_attr_e( 'User Photo', 'mjschool' ); ?>" src="<?php echo esc_url( mjschool_custom_show_avatar_thumbnail( get_current_user_id() ) ); ?>">
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-xl-6 col-md-6 col-sm-12">
							<form action="" method="post" name="demo" id="demo" enctype="multipart/form-data">
								<div class="form-body mjschool-user-form mjschool-photo-custom-form">
									<div class="row">
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
											<div class="form-group">
												<input type="file" name="profile" accept="image/x-png,image/gif,image/jpeg" class="validate[required] form-control"> <br/>
												<input type="hidden" name="HTTP_REFERER" value="<?php echo isset( $_SERVER['HTTP_REFERER'] ) ? esc_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : ''; ?>">
											</div>
										</div>
									</div>
								</div>
								<div class="form-body mjschool-user-form">
									<?php if ( $user_access['edit'] === '1' ) { ?>
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 mjschool-button-section">
												<button type="submit" class="btn mjschool-save-btn" name="save_profile_pic"><?php esc_html_e( 'Save', 'mjschool' ); ?></button>
											</div>
										</div>
									<?php } ?>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div><!-- Photo section close.-->
		</section>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<section class="panel mjschool-content-section">
			<form name="signupform" class="signupform" id="signupform" action="" method="post">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 panel-heading panel-heading mjschool-button-section">
						<h6 class="panel-title custom-panel-title"><?php esc_html_e( 'Change Password', 'mjschool' ); ?></h6>
					</div>
				</div>
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'password_save_change_nonce' ) ); ?>" />
				<div class="row form-body mjschool-user-form">
					<div class="col-md-4 col-lg-4 col-sm-12 col-xl-4">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="current_pass" class="form-control validate[required] text-input" type="password" maxlength="50" value="" name="current_pass">
								<label for="date"><?php esc_html_e( 'Current password', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-lg-4 col-sm-12 col-xl-4">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="new_pass" class="form-control validate[required] text-input" type="password" maxlength="50" value="" name="new_pass">
								<label for="date"><?php esc_html_e( 'New password', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-lg-4 col-sm-12 col-xl-4">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="conform_pass" class="form-control validate[required,equals[new_pass]] text-input" type="password" maxlength="50" value="" name="conform_pass">
								<label for="date"><?php esc_html_e( 'Confirm password', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
				</div>
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<?php if ( $user_access['edit'] === '1' ) { ?>
						<div class="row"><!--Row Div start.-->
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
								<button type="submit" class="btn mjschool-save-btn" name="save_change"><?php esc_html_e( 'Save', 'mjschool' ); ?></button>
							</div>
						</div>
					<?php } ?>
				</div>
			</form>
		</section>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<section class="panel mjschool-content-section">
			<form name="signupform" class="signupform" id="signupform" action="" method="post">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 panel-heading panel-heading mjschool-button-section">
						<h6 class="panel-title custom-panel-title"><?php esc_html_e( 'Personal Information', 'mjschool' ); ?></h6>
					</div>
				</div>
				<?php
				$edit = false;
				if ( ! empty( $mjschool_user_info ) ) {
					$edit = true;
				}
				?>
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'profile_save_change_nonce' ) ); ?>" />
				<div class="row form-body mjschool-user-form">
					<div class="col-md-12 col-lg-12 col-sm-12 col-xl-12">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="first_name" type="text" maxlength="50" class="form-control text-input validate[required,custom[onlyLetter_specialcharacter]]" value="<?php if ( $edit ) { echo esc_attr( $mjschool_user_info->first_name ); } ?>" name="first_name">
								<label for="date"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-6 col-sm-12 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="middle_name" class="form-control validate[custom[onlyLetter_specialcharacter] " type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $mjschool_user_info->middle_name ); } ?>" name="middle_name">
								<label for="date"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-6 col-sm-12 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="last_name" class="form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value=" <?php if ( $edit ) { echo esc_attr( $mjschool_user_info->last_name ); } ?>" name="last_name">
								<label for="date"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-md-4">
								<div class="form-group input mjschool-margin-bottom-0">
									<div class="col-md-12 form-control">
										<input type="text" readonly value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>"  class="form-control" name="phonecode">
										<label for="phonecode" class="pl-2 popup_countery_code_css"><?php esc_html_e( 'Country Code', 'mjschool' ); ?><span class="required red">*</span></label>
									</div>											
								</div>
							</div>
							<div class="col-md-8">
								<div class="form-group input mjschool-margin-bottom-0">
									<div class="col-md-12 form-control">
										<input id="mobile_number" class="form-control mjschool-margin-top-10px_res text-input validate[required,custom[phone_number],minSize[6],maxSize[15]]" type="text" name="mobile_number" value="<?php if ( $edit ) { echo esc_html( $mjschool_user_info->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['mobile_number'] ) ) ); } ?>">
										<label for="mobile"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="address" class="form-control validate[custom[address_description_validation]]" type="text" maxlength="120" name="address" value="<?php if ( $edit ) { echo esc_attr( $mjschool_user_info->address ); } ?>">
								<label for="middle_name"><?php esc_html_e( 'Address', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="city_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" value="<?php if ( $edit ) { echo esc_attr( $mjschool_user_info->city ); } ?>">
								<label for="middle_name"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input class="form-control validate[custom[city_state_country_validation]]" type="text" maxlength="50" name="state_name" value="<?php if ( $edit ) { echo esc_attr( $mjschool_user_info->state ); } ?>">
								<label for="middle_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input class="form-control validate[custom[onlyLetterNumber]]" maxlength="15" type="text" name="zipcode" value="<?php if ( $edit ) { echo esc_attr( $mjschool_user_info->zip_code ); } ?>">
								<label for="middle_name"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?></label>
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
									<label for="possition"><?php esc_html_e( 'Position', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<?php
					}
					?>
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
        $result = $mjschool_obj_user->mjschool_update_user_profile( $userdata, $usermetadata );

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
		$result         = $mjschool_obj_user->mjschool_update_user_profile( $userdata, $usermetadata );
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=account&sucess=4' ) );
		die();
	}
}
// Save profile picture.
if ( isset( $_POST['save_profile_pic'] ) ) {
	// Fixed: Use $_POST instead of $_SERVER for HTTP_REFERER that was passed as hidden input
	$referrer = isset( $_POST['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_POST['HTTP_REFERER'] ) ) : '';
	if ( isset( $_FILES['profile'] ) && $_FILES['profile']['size'] > 0 ) {
		$mjschool_user_image = mjschool_load_documets( $_FILES['profile'], 'profile', 'pimg' );
		$photo_image_url     = esc_url( content_url( '/uploads/school_assets/' . $mjschool_user_image ) );
	}
	$returnans = update_user_meta( $mjschool_user->ID, 'mjschool_user_avatar', $photo_image_url );
	if ( $returnans ) {
		wp_safe_redirect( $referrer . '&sucess=6' );
		die();
	}
}
?>