jQuery(document).ready(function () {
    "use strict";
    // Initialize validation engine.
    jQuery( '#user_account_info, #user_other_info' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    // --- File extension check. ---
    jQuery(document).on( "change", ".profile_file", function() {
        var file = this.files[0];
        var ext = jQuery(this).val().split( '.' ).pop().toLowerCase();
        // Extension Check.
        if ( jQuery.inArray( ext, ['jpeg', 'jpg', 'png', 'bmp'] ) === -1 ) {
            alert( language_translate2.account_alert_1 + " ." + ext + " " + language_translate2.account_alert_2 );
            jQuery( ".profile_file" ).val( "" );
            return false;
        }
    });
    // --- Save button validation. ---
    jQuery(document).on( "click", ".mjschool-save-upload-profile-btn", function() {
        var value = jQuery( ".profile_file" ).val();
        if (!value) {
            alert( "<?php echo esc_html__( 'Please Select Atleast One Image.', 'mjschool' ); ?>" );
            return false;
        }
    });
    var qrData = {
        "user_id": "<?php echo esc_js(get_current_user_id( ) ); ?>",
        "class_id": "<?php echo esc_js($class_id); ?>",
        "section_id": "<?php echo esc_js($section_name); ?>",
        "qr_type": "schoolqr"
    };
    // Encode as URL-safe string.
    var qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" + encodeURIComponent(JSON.stringify(qrData ) ) + "&amp;size=50x50";
    jQuery( ".mjschool-id-card-barcode").attr( "src", qrCodeUrl);
})