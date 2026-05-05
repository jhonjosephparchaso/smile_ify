<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = trim($_POST['otpCode']);

    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_created'])) {
        $_SESSION['otp_error'] = "OTP session expired.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/forgot_password/otp_verification_forgot_password.php");
        exit;
    }

    $originalOtp = (string) $_SESSION['otp'];
    $otpCreatedTime = (int) $_SESSION['otp_created'];
    $currentTime = time();

    if (($currentTime - $otpCreatedTime) > 300) {
        $_SESSION['otp_error'] = "OTP expired. Please request a new one.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/forgot_password/otp_verification_forgot_password.php");
        exit;
    }

    if ((string)$enteredOtp !== $originalOtp) {
        $_SESSION['otp_error'] = "Incorrect OTP. Please try again.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/forgot_password/otp_verification_forgot_password.php");
        exit;
    }

    $_SESSION['otp_verified'] = true;
    if (isset($_SESSION['verified_data']['username'])) {
        $_SESSION['reset_username'] = $_SESSION['verified_data']['username'];
    }
    header("Location: " . BASE_URL . "/includes/OTP Includes/forgot_password/reset_password.php");
    exit;
}
