<?php
/** @var string $base_path */
/** @var string $userName */
/** @var string $currentDate */
/** @var bool $isLoggedIn */
?>

<div class="top-navbar bg-success-brand px-4 shadow-sm d-flex align-items-center justify-content-end">
    <div class="d-flex align-items-center top-bar-text text-white small">
        <span class="me-3 opacity-90">User: <strong class="text-white text-capitalize"><?php echo htmlspecialchars($userName); ?></strong></span>
        <span class="me-3 border-start border-white border-opacity-25 ps-3 opacity-75"><?php echo $currentDate; ?></span>

        <span class="border-start border-white border-opacity-25 ps-3">
            <?php if ($isLoggedIn): ?>
                <a href="<?php echo $base_path; ?>index.php?action=logout" class="btn btn-sm primary_btn font-monospace">Logout</a>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>views/login.php" class="btn btn-sm btn-light text-dark fw-bold px-3">Login</a>
            <?php endif; ?>
        </span>
    </div>
</div>