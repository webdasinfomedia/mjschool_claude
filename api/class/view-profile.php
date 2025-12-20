<?php
class ViewProfile {
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'mjschool_redirect_method' ), 1 );
	}
	public function mjschool_redirect_method() {
		if ( $_REQUEST['mjschool-json-api'] == 'view-profile' ) {
			$response = $this->mjschool_view_profile( $_REQUEST['user_id'] );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'view-profile-teacher-login' ) {
			$response = $this->mjschool_view_profile( $_REQUEST['user_id'] );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'profile_image_update' ) {
			$response = $this->mjschool_update_user_profile( $_REQUEST['user_id'] );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
	}
	public function mjschool_view_profile( $user_id ) {
		if ( ! empty( $_REQUEST['user_id'] ) && ! empty( $_REQUEST['access_token'] ) ) {
			$access_token = get_user_meta( $_REQUEST['user_id'], 'access_token', true );
			if ( $user_id != 0 ) {
				$user_data = get_userdata( $user_id );
			}
			$school_obj = new MJSchool_Management( $user_id );
			if ( ! empty( $user_data ) ) {
				$umetadata = mjschool_get_user_image( $user_id );
				if ( empty( $umetadata ) ) {
					$imageurl = get_option( 'mjschool_student_thumb_new' );
				} else {
					$imageurl = $umetadata;
				}
				$result['ID'] = $user_data->ID;
				$result['first_name']  = $user_data->first_name;
				$result['last_name']   = $user_data->last_name;
				$result['middle_name'] = $user_data->middle_name;
				$result['image']       = $imageurl;
				$result['name']        = $user_data->display_name;
				$result['username']    = $user_data->user_login;
				$result['email']       = $user_data->user_email;
				$result['address']     = $user_data->address;
				$result['city']        = $user_data->city;
				$result['state']       = $user_data->state;
				$result['phone']       = $user_data->mobile_number;
				$obj_subject = new Mjschool_Subject();
				if ( $school_obj->role == 'student' ) {
					if ( $user_data->class_name != '' ) {
						$classname = mjschool_get_class_name( $user_data->class_name );
					}
					if ( isset( $user_data->class_section ) && $user_data->class_section != 0 ) {
						$section = mjschool_get_section_name( $user_data->class_section );
					} else {
						$section = esc_html__( 'No Section', 'mjschool' );
					}
					$parentdata           = get_user_meta( $user_data->ID, 'parent_id', true );
					$result['class_id']   = $user_data->class_name;
					$result['class']      = $classname;
					$result['section_id'] = $user_data->class_section;
					$result['section']    = $section;
					foreach ( $parentdata as $parentid ) {
						$parent              = get_userdata( $parentid );
						$parentarray['name'] = $parent->display_name;
						if ( $parent->smgt_user_avatar ) {
							$parentarray['image'] = $parent->smgt_user_avatar;
						} else {
							$parentarray['image'] = get_option( 'mjschool_parent_thumb_new' );
						}
						$parentarray['relation'] = $parent->relation;
						$parents[]               = $parentarray;
					}
					if ( ! empty( $parents ) ) {
						$result['parents'] = $parents;
					}
				}
				if ( $school_obj->role == 'parent' ) {
					$childsdata = get_user_meta( $user_data->ID, 'child', true );
					foreach ( $childsdata as $childid ) {
						$child                = get_userdata( $childid );
						$childsarray['name']  = $child->display_name;
						$childsarray['image'] = $child->smgt_user_avatar;
						$childrens[]          = $childsarray;
					}
					if ( ! empty( $childrens ) ) {
						$result['child'] = $childrens;
					}
				}
				if ( $school_obj->role == 'teacher' ) {
					$result['subjects'] = $obj_subject->mjschool_get_subject_name_by_teacher( $user_data->ID );
				}
				$response['status']   = 1;
				$response['resource'] = $result;
				return $response;
			} else {
				$response['status']  = 0;
				$response['message'] = esc_html__( 'Record Not Found', 'mjschool' );
			}
		} else {
			$response['status']  = 0;
			$response['message'] = esc_html__( 'Please Fill All Fields', 'mjschool' );
		}
		return $response;
	}
	public function mjschool_update_user_profile( $user_id ) {
		if ( ! empty( $_REQUEST['user_id'] ) && ! empty( $_REQUEST['access_token'] ) ) {
			$access_token = get_user_meta( $_REQUEST['user_id'], 'access_token', true );
			if ( $_REQUEST['access_token'] == $access_token ) {
				if ( isset( $user_id ) && $user_id != '' ) {
					if ( $_FILES['image']['size'] > 0 ) {
						$smgt_avatar_image = mjschool_user_avatar_image_upload( 'image' );
						$smgt_avatar       = content_url() . '/uploads/school_assets/' . $smgt_avatar_image;
					}
					$result = update_user_meta( $user_id, 'mjschool_user_avatar', $smgt_avatar );
					if ( $result ) {
						$response['status']  = 1;
						$response['message'] = esc_html__( 'Profile image successfully updated!', 'mjschool' );
					} else {
						$response['status']  = 0;
						$response['message'] = esc_html__( 'Profile image not updated', 'mjschool' );
					}
					return $response;
				}
			} else {
				$response['status']  = 0;
				$response['message'] = esc_html__( 'An unauthorized user', 'mjschool' );
			}
		} else {
			$response['status']  = 0;
			$response['message'] = esc_html__( 'Please Fill All Fields', 'mjschool' );
		}
	}
}