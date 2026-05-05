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

$appointmentId = $_GET['id'] ?? null;
if (!$appointmentId) {
    echo "<p>No appointment selected.</p>";
    require_once BASE_PATH . '/includes/footer.php';
    exit();
}

$updateSuccess = $_SESSION['updateSuccess'] ?? "";
$updateError   = $_SESSION['updateError'] ?? "";
unset($_SESSION['updateSuccess'], $_SESSION['updateError']);

$backTab = $_GET['backTab'] ?? 'recent';
$validTabs = ['dental_transactions', 'vitals', 'prescriptions'];
$activeTab = in_array($_GET['tab'] ?? '', $validTabs) ? $_GET['tab'] : 'dental_transactions';
?>
<title>Appointment Details</title>

<div class="profile-container">
    <div class="profile-section">
        <div class="back-button-container">
            <a href="<?= BASE_URL ?>/Admin/pages/patients.php?tab=<?= htmlspecialchars($backTab) ?>" class="back-button-icon">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
        </div>
        
        <div class="profile-card" id="appointmentCard">
            <p>Loading profile</p>
        </div>
        
        <?php if (!empty($updateSuccess) || !empty($updateError)): ?>
            <div id="toastContainer">
                <?php if (!empty($updateSuccess)): ?>
                    <div class="toast success"><?= htmlspecialchars($updateSuccess) ?></div>
                <?php endif; ?>
                <?php if (!empty($updateError)): ?>
                    <div class="toast error"><?= htmlspecialchars($updateError) ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="tabs-container">
        <div class="tabs-patient">
            <div class="tab <?= $activeTab === 'dental_transactions' ? 'active' : '' ?>" onclick="switchTab('dental_transactions')">Transaction</div>
            <div class="tab <?= $activeTab === 'vitals' ? 'active' : '' ?>" onclick="switchTab('vitals')">Vitals</div>
            <div class="tab <?= $activeTab === 'prescriptions' ? 'active' : '' ?>" onclick="switchTab('prescriptions')">Prescriptions</div>
        </div> 
        
        <div class="tab-content <?= $activeTab === 'dental_transactions' ? 'active' : '' ?>" id="dental_transactions">
            <table id="dentaltransactionTable" class="transaction-table"></table>
        </div>

        <div class="tab-content <?= $activeTab === 'vitals' ? 'active' : '' ?>" id="vitals">
            <table id="vitalTable" class="transaction-table"></table>
        </div>

        <div class="tab-content <?= $activeTab === 'prescriptions' ? 'active' : '' ?>" id="prescriptions">
            <table id="prescriptionTable" class="transaction-table"></table>
        </div>
    </div>

    <div id="manageRecordModal" class="manage-appointment-modal">
        <div class="manage-appointment-modal-content">
            <div id="modalRecordBody" class="manage-appointment-modal-content-body"></div>
        </div>
    </div>
</div>

<div id="manageAppointmentModal" class="manage-patient-modal">
    <div class="manage-patient-modal-content">
        <div id="appointmentModalBody" class="manage-patient-modal-content-body">
            <!-- Appointment Booking info will be loaded here -->
        </div>
    </div>
</div>

<div id="setStatusModal" class="edit-profile-modal">
    <div class="edit-profile-modal-content">
        <form id="statusForm" method="POST" autocomplete="off">
            <input type="hidden" name="user_id" id="statusUserId">
            <input type="hidden" name="status" id="statusValue">
            <input type="hidden" name="appointment_id" id="statusAppointmentId">

            <p id="statusMessage">Are you sure you want to continue?</p>

            <div class="button-group">
                <button type="submit" class="form-button confirm-btn">Confirm</button>
                <button type="button" class="form-button cancel-btn" onclick="closeStatusModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once BASE_PATH . '/includes/db.php';
$dentistIdQuery = $conn->prepare("
    SELECT dentist_id 
    FROM appointment_transaction 
    WHERE appointment_transaction_id = ?
");
$dentistIdQuery->bind_param("i", $appointmentId);
$dentistIdQuery->execute();
$dentistResult = $dentistIdQuery->get_result();
$dentistId = $dentistResult->fetch_assoc()['dentist_id'] ?? null;
$dentistIdQuery->close();
$conn->close();
?>
<script>
    const appointmentId = "<?= htmlspecialchars($appointmentId) ?>";
    const branchId = "<?= htmlspecialchars($_SESSION['branch_id'] ?? '') ?>";
    const userId = "<?= htmlspecialchars($_SESSION['user_id']) ?>";
    window.appointmentDentistId = <?= $dentistId ? (int)$dentistId : 'null' ?>;
</script>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
