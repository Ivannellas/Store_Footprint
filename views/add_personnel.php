<?php
session_start();

$base_path = "../";

require_once $base_path . 'includes/auth_check.php';
require_once $base_path . 'config/db.php';
require_once $base_path . 'controller/personnel_controller.php';

$conn = getDBConnection();

if (!$conn) {
    die("Database connection failed.");
}

// Handle AJAX Status Toggle Request
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    header('Content-Type: application/json');

    $targetId = (int)$_GET['id'];
    $success = false;
    $newStatus = '0';

    $uQuery = "SELECT status FROM tbl_footprint_personnel WHERE personnel_id = ? LIMIT 1";
    if ($uStmt = $conn->prepare($uQuery)) {
        $uStmt->bind_param("i", $targetId);
        $uStmt->execute();
        $row = $uStmt->get_result()->fetch_assoc();
        $uStmt->close();

        if ($row) {
            $currentStatus = (string)$row['status'];
            $newStatus = ($currentStatus === '1' || $currentStatus === 'Active') ? '0' : '1';

            $upQuery = "UPDATE tbl_footprint_personnel SET status = ? WHERE personnel_id = ?";
            if ($upStmt = $conn->prepare($upQuery)) {
                $upStmt->bind_param("si", $newStatus, $targetId);
                $success = $upStmt->execute();
                $upStmt->close();
            }
        }
    }

    echo json_encode(['success' => $success, 'newStatus' => $newStatus]);
    mysqli_close($conn);
    exit;
}

$personnelController = new PersonnelController($conn);
$message = '';
$msgType = '';

// Handle Add Personnel Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $res = $personnelController->HandleAddPersonnel($_POST);
        $message = $res['message'];
        $msgType = $res['success'] ? 'success' : 'danger';
    }
}

$personnelList = $personnelController->GetAllPersonnel();
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel Management</title>
    <!-- Local CSS Files -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light text-dark">

    <div class="container mt-5" style="max-width: 700px;">
        <!-- Header Controls -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="m-0">Personnel Management</h3>
            <div>
                <button type="button" class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
                    + Add Personnel
                </button>
                <a href="../index.php" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Personnel List Table -->
        <div class="table-responsive bg-white border rounded shadow-sm">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 15%;">#</th>
                        <th style="width: 55%;">Personnel Name</th>
                        <th class="text-center" style="width: 30%;">Status</th>
                    </tr>
                </thead>
                <tbody id="personnelTableBody">
                    <?php if (empty($personnelList)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No personnel found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($personnelList as $index => $person): ?>
                            <?php 
                                $isActive = ($person['status'] === '1' || $person['status'] === 1 || $person['status'] === 'Active');
                            ?>
                            <tr>
                                <td class="text-center text-secondary font-monospace"><?= $index + 1 ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($person['personnel_name']) ?></td>
                                <td class="text-center">
                                    <button type="button"
                                        class="btn-toggle-status border-0 bg-transparent  <?= $isActive ? 'text-success' : 'text-danger'; ?>"
                                        data-id="<?= htmlspecialchars($person['personnel_id']); ?>"
                                        id="status-display-<?= htmlspecialchars($person['personnel_id']); ?>">
                                        <?= $isActive ? 'Active' : 'Inactive'; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Personnel Modal -->
    <div class="modal fade" id="addPersonnelModal" tabindex="-1" aria-labelledby="addPersonnelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded border shadow-sm">
                <form method="POST" action="">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fs-6 fw-bold text-dark m-0" id="addPersonnelModalLabel">Add New Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-dark">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">FULL NAME / PERSONNEL NAME</label>
                            <input type="text" name="personnel_name" class="form-control" placeholder="e.g. Juan Dela Cruz" required autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save Personnel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Real-time Status Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.getElementById('personnelTableBody');

            if (tableBody) {
                tableBody.addEventListener('click', function (e) {
                    const btn = e.target.closest('.btn-toggle-status');
                    if (!btn) return;

                    const id = btn.getAttribute('data-id');
                    if (!id) return;

                    btn.disabled = true;

                    fetch(`add_personnel.php?action=toggle_status&id=${encodeURIComponent(id)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const isNowActive = (data.newStatus === '1' || data.newStatus === 1);
                                btn.textContent = isNowActive ? 'Active' : 'Inactive';
                                
                                if (isNowActive) {
                                    btn.classList.remove('text-danger');
                                    btn.classList.add('text-success');
                                } else {
                                    btn.classList.remove('text-success');
                                    btn.classList.add('text-danger');
                                }
                            } else {
                                alert('Failed to update status.');
                            }
                        })
                        .catch(err => {
                            console.error('Error toggling status:', err);
                            alert('An error occurred while toggling status.');
                        })
                        .finally(() => {
                            btn.disabled = false;
                        });
                });
            }
        });
    </script>
</body>

</html>