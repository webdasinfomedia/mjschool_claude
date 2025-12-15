<?php
class SubjectListing {
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'mjschool_redirect_method' ), 1 );
	}
	public function mjschool_redirect_method() {
		error_reporting( 0 );
		if ( $_REQUEST['mjschool-json-api'] == 'subject-listing' ) {
			$school_obj   = new MJSchool_Management( $_REQUEST['current_user'] );
			$response = $this->mjschool_subject_listing( $_REQUEST );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'all-subject-listing' ) {
			$response = $this->mjschool_all_subject_listing();
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
	}
	public function mjschool_subject_listing( $data ) {
		if ( ! empty( $_REQUEST['current_user'] ) && ! empty( $_REQUEST['access_token'] ) ) {
			$access_token = get_user_meta( $_REQUEST['current_user'], 'access_token', true );
			if ( $_REQUEST['access_token'] == $access_token ) {
				$response    = array();
				$school_obj  = new MJSchool_Management( $data['current_user'] );
				$obj_subject = new mjschool_subject();
				$user_role = mjschool_get_role( $data['current_user'] );
				$role      = $user_role[0];
				$menu_access_data = mjschool_get_user_role_wise_access_right_array_in_api( $data['current_user'], 'subject' );
				if ( $role == 'teacher' ) {
					if ( $menu_access_data['view'] == '1' && $menu_access_data['own_data'] == '1' ) {
						$subjectdata   = array();
						$subjects_data = $obj_subject->mjschool_get_teacher_own_subject( $data['current_user'] );
						foreach ( $subjects_data as $s_id ) {
							$subjectdata[] = mjschool_get_subject( $s_id->subject_id );
						}
					} elseif ( $menu_access_data['view'] == '1' && $menu_access_data['own_data'] == '0' ) {
						$subjectdata = mjschool_get_all_data( 'mjschool_subject' );
					} else {
						$subjectdata = '';
					}
				}
				if ( $role == 'student' ) {
					if ( $menu_access_data['view'] == '1' && $menu_access_data['own_data'] == '1' ) {
						$subjectdata = $school_obj->subject;
					} elseif ( $menu_access_data['view'] == '1' && $menu_access_data['own_data'] == '0' ) {
						$subjectdata = mjschool_get_all_data( 'mjschool_subject' );
					} else {
						$subjectdata = '';
					}
				}
				if ( $role == 'administrator' ) {
					$subjectdata = mjschool_get_all_data( 'mjschool_subject' );
				}
				if ( ! empty( $subjectdata ) ) {
					$i = 0;
					foreach ( $subjectdata as $retrieved_data ) {
						$teacher_group = array();
						$teacher_ids   = mjschool_teacher_by_subject( $retrieved_data );
						foreach ( $teacher_ids as $teacher_id ) {
							$teacher_group[] = mjschool_get_teacher( $teacher_id );
						}
						$teachers                     = implode( ',', $teacher_group );
						$result[ $i ]['id']           = $retrieved_data->subid;
						$result[ $i ]['subject_name'] = $retrieved_data->sub_name;
						if ( $role == 'student' ) {
							$result[ $i ]['teacher'] = $teachers;
						}
						$cid                      = $retrieved_data->class_id;
						$result[ $i ]['class_id'] = $cid;
						$result[ $i ]['class']    = mjschool_get_class_name( $cid );
						if ( $retrieved_data->section_id != 0 ) {
							$result[ $i ]['section_id'] = $retrieved_data->section_id;
							$section_name               = mjschool_get_section_name( $retrieved_data->section_id );
						} else {
							$section_name = esc_html__( 'No Section', 'mjschool' );
						}
						$result[ $i ]['section']     = $section_name;
						$result[ $i ]['author_name'] = $retrieved_data->author_name;
						$result[ $i ]['edition']     = $retrieved_data->edition;
						$syllabus                    = '';
						if ( $retrieved_data->syllabus != '' ) {
							$syllabus = content_url() . '/uploads/school_assets/' . $retrieved_data->syllabus;
						}
						$result[ $i ]['syllabus_url'] = $syllabus;
						++$i;
					}
					$response['status']   = 1;
					$response['resource'] = $result;
					return $response;
				} else {
					$response['status']  = 0;
					$response['message'] = esc_html__( 'Record not found', 'mjschool' );
				}
			} else {
				$response['status']  = 0;
				$response['message'] = esc_html__( 'An unauthorized user', 'mjschool' );
			}
		} else {
			$response['status']  = 0;
			$response['message'] = esc_html__( 'Please Fill All Fields', 'mjschool' );
		}
		return $response;
	}
	public function mjschool_all_subject_listing() {
		global $wpdb;
		$response   = array();
		$table_name = $wpdb->prefix . 'mjschool_subject';
		$sql        = "SELECT * FROM $table_name";
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$subjectdata = $wpdb->get_results( $sql );
		if ( ! empty( $subjectdata ) ) {
			$i = 0;
			foreach ( $subjectdata as $retrieved_data ) {
				$result[ $i ]['id']           = $retrieved_data->subid;
				$result[ $i ]['subject_name'] = $retrieved_data->sub_name;
				++$i;
			}
			$response['status']   = 1;
			$response['resource'] = $result;
			return $response;
		} else {
			$response['status']  = 0;
			$response['message'] = esc_html__( 'Record not found', 'mjschool' );
		}
		return $response;
	}
}
