<?php
session_start();
$base_path = '../';

require_once '../config/db.php';
require_once '../controller/add_user_account_controller.php';
require_once '../controller/user_controller.php';
require_once __DIR__ . '/../includes/auth_check.php';


if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'] ?? '';
$isSuperAdmin = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;

$conn = getDBConnection();

if (!$isSuperAdmin) {
    $authCheck = new UserController($conn);
    $currentModuleId = '3';

    // Check if the user has permission
    if (!$authCheck->checkActionPermission($userId, $currentModuleId, 'Add')) {
        mysqli_close($conn);
        header("Location: user_accounts.php");
        exit;
    }
}

$userController = new AddUserAccountController($conn);

if (isset($_GET['action']) && $_GET['action'] === 'verify_postcode') {
    $code = isset($_GET['code']) ? (int)$_GET['code'] : 0;
    $isAvailable = $userController->isPostcodeAvailable($code);

    echo $isAvailable ? 'available' : 'taken';
    mysqli_close($conn);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $message = "Failed to add, Passwords do not match";
    } else {
        $message = $userController->AddUserAccount($_POST) ? "Account created successfully" : "Failed to create account";
    }
}

$userName = $_SESSION['user_name'] ?? 'User';

$res = mysqli_query($conn, "SHOW TABLE STATUS LIKE 'tbl_user'");
$nextUserId = mysqli_fetch_assoc($res)['Auto_increment'] ?? '';

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New User Account</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/media.css">
    <link rel="icon" href="<?php echo $base_path; ?>assets/images/favicon.png">
    <script>
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed-preload');
        }
    </script>
    <script src="<?php echo $base_path; ?>assets/js/sidebar_button.js"></script>
</head>

<body class="bg-white text-dark ">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <nav class="navbar navbar-dark navbar-header px-4 py-3 mb-5 shadow-sm">

        <div class="navbar_title">
            <h1 class="h1 text-uppercase mb-0">Create User Account</h1>
        </div>

        <div class="d-flex">
            <a href="user_accounts.php" class="primary_btn btn-sm btn-outline-light me-1">Back</a>
        </div>
    </nav>

    <div class="main_parent">
        <div class="wrapper">
            <div class="main_parent_con">

                <div class="container forms" style="max-width: 600px;">

                    <?php if (!empty($message)): ?>
                        <p class="fw-bold mb-3 alert alert-info py-2 px-3 small"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>

                    <form action="add_user_account.php" method="POST">
                        <div class="text-center mb-4" style="margin-top: -1.5rem;">
                            <h2 class="fs-4 fw-bold text-dark mb-1">Create User Account</h2>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary text-uppercase ">User ID:</label>
                            <input type="text" name="userid" class="form-control font-monospace bg-light" value="<?php echo htmlspecialchars($nextUserId); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary text-uppercase ">Full Name:</label>
                            <input type="text" name="fullname" class="form-control custom-input" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary text-uppercase ">Username:</label>
                            <input type="text" name="username" class="form-control custom-input" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary text-uppercase ">Position:</label>
                            <input type="text" name="position" class="form-control custom-input" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary text-uppercase ">Password:</label>
                            <input type="password" id="password" name="password" class="form-control custom-input" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary text-uppercase ">Confirm Password:</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control custom-input" required>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-secondary text-uppercase  mb-0">Postcode:</label>
                                <button onclick="displayCode()" type="button" id="btn-generate-postcode" class="btn btn-link p-0 small text-decoration-none text-dark fw-bold" style="font-size: 1rem;">Generate</button>
                            </div>
                            <input type="number" id="postcode" name="postcode" class="form-control custom-input" min="1000" max="9999" placeholder="0000" required>
                            <div id="postcode-alert" style="display: none; font-size: 0.85rem; font-weight: bold; margin-top: 5px; color: red;"></div>
                        </div>

                        <div class="row mb-4">
                            <div class="col">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="active" value="1" class="form-check-input custom-switch" id="active" checked style="cursor: pointer;">
                                    <label class="form-check-label text-secondary fw-bold small text-uppercase " for="active" style="cursor: pointer;">Active Status</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn custom-btn bg-dark-slate text-white w-100 mb-4 fw-bold border-0 text-uppercase  shadow-sm py-2">Save Account</button>
                        <button type="button" onclick="window.location.href='user_accounts.php'" class="btn btn-sm btn-outline-dark mb-4 w-100 fw-bold text-uppercase py-2 shadow-sm"">Cancel</button>
        </form>
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
                            <script src=" ../assets/js/postcode_generator.js" defer></script>
</body>

</html>