<?php
session_start();
$base_path = '../';

require_once '../config/db.php';
require_once '../controller/edit_user_account_controller.php';
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
$editController = new EditUserAccountController($conn);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($editController->UpdateProfile($_POST, $userId)) {
        $message = "Record updated successfully.";
    } else {
        $message = "Failed to update record details.";
    }
}

$userName = $_SESSION['user_name'] ?? 'User';

$user = $editController->LoadUserData($userId);
mysqli_close($conn);

if (!$user) {
    echo "The targeted user account information could not be found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Configuration</title>
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

<body class="bg-white text-dark ">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <nav class="navbar navbar-dark navbar-header px-4 py-3 mb-5 shadow-sm">

        <div class="navbar_title">
            <h1 class="h1 text-uppercase mb-0">Edit User Account</h1>
        </div>

        <div class="d-flex">
            <a href="user_accounts.php" class="primary_btn btn-sm btn-outline-light me-1">Back</a>
        </div>
    </nav>

    <div class="main_parent">
        <div class="wrapper">
            <div class="main_parent_con">
                <div class="container forms" style="max-width: 500px;">

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info py-2 px-3 small fw-bold mb-3"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" class="small">
                        <div class="text-center mb-4" style="margin-top: -1.5rem;">
                            <h2 class="fs-4 fw-bold text-dark mb-1">Edit User Account</h2>
                        </div>
                        <div class="mb-2">
                            <label class="form-label mb-0 fw-bold mt-3 text-secondary text-uppercase" style="font-size: 0.75rem; ">Username:</label>
                            <input type="text" name="username" class="form-control form-control-sm custom-input" value="<?php echo htmlspecialchars($user['oUsername'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label mb-0 fw-bold mt-3 text-secondary text-uppercase " style="font-size: 0.75rem;">Fullname:</label>
                            <input type="text" name="fullname" class="form-control form-control-sm custom-input" value="<?php echo htmlspecialchars($user['oFullname'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label mb-0 fw-bold mt-3 text-secondary text-uppercase " style="font-size: 0.75rem;">Position / Title:</label>
                            <input type="text" name="position" class="form-control form-control-sm custom-input" value="<?php echo htmlspecialchars($user['oPosition'] ?? ''); ?>">
                        </div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <label class="form-label mb-0 fw-bold mt-3 text-secondary text-uppercase " style="font-size: 0.75rem;">Postcode:</label>
                                <button type="button" id="generatePostcode" class="btn btn-link p-0 text-decoration-none small fw-bold text-dark" style="font-size: 1rem;">Generate</button>
                            </div>
                            <input type="number" id="postcode" name="postcode" class="form-control form-control-sm custom-input" value="<?php echo htmlspecialchars($user['oPostcode'] ?? '0'); ?>">
                        </div>

                        <div class="mb-2">
                            <label class="form-label mb-0 fw-bold mt-3 text-secondary text-uppercase " style="font-size: 0.75rem;">Password:</label>
                            <input type="password" name="password" class="form-control form-control-sm custom-input">
                        </div>

                        <div class="mb-3">
                            <label class="form-label mb-0 fw-bold mt-3 text-secondary text-uppercase " style="font-size: 0.75rem;">Status:</label>
                            <select name="active" class="form-select form-select-sm custom-input">
                                <option value="1" <?php echo ((int)$user['oActive'] === 1) ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ((int)$user['oActive'] === 0) ? 'selected' : ''; ?>>InActive</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-sm custom-btn bg-dark-slate text-white mb-4 w-100 fw-bold border-0 text-uppercase  py-2 shadow-sm">Save </button>
                        <button type="button" onclick="window.location.href='user_accounts.php'" class="btn btn-sm btn-outline-dark mb-4 w-100 text-uppercase py-2 shadow-sm">Cancel</button>
                    </form>
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
        document.getElementById('generatePostcode').addEventListener('click', function() {
            const code = Math.floor(1000 + Math.random() * 9000);
            document.getElementById('postcode').value = code;
        });
    </script>
</body>

</html>