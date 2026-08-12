<?php
session_start();
$base_path = '../';
require_once '../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$userId = isset($_GET['id']) ? trim($_GET['id']) : '';
if (empty($userId)) {
    header("Location: user_accounts.php");
    exit;
}

$conn = getDBConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'update_permission') {
    header('Content-Type: application/json');

    $moduleId   = trim($_POST['module_id'] ?? '');
    $columnName = trim($_POST['column_name'] ?? '');
    $state      = isset($_POST['state']) ? (int)$_POST['state'] : 0;

    $allowedAccess = ['Main', 'Add', 'Edit', 'View', 'Save', 'Post', 'Cancel', 'Print', 'Discount', 'Send', 'SA', 'Supervisor', 'Manager', 'Audit'];

    if (empty($moduleId) || !in_array($columnName, $allowedAccess)) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
        mysqli_close($conn);
        exit;
    }

    if ($columnName === 'Supervisor') {
        $dbColumn = 'oSupervisor';
    } elseif ($columnName === 'Manager') {
        $dbColumn = 'oManager';
    } else {
        $dbColumn = 'o' . $columnName;
    }

    $checkStmt = $conn->prepare("SELECT 1 
                                FROM tbl_access 
                                WHERE oUserid = ? 
                                AND oModuleid = ? 
                                LIMIT 1");
    $checkStmt->bind_param("ss", $userId, $moduleId);
    $checkStmt->execute();
    $recordExists = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    $success = false;
    if ($recordExists) {
        $upQuery = "UPDATE tbl_access 
                    SET {$dbColumn} = ? 
                    WHERE oUserid = ? 
                    AND oModuleid = ?";
        if ($upStmt = $conn->prepare($upQuery)) {
            $upStmt->bind_param("iss", $state, $userId, $moduleId);
            $success = $upStmt->execute();
            $upStmt->close();
        }
    } else {
        $insQuery = "INSERT INTO tbl_access (oUserid, oModuleid, {$dbColumn}) 
                       VALUES (?, ?, ?)";
        if ($insStmt = $conn->prepare($insQuery)) {
            $insStmt->bind_param("ssi", $userId, $moduleId, $state);
            $success = $insStmt->execute();
            $insStmt->close();
        }
    }

    echo json_encode(['success' => $success]);
    mysqli_close($conn);
    exit;
}

$modules = [];
$modRes = $conn->query("SELECT oModuleid, 
                            oModulename 
                            FROM tbl_module 
                            ORDER BY oModuleid ASC");
if ($modRes) {
    while ($row = $modRes->fetch_assoc()) {
        $modules[] = $row;
    }
}

$uStmt = $conn->prepare("SELECT oFullname 
                            FROM tbl_user 
                            WHERE oUserid = ?");
$uStmt->bind_param("s", $userId);
$uStmt->execute();
$resUser = $uStmt->get_result()->fetch_assoc();
$fullname = $resUser ? $resUser['oFullname'] : $userId;
$uStmt->close();

$access = [];
$accStmt = $conn->prepare("SELECT * 
                            FROM tbl_access 
                            WHERE oUserid = ?");
$accStmt->bind_param("s", $userId);
$accStmt->execute();
$accRes = $accStmt->get_result();
while ($row = $accRes->fetch_assoc()) {
    $access[$row['oModuleid']] = $row;
}
$accStmt->close();
mysqli_close($conn);

$columnsList = ['Main', 'Add', 'Edit', 'View', 'Save', 'Post', 'Cancel', 'Print', 'Discount', 'Send', 'SA', 'Supervisor', 'Manager', 'Audit'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Permissions</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/media.css">
    <link rel="icon" href="<?php echo $base_path; ?>assets/images/favicon.png">
    <script src="../assets/js/user_permission_sync.js" defer></script>
    <script>
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed-preload');
        }
    </script>
    <script src="<?php echo $base_path; ?>assets/js/sidebar_button.js"></script>
</head>

<body class="bg-white text-dark">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <nav class="navbar navbar-dark navbar-header px-4 py-3 mb-5 shadow-sm">

        <div class="navbar_title">
            <h1 class="h1 text-uppercase mb-0">User Permissions</h1>
        </div>

        <div class="d-flex">
            <a href="user_accounts.php" class="primary_btn btn-sm btn-outline-light me-1">Back</a>
        </div>
    </nav>

    <div class="main_parent">
        <div class="wrapper">
            <div class="main_parent_con">

                <div class="container-fluid px-4 user_perm_con">

                    <div class="table-responsive border rounded custom-table-shadow table-wrapper table_parent">
                        <div class="user_saved">
                            <span id="syncStatus" class="small text-success-brand ms-2 fw-bold text-uppercase fs-7"></span>
                        </div>
                        <table class="table table-hover table-bordered table-sm align-middle small text-center m-0">
                            <thead>
                                <tr class="bg-light text-secondary text-uppercase fs-7" style="border-bottom: 2px solid #cbd5e1;">
                                    <th class="py-2" style="width: 4%;">ID</th>
                                    <th style="text-align: left; width: 22%;" class="ps-3">Module Name</th>
                                    <?php foreach ($columnsList as $col): ?>
                                        <th style="font-weight: 700;"><?php echo $col; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($modules)): ?>
                                    <tr>
                                        <td colspan="16" class="text-center text-muted py-4 fw-bold">No records found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($modules as $mod):
                                        $mId = $mod['oModuleid'];
                                        $rights = $access[$mId] ?? [];
                                    ?>
                                        <tr>
                                            <td class="font-monospace text-secondary fw-bold"><?php echo htmlspecialchars($mId); ?></td>
                                            <td class="text-start text-dark fw-bold ps-3"><?php echo htmlspecialchars($mod['oModulename']); ?></td>

                                            <?php foreach ($columnsList as $col):
                                                if ($col === 'Supervisor') {
                                                    $dbKey = 'oSupervisor';
                                                } elseif ($col === 'Manager') {
                                                    $dbKey = 'oManager';
                                                } else {
                                                    $dbKey = 'o' . $col;
                                                }

                                                $checked = (isset($rights[$dbKey]) && (int)$rights[$dbKey] === 1) ? 'checked' : '';
                                            ?>
                                                <td>
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <input type="checkbox"
                                                            class="form-check-input custom-switch permission-checkbox m-0"
                                                            data-module="<?php echo htmlspecialchars($mId); ?>"
                                                            data-column="<?php echo htmlspecialchars($col); ?>"
                                                            value="1"
                                                            style="cursor: pointer; width: 1.15rem; height: 1.15rem;"
                                                            <?php echo $checked; ?>>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <!-- Footer -->
    <footer class=" footer_intro">
        <div class="footer_content">
            <div class="wrapper">
                <div class="footer_container">
                    <div class="footer_img_content">
                        <figure><img src="../assets/images/nong_atoy_head.png" alt="Footer Logo"></figure>
                        <p>To become the customer's TOP OF MIND for building materials and home improvement needs in Cebu in 2027</p>
                    </div>

                    <div class="footer_info">
                        <div class="footer_help">
                            <p>Need Help? <span><a href="#">Contact IT Support</a></span></p>
                        </div>
                        <div class="footer_values">
                            <figure><img src="../assets/images/footer-img.png" alt="Values"></figure>
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

    <script>
        const targetUserId = "<?php echo urlencode($userId); ?>";
    </script>
</body>

</html>