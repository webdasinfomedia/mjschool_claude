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

// Initialize library object.
$mjschool_obj_lib = new Mjschool_Library();

// Initialize variables with defaults.
$bookid = 0;
$edit   = false;
$result = null;

// Process book ID if provided.
if ( isset( $_REQUEST['book_id'] ) && ! empty( $_REQUEST['book_id'] ) ) {
    $encrypted_id = sanitize_text_field( wp_unslash( $_REQUEST['book_id'] ) );
    $decrypted_id = mjschool_decrypt_id( $encrypted_id );
    
    if ( false !== $decrypted_id && is_numeric( $decrypted_id ) ) {
        $bookid = intval( $decrypted_id );
    }
}

// Check if this is an edit action.
if ( isset( $_REQUEST['action'] ) && 'edit' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) {
    if ( $bookid > 0 ) {
        $result = $mjschool_obj_lib->mjschool_get_single_books( $bookid );
        
        if ( ! empty( $result ) && is_object( $result ) ) {
            $edit = true;
        } else {
            // Book not found - display error and exit.
            wp_die(
                esc_html__( 'The requested book could not be found.', 'mjschool' ),
                esc_html__( 'Book Not Found', 'mjschool' ),
                array( 'back_link' => true )
            );
        }
    } else {
        // Invalid book ID.
        wp_die(
            esc_html__( 'Invalid book ID provided.', 'mjschool' ),
            esc_html__( 'Invalid Request', 'mjschool' ),
            array( 'back_link' => true )
        );
    }
}

// Determine form action.
$mjschool_action = $edit ? 'edit' : 'insert';

// Pre-fetch categories to avoid duplicate queries.
$book_categories = mjschool_get_all_category( 'smgt_bookcategory' );
$rack_locations  = mjschool_get_all_category( 'smgt_rack' );

// Helper function to render category dropdown options.
function mjschool_render_category_options( $categories, $selected_value = '' ) {
    if ( empty( $categories ) || ! is_array( $categories ) ) {
        return;
    }
    
    foreach ( $categories as $category ) {
        if ( ! isset( $category->ID, $category->post_title ) ) {
            continue;
        }
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr( $category->ID ),
            selected( $category->ID, $selected_value, false ),
            esc_html( $category->post_title )
        );
    }
}
// Get values for edit mode with null safety.
$book_name    = $edit && isset( $result->book_name ) ? wp_unslash( $result->book_name ) : '';
$book_number  = $edit && isset( $result->book_number ) ? wp_unslash( $result->book_number ) : '';
$isbn         = $edit && isset( $result->ISBN ) ? $result->ISBN : '';
$publisher    = $edit && isset( $result->publisher ) ? wp_unslash( $result->publisher ) : '';
$author_name  = $edit && isset( $result->author_name ) ? wp_unslash( $result->author_name ) : '';
$cat_id       = $edit && isset( $result->cat_id ) ? $result->cat_id : '';
$book_price   = $edit && isset( $result->price ) ? $result->price : '';
$rack_id      = $edit && isset( $result->rack_location ) ? $result->rack_location : '';
$quantity     = $edit && isset( $result->quentity ) ? $result->quentity : '';
$post_date    = $edit && isset( $result->added_date ) ? mjschool_get_date_in_input_box( $result->added_date ) : mjschool_get_date_in_input_box( date( 'Y-m-d' ) );
$description  = $edit && isset( $result->description ) ? $result->description : '';

// Currency symbol for price field.
$currency_symbol = mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) );
?>
<div class="mjschool-panel-body">
    <form name="book_form" action="" method="post" class="mjschool-form-horizontal" id="book_form" enctype="multipart/form-data">  
        <?php wp_nonce_field( 'save_book_admin_nonce', 'mjschool_book_nonce' ); ?>
        <input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
        <input type="hidden" name="book_id" value="<?php echo esc_attr( $bookid ); ?>">    
        <div class="header">
            <h3 class="mjschool-first-header"><?php esc_html_e( 'Book Information', 'mjschool' ); ?></h3>
        </div>        
        <div class="form-body mjschool-user-form">
            <div class="row"> 
                <!-- Book Title. -->
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="book_name" class="form-control validate[required] text-input" maxlength="50" type="text" value="<?php echo esc_attr( $book_name ); ?>" name="book_name" aria-required="true" aria-describedby="book_name_desc" >
                            <label for="book_name"> <?php esc_html_e( 'Book Title', 'mjschool' ); ?> <span class="mjschool-require-field" aria-hidden="true">*</span> <span class="screen-reader-text"><?php esc_html_e( '(required)', 'mjschool' ); ?></span> </label>
                        </div>
                    </div>
                </div>
                <!-- Book Number. -->
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="book_number" class="form-control text-input" maxlength="10" type="number" value="<?php echo esc_attr( $book_number ); ?>" name="book_number" >
                            <label for="book_number"><?php esc_html_e( 'Book Number', 'mjschool' ); ?></label>
                        </div>
                    </div>
                </div>
                <!-- ISBN Number. -->
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="isbn" class="form-control validate[required,custom[address_description_validation]]" type="text" maxlength="50" value="<?php echo esc_attr( $isbn ); ?>" name="isbn" aria-required="true" >
                            <label for="isbn"> <?php esc_html_e( 'ISBN Number', 'mjschool' ); ?> <span class="mjschool-require-field" aria-hidden="true">*</span> <span class="screen-reader-text"><?php esc_html_e( '(required)', 'mjschool' ); ?></span> </label>
                        </div>
                    </div>
                </div>
                <!-- Publisher. -->
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="publisher" class="form-control validate[required,custom[city_state_country_validation]] text-input" type="text" maxlength="50" value="<?php echo esc_attr( $publisher ); ?>" name="publisher" aria-required="true" >
                            <label for="publisher"> <?php esc_html_e( 'Publisher', 'mjschool' ); ?> <span class="mjschool-require-field" aria-hidden="true">*</span> <span class="screen-reader-text"><?php esc_html_e( '(required)', 'mjschool' ); ?></span> </label>
                        </div>
                    </div>
                </div>
                <!-- Author Name. -->
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="author_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php echo esc_attr( $author_name ); ?>" name="author_name" aria-required="true" >
                            <label for="author_name"> <?php esc_html_e( 'Author Name', 'mjschool' ); ?> <span class="mjschool-require-field" aria-hidden="true">*</span> <span class="screen-reader-text"><?php esc_html_e( '(required)', 'mjschool' ); ?></span> </label>
                        </div>
                    </div>
                </div>
                <!-- Book Category. -->
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
                    <label class="ml-1 mjschool-custom-top-label top" for="category_data">
                        <?php esc_html_e( 'Select Category', 'mjschool' ); ?>
                        <span class="mjschool-require-field" aria-hidden="true">*</span>
                        <span class="screen-reader-text"><?php esc_html_e( '(required)', 'mjschool' ); ?></span>
                    </label>
                    <select name="bookcat_id" id="category_data" class="form-control smgt_bookcategory validate[required] mjschool-max-width-100px" aria-required="true" >
                        <option value=""><?php esc_html_e( 'Select Category', 'mjschool' ); ?></option>
                        <?php mjschool_render_category_options( $book_categories, $cat_id ); ?>
                    </select>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3">
                    <button type="button" id="mjschool-addremove-bookcat" class="mjschool-rtl-margin-top-15px mjschool-add-btn sibling_add_remove" data-model="smgt_bookcategory" aria-label="<?php esc_attr_e( 'Add new book category', 'mjschool' ); ?>" >
                        <?php esc_html_e( 'Add', 'mjschool' ); ?>
                    </button>
                </div>
                <!-- Book Price. -->
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="book_price" class="form-control validate[required,min[0],maxSize[8]]" type="number" step="0.01" min="0" value="<?php echo esc_attr( $book_price ); ?>" name="book_price" aria-required="true" >
                            <label for="book_price">
                                <?php
                                printf( esc_html__( 'Price (%s)', 'mjschool' ), esc_html( $currency_symbol ) );
                                ?>
                                <span class="mjschool-require-field" aria-hidden="true">*</span>
                                <span class="screen-reader-text"><?php esc_html_e( '(required)', 'mjschool' ); ?></span>
                            </label>
                        </div>
                    </div>
                </div>
                <!-- Rack Location. -->
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 input">
                    <label class="ml-1 mjschool-custom-top-label top" for="rack_category_data"> <?php esc_html_e( 'Rack Location', 'mjschool' ); ?> <span class="mjschool-require-field" aria-hidden="true">*</span> <span class="screen-reader-text"><?php esc_html_e( '(required)', 'mjschool' ); ?></span> </label>
                    <select name="rack_id" id="rack_category_data" class="form-control smgt_rack validate[required] mjschool-max-width-100px" aria-required="true" >
                        <option value=""><?php esc_html_e( 'Select Rack Location', 'mjschool' ); ?></option>
                        <?php mjschool_render_category_options( $rack_locations, $rack_id ); ?>
                    </select>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3">
                    <button type="button" id="mjschool-addremove-rack" class="mjschool-rtl-margin-top-15px mjschool-add-btn sibling_add_remove" data-model="smgt_rack" aria-label="<?php esc_attr_e( 'Add new rack location', 'mjschool' ); ?>" >
                        <?php esc_html_e( 'Add', 'mjschool' ); ?>
                    </button>
                </div>
                <!-- Total Quantity. -->
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input mjschool-rtl-margin-0px">
                        <div class="col-md-12 form-control">
                            <input id="quantity" class="form-control validate[required,min[0],maxSize[5]]" type="number" min="0" value="<?php echo esc_attr( $quantity ); ?>" name="quentity" aria-required="true" >
                            <label for="quantity"> <?php esc_html_e( 'Total Quantity', 'mjschool' ); ?> <span class="mjschool-require-field" aria-hidden="true">*</span> <span class="screen-reader-text"><?php esc_html_e( '(required)', 'mjschool' ); ?></span> </label>
                        </div>
                    </div>
                </div>           
                <!-- Post Date. -->
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="post_date" class="datepicker form-control validate[required] text-input" type="text" name="post_date" value="<?php echo esc_attr( $post_date ); ?>" readonly aria-required="true" >
                            <label for="post_date"> <?php esc_html_e( 'Post Date', 'mjschool' ); ?> <span class="mjschool-require-field" aria-hidden="true">*</span> <span class="screen-reader-text"><?php esc_html_e( '(required)', 'mjschool' ); ?></span> </label>
                        </div>
                    </div>
                </div>      
                <!-- Description. -->
                <div class="col-md-6 mjschool-note-text-notice">
                    <div class="form-group input">
                        <div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
                            <div class="form-field">
                                <textarea id="description" name="description" class="mjschool-textarea-height-47px validate[custom[description_validation]] form-control" aria-describedby="description_help" ><?php echo esc_textarea( $description ); ?></textarea>
                                <span class="mjschool-txt-title-label"></span>
                                <label class="text-area address active" for="description"> <?php esc_html_e( 'Description', 'mjschool' ); ?> </label>
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
        $custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
        ?>  
        <div class="form-body mjschool-user-form">
            <div class="row">
                <div class="col-sm-6">
                    <input type="submit" value="<?php echo $edit ? esc_attr__( 'Save Book', 'mjschool' ) : esc_attr__( 'Add Book', 'mjschool' ); ?>" name="save_book" class="btn btn-success mjschool-save-btn" >
                </div>
            </div>
        </div>
    </form>
</div>