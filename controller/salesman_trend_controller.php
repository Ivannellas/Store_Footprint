<?php

$base_path = "../";
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../entity/salesman_trend_model.php';

function getSalesmenDropdownList(mysqli $conn): array {
    return fetchSalesmenList($conn);
}

function getSalesmanTrendData(mysqli $conn, string $start_date, string $end_date, string $salesman = '', string $timeframe = 'daily'): array {
    $result = fetchSalesmanTrendRaw($conn, $start_date, $end_date, $salesman, $timeframe);
    $trends = [];

    $rank_result = fetchSalesmanRankTrendRaw($conn, $start_date, $end_date, $timeframe);
    $period_ranks = [];

    if ($rank_result) {
        $current_label = null;
        $rank = 0;
        $salesmen_in_period = 0;

        while ($rank_row = mysqli_fetch_assoc($rank_result)) {
            $label = $rank_row['label_date'];

            if ($label !== $current_label) {
                $current_label = $label;
                $rank = 0;
                $salesmen_in_period = 0;
            }

            $rank++;
            $salesmen_in_period++;
            $period_ranks[$label]['ranks'][$rank_row['osalesman']] = $rank;
            $period_ranks[$label]['total_salesmen'] = $salesmen_in_period;
        }
    }

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $label = $row['label_date'];
            $true_rank = $period_ranks[$label]['ranks'][$salesman] ?? 0;
            $total_salesmen = $period_ranks[$label]['total_salesmen'] ?? 0;

            $trends[] = [
                'label'    => $label,
                'salesman' => $row['osalesman'] ?? '',
                'queue'    => (int)($row['total_queue'] ?? 0),
                'sales'    => (int)($row['total_sales'] ?? 0),
                'cashier'  => (int)($row['total_cashier'] ?? 0),
                'rank'     => $true_rank,
                'rank_score' => $true_rank > 0 ? (($total_salesmen - $true_rank) + 1) * 5 : 0,
            ];
        }
    }

    return $trends;
}

// Intercept direct AJAX requests 
if (isset($_GET['salesman_id']) || (isset($_GET['action']) && $_GET['action'] === 'fetch')) {
    error_reporting(0);
    ini_set('display_errors', 0);

    require_once __DIR__ . '/../includes/auth_check.php';
    $conn = getDBConnection();

    $salesman   = $_GET['salesman_id'] ?? $_GET['salesman'] ?? '';
    $timeframe  = $_GET['timeframe']   ?? 'daily';
    $start_date = $_GET['start_date']  ?? '';
    $end_date   = $_GET['end_date']    ?? '';

    $data = getSalesmanTrendData($conn, $start_date, $end_date, $salesman, $timeframe);

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
