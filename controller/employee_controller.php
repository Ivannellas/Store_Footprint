<?php

require_once __DIR__ . '/../entity/employee_model.php';

/**
 * Processes employee performance metrics and calculates percentage changes.
 *
 * @param mysqli $conn
 * @return array
 */
function getEmployeePerformanceData(mysqli $conn): array
{
    $salesmen = [];
    $result = fetchEmployeePerformanceRaw($conn);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $salesmen[] = $row;
        }
    }

    return $salesmen;
}