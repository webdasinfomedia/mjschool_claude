<?php
/**
 * Virtual Classroom - Past Participants List.
 *
 * Displays the list of participants who have attended a specific Zoom meeting 
 * retrieved via the MJSchool Virtual Classroom module. Each participant’s 
 * name and email address are shown in a responsive DataTable with alternating 
 * color styles for better readability.
 *
 * Features:
 * - Fetches past participant data using the meeting UUID.
 * - Displays participant name, email, and profile image.
 * - Applies dynamic color classes for table row variation.
 * - Includes tooltip hints for accessibility and better UI clarity.
 * - Shows a “No Data” placeholder when no participants are found.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/virtual-classroom
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
$past_participle_list = $obj_virtual_classroom->mjschool_view_past_participle_list_in_zoom(sanitize_text_field(wp_unslash($_REQUEST['meeting_uuid'])));
if (! empty($past_participle_list->participants) ) {
    ?>
    <div class="mjschool-panel-body">
        <form id="mjschool-common-form" name="mjschool-common-form" method="post">
            <div class="table-responsive">
                <table id="past_participle_list" class="display datatable" cellspacing="0" width="100%">
                    <thead class="<?php echo esc_attr(mjschool_datatable_header()); ?>">
                        <th><?php esc_html_e('Image', 'mjschool'); ?></th>
                        <th><?php esc_html_e('Name', 'mjschool'); ?></th>
                        <th><?php esc_html_e('Email', 'mjschool'); ?></th>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        foreach ( $past_participle_list->participants as $retrieved_data ) {
                            if ($i === 10 ) {
                                $i = 0;
                            }
                            if ($i === 0 ) {
                                $color_class_css = 'mjschool-class-color0';
                            } elseif ($i === 1 ) {
                                $color_class_css = 'mjschool-class-color1';
                            } elseif ($i === 2 ) {
                                $color_class_css = 'mjschool-class-color2';
                            } elseif ($i === 3 ) {
                                $color_class_css = 'mjschool-class-color3';
                            } elseif ($i === 4 ) {
                                $color_class_css = 'mjschool-class-color4';
                            } elseif ($i === 5 ) {
                                $color_class_css = 'mjschool-class-color5';
                            } elseif ($i === 6 ) {
                                $color_class_css = 'mjschool-class-color6';
                            } elseif ($i === 7 ) {
                                $color_class_css = 'mjschool-class-color7';
                            } elseif ($i === 8 ) {
                                $color_class_css = 'mjschool-class-color8';
                            } elseif ($i === 9 ) {
                                $color_class_css = 'mjschool-class-color9';
                            }
                            ?>
                            <tr>
                                <td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
                                    <p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">    
                                        <img src="<?php echo esc_url(MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/White_Icons/Virtual_class.png")?>" class="mjschool-massage-image">
                                    </p>
                                </td>
                                <td><?php echo esc_html($retrieved_data->name);?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e('Name', 'mjschool');?>"></i></td>
                                <td><?php echo esc_html($retrieved_data->user_email);?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e('Email', 'mjschool');?>"></i></td>
                            </tr>
                            <?php 
                            $i++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <?php 
}
else{
    ?>
    <div class="mjschool-calendar-event-new"> 
        <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG);?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
    </div>    
    <?php 
} ?>
