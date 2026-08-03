<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDBConnection() {
    $host = "192.168.12.11";
    $username = "root";
    $password = "atlantic123";

    // Dynamic registry mapping for stores
    $db_registry = [
        'tdiy' => 'db_store_dashboard_tdiy', // Taboan
        'bdiy' => 'db_store_dashboard_bdiy', // Bulacao
        'cdiy' => 'db_store_dashboard_cdiy', // Carcar
        'mdiy' => 'db_store_dashboard_mdiy'  // Mandaue
    ];

    $selected_store = $_SESSION['active_store'] ?? 'tdiy';

    if (array_key_exists($selected_store, $db_registry)) {
        $dbname = $db_registry[$selected_store];
    } else {
        $dbname = 'db_store_dashboard_tdiy'; 
    }

    $conn = mysqli_connect($host, $username, $password, $dbname);

    if (!$conn) {
        die("Database connection failed for dynamic store [$dbname]: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, "utf8mb4");

    return $conn;
}