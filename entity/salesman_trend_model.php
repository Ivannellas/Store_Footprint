<?php

function fetchSalesmenList(mysqli $conn): array
{
    $sql = "SELECT DISTINCT osalesman
            FROM tbl_sales_performance
            WHERE osalesman IS NOT NULL
              AND osalesman != ''
            ORDER BY osalesman";

    $result = mysqli_query($conn, $sql);
    $salesmen = [];

    while ($result && $row = mysqli_fetch_assoc($result)) {
        $salesmen[] = $row['osalesman'];
    }

    return $salesmen;
}

function fetchSalesmanTrendRaw(
    mysqli $conn,
    string $start_date,
    string $end_date,
    string $salesman = '',
    string $timeframe = 'daily'
) {
    $startDate = mysqli_real_escape_string($conn, $start_date);
    $endDate = mysqli_real_escape_string($conn, $end_date);
    $salesmanName = mysqli_real_escape_string($conn, $salesman);

    $where = "WHERE 1=1";

    if ($startDate !== '' && $endDate !== '') {
        $where .= " AND odate BETWEEN '$startDate' AND '$endDate'";
    }

    if ($salesmanName !== '') {
        $where .= " AND osalesman = '$salesmanName'";
    }

    if ($startDate === '' && $endDate === '') {
        if ($timeframe === 'daily') {
            $where .= " AND odate BETWEEN
                DATE_FORMAT(CURDATE(), '%Y-%m-01')
                AND LAST_DAY(CURDATE())";
        }

        if ($timeframe === 'monthly') {
            $where .= " AND YEAR(odate) = YEAR(CURDATE())";
        }
    }

    if ($timeframe === 'monthly') {
        $label = "DATE_FORMAT(odate, '%m')";
        $sort = "MONTH(odate)";
    } elseif ($timeframe === 'yearly') {
        $label = "DATE_FORMAT(odate, '%Y')";
        $sort = "YEAR(odate)";
    } else {
        $label = "DATE_FORMAT(odate, '%d')";
        $sort = "odate";
    }

    $sql = "SELECT
                $label AS label_date,
                $sort AS sort_date,
                osalesman,
                SUM(ocnt_queue) AS total_queue,
                SUM(ocnt_sales) AS total_sales,
                SUM(ocnt_cashier) AS total_cashier
            FROM tbl_sales_performance
            $where
            GROUP BY sort_date, osalesman
            ORDER BY sort_date";

    return mysqli_query($conn, $sql);
}

/**
 * Gets every salesman's amount ranking for each trend period.  The controller
 * uses this result to attach the selected salesman's rank to its existing
 * trend records without changing the queue, sales, or cashier queries.
 */
function fetchSalesmanRankTrendRaw(
    mysqli $conn,
    string $start_date,
    string $end_date,
    string $timeframe = 'daily'
) {
    $startDate = mysqli_real_escape_string($conn, $start_date);
    $endDate = mysqli_real_escape_string($conn, $end_date);

    $where = "WHERE 1=1";

    if ($startDate !== '' && $endDate !== '') {
        $where .= " AND odate BETWEEN '$startDate' AND '$endDate'";
    }

    if ($startDate === '' && $endDate === '') {
        if ($timeframe === 'daily') {
            $where .= " AND odate BETWEEN
                DATE_FORMAT(CURDATE(), '%Y-%m-01')
                AND LAST_DAY(CURDATE())";
        }

        if ($timeframe === 'monthly') {
            $where .= " AND YEAR(odate) = YEAR(CURDATE())";
        }
    }

    if ($timeframe === 'monthly') {
        $label = "DATE_FORMAT(odate, '%m')";
        $sort = "MONTH(odate)";
    } elseif ($timeframe === 'yearly') {
        $label = "DATE_FORMAT(odate, '%Y')";
        $sort = "YEAR(odate)";
    } else {
        $label = "DATE_FORMAT(odate, '%d')";
        $sort = "odate";
    }

    $sql = "SELECT
                $label AS label_date,
                $sort AS sort_date,
                osalesman,
                SUM(orank_amount_actualvalue) AS total_actual_value
            FROM tbl_sales_performance
            $where
            GROUP BY sort_date, osalesman
            ORDER BY sort_date, total_actual_value DESC, osalesman ASC";

    return mysqli_query($conn, $sql);
}
