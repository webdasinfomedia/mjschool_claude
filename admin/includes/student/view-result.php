<?php
if(isset($_REQUEST['merge_id'])) {
    $total_obtained     = 0;
    $total_max_possible = 0;
    $any_subject_failed = false;
    $class_id   = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['class_id'] ) ) ) );
    $section_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['section_id'] ) ) ) );
    $merge_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['merge_id'] ) ) ) );
    $student_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'] ) ) ) );
    $teacher_comment = isset( $_REQUEST['comment'] ) ? sanitize_text_field( wp_unslash($_REQUEST['comment']) ) : '';
    $teacher_id         = intval( sanitize_text_field(wp_unslash($_REQUEST['teacher_id']) ) );   // error_reporting(0);
    $obj_mark   = new Mjschool_Marks_Manage();
    $subject    = $obj_mark->mjschool_student_subject_list( $class_id, $section_id );
    $exam_obj   = new Mjschool_exam();
    $merge_data = $exam_obj->mjschool_get_single_merge_exam_setting( $merge_id );
    $merge_name        = $merge_data->merge_name;
    $merge_config_data = json_decode( $merge_data->merge_config );
    $total_subject     = count( $subject );
    if($teacher_id) {
        $metadata       = get_user_meta( $teacher_id );
        $signature_path = isset( $metadata['signature'][0] ) ? $metadata['signature'][0] : '';
        $signature_url  = $signature_path ? content_url( $signature_path ) : '';
    }
    // Prevent division by zero.
    if ( $total_subject === 0 ) {
        return '<p>' . esc_html__( 'No subjects found.', 'mjschool' ) . '</p>';
    }
    ?>
    <div id="invoice-pdf" class="pdf-content-main mjschool-float-left-width-100px">
        <div class="mjschool-bottom-8px">
            <div class="mjschool-result-header">
                <div class="mjschool-float-left-width-100pr">
                    <div class="mjschool-float-left-width-25pr"> 
                        <div class="mjschool-custom-logo-class mjschool-float-left-border-radius-50px">
                            <img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool-result-logo" />
                        </div>
                    </div>
                    <div class="mjschool-float-left-width-75-padding-top-10px">
                        <p class="mjschool-name-style"><?php echo esc_html(  get_option( 'mjschool_name' ) ); ?></p>
                        <p class="mjschool-address-style"><?php echo esc_html(  get_option( 'mjschool_address' ) ); ?></p>
                        <div class="mjschool-margin0px-width-100-text-align-center">
                            <p class="mjschool-email-phone-style"><?php esc_html_e( 'E-mail', 'mjschool' ); ?> :<?php echo esc_html(  get_option( 'mjschool_email' ) ); ?></p>
                            <p class="mjschool-email-phone-style">&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> :<?php echo esc_html(  get_option( 'mjschool_contact_number' ) ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mjschool-student-info-container">
            <div class="mjschool-float-left-width-100pr">
                <div class="mjschool_padding_10px">
                    <div class="mjschool-info-column-left">
                        <b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>:
                        <?php echo esc_html( get_user_meta( $student_id, 'first_name', true ) . ' ' . get_user_meta( $student_id, 'last_name', true ) ); ?>
                    </div>
                    <div class="mjschool-info-column-right">
                        <b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>:
                        <?php echo esc_html( $merge_name ); ?>
                    </div>
                    <div class="mjschool-clear-both"></div>
                    <div class="mjschool-info-column-left">
                        <b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>:
                        <?php echo esc_html( get_user_meta( $student_id, 'roll_id', true ) ); ?>
                    </div>
                    <div class="mjschool-info-column-right-with-margin">
                        <b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
                        <?php
                        $mjschool_class = new Mjschool_Class();
                        $class_name = function_exists( 'mjschool_get_class_name' ) ? $mjschool_class->mjschool_get_class_name( $class_id ) : '';
                        if ( ! empty( $section_id ) && function_exists( 'mjschool_get_section_name' ) ) {
                            $section_name = $mjschool_class->mjschool_get_section_name( $section_id );
                            echo esc_html( $class_name . ' ( ' . $section_name . ' )' );
                        } else {
                            echo esc_html( $class_name );
                        }
                        ?>
                    </div>
                    <div class="mjschool-clear-both"></div>
                </div>
            </div>
        </div>
        <table class="mjschool-result-marks-table" cellpadding="10" cellspacing="0">
            <thead>
                <tr class="mjschool-table-header-row">
                    <th rowspan="2" class="mjschool-table-header-cell"><?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
                    <?php
                    $obj_exam = new Mjschool_Exam();
                    if ( ! empty( $merge_config_data ) ) {
                        foreach ( $merge_config_data as $item ) {
                            $exam_id   = $item->exam_id;
                            $exam_name = $obj_exam->mjschool_get_exam_name_id( $exam_id );
                            if ( function_exists( 'mjschool_check_contribution' ) && mjschool_check_contribution( $exam_id ) === 'yes' ) {
                                $exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
                                $contributions_data_array = json_decode( $exam_data->contributions_data, true );
                                $colspan                  = is_array( $contributions_data_array ) ? count( $contributions_data_array ) : 1;
                                echo '<th colspan="' . esc_attr( $colspan ) . '" class="mjschool-table-header-cell">' . esc_html( $exam_name ) . '</th>';
                            } else {
                                echo '<th class="mjschool-table-header-cell">' . esc_html( $exam_name ) . '</th>';
                            }
                        }
                    }
                    ?>
                    <th colspan="2" class="mjschool-table-header-cell">
                        <?php
                        if ( function_exists( 'mjschool_print_weightage_data_pdf' ) ) {
                            echo esc_html( mjschool_print_weightage_data_pdf( $merge_data->merge_config ) );
                        }
                        ?>
                    </th>
                </tr>
                <tr class="mjschool-table-header-row">
                    <?php
                    if ( ! empty( $merge_config_data ) ) {
                        foreach ( $merge_config_data as $item ) {
                            $exam_id = $item->exam_id;

                            if ( function_exists( 'mjschool_check_contribution' ) && mjschool_check_contribution( $exam_id ) === 'yes' ) {
                                $exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
                                $contributions_data_array = json_decode( $exam_data->contributions_data, true );

                                if ( is_array( $contributions_data_array ) ) {
                                    foreach ( $contributions_data_array as $con_value ) {
                                        echo '<th class="mjschool-table-header-cell">' . esc_html( $con_value['label'] ) . ' (' . esc_html( $con_value['mark'] ) . ')</th>';
                                    }
                                }
                            } else {
                                ?>
                                <th class="mjschool-table-header-cell"><?php esc_html_e( 'Grand Total(100)', 'mjschool' ); ?></th>
                                <?php
                            }
                        }
                    }
                    ?>
                    <th class="mjschool-table-header-cell"><?php esc_html_e( 'Grand Total(100)', 'mjschool' ); ?></th>
                    <th class="mjschool-table-header-cell"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $subject as $sub ) {
                    echo '<tr>';
                    echo '<td class="mjschool-table-header-cell">' . esc_html( $sub->sub_name ) . '</td>';

                    $subject_total_weighted = 0;

                    foreach ( $merge_config_data as $item ) {
                        $exam_id        = $item->exam_id;
                        $exam_weightage = isset( $item->weightage ) ? floatval( $item->weightage ) : 0;
                        $marks          = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $student_id );

                        if ( function_exists( 'mjschool_check_contribution' ) && mjschool_check_contribution( $exam_id ) === 'yes' ) {
                            $exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
                            $contributions_data_array = json_decode( $exam_data->contributions_data, true );
                            $subject_total            = 0;

                            if ( is_array( $contributions_data_array ) ) {
                                foreach ( $contributions_data_array as $con_id => $con_value ) {
                                    $mark_value     = isset( $marks[ $con_id ] ) ? floatval( $marks[ $con_id ] ) : 0;
                                    $subject_total += $mark_value;
                                    echo '<td class="mjschool-table-cell-marks">' . esc_html( $mark_value ) . '</td>';
                                }
                            }

                            $weighted_marks = $exam_weightage > 0 ? ( $subject_total * $exam_weightage ) / 100 : 0;
                            $pass_marks     = $obj_mark->mjschool_get_pass_marks( $exam_id );

                            if ( $subject_total < $pass_marks ) {
                                $any_subject_failed = true;
                            }
                        } else {
                            $marks_float = floatval( $marks );
                            echo '<td class="mjschool-table-cell-marks">' . esc_html( $marks_float ) . '</td>';

                            $weighted_marks = $exam_weightage > 0 ? ( $marks_float * $exam_weightage ) / 100 : 0;
                            $pass_marks     = $obj_mark->mjschool_get_pass_marks( $exam_id );

                            if ( $marks_float < $pass_marks ) {
                                $any_subject_failed = true;
                            }
                        }

                        $subject_total_weighted += $weighted_marks;
                    }

                    $subject_grade = $obj_mark->mjschool_get_grade_base_on_grand_total( $subject_total_weighted );
                    echo '<td class="mjschool-table-header-cell">' . esc_html( round( $subject_total_weighted, 2 ) ) . '</td>';
                    echo '<td class="mjschool-table-header-cell">' . esc_html( $subject_grade ) . '</td>';
                    echo '</tr>';

                    $total_obtained     += $subject_total_weighted;
                    $total_max_possible += 100;
                }

                // FIXED: Prevent division by zero
                $percentage   = $total_max_possible > 0 ? ( $total_obtained / $total_max_possible ) * 100 : 0;
                $final_grade  = $obj_mark->mjschool_get_grade_base_on_grand_total( $percentage );
                $final_result = ( $any_subject_failed || $percentage < 33 ) ? esc_html__( 'Fail', 'mjschool' ) : esc_html__( 'Pass', 'mjschool' );
                ?>
            </tbody>
        </table>
        <!-- Summary Table. -->
        <table class="mjschool-result-marks-table" cellpadding="10" cellspacing="0">
            <thead>
                <tr class="mjschool-table-header-row">
                    <th class="mjschool-summary-header-cell"><?php esc_html_e( 'Overall Mark', 'mjschool' ); ?></th>
                    <th class="mjschool-summary-header-cell"><?php esc_html_e( 'Percentage', 'mjschool' ); ?></th>
                    <th class="mjschool-summary-header-cell"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
                    <th class="mjschool-summary-header-cell"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr class="mjschool-table-header-row">
                    <td class="mjschool-table-header-cell"><?php echo esc_html( round( $total_obtained, 2 ) . ' / ' . $total_max_possible ); ?></td>
                    <td class="mjschool-table-header-cell"><?php echo esc_html( number_format( $percentage, 2 ) . '%' ); ?></td>
                    <td class="mjschool-table-header-cell"><?php echo esc_html( $final_grade ); ?></td>
                    <td class="mjschool-table-header-cell"><?php echo esc_html( $final_result ); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="mjschool-signature-container">
            <!-- Teacher's Comment (Left Side) -->
            <div class="mjschool-signature-column-left">
                <div class="mjschool-comment-content">
                    <strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong> <p><?php echo esc_html(  $teacher_comment ); ?></p>
                </div>
            </div>
            <!-- Teacher Signature (Middle) -->
            <div class="mjschool-signature-column-center">
                <?php
                if ( ! empty( $signature_url ) ) {
                    ?>
                    <div>
                        <img src="<?php echo esc_url( $signature_url ); ?>" class="mjschool-signature-image" />
                    </div>
                    <?php
                } else {
                    ?>
                    <div>
                        <div class="mjschool-signature-placeholder"></div>
                    </div>
                <?php } ?>
                <div class="mjschool-signature-line"></div>
                <div class="mjschool_margin_top_5px"> <?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?> </div>
            </div>
            <!-- Principal Signature (Right Side) -->
            <div class="mjschool-signature-column-right">
                
                <div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_sign_width_100px" /> </div>
                
                <div class="mjschool-principal-signature-line"></div>
                <div class="mjschool-principal-signature-label"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 mjschool-padding-top-20 total_mjschool-padding-15px mjschool-float-width-hall">
        <div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-8 col-sm-8 col-xs-8 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
            <div class="col-md-2 mjschool-print-btn-rs mjschool-width-50-res mjschool-width-13per">
                <a student_id="<?php echo esc_attr(mjschool_encrypt_id($student_id ) ); ?>" class_id="<?php echo esc_attr(mjschool_encrypt_id($class_id ) ); ?>" section_id="<?php echo esc_attr(mjschool_encrypt_id($section_id ) ); ?>" merge_id="<?php echo esc_attr(mjschool_encrypt_id($merge_id ) ); ?>" typeformat="print" href="#" class="mjschool-float-right show-popup-teacher-details-marge" target="_blank">
                    <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-print.png"); ?>"> 
                </a>
            </div>
            <div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
                <a href="javascript:void(0)" id="download_result_pdf" class="btn mjschool-color-white mjschool-invoice-btn-div mjschool-save-btn">
                    <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>">
                </a>
            </div>
        </div>
    </div>
    <?php
} else{
    // var_dump($_REQUEST);
    $school_type	 = get_option( 'mjschool_custom_class' );
    $sudent_id       = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'] ) ) ) );    // error_reporting(0);
    $exam_id         = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) );   // error_reporting(0);
    $teacher_id         = intval( sanitize_text_field(wp_unslash($_REQUEST['teacher_id']) ) );   // error_reporting(0);
    $teacher_comment = isset( $_REQUEST['comment'] ) ? sanitize_text_field( wp_unslash($_REQUEST['comment']) ) : '';
    $obj_mark        = new mjschool_Marks_Manage();
    $uid             = $sudent_id;
    $exam_obj        = new mjschool_exam();
    $user            = get_userdata( $uid );
    $user_meta       = get_user_meta( $uid );
    $exam_data       = $exam_obj->mjschool_exam_data( $exam_id );
    $class_id        = $exam_data->class_id;
    $exam_section_id = $exam_data->section_id;
    $signature_url = null;
    if($teacher_id) {
        $metadata       = get_user_meta( $teacher_id );
        $signature_path = isset( $metadata['signature'][0] ) ? $metadata['signature'][0] : '';
        $signature_url  = $signature_path ? content_url( $signature_path ) : '';
    }
    if ( $exam_section_id == 0 ) {
        $obj_subject = new Mjschool_Subject();
        $subject = $obj_subject->mjschool_get_subject_by_class_id($class_id);
    } else {
        $subject = mjschool_get_subjects_by_class_and_section($class_id, $exam_section_id);
    }
    $total_subject = count( $subject );
    // $exam_id = $_REQUEST['exam_id'];
    $total       = 0;
    $grade_point = 0;
    $mjschool_user = new Mjschool_User();
    $umetadata   = $mjschool_user->mjschool_get_user_image( $uid );
    ob_start();
    if ( is_rtl() ) {
        ?>
        <div id="invoice-pdf" class="pdf-content-main mjschool-float-left-width-100px">
            <div class="mjschool-bottom-8px">
                <div class="mjschool-result-header">
                    <div class="mjschool-float-left-width-100pr">
                        <div class="mjschool-float-left-width-25pr">
                            
                            <div class="mjschool-custom-logo-class mjschool-float-left-border-radius-50px">
                                <img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool-result-logo" />
                            </div>
                        </div>
                        
                        <div class="mjschool-float-left-width-75-padding-top-10px">
                            <p class="mjschool-name-style"><?php echo esc_html(  get_option( 'mjschool_name' ) ); ?></p>
                            <p class="mjschool-address-style"><?php echo esc_html(  get_option( 'mjschool_address' ) ); ?></p>
                            <div class="mjschool-margin0px-width-100-text-align-center">
                                <p class="mjschool-email-phone-style"><?php esc_html_e( 'E-mail', 'mjschool' ); ?> :<?php echo esc_html(  get_option( 'mjschool_email' ) ); ?></p>
                                <p class="mjschool-email-phone-style">&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> :<?php echo esc_html(  get_option( 'mjschool_contact_number' ) ); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mjschool-student-info-container">
                <div class="mjschool-float-left-width-100pr">
                    <div class="mjschool-info-padding">
                        <div class="mjschool-info-field-left">
                            <b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>:<?php echo esc_html(  get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html(  get_user_meta( $uid, 'last_name', true ) ); ?>
                        </div>
                        <div class="mjschool-info-field-right">
                            <b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>:<?php echo esc_html(     $obj_exam->mjschool_get_exam_name_id( $exam_id ) ); ?>
                        </div>
                    </div>
                </div>
                <div class="mjschool-info-section-left">
                    <div class="mjschool-info-padding">
                        <div class="mjschool-info-field-width-50">
                            <b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>: <?php echo esc_html(  get_user_meta( $uid, 'roll_id', true ) ); ?>
                        </div>
                    </div>
                </div>
                <div class="mjschool-info-section-right">
                    <div class="mjschool-info-padding-top">
                        <b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
                        <?php
                        $classname = mjschool_get_class_name( $class_id );
                        $section_name = ! empty( $section_id ) ? mjschool_get_section_name( $section_id ) : esc_html__( 'No Section', 'mjschool' );
                        echo esc_html(  $classname ) . ' - ' . esc_html(  $section_name );
                        ?>
                    </div>
                </div>
            </div>
            <div class="mjschool-info-section-left">
                <div class="mjschool-info-padding">
                    <div class="mjschool-info-field-left">
                        <b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>: <?php echo esc_html(  get_user_meta( $uid, 'roll_id', true ) ); ?>
                    </div>
                </div>
            </div>
            <div class="mjschool-info-section-right">
                <div class="mjschool-info-padding-top">
                    <b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
                    <?php
                    $mjschool_class = new Mjschool_Class();
                    $classname = $mjschool_class->mjschool_get_class_name( $class_id );
                    $section_name = ! empty( $section_id ) ? $mjschool_class->mjschool_get_section_name( $section_id ) : esc_html__( 'No Section', 'mjschool' );
                    echo esc_html(  $classname ) . ' - ' . esc_html(  $section_name );
                    ?>
                </div>
            </div>
        </div>
        <table class="mjschool-marks-table-rtl" cellpadding="10" cellspacing="0">
            <thead>
                <?php
                $exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
                $exam_marks    = $exam_data->total_mark;
                $contributions = $exam_data->contributions;
                if ( $contributions === 'yes' ) {
                    $contributions_data       = $exam_data->contributions_data;
                    $contributions_data_array = json_decode( $contributions_data, true );
                }
                ?>
                <tr class="mjschool-marks-table-header-row">
                    <th class="mjschool-marks-table-header-cell-right"> <?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
                    <?php
                    $exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
                    $exam_marks    = $exam_data->total_mark;
                    $contributions = $exam_data->contributions;
                    if ( $contributions === 'yes' ) {
                        $contributions_data       = $exam_data->contributions_data;
                        $contributions_data_array = json_decode( $contributions_data, true );
                    }
                    ?>
                    <tr class="mjschool-marks-table-header-row">
                        <th class="mjschool-marks-table-header-cell-right"> <?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
                        <?php
                        if ( $contributions === 'yes' ) {
                            foreach ( $contributions_data_array as $con_id => $con_value ) {
                                ?>
                                <th class="mjschool-marks-table-header-cell-right"> <?php echo esc_html(  $con_value['label'] ) . ' ( ' . esc_html(  $con_value['mark'] ) . ' )'; ?></th>
                                <?php
                            }
                            ?>
                            <th class="mjschool-marks-table-header-cell-right"> <?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html(  $exam_marks ) . ' )'; ?></th>
                            <?php
                        } else {
                            ?>
                            <th class="mjschool-marks-table-header-cell-right"> <?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html(  $exam_marks ) . ' )'; ?></th>
                            <?php
                        }
                        ?>
                        <th class="mjschool-marks-table-header-cell-right"> <?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ( $school_type === 'school' ){
                        $i               = 1;
                        $total_pass_mark = 0;
                        $total_max_mark  = 0;
                        $total_marks     = 0;
                        $GPA             = 0;
                        foreach ( $subject as $sub ) {
                            $total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
                            $marks_get        = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                            ?>
                            <tr class="mjschool-marks-table-body-row">
                                <td class="mjschool-marks-table-body-cell"><?php echo esc_html(  $sub->sub_name ); ?> </td>
                                <!-- <td class="mjschool-marks-table-body-cell"> <?php echo esc_html(  $obj_mark->mjschool_get_max_marks( $exam_id ) ); ?> </td>
                                <td class="mjschool-marks-table-body-cell"> <?php echo esc_html(  $obj_mark->mjschool_get_pass_marks( $exam_id ) ); ?></td> -->
                                <?php
                                $obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                                if ( $contributions === 'yes' ) {
                                    $subject_total = 0;
                                    foreach ( $contributions_data_array as $con_id => $con_value ) {
                                        $mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                        $subject_total += $mark_value;
                                        ?>
                                        <td class="mjschool-marks-table-body-cell"><?php echo esc_html(  $mark_value ); ?> </td> <?php
                                    }
                                    ?>
                                    <td class="mjschool-marks-table-body-cell"><?php echo esc_html(  $subject_total ); ?> </td>
                                    <?php
                                } else {
                                    ?>
                                    <td class="mjschool-marks-table-body-cell"><?php echo esc_html(  $obtain_marks ); ?> </td>
                                    <?php
                                }
                                ?>
                                <td class="mjschool-marks-table-body-cell"> <?php echo esc_html(  $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?> </td>
                            </tr>
                            <?php
                            ++$i;
                            // Calculate total marks.
                            if ( $contributions === 'yes' ) {
                                foreach ( $contributions_data_array as $con_id => $con_value ) {
                                    $total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                }
                            } else {
                                $total_marks += $obtain_marks;
                            }
                            $grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
                        }
                        $total         += $total_marks;
                        $total_max_mark = $exam_marks * $total_subject;
                        if(!empty($grade_point) && !empty($total_subject)) {
                            $GPA = $grade_point / $total_subject;
                        }
                        if(!empty($total) && !empty($total_max_mark)) {
                            $percentage     = $total / $total_max_mark * 100;
                        }
                    }elseif ( $school_type == "university"){
                        $i               = 1;
                        $total_pass_mark = 0;
                        $total_max_mark  = 0;
                        $total_marks     = 0;
                        $exam_subject_data = json_decode($exam_data->subject_data,true);
                        $exam_subject_lookup = [];
                        foreach ($exam_subject_data as $exam_sub) {
                            $exam_subject_lookup[$exam_sub['subject_id']] = $exam_sub;
                        }
                        foreach ( $subject as $sub ) {
                            $total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
                            $marks_get        = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                            $max_marks = isset($exam_subject_lookup[$sub->subid]) ? $exam_subject_lookup[$sub->subid]['max_marks'] : 'N/A';

                            //filter students for the current subject.
                            $assigned_student_ids = array_map( 'intval', explode( ',', $sub->selected_students ) );
                            $current_student_id   = (int) $user->ID;
                            
                            if (!in_array($current_student_id, $assigned_student_ids, true ) ) {
                                continue; // Skip students not assigned to this subject.
                            }
                            if (!isset($exam_subject_lookup[$sub->subid] ) ) {
                                continue;
                            }
                            ?>
                            <tr class="mjschool-marks-table-body-row">
                                <td class="mjschool-marks-table-body-cell"><?php echo esc_html(  $sub->sub_name ); ?></td>
                                <?php
                                $obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                                if ( $contributions === 'yes' ) {
                                    $subject_total = 0;
                                    foreach ( $contributions_data_array as $con_id => $con_value ) {
                                        $mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                        $subject_total += $mark_value;
                                        ?>
                                        <td class="mjschool-marks-table-body-cell"><?php echo esc_html(  $mark_value ); ?> </td>
                                        <?php
                                    }
                                    ?>
                                    <td class="mjschool-marks-table-body-cell"><?php echo esc_html(  $subject_total ); ?></td>
                                    <?php
                                } else {
                                    ?>
                                    <td class="mjschool-marks-table-body-cell"><?php echo esc_html( $obtain_marks) ." / ". esc_html( $max_marks); ?></td>
                                    <?php
                                    $total_max_mark +=$max_marks;
                                }
                                ?>
                                <td class="mjschool-marks-table-body-cell"><?php echo esc_html(  $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
                            </tr>
                            <?php
                            ++$i;
                            // Calculate total marks.
                            if ( $contributions === 'yes' ) {
                                foreach ( $contributions_data_array as $con_id => $con_value ) {
                                    $total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                }
                            } else {
                                $total_marks += $obtain_marks;
                            }
                            $grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
                        }
                        $total         += $total_marks;
                        $GPA            = $grade_point / $total_subject;
                        if( ! empty( $total) && !empty($total_max_mark ) )
                        {
                            $percentage     = $total / $total_max_mark * 100;
                        }
                    }
                    ?>
                </tbody>
            </table>
            <table class="mjschool-summary-table-rtl" cellpadding="10" cellspacing="0">
                <thead>
                    <tr class="mjschool-summary-table-header-row">
                        <th class="mjschool-summary-table-header-cell-center"><?php esc_html_e( 'Marks Obtainable', 'mjschool' ); ?></th>
                        <th class="mjschool-summary-table-header-cell-center"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
                        <th class="mjschool-summary-table-header-cell-center"><?php esc_html_e( 'Percentage(%)', 'mjschool' ); ?></th>
                        <th class="mjschool-summary-table-header-cell-center"><?php esc_html_e( 'GPA', 'mjschool' ); ?></th>
                        <th class="mjschool-summary-table-header-cell-no-border"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="mjschool-summary-table-body-row">
                        <td class="mjschool-summary-table-body-cell"><?php echo esc_html(  $total_max_mark ); ?></td>
                        <td class="mjschool-summary-table-body-cell"><?php echo esc_html(  $total ); ?></td>
                        <td class="mjschool-summary-table-body-cell">
                            <?php
                            if ( ! empty( $percentage ) ) {
                                echo number_format( $percentage, 2, '.', '' );
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="mjschool-summary-table-body-cell"><?php echo esc_html(  round( $GPA, 2 ) ); ?></td>
                        <td class="mjschool-summary-table-body-cell-no-border">
                            <?php
                            $result = array();
                            $rest1  = array();
                            foreach ( $subject as $sub ) {
                                $obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                                if ( $contributions === 'yes' ) {
                                    $subject_total = 0;
                                    foreach ( $contributions_data_array as $con_id => $con_value ) {
                                        $mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                        $subject_total += $mark_value;
                                    }
                                    $marks_total = $subject_total;
                                } else {
                                    $marks_total = $obtain_marks;
                                }
                                if ( $marks_total >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
                                    $result[] = 'pass';
                                } else {
                                    $result1[] = 'fail';
                                }
                            }
                            if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
                                esc_html_e( 'Fail', 'mjschool' );
                            } elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
                                esc_html_e( 'Pass', 'mjschool' );
                            } elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
                                esc_html_e( 'Fail', 'mjschool' );
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="mjschool-signature-container">
                <!-- Teacher's Comment (Left Side). -->
                <div class="mjschool-signature-column-left">
                    <div class="mjschool-comment-content">
                        <strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong> <p><?php echo esc_html(  $teacher_comment ); ?></p>
                    </div>
                </div>
                <!-- Teacher Signature (Middle). -->
                <div class="mjschool-signature-column-center">
                    <?php
                    if ( ! empty( $signature_url ) ) {
                        ?>
                        <div>
                            <img src="<?php echo esc_url( $signature_url ); ?>" class="mjschool-signature-image" />
                        </div>
                        <?php
                    } else {
                        ?>
                        <div>
                            <div class="mjschool-signature-placeholder"></div>
                        </div>
                    <?php } ?>
                    <div class="mjschool-signature-line"></div>
                    <div class="mjschool_margin_top_5px"> <?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?> </div>
                </div>
                <!-- Principal Signature (Right Side). -->
                <div class="mjschool-signature-column-right">
                    
                    <div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_sign_width_100px" /> </div>
                    
                    <div class="mjschool-principal-signature-line"></div>
                    <div class="mjschool-principal-signature-label"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
                </div>
            </div>
            <div class="col-md-12 mjschool-padding-top-20 total_mjschool-padding-15px mjschool-float-width-hall">
                <div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-8 col-sm-8 col-xs-8 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
                    <div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
                        <a href="javascript:void(0)" id="download_pdf" class="btn mjschool-color-white mjschool-invoice-btn-div mjschool-save-btn">
                            <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div id="invoice-pdf" class="pdf-content-main mjschool-float-left-width-100px">
            <div class="mjschool-bottom-8px">
                <div class="mjschool-result-header">
                    <div class="mjschool-float-left-width-100pr">
                        <div class="mjschool-float-left-width-25pr">
                            <div class="mjschool-custom-logo-class mjschool-float-left-border-radius-50px">
                                <img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool-result-logo" />
                            </div>
                        </div>
                        
                        <div class="mjschool-float-left-width-75-padding-top-10px">
                            <p class="mjschool-name-style"> <?php echo esc_html(  get_option( 'mjschool_name' ) ); ?></p>
                            <p class="mjschool-address-style"> <?php echo esc_html(  get_option( 'mjschool_address' ) ); ?></p>
                            <div class="mjschool-margin0px-width-100-text-align-center">
                                <p class="mjschool-email-phone-style"> <?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html(  get_option( 'mjschool_email' ) ); ?></p>
                                <p class="mjschool-email-phone-style"> &nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html(  get_option( 'mjschool_contact_number' ) ); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mjschool-student-info-container">
                <div class="mjschool-float-left-width-100pr">
                    <div class="mjschool-info-padding">
                        <div class="mjschool-info-field-left">
                            <b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>: <?php echo esc_html(  get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html(  get_user_meta( $uid, 'last_name', true ) ); ?>
                        </div>
                        <div class="mjschool-info-field-right">
                            <b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>: <?php echo esc_html(  $obj_exam->mjschool_get_exam_name_id( $exam_id ) ); ?>
                        </div>
                    </div>
                </div>
                <div class="mjschool-info-section-left">
                    <div class="mjschool-info-padding">
                        <div class="mjschool-info-field-left">
                            <b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>: <?php echo esc_html(  get_user_meta( $uid, 'roll_id', true ) ); ?>
                        </div>
                    </div>
                </div>
                <div class="mjschool-info-section-right">
                    <?php if ( $school_type === 'school' ){ ?>
                        <div class="mjschool-info-padding-top">
                            <b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
                            <?php
                            $mjschool_class = new Mjschool_Class();
                            $classname    = $mjschool_class->mjschool_get_class_name( $class_id );
                            $section_name = ! empty( $section_id ) ? $mjschool_class->mjschool_get_section_name( $section_id ) : esc_html__( 'No Section', 'mjschool' );
                            echo esc_html(  $classname ) . ' - ' . esc_html(  $section_name );
                            ?>
                        </div>
                    <?php }elseif ( $school_type === 'university' ) {?>
                        <div class="mjschool-info-padding-top">
                            <b><?php esc_html_e( 'Class Name', 'mjschool' ); ?></b>:
                            <?php
                            $classname    = $mjschool_class->mjschool_get_class_name( $class_id );
                            echo esc_html(  $classname );
                            ?>
                        </div>
                    <?php }?>
                </div>
            </div>
            <table class="mjschool-marks-table-ltr" cellpadding="10" cellspacing="0">
                <thead>
                    <?php
                    $exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
                    $exam_marks    = $exam_data->total_mark;
                    $contributions = $exam_data->contributions;
                    if ( $contributions === 'yes' ) {
                        $contributions_data       = $exam_data->contributions_data;
                        $contributions_data_array = json_decode( $contributions_data, true );
                    }
                    ?>
                    <tr class="mjschool-marks-table-header-row-ltr">
                        <th class="mjschool-marks-table-header-cell-left"> <?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
                        <?php
                        if ( $contributions === 'yes' ) {
                            foreach ( $contributions_data_array as $con_id => $con_value ) {
                                ?>
                                <th class="mjschool-marks-table-header-cell-left"> <?php echo esc_html(  $con_value['label'] ) . ' ( ' . esc_html(  $con_value['mark'] ) . ' )'; ?></th>
                                <?php
                            }
                            ?>
                            <th class="mjschool-marks-table-header-cell-left"> <?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html(  $exam_marks ) . ' )'; ?></th>
                            <?php
                        } else {
                            if ( $school_type === 'school' ){
                                ?>
                                <th class="mjschool-marks-table-header-cell-left"><?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html(  $exam_marks ) . ' )'; ?></th>
                                <?php
                            }elseif ( $school_type === 'university' ){
                                ?>
                                <th class="mjschool-marks-table-header-cell-left"><?php esc_html_e( 'Total', 'mjschool' ); ?></th>
                                <?php
                            }
                        }
                        ?>
                        <th class="mjschool-marks-table-header-cell-left"> <?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ( $school_type === 'school' ){
                        $i               = 1;
                        $total_pass_mark = 0;
                        $total_max_mark  = 0;
                        $total_marks     = 0;
                        foreach ( $subject as $sub ) {
                            $total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
                            $marks_get        = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                            ?>
                            <tr class="mjschool-marks-table-body-row-ltr">
                                <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html(  $sub->sub_name ); ?></td>
                                <?php
                                $obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                                if ( $contributions === 'yes' ) {
                                    $subject_total = 0;
                                    foreach ( $contributions_data_array as $con_id => $con_value ) {
                                        $mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                        $subject_total += $mark_value;
                                        ?>
                                        <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html(  $mark_value ); ?> </td>
                                        <?php
                                    }
                                    ?>
                                    <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html(  $subject_total ); ?></td>
                                    <?php
                                } else {
                                    ?>
                                    <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html(  $obtain_marks ); ?></td>
                                    <?php
                                }
                                ?>
                                <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html(  $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
                            </tr>
                            <?php
                            ++$i;
                            // Calculate total marks.
                            if ( $contributions === 'yes' ) {
                                foreach ( $contributions_data_array as $con_id => $con_value ) {
                                    $total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                }
                            } else {
                                $total_marks += $obtain_marks;
                            }
                            $grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
                        }
                        $total         += $total_marks;
                        $total_max_mark = $exam_marks * $total_subject;
                        $GPA            = $grade_point / $total_subject;
                        if( ! empty( $total) && !empty($total_max_mark ) )
                        {
                            $percentage     = $total / $total_max_mark * 100;
                        }
                    }elseif ( $school_type == "university"){
                        $i               = 1;
                        $total_pass_mark = 0;
                        $total_max_mark  = 0;
                        $total_marks     = 0;
                        $exam_subject_data = json_decode($exam_data->subject_data,true);
                        $exam_subject_lookup = [];
                        foreach ($exam_subject_data as $exam_sub) {
                            $exam_subject_lookup[$exam_sub['subject_id']] = $exam_sub;
                        }
                        foreach ( $subject as $sub ) {
                            $total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
                            $marks_get        = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                            $max_marks = isset($exam_subject_lookup[$sub->subid]) ? $exam_subject_lookup[$sub->subid]['max_marks'] : 'N/A';

                            //filter students for the current subject.
                            $assigned_student_ids = array_map( 'intval', explode( ',', $sub->selected_students ) );
                            $current_student_id   = (int) $user->ID;
                            
                            if (!in_array($current_student_id, $assigned_student_ids, true ) ) {
                                continue; // Skip students not assigned to this subject.
                            }
                            if (!isset($exam_subject_lookup[$sub->subid] ) ) {
                                continue;
                            }
                            ?>
                            <tr class="mjschool-marks-table-body-row-ltr">
                                <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html(  $sub->sub_name ); ?></td>
                                <?php
                                $obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                                if ( $contributions === 'yes' ) {
                                    $subject_total = 0;
                                    foreach ( $contributions_data_array as $con_id => $con_value ) {
                                        $mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                        $subject_total += $mark_value;
                                        ?>
                                        <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html(  $mark_value ); ?> </td>
                                        <?php
                                    }
                                    ?>
                                    <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html(  $subject_total ); ?></td>
                                    <?php
                                } else {
                                    ?>
                                    <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html( $obtain_marks) ." / ". esc_html( $max_marks); ?></td>
                                    <?php
                                    $total_max_mark +=$max_marks;
                                }
                                ?>
                                <td class="mjschool-marks-table-body-cell-ltr"><?php echo esc_html(  $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
                            </tr>
                            <?php
                            ++$i;
                            // Calculate total marks.
                            if ( $contributions === 'yes' ) {
                                foreach ( $contributions_data_array as $con_id => $con_value ) {
                                    $total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                }
                            } else {
                                $total_marks += $obtain_marks;
                            }
                            $grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
                        }
                        $total         += $total_marks;
                        $GPA            = $grade_point / $total_subject;
                        if( ! empty( $total) && !empty($total_max_mark ) )
                        {
                            $percentage     = $total / $total_max_mark * 100;
                        }
                    }
                    ?>
                </tbody>
            </table>
            <table class="mjschool-summary-table-ltr" cellpadding="10" cellspacing="0">
                <thead>
                    <tr class="mjschool-summary-table-header-row-ltr">
                        <th class="mjschool-summary-table-header-cell-center-ltr"><?php esc_html_e( 'Marks Obtainable', 'mjschool' ); ?></th>
                        <th class="mjschool-summary-table-header-cell-center-ltr"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
                        <th class="mjschool-summary-table-header-cell-center-ltr"><?php esc_html_e( 'Percentage(%)', 'mjschool' ); ?></th>
                        <th class="mjschool-summary-table-header-cell-center-ltr"><?php esc_html_e( 'GPA', 'mjschool' ); ?></th>
                        <th class="mjschool-summary-table-header-cell-no-border-ltr"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="mjschool-summary-table-body-row-ltr">
                        <td class="mjschool-summary-table-body-cell-ltr"><?php echo esc_html(  $total_max_mark ); ?> </td>
                        <td class="mjschool-summary-table-body-cell-ltr"><?php echo esc_html(  $total ); ?></td>
                        <td class="mjschool-summary-table-body-cell-ltr">
                            <?php
                            if ( ! empty( $percentage ) ) {
                                echo number_format( $percentage, 2, '.', '' );
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="mjschool-summary-table-body-cell-ltr"><?php echo esc_html(  round( $GPA, 2 ) ); ?> </td>
                        <td class="mjschool-summary-table-body-cell-no-border-ltr">
                            <?php
                            if ( $school_type !== 'university' )
                            {
                                $result = array();
                                $rest1  = array();
                                foreach ( $subject as $sub ) {
                                    $obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
                                    if ( $contributions === 'yes' ) {
                                        $subject_total = 0;
                                        foreach ( $contributions_data_array as $con_id => $con_value ) {
                                            $mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
                                            $subject_total += $mark_value;
                                        }
                                        $marks_total = $subject_total;
                                    } else {
                                        $marks_total = $obtain_marks;
                                    }
                                    if ( $marks_total >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
                                        $result[] = 'pass';
                                    } else {
                                        $result1[] = 'fail';
                                    }
                                }
                                if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
                                    esc_html_e( 'Fail', 'mjschool' );
                                } elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
                                    esc_html_e( 'Pass', 'mjschool' );
                                } elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
                                    esc_html_e( 'Fail', 'mjschool' );
                                } else {
                                    echo '-';
                                }
                            }elseif ( $school_type === 'university' ){
                                $result = array();
                                $rest1  = array();
                                foreach ( $subject as $sub ) {
                                    $obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid ) ?? 0;
                                    if ( isset( $exam_subject_lookup[ $sub->subid ]['passing_marks'] ) && $obtain_marks >= $exam_subject_lookup[ $sub->subid ]['passing_marks'] ) {
                                        $result[] = 'pass';
                                    } else {
                                        $result1[] = 'fail';
                                    }
                                }
                                if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
                                    esc_html_e( 'Fail', 'mjschool' );
                                } elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
                                    esc_html_e( 'Pass', 'mjschool' );
                                } elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
                                    esc_html_e( 'Fail', 'mjschool' );
                                } else {
                                    echo '-';
                                }
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="mjschool-signature-container">
                <!-- Teacher's Comment (Left Side) -->
                <div class="mjschool-signature-column-left">
                    <div class="mjschool-comment-content">
                        <strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong> <p><?php echo esc_html(  $teacher_comment ); ?></p>
                    </div>
                </div>
                <!-- Teacher Signature (Middle) -->
                <div class="mjschool-signature-column-center">
                    <?php
                    if ( ! empty( $signature_url ) ) {
                        ?>
                        <div>
                            <img src="<?php echo esc_url( $signature_url ); ?>" class="mjschool-signature-image" />
                        </div>
                        <?php
                    } else {
                        ?>
                        <div>
                            <div class="mjschool-signature-placeholder"></div>
                        </div>
                    <?php } ?>
                    <div class="mjschool-signature-line"></div>
                    <div class="mjschool_margin_top_5px"> <?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?> </div>
                </div>
                <!-- Principal Signature (Right Side) -->
                <div class="mjschool-signature-column-right">
                    
                    <div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_sign_width_100px" /> </div>
                    
                    <div class="mjschool-principal-signature-line"></div>
                    <div class="mjschool-principal-signature-label"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 mjschool-padding-top-20 total_mjschool-padding-15px mjschool-float-width-hall">
                <div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-8 col-sm-8 col-xs-8 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
                    <div class="col-md-2 mjschool-print-btn-rs mjschool-width-50-res mjschool-width-13per">
                        <a href="#" student_id="<?php echo esc_js(mjschool_encrypt_id($uid ) ); ?>" class_id="<?php echo esc_js(mjschool_encrypt_id($class_id ) ); ?>" section_id="<?php echo esc_js(mjschool_encrypt_id($section_id ) ); ?>" exam_id="<?php echo esc_js(mjschool_encrypt_id($exam_id ) ); ?>" typeformat="print" class="btn mjschool-color-white btn mjschool-save-btn mjschool-invoice-btn-div">
                            <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-print.png"); ?>"> 
                        </a>
                    </div>
                    <div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
                        <a href="javascript:void(0)" id="download_result_pdf" class="btn mjschool-color-white mjschool-invoice-btn-div mjschool-save-btn">
                            <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>">
                        </a>
                    </div>
                </div>
            </div>
        <?php
    }
}
?>