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
    <script src="assets/js/tabs.js"></script>
    
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

            <div class="main_con">
                <!-- Title & Greeting -->
                <div class="index_flexbox">
                    <div class="landing_date_info index_title">
                        <h1 class="text-capitalize">
                            Atlantic <?php echo $timeContext; ?>, <?php echo htmlspecialchars($userName); ?>
                        </h1>
                        <p>Here's what's happening with your dashboards.</p>
                    </div>
                </div>

                <!-- Tabs For Parking and Store Foot Traffic -->
                <div class="tab_parent">
                    <div class="tab">
                        <button class="tablinks" onclick="openCity(event, 'Store')" id="defaultOpen">Store Foot Traffic</button>
                        <button class="tablinks" onclick="openCity(event, 'Parking')">Parking Foot Traffic</button>
                    </div>
                        <div id="Store" class="tabcontent">
                            <div class="tab_flex">
                                <div class="form_store">
                                    <h2>Foot Traffic</h2>
                                    <form class="traffic_form">
                                        <div class="oTime">
                                        <!-- Start Time Field -->
                                        <div class="oTime_start">
                                            <label for="startTime">Start Time:</label>
                                            <input type="time" id="startTime" name="startTime" value="00:00" required>
                                        </div>
                                        <!-- End Time Field -->
                                         <div class="oTime_end">
                                            <label for="endTime">End Time:</label>
                                            <input type="time" id="endTime" name="endTime" value="00:00" required>
                                        </div>
                                        </div>
                                        
                                        <div class="flex_box_between">
                                            <input type="text" id="name" name="name" placeholder="Personnel Name" required>
                                            <input type="number" id="count" name="name" placeholder="Input Traffic" required>
                                        </div>
                                    
                                        <div class="submit_btn">
                                            <button type="submit" name="submit">Submit</button>
                                        </div>

                                    </form>
                                </div>
                                <div class="form_store_history">
                                        <h2>History</h2>
                                    <div class="form_history_table">
                                        <table>
                                            <tr>
                                                <th>Personnel Name</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Count</th>
                                            </tr>
                                            <tr>
                                                <td>Meycko</td>
                                                <td>08-07-2026</td>
                                                <td>8:00AM - 9:00AM</td>
                                                <td>50</td>
                                            </tr>
                                            <tr>
                                                <td>Ivan</td>
                                                <td>08-07-2026</td>
                                                <td>9:00AM - 10:00AM</td>
                                                <td>100</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="Parking" class="tabcontent">
                            <p class="upcoming">Companies Forms...</p>
                        </div>
                </div>
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