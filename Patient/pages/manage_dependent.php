<?php
session_start();

$currentPage = 'profile';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$guardian_id = $_SESSION['user_id'];
$dependent_id = $_GET['user_id'] ?? null;

if (!$dependent_id) {
    die("Invalid dependent");
}

$sql = "SELECT * FROM users WHERE user_id = ? AND guardian_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $dependent_id, $guardian_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("You are not allowed to view this dependent.");
}

$dependent = $result->fetch_assoc();

$backTab = $_GET['backTab'] ?? 'profile';

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Patient/includes/navbar.php';

$qrImage = BASE_URL . '/images/qr/qr_payment.jpg';
$result = $conn->query("SELECT file_path FROM qr_payment ORDER BY id DESC LIMIT 1");

if ($result && $row = $result->fetch_assoc()) {
    $qrImage = BASE_URL . $row['file_path'];
}

$medcertPrice = 150;

$priceStmt = $conn->prepare("SELECT price FROM service WHERE name = 'Dental Certificate' LIMIT 1");
$priceStmt->execute();
$priceStmt->bind_result($priceFromDB);
$priceStmt->fetch();
$priceStmt->close();

if ($priceFromDB !== null) {
    $medcertPrice = $priceFromDB;
}
?>
<script>window.IS_DEPENDENT_PAGE = true;</script>
<title>Dependent Profile</title>

<div class="profile-container">

    <?php
    $dob_decrypted = null;
    if (!empty($dependent['date_of_birth'])) {
        $dob_decrypted = decryptField(
            $dependent['date_of_birth'],
            $dependent['date_of_birth_iv'],
            $dependent['date_of_birth_tag'],
            $ENCRYPTION_KEY
        );
    }

    $dobFormatted = $dob_decrypted ? date("F j, Y", strtotime($dob_decrypted)) : "-";
    $age = "-";
    if ($dob_decrypted) {
        $age = floor((time() - strtotime($dob_decrypted)) / 31556926);
    }
    ?>
    <div class="profile-section">
        <div class="profile-card">
            <div class="back-button-container">
                <a href="<?= BASE_URL ?>/Patient/pages/profile.php?tab=<?= htmlspecialchars($backTab) ?>" class="back-button-icon">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
            </div>
            <h2><?= htmlspecialchars($dependent['first_name'] . " " . $dependent['last_name']) ?></h2>
            
            <p><strong>Relationship:</strong> <?= htmlspecialchars($dependent['relationship']) ?></p>

            <p><strong>Gender:</strong> <?= htmlspecialchars($dependent['gender']) ?></p>

            <p><strong>Date of Birth:</strong> <?= $dobFormatted ?></p>

            <p><strong>Age:</strong> <?= $age ?></p>

            <p><strong>Status:</strong> <?= htmlspecialchars($dependent['status']) ?></p>

            <p style="margin-top:10px;color:#555;font-style:italic;">
                Dependent account
            </p>
        </div>
    </div>

    <div class="tabs-container">
        <div class="tabs">
            <div class="tab active" onclick="switchTab('appointment_history')">Appointment History</div>
            <div class="tab" onclick="switchTab('dental_transaction')">Dental Transactions</div>
        </div>

        <div class="tab-content active" id="appointment_history">
            <table id="appointmentTable" class="transaction-table"></table>
        </div>

        <div class="tab-content" id="dental_transaction">
            <table id="transactionTable" class="transaction-table"></table>
        </div>
    </div>
</div>

<div id="manageModal" class="manage-calendar-modal">
    <div class="manage-calendar-modal-content">
        <div id="modalBody" class="manage-calendar-modal-content-body">
            <!-- Appointment info will be loaded here -->
        </div>
    </div>
</div>

<div id="transactionModal" class="transaction-record-modal">
    <div class="transaction-record-modal-content">
        <div id="transactionModalBody">
            <!-- Transaction info will be loaded here -->
        </div>
    </div>
</div>

<div id="medCertModal" class="booking-modal" data-transaction-id="">
    <div class="booking-modal-content">
        <h2>Request Dental Certificate</h2>

        <?php if ($medcertPrice > 0): ?>
            <p>Please scan the QR code below to pay for the Dental Certificate fee.</p>

            <div style="text-align: center; margin: 15px 0;">
                <img src="<?= htmlspecialchars($qrImage) ?>" alt="Payment QR Code" style="width: 210px; height: 300px; border: 1px solid #ccc; border-radius: 4px;">
                
                <p><strong>Amount:</strong> ₱<?= number_format($medcertPrice, 0) ?></p>
            </div>
        <?php else: ?>
            <p><strong>This Dental Certificate is FREE.</strong></p>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/Patient/processes/profile/upload_medcert_payment.php" method="POST" enctype="multipart/form-data" id="medCertForm">

            <input type="hidden" name="dental_transaction_id" id="transactionIdInput">

            <label for="paymentReceipt" style="font-weight: bold;">
                Upload Payment Screenshot:
            </label>

            <input type="file" id="paymentReceipt" name="payment_receipt" accept="image/*"
                <?= $medcertPrice > 0 ? "required" : "" ?>
                <?= $medcertPrice == 0 ? "disabled" : "" ?>
                style="margin-top: 10px; width: 100%;">
            
            <?php if ($medcertPrice == 0): ?>
            <?php endif; ?>

            <div class="button-group">
                <button type="submit" class="confirm-btn">Submit</button>
            </div>
        </form>
    </div>
</div>

<div id="medCertReceiptModal" class="booking-modal">
    <div class="booking-modal-content" id="medCertReceiptBody">
        <!-- Receipt will be loaded dynamically -->
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>

<script>
$(document).ready(function () {

    if ($.fn.DataTable.isDataTable('#appointmentTable')) {
        $('#appointmentTable').DataTable().clear().destroy();
    }

    $('#appointmentTable').DataTable({
        destroy: true,
        ajax: `${BASE_URL}/Patient/processes/profile/dependent_accounts/load_dependent_appointments.php?user_id=<?= $dependent_id ?>`,
        pageLength: 20,
        lengthChange: false,
        ordering: true,
        searching: true,
        order: [[3, "desc"], [4, "asc"]],
        columns: [
            { title: "Dentist", searchable: false },
            { title: "Branch" },
            { title: "Service" },
            { title: "Date", searchable: false },
            { title: "Time", searchable: false },
            { title: "Status", searchable: false },
            { title: "Action", orderable: false },
            { title: "Created", visible: false, searchable: false }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search"
        },
        initComplete: function () {
            const $input = $('#appointmentTable_filter input[type=search]');
            $input.attr('id', 'appointmentDependentSearch');
            $('#appointmentTable_filter label').attr('for', 'appointmentDependentSearch');
        }
    });

    if ($.fn.DataTable.isDataTable('#transactionTable')) {
        $('#transactionTable').DataTable().clear().destroy();
    }

    $('#transactionTable').DataTable({
        destroy: true,
        ajax: `${BASE_URL}/Patient/processes/profile/dependent_accounts/load_dependent_transactions.php?user_id=<?= $dependent_id ?>`,
        pageLength: 20,
        lengthChange: false,
        ordering: true,
        searching: true,
        order: [[3, "desc"], [4, "asc"]],
        columns: [
            { title: "Dentist", searchable: false },
            { title: "Branch" },
            { title: "Service" },
            { title: "Date", searchable: false },
            { title: "Time", searchable: false },
            { title: "Amount", searchable: false },
            { title: "Action", orderable: false },
            { title: "Created", visible: false, searchable: false }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search"
        },
        initComplete: function () {
            const $input = $('#transactionTable_filter input[type=search]');
            $input.attr('id', 'transactionDependentSearch');
            $('#transactionTable_filter label').attr('for', 'transactionDependentSearch');
        }
    });

});

function openMedCertModal(transactionId) {
    const modal = document.getElementById("medCertModal");
    const transactionInput = document.getElementById("transactionIdInput");

    transactionInput.value = transactionId;
    modal.style.display = "block";
}

function closeMedCertReceiptModal() {
    document.getElementById("medCertReceiptModal").style.display = "none";
}
document.addEventListener("click", (e) => {
    const modal = document.getElementById("medCertReceiptModal");
    if (e.target === modal) closeMedCertReceiptModal();
});
</script>
