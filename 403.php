<?php
session_start();

$userName = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/media.css">
    <link rel="icon" href="assets/images/favicon.png">
</head>

<body class="body-bg">
    <div class="main_container">
        <div class="card_flex_box">
            <div class="card_body">
                <h1>403 - Access Denied</h1>
                <p>Hello, <strong><?php echo htmlspecialchars($userName); ?></strong>. Sorry, you don’t have permission to access this module. This area is restricted to authorized users only.</p>
                <p>Please contact your <strong>Administrator</strong> if you believe you should have access.</p>
                <div class="for03_btn_parent">
                    <a href="views/login.php" class="primary_btn for03_btn">
                        Back to Login
                    </a>
                </div>
            </div>
            <div class="for03_image">
                <figure><img src="assets/images/403.png" alt="403 image"></figure>
            </div>
        </div>
    </div>

</body>

</html>