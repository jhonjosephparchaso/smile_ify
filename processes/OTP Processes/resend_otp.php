<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $row = $result->fetch_assoc();
    $username = $row['username'];
    $email = $row['email'];

} elseif (isset($_SESSION['pending_login_user'])) {
    $pendingUser = $_SESSION['pending_login_user'];
    $email = $pendingUser['email'];
    $username = $pendingUser['username'];

} elseif (isset($_SESSION['verified_data'])) {
    $verified_data = $_SESSION['verified_data'];
    $email = $verified_data['email'];
    $username = $verified_data['username'] ?? "Customer/Patient";

} else {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in or re-enter your email.']);
    exit;
}

$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_created'] = time();

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
$mail->Subject = "Smile-ify OTP Resend";
$mail->Body = "
    <p>Dear <strong>$username</strong>,</p>
    <p>Here is your OTP code:</p>
    <h3>$otp</h3>
    <br>
    <p><i>Smile with confidence.</i></p>
    <p>Best regards,<br><strong>Smile-ify</strong></p>
";

if (!$mail->send()) {
    echo json_encode(['success' => false, 'message' => 'Failed to resend OTP. Please try again.']);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'OTP resent successfully.',
        'otp_created' => $_SESSION['otp_created']
    ]);
}
?>
