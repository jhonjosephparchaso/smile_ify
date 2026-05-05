<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $enteredOtp = trim($_POST['otpCode']);
    $generatedOtp = $_SESSION['otp'] ?? null;
    $createdAt = $_SESSION['otp_created'] ?? 0;
    $pendingUser = $_SESSION['pending_login_user'] ?? null;

    if (!$generatedOtp || !$pendingUser) {
        $_SESSION['otp_error'] = "Session expired. Please log in again.";
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }

    if (time() - $createdAt > 300) {
        $_SESSION['otp_error'] = "OTP expired. Please request a new OTP.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/login/otp_verification_login.php");
        exit;
    }

    if ($enteredOtp == $generatedOtp) {
        $_SESSION['user_id'] = $pendingUser['user_id'];
        $_SESSION['username'] = $pendingUser['username'];
        $_SESSION['role'] = $pendingUser['role'];
        $_SESSION['branch_id'] = $pendingUser['branch_id'];
        $_SESSION['branch_name'] = $pendingUser['branch_name'];
        $_SESSION['first_name'] = $pendingUser['first_name'];
        $_SESSION['last_name'] = $pendingUser['last_name'];
        $_SESSION['gender'] = $pendingUser['gender'];

        $role = strtolower($pendingUser['role']);

        unset($_SESSION['otp'], $_SESSION['otp_created'], $_SESSION['pending_login_user']);

        switch ($role) {
            case 'owner':
                header("Location: " . BASE_URL . "/Owner/index.php");
                break;
            case 'admin':
                header("Location: " . BASE_URL . "/Admin/index.php");
                break;
            case 'patient':
                header("Location: " . BASE_URL . "/Patient/index.php");
                break;
            default:
                header("Location: " . BASE_URL . "/index.php");
                break;
        }
        exit;
    } else {
        $_SESSION['otp_error'] = "Invalid OTP. Please try again.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/login/otp_verification_login.php");
        exit;
    }
}
?>
