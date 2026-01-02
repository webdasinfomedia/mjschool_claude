jQuery(document).ready(function ($) {

    "use strict";
    
    $('#download_pdf').on('click', function () {
        const { jsPDF } = window.jspdf;
        const original = document.getElementById('invoice-pdf');
        const clone = original.cloneNode(true);
        clone.classList.add('pdf-export');
        // Hide clone from user view.
        clone.style.position = 'absolute';
        clone.style.left = '-9999px';
        clone.style.top = '0';
        document.body.appendChild(clone);
        html2canvas(clone, {
            scale: 3,
            useCORS: true,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            document.body.removeChild(clone); // cleanup.
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pageWidth = 210;
            const pageHeight = 297;
            const margin = 15;
            const usableWidth = pageWidth - margin * 2;
            const imgHeight = (canvas.height * usableWidth) / canvas.width;
            let heightLeft = imgHeight;
            let position = margin;
            const imgData = canvas.toDataURL('image/png');
            pdf.addImage(imgData, 'PNG', margin, position, usableWidth, imgHeight);
            heightLeft -= (pageHeight - margin * 2);
            while (heightLeft > 0) {
                pdf.addPage();
                position = heightLeft - imgHeight + margin;
                pdf.addImage(imgData, 'PNG', margin, position, usableWidth, imgHeight);
                heightLeft -= (pageHeight - margin * 2);
            }
            pdf.save('Exam-Hall-Ticket.pdf');
        });
    });

    $('#download_result_pdf').on('click', function () {
        const { jsPDF } = window.jspdf;
        const original = document.getElementById('invoice-pdf');
        const clone = original.cloneNode(true);
        clone.classList.add('pdf-export');
        // Hide clone from user view.
        clone.style.position = 'absolute';
        clone.style.left = '-9999px';
        clone.style.top = '0';
        document.body.appendChild(clone);
        html2canvas(clone, {
            scale: 3,
            useCORS: true,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            document.body.removeChild(clone); // cleanup.
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pageWidth = 210;
            const pageHeight = 297;
            const margin = 15;
            const usableWidth = pageWidth - margin * 2;
            const imgHeight = (canvas.height * usableWidth) / canvas.width;
            let heightLeft = imgHeight;
            let position = margin;
            const imgData = canvas.toDataURL('image/png');
            pdf.addImage(imgData, 'PNG', margin, position, usableWidth, imgHeight);
            heightLeft -= (pageHeight - margin * 2);
            while (heightLeft > 0) {
                pdf.addPage();
                position = heightLeft - imgHeight + margin;
                pdf.addImage(imgData, 'PNG', margin, position, usableWidth, imgHeight);
                heightLeft -= (pageHeight - margin * 2);
            }
            pdf.save('Exam-Result.pdf');
        });
    });
    $('#download_fees_invoice_pdf').on('click', function () {

        const { jsPDF } = window.jspdf;

        let invoice = document.getElementById('invoice-pdf');
        invoice.classList.add('mjschool-pdf-mode');

        html2canvas(invoice, {
            scale: 3,
            useCORS: true,
            allowTaint: true,
            scrollY: -window.scrollY
        }).then(canvas => {
            invoice.classList.remove('mjschool-pdf-mode');
            const pdf = new jsPDF('p', 'mm', 'a4');

            // A4 size
            const pageWidth = 210;
            const pageHeight = 297;

            // ✅ MARGINS (IMPORTANT)
            const marginTop = 15;
            const marginLeft = 15;
            const marginRight = 15;

            const usableWidth = pageWidth - marginLeft - marginRight;

            const imgWidth = usableWidth;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            let heightLeft = imgHeight;
            let position = marginTop;

            const imgData = canvas.toDataURL('image/png');

            // First page
            pdf.addImage(
                imgData,
                'PNG',
                marginLeft,
                position,
                imgWidth,
                imgHeight
            );

            heightLeft -= (pageHeight - marginTop);

            // Additional pages
            while (heightLeft > 0) {
                pdf.addPage();
                position = heightLeft - imgHeight + marginTop;

                pdf.addImage(
                    imgData,
                    'PNG',
                    marginLeft,
                    position,
                    imgWidth,
                    imgHeight
                );

                heightLeft -= pageHeight;
            }

            pdf.save('Invoice.pdf');

        });
    });
    $('#download_certificate_pdf').on('click', function () {
        const { jsPDF } = window.jspdf;

        let invoice = document.getElementById('invoice-pdf');
        invoice.classList.add('mjschool-certificate-pdf-mode');

        html2canvas(invoice, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            scrollY: -window.scrollY
        }).then(canvas => {
            invoice.classList.remove('mjschool-certificate-pdf-mode');
            const pdf = new jsPDF('p', 'mm', 'a4');

            // A4 size
            const pageWidth = 210;
            const pageHeight = 297;

            // ✅ MARGINS (IMPORTANT)
            const marginTop = 15;
            const marginLeft = 15;
            const marginRight = 15;

            const usableWidth = pageWidth - marginLeft - marginRight;

            const imgWidth = usableWidth;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            let heightLeft = imgHeight;
            let position = marginTop;

            const imgData = canvas.toDataURL('image/png');

            // First page
            pdf.addImage(
                imgData,
                'PNG',
                marginLeft,
                position,
                imgWidth,
                imgHeight
            );

            heightLeft -= (pageHeight - marginTop);

            // Additional pages
            while (heightLeft > 0) {
                pdf.addPage();
                position = heightLeft - imgHeight + marginTop;

                pdf.addImage(
                    imgData,
                    'PNG',
                    marginLeft,
                    position,
                    imgWidth,
                    imgHeight
                );

                heightLeft -= pageHeight;
            }

            pdf.save('Invoice.pdf');

        });
    });

});
