<?php
/**
 * Exam Hall Ticket View.
 *
 * This file renders the Examination Hall Ticket (Admit Card) for a student.
 * It displays student details, exam details, examination centre information,
 * and the complete exam timetable in a printable layout.
 *
 * Key Features:
 * - Secure handling of request parameters (student_id, exam_id)
 * - Decryption and validation of IDs before use
 * - Student profile image handling with fallback image
 * - Dynamic exam timetable generation
 * - Exam hall and centre information display
 * - Print and PDF download support
 * - Conditional mobile app PDF handling
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/student
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$student_id      = sanitize_text_field( $_REQUEST['student_id'] );
$exam_id         = sanitize_text_field( $_REQUEST['exam_id'] );
$student_id      = intval( mjschool_decrypt_id( $student_id ) );
$exam_id         = intval( mjschool_decrypt_id( $exam_id ) );
$student_data    = get_userdata( $student_id );
$umetadata       = mjschool_get_user_image( $student_id );
$exam_data       = mjschool_get_exam_by_id( $exam_id );	
$exam_hall_data  = mjschool_get_exam_hall_name( $student_id, $exam_id );
$exam_hall_name  = mjschool_get_hall_name( $exam_hall_data->hall_id );
$obj_exam        = new mjschool_exam();
$exam_time_table = $obj_exam->mjschool_get_exam_time_table_by_exam( $exam_id );
$obj_subject = new Mjschool_Subject();
if ( is_rtl() ) {
    ?>
    <div class="modal-body mjschool_direction_rtl">
        <div id="exam_receipt_print" class="exam_receipt_print">
            <div class="mjschool_margin_bottom_8px">
                <div class="mjschool-width-print-hall mjschool_border_2px_width_96" >
                    <div class="mjschool_float_left_width_100">
                        <div class="mjschool_float_left_width_25">
                            <div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
                                <img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
                            </div>
                        </div>
                        <div class="mjschool_float_left_width_75">
                            <p class="mjschool_fees_widht_100_fonts_24px">
                                <?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
                            </p>
                            <p class="mjschool_fees_center_fonts_17px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?> </p>
                            <div class="mjschool_fees_center_margin_0px">
                                <p class="mjschool_fees_width_fit_content_inline">
                                    <?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
                                </p>
                                <p class="mjschool_fees_width_fit_content_inline">
                                    &nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mjschool-header-hall mjschool-Examination-header mjschool-margin-top-10px" >
                <span><strong class="mjschool-Examination-header-color"><?php esc_html_e( 'Examination Hall Ticket', 'mjschool' ); ?></strong></span>
            </div>
            <div class="mjschool-float-width-hall">
                <table width="100%" class="count borderpx mjschool-hall-stud-details" cellspacing="0" cellpadding="0">
                    <thead>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="4" class="mjschool-img-td">
                                <?php
                                if (empty($umetadata['meta_value'] ) ) { ?>
                                    <img src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>" width="100px" height="100px">
                                    <?php
                                } else {
                                    ?>
                                    <img src="<?php echo esc_url($umetadata['meta_value']); ?>" width="100px" height="100px">
                                    <?php
                                }
                                ?>
                            </td>
                            
                            <td colspan="2" class="mjschool-border-bottom"> <strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->display_name ); ?></a> </td>
                        </tr>
                        <tr>
                            <td class="mjschool-border-bottom-rigth" align="left"> <strong><?php esc_html_e( 'Roll Number', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->roll_id ); ?> </td>
                            <td class="mjschool-border-bottom" align="left"> <strong><?php esc_html_e( 'Exam Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $exam_data->exam_name ); ?> </td>
                        </tr>
                        <tr>
                            <td class="mjschool-border-bottom-rigth" align="left"> <strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></strong><?php echo esc_html( mjschool_get_class_name( $student_data->class_name ) ); ?> </td>
                            <td class="mjschool-border-bottom" align="left">
                                <strong><?php esc_html_e( 'Section Name', 'mjschool' ); ?> : </strong>
                                <?php
                                $section_name = $student_data->class_section;
                                if ( $section_name != '' ) {
                                    echo esc_html( mjschool_get_section_name( $section_name ) );
                                } else {
                                    esc_html_e( 'No Section', 'mjschool' );
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="mjschool-border-rigth" align="left"> <strong><?php esc_html_e( 'Start Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) ); ?> </td>
                            <td class="mjschool-border-bottom-0" align="left"> <strong><?php esc_html_e( 'End Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) ); ?> </td>
                        </tr>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
            <div class="mjschool-padding-top-20 mjschool-float-width-hall">
                <table width="100%" class="count borderpx mjschool-hall-stud-details" cellspacing="0" cellpadding="0">
                    <thead>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="mjschool-border-bottom">
                                <strong><?php esc_html_e( 'Examination Centre', 'mjschool' ); ?> : </strong>
                                <?php echo esc_html( $exam_hall_name ); ?>,
                                <?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="mjschool-border-bottom-0">
                                <strong><?php esc_html_e( 'Examination Centre Address', 'mjschool' ); ?> : </strong><?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
            <div class="mjschool-padding-top-20 mjschool-float-width-hall">
                <table width="100%" class="count borderpx mjschool-hall-stud-details" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th colspan="5" class="mjschool-border-bottom"> <?php esc_html_e( 'Time Table For Exam Hall', 'mjschool' ); ?> </th>
                        </tr>
                        <tr>
                            <th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
                            <th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
                            <th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></th>
                            <th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php esc_html_e( 'Exam Time', 'mjschool' ); ?></th>
                            <th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php esc_html_e( 'Examiner Sign.', 'mjschool' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ( ! empty( $exam_time_table ) ) {
                            foreach ( $exam_time_table as $retrieved_data ) {
                                ?>
                                <tr>
                                    <td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php echo esc_html( $obj_subject->mjschool_get_single_subject_code( $retrieved_data->subject_id ) ); ?></td>
                                    <td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject_id ) ); ?></td>
                                    <td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_date ) ); ?></td>
                                    <?php
                                    $start_time_data = explode( ':', $retrieved_data->start_time );
                                    $start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
                                    $start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
                                    $start_am_pm     = $start_time_data[2];
                                    $start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
                                    $end_time_data   = explode( ':', $retrieved_data->end_time );
                                    $end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
                                    $end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
                                    $end_am_pm       = $end_time_data[2];
                                    $end_time        = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
                                    ?>
                                    <td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall">
                                        <?php echo esc_html( $start_time ); ?>
                                        <?php esc_html_e( 'To', 'mjschool' ); ?>
                                        <?php echo esc_html( $end_time ); ?>
                                    </td>
                                    <td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td class="mjschool-main-td" colspan="5"> <?php esc_html_e( 'No Data Available', 'mjschool' ); ?> </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
            <div class="resultdate">
                <hr color="#97C4E7">
                <span><?php esc_html_e( 'Student Signature', 'mjschool' ); ?></span>
            </div>
            <div class="signature">
                <span> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool-width-100px-margin-right-15px" /> </span>
                <hr color="#97C4E7">
                <span><?php esc_html_e( 'Authorized Signature', 'mjschool' ); ?></span>
            </div>
        </div>
    </div>
    <!---RTL ENDS-->
    <?php
} else {
    ?>
    <div class="modal-body">
        <div id="exam_receipt_print" class="exam_receipt_print">
            <div class="mjschool_margin_bottom_8px">
                <div class="mjschool-width-print-hall mjschool_border_2px_width_96" >
                    <div class="mjschool_float_left_width_100">
                        <div class="mjschool_float_left_width_25">
                            <div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
                                <img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ) ?>" class="mjschool_main_logo_class" />
                            </div>
                        </div>
                        <div class="mjschool_float_left_width_75">
                            <p class="mjschool_fees_widht_100_fonts_24px"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
                            <p class="mjschool_fees_center_fonts_17px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
                            <div class="mjschool_fees_center_margin_0px">
                                <p class="mjschool_fees_width_fit_content_inline">
                                    <?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
                                </p>
                                <p class="mjschool_fees_width_fit_content_inline">
                                    &nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mjschool-header-hall mjschool-Examination-header mjschool-margin-top-10px">
                <span><strong class="mjschool-Examination-header-color"><?php esc_html_e( 'Examination Hall Ticket', 'mjschool' ); ?></strong></span>
            </div>
            <div class="mjschool-float-width-hall">
                <table width="100%" class="count borderpx mjschool-hall-stud-details" cellspacing="0" cellpadding="0">
                    <thead>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="4" class="mjschool-img-td">
                                <?php
                                if (empty($umetadata ) ) { ?>
                                    <img src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); ?>" width="100px" height="100px">
                                    <?php
                                } else {
                                    ?>
                                    <img src="<?php echo esc_url($umetadata); ?>" width="100px" height="100px">
                                    <?php
                                }
                                ?>
                            </td>
                            
                            <td colspan="2" class="mjschool-border-bottom"> <strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->display_name ); ?></a> </td>
                        </tr>
                        <tr>
                            <td class="mjschool-border-bottom-rigth" align="left"> <strong><?php esc_html_e( 'Roll Nunmber', 'mjschool' ); ?> : </strong><?php echo esc_html( $student_data->roll_id ); ?> </td>
                            <td class="mjschool-border-bottom" align="left"> <strong><?php esc_html_e( 'Exam Name', 'mjschool' ); ?> : </strong><?php echo esc_html( $exam_data->exam_name ); ?> </td>
                        </tr>
                        <tr>
                            <td class="mjschool-border-bottom-rigth" align="left">
                                <strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_class_name( $student_data->class_name ) ); ?>
                            </td>
                            <td class="mjschool-border-bottom" align="left">
                                <strong><?php esc_html_e( 'Section Name', 'mjschool' ); ?> : </strong>
                                <?php
                                $section_name = $student_data->class_section;
                                if ( $section_name != '' ) {
                                    echo esc_html( mjschool_get_section_name( $section_name ) );
                                } else {
                                    esc_html_e( 'No Section', 'mjschool' );
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="mjschool-border-rigth" align="left">
                                <strong><?php esc_html_e( 'Start Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) ); ?>
                            </td>
                            <td class="mjschool-border-bottom-0" align="left">
                                <strong><?php esc_html_e( 'End Date', 'mjschool' ); ?> : </strong><?php echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) ); ?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
            <div class="mjschool-padding-top-20 mjschool-float-width-hall">
                <table width="100%" class="count borderpx mjschool-hall-stud-details" cellspacing="0" cellpadding="0">
                    <thead>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="mjschool-border-bottom">
                                <strong><?php esc_html_e( 'Examination Centre', 'mjschool' ); ?> : </strong>
                                <?php echo esc_html( $exam_hall_name ); ?>,
                                <?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="mjschool-border-bottom-0">
                                <strong><?php esc_html_e( 'Examination Centre Address', 'mjschool' ); ?> : </strong><?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
            <div class="mjschool-padding-top-20 mjschool-float-width-hall">
                <table width="100%" class="count borderpx mjschool-hall-stud-details mjschool-bottom-none" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th colspan="5" class="mjschool-border-bottom"> <?php esc_html_e( 'Time Table For Exam Hall', 'mjschool' ); ?> </th>
                        </tr>
                        <tr>
                            <th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
                            <th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
                            <th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></th>
                            <th class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php esc_html_e( 'Exam Time', 'mjschool' ); ?></th>
                            <th class="mjschool-main-td  mjschool-th-margin-hall"><?php esc_html_e( 'Examiner Sign.', 'mjschool' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ( ! empty( $exam_time_table ) ) {
                            foreach ( $exam_time_table as $retrieved_data ) {
                                ?>
                                <tr>
                                    <td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php echo esc_html( $obj_subject->mjschool_get_single_subject_code( $retrieved_data->subject_id ) ); ?></td>
                                    <td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject_id ) ); ?></td>
                                    <td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_date ) ); ?></td>
                                    <?php
                                    $start_time_data = explode( ':', $retrieved_data->start_time );
                                    $start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
                                    $start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
                                    $start_am_pm     = $start_time_data[2];
                                    $start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
                                    $end_time_data   = explode( ':', $retrieved_data->end_time );
                                    $end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
                                    $end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
                                    $end_am_pm       = $end_time_data[2];
                                    $end_time        = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
                                    ?>
                                    <td class="mjschool-main-td mjschool-border-rigth mjschool-th-margin-hall">
                                        <?php echo esc_html( $start_time ); ?>
                                        <?php esc_html_e( 'To', 'mjschool' ); ?>
                                        <?php echo esc_html( $end_time ); ?>
                                    </td>
                                    <td class="mjschool-main-td  mjschool-th-margin-hall"></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td class="mjschool-main-td" colspan="5"> <?php esc_html_e( 'No Data Available', 'mjschool' ); ?> </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
            <div class="resultdate">
                <hr color="" class="mjschool-border-2px-solid">
                <span><?php esc_html_e( 'Student Signature', 'mjschool' ); ?></span>
            </div>
            <div class="signature">
                <span> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool-width-100px-margin-right-15px" /> </span>
                <hr color="" class="mjschool-border-2px-solid">
                <span><?php esc_html_e( 'Authorized Signature', 'mjschool' ); ?></span>
            </div>
            <!-- Print PDF Button. -->
            <div class="col-md-12 mjschool-padding-top-20 total_mjschool-padding-15px mjschool-float-width-hall">
                <div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-8 col-sm-8 col-xs-8 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
                    <div class="col-md-2 mjschool-print-btn-rs mjschool-width-50-res mjschool-width-13per">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&student_exam_receipt=student_exam_receipt&student_id='.rawurlencode( mjschool_encrypt_id( $student_id ) ).'&exam_id='.rawurlencode( mjschool_encrypt_id( $exam_id ) ) ) ); ?>" target="_blank" class="btn mjschool-color-white btn mjschool-save-btn mjschool-invoice-btn-div"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-print.png"); ?>"> </a>
                    </div>
                    <?php
                    if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
                        if ( isset( $_POST['download_app_pdf'] ) ) {
                            $file_path = content_url() . '/uploads/invoice_pdf/income/' . mjschool_decrypt_id( $idtest ) . '.pdf';
                            if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
                                unlink( $file_path ); // Delete the file.
                            }
                            $generate_pdf = mjschool_fees_income_pdf_for_mobile_app( $idtest, $invoice_type );
                            wp_safe_redirect( $file_path );
                            die();
                        }
                        ?>
                        <div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
                            <form name="app_pdf1" action="" method="post">
                                <div class="form-body mjschool-user-form mjschool-margin-top-40px">
                                    <div class="row mjschool-invoice-print-pdf-btn">
                                        <div class="col-md-1 mjschool-print-btn-rs">
                                            <button data-toggle="tooltip" name="download_app_pdf" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-pdf.png"); ?>"></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&student_exam_receipt_pdf=student_exam_receipt_pdf&student_id='.rawurlencode( mjschool_encrypt_id( $student_id ) ).'&exam_id='.rawurlencode( mjschool_encrypt_id( $exam_id ) ) ) ); ?>" target="_blank" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-pdf.png"); ?>"></a>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php
}
?>