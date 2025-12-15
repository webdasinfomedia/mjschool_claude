jQuery(document).ready(function () {
    "use strict";
    // Form validation.
    jQuery( '#mjschool-registration-form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    // Birth date picker.
    jQuery( '#birth_date' ).datepicker({
        maxDate: 0,
        dateFormat: mjschool_registration_data.date_format,
        changeMonth: true,
        changeYear: true,
        yearRange: '-65:+25',
        onChangeMonthYear: function(year, month, inst) {
            jQuery(this).val(month + "/" + year);
        }
    });
    // Custom field datepickers.
    jQuery( '.after_or_equal' ).datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        }
    });
    jQuery( '.date_equals' ).datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0,
        maxDate: 0,
        changeMonth: true,
        changeYear: true,
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        }
    });
    jQuery( '.before_or_equal' ).datepicker({
        dateFormat: "yy-mm-dd",
        maxDate: 0,
        changeMonth: true,
        changeYear: true,
        beforeShow: function(textbox, instance) {
            instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
        }
    });
    // Prevent spaces in fields.
    jQuery(document).on( 'keypress', '.space_validation', function(e) {
        if (e.which === 32 ) return false;
    });
    // Custom file validation.
    window.mjschool_custom_file_Check = function(obj) {
        var fileExtension = jQuery(obj).attr( 'file_types' );
        var fileExtensionArr = fileExtension.split( ',' );
        var file_size = jQuery(obj).attr( 'file_size' );
        var sizeInkb = obj.files[0].size / 1024;
        if (jQuery.inArray(jQuery(obj).val().split( '.' ).pop().toLowerCase(), fileExtensionArr) === -1) {
            alert( "Only " + fileExtension + " formats are allowed.");
            jQuery(obj).val( '' );
        } else if (sizeInkb > file_size) {
            alert( "Only " + file_size + " kb size is allowed");
            jQuery(obj).val( '' );
        }
    };
    // Simple file check for images.
    window.mjschool_file_check = function(obj) {
        var fileExtension = ['jpeg', 'jpg', 'png', 'bmp', ''];
        if (jQuery.inArray(jQuery(obj).val().split( '.' ).pop().toLowerCase(), fileExtension) === -1) {
            alert( "Only " + fileExtension.join( ', ' ) + " formats are allowed.");
            jQuery(obj).val( '' );
        }
    };
    var allowedTypes = mjschool_registration_data.document_type_json;
    var maxFileSizeMB = mjschool_registration_data.document_size; // Maximum file size in MB.
    var maxFileSizeBytes = maxFileSizeMB * 1024 * 1024;
    if (jQuery(".mjschool-file-validation[type=file]").length > 0) {
        jQuery(document).on("change", ".mjschool-file-validation[type=file]", function () {
            var val = jQuery(this).val().toLowerCase();
            var fileInput = jQuery(this)[0];
            var file = fileInput.files[0];
            // Build regex dynamically.
            var regexPattern = "(.*?)\\.(" + allowedTypes.join("|") + ")$";
            var regex = new RegExp(regexPattern);
            if (!regex.test(val)) {
                jQuery(this).val('');
                alert('Only ' + allowedTypes.join(', ') + ' formats are allowed.');
                return;
            }
            if (file && file.size > maxFileSizeBytes) {
                jQuery(this).val('');
                alert('File too large. Only files smaller than ' + maxFileSizeMB + 'MB are allowed.');
                return;
            }
        });
    }
    // Add more document via AJAX.
    window.mjschool_add_more_document = function () {
        var curr_data = {
            action: 'mjschool_load_more_document',
            nonce: mjschool.nonce,
            dataType: 'json'
        };
        jQuery.post(mjschool.ajax, curr_data, function (response) {
            jQuery( ".mjschool-more-document").append(response);
        });
    };
    // Delete document row.
    window.mjschool_delete_parent_element = function (elem) {
        var confirmDelete = confirm( mjschool_registration_data.document_delete_alert);
        if (confirmDelete) {
            elem.parentNode.parentNode.parentNode.removeChild(elem.parentNode.parentNode);
        }
    };
    // Password toggle.
    document.addEventListener( "click", function(e) {
        if (e.target.classList.contains( "togglePassword" ) ) {
            const targetSelector = e.target.dataset.target;
            const targetInput = document.querySelector(targetSelector);
            if (!targetInput) return;
            // Toggle input type.
            const type = targetInput.type === "password" ? "text" : "password";
            targetInput.type = type;
            // Toggle icon classes.
            e.target.classList.toggle( "fa-eye");
            e.target.classList.toggle( "fa-eye-slash");
        }
    });
    // Custom Field File Validation.
    window.mjschool_custom_filed_file_check = function(obj) {
        var fileTypes = jQuery(obj).attr( 'file_types' ).split( ',' );
        var maxSizeKb = parseFloat(jQuery(obj).attr( 'file_size' ) );
        var file = obj.files[0];
        if (!file) return;
        var ext = jQuery(obj).val().split( '.' ).pop().toLowerCase();
        var sizeInKb = file.size / 1024;
        if (jQuery.inArray(ext, fileTypes) === -1) {
            alert( "Only " + fileTypes.join( ', ' ) + " formats are allowed.");
            jQuery(obj).val('');
        } else if (sizeInKb > maxSizeKb) {
            alert( "Only " + maxSizeKb + " kb size is allowed.");
            jQuery(obj).val('');
        }
    };
    jQuery( '.custom_datepicker' ).datepicker({
        endDate: '+0d',
        autoclose: true,
        orientation: "bottom"
    });
    // Add multiple sibling / file validation.
    jQuery(document).on( "change", ".input-file[type=file]", function() {
        var file = this.files[0];
        var ext = jQuery(this).val().split( '.' ).pop().toLowerCase();
        // Extension Check.
        if (jQuery.inArray(ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'gif', 'png', 'jpg', 'jpeg']) === -1) {
            alert( mjschool_registration_data.admission_doc_alert + ext + mjschool_registration_data.format_alert );
            jQuery(this).replaceWith( '<input class="mjschool-btn-top input-file" name="message_attachment[]" type="file" />' );
            return false;
        }
        // File Size Check (20 MB).
        if (file && file.size > 20480000) {
            alert(language_translate2.large_file_size_alert);
            jQuery(this).replaceWith( '<input class="mjschool-btn-top input-file" name="message_attachment[]" type="file" />' );
            return false;
        }
    });
    // Initialize datepickers.
    function initDatepicker(selector, maxDate) {
        jQuery(selector).datepicker({
            dateFormat: mjschool_registration_data.date_format,
            changeMonth: true,
            changeYear: true,
            yearRange: '-65:+25',
            maxDate: maxDate || null,
            beforeShow: function(textbox, instance) {
                instance.dpDiv.css({
                    marginTop: (-textbox.offsetHeight) + 'px'
                });
            },
            onChangeMonthYear: function(year, month, inst) {
                jQuery(this).val(month + "/" + year);
            }
        });
    }
    initDatepicker( '#admission_date' );
    initDatepicker( '.birth_date', 0); // maxDate 0 = today.
    // Email validation to avoid duplicates.
    function validateEmails(changedField) {
        var studentEmail = jQuery( ".email").val();
        var fatherEmail = jQuery( ".father_email").val();
        var motherEmail = jQuery( ".mother_email").val();
        if ((studentEmail && (studentEmail === fatherEmail || studentEmail === motherEmail ) ) || (fatherEmail && fatherEmail === motherEmail ) ) {
            alert( 'You have used the same email' );
            jQuery(changedField).val('');
        }
    }
    jQuery(document).on('change', '.email, .father_email, .mother_email', function() {
        validateEmails(this);
    });
});