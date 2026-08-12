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
  function initSearchableDropdown({ inputId, hiddenId, dropdownId, wrapperId }) {
    const personnelInput = document.getElementById(inputId);
    const nameStore = document.getElementById(hiddenId);
    const personnelDropdown = document.getElementById(dropdownId);
    const personnelWrapper = document.getElementById(wrapperId);

    if (!personnelInput || !personnelDropdown) return null;

    const options = Array.from(
      personnelDropdown.querySelectorAll(".personnel-dropdown-item")
    );

    const validPersonnelList = options.map((opt) =>
      opt.getAttribute("data-value")
    );

    let activeIndex = -1;

    function openDropdown() {
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
        if (nameStore) nameStore.value = selectedValue;

        if (updateInputValue) {
          personnelInput.value = selectedValue;
          validateSelection();
        }
      }
    }

    function validateSelection() {
      const currentText = personnelInput.value.trim();
      const matched = validPersonnelList.find(
        (name) => name.toLowerCase() === currentText.toLowerCase()
      );

      if (matched) {
        personnelInput.value = matched;
        if (nameStore) nameStore.value = matched;
        personnelInput.setCustomValidity("");
        return true;
      } else {
        if (nameStore) nameStore.value = "";
        personnelInput.setCustomValidity(
          "Please select a valid personnel from the dropdown."
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
            const selectedValue =
              visibleOptions[activeIndex].getAttribute("data-value");
            personnelInput.value = selectedValue;
            if (nameStore) nameStore.value = selectedValue;
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
        if (nameStore) nameStore.value = selectedValue;
        validateSelection();
        closeDropdown();
      });
    });

    document.addEventListener("click", function (e) {
      if (!e.target.closest(`#${wrapperId}`)) {
        closeDropdown();
      }
    });

    return { validateSelection, input: personnelInput };
  }

  // Initialize Searchable Dropdowns for both Foot Traffic and Parking
  const footDropdown = initSearchableDropdown({
    inputId: "personnelInput",
    hiddenId: "nameStore",
    dropdownId: "personnelDropdown",
    wrapperId: "personnelWrapper",
  });

  const parkingDropdown = initSearchableDropdown({
    inputId: "parkingPersonnelInput",
    hiddenId: "nameParking",
    dropdownId: "parkingPersonnelDropdown",
    wrapperId: "parkingPersonnelWrapper",
  });

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
        '[name="personnel_name"], [name="personnel"], [name="name"]'
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
      (r) => r.style.display !== "none" && r.dataset.date
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