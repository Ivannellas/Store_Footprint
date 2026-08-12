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

$userId = $_SESSION['user_id'] ?? '';
$userName = $_SESSION['user_name'] ?? 'User';
$isSuperAdmin = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;

$conn = getDBConnection();
$userController = new UserController($conn);
$editController = new EditUserAccountController($conn);

if (!$isSuperAdmin) {
    if (!$userController->checkActionPermission($userId, '1', 'Main')) {
        mysqli_close($conn);
        header("Location: ../index.php?error=unauthorized");
        exit;
    }
}

$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $canEdit = $isSuperAdmin || $userController->checkActionPermission($userId, '1', 'Edit');

    if ($canEdit) {
        if ($editController->UpdateProfile($_POST, $userId)) {
            $message = "Profile details updated successfully.";
            $messageClass = "alert alert-success py-2 px-3 small fw-bold mb-3";
            if (!empty($_POST['username'])) {
                $_SESSION['user_name'] = trim($_POST['username']);
                $userName = $_SESSION['user_name'];
            }
        } else {
            $message = "Failed to update profile. Required fields are missing.";
            $messageClass = "alert alert-danger py-2 px-3 small fw-bold mb-3";
        }
    } else {
        $message = "Access Denied: You do not have the 'Edit' permission.";
        $messageClass = "alert alert-danger py-2 px-3 small fw-bold mb-3";
    }
}

$profileData = $userController->AlluserId($userId);
$canUserEdit = $isSuperAdmin || $userController->checkActionPermission($userId, '1', 'Edit');

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account Profile</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/media.css">
    <script>
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed-preload');
        }
    </script>
    <script src="<?php echo $base_path; ?>assets/js/sidebar_button.js"></script>
</head>

<body class="bg-white text-dark page-body">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <nav class="navbar navbar-dark navbar-header px-4 py-3 mb-5 shadow-sm">
        <div class="navbar_title">
            <h1 class="h1 text-uppercase mb-0">My Account Profile</h1>
        </div>
        <div>
            <a href="../index.php" class="primary_btn btn-sm btn-outline-light">Back</a>
        </div>
        </div>
    </nav>
    <div class="main_parent">
        <div class="wrapper">
            <div class="main_parent_con">

                <div class="account_info" style="width: 100%; max-width: 800px;">
                    <?php if (!empty($message)): ?>
                        <div class="<?php echo $messageClass; ?>" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="card border rounded shadow-sm">
                            <div class="card-header bg-light border-bottom py-2 d-flex justify-content-between align-items-center">
                                <span class="small fw-bold text-secondary text-uppercase">Profile Information</span>

                                <?php if ($canUserEdit): ?>
                                    <button type="button" id="btn-toggle-edit" class="btn btn-sm btn-link text-dark text-decoration-none p-0 fw-bold small">Edit Details</button>
                                <?php endif; ?>
                            </div>

                            <div class="card-body small">
                                <div class="row mb-3 border-bottom pb-2 align-items-center">
                                    <div class="col-4 fw-bold text-secondary text-uppercase" style="font-size: 0.75rem;">Account ID :</div>
                                    <div class="col-8 fw-bold font-monospace text-dark opacity-75">
                                        <?php echo htmlspecialchars($profileData['oUserid'] ?? $userId); ?>
                                    </div>
                                </div>

                                <div class="row mb-3 border-bottom pb-2 align-items-center">
                                    <div class="col-4 fw-bold text-secondary text-uppercase" style="font-size: 0.75rem;">Username :</div>
                                    <div class="col-8">
                                        <span class="view-mode text-dark fw-semibold"><?php echo htmlspecialchars($profileData['oUsername'] ?? $userName); ?></span>
                                        <input type="text" name="username" class="form-control form-control-sm edit-mode custom-input d-none" value="<?php echo htmlspecialchars($profileData['oUsername'] ?? $userName); ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3 border-bottom pb-2 align-items-center">
                                    <div class="col-4 fw-bold text-secondary text-uppercase" style="font-size: 0.75rem;">Full Name :</div>
                                    <div class="col-8">
                                        <span class="view-mode fw-semibold text-dark"><?php echo htmlspecialchars($profileData['oFullname'] ?? 'N/A'); ?></span>
                                        <input type="text" name="fullname" class="form-control form-control-sm edit-mode custom-input d-none" value="<?php echo htmlspecialchars($profileData['oFullname'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3 border-bottom pb-2 align-items-center">
                                    <div class="col-4 fw-bold text-secondary text-uppercase" style="font-size: 0.75rem;">Assigned Position :</div>
                                    <div class="col-8">
                                        <span class="view-mode text-uppercase fs-7"><?php echo htmlspecialchars($profileData['oPosition'] ?? 'N/A'); ?></span>
                                        <input type="text" name="position" class="form-control form-control-sm edit-mode custom-input d-none" value="<?php echo htmlspecialchars($profileData['oPosition'] ?? 'N/A'); ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3 border-bottom pb-2 align-items-center">
                                    <div class="col-4 fw-bold text-secondary text-uppercase" style="font-size: 0.75rem;">Postcode :</div>
                                    <div class="col-8">
                                        <span class="view-mode font-monospace text-secondary fw-bold"><?php echo htmlspecialchars($profileData['oPostcode'] ?? '0000'); ?></span>
                                        <input type="number" name="postcode" class="form-control form-control-sm edit-mode custom-input d-none" value="<?php echo htmlspecialchars($profileData['oPostcode'] ?? '0000'); ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-2 align-items-center edit-mode d-none">
                                    <div class="col-4 fw-bold text-secondary text-uppercase" style="font-size: 0.75rem;">New Password:</div>
                                    <div class="col-8">
                                        <input type="password" name="password" class="form-control form-control-sm custom-input">
                                        <input type="hidden" name="action" value="update_profile">
                                        <input type="hidden" name="active" value="1">
                                    </div>
                                </div>
                            </div>

                            <div id="edit-actions-footer" class="card-footer flex_box_center bg-light border-top py-2 text-end d-none">
                                <button type="button" id="btn-cancel-edit" class="primary_btn cancel_btn">Cancel</button>
                                <button type="submit" class="primary_btn">Save Changes</button>
                            </div>
                        </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const btnToggleEdit = document.getElementById('btn-toggle-edit');
            const btnCancelEdit = document.getElementById('btn-cancel-edit');
            const viewElements = document.querySelectorAll('.view-mode');
            const editElements = document.querySelectorAll('.edit-mode');
            const footerActions = document.getElementById('edit-actions-footer');

            if (btnToggleEdit) {
                btnToggleEdit.addEventListener('click', function() {
                    viewElements.forEach(el => el.classList.add('d-none'));
                    editElements.forEach(el => el.classList.remove('d-none'));
                    footerActions.classList.remove('d-none');
                    btnToggleEdit.classList.add('d-none');
                });
            }

            if (btnCancelEdit) {
                btnCancelEdit.addEventListener('click', function() {
                    viewElements.forEach(el => el.classList.remove('d-none'));
                    editElements.forEach(el => el.classList.add('d-none'));
                    footerActions.classList.add('d-none');
                    btnToggleEdit.classList.remove('d-none');
                });
            }
        });
    </script>
</body>

</html>