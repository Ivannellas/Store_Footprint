document.addEventListener('DOMContentLoaded', function () {
    // 1. Auto-fill Time Inputs (Start Time & End Time)
    const now = new Date();
    let currentHour = now.getHours();

    let startHour = currentHour - 1;
    if (startHour < 0) {
        startHour = 23;
    }

    function formatTimeInput(hour) {
        return String(hour).padStart(2, '0') + ':00';
    }

    const startTimeElem = document.getElementById('startTime');
    const endTimeElem = document.getElementById('endTime');

    if (startTimeElem) startTimeElem.value = formatTimeInput(startHour);
    if (endTimeElem) endTimeElem.value = formatTimeInput(currentHour);

    // 2. Alert Dismissal (Toast Style)
    const alertBox = document.querySelector('.custom-toast-alert');

    if (alertBox) {
        function removeAlert() {
            if (alertBox.dataset.dismissed) return;
            alertBox.dataset.dismissed = 'true';

            alertBox.style.opacity = '0';
            alertBox.style.transform = 'translateY(-20px)';
            setTimeout(function () {
                alertBox.remove();
            }, 300);
        }

        const closeBtn = alertBox.querySelector('.btn-close-toast');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                removeAlert();
            });
        }

        // Auto hide human sa 3 seconds
        setTimeout(function () {
            removeAlert();
        }, 500);
    }
});