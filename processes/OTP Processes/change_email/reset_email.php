<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

function isValidEmailDomain($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, "MX");
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    $_SESSION['updateError'] = "Unauthorized email change attempt.";
    header("Location: " . BASE_URL . "/includes/OTP Includes/change_email/reset_email.php");
    exit;
}

$role = $_SESSION['role'] ?? 'patient';
$redirects = [
    'admin'   => BASE_URL . '/Admin/pages/profile.php',
    'owner'   => BASE_URL . '/Owner/pages/profile.php',
    'patient' => BASE_URL . '/Patient/pages/profile.php'
];
$profileRedirect = $redirects[$role] ?? BASE_URL . '/Patient/pages/profile.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newEmail = trim($_POST['new_email'] ?? '');
    $confirmEmail = trim($_POST['confirm_email'] ?? '');

    if ($newEmail !== $confirmEmail) {
        $_SESSION['updateError'] = "Email addresses do not match.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/change_email/reset_email.php");
        exit;
    }

    if (!isValidEmailDomain($newEmail)) {
        $_SESSION['updateError'] = "Invalid or unreachable email domain.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/change_email/reset_email.php");
        exit;
    }

    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($currentEmail);
    $stmt->fetch();
    $stmt->close();

    if ($newEmail === $currentEmail) {
        $_SESSION['updateError'] = "New email cannot be the same as your current email.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/change_email/reset_email.php");
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET email = ?, date_updated = NOW() WHERE user_id = ?");
    $stmt->bind_param("si", $newEmail, $userId);

    if ($stmt->execute()) {
        $msg = "Your email was successfully updated to {$newEmail} on " . date("F j, Y, g:i a") . ". If this wasn’t you, please contact the clinic immediately.";
        $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_stmt->bind_param("is", $userId, $msg);
        $notif_stmt->execute();
        $notif_stmt->close();

        unset($_SESSION['otp_verified']);

        $_SESSION['updateSuccess'] = "Email address updated successfully.";
        header("Location: " . $profileRedirect);
        exit;
    } else {
        $_SESSION['updateError'] = "Failed to update email. Please try again.";
        header("Location: " . $profileRedirect);
        exit;
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
