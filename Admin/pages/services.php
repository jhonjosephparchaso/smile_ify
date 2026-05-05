<?php
session_start();

$currentPage = 'services';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Admin/includes/navbar.php';

$updateSuccess = $_SESSION['updateSuccess'] ?? '';
$updateError   = $_SESSION['updateError'] ?? '';
?>

<title>Services</title>

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

<div class="services-table">
    <table id="servicesTable" class="transaction-table"></table>
</div>

<div id="manageServiceModal" class="manage-service-modal">
    <div class="manage-service-modal-content">
        <div id="serviceModalBody" class="manage-service-modal-content-body">
            <!-- Supply info will be loaded here -->
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>

