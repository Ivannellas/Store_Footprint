<?php
$base_path = "../../";

require_once $base_path . 'includes/auth_check.php';
require_once $base_path . 'controller/store_dashboard_controller.php';

$conn = getDBConnection();

$default_dates = getStoreDashboardDateRange($conn);
$start_date    = !empty($_GET['start_date']) ? $_GET['start_date'] : $default_dates['start_date'];
$end_date      = !empty($_GET['end_date'])   ? $_GET['end_date']   : $default_dates['end_date'];

$metrics = getStoreDashboardMetrics($conn, $start_date, $end_date);

$leaderboard_data = $metrics['leaderboard_data'];
$queue_data       = $metrics['queue_data'];
$sales_data       = $metrics['sales_data'];
$cashier_data     = $metrics['cashier_data'];

// Maps to associate throughput per salesman
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
    <title>Employee Performance</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/media.css">
    <link rel="icon" href="<?php echo $base_path; ?>assets/images/favicon.png">
</head>

<body class="bg-light">

    <div class="d-flex main_parent">
        <?php include $base_path . 'includes/sidebar.php'; ?>
        <div class="wrapper">
            <div class="main-content w-100 d-flex flex-column">

                <div class="">

                    <div class="text-center mb-4">
                        <h2 class="title text-dark font-weight-bold" style="color: #0c5460; letter-spacing: 1px;">Ranking of salemen based on total sales amount</h2>
                    </div>

                    <a href="../dashboards/store_dashboard_main.php" class="text-decoration-none small text-muted d-block mb-2">
                        &lt; Back
                    </a>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-4">
                            <input type="text" id="employeeSearch" class="form-control" placeholder="Search employees...">
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm" style="border-radius: 8px; overflow: hidden;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="employeeTable">
                                <thead style="background-color: #0f4c81; color: white;">
                                    <tr>
                                        <th class="py-3 px-4" style="width: 5%;">Rank</th>
                                        <th class="py-3" style="width: 10%;">Salesman Name</th>
                                        <th class="py-3 text-center" style="width: 10%;">Actions</th>
                                        <th class="py-3 text-center" style="width: 20%;">Queue Transaction Count</th>
                                        <th class="py-3 text-center" style="width: 20%;">Sales Transaction Count</th>
                                        <th class="py-3 text-center" style="width: 20%;">Cashier Transaction Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($leaderboard_data)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                No salesmen records found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                        $counter = 1;
                                        foreach ($leaderboard_data as $emp):
                                            $name = $emp['osalesman'];
                                            $q_total = $queue_map[$name] ?? 0;
                                            $s_total = $sales_map[$name] ?? 0;
                                            $c_total = $cashier_map[$name] ?? 0;

                                            $badge_style = "background-color: #f1f5f9; color: #475569;";
                                            if ($counter === 1) {
                                                $badge_style = "background-color: #fef3c7; color: #d97706; font-weight: bold;"; 
                                            } elseif ($counter === 2) {
                                                $badge_style = "background-color: #e2e8f0; color: #475569; font-weight: bold;"; 
                                            } elseif ($counter === 3) {
                                                $badge_style = "background-color: #ffedd5; color: #c2410c; font-weight: bold;"; 
                                            }
                                        ?>
                                            <tr>
                                                <td class="px-4 text-center">
                                                    <span class="badge rounded-circle p-3" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; <?php echo $badge_style; ?>">
                                                        <?php echo $counter++; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong class="text-dark d-block"><?php echo htmlspecialchars($name); ?></strong>
                                                </td>
                                                <td class="text-center">
                                                    <a href="salesman_trends.php?salesman=<?php echo urlencode($name); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="eye_btn">
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border px-3 py-2">
                                                        <?php echo number_format($q_total); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border px-3 py-2">
                                                        <?php echo number_format($s_total); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border px-3 py-2">
                                                        <?php echo number_format($c_total); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div> <!-- /container-fluid -->



            </div> <!-- /main-content -->
        </div> <!-- wrapper -->
    </div> <!-- /d-flex -->

    <!-- Footer rendered at bottom of main-content -->
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script src="<?php echo $base_path; ?>assets/js/sidebar_button.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/employee.js"></script>
</body>

</html>