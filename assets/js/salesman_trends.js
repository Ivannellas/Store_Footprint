let trendChart = null;
let monthlyChart = null;
let yearlyChart = null;

function loadSalesmanTrend() {
  const salesman = document.getElementById("selectedSalesman").value;
  const placeholder = document.getElementById("chartPlaceholder");
  const chartBox = document.getElementById("chartWrapper");
  const monthlyCard = document.getElementById("monthlyChartCard");
  const yearlyCard = document.getElementById("yearlyChartCard");
  const title = document.getElementById("selectedSalesmanHeader");

  // Reset UI if no salesman is selected
  if (!salesman) {
    placeholder.classList.remove("d-none");
    chartBox.classList.add("d-none");
    monthlyCard.classList.add("d-none");
    yearlyCard.classList.add("d-none");
    title.textContent = "Select Employee";
    return;
  }

  placeholder.classList.add("d-none");
  title.textContent = salesman;

  const baseUrl = "../../controller/salesman_trend_controller.php?salesman_id=" + encodeURIComponent(salesman);

  // Fetch Daily Data (Line Chart)
  fetchData(`${baseUrl}&timeframe=daily`).then((data) => {
    chartBox.classList.remove("d-none");

    const today = new Date().getDate();
    const records = {};
    data.forEach((item) => (records[item.label] = item));

    let labels = [], queues = [], sales = [], cashiers = [], ranks = [], rankScores = [];

    for (let day = 1; day <= today; day++) {
      const dayLabel = String(day).padStart(2, "0");
      const rec = records[dayLabel];

      labels.push(dayLabel);
      queues.push(rec ? rec.queue : 0);
      sales.push(rec ? rec.sales : 0);
      cashiers.push(rec ? rec.cashier : 0);
      ranks.push(rec ? rec.rank : 0);
      rankScores.push(rec ? rec.rank_score : 0);
    }

    drawLineChart(labels, queues, sales, cashiers, ranks, rankScores);
  });

  // Fetch Monthly Data (Bar Chart)
  fetchData(`${baseUrl}&timeframe=monthly`).then((data) => {
    monthlyCard.classList.remove("d-none");

    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const records = {};
    data.forEach((item) => (records[item.label] = item));

    const queues = months.map((m, idx) => (records[String(idx + 1).padStart(2, "0")] || records[m])?.queue || 0);
    const sales = months.map((m, idx) => (records[String(idx + 1).padStart(2, "0")] || records[m])?.sales || 0);
    const cashiers = months.map((m, idx) => (records[String(idx + 1).padStart(2, "0")] || records[m])?.cashier || 0);
    const ranks = months.map((m, idx) => (records[String(idx + 1).padStart(2, "0")] || records[m])?.rank || 0);
    const rankScores = months.map((m, idx) => (records[String(idx + 1).padStart(2, "0")] || records[m])?.rank_score || 0);

    drawBarChart(months, queues, sales, cashiers, ranks, rankScores);
  });

  // Fetch Yearly Data (Line Chart)
  fetchData(`${baseUrl}&timeframe=yearly`).then((data) => {
    yearlyCard.classList.remove("d-none");

    const labels = data.map((item) => item.label);
    const queues = data.map((item) => item.queue);
    const sales = data.map((item) => item.sales);
    const cashiers = data.map((item) => item.cashier);
    const ranks = data.map((item) => item.rank);
    const rankScores = data.map((item) => item.rank_score);

    drawYearlyChart(labels, queues, sales, cashiers, ranks, rankScores);
  });
}

function fetchData(url) {
  return fetch(url)
    .then((res) => res.json())
    .catch((err) => console.error("Error:", err));
}

// Render Daily Line Chart
function drawLineChart(labels, queues, sales, cashiers, ranks, rankScores) {
  if (trendChart) trendChart.destroy();

  trendChart = new Chart(document.getElementById("salesmanTrendChart"), {
    type: "line",
    data: {
      labels,
      datasets: [
        { label: "Queue", data: queues, borderColor: "#f59e0b", backgroundColor: "#f59e0b" },
        { label: "Sales", data: sales, borderColor: "#2e7d32", backgroundColor: "#2e7d32" },
        { label: "Cashier", data: cashiers, borderColor: "#424242", backgroundColor: "#424242" },
        { label: "Top Seller Rank By Amount", data: rankScores, rankData: ranks, borderColor: "#1a237e", backgroundColor: "#1a237e" },
      ],
    },
    options: buildTrendChartOptions(rankScores),
  });
}

// Render Monthly Bar Chart
function drawBarChart(labels, queues, sales, cashiers, ranks, rankScores) {
  if (monthlyChart) monthlyChart.destroy();

  const options = buildTrendChartOptions(rankScores);

  options.plugins.datalabels = {
    anchor: 'end',
    align: 'start',
    offset: 4,
    font: {
      weight: 'normal',
      size: 11
    },
    color: '#ffff',
    formatter: function(value, context) {
      if (value === 0) return ''; 

      if (context.dataset.label === "Top Seller Rank By Amount") {
        const rank = context.dataset.rankData[context.dataIndex];
        return rank ? `${rank}` : '';
      }

      return value;
    }
  };

  monthlyChart = new Chart(document.getElementById("monthlySalesmanTrendChart"), {
    type: "bar",
    plugins: [ChartDataLabels], // Registered locally
    data: {
      labels,
      datasets: [
        { label: "Queue", data: queues, backgroundColor: "#f59e0b", borderRadius: 7 },
        { label: "Sales", data: sales, backgroundColor: "#2e7d32", borderRadius: 7 },
        { label: "Cashier", data: cashiers, backgroundColor: "#424242", borderRadius: 7 },
        { label: "Top Seller Rank By Amount", data: rankScores, rankData: ranks, backgroundColor: "#1a237e", borderRadius: 7 },
      ],
    },
    options: options,
  });
}

// Render Yearly Line Chart
function drawYearlyChart(labels, queues, sales, cashiers, ranks, rankScores) {
  if (yearlyChart) yearlyChart.destroy();

  yearlyChart = new Chart(document.getElementById("yearlySalesmanTrendChart"), {
    type: "line",
    data: {
      labels,
      datasets: [
        { label: "Queue", data: queues, borderColor: "#f59e0b", fill: true, backgroundColor: 'rgba(245, 158, 11, 0.1)' },
        { label: "Sales", data: sales, borderColor: "#2e7d32", fill: true, backgroundColor: 'rgba(46, 125, 50, 0.1)' },
        { label: "Cashier", data: cashiers, borderColor: "#424242", fill: true, backgroundColor: 'rgba(66, 66, 66, 0.1)' },
        { label: "Top Seller Rank By Amount", data: rankScores, rankData: ranks, borderColor: "#1a237e", fill: true, backgroundColor: 'rgba(26, 35, 126, 0.1)' },
      ],
    },
    options: buildTrendChartOptions(rankScores),
  });
}

function buildTrendChartOptions(rankScores) {
  const highestRankScore = Math.max(...rankScores, 0);

  return {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        beginAtZero: true,
        suggestedMax: highestRankScore > 0 ? highestRankScore * 1.1 : undefined,
      },
    },
    plugins: {
      tooltip: {
        callbacks: {
          label(context) {
            if (context.dataset.label === "Top Seller Rank") {
              const rank = context.dataset.rankData[context.dataIndex];
              return rank ? `Top Seller Rank: Rank ${rank}` : "Top Seller Rank: Unranked";
            }

            return `${context.dataset.label}: ${context.raw}`;
          },
        },
      },
    },
  };
}

document.addEventListener("DOMContentLoaded", () => {
  const selectedSalesman = document.getElementById("selectedSalesman");
  if (selectedSalesman.value) loadSalesmanTrend();
});
