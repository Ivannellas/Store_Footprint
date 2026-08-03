<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$store_names = [
    'tdiy' => 'Taboan Branch',
    'bdiy' => 'Bulacao Branch',
    'cdiy' => 'Carcar Branch', 
    'mdiy' => 'Mandaue Branch'
];

if (isset($_GET['select_store'])) {
    $store_code = strtolower(trim($_GET['select_store']));

    if (array_key_exists($store_code, $store_names)) {
        $_SESSION['active_store'] = $store_code;
        $_SESSION['store_name']   = $store_names[$store_code];
        
        header("Location: ../index.php");
        exit();
    } else {
        header("Location: ../intro_page.php?error=invalid_store");
        exit();
    }
} else {
    header("Location: ../intro_page.php");
    exit();
}