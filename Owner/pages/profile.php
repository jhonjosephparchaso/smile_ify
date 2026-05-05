<?php
session_start();

$currentPage = 'profile';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Owner/includes/navbar.php';  
$updateSuccess = $_SESSION['updateSuccess'] ?? "";
$updateError = $_SESSION['updateError'] ?? "";
?>
<title>Branches</title>

<div class="profile-container">
    <div class="profile-section">
        <div class="profile-card" id="profileCard">
            <p>Loading profile</p>
        </div>
        
        <?php
            require_once BASE_PATH . '/includes/db.php';
            $qrImage = null;

            $result = $conn->query("SELECT file_path FROM qr_payment ORDER BY id DESC LIMIT 1");
            if ($result && $row = $result->fetch_assoc()) {
                $qrImage = BASE_URL . $row['file_path'];
            }
        ?>

        <script>
            const QR_IMAGE_URL = "<?= $qrImage ? htmlspecialchars($qrImage . '?v=' . time()) : '' ?>";
        </script>

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

<div id="editProfileModal" class="edit-profile-modal">
    <div class="edit-profile-modal-content">
        <form id="editProfileForm" method="POST" action="<?= BASE_URL ?>/Owner/processes/profile/update_profile.php" autocomplete="off">
            <div class="form-group phone-group">
                <input type="tel" id="contactNumber" class="form-control" name="contactNumber" oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="[0-9]{10}" title="Mobile number must be 10 digits" required maxlength="10" />
                <label for="contactNumber" class="form-label">Mobile Number <span class="required">*</span></label>
                <span class="phone-prefix">+63</span>
            </div>

            <div class="form-group">
                <textarea id="address" class="form-control" name="address" rows="3" required placeholder=" "autocomplete="off"></textarea>
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

    <div class="tabs-container-branch">
        <div class="tabs-branch">
            <div class="tab active">Branches</div>
        </div>

        <div class="branches-table">
            <table id="branchesTable" class="transaction-table"></table>
        </div>
    </div>
</div>

<div id="manageBranchModal" class="manage-branch-modal">
    <div class="manage-branch-modal-content">
        <div id="branchModalBody" class="manage-branch-modal-content-body">
            <!-- Branch info will be loaded here -->
        </div>
    </div>
</div>

<div id="branchDeactivateModal" class="change-password-modal" style="display:none;">
    <div class="change-password-modal-content" style="max-width: 650px;">
        <h3>Deactivate Branch?</h3>
        <p id="branchDeactivateMessage">
            Checking affected appointments...
        </p>

        <div class="button-group">
            <button id="confirmDeactivateYes" class="form-button confirm-btn">Yes, Deactivate</button>
            <button id="confirmDeactivateNo" class="form-button cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>

<script>
function openBranchDeactivateConfirm(branchId, onConfirm) {
    const modal = document.getElementById("branchDeactivateModal");
    const message = document.getElementById("branchDeactivateMessage");

    modal.style.display = "block";
    message.innerHTML = "Checking affected appointments...";

    fetch(`${BASE_URL}/Owner/processes/profile/branches/check_affected_appointments.php?branch_id=${branchId}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                message.innerHTML = "Unable to check affected appointments.";
                return;
            }

            if (data.count > 0) {
                message.innerHTML = `
                    This branch has <strong>${data.count}</strong> active appointment(s).<br><br>
                    Deactivating it will affect bookings.
                `;
            } else {
                message.innerHTML = `
                    No active appointments found.<br><br>
                    You may safely deactivate this branch.
                `;
            }
        })
        .catch(() => {
            message.innerHTML = "Error checking appointments.";
        });

    document.getElementById("confirmDeactivateYes").onclick = () => {
        modal.style.display = "none";
        onConfirm();
    };

    document.getElementById("confirmDeactivateNo").onclick = () => {
        modal.style.display = "none";
    };
}

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
</script>
