<?php

/** @var string $base_path */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $base_path . 'config/db.php';

$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

// Force Login Check (Redirect to views/login.php)
if (!$isLoggedIn) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ' . $base_path . 'views/login.php'); 
    exit(); 
}

//Force Branch Selection Rule
$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['active_store']) && $current_page === 'index.php') {
    header('Location: ' . $base_path . 'intro_page.php');
    exit();
}

$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'User') : 'Guest';
$userId = $_SESSION['user_id'] ?? '';
$isSuperAdmin = $isLoggedIn && isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;
$currentDate = date("F d, Y");

$conn = getDBConnection();

// Get the current user's allowed module IDs
$allowedModules = [];
if ($isLoggedIn && !$isSuperAdmin) {
    $queryAllowed = "SELECT oModuleid 
                    FROM tbl_access 
                    WHERE oUserid = ? 
                    AND oMain = 1";
    if ($stmt = $conn->prepare($queryAllowed)) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $resultAllowed = $stmt->get_result();
        while ($row = $resultAllowed->fetch_assoc()) {
            $allowedModules[] = (int)$row['oModuleid'];
        }
        $stmt->close();
    }
}

// Helper function to verify permissions
if (!function_exists('hasAccess')) {
    function hasAccess(int $moduleId, bool $isSuperAdmin, array $allowedModules): bool {
        return $isSuperAdmin || in_array($moduleId, $allowedModules);
    }
}