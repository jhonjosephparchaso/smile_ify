<?php
session_start();

$currentPage = 'profile';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Patient/includes/navbar.php';
require_once BASE_PATH . '/includes/db.php';

$updateSuccess = $_SESSION['updateSuccess'] ?? "";
$updateError = $_SESSION['updateError'] ?? "";

$expireSql = "
    UPDATE dental_transaction
    SET medcert_status = 'Expired', date_updated = NOW()
    WHERE medcert_status NOT IN ('None', 'Expired')
        AND DATEDIFF(NOW(), date_created) >= 7
";
$conn->query($expireSql);

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

<title>Profile</title>

<div class="profile-container">
    <div class="profile-section">
        <div class="profile-card" id="profileCard">
            <p>Loading profile</p>
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
    </div>
    
    <div class="tabs-container">
        <div class="tabs">
            <div class="tab active" onclick="switchTab('appointment_history')">Appointment History</div>
            <div class="tab" onclick="switchTab('dental_transaction')">Dental Transactions</div>
            <div class="tab" onclick="switchTab('dependant_accounts')">Dependent Accounts</div>
        </div> 

        <div class="tab-content active" id="appointment_history">
            <table id="appointmentTable" class="transaction-table"></table>
        </div>

        <div class="tab-content" id="dental_transaction">
            <table id="transactionTable" class="transaction-table"></table>
        </div>
        
        <div class="tab-content" id="dependant_accounts">
            <table id="dependentTable" class="transaction-table"></table>
        </div>
    </div>
</div>

<div id="editProfileModal" class="edit-profile-modal">
    <div class="edit-profile-modal-content">
        <form id="editProfileForm" method="POST" action="<?= BASE_URL ?>/Patient/processes/profile/update_profile.php" autocomplete="off">
            <div class="form-group phone-group">
                <input type="tel" id="contactNumber" class="form-control" name="contactNumber" oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="[0-9]{10}" title="Mobile number must be 10 digits" required maxlength="10" />
                <label for="contactNumber" class="form-label">Mobile Number <span class="required">*</span></label>
                <span class="phone-prefix">+63</span>
            </div>

            <div class="form-group">
                <textarea id="address" class="form-control" name="address" rows="3" required placeholder=" " autocomplete="off"></textarea>
                <label for="address" class="form-label">Address <span class="required">*</span></label>
            </div>

            <div class="button-group">
                <button type="submit" class="form-button confirm-btn">Save Changes</button>
                <button type="button" class="form-button cancel-btn" onclick="closeEditProfileModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="changePasswordModal" class="change-password-modal">
    <div class="change-password-modal-content">
        <form id="requestOtpChangePassword" method="POST" action="<?= BASE_URL ?>/processes/OTP Processes/change_password/request_otp_change_password.php">
            <p style="text-align:center;">Click below to request an OTP for password change.</p>
            <div class="button-group">
                <button type="submit" class="form-button confirm-btn">Send OTP</button>
                <button type="button" class="form-button cancel-btn" onclick="closeChangePasswordModal()">Cancel</button>
            </div>
        </form> 
    </div>
</div>

<div id="changeEmailModal" class="change-password-modal">
    <div class="change-password-modal-content">
        <form id="requestOtpChangeEmail" method="POST" action="<?= BASE_URL ?>/processes/OTP Processes/change_email/request_otp_change_email.php">
            <p style="text-align:center;">Click below to request an OTP for email change.</p>
            <div class="button-group">
                <button type="submit" class="form-button confirm-btn">Send OTP</button>
                <button type="button" class="form-button cancel-btn" onclick="closeChangeEmailModal()">Cancel</button>
            </div>
        </form> 
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

document.addEventListener("DOMContentLoaded", function () {

    let isSubmitting = false;

    function protectForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener("submit", function (e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }

            isSubmitting = true;

            const btn = form.querySelector("button[type='submit']");
            if (btn) {
                btn.disabled = true;
                btn.innerText = "Sending...";
            }
        });
    }

    protectForm("requestOtpChangePassword");
    protectForm("requestOtpChangeEmail");
});

function viewDependent(userId) {
    window.location.href = "<?= BASE_URL ?>/Patient/pages/manage_dependent.php?user_id=" + userId;
}
</script>
