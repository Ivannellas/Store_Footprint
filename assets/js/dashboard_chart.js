// Chart.defaults.font.family = "'Quicksand', 'sans-serif', 'Segoe UI';";
Chart.defaults.font.size = 11;

document.addEventListener("DOMContentLoaded", function () {
  const canvas = document.getElementById("dataChart");
  if (!canvas) return;

  const ctx = canvas.getContext("2d");

  // Keep a structured master copy of original data items
  const originalData = (window.chartLabels || []).map((label, index) => ({
    label: label,
    queue: window.chartQueues ? window.chartQueues[index] : 0,
    sales: window.chartSales ? window.chartSales[index] : 0,
    cashier: window.chartCashiers ? window.chartCashiers[index] : 0,
    total: window.chartTotals ? window.chartTotals[index] : 0,
    rankValue: window.chartTotalActualValueRanks ? window.chartTotalActualValueRanks[index] : 0,
  }));

  // Initialize Performance Data Chart
  const dataChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: window.chartLabels,
      datasets: [
        {
          label: "Queue ",
          data: window.chartQueues,
          backgroundColor: "#f57c00",
        },
        {
          label: "Sales ",
          data: window.chartSales,
          backgroundColor: "#0077b6",
        },
        {
          label: "Cashier ",
          data: window.chartCashiers,
          backgroundColor: "#424242",
        },
        {
          label: "Total ",
          data: window.chartTotals,
          backgroundColor: "#2e7d32",
        },
        {
          label: "Top Seller by Amount",
          data: window.chartTotalActualValueRanks,
          backgroundColor: "#1a237e",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function (context) {
              let label = context.dataset.label || "";
              let value = context.raw;

              if (label.trim() === "Top Seller by Amount") {
                const salesmanName = dataChart.data.labels[context.dataIndex];
                const trueRank = (window.chartTrueRankMap && window.chartTrueRankMap[salesmanName]) || "N/A";

                return label + ": Rank " + trueRank;
              }

              return label + ": " + value;
            },
          },
        },
      },
    },
  });

  // Dynamic Sorting Functionality
  const sortSelect = document.getElementById("chartSortSelect");
  if (sortSelect) {
    sortSelect.addEventListener("change", function () {
      const sortBy = this.value;

      // Create a shallow copy to sort
      let sortedItems = [...originalData];

      if (sortBy !== "default") {
        sortedItems.sort((a, b) => {
          if (sortBy === "queue") return b.queue - a.queue;
          if (sortBy === "sales") return b.sales - a.sales;
          if (sortBy === "cashier") return b.cashier - a.cashier;
          if (sortBy === "total") return b.total - a.total;
          if (sortBy === "rank") return b.rankValue - a.rankValue;
          return 0;
        });
      }

      // Re-assign sorted values back to chart
      dataChart.data.labels = sortedItems.map((item) => item.label);
      dataChart.data.datasets[0].data = sortedItems.map((item) => item.queue);
      dataChart.data.datasets[1].data = sortedItems.map((item) => item.sales);
      dataChart.data.datasets[2].data = sortedItems.map((item) => item.cashier);
      dataChart.data.datasets[3].data = sortedItems.map((item) => item.total);
      dataChart.data.datasets[4].data = sortedItems.map((item) => item.rankValue);

      // Re-render chart smoothly
      dataChart.update();
    });
  }
});

// Horizontal Top 5 Chart
document.addEventListener("DOMContentLoaded", function () {
  const salesmanCanvas = document.getElementById("transactionChart");
  if (!salesmanCanvas) return;

  const salesmanCtx = salesmanCanvas.getContext("2d");

  new Chart(salesmanCtx, {
    type: "bar",
    data: {
      labels: window.top5Names || [],
      datasets: [
        {
          label: "Total Transactions",
          data: window.top5Totals || [],
          backgroundColor: "#2e7d32",
          borderRadius: 4,
          barThickness: 20,
        },
      ],
    },
    options: {
      indexAxis: "y",
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: { precision: 0 },
        },
        y: {
          grid: { display: false },
        },
      },
    },
  });
});