<?php
/** @var string $base_path */
/** @var bool $isSuperAdmin */
/** @var array $allowedModules */

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: views/login.php");
    exit;
}
?>

<script src="<?php echo $base_path; ?>../assets/js/sidebar_button.js"></script>


<div class="sidebar bg-dark-slate text-white flex-shrink-0 p-3 shadow">
    <a href="<?php echo $base_path; ?>intro_page.php">
    <div class="pb-3 mb-4 text-center main_logo">
        <img src="<?php echo $base_path; ?>assets/images/atlantichardware_logo_with_since1963.png" alt="Logo" class="sidebar-logo mb-2">
    </div>
</a>

    <ul class="menu-list list-unstyled px-1">
        <!-- Module 1: MY ACCOUNT -->
        <?php if (hasAccess(1, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="<?php echo $base_path; ?>views/my_account.php" class="my_acc menu-link d-flex align-items-center justify-content-between">
                My Account
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 2: ADMINISTRATIVE TOOLS -->
        <?php if (hasAccess(2, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="<?php echo $base_path; ?>views/administrative_tools.php" class="admin_tools menu-link d-flex align-items-center justify-content-between">
                Administrative Tools
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 3: USER ACCOUNTS -->
        <?php if (hasAccess(3, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="<?php echo $base_path; ?>views/user_accounts.php" class="user_acc menu-link d-flex align-items-center justify-content-between">
                User Accounts
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 4: STORE DASHBOARD - MAIN -->
        <?php if (hasAccess(4, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item current_page_item mb-2">
            <a href="<?php echo $base_path; ?>index.php" class="store_dashboard menu-link d-flex align-items-center justify-content-between">
                Store Dashboard - Main
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 5: SALES PERFORMANCE -->
        <?php if (hasAccess(5, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="<?php echo $base_path; ?>views/dashboards/store_dashboard_main.php" class="sales_perf menu-link d-flex align-items-center justify-content-between">
                sales performance
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 6: OPPORTUNITY SALES -->
        <?php if (hasAccess(6, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="#" class="opp_sales menu-link d-flex align-items-center justify-content-between">
                Opportunity Sales
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 7: STOCK TRANSFER OUT -->
        <?php if (hasAccess(7, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="#" class="stock_out menu-link d-flex align-items-center justify-content-between">
                Stock Transfer Out
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 8: STOCK TRANSFER IN -->
        <?php if (hasAccess(8, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="#" class="stock_in menu-link d-flex align-items-center justify-content-between">
                Stock Transfer In
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 9: SALES TRANSACTION COUNT -->
        <?php if (hasAccess(9, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="#" class="sales_trans menu-link d-flex align-items-center justify-content-between">
                Sales Transaction Count
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 10: DELIVERY TRANSACTION COUNT -->
        <?php if (hasAccess(10, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="#" class="delivery_trans menu-link d-flex align-items-center justify-content-between">
                Delivery Transaction Count
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 11: CYCLE TIME -->
        <?php if (hasAccess(11, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="#" class="cylce_time menu-link d-flex align-items-center justify-content-between">
                Cycle Time
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 12: POS CASHIER PERFORMANCE -->
        <?php if (hasAccess(12, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="#" class="pos_cash menu-link d-flex align-items-center justify-content-between">
                POS Cashier Performance
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 13: CUSTOMER RETURNED -->
        <?php if (hasAccess(13, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="#" class="customer_ret menu-link d-flex align-items-center justify-content-between">
                Customer Returned
            </a>
        </li>
        <?php endif; ?>

        <!-- Module 14: TOP 20 SOLD ITEMS -->
        <?php if (hasAccess(14, $isSuperAdmin, $allowedModules)): ?>
        <li class="menu-item mb-2">
            <a href="#" class="sold_items menu-link d-flex align-items-center justify-content-between">
                Top 20 Sold Items
            </a>
        </li>
        <?php endif; ?>
        <li class="menu-item mb-2">
            <a href="<?php echo $base_path; ?>?action=logout" class="sidebar_logout menu-link d-flex align-items-center justify-content-between">
                Logout
            </a>
        </li>
    </ul>
</div>