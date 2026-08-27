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

  // Reusable Searchable Dropdown Initializer
  function initSearchableDropdown({
    inputId,
    hiddenId,
    dropdownId,
    wrapperId,
  }) {
    const personnelInput = document.getElementById(inputId);
    const nameStore = document.getElementById(hiddenId);
    const personnelDropdown = document.getElementById(dropdownId);
    const personnelWrapper = document.getElementById(wrapperId);

    if (!personnelInput || !personnelDropdown) return null;

    const options = Array.from(
      personnelDropdown.querySelectorAll(".personnel-dropdown-item"),
    );

    const validPersonnelList = options.map((opt) =>
      opt.getAttribute("data-value"),
    );

    let activeIndex = -1;

    function openDropdown() {
      const filter = personnelInput.value.trim().toLowerCase();
      options.forEach(function (option) {
        const text = option.textContent.trim().toLowerCase();
        option.style.display =
          !filter || text.includes(filter) ? "block" : "none";
      });

      personnelDropdown.classList.add("show");
      if (personnelWrapper) personnelWrapper.classList.add("open");
    }

    function closeDropdown() {
      personnelDropdown.classList.remove("show");
      if (personnelWrapper) personnelWrapper.classList.remove("open");
      activeIndex = -1;
      removeHighlight();
    }

    function getVisibleOptions() {
      return options.filter((opt) => opt.style.display !== "none");
    }

    function removeHighlight() {
      options.forEach((opt) => opt.classList.remove("active"));
    }

    function updateHighlight(visibleOptions, updateInputValue = true) {
      removeHighlight();
      if (activeIndex >= 0 && activeIndex < visibleOptions.length) {
        const target = visibleOptions[activeIndex];
        target.classList.add("active");
        target.scrollIntoView({ block: "nearest" });

        const selectedValue = target.getAttribute("data-value");

        if (updateInputValue) {
          personnelInput.value = selectedValue;
          validateSelection();
        }
      }
    }

    function validateSelection() {
      const currentText = personnelInput.value.trim();
      const matched = validPersonnelList.find(
        (name) => name.toLowerCase() === currentText.toLowerCase(),
      );

      if (matched) {
        personnelInput.value = matched;
        if (nameStore) nameStore.value = matched;
        personnelInput.setCustomValidity("");
        return true;
      } else {
        if (nameStore) nameStore.value = "";
        personnelInput.setCustomValidity(
          "Please select a valid personnel from the dropdown.",
        );
        return false;
      }
    }

    // Event Listeners
    personnelInput.addEventListener("focus", openDropdown);
    personnelInput.addEventListener("click", openDropdown);

    personnelInput.addEventListener("input", function () {
      const filter = personnelInput.value.trim().toLowerCase();
      openDropdown();

      options.forEach(function (option) {
        const text = option.textContent.trim().toLowerCase();
        option.style.display = text.includes(filter) ? "block" : "none";
      });

      const visibleOptions = getVisibleOptions();
      if (visibleOptions.length > 0) {
        activeIndex = 0;
        updateHighlight(visibleOptions, false);
      } else {
        activeIndex = -1;
        removeHighlight();
      }

      validateSelection();
    });

    personnelInput.addEventListener("blur", function () {
      setTimeout(() => {
        if (!validateSelection()) {
          personnelInput.value = "";
          if (nameStore) nameStore.value = "";
          personnelInput.setCustomValidity("");

          // FIX: Reset all options back to display block when cleared
          options.forEach(function (option) {
            option.style.display = "block";
          });
        }
      }, 200);
    });

    personnelInput.addEventListener("keydown", function (e) {
      const visibleOptions = getVisibleOptions();

      if (e.key === "ArrowDown") {
        e.preventDefault();
        if (!personnelDropdown.classList.contains("show")) openDropdown();
        if (visibleOptions.length > 0) {
          activeIndex = (activeIndex + 1) % visibleOptions.length;
          updateHighlight(visibleOptions, true);
        }
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        if (visibleOptions.length > 0) {
          activeIndex =
            (activeIndex - 1 + visibleOptions.length) % visibleOptions.length;
          updateHighlight(visibleOptions, true);
        }
      } else if (e.key === "Enter") {
        if (personnelDropdown.classList.contains("show") && activeIndex >= 0) {
          e.preventDefault();
          if (visibleOptions[activeIndex]) {
            validateSelection();
          }
          closeDropdown();
        }
      } else if (e.key === "Escape") {
        closeDropdown();
      }
    });

    options.forEach(function (option) {
      option.addEventListener("click", function () {
        const selectedValue = option.getAttribute("data-value");
        personnelInput.value = selectedValue;
        validateSelection();
        closeDropdown();
      });
    });

    document.addEventListener("click", function (e) {
      if (!e.target.closest(`#${wrapperId}`)) {
        closeDropdown();
      }
    });
  }

  // Initialize Searchable Dropdowns for both Foot Traffic and Parking
  initSearchableDropdown({
    inputId: "personnelInput",
    hiddenId: "nameStore",
    dropdownId: "personnelDropdown",
    wrapperId: "personnelWrapper",
  });

  initSearchableDropdown({
    inputId: "parkingPersonnelInput",
    hiddenId: "nameParking",
    dropdownId: "parkingPersonnelDropdown",
    wrapperId: "parkingPersonnelWrapper",
  });

  // Local Flatpickr Initialization for Foot & Vehicle Date Range Pickers
  if (typeof flatpickr !== "undefined") {
    // Foot Traffic Picker
    const footRangePicker = document.getElementById("date-range-picker");
    if (footRangePicker) {
      flatpickr(footRangePicker, {
        mode: "range",
        dateFormat: "Y-m-d",
        onClose: function (selectedDates, dateStr, instance) {
          if (selectedDates.length === 2) {
            const startDate = instance.formatDate(selectedDates[0], "Y-m-d");
            const endDate = instance.formatDate(selectedDates[1], "Y-m-d");

            const fromInput = document.getElementById("fromperiod");
            const toInput = document.getElementById("toperiod");

            if (fromInput) fromInput.value = startDate;
            if (toInput) toInput.value = endDate;
          }
        },
      });
    }

    // Vehicle Traffic Picker
    const vehicleRangePicker = document.getElementById(
      "vehicle-date-range-picker",
    );
    if (vehicleRangePicker) {
      flatpickr(vehicleRangePicker, {
        mode: "range",
        dateFormat: "Y-m-d",
        onClose: function (selectedDates, dateStr, instance) {
          if (selectedDates.length === 2) {
            const startDate = instance.formatDate(selectedDates[0], "Y-m-d");
            const endDate = instance.formatDate(selectedDates[1], "Y-m-d");

            const fromInput = document.getElementById("vehicle-fromperiod");
            const toInput = document.getElementById("vehicle-toperiod");

            if (fromInput) fromInput.value = startDate;
            if (toInput) toInput.value = endDate;
          }
        },
      });
    }
  }

  // Helper function to convert time strings into total minutes from midnight
  function parseTimeToMinutes(timeStr) {
    if (!timeStr) return null;
    const match = timeStr.trim().match(/^(\d{1,2}):(\d{2})(?:\s*([AP]M))?$/i);
    if (!match) return null;

    let hours = parseInt(match[1], 10);
    const minutes = parseInt(match[2], 10);
    const ampm = match[3] ? match[3].toUpperCase() : null;

    if (ampm === "PM" && hours < 12) hours += 12;
    if (ampm === "AM" && hours === 12) hours = 0;

    return hours * 60 + minutes;
  }

  // Confirmation Modal & Validation Before Submitting Traffic Log
  const trafficForms = document.querySelectorAll(".traffic_form");

  trafficForms.forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      // Validate searchable personnel field if present in the current form
      const searchInput = form.querySelector(".personnel-search-input");
      if (searchInput && !searchInput.checkValidity()) {
        searchInput.reportValidity();
        return;
      }

      const type = form.querySelector('input[name="type"]')?.value || "store";
      const nameElem = form.querySelector(
        '[name="personnel_name"], [name="personnel"], [name="name"]',
      );
      const name = nameElem ? nameElem.value : "";

      const timeRangeElem = form.querySelector('select[name="timeRange"]');
      const timeRangeText = timeRangeElem
        ? timeRangeElem.options[timeRangeElem.selectedIndex]?.text ||
          timeRangeElem.value
        : "";
      const count = form.querySelector('input[name="count"]')?.value || "0";

      const selectedDate =
        form.querySelector('input[name="date"]')?.value || "";

      // Current Date & Time
      const now = new Date();
      const currentYear = now.getFullYear();
      const currentMonth = String(now.getMonth() + 1).padStart(2, "0");
      const currentDay = String(now.getDate()).padStart(2, "0");
      const todayFormatted = `${currentYear}-${currentMonth}-${currentDay}`;

      // Trap Future Date
      if (selectedDate && selectedDate > todayFormatted) {
        Swal.fire({
          title: "Invalid Date",
          text: "You cannot log entries for future dates.",
          icon: "error",
          confirmButtonColor: "#003366",
        });
        return;
      }

      // Trap Future Time Slot (for today)
      if (!selectedDate || selectedDate === todayFormatted) {
        const currentMinutes = now.getHours() * 60 + now.getMinutes();
        const startTimeStr = timeRangeText.split("-")[0]?.trim();
        const startSlotMinutes = parseTimeToMinutes(startTimeStr);

        if (startSlotMinutes !== null && startSlotMinutes > currentMinutes) {
          Swal.fire({
            title: "Invalid Time Range",
            text: "You cannot log entries for advance time range.",
            icon: "error",
            confirmButtonColor: "#003366",
          });
          return;
        }
      }

      const label = type === "parking" ? "Vehicle Traffic" : "Foot Traffic";

      Swal.fire({
        title: "Confirm " + label + " Entry",
        html:
          '<div style="text-align:left; font-size:14px; line-height:1.6;">' +
          "<p><strong>Personnel:</strong> " +
          name +
          "</p>" +
          "<p><strong>Time Range:</strong> " +
          timeRangeText +
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

  // Combined History Table Filter (Date Range + Search Query)
  window.applyFilter = function (context) {
    const isVehicle = context === "vehicle";

    const searchInputId = isVehicle
      ? "vehicle-table-search-input"
      : "table-search-input";
    const fromInputId = isVehicle ? "vehicle-fromperiod" : "fromperiod";
    const toInputId = isVehicle ? "vehicle-toperiod" : "toperiod";
    const tableBodyId = isVehicle ? "vehicleTableBody" : "footTableBody";

    const searchQuery =
      document.getElementById(searchInputId)?.value.toLowerCase().trim() || "";
    const fromDate = document.getElementById(fromInputId)?.value || "";
    const toDate = document.getElementById(toInputId)?.value || "";

    const tableBody = document.getElementById(tableBodyId);
    if (!tableBody) return;

    const rows = tableBody.querySelectorAll("tr");

    rows.forEach(function (row) {
      if (row.classList.contains("no-filter-result")) return;

      const rowDate = row.getAttribute("data-date") || "";
      const rowText = row.textContent.toLowerCase();

      // Check date range condition
      const matchesDate =
        !fromDate || !toDate || (rowDate >= fromDate && rowDate <= toDate);

      // Check search query condition
      const matchesSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchesDate && matchesSearch) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });

    // Handle empty state message
    const visibleRows = Array.from(rows).filter(
      (r) =>
        r.style.display !== "none" && !r.classList.contains("no-filter-result"),
    );
    let noResultRow = tableBody.querySelector(".no-filter-result");

    if (visibleRows.length === 0) {
      if (!noResultRow) {
        noResultRow = document.createElement("tr");
        noResultRow.className = "no-filter-result";

        noResultRow.innerHTML =
          '<td colspan="8" class="text-center text-muted py-4"><i class="bi bi-search me-1"></i> No matching records found</td>';

        tableBody.appendChild(noResultRow);
      }
      noResultRow.style.display = "";
    } else if (noResultRow) {
      noResultRow.style.display = "none";
    }
  };

  // Reset Filter Inputs (Foot & Vehicle Context Aware)
  window.clearFilter = function (context) {
    const isVehicle = context === "vehicle";

    const searchInputId = isVehicle
      ? "vehicle-table-search-input"
      : "table-search-input";
    const pickerId = isVehicle
      ? "vehicle-date-range-picker"
      : "date-range-picker";
    const fromInputId = isVehicle ? "vehicle-fromperiod" : "fromperiod";
    const toInputId = isVehicle ? "vehicle-toperiod" : "toperiod";

    const searchInput = document.getElementById(searchInputId);
    if (searchInput) searchInput.value = "";

    const rangePicker = document.getElementById(pickerId);
    if (rangePicker && rangePicker._flatpickr) {
      rangePicker._flatpickr.clear();
    }

    const fromInput = document.getElementById(fromInputId);
    const toInput = document.getElementById(toInputId);
    if (fromInput) fromInput.value = "";
    if (toInput) toInput.value = "";

    applyFilter(context);
  };

  // Void Record Handler
  window.confirmVoid = function (tableId, type = "store") {
  Swal.fire({
    title: "Void Record",
    html:
      '<div class="text-start mb-2"><label class="form-label small fw-bold">Reason for Voiding</label>' +
      '<textarea id="swal-void-reason" class="swal2-textarea m-0 w-100" placeholder="Enter reason" rows="2"></textarea></div>' +
      '<div class="text-start"><label class="form-label small fw-bold">Approver Postcode</label>' +
      '<input id="swal-approver-postcode" type="password" class="swal2-input m-0 w-100" placeholder="Enter postcode" inputmode="numeric" pattern="[0-9]*" autocomplete="off"></div>',
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Void Record",
    cancelButtonText: "Cancel",
    confirmButtonColor: "#d33",
    reverseButtons: true,
    didOpen: () => {
      const postcodeInput = document.getElementById("swal-approver-postcode");
      if (postcodeInput) {
        postcodeInput.addEventListener("input", (e) => {
          e.target.value = e.target.value.replace(/\D/g, "");
        });
      }
    },
    preConfirm: function () {
      const reason = document.getElementById("swal-void-reason").value.trim();
      const postcode = document.getElementById("swal-approver-postcode").value.trim();

      if (!reason) {
        Swal.showValidationMessage("Please provide a reason for voiding.");
        return false;
      }

      if (!postcode) {
        Swal.showValidationMessage("An approver postcode is required.");
        return false;
      }

      if (!/^\d+$/.test(postcode)) {
        Swal.showValidationMessage("Postcode must contain numbers only.");
        return false;
      }

      return {
        postcode: postcode,
        reason: reason
      };
    },
  }).then(function (result) {
    if (result.isConfirmed) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = "index.php?tab=" + (type === "parking" ? "Parking" : "Store");

      const fields = {
        action: "void_footprint",
        otableid: tableId,
        type: type,
        approver_postcode: result.value.postcode,
        void_reason: result.value.reason
      };

      for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = value;
        form.appendChild(input);
      }

      document.body.appendChild(form);
      form.submit();
    }
  });
};

  // Universal Table Column Sort
  window.sortTable = function (columnIndex, tableId, dataType = "string") {
    const table = document.getElementById(tableId);
    if (!table) return;

    const tbody = table.querySelector("tbody");
    const headers = table.querySelectorAll("th.sortable");
    const targetHeader = headers[columnIndex];

    if (!targetHeader || !tbody) return;

    // Determine direction: default to ascending, toggle if already sorted
    let isAscending = true;
    if (targetHeader.classList.contains("sort-asc")) {
      isAscending = false;
    }

    // Reset all headers' sort indicators
    headers.forEach((th) => {
      th.classList.remove("sort-asc", "sort-desc");
      const icon = th.querySelector(".sort-icon");
      if (icon) icon.textContent = "↕";
    });

    // Apply new sort class and icon to clicked header
    targetHeader.classList.add(isAscending ? "sort-asc" : "sort-desc");
    const activeIcon = targetHeader.querySelector(".sort-icon");
    if (activeIcon) activeIcon.textContent = isAscending ? "▲" : "▼";

    // Get rows excluding the 'no matching records' placeholder row
    const rows = Array.from(tbody.querySelectorAll("tr")).filter(
      (row) => !row.classList.contains("no-filter-result"),
    );

    // Sort rows based on column data type
    rows.sort((rowA, rowB) => {
      const cellA = rowA.children[columnIndex]?.textContent.trim() || "";
      const cellB = rowB.children[columnIndex]?.textContent.trim() || "";

      let comparison = 0;

      if (dataType === "number") {
        const numA = parseFloat(cellA.replace(/[^0-9.-]+/g, "")) || 0;
        const numB = parseFloat(cellB.replace(/[^0-9.-]+/g, "")) || 0;
        comparison = numA - numB;
      } else {
        comparison = cellA.localeCompare(cellB, undefined, {
          numeric: true,
          sensitivity: "base",
        });
      }

      return isAscending ? comparison : -comparison;
    });

    // Re-append sorted rows to the table body
    rows.forEach((row) => tbody.appendChild(row));

    // Ensure 'No records found' stays at the bottom if present
    const noResultRow = tbody.querySelector(".no-filter-result");
    if (noResultRow) {
      tbody.appendChild(noResultRow);
    }
  };


// VEHICLE MODAL
  const vehicleModal = document.getElementById('vehicleModal');
  const openVehicleBtn = document.getElementById('openVehicleModalBtn');
  const closeVehicleBtn = document.getElementById('closeVehicleModalBtn');

  if (openVehicleBtn && vehicleModal) {
    openVehicleBtn.addEventListener('click', () => vehicleModal.classList.add('active'));
  }

  if (closeVehicleBtn && vehicleModal) {
    closeVehicleBtn.addEventListener('click', () => vehicleModal.classList.remove('active'));
  }

  // Close vehicle modal when clicking outside the content box
  window.addEventListener('click', (e) => {
    if (vehicleModal && e.target === vehicleModal) {
      vehicleModal.classList.remove('active');
    }
  });

  // VIEWING EYE ICON MODAL (Exposed globally to support inline onclick attribute)
  window.openTrafficModal = function (button) {
  const trafficModal = document.getElementById('trafficDetailModal');
  const modalTimeRange = document.getElementById('modalTimeRange');
  const modalTableBody = document.getElementById('modalTableBody');

  if (!trafficModal) return;

  // 1. Reset all view buttons back to closed eye state
  document.querySelectorAll('.view_btn img').forEach((img) => {
    img.src = 'assets/images/eyeclose.png';
    img.alt = 'eyesclose';
  });

  // 2. Change the clicked button's image to eyeopen
  const activeImg = button.querySelector('img');
  if (activeImg) {
    activeImg.src = 'assets/images/eyeopen.png';
    activeImg.alt = 'eyesopen';
  }

  // Populate modal data
  const timeRange = button.getAttribute('data-timerange') || '';
  let details = [];

  try {
    details = JSON.parse(button.getAttribute('data-details')) || [];
  } catch (e) {
    details = [];
  }

  if (modalTimeRange) {
    modalTimeRange.textContent = `Time Range: ${timeRange}`;
  }

  if (modalTableBody) {
    modalTableBody.innerHTML = '';

    if (details.length > 0) {
      details.forEach((item) => {
        const row = `
          <tr>
            <td>${item.type}</td>
            <td class="text-center"><strong>${item.count}</strong></td>
          </tr>
        `;
        modalTableBody.innerHTML += row;
      });
    } else {
      modalTableBody.innerHTML = `
        <tr><td>2-Wheels</td><td class="text-center">&mdash;</td></tr>
        <tr><td>3-Wheels</td><td class="text-center">&mdash;</td></tr>
        <tr><td>4-Wheels</td><td class="text-center">&mdash;</td></tr>
        <tr><td>6-Wheels</td><td class="text-center">&mdash;</td></tr>
      `;
    }
  }

  trafficModal.classList.add('active');
};

// Helper function to reset all icons when modal closes
function resetEyeIcons() {
  document.querySelectorAll('.view_btn img').forEach((img) => {
    img.src = 'assets/images/eyeclose.png';
    img.alt = 'eyesclose';
  });
}

// 3. Attach reset handler to close button and backdrop click
const trafficModal = document.getElementById('trafficDetailModal');
const closeTrafficBtn = document.getElementById('closeTrafficModalBtn');

if (closeTrafficBtn && trafficModal) {
  closeTrafficBtn.addEventListener('click', () => {
    trafficModal.classList.remove('active');
    resetEyeIcons();
  });
}

window.addEventListener('click', (e) => {
  if (trafficModal && e.target === trafficModal) {
    trafficModal.classList.remove('active');
    resetEyeIcons();
  }
});

});
