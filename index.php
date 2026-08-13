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

// Fixed time ranges list
$timeRanges = [
    "6:00 AM - 8:00 AM",
    "8:01 AM - 9:00 AM",
    "9:01 AM - 10:00 AM",
    "10:01 AM - 11:00 AM",
    "11:01 AM - 12:00 PM",
    "12:01 PM - 1:00 PM",
    "1:01 PM - 2:00 PM",
    "2:01 PM - 3:00 PM",
    "3:01 PM - 4:00 PM",
    "4:01 PM - 5:00 PM",
    "5:01 PM - 6:00 PM",
    "6:01 PM - 8:00 PM"
];

// Default time range for form selection
function getDefaultTimeRange(): string
{
    $targetTime = strtotime('-1 hour'); // Minus 1 hour
    $targetHour = (int)date('G', $targetTime); // 0 - 23 format

    if ($targetHour >= 6 && $targetHour < 8)   return "6:00 AM - 8:00 AM";
    if ($targetHour == 8)                       return "8:01 AM - 9:00 AM";
    if ($targetHour == 9)                       return "9:01 AM - 10:00 AM";
    if ($targetHour == 10)                      return "10:01 AM - 11:00 AM";
    if ($targetHour == 11)                      return "11:01 AM - 12:00 PM";
    if ($targetHour == 12)                      return "12:01 PM - 1:00 PM";
    if ($targetHour == 13)                      return "1:01 PM - 2:00 PM";
    if ($targetHour == 14)                      return "2:01 PM - 3:00 PM";
    if ($targetHour == 15)                      return "3:01 PM - 4:00 PM";
    if ($targetHour == 16)                      return "4:01 PM - 5:00 PM";
    if ($targetHour == 17)                      return "5:01 PM - 6:00 PM";
    if ($targetHour >= 18 && $targetHour < 20) return "6:01 PM - 8:00 PM";

    return "";
}

$defaultTimeRange = getDefaultTimeRange();

// Open DB Connection
$conn = getDBConnection();
$allowedModules = [];

if ($isLoggedIn && !$isSuperAdmin) {
    $queryAllowed = "SELECT oModuleid FROM tbl_access WHERE oUserid = ? AND oMain = 1";
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

// Fetch Active Personnel List early before controller calls or connection changes
$activePersonnelList = [];
$queryPersonnel = "SELECT personnel_name FROM tbl_footprint_personnel WHERE status = 1 ORDER BY personnel_name ASC";
if ($resultPersonnel = $conn->query($queryPersonnel)) {
    while ($row = $resultPersonnel->fetch_assoc()) {
        $activePersonnelList[] = $row['personnel_name'];
    }
    $resultPersonnel->free();
}

$footprintController = new FootprintController($conn);

// Track active tab dynamically
$activeTab = $_GET['tab'] ?? 'Store';

// Retrieve and clear session error message (PRG Pattern)
$errorMessage = $_SESSION['error_message'] ?? "";
unset($_SESSION['error_message']);

// Handle ALL POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add_footprint';

    if ($action === 'void_footprint') {
        $tableId = intval($_POST['otableid'] ?? 0);
        $type    = strtolower(trim($_POST['type'] ?? 'store'));

        $result      = $footprintController->HandleVoidFootprint($type, $tableId);
        $redirectTab = ($type === 'parking') ? 'Parking' : 'Store';

        if ($result['success']) {
            header("Location: index.php?tab=$redirectTab&status=void_success");
            exit;
        } else {
            $_SESSION['error_message'] = $result['message'];
            header("Location: index.php?tab=$redirectTab");
            exit;
        }
    }

    if ($action === 'add_footprint' || !isset($_POST['action'])) {
        $type         = $_POST['type'] ?? 'store';
        $name         = trim($_POST['name'] ?? '');
        $selectedDate = trim($_POST['date'] ?? date('Y-m-d'));
        $timeRange    = trim($_POST['timeRange'] ?? '');

        // Ensure 0 is captured properly when submitted
        $count = isset($_POST['count']) && $_POST['count'] !== '' ? (int)$_POST['count'] : 0;

        $formData = [
            'opersonnel'  => $name,
            'odate'       => $selectedDate,
            'otimerange'  => $timeRange,
            'ocount'      => $count,
            'added_by'    => $userName
        ];

        $result = $footprintController->HandleAddFootprint($type, $formData);
        $redirectTab = ($type === 'parking') ? 'Parking' : 'Store';

        if ($result['success']) {
            header("Location: index.php?tab=$redirectTab&status=success");
            exit;
        } else {
            $_SESSION['error_message'] = $result['message'];
            header("Location: index.php?tab=$redirectTab");
            exit;
        }
    }
}

// Fetch today's footprints
$todayDate         = date('Y-m-d');
$storeFootprints   = $footprintController->RenderStoreFootprints($todayDate);
$parkingFootprints = $footprintController->RenderParkingFootprints($todayDate);

// Fetch overall footprints history
$storeFootprintsHistory   = $footprintController->RenderStoreFootprints(null) ?? [];
$parkingFootprintsHistory = $footprintController->RenderParkingFootprints(null) ?? [];

if ($conn) {
    mysqli_close($conn);
}

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
    <title>Foot Traffic</title>
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

                <!-- Status Alerts -->
                <div class="toast-container-custom">
                    <?php
                    $status = $_GET['status'] ?? '';
                    $tabLabel = (isset($_GET['tab']) && strtolower($_GET['tab']) === 'parking') ? 'Vehicle Traffic' : 'Foot Traffic';
                    ?>

                    <?php if ($status === 'success'): ?>
                        <div class="custom-toast-alert toast-success" role="alert">
                            <span><strong>Success!</strong> <?php echo $tabLabel; ?> added successfully.</span>
                            <button type="button" class="btn-close-toast" aria-label="Close">&times;</button>
                        </div>
                    <?php endif; ?>

                    <?php if ($status === 'void_success'): ?>
                        <div class="custom-toast-alert toast-success" role="alert">
                            <span><strong>Success!</strong> <?php echo $tabLabel; ?> entry voided successfully.</span>
                            <button type="button" class="btn-close-toast" aria-label="Close">&times;</button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorMessage)): ?>
                        <div class="custom-toast-alert toast-danger" role="alert">
                            <span><strong>Error!</strong> <?php echo htmlspecialchars($errorMessage); ?></span>
                            <button type="button" class="btn-close-toast" aria-label="Close">&times;</button>
                        </div>
                    <?php endif; ?>
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

                <!-- Tabs For Store & Parking Traffic -->
                <div class="tab_parent">
                    <div class="tab">
                        <button class="tablinks <?php echo ($activeTab === 'Store') ? 'active' : ''; ?>" onclick="openCity(event, 'Store')" <?php echo ($activeTab === 'Store') ? 'id="defaultOpen"' : ''; ?>>Foot Traffic</button>
                        <button class="tablinks <?php echo ($activeTab === 'Parking') ? 'active' : ''; ?>" onclick="openCity(event, 'Parking')" <?php echo ($activeTab === 'Parking') ? 'id="defaultOpen"' : ''; ?>>Vehicle Traffic</button>
                    </div>

                    <!-- STORE TAB -->
                    <div id="Store" class="tabcontent">
                        <div class="tab_flex">
                            <div class="form_store">
                                <h2>Foot Traffic</h2>
                                <form class="traffic_form" action="index.php" method="POST">
                                    <input type="hidden" name="type" value="store">

                                    <div class="flex_box_between">
                                        <div class="oDate_range">
                                            <label for="dateStore">Select Date:</label>
                                            <input type="date" id="dateStore" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>

                                        <div class="oTime_range">
                                            <label for="timeRangeStore">Time Range:</label>
                                            <select id="timeRangeStore" name="timeRange" class="form-select" required>
                                                <option value="" disabled <?php echo empty($defaultTimeRange) ? 'selected' : ''; ?>>Select Time Range</option>
                                                <?php foreach ($timeRanges as $range): ?>
                                                    <option value="<?php echo htmlspecialchars($range); ?>" <?php echo ($range === $defaultTimeRange) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($range); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="flex_box_between">
                                        <div class="oPersonnel">
                                            <label for="personnelInput">Choose Personnel</label>
                                            <div class="personnel-select-wrapper" id="personnelWrapper">
                                                <input
                                                    type="text"
                                                    id="personnelInput"
                                                    class="personnel-search-input"
                                                    placeholder="Select Personnel"
                                                    autocomplete="off"
                                                    required>
                                                <svg class="dropdown-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="6 9 12 15 18 9"></polyline>
                                                </svg>

                                                <input type="hidden" id="nameStore" name="name" required>

                                                <div class="personnel-dropdown-list" id="personnelDropdown">
                                                    <?php foreach ($activePersonnelList as $personnel): ?>
                                                        <div class="personnel-dropdown-item" data-value="<?php echo htmlspecialchars($personnel); ?>">
                                                            <?php echo htmlspecialchars($personnel); ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="oTraffic">
                                            <label for="countStore">Input Traffic</label>
                                            <input class="form_input" type="number" id="countStore" name="count" min="0" required>
                                        </div>
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
                                                <th>Time Range</th>
                                                <th>Count</th>
                                                <th>Created By</th>
                                                <th>Created Date</th>
                                                <th>Void</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($storeFootprints)): ?>
                                                <?php foreach ($storeFootprints as $row): ?>
                                                    <?php $isVoided = isset($row['void_status']) && (int)$row['void_status'] === 0; ?>
                                                    <tr class="<?php echo $isVoided ? 'row-voided' : ''; ?>">
                                                        <td><strong><?php echo htmlspecialchars($row['opersonnel']); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($row['odate']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['otimerange']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['ocount']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['added_by']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                                        <td class="text-center">
                                                            <?php if ($isVoided): ?>
                                                                <span class="void_pills void-disabled" title="Record Voided">&cross; </span>
                                                            <?php else: ?>
                                                                <button type="button"
                                                                    class="void_pills void-active"
                                                                    onclick="confirmVoid(<?php echo (int)$row['otableid']; ?>, 'store')">
                                                                    &mdash;
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No data available for today</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="modal_btn">
                                    <a id="footModalBtn" class="primary_btn" href="#">Foot Traffic History</a>
                                </div>

                                <!-- Foot Traffic Modal -->
                                <div class="modal-overlay" id="footTrafficModal">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3>Foot Traffic History Log</h3>
                                            <button class="close-btn" id="closeFootTrafficModal">&times;</button>
                                        </div>

                                        <div class="filter-bar">
                                            <div class="filter-group">
                                                <label for="footDate">Date</label>
                                                <input type="date" id="footDate" />
                                            </div>
                                            <button class="filter-btn" onclick="applyFilter('foot')">Filter</button>
                                            <button class="filter-btn" onclick="clearFilter('foot')" type="button">Clear</button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="table-container">
                                                <table class="personnel-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Personnel Name</th>
                                                            <th>Date</th>
                                                            <th>Time Range</th>
                                                            <th>Count</th>
                                                            <th>Created By</th>
                                                            <th>Created Date</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="footTableBody">
                                                        <?php if (!empty($storeFootprintsHistory)): ?>
                                                            <?php foreach ($storeFootprintsHistory as $row): ?>
                                                                <?php $isVoided = isset($row['void_status']) && (int)$row['void_status'] === 0; ?>
                                                                <tr data-date="<?php echo htmlspecialchars($row['odate']); ?>" class="<?php echo $isVoided ? 'row-voided' : ''; ?>">
                                                                    <td><?php echo htmlspecialchars($row['opersonnel']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['odate']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['otimerange']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['ocount']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['added_by']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                                                    <td class="text-center">
                                                                        <?php if ($isVoided): ?>
                                                                            <span class="void_pills void-disabled" title="Record Voided">Voided</span>
                                                                        <?php else: ?>
                                                                            <span style="color: #28a745; font-weight: 600;">&check;</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="7" class="text-center">No data available</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PARKING TAB -->
                    <div id="Parking" class="tabcontent">
                        <div class="tab_flex">
                            <div class="form_parking">
                                <h2>Vehicle Traffic</h2>
                                <form class="traffic_form" action="index.php" method="POST">
                                    <input type="hidden" name="type" value="parking">

                                    <div class="flex_box_between">
                                        <div class="oDate_range">
                                            <label for="dateParking">Select Date:</label>
                                            <input type="date" id="dateParking" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>

                                        <div class="oTime_range">
                                            <label for="timeRangeParking">Time Range:</label>
                                            <select id="timeRangeParking" name="timeRange" class="form-select" required>
                                                <option value="" disabled <?php echo empty($defaultTimeRange) ? 'selected' : ''; ?>>Select Time Range</option>
                                                <?php foreach ($timeRanges as $range): ?>
                                                    <option value="<?php echo htmlspecialchars($range); ?>" <?php echo ($range === $defaultTimeRange) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($range); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="flex_box_between">
                                        <div class="oPersonnel">
                                            <label for="parkingPersonnelInput">Choose Personnel</label>
                                            <div class="personnel-select-wrapper" id="parkingPersonnelWrapper">
                                                <input
                                                    type="text"
                                                    id="parkingPersonnelInput"
                                                    class="personnel-search-input"
                                                    placeholder="Select Personnel"
                                                    autocomplete="off"
                                                    required>
                                                <svg class="dropdown-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="6 9 12 15 18 9"></polyline>
                                                </svg>

                                                <input type="hidden" id="nameParking" name="name" required>

                                                <div class="personnel-dropdown-list" id="parkingPersonnelDropdown">
                                                    <?php foreach ($activePersonnelList as $personnel): ?>
                                                        <div class="personnel-dropdown-item" data-value="<?php echo htmlspecialchars($personnel); ?>">
                                                            <?php echo htmlspecialchars($personnel); ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="oTraffic">
                                            <label for="countParking">Input Traffic</label>
                                            <input type="number" id="countParking" name="count" min="0" required>
                                        </div>
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
                                                <th>Time Range</th>
                                                <th>Count</th>
                                                <th>Created By</th>
                                                <th>Created Date</th>
                                                <th>Void</th> <!-- Optional: Included for parity with Store tab -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($parkingFootprints)): ?>
                                                <?php foreach ($parkingFootprints as $row): ?>
                                                    <?php $isVoided = isset($row['void_status']) && (int)$row['void_status'] === 0; ?>
                                                    <tr class="<?php echo $isVoided ? 'row-voided' : ''; ?>">
                                                        <td><strong><?php echo htmlspecialchars($row['opersonnel']); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($row['odate']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['otimerange']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['ocount']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['added_by']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                                        <td class="text-center">
                                                            <?php if ($isVoided): ?>
                                                                <span class="void_pills void-disabled" title="Record Voided">&cross; </span>
                                                            <?php else: ?>
                                                                <button type="button"
                                                                    class="void_pills void-active"
                                                                    onclick="confirmVoid(<?php echo (int)$row['otableid']; ?>, 'parking')">
                                                                    &mdash;
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No data available for today</td> <!-- Fixed colspan from 4 to 7 -->
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="modal_btn">
                                    <a id="vehicleModalBtn" class="primary_btn" href="#">Vehicle History</a>
                                </div>

                                <!-- Vehicle Traffic Modal -->
                                <div class="modal-overlay" id="vehicleTrafficModal">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3>Vehicle Traffic History Log</h3>
                                            <button class="close-btn" id="closevehicleTrafficModal">&times;</button>
                                        </div>

                                        <div class="filter-bar">
                                            <div class="filter-group">
                                                <label for="vehicleDate">Date</label>
                                                <input type="date" id="vehicleDate" />
                                            </div>
                                            <button class="filter-btn" onclick="applyFilter('vehicle')">Filter</button>
                                            <button class="filter-btn" onclick="clearFilter('vehicle')" type="button">Clear</button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="table-container">
                                                <table class="personnel-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Personnel Name</th>
                                                            <th>Date</th>
                                                            <th>Time Range</th>
                                                            <th>Count</th>
                                                            <th>Created By</th>
                                                            <th>Created Date</th>
                                                            <th>Status</th> <!-- Added Status header for modal parity -->
                                                        </tr>
                                                    </thead>
                                                    <tbody id="vehicleTableBody">
                                                        <?php if (!empty($parkingFootprintsHistory)): ?>
                                                            <?php foreach ($parkingFootprintsHistory as $row): ?>
                                                                <?php $isVoided = isset($row['void_status']) && (int)$row['void_status'] === 0; ?>
                                                                <tr data-date="<?php echo htmlspecialchars($row['odate']); ?>" class="<?php echo $isVoided ? 'row-voided' : ''; ?>">
                                                                    <td><?php echo htmlspecialchars($row['opersonnel']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['odate']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['otimerange']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['ocount']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['added_by']); ?></td>
                                                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                                                    <td class="text-center">
                                                                        <?php if ($isVoided): ?>
                                                                            <span class="void_pills void-disabled" title="Record Voided">Voided</span>
                                                                        <?php else: ?>
                                                                            <span style="color: #28a745; font-weight: 600;">&check;</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="7" class="text-center">No data available</td> <!-- Fixed colspan from 4 to 7 -->
                                                            </tr>
                                                        <?php endif; ?>
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


    <!-- External script for time autofill and alert dismissal -->
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

    <!-- MODAL JAVASCRIPT -->
    <script>
        const footModalBtn = document.getElementById('footModalBtn');
        const closeFootTrafficModal = document.getElementById('closeFootTrafficModal');
        const footmodalOverlay = document.getElementById('footTrafficModal');

        const vehicleModalBtn = document.getElementById('vehicleModalBtn');
        const closevehicleTrafficModal = document.getElementById('closevehicleTrafficModal');
        const vehiclemodalOverlay = document.getElementById('vehicleTrafficModal');

        // Open modal when button is clicked
        footModalBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            footmodalOverlay.classList.add('active');
        });

        // Close modal when 'X' is clicked
        closeFootTrafficModal?.addEventListener('click', () => {
            footmodalOverlay.classList.remove('active');
        });

        // Open modal when button is clicked
        vehicleModalBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            vehiclemodalOverlay.classList.add('active');
        });

        // Close modal when 'X' is clicked
        closevehicleTrafficModal?.addEventListener('click', () => {
            vehiclemodalOverlay.classList.remove('active');
        });

        // Close modal when user clicks outside the modal box
        window.addEventListener('click', (e) => {
            if (e.target === footmodalOverlay) {
                footmodalOverlay.classList.remove('active');
            }
            if (e.target === vehiclemodalOverlay) {
                vehiclemodalOverlay.classList.remove('active');
            }
        });
    </script>

    <!-- Hidden Form for Void Submission -->
    <form id="voidForm" action="index.php" method="POST" style="display: none;">
        <input type="hidden" name="action" value="void_footprint">
        <input type="hidden" name="otableid" id="voidTableId">
    </form>

    <script>
        function confirmVoid(tableId) {
            if (confirm("Are you sure you want to void this foot traffic entry?")) {
                document.getElementById('voidTableId').value = tableId;
                document.getElementById('voidForm').submit();
            }
        }
    </script>
</body>

</html>