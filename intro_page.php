<?php
$base_path = "./";

require_once $base_path . 'includes/auth_check.php';
require_once $base_path . 'controller/login_controller.php';

// Handle logout action 
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: views/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atlantic Hardware</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/media.css">
    <link rel="icon" href="assets/images/favicon.png">
</head>

<body class="intro_page">

    <div class="intro">
        <div class="wrapper">
            <div class="con">

                <div class="intro_top">
                    <div class="intro_logo">
                        <figure><img src="assets/images/logo/Left_logo.png" alt="atlantic logo"></figure>
                    </div>

                    <div class="top_intro_info">
                        <h1>We Lead, <span>You Build.</span></h1>
                        <p>Please select a branch to continue</p>
                    </div>

                </div>

                <div class="intro_btm">

                    <div class="intro_cards">
                        <!-- 1. MANDAUE (MDIY) -->
                        <section>
                            <figure><img src="assets/images/store/mandaue 1.png" alt="Mandaue Store"></figure>
                            <div class="card_info">
                                <h2>Mdiy</h2>
                                <a class="intro_btn" href="controller/store_controller.php?select_store=mdiy"></a>
                            </div>
                        </section>

                        <!-- 2. TABOAN (TDIY) -->
                        <section>
                            <figure><img src="assets/images/store/taboan 1.png" alt="Taboan Store"></figure>
                            <div class="card_info">
                                <h2>Tdiy</h2>
                                <a class="intro_btn" href="controller/store_controller.php?select_store=tdiy"></a>
                            </div>
                        </section>

                        <!-- 3. BULACAO (BDIY) -->
                        <section>
                            <figure><img src="assets/images/store/tabunok 1.png" alt="Store"></figure>
                            <div class="card_info">
                                <h2>Bdiy</h2>
                                <a class="intro_btn" href="controller/store_controller.php?select_store=bdiy"></a>
                            </div>
                        </section>

                        <!-- 4. CARCAR (CDIY) -->
                        <section>
                            <figure><img src="assets/images/store/carcar 1.png" alt="Carcar Store"></figure>
                            <div class="card_info">
                                <h2>Cdiy</h2>
                                <a class="intro_btn" href="controller/store_controller.php?select_store=cdiy"></a>
                            </div>
                        </section>

                        <!-- 5. MAGUIKAY -->
                        <section>
                            <figure><img src="assets/images/store/maguikay-store 1.png" alt="Maguikay Store"></figure>
                            <div class="card_info">
                                <h2>Maguikay</h2>
                                <a class="intro_btn" href="controller/store_controller.php?select_store=cdiy"></a>
                            </div>
                        </section>

                        <!-- 5. HQ -->
                        <section>
                            <figure><img src="assets/images/store/headq.png" alt="Head Quarters"></figure>
                            <div class="card_info">
                                <h2>HQ</h2>
                                <a class="intro_btn" href="controller/store_controller.php?select_store=cdiy"></a>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

</body>

</html>