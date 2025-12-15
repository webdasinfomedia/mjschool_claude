jQuery(document).ready(function () {
    "use strict";
    jQuery(document).on("mjschool_approve_leave_load", function () {
        jQuery( '#leave_form' ).validationEngine({
            promptPosition: "bottomLeft",
            maxErrorsPerField: 1
        });
    });
    
    if (jQuery("#mjschool-no-access-trigger").length > 0) {
        const box = jQuery("#mjschool-no-access-trigger");
        if (box.data("trigger") == "1") {
            alert(mjschool_function_data.permission_alert_text);
            const redirectUrl = box.data("redirect-url") || '';
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        }
    }
    if (jQuery("#mjschool-admin-no-access-trigger").length > 0) {
        const boxAdmin = jQuery("#mjschool-admin-no-access-trigger");
        if (boxAdmin.data("trigger") == "1") {
            alert(mjschool_function_data.permission_alert_text);
            window.location.href = boxAdmin.data("redirect-url");
        }
    }

    if (jQuery("#mjschool-category-popup-trigger").length > 0) {
        const form = jQuery('#fees_type_form');
        if (form.length) {
            form.validationEngine({
                promptPosition: "bottomLeft",
                maxErrorsPerField: 1
            });
        }
    }

    if (jQuery("#mjschool-print-invoice-trigger").length > 0) {
        const printTrigger = $("#mjschool-print-invoice-trigger");
        if (printTrigger.data("print") == "1") {
            // Wait for everything to fully load.
            $(window).on("load", function () {
                window.print();
            });
        }
    }

});