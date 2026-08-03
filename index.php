<?php
session_start();

$base_path = "./";

require_once $base_path . 'includes/auth_check.php';
require_once $base_path . 'config/db.php';
require_once $base_path . 'controller/login_controller.php';

// Handle logout action 
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: views/login.php");
    exit;
}

// Track authentication states cleanly
$isLoggedIn   = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
$userName     = $isLoggedIn ? ($_SESSION['user_name'] ?? 'User') : 'Guest';
$userId       = $_SESSION['user_id'] ?? '';
$isSuperAdmin = $isLoggedIn && isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;

// Time & greeting context
date_default_timezone_set('Asia/Manila');
$hour = (int)date('G');
if ($hour >= 5 && $hour < 12) {
    $timeContext = "Morning";
} elseif ($hour >= 12 && $hour < 18) {
    $timeContext = "Afternoon";
} else {
    $timeContext = "Evening";
}

// Get the current user's allowed module IDs from tbl_access
$conn = getDBConnection();
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

mysqli_close($conn);

// Helper function to verify module permissions
function hasAccess(int $moduleId, bool $isSuperAdmin, array $allowedModules): bool {
    return $isSuperAdmin || in_array($moduleId, $allowedModules);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Menu</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/media.css">
    <link rel="icon" href="assets/images/favicon.png">
    
    <script>
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed-preload');
        }
    </script>
    <script src="assets/js/sidebar_button.js"></script>
    
    <script>
        function guardModuleAccess(isLoggedIn, hasAccess, moduleName) {
            if (!isLoggedIn) {
                alert("Authentication Required: You must log into an active account to access the " + moduleName);
                window.location.href = "views/login.php";
                return false;
            }
            if (!hasAccess) {
                alert("Access Denied: You are not permitted to access the " + moduleName);
                return false;
            }
            return true;
        }
    </script>
</head>

<body class="bg-light">

    <div class="d-flex main_top_box">
        <?php include $base_path . 'includes/sidebar.php'; ?>

        <div class="wrapper flex-grow-1 d-flex flex-column bg-light">

            <!-- Title & Greeting -->
            <div class="index_flexbox">
                <div class="landing_date_info index_title">
                    <h1 class="text-capitalize">
                        Atlantic <?php echo $timeContext; ?>, <?php echo htmlspecialchars($userName); ?>
                    </h1>
                    <p>Here's what's happening with your dashboards.</p>
                </div>
            </div>

            <div class="index_top">
                <div class="top_index_info">
                    <h2>We Lead, <span>You Build.</span></h2>
                    <p>One dashboard, All your business insights.</p>
                </div>
            </div>

            <!-- Dashboard Cards Grid -->
            <div class="index_cards">
                <?php 
                // Module ID 5: Salesman Performance
                $m5Access = hasAccess(5, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m5Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/salesperf-30px.png" alt="Salesman Performance"></figure>
                        <h2>Salesman Performance</h2>
                        <a href="<?php echo $m5Access ? 'views/dashboards/store_dashboard_main.php' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m5Access ? 'true' : 'false'; ?>, 'Salesman Performance');"></a>
                    </div>
                </section>

                <?php 
                // Module ID 6: Opportunity Loss
                $m6Access = hasAccess(6, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m6Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/oppsales-30px.png" alt="Opportunity Loss"></figure>
                        <h2>Opportunity Loss</h2>
                        <a href="<?php echo $m6Access ? '#' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m6Access ? 'true' : 'false'; ?>, 'Opportunity Loss');"></a>
                    </div>
                </section>

                <?php 
                // Module ID 7: Stock Transfer Out
                $m7Access = hasAccess(7, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m7Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/stockout-30px.png" alt="Stock Transfer Out"></figure>
                        <h2>Stock Transfer Out</h2>
                        <a href="<?php echo $m7Access ? '#' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m7Access ? 'true' : 'false'; ?>, 'Stock Transfer Out');"></a>
                    </div>
                </section>

                <?php 
                // Module ID 8: Stock Transfer In
                $m8Access = hasAccess(8, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m8Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/stockin-30px.png" alt="Stock Transfer In"></figure>
                        <h2>Stock Transfer In</h2>
                        <a href="<?php echo $m8Access ? '#' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m8Access ? 'true' : 'false'; ?>, 'Stock Transfer In');"></a>
                    </div>
                </section>

                <?php 
                // Module ID 9: Sales Transaction Count
                $m9Access = hasAccess(9, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m9Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/transcount-30px.png" alt="Sales Transaction Count"></figure>
                        <h2>Sales Transaction Count</h2>
                        <a href="<?php echo $m9Access ? '#' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m9Access ? 'true' : 'false'; ?>, 'Sales Transaction Count');"></a>
                    </div>
                </section>

                <?php 
                // Module ID 10: Top 20 Sold Items
                $m10Access = hasAccess(10, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m10Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/delivery-30px.png" alt="Delivery Transaction Count"></figure>
                        <h2>Delivery Transaction Count</h2>
                        <a href="<?php echo $m10Access ? '#' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m10Access ? 'true' : 'false'; ?>, 'Delivery Transaction Count');"></a>
                    </div>
                </section>

                <?php 
                // Module ID 11: Cycle Time
                $m11Access = hasAccess(11, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m11Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/cycle-30px.png" alt="Cycle Time"></figure>
                        <h2>Cycle Time</h2>
                        <a href="<?php echo $m11Access ? '#' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m11Access ? 'true' : 'false'; ?>, 'Cycle Time');"></a>
                    </div>
                </section>

                <?php 
                // Module ID 12: POS Cashier Performance
                $m12Access = hasAccess(12, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m12Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/pos-30px.png" alt="Cashier Performance"></figure>
                        <h2>POS Cashier Performance</h2>
                        <a href="<?php echo $m12Access ? '#' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m12Access ? 'true' : 'false'; ?>, 'POS Cashier Performance');"></a>
                    </div>
                </section>

                <?php 
                // Module ID 13: Customer Returned
                $m13Access = hasAccess(13, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m13Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/cusreturn-30px.png" alt="Customer Returned"></figure>
                        <h2>Customer Returned</h2>
                        <a href="<?php echo $m13Access ? '#' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m13Access ? 'true' : 'false'; ?>, 'Customer Returned');"></a>
                    </div>
                </section>

                <?php 
                //  Module ID 14: Top 20 Sold Items
                $m14Access = hasAccess(14, $isSuperAdmin, $allowedModules); 
                ?>
                <section class="<?php echo !$m14Access ? 'module-locked' : ''; ?>">
                    <div class="card_info">
                        <figure><img src="assets/images/icon/solditems-30px.png" alt="Top 20 Sold Items"></figure>
                        <h2>Top 20 Sold Items</h2>
                        <a href="<?php echo $m14Access ? '#' : '#'; ?>" 
                           onclick="return guardModuleAccess(<?php echo $isLoggedIn ? 'true' : 'false'; ?>, <?php echo $m14Access ? 'true' : 'false'; ?>, 'Top 20 Sold Items');"></a>
                    </div>
                </section>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="footer_intro">
        <div class="footer_content">
            <div class="wrapper">
                <div class="footer_container">
                    <div class="footer_img_content">
                        <figure><img src="assets/images/nong_atoy_head.png" alt="Footer Logo"></figure>
                        <p>To become the customer's TOP OF MIND for building materials and home improvement needs in Cebu in 2027</p>
                    </div>

                    <div class="footer_info">
                        <div class="footer_help">
                            <p>Need Help? <span><a href="#">Contact IT Support</a></span></p>
                        </div>
                        <div class="footer_values">
                            <figure><img src="assets/images/footer-img.png" alt="Values"></figure>
                        </div>
                        <div class="copyright">
                            &copy; Copyright
                            <?php
                            $start_year = '2026';
                            $current_year = date('Y');
                            $copyright = ($current_year == $start_year) ? $start_year : $current_year;
                            echo $copyright; ?>
                            <span class="company_name">Atlantic Hardware.</span>
                            <span><br> All rights reserved.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>