<?php

/**
 * Attendance Management Page.
 *
 * This file serves as the main view/controller for the Attendance module within the Mjschool
 * dashboard environment. It is responsible for:
 *
 * 1. Performing necessary browser and JavaScript checks.
 * 2. Implementing robust **role-based access control** for 'view', 'edit', and 'delete'
 * permissions for the current user/page.
 * 3. Retrieving and setting the school type from options to conditionally render fields.
 * 4. Displaying the list of attendance records, including the **attendance date, day of the week, and status**
 * (as seen in the output table structure).
 * 5. Providing navigation or instruction for when no attendance data is found.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
mjschool_browser_javascript_check();
$school_type = get_option( "mjschool_custom_class");
$mjschool_role        = mjschool_get_user_role( get_current_user_id() );
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
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
?>
<?php
if ( $active_tab === 'student_attendance' ) {
	if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
		$active_tab1 = isset( $_REQUEST['tab1'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab1'] ) ) : 'attedance_list';
	}
	if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
		
		$active_tab1 = isset( $_REQUEST['tab1'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab1'] ) ) : 'student_attedance_list';
	}
}
if ( $active_tab === 'teacher_attendance' ) {
	if ( $school_obj->role === 'supportstaff' ) {
		$active_tab1 = isset( $_REQUEST['tab1'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab1'] ) ) : 'teacher_attedance_list';
	}
	if ( $school_obj->role === 'teacher' ) {
		$active_tab1 = isset( $_REQUEST['tab1'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab1'] ) ) : 'role_teacher_attedance_list';
	}
}
$mjschool_obj_attend = new Mjschool_Attendence_Manage();
$current_date        = date( 'y-m-d' );
$class_id            = 0;
$MailCon             = get_option( 'absent_mail_notification' );
$Mailsub             = get_option( 'mjschool_absent_mail_notification_subject' );
require_once ABSPATH . 'wp-admin/includes/plugin.php';
// --------------- Save attendance. ---------------------//
if ( isset( $_REQUEST['save_attendence'] ) ) {
    $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

    if ( ! wp_verify_nonce( $nonce, 'save_attendence_front_nonce' ) ) {
        wp_die( 'Failed security check' );
    } else {

        $class_id  = isset( $_POST['class_id'] ) ? intval( wp_unslash( $_POST['class_id'] ) ) : 0;

        $curr_date = isset( $_POST['curr_date'] ) ? sanitize_text_field( wp_unslash( $_POST['curr_date'] ) ) : '';

        $attend_by = get_current_user_id();
        $exlude_id = mjschool_approve_student_list();

        $students = get_users( array(
            'meta_key'   => 'class_name',
            'meta_value' => $class_id,
            'role'       => 'student',
            'exclude'    => $exlude_id,
        ) );

        foreach ( $students as $stud ) {

            $att_key     = 'attendanace_' . $stud->ID;
            $comment_key = 'attendanace_comment_' . $stud->ID;

            if ( isset( $_POST[ $att_key ] ) ) {

                $attendance_value = sanitize_text_field( wp_unslash( $_POST[ $att_key ] ) );
                $attendance_comment = isset( $_POST[ $comment_key ] )
                    ? sanitize_textarea_field( wp_unslash( $_POST[ $comment_key ] ) )
                    : '';

                if ( isset( $_POST['mjschool_service_enable'] ) ) {

                    if ( $attendance_value === 'Absent' ) {

                        $parent_list = mjschool_get_student_parent_id( $stud->ID );

                        if ( ! empty( $parent_list ) ) {

                            foreach ( $parent_list as $user_id ) {

                                $parent_data = get_userdata( $user_id );
                                if ( ! $parent_data ) {
                                    continue;
                                }

                                $SMSCon = get_option( 'mjschool_attendance_mjschool_content' );

                                $SMSArr = array(
                                    '{{parent_name}}'  => sanitize_text_field( $parent_data->display_name ),
                                    '{{student_name}}' => mjschool_get_display_name( $stud->ID ),
                                    '{{current_date}}' => $curr_date,
                                    '{{school_name}}'  => get_option( 'mjschool_name' ),
                                );

                                $message_content = mjschool_string_replacement( $SMSArr, $SMSCon );
                                $type            = 'Attendanace';

                                mjschool_send_mjschool_notification( $user_id, $type, $message_content );
                            }
                        }
                    }
                }

                if ( $attendance_value === 'Absent' && isset( $_POST['mjschool_mail_service_enable'] ) ) {

                    $parent_list = mjschool_get_student_parent_id( $stud->ID );

                    if ( ! empty( $parent_list ) ) {

                        foreach ( $parent_list as $parent_user_id ) {

                            $parent_data = get_userdata( $parent_user_id );
                            if ( ! $parent_data ) {
                                continue;
                            }

                            $MailCon = get_option( 'mjschool_absent_mail_notification_content' );
                            $Mailsub = get_option( 'mjschool_absent_mail_subject' );

                            $MailArr = array(
                                '{{parent_name}}' => mjschool_get_display_name( $parent_user_id ),
                                '{{child_name}}'  => mjschool_get_display_name( $stud->ID ),
                                '{{school_name}}' => get_option( 'mjschool_name' ),
                            );

                            $Mail_content = mjschool_string_replacement( $MailArr, $MailCon );
                            $subject      = mjschool_string_replacement( $MailArr, $Mailsub );

                            mjschool_send_mail( sanitize_email( $parent_data->user_email ), $subject, $Mail_content );
                        }
                    }
                }

                $attendence_type = 'web';

                $savedata = $mjschool_obj_attend->mjschool_insert_student_attendance(
                    $curr_date,
                    $class_id,
                    $stud->ID,
                    $attend_by,
                    $attendance_value,
                    $attendance_comment,
                    $attendence_type
                );
            }
        }
        ?>

        <div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible margin_left_right_0" role="alert">
            <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span>
            </button>
            <?php esc_html_e( 'Attendance saved successfully.', 'mjschool' ); ?>
        </div>

        <?php
    }
}
// ------------------------ Save subject-wise attendance. ---------------------//
if ( isset( $_REQUEST['save_sub_attendence'] ) ) {
    $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

    $MailCon = get_option( 'mjschool_absent_mail_notification_content' );
    $Mailsub = get_option( 'mjschool_absent_mail_notification_subject' );

    if ( ! wp_verify_nonce( $nonce, 'save_sub_attendence_front_nonce' ) ) {
        wp_die( 'Failed security check' );
    } else {

        $class_id      = isset( $_POST['class_id'] ) ? intval( wp_unslash( $_POST['class_id'] ) ) : 0;
        $curr_date     = isset( $_POST['curr_date'] ) ? sanitize_text_field( wp_unslash( $_POST['curr_date'] ) ) : '';
        $sub_id        = isset( $_POST['sub_id'] ) ? intval( wp_unslash( $_POST['sub_id'] ) ) : 0;
        $class_section = isset( $_POST['class_section'] ) ? sanitize_text_field( wp_unslash( $_POST['class_section'] ) ) : '';

        $attend_by = get_current_user_id();
        $exlude_id = mjschool_approve_student_list();

        $students = get_users( array(
            'meta_key'   => 'class_name',
            'meta_value' => $class_id,
            'role'       => 'student',
            'exclude'    => $exlude_id
        ) );

        foreach ( $students as $stud ) {

            $att_key     = 'attendanace_' . $stud->ID;
            $comment_key = 'attendanace_comment_' . $stud->ID;

            if ( isset( $_POST[ $att_key ] ) ) {

                $attendance_value = sanitize_text_field( wp_unslash( $_POST[ $att_key ] ) );
                $attendance_comment = isset( $_POST[ $comment_key ] )
                    ? sanitize_textarea_field( wp_unslash( $_POST[ $comment_key ] ) )
                    : '';

                if ( isset( $_POST['mjschool_service_enable'] ) || isset( $_POST['mjschool_mail_service_enable'] ) ) {

                    if ( $attendance_value === 'Absent' ) {

                        $parent_list = mjschool_get_student_parent_id( $stud->ID );

                        if ( ! empty( $parent_list ) ) {

                            if ( isset( $_POST['mjschool_service_enable'] ) ) {

                                foreach ( $parent_list as $user_id ) {

                                    $message_content =
                                        'Your Child ' . mjschool_get_user_name_by_id( $stud->ID ) .
                                        ' is absent on ' . $curr_date;

                                    mjschool_send_mjschool_notification( $user_id, 'Attendance', $message_content );
                                }
                            }

                            if ( isset( $_POST['mjschool_mail_service_enable'] ) ) {

                                foreach ( $parent_list as $parent_user_id ) {

                                    $parent_data = get_userdata( $parent_user_id );
                                    if ( ! $parent_data ) {
                                        continue;
                                    }

                                    $MailArr = array(
                                        '{{parent_name}}' => mjschool_get_display_name( $parent_user_id ),
                                        '{{child_name}}'  => mjschool_get_display_name( $stud->ID ),
                                        '{{school_name}}' => get_option( 'mjschool_name' ),
                                    );

                                    $Mail_content = mjschool_string_replacement( $MailArr, $MailCon );
                                    $subject      = mjschool_string_replacement( $MailArr, $Mailsub );

                                    mjschool_send_mail(
                                        sanitize_email( $parent_data->user_email ),
                                        $subject,
                                        $Mail_content
                                    );
                                }
                            }
                        }
                    }
                }

                $mjschool_obj_attend->mjschool_insert_subject_wise_attendance(
                    $curr_date,
                    $class_id,
                    $stud->ID,
                    $attend_by,
                    $attendance_value,
                    $sub_id,
                    $attendance_comment,
                    'Web',
                    $class_section
                );
            }
        }
    }

    // Redirect safely
    $new_nonce = wp_create_nonce( 'mjschool_student_attendance_tab' );
    wp_safe_redirect(home_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance&_wpnonce=' .esc_attr( $new_nonce ) .'&message=1'));
    die();
}
/* Export studant attendance. */
if ( isset( $_POST['export_attendance_in_csv'] ) ) {
	if ( empty( $_POST['filtered_date_type'] ) ) {
		if ( $school_obj->role === 'teacher' ) {
			$date_type               = '';
			$class_id                = sanitize_text_field( wp_unslash( $_POST['filtered_class_id'] ) );
			$student_attendance_list = mjschool_student_attendance_by_class_id( $start_date, $end_date, $class_id, $date_type );
		} else {
			$date_type               = '';
			$class_id                = '';
			$start_date              = date( 'Y-m-d', strtotime( 'first day of this month' ) );
			$end_date                = date( 'Y-m-d', strtotime( 'last day of this month' ) );
			$student_attendance_list = mjschool_get_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $class_id, $date_type );
		}
	} else {
		$date_type               = sanitize_text_field( wp_unslash( $_POST['filtered_date_type'] ) );
		$class_id                = sanitize_text_field( wp_unslash( $_REQUEST['filtered_class_id'] ) );
		$student_attendance_list = mjschool_get_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $class_id, $date_type );
	}
	if ( ! empty( $student_attendance_list ) ) {
		$header   = array();
		$header[] = 'Roll No';
		$header[] = 'Student Name';
		$header[] = 'User_id';
		$header[] = 'Class_name';
		$header[] = 'Class_id';
		$header[] = 'Attend_by_name';
		$header[] = 'Attend_by';
		$header[] = 'Attendence_date';
		$header[] = 'Status';
		$header[] = 'Role_name';
		$header[] = 'Comment';
		$filename = 'export/mjschool-export-attendance.csv';
		$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
		fputcsv( $fh, $header );
		foreach ( $student_attendance_list as $retrive_data ) {
			if ( $retrive_data->role_name === 'student' ) {
				$row       = array();
				$user_info = get_userdata( $retrive_data->user_id );
				$roll_no   = get_user_meta( $retrive_data->user_id, 'roll_id', true );
				if ( ! empty( $roll_no ) ) {
					$roll_no = $roll_no;
				} else {
					$roll_no = '-';
				}
				$row[]     = $roll_no;
				$row[]     = $user_info->display_name;
				$row[]     = $retrive_data->user_id;
				$class_id  = $retrive_data->class_id;
				$classname = mjschool_get_class_name( $class_id );
				if ( ! empty( $classname ) ) {
					$classname = $classname;
				} else {
					$classname = '-';
				}
				$row[]     = $classname;
				$row[]     = $retrive_data->class_id;
				$attend_by = get_userdata( $retrive_data->attend_by );
				$row[]     = $attend_by->display_name;
				$row[]     = $retrive_data->attend_by;
				$row[]     = $retrive_data->attendence_date;
				$row[]     = $retrive_data->status;
				$row[]     = $retrive_data->role_name;
				$row[]     = $retrive_data->comment;
				fputcsv( $fh, $row );
			}
		}
		fclose( $fh );
		// Download csv file.
		ob_clean();
		$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-attendance.csv';// File location.
		$mime = 'text/plain';
		header( 'Content-Type:application/force-download' );
		header( 'Pragma: public' );       // Required.
		header( 'Expires: 0' );           // No cache.
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Last-Modified: ' . date( 'D, d M Y H:i:s', filemtime( $file ) ) . ' GMT' );
		header( 'Cache-Control: private', false );
		header( 'Content-Type: ' . $mime );
		header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Connection: close' );
		readfile( $file );
		die();
	}
}
/* Save teacher attendance. */
if ( isset( $_REQUEST['save_teach_attendence'] ) ) {

    if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_attendance_teacher_add_nonce' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
    }

    $attend_by = get_current_user_id();

    // Get all teachers
    $teachers = get_users( array( 'role' => 'teacher' ) );

    foreach ( $teachers as $stud ) {

        // Check if attendance checkbox exists for this teacher
        if ( isset( $_POST[ 'attendanace_' . $stud->ID ] ) ) {

            $attendance_status = sanitize_text_field( wp_unslash( $_POST[ 'attendanace_' . $stud->ID ] ) );
            $attendance_comment = sanitize_text_field( wp_unslash( $_POST[ 'attendanace_comment_' . $stud->ID ] ) );
            $attendance_date = sanitize_text_field( wp_unslash( $_POST['tcurr_date'] ) );

            $savedata = $mjschool_obj_attend->mjschool_insert_teacher_attendance(
                $attendance_date,
                $stud->ID,
                $attend_by,
                $attendance_status,
                $attendance_comment
            );
        }
    }

    // Redirect safely with nonce
    $nonce = wp_create_nonce( 'mjschool_teacher_attendance_tab' );

    wp_safe_redirect(
        add_query_arg(
            array(
                'dashboard' => 'mjschool_user',
                'page'      => 'attendance',
                'tab'       => 'teacher_attendance',
                '_wpnonce'  => $nonce,
                'message'   => 1,
            ),
            home_url()
        )
    );
    exit;
}
?>
<div class="mjschool-panel-body mjschool-panel-white attendance_list mjschool-frontend-list-margin-30px-res">
	<!-------------- Panel body. ----------------->
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Attendance saved successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Record Deleted Successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible  margin_left_right_0" role="alert">
			
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
			</button>
			
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	?>
	<!--------------- Tabing start. ------------------->
	
	<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per mb-4" role="tablist">
		<?php
		if ( $active_tab === 'student_attendance' ) {
			$nonce = wp_create_nonce( 'mjschool_student_attendance_tab' );
			if ( $school_obj->role === 'student' || $school_obj->role === 'parent' ) {
				?>
				<li class="<?php if ( $active_tab1 === 'student_attedance_list' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance&tab1=student_attedance_list&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'student_attedance_list' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Attendance List', 'mjschool' ); ?> </a>
				</li>
				<?php
			}
			if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
				?>
				<li class="<?php if ( $active_tab1 === 'attedance_list' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance&tab1=attedance_list&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'attedance_list' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Student Attendance List', 'mjschool' ); ?> </a>
				</li>
				<li class="<?php if ( $active_tab1 === 'subject_attendence' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance&tab1=subject_attendence&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'subject_attendence' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Attendance', 'mjschool' ); ?></a>
				</li>
				<li class="<?php if ( $active_tab1 === 'attendence_with_qr' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance&tab1=attendence_with_qr&_wpnonce=' . esc_attr( $nonce ) ); ?>"
 class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'attendence_with_qr' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Attendance With QR Code', 'mjschool' ); ?></a>
				</li>
				<?php
			}
		}
		if ( $active_tab === 'teacher_attendance' ) {
			$nonce = wp_create_nonce( 'mjschool_teacher_attendance_tab' );
			if ( $school_obj->role === 'teacher' ) {
				?>
				<li class="<?php if ( $active_tab1 === 'role_teacher_attedance_list' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=attendance&tab=teacher_attendance&tab1=role_teacher_attedance_list&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'role_teacher_attedance_list' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Attendance List', 'mjschool' ); ?></a>
				</li>
				<?php
			}
			if ( $school_obj->role === 'supportstaff' ) {
				?>
				<li class="<?php if ( $active_tab1 === 'teacher_attedance_list' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=attendance&tab=teacher_attendance&tab1=teacher_attedance_list&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'teacher_attedance_list' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Teacher Attendance List', 'mjschool' ); ?></a>
				</li>
				<li class="<?php if ( $active_tab1 === 'teacher_attendences' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=attendance&tab=teacher_attendance&tab1=teacher_attendences&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'teacher_attendences' ? 'nav-tab-active' : ''; ?>"> <?php echo esc_html__( 'Teacher Attendance', 'mjschool' ); ?></a>
				</li>
				<?php
			}
		}
		?>
	</ul>
	<!--------------- Tabing end. ------------------->
	<?php
	if ( $active_tab1 === 'student_attedance_list' ) {

		// Check nonce for student attendence list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_student_attendance_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		$user_id = get_current_user_id();
		// Attendance for student.
		if ( $mjschool_role === 'student' ) {
			$attendance_list = mjschool_monthly_attendence( $user_id );
		}
		// Attendance For Parent.
		elseif ( $mjschool_role === 'parent' ) {
			$attendance_list = mjschool_monthly_attendence_for_parent( $user_id );
		}
		if ( ! empty( $attendance_list ) ) {
			?>
			<div class="table-div"><!-- Start panel body div. -->
				<div class="table-responsive"><!-- Table responsive div start. -->
					<table id="mjschool-attendance-list-detail-page" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date', 'mjschool' ); ?> </th>
								<th><?php esc_html_e( 'Day', 'mjschool' ); ?> </th>
								<th><?php esc_html_e( 'Attendance Status', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance With QR Code', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i    = 0;
							$srno = 1;
							if ( ! empty( $attendance_list ) ) {
								foreach ( $attendance_list as $retrieved_data ) {
									$class_section_sub_name = mjschool_get_class_section_subject( $retrieved_data->class_id, $retrieved_data->section_id, $retrieved_data->sub_id );
									$created_by             = get_userdata( $retrieved_data->attend_by );
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
											<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr($color_class_css); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>" class="mjschool-massage-image attendace_module_image ">
											</p>
										</td>
										<td class="department">
											<?php echo esc_html( mjschool_student_display_name_with_roll($retrieved_data->user_id ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name & Roll No.', 'mjschool' ); ?>"></i>
										</td>
										<td >
											<?php echo wp_kses_post($class_section_sub_name); ?> 
											<i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i> 
										</td>
										<?php $curremt_date = mjschool_get_date_in_input_box($retrieved_data->attendance_date);
										$day = date( "D", strtotime($curremt_date ) ); ?>
										<td class="name">
											<?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->attendance_date ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance Date', 'mjschool' ); ?>"></i>
										</td>
										<td class="department">
											<?php
											if ($day === 'Mon' ) {
												esc_html_e( 'Monday', 'mjschool' );
											} elseif ($day === 'Sun' ) {
												esc_html_e( 'Sunday', 'mjschool' );
											} elseif ($day === 'Tue' ) {
												esc_html_e( 'Tuesday', 'mjschool' );
											} elseif ($day === 'Wed' ) {
												esc_html_e( 'Wednesday', 'mjschool' );
											} elseif ($day === 'Thu' ) {
												esc_html_e( 'Thursday', 'mjschool' );
											} elseif ($day === 'Fri' ) {
												esc_html_e( 'Friday', 'mjschool' );
											} elseif ($day === 'Sat' ) {
												esc_html_e( 'Saturday', 'mjschool' );
											}
											?> 
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
										</td>
										<td class="name">
											<?php $status_color = mjschool_attendance_status_color($retrieved_data->status); ?>
											<span style="color:<?php echo esc_attr($status_color); ?>;"><?php echo esc_html( $retrieved_data->status); ?></span>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance Status', 'mjschool' ); ?>"></i>
										</td>
										<td class="name">
											<?php echo esc_html( $created_by->display_name); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
										</td>
										<td class="mjschool-width-20px">
											<?php
											if ($retrieved_data->attendence_type === 'QR' ) {
												echo esc_html__( "Yes", "mjschool" );
											} else {
												echo esc_html__( "No", "mjschool" );
											}
											?> 
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance With QR Code', 'mjschool' ); ?>"></i>
										</td>
										<td class="name">
											<?php
											if ( ! empty( $retrieved_data->comment ) ) {
												$comment = $retrieved_data->comment;
												$grade_comment = strlen($comment) > 30 ? substr( $comment, 0, 30) . "..." : $comment;
												echo esc_html( $grade_comment);
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->comment ) ) { echo esc_html( $retrieved_data->comment); } else { esc_html_e( 'Comment', 'mjschool' ); } ?>"></i>
										</td>
									</tr>
									<?php
									$i++;
									$srno++;
								}
							}
							?>
						</tbody>
					</table>
				</div><!-- Table responsive div end. -->
			</div>
			<?php
		} else {
			$page_1 = 'attendance';
			$fattendance_1 = mjschool_get_user_role_wise_filter_access_right_array($page_1);
			if ($mjschool_role === 'administrator' || $fattendance_1['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div">
					<a href="<?php echo esc_url( admin_url() . 'admin.php?page=mjschool_attendence' ); ?>">
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
	}
	if ( $active_tab1 === 'role_teacher_attedance_list' ) {

		// Check nonce for teacher attendence list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_teacher_attendance_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		$teacher_id      = get_current_user_id();
		$attendance_list = mjschool_monthly_attendence_teacher( $teacher_id );
		if ( ! empty( $attendance_list ) ) {
			?>
			<div class="table-div"><!-- Start panel body div. -->
				<div class="table-responsive"><!-- Table responsive div start. -->
					<table id="mjschool-attendance-list-detail-page-teacher" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Day', 'mjschool' ); ?> </th>
								<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mjschool' ); ?> </th>
								<th><?php esc_html_e( 'Comment', 'mjschool' ); ?> </th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i    = 0;
							$srno = 1;
							if ( ! empty( $attendance_list ) ) {
								foreach ( $attendance_list as $retrieved_data ) {
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
											<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr($color_class_css); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>" class="mjschool-massage-image attendace_module_image ">
											</p>
										</td>
										<td >
											<?php echo esc_html( mjschool_get_user_name_by_id( $retrieved_data->user_id ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i>
										</td>
										<td class="name">
											<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendence_date ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance Date', 'mjschool' ); ?>"></i>
										</td>
										<td >
											<?php
											$curremt_date = $retrieved_data->attendence_date;
											$day          = date( 'D', strtotime( $curremt_date ) );
											if ( $day === 'Mon' ) {
												esc_html_e( 'Monday', 'mjschool' );
											} elseif ( $day === 'Sun' ) {
												esc_html_e( 'Sunday', 'mjschool' );
											} elseif ( $day === 'Tue' ) {
												esc_html_e( 'Tuesday', 'mjschool' );
											} elseif ( $day === 'Wed' ) {
												esc_html_e( 'Wednesday', 'mjschool' );
											} elseif ( $day === 'Thu' ) {
												esc_html_e( 'Thursday', 'mjschool' );
											} elseif ( $day === 'Fri' ) {
												esc_html_e( 'Friday', 'mjschool' );
											} elseif ( $day === 'Sat' ) {
												esc_html_e( 'Saturday', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
										</td>
										<td class="name">
											<?php echo esc_html( mjschool_get_display_name( $retrieved_data->attend_by ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php echo esc_html( $retrieved_data->status ); ?> 
											<i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
										</td>
										<td class="name">
											<?php
											if ( ! empty( $retrieved_data->comment ) ) {
												$comment       = $retrieved_data->comment;
												$grade_comment = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
												echo esc_html( $grade_comment );
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->comment ) ) { echo esc_html( $retrieved_data->comment ); } else { esc_html_e( 'Comment', 'mjschool' ); } ?>"></i>
										</td>
									</tr>
									<?php
									++$i;
									++$srno;
								}
							}
							?>
						</tbody>
					</table>
				</div><!-- Table responsive div end. -->
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
	if ( $active_tab1 === 'attedance_list' ) {
		// Check nonce for student attendence list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_student_attendance_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		?>
		<form method="post" id="attendance_list" class="attendance_list">
			<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_attendance_list_nonce' ) ); ?>">
			<div class="form-body mjschool-user-form mjschool-margin-top-15px">
				<div class="row">
					<div class="col-md-3 mb-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php $date_type = isset( $_POST['date_type'] ) ? sanitize_text_field( wp_unslash( $_POST['date_type'] ) ) : ''; ?>
						<select class="mjschool-line-height-30px form-control date_type validate[required]" id="date_type" name="date_type" autocomplete="off">
							<option value="today" <?php selected( $date_type, 'today' ); ?>><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
							<option value="this_week" <?php selected( $date_type, 'this_week' ); ?>><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
							<option value="last_week" <?php selected( $date_type, 'last_week' ); ?>><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
							<option value="this_month" <?php selected( $date_type, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
							<option value="last_month" <?php selected( $date_type, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
							<option value="last_3_month" <?php selected( $date_type, 'last_3_month' ); ?>><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
							<option value="last_6_month" <?php selected( $date_type, 'last_6_month' ); ?>><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
							<option value="last_12_month" <?php selected( $date_type, 'last_12_month' ); ?>><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
							<option value="this_year" <?php selected( $date_type, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
							<option value="last_year" <?php selected( $date_type, 'last_year' ); ?>><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
							<option value="period" <?php selected( $date_type, 'period' ); ?>><?php esc_html_e( 'Period', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-attendance-class-list-id"><?php esc_html_e( 'Select Class', 'mjschool' ); ?></label>
						<?php
						if ( isset( $_REQUEST['class_id'] ) ) {
							$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
						} else {
							$classval = '';
						}
						?>
						<select name="class_id" id="mjschool-attendance-class-list-id" class="form-control user_select mjschool-max-width-100px">
							<?php
							if ( $school_obj->role === 'supportstaff' ) {
								?>
								<option value="all class"><?php esc_html_e( 'All Class', 'mjschool' ); ?></option>
								<?php
							}
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div id="date_type_div" class="col-md-6 <?php echo ( $date_type === 'period' ) ? '' : 'date_type_div_none'; ?>">
						<?php
						if ( $date_type === 'period' ) {
							?>
							<div class="row">
								<div class="col-md-6 mb-2">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text" id="report_sdate" class="form-control" name="start_date" value="<?php echo isset( $_POST['start_date'] ) ? esc_attr( wp_unslash( $_POST['start_date'] ) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
											<label for="report_sdate" class="active"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-6 mb-2">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text" id="report_edate" class="form-control" name="end_date" value="<?php echo isset( $_POST['end_date'] ) ? esc_attr( wp_unslash($_POST['end_date'] ) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
											<label for="report_edate" class="active"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col-md-3 mb-2">
						<input type="submit" name="view_attendance" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
		<div class="clearfix"></div>
		<?php
		if ( isset( $_REQUEST['view_attendance'] ) ) {
			if (! isset($_POST['security']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_attendance_list_nonce')) {
				wp_die(esc_html__('Security check failed.', 'mjschool'));
			}
			$date_type       = sanitize_text_field( wp_unslash( $_REQUEST['date_type'] ) );
			$class_id        = sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) );
			$attendence_data = mjschool_get_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $class_id, $date_type );
		} else {
			$date_type = '';
			if ( $school_obj->role === 'teacher' ) {
				$teacher_id      = get_current_user_id();
				$cla_id          = mjschool_get_class_by_teacher_id( $teacher_id );
				$class_id        = $cla_id[0]->class_id;
				$attendence_data = mjschool_student_attendance_by_class_id( $start_date, $end_date, $class_id, $date_type );
			} else {
				$start_date      = date( 'Y-m-d', strtotime( 'first day of this month' ) );
				$end_date        = date( 'Y-m-d', strtotime( 'last day of this month' ) );
				$attendence_data = mjschool_get_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $class_id, $date_type );
			}
		}
		if ( $start_date > $end_date ) {
			echo '<script type="text/javascript">alert( "' . esc_html__( 'End Date should be greater than the Start Date', 'mjschool' ) . '");</script>';
		}
		if ( ! empty( $attendence_data ) ) {
			?>
			<?php
			if ( isset( $_REQUEST['delete_selected_attendance'] ) ) {
				if ( ! empty( $_REQUEST['id'] ) ) {
					foreach ( $_REQUEST['id'] as $id ) {
						$result = mjschool_delete_attendance( intval( sanitize_text_field( wp_unslash( $id ) ) ) );
					}
				}
				if ( $result ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance&message=2' ) );
					die();
				}
			}
			?>
			<div class="table-div"><!-- Start panel body div. -->
				<div class="table-responsive"><!-- Table responsive div start. -->
					<div class="btn-place"></div>
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<table id="front_student_attendance_list" class="display" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
									<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Attendance Status', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Attendance With QR', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ( $attendence_data as $retrieved_data ) {
									if ( isset( $retrieved_data->class_id ) && $retrieved_data->class_id ) {
										$class_section_sub_name = mjschool_get_class_section_subject( $retrieved_data->class_id, $retrieved_data->section_id, $retrieved_data->sub_id );
										$member_data            = get_userdata( $retrieved_data->user_id );
										$created_by             = get_userdata( $retrieved_data->attend_by );
										if ( ! empty( $member_data->parent_id ) ) {
											$parent_data = get_userdata( $member_data->parent_id );
										}
										?>
										<tr>
											<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->attendance_id ); ?>"></td>
											<td class="mjschool-user-image mjschool-width-50px-td">
												<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . esc_attr( $member_data->ID ) ); ?>">
													<?php
													$umetadata = mjschool_get_user_image($member_data->ID);
													if (empty($umetadata ) ) {
														echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
													} else {
														echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
													}
													?>
												</a>
											</td>
											<td class="name">
												<?php
												if ( $member_data->roles[0] === 'student' ) {
													echo esc_html( $member_data->display_name );
												} else {
													echo esc_html( $member_data->display_name );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
											</td>
											<td class="name">
												<?php echo wp_kses_post( $class_section_sub_name ); ?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
											</td>
											<td class="name">
												<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendance_date ) ); ?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i>
											</td>
											<td class="name">
												<?php
												$day = date( 'l', strtotime( $retrieved_data->attendance_date ) );
												echo esc_html( $day );
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
											</td>
											<td class="name">
												<?php echo esc_html( $created_by->display_name ); ?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
											</td>
											<td class="name">
												<?php $status_color = mjschool_attendance_status_color( $retrieved_data->status ); ?>
												<span style="color:<?php echo esc_attr( $status_color ); ?>;"> <?php echo esc_html( $retrieved_data->status ); ?> </span>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance Status', 'mjschool' ); ?>"></i>
											</td>
											<td class="name">
												<?php
												if ( $retrieved_data->attendence_type === 'QR' ) {
													esc_html_e( 'Yes', 'mjschool' );
												} else {
													esc_html_e( 'No', 'mjschool' );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance With QR', 'mjschool' ); ?>"></i>
											</td>
											<td class="name">
												<?php
												$comment = $retrieved_data->comment;
												if ( ! empty( $comment ) ) {
													$comment_out = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
													echo esc_html( $comment_out );
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php echo esc_html( $retrieved_data->comment ); ?>"></i>
											</td>
										</tr>
										<?php
									}
									++$i;
								}
								?>
							</tbody>
						</table>
						<div class="mjschool-print-button pull-left">
							<button class="mjschool-btn-sms-color mjschool-button-reload">
								<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
								<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
							</button>
							
							<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_attendance" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
							<input type="hidden" name="filtered_date_type" value="<?php echo esc_attr($date_type); ?>" />
							<input type="hidden" name="filtered_class_id" value="<?php echo esc_attr($class_id); ?>" />
							<button data-toggle="tooltip" title="<?php esc_attr_e( 'Export Attendance', 'mjschool' ); ?>" name="export_attendance_in_csv" class="mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-export-csv.png"); ?>"></button>
						</div>
					</form>
				</div><!-- Table responsive div end. -->
			</div>
			<?php
		} else {
			$page_1 = 'attendance';
			$fattendance_1 = mjschool_get_user_role_wise_filter_access_right_array($page_1);
			if ($fattendance_1['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=attendance&tab=student_attendance&tab1=subject_attendence') ); ?>">
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
	}
	if ( $active_tab1 === 'attendence' ) {
		?>
		<div class="mjschool-panel-body"><!------------ Panel body. ------------->
			<!-------------- Student attendance form. -------------------->
			<form method="post" id="student_attendance">
				<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="curr_date_sub" class="form-control" type="text" value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_html( mjschool_get_date_in_input_box( wp_unslash($_POST['curr_date'] ) ) ); } else { echo esc_html( date( 'Y-m-d' ) ); } ?>" name="curr_date" readonly>
									<label class="control-label" for="curr_date_sub"><?php esc_html_e( 'Date', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-3 mb-3 input">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<?php
							if ( isset( $_REQUEST['class_id'] ) ) {
								$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
							}
							?>
							<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required]">
								<option value=""><?php esc_html_e( 'Select class', 'mjschool' ); ?></option>
								<?php
								foreach ( mjschool_get_all_class() as $classdata ) {
									?>
									<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>> <?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php
								}
								?>
							</select>
						</div>
						<?php if ( $school_type === 'school' ) { ?>
							<div class="col-md-3 mb-3 input">
								<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Class Section', 'mjschool' ); ?></label>
								<?php
								$class_section = '';
								if ( isset( $_REQUEST['class_section'] ) ) {
									$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
								}
								?>
								<select name="class_section" class="mjschool-line-height-30px form-control mjschool-class-section-subject" id="class_section">
									<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
									<?php
									if ( isset( $_REQUEST['class_section'] ) ) {
										$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
										foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
											?>
											<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>> <?php echo esc_html( $sectiondata->section_name ); ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
						<?php }?>
						<div class="col-md-3 mb-3">
							<input type="submit" value="<?php esc_attr_e( 'Take Attendance', 'mjschool' ); ?>" name="attendence" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form><!-------------- Student attendance form. -------------------->
			<div class="clearfix"></div>
			<?php
			if ( isset( $_REQUEST['attendence'] ) || isset( $_REQUEST['save_attendence'] ) ) {
				$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
				
				$mjschool_user = count(get_users(array(
					'meta_key' => 'class_name',
					'meta_value' => $class_id
				 ) ) );
				$attendanace_date = sanitize_text_field( wp_unslash( $_REQUEST['curr_date'] ) );
				$holiday_dates = mjschool_get_all_date_of_holidays();
				if (in_array($attendanace_date, $holiday_dates ) ) {
					?>
					<div id="mjschool-message" class="mjschool-message_class  alert alert-warning alert-dismissible mjschool-alert-attendence" role="alert">
						<button type="button" class="btn-default notice-dismiss " data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
						<?php esc_html_e( 'This day is holiday you are not able to take attendance', 'mjschool' ); ?>
					</div>
					<?php
					 
				} elseif ( 0 < $mjschool_user ) {
					if ( isset( $_REQUEST['class_id'] ) && $_REQUEST['class_id'] != ' ' ) {
						$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
					} else {
						$class_id = 0;
					}
					if ( $class_id === 0 ) {
						?>
						<div class="mjschool-panel-heading">
							<h4 class="mjschool-panel-title"><?php esc_html_e( 'Please Select Class', 'mjschool' ); ?></h4>
						</div>
						<?php
					} else {
						         
						if ( isset( $_REQUEST['class_section']) && $_REQUEST['class_section'] != "") {
							$exlude_id = mjschool_approve_student_list();
							$student = get_users(array(
								'meta_key' => 'class_section',
								'meta_value' => sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ),
								'meta_query' => array(array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ),
								'role' => 'student',
								'exclude' => $exlude_id,
								'orderby' => 'display_name',
								'order' => 'ASC'
							 ) );
						} else {
							$exlude_id = mjschool_approve_student_list();
							$student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id, 'orderby' => 'display_name', 'order' => 'ASC' ) );
						}
						
						?>
						<form method="post" class="mjschool-form-horizontal">
							<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
							<input type="hidden" name="class_section" value="<?php echo $class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ); ?>" />
							<input type="hidden" name="curr_date" value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( wp_unslash( $_POST['curr_date'] ) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" />
							<div class="mjschool-panel-heading">
								<h4 class="mjschool-panel-title"> <?php esc_html_e( 'Class', 'mjschool' ); ?> :
									<?php echo esc_html( mjschool_get_class_name( $class_id ) ); ?> ,
									<?php esc_html_e( 'Date', 'mjschool' ); ?> :
									<?php echo esc_html( mjschool_get_date_in_input_box( wp_unslash($_POST['curr_date'] ) ) ); ?>
								</h4>
							</div>
							<div class="col-md-12">
								<div class="table-responsive">
									<table class="table">
										<tr>
											<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Sr. No.', 'mjschool' ); ?></th>
											<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
											<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Student', 'mjschool' ); ?></th>
											<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
											<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
										</tr>
										<?php
										$date = sanitize_text_field( wp_unslash( $_POST['curr_date'] ) );
										$i    = 1;
										foreach ( $student as $mjschool_user ) {
											$date             = sanitize_text_field( wp_unslash( $_POST['curr_date'] ) );
											$check_attendance = $mjschool_obj_attend->mjschool_check_attendence( $mjschool_user->ID, $class_id, $date );
											$attendanc_status = 'Present';
											if ( ! empty( $check_attendance ) ) {
												$attendanc_status = $check_attendance->status;
											}
											echo '<tr>';
											echo '<td>' . esc_html( $i ) . '</td>';
											echo '<td><span>' . esc_html( get_user_meta( $mjschool_user->ID, 'roll_id', true ) ) . '</span></td>';
											echo '<td><span>' . esc_html( $mjschool_user->first_name ) . ' ' . esc_html( $mjschool_user->last_name ) . '</span></td>';
											?>
											<td>
												<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Present" <?php checked( $attendanc_status, 'Present' ); ?>><?php esc_html_e( 'Present', 'mjschool' ); ?></label>
												<label class="radio-inline"> <input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Absent" <?php checked( $attendanc_status, 'Absent' ); ?>><?php esc_html_e( 'Absent', 'mjschool' ); ?></label>
												<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Late" <?php checked( $attendanc_status, 'Late' ); ?>><?php esc_html_e( 'Late', 'mjschool' ); ?></label>
												<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Half Day" <?php checked( $attendanc_status, 'Half Day' ); ?>><?php esc_html_e( 'Half Day', 'mjschool' ); ?></label>
											</td>
											<td class="padding_left_right_0">
												<div class="form-group input mjschool-margin-bottom-0px">
													<div class="col-md-12 form-control">
														<input type="text" name="attendanace_comment_<?php echo esc_attr( $mjschool_user->ID ); ?>" class="form-control" value="<?php if ( ! empty( $check_attendance ) ) { echo esc_attr( $check_attendance->comment );} ?>">
													</div>
												</div>
											</td>
											<?php
											echo '</tr>';
											++$i;
										}
										?>
									</table>
								</div>
								<div class="d-flex mt-2">
									<div class="form-group row mb-3">
										<span class="col-sm-8 control-label" for="enable"> <?php esc_html_e( 'If student absent then Send Mail', 'mjschool' ); ?></span>
										<div class="col-sm-2 ps-0">
											<div class="mjschool-checkbox">
												<label>
													<input class="mjschool-check-box" id="smgt_mail_service_enable" type="checkbox" <?php $smgt_mail_service_enable = 0; if ( $smgt_mail_service_enable ) { echo 'checked'; } ?> value="1" name="smgt_mail_service_enable">
												</label>
											</div>
										</div>
									</div>
									<div class="form-group row mb-3">
										<label class="col-sm-10 control-label col-form-label" for="enable"><?php esc_html_e( 'If student absent then Send  SMS to his/her parents', 'mjschool' ); ?></label>
										<div class="col-sm-2 pt-2 ps-0">
											<div class="mjschool-checkbox">
												<label>
													<input id="chk_mjschool_sent1" type="checkbox" <?php $mjschool_service_enable = 0; if ( $mjschool_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_service_enable">
												</label>
											</div>
										</div>
									</div>
								</div>
								<?php wp_nonce_field( 'save_attendence_front_nonce' ); ?>
								<?php
								if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
									?>
									<div class="col-sm-6 mjschool-rtl-res-att-save">
										<input type="submit" value="<?php esc_attr_e( 'Save  Attendance', 'mjschool' ); ?>" name="save_attendence" class="mjschool-save-btn btn btn-success" />
									</div>
									<?php
								}
								?>
							</div>
						</form>
						<?php
					}
				} else {
					?>
					<div class="smgt_no_attence_css">
						<h4 class="mjschool_font_weight_24px" > <?php esc_html_e( 'No Any Student In This Class', 'mjschool' ); ?></h4>
					</div>
					<?php
				}
			}
			?>
		</div><!------------ Panel body. ------------->
		<?php
	}
	if ( $active_tab1 === 'subject_attendence' ) {
		// Check nonce for student attendence list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_student_attendance_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		?>
		<div class="mjschool-panel-body"><!-------------- Panel body. --------------->
			<!---------------- Subject-wise attendance form. -------------->
			<form method="post" id="subject_attendance">
				<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_take_attendance_nonce' ) ); ?>">
				<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="curr_date_sub" class="form-control" type="text" value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field( wp_unslash( $_POST['curr_date'] ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="curr_date" readonly>
									<label  for="curr_date_sub"><?php esc_html_e( 'Date', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<?php
							if ( isset( $_REQUEST['class_id'] ) ) {
								$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
							}
							?>
							<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required]">
								<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
								<?php
								foreach ( mjschool_get_all_class() as $classdata ) {
									?>
									<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>> <?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php
								}
								?>
							</select>
						</div>
						<?php if ( $school_type === 'school' ) {?>
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
								<?php
								$class_section = '';
								if ( isset( $_REQUEST['class_section'] ) ) {
									$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
								}
								?>
								<select name="class_section" class="mjschool-line-height-30px form-control mjschool-class-section-subject" id="class_section">
									<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
									<?php
									if ( isset( $_REQUEST['class_section'] ) ) {
										$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
										foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
											?>
											<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>> <?php echo esc_html( $sectiondata->section_name ); ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
						<?php }?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-subject-list"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field"></span></label>
							<select name="sub_id" id="mjschool-subject-list" class="mjschool-line-height-30px form-control">
								<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
								<?php
								$sub_id = 0;
								if ( isset( $_POST['sub_id'] ) ) {
									$sub_id = intval( wp_unslash( $_REQUEST['sub_id'] ) );
									$allsubjects = mjschool_get_subject_by_class_id( $_POST['class_id'] );
									foreach ( $allsubjects as $subjectdata ) {
										?>
										<option value="<?php echo esc_attr( $subjectdata->subid ); ?>" <?php selected( $subjectdata->subid, $sub_id ); ?>> <?php echo esc_html( $subjectdata->sub_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					</div>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6">
							<input type="submit" value="<?php esc_attr_e( 'Take Attendance', 'mjschool' ); ?>" name="attendence" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form><!---------------- Subject-wise attendance form. -------------->
		</div><!-------------- Panel form. --------------->
		<div class="clearfix"> </div>
		<?php
		if ( isset( $_REQUEST['attendence'] ) ) {
			if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_take_attendance_nonce')) {
				wp_die(esc_html__('Security check failed.', 'mjschool'));
			}
			$attendanace_date = sanitize_text_field( wp_unslash( $_REQUEST['curr_date'] ) );
			$holiday_dates    = mjschool_get_all_date_of_holidays();
			if ( in_array( $attendanace_date, $holiday_dates ) ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class  alert alert-warning alert-dismissible mjschool-alert-attendence" role="alert">
					<button type="button" class="btn-default notice-dismiss " data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
					<?php esc_html_e( 'This day is holiday you are not able to take attendance', 'mjschool' ); ?>
				</div>
				<?php
			} else {
				if ( isset( $_REQUEST['class_id'] ) && sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) != ' ' ) {
					$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
				} else {
					$class_id = 0;
				}
				if ( $class_id === 0 ) {
					?>
					<div class="mjschool-panel-heading">
						<h4 class="mjschool-panel-title"> <?php esc_html_e( 'Please Select Class', 'mjschool' ); ?> </h4>
					</div>
					<?php
				} else {
					       
					if ( isset( $_REQUEST['class_section']) && sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ) != "") {
						$exlude_id = mjschool_approve_student_list();
						$student = get_users(array(
							'meta_key' => 'class_section',
							'meta_value' => sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ),
							'meta_query' => array(array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ),
							'role' => 'student',
							'exclude' => $exlude_id
						 ) );
						sort($student);
					} else {
						if ( $school_type === 'university' )
						{
							if ( isset( $_REQUEST['sub_id']) && !empty($_REQUEST['sub_id'] ) ) {
								$student = mjschool_get_students_assigned_to_subject( sanitize_text_field( wp_unslash( $_REQUEST['sub_id'] ) ) );
							} else {
								$student = array(); // Fallback if no subject selected.
							}
						}else{
							$exlude_id = mjschool_approve_student_list();
							$student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
							sort($student);
						}
					}
					
					if ( $student ) {
						?>
						<div class="mjschool-panel-body">
							<form method="post" class="mjschool-form-horizontal mt-4 mt-4">
								<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
								<input type="hidden" name="sub_id" value="<?php echo esc_attr( $sub_id ); ?>" />
								<input type="hidden" name="class_section" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_POST['class_section'] ) ) ); ?>" />
								<input type="hidden" name="curr_date" value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field( wp_unslash( $_POST['curr_date'] ) ) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" />
								<div class="mjschool-panel-heading">
									<h4 class="mjschool-panel-title">
										<?php esc_html_e( 'Class', 'mjschool' ); ?> :
										<?php echo esc_html( mjschool_get_class_name( $class_id ) ); ?> ,
										<?php esc_html_e( 'Date', 'mjschool' ); ?> :
										<?php echo esc_html( mjschool_get_date_in_input_box( sanitize_text_field( wp_unslash( $_POST['curr_date'] ) ) ) ); ?>
									</h4>
								</div>
								<div class="col-md-12">
									<div class="table-responsive">
										<table class="table">
											<tr>
												<th class="mjschool-multiple-subject-mark mjschool_widht_75" ><?php esc_html_e( 'Sr. No.', 'mjschool' ); ?></th>
												<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
												<th class="mjschool-multiple-subject-mark" class="mjschool_widht_250px"><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
												<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
											</tr>
											<?php
											$date = sanitize_text_field( wp_unslash( $_POST['curr_date'] ) );
											$i    = 1;
											foreach ( $student as $mjschool_user ) {
												$umetadata = mjschool_get_user_image( $mjschool_user->ID );
												if ( empty( $umetadata ) ) {
													$profile_path = get_option( 'mjschool_student_thumb_new' );
												} else {
													$profile_path = $umetadata;
												}
												$date             = date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['curr_date'] ) ) ) );
												$check_attendance = $mjschool_obj_attend->mjschool_check_has_subject_attendace( $mjschool_user->ID, $class_id, $date, $_POST['sub_id'], $_POST['class_section'] );
												$attendanc_status = 'Present';
												if ( ! empty( $check_attendance ) ) {
													$attendanc_status = $check_attendance->status;
												}
												echo '<tr>';
												echo '<td>' . esc_html( $i ) . '</td>';
												 
												echo '<td><img src=' . esc_url($profile_path) . ' class="img-circle" /><span class="ms-2">' . esc_html( mjschool_student_display_name_with_roll($mjschool_user->ID ) ) . '</span></td>';
												 
												?>
												<td>
													<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Present" <?php checked( $attendanc_status, 'Present' ); ?>>
														<?php esc_html_e( 'Present', 'mjschool' ); ?>
													</label>
													<label class="radio-inline"> <input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Absent" <?php checked( $attendanc_status, 'Absent' ); ?>>
														<?php esc_html_e( 'Absent', 'mjschool' ); ?>
													</label><br>
													<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Late" <?php checked( $attendanc_status, 'Late' ); ?>>
														<?php esc_html_e( 'Late', 'mjschool' ); ?>
													</label>
													<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Half Day" <?php checked( $attendanc_status, 'Half Day' ); ?>>
														<?php esc_html_e( 'Half Day', 'mjschool' ); ?>
													</label>
												</td>
												<td class="padding_left_right_0">
													<div class="form-group input mjschool-margin-bottom-0px">
														<div class="col-md-12 form-control">
															<input type="text" name="attendanace_comment_<?php echo esc_attr( $mjschool_user->ID ); ?>" class="form-control " value="<?php if ( ! empty( $check_attendance ) ) { echo esc_html( $check_attendance->comment );} ?>">
														</div>
													</div>
												</td>
												<?php
												echo '</tr>';
												++$i;
											}
											?>
										</table>
									</div>
									<?php wp_nonce_field( 'save_sub_attendence_front_nonce' ); ?>
									<div class="d-flex mt-2">
										<div class="form-group row mb-3">
											<span class="col-sm-10 control-label pt-2" for="enable">
												<?php esc_html_e( 'If student absent then Send Email to his/her parents', 'mjschool' ); ?>
											</span>
											<div class="col-sm-2 pt-2">
												<div class="mjschool-checkbox">
													<label>
														<input class="mjschool-check-box" id="smgt_mail_service_enable" type="checkbox" <?php $smgt_mail_service_enable = 0; if ( $smgt_mail_service_enable ) { echo 'checked'; } ?> value="1" name="smgt_mail_service_enable">
													</label>
												</div>
											</div>
										</div>
										<div class="form-group row mb-3">
											<span class="col-sm-10 control-label col-form-label" for="enable">
												<?php esc_html_e( 'If student absent then Send  SMS to his/her parents', 'mjschool' ); ?>
											</span>
											<div class="col-sm-2 pt-2">
												<div class="mjschool-checkbox">
													<label>
														<input id="chk_mjschool_sent1" type="checkbox" <?php $mjschool_service_enable = 0; if ( $mjschool_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_service_enable">
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
								if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
									?>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-sm-6 mjschool-rtl-res-att-save">
												<input type="submit" value="<?php esc_attr_e( 'Save Attendance', 'mjschool' ); ?>" name="save_sub_attendence" class="btn btn-success mjschool-save-btn" />
											</div>
										</div>
									</div>
									<?php
								}
								?>
							</form>
						</div>
						<?php
					} else {
						?>
						<div class=" mt-2">
							<h4 class="mjschool-panel-title"> <?php esc_html_e( 'No Any Student In This Class', 'mjschool' ); ?> </h4>
						</div>
						<?php
					}
				}
			}
		}
	}
	if ( $active_tab1 === 'attendence_with_qr' ) {
		// Check nonce for student attendence list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_student_attendance_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		?>
		<div class="mjschool-panel-body mjschool-attendence-panel-body">
			<form method="post">
				<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
					<div class="row"><!--Row Div start..-->
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="curr_date" class="form-control qr_date" type="text" value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field( wp_unslash( $_POST['curr_date'] ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="curr_date" readonly>
									<label  for="curr_date"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<?php
							if ( isset( $_REQUEST['class_id'] ) ) {
								$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
							}
							?>
							<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required] mjschool_qr_class_id">
								<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
								<?php
								foreach ( mjschool_get_all_class() as $classdata ) {
									?>
									<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>> <?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php
								}
								?>
							</select>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
							<?php
							$class_section = '';
							if ( isset( $_REQUEST['class_section'] ) ) {
								$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
							}
							?>
							<select name="class_section" class="mjschool-line-height-30px form-control mjschool-qr-class-section mjschool-class-section-subject" id="class_section">
								<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
								<?php
								if ( isset( $_REQUEST['class_section'] ) ) {
									$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
									foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>> <?php echo esc_html( $sectiondata->section_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-subject-list"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field"></span></label>
							<select name="sub_id" id="mjschool-subject-list" class="mjschool-line-height-30px form-control validate[required] mjschool-qr-class-subject">
								<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
								<?php
								$sub_id = 0;
								if ( isset( $_POST['sub_id'] ) ) {
									$sub_id = intval( wp_unslash( $_REQUEST['sub_id'] ) );
									$allsubjects = mjschool_get_subject_by_class_id( wp_unslash( $_POST['class_id'] ) );
									foreach ( $allsubjects as $subjectdata ) {
										?>
										<option value="<?php echo esc_attr( $subjectdata->subid ); ?>" <?php selected( $subjectdata->subid, $sub_id ); ?>> <?php echo esc_attr( $subjectdata->sub_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					</div>
				</div>
				
				<div class="mjschool-panel-heading">
					<h4 class="mjschool-panel-title"><?php esc_html_e( 'Scan QR Code To Take Attendance', 'mjschool' ); ?>
				</div>
				<div class="col-md-12">
					<div class="qrscanner" id="scanner"></div>
					<hr>
				</div>
			</form>
		</div>
		<?php
	}
	if ( $active_tab1 === 'export_attendance' ) {
		?>
		<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
			<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data">
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<div class="col-sm-12">
					<input type="submit" value="<?php esc_attr_e( 'Export Student Attendance', 'mjschool' ); ?>" name="export_attendance_in_csv" class="col-sm-6 mjschool-save-attr-btn" />
				</div>
			</form>
		</div><!-- Mjschool-panel-body. -->
		<?php
	}
	if ( $active_tab1 === 'teacher_attendences' ) {
		?>
		<form method="post" id="teacher_attendance">
			<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_take_teacher_attendance_nonce' ) ); ?>">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-5 col-md-5 col-lg-5 col-xl-5">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="curr_date_teacher_front" class="form-control" type="text" value="<?php if ( isset( $_POST['tcurr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( wp_unslash( $_POST['tcurr_date'] ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); }?>" name="tcurr_date" readonly>
								<label  for="curr_date_teacher_front"><?php esc_html_e( 'Date', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<input type="submit" value="<?php esc_attr_e( 'Take Attendance', 'mjschool' ); ?>" name="teacher_attendence" class="mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
		<?php
	}
	// ------------------------ Save teacher attendance. ----------------------//
	if (isset($_REQUEST['teacher_attendence'] )) {
		if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_take_teacher_attendance_nonce')) {
			wp_die(esc_html__('Security check failed.', 'mjschool'));
		}
		$attendanace_date = sanitize_text_field( wp_unslash( $_REQUEST['tcurr_date'] ) );
		$holiday_dates    = mjschool_get_all_date_of_holidays();
		if ( in_array( $attendanace_date, $holiday_dates ) ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert updated mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php esc_html_e( 'This day is holiday you are not able to take attendance', 'mjschool' ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		} else {
			?>
			<div class="mjschool-panel-body"> <!-- Mjschool-panel-body. -->
				<form method="post">
					<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_attendance_teacher_add_nonce' ) ); ?>">
					<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
					<input type="hidden" name="tcurr_date" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_POST['tcurr_date'] ) ) ); ?>" />
					<div class="mjschool-panel-heading">
						<h4 class="mjschool-panel-title"><?php esc_html_e( 'Teacher Attendance', 'mjschool' ); ?> ,
							<?php esc_html_e( 'Date', 'mjschool' ); ?> : <?php echo esc_attr( sanitize_text_field( wp_unslash( $_POST['tcurr_date'] ) ) ); ?>
						</h4>
					</div>
					<div class="col-md-12 mjschool-padding-payment mjschool-att-tbl-list">
						<div class="table-responsive">
							<table class="table">
								<tr>
									<th class="mjschool_width_0px"><?php esc_html_e( 'Srno', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Teacher', 'mjschool' ); ?></th>
									<th class="mjschool_widht_250px"><?php esc_html_e( 'Attendance', 'mjschool' ); ?> </th>
									<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
								</tr>
								<?php
								$date    = sanitize_text_field( wp_unslash( $_POST['tcurr_date'] ) );
								$i       = 1;
								$teacher = get_users( array( 'role' => 'teacher' ) );
								foreach ( $teacher as $mjschool_user ) {
									$class_id         = 0;
									$check_attendance = $mjschool_obj_attend->mjschool_check_attendence( $mjschool_user->ID, $class_id, $date );
									$attendanc_status = 'Present';
									if ( ! empty( $check_attendance ) ) {
										$attendanc_status = $check_attendance->status;
									}
									echo '<tr>';
									echo '<tr>';
									echo '<td>' . esc_html( $i ) . '</td>';
									echo '<td class="mjschool_padding_left_0px"><span>' . esc_html( $mjschool_user->first_name ) . ' ' . esc_html( $mjschool_user->last_name ) . '</span></td>';
									?>
									<td class="mjschool_padding_left_0px">
										<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Present" <?php checked( $attendanc_status, 'Present' ); ?>><?php esc_html_e( 'Present', 'mjschool' ); ?></label>
										<label class="radio-inline"> <input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Absent" <?php checked( $attendanc_status, 'Absent' ); ?>><?php esc_html_e( 'Absent', 'mjschool' ); ?></label><br>
										<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Late" <?php checked( $attendanc_status, 'Late' ); ?>><?php esc_html_e( 'Late', 'mjschool' ); ?></label>
										<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Half Day" <?php checked( $attendanc_status, 'Half Day' ); ?>><?php esc_html_e( 'Half Day', 'mjschool' ); ?></label>
									</td>
									<td >
										<div class="form-group input mjschool-margin-bottom-0px">
											<div class="col-md-12 form-control">
												<input type="text" name="attendanace_comment_<?php echo esc_attr( $mjschool_user->ID ); ?>" class="form-control" value="<?php if ( ! empty( $check_attendance ) ) { echo esc_html( $check_attendance->comment );} ?>">
											</div>
										</div>
									</td>
									<?php
									echo '</tr>';
									++$i;
								}
								?>
							</table>
						</div>
					</div>
					<div class="cleatrfix"></div>
					<div class="col-sm-12 col-md-6 padding_top_10px mjschool-rtl-res-att-save mt-3">
						<input type="submit" value="<?php esc_attr_e( 'Save  Attendance', 'mjschool' ); ?>" name="save_teach_attendence" id="mjschool-res-rtl-width-100px" class="mjschool-res-rtl-width-100px col-sm-12 mjschool-save-attr-btn" />
					</div>
				</form>
			</div><!-- Mjschool-panel-body. -->
			<?php
		}
	}
	if ( $active_tab === 'attedance_list' ) {
		$student_id      = get_current_user_id();
		$attendance_list = mjschool_monthly_attendence( $student_id );
		if ( ! empty( $attendance_list ) ) {
			?>
			<div class="table-div"><!-- Start panel body div. -->
				<div class="table-responsive"><!-- Table responsive div start. -->
					<table id="mjschool-attendance-list-detail-page-second" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Student Name & Roll No.', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance Date', 'mjschool' ); ?> </th>
								<th><?php esc_html_e( 'Day', 'mjschool' ); ?> </th>
								<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i    = 0;
							$srno = 1;
							if ( ! empty( $attendance_list ) ) {
								foreach ( $attendance_list as $retrieved_data ) {
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
											<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr($color_class_css); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>" class="mjschool-massage-image attendace_module_image ">
											</p>
										</td>
										<td class="department">
											<?php echo esc_html( mjschool_get_user_name_by_id($retrieved_data->user_id ) ); ?>-<?php echo esc_html( get_user_meta($retrieved_data->user_id, 'roll_id', true ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name & Roll No.', 'mjschool' ); ?>"></i>
										</td>
										<td >
											<?php echo esc_html( mjschool_get_class_name($retrieved_data->class_id ) ); ?> 
											<i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
										</td>
										<?php $curremt_date = mjschool_get_date_in_input_box($retrieved_data->attendence_date);
										$day = date( "D", strtotime($curremt_date ) ); ?>
										<td class="name">
											<?php echo esc_attr( mjschool_get_date_in_input_box($retrieved_data->attendence_date ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance Date', 'mjschool' ); ?>"></i>
										</td>
										<td class="department">
											<?php
											if ($day === 'Mon' ) {
												esc_html_e( 'Monday', 'mjschool' );
											} elseif ($day === 'Sun' ) {
												esc_html_e( 'Sunday', 'mjschool' );
											} elseif ($day === 'Tue' ) {
												esc_html_e( 'Tuesday', 'mjschool' );
											} elseif ($day === 'Wed' ) {
												esc_html_e( 'Wednesday', 'mjschool' );
											} elseif ($day === 'Thu' ) {
												esc_html_e( 'Thursday', 'mjschool' );
											} elseif ($day === 'Fri' ) {
												esc_html_e( 'Friday', 'mjschool' );
											} elseif ($day === 'Sat' ) {
												esc_html_e( 'Saturday', 'mjschool' );
											}
											?> 
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php echo esc_html( $retrieved_data->status); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
										</td>
										<?php
										$comment = $retrieved_data->comment;
										$comment_out = strlen($comment) > 30 ? substr( $comment, 0, 30) . "..." : $comment;
										?>
										<td class="mjschool-width-20px">
											<?php
											if ( ! empty( $retrieved_data->comment ) ) {
												echo esc_html( $comment_out );
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											} ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Comment', 'mjschool' ); ?>"></i>
										</td>
									</tr>
									<?php
									$i++;
									$srno++;
								}
							}
							?>
						</tbody>
					</table>
				</div><!-- Table responsive div end. -->
			</div>
			<?php
		} else {
			$page_1 = 'attendance';
			$fattendance_1 = mjschool_get_user_role_wise_filter_access_right_array($page_1);
			if ($mjschool_role === 'administrator' || $fattendance_1['add'] === '1' ) {
				?>
				<div class="mjschool-no-data-list-div">
					<a href="<?php echo esc_url( admin_url() . 'admin.php?page=mjschool_attendence' ); ?>">
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
	}
	if ( $active_tab1 === 'teacher_attedance_list' ) {
		// Check nonce for teacher attendence list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_teacher_attendance_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		?>
		<form method="post" id="attendance_list" class="attendance_list">
			<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_teacher_attendance_list_nonce' ) ); ?>">
			<div class="form-body mjschool-user-form mjschool-margin-top-15px">
				<div class="row">
					<div class="col-md-3 mb-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php $date_type = isset( $_POST['date_type'] ) ? $_POST['date_type'] : ''; ?>
						<select class="mjschool-line-height-30px form-control date_type validate[required]" id="date_type" name="date_type" autocomplete="off">
							<option value="today" <?php selected( $date_type, 'today' ); ?>><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
							<option value="this_week" <?php selected( $date_type, 'this_week' ); ?>><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
							<option value="last_week" <?php selected( $date_type, 'last_week' ); ?>><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
							<option value="this_month" <?php selected( $date_type, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
							<option value="last_month" <?php selected( $date_type, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
							<option value="last_3_month" <?php selected( $date_type, 'last_3_month' ); ?>><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
							<option value="last_6_month" <?php selected( $date_type, 'last_6_month' ); ?>><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
							<option value="last_12_month" <?php selected( $date_type, 'last_12_month' ); ?>><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
							<option value="this_year" <?php selected( $date_type, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
							<option value="last_year" <?php selected( $date_type, 'last_year' ); ?>><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
							<option value="period" <?php selected( $date_type, 'period' ); ?>><?php esc_html_e( 'Period', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 input">
						<?php
						if ( isset( $_POST['teacher_name'] ) ) {
							$workrval = sanitize_text_field( wp_unslash( $_REQUEST['teacher_name'] ) );
						} else {
							$workrval = '';
						}
						?>
						<select id="teacher_list" class="form-control user_select display-members" name="teacher_name">
							<option value="all_teacher"><?php esc_html_e( 'All Teacher', 'mjschool' ); ?></option>
							<?php
							$teacherdata = mjschool_get_users_data( 'teacher' );
							if ( ! empty( $teacherdata ) ) {
								foreach ( $teacherdata as $teacher ) {
									?>
									<option value="<?php echo esc_attr( $teacher->ID ); ?>" <?php selected( $teacher->ID ); ?>> <?php echo esc_html( $teacher->display_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<div id="date_type_div" class="col-md-6 <?php echo ( $date_type === 'period' ) ? '' : 'date_type_div_none'; ?>">
						<?php
						if ( $date_type === 'period' ) {
							?>
							<div class="row">
								<div class="col-md-6 mb-2">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text" id="report_sdate" class="form-control" name="start_date" value="<?php echo isset( $_POST['start_date'] ) ? esc_attr( wp_unslash($_POST['start_date'] ) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
											<label for="report_sdate" class="active"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-6 mb-2">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text" id="report_edate" class="form-control" name="end_date" value="<?php echo isset( $_POST['end_date'] ) ? esc_attr( wp_unslash( $_POST['end_date'] ) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
											<label for="report_edate" class="active"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col-md-3 mb-2">
						<input type="submit" name="view_attendance" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
		<div class="clearfix"></div>
		<?php
		if ( isset( $_REQUEST['view_attendance'] ) ) {
			if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_teacher_attendance_list_nonce')) {
				wp_die(esc_html__('Security check failed.', 'mjschool'));
			}
			$date_type = $_POST['date_type'];
			if ( $date_type === 'period' ) {
				$start_date      = sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) );
				$end_date        = sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) );
				$type            = 'teacher';
				$attendence_data = mjschool_get_all_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $type );
			} else {
				$result     = mjschool_all_date_type_value( $date_type );
				$response   = json_decode( $result );
				$start_date = $response[0];
				$end_date   = $response[1];
				if ( ! empty( $_REQUEST['teacher_name'] ) && $_REQUEST['teacher_name'] != 'all_teacher' ) {
					$member_id       = sanitize_text_field( wp_unslash( $_REQUEST['teacher_name'] ) );
					$attendence_data = mjschool_get_member_attendence_beetween_satrt_date_to_enddate_for_admin( $start_date, $end_date, $member_id );
				} else {
					$type            = 'teacher';
					$attendence_data = mjschool_get_all_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $type );
				}
			}
		} else {
			$start_date      = date( 'Y-m-d', strtotime( 'first day of this month' ) );
			$end_date        = date( 'Y-m-d', strtotime( 'last day of this month' ) );
			$type            = 'teacher';
			$attendence_data = mjschool_get_all_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $type );
		}
		if ( $start_date > $end_date ) {
			echo '<script type="text/javascript">alert( "' . esc_html__( 'End Date should be greater than the Start Date', 'mjschool' ) . '");</script>';
		}
		if ( ! empty( $attendence_data ) ) {
			?>
			<div class="table-div"><!-- Start panel body div. -->
				<div class="table-responsive"><!-- Table responsive div start. -->
					<div class="btn-place"></div>
					<table id="front_teacher_attendance_list" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance Status', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $attendence_data as $retrieved_data ) {
								$member_data = get_userdata( $retrieved_data->user_id );
								$class       = mjschool_get_class_name_by_teacher_id( $member_data->data->ID );
								if ( ! empty( $member_data->parent_id ) ) {
									$parent_data = get_userdata( $member_data->parent_id );
								}
								?>
								<tr>
									<td class="mjschool-user-image mjschool-width-50px-td">
										<a href="#">
											<?php $uid = $retrieved_data->ID;
											$umetadata = mjschool_get_user_image($uid);
											if (empty($umetadata ) ) {
												echo '<img src=' . esc_url( get_option( 'mjschool_teacher_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
											} else {
												echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
											}
											?>
										</a>
									</td>
									<td class="name">
										<?php
										if ( $member_data->roles[0] === 'student' ) {
											echo esc_html( $member_data->display_name );
										} else {
											echo esc_html( $member_data->display_name );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php echo esc_html( mjschool_get_class_name( $class->class_id ) ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendence_date ) ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php
										$day = date( 'D', strtotime( $retrieved_data->attendence_date ) );
										if ( $day === 'Mon' ) {
											esc_html_e( 'Monday', 'mjschool' );
										} elseif ( $day === 'Sun' ) {
											esc_html_e( 'Sunday', 'mjschool' );
										} elseif ( $day === 'Tue' ) {
											esc_html_e( 'Tuesday', 'mjschool' );
										} elseif ( $day === 'Wed' ) {
											esc_html_e( 'Wednesday', 'mjschool' );
										} elseif ( $day === 'Thu' ) {
											esc_html_e( 'Thursday', 'mjschool' );
										} elseif ( $day === 'Fri' ) {
											esc_html_e( 'Friday', 'mjschool' );
										} elseif ( $day === 'Sat' ) {
											esc_html_e( 'Saturday', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php echo esc_html( mjschool_get_display_name( $retrieved_data->attend_by ) ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php $status_color = mjschool_attendance_status_color( $retrieved_data->status ); ?>
										<span style="color:<?php echo esc_attr( $status_color ); ?>;">
											<?php echo esc_html( $retrieved_data->status ); ?>
										</span>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance Status', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php
										if ( ! empty( $retrieved_data->comment ) ) {
											$comment       = $retrieved_data->comment;
											$grade_comment = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
											echo esc_html( $grade_comment );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->comment ) ) { echo esc_html( $retrieved_data->comment ); } else { esc_html_e( 'Comment', 'mjschool' ); } ?>"></i>
									</td>
								</tr>
								<?php
								++$i;
							}
							?>
						</tbody>
					</table>
				</div><!-- Table responsive div end. -->
			</div>
			<?php
		} else {
			$page_1        = 'attendance';
			$fattendance_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
			if ( $fattendance_1['add'] === '1' ) {
				?>
				
				<div class="mjschool-no-data-list-div">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=attendance&tab=teacher_attendance&tab1=teacher_attendences') ); ?>">
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
		<?php
	}
	if ( $active_tab === 'teacher_attedance_list' ) {
		// Check nonce for teacher attendence list tab.
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_teacher_attendance_tab' ) ) {
				wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
			}
		}
		$teacher_id      = get_current_user_id();
		$attendance_list = mjschool_monthly_attendence( $teacher_id );
		if ( ! empty( $attendance_list ) ) {
			?>
			<div class="table-div"><!-- Start panel body div. -->
				<div class="table-responsive"><!-- Table responsive div start. -->
					<table id="mjschool-attendance-list-detail-page-third" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'No.', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Day', 'mjschool' ); ?> </th>
								<th><?php esc_html_e( 'Status', 'mjschool' ); ?> </th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i    = 0;
							$srno = 1;
							if ( ! empty( $attendance_list ) ) {
								foreach ( $attendance_list as $retrieved_data ) {
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
											<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr($color_class_css); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>" class="mjschool-massage-image attendace_module_image ">
											</p>
										</td>
										<td>
											<?php echo esc_html( $srno); ?> 
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'No.', 'mjschool' ); ?>"></i>
										</td>
										<td >
											<?php echo esc_html( mjschool_get_user_name_by_id($retrieved_data->user_id ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i>
										</td>
										<td class="name">
											<?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->attendence_date ) ); ?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance Date', 'mjschool' ); ?>"></i>
										</td>
										<td >
											<?php
											$curremt_date = $retrieved_data->attendence_date;
											$day = date( "D", strtotime($curremt_date ) );
											echo esc_html( $day, "mjschool" );
											?> 
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php echo esc_html( $retrieved_data->status); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
										</td>
									</tr>
									<?php
									$i++;
									$srno++;
								}
							}
							?>
						</tbody>
					</table>
				</div><!-- Table responsive div end. -->
			</div>
			<?php
		} else { ?>
			<div class="mjschool-no-data-list-div">
				<a href="<?php echo esc_url( admin_url() . 'admin.php?page=mjschool_attendence&tab=teacher_attendence' ); ?>">
					<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
				</a>
				<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
					<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
				</div>
			</div>
			<?php
		}
	}
	?>
</div> <!-------------- Panel body. ----------------->