<?php
class SchoolLogin {
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'mjschool_redirect_method' ), 1 );
	}
	public function mjschool_redirect_method() {
		if ( $_REQUEST['mjschool-json-api'] === 'school-login' ) {
			$lisence_response = array();
			// $lisence_response = $this->mjschool_app_licence_varification();
			$lisence_response['status'] = 1;
			if ( $lisence_response['status'] === 1 ) {
				$response = $this->mjschool_user_login( $_REQUEST['username'], $_REQUEST['password'], $_REQUEST['device_token'] );
				if ( is_array( $response ) ) {
					echo json_encode( $response );
				} else {
					header( 'HTTP/1.1 401 Unauthorized' );
				}
			} else {
				echo json_encode( $lisence_response );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] === 'app-details' ) {
			$response = $this->mjschool_get_app_details( $_REQUEST );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] === 'forgot_password' ) {
			$response = $this->mjschool_forgot_password( $_REQUEST );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] === 'web-login' ) {
			$response = $this->mjschool_user_login_web( $_REQUEST['username'], $_REQUEST['password'], $_REQUEST['access_token'] );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] === 'user-logout' ) {
			$response = $this->mjschool_user_logout( $_REQUEST );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] === 'menu-accessrigts-for-web' ) {
			$response = $this->mjschool_menu_accessrigts( $_REQUEST );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] === 'user_details' ) {
			$response = $this->mjschool_user_details( $_REQUEST );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
	}
	public function mjschool_forgot_password( $data ) {
		if ( ! empty( $data['email_id'] ) ) {
			if ( filter_var( $data['email_id'], FILTER_VALIDATE_EMAIL ) ) {
				$user_login = sanitize_email( $data['email_id'] );
				$user       = get_user_by( 'email', $user_login );
			} else {
				$user_login = sanitize_text_field( $data['email_id'] );
				$user       = get_user_by( 'login', $user_login );
			}
			if ( $user ) {
				$result = retrieve_password( $user_login );
				if ( is_wp_error( $result ) ) {
					$response['status']  = false;
					$response['code']    = 400;
					$response['message'] = wp_strip_all_tags( $result->get_error_message() );
					$response['data']    = null;
				} else {
					$response['status']  = true;
					$response['code']    = 200;
					$response['message'] = esc_html__( 'Link for password reset has been emailed to you. Please check your email.', 'mjschool' );
					$response['data']    = null;
				}
			} else {
				$response['status']  = false;
				$response['code']    = 400;
				$response['message'] = esc_html__( 'Incorrect Email.', 'mjschool' );
				$response['data']    = null;
			}
		} else {
			$response['status']  = false;
			$response['code']    = 401;
			$response['message'] = esc_html__( 'The email Id was missing', 'mjschool' );
			$response['data']    = null;
		}
		return $response;
	}
	public function mjschool_menu_accessrigts( $data ) {
		$user_id      = $data['current_user_id'];
		$verify_token = get_user_meta( $user_id, 'access_token', true );
		if ( $data['access_token'] === $verify_token ) {
			$user = get_userdata( $user_id );
			$role = $user->roles;
			if ( $role === 'parent' ) {
				$menu = get_option( 'mjschool_access_right_parent' );
			} elseif ( $role === 'supportstaff' ) {
				$menu = get_option( 'mjschool_access_right_supportstaff' );
			} elseif ( $role === 'teacher' ) {
				$menu = get_option( 'mjschool_access_right_teacher' );
			} elseif ( $role === 'management' ) {
				$menu = get_option( 'mjschool_access_right_management' );
			}
			$menu_array = array();
			if ( in_array( 'administrator', $role, true ) ) {
				$menu_array = array(
					array(
						'id'        => 1,
						'page_link' => 'mjschool_admission',
						'view'      => true,
					),
					array(
						'id'        => 2,
						'page_link' => 'mjschool_class',
						'view'      => true,
					),
					array(
						'id'        => 3,
						'page_link' => 'mjschool_route',
						'view'      => true,
					),
					array(
						'id'        => 4,
						'page_link' => 'mjschool_Subject',
						'view'      => true,
					),
					array(
						'id'        => 5,
						'page_link' => 'mjschool_student',
						'view'      => true,
					),
					array(
						'id'        => 6,
						'page_link' => 'mjschool_teacher',
						'view'      => true,
					),
					array(
						'id'        => 7,
						'page_link' => 'mjschool_supportstaff',
						'view'      => true,
					),
					array(
						'id'        => 8,
						'page_link' => 'mjschool_parent',
						'view'      => true,
					),
					array(
						'id'        => 9,
						'page_link' => 'mjschool_exam',
						'view'      => true,
					),
					array(
						'id'        => 10,
						'page_link' => 'mjschool_hall',
						'view'      => true,
					),
					array(
						'id'        => 11,
						'page_link' => 'mjschool_result',
						'view'      => true,
					),
					array(
						'id'        => 12,
						'page_link' => 'mjschool_grade',
						'view'      => true,
					),
					array(
						'id'        => 13,
						'page_link' => 'mjschool_Migration',
						'view'      => true,
					),
					array(
						'id'        => 14,
						'page_link' => 'mjschool_student_homewrok',
						'view'      => true,
					),
					array(
						'id'        => 15,
						'page_link' => 'mjschool_attendence',
						'view'      => true,
					),
					array(
						'id'        => 16,
						'page_link' => 'mjschool_document',
						'view'      => true,
					),
					array(
						'id'        => 17,
						'page_link' => 'mjschool_leave',
						'view'      => true,
					),
					array(
						'id'        => 18,
						'page_link' => 'mjschool_fees_payment',
						'view'      => true,
					),
					array(
						'id'        => 19,
						'page_link' => 'mjschool_payment',
						'view'      => true,
					),
					array(
						'id'        => 20,
						'page_link' => 'tax',
						'view'      => true,
					),
					array(
						'id'        => 21,
						'page_link' => 'mjschool_library',
						'view'      => true,
					),
					array(
						'id'        => 22,
						'page_link' => 'mjschool_hostel',
						'view'      => true,
					),
					array(
						'id'        => 23,
						'page_link' => 'mjschool_transport',
						'view'      => true,
					),
					array(
						'id'        => 24,
						'page_link' => 'mjschool_report',
						'view'      => true,
					),
					array(
						'id'        => 25,
						'page_link' => 'mjschool_notice',
						'view'      => true,
					),
					array(
						'id'        => 26,
						'page_link' => 'mjschool_message',
						'view'      => true,
					),
					array(
						'id'        => 27,
						'page_link' => 'mjschool_notification',
						'view'      => true,
					),
					array(
						'id'        => 28,
						'page_link' => 'mjschool_event',
						'view'      => true,
					),
					array(
						'id'        => 29,
						'page_link' => 'custom_field',
						'view'      => true,
					),
					array(
						'id'        => 30,
						'page_link' => 'mjschool_sms_setting',
						'view'      => true,
					),
					array(
						'id'        => 31,
						'page_link' => 'mjschool_email_template',
						'view'      => true,
					),
					array(
						'id'        => 32,
						'page_link' => 'mjschool_access_right',
						'view'      => true,
					),
					array(
						'id'        => 33,
						'page_link' => 'mjschool_system_videos',
						'view'      => true,
					),
					array(
						'id'        => 34,
						'page_link' => 'mjschool_system_addon',
						'view'      => true,
					),
					array(
						'id'        => 35,
						'page_link' => 'mjschool_general_settings',
						'view'      => true,
					),
				);
			} elseif ( $menu ) {
				foreach ( $menu as $key1 => $value1 ) {
					$i = 1;
					foreach ( $value1 as $key => $value ) {
						$menu_array1['id']        = $i;
						$menu_array1['page_link'] = $value['page_link'];
						if ( $value['view'] === '1' ) {
							$menu_array1['view'] = true;
						} else {
							$menu_array1['view'] = false;
						}
						$menu_array[] = $menu_array1;
						++$i;
					}
				}
			}
			$response['status']  = 1;
			$response['code']    = 200;
			$response['message'] = esc_html__( 'You have Access Rights', 'mjschool' );
			$response['data']    = $menu_array;
		} else {
			$response['status']  = 0;
			$response['code']    = 401;
			$response['message'] = esc_html__( 'An unauthorized user', 'mjschool' );
			$response['data']    = null;
			return $response;
		}
		return $response;
	}
	public function mjschool_user_logout( $data ) {
		$user_id      = $data['current_user_id'];
		$verify_token = get_user_meta( $user_id, 'access_token', true );
		if ( $data['access_token'] === $verify_token ) {
			delete_user_meta( $user_id, 'token_id' );
			delete_user_meta( $user_id, 'access_token' );
			clean_user_cache( $user_id );
			wp_clear_auth_cookie();
			$response['status']  = 1;
			$response['code']    = 200;
			$response['message'] = esc_html__( 'Logout successfully', 'mjschool' );
			$response['data']    = null;
		} else {
			$response['status']  = 0;
			$response['code']    = 401;
			$response['message'] = esc_html__( 'An unauthorized user', 'mjschool' );
			$response['data']    = null;
			return $response;
		}
		return $response;
	}
	public function mjschool_user_login_web( $username, $password, $access_token ) {
		if ( is_user_logged_in() ) {
			$current_user      = wp_get_current_user();
			$current_user_role = $current_user->roles[0];
			if ( $current_user_role === 'administrator' ) {
				$redirect_url = admin_url() . 'admin.php?page=mjschool';
			} else {
				$redirect_url = home_url( '/?dashboard=mjschool_user' );
			}
			$response['status']  = 1;
			$response['code']    = 200;
			$response['message'] = esc_html__( 'Successfully login', 'mjschool' );
			$response['data']    = $redirect_url;
		} elseif ( ! empty( $username ) and ! empty( $password ) ) {
			$response = array();
			if ( filter_var( $username, FILTER_VALIDATE_EMAIL ) ) {
				$user = get_user_by( 'email', $username );
			} else {
				$user = get_user_by( 'login', $username );
			}
			if ( $user ) {
				if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
					$response['status']  = 0;
					$response['code']    = 401;
					$response['message'] = esc_html__( 'Username/Password incorrect', 'mjschool' );
					$response['data']    = null;
				} else {
					$user_id    = $user->ID;
					$caps       = get_user_meta( $user_id, 'wp_capabilities', true );
					$user_roles = array_keys( (array) $caps );
					clean_user_cache( $user_id );
					wp_clear_auth_cookie();
					// Set the current user
					wp_set_current_user( $user_id );
					// Set the authentication cookie (force HTTPS if admin runs on HTTPS)
					$is_secure = is_ssl();
					wp_set_auth_cookie( $user_id, true, $is_secure );
					$all_user_role = array( 'administrator' );
					if ( in_array( $user_roles[0], $all_user_role, true ) ) {
						$redirect_url = admin_url() . 'admin.php?page=mjschool';
					} else {
						$redirect_url = home_url( '/?dashboard=mjschool_user' );
					}
					$response['status']  = 1;
					$response['code']    = 200;
					$response['message'] = esc_html__( 'Successfully login', 'mjschool' );
					$response['data']    = $redirect_url;
				}
			}
		} else {
			$response['status']  = 0;
			$response['code']    = 401;
			$response['message'] = esc_html__( 'Authentication credentials were missing', 'mjschool' );
			$response['data']    = null;
		}
		return $response;
	}
	public function mjschool_user_login( $username, $password, $device_token ) {
		if ( ! empty( $username ) and ! empty( $password ) ) {
			$response = array();
			if ( filter_var( $username, FILTER_VALIDATE_EMAIL ) ) {
				// Invalid Email
				$user = get_user_by( 'email', $username );
			} else {
				$user = get_user_by( 'login', $username );
			}
			if ( $user ) {
				$user        = get_userdata( $user->ID );
				$roles_array = array( 'student', 'parent', 'supportstaff', 'teacher', 'management', 'administrator' );
				if ( array_intersect( $roles_array, $user->roles ) ) {
					if ( get_user_meta( $user->ID, 'hash', true ) ) {
						$response['status']   = 0;
						$response['resource'] = null;
						$response['message']  = esc_html__( 'Your account is inactive. Contact your administrator to activate it.', 'mjschool' );
					} elseif ( $user ) {
						if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
							$response['status']   = 0;
							$response['resource'] = null;
							$response['message']  = esc_html__( 'Email/Password incorrect.', 'mjschool' );
						} else {
							$tokan_string  = $user->ID . '_' . $user->user_email . '_' . $user->user_pass;
							$access_token  = base64_encode( $tokan_string );
							$ver_token     = update_user_meta( $user->ID, 'access_token', $access_token );
							$dev_token     = update_user_meta( $user->ID, 'token_id', $device_token );
							$school_obj    = new MJSchool_Management( $user->ID );
							$retrived_data = get_userdata( $user->ID );
							clean_user_cache( $user->ID );
							wp_clear_auth_cookie();
							// Set the current user
							wp_set_current_user( $user->ID );
							// Set the authentication cookie (force HTTPS if admin runs on HTTPS)
							$is_secure = is_ssl();
							wp_set_auth_cookie( $user->ID, true, $is_secure );
							$student['ID'] = $retrived_data->ID;
							$student['display_name'] = $retrived_data->display_name;
							$student['email'] = $retrived_data->user_email;
							$student['image'] = $retrived_data->smgt_user_avatar;
							$student['user_role'] = $school_obj->role;
							$student['currency'] = get_option( 'mjschool_currency_code' );
							$student['access_token'] = $retrived_data->access_token;
							$response['status']   = 1;
							$response['resource'] = $student;
						}
					} else {
						$response['status']   = 0;
						$response['resource'] = null;
						$response['message']  = esc_html__( 'Email/Password incorrect.', 'mjschool' );
					}
				} else {
					$response['status']   = 0;
					$response['resource'] = null;
					$response['message']  = esc_html__( 'You cannot log in the application.', 'mjschool' );
				}
			} else {
				$response['status']   = 0;
				$response['resource'] = null;
				$response['message']  = esc_html__( 'Email/Password incorrect.', 'mjschool' );
			}
		} else {
			$response['status']   = 0;
			$response['resource'] = null;
			$response['message']  = esc_html__( 'Authentication credentials were missing.', 'mjschool' );
		}
		return $response;
	}
	public function mjschool_app_licence_varification() {
		$response    = array();
		$server_url  = home_url();
		$licence_key = get_option( 'mjschool_app_licence_key' );
		$email       = get_option( 'mjschool_app_setup_email' );
		if ( ! empty( $licence_key ) && ! empty( $email ) ) {
			$result = mjschool_check_product_key( $server_url, $licence_key, $email );
			if ( $result === '1' ) {
				$response['status']  = 0;
				$response['code']    = 401;
				$response['message'] = esc_html__( 'There seems to be some problem please try after sometime or contact us on sales@mojoomla.com.', 'mjschool' );
				$response['data']    = null;
			} elseif ( $result === '2' ) {
				$response['status']  = 0;
				$response['code']    = 401;
				$response['message'] = esc_html__( 'This purchase key is already registered with the different domain. If have any issue please contact us at sales@mojoomla.com.', 'mjschool' );
				$response['data']    = null;
			} elseif ( $result === '3' ) {
				$response['status']  = 0;
				$response['code']    = 401;
				$response['message'] = esc_html__( 'There seems to be some problem please try after sometime or contact us on sales@mojoomla.com.', 'mjschool' );
				$response['data']    = null;
			} else {
				$response['status'] = 1;
				$response['code']   = 200;
			}
		} else {
			$response['status']  = 0;
			$response['code']    = 401;
			$response['message'] = esc_html__( 'License verification incomplete. Please contact the administrator for assistance.', 'mjschool' );
			$response['data']    = null;
		}
		return $response;
	}
	public function mjschool_get_app_details( $data ) {
		$get_data['app_icon']  = get_option( 'mjschool_app_logo' );
		$get_data['app_color'] = get_option( 'mjschool_system_color_code' );
		$lancode              = get_locale();
		$code                 = substr( $lancode, 0, 2 );
		$response['status']   = 1;
		$get_data['language'] = $code;
		if ( ! empty( $get_data ) ) {
			$response['status']  = true;
			$response['code']    = 200;
			$response['message'] = esc_html__( 'Record found successfully', 'mjschool' );
			$response['data']    = $get_data;
			return $response;
		} else {
			$response['status']  = false;
			$response['code']    = 401;
			$response['message'] = esc_html__( 'An App Icon not uploaded', 'mjschool' );
			$response['data']    = null;
			return $response;
		}
	}
	public function mjschool_user_details( $data ) {
		$verify_token = get_user_meta( $data['current_user_id'], 'verify_token', true );
		if ( $data['verify_token'] === $verify_token ) {
			$user = get_userdata( $data['current_user_id'] );
			if ( ! empty( $user->smgt_user_avatar ) ) {
				$user_profile = $user->smgt_user_avatar;
			} else {
				if ( array_intersect( array( 'administrator' ), $user->roles ) ) {
					$user_profile = get_option( 'mjschool_app_logo' );
				} elseif ( array_intersect( array( 'student' ), $user->roles ) ) {
					$user_profile = get_option( 'mjschool_student_thumb' );
				} elseif ( array_intersect( array( 'parent' ), $user->roles ) ) {
					$user_profile = get_option( 'mjschool_parent_thumb' );
				} elseif ( array_intersect( array( 'supportstaff' ), $user->roles ) ) {
					$user_profile = get_option( 'mjschool_supportstaff_thumb' );
				} elseif ( array_intersect( array( 'teacher' ), $user->roles ) ) {
					$user_profile = get_option( 'mjschool_teacher_thumb' );
				} else {
					$user_profile = get_option( 'mjschool_app_logo' );
				}
			}
			if ( ! empty( $user ) ) {
				$user_data['user_id']           = $user->ID;
				$user_data['encrypted_user_id'] = mjschool_encrypt_id( $user->ID );
				$user_data['display_name']      = $user->display_name;
				$user_data['profile_image']     = $user_profile;
				$response['status']             = true;
				$response['code']               = 200;
				$response['message']            = esc_html__( 'Record found successfully', 'mjschool' );
				$response['data']               = $user_data;
				return $response;
			} else {
				$response['status']  = false;
				$response['code']    = 401;
				$response['message'] = esc_html__( 'Record not found', 'mjschool' );
				$response['data']    = null;
			}
		} else {
			$response['status']  = false;
			$response['code']    = 401;
			$response['message'] = esc_html__( 'An unauthorized user', 'mjschool' );
			$response['data']    = null;
			return $response;
		}
	}
}