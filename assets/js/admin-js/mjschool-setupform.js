jQuery(document).ready(function () {
    "use strict";
    if (jQuery('#mjschool_otp_trigger').length > 0) {
        const durationStart = 120; // 2 minutes in seconds.
        let duration = durationStart;
        const timerDisplay = document.getElementById( 'timer' );
        const resendBtn = document.getElementById( 'resend-btn' );
        const countdownContainer = document.getElementById( 'countdown-timer' );
        if (!timerDisplay || !resendBtn || !countdownContainer) {
            console.warn( 'Timer elements missing in DOM' );
            return;
        }
        resendBtn.disabled = true;
        resendBtn.classList.add( 'btn-secondary' );
        resendBtn.classList.remove( 'btn-primary' );
        resendBtn.style.cursor = 'not-allowed';
        const interval = setInterval(() => {
            let minutes = Math.floor(duration / 60);
            let seconds = duration % 60;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            timerDisplay.textContent = `${minutes}:${seconds}`;
            if (--duration < 0) {
                clearInterval(interval);
                countdownContainer.textContent = "OTP expired.";
                // Enable the resend button.
                resendBtn.disabled = false;
                resendBtn.classList.remove( 'btn-secondary' );
                resendBtn.classList.add( 'btn-primary' );
                resendBtn.style.backgroundColor = ''; // Reset to default if needed.
                resendBtn.style.cursor = 'pointer';
            }
        }, 1000);
    }

    if (jQuery('#mjschool_form_trigger').length > 0) {
        const trigger = document.getElementById('mjschool_form_trigger');
        const redirectUrl = trigger.dataset.redirectUrl;
        if (jQuery('.successMessage').length && redirectUrl) {
            setTimeout(function () {
                jQuery('.successMessage').fadeOut('fast', function () {
                    window.location.href = redirectUrl;
                });
            }, 3000);
        }
    }
    jQuery( '#verification_form, #reset_form, #verify_otp_form' ).validationEngine({
        promptPosition: "bottomLeft",
        maxErrorsPerField: 1
    });
});