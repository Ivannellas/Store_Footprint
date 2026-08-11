<?php
session_start();

$base_path = "./";

require_once $base_path . 'includes/auth_check.php';
require_once $base_path . 'config/db.php';
require_once $base_path . 'controller/login_controller.php';
require_once $base_path . 'controller/footprint_contoller.php';

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

$footprintController = new FootprintController($conn);

// Track active tab dynamically
$activeTab = $_GET['tab'] ?? 'Store';

// Retrieve and clear session error message if present (PRG Pattern)
$errorMessage = $_SESSION['error_message'] ?? "";
unset($_SESSION['error_message']);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type      = $_POST['type'] ?? 'store';
    $name      = trim($_POST['name'] ?? '');
    $startTime = $_POST['startTime'] ?? '';
    $endTime   = $_POST['endTime'] ?? '';
    $count     = (int)($_POST['count'] ?? 0);

    // Format time range
    $formattedStart = !empty($startTime) ? date('g:i A', strtotime($startTime)) : null;
    $formattedEnd   = !empty($endTime)   ? date('g:i A', strtotime($endTime))   : null;

    $formData = [
        'opersonnel' => $name,
        'odate'      => date('Y-m-d'),
        'ostarttime' => $formattedStart,
        'oendtime'   => $formattedEnd,
        'otype'      => $type,
        'ocount'     => $count,
        'added_by'   => $userName
    ];

    $result = $footprintController->HandleAddFootprint($type, $formData);
    $redirectTab = ($type === 'parking') ? 'Parking' : 'Store';

    if ($result['success']) {
        header("Location: index.php?tab=$redirectTab&status=success");
        exit;
    } else {
        // Redirect back on error so reloads don't prompt form resubmission
        $_SESSION['error_message'] = $result['message'];
        header("Location: index.php?tab=$redirectTab");
        exit;
    }
}

// Fetch history data filtered by current date only
$todayDate         = date('Y-m-d');
$storeFootprints   = $footprintController->RenderStoreFootprints($todayDate);
$parkingFootprints = $footprintController->RenderParkingFootprints($todayDate);

// Footprints history
$storeFootprintsHistory   = $footprintController->RenderStoreFootprints(null);
$parkingFootprintsHistory = $footprintController->RenderParkingFootprints(null);

$storeFootprintsHistory = array_map(function ($record) {
    $record['odate'] = date('Y-m-d', strtotime($record['odate']));
    return $record;
}, $storeFootprintsHistory);

$parkingFootprintsHistory = array_map(function ($record) {
    $record['odate'] = date('Y-m-d', strtotime($record['odate']));
    return $record;
}, $parkingFootprintsHistory);

mysqli_close($conn);

// Helper function to verify module permissions
function hasAccess(int $moduleId, bool $isSuperAdmin, array $allowedModules): bool
{
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
    <script src="assets/js/sweetalert2.all.min.js"></script>

    <!-- Prevent Back-Button Cache View on Logout -->
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0]?.type === 'back_forward')) {
                window.location.reload();
            }
        });

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
                <!-- Status Alerts (Compact Floating Toast) -->
                <div class="toast-container-custom">
                    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                        <div class="custom-toast-alert toast-success" role="alert">
                            <span><strong>Success!</strong> Footprint added.</span>
                            <button type="button" class="btn-close-toast" aria-label="Close">&times;</button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="carousel-container">
                    <div class="carousel-track">
                        <div class="carousel-content">
                            <p>
                                Please Make Sure The Information And Spelling You Input Is Correct !!!
                            </p>
                        </div>

                        <div class="carousel-content" aria-hidden="true">
                            <p>
                                Please Make Sure The Information And Spelling You Input Is Correct !!!
                            </p>
                        </div>

                        <div class="carousel-content" aria-hidden="true">
                            <p>
                                Please Make Sure The Information And Spelling You Input Is Correct !!!
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Title & Greeting -->
                <div class="index_flexbox">
                    <div class="landing_date_info index_title">
                        <h1 class="text-capitalize">
                            Atlantic <?php echo $timeContext; ?>, <?php echo htmlspecialchars($userName); ?>
                        </h1>
                        <p class="mb-1 text-muted">
                            <?php echo date('l, F j, Y'); ?>
                        </p>
                    </div>
                </div>

                <!-- Tabs For Parking and Store Foot Traffic -->
                <div class="tab_parent">
                    <div class="tab">
                        <button class="tablinks" onclick="openCity(event, 'Store')" <?php echo ($activeTab === 'Store') ? 'id="defaultOpen"' : ''; ?>>Foot Traffic</button>
                        <button class="tablinks" onclick="openCity(event, 'Parking')" <?php echo ($activeTab === 'Parking') ? 'id="defaultOpen"' : ''; ?>>Vehicle Traffic</button>
                    </div>

                    <div id="Store" class="tabcontent">
                        <div class="tab_flex">
                            <div class="form_store">
                                <h2>Foot Traffic</h2>
                                <form class="traffic_form" action="index.php" method="POST">
                                    <input type="hidden" name="type" value="store">

                                    <div class="oTime">
                                        <!-- Start Time Field -->
                                        <div class="oTime_start">
                                            <label for="startTime">ST:</label>
                                            <input type="time" id="startTime" name="startTime" required>
                                        </div>
                                        <!-- End Time Field -->
                                        <div class="oTime_end">
                                            <label for="endTime">ET:</label>
                                            <input type="time" id="endTime" name="endTime" required>
                                        </div>
                                    </div>

                                    <div class="flex_box_between">
                                        <input type="text" id="name" name="name" placeholder="Personnel Name"
                                            oninput="this.value = this.value.toUpperCase()"
                                            style="text-transform: uppercase;" required>
                                        <input type="number" id="count" name="count" placeholder="Input Traffic" required>
                                    </div>

                                    <div class="submit_btn">
                                        <button class="primary_btn" type="submit" name="submit">Submit</button>
                                    </div>
                                </form>
                            </div>

                            <div class="form_store_history">
                                <h2>Today's Foot Traffic</h2>
                                <div class="form_history_table">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Personnel Name</th>
                                                <th>Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($storeFootprints)): ?>
                                                <?php foreach ($storeFootprints as $row): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['opersonnel']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['odate']); ?></td>
                                                        <td>
                                                            <?php
                                                            if (!empty($row['ostarttime'])) {
                                                                echo date('g:i A', strtotime($row['ostarttime']));
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if (!empty($row['oendtime'])) {
                                                                echo date('g:i A', strtotime($row['oendtime']));
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['ocount']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No data available for today</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="modal_btn" id="footModalBtn">
                                    <a class="primary_btn" href="#">Foot Traffic History</a>
                                </div>

                                <!-- Modal Overlay Window -->
                                <div class="modal-overlay" id="footTrafficModal">
                                    <div class="modal-content">

                                        <!-- Header -->
                                        <div class="modal-header">
                                            <h3>Foot Traffic History Log</h3>
                                            <button class="close-btn" id="closeFootTrafficModal">&times;</button>
                                        </div>

                                        <!-- Filter Controls -->
                                        <div class="filter-bar">
                                            <div class="filter-group">
                                                <label for="footDate">Date</label>
                                                <input type="date" id="footDate" />
                                            </div>
                                            <button class="filter-btn" onclick="applyFilter('foot')">Filter</button>
                                            <button class="filter-btn" onclick="clearFilter('foot')" type="button">Clear</button>
                                        </div>

                                        <!-- Modal Body / Table -->
                                        <div class="modal-body">
                                            <div class="table-container">
                                                <table class="personnel-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Personnel Name</th>
                                                            <th>Date</th>
                                                            <th>Start Time</th>
                                                            <th>End Time</th>
                                                            <th>Count</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="footTableBody">
                                                        <tr>
                                                            <?php if (!empty($storeFootprintsHistory)): ?>
                                                                <?php foreach ($storeFootprintsHistory as $row): ?>
                                                        <tr data-date="<?php echo htmlspecialchars($row['odate']); ?>">
                                                            <td><?php echo htmlspecialchars($row['opersonnel']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['odate']); ?></td>
                                                            <td>
                                                                <?php
                                                                    if (!empty($row['ostarttime'])) {
                                                                        echo date('g:i A', strtotime($row['ostarttime']));
                                                                    } else {
                                                                        echo '-';
                                                                    }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                    if (!empty($row['oendtime'])) {
                                                                        echo date('g:i A', strtotime($row['oendtime']));
                                                                    } else {
                                                                        echo '-';
                                                                    }
                                                                ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row['ocount']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No data available </td>
                                                    </tr>
                                                <?php endif; ?>
                                                </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="Parking" class="tabcontent">
                        <div class="tab_flex">
                            <div class="form_parking">
                                <h2>Vehicle Traffic</h2>
                                <form class="traffic_form" action="index.php" method="POST">
                                    <input type="hidden" name="type" value="parking">

                                    <div class="oTime">
                                        <!-- Start Time Field -->
                                        <div class="oTime_start">
                                            <label for="startTime">ST:</label>
                                            <input type="time" id="startTime" name="startTime" required>
                                        </div>
                                        <!-- End Time Field -->
                                        <div class="oTime_end">
                                            <label for="endTime">ET:</label>
                                            <input type="time" id="endTime" name="endTime" required>
                                        </div>
                                    </div>

                                    <div class="flex_box_between">
                                        <input type="text" id="name" name="name" placeholder="Personnel Name"
                                            oninput="this.value = this.value.toUpperCase()"
                                            style="text-transform: uppercase;" required>
                                        <input type="number" id="count" name="count" placeholder="Input Traffic" required>
                                    </div>

                                    <div class="submit_btn">
                                        <button class="primary_btn" type="submit" name="submit">Submit</button>
                                    </div>
                                </form>
                            </div>

                            <div class="form_parking_history">
                                <h2>Today's Vehicle Traffic</h2>
                                <div class="form_history_table">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Personnel Name</th>
                                                <th>Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($parkingFootprints)): ?>
                                                <?php foreach ($parkingFootprints as $row): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['opersonnel']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['odate']); ?></td>
                                                        <td>
                                                            <?php
                                                            if (!empty($row['ostarttime'])) {
                                                                echo date('g:i A', strtotime($row['ostarttime']));
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if (!empty($row['oendtime'])) {
                                                                echo date('g:i A', strtotime($row['oendtime']));
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['ocount']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No data available for today</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="modal_btn" id="vehicleModalBtn">
                                    <a class="primary_btn" href="#">Vehicle History</a>
                                </div>

                                <!-- Modal Overlay Window -->
                                <div class="modal-overlay" id="vehicleTrafficModal">
                                    <div class="modal-content">

                                        <!-- Header -->
                                        <div class="modal-header">
                                            <h3>Vehicle Traffic History Log</h3>
                                            <button class="close-btn" id="closevehicleTrafficModal">&times;</button>
                                        </div>

                                        <!-- Filter Controls -->
                                        <div class="filter-bar">
                                            <div class="filter-group">
                                                <label for="vehicleDate">Date</label>
                                                <input type="date" id="vehicleDate" />
                                            </div>
                                            <button class="filter-btn" onclick="applyFilter('vehicle')">Filter</button>
                                            <button class="filter-btn" onclick="clearFilter('vehicle')" type="button">Clear</button>
                                        </div>

                                        <!-- Modal Body / Table -->
                                        <div class="modal-body">
                                            <div class="table-container">
                                                <table class="personnel-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Personnel Name</th>
                                                            <th>Date</th>
                                                            <th>Start Time</th>
                                                            <th>End Time</th>
                                                            <th>Count</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="vehicleTableBody">
                                                        <tr>
                                                            <?php if (!empty($parkingFootprintsHistory)): ?>
                                                                <?php foreach ($parkingFootprintsHistory as $row): ?>
                                                        <tr data-date="<?php echo htmlspecialchars($row['odate']); ?>">
                                                            <td><?php echo htmlspecialchars($row['opersonnel']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['odate']); ?></td>
                                                            <td>
                                                                <?php
                                                                    if (!empty($row['ostarttime'])) {
                                                                        echo date('g:i A', strtotime($row['ostarttime']));
                                                                    } else {
                                                                        echo '-';
                                                                    }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                    if (!empty($row['oendtime'])) {
                                                                        echo date('g:i A', strtotime($row['oendtime']));
                                                                    } else {
                                                                        echo '-';
                                                                    }
                                                                ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row['ocount']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No data available for today</td>
                                                    </tr>
                                                <?php endif; ?>
                                                </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
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

    <!-- External script para sa time autofill ug alert dismissal -->
    <script src="assets/js/footprint.js"></script>
    <?php if (!empty($errorMessage)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Duplicate Log',
                text: <?php echo json_encode($errorMessage); ?>,
                confirmButtonColor: '#003366',
                confirmButtonText: 'OK'
            });
        </script>
    <?php endif; ?>

    <!-- External script para sa time autofill ug alert dismissal -->
    <script src="assets/js/footprint.js"></script>

    <!-- MODAL JAVASCRIPT -->
    <script>
        const footModalBtn = document.getElementById('footModalBtn');
        const closeFootTrafficModal = document.getElementById('closeFootTrafficModal');
        const footmodalOverlay = document.getElementById('footTrafficModal');

        const vehicleModalBtn = document.getElementById('vehicleModalBtn');
        const closevehicleTrafficModal = document.getElementById('closevehicleTrafficModal');
        const vehiclemodalOverlay = document.getElementById('vehicleTrafficModal');

        // Open modal when button is clicked
        footModalBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Prevents link default anchor behavior
            footmodalOverlay.classList.add('active');
        });

        // Close modal when 'X' is clicked
        closeFootTrafficModal.addEventListener('click', () => {
            footmodalOverlay.classList.remove('active');
        });

        // Close modal when user clicks outside the modal box
        window.addEventListener('click', (e) => {
            if (e.target === footmodalOverlay) {
                footmodalOverlay.classList.remove('active');
            }
        });


        // Open modal when button is clicked
        vehicleModalBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Prevents link default anchor behavior
            vehiclemodalOverlay.classList.add('active');
        });

        // Close modal when 'X' is clicked
        closevehicleTrafficModal.addEventListener('click', () => {
            vehiclemodalOverlay.classList.remove('active');
        });

        // Close modal when user clicks outside the modal box
        window.addEventListener('click', (e) => {
            if (e.target === vehiclemodalOverlay) {
                vehiclemodalOverlay.classList.remove('active');
            }
        });
    </script>
</body>

</body>

</html>