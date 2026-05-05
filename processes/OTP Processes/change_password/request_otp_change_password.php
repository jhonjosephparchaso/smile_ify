<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $_SESSION['updateError'] = "You must be logged in to request an OTP.";
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

$stmt = $conn->prepare("SELECT userName, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['updateError'] = "User not found.";
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$row = $result->fetch_assoc();
$username = $row["userName"];
$email = $row["email"];

$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_created'] = time();

$_SESSION['verified_data'] = [
    'email' => $email,
    'user_id' => $userId
];

require BASE_PATH . '/Mail/phpmailer/PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host       = SMTP_HOST;
$mail->Port       = SMTP_PORT;
$mail->SMTPAuth   = SMTP_AUTH;
$mail->SMTPSecure = SMTP_SECURE;
$mail->Username   = SMTP_USER;
$mail->Password   = SMTP_PASS;

$mail->setFrom('smileify.web@gmail.com', 'Smile-ify OTP Verification');
$mail->addAddress($email);

$mail->isHTML(true);
$mail->Subject = "Smile-ify Change Password OTP";
$mail->Body = "
    <p>Dear <strong>$username</strong>,</p>
    <p>Your OTP for changing your password is:</p>
    <h3>$otp</h3>
    <br>
    <p><i>Smile with confidence.</i></p>
    <p>Best regards,<br><strong>Smile-ify</strong></p>
";

if (!$mail->send()) {
    $_SESSION['updateError'] = "Failed to send OTP. Please try again later.";
    
    if ($role === 'admin') {
        header("Location: " . BASE_URL . "/Admin/pages/profile.php");
    } elseif ($role === 'owner') {
        header("Location: " . BASE_URL . "/Owner/pages/profile.php");
    } else {
        header("Location: " . BASE_URL . "/Patient/pages/profile.php");
    }
    exit;
}

header("Location: " . BASE_URL . "/includes/OTP Includes/change_password/otp_verification_change_password.php");
exit;
