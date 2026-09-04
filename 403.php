<?php
session_start();
$base_path = "../";     

$userName = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="icon" href="<?php echo $base_path; ?>assets/images/favicon.png">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 text-center">
                <div class="card shadow-lg border-0 rounded-4 p-4">
                    <div class="card-body">
                        <div class="mb-3 text-danger">
                           <img src="assets/images/icon/security.png" alt="403 Icon" class="mb-3" style="width: 80px;">
                        </div>

                        <h1 class="fw-bold text-dark display-6 mb-2">403 - Access Denied</h1>
                        <p class="text-muted mb-4">
                            Hello, <strong><?php echo htmlspecialchars($userName); ?></strong>. You do not have permission to view this module. Please contact your administrator.
                        </p>

                        <!-- Action Controls -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="views/login.php" class="btn btn-primary px-4 fw-semibold shadow-sm">
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>