<?php
session_start();

$currentPage = 'calendar';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Admin/includes/navbar.php';
$updateSuccess = $_SESSION['updateSuccess'] ?? "";
$updateError = $_SESSION['updateError'] ?? "";

$sql = "SELECT branch_id, nickname, address, phone_number, status FROM branch";
$result = $conn->query($sql);

$branches = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }
}
$conn->close();
?>

<title>Calendar</title>

<?php
$adminBranchId = $_SESSION['branch_id'] ?? '';
?>

<div class="tabs-container">
    <div class="tabs-nomargin">
        <?php foreach ($branches as $branch): ?>
            <div 
                class="tab <?= ($branch['branch_id'] == $adminBranchId ? 'active' : '') ?>" 
                data-branch-id="<?= $branch['branch_id'] ?>"
                onclick="switchTabBranch(<?= $branch['branch_id'] ?>)">
                <?= htmlspecialchars($branch['nickname']) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<input 
    type="hidden" 
    id="branchIdInput" 
    value="<?= htmlspecialchars($adminBranchId) ?>"
>

<div class="calendar-container">
    <div id="calendarLegend" class="legend"></div>
    <div id="calendar"></div>
</div>

<div id="appointmentModalDetails" class="manage-calendar-modal">
    <div class="manage-calendar-modal-content">
        <div id="modalBody" class="manage-calendar-modal-content-body"></div>
    </div>
</div>

<?php if (!empty($updateSuccess) || !empty($updateError)): ?>
    <div id="toastContainer">
        <?php if (!empty($updateSuccess)): ?>
            <div class="toast success"><?= htmlspecialchars($updateSuccess) ?></div>
            <?php unset($_SESSION['updateSuccess']); ?>
        <?php endif; ?>

        <?php if (!empty($updateError)): ?>
            <div class="toast error"><?= htmlspecialchars($updateError) ?></div>
            <?php unset($_SESSION['updateError']); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
