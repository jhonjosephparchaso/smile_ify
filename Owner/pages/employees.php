<?php
session_start();

$currentPage = 'employees';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Owner/includes/navbar.php';
$activeTab = $_GET['tab'] ?? 'admin';
$updateSuccess = $_SESSION['updateSuccess'] ?? '';
$updateError   = $_SESSION['updateError'] ?? '';
?>
<title>Employees</title>

<div class="tabs-container">
    <div class="tabs">
        <div class="tab <?= $activeTab === 'admin' ? 'active' : '' ?>" onclick="switchTab('admin')">Secretaries</div>
        <div class="tab <?= $activeTab === 'dentist' ? 'active' : '' ?>" onclick="switchTab('dentist')">Dentists</div>
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

    <div class="tab-content <?= $activeTab === 'admin' ? 'active' : '' ?>" id="admin">
        <table id="adminsTable" class="transaction-table"></table>
    </div>

    <div class="tab-content <?= $activeTab === 'dentist' ? 'active' : '' ?>" id="dentist">
        <table id="dentistsTable" class="transaction-table"></table>
    </div>
</div>

<div id="manageModal" class="manage-employee-modal">
    <div class="manage-employee-modal-content">
        <div id="modalBody" class="manage-employee-modal-content-body">
            <!-- Appointment info will be loaded here -->
        </div>
    </div>
</div>

<div id="dentistUpdateModal" class="change-password-modal" style="display:none;">
    <div class="change-password-modal-content" style="max-width:650px;">
        <h3>Deactivate Dentist?</h3>
        <p id="dentistUpdateMessage">Checking affected appointments...</p>

        <div class="button-group">
            <button id="confirmDentistYes" class="form-button confirm-btn">Yes, Continue</button>
            <button id="confirmDentistNo" class="form-button cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>

<script>
function openDentistUpdateConfirm(dentistId, currentStatus, newStatus, onConfirm) {

    if (!(currentStatus === "Active" && newStatus === "Inactive")) {
        onConfirm();
        return;
    }

    const modal   = document.getElementById("dentistUpdateModal");
    const message = document.getElementById("dentistUpdateMessage");

    modal.style.display = "block";
    message.innerHTML = "Checking affected appointments...";

    fetch(`${BASE_URL}/Owner/processes/employees/check_affected_dentist_appointments.php?dentist_id=${dentistId}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                message.innerHTML = "Unable to check affected appointments.";
                return;
            }

            if (data.count > 0) {
                message.innerHTML = `
                    This dentist has <strong>${data.count}</strong> active appointment(s).<br><br>
                    Deactivating will affect bookings.
                `;
            } else {
                message.innerHTML = `
                    No active appointments found.<br><br>
                    You may safely deactivate this dentist.
                `;
            }
        })
        .catch(() => {
            message.innerHTML = "Error checking appointments.";
        });

    document.getElementById("confirmDentistYes").onclick = () => {
        modal.style.display = "none";
        onConfirm();
    };

    document.getElementById("confirmDentistNo").onclick = () => {
        modal.style.display = "none";
    };
}
</script>