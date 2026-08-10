document.addEventListener('DOMContentLoaded', function () {
    // 1. Auto-fill Time Inputs (Start Time & End Time) for all forms
    const now = new Date();
    let currentHour = now.getHours();

    let startHour = currentHour - 1;
    if (startHour < 0) {
        startHour = 23;
    }

    function formatTimeInput(hour) {
        return String(hour).padStart(2, '0') + ':00';
    }

    const formattedStart = formatTimeInput(startHour);
    const formattedEnd = formatTimeInput(currentHour);

    // Target all inputs matching name="startTime" and name="endTime"
    const startTimeElems = document.querySelectorAll('input[name="startTime"]');
    const endTimeElems = document.querySelectorAll('input[name="endTime"]');

    startTimeElems.forEach(function (elem) {
        if (!elem.value) elem.value = formattedStart;
    });

    endTimeElems.forEach(function (elem) {
        if (!elem.value) elem.value = formattedEnd;
    });

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

        setTimeout(function () {
            removeAlert();
        }, 2000);
    }
    // Clean URL query parameters so reloading won't trigger the success message again
    if (window.location.search.includes('status=') || window.location.search.includes('success')) {
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
    }
});