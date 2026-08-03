<?php

/**
 * Gets the global min/max dates available in sales performance data.
 */
function fetchDashboardDateRange(mysqli $conn): array {
    $query = "SELECT MIN(odate) as start_date, MAX(odate) as end_date FROM tbl_sales_performance";
    $result = mysqli_query($conn, $query);
    return ($result && $row = mysqli_fetch_assoc($result)) ? $row : [];
}

/**
 * Gets overall salesman metrics grouped by salesman.
 */
function fetchOverallSalesmanMetrics(mysqli $conn, string $start, string $end) {
    $query = "SELECT 
                osalesman,
                SUM(ocnt_queue) as total_queue,
                SUM(ocnt_sales) as total_sales,
                SUM(ocnt_cashier) as total_cashier,
                SUM(ocnt_total) as grand_total,
                SUM(orank_amount_actualvalue) as total_actual_value
            FROM tbl_sales_performance 
            WHERE odate BETWEEN '$start' AND '$end'
            GROUP BY osalesman
            ORDER BY grand_total DESC";
    return mysqli_query($conn, $query);
}

/**
 * Gets overall rankings based on actual value.
 */
function fetchOverallLeaderboard(mysqli $conn, string $start, string $end) {
    $query = "SELECT 
                osalesman,
                SUM(orank_amount_actualvalue) as total_actual_value
            FROM tbl_sales_performance 
            WHERE odate BETWEEN '$start' AND '$end'
            GROUP BY osalesman
            ORDER BY total_actual_value DESC";
    return mysqli_query($conn, $query);
}

/**
 * Gets top 5 leaderboard
 */
function fetchCategoryLeaderboard(mysqli $conn, string $start, string $end, string $column, string $salesman): array {
    $query = "SELECT osalesman, SUM($column) as $salesman 
              FROM tbl_sales_performance 
              WHERE odate BETWEEN '$start' AND '$end' 
              GROUP BY osalesman ORDER BY $salesman DESC LIMIT 5";
    $result = mysqli_query($conn, $query);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Gets top 5 salesmen ordered by total transactions (ocnt_total)
 */
function fetchTop5SalesmenByTransactions(mysqli $conn, string $start, string $end): array {
    $query = "SELECT osalesman, SUM(ocnt_total) as grand_total 
              FROM tbl_sales_performance 
              WHERE odate BETWEEN '$start' AND '$end' 
              GROUP BY osalesman 
              ORDER BY grand_total DESC 
              LIMIT 5";
    $result = mysqli_query($conn, $query);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

