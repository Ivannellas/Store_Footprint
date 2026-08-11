document.addEventListener("DOMContentLoaded", function () {
  // Auto-fill Time Inputs (Start Time & End Time) for all forms
  const now = new Date();
  let currentHour = now.getHours();

  let startHour = currentHour - 1;
  if (startHour < 0) {
    startHour = 23;
  }

  function formatTimeInput(hour) {
    return String(hour).padStart(2, "0") + ":00";
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

  //  Alert Dismissal (Toast Style)
  const alertBox = document.querySelector(".custom-toast-alert");

  if (alertBox) {
    function removeAlert() {
      if (alertBox.dataset.dismissed) return;
      alertBox.dataset.dismissed = "true";

      alertBox.style.opacity = "0";
      alertBox.style.transform = "translateY(-20px)";
      setTimeout(function () {
        alertBox.remove();
      }, 300);
    }

    const closeBtn = alertBox.querySelector(".btn-close-toast");
    if (closeBtn) {
      closeBtn.addEventListener("click", function () {
        removeAlert();
      });
    }

    setTimeout(function () {
      removeAlert();
    }, 2000);
  }
  // Clean URL query parameters so reloading won't trigger the success message again
  if (
    window.location.search.includes("status=") ||
    window.location.search.includes("success")
  ) {
    const cleanUrl =
      window.location.protocol +
      "//" +
      window.location.host +
      window.location.pathname;
    window.history.replaceState({ path: cleanUrl }, "", cleanUrl);
  }

  // Confirmation Modal Before Submitting Traffic Log
  function formatTimeDisplay(timeValue) {
    if (!timeValue) return "-";
    const [h, m] = timeValue.split(":");
    const d = new Date();
    d.setHours(h, m);
    return d.toLocaleTimeString("en-US", {
      hour: "numeric",
      minute: "2-digit",
      hour12: true,
    });
  }

  const trafficForms = document.querySelectorAll(".traffic_form");

  trafficForms.forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const type = form.querySelector('input[name="type"]').value;
      const name = form.querySelector('input[name="name"]').value;
      const startTime = form.querySelector('input[name="startTime"]').value;
      const endTime = form.querySelector('input[name="endTime"]').value;
      const count = form.querySelector('input[name="count"]').value;

      const label = type === "parking" ? "Vehicle Traffic" : "Foot Traffic";

      Swal.fire({
        title: "Confirm " + label + " Entry",
        html:
          '<div style="text-align:left; font-size:14px;">' +
          "<p><strong>Personnel:</strong> " +
          name +
          "</p>" +
          "<p><strong>Start Time:</strong> " +
          formatTimeDisplay(startTime) +
          "</p>" +
          "<p><strong>End Time:</strong> " +
          formatTimeDisplay(endTime) +
          "</p>" +
          "<p><strong>Count:</strong> " +
          count +
          "</p>" +
          "</div>",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Submit",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#003366",
        reverseButtons: true,
      }).then(function (result) {
        if (result.isConfirmed) {
          HTMLFormElement.prototype.submit.call(form);
        }
      });
    });
  });

  // History Modal Filter
    window.applyFilter = function (context) {
        const dateInputId  = context === 'foot' ? 'footDate' : 'vehicleDate';
        const tableBodyId  = context === 'foot' ? 'footTableBody' : 'vehicleTableBody';

        const selectedDate = document.getElementById(dateInputId).value;
        const tableBody     = document.getElementById(tableBodyId);
        const rows           = tableBody.querySelectorAll('tr');

        rows.forEach(function (row) {
            if (!row.dataset.date) return; // skip "no data" placeholder row

            if (!selectedDate || row.dataset.date === selectedDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Show "no results" message if all rows are hidden
        const visibleRows = Array.from(rows).filter(r => r.style.display !== 'none' && r.dataset.date);
        let noResultRow = tableBody.querySelector('.no-filter-result');

        if (visibleRows.length === 0) {
            if (!noResultRow) {
                noResultRow = document.createElement('tr');
                noResultRow.className = 'no-filter-result';
                noResultRow.innerHTML = '<td colspan="5" class="text-center">No records found for selected date</td>';
                tableBody.appendChild(noResultRow);
            }
            noResultRow.style.display = '';
        } else if (noResultRow) {
            noResultRow.style.display = 'none';
        }
    };

    window.clearFilter = function (context) {
        const dateInputId = context === 'foot' ? 'footDate' : 'vehicleDate';
        const tableBodyId = context === 'foot' ? 'footTableBody' : 'vehicleTableBody';

        document.getElementById(dateInputId).value = '';

        const tableBody = document.getElementById(tableBodyId);
        tableBody.querySelectorAll('tr').forEach(function (row) {
            row.style.display = '';
        });
    };
});
