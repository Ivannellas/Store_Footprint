<?php

$base_path = "../../";

require_once $base_path . 'includes/auth_check.php';
require_once $base_path . 'controller/store_dashboard_controller.php';

$conn = getDBConnection();

$default_start_date = date('Y-m-01');
$default_end_date   = date('Y-m-d');

$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : $default_start_date;
$end_date   = !empty($_GET['end_date'])   ? $_GET['end_date']   : $default_end_date;

$metrics = getStoreDashboardMetrics($conn, $start_date, $end_date);

$leaderboard_data = $metrics['leaderboard_data']; // Total Amount
$queue_data       = $metrics['queue_data'];       // Queue
$sales_data       = $metrics['sales_data'];       // Sales
$cashier_data     = $metrics['cashier_data'];     // Cashier
$chart_data       = $metrics['chart_data'];

// Helper maps to merge data easily by salesman name
$queue_map   = [];
foreach ($queue_data as $q) {
    $queue_map[$q['osalesman']]   = $q['total_queue'];
}

$sales_map   = [];
foreach ($sales_data as $s) {
    $sales_map[$s['osalesman']]   = $s['total_sales'];
}

$cashier_map = [];
foreach ($cashier_data as $c) {
    $cashier_map[$c['osalesman']] = $c['total_cashier'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salesman Performance - TDIY</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/media.css">
    <link rel="icon" href="<?php echo $base_path; ?>assets/images/favicon.png">
</head>

<body class="bg-light">

    <div class="main_parent">
        <!-- Dynamic Sidebar Injection -->
        <?php include $base_path . 'includes/sidebar.php'; ?>

        <div class="wrapper">
            <div class="main_holder">
                <!-- Header & Date Filter Row -->
                <div class="row align-items-center mb-4 g-3">
                    <div class="col-md-6 col-12">
                        <h3 class="title text-dark m-0">
                            Salesman Performance - <?php echo htmlspecialchars($_SESSION['store_name'] ?? 'Taboan Branch'); ?>
                        </h3>

                        <nav aria-label="breadcrumb" class="mt-1">
                            <ol class="breadcrumb m-0 small text-muted">
                                <li class="breadcrumb-item"><a href="../../index.php" class="text-decoration-none text-muted">Home</a></li>
                                <li class="breadcrumb-item active text-secondary" aria-current="page">Salesman Performance</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-12 d-flex justify-content-md-end justify-content-start">
                        <form method="GET" class="card p-2 shadow-sm border-0 bg-white w-100" style="max-width: 460px;">
                            <div class="row g-2 align-items-center">
                                <div class="col-sm-5 col-12">
                                    <div class="input-group input-group-sm">
                                        <span class="cal_btn input-group-text bg-primary text-white">From</span>
                                        <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                                    </div>
                                </div>
                                <div class="col-sm-5 col-12">
                                    <div class="input-group input-group-sm">
                                        <span class="cal_btn input-group-text bg-primary text-white">To</span>
                                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                                    </div>
                                </div>
                                <div class="col-sm-2 col-12 d-grid">
                                    <button type="submit" class="cal_btn btn btn-primary btn-sm fw-bold">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row g-3 mb-4 store_top_cards">
                    <!-- Queue Transaction Count Card-->
                    <div class="col-md-3 col-12">
                        <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid #f59e0b !important; border-radius: 12px;">
                            <div class="card-body card-icon-queue p-4">
                                <div class="card-content text-uppercase small fw-bold text-muted mb-2">Queue Transaction Count
                                    <h2 class="fw-bold text-dark m-0" id="total_queue"><?php echo number_format(array_sum($chart_data['queues'] ?? [])); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Transaction Count Card -->
                    <div class="col-md-3 col-12">
                        <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid #1e3a8a !important; border-radius: 12px;">
                            <div class="card-body card-icon-sales p-4">
                                <div class="card-content text-uppercase small fw-bold text-muted mb-2">Sales Transaction Count
                                    <h2 class="fw-bold text-dark m-0" id="SalesTransactionCount"><?php echo number_format(array_sum($chart_data['sales'] ?? [])); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cashier Transaction Count Card -->
                    <div class="col-md-3 col-12">
                        <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid #00a852 !important; border-radius: 12px;">
                            <div class="card-body card-icon-cashier p-4">
                                <div class="card-content text-uppercase small fw-bold text-muted mb-2">Cashier Transaction Count
                                    <h2 class="fw-bold text-dark m-0" id="CashierTransactionCount"><?php echo number_format(array_sum($chart_data['cashiers'] ?? [])); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Transaction Count Card -->
                    <div class="col-md-3 col-12">
                        <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid #0077b6 !important; border-radius: 12px;">
                            <div class="card-body card-icon-total p-4">
                                <div class="card-content text-uppercase small fw-bold text-muted mb-2">Total Transaction Count
                                    <h2 class="fw-bold text-dark m-0" id="TotalTransactionCount"><?php echo number_format(array_sum($chart_data['totals'] ?? [])); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FULL WIDTH TRANSACTION BY SALESMAN, PERFORMANCE CHART -->
                <div class="row mb-4 salesman_performance_parent">
                    <!-- TRANSACTION BY SALESMAN DATA CHART -->
                    <div class="col-3 transaction_chart">
                        <div class="dashboard-card bg-white p-3 shadow-sm rounded">
                            <div class="mb-3">
                                <h5 class="fw-bold text-dark m-0">Transaction by Salesman</h5>
                                <p class="text-muted small m-0 mt-1">
                                    Number of completed Total transactions
                                </p>
                            </div>
                            <div class="chart-container" style="position: relative; height: 275px;">
                                <canvas id="transactionChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- PERFORMANCE DATA CHART -->
                    <div class="col-9 performance_chart">
                        <div class="dashboard-card bg-white p-3 shadow-sm rounded">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark m-0">Performance Data</h5>
                                <div class="d-flex align-items-center">
                                    <label for="chartSortSelect" class="small text-muted me-2 fw-semibold mb-0">Sort By:</label>
                                    <select id="chartSortSelect" class="form-select form-select-sm" style="width: 170px;">
                                        <option value="total">Total</option>
                                        <option value="queue">Queue</option>
                                        <option value="sales">Sales </option>
                                        <option value="cashier">Cashier </option>
                                        <option value="rank">Top Seller Rank</option>
                                    </select>
                                </div>
                            </div>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="dataChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- UNIFIED SALESMAN RANKING TABLE -->
                <div class="row">
                    <div class="rank_chart_parent">
                        <div class="card p-3 shadow-sm border-0 bg-white rounded">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="salesman_ranking_title">
                                    <h5 class="fw-bold text-dark m-0">Salesman Ranking </h5>
                                    <p class="text-muted small m-0 mt-1">Ranking of salemen based on total sales amount</p>
                                </div>
                                <!-- View All Button -->
                                <a href="employee.php?start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>"
                                    class="btn btn-sm btn-outline-primary fw-semibold px-3 rounded-2">
                                    View All
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-borderless align-middle m-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="text-center" style="width: 60px;">Rank</th>
                                            <th scope="col">Salesman</th>
                                            <th scope="col" class="text-center">Queue Transaction Count</th>
                                            <th scope="col" class="text-center">Sales Transaction Count</th>
                                            <th scope="col" class="text-center">Cashier Transaction Count</th>
                                            <th scope="col" class="text-center">Total Transactions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $rank = 1;
                                        // Limit leaderboard output to Top 5
                                        $top5_leaderboard = array_slice($leaderboard_data, 0, 5);

                                        foreach ($top5_leaderboard as $row) {
                                            $name = $row['osalesman'];
                                            $q_total = $queue_map[$name] ?? 0;
                                            $s_total = $sales_map[$name] ?? 0;
                                            $c_total = $cashier_map[$name] ?? 0;

                                            $total_count = $q_total + $s_total + $c_total;

                                            $badge_style = "background-color: #f1f5f9; color: #475569;";
                                            if ($rank === 1) $badge_style = "background-color: #fef3c7; color: #d97706; font-weight: bold;";
                                            elseif ($rank === 2) $badge_style = "background-color: #e2e8f0; color: #475569; font-weight: bold;";
                                            elseif ($rank === 3) $badge_style = "background-color: #ffedd5; color: #c2410c; font-weight: bold;";

                                            echo "<tr>";
                                            echo "<td class='text-center'><span class='badge rounded-circle p-3' style='width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; {$badge_style}'>" . $rank++ . "</span></td>";
                                            echo "<td class='fw-semibold text-dark'>" . htmlspecialchars($name) . "</td>";
                                            echo "<td class='text-center'>" . number_format($q_total) . "</td>";
                                            echo "<td class='text-center'>" . number_format($s_total) . "</td>";
                                            echo "<td class='text-center'>" . number_format($c_total) . "</td>";
                                            echo "<td class='text-center'>" . number_format($total_count) . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
    <script src="<?php echo $base_path; ?>node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_path; ?>node_modules/chart.js/dist/chart.umd.js"></script>

    <script>
        window.chartLabels = <?php echo json_encode($chart_data['names']); ?>;
        window.chartQueues = <?php echo json_encode($chart_data['queues']); ?>;
        window.chartSales = <?php echo json_encode($chart_data['sales']); ?>;
        window.chartCashiers = <?php echo json_encode($chart_data['cashiers']); ?>;
        window.chartTotals = <?php echo json_encode($chart_data['totals']); ?>;

        window.chartTotalActualValueRanks = <?php echo json_encode($chart_data['totalActualValueRanks']); ?>;
        window.chartTrueRankMap = <?php echo json_encode($chart_data['trueRankMap']); ?>;

        window.top5Names = <?php echo json_encode($metrics['top5_data']['names']); ?>;
        window.top5Totals = <?php echo json_encode($metrics['top5_data']['totals']); ?>;
    </script>
    <script src="<?php echo $base_path; ?>assets/js/dashboard_chart.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/sidebar_button.js"></script>

</body>

</html>