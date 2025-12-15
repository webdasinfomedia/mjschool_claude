jQuery(document).ready(function () {
    "use strict";
    // File validation logic.
    var allowedTypes = mjschool_common_data.document_type_json;
    var maxFileSizeMB = mjschool_common_data.document_size; // Maximum file size in MB.
    var maxFileSizeBytes = maxFileSizeMB * 1024 * 1024;
    jQuery(document).on('change', '.mjschool-file-validation', function () {
        var val = jQuery(this).val().toLowerCase();
        var regexPattern = "(.*?)\\.( " + allowedTypes.join("|") + ")$";
        var regex = new RegExp(regexPattern);
        var fileInput = jQuery(this)[0];
        var file = fileInput.files[0];
        if (!regex.test(val)) {
            jQuery(this).val('');
            alert('Only ' + allowedTypes.join(', ') + ' formats are allowed.');
        }
        if (file && file.size > maxFileSizeBytes) {
            jQuery(this).val('');
            alert('Too large file Size. Only files smaller than ' + maxFileSizeMB + 'MB can be uploaded.');
        }
    });
    document.addEventListener( "click", function(e) {
        if (e.target.classList.contains( "togglePassword" ) ) {
            const targetSelector = e.target.dataset.target;
            const targetInput = document.querySelector(targetSelector);
            if (!targetInput) return;
            const newType = targetInput.getAttribute( "type") === "password" ? "text" : "password";
            targetInput.setAttribute( "type", newType);
            e.target.classList.toggle( "fa-eye");
            e.target.classList.toggle( "fa-eye-slash");
        }
    });
    // Select All checkboxes.
    jQuery( '.select_all' ).on( 'click', function(){
        jQuery( ".mjschool-sub-chk").prop( 'checked', jQuery(this).is( ':checked' ) );
    });
    // Sub-checkbox change.
    jQuery( '.mjschool-sub-chk' ).on( 'change', function(){
        var total = jQuery( '.mjschool-sub-chk' ).length;
        var checked = jQuery( '.mjschool-sub-chk:checked' ).length;
        jQuery( ".select_all").prop( 'checked', total === checked);
    });
    jQuery(document).on( 'click', '#checkbox-select-all', function () {
        var rows = table.rows({ 'search': 'applied' }).nodes();
        jQuery( 'input[type="checkbox"]', rows).prop( 'checked', this.checked);
    });
    // Delete selected.
    jQuery(document).on( 'click', '#delete_selected', function() {
        if (jQuery( '.select-checkbox:checked' ).length === 0) {
            alert(language_translate2.one_record_select_alert);
            return false;
        } else {
            return confirm(language_translate2.delete_record_alert);
        }
    });
});