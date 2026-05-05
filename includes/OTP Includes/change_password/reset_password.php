<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';
require_once BASE_PATH . '/includes/header.php';

if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$passwordError = '';
if (isset($_SESSION['updateError'])) {
    $passwordError = $_SESSION['updateError'];
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
    <title>Password Reset</title>
</head>
<body>
    <div class="reset-password-modal">
        <div class="reset-password-modal-content">
            <h2>Password Reset</h2>

            <?php if (!empty($passwordError)): ?>
                <div class="error"><?= htmlspecialchars($passwordError) ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/processes/OTP Processes/change_password/reset_password.php" method="POST">
                <div class="form-group">
                    <input type="password" id="newPassword" name="new_password" class="form-control" placeholder=" " required autocomplete="off" 
                        pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$"
                        title="Must contain at least 8 characters, including uppercase, lowercase, number, and special character"
                        onpaste="return false" />
                    <label for="newPassword" class="form-label">Enter Password <span class="required">*</span></label>
                    <span onclick="togglePassword('newPassword')" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; font-size: 20px;">👁</span>
                </div>

                <div class="form-group">
                    <input type="password" id="confirmPassword" name="confirm_password" class="form-control" placeholder=" " required autocomplete="off" 
                        pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$"
                        title="Must contain at least 8 characters, including uppercase, lowercase, number, and special character"
                        onpaste="return false" />
                    <label for="confirmPassword" class="form-label">Confirm Password <span class="required">*</span></label>
                    <span onclick="togglePassword('confirmPassword')" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; font-size: 20px;">👁</span>
                </div>

                <div class="button-group">
                    <button type="submit" name="verify" class="form-button confirm-btn" id="confirmButton">Confirm</button>
                    <button type="button" onclick="sessionStorage.clear(); window.location.href='<?= $cancelRedirect ?>'" class="form-button cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
