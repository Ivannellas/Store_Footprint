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
    <a href="<?php echo $base_path; ?>index.php">
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
                Dashboard
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