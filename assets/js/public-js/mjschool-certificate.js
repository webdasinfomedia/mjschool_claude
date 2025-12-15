jQuery(document).ready(function () {
    "use strict";
    jQuery( '#certificate' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
    if (jQuery('#grade_list, #certificate_list').length > 0) {
        jQuery('#grade_list, #certificate_list').DataTable({
            initComplete: function () {
                jQuery(".mjschool-print-button").css({ "margin-top": "-55px" });
            },
            responsive: true,
            order: [[2, "desc"]],
            dom: 'lifrtp',
            aoColumns: [
                { bSortable: false },
                { bSortable: false },
                { bSortable: true },
                { bSortable: true },
                { bSortable: true },
                { bSortable: false }
            ],
            language: mjschool_certificate_data.datatable_language
        });
    }
    jQuery('.dataTables_filter input')
        .attr("placeholder", mjschool_certificate_data.search_placeholder)
        .attr("id", "datatable_search")
        .attr("name", "datatable_search");
    const select = document.getElementById( 'certificate_type' );
    const hiddenInput = document.getElementById( 'certificate_id' );
    if (!select || !hiddenInput) return; // Safety check.
    function updateCertificateId() {
        const selectedOption = select.options[select.selectedIndex];
        const id = selectedOption ? selectedOption.getAttribute( 'data-id' ) || '' : '';
        hiddenInput.value = id;
    }
    // Set initial value.
    updateCertificateId();
    // Update on change.
    select.addEventListener('change', updateCertificateId);
    const urlParams = new URLSearchParams(window.location.search);
    const letterType = urlParams.get( 'letter-type' );
    if (!letterType) return;
    const letterTypeSelect = document.getElementById( 'certificate_type' );
    if (!letterTypeSelect) return;
    const options = letterTypeSelect.options;
    for (let i = 0; i < options.length; i++) {
        if (options[i].value === letterType) {
            options[i].selected = true;
            break;
        }
    }
});