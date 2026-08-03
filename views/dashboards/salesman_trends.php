<?php
$base_path = "../../";

require_once $base_path . 'includes/auth_check.php';
require_once $base_path . 'controller/store_dashboard_controller.php';

$conn = getDBConnection();
$selected_salesman = $_GET['salesman'] ?? '';

// Get date filters passed from main dashboard / employee page
$default_dates = getStoreDashboardDateRange($conn);
$start_date    = !empty($_GET['start_date']) ? $_GET['start_date'] : $default_dates['start_date'];
$end_date      = !empty($_GET['end_date'])   ? $_GET['end_date']   : $default_dates['end_date'];

// Fetch metrics filtered by date range
$metrics = getStoreDashboardMetrics($conn, $start_date, $end_date);

$queue_handled = 0;
$sales_closed  = 0;
$cashier_cycle = 0;

if (!empty($selected_salesman)) {
    // Calculate total Queue Handled for selected salesman
    foreach ($metrics['queue_data'] as $q) {
        if ($q['osalesman'] === $selected_salesman) {
            $queue_handled = $q['total_queue'];
            break;
        }
    }

    // Calculate total Sales Closed for selected salesman
    foreach ($metrics['sales_data'] as $s) {
        if ($s['osalesman'] === $selected_salesman) {
            $sales_closed = $s['total_sales'];
            break;
        }
    }

    // Calculate total Cashier Cycle for selected salesman
    foreach ($metrics['cashier_data'] as $c) {
        if ($c['osalesman'] === $selected_salesman) {
            $cashier_cycle = $c['total_cashier'];
            break;
        }
    }
}

$salesmen = [];

$sql = "SELECT DISTINCT osalesman
        FROM tbl_sales_performance
        WHERE osalesman IS NOT NULL
          AND osalesman != ''
        ORDER BY osalesman ASC";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $salesmen[] = $row['osalesman'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salesman Performance Trends </title>

    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/media.css">
    <link rel="icon" href="<?php echo $base_path; ?>assets/images/favicon.png">

    <script src="<?php echo $base_path; ?>node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="<?php echo $base_path; ?>node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>
</head>

<body>

    <div class="main_parent">
        <?php include $base_path . 'includes/sidebar.php'; ?>
        <div class="wrapper">
        <div class="main-content">
            <div class="container-fluid">
                  <div class="salesman_trend_top">
                        <div class="mb-4 d-flex flex-column gap-2">
                            <a href="employee.php?start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="text-decoration-none small text-muted d-block mb-2">< Back</a>
                            
                            <div class="text-uppercase small fw-bold text-muted tracking-wider mb-1">Performance Analysis</div>
                                <h2 class="title text-dark m-0 fw-bold">
                                    <span id="selectedSalesmanHeader">
                                        <?php echo htmlspecialchars($selected_salesman ?: 'Select Employee'); ?>
                                    </span>
                                </h2>
                                <p class="text-muted small m-0 mt-1">
                                    Trend analysis across queue handling, sales, and cashier.
                                </p>
                        </div>
                    

                        <div class="trend_buttons trend-nav-pill d-flex gap-2 mb-2">
                            <a href="#dailyChartCard" class="nav-link">Daily</a>
                            <a href="#monthlyChartCard" class="nav-link">Monthly</a>
                            <a href="#yearlyChartCard" class="nav-link">Yearly</a>
                        </div>
                </div>
          

                <div class="row g-3 mb-4">
                    <!-- Queue Transaction count -->
                    <div class="col-md-4 col-12">
                        <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid #f59e0b !important; border-radius: 12px;">
                            <div class="card-body p-3">
                                <div class="text-uppercase small fw-bold text-muted mb-2">Queue Transaction Count</div>
                                <h2 class="fw-bold text-dark m-0" id="QueueHandled"><?php echo number_format($queue_handled); ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Closed -->
                    <div class="col-md-4 col-12">
                        <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid  #10b981 !important; border-radius: 12px;">
                            <div class="card-body p-3">
                                <div class="text-uppercase small fw-bold text-muted mb-2">Sales Transaction Count</div>
                                <h2 class="fw-bold text-dark m-0" id="SalesClosed"><?php echo number_format($sales_closed); ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- Cashier Cycle -->
                    <div class="col-md-4 col-12">
                        <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid #424242 !important; border-radius: 12px;">
                            <div class="card-body p-3">
                                <div class="text-uppercase small fw-bold text-muted mb-2">Cashier Transaction Count</div>
                                <h2 class="fw-bold text-dark m-0" id="CashierCycle"><?php echo number_format($cashier_cycle); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="selectedSalesman" value="<?php echo htmlspecialchars($selected_salesman); ?>">

                <!-- Daily Breakdown Line Chart Card -->
                <div id="dailyChartCard" class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h5 class="card-title text-dark m-0 fw-bold">Daily Trend</h5>
                    </div>

                    <div class="card-body">
                        <div id="chartPlaceholder" class="text-center py-5 text-muted">
                            <p class="m-0">Select a salesman to view performance metrics.</p>
                        </div>

                        <div id="chartWrapper" class="chart-container d-none" style="position: relative; height: 380px; width: 100%;">
                            <canvas id="salesmanTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Breakdown Bar Chart Card -->
                <div id="monthlyChartCard" class="card shadow-sm border-0 mb-4 d-none">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h5 class="card-title text-dark m-0 fw-bold">Monthly Trend</h5>
                    </div>
                    <div class="card-body">
                        <div id="monthlyChartWrapper" class="chart-container" style="position: relative; height: 380px; width: 100%;">
                            <canvas id="monthlySalesmanTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Yearly Breakdown Line Chart Card -->
                <div id="yearlyChartCard" class="card shadow-sm border-0 d-none">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h5 class="card-title text-dark m-0 fw-bold">Yearly Trend</h5>
                    </div>
                    <div class="card-body">
                        <div id="yearlyChartWrapper" class="chart-container" style="position: relative; height: 380px; width: 100%;">
                            <canvas id="yearlySalesmanTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                </div>
             </div>
        </div>
    </div>

    <script src="<?php echo $base_path; ?>assets/js/sidebar_button.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/salesman_trends.js"></script>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</body>

</html>