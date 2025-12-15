<?php
class MJSchool_Attendance {
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'mjschool_redirect_method' ), 1 );
	}
	public function mjschool_redirect_method() {
		if ( $_REQUEST['mjschool-json-api'] == 'save-attendance' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['student_id'] );
			if ( $school_obj->role == 'student' ) {
				$response = $this->mjschool_attendance_save( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'save-attendance-with_qr_code' ) {
			$role = mjschool_get_roles( $_REQUEST['student_id'] );
			if ( $role == 'student' ) {
				$response = $this->mjschool_student_attendance_with_qr_code( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'view-student-attandance' ) {
			$role = mjschool_get_roles( $_REQUEST['current_user'] );
			if ( $role == 'student' ) {
				$response = $this->mjschool_student_attendance_view( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'view-student-subjectwise-attandance' ) {
			$response = $this->mjschool_student_subject_attendance_view( $_REQUEST );
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'save-teacher-attendance' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['teacher_id'] );
			if ( $school_obj->role == 'teacher' ) {
				$response = $this->mjschool_teacher_attendance_save( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'save-subject-attendance' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['student_id'] );
			if ( $school_obj->role == 'student' ) {
				$response = $this->mjschool_attendance_save_subject( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'view-attendance-list' ) {
			$role = mjschool_get_roles( $_REQUEST['current_user'] );
			if ( $role == 'teacher' || $role == 'admin' ) {
				$response = $this->mjschool_attendance_view_list( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'view-teacher-attendance-list' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
			if ( $school_obj->role == 'admin' ) {
				$response = $this->mjschool_teachers_attendance_list( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
		if ( $_REQUEST['mjschool-json-api'] == 'view-subject-attendance-list' ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
			if ( $school_obj->role == 'teacher' || $school_obj->role == 'admin' ) {
				$response = $this->mjschool_subject_attendance_view_list( $_REQUEST );
			}
			if ( is_array( $response ) ) {
				echo json_encode( $response );
			} else {
				header( 'HTTP/1.1 401 Unauthorized' );
			}
			die();
		}
	}
	public function mjschool_student_attendance_with_qr_code( $data ) {
		$obj_attend          = new MJSchool_Attendence_Manage();
		$school_obj          = new MJSchool_Management( $data['student_id'] );
		$student_class       = $school_obj->class_info->class_id;
		$sub_id              = $data['subject'];
		$user_id             = $data['student_id'];
		$mjschool_qr_class_id         = $data['class_id'];
		$qr_section_id       = $data['section_id'];
		$curr_date           = date( 'Y-m-d' );
		$status              = 'Present';
		$attend_by           = $data['attend_by'];
		$attendanace_comment = '';
		$attendence_type     = 'QR';
		if ( $data['student_id'] != '' && $data['class_id'] != '' && $data['attend_by'] != '' ) {
			if ( $student_class == $mjschool_qr_class_id ) {
				$savedata = $obj_attend->mjschool_insert_subject_wise_attendance( $curr_date, $mjschool_qr_class_id, $user_id, $attend_by, $status, $sub_id, $attendanace_comment, $attendence_type, $qr_section_id );
				if ( $savedata ) {
					$response['status']  = 1;
					$response['message'] = esc_html__( 'Attendance Added Success', 'mjschool' );
				} else {
					$response['status']  = 0;
					$response['message'] = esc_html__( 'Attendance Not Added', 'mjschool' );
				}
			} else {
				$response['status']  = 0;
				$response['message'] = esc_html__( 'Class is not match for this student', 'mjschool' );
			}
		} else {
			$response['status']  = 0;
			$response['message'] = esc_html__( 'Please Fill All Fields', 'mjschool' );
		}
		return $response;
	}
	public function mjschool_student_attendance_view( $data ) {
		if ( ! empty( $_REQUEST['current_user'] ) && ! empty( $_REQUEST['access_token'] ) ) {
			$access_token = get_user_meta( $_REQUEST['current_user'], 'access_token', true );
			if ( $_REQUEST['access_token'] == $access_token ) {
				$response = array();
				$uid      = $data['current_user'];
				global $wpdb;
				$tbl_name    = $wpdb->prefix . 'mjschool_attendence';
				$sql         = "SELECT * FROM $tbl_name WHERE user_id=$uid";
				$tbl_holiday = $wpdb->prefix . 'mjschool_holiday';
				$holiday     = "SELECT * FROM $tbl_holiday";
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$HolidayData = $wpdb->get_results( $holiday );
				$holidaydates = array();
				$array        = array();
				foreach ( $HolidayData as $holiday ) {
					$Date1     = $holiday->date;
					$Date2     = $holiday->end_date;
					$Variable1 = strtotime( $Date1 );
					$Variable2 = strtotime( $Date2 );
					for ( $currentDate = $Variable1; $currentDate <= $Variable2;$currentDate += ( 86400 ) ) {
						$Store   = date( 'd-m-Y', $currentDate );
						$array[] = $Store;
					}
				}
				$holidaydates = array_unique( $array );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$AttData = $wpdb->get_results( $sql );
				if ( ! empty( $AttData ) ) {
					$attendancedate     = array();
					$response['status'] = 1;
					foreach ( $AttData as $key => $attendance ) {
						$attendancedate[] = date( 'd-m-Y', strtotime( $attendance->attendance_date ) );
						$status           = mjschool_get_attendace_status( $attendance->attendance_date );
						if ( $status ) {
							$status = 'Holiday';
						} else {
							$status = $attendance->status;
						}
						$result[] = array(
							'attendance_date' => date( 'd-m-Y', strtotime( $attendance->attendence_date ) ),
							'status'          => $attendance->status,
							'subject'         => null,
							'subject_id'      => null,
							'day'             => date( 'l', strtotime( $attendance->attendence_date ) ),
							'attendance_type' => $attendance->attendence_type,
						);
					}
					foreach ( $holidaydates as $holiday ) {
						if ( ! in_array( $holiday, $attendancedate ) ) {
							$result[] = array(
								'attendance_date' => $holiday,
								'status'          => 'Holiday',
								'subject'         => null,
								'subject_id'      => null,
								'day'             => date( 'l', strtotime( $holiday ) ),
							);
						}
					}
					$message['message'] = esc_html__( 'Record successfully Inserted', 'mjschool' );
					$response['result'] = $result;
				} else {
					$response['status']  = 0;
					$response['message'] = esc_html__( 'Not Record Found', 'mjschool' );
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
	public function mjschool_student_subject_attendance_view( $data ) {
		if ( ! empty( $_REQUEST['current_user'] ) && ! empty( $_REQUEST['access_token'] ) ) {
			$access_token = get_user_meta( $_REQUEST['current_user'], 'access_token', true );
			if ( $_REQUEST['access_token'] == $access_token ) {
				$response = array();
				$uid      = $data['current_user'];
				global $wpdb;
				$tbl_name    = $wpdb->prefix . 'mjschool_sub_attendance';
				$tbl_holiday = $wpdb->prefix . 'mjschool_holiday';
				$sql         = "SELECT * FROM $tbl_name WHERE user_id=$uid";
				$holiday     = "SELECT * FROM $tbl_holiday";
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$HolidayData  = $wpdb->get_results( $holiday );
				$holidaydates = array();
				if ( ! empty( $HolidayData ) ) {
					foreach ( $HolidayData as $holiday_date ) {
						$Date1     = $holiday_date->date;
						$Date2     = $holiday_date->end_date;
						$Variable1 = strtotime( $Date1 );
						$Variable2 = strtotime( $Date2 );
						for ( $currentDate = $Variable1; $currentDate <= $Variable2;$currentDate += ( 86400 ) ) {
							$Store   = date( 'd-m-Y', $currentDate );
							$array[] = $Store;
						}
					}
					$holidaydates = array_unique( $array );
				}
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$AttData = $wpdb->get_results( $sql );
				if ( ! empty( $AttData ) ) {
					$attendancedate     = array();
					$response['status'] = 1;
					foreach ( $AttData as $key => $attendance ) {
						$attendancedate[] = date( 'd-m-Y', strtotime( $attendance->attendance_date ) );
						$status           = mjschool_get_attendace_status( $attendance->attendance_date );
						if ( $status ) {
							$status = 'Holiday';
						} else {
							$status = $attendance->status;
						}
						$result[] = array(
							'attendance_date' => date( 'd-m-Y', strtotime( $attendance->attendance_date ) ),
							'status'          => $status,
							'subject'         => mjschool_get_single_subject_name( $attendance->sub_id ),
							'subject_id'      => $attendance->sub_id,
							'day'             => date( 'l', strtotime( $attendance->attendence_date ) ),
						);
					}
					foreach ( $holidaydates as $holiday ) {
						if ( ! in_array( $holiday, $attendancedate ) ) {
							$result[] = array(
								'attendance_date' => $holiday,
								'status'          => 'Holiday',
								'subject'         => null,
								'subject_id'      => null,
								'day'             => date( 'l', strtotime( $holiday ) ),
							);
						}
					}
					$message['message'] = esc_html__( 'Record successfully Inserted', 'mjschool' );
					$response['result'] = $result;
				} else {
					$response['status']  = 0;
					$response['message'] = esc_html__( 'Not Record Found', 'mjschool' );
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
	public function mjschool_attendance_save_subject( $data ) {
		$response   = array();
		$obj_attend = new Attendence_Manage();
		if ( $_REQUEST['current_user'] != 0 ) {
			$school_obj = new MJSchool_Management( $data['current_user'] );
		}
		$attendance_date = date( 'Y-m-d' );
		$attendence_type = 'QR';
		if ( $school_obj->role == 'teacher' || $school_obj->role == 'admin' ) {
			if ( $data['student_id'] != '' && $data['class_id'] != '' && $data['attendance_status'] != '' && $data['current_user'] != '' ) {
				$result = $obj_attend->mjschool_insert_subject_wise_attendance( $attendance_date, $data['class_id'], $data['student_id'], $data['current_user'], $data['attendance_status'], $data['subject_id'], '', $attendence_type, $data['section_id'] );
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
	}
	public function mjschool_attendance_save( $data ) {
		$response   = array();
		$obj_attend = new Attendence_Manage();
		if ( $_REQUEST['current_user'] != 0 ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
		}
		$attendance_type = 'web';
		$attendance_date = date( 'Y-m-d' );
		if ( $school_obj->role == 'teacher' || $school_obj->role == 'admin' ) {
			if ( $data['student_id'] != '' && $data['class_id'] != '' && $data['attendance_status'] != '' && $data['current_user'] != '' ) {
				$result = $obj_attend->mjschool_insert_student_attendance( $attendance_date, $data['class_id'], $data['student_id'], $data['current_user'], $data['attendance_status'], '', $attendance_type );
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
	}
	public function mjschool_teacher_attendance_save( $data ) {
		$response   = array();
		$obj_attend = new Attendence_Manage();
		if ( $_REQUEST['current_user'] != 0 ) {
			$school_obj = new MJSchool_Management( $_REQUEST['current_user'] );
		}
		if ( $school_obj->role == 'admin' ) {
			if ( $data['attendance_date'] != '' && $data['teacher_id'] != '' && $data['attendance_status'] != '' && $data['current_user'] != '' ) {
				$result = $obj_attend->insert_teacher_attendance( $data['attendance_date'], $data['teacher_id'], $data['current_user'], $data['attendance_status'], $data['attendance_comment'] );
				if ( $result != 0 ) {
					$message['message']   = esc_html__( 'Record successfully Inserted', 'mjschool' );
					$response['status']   = 1;
					$response['resource'] = $message;
					return $response;
				}
			} else {
				$error['message']     = esc_html__( 'Please Fill All Fields', 'mjschool' );
				$response['status']   = 0;
				$response['resource'] = $error;
			}
			return $response;
		}
	}
	public function mjschool_attendance_view_list( $data ) {
		$obj_attend    = new Attendence_Manage();
		$class_id      = $data['class_id'];
		$class_section = 0;
		if ( $data['class_id'] != '' && $data['section_id'] != '' && $data['current_user'] != '' && $data['current_user'] != 0 ) {
			
			if( isset( $data['section_id']) && $data['section_id'] !=0)
			{
				$class_section=$data['section_id'];
				$exlude_id = smgt_approve_student_list();
				$student = get_users(array( 'meta_key' => 'class_section', 'meta_value' =>$data['section_id'], 'meta_query'=> array(array( 'key' => 'class_name','value' => $class_id,'compare' => '=' ) ),'role'=>'student','exclude'=>$exlude_id ) );	
			}
			else
			{ 
				$exlude_id = smgt_approve_student_list();
				$student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id,'role'=>'student','exclude'=>$exlude_id ) );
			}
			
			$response = array();
			if ( ! empty( $student ) ) {
				$result['date']  = $data['attendance_date'];
				$result['class'] = mjschool_get_class_name( $class_id );
				if ( $class_section != '' ) {
					$section = mjschool_get_section_name( $class_section );
				} else {
					$section = esc_html__( 'No Section', 'mjschool' );
				}
				$result['section'] = $section;
				foreach ( $student as $user ) {
					$date             = $data['attendance_date'];
					$check_attendance = $obj_attend->check_attendence( $user->ID, $class_id, $date );
					$attendanc_status = '';
					if ( ! empty( $check_attendance ) ) {
						$attendanc_status = $check_attendance->status;
					} else {
						$comment = '';
						$obj_attend->mjschool_insert_student_attendance( $date, $class_id, $user->ID, $data['current_user'], 'Present', $comment );
						$check_attendance = $obj_attend->check_attendence( $user->ID, $class_id, $date );
						$attendanc_status = $check_attendance->status;
					}
					$students[] = array(
						'student_id'        => $user->ID,
						'student_name'      => $user->display_name,
						'attendance_status' => $attendanc_status,
					);
				}
				$result['students']   = $students;
				$response['status']   = 1;
				$response['resource'] = $result;
				return $response;
			}
		} else {
			$error['message']     = esc_html__( 'Please Fill All Fields', 'mjschool' );
			$response['status']   = 0;
			$response['resource'] = $error;
		}
		return $response;
	}
	public function mjschool_subject_attendance_view_list( $data ) {
		$obj_attend    = new Attendence_Manage();
		$class_id      = $data['class_id'];
		$class_section = 0;
		if ( $data['class_id'] != '' && $data['section_id'] != '' && $data['subject_id'] != '' && $data['current_user'] != '' && $data['current_user'] != 0 ) {
			
			if( isset( $data['section_id']) && $data['section_id'] !=0)
			{
				$class_section=$data['section_id'];
				$exlude_id = mjschool_approve_student_list();
				$student = get_users(array( 'meta_key' => 'class_section', 'meta_value' =>$data['section_id'], 'meta_query'=> array(array( 'key' => 'class_name','value' => $class_id,'compare' => '=' ) ),'role'=>'student','exclude'=>$exlude_id ) );	
			}
			else
			{
				$exlude_id = mjschool_approve_student_list();
				$student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id,'role'=>'student','exclude'=>$exlude_id ) );
			}
			
			$response = array();
			if ( ! empty( $student ) ) {
				$result['date']  = $data['attendance_date'];
				$result['class'] = mjschool_get_class_name( $class_id );
				if ( $class_section != '' ) {
					$section = mjschool_get_section_name( $class_section );
				} else {
					$section = esc_html__( 'No Section', 'mjschool' );
				}
				$result['section'] = $section;
				$result['subject'] = get_subject_byid( $data['subject_id'] );
				foreach ( $student as $user ) {
					$date             = $data['attendance_date'];
					$check_attendance = $obj_attend->check_sub_attendence( $user->ID, $class_id, $date, $data['subject_id'] );
					$attendanc_status = '';
					if ( ! empty( $check_attendance ) ) {
						$attendanc_status = $check_attendance->status;
					} else {
						$comment = '';
						$obj_attend->insert_subject_wise_attendance( $date, $class_id, $user->ID, $data['current_user'], 'Present', $data['subject_id'], $comment );
						$check_attendance = $obj_attend->check_sub_attendence( $user->ID, $class_id, $date, $data['subject_id'] );
						$attendanc_status = $check_attendance->status;
					}
					$students[] = array(
						'student_id'        => $user->ID,
						'student_name'      => $user->display_name,
						'attendance_status' => $attendanc_status,
					);
				}
				$result['students']   = $students;
				$response['status']   = 1;
				$response['resource'] = $result;
				return $response;
			}
		} else {
			$error['message']     = esc_html__( 'Please Fill All Fields', 'mjschool' );
			$response['status']   = 0;
			$response['resource'] = $error;
		}
		return $response;
	}
	public function mjschool_teachers_attendance_list( $data ) {
		$response   = array();
		$obj_attend = new Attendence_Manage();
		$teacher    = get_users( array( 'role' => 'teacher' ) );
		$class_id   = 0;
		if ( ! empty( $teacher ) ) {
			$result['date'] = $data['attendance_date'];
			foreach ( $teacher as $user ) {
				$date             = $data['attendance_date'];
				$check_attendance = $obj_attend->check_attendence( $user->ID, $class_id, $date );
				$attendanc_status = '';
				if ( ! empty( $check_attendance ) ) {
					$attendanc_status = $check_attendance->status;
				}
				if ( $attendanc_status == '' ) {
					$attendanc_status = 'Present';
				}
				$teachers[] = array(
					'teacher_id'        => $user->ID,
					'teacher_name'      => $user->display_name,
					'attendance_status' => $attendanc_status,
				);
			}
			$result['teachers']   = $teachers;
			$response['status']   = 1;
			$response['resource'] = $result;
		} else {
			$error['message']     = esc_html__( 'Please Fill All Fields', 'mjschool' );
			$response['status']   = 0;
			$response['resource'] = $error;
		}
		return $response;
	}
}