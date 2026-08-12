document.addEventListener("DOMContentLoaded", function () {
  // Toast Alert Dismissal
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
      closeBtn.addEventListener("click", removeAlert);
    }

    setTimeout(removeAlert, 2500);
  }

  // Preserve 'tab' parameter while stripping 'status' from URL
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has("status") || urlParams.has("success")) {
    urlParams.delete("status");
    urlParams.delete("success");

    const newQuery = urlParams.toString() ? "?" + urlParams.toString() : "";
    const cleanUrl =
      window.location.protocol +
      "//" +
      window.location.host +
      window.location.pathname +
      newQuery;
    window.history.replaceState({ path: cleanUrl }, "", cleanUrl);
  }

  // Confirmation Modal Before Submitting Traffic Log
  const trafficForms = document.querySelectorAll(".traffic_form");

  trafficForms.forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const type = form.querySelector('input[name="type"]')?.value || "store";

      // Updated Personnel selector to capture select or input elements
      const nameElem = form.querySelector(
        '[name="personnel_name"], [name="personnel"], [name="name"]',
      );
      const name = nameElem ? nameElem.value : "";

      const timeRangeElem = form.querySelector('select[name="timeRange"]');
      const timeRange = timeRangeElem ? timeRangeElem.value : "";
      const count = form.querySelector('input[name="count"]')?.value || "0";

      const label = type === "parking" ? "Vehicle Traffic" : "Foot Traffic";

      Swal.fire({
        title: "Confirm " + label + " Entry",
        html:
          '<div style="text-align:left; font-size:14px; line-height:1.6;">' +
          "<p><strong>Personnel:</strong> " +
          name +
          "</p>" +
          "<p><strong>Time Range:</strong> " +
          timeRange +
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

  // History Modal Filters
  window.applyFilter = function (context) {
    const dateInputId = context === "foot" ? "footDate" : "vehicleDate";
    const tableBodyId =
      context === "foot" ? "footTableBody" : "vehicleTableBody";

    const dateElem = document.getElementById(dateInputId);
    if (!dateElem) return;

    const selectedDate = dateElem.value;
    const tableBody = document.getElementById(tableBodyId);
    if (!tableBody) return;

    const rows = tableBody.querySelectorAll("tr");

    rows.forEach(function (row) {
      if (!row.dataset.date) return;

      if (!selectedDate || row.dataset.date === selectedDate) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });

    const visibleRows = Array.from(rows).filter(
      (r) => r.style.display !== "none" && r.dataset.date,
    );
    let noResultRow = tableBody.querySelector(".no-filter-result");

    if (visibleRows.length === 0) {
      if (!noResultRow) {
        noResultRow = document.createElement("tr");
        noResultRow.className = "no-filter-result";
        noResultRow.innerHTML =
          '<td colspan="4" class="text-center text-muted">No records found for selected date</td>';
        tableBody.appendChild(noResultRow);
      }
      noResultRow.style.display = "";
    } else if (noResultRow) {
      noResultRow.style.display = "none";
    }
  };

  window.clearFilter = function (context) {
    const dateInputId = context === "foot" ? "footDate" : "vehicleDate";
    const tableBodyId =
      context === "foot" ? "footTableBody" : "vehicleTableBody";

    const dateElem = document.getElementById(dateInputId);
    if (dateElem) dateElem.value = "";

    const tableBody = document.getElementById(tableBodyId);
    if (tableBody) {
      tableBody.querySelectorAll("tr").forEach(function (row) {
        row.style.display = "";
      });
    }
  };
});
