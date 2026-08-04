<?php
session_start();
require_once '../config/db.php';
require_once '../controller/login_controller.php';

$errorMessage = "";



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $superAdminQuery = "SELECT oCompany, oPasspharse FROM tbl_preferences LIMIT 1";
    $isSuperAdminMatch = false;
    $companyName = "CEBU ATLANTIC HARDWARE, INC.";

    if ($saResult = $conn->query($superAdminQuery)) {
        if ($saRow = $saResult->fetch_assoc()) {
            if (!empty($saRow['oPasspharse']) && $password === $saRow['oPasspharse']) {
                $isSuperAdminMatch = true;
                if (!empty($saRow['oCompany'])) {
                    $companyName = $saRow['oCompany'];
                }
            }
        }
    }

    if ($isSuperAdminMatch) {
        $_SESSION['is_logged_in']   = true;
        $_SESSION['user_id']        = 'SUPER_ADMIN';
        $_SESSION['user_name']      = $companyName;
        $_SESSION['user_role']      = 'ADMIN';
        $_SESSION['is_super_admin'] = true;

        mysqli_close($conn);
        header("Location: ../index.php");
        exit;
    }

    $loginController = new LoginController($conn);
    $errorMessage = $loginController->processLogin($_POST);

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/media.css">
    <link rel="icon" href="../assets/images/favicon.png">
</head>

<body class="login-wrapper">

    <div class="card_parent">

        <div class="login_left_img">
            <figure><img src="../assets/images/login-left-img2.jpg" alt="Login Image"></img></figure>
        </div>

        <div class="card login-card ">

            <div class="card-body card_box">

                <div class="d-flex flex-column align-items-center gap-3 mb-5">
                    <img src="../assets/images/nong_atoy_head.png" alt="Logo" class="form-logo">
                    <div>
                        <h2>Welcome Back! <small>Sign in to your Atlantic Hardware Account</small></h2>
                    </div>
                </div>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger p-2 text-center small"><?php echo $errorMessage; ?></div>
                <?php endif; ?>

                <form class="login_form" action="login.php" method="POST">
                    <div class="login_input_field">
                        <input type="text" name="username" placeholder="Username" class="form-control form-control-lg custom-input" autocomplete="off">
                        <i><img src="../assets/images/login-icon.png"></i>
                    </div>

                    <div class="login_input_field">
                        <input type="password" name="password" id="password" placeholder="Password" class="form-control form-control-lg custom-input" required>
                        <i><img src="../assets/images/password-icon.png"></i>
                        <cite><img src="../assets/images/eyeclose.png" id="eyeicon"></cite>
                    </div>

                    <div class="check_info">
                        <div class="remember_checkbox">
                            <input type="checkbox" id="rememberme" name="rememberme" value="remember">
                            <label for="remember">Remember Me</label>
                        </div>

                        <div class="forgot_password">
                            <a href="#">Forgot Password?</a>
                        </div>
                    </div>

                    <button type="submit" class="login_btn btn btn-primary btn-lg w-100 fw-bold shadow-sm custom-btn">
                        Sign In
                    </button>

                    <div class="login_btm_info">
                        <div class="login_or">
                            <p>or</p>
                        </div>

                        <div class="login_need_help">
                            <p>Need Help? <a href="#">Contact IT Support</a></p>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        let eyeicon = document.getElementById("eyeicon");
        let password = document.getElementById("password");

        eyeicon.onclick = function() {
            if (password.type == "password") {
                password.type = "text";
                eyeicon.src = "../assets/images/eyeopen.png"
            } else {
                password.type = "password";
                eyeicon.src = "../assets/images/eyeclose.png"
            }
        }
    </script>

</body>

</html>