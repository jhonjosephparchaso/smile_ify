<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';
require_once BASE_PATH . '/includes/header.php';

if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$emailError = '';
if (isset($_SESSION['updateError'])) {
    $emailError = $_SESSION['updateError'];
    unset($_SESSION['updateError']);
}

$role = $_SESSION['role'] ?? 'patient';
$redirects = [
    'admin' => BASE_URL . '/Admin/pages/profile.php',
    'owner' => BASE_URL . '/Owner/pages/profile.php',
    'patient' => BASE_URL . '/Patient/pages/profile.php'
];

$cancelRedirect = $redirects[$role] ?? BASE_URL . '/Patient/pages/profile.php';
?>

<head>
    <title>Email Reset</title>
</head>
<body>
    <div class="reset-password-modal">
        <div class="reset-password-modal-content">
            <h2>Email Reset</h2>

            <?php if (!empty($emailError)): ?>
                <div class="error"><?= htmlspecialchars($emailError) ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/processes/OTP Processes/change_email/reset_email.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <input type="email" id="newEmail" name="new_email" class="form-control" placeholder=" " required>
                    <label for="newEmail" class="form-label">Enter New Email <span class="required">*</span></label>
                    <span id="emailError" class="error-msg-calendar error" style="display:none"></span>
                </div>

                <div class="form-group">
                    <input type="email" id="confirmEmail" name="confirm_email" class="form-control" placeholder=" " required>
                    <label for="confirmEmail" class="form-label">Confirm New Email <span class="required">*</span></label>
                    <span id="confirmEmailError" class="error-msg-calendar error" style="display:none"></span>
                </div>

                <div class="button-group">
                    <button type="submit" name="verify" class="form-button confirm-btn" id="confirmButton">Confirm</button>
                    <button type="button" onclick="sessionStorage.clear(); window.location.href='<?= $cancelRedirect ?>'" class="form-button cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form[action*='reset_email.php']");
    const newEmail = document.getElementById("newEmail");
    const confirmEmail = document.getElementById("confirmEmail");
    const emailError = document.getElementById("emailError");
    const confirmEmailError = document.getElementById("confirmEmailError");

    let emailIsValid = false;

    function showEmailError(msg) {
        emailError.textContent = msg;
        emailError.style.display = "block";
    }

    function hideEmailError() {
        emailError.style.display = "none";
    }

    function showConfirmError(msg) {
        confirmEmailError.textContent = msg;
        confirmEmailError.style.display = "block";
    }

    function hideConfirmError() {
        confirmEmailError.style.display = "none";
    }

    function validateMX(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!regex.test(email)) {
            showEmailError("Invalid email format.");
            emailIsValid = false;
            return;
        }

        fetch(`/Smile-ify/processes/validate_email.php?email=${encodeURIComponent(email)}`)
            .then(res => res.json())
            .then(data => {
                if (!data.valid) {
                    showEmailError("Email domain is not reachable.");
                    emailIsValid = false;
                } else {
                    hideEmailError();
                    emailIsValid = true;
                }
            })
            .catch(() => {
                showEmailError("Unable to validate email right now.");
                emailIsValid = false;
            });
    }

    let timer;
    const delay = 600;

    newEmail.addEventListener("input", () => {
        clearTimeout(timer);
        const email = newEmail.value.trim();

        if (email === "") {
            hideEmailError();
            emailIsValid = false;
            return;
        }

        timer = setTimeout(() => validateMX(email), delay);
    });

    confirmEmail.addEventListener("input", () => {
        const email1 = newEmail.value.trim();
        const email2 = confirmEmail.value.trim();

        if (email2 === "") {
            hideConfirmError();
            return;
        }

        if (email1 !== email2) {
            showConfirmError("Emails do not match.");
        } else {
            hideConfirmError();
        }
    });

    form.addEventListener("submit", function (e) {
        const email1 = newEmail.value.trim();
        const email2 = confirmEmail.value.trim();

        if (!emailIsValid) {
            e.preventDefault();
            showEmailError("Please enter a valid email address.");
            newEmail.focus();
            return;
        }

        if (email1 !== email2) {
            e.preventDefault();
            showConfirmError("Emails do not match.");
            confirmEmail.focus();
            return;
        } else {
            hideConfirmError();
        }

        const btn = form.querySelector("button[type='submit']");
        setTimeout(() => { btn.disabled = true; }, 50);
    });
});
</script>

</body>
