<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $_SESSION['password_error'] = "Passwords do not match.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/forgot_password/reset_password.php");
        exit;
    }

    if (!isset($_SESSION['reset_username'])) {
        $_SESSION['password_error'] = "Session error. Please try again.";
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }

    $username = $_SESSION['reset_username'];

    $user_id_stmt = $conn->prepare("SELECT user_id FROM users WHERE userName = ?");
    $user_id_stmt->bind_param("s", $username);
    $user_id_stmt->execute();
    $user_id_stmt->bind_result($user_id);
    $user_id_stmt->fetch();
    $user_id_stmt->close();

    if (empty($user_id)) {
        $_SESSION['password_error'] = "User not found.";
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE userName = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($currentHashedPassword);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($newPassword, $currentHashedPassword)) {
        $_SESSION['password_error'] = "New password cannot be the same as your previous password.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/forgot_password/reset_password.php");
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE userName = ?");
    $stmt->bind_param("ss", $hashedPassword, $username);

    if ($stmt->execute()) {
        $msg = "Your password was changed successfully on " . date("F j, Y, g:i a") . ". If this wasn’t you, please contact clinic immediately.";
        $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_stmt->bind_param("is", $user_id, $msg);
        $notif_stmt->execute();
        $notif_stmt->close();

        unset($_SESSION['otp_verified'], $_SESSION['reset_username']);

        $_SESSION['login_success'] = "Password reset successful. You can now login.";
        header("Location: " . BASE_URL . "/index.php");
        exit;
    } else {
        $_SESSION['password_error'] = "Failed to reset password.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/forgot_password/reset_password.php");
        exit;
    }
}
?>
