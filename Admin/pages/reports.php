<?php
session_start();

$currentPage = 'reports';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Admin/includes/navbar.php';
require_once BASE_PATH . '/includes/db.php';

$adminBranchId = $_SESSION['branch_id'] ?? null;

if (!$adminBranchId) {
    echo "<div class='alert alert-danger'>No branch assigned to this secretary account.</div>";
    require_once BASE_PATH . '/includes/footer.php';
    exit();
}

$stmt = $conn->prepare("SELECT branch_id, name, address, phone_number, status FROM branch WHERE branch_id = ?");
$stmt->bind_param("i", $adminBranchId);
$stmt->execute();
$result = $stmt->get_result();
$branch = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$branch) {
    require_once BASE_PATH . '/includes/footer.php';
    exit();
}

$modes = ['daily', 'weekly', 'monthly'];
?>

<title>Reports - <?= htmlspecialchars($branch['name']) ?></title>

<div class="reports-container" id="branch<?= $branch['branch_id'] ?>">

    <div class="sub-tabs">
        <?php foreach ($modes as $j => $mode): ?>
            <div 
                class="sub-tab <?= $j === 0 ? 'active' : '' ?>" 
                data-mode="<?= $mode ?>"
                onclick="switchSubTab(<?= $branch['branch_id'] ?>, '<?= $mode ?>')">
                <?= strtoupper($mode) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php foreach ($modes as $j => $mode): ?>
        <div 
            class="sub-tab-content <?= $j === 0 ? 'active' : '' ?>" 
            id="branch<?= $branch['branch_id'] ?>-<?= $mode ?>" 
            style="width: 80%;">
            <?php 
                $currentMode = $mode; 
                include BASE_PATH . "/includes/reportSection.php"; 
            ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
    const ADMIN_BRANCH_ID = <?= json_encode($_SESSION['branch_id'] ?? null) ?>;
</script>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
