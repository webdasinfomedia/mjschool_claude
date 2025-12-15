<?php
class ClassListing {
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'mjschool_redirect_method' ), 1 );
	}
	public function mjschool_redirect_method() {
		if ( $_REQUEST['mjschool-json-api'] == 'class-listing' ) {
			$response = $this->mjschool_class_listing( $_REQUEST );
			if ( is_array( $response ) ) {
				header( 'HTTP/1.1 200' );
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'add-class' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
			if ( $school_obj->role == 'admin' ) {
				$response = $this->mjschool_add_class( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'edit-class' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
			if ( $school_obj->role == 'admin' ) {
				$response = $this->mjschool_edit_class( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'delete-class' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
			if ( $school_obj->role == 'admin' ) {
				$response = $this->mjschool_api_delete_class( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'delete-section' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
			if ( $school_obj->role == 'admin' ) {
				$response = $this->mjschool_api_delete_section( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'add-class-section' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
			if ( $school_obj->role == 'admin' ) {
				$response = $this->mjschool_add_class_section( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'edit-class-section' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
			if ( $school_obj->role == 'admin' ) {
				$response = $this->mjschool_edit_class_section( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
	}
	public function mjschool_class_listing( $data ) {
		$response  = array();
		$tablename = 'mjschool_class';
		if ( $data['teacher_id'] != '' && $data['teacher_id'] != 0 ) {
			$school_obj = new MJSchool_Management( $data['teacher_id'] );
			if ( $school_obj->role == 'teacher' ) {
				$teacher_obj = new Mjschool_Teacher();
				$classdata   = $teacher_obj->mjschool_get_class_by_teacher( $data['teacher_id'] );
				if ( ! empty( $classdata ) ) {
					$i = 0;
					foreach ( $classdata as $class ) {
						$retrieved_data = get_class_by_id( $class['class_id'] );
						$section_result = mjschool_get_class_sections( $retrieved_data->class_id, $data['teacher_id'] );
						if ( ! empty( $section_result ) ) {
							$section_array = array();
							foreach ( $section_result as $retrieved_sectiondata ) {
								if ( get_option( 'mjschool_students_access' ) == 'own' && mjschool_get_roles( $data['teacher_id'] ) == 'teacher' ) {
									$retrieved_sectiondata = mjschool_get_section_name( $retrieved_sectiondata );
								}
								$result['id']             = $retrieved_data->class_id;
								$result['class_name']     = $retrieved_data->class_name;
								$result['class_num_name'] = $retrieved_data->class_num_name;
								$result['class_capacity'] = $retrieved_data->class_capacity;
								$section_result           = mjschool_get_class_sections( $retrieved_data->class_id, $data['teacher_id'] );
								$result['section_id']     = $retrieved_sectiondata->id;
								$result['section_name']   = $retrieved_sectiondata->section_name;
								$result2[] = $result;
							}
						}
					}
					$response['status']   = 1;
					$response['resource'] = $result2;
					return $response;
				}
			} else {
				$error['message']     = esc_html__( 'Please Fill All Fields', 'mjschool' );
				$response['status']   = 0;
				$response['resource'] = $error;
			}
			return $response;
		} else {
			$classdata = mjschool_get_all_data( $tablename );
			if ( ! empty( $classdata ) ) {
				$i = 0;
				foreach ( $classdata as $retrieved_data ) {
					$result[ $i ]['id']             = $retrieved_data->class_id;
					$result[ $i ]['class_name']     = $retrieved_data->class_name;
					$result[ $i ]['class_num_name'] = $retrieved_data->class_num_name;
					$result[ $i ]['class_capacity'] = $retrieved_data->class_capacity;
					$section_result                 = mjschool_get_class_sections( $retrieved_data->class_id );
					if ( ! empty( $section_result ) ) {
						$section_array = array();
						foreach ( $section_result as $retrieved_sectiondata ) {
							$section_array[] = array(
								'section_id'   => $retrieved_sectiondata->id,
								'section_name' => $retrieved_sectiondata->section_name,
							);
						}
						$result[ $i ]['sections'] = $section_array;
					} else {
						$result[ $i ]['sections'] = array();
					}
					++$i;
				}
				$response['status']   = 1;
				$response['resource'] = $result;
				return $response;
			} else {
				$error['message']     = esc_html__( 'Please Fill All Fields', 'mjschool' );
				$response['status']   = 0;
				$response['resource'] = $error;
			}
			return $response;
		}
	}
	function mjschool_add_class( $data ) {
		$created_date = date( 'Y-m-d H:i:s' );
		if ( $data['class_name'] != '' && $data['class_num_name'] != '' ) {
			$classdata = array(
				'class_name'     => $data['class_name'],
				'class_num_name' => $data['class_num_name'],
				'class_capacity' => $data['class_capacity'],
				'creater_id'     => $data['current_user'],
				'created_date'   => $created_date,
			);
			$response = array();
			global $wpdb;
			$table_name = $wpdb->prefix . 'mjschool_class';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_name, $classdata );
			if ( $result != 0 ) {
				$message['message']   = esc_html__( 'Record successfully Inserted', 'mjschool' );
				$response['status']   = 1;
				$response['resource'] = $message;
			}
		} else {
			$message['message']   = esc_html__( 'Please Fill All Fields', 'mjschool' );
			$response['status']   = 0;
			$response['resource'] = $message;
		}
		return $response;
	}
	function mjschool_edit_class( $data ) {
		$created_date = date( 'Y-m-d H:i:s' );
		if ( $data['class_name'] != '' && $data['class_num_name'] != '' && $data['class_id'] != '' && $data['class_id'] != 0 ) {
			$classdata = array(
				'class_name'     => $data['class_name'],
				'class_num_name' => $data['class_num_name'],
				'class_capacity' => $data['class_capacity'],
				'creater_id'     => $data['current_user'],
				'created_date'   => $created_date,
			);
			$response = array();
			global $wpdb;
			$table_name          = $wpdb->prefix . 'mjschool_class';
			$whereid['class_id'] = $data['class_id'];
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->update( $table_name, $classdata, $whereid );
			if ( $result != 0 ) {
				$message['message']   = esc_html__( 'Record successfully Updated', 'mjschool' );
				$response['status']   = 1;
				$response['resource'] = $message;
			}
		} else {
			$message['message']   = esc_html__( 'Please Fill All Fields', 'mjschool' );
			$response['status']   = 0;
			$response['resource'] = $message;
		}
		return $response;
	}
	function mjschool_api_delete_class( $data ) {
		$response = array();
		global $wpdb;
		$table_name = 'mjschool_class';
		if ( $data['class_id'] != 0 ) {
			$result = delete_class( $table_name, $data['class_id'] );
			if ( $result ) {
				$message['message']   = esc_html__( 'Records Deleted Successfully!', 'mjschool' );
				$response['status']   = 1;
				$response['resource'] = $message;
			}
		} else {
			$message['message']   = esc_html__( 'Please Fill All Fields', 'mjschool' );
			$response['status']   = 0;
			$response['resource'] = $message;
		}
		return $response;
	}
	function mjschool_api_delete_section( $data ) {
		$response = array();
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_class_section';
		if ( $data['section_id'] != 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->query( "DELETE FROM $table_name where id= " . $data['section_id'] );
			if ( $result ) {
				$message['message']   = esc_html__( 'Records Deleted Successfully!', 'mjschool' );
				$response['status']   = 1;
				$response['resource'] = $message;
			}
		} else {
			$message['message']   = esc_html__( 'Please Fill All Fields', 'mjschool' );
			$response['status']   = 0;
			$response['resource'] = $message;
		}
		return $response;
	}
	function mjschool_add_class_section( $data ) {
		$created_date = date( 'Y-m-d H:i:s' );
		if ( $data['section_name'] != '' && $data['class_id'] != '' ) {
			$sectiondata = array(
				'section_name' => $data['section_name'],
				'class_id'     => $data['class_id'],
			);
			$response = array();
			global $wpdb;
			$table_name = $wpdb->prefix . 'mjschool_class_section';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_name, $sectiondata );
			if ( $result != 0 ) {
				$message['message']   = esc_html__( 'Record successfully Inserted', 'mjschool' );
				$response['status']   = 1;
				$response['resource'] = $message;
			}
		} else {
			$message['message']   = esc_html__( 'Please Fill All Fields', 'mjschool' );
			$response['status']   = 0;
			$response['resource'] = $message;
		}
		return $response;
	}
	function mjschool_edit_class_section( $data ) {
		$created_date = date( 'Y-m-d H:i:s' );
		if ( $data['section_name'] != '' && $data['class_id'] != '' ) {
			$sectiondata = array(
				'section_name' => $data['section_name'],
				'class_id'     => $data['class_id'],
			);
			$response = array();
			global $wpdb;
			$whereid['id'] = $data['section_id'];
			$table_name    = $wpdb->prefix . 'mjschool_class_section';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->update( $table_name, $sectiondata, $whereid );
			if ( $result != 0 ) {
				$message['message']   = esc_html__( 'Record successfully Updated', 'mjschool' );
				$response['status']   = 1;
				$response['resource'] = $message;
			}
		} else {
			$message['message']   = esc_html__( 'Please Fill All Fields', 'mjschool' );
			$response['status']   = 0;
			$response['resource'] = $message;
		}
		return $response;
	}
}