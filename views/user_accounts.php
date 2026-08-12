<?php
session_start();
$base_path = '../';

require_once '../config/db.php';
require_once '../controller/user_controller.php';
require_once '../controller/edit_user_account_controller.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? 'User';
$userId = $_SESSION['user_id'] ?? '';
$isSuperAdmin = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;

$conn = getDBConnection();

if ($isSuperAdmin) {
    $canAdd  = 1;
    $canEdit = 1;
    $canView = 1;
} else {
    $currentModuleId = '3';
    $privileges = null;

    $pQuery = "SELECT * FROM tbl_access 
            WHERE oUserid = ? 
            AND oModuleid = ? 
            LIMIT 1";
    if ($pStmt = $conn->prepare($pQuery)) {
        $pStmt->bind_param("ss", $userId, $currentModuleId);
        $pStmt->execute();
        $privileges = $pStmt->get_result()->fetch_assoc();
        $pStmt->close();
    }

    if (!$privileges || (int)$privileges['oMain'] !== 1) {
        mysqli_close($conn);
        header("Location: ../index.php");
        exit;
    }

    $canAdd  = (int)($privileges['oAdd'] ?? 0);
    $canEdit = (int)($privileges['oEdit'] ?? 0);
    $canView = (int)($privileges['oView'] ?? 0);
}

$userController = new UserController($conn);

if (isset($_GET['action']) && $_GET['action'] === 'view_user' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $data = $userController->AlluserId((int)$_GET['id']);
    echo json_encode($data ? $data : []);
    mysqli_close($conn);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    header('Content-Type: application/json');

    if ($canEdit !== 1) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        mysqli_close($conn);
        exit;
    }

    $targetUserId = $_GET['id'];
    $success = false;
    $newStatus = 0;

    $uQuery = "SELECT oActive FROM tbl_user WHERE oUserid = ? LIMIT 1";
    if ($uStmt = $conn->prepare($uQuery)) {
        $uStmt->bind_param("s", $targetUserId);
        $uStmt->execute();
        $userRow = $uStmt->get_result()->fetch_assoc();
        $uStmt->close();

        if ($userRow) {
            $newStatus = ((int)$userRow['oActive'] === 1) ? 0 : 1;

            $upQuery = "UPDATE tbl_user SET oActive = ? WHERE oUserid = ?";
            if ($upStmt = $conn->prepare($upQuery)) {
                $upStmt->bind_param("is", $newStatus, $targetUserId);
                $success = $upStmt->execute();
                $upStmt->close();
            }
        }
    }

    echo json_encode(['success' => $success, 'newStatus' => $newStatus]);
    mysqli_close($conn);
    exit;
}

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($searchTerm !== '') {
    $userList = $userController->searchUserManagement($searchTerm);
} else {
    $userList = $userController->renderUserManagement();
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Accounts Management</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/media.css">
    <link rel="icon" href="<?php echo $base_path; ?>assets/images/favicon.png">
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/user_accounts_manager.js"></script>
    <script>
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed-preload');
        }
    </script>
    <script src="<?php echo $base_path; ?>assets/js/sidebar_button.js"></script>
</head>

<body class="bg-light text-dark">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <nav class="navbar navbar-dark navbar-header px-4 py-3 mb-5 shadow-sm">

        <div class="navbar_title">
            <h1 class="h1 text-uppercase mb-0">User Accounts</h1>
        </div>

        <div class="d-flex">
            <a href="../index.php" class="primary_btn btn-sm btn-outline-light me-1">Back</a>
        </div>
    </nav>

    <div class="main_parent">
        <div class="wrapper">
            <div class="main_parent_con">

                <div class="d-flex gap-2 justify-content-between mb-3">
                    <div class="search-bar">
                        <div class="">
                            <input type="text"
                                id="searchInput"
                                name="search"
                                class="form-control"
                                placeholder="Search users..."
                                value="<?php echo htmlspecialchars($searchTerm); ?>"
                                autocomplete="off">
                        </div>
                    </div>
                    <a href="add_user_account.php" class="primary_btn btn-sm btn-light me-1 action-btn" data-permission="<?php echo $canAdd; ?>">Add Account</a>
                </div>


                <div class="table-responsive bg-white border rounded custom-table-shadow">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 15%;">User ID</th>
                                <th style="width: 35%;">Fullname</th>
                                <th style="width: 20%;">Username</th>
                                <th class="text-center" style="width: 15%;">Status</th>
                                <th class="text-center" style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <?php if (empty($userList)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No users found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($userList as $user): ?>
                                    <?php $isActive = ((int)$user['oActive'] === 1); ?>
                                    <tr>
                                        <td class="text-center text-secondary font-monospace"><?php echo htmlspecialchars($user['oUserid']); ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($user['oFullname']); ?></td>
                                        <td><?php echo htmlspecialchars($user['oUsername']); ?></td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="action-btn border-0 bg-transparent <?php echo $isActive ? 'text-success' : 'text-danger'; ?>"
                                                data-permission="<?php echo $canEdit; ?>"
                                                data-id="<?php echo htmlspecialchars($user['oUserid']); ?>"
                                                id="status-display-<?php echo htmlspecialchars($user['oUserid']); ?>">
                                                <?php echo $isActive ? 'Active' : 'InActive'; ?>
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group gap-1">
                                                <a href="edit_user_account.php?id=<?php echo urlencode($user['oUserid']); ?>"
                                                    class="btn btn-sm btn-outline-dark action-btn"
                                                    data-permission="<?php echo $canEdit; ?>">Edit</a>

                                                <a href="user_permission_account.php?id=<?php echo urlencode($user['oUserid']); ?>"
                                                    class="btn btn-sm btn-outline-dark">Permissions</a>

                                                <button type="button"
                                                    class="btn btn-sm btn-outline-dark btn-view-user action-btn"
                                                    data-permission="<?php echo $canView; ?>"
                                                    data-id="<?php echo htmlspecialchars($user['oUserid']); ?>">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded border shadow-sm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fs-6 fw-bold text-dark m-0" id="modalUserTitle">Account Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-dark">
                    <div class="row border-bottom pb-2 mb-2">
                        <div class="col-4 text-muted small fw-bold">USER ID</div>
                        <div class="col-8 font-monospace" id="mUserId"></div>
                    </div>
                    <div class="row border-bottom pb-2 mb-2">
                        <div class="col-4 text-muted small fw-bold">USERNAME</div>
                        <div class="col-8" id="mUsername"></div>
                    </div>
                    <div class="row border-bottom pb-2 mb-2">
                        <div class="col-4 text-muted small fw-bold">FULLNAME</div>
                        <div class="col-8 fw-bold" id="mFullname"></div>
                    </div>
                    <div class="row border-bottom pb-2 mb-2">
                        <div class="col-4 text-muted small fw-bold">POSITION</div>
                        <div class="col-8 text-secondary" id="mPosition"></div>
                    </div>
                    <div class="row border-bottom pb-2 mb-2">
                        <div class="col-4 text-muted small fw-bold">POSTCODE</div>
                        <div class="col-8 text-secondary" id="mPostcode"></div>
                    </div>
                    <div class="row pt-1">
                        <div class="col-4 text-muted small fw-bold">STATUS</div>
                        <div class="col-8 fw-bold" id="mStatus"></div>
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
                        <figure><img src="..//assets/images/nong_atoy_head.png" alt="Footer Logo"></figure>
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


</body>

</html>