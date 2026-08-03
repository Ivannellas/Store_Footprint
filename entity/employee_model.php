<?php

/**
 * Fetches raw sales performance records from the database.
 * 
 * @param mysqli $conn
 * @return mysqli_result|bool
 */
function fetchEmployeePerformanceRaw(mysqli $conn) {
    $query = "SELECT 
                osalesman,
                SUM(ocnt_sales) AS total_sales,
                SUM(CASE WHEN odate >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN ocnt_sales ELSE 0 END) AS curr_sales,
                SUM(CASE WHEN odate >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN ocnt_queue ELSE 0 END) AS curr_queue,
                SUM(CASE WHEN odate >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN ocnt_cashier ELSE 0 END) AS curr_cashier,
                SUM(CASE WHEN odate BETWEEN DATE_SUB(CURDATE(), INTERVAL 13 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN ocnt_queue ELSE 0 END) AS prev_queue,
                SUM(CASE WHEN odate BETWEEN DATE_SUB(CURDATE(), INTERVAL 13 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN ocnt_cashier ELSE 0 END) AS prev_cashier
              FROM tbl_sales_performance 
              WHERE osalesman IS NOT NULL AND osalesman != '' 
              GROUP BY osalesman 
              ORDER BY osalesman ASC";      

    return mysqli_query($conn, $query);
}