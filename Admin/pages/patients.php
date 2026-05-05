<?php
session_start();

$currentPage = 'patients';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Admin/includes/navbar.php';
require_once BASE_PATH . '/Admin/processes/patients/auto_deactivate_patients.php';
$activeTab = $_GET['tab'] ?? 'recent';
$updateSuccess = $_SESSION['updateSuccess'] ?? '';
$updateError   = $_SESSION['updateError'] ?? '';
?>
<title>Patients</title>

<div class="tabs-container">
    <div class="tabs">
        <div class="tab <?= $activeTab === 'recent' ? 'active' : '' ?>" onclick="switchTab('recent')">Recent Bookings</div>
        <div class="tab <?= $activeTab === 'registered' ? 'active' : '' ?>" onclick="switchTab('registered')">Registered Patients</div>
        <div class="tab <?= $activeTab === 'inactive' ? 'active' : '' ?>" onclick="switchTab('inactive')">Inactive / Archived</div>
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

    <div class="tab-content <?= $activeTab === 'recent' ? 'active' : '' ?>" id="recent">
        <table id="recentTable" class="transaction-table"></table>
    </div>

    <div class="tab-content <?= $activeTab === 'registered' ? 'active' : '' ?>" id="registered">
        <table id="registeredTable" class="transaction-table"></table>
    </div>
    
    <div class="tab-content <?= $activeTab === 'inactive' ? 'active' : '' ?>" id="inactive">
        <table id="inactiveTable" class="transaction-table"></table>
    </div>
</div>

<div id="transactionModal" class="manage-transaction-modal">
    <div class="manage-transaction-modal-content">
        <div id="transactionModalBody" class="manage-transaction-modal-content-body">
            <!-- Transaction details will be loaded here -->
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
