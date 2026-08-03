<?php


require_once $base_path . 'entity/store_dashboard_model.php';

function getStoreDashboardDateRange(mysqli $conn): array {
    $data = fetchDashboardDateRange($conn);
    return [
        'start_date' => $data['start_date'] ?? date('Y-m-d'),
        'end_date'   => $data['end_date'] ?? date('Y-m-d')
    ];
}

function getStoreDashboardMetrics(mysqli $conn, string $start_date, string $end_date): array {
    $date_start = mysqli_real_escape_string($conn, $start_date);
    $date_end   = mysqli_real_escape_string($conn, $end_date);

    // Call Model functions
    $main_result = fetchOverallSalesmanMetrics($conn, $date_start, $date_end);
    $lb_result   = fetchOverallLeaderboard($conn, $date_start, $date_end);

    $queue_data   = fetchCategoryLeaderboard($conn, $date_start, $date_end, 'ocnt_queue', 'total_queue');
    $sales_data   = fetchCategoryLeaderboard($conn, $date_start, $date_end, 'ocnt_sales', 'total_sales');
    $cashier_data = fetchCategoryLeaderboard($conn, $date_start, $date_end, 'ocnt_cashier', 'total_cashier');
    $total_data = fetchCategoryLeaderboard($conn, $date_start, $date_end, 'ocnt_total', 'total_count');
    
    // FETCH TOP 5 BY TRANSACTIONS
    $top5_result  = fetchTop5SalesmenByTransactions($conn, $date_start, $date_end);
    $top5_names   = array_column($top5_result, 'osalesman');
    $top5_totals  = array_map('intval', array_column($top5_result, 'grand_total'));

    // Process Ranks
    $leaderboard_data  = [];
    $leaderboard_ranks = [];
    $rank_counter      = 1;

    if ($lb_result) {
        while ($row = mysqli_fetch_assoc($lb_result)) {
            $leaderboard_data[] = $row;
            $leaderboard_ranks[$row['osalesman']] = $rank_counter++;
        }
    }

    // Process Chart Data
    $names = []; $queues = []; $sales = []; $cashiers = []; $totals = [];
    $chart_scores = []; $true_ranks = [];
    $total_salesmen = count($leaderboard_ranks);

    if ($main_result) {
        while ($row = mysqli_fetch_assoc($main_result)) {
            $salesman = $row['osalesman'];
            $names[]    = $salesman;
            $queues[]   = (int)$row['total_queue'];
            $sales[]    = (int)$row['total_sales'];
            $cashiers[] = (int)$row['total_cashier'];
            $totals[]   = (int)$row['grand_total'];

            if (isset($leaderboard_ranks[$salesman])) {
                $rank                      = $leaderboard_ranks[$salesman];
                $chart_scores[]            = (($total_salesmen - $rank) + 1) * 50;
                $true_ranks[$salesman]     = $rank;
            } else {
                $chart_scores[]            = 0;
                $true_ranks[$salesman]     = "Unranked";
            }
        }
    }

    return [
        'leaderboard_data' => $leaderboard_data,
        'queue_data'       => $queue_data,
        'sales_data'       => $sales_data,
        'cashier_data'     => $cashier_data,
        'total_data'      => $total_data,
        'top5_data'        => [
            'names'  => $top5_names,
            'totals' => $top5_totals
        ],
        'chart_data'       => [
            'names'                 => $names,
            'queues'                => $queues,
            'sales'                 => $sales,
            'cashiers'              => $cashiers,
            'totals'                => $totals,
            'totalActualValueRanks' => $chart_scores,
            'trueRankMap'           => $true_ranks
        ]
    ];
}