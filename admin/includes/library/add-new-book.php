<?php
/**
 * Library Book Management Interface.
 *
 * Manages the backend functionality for adding, editing, and viewing book records
 * within the MJSchool plugin. This file provides an intuitive admin interface
 * for managing library books along with metadata such as category, rack location,
 * ISBN, publisher, author, and quantity details.
 *
 * Key Features:
 * - Supports adding and editing book records with validation and sanitization.
 * - Integrates WordPress nonce for secure data submission.
 * - Fetches book categories and rack locations dynamically using taxonomy-based selection.
 * - Handles encrypted book IDs for secure edit operations.
 * - Includes support for custom fields specific to the library module.
 * - Provides role-based access and AJAX-driven select elements.
 * - Implements input validation rules for numeric, text, and date fields.
 * - Ensures localized and translatable UI text using WordPress i18n functions.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/library
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$mjschool_obj_lib = new Mjschool_Library();
$bookid           = 0;
if ( isset( $_REQUEST['book_id'] ) ) {
    $bookid = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['book_id'] ) ) ) );
}
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'] ) ) === 'edit' ) {
    $edit   = 1;
    $result = $mjschool_obj_lib->mjschool_get_single_books( $bookid );
}
?>
<div class="mjschool-panel-body"><!--mjschool-panel-body. -->
    <form name="book_form" action="" method="post" class="mjschool-form-horizontal" id="book_form" enctype="multipart/form-data">
        <?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
        <input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
        <input type="hidden" name="book_id" value="<?php echo esc_attr( $bookid ); ?>">
        <div class="header">
            <h3 class="mjschool-first-header"><?php esc_html_e( 'BooK Information', 'mjschool' ); ?></h3>
        </div>
        <div class="form-body mjschool-user-form">
            <div class="row">
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="book_name" class="form-control validate[required] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( wp_unslash( $result->book_name ) );} ?>" name="book_name">
                            <label for="book_name"><?php esc_html_e( 'Book Title', 'mjschool' ); ?><span class="mjschool-require-field"><span class="mjschool-require-field">*</span></span></label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="book_number" class="form-control text-input" maxlength="10" type="number" value="<?php if ( $edit ) { echo esc_attr( wp_unslash( $result->book_number ) ); }?>" name="book_number">
                            <label for="book_number"><?php esc_html_e( 'Book Number', 'mjschool' ); ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="isbn" class="form-control validate[required,custom[address_description_validation]]" type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $result->ISBN ); }?>" name="isbn">
                            <label for="isbn"><?php esc_html_e( 'ISBN Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="publisher" class="form-control validate[required,custom[city_state_country_validation]] text-input" type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( wp_unslash( $result->publisher ) ); }?>" name="publisher">
                            <label for="publisher"><?php esc_html_e( 'Publisher', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                        </div>
                    </div>
                </div>
                <?php wp_nonce_field( 'save_book_admin_nonce' ); ?>
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="author_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( wp_unslash( $result->author_name ) ); }?>" name="author_name">
                            <label for="author_name"><?php esc_html_e( 'Author Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
                    <label class="ml-1 mjschool-custom-top-label top" for="category_data"><?php esc_html_e( 'Select Category', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                    <select name="bookcat_id" id="category_data" class="form-control smgt_bookcategory validate[required] mjschool-max-width-100px">
                        <option value=""><?php esc_html_e( 'Select Category', 'mjschool' ); ?></option>
                        <?php
                        $activity_category = mjschool_get_all_category( 'smgt_bookcategory' );
                        if ( ! empty( $activity_category ) ) {
                            if ( $edit ) {
                                $fees_val = $result->cat_id;
                            } else {
                                $fees_val = '';
                            }
                            foreach ( $activity_category as $retrive_data ) {
                        		?>
                                <option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $fees_val ); ?>><?php echo esc_html( $retrive_data->post_title ); ?></option>
                        		<?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3">
                    <button id="mjschool-addremove-cat" class="mjschool-rtl-margin-top-15px mjschool-add-btn sibling_add_remove" model="smgt_bookcategory"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="book_price" class="form-control validate[required,min[0],maxSize[8]]" type="number" step="0.01" value="<?php if ( $edit ) { echo esc_attr( $result->price ); }?>" name="book_price">
                            <label class="c" for="book_price"><?php echo esc_html__( 'Price', 'mjschool' ) . '( ' . esc_html( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) ) . ' )'; ?><span class="mjschool-require-field">*</span></label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
                    <label class="ml-1 mjschool-custom-top-label top" for="category_data"><?php esc_html_e( 'Rack Location', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                    <select name="rack_id" id="rack_category_data" class="form-control smgt_rack validate[required] mjschool-max-width-100px">
                        <option value=""><?php esc_html_e( 'Select Rack Location', 'mjschool' ); ?></option>
                        <?php
                        $activity_category = mjschool_get_all_category( 'smgt_rack' );
                        if ( ! empty( $activity_category ) ) {
                            if ( $edit ) {
                                $rank_val = $result->rack_location;
                            } else {
                                $rank_val = '';
                            }
                            foreach ( $activity_category as $retrive_data ) {
                        		?>
                                <option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $rank_val ); ?>><?php echo esc_html( $retrive_data->post_title ); ?> </option>
                        		<?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3">
                    <button id="mjschool-addremove-cat" class="mjschool-rtl-margin-top-15px mjschool-add-btn sibling_add_remove" model="smgt_rack"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="quentity" class="form-control validate[required,min[0],maxSize[5]]" type="number" value="<?php if ( $edit ) { echo esc_attr( $result->quentity ); }?>" name="quentity">
                            <label for="quentity"><?php esc_html_e( 'Total Quantity', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="post_date" class="datepicker form-control validate[required] text-input" type="text" name="post_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $result->added_date ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" readonly>
                            <label for="post_date"><?php esc_html_e( 'Post Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mjschool-note-text-notice">
                    <div class="form-group input">
                        <div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
                            <div class="form-field">
                                <textarea id="description" name="description" class="mjschool-textarea-height-47px validate[custom[description_validation]] form-control"><?php if ( $edit ) { echo esc_textarea( $result->description ); } ?></textarea>
                                <span class="mjschool-txt-title-label"></span>
                                <label class="text-area address active" for="description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // Get Module-Wise Custom Field Data.
        $mjschool_custom_field_obj = new Mjschool_Custome_Field();
        $module                    = 'library';
        $custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback($module);
        ?>
        <div class="form-body mjschool-user-form">
            <div class="row">
                <div class="col-sm-6">
                    <input type="submit" value="<?php if ($edit) { esc_attr_e( 'Save Book', 'mjschool' ); } else { esc_attr_e( 'Add Book', 'mjschool' ); } ?>" name="save_book" class="btn btn-success mjschool-save-btn" />
                </div>
            </div>
        </div>
    </form>
</div><!--mjschool-panel-body. -->